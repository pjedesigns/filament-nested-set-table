# Filament Nested Set Table

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pjedesigns/filament-nested-set-table.svg?style=flat-square)](https://packagist.org/packages/pjedesigns/filament-nested-set-table)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pjedesigns/filament-nested-set-table/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pjedesigns/filament-nested-set-table/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pjedesigns/filament-nested-set-table/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pjedesigns/filament-nested-set-table/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pjedesigns/filament-nested-set-table.svg?style=flat-square)](https://packagist.org/packages/pjedesigns/filament-nested-set-table)

A Filament 4 table component for displaying and managing nested set data structures with drag-and-drop reordering support. Built for use with [kalnoy/nestedset](https://github.com/lazychaser/laravel-nestedset).

## Features

- **Preserves Standard Filament Tables**: Uses standard Filament table rows with columns, filters, and actions
- **Drag-and-Drop Reordering**: Intuitive touch-friendly drag-and-drop with visual drop zones
- **Nested Set Integration**: Works seamlessly with `kalnoy/nestedset` package
- **Expand/Collapse**: Toggle visibility of child nodes
- **Scoped Trees**: Supports scoped nested sets (e.g., navigation items by navigation_id)
- **Authorization**: Integrates with model policies for move permission checks
- **Undo Support**: Temporary undo button for accidental moves

## Requirements

- PHP 8.4+
- Laravel 12+
- Filament 4.0+
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

## Usage

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
}
```

### 2. Add HasTree Trait to Your ListRecords Page

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\ListRecords;
use Pjedesigns\FilamentNestedSetTable\Concerns\HasTree;

class ListCategories extends ListRecords
{
    use HasTree;

    protected static string $resource = CategoryResource::class;

    // The trait automatically modifies the table query for tree display
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
            ->modifyQueryUsing(fn ($query) => $query->withDepth()->withCount('children'))
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

### 4. Authorization (Optional)

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

    // Expand all nodes by default
    'default_expanded' => true,

    // Undo duration in seconds
    'undo_duration' => 10,

    // Enable broadcasting for real-time updates
    'broadcast_enabled' => false,

    // Touch delay to prevent accidental drags (ms)
    'touch_delay' => 150,
];
```

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

## Events

The package dispatches the following events:

- `NodeMoved` - When a node is successfully moved
- `NodeMoveFailed` - When a move operation fails
- `TreeFixed` - When tree structure is repaired

## Testing

```bash
composer test
```

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
