<?php

namespace Pjedesigns\FilamentNestedSetTable\Services;

use Illuminate\Database\Eloquent\Model;

class TreeMover
{
    /**
     * Move a node to a new position in the tree.
     */
    public function move(
        Model $node,
        ?int $newParentId,
        int $newPosition,
        int $maxDepth = 0
    ): MoveResult {
        $wasAutoAdjusted = false;

        // Check max depth and auto-adjust if needed
        if ($maxDepth > 0 && $newParentId !== null) {
            $newParent = $node->newQuery()->withDepth()->find($newParentId);

            if (! $newParent) {
                return MoveResult::failure('The parent item could not be found.');
            }

            $targetDepth = ($newParent->depth ?? 0) + 1;
            $subtreeDepth = $this->getSubtreeDepth($node);

            if ($targetDepth + $subtreeDepth > $maxDepth) {
                // Auto-adjust: make sibling of would-be parent instead
                $newPosition = $this->getSiblingPosition($newParent) + 1;
                $newParentId = $newParent->parent_id;
                $wasAutoAdjusted = true;
            }
        }

        try {
            if ($newParentId === null) {
                return $this->moveToRoot($node, $newPosition, $wasAutoAdjusted);
            }

            if ($node->parent_id !== $newParentId) {
                return $this->moveToNewParent($node, $newParentId, $newPosition, $wasAutoAdjusted);
            }

            return $this->reorderWithinParent($node, $newPosition, $wasAutoAdjusted);
        } catch (\Exception $e) {
            return MoveResult::failure($e->getMessage());
        }
    }

    /**
     * Move a node to become a root node at the specified position.
     */
    protected function moveToRoot(Model $node, int $position, bool $wasAutoAdjusted): MoveResult
    {
        // Check if already at root level
        if ($node->parent_id === null) {
            // Just reorder among roots
            return $this->reorderAtRoot($node, $position, $wasAutoAdjusted);
        }

        $node->makeRoot();
        $node->refresh();

        // Reorder to exact position among roots
        return $this->reorderAtRoot($node, $position, $wasAutoAdjusted);
    }

    /**
     * Reorder a root node among other root nodes.
     */
    protected function reorderAtRoot(Model $node, int $position, bool $wasAutoAdjusted): MoveResult
    {
        $roots = $node->newQuery()
            ->whereNull('parent_id')
            ->where('id', '!=', $node->id)
            ->defaultOrder()
            ->get();

        if ($roots->isEmpty()) {
            return MoveResult::success(
                newParentId: null,
                newPosition: 0,
                wasAutoAdjusted: $wasAutoAdjusted
            );
        }

        if ($position === 0) {
            $firstRoot = $roots->first();
            if ($firstRoot) {
                $node->insertBeforeNode($firstRoot);
            }
        } elseif ($position >= $roots->count()) {
            $lastRoot = $roots->last();
            if ($lastRoot) {
                $node->insertAfterNode($lastRoot);
            }
        } else {
            $targetNode = $roots->get($position);
            if ($targetNode) {
                $node->insertBeforeNode($targetNode);
            }
        }

        return MoveResult::success(
            newParentId: null,
            newPosition: $position,
            wasAutoAdjusted: $wasAutoAdjusted
        );
    }

    /**
     * Move a node to become a child of a new parent.
     */
    protected function moveToNewParent(Model $node, int $parentId, int $position, bool $wasAutoAdjusted): MoveResult
    {
        $parent = $node->newQuery()->find($parentId);

        if (! $parent) {
            return MoveResult::failure('The parent item could not be found.');
        }

        // Check for circular reference
        if ($parent->isDescendantOf($node)) {
            return MoveResult::failure('Cannot move an item under its own descendant.');
        }

        // Get existing children count before the move
        $existingChildrenCount = $parent->children()->count();

        // Make this node a child of the parent
        $parent->appendNode($node);
        $node->refresh();

        // Reorder to exact position among siblings if not appending to end
        if ($existingChildrenCount > 0 && $position < $existingChildrenCount) {
            $siblings = $parent->children()->where('id', '!=', $node->id)->defaultOrder()->get();
            $targetSibling = $siblings->get($position);

            if ($targetSibling) {
                $node->insertBeforeNode($targetSibling);
            }
        }

        return MoveResult::success(
            newParentId: $parentId,
            newPosition: $position,
            wasAutoAdjusted: $wasAutoAdjusted
        );
    }

    /**
     * Reorder a node among its current siblings.
     */
    protected function reorderWithinParent(Model $node, int $position, bool $wasAutoAdjusted): MoveResult
    {
        // Get all children of the parent (including this node)
        $parentId = $node->parent_id;

        $allSiblings = $node->newQuery()
            ->where('parent_id', $parentId)
            ->defaultOrder()
            ->get();

        $currentIndex = $allSiblings->search(fn ($sibling) => $sibling->id === $node->id);

        if ($currentIndex === false) {
            return MoveResult::failure('Failed to reorder the item.');
        }

        if ($currentIndex === $position) {
            // Already at the correct position
            return MoveResult::success(
                newParentId: $parentId,
                newPosition: $position,
                wasAutoAdjusted: $wasAutoAdjusted
            );
        }

        // Get siblings without the current node for accurate positioning
        $siblingsWithoutNode = $allSiblings->filter(fn ($sibling) => $sibling->id !== $node->id)->values();

        if ($siblingsWithoutNode->isEmpty()) {
            // No siblings, nothing to reorder
            return MoveResult::success(
                newParentId: $parentId,
                newPosition: 0,
                wasAutoAdjusted: $wasAutoAdjusted
            );
        }

        if ($position === 0) {
            $firstSibling = $siblingsWithoutNode->first();
            if ($firstSibling) {
                $node->insertBeforeNode($firstSibling);
            }
        } elseif ($position >= $siblingsWithoutNode->count()) {
            $lastSibling = $siblingsWithoutNode->last();
            if ($lastSibling) {
                $node->insertAfterNode($lastSibling);
            }
        } else {
            $targetSibling = $siblingsWithoutNode->get($position);
            if ($targetSibling) {
                $node->insertBeforeNode($targetSibling);
            }
        }

        return MoveResult::success(
            newParentId: $parentId,
            newPosition: $position,
            wasAutoAdjusted: $wasAutoAdjusted
        );
    }

    /**
     * Get the position of a node among its siblings.
     */
    protected function getSiblingPosition(Model $node): int
    {
        return $node->newQuery()
            ->where('parent_id', $node->parent_id)
            ->where('_lft', '<', $node->_lft)
            ->count();
    }

    /**
     * Get the maximum depth of the subtree rooted at the given node.
     */
    protected function getSubtreeDepth(Model $node): int
    {
        $descendants = $node->descendants()->withDepth()->get();

        if ($descendants->isEmpty()) {
            return 0;
        }

        // Refresh node with depth if not already loaded
        if (! isset($node->depth)) {
            $node = $node->newQuery()->withDepth()->find($node->id);
        }

        $maxDescendantDepth = $descendants->max('depth');
        $nodeDepth = $node->depth ?? 0;

        return $maxDescendantDepth - $nodeDepth;
    }
}
