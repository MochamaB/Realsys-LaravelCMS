/**
 * Widget Component Factory for GrapesJS
 * Phase 2.1 Implementation
 * 
 * Generates GrapesJS components from widget schemas and handles:
 * - Preview loading from API
 * - Edit markers and configuration
 * - Error handling and fallbacks
 * - Component lifecycle management
 */

class WidgetComponentFactory {
    constructor(editor) {
        this.editor = editor;
        this.componentManager = editor.Components;
        this.blockManager = editor.BlockManager;
        this.loadedSchemas = new Map();
        this.componentCache = new Map();
        this.previewCache = new Map();
        
        // Configuration
        this.config = {
            apiBaseUrl: '/admin/api',
            previewTimeout: 10000,
            retryAttempts: 3,
            cacheTimeout: 300000, // 5 minutes
            debounceDelay: 500
        };

        // Initialize factory
        this.init();
    }

    /**
     * Initialize the widget component factory
     */
    async init() {
        console.log('🏭 Initializing Widget Component Factory...');
        
        try {
            // Load widget schemas from API
            await this.loadWidgetSchemas();
            
            // Register base widget component type
            this.registerBaseWidgetComponent();
            
            // Create and register all widget components
            await this.registerAllWidgetComponents();
            
            // Set up event listeners
            this.setupEventListeners();
            
            console.log('✅ Widget Component Factory initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize Widget Component Factory:', error);
            this.handleInitializationError(error);
        }
    }

    /**
     * Load widget schemas from API
     */
    async loadWidgetSchemas() {
        try {
            console.log('📡 Loading widget schemas from API...');
            
            const response = await fetch(`${this.config.apiBaseUrl}/widgets/schemas`);
            
            if (!response.ok) {
                throw new Error(`API request failed: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success || !data.schemas) {
                throw new Error('Invalid API response format');
            }
            
            // Store schemas in map for quick access
            data.schemas.forEach(schema => {
                this.loadedSchemas.set(schema.slug, schema);
            });
            
            console.log(`✅ Loaded ${data.schemas.length} widget schemas`);
            return data.schemas;
            
        } catch (error) {
            console.error('❌ Failed to load widget schemas:', error);
            throw error;
        }
    }

    /**
     * Register base widget component type
     */
    registerBaseWidgetComponent() {
        const factory = this;
        
        this.componentManager.addType('widget-base', {
            model: {
                defaults: {
                    tagName: 'div',
                    draggable: '[data-section-type]',
                    droppable: false,
                    editable: false,
                    selectable: true,
                    hoverable: true,
                    attributes: {
                        'data-gjs-type': 'widget',
                        'class': 'gjs-widget-component'
                    },
                    traits: []
                },

                init() {
                    // Component initialization
                    this.on('change:attributes', this.handleAttributeChange.bind(this));
                    this.on('change:content', this.handleContentChange.bind(this));
                    
                    // Load preview if widget data is available
                    const widgetId = this.get('widget-id');
                    const widgetSlug = this.get('widget-slug');
                    
                    if (widgetId && widgetSlug) {
                        this.loadWidgetPreview();
                    }
                },

                /**
                 * Load widget preview from API
                 */
                async loadWidgetPreview() {
                    const widgetId = this.get('widget-id');
                    const pageSectionWidgetId = this.get('page-section-widget-id');
                    
                    if (!widgetId) {
                        console.warn('No widget ID found for preview loading');
                        return;
                    }

                    try {
                        // Show loading state
                        this.showLoadingState();
                        
                        // Check cache first
                        const cacheKey = `${widgetId}_${pageSectionWidgetId || 'default'}`;
                        const cachedPreview = factory.previewCache.get(cacheKey);
                        
                        if (cachedPreview && this.isCacheValid(cachedPreview)) {
                            this.applyPreview(cachedPreview.data);
                            return;
                        }

                        // Build API URL
                        let apiUrl = `${factory.config.apiBaseUrl}/widgets/${widgetId}/preview`;
                        if (pageSectionWidgetId) {
                            apiUrl += `?page_section_widget_id=${pageSectionWidgetId}`;
                        }

                        // Fetch preview with timeout
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), factory.config.previewTimeout);

                        const response = await fetch(apiUrl, {
                            signal: controller.signal,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        clearTimeout(timeoutId);

                        if (!response.ok) {
                            throw new Error(`Preview API failed: ${response.status}`);
                        }

                        const previewData = await response.json();
                        
                        if (!previewData.success) {
                            throw new Error(previewData.error || 'Preview generation failed');
                        }

                        // Cache the preview
                        factory.previewCache.set(cacheKey, {
                            data: previewData,
                            timestamp: Date.now()
                        });

                        // Apply preview to component
                        this.applyPreview(previewData);

                    } catch (error) {
                        console.error('❌ Failed to load widget preview:', error);
                        this.showErrorState(error.message);
                    }
                },

                /**
                 * Apply preview data to component
                 */
                applyPreview(previewData) {
                    try {
                        // Update component content
                        if (previewData.html) {
                            this.components(previewData.html);
                        }

                        // Inject CSS if available
                        if (previewData.css) {
                            this.injectPreviewStyles(previewData.css);
                        }

                        // Update component attributes with preview data
                        if (previewData.widget) {
                            this.addAttributes({
                                'data-widget-name': previewData.widget.name,
                                'data-widget-category': previewData.widget.category
                            });
                        }

                        // Set up traits from schema
                        if (previewData.data && previewData.data.schema) {
                            this.updateTraitsFromSchema(previewData.data.schema);
                        }

                        console.log('✅ Widget preview applied successfully');

                    } catch (error) {
                        console.error('❌ Failed to apply preview:', error);
                        this.showErrorState('Failed to apply widget preview');
                    }
                },

                /**
                 * Inject preview styles into the canvas
                 */
                injectPreviewStyles(css) {
                    try {
                        const canvasDoc = factory.editor.Canvas.getDocument();
                        const existingStyle = canvasDoc.getElementById(`widget-styles-${this.cid}`);
                        
                        if (existingStyle) {
                            existingStyle.remove();
                        }

                        const styleElement = canvasDoc.createElement('style');
                        styleElement.id = `widget-styles-${this.cid}`;
                        styleElement.textContent = css;
                        canvasDoc.head.appendChild(styleElement);

                    } catch (error) {
                        console.warn('Failed to inject preview styles:', error);
                    }
                },

                /**
                 * Update component traits from schema
                 */
                updateTraitsFromSchema(schema) {
                    if (!schema || !schema.fields) return;

                    const traits = schema.fields.map(field => {
                        return factory.convertSchemaFieldToTrait(field);
                    }).filter(trait => trait !== null);

                    this.set('traits', traits);
                },

                /**
                 * Show loading state
                 */
                showLoadingState() {
                    this.components(`
                        <div class="widget-loading-state">
                            <div class="loading-spinner"></div>
                            <p>Loading widget preview...</p>
                        </div>
                    `);
                },

                /**
                 * Show error state
                 */
                showErrorState(errorMessage) {
                    const widgetName = this.get('widget-name') || 'Widget';
                    
                    this.components(`
                        <div class="widget-error-state">
                            <div class="error-icon">⚠️</div>
                            <h4>${widgetName}</h4>
                            <p class="error-message">${errorMessage}</p>
                            <button class="retry-btn" onclick="this.closest('[data-gjs-type=\\"widget\\"]').__gjs_model.loadWidgetPreview()">
                                Retry
                            </button>
                        </div>
                    `);
                },

                /**
                 * Handle attribute changes
                 */
                handleAttributeChange() {
                    // Debounce preview updates
                    clearTimeout(this.updateTimeout);
                    this.updateTimeout = setTimeout(() => {
                        this.loadWidgetPreview();
                    }, factory.config.debounceDelay);
                },

                /**
                 * Handle content changes
                 */
                handleContentChange() {
                    // Component content changed, may need to refresh
                },

                /**
                 * Check if cached preview is still valid
                 */
                isCacheValid(cachedItem) {
                    return (Date.now() - cachedItem.timestamp) < factory.config.cacheTimeout;
                }
            },

            view: {
                events: {
                    'dblclick': 'openWidgetConfig',
                    'click [data-action="edit-widget"]': 'openWidgetConfig',
                    'click [data-action="delete-widget"]': 'deleteWidget'
                },

                /**
                 * Open widget configuration panel
                 */
                openWidgetConfig() {
                    const widgetId = this.model.get('widget-id');
                    const widgetSlug = this.model.get('widget-slug');
                    
                    console.log('🔧 Opening widget configuration:', { widgetId, widgetSlug });
                    
                    // Trigger custom event for widget configuration
                    factory.editor.trigger('widget:configure', {
                        component: this.model,
                        widgetId: widgetId,
                        widgetSlug: widgetSlug
                    });
                },

                /**
                 * Delete widget component
                 */
                deleteWidget() {
                    if (confirm('Are you sure you want to delete this widget?')) {
                        this.model.remove();
                    }
                }
            }
        });
    }

    /**
     * Register all widget components from schemas
     */
    async registerAllWidgetComponents() {
        console.log('🔧 Registering widget components...');
        
        for (const [slug, schema] of this.loadedSchemas) {
            try {
                await this.registerWidgetComponent(schema);
                console.log(`✅ Registered component: ${schema.name}`);
            } catch (error) {
                console.error(`❌ Failed to register component ${schema.name}:`, error);
            }
        }
        
        console.log(`✅ Registered ${this.loadedSchemas.size} widget components`);
    }

    /**
     * Register individual widget component
     */
    async registerWidgetComponent(schema) {
        const componentType = schema.grapesjs.component_type;
        const blockId = schema.grapesjs.block_id;
        
        // Register component type
        this.componentManager.addType(componentType, {
            extend: 'widget-base',
            model: {
                defaults: {
                    ...this.getComponentDefaults(schema),
                    'widget-id': schema.database_id,
                    'widget-slug': schema.slug,
                    'widget-name': schema.name,
                    traits: this.convertSchemaToTraits(schema)
                }
            }
        });

        // Add to block manager
        this.blockManager.add(blockId, {
            label: schema.name,
            category: schema.category,
            content: {
                type: componentType,
                attributes: {
                    'data-widget-id': schema.database_id,
                    'data-widget-slug': schema.slug,
                    'data-widget-name': schema.name
                }
            },
            media: `<i class="${schema.icon}"></i>`,
            activate: true
        });

        // Cache component for quick access
        this.componentCache.set(schema.slug, {
            schema: schema,
            componentType: componentType,
            blockId: blockId
        });
    }

    /**
     * Get component defaults from schema
     */
    getComponentDefaults(schema) {
        return {
            tagName: 'div',
            attributes: {
                'data-gjs-type': 'widget',
                'data-widget-id': schema.database_id,
                'data-widget-slug': schema.slug,
                'class': `gjs-widget-component ${schema.slug}-widget`
            },
            draggable: schema.grapesjs.draggable,
            droppable: schema.grapesjs.droppable,
            resizable: schema.grapesjs.resizable || false
        };
    }

    /**
     * Convert schema to GrapesJS traits
     */
    convertSchemaToTraits(schema) {
        if (!schema.fields) return [];

        return schema.fields.map(field => {
            return this.convertSchemaFieldToTrait(field);
        }).filter(trait => trait !== null);
    }

    /**
     * Convert individual schema field to trait
     */
    convertSchemaFieldToTrait(field) {
        const baseTrait = {
            name: field.slug,
            label: field.label || field.name,
            changeProp: 1
        };

        switch (field.type) {
            case 'text':
                return { ...baseTrait, type: 'text' };
            
            case 'textarea':
                return { ...baseTrait, type: 'textarea' };
            
            case 'number':
                return { ...baseTrait, type: 'number' };
            
            case 'select':
                return {
                    ...baseTrait,
                    type: 'select',
                    options: field.options || []
                };
            
            case 'checkbox':
                return { ...baseTrait, type: 'checkbox' };
            
            case 'color':
                return { ...baseTrait, type: 'color' };
            
            case 'image':
                return {
                    ...baseTrait,
                    type: 'button',
                    text: 'Select Image',
                    command: 'open-image-picker'
                };
            
            case 'repeater':
                return {
                    ...baseTrait,
                    type: 'button',
                    text: `Configure ${field.label}`,
                    command: 'open-repeater-editor'
                };
            
            default:
                return { ...baseTrait, type: 'text' };
        }
    }

    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Listen for component selection
        this.editor.on('component:selected', (component) => {
            if (component.get('type')?.includes('widget')) {
                this.handleWidgetSelection(component);
            }
        });

        // Listen for widget configuration events
        this.editor.on('widget:configure', (data) => {
            this.handleWidgetConfiguration(data);
        });

        // Listen for canvas updates
        this.editor.on('canvas:update', () => {
            this.refreshVisibleWidgets();
        });
    }

    /**
     * Handle widget selection
     */
    handleWidgetSelection(component) {
        console.log('🎯 Widget selected:', component.get('widget-name'));
        
        // Show widget overlay if it exists
        const view = component.view;
        if (view && view.el) {
            const overlay = view.el.querySelector('[data-widget-overlay]');
            if (overlay) {
                overlay.style.display = 'block';
            }
        }
    }

    /**
     * Handle widget configuration
     */
    handleWidgetConfiguration(data) {
        const { component, widgetId, widgetSlug } = data;
        
        console.log('⚙️ Configuring widget:', { widgetId, widgetSlug });
        
        // Open traits panel
        this.editor.Panels.getButton('views', 'open-tm').set('active', true);
        
        // You could also open a custom configuration modal here
        this.openWidgetConfigModal(component, widgetId, widgetSlug);
    }

    /**
     * Open widget configuration modal
     */
    openWidgetConfigModal(component, widgetId, widgetSlug) {
        // This would open a custom modal for widget configuration
        // For now, we'll just log the action
        console.log('📝 Opening configuration modal for widget:', widgetSlug);
        
        // Future implementation would show a modal with:
        // - Widget field editors
        // - Content query builder
        // - Preview updates
    }

    /**
     * Refresh visible widgets
     */
    refreshVisibleWidgets() {
        // Find all widget components in the canvas
        const allComponents = this.editor.Components.getWrapper().find('[data-gjs-type="widget"]');
        
        allComponents.forEach(component => {
            // Check if component is in viewport and refresh if needed
            if (this.isComponentVisible(component)) {
                component.loadWidgetPreview();
            }
        });
    }

    /**
     * Check if component is visible in viewport
     */
    isComponentVisible(component) {
        // Simple visibility check - could be enhanced
        return true; // For now, always refresh
    }

    /**
     * Handle initialization errors
     */
    handleInitializationError(error) {
        console.error('🚨 Widget Component Factory initialization failed:', error);
        
        // Show error message to user
        if (this.editor.Modal) {
            this.editor.Modal.open({
                title: 'Widget System Error',
                content: `
                    <div class="widget-factory-error">
                        <h3>Failed to initialize widget system</h3>
                        <p>Error: ${error.message}</p>
                        <p>Please refresh the page or contact support if the problem persists.</p>
                        <button onclick="location.reload()" class="btn btn-primary">Refresh Page</button>
                    </div>
                `
            });
        }
    }

    /**
     * Get component by widget slug
     */
    getComponent(widgetSlug) {
        return this.componentCache.get(widgetSlug);
    }

    /**
     * Get schema by widget slug
     */
    getSchema(widgetSlug) {
        return this.loadedSchemas.get(widgetSlug);
    }

    /**
     * Clear preview cache
     */
    clearPreviewCache() {
        this.previewCache.clear();
        console.log('🗑️ Preview cache cleared');
    }

    /**
     * Refresh all widget schemas
     */
    async refreshSchemas() {
        console.log('🔄 Refreshing widget schemas...');
        
        try {
            this.loadedSchemas.clear();
            this.componentCache.clear();
            this.clearPreviewCache();
            
            await this.loadWidgetSchemas();
            await this.registerAllWidgetComponents();
            
            console.log('✅ Widget schemas refreshed successfully');
            
        } catch (error) {
            console.error('❌ Failed to refresh schemas:', error);
        }
    }
}

// Export for use in other modules
window.WidgetComponentFactory = WidgetComponentFactory; 