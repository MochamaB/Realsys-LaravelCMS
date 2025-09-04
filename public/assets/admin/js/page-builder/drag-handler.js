/**
 * Page Builder Unified Drag Handler
 * 
 * Handles drag and drop functionality for all draggable elements:
 * - Section templates (from modals and left sidebar)
 * - Widget items (theme and default widgets)
 * - Component items (future expansion)
 */
class PageBuilderDragHandler {
    constructor() {
        this.draggedElement = null;
        this.draggedData = null;
        this.draggedType = null; // 'section', 'widget', 'component'
        
        console.log('🎯 Page Builder Unified Drag Handler initialized');
    }

    /**
     * Initialize drag functionality for all element types
     */
    init() {
        console.log('🔄 Setting up unified drag functionality...');
        
        this.setupSectionDragEvents();
        this.setupWidgetDragEvents();
        this.setupComponentDragEvents();
        
        console.log('✅ Unified drag functionality ready');
    }

    /**
     * Setup drag event listeners for section templates
     */
    setupSectionDragEvents() {
        // Handle section template drag events
        document.addEventListener('dragstart', (e) => {
            const draggedItem = e.target.closest('[draggable="true"]');
            if (!draggedItem) return;
            
            // Check if it's a section template
            const templateKey = draggedItem.getAttribute('data-template-key');
            if (!templateKey) return;
            
            console.log('🎯 Section template drag started:', templateKey);
            
            this.draggedElement = draggedItem;
            this.draggedType = 'section';
            this.draggedData = {
                type: 'section',
                templateKey: templateKey,
                templateType: draggedItem.getAttribute('data-template-type') || 'core',
                templateName: draggedItem.getAttribute('data-template-name') || 'Section',
                gsWidth: draggedItem.getAttribute('data-gs-width') || '12',
                gsHeight: draggedItem.getAttribute('data-gs-height') || '4'
            };
            
            this.applySectionDragStyles(draggedItem);
            this.setSectionDragData(e);
        });
        
        document.addEventListener('dragend', (e) => {
            const draggedItem = e.target.closest('[draggable="true"]');
            if (!draggedItem) return;
            
            const templateKey = draggedItem.getAttribute('data-template-key');
            if (!templateKey) return;
            
            console.log('🏁 Section template drag ended:', templateKey);
            this.resetSectionDragStyles(draggedItem);
            this.clearDragState();
        });
        
        // Add section template hover effects
        this.setupSectionHoverEffects();
    }

    /**
     * Setup drag event listeners for widget items (future implementation)
     */
    setupWidgetDragEvents() {
        // Widget drag events will be implemented here
        document.addEventListener('dragstart', (e) => {
            const draggedItem = e.target.closest('[draggable="true"]');
            if (!draggedItem) return;
            
            // Check if it's a widget item
            const widgetId = draggedItem.getAttribute('data-widget-id');
            if (!widgetId) return;
            
            console.log('🧩 Widget drag started:', widgetId);
            
            this.draggedElement = draggedItem;
            this.draggedType = 'widget';
            this.draggedData = {
                type: 'widget',
                widgetId: widgetId,
                widgetName: draggedItem.getAttribute('data-widget-name') || 'Widget',
                widgetType: draggedItem.getAttribute('data-widget-type') || 'default',
                gsWidth: draggedItem.getAttribute('data-gs-width') || '6',
                gsHeight: draggedItem.getAttribute('data-gs-height') || '3'
            };
            
            this.applyWidgetDragStyles(draggedItem);
            this.setWidgetDragData(e);
        });
    }

    /**
     * Setup drag event listeners for component items (future implementation)
     */
    setupComponentDragEvents() {
        // Component drag events will be implemented here when needed
        console.log('🔮 Component drag events reserved for future implementation');
    }

    /**
     * Apply visual styles during section drag
     */
    applySectionDragStyles(element) {
        element.style.opacity = '0.5';
        element.style.transform = 'rotate(2deg)';
        element.style.cursor = 'grabbing';
        element.classList.add('dragging');
    }

    /**
     * Reset visual styles after section drag
     */
    resetSectionDragStyles(element) {
        element.style.opacity = '';
        element.style.transform = '';
        element.style.cursor = 'grab';
        element.classList.remove('dragging');
    }

    /**
     * Apply visual styles during widget drag
     */
    applyWidgetDragStyles(element) {
        element.style.opacity = '0.6';
        element.style.transform = 'rotate(1deg)';
        element.style.cursor = 'grabbing';
        element.classList.add('dragging');
    }

    /**
     * Set drag data for section templates
     */
    setSectionDragData(event) {
        event.dataTransfer.setData('text/plain', JSON.stringify(this.draggedData));
        event.dataTransfer.setData('text/html', this.createSectionDragPreviewHTML());
        event.dataTransfer.effectAllowed = 'copy';
        
        console.log('📦 Section drag data set:', this.draggedData);
    }

    /**
     * Set drag data for widget items
     */
    setWidgetDragData(event) {
        event.dataTransfer.setData('text/plain', JSON.stringify(this.draggedData));
        event.dataTransfer.setData('text/html', this.createWidgetDragPreviewHTML());
        event.dataTransfer.effectAllowed = 'copy';
        
        console.log('📦 Widget drag data set:', this.draggedData);
    }

    /**
     * Setup hover effects for section templates
     */
    setupSectionHoverEffects() {
        document.addEventListener('mouseenter', (e) => {
            const draggableItem = e.target.closest('[draggable="true"][data-template-key]');
            if (!draggableItem) return;
            
            draggableItem.style.transform = 'translateY(-2px)';
            draggableItem.style.boxShadow = '0 4px 12px rgba(0, 123, 255, 0.15)';
        }, true);
        
        document.addEventListener('mouseleave', (e) => {
            const draggableItem = e.target.closest('[draggable="true"][data-template-key]');
            if (!draggableItem) return;
            
            // Don't reset if currently being dragged
            if (this.draggedElement !== draggableItem) {
                draggableItem.style.transform = '';
                draggableItem.style.boxShadow = '';
            }
        }, true);
    }
    
    /**
     * Create HTML preview for section drag operation
     */
    createSectionDragPreviewHTML() {
        if (!this.draggedData || this.draggedData.type !== 'section') return '';
        
        return `
            <div class="drag-preview-section">
                <div class="card border-primary">
                    <div class="card-body p-2 text-center">
                        <i class="ri-layout-grid-line text-primary mb-1"></i>
                        <div class="small fw-bold">${this.draggedData.templateName}</div>
                        <div class="badge bg-primary-subtle text-primary">${this.draggedData.templateType}</div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Create HTML preview for widget drag operation
     */
    createWidgetDragPreviewHTML() {
        if (!this.draggedData || this.draggedData.type !== 'widget') return '';
        
        return `
            <div class="drag-preview-widget">
                <div class="card border-success">
                    <div class="card-body p-2 text-center">
                        <i class="ri-puzzle-line text-success mb-1"></i>
                        <div class="small fw-bold">${this.draggedData.widgetName}</div>
                        <div class="badge bg-success-subtle text-success">${this.draggedData.widgetType}</div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Clear drag state
     */
    clearDragState() {
        this.draggedElement = null;
        this.draggedData = null;
        this.draggedType = null;
    }
    
    /**
     * Get current drag data (for external access)
     */
    getDragData() {
        return this.draggedData;
    }
    
    /**
     * Get current drag type
     */
    getDragType() {
        return this.draggedType;
    }
    
    /**
     * Check if currently dragging any element
     */
    isDragging() {
        return this.draggedElement !== null;
    }

    /**
     * Check if currently dragging a section
     */
    isDraggingSection() {
        return this.draggedType === 'section';
    }

    /**
     * Check if currently dragging a widget
     */
    isDraggingWidget() {
        return this.draggedType === 'widget';
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize unified drag handler for all draggable elements
    const sectionElements = document.querySelectorAll('[draggable="true"][data-template-key]');
    const widgetElements = document.querySelectorAll('[draggable="true"][data-widget-id]');
    
    const totalElements = sectionElements.length + widgetElements.length;
    
    if (totalElements > 0) {
        window.pageBuilderDragHandler = new PageBuilderDragHandler();
        window.pageBuilderDragHandler.init();
        console.log('✅ Page Builder Unified Drag Handler initialized with', totalElements, 'draggable elements');
        console.log('  - Section templates:', sectionElements.length);
        console.log('  - Widget items:', widgetElements.length);
    } else {
        console.log('🚫 Page Builder Drag Handler not initialized - no draggable elements found');
    }
});

console.log('📦 Page Builder Unified Drag Handler module loaded');