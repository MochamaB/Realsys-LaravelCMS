/**
 * GridStack Widget Manager
 * Handles widget drag-drop, creation, and management within sections
 * Refined to use existing content selection modal
 */
window.WidgetManager = {
    config: {},
    currentWidget: null,
    dropTargetSection: null,
    modalInstance: null,
    isSaving: false, // Flag to prevent multiple simultaneous saves

    /**
     * Initialize the widget manager
     */
    init() {
        console.log('🔧 Initializing Widget Manager...');
        
        this.config = {
            apiBaseUrl: '/admin/api',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };
        
        // Initialize state
        this.isSaving = false;
        this.currentWidget = null;
        
        this.setupWidgetDropHandling();
        this.setupWidgetEvents();
        this.setupContentSelectionModal();
        
        console.log('✅ Widget Manager initialized');
    },

    /**
     * Re-setup drop zones after sections are loaded
     * This should be called from the page builder after sections are rendered
     */
    setupDropZonesAfterSectionsLoad() {
        console.log('🔧 Setting up drop zones after sections load...');
        this.setupSectionDropZones();
    },

    /**
     * Setup widget drop handling for sections
     */
    setupWidgetDropHandling() {
        // Listen for widget drops from the widget library
        document.addEventListener('widgetDropped', (event) => {
            this.handleWidgetDrop(event.detail);
        });
        
        // Setup section drop zones
        this.setupSectionDropZones();
    },

    /**
     * Setup drop zones for sections
     */
    setupSectionDropZones() {
        console.log('🔧 Setting up section drop zones...');
        
        // Find all widget drop zones in sections
        const dropZones = document.querySelectorAll('.widget-drop-zone');
        
        dropZones.forEach(dropZone => {
            const sectionId = dropZone.closest('.page-section')?.getAttribute('data-section-id');
            
            if (!sectionId) {
                console.warn('⚠️ Drop zone not found within a section');
                return;
            }
            
            // Remove existing event listeners
            dropZone.removeEventListener('dragover', this.handleDragOver);
            dropZone.removeEventListener('dragleave', this.handleDragLeave);
            dropZone.removeEventListener('drop', this.handleDrop);
            
            // Add new event listeners
            dropZone.addEventListener('dragover', this.handleDragOver.bind(this));
            dropZone.addEventListener('dragleave', this.handleDragLeave.bind(this));
            dropZone.addEventListener('drop', (e) => this.handleDrop(e, sectionId, dropZone));
            
            console.log(`✅ Setup drop zone for section ${sectionId}`);
        });
        
        console.log(`✅ Setup ${dropZones.length} drop zones`);
    },

    /**
     * Handle drag over on drop zones
     */
    handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('drag-over');
    },

    /**
     * Handle drag leave on drop zones
     */
    handleDragLeave(e) {
        e.currentTarget.classList.remove('drag-over');
    },

    /**
     * Handle drop on drop zones
     */
    handleDrop(e, sectionId, dropZone) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        
        try {
            const widgetData = JSON.parse(e.dataTransfer.getData('text/plain'));
            console.log('📋 Dropping widget to section:', widgetData.name, 'Section:', sectionId);
            this.handleWidgetDropToSection(widgetData, sectionId, dropZone);
        } catch (err) {
            console.error('❌ Error dropping widget:', err);
        }
    },

    /**
     * Handle widget drop to a specific section
     */
    async handleWidgetDropToSection(widgetData, sectionId, dropZone) {
        try {
            console.log(`🎯 Widget dropped to section ${sectionId}:`, widgetData);
            
            // Check if section allows this widget type
            const section = document.querySelector(`[data-section-id="${sectionId}"]`);
            if (!this.canSectionAcceptWidget(section, widgetData)) {
                this.showError('This widget type is not allowed in this section');
                return;
            }
            
            // Store widget data for content selection
            this.currentWidget = {
                ...widgetData,
                sectionId: sectionId,
                dropZone: dropZone
            };
            
            // Open content selection modal
            this.openContentSelectionModal(widgetData);
            
        } catch (error) {
            console.error('❌ Error handling widget drop:', error);
            this.showError('Failed to process widget drop');
        }
    },

    /**
     * Get allowed widget types for a section type
     */
    getAllowedWidgetTypes(sectionType) {
        // Allow all widget types for now - remove restrictions
        // This can be made configurable later based on section templates
        return ['*']; // Allow all widget types
    },

    /**
     * Check if section can accept this widget type
     */
    canSectionAcceptWidget(section, widgetData) {
        const sectionType = section.getAttribute('data-section-type');
        const widgetType = widgetData.type || widgetData.slug;
        
        console.log('🔍 Checking widget compatibility:', {
            sectionType: sectionType,
            widgetType: widgetType,
            widgetData: widgetData
        });
        
        // Get allowed widget types for this section type
        const allowedTypes = this.getAllowedWidgetTypes(sectionType);
        
        console.log('✅ Allowed widget types for section:', allowedTypes);
        
        // If allowedTypes includes '*', allow all widgets
        if (allowedTypes.includes('*')) {
            console.log('✅ Widget allowed - all widgets permitted');
            return true;
        }
        
        const isAllowed = allowedTypes.includes(widgetType);
        console.log(`✅ Widget ${isAllowed ? 'allowed' : 'not allowed'} for section type ${sectionType}`);
        
        return isAllowed;
    },

    /**
     * Setup content selection modal
     */
    setupContentSelectionModal() {
        const modal = document.getElementById('contentSelectionModal');
        if (!modal) {
            console.warn('⚠️ Content selection modal not found');
            return;
        }

        const saveBtn = modal.querySelector('#saveContentSelectionBtn');
        if (saveBtn) {
            // Remove existing event listeners to prevent duplicates
            saveBtn.removeEventListener('click', this.saveWidgetWithContentHandler);
            
            // Create a bound handler function
            this.saveWidgetWithContentHandler = this.saveWidgetWithContent.bind(this);
            
            // Add new event listener
            saveBtn.addEventListener('click', this.saveWidgetWithContentHandler);
            
            console.log('✅ Content selection modal setup complete');
        }
    },

    /**
     * Open content selection modal
     */
    async openContentSelectionModal(widgetData) {
        const modal = document.getElementById('contentSelectionModal');
        const modalTitle = document.getElementById('contentSelectionModalLabel');
        const contentTypeSelect = document.getElementById('contentTypeSelect');
        const contentItemsList = document.getElementById('contentItemsList');
        const selectedContentItemInput = document.getElementById('selectedContentItemId');
        
        if (!modal) {
            console.error('❌ Content selection modal not found');
            return;
        }

        // Set modal title
        if (modalTitle) {
            modalTitle.textContent = `Configure ${widgetData.name || widgetData.label || 'Widget'}`;
        }

        // Reset modal state
        contentTypeSelect.innerHTML = '<option value="" selected disabled>Select content type</option>';
        contentTypeSelect.disabled = true;
        contentItemsList.innerHTML = '';
        selectedContentItemInput.value = '';

        // Show modal
        this.modalInstance = new bootstrap.Modal(modal);
        this.modalInstance.show();

        // Load content types for this widget
        await this.loadContentTypes(widgetData, contentTypeSelect, contentItemsList, selectedContentItemInput);
    },

    /**
     * Load content types for widget
     */
    async loadContentTypes(widgetData, contentTypeSelect, contentItemsList, selectedContentItemInput) {
        try {
            contentTypeSelect.innerHTML = '<option value="" selected disabled>Loading content types...</option>';
            
            const widgetId = widgetData.id;
            const url = `/admin/api/widgets/${widgetId}/content-types`;
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch content types');
            }
            
            const data = await response.json();
            const types = data.content_types || data.data || [];
            
            contentTypeSelect.innerHTML = '<option value="" selected disabled>Select content type</option>';
            
            if (types.length === 0) {
                contentTypeSelect.innerHTML += '<option disabled>No content types available</option>';
            } else {
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.setAttribute('data-slug', type.slug);
                    option.textContent = type.name + (type.description ? ` - ${type.description}` : '');
                    contentTypeSelect.appendChild(option);
                });
                
                // If only one content type exists, auto-select it
                if (types.length === 1) {
                    contentTypeSelect.selectedIndex = 1;
                    setTimeout(() => contentTypeSelect.dispatchEvent(new Event('change')), 0);
                }
            }
            
            contentTypeSelect.disabled = false;
            
            // Listen for content type selection
            contentTypeSelect.onchange = async function() {
                const selectedOption = contentTypeSelect.options[contentTypeSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    contentItemsList.innerHTML = '';
                    selectedContentItemInput.value = '';
                    return;
                }
                
                const contentTypeId = selectedOption.value;
                await this.loadContentItems(contentTypeId, contentItemsList, selectedContentItemInput);
            }.bind(this);
            
        } catch (error) {
            console.error('❌ Error loading content types:', error);
            contentTypeSelect.innerHTML = '<option disabled>Error loading content types</option>';
            contentTypeSelect.disabled = true;
        }
    },

    /**
     * Load content items for selected content type
     */
    async loadContentItems(contentTypeId, contentItemsList, selectedContentItemInput) {
        try {
            contentItemsList.innerHTML = '<div class="text-muted">Loading content items...</div>';
            
            const url = `/admin/api/content/${contentTypeId}`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch content items');
            }
            
            const data = await response.json();
            const items = data.items || data.data || [];
            
            if (items.length === 0) {
                contentItemsList.innerHTML = '<div class="alert alert-warning">No content items found for this content type.</div>';
                selectedContentItemInput.value = '';
            } else {
                // Render as a checkbox list (allowing multiple selections)
                let html = '<div class="list-group">';
                items.forEach(item => {
                    const itemId = item.id;
                    const itemTitle = item.title || item.name || 'Untitled';
                    html += `<label class='list-group-item'>
                        <input type='checkbox' name='contentItemCheckbox' value='${itemId}' class='form-check-input me-2'>
                        ${itemTitle} <span class='text-muted'>#${itemId}</span>
                    </label>`;
                });
                html += '</div>';
                contentItemsList.innerHTML = html;
                
                // Add event listeners to checkboxes
                const checkboxes = contentItemsList.querySelectorAll("input[type='checkbox'][name='contentItemCheckbox']");
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        // Get all checked values
                        const checkedValues = Array.from(checkboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.value);
                        selectedContentItemInput.value = checkedValues.join(',');
                    });
                });
                
                // Auto-select the first item
                if (checkboxes.length > 0) {
                    checkboxes[0].checked = true;
                    selectedContentItemInput.value = checkboxes[0].value;
                }
            }
            
        } catch (error) {
            console.error('❌ Error loading content items:', error);
            contentItemsList.innerHTML = '<div class="alert alert-danger">Error loading content items.</div>';
            selectedContentItemInput.value = '';
        }
    },

    /**
     * Save widget with selected content
     */
    async saveWidgetWithContent() {
        // Prevent multiple simultaneous saves
        if (this.isSaving) {
            console.log('⚠️ Save already in progress, ignoring duplicate request');
            return;
        }

        if (!this.currentWidget) {
            this.showError('No widget data available');
            return;
        }

        const modal = document.getElementById('contentSelectionModal');
        const contentTypeSelect = modal.querySelector('#contentTypeSelect');
        const selectedContentItemInput = modal.querySelector('#selectedContentItemId');
        const saveBtn = modal.querySelector('#saveContentSelectionBtn');

        const selectedTypeId = contentTypeSelect.value;
        const selectedContentItemIds = selectedContentItemInput.value;

        if (!selectedTypeId) {
            this.showError('Please select a content type.');
            return;
        }

        if (!selectedContentItemIds) {
            this.showError('Please select at least one content item.');
            return;
        }

        // Set saving flag
        this.isSaving = true;
        console.log('🔄 Starting widget save process...');

        // Disable save button
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            // Parse content item IDs
            const contentItemIds = selectedContentItemIds.split(',').map(id => parseInt(id.trim()));

            console.log('📝 Creating PageSectionWidget with data:', {
                widgetId: this.currentWidget.id,
                sectionId: this.currentWidget.sectionId,
                contentTypeId: selectedTypeId,
                contentItemIds: contentItemIds
            });

            // Create PageSectionWidget
            const pageSectionWidget = await this.createPageSectionWidget(this.currentWidget, contentItemIds, selectedTypeId);

            if (pageSectionWidget) {
                // Add widget to section's GridStack
                await this.addWidgetToSection(pageSectionWidget, this.currentWidget.sectionId, this.currentWidget.dropZone);

                // Close modal
                this.modalInstance.hide();

                // Show success message
                this.showSuccess('Widget added successfully');

                console.log('✅ Widget created and added to section:', pageSectionWidget);
            }

        } catch (error) {
            console.error('❌ Error saving widget:', error);
            this.showError('Failed to save widget');
        } finally {
            // Clear saving flag
            this.isSaving = false;
            
            // Re-enable save button
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Widget';
            
            console.log('🔄 Widget save process completed');
        }
    },

    /**
     * Create PageSectionWidget in database
     */
    async createPageSectionWidget(widgetData, contentItemIds, contentTypeId) {
        try {
            console.log('📝 Creating PageSectionWidget:', { widgetData, contentItemIds, contentTypeId });

            const widgetPayload = {
                page_section_id: widgetData.sectionId,
                widget_id: widgetData.id,
                position: 1, // Will be calculated by backend
                grid_x: 0,
                grid_y: 0,
                grid_w: 6, // Default width
                grid_h: 3, // Default height
                grid_id: `widget_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                column_position: 0,
                settings: {
                    widget_type: widgetData.type || widgetData.slug,
                    widget_name: widgetData.name || widgetData.label,
                    width: 'half',
                    order: 0,
                    locked: false,
                    noResize: false,
                    resizeHandles: ['se', 'sw', 'ne', 'nw']
                },
                content_query: {
                    content_type_id: parseInt(contentTypeId),
                    content_item_ids: contentItemIds,
                    query_type: 'multiple',
                    filters: {},
                    sort_by: 'created_at',
                    sort_order: 'desc',
                    limit: contentItemIds.length
                },
                css_classes: '',
                padding: { top: 0, bottom: 0, left: 0, right: 0 },
                margin: { top: 0, bottom: 0, left: 0, right: 0 },
                min_height: null,
                max_height: null
            };

            const response = await fetch(`${this.config.apiBaseUrl}/page-section-widgets`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify(widgetPayload)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to create widget');
            }

            const result = await response.json();
            return result.data;

        } catch (error) {
            console.error('❌ Error creating PageSectionWidget:', error);
            throw error;
        }
    },

    /**
     * Add widget to section's GridStack
     */
    async addWidgetToSection(pageSectionWidget, sectionId, dropZone) {
        try {
            // Find the section's GridStack
            const section = document.querySelector(`[data-section-id="${sectionId}"]`);
            const gridStack = section.querySelector('.section-grid-stack');

            if (!gridStack) {
                console.error('❌ GridStack not found for section:', sectionId);
                return;
            }

            // Create widget HTML
            const widgetHtml = await this.createWidgetHtml(pageSectionWidget);

            // Add to GridStack
            const gridInstance = GridStack.getGridStack(gridStack);
            if (gridInstance) {
                const widgetElement = gridInstance.addWidget({
                    x: pageSectionWidget.grid_x,
                    y: pageSectionWidget.grid_y,
                    w: pageSectionWidget.grid_w,
                    h: pageSectionWidget.grid_h,
                    id: pageSectionWidget.grid_id,
                    content: widgetHtml
                });

                // Set data attributes
                widgetElement.setAttribute('data-page-section-widget-id', pageSectionWidget.id);
                widgetElement.setAttribute('data-widget-id', pageSectionWidget.widget_id);
                widgetElement.setAttribute('data-widget-type', pageSectionWidget.settings.widget_type);

                // Remove drop zone content
                dropZone.innerHTML = '';

                console.log('✅ Widget added to GridStack:', widgetElement);
            }

        } catch (error) {
            console.error('❌ Error adding widget to section:', error);
        }
    },

    /**
     * Create widget HTML with actual content
     */
    async createWidgetHtml(pageSectionWidget) {
        const widgetType = pageSectionWidget.settings.widget_type;
        const widgetName = pageSectionWidget.settings.widget_name;
        const widgetId = pageSectionWidget.widget_id;

        try {
            console.log('🎨 Rendering widget content for:', {
                widgetId: widgetId,
                widgetType: widgetType,
                pageSectionWidgetId: pageSectionWidget.id,
                apiBaseUrl: this.config.apiBaseUrl
            });

            const apiUrl = `${this.config.apiBaseUrl}/widgets/${widgetId}/render?page_section_widget_id=${pageSectionWidget.id}`;
            console.log('🌐 Calling API URL:', apiUrl);

            // Fetch actual widget HTML from API
            const response = await fetch(apiUrl, {
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                }
            });

            console.log('📡 API Response status:', response.status);
            console.log('📡 API Response headers:', Object.fromEntries(response.headers.entries()));

            if (response.ok) {
                const data = await response.json();
                console.log('📦 API Response data:', data);
                
                if (data.success && data.html) {
                    console.log('✅ Widget content rendered successfully');
                    console.log('📄 HTML content length:', data.html.length);
                    console.log('📄 HTML preview:', data.html.substring(0, 200) + '...');
                    
                    return `
                        <div class="widget-preview-container" data-widget-type="${widgetType}" data-page-section-widget-id="${pageSectionWidget.id}">
                            <div class="widget-preview-content">
                                ${data.html}
                            </div>
                            <div class="widget-preview-overlay">
                                <div class="widget-controls">
                                    <button class="widget-control-btn" onclick="window.WidgetManager.editWidget('${pageSectionWidget.id}')" title="Edit Widget">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="widget-control-btn" onclick="window.WidgetManager.duplicateWidget('${pageSectionWidget.id}')" title="Duplicate Widget">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                    <button class="widget-control-btn" onclick="window.WidgetManager.deleteWidget('${pageSectionWidget.id}')" title="Delete Widget">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    console.warn('⚠️ Widget render failed:', data.error || 'No error message provided');
                    console.warn('⚠️ Response data:', data);
                    return this.createFallbackWidgetHtml(pageSectionWidget, data.error || 'Failed to render widget');
                }
            } else {
                const errorText = await response.text();
                console.error('❌ Widget render request failed:', response.status);
                console.error('❌ Error response:', errorText);
                return this.createFallbackWidgetHtml(pageSectionWidget, `Failed to load widget (HTTP ${response.status})`);
            }

        } catch (error) {
            console.error('❌ Error rendering widget content:', error);
            console.error('❌ Error details:', {
                message: error.message,
                stack: error.stack
            });
            return this.createFallbackWidgetHtml(pageSectionWidget, 'Error loading widget: ' + error.message);
        }
    },

    /**
     * Create fallback widget HTML when rendering fails
     */
    createFallbackWidgetHtml(pageSectionWidget, errorMessage) {
        const widgetType = pageSectionWidget.settings.widget_type;
        const widgetName = pageSectionWidget.settings.widget_name;

        return `
            <div class="widget-preview-container" data-widget-type="${widgetType}" data-page-section-widget-id="${pageSectionWidget.id}">
                <div class="widget-preview-content">
                    <div class="text-center p-3">
                        <i class="ri-apps-line fs-1 text-muted"></i>
                        <h6>${widgetName}</h6>
                        <small class="text-muted">${errorMessage}</small>
                    </div>
                </div>
                <div class="widget-preview-overlay">
                    <div class="widget-controls">
                        <button class="widget-control-btn" onclick="window.WidgetManager.editWidget('${pageSectionWidget.id}')" title="Edit Widget">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="widget-control-btn" onclick="window.WidgetManager.duplicateWidget('${pageSectionWidget.id}')" title="Duplicate Widget">
                            <i class="ri-file-copy-line"></i>
                        </button>
                        <button class="widget-control-btn" onclick="window.WidgetManager.deleteWidget('${pageSectionWidget.id}')" title="Delete Widget">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Setup widget events
     */
    setupWidgetEvents() {
        // Widget edit event
        document.addEventListener('click', (e) => {
            if (e.target.closest('.widget-control-btn[onclick*="editWidget"]')) {
                e.preventDefault();
                const widgetId = e.target.closest('[data-page-section-widget-id]')?.getAttribute('data-page-section-widget-id');
                if (widgetId) {
                    this.editWidget(widgetId);
                }
            }
        });
    },

    /**
     * Edit widget (placeholder for future implementation)
     */
    editWidget(widgetId) {
        console.log(`✏️ Edit widget ${widgetId} - Not implemented yet`);
        alert('Widget editing will be implemented in Phase 4');
    },

    /**
     * Duplicate widget (placeholder for future implementation)
     */
    duplicateWidget(widgetId) {
        console.log(`📋 Duplicate widget ${widgetId} - Not implemented yet`);
        alert('Widget duplication will be implemented in Phase 4');
    },

    /**
     * Delete widget (placeholder for future implementation)
     */
    deleteWidget(widgetId) {
        console.log(`🗑️ Delete widget ${widgetId} - Not implemented yet`);
        alert('Widget deletion will be implemented in Phase 4');
    },

    /**
     * Show success message
     */
    showSuccess(message) {
        // You can implement a proper notification system here
        console.log('✅ Success:', message);
    },

    /**
     * Show error message
     */
    showError(message) {
        // You can implement a proper notification system here
        console.error('❌ Error:', message);
        alert(message);
    }
}; 