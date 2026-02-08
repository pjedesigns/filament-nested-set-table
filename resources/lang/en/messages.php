<?php

return [
    // Move operations
    'move_success' => 'Item moved successfully.',
    'move_adjusted' => 'Item moved (position auto-adjusted due to max depth).',
    'move_failed' => 'Failed to move item.',
    'undo_success' => 'Move undone successfully.',
    'item_moved' => 'Item moved',

    // Authorization & Validation
    'unauthorized' => 'You are not authorized to move this item.',
    'cross_scope' => 'Cannot move items between different scopes.',
    'node_not_found' => 'The item could not be found.',
    'parent_not_found' => 'The parent item could not be found.',
    'circular_reference' => 'Cannot move an item under its own descendant.',
    'max_depth_exceeded' => 'Cannot move here: would exceed maximum depth of :max levels.',
    'cannot_have_children' => 'This item cannot have children.',
    'reorder_failed' => 'Failed to reorder the item.',

    // Tree integrity
    'tree_fixed' => 'Tree structure has been repaired.',
    'tree_corrupted' => 'Tree structure may be corrupted. Click "Fix Tree" to repair.',

    // UI Actions
    'expand_all' => 'Expand All',
    'collapse_all' => 'Collapse All',
    'tree_view' => 'Tree View',
    'flat_view' => 'Flat View',
    'fix_tree' => 'Fix Tree',
    'fix_tree_tooltip' => 'Repair tree structure if items are out of order',
    'undo' => 'Undo',
    'back_to_list' => 'Back to :resource',

    // OrderPage specific
    'order' => 'Order',
    'no_items' => 'No items to display.',
    'drag_to_reorder' => 'Drag to reorder',
    'expand' => 'Expand',
    'collapse' => 'Collapse',
    'order_items' => 'Order Items',
    'tree_structure' => 'Order Tree',
    'tree_description' => 'Drag and drop items to reorder. Drop on an item to make it a child.',
    'tree_description_flat' => 'Drag and drop items to reorder.',

    // Alphabetical ordering
    'save_alphabetically' => 'Save Alphabetically',
    'alphabetical_confirm' => 'This will reorder all items alphabetically within each level. Are you sure?',
    'alphabetical_success' => 'Items reordered alphabetically.',
    'alphabetical_failed' => 'Failed to reorder items alphabetically.',

    // Loading states
    'processing' => 'Processing...',
    'moving' => 'Moving item...',
];
