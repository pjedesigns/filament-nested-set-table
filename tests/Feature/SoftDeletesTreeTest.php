<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\HasTree;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;

beforeEach(function () {
    Schema::create('soft_delete_tree_items', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->softDeletes();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });

    session()->forget('filament-tree-expanded.SoftDeleteTreeItem');
});

afterEach(function () {
    Schema::dropIfExists('soft_delete_tree_items');
    session()->forget('filament-tree-expanded.SoftDeleteTreeItem');
});

// Test Model with SoftDeletes
class SoftDeleteTreeItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;
    use SoftDeletes;

    protected $table = 'soft_delete_tree_items';

    protected $fillable = ['title'];

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }
}

// Test Controller that simulates table filter state
class SoftDeleteTreeController
{
    use HasTree;

    public array $notifications = [];

    public array $tableFilters = [];

    public function __construct()
    {
        $this->bootHasTree();
        $this->mountHasTree();
    }

    public function getModel(): string
    {
        return SoftDeleteTreeItem::class;
    }

    public function dispatch(string $event, ...$args): void
    {
    }

    public function js(string $code): void
    {
    }

    protected function resetTable(): void
    {
    }

    protected function notifyMoveSuccess(\Pjedesigns\FilamentNestedSetTable\Services\MoveResult $result): void
    {
        $this->notifications[] = ['type' => 'success', 'result' => $result];
    }

    protected function notifyMoveFailed(string $message): void
    {
        $this->notifications[] = ['type' => 'failed', 'message' => $message];
    }

    /**
     * Simulate Filament's getTableFilterState method.
     */
    public function getTableFilterState(string $name): ?array
    {
        return $this->tableFilters[$name] ?? null;
    }

    /**
     * Set filter state for testing.
     */
    public function setFilterState(string $name, array $state): void
    {
        $this->tableFilters[$name] = $state;
    }
}

// Helper to create test tree with soft deleted items
function createSoftDeleteTreeData(): array
{
    $root1 = SoftDeleteTreeItem::create(['title' => 'Root 1']);
    $root2 = SoftDeleteTreeItem::create(['title' => 'Root 2']);
    $root3Trashed = SoftDeleteTreeItem::create(['title' => 'Root 3 (Trashed)']);

    $child1 = SoftDeleteTreeItem::create(['title' => 'Child 1.1']);
    $child2 = SoftDeleteTreeItem::create(['title' => 'Child 1.2']);
    $child3Trashed = SoftDeleteTreeItem::create(['title' => 'Child 1.3 (Trashed)']);
    $grandchild1 = SoftDeleteTreeItem::create(['title' => 'Grandchild 1.1.1']);
    $grandchild2Trashed = SoftDeleteTreeItem::create(['title' => 'Grandchild 1.1.2 (Trashed)']);

    $root1->appendNode($child1);
    $root1->appendNode($child2);
    $root1->appendNode($child3Trashed);
    $child1->appendNode($grandchild1);
    $child1->appendNode($grandchild2Trashed);

    SoftDeleteTreeItem::fixTree();

    // Soft delete the "trashed" items
    $root3Trashed->delete();
    $child3Trashed->delete();
    $grandchild2Trashed->delete();

    return [
        'root1' => $root1->fresh(),
        'root2' => $root2->fresh(),
        'root3Trashed' => $root3Trashed->fresh(),
        'child1' => $child1->fresh(),
        'child2' => $child2->fresh(),
        'child3Trashed' => $child3Trashed->fresh(),
        'grandchild1' => $grandchild1->fresh(),
        'grandchild2Trashed' => $grandchild2Trashed->fresh(),
    ];
}

// ============================================
// Soft Delete Filter Tests
// ============================================

it('getFilteredBaseQuery excludes trashed by default', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // No filter state set = default behavior (without trashed)
    $query = $controller->getFilteredBaseQuery();
    $ids = $query->pluck('id')->toArray();

    // Should include only non-trashed items
    expect($ids)
        ->toContain($tree['root1']->id)
        ->toContain($tree['root2']->id)
        ->toContain($tree['child1']->id)
        ->toContain($tree['child2']->id)
        ->toContain($tree['grandchild1']->id)
        ->not->toContain($tree['root3Trashed']->id)
        ->not->toContain($tree['child3Trashed']->id)
        ->not->toContain($tree['grandchild2Trashed']->id);
});

it('getFilteredBaseQuery includes trashed when filter is "with trashed"', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Set filter to "with trashed" (true)
    $controller->setFilterState('trashed', ['value' => true]);

    $query = $controller->getFilteredBaseQuery();
    $ids = $query->pluck('id')->toArray();

    // Should include all items (trashed and non-trashed)
    expect($ids)
        ->toContain($tree['root1']->id)
        ->toContain($tree['root2']->id)
        ->toContain($tree['root3Trashed']->id)
        ->toContain($tree['child1']->id)
        ->toContain($tree['child2']->id)
        ->toContain($tree['child3Trashed']->id)
        ->toContain($tree['grandchild1']->id)
        ->toContain($tree['grandchild2Trashed']->id);
});

it('getFilteredBaseQuery shows only trashed when filter is "only trashed"', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Set filter to "only trashed" (false)
    $controller->setFilterState('trashed', ['value' => false]);

    $query = $controller->getFilteredBaseQuery();
    $ids = $query->pluck('id')->toArray();

    // Should include only trashed items
    expect($ids)
        ->toContain($tree['root3Trashed']->id)
        ->toContain($tree['child3Trashed']->id)
        ->toContain($tree['grandchild2Trashed']->id)
        ->not->toContain($tree['root1']->id)
        ->not->toContain($tree['root2']->id)
        ->not->toContain($tree['child1']->id)
        ->not->toContain($tree['child2']->id)
        ->not->toContain($tree['grandchild1']->id);
});

it('getFilteredBaseQuery handles string filter values', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Filter value might come as string "1" from form
    $controller->setFilterState('trashed', ['value' => '1']);

    $query = $controller->getFilteredBaseQuery();
    $ids = $query->pluck('id')->toArray();

    // Should include all items (with trashed)
    expect($ids)->toContain($tree['root3Trashed']->id);
});

it('getFilteredBaseQuery handles string "0" for only trashed', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Filter value might come as string "0" from form
    $controller->setFilterState('trashed', ['value' => '0']);

    $query = $controller->getFilteredBaseQuery();
    $ids = $query->pluck('id')->toArray();

    // Should include only trashed items
    expect($ids)
        ->toContain($tree['root3Trashed']->id)
        ->not->toContain($tree['root1']->id);
});

it('expandAllNodes respects trashed filter', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Default behavior (without trashed)
    $controller->expandAllNodes();

    // Only non-trashed nodes with children should be expanded
    // root1 and child1 have non-trashed children
    expect($controller->expandedNodes)
        ->toContain($tree['root1']->id)
        ->toContain($tree['child1']->id)
        ->not->toContain($tree['root3Trashed']->id);
});

it('expandAllNodes includes trashed nodes when filter shows all', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Set filter to "with trashed"
    $controller->setFilterState('trashed', ['value' => true]);

    $controller->expandAllNodes();

    // Now root1 and child1 should still be expanded
    // (their trashed children count toward having children)
    expect($controller->expandedNodes)
        ->toContain($tree['root1']->id)
        ->toContain($tree['child1']->id);
});

it('expandAllNodes only expands trashed nodes when filter is only trashed', function () {
    $tree = createSoftDeleteTreeData();

    $controller = new SoftDeleteTreeController;

    // Set filter to "only trashed"
    $controller->setFilterState('trashed', ['value' => false]);

    $controller->expandAllNodes();

    // Only trashed nodes with trashed children would be expanded
    // In our test data, trashed items don't have children
    expect($controller->expandedNodes)->toBeEmpty();
});
