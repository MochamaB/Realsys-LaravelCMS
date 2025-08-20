/**
 * Grid Manager
 * 
 * Handles GridStack library integration, manages the grid layout,
 * and handles drag & drop functionality.
 */
class GridManager {
    constructor(containerSelector = '#gridStackContainer') {
        this.containerSelector = containerSelector;
        this.container = null;
        this.grid = null;
        this.gridItems = new Map(); // Track grid items by ID
        this.initialized = false;
        
        console.log('⚡ Grid Manager initialized with container:', containerSelector);
    }

    /**
     * Initialize GridStack
     */
    async initialize(options = {}) {
        try {
            console.log('🔄 Initializing GridStack...');
            
            // Wait for DOM if needed
            if (document.readyState !== 'complete') {
                console.log('⏳ Waiting for DOM to be ready...');
                await new Promise(resolve => {
                    if (document.readyState === 'complete') {
                        resolve();
                    } else {
                        window.addEventListener('load', resolve, { once: true });
                    }
                });
            }
            
            this.container = document.querySelector(this.containerSelector);
            if (!this.container) {
                console.error('❌ Available containers:', Array.from(document.querySelectorAll('[id]')).map(el => `#${el.id}`));
                throw new Error(`GridStack container not found: ${this.containerSelector}`);
            }
            
            // Default GridStack options
            const defaultOptions = {
                column: 12,
                cellHeight: 60,
                margin: 8,
                resizable: {
                    handles: 'e, se, s, sw, w'
                },
                draggable: {
                    handle: '.section-header'
                },
                acceptWidgets: true,
                removable: false,
                animate: true,
                float: false
            };
            
            const gridOptions = { ...defaultOptions, ...options };
            
            // Initialize GridStack
            this.grid = GridStack.init(gridOptions, this.container);
            
            // Set up event listeners
            this.setupGridEvents();
            
            this.initialized = true;
            console.log('✅ GridStack initialized successfully with options:', gridOptions);
            
        } catch (error) {
            console.error('❌ Error initializing GridStack:', error);
            throw error;
        }
    }

    /**
     * Set up GridStack event listeners
     */
    setupGridEvents() {
        if (!this.grid) return;
        
        // Handle item position changes
        this.grid.on('change', (event, items) => {
            console.log('📍 GridStack change event:', items);
            
            items.forEach(item => {
                const element = item.el;
                const sectionId = this.extractSectionIdFromElement(element);
                
                if (sectionId) {
                    // Emit position change event for section
                    document.dispatchEvent(new CustomEvent('pagebuilder:section-position-changed', {
                        detail: {
                            sectionId: sectionId,
                            position: {
                                x: item.x,
                                y: item.y,
                                w: item.w,
                                h: item.h
                            }
                        }
                    }));
                }
            });
        });
        
        // Handle item resize
        this.grid.on('resizestop', (event, element) => {
            console.log('📏 GridStack resize event:', element);
            
            const sectionId = this.extractSectionIdFromElement(element);
            if (sectionId) {
                const node = element.gridstackNode;
                document.dispatchEvent(new CustomEvent('pagebuilder:section-resized', {
                    detail: {
                        sectionId: sectionId,
                        position: {
                            x: node.x,
                            y: node.y,
                            w: node.w,
                            h: node.h
                        }
                    }
                }));
            }
        });
        
        // Handle drag start
        this.grid.on('dragstart', (event, element) => {
            console.log('🎯 GridStack drag start:', element);
            element.classList.add('dragging');
        });
        
        // Handle drag stop  
        this.grid.on('dragstop', (event, element) => {
            console.log('🎯 GridStack drag stop:', element);
            element.classList.remove('dragging');
        });
        
        // Handle external widget drops
        this.setupExternalDropHandling();
    }

    /**
     * Set up external drop handling for widgets from sidebar
     */
    setupExternalDropHandling() {
        if (!this.grid) return;
        
        // Enable external drop zones
        this.grid.on('dropped', (event, previousWidget, newWidget) => {
            console.log('🎯 External widget dropped:', { previousWidget, newWidget });
            
            // Extract widget information from the dropped element
            const widgetId = previousWidget.el?.getAttribute('data-widget-id');
            const sectionElement = newWidget.el?.closest('.page-section');
            const sectionId = sectionElement?.getAttribute('data-section-id');
            
            if (widgetId && sectionId) {
                // Emit widget drop event
                document.dispatchEvent(new CustomEvent('pagebuilder:widget-dropped', {
                    detail: {
                        widgetId: parseInt(widgetId),
                        sectionId: parseInt(sectionId),
                        position: {
                            x: newWidget.x || 0,
                            y: newWidget.y || 0,
                            w: newWidget.w || 6,
                            h: newWidget.h || 3
                        }
                    }
                }));
            }
        });
    }

    /**
     * Add a grid item
     */
    addGridItem(itemConfig) {
        if (!this.grid) {
            throw new Error('GridStack not initialized');
        }
        
        try {
            console.log('➕ Adding grid item:', itemConfig);
            
            const widget = {
                x: itemConfig.x || 0,
                y: itemConfig.y || 0,
                w: itemConfig.w || 12,
                h: itemConfig.h || 4,
                id: itemConfig.id,
                content: itemConfig.content || '<div>Grid Item</div>'
            };
            
            const addedElement = this.grid.addWidget(widget);
            
            if (addedElement && itemConfig.id) {
                this.gridItems.set(itemConfig.id, {
                    element: addedElement,
                    config: itemConfig
                });
                
                // Set custom attributes
                addedElement.setAttribute('gs-id', itemConfig.id);
                
                console.log('✅ Grid item added successfully:', itemConfig.id);
            }
            
            return addedElement;
            
        } catch (error) {
            console.error('❌ Error adding grid item:', error);
            throw error;
        }
    }

    /**
     * Remove a grid item
     */
    removeGridItem(elementOrId) {
        if (!this.grid) return;
        
        try {
            let element;
            let itemId;
            
            if (typeof elementOrId === 'string') {
                // Remove by ID
                itemId = elementOrId;
                const gridItem = this.gridItems.get(itemId);
                element = gridItem?.element;
            } else {
                // Remove by element
                element = elementOrId;
                itemId = element.getAttribute('gs-id');
            }
            
            if (element) {
                console.log('➖ Removing grid item:', itemId);
                
                this.grid.removeWidget(element);
                
                if (itemId) {
                    this.gridItems.delete(itemId);
                }
                
                console.log('✅ Grid item removed successfully');
            }
            
        } catch (error) {
            console.error('❌ Error removing grid item:', error);
        }
    }

    /**
     * Update grid item position
     */
    updateGridItemPosition(elementOrId, position) {
        if (!this.grid) return;
        
        try {
            let element;
            
            if (typeof elementOrId === 'string') {
                const gridItem = this.gridItems.get(elementOrId);
                element = gridItem?.element;
            } else {
                element = elementOrId;
            }
            
            if (element) {
                console.log('📍 Updating grid item position:', position);
                
                this.grid.update(element, {
                    x: position.x,
                    y: position.y,
                    w: position.w,
                    h: position.h
                });
                
                console.log('✅ Grid item position updated');
            }
            
        } catch (error) {
            console.error('❌ Error updating grid item position:', error);
        }
    }

    /**
     * Clear all grid items
     */
    clearGrid() {
        if (!this.grid) return;
        
        try {
            console.log('🧹 Clearing grid...');
            
            this.grid.removeAll();
            this.gridItems.clear();
            
            console.log('✅ Grid cleared successfully');
            
        } catch (error) {
            console.error('❌ Error clearing grid:', error);
        }
    }

    /**
     * Resize grid to fit content
     */
    resizeGrid() {
        if (!this.grid) return;
        
        try {
            this.grid.compact();
            console.log('📏 Grid resized and compacted');
        } catch (error) {
            console.error('❌ Error resizing grid:', error);
        }
    }

    /**
     * Enable/disable grid
     */
    setGridEnabled(enabled) {
        if (!this.grid) return;
        
        try {
            if (enabled) {
                this.grid.enable();
                console.log('✅ Grid enabled');
            } else {
                this.grid.disable();
                console.log('🔒 Grid disabled');
            }
        } catch (error) {
            console.error('❌ Error setting grid state:', error);
        }
    }

    /**
     * Set grid animation
     */
    setGridAnimation(animate) {
        if (!this.grid) return;
        
        try {
            this.grid.setAnimation(animate);
            console.log(`🎬 Grid animation ${animate ? 'enabled' : 'disabled'}`);
        } catch (error) {
            console.error('❌ Error setting grid animation:', error);
        }
    }

    /**
     * Get grid statistics
     */
    getGridStats() {
        if (!this.grid || !this.container) return null;
        
        return {
            itemCount: this.gridItems.size,
            containerHeight: this.container.offsetHeight,
            containerWidth: this.container.offsetWidth,
            isEnabled: !this.grid.opts.staticGrid,
            hasAnimation: this.grid.opts.animate,
            column: this.grid.opts.column,
            cellHeight: this.grid.opts.cellHeight,
            margin: this.grid.opts.margin
        };
    }

    /**
     * Extract section ID from grid element
     */
    extractSectionIdFromElement(element) {
        if (!element) return null;
        
        // Check for gs-id attribute first
        const gsId = element.getAttribute('gs-id');
        if (gsId && gsId.startsWith('section-')) {
            return parseInt(gsId.replace('section-', ''));
        }
        
        // Check for data-section-id in child elements
        const sectionElement = element.querySelector('[data-section-id]');
        if (sectionElement) {
            return parseInt(sectionElement.getAttribute('data-section-id'));
        }
        
        return null;
    }

    /**
     * Show loading state
     */
    showLoading(message = 'Loading...') {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="grid-loading text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>${message}</h5>
                <p class="text-muted">Setting up your page builder...</p>
            </div>
        `;
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        if (!this.container) return;
        
        const loader = this.container.querySelector('.grid-loading');
        if (loader) {
            loader.remove();
        }
    }

    /**
     * Show empty state
     */
    showEmptyState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="grid-empty-state text-center py-5">
                <div class="empty-icon mb-3">
                    <i class="ri-layout-grid-line display-1 text-muted"></i>
                </div>
                <h5 class="text-muted">Start Building Your Page</h5>
                <p class="text-muted mb-4">Drag sections from the sidebar or click below to get started</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionTemplatesModal">
                    <i class="ri-add-line me-2"></i>Add Your First Section
                </button>
            </div>
        `;
    }

    /**
     * Check if GridStack is initialized
     */
    isInitialized() {
        return this.initialized && this.grid !== null;
    }

    /**
     * Destroy GridStack instance
     */
    destroy() {
        if (this.grid) {
            console.log('💥 Destroying GridStack...');
            this.grid.destroy();
            this.grid = null;
            this.gridItems.clear();
            this.initialized = false;
            console.log('✅ GridStack destroyed');
        }
    }
}

// Export for global use
window.GridManager = GridManager;

console.log('📦 Grid Manager module loaded');