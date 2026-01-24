import Sortable from 'sortablejs';

/**
 * Tree Node Component - Used by tree-column.blade.php for draggable nodes
 * This handles the drag/drop functionality for individual tree nodes in the table
 */
export function treeNode(config = {}) {
    return {
        nodeId: config.nodeId,
        isExpanded: config.isExpanded ?? false,
        dropPosition: null,
        dropIndicator: null,
        isProcessing: false,

        init() {
            const tr = this.$el.closest('tr');
            if (tr) {
                tr.dataset.nodeId = this.nodeId;
                tr.dataset.parentId = config.parentId ?? '';
                tr.dataset.depth = config.depth ?? 0;
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
            this.$wire.toggleNode(this.nodeId);
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
            const draggedRow = document.querySelector(`tr[data-node-id="${draggedId}"]`);
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
                this.$wire.handleNodeMoved(draggedId, this.nodeId, false, true);
            } else {
                this.$wire.handleNodeMoved(draggedId, this.nodeId, this.dropPosition === 'before', false);
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
                const clone = tr.cloneNode(true);
                clone.id = 'tree-drag-clone';

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

                clone.style.cssText = `
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
                `;
                document.body.appendChild(clone);

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
    };
}

/**
 * Simple Tree Node Component - Used for non-draggable tree nodes
 */
export function treeNodeSimple(config = {}) {
    return {
        nodeId: config.nodeId,
        isExpanded: config.isExpanded ?? false,

        toggleExpand() {
            this.$wire.toggleNode(this.nodeId);
        }
    };
}

/**
 * Sortable Table Component - Original component for SortableJS integration
 */
export default function filamentNestedSetTable(config = {}) {
    return {
        initialized: false,
        sortableInstance: null,
        touchDelay: config.touchDelay ?? 150,
        dragEnabled: config.dragEnabled ?? true,

        init() {
            if (this.initialized) return;

            this.$nextTick(() => {
                this.initializeSortable();
                this.initialized = true;
            });

            // Re-initialize after Livewire updates (Livewire v4 syntax)
            Livewire.interceptMessage(({ onSuccess }) => {
                onSuccess(() => {
                    this.$nextTick(() => {
                        this.initializeSortable();
                    });
                });
            });
        },

        initializeSortable() {
            if (!this.dragEnabled) return;

            const table = this.$el.querySelector('[data-tree-table]');
            if (!table) return;

            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            // Destroy existing instance
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }

            this.sortableInstance = new Sortable(tbody, {
                group: {
                    name: 'tree-rows',
                    pull: true,
                    put: true,
                },
                animation: 150,
                handle: '.tree-drag-handle',
                ghostClass: 'tree-ghost',
                chosenClass: 'tree-chosen',
                dragClass: 'tree-drag',
                filter: '.tree-no-drag',

                // Touch support
                delay: this.touchDelay,
                delayOnTouchOnly: true,
                touchStartThreshold: 3,

                // Nested sorting with drop zones
                fallbackOnBody: true,
                swapThreshold: 0.65,
                invertSwap: true,

                // Events
                onStart: (evt) => {
                    this.onDragStart(evt);
                },

                onMove: (evt, originalEvent) => {
                    return this.onDragMove(evt, originalEvent);
                },

                onEnd: (evt) => {
                    this.onDragEnd(evt);
                },
            });
        },

        onDragStart(evt) {
            const row = evt.item;
            row.classList.add('tree-dragging');

            // Store original position data
            row.dataset.originalIndex = evt.oldIndex;
        },

        onDragMove(evt, originalEvent) {
            const dragged = evt.dragged;
            const related = evt.related;

            // Clear existing drop zone indicators
            this.clearDropZones();

            if (!related) return true;

            const y = originalEvent.clientY || originalEvent.touches?.[0]?.clientY;
            const rect = related.getBoundingClientRect();

            // Determine drop zone based on cursor position
            const relativeY = (y - rect.top) / rect.height;

            if (relativeY < 0.25) {
                // Top zone - insert as sibling before
                related.classList.add('tree-drop-above');
            } else if (relativeY > 0.75) {
                // Bottom zone - insert as sibling after
                related.classList.add('tree-drop-below');
            } else {
                // Middle zone - make child
                related.classList.add('tree-drop-child');
            }

            return true;
        },

        onDragEnd(evt) {
            const row = evt.item;
            row.classList.remove('tree-dragging');

            this.clearDropZones();

            // Skip if no actual movement
            if (evt.oldIndex === evt.newIndex && evt.from === evt.to) {
                return;
            }

            // Get node data
            const nodeId = parseInt(row.dataset.nodeId);
            const treeColumn = row.querySelector('.fi-ta-tree-column');

            if (!treeColumn) {
                console.error('Tree column not found in row');
                return;
            }

            // Determine new parent and position
            let newParentId = null;
            let newPosition = evt.newIndex;

            // Check if we're dropping as a child
            const targetRow = this.getTargetRow(evt);
            const dropZone = this.detectDropZone(evt);

            if (dropZone === 'child' && targetRow) {
                // Make this node a child of the target row
                newParentId = parseInt(targetRow.querySelector('.fi-ta-tree-column')?.dataset?.nodeId);
                newPosition = 0; // First child
            } else if (targetRow) {
                // Get parent from the target row
                const targetTreeColumn = targetRow.querySelector('.fi-ta-tree-column');
                newParentId = targetTreeColumn?.dataset?.parentId
                    ? parseInt(targetTreeColumn.dataset.parentId)
                    : null;
            }

            // Dispatch Livewire event
            Livewire.dispatch('tree-node-moved', {
                nodeId: nodeId,
                newParentId: newParentId,
                newPosition: newPosition,
            });
        },

        getTargetRow(evt) {
            const rows = Array.from(evt.to.querySelectorAll('tr[data-node-id]'));
            const newIndex = evt.newIndex;

            if (newIndex > 0 && newIndex <= rows.length) {
                return rows[newIndex - 1];
            }

            return null;
        },

        detectDropZone(evt) {
            // Check which drop zone class was applied
            const rows = evt.to.querySelectorAll('tr');

            for (const row of rows) {
                if (row.classList.contains('tree-drop-child')) {
                    return 'child';
                }
                if (row.classList.contains('tree-drop-above')) {
                    return 'above';
                }
                if (row.classList.contains('tree-drop-below')) {
                    return 'below';
                }
            }

            return 'sibling';
        },

        clearDropZones() {
            document.querySelectorAll('.tree-drop-above, .tree-drop-below, .tree-drop-child')
                .forEach(el => {
                    el.classList.remove('tree-drop-above', 'tree-drop-below', 'tree-drop-child');
                });
        },

        destroy() {
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }
            this.initialized = false;
        },
    };
}

// Make functions available globally for Alpine x-data expressions
// This is critical because Filament loads AlpineComponent assets with defer,
// which means Alpine may evaluate x-data before our components are registered
if (typeof window !== 'undefined') {
    // Expose as global functions so x-data="treeNode({...})" works
    window.treeNode = treeNode;
    window.treeNodeSimple = treeNodeSimple;
    window.filamentNestedSetTable = filamentNestedSetTable;

    // Also register as Alpine.data components for consistency
    const registerComponents = () => {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('treeNode', treeNode);
            Alpine.data('treeNodeSimple', treeNodeSimple);
            Alpine.data('filamentNestedSetTable', filamentNestedSetTable);
        }
    };

    // Try to register immediately (Alpine might already be loaded)
    if (typeof Alpine !== 'undefined') {
        registerComponents();
    }

    // Also listen for alpine:init in case Alpine loads later
    document.addEventListener('alpine:init', registerComponents);

    // Also listen for livewire:init which fires after Alpine is ready in Filament
    document.addEventListener('livewire:init', registerComponents);
}
