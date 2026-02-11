<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoveFailed;
use Pjedesigns\FilamentNestedSetTable\Events\TreeFixed;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;

beforeEach(function () {
    Schema::create('event_test_items', function (Blueprint $table) {
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
    Schema::dropIfExists('event_test_items');
});

class EventTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'event_test_items';

    protected $fillable = ['title'];

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }
}

// ============================================
// NodeMoved Event Tests
// ============================================

it('NodeMoved event stores all constructor properties', function () {
    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $result = MoveResult::success(newParentId: 5, newPosition: 2, wasAutoAdjusted: true);

    $event = new NodeMoved(
        node: $node,
        result: $result,
        previousParentId: 3,
        previousPosition: 1,
    );

    expect($event->node->id)->toBe($node->id)
        ->and($event->result->success)->toBeTrue()
        ->and($event->result->newParentId)->toBe(5)
        ->and($event->result->newPosition)->toBe(2)
        ->and($event->result->wasAutoAdjusted)->toBeTrue()
        ->and($event->previousParentId)->toBe(3)
        ->and($event->previousPosition)->toBe(1);
});

it('NodeMoved broadcastOn returns empty array when broadcast disabled', function () {
    config(['filament-nested-set-table.broadcast_enabled' => false]);

    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $result = MoveResult::success();

    $event = new NodeMoved(node: $node, result: $result);

    expect($event->broadcastOn())->toBe([]);
});

it('NodeMoved broadcastOn returns channel when broadcast enabled', function () {
    config(['filament-nested-set-table.broadcast_enabled' => true]);

    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $result = MoveResult::success();

    $event = new NodeMoved(node: $node, result: $result);

    $channels = $event->broadcastOn();
    expect($channels)->toHaveCount(1)
        ->and($channels[0])->toBe('tree-updates.EventTestItem');
});

it('NodeMoved broadcastAs returns correct event name', function () {
    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $result = MoveResult::success();

    $event = new NodeMoved(node: $node, result: $result);

    expect($event->broadcastAs())->toBe('node.moved');
});

it('NodeMoved broadcastWith returns correct payload', function () {
    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $result = MoveResult::success(newParentId: 10, newPosition: 3, wasAutoAdjusted: true);

    $event = new NodeMoved(node: $node, result: $result);

    $payload = $event->broadcastWith();

    expect($payload)->toHaveKeys(['nodeId', 'newParentId', 'newPosition', 'wasAutoAdjusted'])
        ->and($payload['nodeId'])->toBe($node->id)
        ->and($payload['newParentId'])->toBe(10)
        ->and($payload['newPosition'])->toBe(3)
        ->and($payload['wasAutoAdjusted'])->toBeTrue();
});

it('NodeMoved implements ShouldBroadcast', function () {
    expect(NodeMoved::class)
        ->toImplement(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class);
});

it('NodeMoved handles null previous values', function () {
    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $result = MoveResult::success();

    $event = new NodeMoved(node: $node, result: $result);

    expect($event->previousParentId)->toBeNull()
        ->and($event->previousPosition)->toBeNull();
});

// ============================================
// NodeMoveFailed Event Tests
// ============================================

it('NodeMoveFailed event stores all constructor properties', function () {
    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $event = new NodeMoveFailed(
        node: $node,
        error: 'Cannot move an item under its own descendant.',
        attemptedParentId: 5,
        attemptedPosition: 2,
    );

    expect($event->node->id)->toBe($node->id)
        ->and($event->error)->toBe('Cannot move an item under its own descendant.')
        ->and($event->attemptedParentId)->toBe(5)
        ->and($event->attemptedPosition)->toBe(2);
});

it('NodeMoveFailed handles null optional values', function () {
    $node = EventTestItem::create(['title' => 'Test Node']);
    EventTestItem::fixTree();

    $event = new NodeMoveFailed(
        node: $node,
        error: 'Some error',
    );

    expect($event->attemptedParentId)->toBeNull()
        ->and($event->attemptedPosition)->toBeNull();
});

it('NodeMoveFailed does not implement ShouldBroadcast', function () {
    $interfaces = class_implements(NodeMoveFailed::class);

    expect($interfaces)->not->toContain(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class);
});

// ============================================
// TreeFixed Event Tests
// ============================================

it('TreeFixed event stores all constructor properties', function () {
    $event = new TreeFixed(
        modelClass: EventTestItem::class,
        nodesFixed: 5,
        scopeAttributes: ['scope_id'],
    );

    expect($event->modelClass)->toBe(EventTestItem::class)
        ->and($event->nodesFixed)->toBe(5)
        ->and($event->scopeAttributes)->toBe(['scope_id']);
});

it('TreeFixed handles null scope attributes', function () {
    $event = new TreeFixed(
        modelClass: EventTestItem::class,
        nodesFixed: 0,
    );

    expect($event->scopeAttributes)->toBeNull();
});

it('TreeFixed stores zero nodes fixed', function () {
    $event = new TreeFixed(
        modelClass: EventTestItem::class,
        nodesFixed: 0,
    );

    expect($event->nodesFixed)->toBe(0);
});
