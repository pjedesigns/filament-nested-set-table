// Bundled Sortable.js with Filament Tree Table integration
// Built from resources/js/filament-nested-set-table.js

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
                delay: this.touchDelay,
                delayOnTouchOnly: true,
                touchStartThreshold: 3,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                invertSwap: true,

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
            row.dataset.originalIndex = evt.oldIndex;
        },

        onDragMove(evt, originalEvent) {
            const related = evt.related;
            this.clearDropZones();

            if (!related) return true;

            const y = originalEvent.clientY || originalEvent.touches?.[0]?.clientY;
            const rect = related.getBoundingClientRect();
            const relativeY = (y - rect.top) / rect.height;

            if (relativeY < 0.25) {
                related.classList.add('tree-drop-above');
            } else if (relativeY > 0.75) {
                related.classList.add('tree-drop-below');
            } else {
                related.classList.add('tree-drop-child');
            }

            return true;
        },

        onDragEnd(evt) {
            const row = evt.item;
            row.classList.remove('tree-dragging');
            this.clearDropZones();

            if (evt.oldIndex === evt.newIndex && evt.from === evt.to) {
                return;
            }

            const nodeId = parseInt(row.dataset.nodeId);
            const treeColumn = row.querySelector('.fi-ta-tree-column');

            if (!treeColumn) {
                console.error('Tree column not found in row');
                return;
            }

            let newParentId = null;
            let newPosition = evt.newIndex;

            const targetRow = this.getTargetRow(evt);
            const dropZone = this.detectDropZone(evt);

            if (dropZone === 'child' && targetRow) {
                newParentId = parseInt(targetRow.querySelector('.fi-ta-tree-column')?.dataset?.nodeId);
                newPosition = 0;
            } else if (targetRow) {
                const targetTreeColumn = targetRow.querySelector('.fi-ta-tree-column');
                newParentId = targetTreeColumn?.dataset?.parentId
                    ? parseInt(targetTreeColumn.dataset.parentId)
                    : null;
            }

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
