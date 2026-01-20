<?php

namespace Pjedesigns\FilamentNestedSetTable\Concerns;

trait InteractsWithTree
{
    /**
     * Get the label to display for this tree node.
     */
    public function getTreeLabel(): string
    {
        return $this->getAttribute($this->getTreeLabelColumn());
    }

    /**
     * Get the column name used for the tree label.
     */
    public function getTreeLabelColumn(): string
    {
        return 'title';
    }

    /**
     * Get an optional icon for this tree node.
     */
    public function getTreeIcon(): ?string
    {
        return null;
    }

    /**
     * Determine if this node can have children.
     */
    public function canHaveChildren(): bool
    {
        return true;
    }

    /**
     * Determine if this node can be dragged.
     */
    public function canBeDragged(): bool
    {
        return true;
    }

    /**
     * Get the position of this node among its siblings.
     */
    public function getSiblingPosition(): int
    {
        return $this->siblings()->where('_lft', '<', $this->_lft)->count();
    }

    /**
     * Get the maximum allowed depth for this tree.
     * Return 0 for unlimited depth.
     */
    public function getMaxTreeDepth(): int
    {
        return config('filament-nested-set-table.max_depth', 0);
    }

    /**
     * Get the tree scope attributes for this model.
     * This is a wrapper that checks if the model defines getScopeAttributes via NodeTrait.
     * Override in your model to define scope attributes for scoped nested sets.
     *
     * Note: If using kalnoy/nestedset's scoped trees, override getScopeAttributes()
     * in your model to return the scope column names, e.g.: return ['navigation_id'];
     */
    public function getTreeScopeAttributes(): array
    {
        if (method_exists($this, 'getScopeAttributes')) {
            return $this->getScopeAttributes();
        }

        return [];
    }
}
