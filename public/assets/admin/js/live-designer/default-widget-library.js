/**
 * Default Widget Library Manager - Live Designer Edition
 * 
 * Handles default/fallback widgets in the left sidebar.
 * These are basic widgets that don't require API calls.
 */
class DefaultWidgetLibrary {
    constructor(livePreview, unifiedLoader) {
        this.livePreview = livePreview;
        this.unifiedLoader = unifiedLoader;
        this.defaultWidgets = [];
        this.container = null;
        
        console.log('📦 Live Designer Default Widget Library initialized');
    }

    /**
     * Initialize the default widget library
     */
    async init() {
        try {
            console.log('🔄 Initializing Live Designer Default Widget Library...');
            
            // Find the default widgets container
            this.container = document.getElementById('defaultWidgetsGrid');
            if (!this.container) {
                throw new Error('Default widget library container not found: #defaultWidgetsGrid');
            }
            
            // Show loader
            if (this.unifiedLoader) {
                this.unifiedLoader.show('default-widget-loading', 'Loading default widgets...', 10);
            }
            
            // Load default widgets
            this.loadDefaultWidgets();
            
            // Render widgets in sidebar
            this.renderDefaultWidgets();
            
            // Setup drag and drop functionality
            this.setupDragAndDrop();
            
            // Hide loader
            if (this.unifiedLoader) {
                this.unifiedLoader.hide('default-widget-loading');
            }
            
            console.log('✅ Live Designer Default Widget Library initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Live Designer Default Widget Library:', error);
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('default-widget-loading', 'Failed to load default widgets');
            }
            this.showErrorState();
        }
    }

    /**
     * Load default/fallback widgets (no API call needed)
     */
    loadDefaultWidgets() {
        console.log('📦 Loading default widget data...');
        
        this.defaultWidgets = [
            {
                id: 'default-1',
                name: 'Text Block',
                slug: 'text-block',
                description: 'Rich text content block',
                icon: 'ri-text',
                category: 'content',
                type: 'default',
                default_settings: {}
            },
            {
                id: 'default-2',
                name: 'Heading',
                slug: 'heading',
                description: 'Title and subtitle text',
                icon: 'ri-heading',
                category: 'content',
                type: 'default',
                default_settings: {}
            },
            {
                id: 'default-3',
                name: 'Image',
                slug: 'image',
                description: 'Single image display',
                icon: 'ri-image-line',
                category: 'media',
                type: 'default',
                default_settings: {}
            },
            {
                id: 'default-4',
                name: 'Button',
                slug: 'button',
                description: 'Call-to-action button',
                icon: 'ri-checkbox-blank-line',
                category: 'interactive',
                type: 'default',
                default_settings: {}
            },
            {
                id: 'default-5',
                name: 'Spacer',
                slug: 'spacer',
                description: 'Empty space divider',
                icon: 'ri-separator',
                category: 'layout',
                type: 'default',
                default_settings: {}
            },
            {
                id: 'default-6',
                name: 'Divider',
                slug: 'divider',
                description: 'Visual separator line',
                icon: 'ri-subtract-line',
                category: 'layout',
                type: 'default',
                default_settings: {}
            }
        ];

        console.log(`📦 Loaded ${this.defaultWidgets.length} default widgets`);
    }

    /**
     * Render default widgets in the sidebar
     */
    renderDefaultWidgets() {
        if (!this.container) return;
        
        try {
            console.log('🎨 Rendering default widgets in live designer sidebar...');
            
            if (this.defaultWidgets.length === 0) {
                this.showEmptyState();
                return;
            }

            let widgetsHtml = '';
            
            // Render all default widgets
            this.defaultWidgets.forEach(widget => {
                widgetsHtml += this.createWidgetHTML(widget);
            });

            this.container.innerHTML = widgetsHtml;
            
            console.log(`✅ Rendered ${this.defaultWidgets.length} default widgets in live designer`);
            
        } catch (error) {
            console.error('❌ Error rendering default widgets:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single default widget item (draggable like page-builder)
     */
    createWidgetHTML(widget) {
        return `
            <div class="component-item" 
                 data-widget-id="${widget.id}" 
                 data-widget-slug="${widget.slug}"
                 data-widget-category="${widget.category}"
                 data-widget-type="default"
                 draggable="true"
                 title="${widget.description || widget.name}">
                <i class="${widget.icon || 'ri-apps-line'}"></i>
                <div class="label">${widget.name}</div>
            </div>
        `;
    }

    /**
     * Setup drag and drop functionality
     */
    setupDragAndDrop() {
        if (!this.container) return;
        
        console.log('🎯 Setting up default widget drag & drop...');
        
        // Add event listeners for drag and drop
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.matches('.component-item')) {
                this.handleDragStart(e);
            }
        });
        
        this.container.addEventListener('dragend', (e) => {
            if (e.target.matches('.component-item')) {
                this.handleDragEnd(e);
            }
        });
        
        console.log('✅ Default widget drag & drop setup complete');
    }

    /**
     * Handle drag start event
     */
    handleDragStart(e) {
        const widgetItem = e.target;
        const widgetId = widgetItem.getAttribute('data-widget-id');
        const widgetSlug = widgetItem.getAttribute('data-widget-slug');
        const widgetCategory = widgetItem.getAttribute('data-widget-category');
        
        console.log('🎯 Default widget drag started:', { widgetId, widgetSlug, widgetCategory });
        
        // Add dragging visual state
        widgetItem.classList.add('dragging');
        
        // Set drag data for iframe communication
        const dragData = {
            type: 'widget',
            widgetId: widgetId,
            widgetSlug: widgetSlug,
            widgetCategory: widgetCategory,
            widgetType: 'default',
            source: 'live-designer-default-widget-library'
        };
        
        e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'copy';
        
        // Highlight iframe drop zone
        this.highlightIframeDropZone(true);
    }

    /**
     * Handle drag end event
     */
    handleDragEnd(e) {
        console.log('🎯 Default widget drag ended');
        
        // Remove dragging visual state
        e.target.classList.remove('dragging');
        
        // Remove iframe drop zone highlight
        this.highlightIframeDropZone(false);
    }

    /**
     * Highlight/unhighlight iframe drop zone
     */
    highlightIframeDropZone(highlight) {
        const canvasContainer = document.getElementById('canvasContainer');
        
        if (canvasContainer) {
            if (highlight) {
                canvasContainer.style.background = '#fff3cd';
                canvasContainer.style.border = '2px dashed #ffc107';
                canvasContainer.style.borderRadius = '8px';
            } else {
                canvasContainer.style.background = '#f1f4f7';
                canvasContainer.style.border = 'none';
                canvasContainer.style.borderRadius = '0';
            }
        }
    }

    /**
     * Show empty state when no widgets available
     */
    showEmptyState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="component-loading">
                <i class="ri-apps-line text-muted mb-2" style="font-size: 2rem;"></i>
                <div class="text-muted small">No default widgets available</div>
            </div>
        `;
    }

    /**
     * Show error state when loading fails
     */
    showErrorState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="component-error">
                <i class="ri-error-warning-line error-icon"></i>
                <div class="error-message">Failed to load default widgets</div>
                <button class="btn btn-sm btn-outline-primary retry-btn" onclick="window.defaultWidgetLibrary?.init()">
                    <i class="ri-refresh-line me-1"></i>Retry
                </button>
            </div>
        `;
    }

    /**
     * Get all default widgets
     */
    getAllDefaultWidgets() {
        return [...this.defaultWidgets];
    }

    /**
     * Refresh default widget library
     */
    async refresh() {
        console.log('🔄 Refreshing Live Designer Default Widget Library...');
        this.loadDefaultWidgets();
        this.renderDefaultWidgets();
    }
}

// Export for global use
window.DefaultWidgetLibrary = DefaultWidgetLibrary;

console.log('📦 Live Designer Default Widget Library module loaded');