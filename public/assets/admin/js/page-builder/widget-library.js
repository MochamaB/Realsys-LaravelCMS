/**
 * WIDGET LIBRARY MANAGER
 * ======================
 * 
 * GENERAL PURPOSE:
 * Manages the widget library in the left sidebar, including loading, rendering,
 * and drag & drop functionality for available widgets. Provides the interface
 * for users to discover and add widgets to sections.
 * 
 * KEY FUNCTIONS/METHODS & DUPLICATION STATUS:
 * 
 * WIDGET LIBRARY INITIALIZATION:
 * • init() - **UNIQUE** - Initialize widget library in sidebar
 * • loadAvailableWidgets() - **DUPLICATED** - Similar logic in widget-manager.js
 * • loadFallbackWidgets() - **UNIQUE** - Provide fallback widgets during development
 * 
 * WIDGET RENDERING IN SIDEBAR:
 * • renderWidgets() - **UNIQUE** - Display all widgets in sidebar grid
 * • renderWidgetsByCategory() - **UNIQUE** - Group widgets by category in sidebar
 * • renderWidgetCard() - **UNIQUE** - Generate individual widget cards for sidebar
 * • updateWidgetLibraryUI() - **UNIQUE** - Refresh sidebar widget display
 * 
 * DRAG & DROP FUNCTIONALITY:
 * • setupDragAndDrop() - **UNIQUE** - Configure draggable widgets in sidebar
 * • handleDragStart() - **UNIQUE** - Start drag operation for widgets
 * • handleDragEnd() - **UNIQUE** - Clean up after drag operation
 * • createDragPreview() - **UNIQUE** - Visual preview during drag
 * • setDragData() - **UNIQUE** - Set data for drop operations
 * 
 * WIDGET FILTERING & SEARCH:
 * • filterWidgets() - **UNIQUE** - Filter widgets by search term
 * • filterByCategory() - **UNIQUE** - Show/hide widgets by category
 * • setupSearchHandlers() - **UNIQUE** - Configure search functionality
 * • resetFilters() - **UNIQUE** - Clear all applied filters
 * 
 * WIDGET INFORMATION:
 * • showWidgetTooltip() - **UNIQUE** - Display widget information on hover
 * • hideWidgetTooltip() - **UNIQUE** - Hide widget tooltip
 * • getWidgetInfo() - **UNIQUE** - Get detailed widget information
 * • formatWidgetDescription() - **UNIQUE** - Format widget descriptions for display
 * 
 * ERROR & LOADING STATES:
 * • showLoadingState() - **DUPLICATED** - Loading states scattered in multiple files
 * • hideLoadingState() - **DUPLICATED** - Loading states scattered in multiple files
 * • showErrorState() - **UNIQUE** - Display error when widgets fail to load
 * • showEmptyState() - **UNIQUE** - Show message when no widgets available
 * 
 * UTILITY METHODS:
 * • getWidgetsByCategory() - **UNIQUE** - Group widgets by category for filtering
 * • isWidgetAvailable() - **UNIQUE** - Check if widget is available for use
 * • refreshLibrary() - **UNIQUE** - Reload widget library from API
 * 
 * MAJOR DUPLICATION ISSUES:
 * 1. **WIDGET LOADING**: loadAvailableWidgets() duplicated in widget-manager.js
 * 2. **LOADING STATES**: Loading indicators implemented separately from unified loader
 * 3. **API CALLS**: Some direct API calls bypass the centralized PageBuilderAPI
 * 4. **DRAG DATA**: Drag data format might be inconsistent with drop handlers
 * 
 * INCONSISTENCIES WITH OTHER FILES:
 * • widget-manager.js has loadAvailableWidgets() with similar but different logic
 * • Drag data format may not match expectations in drop handlers
 * • Loading states don't use unified-loader-manager.js
 * • Some API calls bypass page-builder-api.js layer
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
            
            if (response.success && response.data && response.data.widgets) {
                // Clear existing widgets
                this.widgets.clear();
                
                // Flatten all widgets from all categories into a single array
                const allWidgets = [];
                Object.keys(response.data.widgets).forEach(category => {
                    response.data.widgets[category].forEach(widget => {
                        allWidgets.push({
                            ...widget,
                            category: category
                        });
                    });
                });
                
                // Store all widgets in a single 'all' category for unified display
                this.widgets.set('all', allWidgets);
                
                console.log(`✅ Loaded ${allWidgets.length} widgets from all categories:`, allWidgets);
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
        
        const fallbackWidgets = [
            {
                id: 1,
                name: 'Text Block',
                slug: 'text-block',
                description: 'Rich text content block',
                icon: 'ri-text',
                category: 'content',
                default_settings: {}
            },
            {
                id: 2,
                name: 'Heading',
                slug: 'heading',
                description: 'Title and subtitle text',
                icon: 'ri-heading',
                category: 'content',
                default_settings: {}
            },
            {
                id: 3,
                name: 'Image',
                slug: 'image',
                description: 'Single image display',
                icon: 'ri-image-line',
                category: 'media',
                default_settings: {}
            },
            {
                id: 4,
                name: 'Gallery',
                slug: 'gallery',
                description: 'Image gallery grid',
                icon: 'ri-gallery-line',
                category: 'media',
                default_settings: {}
            },
            {
                id: 5,
                name: 'Spacer',
                slug: 'spacer',
                description: 'Empty space divider',
                icon: 'ri-separator',
                category: 'layout',
                default_settings: {}
            },
            {
                id: 6,
                name: 'Divider',
                slug: 'divider',
                description: 'Visual separator line',
                icon: 'ri-subtract-line',
                category: 'layout',
                default_settings: {}
            }
        ];

        // Store all fallback widgets in single 'all' category
        this.widgets.set('all', fallbackWidgets);
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
            
            // Get all widgets from the unified 'all' category
            const allWidgets = this.widgets.get('all') || [];
            
            if (allWidgets.length === 0) {
                this.showEmptyState();
                return;
            }
            
            // Render all widgets in a flat list without category separation
            allWidgets.forEach(widget => {
                widgetsHtml += this.createWidgetHTML(widget);
            });

            this.container.innerHTML = widgetsHtml;
            
            console.log(`✅ Rendered ${allWidgets.length} widgets successfully`);
            
        } catch (error) {
            console.error('❌ Error rendering widgets:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single widget item with full-width template-item styling
     */
    createWidgetHTML(widget) {
        // Determine preview image or fallback to icon
        const hasPreviewImage = widget.preview_image && !widget.preview_image.includes('widget-placeholder.png');
        
        return `
            <div class="template-item widget-item w-100 mb-2" 
                 data-widget-id="${widget.id}" 
                 data-widget-slug="${widget.slug}"
                 data-widget-category="${widget.category}"
                 data-widget-type="theme"
                 draggable="true"
                 title="${widget.description || widget.name}">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <!-- Preview Image or Icon + Name aligned left -->
                    <div class="d-flex align-items-center">
                        <div class="template-icon me-2" style="width: 40px; height: 30px; display: flex; align-items: center; justify-content: center;">
                            ${hasPreviewImage ? 
                                `<img src="${widget.preview_image}" alt="${widget.name}" class="img-fluid rounded" style="max-width: 40px; max-height: 30px; object-fit: cover;">` :
                                `<i class="${widget.icon || 'ri-apps-line'}"></i>`
                            }
                        </div>
                        <div class="template-name" style="font-size: 0.875rem; line-height: 1.2;">
                            ${widget.name}
                        </div>
                    </div>
                    <!-- Drag handle aligned right -->
                    <div class="drag-handle" title="Drag to add widget">
                        <i class="ri-drag-move-line"></i>
                    </div>
                </div>
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