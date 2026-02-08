<?php

namespace Pjedesigns\FilamentNestedSetTable\Concerns;

use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoveFailed;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;

trait HasTree
{
    public bool $treeMode = true;

    public array $expandedNodes = [];

    public ?array $lastMove = null;

    protected static int $maxDepth = 0;

    protected static bool $rememberExpandedState = true;

    protected static bool $defaultExpanded = false;

    /**
     * Relationships to eager load with tree queries.
     * Override getTreeWith() method to customize.
     */
    protected array $treeWith = [];

    public function bootHasTree(): void
    {
        static::$maxDepth = config('filament-nested-set-table.max_depth', 0);
        static::$rememberExpandedState = config('filament-nested-set-table.remember_expanded_state', true);
        static::$defaultExpanded = config('filament-nested-set-table.default_expanded', false);

        // Note: We don't load from session here anymore.
        // Session loading happens in mountHasTree() which only runs on initial page load.
        // This boot method runs on every Livewire request, so we should not override
        // the Livewire-managed state.
    }

    /**
     * Reset tree to default state (clear session and collapse/expand based on config).
     * Useful as a header action to let users reset the tree view.
     */
    public function resetTreeState(): void
    {
        $this->clearExpandedState();

        if (static::$defaultExpanded) {
            $this->expandAllNodes();
        }
    }

    /**
     * Clear the saved expanded state from session.
     * Useful for resetting to default state.
     */
    public function clearExpandedState(): void
    {
        $this->expandedNodes = [];
        session()->forget($this->getExpandedStateKey());
    }

    public function mountHasTree(): void
    {
        $this->bootHasTree();

        $hasSessionState = false;

        // Load saved expanded state from session on initial mount only
        if (static::$rememberExpandedState) {
            $savedState = session()->get($this->getExpandedStateKey());
            // Check if session has ANY value (including empty array)
            // This distinguishes between "never visited" (null) and "explicitly collapsed" ([])
            if ($savedState !== null) {
                $this->expandedNodes = $savedState;
                $hasSessionState = true;
            }
        }

        // Only expand all by default if:
        // 1. defaultExpanded is true
        // 2. There's NO saved session state (first visit)
        if (static::$defaultExpanded && ! $hasSessionState) {
            $this->expandAllNodes();
        }
    }

    /**
     * Override the table query to apply tree modifications.
     * This method is automatically called by Filament's ListRecords.
     */
    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if ($query && $this->treeMode) {
            return $this->applyTreeQueryModifications($query);
        }

        return $query;
    }

    /**
     * Override pagination to properly handle tree structure.
     * Pagination counts root nodes only, but all visible children are included.
     */
    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        if (! $this->treeMode) {
            return parent::paginateTableQuery($query);
        }

        $perPage = $this->getTableRecordsPerPage();

        // If pagination is disabled, use parent method
        if ($perPage === 'all') {
            return parent::paginateTableQuery($query);
        }

        $page = $this->getTablePage();

        // Get a fresh base query that respects filters (soft deletes, etc.)
        // but without the tree modifications we applied in applyTreeQueryModifications
        $baseQuery = $this->getFilteredBaseQuery();

        // Count only root nodes for pagination (respecting filters)
        $totalRootNodes = (clone $baseQuery)
            ->whereNull('parent_id')
            ->count();

        // Get paginated root node IDs (respecting filters)
        $rootNodeIds = (clone $baseQuery)
            ->whereNull('parent_id')
            ->defaultOrder()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->pluck('id')
            ->toArray();

        // Build collection: root nodes + their visible children
        $records = collect();

        if (! empty($rootNodeIds)) {
            // Get root nodes with depth, children count, and eager loaded relations
            $rootNodesQuery = (clone $baseQuery)
                ->whereIn('id', $rootNodeIds)
                ->withDepth()
                ->withCount('children')
                ->defaultOrder();

            // Apply eager loading if configured
            $eagerLoad = $this->getTreeWith();
            if (! empty($eagerLoad)) {
                $rootNodesQuery->with($eagerLoad);
            }

            $rootNodes = $rootNodesQuery->get();

            // For each root node, add it and its visible descendants
            foreach ($rootNodes as $rootNode) {
                $records->push($rootNode);
                $this->addVisibleDescendantsToCollection($records, $rootNode, $baseQuery);
            }
        }

        // Create a custom paginator with root node count
        return new LengthAwarePaginator(
            $records,
            $totalRootNodes,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $this->getTablePaginationPageName(),
            ]
        );
    }

    /**
     * Get a fresh base query that respects table filters (soft deletes, etc.)
     * but without tree-specific modifications.
     */
    public function getFilteredBaseQuery(): Builder
    {
        $model = $this->getModel();

        // Start with a fresh query
        $query = $model::query();

        // Apply the trashed filter state if it exists
        $trashedState = $this->getTableFilterState('trashed');

        if ($trashedState && array_key_exists('value', $trashedState)) {
            $value = $trashedState['value'];

            if ($value === true || $value === '1' || $value === 1) {
                // "With trashed" - show all including soft deleted
                $query->withTrashed();
            } elseif ($value === false || $value === '0' || $value === 0) {
                // "Only trashed" - show only soft deleted
                $query->onlyTrashed();
            }
            // If value is null/blank, default behavior (withoutTrashed) applies automatically
        }

        return $query;
    }

    /**
     * Recursively add visible descendants of a node to the collection.
     * A descendant is visible if all its ancestors are expanded.
     *
     * @param  Collection  $records  The collection to add records to
     * @param  Model  $node  The parent node
     * @param  Builder  $baseQuery  The base query with filters applied (soft deletes, etc.)
     */
    protected function addVisibleDescendantsToCollection(Collection $records, Model $node, Builder $baseQuery): void
    {
        // Only add children if this node is expanded
        if (! in_array($node->getKey(), $this->expandedNodes)) {
            return;
        }

        $childrenQuery = (clone $baseQuery)
            ->where('parent_id', $node->getKey())
            ->withDepth()
            ->withCount('children')
            ->defaultOrder();

        // Apply eager loading if configured
        $eagerLoad = $this->getTreeWith();
        if (! empty($eagerLoad)) {
            $childrenQuery->with($eagerLoad);
        }

        $children = $childrenQuery->get();

        foreach ($children as $child) {
            $records->push($child);
            // Recursively add this child's visible descendants
            $this->addVisibleDescendantsToCollection($records, $child, $baseQuery);
        }
    }

    /**
     * Configure the table for tree functionality.
     * Call this in your table() method to add tree-specific configuration.
     */
    public function configureTreeTable(Table $table): Table
    {
        return $table
            ->recordUrl(null) // Disable row click navigation in tree mode
            ->contentGrid(null);
    }

    /**
     * Override the table query to add tree-specific modifications.
     * Uses lazy loading: only root nodes + children of expanded nodes are loaded.
     * Pagination applies to root nodes only.
     */
    protected function applyTreeQueryModifications(Builder $query): Builder
    {
        $query->withDepth()->withCount('children');

        if ($this->treeMode) {
            // Build a query that includes:
            // 1. Root nodes (for pagination)
            // 2. Children of expanded nodes (loaded on demand)
            $query->where(function (Builder $q) {
                // Include root nodes
                $q->whereNull('parent_id');

                // Include children of expanded nodes
                if (! empty($this->expandedNodes)) {
                    $q->orWhereIn('parent_id', $this->expandedNodes);

                    // Also include descendants of expanded nodes whose ancestors are all expanded
                    // This ensures we show grandchildren when both parent and grandparent are expanded
                    $this->addExpandedDescendants($q);
                }
            });

            $query->defaultOrder();
        }

        return $query;
    }

    /**
     * Recursively add descendants of expanded nodes to the query.
     * Only includes nodes whose entire ancestor chain is expanded.
     */
    protected function addExpandedDescendants(Builder $query): void
    {
        if (empty($this->expandedNodes)) {
            return;
        }

        // Use filtered base query to respect soft deletes
        $baseQuery = $this->getFilteredBaseQuery();

        // Get all node IDs that are children of expanded nodes
        $childIds = (clone $baseQuery)
            ->whereIn('parent_id', $this->expandedNodes)
            ->pluck('id')
            ->toArray();

        // Find which of these children are also expanded (and thus need their children loaded)
        $expandedChildIds = array_intersect($childIds, $this->expandedNodes);

        if (! empty($expandedChildIds)) {
            // Add children of expanded children
            $query->orWhereIn('parent_id', $expandedChildIds);

            // Recursively check for more levels
            $this->addNestedExpandedDescendants($query, $expandedChildIds, $baseQuery);
        }
    }

    /**
     * Recursively add deeper nested descendants.
     *
     * @param  Builder  $baseQuery  The base query with filters applied
     */
    protected function addNestedExpandedDescendants(Builder $query, array $parentIds, Builder $baseQuery, int $depth = 0): void
    {
        // Prevent infinite recursion
        if ($depth > 20 || empty($parentIds)) {
            return;
        }

        // Get children of these parents (respecting filters)
        $childIds = (clone $baseQuery)
            ->whereIn('parent_id', $parentIds)
            ->pluck('id')
            ->toArray();

        // Find which are expanded
        $expandedChildIds = array_intersect($childIds, $this->expandedNodes);

        if (! empty($expandedChildIds)) {
            $query->orWhereIn('parent_id', $expandedChildIds);
            $this->addNestedExpandedDescendants($query, $expandedChildIds, $baseQuery, $depth + 1);
        }
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
     * This triggers a re-render to load/unload children via lazy loading.
     */
    public function toggleNode(int $nodeId): void
    {
        $wasExpanded = in_array($nodeId, $this->expandedNodes);

        if ($wasExpanded) {
            // Collapsing - also collapse all descendants
            $this->collapseNodeAndDescendants($nodeId);
        } else {
            // Expanding
            $this->expandedNodes[] = $nodeId;
        }

        if (static::$rememberExpandedState) {
            session()->put($this->getExpandedStateKey(), $this->expandedNodes);
        }

        // Dispatch event for frontend to know the toggle happened
        $this->dispatch('tree-node-toggled', nodeId: $nodeId, expanded: ! $wasExpanded);
    }

    /**
     * Collapse a node and all its expanded descendants.
     */
    protected function collapseNodeAndDescendants(int $nodeId): void
    {
        $model = $this->getModel();
        $node = $model::find($nodeId);

        if (! $node) {
            $this->expandedNodes = array_values(array_diff($this->expandedNodes, [$nodeId]));

            return;
        }

        // Get all descendant IDs
        $descendantIds = $node->descendants()->pluck('id')->map(fn ($id) => (int) $id)->toArray();

        // Remove the node and all its descendants from expanded list
        $toRemove = array_merge([$nodeId], $descendantIds);
        $this->expandedNodes = array_values(array_diff($this->expandedNodes, $toRemove));
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
        $this->expandedNodes = $this->getFilteredBaseQuery()
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
     *
     * @param  int  $nodeId  The ID of the node being moved
     * @param  int|null  $targetNodeId  The ID of the target node
     * @param  bool  $insertBefore  If true, insert before target; if false, insert after target (when not making child)
     * @param  bool  $makeChild  If true, make the node a child of the target node
     */
    public function handleNodeMoved(
        int $nodeId,
        ?int $targetNodeId,
        bool $insertBefore = true,
        bool $makeChild = false
    ): void {
        $model = $this->getModel();
        $node = $model::withDepth()->find($nodeId);

        if (! $node) {
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.node_not_found'));

            return;
        }

        // Authorization check
        if (! $this->authorizeMove($node)) {
            event(new NodeMoveFailed($node, 'Unauthorized', $targetNodeId, 0));
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.unauthorized'));

            return;
        }

        // Get target node
        $targetNode = $targetNodeId ? $model::withDepth()->find($targetNodeId) : null;

        if ($targetNodeId && ! $targetNode) {
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.parent_not_found'));

            return;
        }

        // Determine the new parent based on operation
        $newParentId = $makeChild ? $targetNodeId : $targetNode?->parent_id;

        // Prevent circular reference - can't make a node a child of its own descendant
        if ($makeChild && $targetNode && $node->isAncestorOf($targetNode)) {
            $this->notifyMoveFailed(__('filament-nested-set-table::messages.circular_reference'));

            return;
        }

        // Max depth check
        $maxDepth = $this->getMaxDepth();
        if ($maxDepth > 0) {
            $targetDepth = $makeChild
                ? (($targetNode->depth ?? 0) + 1)
                : ($targetNode->depth ?? 0);

            // Calculate the depth of the deepest descendant of the node being moved
            $nodeSubtreeDepth = $this->getSubtreeDepth($node);

            $resultingMaxDepth = $targetDepth + $nodeSubtreeDepth;

            if ($resultingMaxDepth > $maxDepth) {
                $this->notifyMoveFailed(__('filament-nested-set-table::messages.max_depth_exceeded', [
                    'max' => $maxDepth,
                    'resulting' => $resultingMaxDepth,
                ]));

                return;
            }
        }

        // Scope validation
        if (! $this->validateScopeMove($node, $newParentId)) {
            event(new NodeMoveFailed($node, 'Cross-scope move', $newParentId, 0));
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
                // Move to root
                $node->makeRoot();
                $result = MoveResult::success(
                    newParentId: null,
                    newPosition: 0,
                    wasAutoAdjusted: false
                );
            }

            event(new NodeMoved($node->fresh(), $result, $previousParentId, $previousPosition));
            $this->notifyMoveSuccess($result);
            $this->dispatch('tree-updated');
            $this->js('window.dispatchEvent(new CustomEvent("tree-updated"))');
        } catch (\Throwable $e) {
            $result = MoveResult::failure($e->getMessage());
            event(new NodeMoveFailed($node, $result->error, $newParentId, 0));
            $this->notifyMoveFailed($result->error);
            $this->lastMove = null;
            $this->js('window.dispatchEvent(new CustomEvent("tree-updated"))');
        }
    }

    /**
     * Get the depth of the deepest descendant relative to the given node.
     * Returns 0 if the node has no children.
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

            Notification::make()
                ->title(__('filament-nested-set-table::messages.undo_success'))
                ->success()
                ->send();

            $this->dispatch('tree-updated');
            $this->js('window.dispatchEvent(new CustomEvent("tree-updated"))');
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('filament-nested-set-table::messages.move_failed'))
                ->body($e->getMessage())
                ->danger()
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
     * Whether the "Save Alphabetically" button should be shown.
     * Override this method in your ListRecords class to enable.
     */
    public function shouldShowAlphabeticalButton(): bool
    {
        return false;
    }

    /**
     * Get the fields to order alphabetically by.
     * Override in extending classes to customize.
     */
    public function getAlphabeticalOrderField(): array
    {
        return ['title'];
    }

    /**
     * Reorder all records alphabetically within each parent group.
     */
    public function saveAlphabetically(): void
    {
        $model = $this->getModel();
        $orderFields = $this->getAlphabeticalOrderField();

        try {
            $allNodes = $model::query()->defaultOrder()->get();

            // Group nodes by parent_id
            $grouped = $allNodes->groupBy(fn (Model $node) => $node->parent_id ?? 'root');

            foreach ($grouped as $nodes) {
                // Sort the group alphabetically by the configured fields
                $sorted = $nodes->sort(function (Model $a, Model $b) use ($orderFields) {
                    foreach ($orderFields as $field) {
                        $comparison = strnatcasecmp(
                            (string) $a->getAttribute($field),
                            (string) $b->getAttribute($field)
                        );

                        if ($comparison !== 0) {
                            return $comparison;
                        }
                    }

                    return 0;
                })->values();

                // Reposition nodes in the sorted order
                foreach ($sorted as $index => $node) {
                    if ($index === 0) {
                        continue;
                    }

                    $previousNode = $sorted->get($index - 1);
                    $node->insertAfterNode($previousNode);
                }
            }

            $model::fixTree();

            Notification::make()
                ->title(__('filament-nested-set-table::messages.alphabetical_success'))
                ->success()
                ->send();

            $this->dispatch('tree-updated');
            $this->js('window.dispatchEvent(new CustomEvent("tree-updated"))');
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('filament-nested-set-table::messages.alphabetical_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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
        return 'filament-tree-expanded.'.$this->getModel();
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
     * Override this method in your ListRecords class to set a custom max depth.
     *
     * Example:
     * public function getMaxDepth(): int
     * {
     *     return 5; // Limit to 5 levels
     * }
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

    /**
     * Get the relationships to eager load with tree queries.
     * Override this method in your ListRecords class to specify relationships.
     *
     * Example:
     * protected function getTreeWith(): array
     * {
     *     return ['mediaCoverImages', 'author'];
     * }
     */
    protected function getTreeWith(): array
    {
        return $this->treeWith;
    }

    /**
     * Set the relationships to eager load with tree queries.
     * Can be called in mount() or used via property.
     */
    protected function treeWith(array $relations): static
    {
        $this->treeWith = $relations;

        return $this;
    }
}
