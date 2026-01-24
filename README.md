[# Filament Nested Set Table
]()
[![Latest Version on Packagist](https://img.shields.io/packagist/v/pjedesigns/filament-nested-set-table.svg?style=flat-square)](https://packagist.org/packages/pjedesigns/filament-nested-set-table)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pjedesigns/filament-nested-set-table/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pjedesigns/filament-nested-set-table/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pjedesigns/filament-nested-set-table/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pjedesigns/filament-nested-set-table/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pjedesigns/filament-nested-set-table.svg?style=flat-square)](https://packagist.org/packages/pjedesigns/filament-nested-set-table)

A Filament table component for displaying and managing nested set data structures with drag-and-drop reordering support. Works with Filament v4/v5. Built for use with [kalnoy/nestedset](https://github.com/lazychaser/laravel-nestedset).

## Features

- **Two Display Modes**: Standard Filament table with tree features OR dedicated ordering page
- **Drag-and-Drop Reordering**: Intuitive touch-friendly drag-and-drop with visual drop zones
- **Nested Set Integration**: Works seamlessly with `kalnoy/nestedset` package
- **Tree-Aware Actions**: Delete, force-delete, and restore actions that show descendant counts
- **Expand/Collapse**: Toggle visibility of child nodes with session persistence
- **Lazy Loading**: Only loads visible nodes for better performance with large trees (HasTree)
- **Eager Loading Support**: Configure relationships to eager load with tree queries
- **Smart Pagination**: Pagination counts root nodes only, children are loaded on expand
- **Scoped Trees**: Supports scoped nested sets (e.g., navigation items by navigation_id)
- **Authorization**: Integrates with model policies for move permission checks
- **Undo Support**: Temporary undo button for accidental moves
- **Dark Mode**: Full Filament dark mode support

## Requirements

- PHP 8.4+
- Laravel 12+
- Filament v4/v5
- kalnoy/nestedset 6.0+

## Installation

Install the package via composer:

```bash
composer require pjedesigns/filament-nested-set-table
```

Publish the config file (optional):

```bash
php artisan vendor:publish --tag="filament-nested-set-table-config"
```

## Usage Options

This package provides two ways to display and manage nested set data:

| Feature | HasTree (Table) | OrderPage (Dedicated) |
|---------|-----------------|----------------------|
| Use case | Full CRUD with tree | Focused reordering |
| Loading | Lazy (on expand) | All at once |
| Expand/Collapse | Server call | Pure JavaScript |
| Columns/Actions | Full Filament support | Label only |
| Best for | Data management | Quick reordering |

---

## Option 1: HasTree Trait (Table Integration)

Best for: Full CRUD functionality with tree visualization in standard Filament tables.

### 1. Prepare Your Model

Ensure your model uses the `NodeTrait` from kalnoy/nestedset and optionally add the `InteractsWithTree` trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;

class Category extends Model
{
    use NodeTrait;
    use InteractsWithTree;

    protected $fillable = ['title'];

    // Optional: customize the label column
    public function getTreeLabelColumn(): string
    {
        return 'title';
    }

    // Optional: provide an icon for tree nodes
    public function getTreeIcon(): ?string
    {
        return 'heroicon-o-folder';
    }

    // Optional: control if node can be dragged
    public function canBeDragged(): bool
    {
        return true;
    }

    // Optional: control if node can have children
    public function canHaveChildren(): bool
    {
        return true;
    }
}
```

### 2. Add HasTree Trait to Your ListRecords Page

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Pjedesigns\FilamentNestedSetTable\Concerns\HasTree;

class ListCategories extends ListRecords
{
    use HasTree;

    protected static string $resource = CategoryResource::class;

    // Optional: Configure eager loading for relationships
    protected function getTreeWith(): array
    {
        return ['media', 'author'];
    }

    // Optional: Add expand/collapse all actions
    protected function getHeaderActions(): array
    {
        return [
            Action::make('expandAll')
                ->label(__('Expand All'))
                ->icon('heroicon-o-chevron-double-down')
                ->color('gray')
                ->action(fn () => $this->expandAllNodes()),
            Action::make('collapseAll')
                ->label(__('Collapse All'))
                ->icon('heroicon-o-chevron-double-up')
                ->color('gray')
                ->action(fn () => $this->collapseAllNodes()),
            // ... other actions
        ];
    }
}
```

### 3. Configure Your Table with TreeColumn

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Pjedesigns\FilamentNestedSetTable\Tables\Columns\TreeColumn;

class CategoryResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // Recommended: disable row click for drag-and-drop
            ->defaultSort('_lft', 'asc')
            ->columns([
                TreeColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->dragHandle()      // Show drag handle
                    ->expandToggle()    // Show expand/collapse toggle
                    ->indentSize(24),   // Pixels per depth level

                // Add any other columns as normal
                TextColumn::make('slug'),
            ]);
    }
}
```

**Note:** The `HasTree` trait automatically handles query modifications (`withDepth`, `withCount('children')`, lazy loading). Do not add `modifyQueryUsing` for these - use `getTreeWith()` for eager loading relationships instead.

---

## Option 2: OrderPage (Dedicated Ordering Page)

Best for: Focused, fast reordering experience with minimal server calls.

The `OrderPage` is a dedicated Filament page optimized for tree reordering:
- Loads all nodes at once (no lazy loading delays)
- Expand/collapse is pure JavaScript (no server calls)
- Server calls only when you drop a node
- Built-in Expand All / Collapse All buttons
- Built-in Fix Tree action

### Order Page Features

The Order Page provides a streamlined UI with:
- **Header buttons**: Expand All, Collapse All, Fix Tree, Back to List
- **Undo support**: After moving a node, an Undo button appears in the success notification
- **Conditional description**: Shows appropriate help text based on max depth setting
- **Visual drop zones**: Blue indicators show where items will be placed

### 1. Create Your Order Page

Create a page class that extends `OrderPage` and links it to your resource:

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Pjedesigns\FilamentNestedSetTable\Pages\OrderPage;

class OrderCategories extends OrderPage
{
    // Link to your resource - model is automatically resolved
    protected static string $resource = CategoryResource::class;

    // Optional: override the page title (default: "Order {PluralModelLabel}")
    // protected static ?string $title = 'Order Categories';

    // Optional: customize the label column (default: 'title')
    public function getLabelColumn(): string
    {
        return 'name';
    }

    // Optional: set max depth (default: from config, 0 = unlimited)
    // When set to 1, only reordering is allowed (no nesting)
    public function getMaxDepth(): int
    {
        return 5;
    }

    // Optional: customize indent size (default: from config)
    public function getIndentSize(): int
    {
        return 24;
    }

    // Optional: eager load relationships
    public function getEagerLoading(): array
    {
        return ['media'];
    }

    // Optional: filter by scope (for scoped nested sets)
    public function getScopeFilter(): array
    {
        return ['navigation_id' => 1];
    }

    // Optional: disable drag and drop
    public function isDragEnabled(): bool
    {
        return true;
    }
}
```

### 2. Register the Page

Add it to your Resource's pages array:

```php
// In your Resource class
public static function getPages(): array
{
    return [
        'index' => Pages\ListCategories::route('/'),
        'create' => Pages\CreateCategory::route('/create'),
        'edit' => Pages\EditCategory::route('/{record}/edit'),
        'order' => Pages\OrderCategories::route('/order'), // Add ordering page
    ];
}
```

### 3. Link to Order Page from List Page

```php
// In your ListRecords page
protected function getHeaderActions(): array
{
    return [
        Action::make('order')
            ->label('Reorder')
            ->icon('heroicon-o-bars-arrow-down')
            ->url(OrderCategories::getUrl()),
        // ... other actions
    ];
}
```

---

## Authorization

Add a `reorder` method to your model's policy:

```php
<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function reorder(User $user, Category $category): bool
    {
        return $user->can('update', $category);
    }
}
```

If no `reorder` method exists, the package falls back to checking `update` permission.

---

## Configuration

```php
// config/filament-nested-set-table.php

return [
    // Default indentation per depth level (pixels)
    'indent_size' => 24,

    // Enable drag-and-drop by default
    'drag_enabled' => true,

    // Maximum tree depth (0 = unlimited)
    'max_depth' => 0,

    // Remember expanded/collapsed state in session
    'remember_expanded_state' => true,

    // Expand all nodes by default on first visit
    'default_expanded' => false,

    // Undo duration in seconds
    'undo_duration' => 10,

    // Enable broadcasting for real-time updates
    'broadcast_enabled' => false,

    // Touch delay to prevent accidental drags (ms)
    'touch_delay' => 150,
];
```

---

## TreeColumn Options

```php
TreeColumn::make('title')
    ->indentSize(24)           // Set indent size per level
    ->dragHandle(true)         // Show/hide drag handle
    ->expandToggle(true)       // Show/hide expand toggle
    ->draggable(true)          // Enable/disable dragging
    ->icon('heroicon-o-folder') // Set an icon
    ->badge()                   // Display as badge (inherited from TextColumn)
    ->searchable()              // Make searchable (inherited)
    ->sortable();               // Make sortable (inherited)
```

---

## HasTree Trait Methods

The `HasTree` trait provides several useful methods:

```php
// Expand/Collapse
$this->expandAllNodes();        // Expand all nodes
$this->collapseAllNodes();      // Collapse all nodes
$this->toggleNode($nodeId);     // Toggle a specific node
$this->isNodeExpanded($nodeId); // Check if node is expanded

// State management
$this->resetTreeState();        // Reset to default state
$this->clearExpandedState();    // Clear session state

// Configuration
$this->getTreeWith();           // Override to specify eager loading
$this->getMaxDepth();           // Override to set custom max depth
```

### Overriding Max Depth Per Page

You can override the maximum tree depth for a specific ListRecords page by overriding the `getMaxDepth()` method:

```php
class ListCategories extends ListRecords
{
    use HasTree;

    protected static string $resource = CategoryResource::class;

    // Override max depth for this specific page (0 = unlimited)
    public function getMaxDepth(): int
    {
        return 5; // Limit to 5 levels for this page only
    }
}
```

This overrides the global config value (`filament-nested-set-table.max_depth`) for this specific page.

---

## Tree-Aware Actions

When working with nested set data, deleting, force-deleting, or restoring a node will also affect its descendants. The `kalnoy/nestedset` package automatically cascades these operations to child nodes.

This package provides tree-aware action classes that display the descendant count in the confirmation modal, so users know exactly how many items will be affected.

### Available Actions

| Action | Description |
|--------|-------------|
| `TreeDeleteAction` | Shows count of child items that will also be soft-deleted |
| `TreeForceDeleteAction` | Shows count of child items (including trashed) that will be permanently deleted |
| `TreeRestoreAction` | Shows count of trashed child items that will also be restored |

### Basic Usage

```php
use Pjedesigns\FilamentNestedSetTable\Actions\TreeDeleteAction;
use Pjedesigns\FilamentNestedSetTable\Actions\TreeForceDeleteAction;
use Pjedesigns\FilamentNestedSetTable\Actions\TreeRestoreAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([
            TreeDeleteAction::make(),
            TreeRestoreAction::make(),
            TreeForceDeleteAction::make(),
        ]);
}
```

### Custom Delete Logic

The tree actions extend Filament's base actions (`DeleteAction`, `ForceDeleteAction`, `RestoreAction`) and only add the `modalDescription` showing the descendant count. The actual delete/restore logic uses the default Filament behavior.

If your application has custom delete logic (e.g., using service classes, custom validation, or additional cleanup), you should extend the tree actions and override the `using()` method:

```php
<?php

namespace App\Filament\Actions;

use Illuminate\Database\Eloquent\Model;
use Pjedesigns\FilamentNestedSetTable\Actions\TreeDeleteAction as BaseTreeDeleteAction;

class TreeDeleteAction extends BaseTreeDeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        // Customize modal heading
        $this->modalHeading(fn (): string => __('Delete :title', ['title' => $this->getRecordTitle()]));

        // Customize success notification
        $this->successNotificationTitle(fn (?Model $record): string =>
            __(':title deleted successfully', ['title' => $record?->title ?? 'Record'])
        );

        // Custom delete logic
        $this->using(function (Model $record): Model {
            // Your custom delete logic here
            // For example, using a service class:
            $record->getService()->delete($record);

            return $record;
        });
    }
}
```

### Handling Orphaned Nodes on Restore

When restoring a soft-deleted node, its parent may have been permanently deleted. The `kalnoy/nestedset` package will restore the node, but it may be left orphaned (with a `parent_id` pointing to a non-existent record).

Consider handling this in your custom restore action:

```php
$this->using(function (Model $record): Model {
    // Check if parent was permanently deleted
    if ($record->parent_id && ! $record->parent) {
        // Make this node a root node
        $record->saveAsRoot();
    }

    $record->restore();

    return $record;
});
```

### Translations

The actions use the following translation keys from `filament-nested-set-table::actions`:

```php
return [
    'delete_confirm' => 'Are you sure you want to delete this item?',
    'delete_confirm_with_children' => 'Are you sure you want to delete this item? This will also delete :count child item.|Are you sure you want to delete this item? This will also delete :count child items.',

    'force_delete_confirm' => 'Are you sure you want to permanently delete this item? This action cannot be undone.',
    'force_delete_confirm_with_children' => 'Are you sure you want to permanently delete this item? This will also permanently delete :count child item. This action cannot be undone.|Are you sure you want to permanently delete this item? This will also permanently delete :count child items. This action cannot be undone.',

    'restore_confirm' => 'Are you sure you want to restore this item?',
    'restore_confirm_with_children' => 'Are you sure you want to restore this item? This will also restore :count child item.|Are you sure you want to restore this item? This will also restore :count child items.',
];
```

Publish translations to customize:

```bash
php artisan vendor:publish --tag="filament-nested-set-table-translations"
```

---

## InteractsWithTree Trait Methods

Add this trait to your model for additional customization:

```php
use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;

class Category extends Model
{
    use NodeTrait;
    use InteractsWithTree;

    // Get the label for tree display
    public function getTreeLabel(): string
    {
        return $this->getAttribute($this->getTreeLabelColumn());
    }

    // Column used for tree label (default: 'title')
    public function getTreeLabelColumn(): string
    {
        return 'title';
    }

    // Icon for this node (default: 'heroicon-o-folder')
    public function getTreeIcon(): ?string
    {
        return 'heroicon-o-folder';
    }

    // Can this node have children? (default: true)
    public function canHaveChildren(): bool
    {
        return true;
    }

    // Can this node be dragged? (default: true)
    public function canBeDragged(): bool
    {
        return true;
    }

    // Max depth for this tree (default: from config)
    public function getMaxTreeDepth(): int
    {
        return config('filament-nested-set-table.max_depth', 0);
    }
}
```

---

## Eager Loading Relationships

To eager load relationships with tree queries, override the `getTreeWith()` method (HasTree) or `getEagerLoading()` method (OrderPage):

```php
// HasTree (ListRecords page)
protected function getTreeWith(): array
{
    return ['media', 'author', 'tags'];
}

// OrderPage
public function getEagerLoading(): array
{
    return ['media', 'author', 'tags'];
}
```

This ensures relationships are loaded efficiently when fetching tree nodes, preventing N+1 query issues.

---

## Scoped Trees

For models with scoped nested sets (e.g., navigation items scoped by navigation_id):

```php
class NavigationItem extends Model
{
    use NodeTrait;
    use InteractsWithTree;

    protected function getScopeAttributes(): array
    {
        return ['navigation_id'];
    }
}
```

The package will automatically prevent moving nodes between different scopes.

For OrderPage, you can filter by scope:

```php
public function getScopeFilter(): array
{
    return ['navigation_id' => $this->navigationId];
}
```

---

## Nested Resources (Child Resources)

The `OrderPage` fully supports Filament's nested resources. When using a child resource (a resource that has a `$parentResource` property), the package automatically handles parent record resolution.

### Example: Navigation Items as a Nested Resource

If you have a `NavigationResource` with a nested `NavigationItemResource`:

```php
// NavigationItemResource.php
class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;
    protected static ?string $parentResource = NavigationResource::class;

    public static function getPages(): array
    {
        return [
            'create' => CreateNavigationItem::route('/create'),
            'edit' => EditNavigationItem::route('/{record}/edit'),
            'order' => OrderNavigationItems::route('/order'),
        ];
    }
}
```

Your OrderPage implementation is simple - just use `getParentRecord()` to access the parent:

```php
// OrderNavigationItems.php
use Pjedesigns\FilamentNestedSetTable\Pages\OrderPage;

class OrderNavigationItems extends OrderPage
{
    protected static string $resource = NavigationItemResource::class;

    public function getScopeFilter(): array
    {
        return ['navigation_id' => $this->getParentRecord()?->getKey()];
    }
}
```

### How It Works

The `OrderPage` uses Filament's `InteractsWithParentRecord` trait, which:

1. **Automatically resolves the parent record** from route parameters
2. **Provides `getParentRecord()`** method to access the parent model instance
3. **Provides `getParentResource()`** static method to get the parent resource class
4. **Handles authorization** by checking view/edit permissions on the parent

### Linking to the Order Page from a Relation Page

In your parent resource's `ManageRelatedRecords` page:

```php
// ManageNavigationItems.php (in NavigationResource)
class ManageNavigationItems extends ManageRelatedRecords
{
    protected static string $resource = NavigationResource::class;
    protected static string $relationship = 'navigationItems';
    protected static ?string $relatedResource = NavigationItemResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('order')
                    ->label('Order Items')
                    ->icon('heroicon-o-bars-arrow-down')
                    ->url(NavigationItemResource::getUrl('order', [
                        'navigation' => $this->record->id
                    ])),
            ]);
    }
}
```

### Back Navigation

The `OrderPage` automatically handles the "Back to List" button for nested resources. It will navigate back to the appropriate parent page (typically the `ManageRelatedRecords` page or the parent's edit page).

---

## Events

The package dispatches the following events:

| Event | Description | Properties |
|-------|-------------|------------|
| `NodeMoved` | Node successfully moved | `$node`, `$result`, `$previousParentId`, `$previousPosition` |
| `NodeMoveFailed` | Move operation failed | `$node`, `$error`, `$attemptedParentId`, `$attemptedPosition` |
| `TreeFixed` | Tree structure repaired | `$modelClass`, `$nodesFixed`, `$scopeAttributes` |

### Listening to Events

```php
// In EventServiceProvider or a listener
use Pjedesigns\FilamentNestedSetTable\Events\NodeMoved;

Event::listen(NodeMoved::class, function (NodeMoved $event) {
    // Log the move
    activity()
        ->performedOn($event->node)
        ->log('Node moved');
});
```

---

## Translations

The package includes English translations. Publish them to customize:

```bash
php artisan vendor:publish --tag="filament-nested-set-table-translations"
```

Available translation keys:

```php
return [
    'move_success' => 'Item moved successfully.',
    'move_failed' => 'Failed to move item.',
    'undo_success' => 'Move undone successfully.',
    'item_moved' => 'Item moved',
    'unauthorized' => 'You are not authorized to move this item.',
    'circular_reference' => 'Cannot move an item under its own descendant.',
    'max_depth_exceeded' => 'Cannot move here: would exceed maximum depth of :max levels.',
    'expand_all' => 'Expand All',
    'collapse_all' => 'Collapse All',
    'fix_tree' => 'Fix Tree',
    'undo' => 'Undo',
    'back_to_list' => 'Back to :resource',
    'tree_structure' => 'Order Tree',
    'tree_description' => 'Drag and drop items to reorder. Drop on an item to make it a child.',
    'tree_description_flat' => 'Drag and drop items to reorder.',
    // ... and more
];
```

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Paul Egan](https://github.com/pjedesigns)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
