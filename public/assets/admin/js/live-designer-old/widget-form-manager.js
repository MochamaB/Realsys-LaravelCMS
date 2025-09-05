/**
 * Widget Form Manager
 * 
 * Manages widget editing forms and handles real-time updates
 */
class WidgetFormManager {
    constructor(livePreview, updateManager) {
        this.livePreview = livePreview;
        this.updateManager = updateManager;
        this.currentWidget = null;
        this.forms = new Map();
        this.watchers = new Map();
        
        this.init();
    }
    
    /**
     * Initialize widget form manager
     */
    init() {
        console.log('📝 Widget Form Manager initialized');
    }
    
    /**
     * Initialize a specific widget form
     */
    initializeWidget(widgetId) {
        this.currentWidget = widgetId;
        this.setupFormWatchers(widgetId);
        this.setupTabSwitching();
        this.setupContentPickers();
        this.setupColorPickers();
        console.log(`📝 Widget ${widgetId} form initialized`);
    }
    
    /**
     * Setup form watchers for real-time updates
     */
    setupFormWatchers(widgetId) {
        const container = document.getElementById('widget-editor-container');
        if (!container) return;
        
        // Remove existing watchers
        this.cleanupWatchers(widgetId);
        
        // Watch settings form
        const settingsForm = container.querySelector('.widget-settings-form, [data-tab-form="settings"]');
        if (settingsForm) {
            this.watchForm(widgetId, 'settings', settingsForm);
        }
        
        // Watch content form
        const contentForm = container.querySelector('.widget-content-form, [data-tab-form="content"]');
        if (contentForm) {
            this.watchForm(widgetId, 'content', contentForm);
        }
        
        // Watch style form
        const styleForm = container.querySelector('.widget-style-form, [data-tab-form="style"]');
        if (styleForm) {
            this.watchForm(widgetId, 'style', styleForm);
        }
    }
    
    /**
     * Watch individual form for changes
     */
    watchForm(widgetId, formType, formElement) {
        const watcherKey = `${widgetId}_${formType}`;
        
        // Create watcher function
        const watcher = (event) => {
            this.handleFormChange(widgetId, formType, event);
        };
        
        // Add event listeners
        const inputs = formElement.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            // Different events for different input types
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.addEventListener('change', watcher);
            } else if (input.type === 'color') {
                input.addEventListener('input', watcher);
                input.addEventListener('change', watcher);
            } else if (input.tagName === 'SELECT') {
                input.addEventListener('change', watcher);
            } else {
                input.addEventListener('input', watcher);
                input.addEventListener('change', watcher);
            }
        });
        
        // Store watcher for cleanup
        this.watchers.set(watcherKey, { inputs, watcher });
    }
    
    /**
     * Handle form change events
     */
    handleFormChange(widgetId, formType, event) {
        const input = event.target;
        const form = input.closest('form') || input.closest('[data-tab-form]');
        
        if (!form) return;
        
        // Get form data
        const formData = this.getFormData(form);
        
        // Prepare update data
        const updateData = {};
        updateData[formType === 'content' ? 'content_query' : formType] = formData;
        
        // Queue update
        this.updateManager.queueUpdate('widget', widgetId, updateData, (response) => {
            this.onUpdateSuccess(widgetId, formType, response);
        });
        
        // Show visual feedback
        this.showFormFeedback(input, 'saving');
        
        console.log(`📝 Widget ${widgetId} ${formType} change:`, formData);
    }
    
    /**
     * Get form data as object
     */
    getFormData(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            // Handle array fields (like checkboxes with same name)
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!data[cleanKey]) data[cleanKey] = [];
                data[cleanKey].push(value);
            } else if (data[key]) {
                // Convert to array if multiple values
                if (!Array.isArray(data[key])) {
                    data[key] = [data[key]];
                }
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }
        
        // Handle unchecked checkboxes
        form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (!checkbox.checked && !data.hasOwnProperty(checkbox.name)) {
                data[checkbox.name] = false;
            }
        });
        
        return data;
    }
    
    /**
     * Handle successful update
     */
    onUpdateSuccess(widgetId, formType, response) {
        console.log(`✅ Widget ${widgetId} ${formType} updated successfully`);
        
        // Show success feedback on form inputs
        const container = document.getElementById('widget-editor-container');
        if (container) {
            const formInputs = container.querySelectorAll('input, select, textarea');
            formInputs.forEach(input => {
                this.showFormFeedback(input, 'saved');
            });
        }
    }
    
    /**
     * Show visual feedback on form inputs
     */
    showFormFeedback(input, state) {
        // Remove existing feedback classes
        input.classList.remove('form-saving', 'form-saved', 'form-error');
        
        // Add new state class
        switch (state) {
            case 'saving':
                input.classList.add('form-saving');
                break;
            case 'saved':
                input.classList.add('form-saved');
                setTimeout(() => {
                    input.classList.remove('form-saved');
                }, 2000);
                break;
            case 'error':
                input.classList.add('form-error');
                setTimeout(() => {
                    input.classList.remove('form-error');
                }, 3000);
                break;
        }
    }
    
    /**
     * Setup tab switching for widget editor
     */
    setupTabSwitching() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetTab = button.dataset.tab;
                
                // Update active button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Update active content
                tabContents.forEach(content => {
                    content.classList.toggle('active', content.dataset.tab === targetTab);
                });
                
                console.log(`📑 Switched to tab: ${targetTab}`);
            });
        });
    }
    
    /**
     * Setup content pickers for content queries
     */
    setupContentPickers() {
        // Content type selector
        const contentTypeSelect = document.querySelector('[name="content_query[content_type_id]"]');
        if (contentTypeSelect) {
            contentTypeSelect.addEventListener('change', (e) => {
                this.loadContentTypeFields(e.target.value);
            });
        }
        
        // Content browser button
        const contentBrowserBtn = document.querySelector('.open-content-browser');
        if (contentBrowserBtn) {
            contentBrowserBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openContentBrowser();
            });
        }
    }
    
    /**
     * Load content type fields dynamically
     */
    async loadContentTypeFields(contentTypeId) {
        if (!contentTypeId) {
            const contentFilters = document.getElementById('content-filters');
            if (contentFilters) {
                contentFilters.innerHTML = '';
            }
            return;
        }
        
        try {
            const response = await fetch(`/admin/api/content-types/${contentTypeId}/fields`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.updateManager.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderContentFilters(data.data.fields);
            }
            
        } catch (error) {
            console.error('Failed to load content type fields:', error);
        }
    }
    
    /**
     * Render content type filters
     */
    renderContentFilters(fields) {
        const container = document.getElementById('content-filters');
        if (!container) return;
        
        let html = '<div class="content-filter-section"><h6>Content Filters</h6>';
        
        // Add common filters
        html += `
            <div class="form-group">
                <label class="form-label">Limit</label>
                <input type="number" name="content_query[limit]" class="form-control" value="5" min="1" max="50">
            </div>
            <div class="form-group">
                <label class="form-label">Order By</label>
                <select name="content_query[order_by]" class="form-control">
                    <option value="created_at">Date Created</option>
                    <option value="updated_at">Date Modified</option>
                    <option value="title">Title</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Order Direction</label>
                <select name="content_query[order_direction]" class="form-control">
                    <option value="desc">Newest First</option>
                    <option value="asc">Oldest First</option>
                </select>
            </div>
        `;
        
        html += '</div>';
        
        container.innerHTML = html;
        
        // Re-setup watchers for new inputs
        if (this.currentWidget) {
            this.setupFormWatchers(this.currentWidget);
        }
    }
    
    /**
     * Setup color pickers
     */
    setupColorPickers() {
        const colorInputs = document.querySelectorAll('input[type="color"], .color-picker');
        
        colorInputs.forEach(input => {
            // Add color preview if not already present
            if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('color-preview')) {
                const preview = document.createElement('div');
                preview.className = 'color-preview';
                preview.style.cssText = `
                    width: 30px;
                    height: 30px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin-left: 10px;
                    display: inline-block;
                    vertical-align: middle;
                    background-color: ${input.value};
                `;
                
                input.parentNode.insertBefore(preview, input.nextSibling);
                
                // Update preview on color change
                input.addEventListener('input', () => {
                    preview.style.backgroundColor = input.value;
                });
            }
        });
    }
    
    /**
     * Open content browser modal
     */
    openContentBrowser() {
        // This would open a content selection modal
        console.log('📖 Opening content browser...');
        // Implementation would depend on your content browser modal
    }
    
    /**
     * Cleanup watchers for a widget
     */
    cleanupWatchers(widgetId) {
        const keys = Array.from(this.watchers.keys()).filter(key => key.startsWith(`${widgetId}_`));
        
        keys.forEach(key => {
            const watcher = this.watchers.get(key);
            if (watcher) {
                watcher.inputs.forEach(input => {
                    input.removeEventListener('input', watcher.watcher);
                    input.removeEventListener('change', watcher.watcher);
                });
                this.watchers.delete(key);
            }
        });
    }
    
    /**
     * Cleanup all watchers
     */
    cleanup() {
        this.watchers.forEach((watcher, key) => {
            watcher.inputs.forEach(input => {
                input.removeEventListener('input', watcher.watcher);
                input.removeEventListener('change', watcher.watcher);
            });
        });
        
        this.watchers.clear();
        console.log('🧹 Widget form watchers cleaned up');
    }
    
    /**
     * Get current widget ID
     */
    getCurrentWidget() {
        return this.currentWidget;
    }
    
    /**
     * Check if a widget form is being edited
     */
    isEditing(widgetId = null) {
        if (widgetId) {
            return this.currentWidget === widgetId;
        }
        return this.currentWidget !== null;
    }
}

// Add CSS for form feedback states
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('widget-form-styles')) {
        const style = document.createElement('style');
        style.id = 'widget-form-styles';
        style.textContent = `
            .form-saving {
                border-color: #ffc107 !important;
                box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
            }
            
            .form-saved {
                border-color: #198754 !important;
                box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
            }
            
            .form-error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            
            .widget-item.selected {
                background-color: #e3f2fd;
                border-left: 3px solid #0d6efd;
            }
            
            .tab-button {
                padding: 0.5rem 1rem;
                border: 1px solid #dee2e6;
                background: #fff;
                color: #495057;
                border-radius: 0;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .tab-button:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .tab-button:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            .tab-button + .tab-button {
                border-left: none;
            }
            
            .tab-button:hover {
                background: #f8f9fa;
            }
            
            .tab-button.active {
                background: #0d6efd;
                color: #fff;
                border-color: #0d6efd;
            }
            
            .tab-content {
                display: none;
                padding: 1rem 0;
            }
            
            .tab-content.active {
                display: block;
            }
            
            .color-preview {
                cursor: pointer;
            }
        `;
        document.head.appendChild(style);
    }
});

// Export for global use
window.WidgetFormManager = WidgetFormManager;