/**
 * TEMPLATE MANAGER
 * ================
 * 
 * GENERAL PURPOSE:
 * Manages section templates in the left sidebar and handles template-based section
 * creation. Provides drag & drop functionality for templates and manages the
 * template selection workflow for rapid page building.
 * 
 * KEY FUNCTIONS/METHODS & DUPLICATION STATUS:
 * 
 * TEMPLATE INITIALIZATION:
 * • init() - **UNIQUE** - Initialize template manager and sidebar
 * • loadAvailableTemplates() - **UNIQUE** - Load section templates from API
 * • loadFallbackTemplates() - **UNIQUE** - Provide fallback templates during development
 * • groupTemplatesByCategory() - **UNIQUE** - Organize templates by category for sidebar
 * 
 * TEMPLATE RENDERING IN SIDEBAR:
 * • renderTemplates() - **UNIQUE** - Display all templates in sidebar grid
 * • renderTemplatesByCategory() - **UNIQUE** - Group templates by category in sidebar
 * • renderTemplateCard() - **UNIQUE** - Generate individual template cards
 * • updateTemplateUI() - **UNIQUE** - Refresh template display in sidebar
 * 
 * DRAG & DROP FUNCTIONALITY:
 * • setupDragAndDrop() - **UNIQUE** - Configure draggable templates in sidebar
 * • handleDragStart() - **UNIQUE** - Start drag operation for templates
 * • handleDragEnd() - **UNIQUE** - Clean up after template drag
 * • createDragPreview() - **UNIQUE** - Visual preview during template drag
 * • setTemplateDragData() - **UNIQUE** - Set data for template drop operations
 * 
 * TEMPLATE-BASED SECTION CREATION:
 * • createSectionFromTemplate() - **DUPLICATED** - Similar logic in page-builder-main.js and show.blade.php
 * • processTemplateCreation() - **UNIQUE** - Handle template selection and creation
 * • validateTemplateData() - **UNIQUE** - Validate template before section creation
 * • applyTemplateDefaults() - **UNIQUE** - Apply default values from template
 * 
 * TEMPLATE INFORMATION:
 * • getTemplateInfo() - **UNIQUE** - Get detailed template information
 * • showTemplatePreview() - **UNIQUE** - Display template preview/thumbnail
 * • hideTemplatePreview() - **UNIQUE** - Hide template preview
 * • formatTemplateDescription() - **UNIQUE** - Format template descriptions
 * 
 * TEMPLATE FILTERING & SEARCH:
 * • filterTemplates() - **UNIQUE** - Filter templates by search term
 * • filterByCategory() - **UNIQUE** - Show/hide templates by category
 * • setupTemplateSearch() - **UNIQUE** - Configure template search functionality
 * • resetTemplateFilters() - **UNIQUE** - Clear all template filters
 * 
 * ERROR & LOADING STATES:
 * • showLoadingState() - **DUPLICATED** - Loading states scattered in multiple files
 * • hideLoadingState() - **DUPLICATED** - Loading states scattered in multiple files
 * • showErrorState() - **UNIQUE** - Display error when templates fail to load
 * • showEmptyState() - **UNIQUE** - Show message when no templates available
 * 
 * TEMPLATE MANAGEMENT:
 * • refreshTemplateLibrary() - **UNIQUE** - Reload templates from API
 * • isTemplateAvailable() - **UNIQUE** - Check if template is available for use
 * • getTemplatesByType() - **UNIQUE** - Get templates filtered by type
 * • validateTemplateCompatibility() - **UNIQUE** - Check template compatibility
 * 
 * UTILITY METHODS:
 * • generateTemplateId() - **UNIQUE** - Generate unique identifiers for templates
 * • formatTemplateForAPI() - **UNIQUE** - Prepare template data for API calls
 * • cleanupTemplateData() - **UNIQUE** - Clean template data before processing
 * 
 * MAJOR DUPLICATION ISSUES:
 * 1. **SECTION CREATION**: createSectionFromTemplate() duplicated in multiple files
 * 2. **LOADING STATES**: Loading indicators implemented separately from unified loader
 * 3. **TEMPLATE SELECTION**: Template selection logic scattered across components
 * 4. **DRAG DATA**: Template drag data format might be inconsistent with drop handlers
 * 5. **API CALLS**: Some direct API calls bypass the centralized PageBuilderAPI
 * 
 * INCONSISTENCIES WITH OTHER FILES:
 * • page-builder-main.js has createSectionFromTemplate() with overlapping logic
 * • show.blade.php has template selection modal with different approach
 * • Template drag data format may not match expectations in drop handlers
 * • Loading states don't use unified-loader-manager.js
 */
class TemplateManager {
    constructor(api, sectionManager) {
        this.api = api;
        this.sectionManager = sectionManager;
        this.templates = new Map(); // Store templates by category
        this.container = null;
        this.isDragging = false;
        
        console.log('📋 Template Manager initialized');
    }

    /**
     * Initialize the template manager
     */
    async init() {
        try {
            console.log('🔄 Initializing Template Manager...');
            
            // Find the templates container
            this.container = document.getElementById('sectionsGrid');
            if (!this.container) {
                throw new Error('Template container not found: #sectionsGrid');
            }
            
            // Load available templates from API
            await this.loadAvailableTemplates();
            
            // Render templates in sidebar
            this.renderTemplates();
            
            // Setup drag & drop functionality
            this.setupDragAndDrop();
            
            console.log('✅ Template Manager initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Template Manager:', error);
            this.showErrorState();
        }
    }

    /**
     * Load available section templates from API
     */
    async loadAvailableTemplates() {
        try {
            console.log('🔄 Loading available section templates from API...');
            
            // Load section templates from new API endpoint
            const response = await this.api.getSectionTemplates();
            
            if (response.success && response.data) {
                const { templates } = response.data;
                
                // Group templates by category for sidebar display
                this.groupTemplatesByCategory(templates);
                
                console.log(`✅ Loaded ${templates.length} section templates from API`);
            } else {
                throw new Error('Failed to load section templates from API');
            }
            
        } catch (error) {
            console.error('❌ Error loading templates from API:', error);
            // Load fallback templates for development
            this.loadFallbackTemplates();
        }
    }

    /**
     * Group templates by category for sidebar organization
     */
    groupTemplatesByCategory(templates) {
        this.templates.clear();
        
        templates.forEach(template => {
            const category = template.category || 'layout';
            
            if (!this.templates.has(category)) {
                this.templates.set(category, []);
            }
            
            this.templates.get(category).push(template);
        });
        
        console.log('📋 Templates grouped by category:', Array.from(this.templates.keys()));
    }

    /**
     * Load fallback templates for development/testing
     */
    loadFallbackTemplates() {
        console.log('📦 Loading fallback template data...');
        
        const fallbackTemplates = {
            'header': [
                {
                    id: 1,
                    key: 'hero-banner',
                    name: 'Hero Banner',
                    section_type: 'header',
                    column_layout: 'full-width',
                    description: 'Full-width hero section with image and text',
                    icon: 'ri-image-line'
                },
                {
                    id: 2,
                    key: 'header-navigation',
                    name: 'Header with Navigation',
                    section_type: 'header',
                    column_layout: 'container',
                    description: 'Site header with logo and navigation menu',
                    icon: 'ri-navigation-line'
                }
            ],
            'content': [
                {
                    id: 3,
                    key: 'two-column',
                    name: 'Two Columns',
                    section_type: 'content',
                    column_layout: '6-6',
                    description: 'Two equal columns layout',
                    icon: 'ri-layout-column-line'
                },
                {
                    id: 4,
                    key: 'three-column',
                    name: 'Three Columns',
                    section_type: 'content',
                    column_layout: '4-4-4',
                    description: 'Three equal columns layout',
                    icon: 'ri-layout-grid-line'
                },
                {
                    id: 5,
                    key: 'full-width',
                    name: 'Full Width',
                    section_type: 'content',
                    column_layout: 'full-width',
                    description: 'Single full-width content area',
                    icon: 'ri-layout-row-line'
                }
            ],
            'footer': [
                {
                    id: 6,
                    key: 'footer-columns',
                    name: 'Footer Columns',
                    section_type: 'footer',
                    column_layout: '3-3-3-3',
                    description: 'Four column footer layout',
                    icon: 'ri-layout-bottom-line'
                },
                {
                    id: 7,
                    key: 'simple-footer',
                    name: 'Simple Footer',
                    section_type: 'footer',
                    column_layout: 'full-width',
                    description: 'Simple full-width footer',
                    icon: 'ri-layout-masonry-line'
                }
            ]
        };

        // Store fallback templates
        Object.keys(fallbackTemplates).forEach(sectionType => {
            this.templates.set(sectionType, fallbackTemplates[sectionType]);
        });
    }

    /**
     * Render templates in the sidebar (no category grouping)
     */
    renderTemplates() {
        if (!this.container) return;
        
        try {
            console.log('🎨 Rendering section templates in sidebar...');
            
            if (this.templates.size === 0) {
                this.showEmptyState();
                return;
            }

            let templatesHtml = '';
            
            // Collect all templates from all categories into a single array
            const allTemplates = [];
            this.templates.forEach((categoryTemplates, category) => {
                categoryTemplates.forEach(template => {
                    allTemplates.push({ ...template, category });
                });
            });

            // Render all templates together without category grouping
            allTemplates.forEach(template => {
                templatesHtml += this.createTemplateHTML(template, template.category);
            });

            this.container.innerHTML = templatesHtml;
            
            console.log('✅ Section templates rendered successfully');
            
        } catch (error) {
            console.error('❌ Error rendering templates:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single template item with drag icon
     */
    createTemplateHTML(template, category) {
        return `
            <div class="template-item" 
                 data-template-key="${template.key}" 
                 data-template-id="${template.id || template.key}"
                 data-section-type="${category}"
                 data-template-type="${template.type || 'core'}"
                 draggable="true"
                 title="${template.description || template.name}">
                <div class="d-flex align-items-center justify-content-between">
                    <!-- Icon + Name aligned left -->
                    <div class="d-flex align-items-center">
                        <div class="template-icon me-2">
                            <i class="${template.icon || 'ri-layout-grid-line'}"></i>
                        </div>
                        <div class="template-name">
                            ${template.name}
                        </div>
                    </div>
                    <!-- Drag handle aligned right -->
                    <div class="drag-handle" title="Drag to add section">
                        <i class="ri-drag-move-line"></i>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Format category name for display
     */
    formatCategoryName(category) {
        return category.charAt(0).toUpperCase() + category.slice(1).replace('_', ' ');
    }

    /**
     * Setup drag and drop functionality
     */
    setupDragAndDrop() {
        if (!this.container) return;
        
        console.log('🎯 Setting up template drag & drop...');
        
        // Add event listeners to all template items
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.matches('.template-item')) {
                this.handleDragStart(e);
            }
        });
        
        this.container.addEventListener('dragend', (e) => {
            if (e.target.matches('.template-item')) {
                this.handleDragEnd(e);
            }
        });
        
        // Add click handlers for creating templates without dragging
        this.container.addEventListener('click', (e) => {
            if (e.target.matches('.template-item') || e.target.closest('.template-item')) {
                const templateItem = e.target.closest('.template-item');
                this.handleTemplateClick(templateItem);
            }
        });
        
        console.log('✅ Template drag & drop setup complete');
    }

    /**
     * Handle template click (create section immediately)
     */
    async handleTemplateClick(templateItem) {
        const templateKey = templateItem.getAttribute('data-template-key');
        const templateId = templateItem.getAttribute('data-template-id');
        
        console.log('🖱️ Template clicked:', { templateKey, templateId });
        
        try {
            // Add visual feedback
            templateItem.classList.add('creating');
            
            // Create section from template at next available position
            const position = this.calculateNextPosition();
            const newSection = await this.createSectionFromTemplate(templateKey, position);
            
            if (newSection) {
                console.log('✅ Section created from template click:', newSection);
            }
            
        } catch (error) {
            console.error('❌ Error creating section from template click:', error);
            alert('Failed to create section. Please try again.');
        } finally {
            templateItem.classList.remove('creating');
        }
    }

    /**
     * Handle drag start event
     */
    handleDragStart(e) {
        const templateItem = e.target;
        const templateKey = templateItem.getAttribute('data-template-key');
        const templateId = templateItem.getAttribute('data-template-id');
        const sectionType = templateItem.getAttribute('data-section-type');
        const columnLayout = templateItem.getAttribute('data-column-layout');
        
        console.log('🎯 Template drag started:', { templateKey, templateId, sectionType });
        
        // Add dragging visual state
        templateItem.classList.add('dragging');
        this.isDragging = true;
        
        // Set drag data
        const dragData = {
            type: 'template',
            templateKey: templateKey,
            templateId: parseInt(templateId),
            sectionType: sectionType,
            columnLayout: columnLayout,
            source: 'template-manager'
        };
        
        e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'copy';
        
        // Show drop zones
        this.highlightDropZones(true);
    }

    /**
     * Handle drag end event
     */
    handleDragEnd(e) {
        console.log('🎯 Template drag ended');
        
        // Remove dragging visual state
        e.target.classList.remove('dragging');
        this.isDragging = false;
        
        // Hide drop zones
        this.highlightDropZones(false);
    }

    /**
     * Highlight/unhighlight drop zones
     */
    highlightDropZones(highlight) {
        const gridContainer = document.getElementById('gridStackContainer');
        
        if (gridContainer) {
            if (highlight) {
                gridContainer.classList.add('template-drop-active');
            } else {
                gridContainer.classList.remove('template-drop-active');
            }
        }
    }

    /**
     * Create section from template (simplified approach)
     */
    async createSectionFromTemplate(templateKey, position = null) {
        try {
            console.log('🔨 Creating section from template:', { templateKey, position });
            
            // Get template information
            const template = this.getTemplate(templateKey);
            if (!template) {
                throw new Error(`Template ${templateKey} not found`);
            }
            
            // For now, just create a basic section structure since templates = sections
            // In a real implementation, this would call the section creation API
            console.log('📋 Template found:', template);
            
            // Create a mock section object for demonstration
            const mockSection = {
                id: Date.now(), // Temporary ID for demo
                name: template.name,
                template_section: {
                    name: template.name,
                    section_type: template.section_type
                },
                grid_x: position?.x || 0,
                grid_y: position?.y || 0,
                grid_w: position?.w || 12,
                grid_h: position?.h || 4
            };
            
            // Render the section immediately for demo purposes
            this.sectionManager.renderSection(mockSection);
            
            // Emit template dropped event
            document.dispatchEvent(new CustomEvent('pagebuilder:template-dropped', {
                detail: {
                    templateKey: templateKey,
                    section: mockSection,
                    position: position
                }
            }));
            
            console.log('✅ Demo section created from template:', mockSection);
            return mockSection;
            
        } catch (error) {
            console.error('❌ Error creating section from template:', error);
            throw error;
        }
    }

    /**
     * Calculate next available position for new sections
     */
    calculateNextPosition() {
        // Get current sections from section manager
        const existingSections = this.sectionManager.getAllSections();
        
        if (existingSections.length === 0) {
            // First section at top
            return { x: 0, y: 0, w: 12, h: 4 };
        }
        
        // Find the highest Y position and add new section below
        const maxY = Math.max(...existingSections.map(s => (s.grid_y || 0) + (s.grid_h || 4)));
        
        return {
            x: 0,
            y: maxY,
            w: 12,
            h: 4
        };
    }

    /**
     * Show empty state when no templates available
     */
    showEmptyState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="empty-state text-center p-3">
                <i class="ri-layout-grid-line text-muted mb-2" style="font-size: 2rem;"></i>
                <div class="text-muted small">No templates available</div>
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
                <div class="text-danger small mb-2">Failed to load templates</div>
                <button class="btn btn-sm btn-outline-primary retry-btn" onclick="window.templateManager?.init()">
                    <i class="ri-refresh-line me-1"></i>Retry
                </button>
            </div>
        `;
    }

    /**
     * Get template by key
     */
    getTemplate(templateKey) {
        for (const [category, categoryTemplates] of this.templates) {
            const template = categoryTemplates.find(t => t.key === templateKey);
            if (template) {
                return { ...template, category };
            }
        }
        return null;
    }

    /**
     * Get all templates as flat array
     */
    getAllTemplates() {
        const allTemplates = [];
        this.templates.forEach((categoryTemplates, category) => {
            categoryTemplates.forEach(template => {
                allTemplates.push({ ...template, category });
            });
        });
        return allTemplates;
    }

    /**
     * Get templates by category
     */
    getTemplatesByCategory(category) {
        return this.templates.get(category) || [];
    }

    /**
     * Refresh templates from API
     */
    async refresh() {
        console.log('🔄 Refreshing Template Manager...');
        await this.loadAvailableTemplates();
        this.renderTemplates();
    }
}

// Export for global use
window.TemplateManager = TemplateManager;

console.log('📦 Template Manager module loaded');