/**
 * Widget Library Manager - Live Designer Edition
 * 
 * Handles loading and rendering available widgets in the left sidebar.
 * Adapted for live-designer context with click-to-add functionality.
 */
class WidgetLibrary {
    constructor(livePreview, unifiedLoader) {
        this.livePreview = livePreview;
        this.unifiedLoader = unifiedLoader;
        this.widgets = new Map(); // Store widgets by category
        this.container = null;
        
        console.log('📚 Live Designer Widget Library initialized');
    }

    /**
     * Initialize the widget library
     */
    async init() {
        try {
            console.log('🔄 Initializing Live Designer Widget Library...');
            
            // Find the widgets container
            this.container = document.getElementById('themeWidgetsGrid');
            if (!this.container) {
                throw new Error('Widget library container not found: #themeWidgetsGrid');
            }
            
            // Show loader
            if (this.unifiedLoader) {
                this.unifiedLoader.show('widget-loading', 'Loading widgets...', 10);
            }
            
            // Load widgets from server-rendered data
            this.loadWidgetsFromServerData();
            
            // Setup drag and drop functionality (like page-builder)
            this.setupDragAndDrop();
            
            // Update widget count badge
            this.updateWidgetCount();
            
            // Hide loader
            if (this.unifiedLoader) {
                this.unifiedLoader.hide('widget-loading');
            }
            
            console.log('✅ Live Designer Widget Library initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Live Designer Widget Library:', error);
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('widget-loading', 'Failed to load widgets');
            }
            this.showErrorState();
        }
    }

    /**
     * Load widgets from server-rendered data (no API call needed)
     */
    loadWidgetsFromServerData() {
        // Widgets are now pre-rendered in HTML, just setup interactions
        console.log('📦 Using server-rendered theme widgets');
    }

    /**
     * Load fallback widgets for development/testing (now only theme widgets)
     */
    loadFallbackWidgets() {
        console.log('📦 Loading fallback theme widget data...');
        
        const fallbackThemeWidgets = [
            {
                id: 'theme-1',
                name: 'Hero Section',
                slug: 'hero-section',
                description: 'Large hero banner with image background',
                icon: 'ri-landscape-line',
                category: 'sections',
                preview_image: '/assets/admin/img/widgets/hero-preview.jpg',
                default_settings: {}
            },
            {
                id: 'theme-2',
                name: 'Testimonial Card',
                slug: 'testimonial-card',
                description: 'Customer testimonial with photo',
                icon: 'ri-chat-quote-line',
                category: 'content',
                preview_image: '/assets/admin/img/widgets/testimonial-preview.jpg',
                default_settings: {}
            },
            {
                id: 'theme-3',
                name: 'Pricing Table',
                slug: 'pricing-table',
                description: 'Pricing plans comparison table',
                icon: 'ri-price-tag-3-line',
                category: 'business',
                preview_image: '/assets/admin/img/widgets/pricing-preview.jpg',
                default_settings: {}
            },
            {
                id: 'theme-4',
                name: 'Team Member',
                slug: 'team-member',
                description: 'Team member profile card',
                icon: 'ri-team-line',
                category: 'content',
                preview_image: '/assets/admin/img/widgets/team-preview.jpg',
                default_settings: {}
            },
            {
                id: 'theme-5',
                name: 'Call to Action',
                slug: 'call-to-action',
                description: 'Prominent call-to-action section',
                icon: 'ri-arrow-right-circle-line',
                category: 'marketing',
                preview_image: '/assets/admin/img/widgets/cta-preview.jpg',
                default_settings: {}
            }
        ];

        // Store fallback theme widgets
        this.widgets.set('all', fallbackThemeWidgets);
    }

    /**
     * Render widgets in the sidebar
     */
    renderWidgets() {
        if (!this.container) return;
        
        try {
            console.log('🎨 Rendering widgets in live designer sidebar...');
            
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
            
            // Render all widgets
            allWidgets.forEach(widget => {
                widgetsHtml += this.createWidgetHTML(widget);
            });

            this.container.innerHTML = widgetsHtml;
            
            console.log(`✅ Rendered ${allWidgets.length} widgets in live designer`);
            
        } catch (error) {
            console.error('❌ Error rendering widgets:', error);
            this.showErrorState();
        }
    }

    /**
     * Create HTML for a single theme widget item with preview image (like page-builder)
     */
    createWidgetHTML(widget) {
        // Check if widget has a valid preview image
        const hasPreviewImage = widget.preview_image && 
                               !widget.preview_image.includes('widget-placeholder.png') &&
                               widget.preview_image.trim() !== '';
        
        if (hasPreviewImage) {
            // Theme widget with preview image (page-builder style)
            return `
                <div class="theme-widget-item" 
                     data-widget-id="${widget.id}" 
                     data-widget-slug="${widget.slug}"
                     data-widget-category="${widget.category}"
                     data-widget-type="theme"
                     draggable="true"
                     title="${widget.description || widget.name}">
                    <div class="widget-preview">
                        <img src="${widget.preview_image}" alt="${widget.name}" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\\'${widget.icon || 'ri-apps-line'}\\'></i>';">
                    </div>
                    <div class="widget-title">${widget.name}</div>
                </div>
            `;
        } else {
            // Fallback to icon-based widget (like default widgets)
            return `
                <div class="component-item" 
                     data-widget-id="${widget.id}" 
                     data-widget-slug="${widget.slug}"
                     data-widget-category="${widget.category}"
                     data-widget-type="theme"
                     draggable="true"
                     title="${widget.description || widget.name}">
                    <i class="${widget.icon || 'ri-apps-line'}"></i>
                    <div class="label">${widget.name}</div>
                </div>
            `;
        }
    }

    /**
     * Setup drag and drop functionality
     */
    setupDragAndDrop() {
        if (!this.container) return;
        
        console.log('🎯 Setting up widget drag & drop...');
        
        // Add event listeners for drag and drop (both component-item and theme-widget-item)
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.matches('.component-item, .theme-widget-item') || 
                e.target.closest('.component-item, .theme-widget-item')) {
                this.handleDragStart(e);
            }
        });
        
        this.container.addEventListener('dragend', (e) => {
            if (e.target.matches('.component-item, .theme-widget-item') || 
                e.target.closest('.component-item, .theme-widget-item')) {
                this.handleDragEnd(e);
            }
        });
        
        console.log('✅ Widget drag & drop setup complete');
    }

    /**
     * Handle drag start event
     */
    handleDragStart(e) {
        const widgetItem = e.target.closest('.component-item, .theme-widget-item') || e.target;
        const widgetId = widgetItem.getAttribute('data-widget-id');
        const widgetSlug = widgetItem.getAttribute('data-widget-slug');
        const widgetCategory = widgetItem.getAttribute('data-widget-category');
        
        console.log('🎯 Widget drag started:', { widgetId, widgetSlug, widgetCategory });
        
        // Add dragging visual state
        widgetItem.classList.add('dragging');
        
        // Set drag data for iframe communication
        const dragData = {
            type: 'widget',
            widgetId: parseInt(widgetId),
            widgetSlug: widgetSlug,
            widgetCategory: widgetCategory,
            source: 'live-designer-widget-library'
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
        console.log('🎯 Widget drag ended');
        
        // Remove dragging visual state
        const widgetItem = e.target.closest('.component-item, .theme-widget-item') || e.target;
        widgetItem.classList.remove('dragging');
        
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
                canvasContainer.style.background = '#d4edda';
                canvasContainer.style.border = '2px dashed #28a745';
                canvasContainer.style.borderRadius = '8px';
            } else {
                canvasContainer.style.background = '#f1f4f7';
                canvasContainer.style.border = 'none';
                canvasContainer.style.borderRadius = '0';
            }
        }
    }

    /**
     * Handle widget click (add widget to live preview)
     */
    async handleWidgetClick(widgetItem) {
        const widgetId = widgetItem.getAttribute('data-widget-id');
        const widgetSlug = widgetItem.getAttribute('data-widget-slug');
        const widgetCategory = widgetItem.getAttribute('data-widget-category');
        
        console.log('🖱️ Widget clicked:', { widgetId, widgetSlug, widgetCategory });
        
        try {
            // Add visual feedback
            widgetItem.classList.add('loading');
            
            // Show loader
            if (this.unifiedLoader) {
                this.unifiedLoader.show('widget-add', `Adding ${widgetSlug} widget...`, 20);
            }
            
            // Show widget modal for configuration (similar to page-builder)
            await this.showWidgetModal(widgetId, widgetSlug);
            
            // Hide loader
            if (this.unifiedLoader) {
                this.unifiedLoader.hide('widget-add');
            }
            
        } catch (error) {
            console.error('❌ Error adding widget:', error);
            
            // Show error
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('widget-add', 'Failed to add widget');
            }
            
            if (this.livePreview?.showMessage) {
                this.livePreview.showMessage('Failed to add widget. Please try again.', 'error');
            }
        } finally {
            widgetItem.classList.remove('loading');
        }
    }

    /**
     * Show widget configuration modal
     */
    async showWidgetModal(widgetId, widgetSlug) {
        try {
            console.log('📋 Opening widget modal for:', { widgetId, widgetSlug });
            
            // Check if widget modal manager is available
            if (typeof window.widgetFormManager !== 'undefined' && window.widgetFormManager) {
                // Use existing widget form manager to open modal
                await window.widgetFormManager.openNewWidgetModal(widgetSlug, null, 'add');
            } else {
                console.warn('Widget form manager not available, adding widget directly');
                // Simple fallback - directly add widget without configuration
                await this.addWidgetToLivePreview(widgetSlug);
            }
            
        } catch (error) {
            console.error('❌ Error showing widget modal:', error);
            throw error;
        }
    }

    /**
     * Add widget to live preview using API
     */
    async addWidgetToLivePreview(widgetSlug, settings = {}) {
        try {
            console.log('🔨 Adding widget to live preview:', { widgetSlug, settings });
            
            // Get page ID from live preview
            const pageId = this.livePreview?.options?.pageId;
            if (!pageId) {
                throw new Error('Page ID not available');
            }
            
            // Call API to add widget (this would need to be implemented in the backend)
            const response = await fetch('/admin/api/live-preview/widgets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    page_id: pageId,
                    widget_slug: widgetSlug,
                    settings: settings,
                    position: 'bottom' // Add to bottom section
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    // Refresh live preview to show new widget
                    if (this.livePreview?.refreshPreview) {
                        await this.livePreview.refreshPreview();
                    }
                    
                    // Show success message
                    if (this.livePreview?.showMessage) {
                        this.livePreview.showMessage(`${widgetSlug} widget added successfully!`, 'success');
                    }
                    
                    return data.data;
                } else {
                    throw new Error(data.message || 'Failed to add widget');
                }
            } else {
                throw new Error(`API Error: ${response.status}`);
            }
            
        } catch (error) {
            console.error('❌ Error adding widget to live preview:', error);
            throw error;
        }
    }

    /**
     * Update widget count badge
     */
    updateWidgetCount() {
        const badge = document.getElementById('widgetsCount');
        if (badge && this.widgets) {
            let totalCount = 0;
            this.widgets.forEach(categoryWidgets => {
                totalCount += categoryWidgets.length;
            });
            badge.textContent = totalCount;
        }
    }

    /**
     * Show empty state when no widgets available
     */
    showEmptyState() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="component-loading">
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
            <div class="component-error">
                <i class="ri-error-warning-line error-icon"></i>
                <div class="error-message">Failed to load widgets</div>
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
        console.log('🔄 Refreshing Live Designer Widget Library...');
        await this.loadAvailableWidgets();
        this.renderWidgets();
        this.updateWidgetCount();
    }
}

// Export for global use
window.WidgetLibrary = WidgetLibrary;

console.log('📦 Live Designer Widget Library module loaded');