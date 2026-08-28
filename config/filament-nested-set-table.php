<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Indent Size
    |--------------------------------------------------------------------------
    |
    | The default indentation size (in pixels) for nested items in the table.
    |
    */
    'indent_size' => 24,

    /*
    |--------------------------------------------------------------------------
    | Drag and Drop
    |--------------------------------------------------------------------------
    |
    | Whether drag-and-drop reordering is enabled by default.
    |
    */
    'drag_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Max Depth
    |--------------------------------------------------------------------------
    |
    | The maximum depth allowed for nested items. Depth is zero-indexed:
    | root nodes are at depth 0, their children at depth 1, and so on.
    |
    | - null = unlimited nesting
    | -    0 = root only (nesting disabled)
    | -    N = allow up to N levels below root
    |
    */
    'max_depth' => null,

    /*
    |--------------------------------------------------------------------------
    | Remember Expanded State
    |--------------------------------------------------------------------------
    |
    | Whether to remember the expanded/collapsed state of nodes in the session.
    |
    */
    'remember_expanded_state' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Expanded
    |--------------------------------------------------------------------------
    |
    | Whether nodes are expanded by default when the table is first loaded.
    | With lazy loading, setting to false improves initial page load performance.
    |
    */
    'default_expanded' => false,

    /*
    |--------------------------------------------------------------------------
    | Undo Duration (seconds)
    |--------------------------------------------------------------------------
    |
    | How long the "Undo" button is available after a move operation.
    |
    */
    'undo_duration' => 10,

    /*
    |--------------------------------------------------------------------------
    | Broadcasting (for real-time collaboration)
    |--------------------------------------------------------------------------
    |
    | Enable to broadcast tree updates via Laravel Echo/Reverb for real-time
    | collaboration. Requires Echo/Reverb to be configured.
    |
    */
    'broadcast_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Fix Tree Quietly
    |--------------------------------------------------------------------------
    |
    | When enabled, the OrderPage runs fixTree() inside withoutEvents(), so
    | model events (and observers, e.g. cache invalidation or activity logs)
    | are not fired for every node touched during a tree repair. Disable to
    | fire model events as normal.
    |
    */
    'fix_tree_quietly' => true,

    /*
    |--------------------------------------------------------------------------
    | Touch Delay (ms)
    |--------------------------------------------------------------------------
    |
    | Delay before drag starts on touch devices. Prevents accidental drags.
    |
    */
    'touch_delay' => 150,
];
