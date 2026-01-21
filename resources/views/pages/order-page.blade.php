@php
    $nodes = $this->nodes;
    $indentSize = $this->getIndentSize();
    $dragEnabled = $this->isDragEnabled();
    $indexUrl = $this->getBackUrl();
    $resourceLabel = static::getResource()::getPluralModelLabel();
@endphp

<x-filament-panels::page>
    <div
        x-data="orderTree({
            nodes: @js($nodes),
            indentSize: {{ $indentSize }},
            dragEnabled: {{ $dragEnabled ? 'true' : 'false' }},
        })"
        x-on:tree-updated.window="fetchNodes()"
        wire:loading.class="opacity-50 pointer-events-none"
        class="transition-opacity duration-200"
    >
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament-nested-set-table::messages.tree_structure') }}
            </x-slot>

            <x-slot name="description">
                @if($this->getMaxDepth() === 1)
                    {{ __('filament-nested-set-table::messages.tree_description_flat') }}
                @else
                    {{ __('filament-nested-set-table::messages.tree_description') }}
                @endif
            </x-slot>

            <x-slot name="afterHeader">
                <div class="flex items-center gap-2">
                    <x-filament::button
                        x-on:click="expandAll()"
                        icon="heroicon-o-chevron-double-down"
                        color="gray"
                        size="sm"
                    >
                        {{ __('filament-nested-set-table::messages.expand_all') }}
                    </x-filament::button>

                    <x-filament::button
                        x-on:click="collapseAll()"
                        icon="heroicon-o-chevron-double-up"
                        color="gray"
                        size="sm"
                    >
                        {{ __('filament-nested-set-table::messages.collapse_all') }}
                    </x-filament::button>

                    <x-filament::button
                        wire:click="fixTree"
                        icon="heroicon-o-wrench-screwdriver"
                        color="warning"
                        size="sm"
                    >
                        {{ __('filament-nested-set-table::messages.fix_tree') }}
                    </x-filament::button>

                    <x-filament::button
                        :href="$indexUrl"
                        tag="a"
                        icon="heroicon-o-arrow-left"
                        color="gray"
                        size="sm"
                    >
                        {{ __('filament-nested-set-table::messages.back_to_list', ['resource' => $resourceLabel]) }}
                    </x-filament::button>
                </div>
            </x-slot>

            {{-- Tree Container - matches Filament table styling --}}
            <div class="fi-ta-content rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-white/10 -mx-6 -mb-6 mt-2">
                {{-- Empty State --}}
                <template x-if="nodes.length === 0">
                    <div class="px-6 py-12 text-center">
                        <x-filament::icon
                            icon="heroicon-o-folder-open"
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
                        />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ __('filament-nested-set-table::messages.no_items') }}
                        </h3>
                    </div>
                </template>

                {{-- Tree List - Flat rendering with visibility control --}}
                <div x-show="nodes.length > 0" class="divide-y divide-gray-200 dark:divide-white/5">
                    <template x-for="node in nodes" :key="node.id">
                        <div
                            x-show="isNodeVisible(node)"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                        >
                            {{-- Node Row --}}
                            <div
                                class="order-tree-node group relative flex items-center gap-2 px-4 py-3 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-white/5"
                                :class="{
                                    'opacity-30': isDragging && draggedNode?.id === node.id,
                                    'opacity-50 pointer-events-none': isProcessing && processingNodeId === node.id,
                                }"
                                :style="{ paddingLeft: (node.depth * indentSize + 16) + 'px' }"
                                :data-node-id="node.id"
                                :data-parent-id="node.parent_id"
                                :data-depth="node.depth"
                                x-on:dragover.prevent="handleDragOver($event, node)"
                                x-on:dragleave="handleDragLeave($event, node)"
                                x-on:drop.prevent="handleDrop($event, node)"
                            >
                                {{-- Drag Handle --}}
                                @if($dragEnabled)
                                <template x-if="node.can_drag">
                                    <span
                                        class="order-tree-drag-handle flex h-6 w-6 shrink-0 cursor-grab items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-white/10 dark:hover:text-gray-400"
                                        title="{{ __('filament-nested-set-table::messages.drag_to_reorder') }}"
                                        draggable="true"
                                        x-on:dragstart="startDrag($event, node)"
                                        x-on:dragend="endDrag($event)"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-m-ellipsis-vertical"
                                            class="h-4 w-4 -mr-2"
                                        />
                                        <x-filament::icon
                                            icon="heroicon-m-ellipsis-vertical"
                                            class="h-4 w-4"
                                        />
                                    </span>
                                </template>
                                <template x-if="!node.can_drag">
                                    <span class="w-6 shrink-0"></span>
                                </template>
                                @endif

                                {{-- Expand/Collapse Toggle --}}
                                <template x-if="node.has_children">
                                    <button
                                        type="button"
                                        x-on:click.stop="toggleNode(node.id)"
                                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-white/10 dark:hover:text-gray-400"
                                        :title="expandedNodes.has(node.id) ? '{{ __('filament-nested-set-table::messages.collapse') }}' : '{{ __('filament-nested-set-table::messages.expand') }}'"
                                    >
                                        <span
                                            style="display: inline-block; transition: transform 150ms;"
                                            :style="expandedNodes.has(node.id) ? '' : 'transform: rotate(-90deg);'"
                                        >
                                            <x-filament::icon
                                                icon="heroicon-m-chevron-down"
                                                class="h-4 w-4"
                                            />
                                        </span>
                                    </button>
                                </template>
                                <template x-if="!node.has_children">
                                    <span class="w-6 shrink-0"></span>
                                </template>

                                {{-- Label --}}
                                <span class="flex-1 truncate text-sm text-gray-900 dark:text-white" x-text="node.label"></span>

                                {{-- Loading Spinner --}}
                                <template x-if="isProcessing && processingNodeId === node.id">
                                    <span class="inline-flex items-center text-primary-500">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </template>

                                {{-- Children Count Badge --}}
                                <template x-if="node.has_children && !(isProcessing && processingNodeId === node.id)">
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                        x-text="node.children_count"
                                    ></span>
                                </template>

                                {{-- Drop indicators are now handled via DOM manipulation in handleDragOver() --}}
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </x-filament::section>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderTree', ({ nodes, indentSize, dragEnabled }) => ({
                nodes: nodes,
                indentSize: indentSize,
                dragEnabled: dragEnabled,
                expandedNodes: new Set(),
                isDragging: false,
                draggedNode: null,
                dropTarget: null,
                dropPosition: null,
                dragClone: null,
                dragOffset: { x: 0, y: 0 },
                isProcessing: false,
                processingNodeId: null,

                init() {
                    // Start with all nodes collapsed (expandedNodes is already an empty Set)

                    // Set up global drag handlers
                    document.addEventListener('dragover', this.handleGlobalDragOver.bind(this));

                    // Create a reusable drop indicator element
                    this.dropIndicatorEl = document.createElement('div');
                    this.dropIndicatorEl.className = 'order-tree-drop-indicator';
                    this.dropIndicatorEl.style.cssText = 'position:absolute;left:0;right:0;height:4px;background:#3b82f6;z-index:1000;pointer-events:none;display:none;border-radius:9999px;box-shadow:0 0 10px 2px rgba(59,130,246,0.6);';
                },

                get rootNodes() {
                    return this.nodes.filter(n => n.parent_id === null);
                },

                getChildren(parentId) {
                    return this.nodes.filter(n => n.parent_id === parentId);
                },

                isExpanded(nodeId) {
                    return this.expandedNodes.has(nodeId);
                },

                isNodeVisible(node) {
                    // Root nodes are always visible
                    if (node.parent_id === null) {
                        return true;
                    }

                    // Check if all ancestors are expanded
                    let currentParentId = node.parent_id;
                    while (currentParentId !== null) {
                        if (!this.expandedNodes.has(currentParentId)) {
                            return false;
                        }
                        const parent = this.nodes.find(n => n.id === currentParentId);
                        currentParentId = parent ? parent.parent_id : null;
                    }
                    return true;
                },

                toggleNode(nodeId) {
                    if (this.expandedNodes.has(nodeId)) {
                        this.expandedNodes.delete(nodeId);
                    } else {
                        this.expandedNodes.add(nodeId);
                    }
                },

                expandAll() {
                    this.nodes.forEach(node => {
                        if (node.has_children) {
                            this.expandedNodes.add(node.id);
                        }
                    });
                },

                collapseAll() {
                    this.expandedNodes.clear();
                },

                startDrag(event, node) {
                    if (!this.dragEnabled || !node.can_drag) return;

                    event.stopPropagation();
                    this.isDragging = true;
                    this.draggedNode = node;

                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('application/x-order-tree-node', String(node.id));
                    event.dataTransfer.setData('text/plain', String(node.id));

                    // Create floating clone
                    const target = event.target.closest('.order-tree-node');
                    if (target) {
                        const rect = target.getBoundingClientRect();
                        this.dragOffset = {
                            x: event.clientX - rect.left,
                            y: event.clientY - rect.top
                        };

                        // Create clone
                        this.dragClone = target.cloneNode(true);
                        this.dragClone.id = 'order-tree-drag-clone';

                        // Remove Alpine/Livewire attributes
                        this.dragClone.querySelectorAll('*').forEach(el => {
                            [...el.attributes].forEach(attr => {
                                if (attr.name.startsWith('x-') || attr.name.startsWith('wire:') || attr.name.startsWith('@') || attr.name.startsWith(':')) {
                                    el.removeAttribute(attr.name);
                                }
                            });
                        });
                        [...this.dragClone.attributes].forEach(attr => {
                            if (attr.name.startsWith('x-') || attr.name.startsWith('wire:') || attr.name.startsWith('@') || attr.name.startsWith(':')) {
                                this.dragClone.removeAttribute(attr.name);
                            }
                        });

                        this.dragClone.style.cssText = `
                            position: fixed;
                            top: ${rect.top}px;
                            left: ${rect.left}px;
                            width: ${rect.width}px;
                            background: white;
                            box-shadow: 0 10px 40px rgba(0,0,0,0.15), 0 4px 12px rgba(0,0,0,0.1), 0 0 0 1px rgba(59,130,246,0.3);
                            border-radius: 8px;
                            z-index: 9999;
                            pointer-events: none;
                            opacity: 0.95;
                            transform: scale(1.02) rotate(1deg);
                        `;
                        document.body.appendChild(this.dragClone);

                        // Use transparent drag image
                        const emptyImg = new Image();
                        emptyImg.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                        event.dataTransfer.setDragImage(emptyImg, 0, 0);
                    }
                },

                handleGlobalDragOver(event) {
                    if (this.dragClone && this.isDragging) {
                        this.dragClone.style.top = (event.clientY - this.dragOffset.y) + 'px';
                        this.dragClone.style.left = (event.clientX - this.dragOffset.x) + 'px';
                    }
                },

                endDrag(event) {
                    this.isDragging = false;
                    this.dropTarget = null;
                    this.dropPosition = null;

                    // Clear any drop indicators
                    this.clearDropIndicators();

                    // Remove clone
                    if (this.dragClone) {
                        this.dragClone.remove();
                        this.dragClone = null;
                    }

                    this.draggedNode = null;
                },

                handleDragOver(event, node) {
                    if (!this.isDragging || !this.draggedNode) return;
                    if (this.draggedNode.id === node.id) return;

                    // Can't drop on descendants
                    if (this.isDescendant(node.id, this.draggedNode.id)) return;

                    event.dataTransfer.dropEffect = 'move';

                    const target = event.target.closest('.order-tree-node');
                    if (!target) return;

                    const rect = target.getBoundingClientRect();
                    const mouseY = event.clientY - rect.top;
                    const height = rect.height;

                    // Determine drop zone
                    const edgeZone = Math.min(12, height * 0.25);

                    this.dropTarget = node;

                    // Clear previous styling
                    this.clearDropIndicators();

                    // Ensure target has relative positioning
                    target.style.position = 'relative';

                    if (mouseY < edgeZone) {
                        this.dropPosition = 'before';
                        // Show line indicator at top
                        this.dropIndicatorEl.style.display = 'block';
                        this.dropIndicatorEl.style.top = '-2px';
                        this.dropIndicatorEl.style.bottom = 'auto';
                        target.appendChild(this.dropIndicatorEl);
                    } else if (mouseY > height - edgeZone) {
                        this.dropPosition = 'after';
                        // Show line indicator at bottom
                        this.dropIndicatorEl.style.display = 'block';
                        this.dropIndicatorEl.style.top = 'auto';
                        this.dropIndicatorEl.style.bottom = '-2px';
                        target.appendChild(this.dropIndicatorEl);
                    } else if (node.can_have_children !== false) {
                        this.dropPosition = 'child';
                        // Highlight the entire row for child drop
                        target.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                        target.style.boxShadow = 'inset 0 0 0 2px #3b82f6';
                    } else {
                        this.dropPosition = 'after';
                        this.dropIndicatorEl.style.display = 'block';
                        this.dropIndicatorEl.style.top = 'auto';
                        this.dropIndicatorEl.style.bottom = '-2px';
                        target.appendChild(this.dropIndicatorEl);
                    }
                },

                clearDropIndicators() {
                    // Hide the line indicator
                    this.dropIndicatorEl.style.display = 'none';
                    // Clear all row highlights
                    document.querySelectorAll('.order-tree-node').forEach(el => {
                        el.style.backgroundColor = '';
                        el.style.boxShadow = '';
                    });
                },

                handleDragLeave(event, node) {
                    const target = event.target.closest('.order-tree-node');
                    const relatedTarget = event.relatedTarget?.closest?.('.order-tree-node');

                    if (target && target !== relatedTarget) {
                        if (this.dropTarget?.id === node.id) {
                            this.clearDropIndicators();
                            this.dropTarget = null;
                            this.dropPosition = null;
                        }
                    }
                },

                handleDrop(event, node) {
                    if (!this.isDragging || !this.draggedNode) return;
                    if (this.draggedNode.id === node.id) return;
                    if (this.isDescendant(node.id, this.draggedNode.id)) return;

                    // Clear indicators immediately
                    this.clearDropIndicators();

                    const insertBefore = this.dropPosition === 'before';
                    const makeChild = this.dropPosition === 'child';

                    // Set processing state
                    this.isProcessing = true;
                    this.processingNodeId = this.draggedNode.id;

                    // Call Livewire method
                    this.$wire.moveNode(
                        this.draggedNode.id,
                        node.id,
                        insertBefore,
                        makeChild
                    ).then(() => {
                        // Processing complete - will be reset on tree-updated
                    }).catch(() => {
                        this.isProcessing = false;
                        this.processingNodeId = null;
                    });

                    this.dropTarget = null;
                    this.dropPosition = null;
                },

                isDescendant(nodeId, potentialAncestorId) {
                    let current = this.nodes.find(n => n.id === nodeId);
                    while (current && current.parent_id !== null) {
                        if (current.parent_id === potentialAncestorId) {
                            return true;
                        }
                        current = this.nodes.find(n => n.id === current.parent_id);
                    }
                    return false;
                },

                async fetchNodes() {
                    this.isProcessing = false;
                    this.processingNodeId = null;
                    // Fetch fresh nodes from Livewire computed property
                    const newNodes = await this.$wire.getNodesForAlpine();
                    this.nodes = newNodes;
                    // Keep current expanded state - don't auto-expand
                }
            }));
        });
    </script>
    @endpush
</x-filament-panels::page>
