/**
 * Section Component Factory for GrapesJS
 * Phase 2.3 Implementation
 * 
 * Creates section components that can contain widgets and handles:
 * - Drop zones for widget components
 * - Layout management (columns, responsive)
 * - Visual boundaries and identification
 * - Integration with section schema APIs
 */

class SectionComponentFactory {
    constructor(editor, widgetFactory) {
        this.editor = editor;
        this.widgetFactory = widgetFactory;
        this.componentManager = editor.Components;
        this.blockManager = editor.BlockManager;
        this.loadedSchemas = new Map();
        this.sectionTypes = new Map();
        this.currentPageId = null;
        
        // Configuration
        this.config = {
            apiBaseUrl: '/admin/api',
            sectionTimeout: 10000,
            retryAttempts: 3,
            cacheTimeout: 300000, // 5 minutes
            columnClasses: {
                1: ['col-12'],
                2: ['col-md-6', 'col-md-6'],
                3: ['col-md-4', 'col-md-4', 'col-md-4'],
                4: ['col-md-3', 'col-md-3', 'col-md-3', 'col-md-3']
            }
        };

        // Initialize factory
        this.init();
    }

    /**
     * Initialize the section component factory
     */
    async init() {
        console.log('🏗️ Initializing Section Component Factory...');
        
        try {
            // Get current page ID
            this.currentPageId = this.getCurrentPageId();
            
            // Load section types and schemas
            await this.loadSectionTypes();
            await this.loadPageSectionSchemas();
            
            // Register base section component
            this.registerBaseSectionComponent();
            
            // Register section type components
            await this.registerSectionTypeComponents();
            
            // Set up event listeners
            this.setupEventListeners();
            
            console.log('✅ Section Component Factory initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize Section Component Factory:', error);
            this.handleInitializationError(error);
        }
    }

    /**
     * Get current page ID from DOM
     */
    getCurrentPageId() {
        const gjsContainer = document.getElementById('gjs');
        return gjsContainer ? gjsContainer.dataset.pageId : null;
    }

    /**
     * Load available section types from API
     */
    async loadSectionTypes() {
        try {
            console.log('📡 Loading section types from API...');
            
            const response = await fetch(`${this.config.apiBaseUrl}/sections/types`);
            
            if (!response.ok) {
                throw new Error(`Section types API failed: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success || !data.section_types) {
                throw new Error('Invalid section types API response');
            }
            
            // Store section types
            Object.entries(data.section_types).forEach(([key, type]) => {
                this.sectionTypes.set(key, type);
            });
            
            console.log(`✅ Loaded ${Object.keys(data.section_types).length} section types`);
            return data.section_types;
            
        } catch (error) {
            console.error('❌ Failed to load section types:', error);
            // Use default section types as fallback
            this.loadDefaultSectionTypes();
        }
    }

    /**
     * Load default section types as fallback
     */
    loadDefaultSectionTypes() {
        const defaultTypes = {
            'full-width': {
                name: 'Full Width',
                description: 'Single column spanning full width',
                columns: [{ class: 'col-12' }],
                icon: 'ri-layout-column-line'
            },
            'two-column': {
                name: 'Two Columns',
                description: 'Two equal columns',
                columns: [{ class: 'col-md-6' }, { class: 'col-md-6' }],
                icon: 'ri-layout-2-line'
            },
            'three-column': {
                name: 'Three Columns',
                description: 'Three equal columns',
                columns: [{ class: 'col-md-4' }, { class: 'col-md-4' }, { class: 'col-md-4' }],
                icon: 'ri-layout-3-line'
            }
        };

        Object.entries(defaultTypes).forEach(([key, type]) => {
            this.sectionTypes.set(key, type);
        });

        console.log('✅ Loaded default section types');
    }

    /**
     * Load page section schemas from API
     */
    async loadPageSectionSchemas() {
        if (!this.currentPageId) {
            console.warn('No page ID found, skipping section schema loading');
            return;
        }

        try {
            console.log(`📡 Loading section schemas for page ${this.currentPageId}...`);
            
            const response = await fetch(`${this.config.apiBaseUrl}/pages/${this.currentPageId}/sections/schemas`);
            
            if (!response.ok) {
                throw new Error(`Section schemas API failed: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success || !data.schemas) {
                throw new Error('Invalid section schemas API response');
            }
            
            // Store schemas
            data.schemas.forEach(schema => {
                this.loadedSchemas.set(schema.id, schema);
            });
            
            console.log(`✅ Loaded ${data.schemas.length} section schemas`);
            return data.schemas;
            
        } catch (error) {
            console.error('❌ Failed to load section schemas:', error);
            throw error;
        }
    }

    /**
     * Register base section component
     */
    registerBaseSectionComponent() {
        const factory = this;
        
        this.componentManager.addType('section-base', {
            model: {
                defaults: {
                    tagName: 'section',
                    draggable: true,
                    droppable: '[data-gjs-type="widget"]',
                    editable: false,
                    selectable: true,
                    hoverable: true,
                    attributes: {
                        'data-gjs-type': 'section',
                        'data-section-type': 'section',
                        'class': 'gjs-section-component'
                    },
                    traits: []
                },

                init() {
                    // Component initialization
                    this.on('change:attributes', this.handleAttributeChange.bind(this));
                    this.on('component:add', this.handleComponentAdd.bind(this));
                    this.on('component:remove', this.handleComponentRemove.bind(this));
                    
                    // Load section content if schema is available
                    const sectionId = this.get('section-id');
                    if (sectionId) {
                        this.loadSectionContent();
                    } else {
                        this.initializeEmptySection();
                    }
                },

                /**
                 * Load section content from schema
                 */
                async loadSectionContent() {
                    const sectionId = this.get('section-id');
                    const schema = factory.loadedSchemas.get(parseInt(sectionId));
                    
                    if (!schema) {
                        console.warn('No schema found for section:', sectionId);
                        this.initializeEmptySection();
                        return;
                    }

                    try {
                        // Show loading state
                        this.showLoadingState();
                        
                        // Create section structure from schema
                        await this.buildSectionFromSchema(schema);
                        
                        console.log('✅ Section content loaded successfully');
                        
                    } catch (error) {
                        console.error('❌ Failed to load section content:', error);
                        this.showErrorState(error.message);
                    }
                },

                /**
                 * Build section structure from schema
                 */
                async buildSectionFromSchema(schema) {
                    // Clear existing content
                    this.components().reset();
                    
                    // Add section header
                    this.addSectionHeader(schema);
                    
                    // Create container for columns
                    const container = this.components().add({
                        tagName: 'div',
                        attributes: { 
                            class: 'container-fluid gjs-section-container',
                            'data-section-container': 'true'
                        }
                    });

                    const row = container.components().add({
                        tagName: 'div',
                        attributes: { 
                            class: 'row gjs-section-row',
                            'data-section-row': 'true'
                        }
                    });

                    // Create columns from schema
                    for (const columnData of schema.columns) {
                        const column = row.components().add({
                            tagName: 'div',
                            attributes: { 
                                class: `${columnData.class} gjs-section-column`,
                                'data-section-column': 'true',
                                'data-column-id': columnData.id
                            },
                            droppable: '[data-gjs-type="widget"]'
                        });

                        // Add widgets to column
                        for (const widgetData of columnData.widgets) {
                            await this.addWidgetToColumn(column, widgetData);
                        }

                        // Add drop zone placeholder if column is empty
                        if (columnData.widgets.length === 0) {
                            this.addDropZonePlaceholder(column);
                        }
                    }

                    // Update component attributes
                    this.addAttributes({
                        'data-section-name': schema.name,
                        'data-section-type': schema.type,
                        'data-widget-count': schema.meta.widget_count
                    });
                },

                /**
                 * Add section header for visual identification
                 */
                addSectionHeader(schema) {
                    this.components().add({
                        tagName: 'div',
                        attributes: { 
                            class: 'gjs-section-header',
                            'data-section-header': 'true'
                        },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'section-header-content' },
                                components: [
                                    {
                                        tagName: 'h5',
                                        attributes: { class: 'section-title' },
                                        components: [{ type: 'textnode', content: schema.name }]
                                    },
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'section-controls' },
                                        components: [
                                            {
                                                tagName: 'button',
                                                attributes: { 
                                                    class: 'btn btn-sm btn-outline-primary',
                                                    'data-action': 'configure-section',
                                                    title: 'Configure Section'
                                                },
                                                components: [{ type: 'textnode', content: '⚙️' }]
                                            },
                                            {
                                                tagName: 'button',
                                                attributes: { 
                                                    class: 'btn btn-sm btn-outline-danger',
                                                    'data-action': 'delete-section',
                                                    title: 'Delete Section'
                                                },
                                                components: [{ type: 'textnode', content: '🗑️' }]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    });
                },

                /**
                 * Add widget to column
                 */
                async addWidgetToColumn(column, widgetData) {
                    try {
                        // Get widget component type
                        const widgetComponent = factory.widgetFactory.getComponent(widgetData.widget_type);
                        
                        if (!widgetComponent) {
                            console.warn('Widget component not found:', widgetData.widget_type);
                            return;
                        }

                        // Add widget component to column
                        const widget = column.components().add({
                            type: widgetComponent.componentType,
                            attributes: {
                                'data-widget-id': widgetData.widget_id,
                                'data-widget-slug': widgetData.widget_type,
                                'data-widget-name': widgetData.widget_name,
                                'data-page-section-widget-id': widgetData.id,
                                'data-widget-position': widgetData.position
                            }
                        });

                        console.log('✅ Added widget to column:', widgetData.widget_name);
                        
                    } catch (error) {
                        console.error('❌ Failed to add widget to column:', error);
                    }
                },

                /**
                 * Add drop zone placeholder to empty column
                 */
                addDropZonePlaceholder(column) {
                    column.components().add({
                        tagName: 'div',
                        attributes: { 
                            class: 'gjs-drop-zone-placeholder',
                            'data-drop-zone': 'true'
                        },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'drop-zone-content' },
                                components: [
                                    {
                                        tagName: 'i',
                                        attributes: { class: 'ri-add-line drop-zone-icon' }
                                    },
                                    {
                                        tagName: 'p',
                                        attributes: { class: 'drop-zone-text' },
                                        components: [{ type: 'textnode', content: 'Drop widgets here' }]
                                    }
                                ]
                            }
                        ]
                    });
                },

                /**
                 * Initialize empty section
                 */
                initializeEmptySection() {
                    const sectionType = this.get('section-type') || 'full-width';
                    const typeConfig = factory.sectionTypes.get(sectionType);
                    
                    if (!typeConfig) {
                        console.warn('Unknown section type:', sectionType);
                        return;
                    }

                    // Create empty section structure
                    this.buildEmptySection(typeConfig, sectionType);
                },

                /**
                 * Build empty section structure
                 */
                buildEmptySection(typeConfig, sectionType) {
                    // Add section header
                    this.addSectionHeader({
                        name: `New ${typeConfig.name}`,
                        type: sectionType
                    });
                    
                    // Create container and row
                    const container = this.components().add({
                        tagName: 'div',
                        attributes: { 
                            class: 'container-fluid gjs-section-container',
                            'data-section-container': 'true'
                        }
                    });

                    const row = container.components().add({
                        tagName: 'div',
                        attributes: { 
                            class: 'row gjs-section-row',
                            'data-section-row': 'true'
                        }
                    });

                    // Create columns based on type
                    typeConfig.columns.forEach((columnConfig, index) => {
                        const column = row.components().add({
                            tagName: 'div',
                            attributes: { 
                                class: `${columnConfig.class} gjs-section-column`,
                                'data-section-column': 'true',
                                'data-column-id': index
                            },
                            droppable: '[data-gjs-type="widget"]'
                        });

                        // Add drop zone placeholder
                        this.addDropZonePlaceholder(column);
                    });
                },

                /**
                 * Show loading state
                 */
                showLoadingState() {
                    this.components().reset();
                    this.components().add({
                        tagName: 'div',
                        attributes: { class: 'section-loading-state' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'loading-spinner' }
                            },
                            {
                                tagName: 'p',
                                components: [{ type: 'textnode', content: 'Loading section...' }]
                            }
                        ]
                    });
                },

                /**
                 * Show error state
                 */
                showErrorState(errorMessage) {
                    this.components().reset();
                    this.components().add({
                        tagName: 'div',
                        attributes: { class: 'section-error-state' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'error-icon' },
                                components: [{ type: 'textnode', content: '⚠️' }]
                            },
                            {
                                tagName: 'h4',
                                components: [{ type: 'textnode', content: 'Section Error' }]
                            },
                            {
                                tagName: 'p',
                                attributes: { class: 'error-message' },
                                components: [{ type: 'textnode', content: errorMessage }]
                            },
                            {
                                tagName: 'button',
                                attributes: { 
                                    class: 'retry-btn',
                                    onclick: 'this.closest("[data-gjs-type=\\"section\\"]").__gjs_model.loadSectionContent()'
                                },
                                components: [{ type: 'textnode', content: 'Retry' }]
                            }
                        ]
                    });
                },

                /**
                 * Handle attribute changes
                 */
                handleAttributeChange() {
                    // Section attribute changed, may need updates
                },

                /**
                 * Handle component add
                 */
                handleComponentAdd(component) {
                    // Widget added to section
                    if (component.get('type')?.includes('widget')) {
                        this.handleWidgetAdded(component);
                    }
                },

                /**
                 * Handle component remove
                 */
                handleComponentRemove(component) {
                    // Widget removed from section
                    if (component.get('type')?.includes('widget')) {
                        this.handleWidgetRemoved(component);
                    }
                },

                /**
                 * Handle widget added to section
                 */
                handleWidgetAdded(widget) {
                    console.log('🎯 Widget added to section:', widget.get('widget-name'));
                    
                    // Remove drop zone placeholder if it exists
                    const parent = widget.parent();
                    if (parent) {
                        const placeholder = parent.find('[data-drop-zone="true"]')[0];
                        if (placeholder) {
                            placeholder.remove();
                        }
                    }
                    
                    // Trigger section update event
                    factory.editor.trigger('section:widget-added', {
                        section: this,
                        widget: widget
                    });
                },

                /**
                 * Handle widget removed from section
                 */
                handleWidgetRemoved(widget) {
                    console.log('🗑️ Widget removed from section:', widget.get('widget-name'));
                    
                    // Add drop zone placeholder if column is now empty
                    const parent = widget.parent();
                    if (parent && parent.components().length === 0) {
                        this.addDropZonePlaceholder(parent);
                    }
                    
                    // Trigger section update event
                    factory.editor.trigger('section:widget-removed', {
                        section: this,
                        widget: widget
                    });
                }
            },

            view: {
                events: {
                    'click [data-action="configure-section"]': 'configureSectionHandler',
                    'click [data-action="delete-section"]': 'deleteSectionHandler',
                    'dragover [data-section-column]': 'handleDragOver',
                    'drop [data-section-column]': 'handleDrop'
                },

                /**
                 * Configure section handler
                 */
                configureSectionHandler(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const sectionId = this.model.get('section-id');
                    const sectionType = this.model.get('section-type');
                    
                    console.log('⚙️ Configuring section:', { sectionId, sectionType });
                    
                    // Trigger section configuration event
                    factory.editor.trigger('section:configure', {
                        section: this.model,
                        sectionId: sectionId,
                        sectionType: sectionType
                    });
                },

                /**
                 * Delete section handler
                 */
                deleteSectionHandler(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (confirm('Are you sure you want to delete this section and all its widgets?')) {
                        this.model.remove();
                    }
                },

                /**
                 * Handle drag over for drop zones
                 */
                handleDragOver(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    
                    const column = e.target.closest('[data-section-column]');
                    if (column) {
                        column.classList.add('drag-over');
                    }
                },

                /**
                 * Handle drop in columns
                 */
                handleDrop(e) {
                    e.preventDefault();
                    
                    const column = e.target.closest('[data-section-column]');
                    if (column) {
                        column.classList.remove('drag-over');
                        // GrapesJS will handle the actual drop
                    }
                }
            }
        });
    }

    /**
     * Register section type components
     */
    async registerSectionTypeComponents() {
        console.log('🔧 Registering section type components...');
        
        for (const [sectionType, typeConfig] of this.sectionTypes) {
            try {
                await this.registerSectionTypeComponent(sectionType, typeConfig);
                console.log(`✅ Registered section type: ${typeConfig.name}`);
            } catch (error) {
                console.error(`❌ Failed to register section type ${sectionType}:`, error);
            }
        }
        
        // Add section types to block manager
        this.addSectionTypesToBlockManager();
        
        console.log(`✅ Registered ${this.sectionTypes.size} section type components`);
    }

    /**
     * Register individual section type component
     */
    async registerSectionTypeComponent(sectionType, typeConfig) {
        const componentType = `section-${sectionType}`;
        
        // Register component type
        this.componentManager.addType(componentType, {
            extend: 'section-base',
            model: {
                defaults: {
                    'section-type': sectionType,
                    attributes: {
                        'data-section-type': sectionType,
                        'class': `gjs-section-component section-${sectionType}`
                    }
                }
            }
        });
    }

    /**
     * Add section types to block manager
     */
    addSectionTypesToBlockManager() {
        // Add section category
        this.blockManager.add('sections', {
            label: 'Sections',
            open: false,
            attributes: { class: 'gjs-block-category' }
        });

        // Add each section type as a block
        for (const [sectionType, typeConfig] of this.sectionTypes) {
            this.blockManager.add(`section-${sectionType}`, {
                label: typeConfig.name,
                category: 'Sections',
                content: {
                    type: `section-${sectionType}`,
                    attributes: {
                        'data-section-type': sectionType
                    }
                },
                media: `<i class="${typeConfig.icon}"></i>`,
                activate: true
            });
        }
    }

    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Listen for section configuration
        this.editor.on('section:configure', (data) => {
            this.handleSectionConfiguration(data);
        });

        // Listen for section updates
        this.editor.on('section:widget-added', (data) => {
            this.handleSectionUpdate(data);
        });

        this.editor.on('section:widget-removed', (data) => {
            this.handleSectionUpdate(data);
        });
    }

    /**
     * Handle section configuration
     */
    handleSectionConfiguration(data) {
        const { section, sectionId, sectionType } = data;
        
        console.log('⚙️ Handling section configuration:', { sectionId, sectionType });
        
        // Open section configuration panel/modal
        this.openSectionConfigModal(section, sectionId, sectionType);
    }

    /**
     * Open section configuration modal
     */
    openSectionConfigModal(section, sectionId, sectionType) {
        console.log('📝 Opening section configuration modal');
        
        // This would open a modal for section configuration
        // For now, we'll just show the traits panel
        this.editor.Panels.getButton('views', 'open-tm').set('active', true);
    }

    /**
     * Handle section updates
     */
    handleSectionUpdate(data) {
        const { section, widget } = data;
        
        console.log('🔄 Section updated:', {
            sectionType: section.get('section-type'),
            widgetName: widget.get('widget-name')
        });
        
        // Here you could save section changes to the backend
        // or trigger other update mechanisms
    }

    /**
     * Handle initialization errors
     */
    handleInitializationError(error) {
        console.error('🚨 Section Component Factory initialization failed:', error);
        
        // Show error message to user
        if (this.editor.Modal) {
            this.editor.Modal.open({
                title: 'Section System Error',
                content: `
                    <div class="section-factory-error">
                        <h3>Failed to initialize section system</h3>
                        <p>Error: ${error.message}</p>
                        <p>Some section features may not work properly.</p>
                        <button onclick="location.reload()" class="btn btn-primary">Refresh Page</button>
                    </div>
                `
            });
        }
    }

    /**
     * Get section schema by ID
     */
    getSectionSchema(sectionId) {
        return this.loadedSchemas.get(parseInt(sectionId));
    }

    /**
     * Get section type configuration
     */
    getSectionType(sectionType) {
        return this.sectionTypes.get(sectionType);
    }

    /**
     * Refresh section schemas
     */
    async refreshSectionSchemas() {
        console.log('🔄 Refreshing section schemas...');
        
        try {
            this.loadedSchemas.clear();
            await this.loadPageSectionSchemas();
            
            console.log('✅ Section schemas refreshed successfully');
            
        } catch (error) {
            console.error('❌ Failed to refresh section schemas:', error);
        }
    }
}

// Export for use in other modules
window.SectionComponentFactory = SectionComponentFactory; 