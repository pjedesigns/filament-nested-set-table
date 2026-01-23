@php
    $record = $getRecord();
    $state = $getState();
    $indentPadding = $getIndentPadding();
    $hasChildren = $hasChildren();
    $showDragHandle = $shouldShowDragHandle() && $isDraggable();
    $showExpandToggle = $shouldShowExpandToggle() && $hasChildren;
    $nodeId = (int) $record->getKey();
    $parentId = $record->parent_id;
    $depth = $record->depth ?? 0;

    // Access the Livewire component via the column to check expanded state
    $livewire = $column->getLivewire();
    $isExpanded = method_exists($livewire, 'isNodeExpanded') ? $livewire->isNodeExpanded($nodeId) : false;
    $resource = method_exists($livewire, 'getResource') ? $livewire::getResource() : null;

    $viewUrl = $resource
        ? $resource::getUrl('view', ['record' => $record])
        : null;

    // Get the formatted state for display
    $formattedState = $formatState($state);

    // Inline style for arrow rotation (vanilla CSS)
    $arrowStyle = $isExpanded ? '' : 'transform: rotate(-90deg);';
@endphp

@if($showDragHandle)
<div
    wire:key="tree-node-{{ $nodeId }}"
    class="fi-ta-tree-column flex items-center gap-1 w-full relative"
    style="padding-left: {{ $indentPadding }}px"
    data-node-id="{{ $nodeId }}"
    data-parent-id="{{ $parentId ?? '' }}"
    data-depth="{{ $depth }}"
    data-has-children="{{ $hasChildren ? 'true' : 'false' }}"
    data-expanded="{{ $isExpanded ? 'true' : 'false' }}"
    x-data="{
        nodeId: {{ $nodeId }},
        isExpanded: {{ $isExpanded ? 'true' : 'false' }},
        dropPosition: null,
        dropIndicator: null,
        isProcessing: false,
        init() {
            const tr = this.$el.closest('tr');
            if (tr) {
                tr.dataset.nodeId = this.nodeId;
                tr.dataset.parentId = '{{ $parentId ?? '' }}';
                tr.dataset.depth = {{ $depth }};
                tr.style.position = 'relative';
                tr.style.overflow = 'visible';

                if (!tr._treeDragEventsAttached) {
                    tr._treeDragEventsAttached = true;

                    this.dropIndicator = document.createElement('div');
                    this.dropIndicator.className = 'tree-drop-indicator';
                    this.dropIndicator.style.cssText = 'position:absolute;left:0;right:0;height:3px;background:#3b82f6;z-index:1000;pointer-events:none;display:none;border-radius:2px;box-shadow:0 0 4px rgba(59,130,246,0.5);';
                    tr.appendChild(this.dropIndicator);

                    tr.addEventListener('dragover', (e) => this.handleRowDragOver(e, tr));
                    tr.addEventListener('dragleave', (e) => this.handleRowDragLeave(e, tr));
                    tr.addEventListener('drop', (e) => this.handleRowDrop(e, tr));
                }
            }

            // Listen for tree updates to clear processing state
            window.addEventListener('tree-updated', () => {
                this.isProcessing = false;
                document.querySelectorAll('tr.tree-processing').forEach(row => {
                    row.classList.remove('tree-processing');
                    row.style.opacity = '';
                    row.style.pointerEvents = '';
                });
            });

            // Listen for processing state from other nodes
            window.addEventListener('tree-node-processing', (e) => {
                if (e.detail && e.detail.nodeId === this.nodeId) {
                    this.isProcessing = true;
                }
            });
        },
        toggleExpand() {
            $wire.toggleNode(this.nodeId);
        },
        handleRowDragOver(e, tr) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const rect = tr.getBoundingClientRect();
            const mouseY = e.clientY - rect.top;
            const rowHeight = rect.height;

            // Use pixel-based zones for more precise control
            const edgeZone = Math.min(12, rowHeight * 0.25);

            tr.style.backgroundColor = '';
            tr.style.boxShadow = '';
            if (this.dropIndicator) {
                this.dropIndicator.style.display = 'none';
            }

            if (mouseY < edgeZone) {
                this.dropPosition = 'before';
                if (this.dropIndicator) {
                    this.dropIndicator.style.display = 'block';
                    this.dropIndicator.style.top = '0px';
                    this.dropIndicator.style.bottom = 'auto';
                }
            } else if (mouseY > rowHeight - edgeZone) {
                this.dropPosition = 'after';
                if (this.dropIndicator) {
                    this.dropIndicator.style.display = 'block';
                    this.dropIndicator.style.top = 'auto';
                    this.dropIndicator.style.bottom = '0px';
                }
            } else {
                this.dropPosition = 'child';
                tr.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                tr.style.boxShadow = 'inset 0 0 0 2px #3b82f6';
            }
        },
        handleRowDragLeave(e, tr) {
            if (!tr.contains(e.relatedTarget)) {
                tr.style.backgroundColor = '';
                tr.style.boxShadow = '';
                if (this.dropIndicator) {
                    this.dropIndicator.style.display = 'none';
                }
                this.dropPosition = null;
            }
        },
        handleRowDrop(e, tr) {
            e.preventDefault();
            e.stopPropagation();

            const draggedId = parseInt(e.dataTransfer.getData('application/x-tree-node'));

            tr.style.backgroundColor = '';
            tr.style.boxShadow = '';
            if (this.dropIndicator) {
                this.dropIndicator.style.display = 'none';
            }

            if (!draggedId || draggedId === this.nodeId) {
                return;
            }

            // Find the dragged row and add processing state
            const draggedRow = document.querySelector(`tr[data-node-id='${draggedId}']`);
            if (draggedRow) {
                draggedRow.classList.add('tree-processing');
                draggedRow.style.opacity = '0.5';
                draggedRow.style.pointerEvents = 'none';
            }

            // Dispatch event to notify the dragged node's Alpine component
            window.dispatchEvent(new CustomEvent('tree-node-processing', {
                detail: { nodeId: draggedId }
            }));

            if (this.dropPosition === 'child') {
                $wire.handleNodeMoved(draggedId, this.nodeId, false, true);
            } else {
                $wire.handleNodeMoved(draggedId, this.nodeId, this.dropPosition === 'before', false);
            }

            this.dropPosition = null;
        },
        startDrag(e) {
            e.stopPropagation();
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('application/x-tree-node', String(this.nodeId));
            e.dataTransfer.setData('text/plain', String(this.nodeId));

            const tr = this.$el.closest('tr');
            if (tr) {
                const rect = tr.getBoundingClientRect();

                // Get the original table to copy column widths
                const originalTable = tr.closest('table');
                const originalCells = tr.querySelectorAll('td');

                // Create a wrapper table to preserve cell layout
                const wrapperTable = document.createElement('table');
                wrapperTable.id = 'tree-drag-clone';

                // Copy table classes for styling consistency
                if (originalTable) {
                    wrapperTable.className = originalTable.className;
                }

                const tbody = document.createElement('tbody');
                const clone = tr.cloneNode(true);

                // Remove all Alpine/Livewire attributes to prevent initialization errors
                clone.querySelectorAll('*').forEach(el => {
                    [...el.attributes].forEach(attr => {
                        if (attr.name.startsWith('x-') || attr.name.startsWith('wire:') || attr.name === 'x-data' || attr.name === 'x-bind:class') {
                            el.removeAttribute(attr.name);
                        }
                    });
                });
                // Also remove from the clone itself
                [...clone.attributes].forEach(attr => {
                    if (attr.name.startsWith('x-') || attr.name.startsWith('wire:')) {
                        clone.removeAttribute(attr.name);
                    }
                });

                // Explicitly set cell widths to match original
                const cloneCells = clone.querySelectorAll('td');
                originalCells.forEach((cell, index) => {
                    if (cloneCells[index]) {
                        const cellRect = cell.getBoundingClientRect();
                        cloneCells[index].style.width = cellRect.width + 'px';
                        cloneCells[index].style.minWidth = cellRect.width + 'px';
                        cloneCells[index].style.maxWidth = cellRect.width + 'px';
                    }
                });

                tbody.appendChild(clone);
                wrapperTable.appendChild(tbody);

                wrapperTable.style.cssText = `
                    position: fixed;
                    top: ${rect.top}px;
                    left: ${rect.left}px;
                    width: ${rect.width}px;
                    height: ${rect.height}px;
                    background: white;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2), 0 4px 12px rgba(0,0,0,0.1);
                    border-radius: 8px;
                    z-index: 9999;
                    pointer-events: none;
                    opacity: 0.95;
                    transform: scale(1.02) rotate(1deg);
                    transition: transform 0.1s ease;
                    border-collapse: collapse;
                    table-layout: fixed;
                `;
                document.body.appendChild(wrapperTable);

                // Store initial offset for drag tracking
                window._treeDragOffset = {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };

                // Ghost the original row
                tr.style.opacity = '0.3';
                tr.style.background = 'repeating-linear-gradient(45deg, transparent, transparent 5px, rgba(0,0,0,0.03) 5px, rgba(0,0,0,0.03) 10px)';

                // Use a tiny transparent image as the native drag image
                const emptyImg = new Image();
                emptyImg.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                e.dataTransfer.setDragImage(emptyImg, 0, 0);

                // Add dragover event listener on document to move the clone
                const dragHandler = (dragEvent) => {
                    const cloneEl = document.getElementById('tree-drag-clone');
                    if (cloneEl && window._treeDragOffset && dragEvent.clientX !== 0 && dragEvent.clientY !== 0) {
                        cloneEl.style.top = (dragEvent.clientY - window._treeDragOffset.y) + 'px';
                        cloneEl.style.left = (dragEvent.clientX - window._treeDragOffset.x) + 'px';
                    }
                };
                document.addEventListener('dragover', dragHandler);
                window._treeDragHandler = dragHandler;
            }
        },
        endDrag(e) {
            // Remove the floating clone
            const clone = document.getElementById('tree-drag-clone');
            if (clone) {
                clone.remove();
            }

            // Clean up drag handler
            if (window._treeDragHandler) {
                document.removeEventListener('dragover', window._treeDragHandler);
                window._treeDragHandler = null;
            }
            window._treeDragOffset = null;

            // Restore the original row
            const tr = this.$el.closest('tr');
            if (tr) {
                tr.style.opacity = '';
                tr.style.background = '';
            }

            // Clean up all drop indicators and highlights
            document.querySelectorAll('.tree-drop-indicator').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('tr[data-node-id]').forEach(el => {
                el.style.backgroundColor = '';
                el.style.boxShadow = '';
            });
        }
    }"
>
    {{-- Drag Handle / Loading Spinner --}}
    <span
        class="tree-drag-handle flex h-6 w-6 shrink-0 cursor-grab items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400"
        title="{{ __('Drag to reorder') }}"
        draggable="true"
        x-on:dragstart="startDrag($event)"
        x-on:dragend="endDrag($event)"
    >
        {{-- Regular drag handle icons --}}
        <span class="tree-drag-icons" x-show="!isProcessing">
            <x-filament::icon
                icon="heroicon-m-ellipsis-vertical"
                class="h-4 w-4 -mr-2 pointer-events-none inline-block"
            />
            <x-filament::icon
                icon="heroicon-m-ellipsis-vertical"
                class="h-4 w-4 pointer-events-none inline-block"
            />
        </span>
        {{-- Loading spinner --}}
        <svg x-show="isProcessing" x-cloak class="animate-spin h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </span>
@else
<div
    wire:key="tree-node-{{ $nodeId }}"
    class="fi-ta-tree-column flex items-center gap-1 w-full relative"
    style="padding-left: {{ $indentPadding }}px"
    data-node-id="{{ $nodeId }}"
    data-parent-id="{{ $parentId ?? '' }}"
    data-depth="{{ $depth }}"
    data-has-children="{{ $hasChildren ? 'true' : 'false' }}"
    data-expanded="{{ $isExpanded ? 'true' : 'false' }}"
    x-data="{
        nodeId: {{ $nodeId }},
        isExpanded: {{ $isExpanded ? 'true' : 'false' }},
        toggleExpand() {
            $wire.toggleNode(this.nodeId);
        }
    }"
>
@endif

    {{-- Expand/Collapse Toggle --}}
    @if ($showExpandToggle)
        <button
            type="button"
            wire:click.stop="toggleNode({{ $nodeId }})"
            wire:key="toggle-btn-{{ $nodeId }}"
            class="tree-expand-toggle flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400"
            title="{{ $isExpanded ? __('Collapse') : __('Expand') }}"
        >
            <span style="display: inline-block; transition: transform 150ms; {{ $arrowStyle }}">
                <x-filament::icon
                    icon="heroicon-m-chevron-down"
                    class="h-4 w-4"
                />
            </span>
        </button>
    @elseif ($shouldShowExpandToggle() && !$hasChildren)
        {{-- Placeholder to maintain alignment when item has no children but siblings might --}}
        <span class="w-6 shrink-0"></span>
    @endif

    {{-- Content --}}
    <div class="tree-content min-w-0 flex-1">
        @if ($viewUrl)
            <a
                href="{{ $viewUrl }}"
                class="fi-ta-text-item text-sm text-gray-950 dark:text-white hover:underline"
                wire:navigate
            >
                {{ $formattedState }}
            </a>
        @else
            <span class="fi-ta-text-item text-sm text-gray-950 dark:text-white">
                {{ $formattedState }}
            </span>
        @endif
    </div>
</div>
