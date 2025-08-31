/**
 * Widget Modal Manager - Handles multi-step widget addition modal
 * Based on the Step-by-Step Widget Modal Implementation Plan
 */
class WidgetModalManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.currentStep = 1;
        this.totalSteps = 3;
        this.modalData = {
            selectedWidget: null,
            selectedContentType: null,
            selectedItems: [],
            contentQuery: null,
            widgetConfig: {},
            sectionId: null,
            sectionName: null
        };
        
        this.modal = null;
        this.init();
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('widgetContentModal'));
        this.setupEventListeners();
        this.initialized = true;
    }
    
    
    setupEventListeners() {
        // Step navigation buttons
        document.getElementById('nextStepBtn')?.addEventListener('click', () => this.nextStep());
        document.getElementById('prevStepBtn')?.addEventListener('click', () => this.previousStep());
        
        // Final submission button
        document.getElementById('addWidgetToSectionBtn')?.addEventListener('click', () => this.handleFinalSubmission());
        
        // Step indicator clicks
        document.querySelectorAll('.step-indicator').forEach(indicator => {
            indicator.addEventListener('click', (e) => {
                const step = parseInt(e.currentTarget.dataset.step);
                if (this.canNavigateToStep(step)) {
                    this.goToStep(step);
                }
            });
        });

        // Modal reset on close
        document.getElementById('widgetContentModal')?.addEventListener('hidden.bs.modal', () => {
            this.resetModal();
        });
    }

    // Public methods
    openForSection(sectionId, sectionName) {
        // Ensure we have valid data
        if (!sectionId) {
            console.error('Section ID is required to open widget modal');
            this.showError('Section ID is required');
            return;
        }

        // Use fallback if section name is not provided
        const displayName = sectionName || `Section ${sectionId}`;
        
        this.modalData.sectionId = sectionId;
        this.modalData.sectionName = displayName;
        
        console.log('Opening modal for section:', { sectionId, sectionName: displayName });
        
        // Update section info in modal
        const targetIdElement = document.getElementById('targetSectionId');
        const targetNameElement = document.getElementById('targetSectionName');
        
        if (targetIdElement) targetIdElement.value = sectionId;
        if (targetNameElement) targetNameElement.textContent = displayName;
        
        // Reset to step 1
        this.goToStep(1);
        
        // Load widgets for step 1
        this.loadWidgetLibrary();
        
        // Show modal
        this.modal.show();
    }

    // Step navigation
    nextStep() {
        if (!this.canGoToNextStep()) {
            this.showError('Please complete the current step before continuing.');
            return;
        }

        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.renderCurrentStep();
            this.updateProgressBar();
        }
    }

    previousStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.renderCurrentStep();
            this.updateProgressBar();
        }
    }

    goToStep(step) {
        if (step >= 1 && step <= this.totalSteps && this.canNavigateToStep(step)) {
            this.currentStep = step;
            this.renderCurrentStep();
            this.updateProgressBar();
        }
    }

    renderCurrentStep() {
        // Hide all steps
        document.querySelectorAll('.modal-step').forEach(step => {
            step.style.display = 'none';
        });

        // Show current step
        const currentStepEl = document.getElementById(`step${this.currentStep}`);
        if (currentStepEl) {
            currentStepEl.style.display = 'block';
        }

        // Update navigation buttons
        this.updateNavigationButtons();

        // Load step-specific content
        this.loadStepContent();
    }

    updateProgressBar() {
        // Update step indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            const stepNumber = index + 1;
            indicator.classList.remove('active', 'completed');
            
            if (stepNumber < this.currentStep) {
                indicator.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                indicator.classList.add('active');
            }
        });
    }

    updateNavigationButtons() {
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const addBtn = document.getElementById('addWidgetToSectionBtn');

        // Previous button
        if (prevBtn) {
            prevBtn.disabled = this.currentStep === 1;
        }

        // Next/Add buttons
        if (this.currentStep === this.totalSteps) {
            // Final step - show Add button, hide Next
            if (nextBtn) nextBtn.style.display = 'none';
            if (addBtn) addBtn.style.display = 'inline-block';
        } else {
            // Not final step - show Next button, hide Add
            if (nextBtn) nextBtn.style.display = 'inline-block';
            if (addBtn) addBtn.style.display = 'none';
            if (nextBtn) nextBtn.disabled = !this.canGoToNextStep();
        }
    }

    // Step content loading
    loadStepContent() {
        switch (this.currentStep) {
            case 1:
                this.loadWidgetLibrary();
                break;
            case 2:
                if (this.modalData.selectedWidget?.supports_content) {
                    this.loadContentTypes();
                } else {
                    // For non-content widgets, show message and allow navigation to step 3
                    this.showNoContentTypes();
                }
                break;
            case 3:
                if (this.modalData.selectedWidget?.supports_content) {
                    this.loadContentItems();
                } else {
                    // For non-content widgets, go directly to final submission
                    this.loadReviewStep();
                }
                break;
        }
    }

    // API calls
    async loadWidgetLibrary() {
        try {
            this.showWidgetLibraryLoading();
            
            const response = await fetch(`${this.apiBaseUrl}/widgets/available`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.renderWidgetLibrary(data.data);
            } else {
                throw new Error(data.error || 'Failed to load widgets');
            }
        } catch (error) {
            console.error('Error loading widget library:', error);
            this.showError('Failed to load widget library: ' + error.message);
        }
    }

    renderWidgetLibrary(data) {
        const libraryGrid = document.getElementById('widgetLibraryGrid');
        const loadingEl = document.getElementById('widgetLibraryLoading');
        
        if (!libraryGrid || !loadingEl) return;

        // Hide loading state
        loadingEl.style.display = 'none';
        
        // Clear existing content
        libraryGrid.innerHTML = '';
        
        // Render widget categories
        Object.keys(data.widgets).forEach(category => {
            const widgets = data.widgets[category];
            if (widgets.length === 0) return;

            const categorySection = this.createWidgetCategorySection(category, widgets);
            libraryGrid.appendChild(categorySection);
        });

        // Show the grid
        libraryGrid.style.display = 'block';
        
        // Setup widget selection handlers
        this.setupWidgetSelectionHandlers();
    }

    createWidgetCategorySection(category, widgets) {
        const section = document.createElement('div');
        section.className = 'widget-category mb-4';
        
        const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
        
        section.innerHTML = `
            <h6 class="widget-category-title mb-3">
                <i class="ri-folder-line me-2"></i>${categoryName} Widgets
                <span class="badge bg-secondary ms-2">${widgets.length}</span>
            </h6>
            <div class="widget-grid row g-3">
                ${widgets.map(widget => this.createWidgetCard(widget)).join('')}
            </div>
        `;
        
        return section;
    }

    createWidgetCard(widget) {
        return `
            <div class="col-md-6 col-lg-4">
                <div class="widget-library-item border rounded p-3 h-100" 
                     style="cursor: pointer; transition: all 0.2s;"
                     data-widget-id="${widget.id}"
                     data-widget='${JSON.stringify(widget)}'>
                    <div class="d-flex align-items-start mb-2">
                        <i class="${widget.icon} me-3 fs-4 text-primary"></i>
                        <div class="flex-grow-1">
                            <h6 class="widget-name mb-1">${widget.name}</h6>
                            <p class="widget-description text-muted mb-2 small">${widget.description || 'No description available'}</p>
                        </div>
                    </div>
                    
                    <div class="widget-meta">
                        ${widget.supports_content ? 
                            `<span class="badge bg-info me-1">Content</span>` : 
                            `<span class="badge bg-secondary me-1">Static</span>`
                        }
                        <span class="badge bg-light text-dark">${widget.field_count} fields</span>
                    </div>
                </div>
            </div>
        `;
    }

    setupWidgetSelectionHandlers() {
        document.querySelectorAll('.widget-library-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const widgetData = JSON.parse(e.currentTarget.dataset.widget);
                this.handleWidgetSelection(widgetData);
            });

            // Hover effects
            item.addEventListener('mouseenter', () => {
                item.style.borderColor = '#007bff';
                item.style.transform = 'translateY(-2px)';
                item.style.boxShadow = '0 4px 8px rgba(0,123,255,0.2)';
            });

            item.addEventListener('mouseleave', () => {
                if (!item.classList.contains('selected')) {
                    item.style.borderColor = '';
                    item.style.transform = '';
                    item.style.boxShadow = '';
                }
            });
        });
    }

    handleWidgetSelection(widget) {
        // Remove previous selection
        document.querySelectorAll('.widget-library-item').forEach(item => {
            item.classList.remove('selected');
            item.style.borderColor = '';
            item.style.transform = '';
            item.style.boxShadow = '';
        });

        // Mark as selected
        const selectedItem = document.querySelector(`[data-widget-id="${widget.id}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
            selectedItem.style.borderColor = '#28a745';
            selectedItem.style.transform = 'translateY(-2px)';
            selectedItem.style.boxShadow = '0 4px 8px rgba(40,167,69,0.3)';
        }

        // Store selection
        this.modalData.selectedWidget = widget;

        // Update preview panel
        this.updateWidgetPreview(widget);

        // Enable next button
        this.updateNavigationButtons();
    }

    updateWidgetPreview(widget) {
        const noSelectionEl = document.getElementById('noWidgetSelected');
        const previewEl = document.getElementById('selectedWidgetPreview');
        
        if (!noSelectionEl || !previewEl) return;

        // Hide no selection state
        noSelectionEl.style.display = 'none';
        
        // Update preview content
        document.getElementById('selectedWidgetIcon').className = widget.icon + ' me-2 text-primary';
        document.getElementById('selectedWidgetName').textContent = widget.name;
        document.getElementById('selectedWidgetDescription').textContent = widget.description || 'No description available';
        
        // Update content types
        const contentTypesEl = document.getElementById('selectedWidgetContentTypes');
        if (contentTypesEl) {
            contentTypesEl.innerHTML = widget.content_types.length > 0 
                ? widget.content_types.map(type => `<span class="badge bg-info me-1">${type}</span>`).join('')
                : '<span class="text-muted">None</span>';
        }
        
        // Update field count
        document.getElementById('selectedWidgetFieldCount').textContent = widget.field_count;
        
        // Update supports content
        const supportsContentEl = document.getElementById('selectedWidgetSupportsContent');
        if (supportsContentEl) {
            supportsContentEl.textContent = widget.supports_content ? 'Yes' : 'No';
            supportsContentEl.className = widget.supports_content ? 'badge bg-success' : 'badge bg-secondary';
        }
        
        // Update preview image
        const previewImageEl = document.getElementById('selectedWidgetPreviewImage');
        const placeholderEl = document.getElementById('selectedWidgetPreviewPlaceholder');
        
        if (widget.preview_image) {
            previewImageEl.src = widget.preview_image;
            previewImageEl.style.display = 'block';
            placeholderEl.style.display = 'none';
        } else {
            previewImageEl.style.display = 'none';
            placeholderEl.style.display = 'block';
        }
        
        // Show preview
        previewEl.style.display = 'block';
    }

    showWidgetLibraryLoading() {
        const libraryGrid = document.getElementById('widgetLibraryGrid');
        const loadingEl = document.getElementById('widgetLibraryLoading');
        const noResultsEl = document.getElementById('widgetNoResults');
        
        if (libraryGrid) libraryGrid.style.display = 'none';
        if (loadingEl) loadingEl.style.display = 'block';
        if (noResultsEl) noResultsEl.style.display = 'none';
    }

    // Validation methods
    canGoToNextStep() {
        switch (this.currentStep) {
            case 1:
                return this.modalData.selectedWidget !== null;
            case 2:
                // Step 2 is only valid if widget doesn't support content OR a content type is selected
                if (!this.modalData.selectedWidget?.supports_content) {
                    return true; // Skip step 2 for non-content widgets
                }
                return this.modalData.selectedContentType !== null;
            case 3:
                // Step 3 is the final step - ready to submit
                if (this.modalData.selectedWidget?.supports_content) {
                    // Content widgets need items selected OR query configured
                    return this.modalData.selectedItems.length > 0 || this.modalData.contentQuery !== null;
                } else {
                    // Non-content widgets are always ready
                    return true;
                }
            default:
                return false;
        }
    }

    canNavigateToStep(step) {
        // Can always go backwards
        if (step <= this.currentStep) return true;
        
        // Can only go forward if all previous steps are completed
        for (let i = 1; i < step; i++) {
            const tempCurrent = this.currentStep;
            this.currentStep = i;
            if (!this.canGoToNextStep()) {
                this.currentStep = tempCurrent;
                return false;
            }
        }
        
        this.currentStep = step;
        return true;
    }

    // Phase 2: Content Type Loading
    async loadContentTypes() {
        console.log('Loading content types for widget:', this.modalData.selectedWidget);
        
        if (!this.modalData.selectedWidget) {
            this.showError('No widget selected');
            return;
        }

        try {
            this.showContentTypeLoading();
            
            const response = await fetch(`${this.apiBaseUrl}/widgets/${this.modalData.selectedWidget.id}/content-types`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.renderContentTypes(data.data);
            } else {
                throw new Error(data.error || 'Failed to load content types');
            }
        } catch (error) {
            console.error('Error loading content types:', error);
            this.showError('Failed to load content types: ' + error.message);
            this.showNoContentTypes();
        }
    }

    renderContentTypes(data) {
        const { widget, content_types } = data;
        const loadingEl = document.getElementById('contentTypeLoading');
        const gridEl = document.getElementById('contentTypeGrid');
        const noTypesEl = document.getElementById('noContentTypes');
        
        if (!loadingEl || !gridEl || !noTypesEl) return;

        // Hide loading state
        loadingEl.style.display = 'none';
        
        if (content_types.length === 0) {
            // Show no content types state
            noTypesEl.style.display = 'block';
            gridEl.style.display = 'none';
        } else {
            // Show content types grid
            noTypesEl.style.display = 'none';
            this.populateContentTypeGrid(content_types);
            gridEl.style.display = 'block';
        }
    }

    populateContentTypeGrid(contentTypes) {
        const gridContainer = document.querySelector('#contentTypeGrid .row');
        if (!gridContainer) return;

        // Clear existing content
        gridContainer.innerHTML = '';
        
        // Create content type cards
        contentTypes.forEach(contentType => {
            const card = this.createContentTypeCard(contentType);
            gridContainer.appendChild(card);
        });

        // Setup selection handlers
        this.setupContentTypeSelectionHandlers();
    }

    createContentTypeCard(contentType) {
        const col = document.createElement('div');
        col.className = 'col-md-6';
        
        col.innerHTML = `
            <div class="content-type-card border rounded p-3 h-100" 
                 style="cursor: pointer; transition: all 0.2s;"
                 data-content-type-id="${contentType.id}"
                 data-content-type='${JSON.stringify(contentType)}'>
                <div class="d-flex align-items-start mb-2">
                    <i class="${contentType.icon} me-3 fs-4 text-primary"></i>
                    <div class="flex-grow-1">
                        <h6 class="content-type-name mb-1">${contentType.name}</h6>
                        <p class="content-type-description text-muted mb-2 small">${contentType.description || 'No description available'}</p>
                    </div>
                </div>
                
                <div class="content-type-meta">
                    <span class="badge bg-secondary me-1">${contentType.field_count} fields</span>
                    <span class="badge bg-info">${contentType.items_count} items</span>
                </div>
            </div>
        `;
        
        return col;
    }

    setupContentTypeSelectionHandlers() {
        document.querySelectorAll('.content-type-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const contentTypeData = JSON.parse(e.currentTarget.dataset.contentType);
                this.handleContentTypeSelection(contentTypeData);
            });

            // Hover effects
            card.addEventListener('mouseenter', () => {
                card.style.borderColor = '#007bff';
                card.style.transform = 'translateY(-2px)';
                card.style.boxShadow = '0 4px 8px rgba(0,123,255,0.2)';
            });

            card.addEventListener('mouseleave', () => {
                if (!card.classList.contains('selected')) {
                    card.style.borderColor = '';
                    card.style.transform = '';
                    card.style.boxShadow = '';
                }
            });
        });
    }

    handleContentTypeSelection(contentType) {
        // Remove previous selection
        document.querySelectorAll('.content-type-card').forEach(card => {
            card.classList.remove('selected');
            card.style.borderColor = '';
            card.style.transform = '';
            card.style.boxShadow = '';
        });

        // Mark as selected
        const selectedCard = document.querySelector(`[data-content-type-id="${contentType.id}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
            selectedCard.style.borderColor = '#28a745';
            selectedCard.style.transform = 'translateY(-2px)';
            selectedCard.style.boxShadow = '0 4px 8px rgba(40,167,69,0.3)';
        }

        // Store selection
        this.modalData.selectedContentType = contentType;

        // Update info panel
        this.updateContentTypeInfo(contentType);

        // Update navigation buttons
        this.updateNavigationButtons();

        console.log('Content type selected:', contentType);
    }

    updateContentTypeInfo(contentType) {
        const infoEl = document.getElementById('selectedContentTypeInfo');
        if (!infoEl) return;

        // Update info content
        document.getElementById('selectedContentTypeIcon').className = contentType.icon + ' me-3 fs-4';
        document.getElementById('selectedContentTypeName').textContent = contentType.name;
        document.getElementById('selectedContentTypeDescription').textContent = contentType.description || 'No description available';
        document.getElementById('selectedContentTypeFieldCount').textContent = contentType.field_count;
        document.getElementById('selectedContentTypeItemCount').textContent = contentType.items_count;

        // Show info panel
        infoEl.style.display = 'block';
    }

    showContentTypeLoading() {
        const loadingEl = document.getElementById('contentTypeLoading');
        const gridEl = document.getElementById('contentTypeGrid');
        const noTypesEl = document.getElementById('noContentTypes');
        const infoEl = document.getElementById('selectedContentTypeInfo');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (gridEl) gridEl.style.display = 'none';
        if (noTypesEl) noTypesEl.style.display = 'none';
        if (infoEl) infoEl.style.display = 'none';
    }

    showNoContentTypes() {
        const loadingEl = document.getElementById('contentTypeLoading');
        const gridEl = document.getElementById('contentTypeGrid');
        const noTypesEl = document.getElementById('noContentTypes');
        
        if (loadingEl) loadingEl.style.display = 'none';
        if (gridEl) gridEl.style.display = 'none';
        if (noTypesEl) noTypesEl.style.display = 'block';
    }

    // Phase 3: Content Item Loading
    async loadContentItems() {
        console.log('Loading content items for content type:', this.modalData.selectedContentType);
        
        if (!this.modalData.selectedContentType) {
            this.showError('No content type selected');
            return;
        }

        // Setup tab switching handlers
        this.setupContentSelectionTabs();
        
        // Load manual selection by default
        await this.loadManualSelection();
    }

    setupContentSelectionTabs() {
        const manualTab = document.getElementById('manual-tab');
        const queryTab = document.getElementById('query-tab');

        if (manualTab && queryTab) {
            manualTab.addEventListener('shown.bs.tab', () => {
                this.loadManualSelection();
            });

            queryTab.addEventListener('shown.bs.tab', () => {
                this.setupQueryBuilder();
            });
        }
    }

    async loadManualSelection() {
        try {
            this.showContentItemsLoading();
            
            const response = await fetch(`${this.apiBaseUrl}/content-types/${this.modalData.selectedContentType.id}/items`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.renderContentItems(data.data);
            } else {
                throw new Error(data.error || 'Failed to load content items');
            }
        } catch (error) {
            console.error('Error loading content items:', error);
            this.showError('Failed to load content items: ' + error.message);
            this.showNoContentItems();
        }
    }

    renderContentItems(data) {
        const { content_type, items, pagination } = data;
        const loadingEl = document.getElementById('contentItemsLoading');
        const listEl = document.getElementById('contentItemsList');
        const noItemsEl = document.getElementById('noContentItems');
        
        if (!loadingEl || !listEl || !noItemsEl) return;

        // Hide loading state
        loadingEl.style.display = 'none';
        
        if (items.length === 0) {
            // Show no content items state
            noItemsEl.style.display = 'block';
            listEl.style.display = 'none';
        } else {
            // Show content items list
            noItemsEl.style.display = 'none';
            this.populateContentItemsList(items);
            this.renderPagination(pagination);
            listEl.style.display = 'block';
        }
    }

    populateContentItemsList(items) {
        const container = document.querySelector('#contentItemsList .content-items-container');
        if (!container) return;

        // Clear existing content
        container.innerHTML = '';
        
        // Add "Create New Content Item" button
        const createNewButton = this.createNewContentItemButton();
        container.appendChild(createNewButton);
        
        // Create content item cards
        items.forEach(item => {
            const card = this.createContentItemCard(item);
            container.appendChild(card);
        });

        // Setup selection handlers
        this.setupContentItemSelectionHandlers();
    }

    createNewContentItemButton() {
        const buttonDiv = document.createElement('div');
        buttonDiv.className = 'create-new-item-button mb-3';
        
        buttonDiv.innerHTML = `
            <div class="border border-dashed rounded p-4 text-center" style="border-color: #007bff !important; cursor: pointer; transition: all 0.2s;">
                <div class="create-new-content">
                    <i class="ri-add-circle-line fs-1 text-primary mb-2"></i>
                    <h6 class="text-primary mb-1">Create New Content Item</h6>
                    <p class="text-muted small mb-0">Add a new ${this.modalData.selectedContentType?.name || 'content'} item with default field values</p>
                </div>
            </div>
        `;
        
        // Add hover effects
        const createDiv = buttonDiv.querySelector('div');
        createDiv.addEventListener('mouseenter', () => {
            createDiv.style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
            createDiv.style.transform = 'translateY(-2px)';
            createDiv.style.boxShadow = '0 4px 8px rgba(0,123,255,0.2)';
        });
        
        createDiv.addEventListener('mouseleave', () => {
            createDiv.style.backgroundColor = '';
            createDiv.style.transform = '';
            createDiv.style.boxShadow = '';
        });
        
        // Add click handler
        createDiv.addEventListener('click', () => {
            this.handleCreateNewContentItem();
        });
        
        return buttonDiv;
    }

    async handleCreateNewContentItem() {
        console.log('Creating new content item for content type:', this.modalData.selectedContentType);
        
        try {
            // Use the Field Type Defaults Service
            if (!window.fieldTypeDefaultsService) {
                console.error('Field Type Defaults Service not initialized');
                this.showError('Field type defaults service is not available. Please refresh the page.');
                return;
            }
            
            const contentTypeId = this.modalData.selectedContentType.id;
            const newItem = await window.fieldTypeDefaultsService.createContentItemWithDefaults(contentTypeId);
            
            if (newItem) {
                // Reload the content items list to include the new item
                await this.loadManualSelection();
                
                // Auto-select the newly created item
                setTimeout(() => {
                    const newItemCheckbox = document.querySelector(`input[value="${newItem.id}"]`);
                    if (newItemCheckbox) {
                        newItemCheckbox.checked = true;
                        newItemCheckbox.dispatchEvent(new Event('change'));
                    }
                }, 100);
                
                this.showSuccess(`New ${this.modalData.selectedContentType.name} item created successfully!`);
            }
        } catch (error) {
            console.error('Error creating new content item:', error);
            this.showError('Failed to create new content item: ' + error.message);
        }
    }

    createContentItemCard(item) {
        const card = document.createElement('div');
        card.className = 'content-item-card border rounded p-3 mb-2';
        card.style.cssText = 'cursor: pointer; transition: all 0.2s;';
        card.setAttribute('data-item-id', item.id);
        card.setAttribute('data-item', JSON.stringify(item));
        
        card.innerHTML = `
            <div class="d-flex">
                <div class="form-check me-3 d-flex align-items-start pt-1">
                    <input class="form-check-input" type="checkbox" value="${item.id}" id="item-${item.id}">
                </div>
                
                ${item.thumbnail ? `
                    <div class="item-thumbnail me-3">
                        <img src="${item.thumbnail}" alt="${item.title}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                    </div>
                ` : ''}
                
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="item-title mb-0">${item.title}</h6>
                        <span class="badge bg-${this.getStatusBadgeColor(item.status)} ms-2">${item.status}</span>
                    </div>
                    <p class="item-excerpt text-muted mb-1 small">${item.excerpt || 'No description available'}</p>
                    <div class="item-meta">
                        <small class="text-muted">
                            <i class="ri-calendar-line me-1"></i>${item.created_at}
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }

    getStatusBadgeColor(status) {
        switch (status) {
            case 'published': return 'success';
            case 'draft': return 'warning';
            case 'private': return 'secondary';
            default: return 'primary';
        }
    }

    setupContentItemSelectionHandlers() {
        // Handle checkbox changes
        document.querySelectorAll('.content-item-card input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleContentItemSelection();
                
                // Update card appearance
                const card = e.target.closest('.content-item-card');
                if (e.target.checked) {
                    card.style.backgroundColor = 'rgba(40,167,69,0.05)';
                    card.style.borderColor = '#28a745';
                } else {
                    card.style.backgroundColor = '';
                    card.style.borderColor = '';
                }
            });
        });

        // Handle card clicks (toggle checkbox)
        document.querySelectorAll('.content-item-card').forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't trigger if clicking the checkbox itself
                if (e.target.type !== 'checkbox') {
                    const checkbox = card.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
    }

    handleContentItemSelection() {
        const selectedCheckboxes = document.querySelectorAll('.content-item-card input[type="checkbox"]:checked');
        const selectedItems = Array.from(selectedCheckboxes).map(checkbox => {
            const card = checkbox.closest('.content-item-card');
            return JSON.parse(card.getAttribute('data-item'));
        });

        // Store selected items
        this.modalData.selectedItems = selectedItems;
        this.modalData.contentQuery = null; // Clear query if using manual selection

        // Update summary
        this.updateSelectionSummary('manual', selectedItems.length);

        // Update navigation buttons
        this.updateNavigationButtons();

        console.log('Selected items:', selectedItems);
    }

    setupQueryBuilder() {
        console.log('Setting up query builder');
        
        // Setup preview button handler
        const previewBtn = document.getElementById('previewQueryBtn');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => {
                this.previewQueryResults();
            });
        }

        // Auto-update preview when filters change
        this.setupQueryBuilderAutoPreview();
    }

    setupQueryBuilderAutoPreview() {
        const inputs = [
            'queryStatusFilter',
            'queryDateFilter', 
            'querySearchKeywords',
            'querySortBy',
            'querySortDirection',
            'queryLimit'
        ];

        inputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', () => {
                    // Clear previous preview
                    document.getElementById('queryPreviewResults').innerHTML = `
                        <div class="text-center p-3 text-muted">
                            <i class="ri-eye-line display-6 mb-2"></i>
                            <p class="small mb-0">Click "Preview Results" to see updated results</p>
                        </div>
                    `;
                    document.getElementById('querySummary').style.display = 'none';
                });
            }
        });
    }

    async previewQueryResults() {
        try {
            this.showQueryPreviewLoading();
            
            const querySettings = this.gatherQuerySettings();
            
            const response = await fetch(`${this.apiBaseUrl}/content-types/${this.modalData.selectedContentType.id}/items/query`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(querySettings)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.renderQueryPreview(data.data);
            } else {
                throw new Error(data.error || 'Failed to preview query results');
            }
        } catch (error) {
            console.error('Error previewing query:', error);
            this.showError('Failed to preview query: ' + error.message);
            this.hideQueryPreviewLoading();
        }
    }

    gatherQuerySettings() {
        const settings = {
            filters: {},
            sort_by: document.getElementById('querySortBy')?.value || 'created_at',
            sort_direction: document.getElementById('querySortDirection')?.value || 'desc',
            limit: parseInt(document.getElementById('queryLimit')?.value || '10')
        };

        // Gather filters
        const status = document.getElementById('queryStatusFilter')?.value;
        if (status) settings.filters.status = status;

        const search = document.getElementById('querySearchKeywords')?.value;
        if (search) settings.filters.search = search;

        const dateRange = document.getElementById('queryDateFilter')?.value;
        if (dateRange) {
            const dates = this.getDateRangeFromFilter(dateRange);
            if (dates.from) settings.filters.date_from = dates.from;
            if (dates.to) settings.filters.date_to = dates.to;
        }

        return settings;
    }

    getDateRangeFromFilter(filter) {
        const now = new Date();
        const dates = { from: null, to: null };

        switch (filter) {
            case 'today':
                dates.from = new Date(now.getFullYear(), now.getMonth(), now.getDate()).toISOString();
                dates.to = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1).toISOString();
                break;
            case 'week':
                const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
                dates.from = weekStart.toISOString();
                break;
            case 'month':
                dates.from = new Date(now.getFullYear(), now.getMonth(), 1).toISOString();
                break;
            case 'year':
                dates.from = new Date(now.getFullYear(), 0, 1).toISOString();
                break;
        }

        return dates;
    }

    renderQueryPreview(data) {
        const { query_preview, total_matches, query_settings } = data;
        
        this.hideQueryPreviewLoading();

        // Render preview items
        const previewEl = document.getElementById('queryPreviewResults');
        if (previewEl) {
            if (query_preview.length === 0) {
                previewEl.innerHTML = `
                    <div class="text-center p-3 text-muted">
                        <i class="ri-search-line display-6 mb-2"></i>
                        <p class="small mb-0">No items match your query criteria</p>
                    </div>
                `;
            } else {
                previewEl.innerHTML = query_preview.map(item => `
                    <div class="query-preview-item border-bottom py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 small">${item.title}</h6>
                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">${item.excerpt}</p>
                            </div>
                            <span class="badge bg-${this.getStatusBadgeColor(item.status)} ms-2" style="font-size: 0.6rem;">${item.status}</span>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Show query summary
        this.renderQuerySummary(query_settings, total_matches);

        // Store query for later use
        this.modalData.contentQuery = query_settings;
        this.modalData.selectedItems = []; // Clear manual selection

        // Update selection summary
        this.updateSelectionSummary('query', total_matches);

        // Update navigation buttons
        this.updateNavigationButtons();
    }

    renderQuerySummary(settings, totalMatches) {
        const summaryEl = document.getElementById('querySummary');
        const contentEl = summaryEl?.querySelector('.query-summary-content');
        
        if (!contentEl) return;

        let summaryText = `<strong>${totalMatches} items</strong> will be selected<br>`;
        
        if (settings.filters.status) {
            summaryText += `Status: <code>${settings.filters.status}</code><br>`;
        }
        
        if (settings.filters.search) {
            summaryText += `Search: <code>${settings.filters.search}</code><br>`;
        }
        
        summaryText += `Sort: <code>${settings.sort_by} ${settings.sort_direction}</code><br>`;
        summaryText += `Limit: <code>${settings.limit} items</code>`;

        contentEl.innerHTML = summaryText;
        summaryEl.style.display = 'block';
    }

    updateSelectionSummary(mode, count) {
        const summaryEl = document.getElementById('selectedItemsSummary');
        const textEl = document.getElementById('selectionSummaryText');
        
        if (!summaryEl || !textEl) return;

        if (count > 0) {
            const modeText = mode === 'manual' ? 'manually selected' : 'matched by query';
            textEl.textContent = `${count} content ${count === 1 ? 'item' : 'items'} ${modeText}`;
            summaryEl.style.display = 'block';
        } else {
            summaryEl.style.display = 'none';
        }
    }

    showContentItemsLoading() {
        const loadingEl = document.getElementById('contentItemsLoading');
        const listEl = document.getElementById('contentItemsList');
        const noItemsEl = document.getElementById('noContentItems');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (listEl) listEl.style.display = 'none';
        if (noItemsEl) noItemsEl.style.display = 'none';
    }

    showNoContentItems() {
        const loadingEl = document.getElementById('contentItemsLoading');
        const listEl = document.getElementById('contentItemsList');
        const noItemsEl = document.getElementById('noContentItems');
        
        if (loadingEl) loadingEl.style.display = 'none';
        if (listEl) listEl.style.display = 'none';
        if (noItemsEl) noItemsEl.style.display = 'block';
    }

    showQueryPreviewLoading() {
        const loadingEl = document.getElementById('queryPreviewLoading');
        const resultsEl = document.getElementById('queryPreviewResults');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (resultsEl) resultsEl.style.display = 'none';
    }

    hideQueryPreviewLoading() {
        const loadingEl = document.getElementById('queryPreviewLoading');
        const resultsEl = document.getElementById('queryPreviewResults');
        
        if (loadingEl) loadingEl.style.display = 'none';
        if (resultsEl) resultsEl.style.display = 'block';
    }

    renderPagination(pagination) {
        const paginationEl = document.getElementById('contentItemsPagination');
        if (!paginationEl || pagination.total_pages <= 1) return;

        let paginationHtml = '<nav aria-label="Content items pagination"><ul class="pagination pagination-sm justify-content-center">';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHtml += `<li class="page-item"><button class="page-link" data-page="${pagination.current_page - 1}">Previous</button></li>`;
        }
        
        // Page numbers (show current + 2 before and after)
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === pagination.current_page ? ' active' : '';
            paginationHtml += `<li class="page-item${activeClass}"><button class="page-link" data-page="${i}">${i}</button></li>`;
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
            paginationHtml += `<li class="page-item"><button class="page-link" data-page="${pagination.current_page + 1}">Next</button></li>`;
        }
        
        paginationHtml += '</ul></nav>';
        
        paginationEl.innerHTML = paginationHtml;
        
        // Setup pagination handlers
        paginationEl.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page);
                this.loadContentItemsPage(page);
            });
        });
    }

    async loadContentItemsPage(page) {
        // Implementation for pagination - simplified for now
        console.log('Loading content items page:', page);
        // TODO: Implement pagination loading
    }

    // Phase 4: Widget Configuration
    async loadWidgetConfiguration() {
        console.log('Loading widget configuration for widget:', this.modalData.selectedWidget);
        
        if (!this.modalData.selectedWidget) {
            this.showError('No widget selected');
            return;
        }

        try {
            this.showConfigurationLoading();
            
            const response = await fetch(`${this.apiBaseUrl}/widgets/${this.modalData.selectedWidget.id}/field-definitions`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.renderWidgetConfiguration(data.data);
            } else {
                throw new Error(data.error || 'Failed to load widget configuration');
            }
        } catch (error) {
            console.error('Error loading widget configuration:', error);
            this.showError('Failed to load widget configuration: ' + error.message);
            this.showNoConfigurationFields();
        }
    }

    renderWidgetConfiguration(data) {
        const { widget, field_definitions, default_settings } = data;
        const loadingEl = document.getElementById('configurationLoading');
        const contentEl = document.getElementById('configurationContent');
        const noFieldsEl = document.getElementById('noConfigurationFields');
        
        if (!loadingEl || !contentEl || !noFieldsEl) return;

        // Hide loading state
        loadingEl.style.display = 'none';
        
        // Initialize default widget configuration
        this.modalData.widgetConfig = {
            widget_fields: {},
            layout: default_settings.layout,
            styling: default_settings.styling,
            advanced: default_settings.advanced
        };

        // Render widget fields if any
        if (field_definitions && field_definitions.length > 0) {
            this.renderWidgetFields(field_definitions);
            document.getElementById('fieldCount').textContent = `${field_definitions.length} fields`;
        } else {
            // Hide widget fields section if no fields
            document.getElementById('widgetFieldsSection').style.display = 'none';
            document.getElementById('fieldCount').textContent = '0 fields';
        }

        // Setup configuration form handlers
        this.setupConfigurationHandlers();
        
        // Show configuration content
        contentEl.style.display = 'block';

        // Update navigation buttons
        this.updateNavigationButtons();
    }

    renderWidgetFields(fieldDefinitions) {
        const container = document.querySelector('.widget-fields-container');
        if (!container) return;

        // Clear existing fields
        container.innerHTML = '';
        
        fieldDefinitions.forEach(field => {
            const fieldElement = this.createFieldElement(field);
            container.appendChild(fieldElement);
        });
    }

    createFieldElement(field) {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'widget-field mb-3';
        fieldDiv.setAttribute('data-field-id', field.id);
        fieldDiv.setAttribute('data-field-slug', field.slug);

        const isRequired = field.is_required;
        const requiredText = isRequired ? '<span class="text-danger">*</span>' : '';
        
        let fieldInput = '';
        
        switch (field.field_type) {
            case 'text':
                fieldInput = `<input type="text" class="form-control" id="field_${field.slug}" placeholder="${field.default_value || ''}" ${isRequired ? 'required' : ''}>`;
                break;
                
            case 'textarea':
                fieldInput = `<textarea class="form-control" id="field_${field.slug}" rows="3" placeholder="${field.default_value || ''}" ${isRequired ? 'required' : ''}></textarea>`;
                break;
                
            case 'number':
                const min = field.settings?.min || '';
                const max = field.settings?.max || '';
                fieldInput = `<input type="number" class="form-control" id="field_${field.slug}" placeholder="${field.default_value || ''}" ${min ? `min="${min}"` : ''} ${max ? `max="${max}"` : ''} ${isRequired ? 'required' : ''}>`;
                break;
                
            case 'select':
                const options = field.settings?.options || [];
                const optionsHtml = options.map(option => 
                    `<option value="${option.value}">${option.label}</option>`
                ).join('');
                fieldInput = `<select class="form-select" id="field_${field.slug}" ${isRequired ? 'required' : ''}>
                    <option value="">Choose...</option>
                    ${optionsHtml}
                </select>`;
                break;
                
            case 'checkbox':
                fieldInput = `<div class="form-check">
                    <input class="form-check-input" type="checkbox" id="field_${field.slug}" ${field.default_value ? 'checked' : ''}>
                    <label class="form-check-label" for="field_${field.slug}">
                        Enable ${field.name}
                    </label>
                </div>`;
                break;
                
            case 'color':
                fieldInput = `<div class="input-group">
                    <input type="color" class="form-control form-control-color" id="field_${field.slug}" value="${field.default_value || '#000000'}">
                    <input type="text" class="form-control" id="field_${field.slug}_text" placeholder="#000000">
                </div>`;
                break;
                
            case 'image':
                fieldInput = `<input type="file" class="form-control" id="field_${field.slug}" accept="image/*" ${isRequired ? 'required' : ''}>`;
                break;
                
            default:
                fieldInput = `<input type="text" class="form-control" id="field_${field.slug}" placeholder="${field.default_value || ''}" ${isRequired ? 'required' : ''}>`;
                break;
        }

        fieldDiv.innerHTML = `
            <label class="form-label small fw-bold" for="field_${field.slug}">
                ${field.name} ${requiredText}
            </label>
            ${fieldInput}
            ${field.description ? `<small class="form-text text-muted">${field.description}</small>` : ''}
        `;

        return fieldDiv;
    }

    setupConfigurationHandlers() {
        // Widget field change handlers
        document.querySelectorAll('.widget-field input, .widget-field select, .widget-field textarea').forEach(input => {
            input.addEventListener('change', () => {
                this.handleWidgetFieldChange();
            });
        });

        // Layout change handlers
        document.getElementById('layoutWidth')?.addEventListener('change', () => {
            this.handleLayoutChange();
        });
        document.getElementById('layoutHeight')?.addEventListener('change', () => {
            this.handleLayoutChange();
        });
        document.getElementById('layoutAlignment')?.addEventListener('change', () => {
            this.handleLayoutChange();
        });

        // Styling change handlers
        this.setupStylingHandlers();

        // Advanced settings handlers
        document.getElementById('animationEffect')?.addEventListener('change', () => {
            this.handleAdvancedChange();
        });
        document.querySelectorAll('.responsive-controls input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.handleAdvancedChange();
            });
        });

        // Real-time updates for color and range inputs
        document.getElementById('backgroundColor')?.addEventListener('input', (e) => {
            document.getElementById('backgroundColorHex').value = e.target.value;
            this.handleStylingChange();
        });
        
        document.getElementById('backgroundColorHex')?.addEventListener('input', (e) => {
            document.getElementById('backgroundColor').value = e.target.value;
            this.handleStylingChange();
        });

        document.getElementById('borderRadius')?.addEventListener('input', (e) => {
            document.getElementById('borderRadiusValue').textContent = e.target.value;
            this.handleStylingChange();
        });
    }

    setupStylingHandlers() {
        // Spacing inputs
        ['paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
         'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].forEach(inputId => {
            document.getElementById(inputId)?.addEventListener('change', () => {
                this.handleStylingChange();
            });
        });

        // Background and border inputs
        ['backgroundColor', 'backgroundColorHex', 'backgroundOpacity', 
         'borderRadius', 'customCssClass'].forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                const eventType = input.type === 'range' ? 'input' : 'change';
                input.addEventListener(eventType, () => {
                    this.handleStylingChange();
                });
            }
        });
    }

    handleWidgetFieldChange() {
        // Gather all widget field values
        const fields = {};
        
        document.querySelectorAll('.widget-field').forEach(fieldDiv => {
            const slug = fieldDiv.getAttribute('data-field-slug');
            const input = fieldDiv.querySelector('input, select, textarea');
            
            if (input) {
                if (input.type === 'checkbox') {
                    fields[slug] = input.checked;
                } else if (input.type === 'file') {
                    fields[slug] = input.files[0] || null;
                } else {
                    fields[slug] = input.value;
                }
            }
        });

        this.modalData.widgetConfig.widget_fields = fields;
        console.log('Widget fields updated:', fields);
    }

    handleLayoutChange() {
        const layout = {
            width: parseInt(document.getElementById('layoutWidth')?.value || '12'),
            height: document.getElementById('layoutHeight')?.value || 'auto',
            alignment: document.getElementById('layoutAlignment')?.value || 'left'
        };

        this.modalData.widgetConfig.layout = layout;
        console.log('Layout updated:', layout);
    }

    handleStylingChange() {
        const styling = {
            padding: {
                top: parseInt(document.getElementById('paddingTop')?.value || '0'),
                right: parseInt(document.getElementById('paddingRight')?.value || '0'),
                bottom: parseInt(document.getElementById('paddingBottom')?.value || '0'),
                left: parseInt(document.getElementById('paddingLeft')?.value || '0')
            },
            margin: {
                top: parseInt(document.getElementById('marginTop')?.value || '0'),
                right: parseInt(document.getElementById('marginRight')?.value || '0'),
                bottom: parseInt(document.getElementById('marginBottom')?.value || '0'),
                left: parseInt(document.getElementById('marginLeft')?.value || '0')
            },
            background_color: document.getElementById('backgroundColor')?.value || '#ffffff',
            background_opacity: parseInt(document.getElementById('backgroundOpacity')?.value || '100'),
            border_radius: parseInt(document.getElementById('borderRadius')?.value || '0'),
            custom_css_class: document.getElementById('customCssClass')?.value || ''
        };

        this.modalData.widgetConfig.styling = styling;
        console.log('Styling updated:', styling);
    }

    handleAdvancedChange() {
        const responsiveVisibility = {};
        ['xs', 'sm', 'md', 'lg', 'xl'].forEach(size => {
            const checkbox = document.getElementById(`visible${size.charAt(0).toUpperCase() + size.slice(1)}`);
            responsiveVisibility[size] = checkbox ? checkbox.checked : true;
        });

        const advanced = {
            animation: document.getElementById('animationEffect')?.value || 'none',
            responsive_visibility: responsiveVisibility
        };

        this.modalData.widgetConfig.advanced = advanced;
        console.log('Advanced settings updated:', advanced);
    }

    showConfigurationLoading() {
        const loadingEl = document.getElementById('configurationLoading');
        const contentEl = document.getElementById('configurationContent');
        const noFieldsEl = document.getElementById('noConfigurationFields');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (contentEl) contentEl.style.display = 'none';
        if (noFieldsEl) noFieldsEl.style.display = 'none';
    }

    showNoConfigurationFields() {
        const loadingEl = document.getElementById('configurationLoading');
        const contentEl = document.getElementById('configurationContent');
        const noFieldsEl = document.getElementById('noConfigurationFields');
        
        if (loadingEl) loadingEl.style.display = 'none';
        if (contentEl) contentEl.style.display = 'none';
        if (noFieldsEl) noFieldsEl.style.display = 'block';
    }

    async loadReviewStep() {
        console.log('Loading review step for simplified 3-step flow');
        
        try {
            // For simplified flow, show content selection summary
            this.renderContentSelectionSummary();
            
            // Update navigation buttons for final step
            this.updateNavigationButtons();
            
        } catch (error) {
            console.error('Error loading review step:', error);
            this.showError('Failed to load review step: ' + error.message);
        }
    }

    renderContentSelectionSummary() {
        const reviewContainer = document.getElementById('widgetReview');
        if (!reviewContainer) return;

        const widget = this.modalData.selectedWidget;
        const contentType = this.modalData.selectedContentType;
        
        let contentSummary = '';
        if (widget.supports_content) {
            if (this.modalData.selectedItems.length > 0) {
                contentSummary = `${this.modalData.selectedItems.length} content item(s) manually selected`;
            } else if (this.modalData.contentQuery) {
                contentSummary = `Content will be loaded via query (max ${this.modalData.contentQuery.limit || 10} items)`;
            } else {
                contentSummary = 'No content selected';
            }
        } else {
            contentSummary = 'Static widget (no content required)';
        }

        reviewContainer.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-check-line me-2"></i>Content Selection Complete
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="${widget.icon} me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-0">${widget.name}</h6>
                            <small class="text-muted">${widget.description || 'No description'}</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold mb-1">CONTENT SELECTION</label>
                        <p class="mb-0">${contentSummary}</p>
                        ${contentType ? `<small class="text-muted">Content Type: ${contentType.name}</small>` : ''}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold mb-1">TARGET SECTION</label>
                        <p class="mb-0">${this.modalData.sectionName}</p>
                    </div>

                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="ri-information-line me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Ready to Add Widget</h6>
                                <p class="mb-0">The widget will be added with default configuration. You can customize it later.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async generateWidgetPreview() {
        try {
            const previewData = {
                widget_id: this.modalData.selectedWidget.id,
                content_type_id: this.modalData.selectedContentType?.id || null,
                selected_items: this.modalData.selectedItems,
                content_query: this.modalData.contentQuery,
                widget_config: this.modalData.widgetConfig,
                section_id: this.modalData.sectionId
            };

            const response = await fetch(`${this.apiBaseUrl}/widgets/${this.modalData.selectedWidget.id}/preview`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(previewData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.modalData.previewHtml = data.data.preview_html;
                this.modalData.widgetSummary = data.data.widget_summary;
            } else {
                throw new Error(data.error || 'Failed to generate widget preview');
            }
        } catch (error) {
            console.error('Error generating widget preview:', error);
            // Continue without preview if it fails
            this.modalData.previewHtml = '<div class="text-center p-4 text-muted"><i class="ri-eye-off-line display-4 mb-2"></i><p>Preview not available</p></div>';
            this.modalData.widgetSummary = {
                name: this.modalData.selectedWidget.name,
                content_type: this.modalData.selectedContentType?.name || 'None',
                items_count: this.modalData.selectedItems.length || (this.modalData.contentQuery ? 'Query-based' : 0),
                settings_count: Object.keys(this.modalData.widgetConfig.widget_fields || {}).length
            };
        }
    }

    renderReviewSummary() {
        const reviewContainer = document.getElementById('widgetReview');
        if (!reviewContainer) return;

        const summary = this.modalData.widgetSummary;
        const widget = this.modalData.selectedWidget;
        const contentType = this.modalData.selectedContentType;
        const config = this.modalData.widgetConfig;

        let contentSummary = '';
        if (widget.supports_content) {
            if (this.modalData.selectedItems.length > 0) {
                contentSummary = `${this.modalData.selectedItems.length} manually selected item(s)`;
            } else if (this.modalData.contentQuery) {
                contentSummary = `Query-based selection (${this.modalData.contentQuery.limit || 10} items max)`;
            } else {
                contentSummary = 'No content selected';
            }
        } else {
            contentSummary = 'Static widget (no content)';
        }

        const layoutSummary = `${config.layout?.width || 12} columns, ${config.layout?.height || 'auto'} height, ${config.layout?.alignment || 'left'} aligned`;
        
        const stylingSummary = [];
        if (config.styling?.background_color && config.styling.background_color !== '#ffffff') {
            stylingSummary.push(`Background: ${config.styling.background_color}`);
        }
        if (config.styling?.border_radius && config.styling.border_radius > 0) {
            stylingSummary.push(`Border radius: ${config.styling.border_radius}px`);
        }
        if (config.styling?.custom_css_class) {
            stylingSummary.push(`Custom class: ${config.styling.custom_css_class}`);
        }

        reviewContainer.innerHTML = `
            <div class="row">
                <div class="col-lg-8">
                    <!-- Widget Preview -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="ri-eye-line me-2"></i>Widget Preview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="widget-preview-container p-3 border rounded" style="background: #f8f9fa; min-height: 200px;">
                                ${this.modalData.previewHtml}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Configuration Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="ri-settings-line me-2"></i>Configuration Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Widget Info -->
                            <div class="summary-section mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="${widget.icon} me-2 text-primary"></i>
                                    <strong>${widget.name}</strong>
                                </div>
                                <p class="text-muted small mb-0">${widget.description || 'No description'}</p>
                            </div>

                            <!-- Target Section -->
                            <div class="summary-section mb-3">
                                <label class="form-label small text-muted fw-bold mb-1">TARGET SECTION</label>
                                <p class="mb-0">${this.modalData.sectionName}</p>
                            </div>

                            <!-- Content Info -->
                            <div class="summary-section mb-3">
                                <label class="form-label small text-muted fw-bold mb-1">CONTENT</label>
                                <p class="mb-0">${contentSummary}</p>
                                ${contentType ? `<small class="text-muted">Type: ${contentType.name}</small>` : ''}
                            </div>

                            <!-- Layout -->
                            <div class="summary-section mb-3">
                                <label class="form-label small text-muted fw-bold mb-1">LAYOUT</label>
                                <p class="mb-0 small">${layoutSummary}</p>
                            </div>

                            <!-- Widget Fields -->
                            ${Object.keys(config.widget_fields || {}).length > 0 ? `
                                <div class="summary-section mb-3">
                                    <label class="form-label small text-muted fw-bold mb-1">WIDGET SETTINGS</label>
                                    <div class="small">
                                        ${Object.entries(config.widget_fields || {}).map(([key, value]) => 
                                            `<div class="d-flex justify-content-between">
                                                <span class="text-muted">${key}:</span>
                                                <span>${this.formatFieldValue(value)}</span>
                                            </div>`
                                        ).join('')}
                                    </div>
                                </div>
                            ` : ''}

                            <!-- Styling -->
                            ${stylingSummary.length > 0 ? `
                                <div class="summary-section mb-3">
                                    <label class="form-label small text-muted fw-bold mb-1">STYLING</label>
                                    <div class="small">
                                        ${stylingSummary.map(item => `<div>${item}</div>`).join('')}
                                    </div>
                                </div>
                            ` : ''}

                            <!-- Advanced Settings -->
                            ${config.advanced?.animation && config.advanced.animation !== 'none' ? `
                                <div class="summary-section">
                                    <label class="form-label small text-muted fw-bold mb-1">ADVANCED</label>
                                    <div class="small">
                                        <div>Animation: ${config.advanced.animation}</div>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Confirmation -->
            <div class="alert alert-info mt-4">
                <div class="d-flex align-items-center">
                    <i class="ri-information-line me-3 fs-4"></i>
                    <div>
                        <h6 class="mb-1">Ready to Add Widget</h6>
                        <p class="mb-0">Click "Add Widget to Section" to add this configured widget to <strong>${this.modalData.sectionName}</strong>.</p>
                    </div>
                </div>
            </div>
        `;
    }

    formatFieldValue(value) {
        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }
        if (value === null || value === undefined || value === '') {
            return '<em class="text-muted">Empty</em>';
        }
        if (typeof value === 'string' && value.length > 30) {
            return value.substring(0, 30) + '...';
        }
        return value;
    }

    async handleFinalSubmission() {
        console.log('Submitting widget to section:', this.modalData);
        
        const addBtn = document.getElementById('addWidgetToSectionBtn');
        const originalText = addBtn ? addBtn.innerHTML : '<i class="ri-add-line me-2"></i>Add Widget to Section';
        
        try {
            // Show loading state
            if (addBtn) {
                addBtn.disabled = true;
                addBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Adding Widget...';
            }

            // Prepare content query - ensure it's always an object/array, never null
            let contentQuery = {};
            if (this.modalData.selectedItems && this.modalData.selectedItems.length > 0) {
                // Manual selection
                contentQuery = {
                    limit: this.modalData.selectedItems.length,
                    filters: [],
                    sort_by: 'created_at',
                    query_type: 'multiple',
                    sort_order: 'desc',
                    content_type_id: this.modalData.selectedContentType?.id || null,
                    content_item_ids: this.modalData.selectedItems.map(item => item.id)
                };
            } else if (this.modalData.contentQuery && typeof this.modalData.contentQuery === 'object') {
                // Query builder
                contentQuery = this.modalData.contentQuery;
                if (!contentQuery.content_type_id && this.modalData.selectedContentType) {
                    contentQuery.content_type_id = this.modalData.selectedContentType.id;
                }
            } else if (this.modalData.selectedContentType) {
                // Widget has content type but no selection - empty query
                contentQuery = {
                    limit: 0,
                    filters: [],
                    sort_by: 'created_at',
                    query_type: 'multiple',
                    sort_order: 'desc',
                    content_type_id: this.modalData.selectedContentType.id,
                    content_item_ids: []
                };
            }

            // Auto-apply default widget configuration
            const defaultWidgetConfig = {
                widget_fields: {},
                layout: {
                    width: 12, // Full width
                    height: 'auto',
                    alignment: 'left'
                },
                styling: {
                    padding: { top: 0, right: 0, bottom: 0, left: 0 },
                    margin: { top: 0, right: 0, bottom: 0, left: 0 },
                    background_color: '',
                    background_opacity: 100,
                    border_radius: 0,
                    custom_css_class: ''
                },
                advanced: {
                    animation: 'none',
                    responsive_visibility: { xs: true, sm: true, md: true, lg: true, xl: true }
                }
            };

            // Debug logging
            console.log('Submission data:', {
                widget_id: this.modalData.selectedWidget?.id,
                content_type_id: this.modalData.selectedContentType?.id || null,
                selected_items: this.modalData.selectedItems?.map(item => item.id) || [],
                content_query: contentQuery,
                widget_config: defaultWidgetConfig,
                section_id: this.modalData.sectionId
            });

            // Prepare submission data
            const submissionData = {
                widget_id: this.modalData.selectedWidget.id,
                content_type_id: this.modalData.selectedContentType?.id || null,
                selected_items: this.modalData.selectedItems?.map(item => item.id) || [],
                content_query: contentQuery,
                widget_config: defaultWidgetConfig
            };

            const response = await fetch(`${this.apiBaseUrl}/sections/${this.modalData.sectionId}/add-widget`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(submissionData)
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Success! Close modal and refresh page
                this.modal.hide();
                this.showSuccess('Widget added successfully to ' + (this.modalData.sectionName || 'section') + '!');
                
                // Refresh the page builder to show the new widget
                if (window.pageBuilder && window.pageBuilder.refreshPagePreview) {
                    window.pageBuilder.refreshPagePreview();
                } else {
                    // Fallback: reload the page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                throw new Error(data.error || 'Failed to add widget to section');
            }

        } catch (error) {
            console.error('Error submitting widget:', error);
            this.showError('Failed to add widget: ' + error.message);
            
            // Restore button state
            if (addBtn) {
                addBtn.disabled = false;
                addBtn.innerHTML = originalText;
            }
        }
    }

    // Utility methods
    resetModal() {
        this.currentStep = 1;
        this.modalData = {
            selectedWidget: null,
            selectedContentType: null,
            selectedItems: [],
            contentQuery: null,
            widgetConfig: {},
            sectionId: null,
            sectionName: null
        };
        
        // Reset UI elements
        document.querySelectorAll('.widget-library-item').forEach(item => {
            item.classList.remove('selected');
            item.style.borderColor = '';
            item.style.transform = '';
            item.style.boxShadow = '';
        });

        // Reset content type selections
        document.querySelectorAll('.content-type-card').forEach(card => {
            card.classList.remove('selected');
            card.style.borderColor = '';
            card.style.transform = '';
            card.style.boxShadow = '';
        });

        // Hide preview and info panels
        const noSelectionEl = document.getElementById('noWidgetSelected');
        const previewEl = document.getElementById('selectedWidgetPreview');
        const contentTypeInfoEl = document.getElementById('selectedContentTypeInfo');
        
        if (noSelectionEl) noSelectionEl.style.display = 'block';
        if (previewEl) previewEl.style.display = 'none';
        if (contentTypeInfoEl) contentTypeInfoEl.style.display = 'none';
    }

    showError(message) {
        console.error(message);
        // TODO: Implement proper error display
        alert(message);
    }

    showSuccess(message) {
        console.log(message);
        // TODO: Implement proper success display
        alert(message);
    }
}

// Make it globally available
window.WidgetModalManager = WidgetModalManager;