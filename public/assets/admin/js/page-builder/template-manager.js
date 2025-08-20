/**
 * Template Manager
 * 
 * Handles loading, rendering, and creating sections from templates.
 * Manages section templates in the left sidebar and template creation workflow.
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
     * Load available section templates (using fallback since templates = sections)
     */
    async loadAvailableTemplates() {
        try {
            console.log('🔄 Loading available section templates (using fallback data)...');
            
            // Since template sections are the same as page sections, 
            // we'll use fallback templates for the sidebar
            this.loadFallbackTemplates();
            
            console.log(`✅ Loaded ${this.templates.size} template categories`);
            
        } catch (error) {
            console.error('❌ Error loading templates:', error);
            // Load fallback templates for development
            this.loadFallbackTemplates();
        }
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
     * Render templates in the sidebar
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
            
            // Render templates grouped by category with headers
            this.templates.forEach((categoryTemplates, category) => {
                // Add category header
                templatesHtml += `
                    <div class="template-category-header">
                        <div class="small text-muted text-uppercase fw-bold mb-2">
                            ${this.formatCategoryName(category)}
                        </div>
                    </div>
                `;
                
                // Add templates in this category
                categoryTemplates.forEach(template => {
                    templatesHtml += this.createTemplateHTML(template, category);
                });
                
                // Add spacing between categories
                templatesHtml += '<div class="mb-3"></div>';
            });

            this.container.innerHTML = templatesHtml;
            
            console.log('✅ Section templates rendered successfully');
            
        } catch (error) {
            console.error('❌ Error rendering templates:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single template item
     */
    createTemplateHTML(template, category) {
        return `
            <div class="component-item template-item" 
                 data-template-key="${template.key}" 
                 data-template-id="${template.id}"
                 data-section-type="${category}"
                 data-column-layout="${template.column_layout}"
                 draggable="true"
                 title="${template.description || template.name}">
                <i class="${template.icon || 'ri-layout-grid-line'}"></i>
                <div class="label">${template.name}</div>
                <div class="template-meta">
                    <small class="text-muted">${template.column_layout}</small>
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