/**
 * GridStack Page Builder Core
 * Main controller for the page builder functionality
 */
window.GridStackPageBuilder = {
    config: {},
    gridStacks: {},
    selectedWidget: null,
    isDragging: false,

    /**
     * Initialize the page builder
     */
    async init(config) {
        console.log('🚀 Initializing GridStack Page Builder...');
        
        this.config = config;
        
        // Setup global drag events for loading indicator
        this.setupDragEvents();
        
        // Initialize widget manager
        if (window.WidgetManager) {
            window.WidgetManager.init();
        }
        
        // Show loader while loading content
        this.showPageLoader();
        
        // Fetch page content if available
        if (this.config && this.config.pageId) {
            await this.loadPageContent();
        }
        
        // Hide loader after content is loaded
        this.hidePageLoader();
        
        console.log('✅ GridStack Page Builder initialized');
    },

    /**
     * Setup global drag events for loading indicator
     */
    setupDragEvents() {
        document.addEventListener('dragstart', () => {
            this.isDragging = true;
            document.body.classList.add('is-dragging');
            
            // Show the loading indicator
            this.showLoadingIndicator();
        });
        
        document.addEventListener('dragend', () => {
            this.isDragging = false;
            document.body.classList.remove('is-dragging');
            
            // Hide the loading indicator
            this.hideLoadingIndicator();
        });
    },
    
    /**
     * Show loading indicator during drag operations
     */
    showLoadingIndicator() {
        // Create or show loading indicator
        let indicator = document.querySelector('.drag-loading-indicator');
        
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'drag-loading-indicator';
            indicator.innerHTML = `
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Processing drag operation...</span>
            `;
            document.body.appendChild(indicator);
        }
        
        // Show it with a small delay
        setTimeout(() => {
            indicator.classList.add('active');
        }, 50);
    },
    
    /**
     * Hide loading indicator
     */
    hideLoadingIndicator() {
        const indicator = document.querySelector('.drag-loading-indicator');
        
        if (indicator) {
            indicator.classList.remove('active');
        }
    },

    /**
     * Show page loader
     */
    showPageLoader() {
        const container = document.getElementById('pageSectionsContainer');
        if (container) {
            container.innerHTML = `
                <div class="page-loader">
                    <div class="loader-content">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="loader-text">
                            <h5>Loading Page Content...</h5>
                            <p>Please wait while we load your page sections</p>
                        </div>
                    </div>
                </div>
            `;
        }
    },

    /**
     * Hide page loader
     */
    hidePageLoader() {
        const loader = document.querySelector('.page-loader');
        if (loader) {
            loader.remove();
        }
    },

    /**
     * Render demo section with GridStack
     */
    renderDemoSection() {
        const container = document.getElementById('pageSectionsContainer');
        container.innerHTML = '';
        
        // Create a single section for demo
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'page-section';
        sectionDiv.setAttribute('data-section-id', 'demo');
        
        sectionDiv.innerHTML = `
            <div class="canvas-wrapper">
                <div class="section-grid-stack" id="demoSectionGrid">
                    <!-- Widgets will be added here -->
                    <div class="widget-drop-zone">
                        <i class="ri-add-line"></i>
                        <span>Drop widgets here</span>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(sectionDiv);
        
        // Initialize GridStack for this section
        const gridElement = sectionDiv.querySelector('.section-grid-stack');
        const grid = GridStack.init({
            cellHeight: 80,
            verticalMargin: 10,
            horizontalMargin: 10,
            column: 12,
            float: true,
            resizable: { handles: 'se, sw' },
            acceptWidgets: true,
            animate: true
        }, gridElement);
        
        // Store reference
        this.gridStacks['demo'] = grid;
        gridElement.gridStackInstance = grid;
        
        // Setup grid events
        grid.on('added', (event, items) => {
            items.forEach(item => {
                const element = item.el;
                element.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.selectWidget(element);
                });
            });
            
            // Hide drop zone when widgets are added
            gridElement.classList.add('has-widgets');
        });
        
        grid.on('removed', (event, items) => {
            // Show drop zone if no widgets left
            if (grid.getGridItems().length === 0) {
                gridElement.classList.remove('has-widgets');
            }
        });
    },
    
    /**
     * Load page content from the backend
     */
    async loadPageContent() {
        if (!this.config.pageId) return;
        
        try {
            console.log(`🔄 Loading page content for page ID: ${this.config.pageId}...`);
            
            // Fetch sections from the API
            const response = await fetch(`${this.config.apiBaseUrl}/pages/${this.config.pageId}/sections`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                console.log(`📦 Loaded ${data.data.length} sections from backend`);
                this.renderSections(data.data);
            } else {
                console.log('📭 No sections found, showing default placeholder');
                this.showDefaultPlaceholder();
            }
            
        } catch (error) {
            console.error('❌ Error loading page content:', error);
            // Show default placeholder on error
            this.showDefaultPlaceholder();
        }
    },

    /**
     * Render sections from backend data
     */
    renderSections(sections) {
        const container = document.getElementById('pageSectionsContainer');
        if (!container) {
            console.error('❌ Page sections container not found');
            return;
        }
        
        // Clear existing content
        container.innerHTML = '';
        
        // Hide default placeholder
        this.hideDefaultPlaceholder();
        
        // Render each section
        sections.forEach((section, index) => {
            this.renderSection(section, index);
        });
        
        // Update section counter
        this.updateSectionCounter(sections.length);
        
        // Setup widget drop zones after sections are rendered
        if (window.WidgetManager && window.WidgetManager.setupDropZonesAfterSectionsLoad) {
            window.WidgetManager.setupDropZonesAfterSectionsLoad();
        }
        
        console.log(`✅ Rendered ${sections.length} sections`);
    },

    /**
     * Render a single section
     */
    renderSection(section, index) {
        const container = document.getElementById('pageSectionsContainer');
        
        // Create section element
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'page-section';
        sectionDiv.setAttribute('data-section-id', section.id);
        
        // Get the actual section type from the database using template_section_id
        const sectionType = section.template_section?.section_type || 'full-width';
        sectionDiv.setAttribute('data-section-type', sectionType);
        
        console.log(`🎨 Rendering section ${section.id} with type: ${sectionType}`, {
            section_id: section.id,
            template_section_id: section.template_section_id,
            section_type: sectionType,
            template_section: section.template_section
        });
        
        // Set section content based on template type
        const sectionContent = this.getSectionContent(section, sectionType);
        sectionDiv.innerHTML = sectionContent;
        
        // Add to container
        container.appendChild(sectionDiv);
        
        // Initialize GridStack for this section
        this.initializeSectionGridStack(section.id, section, sectionType);
        
        console.log(`✅ Rendered section ${section.id} (${sectionType})`);
    },

    /**
     * Get section content based on template type
     */
    getSectionContent(section, sectionType) {
        const sectionName = section.template_section?.name || this.getDefaultSectionName(sectionType);
        
        switch (sectionType) {
            case 'full-width':
                return `
                    <div class="section-content-wrapper">
                        <div class="section-actions">
                            <button class="btn btn-sm btn-outline-secondary section-action-btn" onclick="window.GridStackPageBuilder.editSection(${section.id})" title="Edit Section">
                                <i class="ri-settings-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger section-action-btn" onclick="window.GridStackPageBuilder.deleteSection(${section.id})" title="Delete Section">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <div class="section-grid-stack" data-section-grid="${section.id}">
                            <div class="widget-drop-zone">
                                <div class="drop-zone-content">
                                    <i class="ri-add-line"></i>
                                    <span>Drop widgets here</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'multi-column':
                return `
                    <div class="section-content-wrapper">
                        <div class="section-actions">
                            <button class="btn btn-sm btn-outline-secondary section-action-btn" onclick="window.GridStackPageBuilder.editSection(${section.id})" title="Edit Section">
                                <i class="ri-settings-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger section-action-btn" onclick="window.GridStackPageBuilder.deleteSection(${section.id})" title="Delete Section">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="section-grid-stack" data-section-grid="${section.id}_col1">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Column 1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="section-grid-stack" data-section-grid="${section.id}_col2">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Column 2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="section-grid-stack" data-section-grid="${section.id}_col3">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Column 3</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'sidebar-left':
                return `
                    <div class="section-content-wrapper">
                        <div class="section-actions">
                            <button class="btn btn-sm btn-outline-secondary section-action-btn" onclick="window.GridStackPageBuilder.editSection(${section.id})" title="Edit Section">
                                <i class="ri-settings-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger section-action-btn" onclick="window.GridStackPageBuilder.deleteSection(${section.id})" title="Delete Section">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="section-grid-stack" data-section-grid="${section.id}_sidebar">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Sidebar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="section-grid-stack" data-section-grid="${section.id}_main">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Main Content</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'sidebar-right':
                return `
                    <div class="section-content-wrapper">
                        <div class="section-actions">
                            <button class="btn btn-sm btn-outline-secondary section-action-btn" onclick="window.GridStackPageBuilder.editSection(${section.id})" title="Edit Section">
                                <i class="ri-settings-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger section-action-btn" onclick="window.GridStackPageBuilder.deleteSection(${section.id})" title="Delete Section">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="section-grid-stack" data-section-grid="${section.id}_main">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Main Content</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="section-grid-stack" data-section-grid="${section.id}_sidebar">
                                    <div class="widget-drop-zone">
                                        <div class="drop-zone-content">
                                            <i class="ri-add-line"></i>
                                            <span>Sidebar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
            default:
                return `
                    <div class="section-content-wrapper">
                        <div class="section-actions">
                            <button class="btn btn-sm btn-outline-secondary section-action-btn" onclick="window.GridStackPageBuilder.editSection(${section.id})" title="Edit Section">
                                <i class="ri-settings-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger section-action-btn" onclick="window.GridStackPageBuilder.deleteSection(${section.id})" title="Delete Section">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <div class="section-grid-stack" data-section-grid="${section.id}">
                            <div class="widget-drop-zone">
                                <div class="drop-zone-content">
                                    <i class="ri-add-line"></i>
                                    <span>Drop widgets here</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
        }
    },

    /**
     * Get default section name based on section type
     */
    getDefaultSectionName(sectionType) {
        const names = {
            'full-width': 'Full Width Section',
            'multi-column': 'Multi Column Section',
            'sidebar-left': 'Sidebar Left Section',
            'sidebar-right': 'Sidebar Right Section'
        };
        return names[sectionType] || 'Custom Section';
    },

    /**
     * Initialize GridStack for a section
     */
    initializeSectionGridStack(sectionId, section, sectionType) {
        try {
            const sectionElement = document.querySelector(`[data-section-id="${sectionId}"]`);
            if (!sectionElement) {
                console.error(`❌ Section element not found for ID: ${sectionId}`);
                return;
            }
            
            // Find all grid containers in this section
            const gridContainers = sectionElement.querySelectorAll('.section-grid-stack');
            
            gridContainers.forEach((container, index) => {
                const gridId = container.getAttribute('data-section-grid');
                
                // Initialize GridStack for this container
                const grid = GridStack.init({
                    column: 12,
                    cellHeight: 80,
                    margin: '10px',
                    float: false,
                    resizable: {
                        handles: 'se, sw'
                    },
                    draggable: {
                        handle: '.widget-controls'
                    }
                }, container);
                
                // Store the GridStack instance
                this.gridStacks[gridId] = grid;
                
                console.log(`✅ Initialized GridStack for ${gridId} (${sectionType})`);
                
                // Load existing widgets for this section
                this.loadSectionWidgets(sectionId, grid);
            });
            
        } catch (error) {
            console.error(`❌ Error initializing GridStack for section ${sectionId}:`, error);
        }
    },

    /**
     * Load widgets for a section from the database
     */
    async loadSectionWidgets(sectionId, grid) {
        try {
            console.log(`🔄 Loading widgets for section ${sectionId}`);
            
            const response = await fetch(`${this.config.apiBaseUrl}/page-sections/${sectionId}/widgets`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                const widgets = data.data || [];
                
                console.log(`📦 Found ${widgets.length} widgets for section ${sectionId}`, widgets);
                
                // Load each widget
                for (const widget of widgets) {
                    await this.loadWidgetIntoGrid(widget, grid);
                }
                
                // Hide drop zone if widgets are loaded
                if (widgets.length > 0) {
                    const dropZone = grid.el.querySelector('.widget-drop-zone');
                    if (dropZone) {
                        dropZone.style.display = 'none';
                    }
                }
                
            } else {
                console.error(`❌ Failed to load widgets for section ${sectionId}:`, response.status);
            }
            
        } catch (error) {
            console.error(`❌ Error loading widgets for section ${sectionId}:`, error);
        }
    },

    /**
     * Load a single widget into the GridStack
     */
    async loadWidgetIntoGrid(widget, grid) {
        try {
            console.log(`🎨 Loading widget ${widget.id} into grid`, widget);
            
            // Create widget HTML using WidgetManager
            if (window.WidgetManager && window.WidgetManager.createWidgetHtml) {
                const widgetHtml = await window.WidgetManager.createWidgetHtml(widget);
                
                console.log(`📄 Widget HTML for ${widget.id}:`, widgetHtml.substring(0, 200) + '...');
                
                // Convert resize_handles array to string for GridStack
                let resizeHandles = 'se, sw'; // default
                if (widget.resize_handles) {
                    if (Array.isArray(widget.resize_handles)) {
                        resizeHandles = widget.resize_handles.join(', ');
                    } else if (typeof widget.resize_handles === 'string') {
                        resizeHandles = widget.resize_handles;
                    }
                }
                
                console.log(`🔧 Resize handles for widget ${widget.id}:`, resizeHandles);
                
                // Add to GridStack with a simple placeholder first
                const widgetElement = grid.addWidget({
                    x: widget.grid_x || 0,
                    y: widget.grid_y || 0,
                    w: widget.grid_w || 6,
                    h: widget.grid_h || 4,
                    id: widget.grid_id || `widget_${widget.id}`,
                    content: '<div class="widget-placeholder">Loading widget...</div>'
                });
                
                // Set data attributes
                widgetElement.setAttribute('data-page-section-widget-id', widget.id);
                widgetElement.setAttribute('data-widget-id', widget.widget_id);
                widgetElement.setAttribute('data-widget-type', widget.settings?.widget_type || 'widget');
                
                // Now replace the content with the actual widget HTML
                const contentElement = widgetElement.querySelector('.grid-stack-item-content');
                if (contentElement) {
                    // Clear the placeholder and set the real HTML
                    contentElement.innerHTML = widgetHtml;
                    console.log(`✅ Widget ${widget.id} HTML content set successfully`);
                } else {
                    console.error(`❌ Could not find content element for widget ${widget.id}`);
                }
                
                console.log(`✅ Widget ${widget.id} loaded into grid`);
                
            } else {
                console.error('❌ WidgetManager not available for loading widget');
            }
            
        } catch (error) {
            console.error(`❌ Error loading widget ${widget.id}:`, error);
        }
    },

    /**
     * Show default placeholder when no sections exist
     */
    showDefaultPlaceholder() {
        const container = document.getElementById('pageSectionsContainer');
        if (container) {
            container.innerHTML = `
                <div class="section-add-zone" id="addFirstSection">
                    <div class="section-add-prompt">
                        <div class="placeholder-content">
                            <div class="placeholder-icon">
                                <i class="ri-layout-masonry-line"></i>
                            </div>
                            <h4>Start Building Your Page</h4>
                            <p>Drag and drop sections from the left sidebar to begin creating your page layout.</p>
                            <div class="placeholder-suggestions">
                                <div class="suggestion-item">
                                    <i class="ri-layout-masonry-line"></i>
                                    <span>Full Width</span>
                                </div>
                                <div class="suggestion-item">
                                    <i class="ri-layout-grid-line"></i>
                                    <span>Multi Column</span>
                                </div>
                                <div class="suggestion-item">
                                    <i class="ri-layout-left-line"></i>
                                    <span>Sidebar Left</span>
                                </div>
                                <div class="suggestion-item">
                                    <i class="ri-layout-right-line"></i>
                                    <span>Sidebar Right</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        this.updateSectionCounter(0);
    },

    /**
     * Hide default placeholder
     */
    hideDefaultPlaceholder() {
        const placeholder = document.getElementById('addFirstSection');
        if (placeholder) {
            placeholder.remove();
        }
    },

    /**
     * Update section counter
     */
    updateSectionCounter(count) {
        const counter = document.getElementById('sectionCount');
        if (counter) {
            counter.textContent = count;
        }
    },
    
    /**
     * Add placeholder widget to section (called from WidgetLibrary)
     */
    addPlaceholderWidgetToSection(sectionGrid, widgetData) {
        const grid = sectionGrid.gridStackInstance;
        if (!grid) {
            console.error('❌ No GridStack instance found for section');
            return;
        }

        console.log('🔧 Adding placeholder widget:', widgetData);

        // Get the widget name (try both label and name properties)
        const widgetName = widgetData.label || widgetData.name || 'Unknown Widget';

        // Create grid item with placeholder content
        const gridItem = {
            x: 0, 
            y: 0, 
            w: 6, 
            h: 3,
            id: `widget-${Date.now()}`,
            content: `<div class="widget-loading">Loading ${widgetName}...</div>`
        };

        // Add to GridStack first
        const addedElement = grid.addWidget(gridItem);
        
        // Set data attributes on the grid item element
        addedElement.setAttribute('data-widget-id', widgetData.id);
        addedElement.setAttribute('data-widget-slug', widgetData.slug);
        addedElement.setAttribute('data-widget-name', widgetName);

        // Now set the innerHTML of the content area
        const contentDiv = addedElement.querySelector('.grid-stack-item-content');
        if (contentDiv) {
            contentDiv.innerHTML = `
                <div class="widget-preview-container" data-widget-type="${widgetData.slug}">
                    <div class="widget-preview-content">
                        <div class="text-center p-3">
                            <i class="ri-apps-line fs-1 text-muted"></i>
                            <h6>${widgetName}</h6>
                            <small class="text-muted">Widget placeholder</small>
                        </div>
                    </div>
                    <div class="widget-preview-overlay">
                        <div class="widget-controls">
                            <button class="widget-control-btn" onclick="window.WidgetManager.editWidget('${addedElement.id}')">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="widget-control-btn" onclick="window.WidgetManager.duplicateWidget('${addedElement.id}')">
                                <i class="ri-file-copy-line"></i>
                            </button>
                            <button class="widget-control-btn" onclick="window.WidgetManager.deleteWidget('${addedElement.id}')">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        console.log('✅ Widget placeholder added to grid:', {
            id: addedElement.id,
            widgetId: widgetData.id,
            widgetName: widgetName,
            slug: widgetData.slug
        });
    },

    /**
     * Select widget (basic implementation for testing)
     */
    selectWidget(element) {
        // Remove previous selection
        document.querySelectorAll('.grid-stack-item-selected').forEach(el => {
            el.classList.remove('grid-stack-item-selected');
        });

        // Add selection to current element
        element.classList.add('grid-stack-item-selected');
        this.selectedWidget = element;

        // Debug: Log all widget data attributes
        const widgetData = {
            id: element.id,
            widgetId: element.getAttribute('data-widget-id'),
            widgetSlug: element.getAttribute('data-widget-slug'),
            widgetName: element.getAttribute('data-widget-name'),
            pageSectionWidgetId: element.getAttribute('data-page-section-widget-id')
        };

        console.log('📌 Widget selected:', widgetData);
    },

    /**
     * Delete a section
     */
    async deleteSection(sectionId) {
        // Store the section ID for the modal
        this.sectionToDelete = sectionId;
        
        // Show the delete confirmation modal
        const modal = new bootstrap.Modal(document.getElementById('deleteSectionModal'));
        modal.show();
    },

    /**
     * Confirm section deletion (called from modal)
     */
    async confirmDeleteSection() {
        if (!this.sectionToDelete) return;

        try {
            console.log(`🗑️ Deleting section ${this.sectionToDelete}...`);
            
            const response = await fetch(`${this.config.apiBaseUrl}/pages/${this.config.pageId}/sections/${this.sectionToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });

            if (response.ok) {
                const result = await response.json();
                console.log('✅ Section deleted successfully:', result);
                
                // Remove the section from the DOM
                const sectionElement = document.querySelector(`[data-section-id="${this.sectionToDelete}"]`);
                if (sectionElement) {
                    sectionElement.remove();
                }
                
                // Reload page content to refresh the view
                this.loadPageContent();
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteSectionModal'));
                modal.hide();
                
            } else {
                const errorData = await response.json();
                console.error('❌ Failed to delete section:', errorData);
                alert('Failed to delete section. Please try again.');
            }
            
        } catch (error) {
            console.error('❌ Error deleting section:', error);
            alert('Error deleting section. Please try again.');
        } finally {
            this.sectionToDelete = null;
        }
    },

    /**
     * Edit a section (placeholder for future implementation)
     */
    editSection(sectionId) {
        console.log(`✏️ Edit section ${sectionId} - Not implemented yet`);
        // TODO: Implement section editing functionality
        alert('Section editing will be implemented in the next phase.');
    }
}; 