/**
 * SortableManager - Drag and drop functionality for component reordering
 * 
 * Features:
 * - SortableJS integration for drag and drop
 * - Component-specific sortable containers
 * - Visual feedback during sorting
 * - Reorder event handling and communication
 */
class SortableManager {
    constructor(selectionManager) {
        this.selectionManager = selectionManager;
        this.sortableInstances = new Map();
        this.isEnabled = false;
        this.currentComponentType = null;
        
        // Sortable configuration for different component types
        this.sortableConfigs = {
            section: {
                containerSelector: '[data-preview-page]',
                itemSelector: '[data-section-id]',
                handle: '.sortable-handle-section',
                ghostClass: 'sortable-ghost-section',
                chosenClass: 'sortable-chosen-section',
                dragClass: 'sortable-drag-section'
            },
            widget: {
                containerSelector: '[data-section-id]',
                itemSelector: '[data-page-section-widget-id]',
                handle: '.sortable-handle-widget',
                ghostClass: 'sortable-ghost-widget',
                chosenClass: 'sortable-chosen-widget',
                dragClass: 'sortable-drag-widget'
            }
        };
        
        console.log('📋 Sortable manager initialized');
    }
    
    /**
     * Enable sortable functionality for component type
     * @param {string} componentType - Type of components to make sortable
     */
    enable(componentType = 'section') {
        if (!this.sortableConfigs[componentType]) {
            console.warn(`⚠️ No sortable configuration for component type: ${componentType}`);
            return;
        }
        
        // Disable any existing sortables
        this.disable();
        
        this.currentComponentType = componentType;
        this.isEnabled = true;
        
        // Create sortable instances
        this.createSortableInstances(componentType);
        
        // Add visual indicators
        this.addSortableIndicators(componentType);
        
        // Update body class
        document.body.classList.add('sortable-enabled');
        document.body.classList.add(`sortable-enabled--${componentType}`);
        
        console.log(`📋 Sortable enabled for ${componentType}s`);
    }
    
    /**
     * Disable sortable functionality
     */
    disable() {
        if (!this.isEnabled) {
            return;
        }
        
        // Destroy all sortable instances
        this.destroySortableInstances();
        
        // Remove visual indicators
        this.removeSortableIndicators();
        
        // Update body class
        document.body.classList.remove('sortable-enabled');
        document.body.classList.remove(`sortable-enabled--${this.currentComponentType}`);
        
        this.isEnabled = false;
        this.currentComponentType = null;
        
        console.log('📋 Sortable disabled');
    }
    
    /**
     * Create sortable instances for component type
     * @param {string} componentType - Component type to create sortables for
     */
    createSortableInstances(componentType) {
        const config = this.sortableConfigs[componentType];
        const containers = document.querySelectorAll(config.containerSelector);
        
        containers.forEach(container => {
            // Skip if container has no sortable items
            const items = container.querySelectorAll(config.itemSelector);
            if (items.length === 0) {
                return;
            }
            
            // Create sortable instance
            const sortable = Sortable.create(container, {
                group: `${componentType}-group`,
                animation: 200,
                ghostClass: config.ghostClass,
                chosenClass: config.chosenClass,
                dragClass: config.dragClass,
                handle: config.handle,
                filter: '.no-sort',
                preventOnFilter: false,
                
                // Event handlers
                onStart: (evt) => this.handleSortStart(evt, componentType),
                onEnd: (evt) => this.handleSortEnd(evt, componentType),
                onMove: (evt) => this.handleSortMove(evt, componentType),
                onChange: (evt) => this.handleSortChange(evt, componentType)
            });
            
            // Store instance for cleanup
            this.sortableInstances.set(container, sortable);
            
            console.log(`📋 Created sortable instance for ${componentType} container`);
        });
    }
    
    /**
     * Destroy all sortable instances
     */
    destroySortableInstances() {
        this.sortableInstances.forEach((sortable, container) => {
            sortable.destroy();
        });
        
        this.sortableInstances.clear();
        console.log('📋 Destroyed all sortable instances');
    }
    
    /**
     * Add visual indicators for sortable items
     * @param {string} componentType - Component type
     */
    addSortableIndicators(componentType) {
        const config = this.sortableConfigs[componentType];
        const items = document.querySelectorAll(config.itemSelector);
        
        items.forEach(item => {
            // Add sortable class
            item.classList.add('sortable-item');
            item.classList.add(`sortable-item--${componentType}`);
            
            // Create drag handle if it doesn't exist
            if (!item.querySelector(config.handle)) {
                const handle = this.createDragHandle(componentType);
                item.appendChild(handle);
            }
            
            // Add hover effects
            item.addEventListener('mouseenter', () => {
                if (this.isEnabled) {
                    item.classList.add('sortable-hover');
                }
            });
            
            item.addEventListener('mouseleave', () => {
                item.classList.remove('sortable-hover');
            });
        });
    }
    
    /**
     * Remove visual indicators from sortable items
     */
    removeSortableIndicators() {
        // Remove sortable classes
        document.querySelectorAll('.sortable-item').forEach(item => {
            item.classList.remove('sortable-item');
            item.classList.remove('sortable-item--section');
            item.classList.remove('sortable-item--widget');
            item.classList.remove('sortable-hover');
            item.classList.remove('sortable-dragging');
        });
        
        // Remove drag handles
        document.querySelectorAll('.sortable-handle').forEach(handle => {
            handle.remove();
        });
    }
    
    /**
     * Create drag handle element
     * @param {string} componentType - Component type
     * @returns {Element} Drag handle element
     */
    createDragHandle(componentType) {
        const handle = document.createElement('div');
        handle.className = `sortable-handle sortable-handle-${componentType}`;
        handle.innerHTML = `
            <div class="sortable-handle-icon">
                <span class="handle-dot"></span>
                <span class="handle-dot"></span>
                <span class="handle-dot"></span>
                <span class="handle-dot"></span>
                <span class="handle-dot"></span>
                <span class="handle-dot"></span>
            </div>
            <div class="sortable-handle-label">Drag to reorder</div>
        `;
        
        return handle;
    }
    
    /**
     * Handle sort start event
     * @param {Event} evt - Sortable event
     * @param {string} componentType - Component type
     */
    handleSortStart(evt, componentType) {
        const item = evt.item;
        const component = this.selectionManager.detector.identifyComponent(item);
        
        if (component) {
            // Add dragging class
            item.classList.add('sortable-dragging');
            
            // Store original position
            item.setAttribute('data-original-index', evt.oldIndex);
            
            console.log(`📋 Started dragging ${componentType} ${component.id}`);
            
            // Notify communicator
            this.selectionManager.communicator.sendMessage('sortable:start', {
                componentType: componentType,
                component: {
                    type: component.type,
                    id: component.id,
                    name: component.name
                },
                originalIndex: evt.oldIndex
            });
        }
    }
    
    /**
     * Handle sort end event
     * @param {Event} evt - Sortable event
     * @param {string} componentType - Component type
     */
    handleSortEnd(evt, componentType) {
        const item = evt.item;
        const component = this.selectionManager.detector.identifyComponent(item);
        
        if (component) {
            // Remove dragging class
            item.classList.remove('sortable-dragging');
            
            const originalIndex = parseInt(item.getAttribute('data-original-index'));
            const newIndex = evt.newIndex;
            
            // Remove temporary attribute
            item.removeAttribute('data-original-index');
            
            // Only process if position actually changed
            if (originalIndex !== newIndex) {
                this.handleComponentReorder(component, originalIndex, newIndex, evt);
            }
            
            console.log(`📋 Finished dragging ${componentType} ${component.id}`);
            
            // Notify communicator
            this.selectionManager.communicator.sendMessage('sortable:end', {
                componentType: componentType,
                component: {
                    type: component.type,
                    id: component.id,
                    name: component.name
                },
                originalIndex: originalIndex,
                newIndex: newIndex,
                moved: originalIndex !== newIndex
            });
        }
    }
    
    /**
     * Handle sort move event (validation)
     * @param {Event} evt - Sortable event
     * @param {string} componentType - Component type
     * @returns {boolean} Whether move is allowed
     */
    handleSortMove(evt, componentType) {
        // Add any validation logic here
        // Return false to prevent the move
        
        const draggedElement = evt.dragged;
        const relatedElement = evt.related;
        
        // Ensure we're only moving within the same component type
        const draggedComponent = this.selectionManager.detector.identifyComponent(draggedElement);
        const relatedComponent = this.selectionManager.detector.identifyComponent(relatedElement);
        
        if (draggedComponent && relatedComponent) {
            return draggedComponent.type === relatedComponent.type;
        }
        
        return true;
    }
    
    /**
     * Handle sort change event (during drag)
     * @param {Event} evt - Sortable event
     * @param {string} componentType - Component type
     */
    handleSortChange(evt, componentType) {
        // This fires during the drag when the list changes
        // Can be used for real-time feedback
        
        const item = evt.item;
        const component = this.selectionManager.detector.identifyComponent(item);
        
        if (component) {
            console.log(`📋 ${componentType} ${component.id} position changed during drag`);
        }
    }
    
    /**
     * Handle component reorder completion
     * @param {Object} component - Reordered component
     * @param {number} originalIndex - Original position
     * @param {number} newIndex - New position
     * @param {Event} evt - Sortable event
     */
    handleComponentReorder(component, originalIndex, newIndex, evt) {
        const reorderData = {
            component: {
                type: component.type,
                id: component.id,
                name: component.name,
                metadata: component.metadata
            },
            originalIndex: originalIndex,
            newIndex: newIndex,
            direction: newIndex > originalIndex ? 'down' : 'up',
            container: {
                element: evt.to,
                id: this.getContainerId(evt.to, component.type)
            }
        };
        
        console.log(`📋 Component reordered:`, reorderData);
        
        // Update component positions in DOM attributes
        this.updateComponentPositions(evt.to, component.type);
        
        // Notify communicator
        this.selectionManager.communicator.notifyComponentReorder(reorderData);
        
        // Here you would typically make an API call to persist the new order
        this.persistReorder(reorderData);
    }
    
    /**
     * Update position attributes for components in container
     * @param {Element} container - Container element
     * @param {string} componentType - Component type
     */
    updateComponentPositions(container, componentType) {
        const config = this.sortableConfigs[componentType];
        const items = container.querySelectorAll(config.itemSelector);
        
        items.forEach((item, index) => {
            const positionAttr = componentType === 'section' ? 'data-section-position' : 'data-widget-position';
            item.setAttribute(positionAttr, index.toString());
        });
        
        console.log(`📋 Updated positions for ${items.length} ${componentType}s`);
    }
    
    /**
     * Get container ID for reorder data
     * @param {Element} container - Container element
     * @param {string} componentType - Component type
     * @returns {string} Container ID
     */
    getContainerId(container, componentType) {
        if (componentType === 'section') {
            return container.getAttribute('data-preview-page') || '';
        } else if (componentType === 'widget') {
            return container.getAttribute('data-section-id') || '';
        }
        
        return '';
    }
    
    /**
     * Persist reorder to backend (placeholder)
     * @param {Object} reorderData - Reorder data
     */
    async persistReorder(reorderData) {
        try {
            // This would make an actual API call to save the new order
            console.log('💾 Persisting reorder:', reorderData);
            
            // Example API call structure:
            /*
            const response = await fetch('/admin/api/reorder-component', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(reorderData)
            });
            
            if (!response.ok) {
                throw new Error('Failed to persist reorder');
            }
            */
            
        } catch (error) {
            console.error('❌ Failed to persist reorder:', error);
            
            // Could revert the DOM changes here if needed
            // this.revertReorder(reorderData);
        }
    }
    
    /**
     * Move component programmatically
     * @param {Object} component - Component to move
     * @param {string} direction - 'up' or 'down'
     */
    moveComponent(component, direction) {
        if (!this.isEnabled) {
            console.warn('⚠️ Cannot move component: sortable not enabled');
            return;
        }
        
        const element = component.element;
        const container = element.parentElement;
        const siblings = Array.from(container.children).filter(child => 
            child.matches(this.sortableConfigs[component.type].itemSelector)
        );
        
        const currentIndex = siblings.indexOf(element);
        let newIndex;
        
        if (direction === 'up' && currentIndex > 0) {
            newIndex = currentIndex - 1;
        } else if (direction === 'down' && currentIndex < siblings.length - 1) {
            newIndex = currentIndex + 1;
        } else {
            console.log(`📋 Cannot move ${component.type} ${direction}: already at boundary`);
            return;
        }
        
        // Move element in DOM
        const targetElement = siblings[newIndex];
        if (direction === 'up') {
            container.insertBefore(element, targetElement);
        } else {
            container.insertBefore(element, targetElement.nextSibling);
        }
        
        // Handle reorder
        this.handleComponentReorder(component, currentIndex, newIndex, {
            to: container,
            item: element
        });
        
        console.log(`📋 Moved ${component.type} ${component.id} ${direction}`);
    }
    
    /**
     * Handle zoom changes
     * @param {Object} zoomData - Zoom level and inverse scale
     */
    handleZoomChange(zoomData) {
        // Update drag handle scaling if needed
        const handles = document.querySelectorAll('.sortable-handle');
        handles.forEach(handle => {
            handle.style.transform = `scale(${zoomData.inverse})`;
        });
    }
    
    /**
     * Check if sortable is currently enabled
     * @returns {boolean} True if enabled
     */
    isActive() {
        return this.isEnabled;
    }
    
    /**
     * Get current sortable component type
     * @returns {string|null} Current component type or null
     */
    getCurrentComponentType() {
        return this.currentComponentType;
    }
    
    /**
     * Get sortable statistics
     * @returns {Object} Statistics about sortable instances
     */
    getStats() {
        return {
            enabled: this.isEnabled,
            componentType: this.currentComponentType,
            instances: this.sortableInstances.size,
            sortableItems: document.querySelectorAll('.sortable-item').length
        };
    }
    
    /**
     * Cleanup method for destroying the sortable manager
     */
    destroy() {
        this.disable();
        console.log('🧹 Sortable manager destroyed');
    }
}

// Export for use in other modules
window.SortableManager = SortableManager;
