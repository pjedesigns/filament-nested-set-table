<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;

beforeEach(function () {
    Schema::create('trait_test_items', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('name')->nullable();
        $table->string('icon')->nullable();
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();

        $table->index(['_lft', '_rgt', 'parent_id']);
    });
});

afterEach(function () {
    Schema::dropIfExists('trait_test_items');
});

// Default implementation model
class DefaultTraitTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'trait_test_items';

    protected $fillable = ['title', 'name', 'icon'];
}

// Custom overrides model
class CustomTraitTestItem extends Model
{
    use InteractsWithTree;
    use NodeTrait;

    protected $table = 'trait_test_items';

    protected $fillable = ['title', 'name', 'icon'];

    public function getTreeLabelColumn(): string
    {
        return 'name';
    }

    public function getTreeIcon(): ?string
    {
        return $this->icon;
    }

    public function canHaveChildren(): bool
    {
        return false;
    }

    public function canBeDragged(): bool
    {
        return false;
    }

    public function getMaxTreeDepth(): int
    {
        return 5;
    }
}

// ============================================
// Default InteractsWithTree Behavior
// ============================================

it('default getTreeLabelColumn returns title', function () {
    $item = new DefaultTraitTestItem;

    expect($item->getTreeLabelColumn())->toBe('title');
});

it('default getTreeLabel returns title attribute value', function () {
    $item = DefaultTraitTestItem::create(['title' => 'My Title']);

    expect($item->getTreeLabel())->toBe('My Title');
});

it('default getTreeIcon returns heroicon-o-folder', function () {
    $item = new DefaultTraitTestItem;

    expect($item->getTreeIcon())->toBe('heroicon-o-folder');
});

it('default canHaveChildren returns true', function () {
    $item = new DefaultTraitTestItem;

    expect($item->canHaveChildren())->toBeTrue();
});

it('default canBeDragged returns true', function () {
    $item = new DefaultTraitTestItem;

    expect($item->canBeDragged())->toBeTrue();
});

it('default getMaxTreeDepth reads from config', function () {
    config(['filament-nested-set-table.max_depth' => 4]);

    $item = new DefaultTraitTestItem;

    expect($item->getMaxTreeDepth())->toBe(4);
});

it('default getMaxTreeDepth returns 0 when config not set', function () {
    config(['filament-nested-set-table.max_depth' => 0]);

    $item = new DefaultTraitTestItem;

    expect($item->getMaxTreeDepth())->toBe(0);
});

it('default getTreeScopeAttributes returns empty array', function () {
    $item = new DefaultTraitTestItem;

    expect($item->getTreeScopeAttributes())->toBe([]);
});

// ============================================
// Custom Override Behavior
// ============================================

it('custom getTreeLabelColumn returns overridden column', function () {
    $item = new CustomTraitTestItem;

    expect($item->getTreeLabelColumn())->toBe('name');
});

it('custom getTreeLabel uses overridden column', function () {
    $item = CustomTraitTestItem::create(['title' => 'Title', 'name' => 'Custom Name']);

    expect($item->getTreeLabel())->toBe('Custom Name');
});

it('custom getTreeIcon returns overridden value', function () {
    $item = CustomTraitTestItem::create(['title' => 'Test', 'icon' => 'heroicon-o-star']);

    expect($item->getTreeIcon())->toBe('heroicon-o-star');
});

it('custom getTreeIcon returns null when icon is null', function () {
    $item = CustomTraitTestItem::create(['title' => 'Test']);

    expect($item->getTreeIcon())->toBeNull();
});

it('custom canHaveChildren returns false', function () {
    $item = new CustomTraitTestItem;

    expect($item->canHaveChildren())->toBeFalse();
});

it('custom canBeDragged returns false', function () {
    $item = new CustomTraitTestItem;

    expect($item->canBeDragged())->toBeFalse();
});

it('custom getMaxTreeDepth returns overridden value', function () {
    $item = new CustomTraitTestItem;

    expect($item->getMaxTreeDepth())->toBe(5);
});

// ============================================
// getSiblingPosition Tests
// ============================================

it('getSiblingPosition returns 0 for first sibling', function () {
    $root = DefaultTraitTestItem::create(['title' => 'Root']);
    $child1 = DefaultTraitTestItem::create(['title' => 'Child 1']);
    $child2 = DefaultTraitTestItem::create(['title' => 'Child 2']);

    $root->appendNode($child1);
    $root->appendNode($child2);
    DefaultTraitTestItem::fixTree();

    $child1->refresh();

    expect($child1->getSiblingPosition())->toBe(0);
});

it('getSiblingPosition returns correct index for subsequent siblings', function () {
    $root = DefaultTraitTestItem::create(['title' => 'Root']);
    $child1 = DefaultTraitTestItem::create(['title' => 'Child 1']);
    $child2 = DefaultTraitTestItem::create(['title' => 'Child 2']);
    $child3 = DefaultTraitTestItem::create(['title' => 'Child 3']);

    $root->appendNode($child1);
    $root->appendNode($child2);
    $root->appendNode($child3);
    DefaultTraitTestItem::fixTree();

    expect($child1->fresh()->getSiblingPosition())->toBe(0);
    expect($child2->fresh()->getSiblingPosition())->toBe(1);
    expect($child3->fresh()->getSiblingPosition())->toBe(2);
});

it('getSiblingPosition returns 0 for root with no siblings', function () {
    $root = DefaultTraitTestItem::create(['title' => 'Root']);
    DefaultTraitTestItem::fixTree();

    expect($root->fresh()->getSiblingPosition())->toBe(0);
});

// ============================================
// getTreeScopeAttributes with Scoped Models
// ============================================

it('getTreeScopeAttributes returns scope columns when getScopeAttributes defined', function () {
    $item = new class extends Model
    {
        use InteractsWithTree;
        use NodeTrait;

        protected $table = 'trait_test_items';

        protected function getScopeAttributes(): array
        {
            return ['navigation_id', 'site_id'];
        }
    };

    expect($item->getTreeScopeAttributes())->toBe(['navigation_id', 'site_id']);
});
