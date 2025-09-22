/**
 * SortableManager - Drag & drop functionality for live designer
 * Manages SortableJS integration for sections and widgets
 */
class SortableManager {
    constructor() {
        this.activeSortables = new Map();
        this.isDragMode = false;
        this.draggedElement = null;
        this.currentComponent = null;
        
        console.log('📋 SortableManager initialized');
    }
    
    /**
     * Receive component selection - store for sortable operations
     * @param {Object} component - Selected component object
     */
    onComponentSelected(component) {
        this.currentComponent = component;
        console.log(`🎯 SORTABLE: Component selected for potential sorting: ${component.type} ${component.id}`);
    }
    
    /**
     * Enable sortable functionality for component
     * Called automatically when component is selected
     * @param {Object} component - Component to enable sorting for
     */
    enableSortableForComponent(component) {
        console.log('🚀 SORTABLE: Auto-enabling sortable for component:', component.type, component.id);
        
        // Disable any existing sortables first
        this.disableAllSortables();
        
        // Determine what should be sortable based on component type
        if (component.type === 'page') {
            const success = this.makeSectionsSortable(component);
            if (success) {
                console.log('✨ SORTABLE: Page selected → sections are now draggable');
            }
            return success;
        } else if (component.type === 'section') {
            const success = this.makeWidgetsSortable(component);
            if (success) {
                console.log('✨ SORTABLE: Section selected → widgets are now draggable');
            }
            return success;
        } else {
            console.log('ℹ️ SORTABLE: Widget selected - no children to sort');
            return false;
        }
    }
    
    /**
     * Make sections sortable within a page
     * @param {Object} pageComponent - Page component
     */
    makeSectionsSortable(pageComponent) {
        console.log('📦 SORTABLE: Making sections sortable in page:', pageComponent.id);
        
        const container = pageComponent.element;
        const sections = container.querySelectorAll('[data-section-id]');
        
        if (sections.length === 0) {
            console.log('⚠️ SORTABLE: No sections found to sort');
            return false;
        }
        
        console.log(`📦 SORTABLE: Found ${sections.length} sections to make sortable`);
        
        // Add drag handles to sections
        this.addDragHandles(sections, 'section');
        
        // Create sortable instance
        const sortable = Sortable.create(container, {
            group: 'sections',
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onStart: (evt) => this.onDragStart(evt, 'section'),
            onEnd: (evt) => this.onDragEnd(evt, 'section', pageComponent.id)
        });
        
        this.activeSortables.set('sections', sortable);
        console.log('✅ SORTABLE: Sections are now sortable');
        return true;
    }
    
    /**
     * Make widgets sortable within a section
     * @param {Object} sectionComponent - Section component
     */
    makeWidgetsSortable(sectionComponent) {
        console.log('🧩 SORTABLE: Making widgets sortable in section:', sectionComponent.id);
        
        const container = sectionComponent.element;
        const widgets = container.querySelectorAll('[data-page-section-widget-id]');
        
        if (widgets.length === 0) {
            console.log('⚠️ SORTABLE: No widgets found to sort');
            return false;
        }
        
        console.log(`🧩 SORTABLE: Found ${widgets.length} widgets to make sortable`);
        
        // Add drag handles to widgets
        this.addDragHandles(widgets, 'widget');
        
        // Create sortable instance
        const sortable = Sortable.create(container, {
            group: 'widgets',
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onStart: (evt) => this.onDragStart(evt, 'widget'),
            onEnd: (evt) => this.onDragEnd(evt, 'widget', sectionComponent.id)
        });
        
        this.activeSortables.set(`widgets-${sectionComponent.id}`, sortable);
        console.log('✅ SORTABLE: Widgets are now sortable');
        return true;
    }
    
    /**
     * Add drag handles to elements
     * @param {NodeList} elements - Elements to add handles to
     * @param {string} type - Type of elements (section/widget)
     */
    addDragHandles(elements, type) {
        elements.forEach(element => {
            // Skip if already has handle
            if (element.querySelector('.drag-handle')) return;
            
            const handle = document.createElement('div');
            handle.className = `drag-handle drag-handle--${type}`;
            handle.innerHTML = '<i class="bx bx-move"></i>';
            handle.title = `Drag to reorder ${type}`;
            
            // Position handle at top-right of element
            handle.style.position = 'absolute';
            handle.style.top = '5px';
            handle.style.right = '5px';
            handle.style.zIndex = '1000';
            
            // Make parent position relative if not already
            if (getComputedStyle(element).position === 'static') {
                element.style.position = 'relative';
            }
            
            element.appendChild(handle);
        });
    }
    
    /**
     * Remove drag handles from elements
     * @param {NodeList} elements - Elements to remove handles from
     */
    removeDragHandles(elements) {
        elements.forEach(element => {
            const handle = element.querySelector('.drag-handle');
            if (handle) {
                handle.remove();
            }
        });
    }
    
    /**
     * Handle drag start event
     * @param {Event} evt - SortableJS event
     * @param {string} type - Type being dragged
     */
    onDragStart(evt, type) {
        this.isDragMode = true;
        this.draggedElement = evt.item;
        
        console.log(`🎯 SORTABLE: Started dragging ${type}:`, evt.item);
        
        // Hide component toolbar during drag
        if (window.componentToolbar) {
            window.componentToolbar.hide();
        }
        
        // Send message to parent
        if (window.iframeCommunicator) {
            window.iframeCommunicator.sendMessage('drag:started', {
                type: type,
                elementId: this.getElementId(evt.item, type)
            });
        }
    }
    
    /**
     * Handle drag end event
     * @param {Event} evt - SortableJS event
     * @param {string} type - Type being dragged
     * @param {string} parentId - Parent container ID
     */
    onDragEnd(evt, type, parentId) {
        this.isDragMode = false;
        
        const elementId = this.getElementId(evt.item, type);
        console.log(`✅ SORTABLE: Finished dragging ${type} ${elementId}:`, {
            oldIndex: evt.oldIndex,
            newIndex: evt.newIndex
        });
        
        // Only process if position actually changed
        if (evt.oldIndex !== evt.newIndex) {
            // Send reorder message to parent
            if (window.iframeCommunicator) {
                window.iframeCommunicator.sendMessage('component:reordered', {
                    componentType: type,
                    componentId: elementId,
                    parentId: parentId,
                    oldPosition: evt.oldIndex,
                    newPosition: evt.newIndex
                });
            }
        }
        
        this.draggedElement = null;
        
        // Send drag ended message
        if (window.iframeCommunicator) {
            window.iframeCommunicator.sendMessage('drag:ended', {
                type: type,
                elementId: elementId
            });
        }
    }
    
    /**
     * Extract component ID from element
     * @param {Element} element - DOM element
     * @param {string} type - Component type
     * @returns {string} Component ID
     */
    getElementId(element, type) {
        if (type === 'section') {
            return element.getAttribute('data-section-id');
        } else if (type === 'widget') {
            return element.getAttribute('data-page-section-widget-id');
        }
        return null;
    }
    
    /**
     * Disable all active sortables
     */
    disableAllSortables() {
        console.log('🛑 SORTABLE: Disabling all sortables');
        
        this.activeSortables.forEach((sortable, key) => {
            if (sortable) {
                sortable.destroy();
                console.log(`🛑 SORTABLE: Destroyed sortable: ${key}`);
            }
        });
        
        this.activeSortables.clear();
        
        // Remove all drag handles
        const allHandles = document.querySelectorAll('.drag-handle');
        allHandles.forEach(handle => handle.remove());
        
        this.isDragMode = false;
        this.draggedElement = null;
    }
    
    /**
     * Disable sortable functionality
     */
    disable() {
        this.disableAllSortables();
        this.currentComponent = null;
        console.log('🛑 SORTABLE: Sortable functionality disabled');
    }
    
    /**
     * Cleanup method
     */
    destroy() {
        this.disable();
        console.log('🧹 SortableManager destroyed');
    }
}

// Make available globally
window.SortableManager = new SortableManager();