# Changelog

All notable changes to `filament-nested-set-table` will be documented in this file.

## v1.0.1 - 2026-01-20

### Enhanced
- Floating row effect during drag-and-drop - the entire row now visually lifts and follows the cursor
- Original row shows ghosted placeholder with striped background while dragging
- Clone has elevated shadow and slight rotation for a polished "lifted" appearance

### Fixed
- Removed Alpine/Livewire attributes from drag clone to prevent `isRecordSelected` errors

## v1.0.0 - 2026-01-20

### Features
- Initial release with full Filament 4 support
- TreeColumn component for displaying nested set data with indentation
- Drag-and-drop reordering with visual drop indicators
- Expand/collapse functionality with session persistence
- Lazy loading of child nodes for better performance
- Smart pagination (counts root nodes only)
- Eager loading support via `getTreeWith()` method
- Expand All / Collapse All actions
- Authorization support via model policies
- Scoped tree support for multi-tenant nested sets
- Undo support for accidental moves
- Touch-friendly drag-and-drop with configurable delay

### Configuration Options
- `indent_size` - Pixels per depth level
- `drag_enabled` - Enable/disable drag-and-drop
- `max_depth` - Maximum allowed tree depth
- `remember_expanded_state` - Persist expand/collapse in session
- `default_expanded` - Default state on first visit
- `undo_duration` - Seconds to show undo button
- `broadcast_enabled` - Real-time updates (requires Laravel Echo)
- `touch_delay` - Delay before drag starts on touch devices
