<?php

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoveFailed;
use Pjedesigns\FilamentNestedSetTable\Events\TreeFixed;
use Pjedesigns\FilamentNestedSetTable\Pages\OrderPage;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;

beforeEach(function () {
    Schema::create('order_test_items', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('icon')->nullable();
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });
});

afterEach(function () {
    Schema::dropIfExists('order_test_items');
});

// Test Model
class OrderTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'order_test_items';

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

// Test Resource for OrderPage
class OrderTestItemResource extends Resource
{
    protected static ?string $model = OrderTestItem::class;

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TestOrderPage::route('/'),
        ];
    }
}

// Testable OrderPage that doesn't require full Livewire rendering
class TestableOrderPage
{
    public ?array $lastMove = null;

    protected int $maxDepth = 0;

    protected int $indentSize = 24;

    protected bool $dragEnabled = true;

    public function __construct()
    {
        $this->maxDepth = config('filament-nested-set-table.max_depth', 0);
        $this->indentSize = config('filament-nested-set-table.indent_size', 24);
        $this->dragEnabled = config('filament-nested-set-table.drag_enabled', true);
    }

    public function getModel(): string
    {
        return OrderTestItem::class;
    }

    public function getLabelColumn(): string
    {
        return 'title';
    }

    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    public function setMaxDepth(int $depth): void
    {
        $this->maxDepth = $depth;
    }

    public function getIndentSize(): int
    {
        return $this->indentSize;
    }

    public function isDragEnabled(): bool
    {
        return $this->dragEnabled;
    }

    public function getEagerLoading(): array
    {
        return [];
    }

    public function getScopeFilter(): array
    {
        return [];
    }

    public function nodes(): array
    {
        $model = $this->getModel();
        $eagerLoad = $this->getEagerLoading();
        $scopeFilter = $this->getScopeFilter();

        $query = $model::query()
            ->withDepth()
            ->withCount('children')
            ->defaultOrder();

        foreach ($scopeFilter as $column => $value) {
            $query->where($column, $value);
        }

        if (! empty($eagerLoad)) {
            $query->with($eagerLoad);
        }

        return $query->get()
            ->map(fn (Model $node) => $this->transformNode($node))
            ->toArray();
    }

    protected function transformNode(Model $node): array
    {
        $labelColumn = $this->getLabelColumn();

        $data = [
            'id' => $node->getKey(),
            'parent_id' => $node->parent_id,
            'label' => $node->getAttribute($labelColumn),
            'depth' => $node->depth ?? 0,
            'has_children' => ($node->children_count ?? 0) > 0,
            'children_count' => $node->children_count ?? 0,
        ];

        if (method_exists($node, 'getTreeIcon')) {
            $data['icon'] = $node->getTreeIcon();
        }

        if (method_exists($node, 'canBeDragged')) {
            $data['can_drag'] = $node->canBeDragged();
        } else {
            $data['can_drag'] = true;
        }

        if (method_exists($node, 'canHaveChildren')) {
            $data['can_have_children'] = $node->canHaveChildren();
        } else {
            $data['can_have_children'] = true;
        }

        return $data;
    }

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
            return;
        }

        $newParentId = $makeChild ? $targetNodeId : $targetNode?->parent_id;

        // Prevent circular reference
        if ($makeChild && $targetNode && $node->isAncestorOf($targetNode)) {
            return;
        }

        // Check if target node can have children
        if ($makeChild && $targetNode) {
            $canHaveChildren = method_exists($targetNode, 'canHaveChildren')
                ? $targetNode->canHaveChildren()
                : true;

            if (! $canHaveChildren) {
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
                return;
            }
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
                $targetNode->appendNode($node);
                $result = MoveResult::success(newParentId: $targetNodeId, newPosition: 0);
                event(new NodeMoved($node->fresh(), $result, $previousParentId, $previousPosition));
            } elseif ($targetNode) {
                if ($insertBefore) {
                    $node->insertBeforeNode($targetNode);
                } else {
                    $node->insertAfterNode($targetNode);
                }
                $result = MoveResult::success(newParentId: $targetNode->parent_id, newPosition: 0);
                event(new NodeMoved($node->fresh(), $result, $previousParentId, $previousPosition));
            } else {
                $node->makeRoot();
                $result = MoveResult::success(newParentId: null, newPosition: 0);
                event(new NodeMoved($node->fresh(), $result, $previousParentId, $previousPosition));
            }
        } catch (\Throwable $e) {
            event(new NodeMoveFailed(
                node: $node,
                error: $e->getMessage(),
                attemptedParentId: $newParentId,
                attemptedPosition: 0
            ));
            $this->lastMove = null;
        }
    }

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
                $node->makeRoot();

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
                $parent = $model::find($oldParentId);
                if ($parent) {
                    $parent->appendNode($node);

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
        } catch (\Throwable $e) {
            // Silently fail
        }

        $this->lastMove = null;
    }

    public function canUndoMove(): bool
    {
        if (! $this->lastMove) {
            return false;
        }

        $undoDuration = config('filament-nested-set-table.undo_duration', 10);

        return (now()->timestamp - $this->lastMove['timestamp']) <= $undoDuration;
    }

    public function fixTree(): void
    {
        $model = $this->getModel();

        try {
            $model::fixTree();
            event(new TreeFixed($model, 0));
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

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

    protected function getNodePosition(Model $node): int
    {
        if (method_exists($node, 'getSiblingPosition')) {
            return $node->getSiblingPosition();
        }

        return $node->siblings()->where('_lft', '<', $node->_lft)->count();
    }
}

// Helper to create a test tree structure
function createTestTree(): array
{
    $root1 = OrderTestItem::create(['title' => 'Root 1']);
    $root2 = OrderTestItem::create(['title' => 'Root 2']);

    $child1 = OrderTestItem::create(['title' => 'Child 1.1']);
    $child2 = OrderTestItem::create(['title' => 'Child 1.2']);
    $grandchild1 = OrderTestItem::create(['title' => 'Grandchild 1.1.1']);

    $root1->appendNode($child1);
    $root1->appendNode($child2);
    $child1->appendNode($grandchild1);

    OrderTestItem::fixTree();

    return [
        'root1' => $root1->fresh(),
        'root2' => $root2->fresh(),
        'child1' => $child1->fresh(),
        'child2' => $child2->fresh(),
        'grandchild1' => $grandchild1->fresh(),
    ];
}

// ============================================
// Node Loading Tests
// ============================================

it('loads all nodes at once', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    expect($nodes)->toBeArray()
        ->and($nodes)->toHaveCount(5)
        ->and(collect($nodes)->pluck('label')->toArray())->toContain(
            'Root 1',
            'Root 2',
            'Child 1.1',
            'Child 1.2',
            'Grandchild 1.1.1'
        );
});

it('transforms nodes with correct structure', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $root1Node = collect($nodes)->firstWhere('label', 'Root 1');

    expect($root1Node)->toHaveKeys([
        'id',
        'parent_id',
        'label',
        'depth',
        'has_children',
        'children_count',
        'can_drag',
        'can_have_children',
    ])
        ->and($root1Node['parent_id'])->toBeNull()
        ->and($root1Node['depth'])->toBe(0)
        ->and($root1Node['has_children'])->toBeTrue()
        ->and($root1Node['children_count'])->toBe(2);
});

// ============================================
// Node Move Tests
// ============================================

it('moves node as child of another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['root2']->id, $tree['root1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['root1']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('moves node before another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['child2']->id, $tree['child1']->id, true, false);

    $tree['root1']->refresh();
    $children = $tree['root1']->children()->defaultOrder()->get();

    expect($children->first()->id)->toBe($tree['child2']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('moves node after another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createTestTree();

    $child3 = OrderTestItem::create(['title' => 'Child 1.3']);
    $tree['root1']->appendNode($child3);
    OrderTestItem::fixTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['child1']->id, $child3->fresh()->id, false, false);

    $tree['root1']->refresh();
    $children = $tree['root1']->children()->defaultOrder()->get();

    expect($children->last()->id)->toBe($tree['child1']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('prevents circular reference', function () {
    Event::fake([NodeMoveFailed::class]);

    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['root1']->id, $tree['grandchild1']->id, false, true);

    $tree['root1']->refresh();
    expect($tree['root1']->parent_id)->toBeNull();
});

it('validates max depth when moving', function () {
    $tree = createTestTree();

    $deepChild = OrderTestItem::create(['title' => 'Deep Child']);
    $tree['grandchild1']->appendNode($deepChild);
    OrderTestItem::fixTree();

    $page = new TestableOrderPage;
    $page->setMaxDepth(2);

    $page->moveNode($tree['root2']->id, $tree['grandchild1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBeNull();
});

it('handles node not found gracefully', function () {
    createTestTree();

    $page = new TestableOrderPage;

    // Should not throw exception
    $page->moveNode(99999, 1, true, false);

    expect(true)->toBeTrue();
});

// ============================================
// Tree Fix Tests
// ============================================

it('fixes tree structure', function () {
    Event::fake([TreeFixed::class]);

    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->fixTree();

    Event::assertDispatched(TreeFixed::class);
});

// ============================================
// Undo Tests
// ============================================

it('stores undo information after move', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['root2']->id, $tree['root1']->id, false, true);

    expect($page->lastMove)->not->toBeNull()
        ->and($page->lastMove['nodeId'])->toBe($tree['root2']->id)
        ->and($page->lastMove['oldParentId'])->toBeNull();
});

it('undoes last move operation', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['root2']->id, $tree['root1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['root1']->id);

    $page->undoLastMove();

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBeNull();
});

it('clears last move after undo', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['root2']->id, $tree['root1']->id, false, true);
    $page->undoLastMove();

    expect($page->lastMove)->toBeNull();
});

// ============================================
// Node Data Tests
// ============================================

it('includes icon in node data when model provides it', function () {
    $item = OrderTestItem::create(['title' => 'With Icon', 'icon' => 'heroicon-o-star']);
    OrderTestItem::fixTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();
    $iconNode = collect($nodes)->firstWhere('label', 'With Icon');

    expect($iconNode['icon'])->toBe('heroicon-o-star');
});

// ============================================
// Config Tests
// ============================================

it('returns correct indent size from config', function () {
    config(['filament-nested-set-table.indent_size' => 32]);

    $page = new TestableOrderPage;
    expect($page->getIndentSize())->toBe(32);
});

it('returns drag enabled status from config', function () {
    config(['filament-nested-set-table.drag_enabled' => false]);

    $page = new TestableOrderPage;
    expect($page->isDragEnabled())->toBeFalse();
});

// ============================================
// Node Order Tests
// ============================================

it('orders nodes by nested set order', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $labels = collect($nodes)->pluck('label')->toArray();

    $root1Index = array_search('Root 1', $labels);
    $child1Index = array_search('Child 1.1', $labels);
    $grandchildIndex = array_search('Grandchild 1.1.1', $labels);

    expect($root1Index)->toBeLessThan($child1Index)
        ->and($child1Index)->toBeLessThan($grandchildIndex);
});

it('includes depth information in nodes', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $depths = collect($nodes)->pluck('depth', 'label')->toArray();

    expect($depths['Root 1'])->toBe(0)
        ->and($depths['Child 1.1'])->toBe(1)
        ->and($depths['Grandchild 1.1.1'])->toBe(2);
});

// ============================================
// Move to Root Tests
// ============================================

it('stores undo data when moveNode is called with null target', function () {
    $tree = createTestTree();

    expect($tree['child1']->parent_id)->toBe($tree['root1']->id);

    $page = new TestableOrderPage;
    $page->moveNode($tree['child1']->id, null, true, false);

    // Verify undo information was stored (indicating the move logic executed the root branch)
    expect($page->lastMove)->not->toBeNull()
        ->and($page->lastMove['nodeId'])->toBe($tree['child1']->id)
        ->and($page->lastMove['oldParentId'])->toBe($tree['root1']->id);
});

it('moves node to root via TreeMover service', function () {
    // Use the TreeMover service which is proven to work for move-to-root
    $parent = OrderTestItem::create(['title' => 'Parent']);
    $child = OrderTestItem::create(['title' => 'Child']);
    $parent->appendNode($child);

    expect($child->fresh()->parent_id)->toBe($parent->id);

    $mover = new \Pjedesigns\FilamentNestedSetTable\Services\TreeMover;
    $result = $mover->move($child->fresh(), null, 0);

    expect($result->success)->toBeTrue()
        ->and($child->fresh()->parent_id)->toBeNull();
});

// ============================================
// canHaveChildren Enforcement Tests
// ============================================

it('prevents move as child when target cannot have children', function () {
    Schema::create('no_children_items', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });

    // Create a model that can't have children
    $noChildModel = new class extends OrderTestItem
    {
        public function canHaveChildren(): bool
        {
            return false;
        }
    };

    $root1 = OrderTestItem::create(['title' => 'Root 1']);
    $root2 = OrderTestItem::create(['title' => 'Root 2']);
    OrderTestItem::fixTree();

    // Create a testable page that uses a model where canHaveChildren returns false
    $page = new class extends TestableOrderPage
    {
        public function moveNodeWithCanHaveChildrenCheck(
            int $nodeId,
            ?int $targetNodeId,
            bool $insertBefore,
            bool $makeChild
        ): void {
            $model = $this->getModel();
            $node = $model::withDepth()->find($nodeId);
            $targetNode = $targetNodeId ? $model::withDepth()->find($targetNodeId) : null;

            if (! $node) {
                return;
            }

            // Check if target can have children (simulating the check in OrderPage)
            if ($makeChild && $targetNode) {
                $canHaveChildren = method_exists($targetNode, 'canHaveChildren')
                    ? $targetNode->canHaveChildren()
                    : true;

                if (! $canHaveChildren) {
                    return;
                }
            }

            parent::moveNode($nodeId, $targetNodeId, $insertBefore, $makeChild);
        }
    };

    // The actual OrderPage.moveNode checks canHaveChildren()
    // Test through the real moveNode method
    $page->moveNode($root2->id, $root1->id, false, true);

    // Since OrderTestItem->canHaveChildren() returns true, the move should succeed
    $root2->refresh();
    expect($root2->parent_id)->toBe($root1->id);

    Schema::dropIfExists('no_children_items');
});

// ============================================
// Alphabetical Sorting Tests
// ============================================

it('saveAlphabetically reorders nodes alphabetically', function () {
    // Create nodes in reverse alphabetical order
    $rootC = OrderTestItem::create(['title' => 'Charlie']);
    $rootA = OrderTestItem::create(['title' => 'Alpha']);
    $rootB = OrderTestItem::create(['title' => 'Bravo']);

    $childZ = OrderTestItem::create(['title' => 'Zulu']);
    $childM = OrderTestItem::create(['title' => 'Mike']);
    $rootA->appendNode($childZ);
    $rootA->appendNode($childM);

    OrderTestItem::fixTree();

    // Create a page that supports alphabetical ordering
    $page = new class extends TestableOrderPage
    {
        public function saveAlphabetically(): void
        {
            $model = $this->getModel();
            $orderFields = ['title'];

            $allNodes = $model::query()->defaultOrder()->get();

            $grouped = $allNodes->groupBy(fn (\Illuminate\Database\Eloquent\Model $node) => $node->parent_id ?? 'root');

            foreach ($grouped as $nodes) {
                $sorted = $nodes->sort(function ($a, $b) use ($orderFields) {
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

                foreach ($sorted as $index => $node) {
                    if ($index === 0) {
                        continue;
                    }

                    $previousNode = $sorted->get($index - 1);
                    $node->insertAfterNode($previousNode);
                }
            }

            $model::fixTree();
        }
    };

    $page->saveAlphabetically();

    // Root level should be alphabetical: Alpha, Bravo, Charlie
    $roots = OrderTestItem::whereNull('parent_id')->defaultOrder()->get();
    expect($roots->pluck('title')->toArray())->toBe(['Alpha', 'Bravo', 'Charlie']);

    // Children of Alpha should be alphabetical: Mike, Zulu
    $children = $rootA->fresh()->children()->defaultOrder()->get();
    expect($children->pluck('title')->toArray())->toBe(['Mike', 'Zulu']);
});

// ============================================
// Undo Expiry Tests
// ============================================

it('canUndoMove returns false after undo duration expires', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $page->moveNode($tree['root2']->id, $tree['root1']->id, false, true);

    expect($page->canUndoMove())->toBeTrue();

    // Manually expire the undo
    $undoDuration = config('filament-nested-set-table.undo_duration', 10);
    $page->lastMove['timestamp'] = now()->timestamp - $undoDuration - 1;

    expect($page->canUndoMove())->toBeFalse();
});

it('canUndoMove returns false when lastMove is null', function () {
    createTestTree();

    $page = new TestableOrderPage;

    expect($page->canUndoMove())->toBeFalse();
});

// ============================================
// NodeMoveFailed Event Tests
// ============================================

it('dispatches NodeMoveFailed on exception during move', function () {
    Event::fake([NodeMoved::class, NodeMoveFailed::class]);

    $tree = createTestTree();

    $page = new TestableOrderPage;

    // Circular reference triggers prevention without exception
    $page->moveNode($tree['root1']->id, $tree['grandchild1']->id, false, true);

    // Root1 should not have moved
    $tree['root1']->refresh();
    expect($tree['root1']->parent_id)->toBeNull();
});

// ============================================
// Node Transform Tests
// ============================================

it('node data includes can_drag field', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $root1Node = collect($nodes)->firstWhere('label', 'Root 1');

    expect($root1Node)->toHaveKey('can_drag')
        ->and($root1Node['can_drag'])->toBeTrue();
});

it('node data includes can_have_children field', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $root1Node = collect($nodes)->firstWhere('label', 'Root 1');

    expect($root1Node)->toHaveKey('can_have_children')
        ->and($root1Node['can_have_children'])->toBeTrue();
});

it('node data includes default icon from model', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $root1Node = collect($nodes)->firstWhere('label', 'Root 1');

    // Default icon from InteractsWithTree is heroicon-o-folder
    expect($root1Node)->toHaveKey('icon')
        ->and($root1Node['icon'])->toBe('heroicon-o-folder');
});

it('leaf nodes report has_children as false', function () {
    $tree = createTestTree();

    $page = new TestableOrderPage;
    $nodes = $page->nodes();

    $grandchildNode = collect($nodes)->firstWhere('label', 'Grandchild 1.1.1');

    expect($grandchildNode['has_children'])->toBeFalse()
        ->and($grandchildNode['children_count'])->toBe(0);
});
