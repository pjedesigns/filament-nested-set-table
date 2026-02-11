<?php

declare(strict_types=1);

use Pjedesigns\FilamentNestedSetTable\Services\TreeMover;

it('registers TreeMover as singleton', function () {
    $instance1 = app(TreeMover::class);
    $instance2 = app(TreeMover::class);

    expect($instance1)->toBeInstanceOf(TreeMover::class)
        ->and($instance1)->toBe($instance2);
});

it('publishes config file', function () {
    expect(config('filament-nested-set-table'))->toBeArray()
        ->and(config('filament-nested-set-table.indent_size'))->toBe(24)
        ->and(config('filament-nested-set-table.drag_enabled'))->toBeTrue()
        ->and(config('filament-nested-set-table.max_depth'))->toBe(0)
        ->and(config('filament-nested-set-table.remember_expanded_state'))->toBeTrue()
        ->and(config('filament-nested-set-table.default_expanded'))->toBeFalse()
        ->and(config('filament-nested-set-table.undo_duration'))->toBe(10)
        ->and(config('filament-nested-set-table.broadcast_enabled'))->toBeFalse()
        ->and(config('filament-nested-set-table.touch_delay'))->toBe(150);
});

it('loads translations', function () {
    expect(__('filament-nested-set-table::messages.move_success'))
        ->toBe('Item moved successfully.');

    expect(__('filament-nested-set-table::messages.circular_reference'))
        ->toBe('Cannot move an item under its own descendant.');

    expect(__('filament-nested-set-table::actions.delete_confirm'))
        ->toBe('Are you sure you want to delete this item?');
});

it('loads views namespace', function () {
    $viewFinder = app('view');

    // The view namespace should be registered
    expect($viewFinder->exists('filament-nested-set-table::pages.order-page'))->toBeTrue();
    expect($viewFinder->exists('filament-nested-set-table::tables.columns.tree-column'))->toBeTrue();
});
