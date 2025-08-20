/**
 * GridStack Page Builder - Phase 3
 * Simple implementation to load and display existing sections and widgets
 */
window.GridStackPageBuilder = {
    config: {},
    gridStacks: {},
    initialized: false,

    /**
     * Initialize the page builder
     */
    async init(config) {
        if (this.initialized) {
            console.log('🔄 GridStack Page Builder already initialized');
            return;
        }

        console.log('🚀 Initializing GridStack Page Builder Phase 3...');
        this.config = config;
        
        try {
            // Show loading state
            this.showPageLoader();
            
            // Initialize sidebar toggle functionality
            this.initializeSidebarToggle();
            
            // Load page content
            if (this.config && this.config.pageId) {
                await this.loadPageContent();
            } else {
                console.warn('⚠️ No page ID provided');
                this.showDefaultPlaceholder();
            }
            
            // Hide loading state
            this.hidePageLoader();
            
            this.initialized = true;
            console.log('✅ GridStack Page Builder initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing GridStack Page Builder:', error);
            this.hidePageLoader();
            this.showDefaultPlaceholder();
        }
    },

    /**
     * Show page loader
     */
    showPageLoader() {
        // Hide empty state during loading
        this.hideDefaultPlaceholder();
        
        const container = document.getElementById('gridStackContainer');
        if (container) {
            container.innerHTML = `
                <div class="page-loader text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Loading Page Content...</h5>
                    <p class="text-muted">Please wait while we load your page sections</p>
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
     * Show default placeholder when no sections exist
     */
    showDefaultPlaceholder() {
        // Clear the grid container
        const container = document.getElementById('gridStackContainer');
        if (container) {
            container.innerHTML = '';
        }
        
        // Show the empty canvas state
        const emptyState = document.getElementById('emptyCanvasState');
        if (emptyState) {
            emptyState.style.display = 'block';
        }
        
        // Update counters
        this.updateCounters(0, 0);
    },

    /**
     * Hide empty state placeholder
     */
    hideDefaultPlaceholder() {
        const emptyState = document.getElementById('emptyCanvasState');
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    },

    /**
     * Load page content from API
     */
    async loadPageContent() {
        if (!this.config.pageId) {
            console.warn('⚠️ No page ID provided');
            return;
        }

        try {
            console.log(`🔄 Loading page content for page ID: ${this.config.pageId}`);
            
            const apiUrl = `${this.config.apiBaseUrl}/pages/${this.config.pageId}/sections`;
            console.log(`📡 API URL: ${apiUrl}`);

            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                }
            });

            console.log(`📡 Response status: ${response.status}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('📡 API Response:', data);

            if (data.success && data.data && data.data.length > 0) {
                console.log(`📦 Found ${data.data.length} sections`);
                await this.renderSections(data.data);
            } else {
                console.log('📭 No sections found');
                this.showDefaultPlaceholder();
            }

        } catch (error) {
            console.error('❌ Error loading page content:', error);
            this.showDefaultPlaceholder();
        }
    },

    /**
     * Render sections from API data
     */
    async renderSections(sections) {
        const container = document.getElementById('gridStackContainer');
        if (!container) {
            console.error('❌ GridStack container not found');
            return;
        }

        // Hide empty state since we have sections to show
        this.hideDefaultPlaceholder();

        // Clear container
        container.innerHTML = '';

        let totalWidgets = 0;

        // Render each section
        for (const [index, section] of sections.entries()) {
            console.log(`🎨 Rendering section ${section.id}:`, section);
            
            const sectionElement = await this.renderSection(section, index);
            container.appendChild(sectionElement);
            
            // Load widgets for this section
            const widgetCount = await this.loadSectionWidgets(section.id, sectionElement);
            totalWidgets += widgetCount;
        }

        // Update counters
        this.updateCounters(sections.length, totalWidgets);

        console.log(`✅ Rendered ${sections.length} sections with ${totalWidgets} total widgets`);
    },

    /**
     * Render a single section
     */
    async renderSection(section, index) {
        const sectionType = section.template_section?.section_type || 'full-width';
        
        console.log(`🎨 Rendering section ${section.id} of type: ${sectionType}`);

        // Create section wrapper
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'page-section mb-4';
        sectionDiv.setAttribute('data-section-id', section.id);
        sectionDiv.setAttribute('data-section-type', sectionType);

        // Create section content based on type
        sectionDiv.innerHTML = this.getSectionHTML(section, sectionType);

        return sectionDiv;
    },

    /**
     * Get section HTML based on section type
     */
    getSectionHTML(section, sectionType) {
        const sectionName = section.template_section?.name || 'Section';
        
        switch (sectionType) {
            case 'full-width':
                return `
                    <div class="section-header border-bottom pb-2 mb-3">
                        <h6 class="mb-0">
                            <i class="ri-layout-masonry-line me-2"></i>
                            ${sectionName} (Full Width)
                        </h6>
                    </div>
                    <div class="section-grid" data-section-grid="${section.id}">
                        <!-- Widgets will be loaded here -->
                    </div>
                `;
                
            case 'multi-column':
                return `
                    <div class="section-header border-bottom pb-2 mb-3">
                        <h6 class="mb-0">
                            <i class="ri-layout-grid-line me-2"></i>
                            ${sectionName} (Multi Column)
                        </h6>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="section-grid" data-section-grid="${section.id}_col1">
                                <div class="text-muted small mb-2">Column 1</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="section-grid" data-section-grid="${section.id}_col2">
                                <div class="text-muted small mb-2">Column 2</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="section-grid" data-section-grid="${section.id}_col3">
                                <div class="text-muted small mb-2">Column 3</div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'sidebar-left':
                return `
                    <div class="section-header border-bottom pb-2 mb-3">
                        <h6 class="mb-0">
                            <i class="ri-layout-left-line me-2"></i>
                            ${sectionName} (Sidebar Left)
                        </h6>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="section-grid" data-section-grid="${section.id}_sidebar">
                                <div class="text-muted small mb-2">Sidebar</div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="section-grid" data-section-grid="${section.id}_main">
                                <div class="text-muted small mb-2">Main Content</div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'sidebar-right':
                return `
                    <div class="section-header border-bottom pb-2 mb-3">
                        <h6 class="mb-0">
                            <i class="ri-layout-right-line me-2"></i>
                            ${sectionName} (Sidebar Right)
                        </h6>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="section-grid" data-section-grid="${section.id}_main">
                                <div class="text-muted small mb-2">Main Content</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="section-grid" data-section-grid="${section.id}_sidebar">
                                <div class="text-muted small mb-2">Sidebar</div>
                            </div>
                        </div>
                    </div>
                `;
                
            default:
                return `
                    <div class="section-header border-bottom pb-2 mb-3">
                        <h6 class="mb-0">
                            <i class="ri-layout-line me-2"></i>
                            ${sectionName}
                        </h6>
                    </div>
                    <div class="section-grid" data-section-grid="${section.id}">
                        <!-- Widgets will be loaded here -->
                    </div>
                `;
        }
    },

    /**
     * Load widgets for a section
     */
    async loadSectionWidgets(sectionId, sectionElement) {
        try {
            console.log(`🔄 Loading widgets for section ${sectionId}`);
            
            const apiUrl = `${this.config.apiBaseUrl}/page-sections/${sectionId}/widgets`;
            
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.warn(`⚠️ Failed to load widgets for section ${sectionId}: ${response.status}`);
                return 0;
            }

            const data = await response.json();
            const widgets = data.data || [];
            
            console.log(`📦 Found ${widgets.length} widgets for section ${sectionId}:`, widgets);

            // Render widgets in section grids
            await this.renderSectionWidgets(widgets, sectionElement);
            
            return widgets.length;

        } catch (error) {
            console.error(`❌ Error loading widgets for section ${sectionId}:`, error);
            return 0;
        }
    },

    /**
     * Render widgets within a section
     */
    async renderSectionWidgets(widgets, sectionElement) {
        const sectionGrids = sectionElement.querySelectorAll('.section-grid');
        
        if (widgets.length === 0) {
            // Show empty state for each grid
            sectionGrids.forEach(grid => {
                const emptyMsg = document.createElement('div');
                emptyMsg.className = 'text-muted text-center py-3 border border-dashed rounded';
                emptyMsg.innerHTML = '<i class="ri-apps-line me-2"></i>No widgets';
                grid.appendChild(emptyMsg);
            });
            return;
        }

        // For now, just add all widgets to the first/main grid
        // TODO: In future phases, we'll properly distribute based on grid positioning
        const mainGrid = sectionGrids[0];
        if (!mainGrid) return;

        widgets.forEach((widget, index) => {
            const widgetElement = this.createWidgetElement(widget, index);
            mainGrid.appendChild(widgetElement);
        });
    },

    /**
     * Create widget element for display
     */
    createWidgetElement(widget, index) {
        const widgetDiv = document.createElement('div');
        widgetDiv.className = 'widget-item border rounded p-3 mb-2';
        widgetDiv.setAttribute('data-widget-id', widget.id);
        widgetDiv.setAttribute('data-widget-type', widget.widget?.slug || 'unknown');

        widgetDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="widget-icon me-3">
                    <i class="ri-apps-line fs-4 text-primary"></i>
                </div>
                <div class="widget-info flex-fill">
                    <h6 class="mb-1">${widget.widget?.name || 'Unknown Widget'}</h6>
                    <small class="text-muted">
                        Position: ${widget.grid_x || 0}, ${widget.grid_y || 0} | 
                        Size: ${widget.grid_w || 'auto'}x${widget.grid_h || 'auto'}
                    </small>
                </div>
                <div class="widget-status">
                    <span class="badge bg-success">Loaded</span>
                </div>
            </div>
        `;

        return widgetDiv;
    },

    /**
     * Update section and widget counters
     */
    updateCounters(sectionCount, widgetCount) {
        const sectionCounter = document.getElementById('sectionCount');
        const widgetCounter = document.getElementById('widgetCount');
        const loadingStatus = document.getElementById('loadingStatus');

        if (sectionCounter) {
            sectionCounter.textContent = sectionCount;
        }
        
        if (widgetCounter) {
            widgetCounter.textContent = widgetCount;
        }
        
        if (loadingStatus) {
            if (sectionCount > 0) {
                loadingStatus.textContent = '• Loaded';
                loadingStatus.className = 'text-success ms-2';
            } else {
                loadingStatus.textContent = '• Empty';
                loadingStatus.className = 'text-warning ms-2';
            }
        }
    },

    /**
     * Initialize sidebar toggle functionality
     */
    initializeSidebarToggle() {
        const toggleBtn = document.getElementById('toggleLeftSidebarBtn');
        const leftSidebarContainer = document.getElementById('leftSidebarContainer');
        
        if (toggleBtn && leftSidebarContainer) {
            // Set up toggle button click event
            toggleBtn.addEventListener('click', () => {
                leftSidebarContainer.classList.toggle('collapsed');
                
                // Update button icon and title
                const icon = toggleBtn.querySelector('i');
                if (leftSidebarContainer.classList.contains('collapsed')) {
                    icon.className = 'ri-sidebar-unfold-line';
                    toggleBtn.title = 'Expand Widget Library';
                    console.log('✅ Left sidebar collapsed');
                } else {
                    icon.className = 'ri-apps-line';
                    toggleBtn.title = 'Toggle Widget Library';
                    console.log('✅ Left sidebar expanded');
                }
            });

            // Set up collapsed icon click events (to expand categories when collapsed)
            const collapsedIcons = document.querySelectorAll('.collapsed-icon-item');
            collapsedIcons.forEach(icon => {
                icon.addEventListener('click', (e) => {
                    // If sidebar is collapsed, expand it first
                    if (leftSidebarContainer.classList.contains('collapsed')) {
                        leftSidebarContainer.classList.remove('collapsed');
                        
                        // Update toggle button
                        const toggleIcon = toggleBtn.querySelector('i');
                        toggleIcon.className = 'ri-apps-line';
                        toggleBtn.title = 'Toggle Widget Library';
                        
                        // Then expand the target section
                        setTimeout(() => {
                            const targetId = icon.getAttribute('data-target');
                            const targetCollapse = document.querySelector(targetId);
                            const targetLink = document.querySelector(`[href="${targetId}"]`);
                            
                            if (targetCollapse && targetLink) {
                                // Expand the section
                                targetCollapse.classList.add('show');
                                targetLink.classList.remove('collapsed');
                                targetLink.setAttribute('aria-expanded', 'true');
                            }
                        }, 300); // Wait for sidebar animation
                        
                        console.log('✅ Left sidebar expanded via collapsed icon');
                    }
                });
            });

            console.log('✅ Sidebar toggle functionality initialized');
        } else {
            console.warn('⚠️ Sidebar toggle elements not found');
        }
    }
};

// Debug helper
window.debugGridStack = function() {
    console.log('🔍 GridStack Debug Info:', {
        initialized: window.GridStackPageBuilder.initialized,
        config: window.GridStackPageBuilder.config,
        gridStacks: Object.keys(window.GridStackPageBuilder.gridStacks)
    });
};