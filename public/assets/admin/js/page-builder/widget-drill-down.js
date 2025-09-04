/**
 * Page Builder Widget Drill-Down Navigation
 * 
 * Handles drill-down navigation in Page Builder theme widgets.
 * Provides three-level navigation: Widgets -> Content Types -> Content Items
 * 
 * Uses Page Builder specific API endpoints and data structure
 */
class PageBuilderWidgetDrillDown {
    constructor() {
        this.currentView = 'widgets';
        this.currentWidget = null;
        this.currentContentType = null;
        this.navigationHistory = [];
        
        console.log('🎯 Page Builder Widget Drill-Down initialized');
    }

    /**
     * Initialize drill-down functionality
     */
    init() {
        console.log('🔄 Setting up Page Builder widget drill-down navigation...');
        
        // Setup event delegation immediately
        this.setupEventDelegation();
        
        // Setup accordion listeners for lazy initialization
        this.setupAccordionListeners();
        
        console.log('✅ Page Builder widget drill-down navigation ready');
    }

    /**
     * Setup document-level event delegation
     */
    setupEventDelegation() {
        document.addEventListener('click', (e) => {
            // Drill-down button clicks
            const drillDownBtn = e.target.closest('.drill-down-btn') || e.target.closest('.expand-content-types-btn');
            if (drillDownBtn) {
                console.log('🎯 Page Builder drill-down button clicked!', drillDownBtn);
                e.preventDefault();
                e.stopPropagation();
                
                const widgetId = drillDownBtn.getAttribute('data-drill-widget-id');
                console.log('📦 Widget ID:', widgetId);
                
                if (widgetId) {
                    // Show loading view immediately
                    this.showLoadingView('content-types', 'Loading content types...');
                    
                    this.showContentTypes(widgetId);
                } else {
                    console.error('❌ No widget ID found on drill-down button');
                }
                return;
            }
            
            // Back button clicks
            if (e.target.closest('#drillDownBack')) {
                console.log('🔙 Back button clicked');
                e.preventDefault();
                this.navigateBack();
                return;
            }
            
            // Content type card clicks
            const contentTypeCard = e.target.closest('.content-type-card');
            if (contentTypeCard) {
                console.log('📋 Content type card clicked');
                e.preventDefault();
                const contentTypeId = contentTypeCard.getAttribute('data-content-type-id');
                
                // Show loading view immediately
                this.showLoadingView('content-items', 'Loading content items...');
                
                this.showContentItems(contentTypeId);
                return;
            }
        });
        
        console.log('✅ Page Builder event delegation setup complete');
    }

    /**
     * Setup accordion listeners for lazy initialization
     */
    setupAccordionListeners() {
        // Listen for Theme Widgets accordion opening
        const themeWidgetsAccordion = document.querySelector('[href="#themeWidgetsCollapse"]');
        if (themeWidgetsAccordion) {
            themeWidgetsAccordion.addEventListener('click', () => {
                setTimeout(() => {
                    this.checkDrillDownButtons();
                }, 200);
            });
        }
        
        // Also listen for Bootstrap collapse events
        const themeWidgetsCollapse = document.getElementById('themeWidgetsCollapse');
        if (themeWidgetsCollapse) {
            themeWidgetsCollapse.addEventListener('shown.bs.collapse', () => {
                this.checkDrillDownButtons();
            });
        }
    }

    /**
     * Check and log drill-down buttons after accordion opens
     */
    checkDrillDownButtons() {
        const drillDownButtons = document.querySelectorAll('.drill-down-btn, .expand-content-types-btn');
        console.log(`🔍 Found ${drillDownButtons.length} Page Builder drill-down buttons after accordion open`);
        
        drillDownButtons.forEach((btn, index) => {
            console.log(`Button ${index + 1}:`, {
                classes: btn.className,
                widgetId: btn.getAttribute('data-drill-widget-id'),
                element: btn
            });
        });
    }

    /**
     * Show content types for a widget - Page Builder API
     */
    async showContentTypes(widgetId) {
        try {
            console.log('📋 Loading content types for widget via Page Builder API:', widgetId);
            
            // Get widget info from DOM
            const widgetElement = document.querySelector(`[data-drill-widget-id="${widgetId}"]`);
            if (!widgetElement) {
                throw new Error('Widget element not found');
            }
            
            const widgetTitle = widgetElement.closest('.theme-widget-item, .component-item')
                ?.querySelector('.widget-title, .label')?.textContent || 'Widget';
            
            this.currentWidget = { id: widgetId, title: widgetTitle };
            
            // Fetch content types using Page Builder API endpoint
            console.log(`🌐 Fetching: /admin/api/page-builder/widgets/${widgetId}/content-types`);
            const response = await fetch(`/admin/api/page-builder/widgets/${widgetId}/content-types`);
            console.log('📡 Response status:', response.status, response.statusText);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.log('❌ Response body:', errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
            }
            
            const data = await response.json();
            console.log('📦 Page Builder response data:', data);
            
            // Check if response has success and data fields
            if (!data.success) {
                throw new Error(data.message || 'Failed to load content types');
            }
            
            const contentTypes = data.data || [];
            
            // Render content types
            this.renderContentTypesView(contentTypes);
            this.updateNavigation('content-types', `${widgetTitle} Content Types`);
            
            console.log('✅ Content types loaded successfully via Page Builder API');
            
        } catch (error) {
            console.error('❌ Error loading content types:', error);
            this.showErrorState('Failed to load content types: ' + error.message);
        }
    }

    /**
     * Show content items for a content type - Page Builder API
     */
    async showContentItems(contentTypeId) {
        try {
            console.log('📄 Loading content items for content type via Page Builder API:', contentTypeId);
            
            // Get content type info from DOM
            const contentTypeElement = document.querySelector(`[data-content-type-id="${contentTypeId}"]`);
            const contentTypeName = contentTypeElement?.querySelector('.content-type-name')?.textContent || 'Content Type';
            
            this.currentContentType = { id: contentTypeId, name: contentTypeName };
            
            // Fetch content items using Page Builder API endpoint
            console.log(`🌐 Fetching: /admin/api/page-builder/content-types/${contentTypeId}/items`);
            const response = await fetch(`/admin/api/page-builder/content-types/${contentTypeId}/items`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📦 Page Builder content items response data:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load content items');
            }
            
            // Extract items array from nested data structure
            const contentItems = data.data?.items || [];
            console.log('📄 Content items array:', contentItems);
            
            // Render content items
            this.renderContentItemsView(contentItems);
            this.updateNavigation('content-items', `${contentTypeName} Items`);
            
            console.log('✅ Content items loaded successfully via Page Builder API');
            
        } catch (error) {
            console.error('❌ Error loading content items:', error);
            this.showErrorState('Failed to load content items: ' + error.message);
        }
    }

    /**
     * Render content types view using existing templates
     */
    renderContentTypesView(contentTypes) {
        const contentTypesGrid = document.getElementById('contentTypesGrid');
        if (!contentTypesGrid) {
            console.error('❌ Content types grid container not found');
            return;
        }
        
        // Clear existing content
        contentTypesGrid.innerHTML = '';
        
        if (contentTypes.length === 0) {
            // Show empty state
            const emptyTemplate = document.querySelector('.content-types-empty-template');
            if (emptyTemplate) {
                const emptyClone = emptyTemplate.cloneNode(true);
                emptyClone.classList.remove('d-none', 'content-types-empty-template');
                contentTypesGrid.appendChild(emptyClone);
            }
        } else {
            // Populate content types using template
            const template = document.querySelector('.content-type-card-template');
            if (!template) {
                console.error('❌ Content type card template not found');
                return;
            }
            
            contentTypes.forEach(contentType => {
                const clone = template.cloneNode(true);
                clone.classList.remove('d-none', 'content-type-card-template');
                clone.classList.add('content-type-card', 'mb-2');
                clone.setAttribute('data-content-type-id', contentType.id);
                clone.style.cursor = 'pointer';
                
                // Populate data
                const iconElement = clone.querySelector('.content-type-icon-placeholder');
                if (iconElement) {
                    iconElement.className = contentType.icon || 'ri-folder-line';
                }
                
                const nameElement = clone.querySelector('.content-type-name');
                if (nameElement) {
                    nameElement.textContent = contentType.name;
                }
                
                const countElement = clone.querySelector('.content-type-count');
                if (countElement) {
                    countElement.textContent = `${contentType.items_count || 0} items`;
                }
                
                contentTypesGrid.appendChild(clone);
            });
        }
        
        console.log(`✅ Rendered ${contentTypes.length} content types for Page Builder`);
    }

    /**
     * Render content items view using existing templates
     */
    renderContentItemsView(contentItems) {
        const contentItemsGrid = document.getElementById('contentItemsGrid');
        if (!contentItemsGrid) {
            console.error('❌ Content items grid container not found');
            return;
        }
        
        // Clear existing content
        contentItemsGrid.innerHTML = '';
        
        if (contentItems.length === 0) {
            // Show empty state
            const emptyTemplate = document.querySelector('.content-items-empty-template');
            if (emptyTemplate) {
                const emptyClone = emptyTemplate.cloneNode(true);
                emptyClone.classList.remove('d-none', 'content-items-empty-template');
                contentItemsGrid.appendChild(emptyClone);
            }
        } else {
            // Populate content items using template
            const template = document.querySelector('.content-item-card-template');
            if (!template) {
                console.error('❌ Content item card template not found');
                return;
            }
            
            contentItems.forEach(item => {
                const clone = template.cloneNode(true);
                clone.classList.remove('d-none', 'content-item-card-template');
                clone.classList.add('content-item-card', 'mb-2');
                clone.setAttribute('data-content-item-id', item.id);
                clone.style.cursor = 'pointer';
                
                // Populate data with fallback handling
                const thumbnailElement = clone.querySelector('.content-item-thumbnail');
                if (thumbnailElement) {
                    const thumbnail = item.thumbnail || '/assets/admin/images/placeholder-image.png';
                    thumbnailElement.src = thumbnail;
                    thumbnailElement.alt = item.title || 'Content Item';
                    thumbnailElement.onerror = function() {
                        this.src = '/assets/admin/images/placeholder-image.png';
                    };
                }
                
                const titleElement = clone.querySelector('.content-item-title');
                if (titleElement) {
                    titleElement.textContent = item.title || 'Untitled';
                }
                
                const statusElement = clone.querySelector('.content-item-status');
                if (statusElement) {
                    const status = item.status || 'draft';
                    statusElement.textContent = status;
                    // Update badge color based on status
                    statusElement.className = `badge content-item-status ${
                        status === 'published' ? 'bg-success-subtle text-success' :
                        status === 'draft' ? 'bg-warning-subtle text-warning' :
                        'bg-secondary-subtle text-secondary'
                    }`;
                }
                
                contentItemsGrid.appendChild(clone);
            });
        }
        
        console.log(`✅ Rendered ${contentItems.length} content items for Page Builder`);
    }

    /**
     * Update navigation state and UI
     */
    updateNavigation(view, breadcrumbLabel) {
        // Store previous state for back navigation
        this.navigationHistory.push({
            view: this.currentView,
            widget: this.currentWidget,
            contentType: this.currentContentType
        });
        
        // Update current view
        this.currentView = view;
        
        // Get view containers
        const widgetsView = document.getElementById('widgetsView');
        const contentTypesView = document.getElementById('contentTypesView');
        const contentItemsView = document.getElementById('contentItemsView');
        const breadcrumbContainer = document.getElementById('drillDownBreadcrumb');
        const breadcrumbText = document.getElementById('breadcrumbText');
        
        // Show/hide views
        if (widgetsView) widgetsView.style.display = view === 'widgets' ? 'block' : 'none';
        if (contentTypesView) contentTypesView.style.display = view === 'content-types' ? 'block' : 'none';
        if (contentItemsView) contentItemsView.style.display = view === 'content-items' ? 'block' : 'none';
        
        // Update breadcrumb
        if (breadcrumbContainer) {
            breadcrumbContainer.style.display = view === 'widgets' ? 'none' : 'flex';
        }
        if (breadcrumbText) {
            breadcrumbText.textContent = breadcrumbLabel;
        }
        
        console.log(`🧭 Page Builder navigation updated to: ${view}`);
    }

    /**
     * Navigate back to previous view
     */
    navigateBack() {
        if (this.navigationHistory.length === 0) {
            console.log('🔙 No navigation history, returning to widgets view');
            this.showWidgetsView();
            return;
        }
        
        const previousState = this.navigationHistory.pop();
        
        // Restore previous state
        this.currentView = previousState.view;
        this.currentWidget = previousState.widget;
        this.currentContentType = previousState.contentType;
        
        // Update UI based on previous view
        switch (previousState.view) {
            case 'widgets':
                this.showWidgetsView();
                break;
            case 'content-types':
                if (this.currentWidget) {
                    this.updateNavigation('content-types', `${this.currentWidget.title} Content Types`);
                }
                break;
        }
        
        console.log(`🔙 Page Builder navigated back to: ${previousState.view}`);
    }

    /**
     * Show widgets view (reset to initial state)
     */
    showWidgetsView() {
        this.currentView = 'widgets';
        this.currentWidget = null;
        this.currentContentType = null;
        this.navigationHistory = [];
        
        // Get view containers
        const widgetsView = document.getElementById('widgetsView');
        const contentTypesView = document.getElementById('contentTypesView');
        const contentItemsView = document.getElementById('contentItemsView');
        const breadcrumbContainer = document.getElementById('drillDownBreadcrumb');
        
        // Show/hide views
        if (widgetsView) widgetsView.style.display = 'block';
        if (contentTypesView) contentTypesView.style.display = 'none';
        if (contentItemsView) contentItemsView.style.display = 'none';
        
        // Hide breadcrumb
        if (breadcrumbContainer) {
            breadcrumbContainer.style.display = 'none';
        }
        
        console.log('🏠 Page Builder returned to widgets view');
    }

    /**
     * Show loading view for specific view type
     */
    showLoadingView(viewType, message) {
        // Update navigation first
        if (viewType === 'content-types') {
            this.updateNavigation('content-types', 'Loading...');
        } else if (viewType === 'content-items') {
            this.updateNavigation('content-items', 'Loading...');
        }
        
        // Show loading state in the appropriate grid
        const targetGrid = viewType === 'content-types' ? 
            document.getElementById('contentTypesGrid') : 
            document.getElementById('contentItemsGrid');
            
        if (!targetGrid) {
            console.error(`❌ ${viewType} grid not found`);
            return;
        }
        
        const loadingTemplate = document.querySelector('.content-loading-template');
        if (!loadingTemplate) {
            console.error('❌ Loading template not found');
            return;
        }
        
        const loadingClone = loadingTemplate.cloneNode(true);
        loadingClone.classList.remove('d-none', 'content-loading-template');
        
        // Update loading message
        const messageElement = loadingClone.querySelector('.loading-message');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        targetGrid.innerHTML = '';
        targetGrid.appendChild(loadingClone);
    }

    /**
     * Show error state using existing templates
     */
    showErrorState(message) {
        const contentTypesGrid = document.getElementById('contentTypesGrid');
        const contentItemsGrid = document.getElementById('contentItemsGrid');
        
        const errorTemplate = document.querySelector('.content-error-template');
        if (!errorTemplate) {
            console.error('❌ Error template not found');
            return;
        }
        
        const errorClone = errorTemplate.cloneNode(true);
        errorClone.classList.remove('d-none', 'content-error-template');
        
        // Update error message
        const messageElement = errorClone.querySelector('.error-message');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        if (this.currentView === 'content-types' && contentTypesGrid) {
            contentTypesGrid.innerHTML = '';
            contentTypesGrid.appendChild(errorClone);
        } else if (this.currentView === 'content-items' && contentItemsGrid) {
            contentItemsGrid.innerHTML = '';
            contentItemsGrid.appendChild(errorClone);
        }
        
        console.log('❌ Page Builder error state displayed:', message);
    }
}

// Initialize on DOM ready - Page Builder specific
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a Page Builder page
    if (document.getElementById('leftSidebarContainer') && 
        document.getElementById('themeWidgetsCollapse')) {
        window.pageBuilderWidgetDrillDown = new PageBuilderWidgetDrillDown();
        window.pageBuilderWidgetDrillDown.init();
        console.log('✅ Page Builder Widget Drill-Down initialized');
    } else {
        console.log('🚫 Page Builder Widget Drill-Down not initialized - required elements not found');
    }
});

console.log('📦 Page Builder Widget Drill-Down module loaded');