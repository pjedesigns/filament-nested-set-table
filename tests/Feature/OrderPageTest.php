<?php

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Livewire\Livewire;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoveFailed;
use Pjedesigns\FilamentNestedSetTable\Events\TreeFixed;
use Pjedesigns\FilamentNestedSetTable\Pages\OrderPage;

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

// Test OrderPage Implementation (using $resource property)
class TestOrderPage extends OrderPage
{
    protected static string $resource = OrderTestItemResource::class;

    protected static ?string $title = 'Order Items';

    public function getLabelColumn(): string
    {
        return 'title';
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

    // Fix tree to ensure _lft/_rgt are correct
    OrderTestItem::fixTree();

    return [
        'root1' => $root1->fresh(),
        'root2' => $root2->fresh(),
        'child1' => $child1->fresh(),
        'child2' => $child2->fresh(),
        'grandchild1' => $grandchild1->fresh(),
    ];
}

it('loads all nodes at once', function () {
    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    $nodes = $livewire->get('nodes');

    expect($nodes)->toBeArray()
        ->and($nodes)->toHaveCount(5) // All nodes loaded
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

    $livewire = Livewire::test(TestOrderPage::class);

    $nodes = $livewire->get('nodes');

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

it('moves node as child of another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Move Root 2 as child of Root 1
    $livewire->call('moveNode', $tree['root2']->id, $tree['root1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['root1']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('moves node before another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Move Child 1.2 before Child 1.1
    $livewire->call('moveNode', $tree['child2']->id, $tree['child1']->id, true, false);

    $tree['root1']->refresh();
    $children = $tree['root1']->children()->defaultOrder()->get();

    expect($children->first()->id)->toBe($tree['child2']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('moves node after another node', function () {
    Event::fake([NodeMoved::class]);

    $tree = createTestTree();

    // Create another child first
    $child3 = OrderTestItem::create(['title' => 'Child 1.3']);
    $tree['root1']->appendNode($child3);
    OrderTestItem::fixTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Move Child 1.1 after Child 1.3
    $livewire->call('moveNode', $tree['child1']->id, $child3->fresh()->id, false, false);

    $tree['root1']->refresh();
    $children = $tree['root1']->children()->defaultOrder()->get();

    // Child 1.1 should now be last (after Child 1.3)
    expect($children->last()->id)->toBe($tree['child1']->id);

    Event::assertDispatched(NodeMoved::class);
});

it('prevents circular reference', function () {
    Event::fake([NodeMoveFailed::class]);

    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Try to move Root 1 as child of its own grandchild
    $livewire->call('moveNode', $tree['root1']->id, $tree['grandchild1']->id, false, true);

    // Should not have changed
    $tree['root1']->refresh();
    expect($tree['root1']->parent_id)->toBeNull();
});

it('validates max depth when moving', function () {
    $tree = createTestTree();

    // Create a deeper structure
    $deepChild = OrderTestItem::create(['title' => 'Deep Child']);
    $tree['grandchild1']->appendNode($deepChild);
    OrderTestItem::fixTree();

    // Create a test page with max depth of 2
    $page = new class extends TestOrderPage
    {
        public function getMaxDepth(): int
        {
            return 2;
        }
    };

    $livewire = Livewire::test($page::class);

    // Try to move Deep Child (with its subtree) under Root 2
    // Since Deep Child is at depth 3, this should fail max depth
    // Actually the validation is on resulting depth, so let's test differently

    // The grandchild is at depth 2, so trying to add more children would exceed maxDepth=2
    $livewire->call('moveNode', $tree['root2']->id, $tree['grandchild1']->id, false, true);

    // Should have been prevented due to max depth
    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBeNull();
});

it('handles node not found gracefully', function () {
    createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Try to move non-existent node
    $livewire->call('moveNode', 99999, 1, true, false);

    // Should show notification (test that no exception was thrown)
    $livewire->assertDispatched('close-modal');
});

it('fixes tree structure', function () {
    Event::fake([TreeFixed::class]);

    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    $livewire->call('fixTree');

    Event::assertDispatched(TreeFixed::class);
});

it('stores undo information after move', function () {
    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Move Root 2 as child of Root 1
    $livewire->call('moveNode', $tree['root2']->id, $tree['root1']->id, false, true);

    $lastMove = $livewire->get('lastMove');

    expect($lastMove)->not->toBeNull()
        ->and($lastMove['nodeId'])->toBe($tree['root2']->id)
        ->and($lastMove['oldParentId'])->toBeNull(); // Was at root
});

it('undoes last move operation', function () {
    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Move Root 2 as child of Root 1
    $livewire->call('moveNode', $tree['root2']->id, $tree['root1']->id, false, true);

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBe($tree['root1']->id);

    // Undo
    $livewire->call('undoLastMove');

    $tree['root2']->refresh();
    expect($tree['root2']->parent_id)->toBeNull();
});

it('clears last move after undo', function () {
    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    // Move and undo
    $livewire->call('moveNode', $tree['root2']->id, $tree['root1']->id, false, true);
    $livewire->call('undoLastMove');

    expect($livewire->get('lastMove'))->toBeNull();
});

it('includes icon in node data when model provides it', function () {
    $item = OrderTestItem::create(['title' => 'With Icon', 'icon' => 'heroicon-o-star']);
    OrderTestItem::fixTree();

    $livewire = Livewire::test(TestOrderPage::class);

    $nodes = $livewire->get('nodes');
    $iconNode = collect($nodes)->firstWhere('label', 'With Icon');

    expect($iconNode['icon'])->toBe('heroicon-o-star');
});

it('returns correct indent size from config', function () {
    config(['filament-nested-set-table.indent_size' => 32]);

    $livewire = Livewire::test(TestOrderPage::class);

    // Access the component method
    $component = $livewire->instance();
    expect($component->getIndentSize())->toBe(32);

    // Reset
    config(['filament-nested-set-table.indent_size' => 24]);
});

it('returns drag enabled status from config', function () {
    config(['filament-nested-set-table.drag_enabled' => false]);

    $livewire = Livewire::test(TestOrderPage::class);

    $component = $livewire->instance();
    expect($component->isDragEnabled())->toBeFalse();

    // Reset
    config(['filament-nested-set-table.drag_enabled' => true]);
});

it('orders nodes by nested set order', function () {
    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    $nodes = $livewire->get('nodes');

    // Nodes should be in nested set order (depth-first)
    $labels = collect($nodes)->pluck('label')->toArray();

    // Root 1 should come before its children
    $root1Index = array_search('Root 1', $labels);
    $child1Index = array_search('Child 1.1', $labels);
    $grandchildIndex = array_search('Grandchild 1.1.1', $labels);

    expect($root1Index)->toBeLessThan($child1Index)
        ->and($child1Index)->toBeLessThan($grandchildIndex);
});

it('includes depth information in nodes', function () {
    $tree = createTestTree();

    $livewire = Livewire::test(TestOrderPage::class);

    $nodes = $livewire->get('nodes');

    $depths = collect($nodes)->pluck('depth', 'label')->toArray();

    expect($depths['Root 1'])->toBe(0)
        ->and($depths['Child 1.1'])->toBe(1)
        ->and($depths['Grandchild 1.1.1'])->toBe(2);
});
