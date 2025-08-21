/**
 * Component Editor Panel
 * Handles editing of component (section/widget) settings and content
 */
class ComponentEditor {
    constructor(api, container) {
        this.api = api;
        this.container = container;
        this.pageId = null;
        this.currentComponent = null;
        this.contentTypes = [];
        this.onComponentUpdate = null; // Callback for component updates
        
        console.log('✏️ ComponentEditor initialized');
    }
    
    /**
     * Initialize the component editor
     */
    async initialize(pageId) {
        this.pageId = pageId;
        
        // Load content types for content selection
        try {
            const response = await this.api.loadContentTypes(pageId);
            if (response.success) {
                this.contentTypes = response.data.content_types || [];
            }
        } catch (error) {
            console.warn('Could not load content types:', error);
        }
        
        this.showEmptyState();
    }
    
    /**
     * Edit a component
     */
    async editComponent(component) {
        this.currentComponent = component;
        
        console.log('✏️ Editing component:', component);
        
        this.showLoading();
        
        try {
            if (component.type === 'widget') {
                await this.renderWidgetEditor(component);
            } else if (component.type === 'section') {
                await this.renderSectionEditor(component);
            }
        } catch (error) {
            console.error('❌ Error rendering component editor:', error);
            this.showError('Failed to load component editor');
        }
    }
    
    /**
     * Render widget editor
     */
    async renderWidgetEditor(component) {
        const widget = component.data;
        
        const html = `
            <div class="component-editor-panel">
                <div class="component-editor-header">
                    <h3 class="component-editor-title">${this.escapeHtml(widget.name)}</h3>
                    <p class="component-editor-subtitle">Widget Settings</p>
                </div>
                <div class="component-editor-content">
                    ${this.renderWidgetSettings(widget)}
                    ${this.renderContentSettings(widget)}
                    ${this.renderStyleSettings(widget)}
                    ${this.renderActions()}
                </div>
            </div>
        `;
        
        this.container.innerHTML = html;
        this.attachEventListeners();
    }
    
    /**
     * Render section editor
     */
    async renderSectionEditor(component) {
        const section = component.data;
        
        const html = `
            <div class="component-editor-panel">
                <div class="component-editor-header">
                    <h3 class="component-editor-title">${this.escapeHtml(section.name)}</h3>
                    <p class="component-editor-subtitle">Section Settings</p>
                </div>
                <div class="component-editor-content">
                    ${this.renderSectionSettings(section)}
                    ${this.renderStyleSettings(section)}
                    ${this.renderActions()}
                </div>
            </div>
        `;
        
        this.container.innerHTML = html;
        this.attachEventListeners();
    }
    
    /**
     * Render widget settings section
     */
    renderWidgetSettings(widget) {
        if (!widget.settings || Object.keys(widget.settings).length === 0) {
            return `
                <div class="editor-section">
                    <h4 class="editor-section-title">Widget Settings</h4>
                    <p class="text-muted">No configurable settings for this widget.</p>
                </div>
            `;
        }
        
        const settingsFields = Object.entries(widget.settings).map(([key, value]) => {
            return `
                <div class="form-group">
                    <label class="form-label">${this.formatFieldLabel(key)}</label>
                    <input type="text" 
                           class="form-control" 
                           name="settings[${key}]" 
                           value="${this.escapeHtml(value)}"
                           data-setting-key="${key}">
                </div>
            `;
        }).join('');
        
        return `
            <div class="editor-section">
                <h4 class="editor-section-title">Widget Settings</h4>
                ${settingsFields}
            </div>
        `;
    }
    
    /**
     * Render content settings section
     */
    renderContentSettings(widget) {
        const currentContentQuery = widget.content_query || {};
        const selectedContentTypeId = currentContentQuery.content_type_id || '';
        
        const contentTypeOptions = this.contentTypes.map(ct => 
            `<option value="${ct.id}" ${ct.id == selectedContentTypeId ? 'selected' : ''}>
                ${this.escapeHtml(ct.name)} (${ct.content_count} items)
            </option>`
        ).join('');
        
        return `
            <div class="editor-section">
                <h4 class="editor-section-title">Content Source</h4>
                
                <div class="form-group">
                    <label class="form-label">Content Type</label>
                    <select class="form-control form-select" name="content_type_id" data-content-type-select>
                        <option value="">No content source</option>
                        ${contentTypeOptions}
                    </select>
                </div>
                
                ${selectedContentTypeId ? this.renderContentQuerySettings(currentContentQuery) : ''}
                
                <div class="form-group">
                    <button type="button" class="btn-outline-primary" data-browse-content>
                        <i class="ri-search-line"></i> Browse Content
                    </button>
                </div>
                
                ${widget.content_items && widget.content_items.length > 0 ? 
                    this.renderCurrentContent(widget.content_items) : ''
                }
            </div>
        `;
    }
    
    /**
     * Render content query settings
     */
    renderContentQuerySettings(contentQuery) {
        return `
            <div class="content-query-settings">
                <div class="form-group">
                    <label class="form-label">Number of Items</label>
                    <input type="number" 
                           class="form-control" 
                           name="content_query[limit]" 
                           value="${contentQuery.limit || 1}" 
                           min="1" max="50">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sort By</label>
                    <select class="form-control form-select" name="content_query[sort_by]">
                        <option value="created_at" ${contentQuery.sort_by === 'created_at' ? 'selected' : ''}>Date Created</option>
                        <option value="updated_at" ${contentQuery.sort_by === 'updated_at' ? 'selected' : ''}>Date Updated</option>
                        <option value="title" ${contentQuery.sort_by === 'title' ? 'selected' : ''}>Title</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <select class="form-control form-select" name="content_query[sort_order]">
                        <option value="desc" ${contentQuery.sort_order === 'desc' ? 'selected' : ''}>Descending</option>
                        <option value="asc" ${contentQuery.sort_order === 'asc' ? 'selected' : ''}>Ascending</option>
                    </select>
                </div>
            </div>
        `;
    }
    
    /**
     * Render current content items
     */
    renderCurrentContent(contentItems) {
        if (!Array.isArray(contentItems) || contentItems.length === 0) {
            return '';
        }
        
        const items = contentItems.slice(0, 3); // Show first 3 items
        
        return `
            <div class="current-content">
                <h5>Current Content</h5>
                <div class="content-preview">
                    ${items.map(item => `
                        <div class="content-preview-item">
                            <strong>${this.escapeHtml(item.title || 'Untitled')}</strong>
                            ${item.excerpt ? `<p>${this.escapeHtml(item.excerpt.substring(0, 100))}...</p>` : ''}
                        </div>
                    `).join('')}
                    ${contentItems.length > 3 ? `<p class="text-muted">... and ${contentItems.length - 3} more</p>` : ''}
                </div>
            </div>
        `;
    }
    
    /**
     * Render section settings
     */
    renderSectionSettings(section) {
        return `
            <div class="editor-section">
                <h4 class="editor-section-title">Section Settings</h4>
                
                <div class="form-group">
                    <label class="form-label">Section Name</label>
                    <input type="text" 
                           class="form-control" 
                           value="${this.escapeHtml(section.name)}" 
                           readonly>
                    <small class="form-text">Section name is defined by the template</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Background Color</label>
                    <input type="color" 
                           class="form-control" 
                           name="background_color" 
                           value="${section.background_color || '#ffffff'}">
                </div>
            </div>
        `;
    }
    
    /**
     * Render style settings section
     */
    renderStyleSettings(component) {
        const data = component.data;
        
        return `
            <div class="editor-section">
                <h4 class="editor-section-title">Styling</h4>
                
                <div class="form-group">
                    <label class="form-label">CSS Classes</label>
                    <input type="text" 
                           class="form-control" 
                           name="css_classes" 
                           value="${this.escapeHtml(data.css_classes || '')}"
                           placeholder="custom-class another-class">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Padding</label>
                    <input type="text" 
                           class="form-control" 
                           name="padding" 
                           value="${this.escapeHtml(data.padding || '')}"
                           placeholder="e.g., 20px or 1rem 2rem">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Margin</label>
                    <input type="text" 
                           class="form-control" 
                           name="margin" 
                           value="${this.escapeHtml(data.margin || '')}"
                           placeholder="e.g., 10px or 1rem 0">
                </div>
            </div>
        `;
    }
    
    /**
     * Render action buttons
     */
    renderActions() {
        return `
            <div class="editor-section">
                <div class="form-actions">
                    <button type="button" class="btn-primary" data-save-component>
                        <i class="ri-save-line"></i> Save Changes
                    </button>
                    <button type="button" class="btn-secondary" data-preview-component>
                        <i class="ri-eye-line"></i> Preview
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Save button
        const saveButton = this.container.querySelector('[data-save-component]');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveComponent());
        }
        
        // Preview button
        const previewButton = this.container.querySelector('[data-preview-component]');
        if (previewButton) {
            previewButton.addEventListener('click', () => this.previewComponent());
        }
        
        // Browse content button
        const browseButton = this.container.querySelector('[data-browse-content]');
        if (browseButton) {
            browseButton.addEventListener('click', () => this.browseContent());
        }
        
        // Content type selection
        const contentTypeSelect = this.container.querySelector('[data-content-type-select]');
        if (contentTypeSelect) {
            contentTypeSelect.addEventListener('change', () => this.onContentTypeChange());
        }
    }
    
    /**
     * Save component changes
     */
    async saveComponent() {
        if (!this.currentComponent) return;
        
        const saveButton = this.container.querySelector('[data-save-component]');
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<div class="loading-spinner"></div> Saving...';
        saveButton.disabled = true;
        
        try {
            // Collect form data
            const formData = this.collectFormData();
            
            // Prepare update data
            const updateData = {
                component_id: this.currentComponent.id,
                component_type: this.currentComponent.type,
                ...formData
            };
            
            console.log('💾 Saving component changes:', updateData);
            
            // Save via API
            const response = await this.api.updateComponent(this.pageId, updateData);
            
            if (response.success) {
                console.log('✅ Component saved successfully');
                
                // Trigger callback if set
                if (this.onComponentUpdate) {
                    this.onComponentUpdate(this.currentComponent.type, this.currentComponent.id, formData);
                }
                
                // Show success feedback
                saveButton.innerHTML = '<i class="ri-check-line"></i> Saved!';
                setTimeout(() => {
                    saveButton.innerHTML = originalText;
                    saveButton.disabled = false;
                }, 2000);
                
            } else {
                throw new Error(response.error || 'Save failed');
            }
            
        } catch (error) {
            console.error('❌ Error saving component:', error);
            saveButton.innerHTML = '<i class="ri-error-warning-line"></i> Error';
            setTimeout(() => {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
            }, 3000);
        }
    }
    
    /**
     * Preview component changes
     */
    async previewComponent() {
        if (!this.currentComponent) return;
        
        try {
            const response = await this.api.getComponentPreview(
                this.pageId,
                this.currentComponent.id,
                this.currentComponent.type
            );
            
            if (response.success) {
                console.log('👀 Component preview loaded:', response.data);
                // You could show a preview modal here
                alert('Preview functionality would show component preview here');
            }
            
        } catch (error) {
            console.error('❌ Error getting component preview:', error);
        }
    }
    
    /**
     * Browse content (open content browser modal)
     */
    browseContent() {
        const contentTypeSelect = this.container.querySelector('[data-content-type-select]');
        const contentTypeId = contentTypeSelect?.value;
        
        if (!contentTypeId) {
            alert('Please select a content type first');
            return;
        }
        
        // This would open the content browser modal
        console.log('🔍 Opening content browser for content type:', contentTypeId);
        
        // For now, just log - the content browser modal would be implemented separately
        if (window.contentBrowser) {
            window.contentBrowser.open(contentTypeId, this.currentComponent);
        } else {
            alert('Content browser functionality would open here');
        }
    }
    
    /**
     * Handle content type selection change
     */
    onContentTypeChange() {
        // Re-render the content settings section when content type changes
        if (this.currentComponent && this.currentComponent.type === 'widget') {
            const contentSection = this.container.querySelector('.editor-section:nth-child(2)');
            if (contentSection) {
                contentSection.innerHTML = this.renderContentSettings(this.currentComponent.data);
                this.attachEventListeners(); // Re-attach listeners
            }
        }
    }
    
    /**
     * Collect form data
     */
    collectFormData() {
        const form = this.container;
        const formData = {};
        
        // Collect settings
        const settingsInputs = form.querySelectorAll('[name^="settings["]');
        if (settingsInputs.length > 0) {
            formData.settings = {};
            settingsInputs.forEach(input => {
                const key = input.dataset.settingKey;
                formData.settings[key] = input.value;
            });
        }
        
        // Collect content query
        const contentQueryInputs = form.querySelectorAll('[name^="content_query["]');
        const contentTypeInput = form.querySelector('[name="content_type_id"]');
        
        if (contentTypeInput?.value || contentQueryInputs.length > 0) {
            formData.content_query = {};
            
            if (contentTypeInput?.value) {
                formData.content_query.content_type_id = parseInt(contentTypeInput.value);
            }
            
            contentQueryInputs.forEach(input => {
                const key = input.name.match(/content_query\[(.+)\]/)[1];
                formData.content_query[key] = input.type === 'number' ? 
                    parseInt(input.value) : input.value;
            });
        }
        
        // Collect style settings
        const styleFields = ['css_classes', 'padding', 'margin', 'background_color'];
        styleFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input && input.value.trim()) {
                formData[field] = input.value.trim();
            }
        });
        
        return formData;
    }
    
    /**
     * Set component update callback
     */
    setComponentUpdateCallback(callback) {
        this.onComponentUpdate = callback;
    }
    
    /**
     * Clear editor
     */
    clear() {
        this.currentComponent = null;
        this.showEmptyState();
    }
    
    /**
     * Show empty state
     */
    showEmptyState() {
        this.container.innerHTML = `
            <div class="component-editor-panel">
                <div class="component-editor-header">
                    <h3 class="component-editor-title">Component Editor</h3>
                    <p class="component-editor-subtitle">Select a component to edit</p>
                </div>
                <div class="component-editor-content">
                    <div class="component-loading">
                        <i class="ri-cursor-line" style="font-size: 2rem; color: #6c757d;"></i>
                        <p>Click on a section or widget in the component tree to edit its settings.</p>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Show loading state
     */
    showLoading() {
        this.container.innerHTML = `
            <div class="component-editor-panel">
                <div class="component-editor-header">
                    <h3 class="component-editor-title">Component Editor</h3>
                    <p class="component-editor-subtitle">Loading...</p>
                </div>
                <div class="component-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading component settings...</p>
                </div>
            </div>
        `;
    }
    
    /**
     * Show error state
     */
    showError(message) {
        this.container.innerHTML = `
            <div class="component-editor-panel">
                <div class="component-editor-header">
                    <h3 class="component-editor-title">Component Editor</h3>
                    <p class="component-editor-subtitle">Error</p>
                </div>
                <div class="component-error">
                    <p>⚠️ ${this.escapeHtml(message)}</p>
                </div>
            </div>
        `;
    }
    
    /**
     * Format field label for display
     */
    formatFieldLabel(key) {
        return key.split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text.toString();
        return div.innerHTML;
    }
}

// Export for global use
window.ComponentEditor = ComponentEditor;

console.log('📦 ComponentEditor module loaded');