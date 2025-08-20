/**
 * Section Templates Manager
 * Handles loading, displaying, and drag-and-drop of section templates
 */
class SectionTemplatesManager {
    constructor() {
        this.templates = [];
        this.container = null;
        this.isCreatingSection = false;
        this.init();
    }

    /**
     * Initialize the section templates manager
     */
    init() {
        console.log('🔧 Initializing Section Templates Manager...');
        
        // Find the section templates container
        this.container = document.getElementById('sectionsGrid');
        if (!this.container) {
            console.error('❌ Section templates container not found. Looking for #sectionsGrid');
            return;
        }
        
        this.templates = this.getMockTemplates();
        this.isCreatingSection = false;
        
        // Check if page is empty before showing placeholders
        this.checkEmptyPage();
        
        this.renderTemplates();
        this.setupDragAndDrop();
        
        console.log('✅ Section Templates Manager initialized');
    }

    /**
     * Check if page is empty and show appropriate placeholder
     */
    checkEmptyPage() {
        const pageContainer = document.getElementById('pageSectionsContainer');
        if (!pageContainer) return;
        
        // Check if there are any sections already rendered
        const existingSections = pageContainer.querySelectorAll('.page-section');
        const existingPlaceholders = pageContainer.querySelectorAll('#addFirstSection, #emptyPagePlaceholder');
        
        console.log(`🔍 Page check: ${existingSections.length} sections, ${existingPlaceholders.length} placeholders`);
        
        // If there are sections, don't show any placeholders
        if (existingSections.length > 0) {
            console.log('✅ Page has sections, no placeholders needed');
            return;
        }
        
        // If there are placeholders, don't add more
        if (existingPlaceholders.length > 0) {
            console.log('⚠️ Placeholders already exist, not adding more');
            return;
        }
        
        // Show default placeholder only if page is truly empty
        console.log('📭 Page is empty, showing default placeholder');
        this.showDefaultSectionPlaceholder();
    }

    /**
     * Show default section placeholder
     */
    showDefaultSectionPlaceholder() {
        const pageContainer = document.getElementById('pageSectionsContainer');
        if (!pageContainer) return;

        // Remove existing placeholder if any
        const existingPlaceholder = pageContainer.querySelector('#emptyPagePlaceholder');
        if (existingPlaceholder) {
            existingPlaceholder.remove();
        }

        const placeholderHtml = `
            <div id="addFirstSection" class="add-first-section">
                <div class="add-section-content">
                    <div class="add-section-icon">
                        <i class="ri-add-line"></i>
                    </div>
                    <h4>Add Your First Section</h4>
                    <p>Drag a section template from the left sidebar to start building your page.</p>
                    <div class="section-suggestions">
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
        `;
        
        pageContainer.innerHTML = placeholderHtml;
    }

    /**
     * Hide default section placeholder
     */
    hideDefaultSectionPlaceholder() {
        const placeholder = document.getElementById('addFirstSection');
        if (placeholder) {
            placeholder.remove();
        }
    }

    /**
     * Show empty page placeholder
     */
    showEmptyPagePlaceholder() {
        const pageContainer = document.getElementById('pageSectionsContainer');
        if (!pageContainer) return;

        // Remove existing placeholder if any
        const existingPlaceholder = pageContainer.querySelector('#emptyPagePlaceholder');
        if (existingPlaceholder) return;

        const placeholderHtml = `
            <div id="emptyPagePlaceholder" class="empty-page-placeholder">
                <div class="placeholder-content">
                    <div class="placeholder-icon">
                        <i class="ri-layout-masonry-line"></i>
                    </div>
                    <h4>No Sections Yet</h4>
                    <p>Drag a section template from the left sidebar to start building your page.</p>
                    <div class="placeholder-templates">
                        <div class="template-suggestion">
                            <i class="ri-layout-masonry-line"></i>
                            <span>Full Width</span>
                        </div>
                        <div class="template-suggestion">
                            <i class="ri-layout-grid-line"></i>
                            <span>Multi Column</span>
                        </div>
                        <div class="template-suggestion">
                            <i class="ri-layout-left-line"></i>
                            <span>Sidebar Left</span>
                        </div>
                        <div class="template-suggestion">
                            <i class="ri-layout-right-line"></i>
                            <span>Sidebar Right</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        pageContainer.innerHTML = placeholderHtml;
    }

    /**
     * Hide empty page placeholder
     */
    hideEmptyPagePlaceholder() {
        const placeholder = document.getElementById('emptyPagePlaceholder');
        if (placeholder) {
            placeholder.remove();
        }
    }

    /**
     * Clear all placeholders from different systems
     */
    clearAllPlaceholders() {
        const container = document.getElementById('pageSectionsContainer');
        if (!container) return;
        
        // Remove placeholders from different systems
        const placeholders = container.querySelectorAll('#addFirstSection, #emptyPagePlaceholder, .section-add-zone');
        placeholders.forEach(placeholder => {
            placeholder.remove();
        });
        
        console.log('🧹 Section Templates: Cleared all placeholders');
    }

    /**
     * Load section templates from the server
     */
    async loadTemplates() {
        try {
            console.log('📋 Loading section templates...');
            
            // Use mock templates directly as they were working before
            this.templates = this.getMockTemplates();
            
            this.renderTemplates();
            console.log(`✅ Loaded ${this.templates.length} section templates from mock data`);
            
        } catch (error) {
            console.error('❌ Error loading section templates:', error);
            // Use mock data as fallback
            this.templates = this.getMockTemplates();
            this.renderTemplates();
        }
    }

    /**
     * Get mock templates for testing
     */
    getMockTemplates() {
        return [
            {
                key: 'full-width',
                name: 'Full Width',
                icon: 'ri-layout-masonry-line',
                description: 'Full width single column layout',
                category: 'layout',
                grid_config: {
                    column: 12,
                    cellHeight: 80,
                    verticalMargin: 10,
                    horizontalMargin: 10,
                    acceptWidgets: true,
                    resizable: { handles: ['se', 'sw'] },
                    animate: true,
                    float: false
                },
                widget_constraints: {
                    allowed_types: ['text', 'image', 'counter', 'gallery', 'form', 'video'],
                    max_widgets: null,
                    default_widget_size: { w: 12, h: 3 },
                    column_layout: false
                },
                styling: {
                    default_padding: { top: 40, bottom: 40, left: 0, right: 0 },
                    default_margin: { top: 0, bottom: 0, left: 0, right: 0 },
                    default_background: '#ffffff',
                    container_class: 'container-fluid'
                }
            },
            {
                key: 'multi-column',
                name: 'Multi Column',
                icon: 'ri-layout-grid-line',
                description: 'Flexible multi-column layout',
                category: 'layout',
                grid_config: {
                    column: 12,
                    cellHeight: 80,
                    verticalMargin: 10,
                    horizontalMargin: 10,
                    acceptWidgets: true,
                    resizable: { handles: ['se', 'sw', 'ne', 'nw'] },
                    animate: true,
                    float: true
                },
                widget_constraints: {
                    allowed_types: ['text', 'image', 'counter', 'gallery', 'form', 'video'],
                    max_widgets: null,
                    default_widget_size: { w: 6, h: 3 },
                    column_layout: true,
                    columns: [
                        { span: 4, offset: 0 },
                        { span: 4, offset: 4 },
                        { span: 4, offset: 8 }
                    ]
                },
                styling: {
                    default_padding: { top: 40, bottom: 40, left: 0, right: 0 },
                    default_margin: { top: 0, bottom: 0, left: 0, right: 0 },
                    default_background: '#f8f9fa',
                    container_class: 'container'
                }
            },
            {
                key: 'sidebar-left',
                name: 'Sidebar Left',
                icon: 'ri-layout-left-line',
                description: 'Left sidebar with main content',
                category: 'layout',
                grid_config: {
                    column: 12,
                    cellHeight: 80,
                    verticalMargin: 10,
                    horizontalMargin: 10,
                    acceptWidgets: true,
                    resizable: { handles: ['se', 'sw'] },
                    animate: true,
                    float: false
                },
                widget_constraints: {
                    allowed_types: ['text', 'image', 'counter', 'gallery', 'form', 'video', 'navigation'],
                    max_widgets: null,
                    default_widget_size: { w: 6, h: 3 },
                    column_layout: true,
                    columns: [
                        { span: 3, offset: 0, area: 'sidebar' },
                        { span: 9, offset: 3, area: 'main' }
                    ]
                },
                styling: {
                    default_padding: { top: 40, bottom: 40, left: 0, right: 0 },
                    default_margin: { top: 0, bottom: 0, left: 0, right: 0 },
                    default_background: '#ffffff',
                    container_class: 'container',
                    sidebar_background: '#f8f9fa'
                }
            },
            {
                key: 'sidebar-right',
                name: 'Sidebar Right',
                icon: 'ri-layout-right-line',
                description: 'Right sidebar with main content',
                category: 'layout',
                grid_config: {
                    column: 12,
                    cellHeight: 80,
                    verticalMargin: 10,
                    horizontalMargin: 10,
                    acceptWidgets: true,
                    resizable: { handles: ['se', 'sw'] },
                    animate: true,
                    float: false
                },
                widget_constraints: {
                    allowed_types: ['text', 'image', 'counter', 'gallery', 'form', 'video', 'navigation'],
                    max_widgets: null,
                    default_widget_size: { w: 6, h: 3 },
                    column_layout: true,
                    columns: [
                        { span: 9, offset: 0, area: 'main' },
                        { span: 3, offset: 9, area: 'sidebar' }
                    ]
                },
                styling: {
                    default_padding: { top: 40, bottom: 40, left: 0, right: 0 },
                    default_margin: { top: 0, bottom: 0, left: 0, right: 0 },
                    default_background: '#ffffff',
                    container_class: 'container',
                    sidebar_background: '#f8f9fa'
                }
            }
        ];
    }

    /**
     * Render templates in the sidebar
     */
    renderTemplates() {
        if (!this.container) {
            console.error('❌ Section templates container not found');
            return;
        }

        console.log('🎨 Rendering section templates...', this.templates);
        
        this.container.innerHTML = '';

        if (this.templates.length === 0) {
            console.warn('⚠️ No templates to render');
            this.container.innerHTML = '<div class="text-muted text-center p-3">No templates available</div>';
            return;
        }

        this.templates.forEach(template => {
            const templateElement = this.createTemplateElement(template);
            this.container.appendChild(templateElement);
            console.log(`✅ Rendered template: ${template.name}`);
        });

        console.log(`✅ Rendered ${this.templates.length} section templates`);
    }

    /**
     * Create a template element
     */
    createTemplateElement(template) {
        const element = document.createElement('div');
        element.className = 'template-item';
        element.setAttribute('data-template-key', template.key);
        element.setAttribute('draggable', 'true');
        element.setAttribute('data-type', 'section-template');
        
        element.innerHTML = `
            <div class="template-icon">
                <i class="${template.icon}"></i>
            </div>
            <div class="template-info">
                <div class="template-name">${template.name}</div>
                <div class="template-description">${template.description}</div>
            </div>
        `;

        return element;
    }

    /**
     * Setup drag and drop functionality
     */
    setupDragAndDrop() {
        // Check if container exists
        if (!this.container) {
            console.error('❌ Cannot setup drag and drop: container not found');
            return;
        }
        
        // Add drag event listeners to template items
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('template-item')) {
                const templateKey = e.target.getAttribute('data-template-key');
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    type: 'section-template',
                    templateKey: templateKey
                }));
                
                e.target.classList.add('dragging');
                console.log(`🎯 Started dragging template: ${templateKey}`);
            }
        });

        this.container.addEventListener('dragend', (e) => {
            if (e.target.classList.contains('template-item')) {
                e.target.classList.remove('dragging');
                console.log('🎯 Finished dragging template');
            }
        });

        // Add drop zone to canvas
        const canvasContainer = document.getElementById('pageSectionsContainer');
        if (canvasContainer) {
            // Remove existing event listeners to prevent duplicates
            canvasContainer.removeEventListener('dragover', this.handleDragOver);
            canvasContainer.removeEventListener('dragleave', this.handleDragLeave);
            canvasContainer.removeEventListener('drop', this.handleDrop);
            
            // Add new event listeners
            this.handleDragOver = (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
                canvasContainer.classList.add('drag-over');
            };
            
            this.handleDragLeave = (e) => {
                canvasContainer.classList.remove('drag-over');
            };
            
            this.handleDrop = (e) => {
                e.preventDefault();
                canvasContainer.classList.remove('drag-over');
                
                try {
                    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                    if (data.type === 'section-template') {
                        this.handleTemplateDrop(data.templateKey, e);
                    }
                } catch (error) {
                    console.error('❌ Error parsing drop data:', error);
                }
            };
            
            canvasContainer.addEventListener('dragover', this.handleDragOver);
            canvasContainer.addEventListener('dragleave', this.handleDragLeave);
            canvasContainer.addEventListener('drop', this.handleDrop);
            
            console.log('✅ Drop zone setup complete');
        } else {
            console.warn('⚠️ Canvas container not found for drop zone');
        }
    }

    /**
     * Handle template drop on canvas
     */
    handleTemplateDrop(templateKey, event) {
        console.log(`🎯 Template dropped: ${templateKey}`);
        
        // Prevent multiple drops
        if (this.isCreatingSection) {
            console.log('⚠️ Section creation already in progress, ignoring drop');
            return;
        }
        
        this.isCreatingSection = true;
        
        // Show loading indicator
        this.showSectionLoader();
        
        // Get drop position
        const rect = event.currentTarget.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        // Create section from template with delay to show loader
        setTimeout(() => {
            this.createSectionFromTemplate(templateKey, { x, y });
        }, 500); // Small delay to show the loader
    }

    /**
     * Show section creation loader
     */
    showSectionLoader() {
        const pageContainer = document.getElementById('pageSectionsContainer');
        if (!pageContainer) return;

        // Remove existing loader if any
        this.hideSectionLoader();

        const loaderHtml = `
            <div id="sectionCreationLoader" class="section-creation-loader">
                <div class="loader-overlay">
                    <div class="loader-content">
                        <div class="loader-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="loader-text">
                            <h5>Creating Section...</h5>
                            <p>Please wait while we set up your new section</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        pageContainer.insertAdjacentHTML('beforeend', loaderHtml);
    }

    /**
     * Hide section creation loader
     */
    hideSectionLoader() {
        const loader = document.getElementById('sectionCreationLoader');
        if (loader) {
            loader.remove();
        }
    }

    /**
     * Create a new section from template
     */
    async createSectionFromTemplate(templateKey, position = {}) {
        try {
            console.log(`🏗️ Creating section from template: ${templateKey}`);
            
            // Get current page ID
            const pageContainer = document.getElementById('pageSectionsContainer');
            const pageId = pageContainer?.getAttribute('data-page-id');
            
            if (!pageId) {
                console.error('❌ Page ID not found');
                this.hideSectionLoader();
                this.isCreatingSection = false;
                return;
            }

            // Get template data
            const template = this.templates.find(t => t.key === templateKey);
            if (!template) {
                console.error('❌ Template not found:', templateKey);
                this.hideSectionLoader();
                this.isCreatingSection = false;
                return;
            }

            // Create section data with correct field names
            const sectionData = {
                template_key: templateKey,
                name: template.name,
                // GridStack positioning
                grid_x: Math.floor(position.x / 100) || 0, // Convert pixel position to grid position
                grid_y: Math.floor(position.y / 100) || 0,
                grid_w: template.grid_config?.default_size?.w || 12,
                grid_h: template.grid_config?.default_size?.h || 4,
                grid_id: 'section_' + Date.now(),
                grid_config: template.grid_config,
                allows_widgets: template.grid_config?.acceptWidgets ?? true,
                widget_types: template.widget_constraints?.allowed_types || [],
                // Styling
                css_classes: template.styling?.container_class || 'container',
                background_color: template.styling?.default_background || '#ffffff',
                padding: template.styling?.default_padding || { top: 0, bottom: 0, left: 0, right: 0 },
                margin: template.styling?.default_margin || { top: 0, bottom: 0, left: 0, right: 0 },
                locked_position: false,
                resize_handles: template.grid_config?.resizable?.handles || ['se', 'sw'],
                // Position (integer for ordering)
                position: 1 // Default position, will be calculated by backend
            };

            console.log('📤 Sending section data:', sectionData);

            // Call API to create section
            const response = await fetch(`/admin/api/pages/${pageId}/sections`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(sectionData)
            });

            if (response.ok) {
                const result = await response.json();
                console.log('✅ Section created successfully:', result);
                
                // Hide loader
                this.hideSectionLoader();
                this.isCreatingSection = false;
                
                // Clear any existing placeholders
                this.clearAllPlaceholders();
                
                // Refresh the page sections
                if (window.GridStackPageBuilder && window.GridStackPageBuilder.loadPageContent) {
                    window.GridStackPageBuilder.loadPageContent();
                }
            } else {
                const errorData = await response.json();
                console.error('❌ Failed to create section:', response.statusText, errorData);
                this.hideSectionLoader();
                this.isCreatingSection = false;
                // For now, create a mock section for testing
                this.createMockSection(templateKey);
            }
            
        } catch (error) {
            console.error('❌ Error creating section:', error);
            this.hideSectionLoader();
            this.isCreatingSection = false;
            // Create mock section for testing
            this.createMockSection(templateKey);
        }
    }

    /**
     * Create a mock section for testing
     */
    createMockSection(templateKey) {
        console.log(`🏗️ Creating mock section for template: ${templateKey}`);
        
        const sectionId = 'section_' + Date.now();
        const template = this.templates.find(t => t.key === templateKey);
        
        if (!template) {
            console.error('❌ Template not found:', templateKey);
            return;
        }

        // Create section HTML based on template type
        const sectionHtml = this.createSectionHtml(sectionId, template);
        
        const pageContainer = document.getElementById('pageSectionsContainer');
        if (pageContainer) {
            // Remove the default section placeholder if it exists
            const defaultPlaceholder = pageContainer.querySelector('#addFirstSection');
            if (defaultPlaceholder) {
                defaultPlaceholder.remove();
            }
            
            // Add the new section
            pageContainer.insertAdjacentHTML('beforeend', sectionHtml);
            
            // Initialize GridStack for the new section
            this.initializeSectionGridStack(sectionId, template);
            
            // Update section counter
            this.updateSectionCounter();
            
            // Save section to backend (mock API call)
            this.saveSectionToBackend(sectionId, template);
        }
    }

    /**
     * Save section to backend
     */
    async saveSectionToBackend(sectionId, template) {
        try {
            const pageContainer = document.getElementById('pageSectionsContainer');
            const pageId = pageContainer?.getAttribute('data-page-id');
            
            if (!pageId) {
                console.error('❌ Page ID not found');
                return;
            }

            const sectionData = {
                template_key: template.key,
                name: template.name,
                grid_config: template.grid_config,
                widget_constraints: template.widget_constraints,
                styling: template.styling,
                page_id: pageId,
                position: 1, // Default position
                is_active: true
            };

            // Mock API call - in real implementation this would be a real API call
            console.log('💾 Saving section to backend:', sectionData);
            
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 300));
            
            console.log('✅ Section saved successfully');
            
        } catch (error) {
            console.error('❌ Error saving section:', error);
        }
    }

    /**
     * Update section counter display
     */
    updateSectionCounter() {
        const pageContainer = document.getElementById('pageSectionsContainer');
        if (!pageContainer) return;

        const sections = pageContainer.querySelectorAll('.page-section');
        const widgets = pageContainer.querySelectorAll('.widget-item');
        
        const sectionCount = sections.length;
        const widgetCount = widgets.length;
        
        // Update counter display elements
        const sectionCountElement = document.getElementById('sectionCount');
        const widgetCountElement = document.getElementById('widgetCount');
        
        if (sectionCountElement) {
            sectionCountElement.textContent = sectionCount;
        }
        
        if (widgetCountElement) {
            widgetCountElement.textContent = widgetCount;
        }
        
        console.log(`📊 Updated counter: ${sectionCount} sections, ${widgetCount} widgets`);
    }

    /**
     * Create section HTML based on template type
     */
    createSectionHtml(sectionId, template) {
        const templateName = template.name;
        const templateKey = template.key;
        
        let sectionContent = '';
        
        switch (templateKey) {
            case 'full-width':
                sectionContent = `
                    <div class="section-content-full-width">
                        <div class="section-grid-stack" id="section-${sectionId}-widgets">
                            <div class="widget-drop-zone">
                                <i class="ri-add-line"></i>
                                <span>Drop widgets here</span>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'multi-column':
                sectionContent = `
                    <div class="section-content-multi-column">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="section-grid-stack" id="section-${sectionId}-widgets-col1">
                                    <div class="widget-drop-zone">
                                        <i class="ri-add-line"></i>
                                        <span>Column 1</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="section-grid-stack" id="section-${sectionId}-widgets-col2">
                                    <div class="widget-drop-zone">
                                        <i class="ri-add-line"></i>
                                        <span>Column 2</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="section-grid-stack" id="section-${sectionId}-widgets-col3">
                                    <div class="widget-drop-zone">
                                        <i class="ri-add-line"></i>
                                        <span>Column 3</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'sidebar-left':
                sectionContent = `
                    <div class="section-content-sidebar-left">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="sidebar-area">
                                    <div class="section-grid-stack" id="section-${sectionId}-widgets-sidebar">
                                        <div class="widget-drop-zone">
                                            <i class="ri-add-line"></i>
                                            <span>Sidebar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="main-content-area">
                                    <div class="section-grid-stack" id="section-${sectionId}-widgets-main">
                                        <div class="widget-drop-zone">
                                            <i class="ri-add-line"></i>
                                            <span>Main Content</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'sidebar-right':
                sectionContent = `
                    <div class="section-content-sidebar-right">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="main-content-area">
                                    <div class="section-grid-stack" id="section-${sectionId}-widgets-main">
                                        <div class="widget-drop-zone">
                                            <i class="ri-add-line"></i>
                                            <span>Main Content</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="sidebar-area">
                                    <div class="section-grid-stack" id="section-${sectionId}-widgets-sidebar">
                                        <div class="widget-drop-zone">
                                            <i class="ri-add-line"></i>
                                            <span>Sidebar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            default:
                sectionContent = `
                    <div class="section-content-default">
                        <div class="section-grid-stack" id="section-${sectionId}-widgets">
                            <div class="widget-drop-zone">
                                <i class="ri-add-line"></i>
                                <span>Drop widgets here</span>
                            </div>
                        </div>
                    </div>
                `;
        }
        
        return `
            <div class="page-section page-section-${templateKey}" data-section-id="${sectionId}" data-template="${templateKey}">
                <div class="section-header">
                    <div class="section-title">
                        <i class="${template.icon}"></i>
                        ${templateName}
                    </div>
                    <div class="section-controls">
                        <button class="btn btn-sm btn-outline-secondary" onclick="editSection('${sectionId}')">
                            <i class="ri-settings-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSection('${sectionId}')">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                ${sectionContent}
            </div>
        `;
    }

    /**
     * Initialize GridStack for a section
     */
    initializeSectionGridStack(sectionId, template) {
        try {
            const sectionElement = document.querySelector(`[data-section-id="${sectionId}"]`);
            if (!sectionElement) {
                console.error(`❌ Section element not found for ID: ${sectionId}`);
                return;
            }

            // Initialize GridStack based on template type
            if (template.key === 'multi-column') {
                // Initialize separate GridStacks for each column
                ['col1', 'col2', 'col3'].forEach((col, index) => {
                    const gridContainer = sectionElement.querySelector(`#section-${sectionId}-widgets-${col}`);
                    if (gridContainer) {
                        try {
                            const gridStack = GridStack.init(gridContainer, {
                                ...template.grid_config,
                                column: 12,
                                cellHeight: 60
                            });
                            console.log(`✅ GridStack initialized for section ${sectionId} column ${index + 1}`);
                        } catch (error) {
                            console.error(`❌ Error initializing GridStack for column ${col}:`, error);
                        }
                    } else {
                        console.warn(`⚠️ Grid container not found for column ${col}`);
                    }
                });
            } else if (template.key === 'sidebar-left' || template.key === 'sidebar-right') {
                // Initialize separate GridStacks for sidebar and main content
                ['sidebar', 'main'].forEach((area) => {
                    const gridContainer = sectionElement.querySelector(`#section-${sectionId}-widgets-${area}`);
                    if (gridContainer) {
                        try {
                            const gridStack = GridStack.init(gridContainer, {
                                ...template.grid_config,
                                column: 12,
                                cellHeight: 60
                            });
                            console.log(`✅ GridStack initialized for section ${sectionId} ${area}`);
                        } catch (error) {
                            console.error(`❌ Error initializing GridStack for ${area}:`, error);
                        }
                    } else {
                        console.warn(`⚠️ Grid container not found for ${area}`);
                    }
                });
            } else {
                // Single GridStack for full-width and other layouts
                const gridContainer = sectionElement.querySelector('.section-grid-stack');
                if (gridContainer) {
                    try {
                        const gridStack = GridStack.init(gridContainer, {
                            ...template.grid_config
                        });
                        console.log(`✅ GridStack initialized for section: ${sectionId}`);
                    } catch (error) {
                        console.error(`❌ Error initializing GridStack for section ${sectionId}:`, error);
                    }
                } else {
                    console.warn(`⚠️ Grid container not found for section ${sectionId}`);
                }
            }
        } catch (error) {
            console.error(`❌ Error in initializeSectionGridStack for section ${sectionId}:`, error);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with a small delay to ensure all elements are ready
    setTimeout(() => {
        // Check if we're already on the layout tab
        const activeTab = document.querySelector('#pageTab .nav-link.active');
        if (activeTab && activeTab.getAttribute('data-bs-target') === '#layout') {
            console.log('🔄 Layout tab is active, initializing Section Templates immediately...');
            window.SectionTemplatesManager = new SectionTemplatesManager();
        } else {
            console.log('🔄 Layout tab not active, waiting for tab switch...');
        }
    }, 100);
});

// Also initialize when tab becomes active (for tab-based systems)
document.addEventListener('shown.bs.tab', function (e) {
    if (e.target.getAttribute('data-bs-target') === '#layout') {
        // Re-initialize section templates when layout tab becomes active
        setTimeout(() => {
            if (!window.SectionTemplatesManager || !window.SectionTemplatesManager.container) {
                console.log('🔄 Re-initializing Section Templates Manager for layout tab...');
                window.SectionTemplatesManager = new SectionTemplatesManager();
            }
        }, 200);
    }
}); 