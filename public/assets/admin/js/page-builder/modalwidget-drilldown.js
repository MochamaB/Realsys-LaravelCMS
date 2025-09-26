/**
 * Page Builder Modal Widget Drill-Down
 *
 * Fixed version that properly handles drill-down navigation in the modal
 */
class PageBuilderModalWidgetDrillDown {
    constructor() {
        this.modalState = {
            selectedWidget: null,
            targetSectionData: null,
            currentView: 'widgets',
            currentWidget: null,
            currentContentType: null,
            navigationHistory: []
        };

        this.modal = null;
        this.addBtn = null;
        this.widgetInfo = null;
        this.targetSectionInfo = null;

        console.log('Modal Widget Drill-Down initialized');
    }

    /**
     * Initialize the modal functionality
     */
    init() {
        // Get DOM elements
        this.modal = document.getElementById('addWidgetModal');
        this.addBtn = document.getElementById('addSelectedWidgetBtn');
        this.widgetInfo = document.getElementById('selectedWidgetInfo');
        this.targetSectionInfo = document.getElementById('targetSectionInfo');

        if (!this.modal || !this.addBtn) {
            console.error('Required modal elements not found');
            return;
        }

        // Setup event listeners
        this.setupEventListeners();

        console.log('Modal Widget Drill-Down ready');
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Widget selection - only for modal context
        document.addEventListener('click', (e) => {
            if (!e.target || typeof e.target.closest !== 'function') return;
            
            // Only handle clicks inside the modal
            if (!e.target.closest('#addWidgetModal')) return;

            const widgetCard = e.target.closest('.widget-card');
            if (widgetCard) {
                // Skip if clicking drill-down button
                if (e.target.closest('.drill-down-btn')) return;
                this.selectWidget(widgetCard);
                return;
            }

            // Drill-down navigation
            const drillDownBtn = e.target.closest('.drill-down-btn');
            if (drillDownBtn) {
                e.preventDefault();
                e.stopPropagation();
                const widgetId = drillDownBtn.getAttribute('data-drill-widget-id');
                console.log('Drill-down clicked for widget:', widgetId);
                this.showContentTypes(widgetId);
                return;
            }

            // Back button
            if (e.target.closest('#modalDrillDownBack')) {
                e.preventDefault();
                this.navigateBack();
                return;
            }

            // Content type card clicks
            const contentTypeCard = e.target.closest('.content-type-card');
            if (contentTypeCard) {
                e.preventDefault();
                const contentTypeId = contentTypeCard.getAttribute('data-content-type-id');
                this.showContentItems(contentTypeId);
                return;
            }
        });

        // Add widget button
        this.addBtn.addEventListener('click', this.handleAddWidget.bind(this));

        // Modal reset on close
        this.modal.addEventListener('hidden.bs.modal', this.resetModal.bind(this));

        // Hover effects for widget cards
        document.addEventListener('mouseenter', (e) => {
            if (!e.target || typeof e.target.closest !== 'function') return;
            if (!e.target.closest('#addWidgetModal')) return;

            const widgetCard = e.target.closest('.widget-card');
            if (widgetCard && !widgetCard.classList.contains('border-primary')) {
                widgetCard.style.borderColor = '#0d6efd';
                widgetCard.style.transform = 'translateY(-2px)';
                widgetCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            }
        }, true);

        document.addEventListener('mouseleave', (e) => {
            if (!e.target || typeof e.target.closest !== 'function') return;
            if (!e.target.closest('#addWidgetModal')) return;

            const widgetCard = e.target.closest('.widget-card');
            if (widgetCard && !widgetCard.classList.contains('border-primary')) {
                widgetCard.style.borderColor = '';
                widgetCard.style.transform = '';
                widgetCard.style.boxShadow = '';
            }
        }, true);

        console.log('Modal drill-down event handlers attached');
    }

    /**
     * Show content types for a widget
     */
    async showContentTypes(widgetId) {
        try {
            console.log('Loading content types for widget:', widgetId);
            
            // Find widget data from DOM
            const widgetElement = document.querySelector(`#addWidgetModal [data-drill-widget-id="${widgetId}"]`);
            if (!widgetElement) {
                throw new Error('Widget element not found');
            }
            
            const widgetCard = widgetElement.closest('.widget-card');
            const widgetTitle = widgetCard?.querySelector('.widget-title, .card-title')?.textContent || 'Widget';
            
            this.modalState.currentWidget = { id: widgetId, title: widgetTitle };
            
            // Show loading
            this.showLoadingInGrid('modalContentTypesGrid', 'Loading content types...');
            
            // Switch views
            this.updateModalViews('content-types');
            this.updateBreadcrumb(`${widgetTitle} Content Types`);
            
            // Fetch content types
            console.log(`Fetching: /admin/api/page-builder/widgets/${widgetId}/content-types`);
            const response = await fetch(`/admin/api/page-builder/widgets/${widgetId}/content-types`);
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
            }
            
            const data = await response.json();
            console.log('API response data:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load content types');
            }
            
            const contentTypes = data.data || [];
            this.populateContentTypes(contentTypes);
            
            console.log('Content types loaded successfully');
            
        } catch (error) {
            console.error('Error loading content types:', error);
            this.showErrorInGrid('modalContentTypesGrid', 'Failed to load content types: ' + error.message);
        }
    }

    /**
     * Show content items for a content type
     */
    async showContentItems(contentTypeId) {
        try {
            console.log('Loading content items for content type:', contentTypeId);
            
            const contentTypeElement = document.querySelector(`[data-content-type-id="${contentTypeId}"]`);
            const contentTypeName = contentTypeElement?.querySelector('.content-type-name')?.textContent || 'Content Type';
            
            this.modalState.currentContentType = { id: contentTypeId, name: contentTypeName };
            
            // Show loading
            this.showLoadingInGrid('modalContentItemsGrid', 'Loading content items...');
            
            // Switch views
            this.updateModalViews('content-items');
            this.updateBreadcrumb(`${contentTypeName} Items`);
            
            // Fetch content items
            const response = await fetch(`/admin/api/page-builder/content-types/${contentTypeId}/items`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load content items');
            }
            
            const contentItems = data.data?.items || [];
            this.populateContentItems(contentItems);
            
            console.log('Content items loaded successfully');
            
        } catch (error) {
            console.error('Error loading content items:', error);
            this.showErrorInGrid('modalContentItemsGrid', 'Failed to load content items: ' + error.message);
        }
    }

    /**
     * Populate content types grid using template
     */
    populateContentTypes(contentTypes) {
        const grid = document.getElementById('modalContentTypesGrid');
        
        if (!contentTypes || contentTypes.length === 0) {
            this.showEmptyState(grid, 'No content types available for this widget.');
            return;
        }

        // Find template - check both possible locations
        let template = document.getElementById('contentTypeCardTemplate');
        if (!template) {
            template = document.querySelector('.content-type-card-template');
        }
        
        if (!template) {
            console.error('Content type card template not found');
            grid.innerHTML = '<div class="col-12"><p class="text-center text-muted">Template not found</p></div>';
            return;
        }

        // Create row wrapper for Bootstrap grid
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row g-3';

        contentTypes.forEach(contentType => {
            const cardClone = template.cloneNode(true);
            cardClone.classList.remove('d-none');
            cardClone.removeAttribute('id');
            
            // Find the actual card element
            const card = cardClone.querySelector('.content-type-card') || cardClone;
            card.setAttribute('data-content-type-id', contentType.id);
            card.style.cursor = 'pointer';
            
            // Add hover effects
            card.addEventListener('mouseenter', () => {
                card.style.borderColor = '#0d6efd';
                card.style.transform = 'translateY(-2px)';
                card.style.boxShadow = '0 2px 8px rgba(0,123,255,0.1)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.borderColor = '';
                card.style.transform = '';
                card.style.boxShadow = '';
            });

            // Populate data - handle both template structures
            const iconElement = cardClone.querySelector('.content-type-icon') || 
                               cardClone.querySelector('.content-type-icon-placeholder');
            if (iconElement) {
                iconElement.className = contentType.icon || 'ri-file-list-line';
            }

            const nameElement = cardClone.querySelector('.content-type-name');
            if (nameElement) {
                nameElement.textContent = contentType.name;
            }

            const countElement = cardClone.querySelector('.content-type-count');
            if (countElement) {
                countElement.textContent = `${contentType.items_count || 0} items available`;
            }

            rowDiv.appendChild(cardClone);
        });

        grid.innerHTML = '';
        grid.appendChild(rowDiv);
    }

    /**
     * Populate content items grid
     */
    populateContentItems(contentItems) {
        const grid = document.getElementById('modalContentItemsGrid');
        
        if (!contentItems || contentItems.length === 0) {
            this.showEmptyState(grid, 'No content items available for this content type.');
            return;
        }

        // Find template
        let template = document.getElementById('contentItemCardTemplate');
        if (!template) {
            template = document.querySelector('.content-item-card-template');
        }
        
        if (!template) {
            console.error('Content item card template not found');
            grid.innerHTML = '<div class="col-12"><p class="text-center text-muted">Template not found</p></div>';
            return;
        }

        // Create row wrapper
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row g-3';

        contentItems.forEach(item => {
            const cardClone = template.cloneNode(true);
            cardClone.classList.remove('d-none');
            cardClone.removeAttribute('id');
            
            const card = cardClone.querySelector('.content-item-card') || cardClone;
            card.setAttribute('data-content-item-id', item.id);
            card.style.cursor = 'pointer';

            // Populate data
            const titleElement = cardClone.querySelector('.content-item-title');
            if (titleElement) {
                titleElement.textContent = item.title || 'Untitled';
            }

            const excerptElement = cardClone.querySelector('.content-item-excerpt');
            if (excerptElement) {
                excerptElement.textContent = item.excerpt || 'No description available';
            }

            const metaElement = cardClone.querySelector('.content-item-meta');
            if (metaElement) {
                metaElement.textContent = item.status || 'draft';
            }

            rowDiv.appendChild(cardClone);
        });

        grid.innerHTML = '';
        grid.appendChild(rowDiv);
    }

    /**
     * Update modal views visibility
     */
    updateModalViews(currentView) {
        // Store navigation history
        this.modalState.navigationHistory.push({
            view: this.modalState.currentView,
            widget: this.modalState.currentWidget,
            contentType: this.modalState.currentContentType
        });
        
        this.modalState.currentView = currentView;

        // Update view visibility
        const widgetsView = document.getElementById('modalThemeWidgetsView');
        const contentTypesView = document.getElementById('modalContentTypesView');
        const contentItemsView = document.getElementById('modalContentItemsView');

        if (widgetsView) widgetsView.style.display = currentView === 'widgets' ? 'block' : 'none';
        if (contentTypesView) contentTypesView.style.display = currentView === 'content-types' ? 'block' : 'none';
        if (contentItemsView) contentItemsView.style.display = currentView === 'content-items' ? 'block' : 'none';
    }

    /**
     * Navigate back in drill-down
     */
    navigateBack() {
        if (this.modalState.navigationHistory.length === 0) {
            this.resetToWidgetsView();
            return;
        }

        const previousState = this.modalState.navigationHistory.pop();
        
        this.modalState.currentView = previousState.view;
        this.modalState.currentWidget = previousState.widget;
        this.modalState.currentContentType = previousState.contentType;

        if (previousState.view === 'widgets') {
            this.resetToWidgetsView();
        } else if (previousState.view === 'content-types') {
            this.updateModalViews('content-types');
            this.updateBreadcrumb(`${this.modalState.currentWidget.title} Content Types`);
        }

        this.clearSelection();
    }

    /**
     * Reset to widgets view
     */
    resetToWidgetsView() {
        this.modalState.currentView = 'widgets';
        this.modalState.currentWidget = null;
        this.modalState.currentContentType = null;

        document.getElementById('modalThemeWidgetsView').style.display = 'block';
        document.getElementById('modalContentTypesView').style.display = 'none';
        document.getElementById('modalContentItemsView').style.display = 'none';
        
        this.hideBreadcrumb();
    }

    /**
     * Show loading state in grid
     */
    showLoadingInGrid(gridId, message) {
        const grid = document.getElementById(gridId);
        if (!grid) return;

        // Try to find loading template
        let template = document.getElementById('loadingTemplate');
        if (!template) {
            // Create simple loading HTML
            grid.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">${message}</p>
                    </div>
                </div>
            `;
            return;
        }

        const loadingClone = template.cloneNode(true);
        loadingClone.classList.remove('d-none');
        loadingClone.removeAttribute('id');
        
        const messageElement = loadingClone.querySelector('.loading-message');
        if (messageElement) {
            messageElement.textContent = message;
        }

        grid.innerHTML = '';
        grid.appendChild(loadingClone);
    }

    /**
     * Show empty state in grid
     */
    showEmptyState(grid, message) {
        grid.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="ri-inbox-line display-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-2">No Data Available</h5>
                    <p class="text-muted">${message}</p>
                </div>
            </div>
        `;
    }

    /**
     * Show error state in grid
     */
    showErrorInGrid(gridId, message) {
        const grid = document.getElementById(gridId);
        if (!grid) return;

        grid.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="ri-error-warning-line display-1 text-danger"></i>
                    </div>
                    <h5 class="text-danger mb-2">Error Loading Data</h5>
                    <p class="text-muted mb-3">${message}</p>
                    <button class="btn btn-outline-primary btn-sm" onclick="window.pageBuilderModalWidgetDrillDown.navigateBack()">
                        <i class="ri-arrow-left-line me-1"></i>Go Back
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Update breadcrumb
     */
    updateBreadcrumb(text) {
        const breadcrumbText = document.getElementById('modalBreadcrumbText');
        const breadcrumb = document.getElementById('modalDrillDownBreadcrumb');
        
        if (breadcrumbText) breadcrumbText.textContent = text;
        if (breadcrumb) breadcrumb.style.display = 'block';
    }

    /**
     * Hide breadcrumb
     */
    hideBreadcrumb() {
        const breadcrumb = document.getElementById('modalDrillDownBreadcrumb');
        if (breadcrumb) breadcrumb.style.display = 'none';
    }

    /**
     * Select a widget
     */
    selectWidget(widgetCard) {
        this.clearAllWidgetSelections();

        widgetCard.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        widgetCard.style.borderWidth = '2px';
        widgetCard.style.transform = 'translateY(-2px)';
        widgetCard.style.boxShadow = '0 4px 12px rgba(13, 110, 253, 0.25)';

        this.modalState.selectedWidget = {
            id: widgetCard.dataset.widgetId,
            slug: widgetCard.dataset.widgetSlug,
            name: widgetCard.dataset.widgetName,
            type: widgetCard.dataset.widgetType
        };

        this.updateSelectionUI();
        console.log('Widget selected:', this.modalState.selectedWidget);
    }

    /**
     * Clear all widget selections
     */
    clearAllWidgetSelections() {
        document.querySelectorAll('#addWidgetModal .widget-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            card.style.borderWidth = '';
            card.style.borderColor = '';
            card.style.transform = '';
            card.style.boxShadow = '';
        });
    }

    /**
     * Update selection UI
     */
    updateSelectionUI() {
        if (this.modalState.selectedWidget) {
            this.addBtn.disabled = false;
            this.widgetInfo.innerHTML = `<strong>${this.modalState.selectedWidget.name}</strong> (${this.modalState.selectedWidget.type}) selected`;
        } else {
            this.addBtn.disabled = true;
            this.widgetInfo.textContent = 'Select a widget to continue';
        }
    }

    /**
     * Clear selection
     */
    clearSelection() {
        this.modalState.selectedWidget = null;
        this.updateSelectionUI();
        this.clearAllWidgetSelections();
    }

    /**
     * Handle add widget button click
     */
    handleAddWidget() {
        if (this.modalState.selectedWidget && this.modalState.targetSectionData) {
            console.log('Adding widget:', this.modalState.selectedWidget, 'to section:', this.modalState.targetSectionData.id);

            alert(`Widget "${this.modalState.selectedWidget.name}" will be added to section ${this.modalState.targetSectionData.id}!\n\nWidget ID: ${this.modalState.selectedWidget.id}\nType: ${this.modalState.selectedWidget.type}\n\nAPI integration coming in next phase.`);

            bootstrap.Modal.getInstance(this.modal).hide();
        }
    }

    /**
     * Reset modal to initial state
     */
    resetModal() {
        this.resetToWidgetsView();
        this.modalState.navigationHistory = [];
        this.modalState.targetSectionData = null;
        this.clearSelection();
        
        if (this.targetSectionInfo) {
            this.targetSectionInfo.textContent = '';
        }
    }

    /**
     * Set section context for adding widget
     */
    setAddWidgetContext(sectionData) {
        this.modalState.targetSectionData = sectionData;
        if (this.targetSectionInfo) {
            this.targetSectionInfo.textContent = `Adding widget to: Section ${sectionData.id}`;
        }
        console.log('Add widget context set for section:', sectionData.id);
    }
}

// Global instance
window.pageBuilderModalWidgetDrillDown = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('modalwidget-drilldown.js DOMContentLoaded fired');

    const modalElement = document.getElementById('addWidgetModal');
    console.log('Looking for addWidgetModal:', modalElement ? 'Found' : 'Not found');

    if (modalElement) {
        window.pageBuilderModalWidgetDrillDown = new PageBuilderModalWidgetDrillDown();
        window.pageBuilderModalWidgetDrillDown.init();

        // Set up global method for parent communicator
        window.setAddWidgetContext = function(sectionData) {
            if (window.pageBuilderModalWidgetDrillDown) {
                window.pageBuilderModalWidgetDrillDown.setAddWidgetContext(sectionData);
            }
        };

        console.log('PageBuilder Modal Widget Drill-Down initialized and assigned to window');
    } else {
        console.log('PageBuilder Modal Widget Drill-Down not initialized - addWidgetModal not found');
    }
});

console.log('PageBuilder Modal Widget Drill-Down module loaded');