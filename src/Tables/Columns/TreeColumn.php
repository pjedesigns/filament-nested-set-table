<?php

namespace Pjedesigns\FilamentNestedSetTable\Tables\Columns;

use Closure;
use Filament\Tables\Columns\TextColumn;

class TreeColumn extends TextColumn
{
    protected string $view = 'filament-nested-set-table::tables.columns.tree-column';

    protected int|Closure $indentSize = 24;

    protected bool|Closure $showDragHandle = true;

    protected bool|Closure $showExpandToggle = true;

    protected bool|Closure $draggable = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indentSize(config('filament-nested-set-table.indent_size', 24));
        $this->draggable(config('filament-nested-set-table.drag_enabled', true));

        // Enable HTML rendering for the custom view
        $this->html();
    }

    public function indentSize(int|Closure $pixels): static
    {
        $this->indentSize = $pixels;

        return $this;
    }

    public function dragHandle(bool|Closure $condition = true): static
    {
        $this->showDragHandle = $condition;

        return $this;
    }

    public function expandToggle(bool|Closure $condition = true): static
    {
        $this->showExpandToggle = $condition;

        return $this;
    }

    public function draggable(bool|Closure $condition = true): static
    {
        $this->draggable = $condition;

        return $this;
    }

    public function getIndentSize(): int
    {
        return $this->evaluate($this->indentSize);
    }

    public function shouldShowDragHandle(): bool
    {
        return $this->evaluate($this->showDragHandle);
    }

    public function shouldShowExpandToggle(): bool
    {
        return $this->evaluate($this->showExpandToggle);
    }

    public function isDraggable(): bool
    {
        return $this->evaluate($this->draggable);
    }

    public function getIndentPadding(): int
    {
        $record = $this->getRecord();
        $depth = $record->depth ?? 0;

        return $depth * $this->getIndentSize();
    }

    public function hasChildren(): bool
    {
        $record = $this->getRecord();

        return ($record->children_count ?? 0) > 0;
    }
}
