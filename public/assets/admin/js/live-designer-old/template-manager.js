/**
 * Template Manager - Live Designer Edition
 * 
 * Handles loading, rendering, and creating sections from templates in live preview.
 * Adapted for live-designer context with click-to-add functionality.
 */
class TemplateManager {
    constructor(api, livePreview, unifiedLoader) {
        this.api = api;
        this.livePreview = livePreview;
        this.unifiedLoader = unifiedLoader;
        this.templates = new Map(); // Store templates by category
        this.container = null;
        
        console.log('📋 Live Designer Template Manager initialized');
    }

    /**
     * Initialize the template manager
     */
    async init() {
        try {
            console.log('🔄 Initializing Live Designer Template Manager...');
            
            // Find the templates container
            this.container = document.getElementById('sectionsGrid');
            if (!this.container) {
                throw new Error('Template container not found: #sectionsGrid');
            }
            
            // Show loader
            if (this.unifiedLoader) {
                this.unifiedLoader.show('template-loading', 'Loading templates...', 10);
            }
            
            // Load available templates from API
            await this.loadAvailableTemplates();
            
            // Render templates in sidebar
            this.renderTemplates();
            
            // Setup drag and drop functionality (like page-builder)
            this.setupDragAndDrop();
            
            // Update section count badge
            this.updateSectionCount();
            
            // Hide loader
            if (this.unifiedLoader) {
                this.unifiedLoader.hide('template-loading');
            }
            
            console.log('✅ Live Designer Template Manager initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Live Designer Template Manager:', error);
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('template-loading', 'Failed to load templates');
            }
            this.showErrorState();
        }
    }

    /**
     * Load available section templates from API
     */
    async loadAvailableTemplates() {
        try {
            console.log('🔄 Loading available section templates from API...');
            
            // Use the correct API endpoint for page-builder section templates
            const response = await fetch('/admin/api/page-builder/section-templates', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.success && data.data?.templates) {
                    // Group templates by category for sidebar display
                    this.groupTemplatesByCategory(data.data.templates);
                    console.log(`✅ Loaded ${data.data.templates.length} section templates from API:`, data.data.templates.map(t => `${t.name} (${t.category})`));
                } else {
                    throw new Error(data.message || 'Failed to load section templates');
                }
            } else {
                throw new Error(`API Error: ${response.status}`);
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
            console.log('🎨 Rendering section templates in live designer sidebar...');
            
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

            // Render all templates together
            allTemplates.forEach(template => {
                templatesHtml += this.createTemplateHTML(template, template.category);
            });

            this.container.innerHTML = templatesHtml;
            
            console.log('✅ Section templates rendered successfully in live designer');
            
        } catch (error) {
            console.error('❌ Error rendering templates:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single template item (draggable like page-builder)
     */
    createTemplateHTML(template, category) {
        return `
            <div class="component-item" 
                 data-template-key="${template.key}" 
                 data-template-id="${template.id || template.key}"
                 data-section-type="${category}"
                 data-template-type="${template.type || 'core'}"
                 draggable="true"
                 title="${template.description || template.name}">
                <i class="${template.icon || 'ri-layout-grid-line'}"></i>
                <div class="label">${template.name}</div>
            </div>
        `;
    }

    /**
     * Setup drag and drop functionality
     */
    setupDragAndDrop() {
        if (!this.container) return;
        
        console.log('🎯 Setting up template drag & drop...');
        
        // Add event listeners for drag and drop
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.matches('.component-item')) {
                this.handleDragStart(e);
            }
        });
        
        this.container.addEventListener('dragend', (e) => {
            if (e.target.matches('.component-item')) {
                this.handleDragEnd(e);
            }
        });
        
        console.log('✅ Template drag & drop setup complete');
    }

    /**
     * Handle drag start event
     */
    handleDragStart(e) {
        const templateItem = e.target;
        const templateKey = templateItem.getAttribute('data-template-key');
        const templateId = templateItem.getAttribute('data-template-id');
        const sectionType = templateItem.getAttribute('data-section-type');
        
        console.log('🎯 Template drag started:', { templateKey, templateId, sectionType });
        
        // Add dragging visual state
        templateItem.classList.add('dragging');
        
        // Set drag data for iframe communication
        const dragData = {
            type: 'template',
            templateKey: templateKey,
            templateId: parseInt(templateId),
            sectionType: sectionType,
            source: 'live-designer-template-manager'
        };
        
        e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'copy';
        
        // Highlight iframe drop zone
        this.highlightIframeDropZone(true);
    }

    /**
     * Handle drag end event
     */
    handleDragEnd(e) {
        console.log('🎯 Template drag ended');
        
        // Remove dragging visual state
        e.target.classList.remove('dragging');
        
        // Remove iframe drop zone highlight
        this.highlightIframeDropZone(false);
    }

    /**
     * Highlight/unhighlight iframe drop zone
     */
    highlightIframeDropZone(highlight) {
        const iframe = document.getElementById('preview-iframe');
        const canvasContainer = document.getElementById('canvasContainer');
        
        if (canvasContainer) {
            if (highlight) {
                canvasContainer.style.background = '#e3f2fd';
                canvasContainer.style.border = '2px dashed #007bff';
                canvasContainer.style.borderRadius = '8px';
            } else {
                canvasContainer.style.background = '#f1f4f7';
                canvasContainer.style.border = 'none';
                canvasContainer.style.borderRadius = '0';
            }
        }
    }

    /**
     * Handle template click (add section to live preview)
     */
    async handleTemplateClick(templateItem) {
        const templateKey = templateItem.getAttribute('data-template-key');
        const templateId = templateItem.getAttribute('data-template-id');
        const sectionType = templateItem.getAttribute('data-section-type');
        
        console.log('🖱️ Template clicked:', { templateKey, templateId, sectionType });
        
        try {
            // Add visual feedback
            templateItem.classList.add('loading');
            
            // Show loader
            if (this.unifiedLoader) {
                this.unifiedLoader.show('template-add', `Adding ${templateKey} section...`, 20);
            }
            
            // Create section from template in live preview
            const newSection = await this.addSectionToLivePreview(templateKey, sectionType);
            
            if (newSection) {
                console.log('✅ Section added to live preview:', newSection);
                
                // Show success message
                if (this.livePreview?.showMessage) {
                    this.livePreview.showMessage(`${templateKey} section added successfully!`, 'success');
                }
            }
            
            // Hide loader
            if (this.unifiedLoader) {
                this.unifiedLoader.hide('template-add');
            }
            
        } catch (error) {
            console.error('❌ Error adding section from template:', error);
            
            // Show error
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('template-add', 'Failed to add section');
            }
            
            if (this.livePreview?.showMessage) {
                this.livePreview.showMessage('Failed to add section. Please try again.', 'error');
            }
        } finally {
            templateItem.classList.remove('loading');
        }
    }

    /**
     * Add section to live preview using API
     */
    async addSectionToLivePreview(templateKey, sectionType) {
        try {
            console.log('🔨 Adding section to live preview:', { templateKey, sectionType });
            
            // Get page ID from live preview
            const pageId = this.livePreview?.options?.pageId;
            if (!pageId) {
                throw new Error('Page ID not available');
            }
            
            // Call API to add section from template
            const response = await fetch('/admin/api/live-preview/sections', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    page_id: pageId,
                    template_key: templateKey,
                    section_type: sectionType,
                    position: 'bottom' // Add to bottom of page
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    // Refresh live preview to show new section
                    if (this.livePreview?.refreshPreview) {
                        await this.livePreview.refreshPreview();
                    }
                    
                    return data.data;
                } else {
                    throw new Error(data.message || 'Failed to add section');
                }
            } else {
                throw new Error(`API Error: ${response.status}`);
            }
            
        } catch (error) {
            console.error('❌ Error adding section to live preview:', error);
            throw error;
        }
    }

    /**
     * Update section count badge
     */
    updateSectionCount() {
        const badge = document.getElementById('sectionsCount');
        if (badge && this.templates) {
            let totalCount = 0;
            this.templates.forEach(categoryTemplates => {
                totalCount += categoryTemplates.length;
            });
            badge.textContent = totalCount;
        }
    }

    /**
     * Show empty state when no templates available
     */
    showEmptyState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="component-loading">
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
            <div class="component-error">
                <i class="ri-error-warning-line error-icon"></i>
                <div class="error-message">Failed to load templates</div>
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
     * Refresh templates from API
     */
    async refresh() {
        console.log('🔄 Refreshing Live Designer Template Manager...');
        await this.loadAvailableTemplates();
        this.renderTemplates();
        this.updateSectionCount();
    }
}

// Export for global use
window.TemplateManager = TemplateManager;

console.log('📦 Live Designer Template Manager module loaded');