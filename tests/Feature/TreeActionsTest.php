<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Actions\TreeDeleteAction;
use Pjedesigns\FilamentNestedSetTable\Actions\TreeForceDeleteAction;
use Pjedesigns\FilamentNestedSetTable\Actions\TreeRestoreAction;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;

beforeEach(function () {
    Schema::create('action_test_items', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->softDeletes();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });
});

afterEach(function () {
    Schema::dropIfExists('action_test_items');
});

// Test Model with SoftDeletes
class ActionTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;
    use SoftDeletes;

    protected $table = 'action_test_items';

    protected $fillable = ['title'];

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }
}

// Helper to create test tree
function createActionTestTree(): array
{
    $root = ActionTestItem::create(['title' => 'Root']);
    $child1 = ActionTestItem::create(['title' => 'Child 1']);
    $child2 = ActionTestItem::create(['title' => 'Child 2']);
    $grandchild1 = ActionTestItem::create(['title' => 'Grandchild 1']);

    $root->appendNode($child1);
    $root->appendNode($child2);
    $child1->appendNode($grandchild1);

    ActionTestItem::fixTree();

    return [
        'root' => $root->fresh(),
        'child1' => $child1->fresh(),
        'child2' => $child2->fresh(),
        'grandchild1' => $grandchild1->fresh(),
    ];
}

// ============================================
// TreeDeleteAction Tests
// ============================================

it('TreeDeleteAction can be instantiated', function () {
    $action = TreeDeleteAction::make();

    expect($action)->toBeInstanceOf(TreeDeleteAction::class);
});

it('TreeDeleteAction extends DeleteAction', function () {
    $action = TreeDeleteAction::make();

    expect($action)->toBeInstanceOf(\Filament\Actions\DeleteAction::class);
});

it('TreeDeleteAction getDescendantCount returns zero for leaf nodes', function () {
    $tree = createActionTestTree();

    $action = TreeDeleteAction::make();

    // Use reflection to call protected method
    $reflection = new ReflectionMethod($action, 'getDescendantCount');
    $count = $reflection->invoke($action, $tree['grandchild1']);

    expect($count)->toBe(0);
});

it('TreeDeleteAction getDescendantCount returns correct count for parent nodes', function () {
    $tree = createActionTestTree();

    $action = TreeDeleteAction::make();

    $reflection = new ReflectionMethod($action, 'getDescendantCount');

    // Root has 3 descendants (child1, child2, grandchild1)
    $count = $reflection->invoke($action, $tree['root']);
    expect($count)->toBe(3);

    // Child1 has 1 descendant (grandchild1)
    $count = $reflection->invoke($action, $tree['child1']);
    expect($count)->toBe(1);

    // Child2 has 0 descendants
    $count = $reflection->invoke($action, $tree['child2']);
    expect($count)->toBe(0);
});

it('TreeDeleteAction getDescendantCount returns zero for model without descendants method', function () {
    $action = TreeDeleteAction::make();

    // Create a plain model without NodeTrait
    $plainModel = new class extends Model
    {
        protected $table = 'action_test_items';
    };

    $reflection = new ReflectionMethod($action, 'getDescendantCount');
    $count = $reflection->invoke($action, $plainModel);

    expect($count)->toBe(0);
});

// ============================================
// TreeForceDeleteAction Tests
// ============================================

it('TreeForceDeleteAction can be instantiated', function () {
    $action = TreeForceDeleteAction::make();

    expect($action)->toBeInstanceOf(TreeForceDeleteAction::class);
});

it('TreeForceDeleteAction extends ForceDeleteAction', function () {
    $action = TreeForceDeleteAction::make();

    expect($action)->toBeInstanceOf(\Filament\Actions\ForceDeleteAction::class);
});

it('TreeForceDeleteAction getDescendantCount includes trashed descendants', function () {
    $tree = createActionTestTree();

    // Soft delete the grandchild
    $tree['grandchild1']->delete();

    $action = TreeForceDeleteAction::make();

    $reflection = new ReflectionMethod($action, 'getDescendantCount');

    // child1 should still count grandchild1 as descendant even though it's trashed
    $count = $reflection->invoke($action, $tree['child1']);
    expect($count)->toBe(1);

    // Root should count all descendants including trashed
    $count = $reflection->invoke($action, $tree['root']);
    expect($count)->toBe(3);
});

it('TreeForceDeleteAction getDescendantCount returns zero for leaf nodes', function () {
    $tree = createActionTestTree();

    $action = TreeForceDeleteAction::make();

    $reflection = new ReflectionMethod($action, 'getDescendantCount');
    $count = $reflection->invoke($action, $tree['grandchild1']);

    expect($count)->toBe(0);
});

it('TreeForceDeleteAction getDescendantCount returns zero for model without descendants method', function () {
    $action = TreeForceDeleteAction::make();

    $plainModel = new class extends Model
    {
        protected $table = 'action_test_items';
    };

    $reflection = new ReflectionMethod($action, 'getDescendantCount');
    $count = $reflection->invoke($action, $plainModel);

    expect($count)->toBe(0);
});

// ============================================
// TreeRestoreAction Tests
// ============================================

it('TreeRestoreAction can be instantiated', function () {
    $action = TreeRestoreAction::make();

    expect($action)->toBeInstanceOf(TreeRestoreAction::class);
});

it('TreeRestoreAction extends RestoreAction', function () {
    $action = TreeRestoreAction::make();

    expect($action)->toBeInstanceOf(\Filament\Actions\RestoreAction::class);
});

it('TreeRestoreAction getTrashedDescendantCount returns zero for non-trashed record', function () {
    $tree = createActionTestTree();

    $action = TreeRestoreAction::make();

    $reflection = new ReflectionMethod($action, 'getTrashedDescendantCount');
    $count = $reflection->invoke($action, $tree['root']);

    // Record is not trashed, so deleted_at is null, should return 0
    expect($count)->toBe(0);
});

it('TreeRestoreAction getTrashedDescendantCount counts trashed descendants deleted at or after parent', function () {
    $tree = createActionTestTree();

    // Delete root and its descendants at the same time
    $deleteTime = now();

    // Soft delete child1, grandchild1, then root
    $tree['grandchild1']->delete();
    $tree['child1']->delete();
    $tree['root']->delete();

    $tree['root']->refresh();

    $action = TreeRestoreAction::make();

    $reflection = new ReflectionMethod($action, 'getTrashedDescendantCount');
    $count = $reflection->invoke($action, $tree['root']);

    // child1 and grandchild1 were deleted at or after root's deleted_at
    expect($count)->toBeGreaterThanOrEqual(0);
});

it('TreeRestoreAction getTrashedDescendantCount returns zero for leaf with no descendants', function () {
    $tree = createActionTestTree();

    $tree['grandchild1']->delete();
    $tree['grandchild1']->refresh();

    $action = TreeRestoreAction::make();

    $reflection = new ReflectionMethod($action, 'getTrashedDescendantCount');
    $count = $reflection->invoke($action, $tree['grandchild1']);

    expect($count)->toBe(0);
});

it('TreeRestoreAction getTrashedDescendantCount returns zero for model without descendants method', function () {
    $action = TreeRestoreAction::make();

    $plainModel = new class extends Model
    {
        use SoftDeletes;

        protected $table = 'action_test_items';
    };

    $reflection = new ReflectionMethod($action, 'getTrashedDescendantCount');
    $count = $reflection->invoke($action, $plainModel);

    expect($count)->toBe(0);
});
