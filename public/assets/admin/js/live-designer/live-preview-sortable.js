/**
 * Initializes and manages SortableJS for the live preview iframe.
 * This script runs on DOMContentLoaded to make sections and widgets sortable immediately.
 */
class LivePreviewSortable {
    constructor() {
        if (!window.iframeCommunicator) {
            console.error('IframeCommunicator is not available for SortableJS.');
            return;
        }
        this.communicator = window.iframeCommunicator;
        this.initializeSortable();
    }

    initializeSortable() {
        const sectionContainer = document.getElementById('pageList-group');
        if (!sectionContainer) {
            console.warn('Sortable section container #pageList-group not found.');
            return;
        }

        // Initialize sortable for sections
        new Sortable(sectionContainer, {
            group: 'sections',
            animation: 150,
            handle: '[data-sortable-section]', // Allow dragging the whole section
            onEnd: (evt) => this.onDragEnd(evt, 'section')
        });

        // Initialize sortable for widgets within each section
        const sections = document.querySelectorAll('[data-sortable-section]');
        sections.forEach(section => {
            new Sortable(section, {
                group: 'widgets',
                animation: 150,
                handle: '[data-sortable-widget]', // Allow dragging the whole widget
                onEnd: (evt) => this.onDragEnd(evt, 'widget')
            });
        });

        console.log('✅ Live Preview SortableJS initialized.');
    }

    onDragEnd(evt, type) {
        const item = evt.item;
        const payload = {
            type: type,
            elementId: item.dataset.sortableSection || item.dataset.sortableWidget,
            newIndex: evt.newIndex,
            oldIndex: evt.oldIndex,
            newParentId: evt.to.dataset.sortableSection || null,
            oldParentId: evt.from.dataset.sortableSection || null
        };

        // Notify the parent window (SortableManager) about the change
        this.communicator.send('sortable:dragEnd', payload);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.livePreviewSortable = new LivePreviewSortable();
});