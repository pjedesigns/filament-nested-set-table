import Sortable from 'sortablejs';

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

            // Re-initialize after Livewire updates
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
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
