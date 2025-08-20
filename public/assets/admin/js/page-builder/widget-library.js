/**
 * Widget Library Manager
 * 
 * Handles loading and rendering available widgets in the left sidebar.
 * Manages drag & drop functionality for widgets.
 */
class WidgetLibrary {
    constructor(api) {
        this.api = api;
        this.widgets = new Map(); // Store widgets by category
        this.container = null;
        this.isDragging = false;
        
        console.log('📚 Widget Library initialized');
    }

    /**
     * Initialize the widget library
     */
    async init() {
        try {
            console.log('🔄 Initializing Widget Library...');
            
            // Find the widgets container
            this.container = document.getElementById('themeWidgetsGrid');
            if (!this.container) {
                throw new Error('Widget library container not found: #themeWidgetsGrid');
            }
            
            // Load available widgets from API
            await this.loadAvailableWidgets();
            
            // Render widgets in sidebar
            this.renderWidgets();
            
            // Setup drag & drop functionality
            this.setupDragAndDrop();
            
            console.log('✅ Widget Library initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Widget Library:', error);
            this.showErrorState();
        }
    }

    /**
     * Load available widgets from the API
     */
    async loadAvailableWidgets() {
        try {
            console.log('🔄 Loading available widgets from API...');
            
            const response = await this.api.getAvailableWidgets();
            
            if (response.success && response.data) {
                // Clear existing widgets
                this.widgets.clear();
                
                // Store widgets by category
                Object.keys(response.data).forEach(category => {
                    this.widgets.set(category, response.data[category]);
                });
                
                console.log(`✅ Loaded widgets for ${this.widgets.size} categories:`, response.data);
            } else {
                throw new Error('No widgets data received from API');
            }
            
        } catch (error) {
            console.error('❌ Error loading widgets from API:', error);
            console.log('📦 Using fallback widget data for development');
            // Load fallback widgets for development
            this.loadFallbackWidgets();
        }
    }

    /**
     * Load fallback widgets for development/testing
     */
    loadFallbackWidgets() {
        console.log('📦 Loading fallback widget data...');
        
        const fallbackWidgets = {
            'Content': [
                {
                    id: 1,
                    name: 'Text Block',
                    slug: 'text-block',
                    description: 'Rich text content block',
                    icon: 'ri-text',
                    default_settings: {}
                },
                {
                    id: 2,
                    name: 'Heading',
                    slug: 'heading',
                    description: 'Title and subtitle text',
                    icon: 'ri-heading',
                    default_settings: {}
                }
            ],
            'Media': [
                {
                    id: 3,
                    name: 'Image',
                    slug: 'image',
                    description: 'Single image display',
                    icon: 'ri-image-line',
                    default_settings: {}
                },
                {
                    id: 4,
                    name: 'Gallery',
                    slug: 'gallery',
                    description: 'Image gallery grid',
                    icon: 'ri-gallery-line',
                    default_settings: {}
                }
            ],
            'Layout': [
                {
                    id: 5,
                    name: 'Spacer',
                    slug: 'spacer',
                    description: 'Empty space divider',
                    icon: 'ri-separator',
                    default_settings: {}
                },
                {
                    id: 6,
                    name: 'Divider',
                    slug: 'divider',
                    description: 'Visual separator line',
                    icon: 'ri-subtract-line',
                    default_settings: {}
                }
            ]
        };

        // Store fallback widgets
        Object.keys(fallbackWidgets).forEach(category => {
            this.widgets.set(category, fallbackWidgets[category]);
        });
    }

    /**
     * Render widgets in the sidebar
     */
    renderWidgets() {
        if (!this.container) return;
        
        try {
            console.log('🎨 Rendering widgets in sidebar...');
            
            if (this.widgets.size === 0) {
                this.showEmptyState();
                return;
            }

            let widgetsHtml = '';
            
            // Render all widgets from all categories in a flat grid
            this.widgets.forEach((categoryWidgets, category) => {
                categoryWidgets.forEach(widget => {
                    widgetsHtml += this.createWidgetHTML(widget, category);
                });
            });

            this.container.innerHTML = widgetsHtml;
            
            console.log('✅ Widgets rendered successfully');
            
        } catch (error) {
            console.error('❌ Error rendering widgets:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single widget item
     */
    createWidgetHTML(widget, category) {
        return `
            <div class="component-item widget-item" 
                 data-widget-id="${widget.id}" 
                 data-widget-slug="${widget.slug}"
                 data-widget-category="${category}"
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
        
        console.log('🎯 Setting up widget drag & drop...');
        
        // Add event listeners to all widget items
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.matches('.widget-item')) {
                this.handleDragStart(e);
            }
        });
        
        this.container.addEventListener('dragend', (e) => {
            if (e.target.matches('.widget-item')) {
                this.handleDragEnd(e);
            }
        });
        
        console.log('✅ Widget drag & drop setup complete');
    }

    /**
     * Handle drag start event
     */
    handleDragStart(e) {
        const widgetItem = e.target;
        const widgetId = widgetItem.getAttribute('data-widget-id');
        const widgetSlug = widgetItem.getAttribute('data-widget-slug');
        const widgetCategory = widgetItem.getAttribute('data-widget-category');
        
        console.log('🎯 Widget drag started:', { widgetId, widgetSlug, widgetCategory });
        
        // Add dragging visual state
        widgetItem.classList.add('dragging');
        this.isDragging = true;
        
        // Set drag data
        const dragData = {
            type: 'widget',
            widgetId: parseInt(widgetId),
            widgetSlug: widgetSlug,
            widgetCategory: widgetCategory,
            source: 'widget-library'
        };
        
        e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'copy';
        
        // Show drop zones in sections
        this.highlightDropZones(true);
    }

    /**
     * Handle drag end event
     */
    handleDragEnd(e) {
        console.log('🎯 Widget drag ended');
        
        // Remove dragging visual state
        e.target.classList.remove('dragging');
        this.isDragging = false;
        
        // Hide drop zones
        this.highlightDropZones(false);
    }

    /**
     * Highlight/unhighlight drop zones in sections
     */
    highlightDropZones(highlight) {
        const dropZones = document.querySelectorAll('.widget-drop-zone, .section-widgets-container');
        
        dropZones.forEach(zone => {
            if (highlight) {
                zone.classList.add('drop-zone-active');
            } else {
                zone.classList.remove('drop-zone-active');
            }
        });
    }

    /**
     * Show empty state when no widgets available
     */
    showEmptyState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="empty-state text-center p-3">
                <i class="ri-apps-line text-muted mb-2" style="font-size: 2rem;"></i>
                <div class="text-muted small">No widgets available</div>
            </div>
        `;
    }

    /**
     * Show error state when loading fails
     */
    showErrorState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="error-state text-center p-3">
                <i class="ri-error-warning-line text-danger mb-2" style="font-size: 2rem;"></i>
                <div class="text-danger small mb-2">Failed to load widgets</div>
                <button class="btn btn-sm btn-outline-primary retry-btn" onclick="window.widgetLibrary?.init()">
                    <i class="ri-refresh-line me-1"></i>Retry
                </button>
            </div>
        `;
    }

    /**
     * Get widget by ID
     */
    getWidget(widgetId) {
        for (const [category, categoryWidgets] of this.widgets) {
            const widget = categoryWidgets.find(w => w.id === parseInt(widgetId));
            if (widget) {
                return { ...widget, category };
            }
        }
        return null;
    }

    /**
     * Get all widgets as flat array
     */
    getAllWidgets() {
        const allWidgets = [];
        this.widgets.forEach((categoryWidgets, category) => {
            categoryWidgets.forEach(widget => {
                allWidgets.push({ ...widget, category });
            });
        });
        return allWidgets;
    }

    /**
     * Refresh widget library
     */
    async refresh() {
        console.log('🔄 Refreshing Widget Library...');
        await this.loadAvailableWidgets();
        this.renderWidgets();
    }
}

// Export for global use
window.WidgetLibrary = WidgetLibrary;

console.log('📦 Widget Library module loaded');