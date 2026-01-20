<?php

namespace Pjedesigns\FilamentNestedSetTable\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoveFailed;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;
use Pjedesigns\FilamentNestedSetTable\Services\TreeMover;

trait HasTree
{
    public bool $treeMode = true;

    public array $expandedNodes = [];

    public ?array $lastMove = null;

    protected static int $maxDepth = 0;

    protected static bool $rememberExpandedState = true;

    protected static bool $defaultExpanded = true;

    public function bootHasTree(): void
    {
        static::$maxDepth = config('filament-nested-set-table.max_depth', 0);
        static::$rememberExpandedState = config('filament-nested-set-table.remember_expanded_state', true);
        static::$defaultExpanded = config('filament-nested-set-table.default_expanded', true);

        if (static::$rememberExpandedState) {
            $this->expandedNodes = session()->get($this->getExpandedStateKey(), []);
        }

        // If default expanded and no saved state, expand all
        if (static::$defaultExpanded && empty($this->expandedNodes)) {
            $this->expandAllNodes();
        }
    }

    public function mountHasTree(): void
    {
        $this->bootHasTree();
    }

    /**
     * Override the table query to add tree-specific modifications.
     */
    protected function applyTreeQueryModifications(Builder $query): Builder
    {
        $query->withDepth()->withCount('children');

        if ($this->treeMode) {
            $query->defaultOrder();
        }

        return $query;
    }

    /**
     * Toggle between tree mode and flat list mode.
     */
    public function toggleTreeMode(): void
    {
        $this->treeMode = ! $this->treeMode;
        $this->resetTable();
    }

    /**
     * Toggle a single node's expanded state.
     */
    public function toggleNode(int $nodeId): void
    {
        if (in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes = array_values(array_diff($this->expandedNodes, [$nodeId]));
        } else {
            $this->expandedNodes[] = $nodeId;
        }

        if (static::$rememberExpandedState) {
            session()->put($this->getExpandedStateKey(), $this->expandedNodes);
        }
    }

    /**
     * Check if a node is currently expanded.
     */
    public function isNodeExpanded(int $nodeId): bool
    {
        return in_array($nodeId, $this->expandedNodes);
    }

    /**
     * Expand all nodes in the tree.
     */
    public function expandAllNodes(): void
    {
        $model = $this->getModel();
        $this->expandedNodes = $model::query()
            ->whereHas('children')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (static::$rememberExpandedState) {
            session()->put($this->getExpandedStateKey(), $this->expandedNodes);
        }
    }

    /**
     * Collapse all nodes in the tree.
     */
    public function collapseAllNodes(): void
    {
        $this->expandedNodes = [];

        if (static::$rememberExpandedState) {
            session()->put($this->getExpandedStateKey(), $this->expandedNodes);
        }
    }

    /**
     * Handle a node move event from the frontend.
     */
    #[On('tree-node-moved')]
    public function handleNodeMoved(
        int $nodeId,
        ?int $newParentId,
        int $newPosition
    ): void {
        $model = $this->getModel();
        $node = $model::find($nodeId);

        if (! $node) {
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.node_not_found'));

            return;
        }

        // Authorization check
        if (! $this->authorizeMove($node)) {
            event(new NodeMoveFailed($node, 'Unauthorized', $newParentId, $newPosition));
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.unauthorized'));

            return;
        }

        // Scope validation
        if (! $this->validateScopeMove($node, $newParentId)) {
            event(new NodeMoveFailed($node, 'Cross-scope move', $newParentId, $newPosition));
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.cross_scope'));

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

        // Perform move
        $mover = app(TreeMover::class);
        $result = $mover->move($node, $newParentId, $newPosition, static::$maxDepth);

        if ($result->success) {
            event(new NodeMoved($node->fresh(), $result, $previousParentId, $previousPosition));
            $this->notifyMoveSuccess($result);
            $this->dispatch('tree-updated');
        } else {
            event(new NodeMoveFailed($node, $result->error, $newParentId, $newPosition));
            $this->notifyMoveFailed($result->error);
            $this->lastMove = null;
        }
    }

    /**
     * Undo the last move operation.
     */
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

        $mover = app(TreeMover::class);
        $result = $mover->move($node, $this->lastMove['oldParentId'], $this->lastMove['oldPosition']);

        if ($result->success) {
            Notification::make()
                ->title(__('filament-nested-set-table::messages.undo_success'))
                ->success()
                ->send();
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
     * Check if the user is authorized to move the given node.
     */
    protected function authorizeMove(Model $node): bool
    {
        $policy = policy($node);

        if ($policy && method_exists($policy, 'reorder')) {
            return $policy->reorder(auth()->user(), $node);
        }

        // Default to checking update permission
        if ($policy && method_exists($policy, 'update')) {
            return $policy->update(auth()->user(), $node);
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
     * Send a success notification for a move operation.
     */
    protected function notifyMoveSuccess(MoveResult $result): void
    {
        $title = __('filament-nested-set-table::messages.move_success');

        if ($result->wasAutoAdjusted) {
            $title = __('filament-nested-set-table::messages.move_adjusted');
        }

        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }

    /**
     * Send a failure notification for a move operation.
     */
    protected function notifyMoveFailed(string $message): void
    {
        Notification::make()
            ->title(__('filament-nested-set-table::messages.move_failed'))
            ->body($message)
            ->danger()
            ->send();
    }

    /**
     * Get the session key for storing expanded state.
     */
    protected function getExpandedStateKey(): string
    {
        return 'filament-tree-expanded.' . $this->getModel();
    }

    /**
     * Check if tree mode is currently active.
     */
    public function isTreeModeActive(): bool
    {
        return $this->treeMode;
    }

    /**
     * Get the maximum allowed tree depth.
     */
    public function getMaxDepth(): int
    {
        return static::$maxDepth;
    }

    /**
     * Set the maximum allowed tree depth.
     */
    public static function maxDepth(int $depth): void
    {
        static::$maxDepth = $depth;
    }

    /**
     * Check if the tree should remember expanded state.
     */
    public function shouldRememberExpandedState(): bool
    {
        return static::$rememberExpandedState;
    }
}
