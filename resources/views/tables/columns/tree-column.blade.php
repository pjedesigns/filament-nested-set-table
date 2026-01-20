@php
    $record = $getRecord();
    $state = $getState();
    $indentPadding = $getIndentPadding();
    $hasChildren = $hasChildren();
    $showDragHandle = $shouldShowDragHandle() && $isDraggable();
    $showExpandToggle = $shouldShowExpandToggle() && $hasChildren;
    $nodeId = $record->getKey();
    $parentId = $record->parent_id;
    $depth = $record->depth ?? 0;

    $isExpanded = $this->isNodeExpanded($nodeId);

    // Get the formatted state for display
    $formattedState = $formatState($state);
@endphp

<div
    class="fi-ta-tree-column flex items-center gap-1 w-full py-1"
    style="padding-left: {{ $indentPadding }}px"
    data-node-id="{{ $nodeId }}"
    data-parent-id="{{ $parentId }}"
    data-depth="{{ $depth }}"
    x-data="{
        nodeId: {{ $nodeId }},
        parentId: {{ $parentId ?? 'null' }},
        depth: {{ $depth }},
        isDragOver: false
    }"
    @if($showDragHandle)
    x-on:dragover.prevent="isDragOver = true; $event.dataTransfer.dropEffect = 'move'"
    x-on:dragleave="isDragOver = false"
    x-on:drop.prevent="
        isDragOver = false;
        const draggedNodeId = parseInt($event.dataTransfer.getData('text/plain'));
        if (draggedNodeId && draggedNodeId !== nodeId) {
            $wire.call('handleNodeMoved', draggedNodeId, nodeId, 0);
        }
    "
    x-bind:class="{ 'bg-primary-50 dark:bg-primary-900/50 rounded': isDragOver }"
    @endif
>
    {{-- Drag Handle --}}
    @if ($showDragHandle)
        <div
            class="tree-drag-handle flex h-6 w-6 shrink-0 cursor-grab items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400"
            title="{{ __('Drag to reorder') }}"
            draggable="true"
            x-on:dragstart="
                $event.dataTransfer.effectAllowed = 'move';
                $event.dataTransfer.setData('text/plain', nodeId.toString());
                $el.closest('tr').style.opacity = '0.5';
            "
            x-on:dragend="$el.closest('tr').style.opacity = '1'"
        >
            <x-filament::icon
                icon="heroicon-m-ellipsis-vertical"
                class="h-4 w-4 -mr-2"
            />
            <x-filament::icon
                icon="heroicon-m-ellipsis-vertical"
                class="h-4 w-4"
            />
        </div>
    @endif

    {{-- Expand/Collapse Toggle --}}
    @if ($showExpandToggle)
        <button
            type="button"
            class="tree-expand-toggle flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400"
            wire:click="toggleNode({{ $nodeId }})"
            title="{{ $isExpanded ? __('Collapse') : __('Expand') }}"
        >
            <x-filament::icon
                :icon="$isExpanded ? 'heroicon-m-chevron-down' : 'heroicon-m-chevron-right'"
                class="h-4 w-4 transition-transform duration-150"
            />
        </button>
    @elseif ($shouldShowExpandToggle())
        {{-- Placeholder to maintain alignment when item has no children but siblings might --}}
        <span class="w-6 shrink-0"></span>
    @endif

    {{-- Content --}}
    <div class="tree-content min-w-0 flex-1">
        <span class="fi-ta-text-item text-sm text-gray-950 dark:text-white">
            {{ $formattedState }}
        </span>
    </div>
</div>
