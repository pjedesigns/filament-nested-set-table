<?php

namespace Pjedesigns\FilamentNestedSetTable\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoveFailed;
use Pjedesigns\FilamentNestedSetTable\Events\TreeFixed;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;

/**
 * Dedicated ordering page for tree structures.
 *
 * This page is optimized for reordering:
 * - All nodes loaded at once (no lazy loading)
 * - Expand/collapse is pure JavaScript (no server calls)
 * - Server calls only on move operations
 *
 * Usage with a Resource (recommended):
 * ```php
 * protected static string $resource = CategoryResource::class;
 * ```
 *
 * The model is automatically resolved from the resource.
 *
 * For nested resources (child resources), the trait InteractsWithParentRecord
 * is automatically included to handle parent record resolution.
 */
abstract class OrderPage extends Page
{
    use InteractsWithParentRecord;

    protected string $view = 'filament-nested-set-table::pages.order-page';

    /**
     * Store for undo functionality.
     */
    public ?array $lastMove = null;

    /**
     * Get the page title.
     * Defaults to "Order {PluralModelLabel}" if not set.
     */
    public function getTitle(): string|Htmlable
    {
        return static::$title ?? __('filament-nested-set-table::messages.order').' '.Str::headline(static::getResource()::getPluralModelLabel());
    }

    /**
     * Get the column to display as the node label.
     */
    public function getLabelColumn(): string
    {
        return 'title';
    }

    /**
     * Get the maximum tree depth (0 = unlimited).
     */
    public function getMaxDepth(): int
    {
        return config('filament-nested-set-table.max_depth', 0);
    }

    /**
     * Get the indent size in pixels.
     */
    public function getIndentSize(): int
    {
        return config('filament-nested-set-table.indent_size', 24);
    }

    /**
     * Get relationships to eager load.
     */
    public function getEagerLoading(): array
    {
        return [];
    }

    /**
     * Determine if drag and drop is enabled.
     */
    public function isDragEnabled(): bool
    {
        return config('filament-nested-set-table.drag_enabled', true);
    }

    /**
     * Get the tree scope attributes for scoped nested sets.
     * Override this method if you need to filter by scope.
     */
    public function getScopeFilter(): array
    {
        return [];
    }

    /**
     * Get nodes for Alpine.js to fetch via $wire call.
     * This is a public method that can be called from Alpine.
     * It forces a fresh query to get the current tree state.
     */
    public function getNodesForAlpine(): array
    {
        // Clear the computed property cache to force fresh data
        unset($this->nodes);

        return $this->nodes;
    }

    /**
     * Get all nodes for the tree, fully loaded.
     * Returns a flat array with all nodes for Alpine.js to render recursively.
     */
    #[Computed]
    public function nodes(): array
    {
        $model = $this->getModel();
        $eagerLoad = $this->getEagerLoading();
        $scopeFilter = $this->getScopeFilter();

        $query = $model::query()
            ->withDepth()
            ->withCount('children')
            ->defaultOrder();

        // Apply scope filter if provided
        foreach ($scopeFilter as $column => $value) {
            $query->where($column, $value);
        }

        // Apply eager loading if configured
        if (! empty($eagerLoad)) {
            $query->with($eagerLoad);
        }

        $nodes = $query->get();

        // For scoped trees, withDepth() may return -1, so we calculate depth manually
        $depthMap = $this->calculateDepths($nodes);

        return $nodes
            ->map(fn (Model $node) => $this->transformNode($node, $depthMap[$node->getKey()] ?? 0))
            ->toArray();
    }

    /**
     * Calculate depths manually for scoped trees where withDepth() returns -1.
     *
     * @param  \Illuminate\Support\Collection  $nodes
     * @return array<int, int> Map of node ID to depth
     */
    protected function calculateDepths($nodes): array
    {
        $depthMap = [];
        $nodeMap = $nodes->keyBy(fn ($node) => $node->getKey());

        foreach ($nodes as $node) {
            $depth = 0;
            $currentParentId = $node->parent_id;

            while ($currentParentId !== null) {
                $depth++;
                $parent = $nodeMap->get($currentParentId);
                $currentParentId = $parent?->parent_id;
            }

            $depthMap[$node->getKey()] = $depth;
        }

        return $depthMap;
    }

    /**
     * Transform a node model into an array for the frontend.
     *
     * @param  int|null  $calculatedDepth  Manually calculated depth for scoped trees
     */
    protected function transformNode(Model $node, ?int $calculatedDepth = null): array
    {
        $labelColumn = $this->getLabelColumn();

        // Use calculated depth if provided, otherwise fall back to model depth
        $depth = $calculatedDepth ?? ($node->depth >= 0 ? $node->depth : 0);

        $data = [
            'id' => $node->getKey(),
            'parent_id' => $node->parent_id,
            'label' => $node->getAttribute($labelColumn),
            'depth' => $depth,
            'has_children' => ($node->children_count ?? 0) > 0,
            'children_count' => $node->children_count ?? 0,
        ];

        // Add icon if the model supports it
        if (method_exists($node, 'getTreeIcon')) {
            $data['icon'] = $node->getTreeIcon();
        }

        // Add draggable status if the model supports it
        if (method_exists($node, 'canBeDragged')) {
            $data['can_drag'] = $node->canBeDragged();
        } else {
            $data['can_drag'] = true;
        }

        // Add can have children status if the model supports it
        if (method_exists($node, 'canHaveChildren')) {
            $data['can_have_children'] = $node->canHaveChildren();
        } else {
            $data['can_have_children'] = true;
        }

        return $data;
    }

    /**
     * Handle node move - this is the ONLY server call during reordering.
     *
     * @param  int  $nodeId  The ID of the node being moved
     * @param  int|null  $targetNodeId  The ID of the target node
     * @param  bool  $insertBefore  If true, insert before target; if false, insert after (when not making child)
     * @param  bool  $makeChild  If true, make the node a child of the target node
     */
    public function moveNode(
        int $nodeId,
        ?int $targetNodeId,
        bool $insertBefore = true,
        bool $makeChild = false
    ): void {
        $model = $this->getModel();
        $node = $model::withDepth()->find($nodeId);
        $targetNode = $targetNodeId ? $model::withDepth()->find($targetNodeId) : null;

        if (! $node) {
            $this->notifyError(__('filament-nested-set-table::messages.node_not_found'));

            return;
        }

        // Authorization check
        if (! $this->authorizeMove($node)) {
            event(new NodeMoveFailed(
                node: $node,
                error: 'Unauthorized',
                attemptedParentId: $targetNodeId,
                attemptedPosition: 0
            ));
            $this->notifyError(__('filament-nested-set-table::messages.unauthorized'));

            return;
        }

        // Determine the new parent based on operation
        $newParentId = $makeChild ? $targetNodeId : $targetNode?->parent_id;

        // Prevent circular reference - can't make a node a child of its own descendant
        if ($makeChild && $targetNode && $node->isAncestorOf($targetNode)) {
            $this->notifyError(__('filament-nested-set-table::messages.circular_reference'));

            return;
        }

        // Check if target node can have children
        if ($makeChild && $targetNode) {
            $canHaveChildren = method_exists($targetNode, 'canHaveChildren')
                ? $targetNode->canHaveChildren()
                : true;

            if (! $canHaveChildren) {
                $this->notifyError(__('filament-nested-set-table::messages.cannot_have_children'));

                return;
            }
        }

        // Max depth check
        $maxDepth = $this->getMaxDepth();
        if ($maxDepth > 0) {
            $targetDepth = $makeChild
                ? (($targetNode->depth ?? 0) + 1)
                : ($targetNode->depth ?? 0);

            $nodeSubtreeDepth = $this->getSubtreeDepth($node);
            $resultingMaxDepth = $targetDepth + $nodeSubtreeDepth;

            if ($resultingMaxDepth > $maxDepth) {
                $this->notifyError(__('filament-nested-set-table::messages.max_depth_exceeded', [
                    'max' => $maxDepth,
                    'resulting' => $resultingMaxDepth,
                ]));

                return;
            }
        }

        // Scope validation
        if (! $this->validateScopeMove($node, $newParentId)) {
            event(new NodeMoveFailed(
                node: $node,
                error: 'Cross-scope move',
                attemptedParentId: $newParentId,
                attemptedPosition: 0
            ));
            $this->notifyError(__('filament-nested-set-table::messages.cross_scope'));

            return;
        }

        // Store for undo
        $previousParentId = $node->parent_id;
        $previousPosition = $this->getNodePosition($node);

        $this->lastMove = [
            'nodeId' => $nodeId,
            'oldParentId' => $previousParentId,
            'oldPosition' => $previousPosition,
            'timestamp' => now()->timestamp,
        ];

        try {
            if ($makeChild && $targetNode) {
                // Make node a child of target (append as last child)
                $targetNode->appendNode($node);

                $result = MoveResult::success(
                    newParentId: $targetNodeId,
                    newPosition: $this->getNodePosition($node->fresh()),
                    wasAutoAdjusted: false
                );
            } elseif ($targetNode) {
                // Insert before or after the target node as sibling
                if ($insertBefore) {
                    $node->insertBeforeNode($targetNode);
                } else {
                    $node->insertAfterNode($targetNode);
                }

                $result = MoveResult::success(
                    newParentId: $targetNode->parent_id,
                    newPosition: $this->getNodePosition($node->fresh()),
                    wasAutoAdjusted: false
                );
            } else {
                // Move to root (no target node)
                $node->makeRoot();
                $result = MoveResult::success(
                    newParentId: null,
                    newPosition: 0,
                    wasAutoAdjusted: false
                );
            }

            event(new NodeMoved($node->fresh(), $result, $previousParentId, $previousPosition));

            // Send notification with Undo action
            $undoDuration = config('filament-nested-set-table.undo_duration', 10);
            Notification::make()
                ->title(__('filament-nested-set-table::messages.move_success'))
                ->success()
                ->actions([
                    Action::make('undo')
                        ->label(__('filament-nested-set-table::messages.undo'))
                        ->dispatch('undoMove')
                        ->close(),
                ])
                ->duration($undoDuration * 1000)
                ->send();

            // Dispatch event for frontend to refresh
            $this->dispatch('tree-updated');
        } catch (\Throwable $e) {
            $result = MoveResult::failure($e->getMessage());
            event(new NodeMoveFailed(
                node: $node,
                error: $result->error ?? $e->getMessage(),
                attemptedParentId: $newParentId,
                attemptedPosition: 0
            ));
            $this->notifyError($e->getMessage());
            $this->lastMove = null;
        }
    }

    /**
     * Undo the last move operation.
     * Can be called directly or via notification action.
     */
    #[On('undoMove')]
    public function undoLastMove(): void
    {
        if (! $this->canUndoMove()) {
            $this->lastMove = null;

            return;
        }

        $model = $this->getModel();
        $node = $model::find($this->lastMove['nodeId']);

        if (! $node) {
            $this->lastMove = null;

            return;
        }

        try {
            $oldParentId = $this->lastMove['oldParentId'];
            $oldPosition = $this->lastMove['oldPosition'];

            if ($oldParentId === null) {
                // Was at root level
                $node->makeRoot();

                // Reorder among roots
                $roots = $model::query()
                    ->whereNull('parent_id')
                    ->where('id', '!=', $node->id)
                    ->defaultOrder()
                    ->get();

                if ($oldPosition > 0 && $roots->count() >= $oldPosition) {
                    $targetRoot = $roots->get($oldPosition - 1);
                    if ($targetRoot) {
                        $node->insertAfterNode($targetRoot);
                    }
                } elseif ($roots->isNotEmpty()) {
                    $node->insertBeforeNode($roots->first());
                }
            } else {
                // Had a parent
                $parent = $model::find($oldParentId);
                if ($parent) {
                    $parent->appendNode($node);

                    // Reorder among siblings
                    $siblings = $parent->children()->where('id', '!=', $node->id)->defaultOrder()->get();
                    if ($oldPosition > 0 && $siblings->count() >= $oldPosition) {
                        $targetSibling = $siblings->get($oldPosition - 1);
                        if ($targetSibling) {
                            $node->insertAfterNode($targetSibling);
                        }
                    } elseif ($siblings->isNotEmpty()) {
                        $node->insertBeforeNode($siblings->first());
                    }
                }
            }

            $this->notifySuccess(__('filament-nested-set-table::messages.undo_success'));
            $this->dispatch('tree-updated');
        } catch (\Throwable $e) {
            $this->notifyError($e->getMessage());
        }

        $this->lastMove = null;
    }

    /**
     * Check if the last move can still be undone.
     */
    #[Computed]
    public function canUndoMove(): bool
    {
        if (! $this->lastMove) {
            return false;
        }

        $undoDuration = config('filament-nested-set-table.undo_duration', 10);

        return (now()->timestamp - $this->lastMove['timestamp']) <= $undoDuration;
    }

    /**
     * Fix corrupted tree structure.
     */
    public function fixTree(): void
    {
        $model = $this->getModel();

        try {
            $model::fixTree();

            event(new TreeFixed($model, 0));

            $this->notifySuccess(__('filament-nested-set-table::messages.tree_fixed'));
            $this->dispatch('tree-updated');
        } catch (\Throwable $e) {
            $this->notifyError($e->getMessage());
        }
    }

    /**
     * Check if the user is authorized to move the given node.
     */
    protected function authorizeMove(Model $node): bool
    {
        $policy = policy($node);

        if ($policy && method_exists($policy, 'reorder')) {
            $result = $policy->reorder(auth()->user(), $node);

            return $result instanceof \Illuminate\Auth\Access\Response ? $result->allowed() : (bool) $result;
        }

        // Default to checking update permission
        if ($policy && method_exists($policy, 'update')) {
            $result = $policy->update(auth()->user(), $node);

            return $result instanceof \Illuminate\Auth\Access\Response ? $result->allowed() : (bool) $result;
        }

        return true;
    }

    /**
     * Validate that a move doesn't cross tree scope boundaries.
     */
    protected function validateScopeMove(Model $node, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return true;
        }

        $newParent = $node->newQuery()->find($newParentId);

        if (! $newParent) {
            return false;
        }

        // Check if model has scope attributes method
        $scopeAttributes = [];

        if (method_exists($node, 'getTreeScopeAttributes')) {
            $scopeAttributes = $node->getTreeScopeAttributes();
        } elseif (method_exists($node, 'getScopeAttributes')) {
            $scopeAttributes = $node->getScopeAttributes();
        }

        if (empty($scopeAttributes)) {
            return true;
        }

        foreach ($scopeAttributes as $attr) {
            if ($node->$attr !== $newParent->$attr) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the depth of the deepest descendant relative to the given node.
     */
    protected function getSubtreeDepth(Model $node): int
    {
        $descendants = $node->descendants()->withDepth()->get();

        if ($descendants->isEmpty()) {
            return 0;
        }

        $nodeDepth = $node->depth ?? 0;
        $maxDescendantDepth = $descendants->max('depth') ?? $nodeDepth;

        return $maxDescendantDepth - $nodeDepth;
    }

    /**
     * Get the position of a node among its siblings.
     */
    protected function getNodePosition(Model $node): int
    {
        if (method_exists($node, 'getSiblingPosition')) {
            return $node->getSiblingPosition();
        }

        return $node->siblings()->where('_lft', '<', $node->_lft)->count();
    }

    /**
     * Send a success notification.
     */
    protected function notifySuccess(string $message): void
    {
        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    /**
     * Send an error notification.
     */
    protected function notifyError(string $message): void
    {
        Notification::make()
            ->title(__('filament-nested-set-table::messages.move_failed'))
            ->body($message)
            ->danger()
            ->send();
    }

    /**
     * Get the URL for the back to list button.
     * Handles both regular and nested resources.
     */
    public function getBackUrl(): string
    {
        $resource = static::getResource();

        // Check if this is a nested resource with a parent
        if ($this->getParentRecord()) {
            $parentResource = static::getParentResource();

            if ($parentResource) {
                // Try to find a relation page for this child resource
                $parentPages = $parentResource::getPages();

                foreach ($parentPages as $name => $page) {
                    // Look for ManageRelatedRecords pages that manage this relationship
                    if (str_contains($name, '.index') || str_contains($name, $resource::getSlug())) {
                        return $parentResource::getUrl($name, ['record' => $this->getParentRecord()]);
                    }
                }

                // Fallback to parent resource edit page
                return $parentResource::getUrl('edit', ['record' => $this->getParentRecord()]);
            }
        }

        // Regular resource - just go to index
        return $resource::getUrl('index');
    }
}
