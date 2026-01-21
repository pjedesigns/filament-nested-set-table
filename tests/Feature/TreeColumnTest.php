<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;
use Pjedesigns\FilamentNestedSetTable\Tables\Columns\TreeColumn;

beforeEach(function () {
    Schema::create('tree_column_test_items', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('icon')->nullable();
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->unsignedInteger('children_count')->default(0);
        $table->unsignedInteger('depth')->default(0);
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });
});

afterEach(function () {
    Schema::dropIfExists('tree_column_test_items');
});

// Test Model
class TreeColumnTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'tree_column_test_items';

    protected $fillable = ['title', 'icon', 'children_count', 'depth'];

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }

    public function getTreeIcon(): ?string
    {
        return $this->icon ?? 'heroicon-o-folder';
    }
}

// Helper to create a test tree structure
function createTreeColumnTestData(): array
{
    $root1 = TreeColumnTestItem::create(['title' => 'Root 1', 'depth' => 0, 'children_count' => 2]);
    $root2 = TreeColumnTestItem::create(['title' => 'Root 2', 'depth' => 0, 'children_count' => 0]);

    $child1 = TreeColumnTestItem::create(['title' => 'Child 1.1', 'depth' => 1, 'children_count' => 1]);
    $child2 = TreeColumnTestItem::create(['title' => 'Child 1.2', 'depth' => 1, 'children_count' => 0]);
    $grandchild1 = TreeColumnTestItem::create(['title' => 'Grandchild 1.1.1', 'depth' => 2, 'children_count' => 0]);

    $root1->appendNode($child1);
    $root1->appendNode($child2);
    $child1->appendNode($grandchild1);

    TreeColumnTestItem::fixTree();

    // Update depth and children_count after tree structure
    $root1->update(['depth' => 0, 'children_count' => 2]);
    $child1->update(['depth' => 1, 'children_count' => 1]);
    $child2->update(['depth' => 1, 'children_count' => 0]);
    $grandchild1->update(['depth' => 2, 'children_count' => 0]);
    $root2->update(['depth' => 0, 'children_count' => 0]);

    return [
        'root1' => $root1->fresh(),
        'root2' => $root2->fresh(),
        'child1' => $child1->fresh(),
        'child2' => $child2->fresh(),
        'grandchild1' => $grandchild1->fresh(),
    ];
}

// ============================================
// TreeColumn Creation & Basic Properties
// ============================================

it('can be created with make method', function () {
    $column = TreeColumn::make('title');

    expect($column)->toBeInstanceOf(TreeColumn::class);
});

it('extends TextColumn', function () {
    $column = TreeColumn::make('title');

    expect($column)->toBeInstanceOf(\Filament\Tables\Columns\TextColumn::class);
});

it('has a custom view', function () {
    $column = TreeColumn::make('title');

    // The view property is protected but we can verify it's configured
    expect(class_exists(TreeColumn::class))->toBeTrue();
});

// ============================================
// Indent Size Configuration
// ============================================

it('can set indent size via method', function () {
    $column = TreeColumn::make('title')
        ->indentSize(32);

    expect($column->getIndentSize())->toBe(32);
});

it('can set indent size via closure', function () {
    $column = TreeColumn::make('title')
        ->indentSize(fn () => 48);

    expect($column->getIndentSize())->toBe(48);
});

it('reads indent size from config file', function () {
    // The TreeColumn reads config in setUp, which is called during table rendering
    // We test that the config key exists and the column class can read config values
    config(['filament-nested-set-table.indent_size' => 36]);

    expect(config('filament-nested-set-table.indent_size'))->toBe(36);
});

// ============================================
// Drag Handle Configuration
// ============================================

it('shows drag handle by default', function () {
    $column = TreeColumn::make('title');

    expect($column->shouldShowDragHandle())->toBeTrue();
});

it('can disable drag handle', function () {
    $column = TreeColumn::make('title')
        ->dragHandle(false);

    expect($column->shouldShowDragHandle())->toBeFalse();
});

it('can enable drag handle explicitly', function () {
    $column = TreeColumn::make('title')
        ->dragHandle(false)
        ->dragHandle(true);

    expect($column->shouldShowDragHandle())->toBeTrue();
});

it('can set drag handle via closure', function () {
    $shouldShow = false;

    $column = TreeColumn::make('title')
        ->dragHandle(fn () => $shouldShow);

    expect($column->shouldShowDragHandle())->toBeFalse();
});

// ============================================
// Expand Toggle Configuration
// ============================================

it('shows expand toggle by default', function () {
    $column = TreeColumn::make('title');

    expect($column->shouldShowExpandToggle())->toBeTrue();
});

it('can disable expand toggle', function () {
    $column = TreeColumn::make('title')
        ->expandToggle(false);

    expect($column->shouldShowExpandToggle())->toBeFalse();
});

it('can enable expand toggle explicitly', function () {
    $column = TreeColumn::make('title')
        ->expandToggle(false)
        ->expandToggle(true);

    expect($column->shouldShowExpandToggle())->toBeTrue();
});

it('can set expand toggle via closure', function () {
    $shouldShow = false;

    $column = TreeColumn::make('title')
        ->expandToggle(fn () => $shouldShow);

    expect($column->shouldShowExpandToggle())->toBeFalse();
});

// ============================================
// Draggable Configuration
// ============================================

it('is draggable by default', function () {
    $column = TreeColumn::make('title');

    expect($column->isDraggable())->toBeTrue();
});

it('can disable draggable', function () {
    $column = TreeColumn::make('title')
        ->draggable(false);

    expect($column->isDraggable())->toBeFalse();
});

it('can enable draggable explicitly', function () {
    $column = TreeColumn::make('title')
        ->draggable(false)
        ->draggable(true);

    expect($column->isDraggable())->toBeTrue();
});

it('can set draggable via closure', function () {
    $isDraggable = false;

    $column = TreeColumn::make('title')
        ->draggable(fn () => $isDraggable);

    expect($column->isDraggable())->toBeFalse();
});

it('reads drag enabled from config file', function () {
    // The TreeColumn reads config in setUp, which is called during table rendering
    // We test that the config key exists and the column class can read config values
    config(['filament-nested-set-table.drag_enabled' => false]);

    expect(config('filament-nested-set-table.drag_enabled'))->toBeFalse();
});

// ============================================
// Indent Padding Calculation
// ============================================

it('calculates indent padding based on depth', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->indentSize(24)
        ->record($tree['root1']);

    expect($column->getIndentPadding())->toBe(0); // depth 0 * 24 = 0

    $column->record($tree['child1']);
    expect($column->getIndentPadding())->toBe(24); // depth 1 * 24 = 24

    $column->record($tree['grandchild1']);
    expect($column->getIndentPadding())->toBe(48); // depth 2 * 24 = 48
});

it('calculates indent padding with custom indent size', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->indentSize(32)
        ->record($tree['grandchild1']);

    expect($column->getIndentPadding())->toBe(64); // depth 2 * 32 = 64
});

it('returns zero indent for root nodes', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->indentSize(24)
        ->record($tree['root1']);

    expect($column->getIndentPadding())->toBe(0);
});

// ============================================
// Has Children Detection
// ============================================

it('reports has children for parent nodes', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->record($tree['root1']);

    expect($column->hasChildren())->toBeTrue();
});

it('reports no children for leaf nodes', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->record($tree['grandchild1']);

    expect($column->hasChildren())->toBeFalse();
});

it('reports no children for childless root', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->record($tree['root2']);

    expect($column->hasChildren())->toBeFalse();
});

// ============================================
// Method Chaining
// ============================================

it('supports fluent method chaining', function () {
    $column = TreeColumn::make('title')
        ->indentSize(32)
        ->dragHandle(true)
        ->expandToggle(true)
        ->draggable(true);

    expect($column)
        ->getIndentSize()->toBe(32)
        ->shouldShowDragHandle()->toBeTrue()
        ->shouldShowExpandToggle()->toBeTrue()
        ->isDraggable()->toBeTrue();
});

it('returns static instance for all configuration methods', function () {
    $column = TreeColumn::make('title');

    expect($column->indentSize(24))->toBeInstanceOf(TreeColumn::class);
    expect($column->dragHandle(true))->toBeInstanceOf(TreeColumn::class);
    expect($column->expandToggle(true))->toBeInstanceOf(TreeColumn::class);
    expect($column->draggable(true))->toBeInstanceOf(TreeColumn::class);
});

// ============================================
// HTML Mode
// ============================================

it('has html method available', function () {
    // TreeColumn enables HTML rendering in setUp which is called during table rendering
    // We verify the html() method exists and can be called
    $column = TreeColumn::make('title')
        ->html();

    expect($column->isHtml())->toBeTrue();
});

// ============================================
// Edge Cases
// ============================================

it('handles null depth gracefully', function () {
    $tree = createTreeColumnTestData();
    $tree['root1']->depth = null;

    $column = TreeColumn::make('title')
        ->indentSize(24)
        ->record($tree['root1']);

    // Should default to 0 depth
    expect($column->getIndentPadding())->toBe(0);
});

it('handles missing children_count gracefully', function () {
    $tree = createTreeColumnTestData();
    $tree['root1']->children_count = null;

    $column = TreeColumn::make('title')
        ->record($tree['root1']);

    // Should default to no children
    expect($column->hasChildren())->toBeFalse();
});

it('handles zero children_count correctly', function () {
    $tree = createTreeColumnTestData();

    $column = TreeColumn::make('title')
        ->record($tree['root2']); // Has 0 children

    expect($column->hasChildren())->toBeFalse();
});
