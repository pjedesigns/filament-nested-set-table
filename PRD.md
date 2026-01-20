# Product Requirements Document (PRD)

## Filament Nested Set Table

**Package:** `pjedesigns/filament-nested-set-table`
**Version:** 1.0.0
**Author:** Paul Egan
**Date:** January 2026
**Timeline:** ~1 month (thorough MVP with documentation)

---

## 1. Executive Summary

### 1.1 Purpose

Create a Filament 4 package that provides a tree table component for managing hierarchical data stored using the `kalnoy/nestedset` package. The package will enable drag-and-drop reordering of nested set records directly within the Filament admin panel while preserving the standard Filament table row styling and configuration patterns.

### 1.2 Key Differentiators from filament-tree

| Aspect | filament-tree | filament-nested-set-table |
|--------|---------------|---------------------------|
| Row Rendering | Custom row component with fixed layout | Standard Filament table rows with columns, filters, actions |
| Table Configuration | Separate form definitions | Uses standard `Table::configure()` pattern |
| Tree Management | Full tree page replacement | **Both**: Drop-in enhancement for ListRecords AND dedicated TreePage |
| Nested Set Package | Bundled implementation | Uses existing `kalnoy/nestedset` |
| Design Philosophy | Self-contained tree UI | Minimal modification to Filament defaults |
| Touch Support | Limited | Full mobile/touch support from Phase 1 |

### 1.3 Target Use Cases

- Pages with hierarchical structure (CMS)
- Navigation menu items
- Categories with subcategories
- Organizational structures
- Any model using `kalnoy/nestedset`'s `NodeTrait`

### 1.4 Expected Scale

- Primary focus: Small trees (< 100 nodes)
- Suitable for: Navigation menus, small category trees, CMS pages
- Pagination disabled in tree mode (collapse manages large sets)

---

## 2. Goals & Non-Goals

### 2.1 Goals

1. **Preserve Filament Table Patterns**: Developers should configure tables exactly as they do in standard Filament resources using `Table::configure()`, `columns()`, `filters()`, `actions()`, etc.

2. **Non-Invasive Row Styling**: Only add visual indicators for tree structure (indentation, drag handles, expand/collapse) without overriding Filament's table row styling. CSS indent only - no connector lines.

3. **Drag-and-Drop Reordering**: Enable moving nodes between parents and reordering siblings via intuitive drag-and-drop with explicit drop zones.

4. **Nested Set Integration**: Work seamlessly with `kalnoy/nestedset` using its existing `NodeTrait` and tree manipulation methods.

5. **Scoped Tree Support**: Support `scoped` nested sets where multiple trees exist in one table (e.g., navigation items scoped by `navigation_id`). Cross-scope moves are blocked.

6. **Dual Approach**: Provide both `HasTree` trait for ListRecords enhancement AND a dedicated `TreePage` class.

7. **Mobile-First Drag**: Touch/mobile support is essential from Phase 1.

8. **Authorization**: Integrate with model policies for move permission checks.

9. **Real-time Updates**: Optional Laravel Echo/Reverb integration for collaborative editing.

10. **Event System**: Full Laravel event dispatch for NodeMoved, NodeMoveFailed, TreeFixed.

### 2.2 Non-Goals

1. **Replace Filament Tables**: This is not a replacement for Filament's table system; it enhances it.

2. **Non-Nested-Set Models**: Only models using `kalnoy/nestedset` are supported.

3. **Complex Inline Editing**: Use Filament's existing modal actions.

4. **Virtual Scrolling**: Not needed for target scale (< 100 nodes).

5. **Backward Compatibility**: Clean break from existing OrderPage/OrderableModelTrait.

---

## 3. Decisions from Interview

### 3.1 Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Page Options | Both ListRecords enhancement AND TreePage | Maximum flexibility |
| Code Sharing | Shared core, different presentation | TreePage and HasTree use same trait/service layer |
| Migration Path | Clean break | No compatibility layer with old OrderPage |
| Simple Resources | Both supported | Works with modal CRUD and full-page resources |

### 3.2 UX Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Drag UX | Explicit drop zones | Line between rows for siblings, highlighted area for "make child" |
| Collapsed Search | Auto-expand to show matches | Parents auto-expand when children match search/filter |
| Tree Mode Toggle | Toggle button in header | Users can switch between Tree View and Flat List |
| Visual Style | CSS indent only | Clean, minimal - matches Filament aesthetic |
| Root Node Style | No distinction | Same as children, just not indented |
| Notifications | Standard Filament | Use built-in notification system |

### 3.3 Behavior Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Move with Children | Always | Standard nested set behavior - subtree moves together |
| Max Depth Exceeded | Auto-adjust position | Place as sibling if can't go deeper |
| Cross-Scope Move | Block | Error when attempting to move between scopes |
| Pagination | Disabled in tree mode | Collapse manages large sets; pagination only in flat mode |
| Undo/Redo | Single undo only | "Undo last move" button appears for ~10 seconds after move |

### 3.4 Technical Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| JS Bundling | Bundled with package | Sortable.js compiled into package, no CDN dependency |
| TreeColumn Design | Extend TextColumn | Inherits all TextColumn features (icon, badge, formatting) |
| Tree Integrity | Warn with fix action | Header action appears when corruption detected |
| Real-time | Optional/configurable | Event broadcasting disabled by default, opt-in via config |
| Re-render Strategy | Surgical updates | Only re-render affected rows, not entire table |
| Authorization | Model policy integration | Check 'reorder' ability on model's policy |
| Event System | Full Laravel events | Dispatch events for all significant actions |
| Keyboard Nav | Basic only | Arrow keys navigate, Enter/Space expand/collapse |
| API Naming | Filament-style | HasTree, TreeColumn - match Filament conventions |

### 3.5 Feature Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Create Child Action | Provide helper only | Method like `->getCreateChildUrl($parentId)` for developers |
| Fix Tree Location | Header action | Visible alongside Create button when issues detected |
| Touch Support | Essential - Phase 1 | Must work on tablets/phones from initial release |
| Bulk Actions | Selected only | Standard Filament behavior, no auto-include descendants |
| TreePage Features | Full table features | Filters, search, bulk actions all available |
| Examples | Documentation only | Comprehensive docs, no demo model/resource included |

---

## 4. Technical Architecture

### 4.1 Package Structure

```
packages/pjedesigns/filament-nested-set-table/
├── src/
│   ├── FilamentNestedSetTableServiceProvider.php
│   ├── Concerns/
│   │   ├── HasTree.php                    # Trait for ListRecords pages
│   │   └── InteractsWithTree.php          # Trait for Eloquent models
│   ├── Tables/
│   │   └── Columns/
│   │       └── TreeColumn.php             # Extends TextColumn with tree indicators
│   ├── Pages/
│   │   └── TreePage.php                   # Dedicated tree page (extends Page)
│   ├── Actions/
│   │   ├── FixTreeAction.php              # Header action to fix corrupted tree
│   │   └── UndoMoveAction.php             # Temporary undo button
│   ├── Events/
│   │   ├── NodeMoved.php
│   │   ├── NodeMoveFailed.php
│   │   └── TreeFixed.php
│   └── Services/
│       └── TreeMover.php                  # Business logic for moving nodes
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   ├── tree-indicators.blade.php  # Indent + expand/collapse + drag handle
│   │   │   ├── drop-zone.blade.php        # Visual drop target indicators
│   │   │   └── undo-banner.blade.php      # Temporary undo notification
│   │   └── pages/
│   │       └── tree-page.blade.php        # TreePage view
│   ├── js/
│   │   └── filament-nested-set-table.js   # Sortable.js bundled + touch support
│   └── css/
│       └── filament-nested-set-table.css  # Minimal styles for drop zones
├── config/
│   └── filament-nested-set-table.php
└── tests/
    ├── Unit/
    │   └── TreeMoverTest.php
    ├── Feature/
    │   ├── HasTreeTest.php
    │   └── TreePageTest.php
    └── TestCase.php
```

### 4.2 Core Components

#### 4.2.1 HasTree Trait (for ListRecords)

```php
<?php

namespace Pjedesigns\FilamentNestedSetTable\Concerns;

use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

trait HasTree
{
    public bool $treeMode = true;

    public array $expandedNodes = [];

    public ?array $lastMove = null; // For undo functionality

    protected static int $maxDepth = 10;

    protected static bool $rememberExpandedState = true;

    public function bootHasTree(): void
    {
        if (static::$rememberExpandedState) {
            $this->expandedNodes = session()->get($this->getExpandedStateKey(), []);
        }
    }

    public function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()
            ->withDepth()
            ->withCount('children');

        if ($this->treeMode) {
            $query->defaultOrder();
        }

        return $query;
    }

    public function toggleTreeMode(): void
    {
        $this->treeMode = !$this->treeMode;
        $this->resetTable();
    }

    public function toggleNode(int $nodeId): void
    {
        if (in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes = array_diff($this->expandedNodes, [$nodeId]);
        } else {
            $this->expandedNodes[] = $nodeId;
        }

        if (static::$rememberExpandedState) {
            session()->put($this->getExpandedStateKey(), $this->expandedNodes);
        }
    }

    public function isNodeExpanded(int $nodeId): bool
    {
        return in_array($nodeId, $this->expandedNodes);
    }

    #[On('tree-node-moved')]
    public function handleNodeMoved(
        int $nodeId,
        ?int $newParentId,
        int $newPosition
    ): void {
        $model = $this->getModel();
        $node = $model::find($nodeId);

        // Authorization check
        if (!$this->authorizeMove($node)) {
            $this->notifyMoveFailed(__('Unauthorized to move this item.'));
            return;
        }

        // Scope validation
        if (!$this->validateScopeMove($node, $newParentId)) {
            $this->notifyMoveFailed(__('Cannot move between different scopes.'));
            return;
        }

        // Store for undo
        $this->lastMove = [
            'nodeId' => $nodeId,
            'oldParentId' => $node->parent_id,
            'oldPosition' => $node->getSiblingPosition(),
            'timestamp' => now(),
        ];

        // Perform move
        $mover = app(TreeMover::class);
        $result = $mover->move($node, $newParentId, $newPosition, static::$maxDepth);

        if ($result->success) {
            event(new NodeMoved($node, $result));
            $this->notifyMoveSuccess();
            $this->dispatch('tree-updated');
        } else {
            event(new NodeMoveFailed($node, $result->error));
            $this->notifyMoveFailed($result->error);
        }
    }

    public function undoLastMove(): void
    {
        if (!$this->lastMove || $this->lastMove['timestamp']->diffInSeconds(now()) > 10) {
            $this->lastMove = null;
            return;
        }

        $model = $this->getModel();
        $node = $model::find($this->lastMove['nodeId']);

        $mover = app(TreeMover::class);
        $mover->move($node, $this->lastMove['oldParentId'], $this->lastMove['oldPosition']);

        $this->lastMove = null;
        $this->notifyUndoSuccess();
    }

    protected function authorizeMove($node): bool
    {
        $policy = policy($node);

        if ($policy && method_exists($policy, 'reorder')) {
            return $policy->reorder(auth()->user(), $node);
        }

        return true;
    }

    protected function validateScopeMove($node, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return true;
        }

        $newParent = $node->newQuery()->find($newParentId);
        $scopeAttributes = $node->getScopeAttributes();

        foreach ($scopeAttributes as $attr) {
            if ($node->$attr !== $newParent->$attr) {
                return false;
            }
        }

        return true;
    }
}
```

#### 4.2.2 InteractsWithTree Model Trait

```php
<?php

namespace Pjedesigns\FilamentNestedSetTable\Concerns;

trait InteractsWithTree
{
    public function getTreeLabel(): string
    {
        return $this->getAttribute($this->getTreeLabelColumn());
    }

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }

    public function getTreeIcon(): ?string
    {
        return null;
    }

    public function canHaveChildren(): bool
    {
        return true;
    }

    public function canBeDragged(): bool
    {
        return true;
    }

    public function getSiblingPosition(): int
    {
        return $this->siblings()->where('_lft', '<', $this->_lft)->count();
    }

    public function getScopeAttributes(): array
    {
        return [];
    }
}
```

#### 4.2.3 TreeColumn (Extends TextColumn)

```php
<?php

namespace Pjedesigns\FilamentNestedSetTable\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class TreeColumn extends TextColumn
{
    protected string $view = 'filament-nested-set-table::tables.columns.tree-column';

    protected int $indentSize = 24;

    protected bool $showDragHandle = true;

    protected bool $showExpandToggle = true;

    public function indentSize(int $pixels): static
    {
        $this->indentSize = $pixels;
        return $this;
    }

    public function dragHandle(bool $condition = true): static
    {
        $this->showDragHandle = $condition;
        return $this;
    }

    public function expandToggle(bool $condition = true): static
    {
        $this->showExpandToggle = $condition;
        return $this;
    }

    public function getIndentSize(): int
    {
        return $this->indentSize;
    }

    public function shouldShowDragHandle(): bool
    {
        return $this->showDragHandle;
    }

    public function shouldShowExpandToggle(): bool
    {
        return $this->showExpandToggle;
    }
}
```

#### 4.2.4 TreeMover Service

```php
<?php

namespace Pjedesigns\FilamentNestedSetTable\Services;

use Illuminate\Database\Eloquent\Model;

class TreeMover
{
    public function move(
        Model $node,
        ?int $newParentId,
        int $newPosition,
        int $maxDepth = 0
    ): MoveResult {
        // Check max depth and auto-adjust if needed
        if ($maxDepth > 0 && $newParentId !== null) {
            $newParent = $node->newQuery()->find($newParentId);
            $targetDepth = ($newParent->depth ?? 0) + 1;
            $subtreeDepth = $this->getSubtreeDepth($node);

            if ($targetDepth + $subtreeDepth > $maxDepth) {
                // Auto-adjust: make sibling of would-be parent
                $newPosition = $newParent->getSiblingPosition() + 1;
                $newParentId = $newParent->parent_id;
            }
        }

        try {
            if ($newParentId === null) {
                return $this->moveToRoot($node, $newPosition);
            }

            if ($node->parent_id !== $newParentId) {
                return $this->moveToNewParent($node, $newParentId, $newPosition);
            }

            return $this->reorderWithinParent($node, $newPosition);
        } catch (\Exception $e) {
            return new MoveResult(false, $e->getMessage());
        }
    }

    protected function moveToRoot(Model $node, int $position): MoveResult
    {
        $node->saveAsRoot();

        $roots = $node->newQuery()->whereNull('parent_id')->defaultOrder()->get();
        $this->reorderSiblings($node, $roots, $position);

        return new MoveResult(true);
    }

    protected function moveToNewParent(Model $node, int $parentId, int $position): MoveResult
    {
        $parent = $node->newQuery()->find($parentId);

        if ($position === 0) {
            $parent->prependNode($node);
        } else {
            $parent->appendNode($node);
        }

        // Reorder to exact position
        $siblings = $parent->children()->defaultOrder()->get();
        $this->reorderSiblings($node, $siblings, $position);

        return new MoveResult(true);
    }

    protected function reorderWithinParent(Model $node, int $position): MoveResult
    {
        $siblings = $node->siblings()->defaultOrder()->get();
        $currentPosition = $siblings->search(fn ($s) => $s->id === $node->id);

        $moves = $position - $currentPosition;

        if ($moves > 0) {
            $node->down($moves);
        } elseif ($moves < 0) {
            $node->up(abs($moves));
        }

        return new MoveResult(true);
    }

    protected function reorderSiblings(Model $node, $siblings, int $targetPosition): void
    {
        $currentPosition = $siblings->search(fn ($s) => $s->id === $node->id);
        $moves = $targetPosition - $currentPosition;

        if ($moves > 0) {
            $node->down($moves);
        } elseif ($moves < 0) {
            $node->up(abs($moves));
        }
    }

    protected function getSubtreeDepth(Model $node): int
    {
        $maxChildDepth = $node->descendants()
            ->withDepth()
            ->get()
            ->max('depth');

        return $maxChildDepth ? ($maxChildDepth - $node->depth) : 0;
    }
}

class MoveResult
{
    public function __construct(
        public bool $success,
        public ?string $error = null
    ) {}
}
```

#### 4.2.5 TreePage (Dedicated Page)

```php
<?php

namespace Pjedesigns\FilamentNestedSetTable\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Pjedesigns\FilamentNestedSetTable\Concerns\HasTree;

abstract class TreePage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasTree;

    protected static string $view = 'filament-nested-set-table::pages.tree-page';

    // Force tree mode always on for TreePage
    public bool $treeMode = true;

    public function toggleTreeMode(): void
    {
        // No-op for TreePage - always in tree mode
    }

    abstract public static function getModel(): string;

    abstract public static function table(Table $table): Table;
}
```

### 4.3 JavaScript Integration (Bundled with Touch Support)

```javascript
// filament-nested-set-table.js
import Sortable from 'sortablejs';

const initTree = () => {
    const tables = document.querySelectorAll('[data-tree-table]');

    tables.forEach(table => {
        if (table._sortable) {
            table._sortable.destroy();
        }

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        table._sortable = new Sortable(tbody, {
            group: {
                name: 'tree',
                pull: true,
                put: true
            },
            animation: 150,
            handle: '.tree-drag-handle',
            ghostClass: 'tree-ghost',
            chosenClass: 'tree-chosen',
            dragClass: 'tree-drag',

            // Touch support
            delay: 150,
            delayOnTouchOnly: true,
            touchStartThreshold: 3,

            // Nested sorting with drop zones
            fallbackOnBody: true,
            swapThreshold: 0.65,
            invertSwap: true,

            // Drop zone indicators
            onMove: (evt, originalEvent) => {
                const dragged = evt.dragged;
                const related = evt.related;

                // Show drop zone indicators
                clearDropZones();

                if (isOverTopHalf(originalEvent, related)) {
                    related.classList.add('tree-drop-above');
                } else if (isOverBottomHalf(originalEvent, related)) {
                    related.classList.add('tree-drop-below');
                } else {
                    related.classList.add('tree-drop-child');
                }

                return true;
            },

            onEnd: (evt) => {
                clearDropZones();

                const nodeId = parseInt(evt.item.dataset.nodeId);
                const targetRow = evt.item.previousElementSibling || evt.item.nextElementSibling;

                let newParentId = null;
                let newPosition = evt.newIndex;

                // Determine if dropped as child or sibling
                if (evt.item.classList.contains('dropped-as-child')) {
                    newParentId = parseInt(targetRow?.dataset.nodeId);
                    newPosition = 0;
                } else {
                    newParentId = targetRow ? parseInt(targetRow.dataset.parentId) : null;
                }

                if (evt.oldIndex === evt.newIndex && evt.from === evt.to) {
                    return;
                }

                Livewire.dispatch('tree-node-moved', {
                    nodeId,
                    newParentId,
                    newPosition
                });
            }
        });
    });
};

const clearDropZones = () => {
    document.querySelectorAll('.tree-drop-above, .tree-drop-below, .tree-drop-child')
        .forEach(el => el.classList.remove('tree-drop-above', 'tree-drop-below', 'tree-drop-child'));
};

const isOverTopHalf = (event, element) => {
    const rect = element.getBoundingClientRect();
    const y = event.clientY || event.touches?.[0]?.clientY;
    return y < rect.top + rect.height * 0.25;
};

const isOverBottomHalf = (event, element) => {
    const rect = element.getBoundingClientRect();
    const y = event.clientY || event.touches?.[0]?.clientY;
    return y > rect.bottom - rect.height * 0.25;
};

// Initialize
document.addEventListener('DOMContentLoaded', initTree);
document.addEventListener('livewire:navigated', initTree);
Livewire.hook('commit', ({ succeed }) => succeed(initTree));
```

### 4.4 Events

```php
<?php
// Events/NodeMoved.php
namespace Pjedesigns\FilamentNestedSetTable\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NodeMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public $node,
        public $result
    ) {}

    public function broadcastOn(): array
    {
        if (!config('filament-nested-set-table.broadcast_enabled')) {
            return [];
        }

        return ['tree-updates'];
    }

    public function broadcastAs(): string
    {
        return 'node.moved';
    }
}
```

---

## 5. Configuration

```php
<?php
// config/filament-nested-set-table.php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Indent Size
    |--------------------------------------------------------------------------
    */
    'indent_size' => 24,

    /*
    |--------------------------------------------------------------------------
    | Drag and Drop
    |--------------------------------------------------------------------------
    */
    'drag_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Max Depth (0 = unlimited)
    |--------------------------------------------------------------------------
    */
    'max_depth' => 0,

    /*
    |--------------------------------------------------------------------------
    | Remember Expanded State
    |--------------------------------------------------------------------------
    */
    'remember_expanded_state' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Expanded
    |--------------------------------------------------------------------------
    */
    'default_expanded' => true,

    /*
    |--------------------------------------------------------------------------
    | Undo Duration (seconds)
    |--------------------------------------------------------------------------
    */
    'undo_duration' => 10,

    /*
    |--------------------------------------------------------------------------
    | Broadcasting (for real-time collaboration)
    |--------------------------------------------------------------------------
    */
    'broadcast_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Touch Delay (ms) - prevents accidental drags on touch devices
    |--------------------------------------------------------------------------
    */
    'touch_delay' => 150,
];
```

---

## 6. Integration Patterns

### 6.1 Basic Usage (ListRecords Enhancement)

```php
<?php

namespace App\Filament\Resources\Pages\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Pages\PageResource;
use Pjedesigns\FilamentNestedSetTable\Concerns\HasTree;

class ListPages extends ListRecords
{
    use HasTree;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
```

### 6.2 Table Configuration

```php
<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Pjedesigns\FilamentNestedSetTable\Tables\Columns\TreeColumn;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withDepth()->withCount('children'))
            ->defaultSort('_lft', 'asc')
            ->columns([
                TreeColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->indentSize(24)
                    ->dragHandle()
                    ->expandToggle()
                    ->icon(fn ($record) => $record->getTreeIcon()),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                ToggleColumn::make('hidden')
                    ->label('Hidden'),
            ])
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

### 6.3 Dedicated TreePage

```php
<?php

namespace App\Filament\Pages;

use Filament\Tables\Table;
use App\Models\Category;
use Pjedesigns\FilamentNestedSetTable\Pages\TreePage;
use Pjedesigns\FilamentNestedSetTable\Tables\Columns\TreeColumn;

class CategoryTree extends TreePage
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-tree';

    protected static ?string $title = 'Category Tree';

    public static function getModel(): string
    {
        return Category::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TreeColumn::make('name')
                    ->searchable()
                    ->dragHandle()
                    ->expandToggle(),
            ])
            ->filters([
                // Full filter support
            ])
            ->actions([
                // Full action support
            ]);
    }
}
```

### 6.4 Model with Policy

```php
<?php
// app/Models/Page.php

use Pjedesigns\FilamentNestedSetTable\Concerns\InteractsWithTree;

class Page extends Model
{
    use NodeTrait;
    use InteractsWithTree;

    public function getTreeLabelColumn(): string
    {
        return 'title';
    }
}

// app/Policies/PagePolicy.php

class PagePolicy
{
    public function reorder(User $user, Page $page): bool
    {
        return $user->can('update', $page);
    }
}
```

### 6.5 Create Child Helper

```php
// In your Resource or Table class
use Filament\Tables\Actions\Action;

Action::make('createChild')
    ->label('Add Child')
    ->icon('heroicon-o-plus')
    ->url(fn ($record) => PageResource::getUrl('create', ['parent' => $record->id]))
```

---

## 7. Implementation Phases

### Phase 1: Core Foundation + Touch (Week 1-2)

- [ ] Service provider setup with bundled JS
- [ ] `HasTree` trait with basic query modification
- [ ] `TreeColumn` extending TextColumn with indentation
- [ ] Expand/collapse functionality
- [ ] Touch-enabled Sortable.js integration
- [ ] Basic drag-and-drop (siblings only)
- [ ] Unit tests for TreeMover

### Phase 2: Full Drag + Auth (Week 2-3)

- [ ] Drop zones for parent/sibling indication
- [ ] TreeMover service with all move types
- [ ] Policy-based authorization
- [ ] Scope validation (block cross-scope)
- [ ] Max depth with auto-adjust
- [ ] Undo last move (10-second window)
- [ ] Feature tests for Livewire integration

### Phase 3: TreePage + Polish (Week 3-4)

- [ ] TreePage class with full table features
- [ ] Tree mode toggle for ListRecords
- [ ] Fix Tree header action
- [ ] Auto-expand on search match
- [ ] Laravel events (NodeMoved, etc.)
- [ ] Optional Echo broadcasting
- [ ] Session persistence for expanded state
- [ ] Documentation

### Phase 4: Release Prep (Week 4)

- [ ] Browser tests
- [ ] Performance testing
- [ ] Edge case handling
- [ ] README and docs
- [ ] Packagist submission

---

## 8. Testing Strategy

### 8.1 Unit Tests

```php
it('moves node to new parent', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => null]);

    $mover = new TreeMover();
    $result = $mover->move($child, $parent->id, 0);

    expect($result->success)->toBeTrue();
    expect($child->fresh()->parent_id)->toBe($parent->id);
});

it('auto-adjusts when max depth exceeded', function () {
    $root = Category::factory()->create();
    $level1 = Category::factory()->create(['parent_id' => $root->id]);
    $level2 = Category::factory()->create(['parent_id' => $level1->id]);
    $nodeToMove = Category::factory()->create(['parent_id' => null]);

    $mover = new TreeMover();
    $result = $mover->move($nodeToMove, $level2->id, 0, maxDepth: 3);

    // Should become sibling of level2, not child
    expect($nodeToMove->fresh()->parent_id)->toBe($level1->id);
});

it('blocks cross-scope moves', function () {
    $nav1 = Navigation::factory()->create();
    $nav2 = Navigation::factory()->create();
    $item1 = NavigationItem::factory()->create(['navigation_id' => $nav1->id]);
    $item2 = NavigationItem::factory()->create(['navigation_id' => $nav2->id]);

    Livewire::test(ListNavigationItems::class, ['ownerRecord' => $nav1])
        ->dispatch('tree-node-moved', [
            'nodeId' => $item1->id,
            'newParentId' => $item2->id,
            'newPosition' => 0,
        ])
        ->assertNotified('Cannot move between different scopes.');
});
```

### 8.2 Feature Tests

```php
it('handles node moved event with authorization', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create();
    $child = Page::factory()->create(['parent_id' => $page->id]);

    Livewire::actingAs($user)
        ->test(ListPages::class)
        ->dispatch('tree-node-moved', [
            'nodeId' => $child->id,
            'newParentId' => null,
            'newPosition' => 0,
        ])
        ->assertNotified();

    expect($child->fresh()->parent_id)->toBeNull();
});

it('auto-expands parents when search matches child', function () {
    $parent = Page::factory()->create(['title' => 'Parent']);
    $child = Page::factory()->create(['title' => 'UniqueChild', 'parent_id' => $parent->id]);

    Livewire::test(ListPages::class)
        ->set('tableSearch', 'UniqueChild')
        ->assertSee('UniqueChild')
        ->assertSet('expandedNodes', [$parent->id]);
});
```

---

## 9. Success Criteria

1. **Developer Experience**: Adding tree support requires only `use HasTree` and `TreeColumn`
2. **User Experience**: Drag-and-drop feels smooth on both desktop and touch devices
3. **Compatibility**: Works with all Filament table features (filters, search, actions, bulk)
4. **Performance**: Handles < 100 nodes without lag
5. **Reliability**: No data corruption, proper authorization checks
6. **Maintainability**: Clean code following Filament conventions

---

## 10. References

- [filament-tree Package](https://github.com/15web/filament-tree)
- [kalnoy/nestedset Documentation](https://github.com/lazychaser/laravel-nestedset)
- [Sortable.js Documentation](https://sortablejs.github.io/Sortable/)
- [Filament Documentation](https://filamentphp.com/docs/4.x)
