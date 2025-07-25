/**
 * Widget Manager for GridStack
 * Handles widget configuration, editing, and content management
 */
window.WidgetManager = {
    currentWidget: null,
    modalInstance: null,

    /**
     * Initialize widget manager
     */
    init() {
        console.log('🔧 Initializing Widget Manager...');
        this.setupModalEventListeners();
    },

    /**
     * Setup modal event listeners
     */
    setupModalEventListeners() {
        const saveBtn = document.getElementById('saveWidgetConfigBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveWidgetConfiguration();
            });
        }
    },

    /**
     * Edit widget content
     */
    async editWidget(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error('❌ Widget element not found:', elementId);
            return;
        }

        this.currentWidget = element;
        
        const widgetId = element.getAttribute('data-widget-id');
        const widgetName = element.getAttribute('data-widget-name');
        const pageSectionWidgetId = element.getAttribute('data-page-section-widget-id');

        console.log('📝 Editing widget:', {
            elementId,
            widgetId,
            widgetName,
            pageSectionWidgetId: pageSectionWidgetId || 'NEW WIDGET'
        });

        // Show modal (even for new widgets without page_section_widget_id)
        await this.showWidgetConfigModal(widgetId, widgetName, pageSectionWidgetId);
    },

    /**
     * Show widget configuration modal
     */
    async showWidgetConfigModal(widgetId, widgetName, pageSectionWidgetId = null) {
        const modal = document.getElementById('widgetConfigModal');
        const modalTitle = document.getElementById('widgetConfigModalLabel');
        const formContainer = document.getElementById('widgetConfigForm');

        // Set modal title
        modalTitle.textContent = `Configure ${widgetName}`;

        // Show loading state
        formContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div><p>Loading widget configuration...</p></div>';

        // Show modal
        this.modalInstance = new bootstrap.Modal(modal);
        this.modalInstance.show();

        try {
            // Load widget schema and current data
            const [schemaData, currentData] = await Promise.all([
                this.loadWidgetSchema(widgetId),
                pageSectionWidgetId ? this.loadWidgetCurrentData(pageSectionWidgetId) : null
            ]);

            // Render configuration form
            this.renderWidgetConfigForm(schemaData, currentData);

        } catch (error) {
            console.error('❌ Failed to load widget configuration:', error);
            formContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error Loading Configuration</h6>
                    <p>${error.message}</p>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.WidgetManager.retryLoadConfig()">Retry</button>
                </div>
            `;
        }
    },

    /**
     * Load widget schema
     */
    async loadWidgetSchema(widgetId) {
        const response = await fetch(`/admin/api/widgets/${widgetId}/schema`, {
            headers: {
                'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to load widget schema: ${response.status}`);
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Invalid schema response');
        }

        return data.schema;
    },

    /**
     * Load widget current data
     */
    async loadWidgetCurrentData(pageSectionWidgetId) {
        const response = await fetch(`/admin/api/page-section-widgets/${pageSectionWidgetId}`, {
            headers: {
                'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to load widget data: ${response.status}`);
        }

        const data = await response.json();
        return data.data || {};
    },

    /**
     * Render widget configuration form
     */
    renderWidgetConfigForm(schema, currentData = {}) {
        const formContainer = document.getElementById('widgetConfigForm');
        
        let formHTML = '';

        // Content Type Selection
        formHTML += `
            <div class="form-group mb-3">
                <label class="form-label">Content Source</label>
                <select class="form-control" id="contentTypeSelect">
                    <option value="">Manual Content</option>
                    <!-- Content types will be loaded dynamically -->
                </select>
                <small class="form-text text-muted">Choose how this widget gets its content</small>
            </div>
        `;

        // Dynamic Fields Container
        formHTML += '<div id="dynamicFields">';

        // Render schema fields
        if (schema && schema.fields) {
            schema.fields.forEach(field => {
                formHTML += this.renderFieldInput(field, currentData.settings || {});
            });
        }

        formHTML += '</div>';

        // Content Query Builder (hidden by default)
        formHTML += `
            <div id="contentQueryBuilder" style="display: none;">
                <h6>Content Selection</h6>
                <div class="form-group mb-3">
                    <label class="form-label">Select Items</label>
                    <select multiple class="form-control" id="contentItemsSelect">
                        <!-- Populated via AJAX -->
                    </select>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Limit</label>
                            <input type="number" class="form-control" id="contentLimit" value="5" min="1" max="100">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Order By</label>
                            <select class="form-control" id="contentOrderBy">
                                <option value="created_at">Date Created</option>
                                <option value="title">Title</option>
                                <option value="updated_at">Date Updated</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;

        formContainer.innerHTML = formHTML;

        // Setup form interactions
        this.setupFormInteractions();

        // Load content types
        this.loadContentTypes();

        // Populate current values
        if (currentData) {
            this.populateFormValues(currentData);
        }
    },

    /**
     * Render individual field input
     */
    renderFieldInput(field, currentValues = {}) {
        const fieldValue = currentValues[field.name] || field.default || '';
        let inputHTML = '';

        switch (field.type) {
            case 'text':
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <input type="text" class="form-control" name="${field.name}" value="${fieldValue}" ${field.required ? 'required' : ''}>
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            case 'textarea':
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <textarea class="form-control" name="${field.name}" rows="3" ${field.required ? 'required' : ''}>${fieldValue}</textarea>
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            case 'number':
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <input type="number" class="form-control" name="${field.name}" value="${fieldValue}" ${field.required ? 'required' : ''}>
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            case 'boolean':
                inputHTML = `
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="${field.name}" ${fieldValue ? 'checked' : ''}>
                            <label class="form-check-label">${field.label}</label>
                        </div>
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            case 'select':
                let options = '';
                if (field.options) {
                    field.options.forEach(option => {
                        const selected = option.value === fieldValue ? 'selected' : '';
                        options += `<option value="${option.value}" ${selected}>${option.label}</option>`;
                    });
                }
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <select class="form-control" name="${field.name}" ${field.required ? 'required' : ''}>
                            ${options}
                        </select>
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            case 'color':
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <input type="color" class="form-control form-control-color" name="${field.name}" value="${fieldValue || '#000000'}">
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            case 'image':
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="${field.name}" value="${fieldValue}" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.WidgetManager.selectImage('${field.name}')">
                                <i class="ri-image-line"></i> Select Image
                            </button>
                        </div>
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
                break;

            default:
                inputHTML = `
                    <div class="form-group mb-3">
                        <label class="form-label">${field.label}</label>
                        <input type="text" class="form-control" name="${field.name}" value="${fieldValue}">
                        ${field.help ? `<small class="form-text text-muted">${field.help}</small>` : ''}
                    </div>
                `;
        }

        return inputHTML;
    },

    /**
     * Setup form interactions
     */
    setupFormInteractions() {
        const contentTypeSelect = document.getElementById('contentTypeSelect');
        const contentQueryBuilder = document.getElementById('contentQueryBuilder');

        if (contentTypeSelect) {
            contentTypeSelect.addEventListener('change', (e) => {
                if (e.target.value) {
                    contentQueryBuilder.style.display = 'block';
                    this.onContentTypeChange(e.target.value);
                } else {
                    contentQueryBuilder.style.display = 'none';
                }
            });
        }
    },

    /**
     * Load available content types
     */
    async loadContentTypes() {
        try {
            const response = await fetch('/admin/api/content-types', {
                headers: {
                    'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                const select = document.getElementById('contentTypeSelect');
                
                // Add content type options
                data.content_types?.forEach(contentType => {
                    const option = document.createElement('option');
                    option.value = contentType.id;
                    option.textContent = contentType.name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('❌ Failed to load content types:', error);
        }
    },

    /**
     * Handle content type change
     */
    async onContentTypeChange(contentTypeId) {
        try {
            const response = await fetch(`/admin/api/content-types/${contentTypeId}/items`, {
                headers: {
                    'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                const select = document.getElementById('contentItemsSelect');
                select.innerHTML = '';

                // Add content item options
                data.items?.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.title || item.name || `Item ${item.id}`;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('❌ Failed to load content items:', error);
        }
    },

    /**
     * Populate form values with current data
     */
    populateFormValues(currentData) {
        // Populate content type
        if (currentData.content_query?.content_type_id) {
            const contentTypeSelect = document.getElementById('contentTypeSelect');
            contentTypeSelect.value = currentData.content_query.content_type_id;
            contentTypeSelect.dispatchEvent(new Event('change'));
        }

        // Populate other fields
        const form = document.getElementById('widgetConfigForm');
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            const fieldName = input.name;
            if (fieldName && currentData.settings && currentData.settings[fieldName] !== undefined) {
                if (input.type === 'checkbox') {
                    input.checked = currentData.settings[fieldName];
                } else {
                    input.value = currentData.settings[fieldName];
                }
            }
        });
    },

    /**
     * Save widget configuration
     */
    async saveWidgetConfiguration() {
        if (!this.currentWidget) {
            console.error('❌ No current widget to save');
            return;
        }

        try {
            const formData = this.serializeWidgetConfig();
            
            console.log('💾 Saving widget configuration:', formData);

            // Save to backend
            await this.saveWidgetToBackend(formData);

            // Refresh widget preview
            await this.refreshWidgetPreview();

            // Close modal
            if (this.modalInstance) {
                this.modalInstance.hide();
            }

            console.log('✅ Widget configuration saved successfully');

        } catch (error) {
            console.error('❌ Failed to save widget configuration:', error);
            alert('Failed to save widget configuration. Please try again.');
        }
    },

    /**
     * Serialize widget configuration from form
     */
    serializeWidgetConfig() {
        const form = document.getElementById('widgetConfigForm');
        const formData = new FormData(form);
        
        const config = {
            widget_id: this.currentWidget.getAttribute('data-widget-id'),
            page_section_widget_id: this.currentWidget.getAttribute('data-page-section-widget-id'),
            settings: {},
            content_query: {},
            css_classes: ''
        };

        // Serialize form fields
        for (let [key, value] of formData.entries()) {
            config.settings[key] = value;
        }

        // Handle content query
        const contentTypeSelect = document.getElementById('contentTypeSelect');
        if (contentTypeSelect && contentTypeSelect.value) {
            config.content_query.content_type_id = parseInt(contentTypeSelect.value);
            
            const contentItemsSelect = document.getElementById('contentItemsSelect');
            const contentLimit = document.getElementById('contentLimit');
            const contentOrderBy = document.getElementById('contentOrderBy');
            
            if (contentItemsSelect) {
                config.content_query.content_item_ids = Array.from(contentItemsSelect.selectedOptions).map(opt => parseInt(opt.value));
            }
            
            if (contentLimit) {
                config.content_query.limit = parseInt(contentLimit.value);
            }
            
            if (contentOrderBy) {
                config.content_query.order_by = contentOrderBy.value;
            }
        }

        return config;
    },

    /**
     * Save widget to backend
     */
    async saveWidgetToBackend(configData) {
        const pageSectionWidgetId = configData.page_section_widget_id;
        
        const url = pageSectionWidgetId 
            ? `/admin/api/page-section-widgets/${pageSectionWidgetId}`
            : `/admin/api/pages/${window.GridStackPageBuilder.config.pageId}/widgets`;
            
        const method = pageSectionWidgetId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(configData)
        });

        if (!response.ok) {
            throw new Error(`Failed to save widget: ${response.status}`);
        }

        return await response.json();
    },

    /**
     * Refresh widget preview after configuration change
     */
    async refreshWidgetPreview() {
        if (!this.currentWidget) return;

        const widgetId = this.currentWidget.getAttribute('data-widget-id');
        const pageSectionWidgetId = this.currentWidget.getAttribute('data-page-section-widget-id');

        if (widgetId) {
            // Reload widget preview
            await window.WidgetLibrary.loadWidgetPreview(this.currentWidget, {
                id: widgetId,
                name: this.currentWidget.getAttribute('data-widget-name'),
                slug: this.currentWidget.getAttribute('data-widget-slug')
            });
        }
    },

    /**
     * Duplicate widget
     */
    duplicateWidget(elementId) {
        console.log('📋 Duplicating widget:', elementId);
        // Implementation for duplicating widgets
    },

    /**
     * Delete widget
     */
    deleteWidget(elementId) {
        if (confirm('Are you sure you want to delete this widget?')) {
            const element = document.getElementById(elementId);
            if (element && window.GridStackPageBuilder.gridStack) {
                window.GridStackPageBuilder.gridStack.removeWidget(element);
            }
        }
    },

    /**
     * Select image for image fields
     */
    selectImage(fieldName) {
        console.log('🖼️ Selecting image for field:', fieldName);
        // Implementation for image selection
        // This would typically open a media library modal
    },

    /**
     * Retry loading configuration
     */
    retryLoadConfig() {
        if (this.currentWidget) {
            const widgetId = this.currentWidget.getAttribute('data-widget-id');
            const widgetName = this.currentWidget.getAttribute('data-widget-name');
            const pageSectionWidgetId = this.currentWidget.getAttribute('data-page-section-widget-id');
            
            this.showWidgetConfigModal(widgetId, widgetName, pageSectionWidgetId);
        }
    }
}; 