/**
 * Page Builder Main Controller
 * 
 * Orchestrates all page builder components including GridStack integration,
 * section management, widget management, and API communication.
 */
class PageBuilderMain {
    constructor(options = {}) {
        this.options = {
            pageId: options.pageId,
            apiBaseUrl: options.apiBaseUrl || '/admin/api',
            csrfToken: options.csrfToken,
            containerId: options.containerId || 'gridStackContainer',
            ...options
        };
        
        // Core components
        this.api = null;
        this.gridManager = null;
        this.sectionManager = null;
        this.widgetManager = null;
        this.widgetLibrary = null;
        this.templateManager = null;
        this.themeManager = null;
        
        // State
        this.initialized = false;
        this.currentSections = [];
        this.isLoading = false;
        
        console.log('🏗️ Page Builder Main Controller initialized with options:', this.options);
    }

    /**
     * Initialize the complete page builder system
     */
    async init() {
        if (this.initialized) {
            console.log('⚠️ Page Builder already initialized');
            return;
        }

        try {
            console.log('🚀 Initializing Page Builder Main Controller...');
            this.isLoading = true;
            
            // Show initial loading state
            this.showGlobalLoader();
            
            // Initialize core API layer
            this.initializeAPI();
            
            // Initialize managers in correct order
            await this.initializeManagers();
            
            // Load initial content
            await this.loadInitialContent();
            
            // Setup global event listeners
            this.setupGlobalEvents();
            
            // Initialize drag & drop zones
            this.setupDropZones();
            
            // Hide loading state
            this.hideGlobalLoader();
            
            this.initialized = true;
            this.isLoading = false;
            
            console.log('✅ Page Builder Main Controller initialized successfully');
            
            // Emit initialization complete event
            document.dispatchEvent(new CustomEvent('pagebuilder:initialized', {
                detail: { pageId: this.options.pageId }
            }));
            
        } catch (error) {
            console.error('❌ Error initializing Page Builder:', error);
            this.isLoading = false;
            this.hideGlobalLoader();
            this.showErrorState(error);
        }
    }

    /**
     * Initialize the API layer
     */
    initializeAPI() {
        console.log('🔗 Initializing API layer...');
        
        this.api = new PageBuilderAPI({
            baseUrl: this.options.apiBaseUrl,
            csrfToken: this.options.csrfToken,
            pageId: this.options.pageId
        });
        
        console.log('✅ API layer initialized');
    }

    /**
     * Initialize all manager components
     */
    async initializeManagers() {
        console.log('🔧 Initializing managers...');
        
        try {
            // Check if all required classes are available
            console.log('🔍 Checking class availability:', {
                SectionManager: typeof window.SectionManager,
                WidgetManager: typeof window.WidgetManager,
                WidgetLibrary: typeof window.WidgetLibrary,
                TemplateManager: typeof window.TemplateManager,
                ThemeManager: typeof window.ThemeManager
            });
            
            // Skip GridStack initialization for now - use simple container approach
            // this.gridManager = new GridManager(`#${this.options.containerId}`);
            // await this.gridManager.initialize({
            //     cellHeight: 70,
            //     margin: 10,
            //     resizable: { handles: 'e, se, s, sw, w' },
            //     draggable: { handle: '.section-header' },
            //     acceptWidgets: true
            // });
            
            // Initialize Section manager (without GridStack for now)
            if (typeof window.SectionManager !== 'function') {
                throw new Error('SectionManager class not available');
            }
            this.sectionManager = new SectionManager(this.api, null);
            
            // Initialize Widget manager
            if (typeof window.WidgetManager !== 'function') {
                throw new Error('WidgetManager class not available');
            }
            this.widgetManager = new WidgetManager(this.api, this.sectionManager);
            
            // Initialize Widget library
            if (typeof window.WidgetLibrary !== 'function') {
                throw new Error('WidgetLibrary class not available');
            }
            this.widgetLibrary = new WidgetLibrary(this.api);
            await this.widgetLibrary.init();
            
            // Initialize Template manager
            if (typeof window.TemplateManager !== 'function') {
                throw new Error('TemplateManager class not available');
            }
            this.templateManager = new TemplateManager(this.api, this.sectionManager);
            await this.templateManager.init();
            
            // Initialize Theme manager
            if (typeof window.ThemeManager !== 'function') {
                throw new Error('ThemeManager class not available');
            }
            this.themeManager = new ThemeManager(this.api);
            await this.themeManager.init();
            
            console.log('✅ All managers initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing managers:', error);
            throw error;
        }
    }

    /**
     * Load initial content (sections and widgets)
     */
    async loadInitialContent() {
        if (!this.options.pageId) {
            console.log('📭 No page ID provided, showing empty state');
            this.showEmptyPageState();
            return;
        }

        try {
            console.log('🔄 Loading initial page content with rendered HTML for page ID:', this.options.pageId);
            
            // Load complete rendered page (sections + widgets + theme assets)
            console.log('🎨 Loading rendered page content...');
            const renderedPageData = await this.api.getRenderedPage();
            
            if (!renderedPageData.success) {
                throw new Error(renderedPageData.error || 'Failed to load rendered page');
            }
            
            const { page, sections, theme_assets } = renderedPageData.data;
            this.currentSections = sections;
            
            console.log(`🎨 Loaded rendered page:`, page);
            console.log(`📋 Found ${sections.length} rendered sections:`, sections);
            console.log(`🎯 Theme assets:`, theme_assets);
            
            if (sections.length === 0) {
                console.log('📭 No sections found, showing empty state');
                this.showEmptyPageState();
                return;
            }

            // Load theme assets first (like Live Preview)
            await this.loadThemeAssets(theme_assets);

            // Render sections with real HTML content to GridStack
            console.log('🎨 Rendering sections with real content to GridStack...');
            sections.forEach(section => {
                console.log('🎨 Adding rendered section to grid:', section.id, section.template_section?.name);
                this.addRenderedSectionToGrid(section);
            });
            
            console.log(`✅ Loaded ${sections.length} sections with real rendered content`);
            
        } catch (error) {
            console.error('❌ Error loading initial content:', error);
            console.error('❌ Error details:', {
                message: error.message,
                stack: error.stack,
                apiConfig: {
                    baseUrl: this.api?.baseUrl,
                    pageId: this.options.pageId,
                    csrfToken: this.options.csrfToken ? 'Present' : 'Missing'
                }
            });
            this.showEmptyPageState();
        }
    }

    /**
     * Setup global event listeners
     */
    setupGlobalEvents() {
        console.log('🎧 Setting up global event listeners...');
        
        // GridStack events
        document.addEventListener('pagebuilder:section-position-changed', (e) => {
            this.handleSectionPositionChanged(e.detail);
        });
        
        // Section events
        document.addEventListener('pagebuilder:section-created', (e) => {
            this.handleSectionCreated(e.detail);
        });
        
        document.addEventListener('pagebuilder:section-deleted', (e) => {
            this.handleSectionDeleted(e.detail);
        });
        
        // Widget events
        document.addEventListener('pagebuilder:widget-dropped', (e) => {
            this.handleWidgetDropped(e.detail);
        });
        
        document.addEventListener('pagebuilder:widget-deleted', (e) => {
            this.handleWidgetDeleted(e.detail);
        });
        
        // Template events
        document.addEventListener('pagebuilder:template-dropped', (e) => {
            this.handleTemplateDropped(e.detail);
        });
        
        console.log('✅ Global event listeners setup complete');
    }

    /**
     * Setup drop zones for widgets and templates
     */
    setupDropZones() {
        console.log('🎯 Setting up drop zones...');
        
        const container = document.getElementById(this.options.containerId);
        if (!container) return;
        
        // Global drop handler for the grid container
        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });
        
        container.addEventListener('drop', (e) => {
            e.preventDefault();
            this.handleGlobalDrop(e);
        });
        
        console.log('✅ Drop zones setup complete');
    }

    /**
     * Handle global drop events
     */
    handleGlobalDrop(e) {
        try {
            const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
            console.log('🎯 Global drop detected:', dragData);
            
            if (dragData.type === 'template') {
                // Template dropped - create new section
                this.handleTemplateDropOnCanvas(dragData, e);
            } else if (dragData.type === 'widget') {
                // Widget dropped - find nearest section or create section
                this.handleWidgetDropOnCanvas(dragData, e);
            }
            
        } catch (error) {
            console.error('❌ Error handling global drop:', error);
        }
    }

    /**
     * Handle template drop on canvas
     */
    async handleTemplateDropOnCanvas(dragData, dropEvent) {
        try {
            console.log('📋 Creating section from template:', dragData.templateKey);
            
            // Calculate grid position from drop coordinates
            const gridPos = this.calculateGridPosition(dropEvent);
            
            // Create section using template manager
            const newSection = await this.templateManager.createSectionFromTemplate(
                dragData.templateKey, 
                gridPos
            );
            
            if (newSection) {
                this.currentSections.push(newSection);
                console.log('✅ Section created from template successfully');
            }
            
        } catch (error) {
            console.error('❌ Error creating section from template:', error);
            alert('Failed to create section. Please try again.');
        }
    }

    /**
     * Handle widget drop on canvas
     */
    async handleWidgetDropOnCanvas(dragData, dropEvent) {
        try {
            console.log('🧩 Widget dropped on canvas:', dragData.widgetId);
            
            // Find the nearest section or create a default one
            let targetSection = this.findNearestSection(dropEvent);
            
            if (!targetSection && this.currentSections.length === 0) {
                // No sections exist, create a default section first
                targetSection = await this.createDefaultSection();
            }
            
            if (targetSection) {
                // Add widget to the section
                await this.widgetManager.handleWidgetDrop(
                    targetSection.id,
                    dragData.widgetId,
                    this.calculateGridPosition(dropEvent)
                );
            } else {
                console.warn('⚠️ No target section found for widget drop');
            }
            
        } catch (error) {
            console.error('❌ Error handling widget drop on canvas:', error);
            alert('Failed to add widget. Please try again.');
        }
    }

    /**
     * Calculate grid position from drop coordinates
     */
    calculateGridPosition(dropEvent) {
        const container = document.getElementById(this.options.containerId);
        if (!container) return { x: 0, y: 0, w: 12, h: 4 };
        
        const containerRect = container.getBoundingClientRect();
        const relativeX = dropEvent.clientX - containerRect.left;
        const relativeY = dropEvent.clientY - containerRect.top;
        
        // Convert to grid coordinates (rough calculation)
        const gridX = Math.floor((relativeX / containerRect.width) * 12);
        const gridY = Math.floor(relativeY / 80); // Assuming ~80px per grid row
        
        return {
            x: Math.max(0, Math.min(gridX, 11)),
            y: Math.max(0, gridY),
            w: 12,
            h: 4
        };
    }

    /**
     * Find nearest section to drop coordinates
     */
    findNearestSection(dropEvent) {
        // For now, just return the first section or null
        return this.currentSections.length > 0 ? this.currentSections[0] : null;
    }

    /**
     * Create a default section when none exist
     */
    async createDefaultSection() {
        try {
            console.log('🏗️ Creating default section...');
            
            // Use template manager to create a basic section
            const defaultSection = await this.templateManager.createSectionFromTemplate(
                'full-width', // Assuming this template exists
                { x: 0, y: 0, w: 12, h: 4 }
            );
            
            if (defaultSection) {
                this.currentSections.push(defaultSection);
                return defaultSection;
            }
            
        } catch (error) {
            console.error('❌ Error creating default section:', error);
        }
        
        return null;
    }

    /**
     * Event Handlers
     */
    async handleSectionPositionChanged(detail) {
        await this.sectionManager.updateSectionPosition(detail.sectionId, detail.position);
    }

    handleSectionCreated(detail) {
        this.currentSections.push(detail.section);
        this.hideEmptyPageState();
    }

    handleSectionDeleted(detail) {
        this.currentSections = this.currentSections.filter(s => s.id !== detail.sectionId);
        if (this.currentSections.length === 0) {
            this.showEmptyPageState();
        }
    }

    async handleWidgetDropped(detail) {
        await this.widgetManager.handleWidgetDrop(detail.sectionId, detail.widgetId, detail.position);
    }

    handleWidgetDeleted(detail) {
        // Widget deletion is handled by widget manager
        console.log('🧩 Widget deleted:', detail.widgetId);
    }

    async handleTemplateDropped(detail) {
        const newSection = await this.templateManager.createSectionFromTemplate(
            detail.templateKey, 
            detail.position
        );
        
        if (newSection) {
            this.currentSections.push(newSection);
        }
    }

    /**
     * UI State Management
     */
    showGlobalLoader() {
        const container = document.getElementById(this.options.containerId);
        if (container) {
            container.innerHTML = `
                <div class="page-builder-loader text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Initializing Page Builder...</h5>
                    <p class="text-muted">Setting up components and loading content...</p>
                </div>
            `;
        }
    }

    hideGlobalLoader() {
        const loader = document.querySelector('.page-builder-loader');
        if (loader) {
            loader.remove();
        }
    }

    showEmptyPageState() {
        // Clear the grid container
        const container = document.getElementById(this.options.containerId);
        if (container) {
            container.innerHTML = '';
        }
        
        // Show the empty canvas state
        const emptyState = document.getElementById('emptyCanvasState');
        if (emptyState) {
            emptyState.style.display = 'block';
        }
        
        // Update counters
        this.updatePageStats();
    }

    hideEmptyPageState() {
        const emptyState = document.querySelector('.grid-empty-state');
        if (emptyState) {
            emptyState.remove();
        }
    }

    showErrorState(error) {
        const container = document.getElementById(this.options.containerId);
        if (container) {
            container.innerHTML = `
                <div class="page-builder-error text-center py-5">
                    <i class="ri-error-warning-line text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-danger">Page Builder Error</h5>
                    <p class="text-muted mb-3">${error.message || 'An unexpected error occurred'}</p>
                    <button class="btn btn-primary" onclick="window.pageBuilder?.init()">
                        <i class="ri-refresh-line me-2"></i>Try Again
                    </button>
                </div>
            `;
        }
    }

    /**
     * Update page statistics (matching old implementation)
     */
    updatePageStats() {
        const sectionCount = this.currentSections.length;
        let widgetCount = 0; // For now, we'll update this later
        
        // Update counters in the UI
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
        
        console.log(`📊 Page Stats: ${sectionCount} sections, ${widgetCount} widgets`);
    }

    /**
     * Public API Methods
     */
    
    /**
     * Refresh the entire page builder
     */
    async refresh() {
        console.log('🔄 Refreshing Page Builder...');
        
        // Clear container
        const container = document.getElementById(this.options.containerId);
        if (container) {
            container.innerHTML = '';
        }
        
        this.currentSections = [];
        
        await this.loadInitialContent();
    }

    /**
     * Get current page data
     */
    getPageData() {
        return {
            pageId: this.options.pageId,
            sections: this.currentSections,
            gridStats: this.gridManager?.getGridStats()
        };
    }

    /**
     * Check if page builder is ready
     */
    isReady() {
        return this.initialized && !this.isLoading;
    }

    // =====================================================================
    // HYBRID: RENDERED CONTENT METHODS (Live Preview Integration)
    // =====================================================================

    /**
     * Load theme assets for proper styling (like Live Preview)
     */
    async loadThemeAssets(themeAssets) {
        if (!themeAssets || (!themeAssets.css && !themeAssets.js)) {
            console.log('🎯 No theme assets to load');
            return;
        }

        console.log('🎯 Loading theme assets:', themeAssets);
        
        const assetsContainer = document.getElementById('themeAssetsContainer');
        if (!assetsContainer) {
            console.warn('⚠️ Theme assets container not found');
            return;
        }

        let assetsHTML = '';

        // Load CSS assets
        if (themeAssets.css && themeAssets.css.length > 0) {
            themeAssets.css.forEach(cssFile => {
                const cssUrl = cssFile.startsWith('http') ? cssFile : `${themeAssets.base_path}/${cssFile}`;
                assetsHTML += `<link rel="stylesheet" href="${cssUrl}" data-theme-asset="css">\n`;
            });
        }

        // Load JS assets
        if (themeAssets.js && themeAssets.js.length > 0) {
            themeAssets.js.forEach(jsFile => {
                const jsUrl = jsFile.startsWith('http') ? jsFile : `${themeAssets.base_path}/${jsFile}`;
                assetsHTML += `<script src="${jsUrl}" data-theme-asset="js"></script>\n`;
            });
        }

        assetsContainer.innerHTML = assetsHTML;
        console.log('✅ Theme assets loaded');
    }

    /**
     * Add rendered section to GridStack with real HTML content
     */
    addRenderedSectionToGrid(section) {
        if (!this.gridManager || !this.gridManager.grid) {
            console.warn('⚠️ GridStack not available, using fallback rendering');
            this.fallbackRenderSection(section);
            return;
        }

        console.log('🎨 Adding rendered section to grid:', section.id);

        // Create GridStack widget with real HTML content
        const gridWidget = {
            x: section.grid_position.x,
            y: section.grid_position.y,
            w: section.grid_position.w,
            h: section.grid_position.h,
            id: `section-${section.id}`,
            content: this.wrapSectionForGrid(section)
        };

        try {
            this.gridManager.grid.addWidget(gridWidget);
            console.log('✅ Section added to GridStack:', section.id);
        } catch (error) {
            console.error('❌ Failed to add section to GridStack:', error);
            this.fallbackRenderSection(section);
        }
    }

    /**
     * Wrap rendered section HTML for GridStack compatibility
     */
    wrapSectionForGrid(section) {
        const sectionName = section.template_section?.name || `Section ${section.id}`;
        const sectionType = section.template_section?.type || 'default';
        
        return `
            <div class="grid-section-wrapper" data-section-id="${section.id}" data-section-type="${sectionType}">
                <div class="section-controls">
                    <div class="section-info">
                        <span class="section-name">${sectionName}</span>
                        <span class="widget-count">${section.widgets?.length || 0} widgets</span>
                    </div>
                    <div class="section-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="pageBuilder.editSection(${section.id})" title="Edit Section">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="pageBuilder.deleteSection(${section.id})" title="Delete Section">
                            <i class="ri-delete-line"></i>
                        </button>
                    </div>
                </div>
                <div class="section-content">
                    ${section.rendered_html || '<div class="empty-section">No content</div>'}
                </div>
            </div>
        `;
    }

    /**
     * Fallback rendering when GridStack is not available
     */
    fallbackRenderSection(section) {
        console.log('🔧 Using fallback rendering for section:', section.id);
        
        const container = document.getElementById(this.options.containerId);
        if (!container) {
            console.error('❌ Container not found for fallback rendering');
            return;
        }

        const sectionElement = document.createElement('div');
        sectionElement.className = 'fallback-section';
        sectionElement.innerHTML = this.wrapSectionForGrid(section);
        
        container.appendChild(sectionElement);
    }

    /**
     * Edit section (will be connected to modal)
     */
    editSection(sectionId) {
        console.log('✏️ Edit section:', sectionId);
        // This will be connected to the section config modal
        document.dispatchEvent(new CustomEvent('pagebuilder:edit-section', {
            detail: { sectionId }
        }));
    }

    /**
     * Delete section
     */
    async deleteSection(sectionId) {
        if (!confirm('Are you sure you want to delete this section?')) {
            return;
        }

        console.log('🗑️ Delete section:', sectionId);
        
        try {
            await this.sectionManager.deleteSection(sectionId);
            
            // Remove from current sections
            this.currentSections = this.currentSections.filter(s => s.id !== sectionId);
            
            // Remove from grid
            if (this.gridManager?.grid) {
                this.gridManager.grid.removeWidget(`#section-${sectionId}`);
            }
            
            console.log('✅ Section deleted successfully');
            
        } catch (error) {
            console.error('❌ Failed to delete section:', error);
            alert('Failed to delete section. Please try again.');
        }
    }
}

// Export for global use
window.PageBuilderMain = PageBuilderMain;

console.log('📦 Page Builder Main Controller loaded');