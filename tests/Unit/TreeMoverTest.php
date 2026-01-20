<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;
use Pjedesigns\FilamentNestedSetTable\Services\TreeMover;

beforeEach(function () {
    Schema::create('test_categories', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });
});

afterEach(function () {
    Schema::dropIfExists('test_categories');
});

class TestCategory extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'test_categories';

    protected $fillable = ['title'];

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }
}

it('moves node to new parent', function () {
    $parent = TestCategory::create(['title' => 'Parent']);
    $child = TestCategory::create(['title' => 'Child']);

    $mover = new TreeMover;
    $result = $mover->move($child, $parent->id, 0);

    expect($result)->toBeInstanceOf(MoveResult::class)
        ->and($result->success)->toBeTrue()
        ->and($child->fresh()->parent_id)->toBe($parent->id);
});

it('moves node to root', function () {
    $parent = TestCategory::create(['title' => 'Parent']);
    $child = TestCategory::create(['title' => 'Child']);
    $parent->appendNode($child);

    expect($child->fresh()->parent_id)->toBe($parent->id);

    $mover = new TreeMover;
    $result = $mover->move($child, null, 0);

    expect($result->success)->toBeTrue()
        ->and($child->fresh()->parent_id)->toBeNull();
});

it('reorders siblings within same parent', function () {
    $parent = TestCategory::create(['title' => 'Parent']);
    $child1 = TestCategory::create(['title' => 'Child 1']);
    $child2 = TestCategory::create(['title' => 'Child 2']);
    $child3 = TestCategory::create(['title' => 'Child 3']);

    $parent->appendNode($child1);
    $parent->appendNode($child2);
    $parent->appendNode($child3);

    // Move child3 to first position
    $mover = new TreeMover;
    $result = $mover->move($child3, $parent->id, 0);

    expect($result->success)->toBeTrue();

    $parent->refresh();
    $children = $parent->children()->defaultOrder()->get();

    expect($children->first()->id)->toBe($child3->id);
});

it('auto-adjusts position when max depth exceeded', function () {
    // Create tree: Root (0) -> Level1 (1) -> Level2 (2)
    $root = TestCategory::create(['title' => 'Root']);
    $level1 = TestCategory::create(['title' => 'Level 1']);
    $level2 = TestCategory::create(['title' => 'Level 2']);
    $nodeToMove = TestCategory::create(['title' => 'Node To Move']);

    $root->appendNode($level1);
    $level1->appendNode($level2);

    // Rebuild tree to ensure _lft/_rgt are correct
    TestCategory::fixTree();

    // Try to move nodeToMove as child of level2 with max depth of 2
    // level2 is at depth 2, so adding a child would make depth 3, exceeding maxDepth of 2
    $mover = new TreeMover;
    $result = $mover->move($nodeToMove, $level2->id, 0, maxDepth: 2);

    expect($result->success)->toBeTrue()
        ->and($result->wasAutoAdjusted)->toBeTrue();

    // Should become sibling of level2, not child (placed under level1)
    $nodeToMove->refresh();
    expect($nodeToMove->parent_id)->toBe($level1->id);
});

it('prevents circular reference', function () {
    $parent = TestCategory::create(['title' => 'Parent']);
    $child = TestCategory::create(['title' => 'Child']);

    $parent->appendNode($child);

    // Try to move parent under its own child
    $mover = new TreeMover;
    $result = $mover->move($parent, $child->id, 0);

    expect($result->success)->toBeFalse()
        ->and($result->error)->toContain('descendant');
});

it('returns failure when parent not found', function () {
    $node = TestCategory::create(['title' => 'Node']);

    $mover = new TreeMover;
    $result = $mover->move($node, 99999, 0);

    expect($result->success)->toBeFalse()
        ->and($result->error)->toBe('The parent item could not be found.');
});

it('creates MoveResult success correctly', function () {
    $result = MoveResult::success(
        newParentId: 5,
        newPosition: 2,
        wasAutoAdjusted: true
    );

    expect($result->success)->toBeTrue()
        ->and($result->newParentId)->toBe(5)
        ->and($result->newPosition)->toBe(2)
        ->and($result->wasAutoAdjusted)->toBeTrue()
        ->and($result->error)->toBeNull();
});

it('creates MoveResult failure correctly', function () {
    $result = MoveResult::failure('Something went wrong');

    expect($result->success)->toBeFalse()
        ->and($result->error)->toBe('Something went wrong');
});

it('handles move to same position', function () {
    $parent = TestCategory::create(['title' => 'Parent']);
    $child1 = TestCategory::create(['title' => 'Child 1']);
    $child2 = TestCategory::create(['title' => 'Child 2']);

    $parent->appendNode($child1);
    $parent->appendNode($child2);

    // Get child1's current position (0-indexed among all siblings including self)
    $child1->refresh();
    $allChildren = $parent->children()->defaultOrder()->get();
    $currentPosition = $allChildren->search(fn ($c) => $c->id === $child1->id);

    // Move to same position
    $mover = new TreeMover;
    $result = $mover->move($child1, $parent->id, $currentPosition);

    expect($result->success)->toBeTrue();
});

it('moves node between different parents', function () {
    $parent1 = TestCategory::create(['title' => 'Parent 1']);
    $parent2 = TestCategory::create(['title' => 'Parent 2']);
    $child = TestCategory::create(['title' => 'Child']);

    $parent1->appendNode($child);

    expect($child->fresh()->parent_id)->toBe($parent1->id);

    $mover = new TreeMover;
    $result = $mover->move($child, $parent2->id, 0);

    expect($result->success)->toBeTrue()
        ->and($child->fresh()->parent_id)->toBe($parent2->id);
});
