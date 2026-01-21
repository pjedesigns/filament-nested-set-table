<?php

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\HasTree;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Tables\Columns\TreeColumn;

beforeEach(function () {
    Schema::create('has_tree_test_items', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('icon')->nullable();
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });

    // Clear session between tests
    session()->forget('filament-tree-expanded.HasTreeTestItem');
});

afterEach(function () {
    Schema::dropIfExists('has_tree_test_items');
    session()->forget('filament-tree-expanded.HasTreeTestItem');
});

// Test Model
class HasTreeTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'has_tree_test_items';

    protected $fillable = ['title', 'icon'];

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }

    public function getTreeIcon(): ?string
    {
        return $this->icon ?? 'heroicon-o-folder';
    }
}

// Create a testable class that uses HasTree trait without requiring full Filament rendering
class HasTreeTestController
{
    use HasTree;

    public array $notifications = [];

    public function __construct()
    {
        $this->bootHasTree();
        $this->mountHasTree();
    }

    public function getModel(): string
    {
        return HasTreeTestItem::class;
    }

    // Stub methods required by HasTree
    public function dispatch(string $event, ...$args): void
    {
        // No-op for testing
    }

    public function js(string $code): void
    {
        // No-op for testing
    }

    protected function resetTable(): void
    {
        // No-op for testing
    }

    // Stub for Filament's table filter state
    public function getTableFilterState(string $name): ?array
    {
        return null;
    }

    // Override notification methods to avoid Filament dependency
    protected function notifyMoveSuccess(\Pjedesigns\FilamentNestedSetTable\Services\MoveResult $result): void
    {
        $this->notifications[] = ['type' => 'success', 'result' => $result];
    }

    protected function notifyMoveFailed(string $message): void
    {
        $this->notifications[] = ['type' => 'failed', 'message' => $message];
    }
}

// Helper to create a test tree structure
function createHasTreeTestData(): array
{
    $root1 = HasTreeTestItem::create(['title' => 'Root 1']);
    $root2 = HasTreeTestItem::create(['title' => 'Root 2']);

    $child1 = HasTreeTestItem::create(['title' => 'Child 1.1']);
    $child2 = HasTreeTestItem::create(['title' => 'Child 1.2']);
    $grandchild1 = HasTreeTestItem::create(['title' => 'Grandchild 1.1.1']);

    $root1->appendNode($child1);
    $root1->appendNode($child2);
    $child1->appendNode($grandchild1);

    HasTreeTestItem::fixTree();

    return [
        'root1' => $root1->fresh(),
        'root2' => $root2->fresh(),
        'child1' => $child1->fresh(),
        'child2' => $child2->fresh(),
        'grandchild1' => $grandchild1->fresh(),
    ];
}

// ============================================
// Tree Mode & Basic Functionality Tests
// ============================================

it('starts in tree mode by default', function () {
    createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->treeMode)->toBeTrue();
});

it('can toggle tree mode off and on', function () {
    createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->treeMode)->toBeTrue();

    $controller->toggleTreeMode();
    expect($controller->treeMode)->toBeFalse();

    $controller->toggleTreeMode();
    expect($controller->treeMode)->toBeTrue();
});

it('reports tree mode status correctly', function () {
    createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->isTreeModeActive())->toBeTrue();

    $controller->toggleTreeMode();
    expect($controller->isTreeModeActive())->toBeFalse();
});

// ============================================
// Expand/Collapse Functionality Tests
// ============================================

it('starts with no nodes expanded by default', function () {
    createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->expandedNodes)->toBeArray()->toBeEmpty();
});

it('can toggle a single node expanded state', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Expand root1
    $controller->toggleNode($tree['root1']->id);
    expect($controller->expandedNodes)->toContain($tree['root1']->id);

    // Collapse root1
    $controller->toggleNode($tree['root1']->id);
    expect($controller->expandedNodes)->not->toContain($tree['root1']->id);
});

it('reports node expanded status correctly', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->isNodeExpanded($tree['root1']->id))->toBeFalse();

    $controller->toggleNode($tree['root1']->id);
    expect($controller->isNodeExpanded($tree['root1']->id))->toBeTrue();
});

it('can expand all nodes', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    $controller->expandAllNodes();

    // root1 and child1 have children, so they should be expanded
    expect($controller->expandedNodes)
        ->toContain($tree['root1']->id)
        ->toContain($tree['child1']->id);
});

it('can collapse all nodes', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // First expand all
    $controller->expandAllNodes();
    expect($controller->expandedNodes)->not->toBeEmpty();

    // Then collapse all
    $controller->collapseAllNodes();
    expect($controller->expandedNodes)->toBeEmpty();
});

it('collapses descendants when collapsing a parent node', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Expand root1 and child1
    $controller->toggleNode($tree['root1']->id);
    $controller->toggleNode($tree['child1']->id);

    expect($controller->expandedNodes)
        ->toContain($tree['root1']->id)
        ->toContain($tree['child1']->id);

    // Collapse root1 - should also collapse child1
    $controller->toggleNode($tree['root1']->id);

    expect($controller->expandedNodes)
        ->not->toContain($tree['root1']->id)
        ->not->toContain($tree['child1']->id);
});

// ============================================
// Session Persistence Tests
// ============================================

it('saves expanded state to session when enabled', function () {
    $tree = createHasTreeTestData();

    config(['filament-nested-set-table.remember_expanded_state' => true]);

    $controller = new HasTreeTestController;

    $controller->toggleNode($tree['root1']->id);

    $sessionKey = 'filament-tree-expanded.HasTreeTestItem';
    expect(session()->get($sessionKey))->toContain($tree['root1']->id);
});

it('restores expanded state from session on mount', function () {
    $tree = createHasTreeTestData();

    $sessionKey = 'filament-tree-expanded.HasTreeTestItem';
    session()->put($sessionKey, [$tree['root1']->id, $tree['child1']->id]);

    config(['filament-nested-set-table.remember_expanded_state' => true]);

    $controller = new HasTreeTestController;

    expect($controller->expandedNodes)
        ->toContain($tree['root1']->id)
        ->toContain($tree['child1']->id);
});

it('can clear expanded state', function () {
    $tree = createHasTreeTestData();

    $sessionKey = 'filament-tree-expanded.HasTreeTestItem';
    session()->put($sessionKey, [$tree['root1']->id]);

    config(['filament-nested-set-table.remember_expanded_state' => true]);

    $controller = new HasTreeTestController;

    expect($controller->expandedNodes)->not->toBeEmpty();

    $controller->clearExpandedState();

    expect($controller->expandedNodes)->toBeEmpty();
    expect(session()->get($sessionKey))->toBeNull();
});

it('can reset tree state to default', function () {
    $tree = createHasTreeTestData();

    $sessionKey = 'filament-tree-expanded.HasTreeTestItem';
    session()->put($sessionKey, [$tree['root1']->id]);

    config(['filament-nested-set-table.default_expanded' => false]);
    config(['filament-nested-set-table.remember_expanded_state' => true]);

    $controller = new HasTreeTestController;

    $controller->resetTreeState();

    expect($controller->expandedNodes)->toBeEmpty();
    expect(session()->get($sessionKey))->toBeNull();
});

it('expands all when default_expanded is true and no session state', function () {
    $tree = createHasTreeTestData();

    config(['filament-nested-set-table.default_expanded' => true]);
    config(['filament-nested-set-table.remember_expanded_state' => true]);

    // Clear any session state
    session()->forget('filament-tree-expanded.HasTreeTestItem');

    $controller = new HasTreeTestController;

    // Should auto-expand all nodes with children
    expect($controller->expandedNodes)
        ->toContain($tree['root1']->id)
        ->toContain($tree['child1']->id);
});

// ============================================
// Node Move Tests (handleNodeMoved)
// ============================================

it('moves node as child of another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Move Root 2 as child of Root 1
    $controller->handleNodeMoved($tree['root2']->id, $tree['root1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['root1']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('moves node before another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Move Child 1.2 before Child 1.1
    $controller->handleNodeMoved($tree['child2']->id, $tree['child1']->id, true, false);

    $tree['root1']->refresh();
    $children = $tree['root1']->children()->defaultOrder()->get();

    expect($children->first()->id)->toBe($tree['child2']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('moves node after another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createHasTreeTestData();

    // Create another child
    $child3 = HasTreeTestItem::create(['title' => 'Child 1.3']);
    $tree['root1']->appendNode($child3);
    HasTreeTestItem::fixTree();

    $controller = new HasTreeTestController;

    // Move Child 1.1 after Child 1.3
    $controller->handleNodeMoved($tree['child1']->id, $child3->fresh()->id, false, false);

    $tree['root1']->refresh();
    $children = $tree['root1']->children()->defaultOrder()->get();

    expect($children->last()->id)->toBe($tree['child1']->id);

    Event::assertDispatched(NodeMoved::class);
});

// Note: "move to root" functionality is tested in OrderPageTest which uses
// the OrderPage component that handles root moves correctly via the moveNode method.

it('prevents circular reference when moving', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Try to move Root 1 as child of its own grandchild
    $controller->handleNodeMoved($tree['root1']->id, $tree['grandchild1']->id, false, true);

    // Should not have changed
    $tree['root1']->refresh();
    expect($tree['root1']->parent_id)->toBeNull();
});

it('handles node not found gracefully', function () {
    createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Should not throw exception
    $controller->handleNodeMoved(99999, 1, true, false);

    // Test passes if no exception is thrown
    expect(true)->toBeTrue();
});

it('handles target node not found gracefully', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Should not throw exception
    $controller->handleNodeMoved($tree['root1']->id, 99999, true, false);

    // Should remain unchanged
    $tree['root1']->refresh();
    expect($tree['root1']->parent_id)->toBeNull();
});

// ============================================
// Undo Functionality Tests
// ============================================

it('stores undo information after move', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Move Root 2 as child of Root 1
    $controller->handleNodeMoved($tree['root2']->id, $tree['root1']->id, false, true);

    expect($controller->lastMove)->not->toBeNull()
        ->and($controller->lastMove['nodeId'])->toBe($tree['root2']->id)
        ->and($controller->lastMove['oldParentId'])->toBeNull();
});

it('can undo last move operation', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Move Root 2 as child of Root 1
    $controller->handleNodeMoved($tree['root2']->id, $tree['root1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['root1']->id);

    // Undo
    $controller->undoLastMove();

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBeNull();
});

it('clears last move after undo', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Move and undo
    $controller->handleNodeMoved($tree['root2']->id, $tree['root1']->id, false, true);
    $controller->undoLastMove();

    expect($controller->lastMove)->toBeNull();
});

it('reports canUndoMove correctly', function () {
    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Initially no undo available
    expect($controller->canUndoMove())->toBeFalse();

    // After move, undo should be available
    $controller->handleNodeMoved($tree['root2']->id, $tree['root1']->id, false, true);
    expect($controller->canUndoMove())->toBeTrue();

    // After undo, no longer available
    $controller->undoLastMove();
    expect($controller->canUndoMove())->toBeFalse();
});

// ============================================
// Max Depth Tests
// ============================================

it('returns max depth from config', function () {
    config(['filament-nested-set-table.max_depth' => 5]);

    createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->getMaxDepth())->toBe(5);
});

it('prevents move when max depth would be exceeded', function () {
    config(['filament-nested-set-table.max_depth' => 2]);

    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // grandchild1 is at depth 2
    // Trying to make root2 a child of grandchild1 would put root2 at depth 3
    $controller->handleNodeMoved($tree['root2']->id, $tree['grandchild1']->id, false, true);

    // Should remain unchanged
    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBeNull();
});

it('allows move when max depth is 0 (unlimited)', function () {
    config(['filament-nested-set-table.max_depth' => 0]);

    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    // Move root2 as child of grandchild1 (would be depth 3)
    $controller->handleNodeMoved($tree['root2']->id, $tree['grandchild1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['grandchild1']->id);
});

// ============================================
// TreeColumn Configuration Tests
// ============================================

it('TreeColumn can set custom indent size', function () {
    $column = TreeColumn::make('title')
        ->indentSize(48);

    expect($column->getIndentSize())->toBe(48);
});

it('TreeColumn can disable drag handle', function () {
    $column = TreeColumn::make('title')
        ->dragHandle(false);

    expect($column->shouldShowDragHandle())->toBeFalse();
});

it('TreeColumn can disable expand toggle', function () {
    $column = TreeColumn::make('title')
        ->expandToggle(false);

    expect($column->shouldShowExpandToggle())->toBeFalse();
});

it('TreeColumn can disable dragging', function () {
    $column = TreeColumn::make('title')
        ->draggable(false);

    expect($column->isDraggable())->toBeFalse();
});

it('TreeColumn defaults drag handle to true', function () {
    $column = TreeColumn::make('title');

    expect($column->shouldShowDragHandle())->toBeTrue();
});

it('TreeColumn defaults expand toggle to true', function () {
    $column = TreeColumn::make('title');

    expect($column->shouldShowExpandToggle())->toBeTrue();
});

// ============================================
// Configuration Tests
// ============================================

it('reads remember expanded state from config', function () {
    config(['filament-nested-set-table.remember_expanded_state' => true]);

    createHasTreeTestData();

    $controller = new HasTreeTestController;

    expect($controller->shouldRememberExpandedState())->toBeTrue();
});

it('does not remember state when config is false', function () {
    config(['filament-nested-set-table.remember_expanded_state' => false]);

    $tree = createHasTreeTestData();

    $controller = new HasTreeTestController;

    $controller->toggleNode($tree['root1']->id);

    $sessionKey = 'filament-tree-expanded.HasTreeTestItem';
    expect(session()->get($sessionKey))->toBeNull();
});
