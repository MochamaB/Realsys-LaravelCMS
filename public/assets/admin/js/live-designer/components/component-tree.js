/**
 * Component Tree Manager
 * Handles the sidebar component tree for Live Designer
 */
class ComponentTree {
    constructor(api, container) {
        this.api = api;
        this.container = container;
        this.pageId = null;
        this.components = null;
        this.selectedComponent = null;
        this.onComponentSelect = null; // Callback for component selection
        
        console.log('🌳 ComponentTree initialized');
    }
    
    /**
     * Initialize the component tree for a page
     */
    async initialize(pageId) {
        this.pageId = pageId;
        
        console.log('🌳 Initializing component tree for page:', pageId);
        
        this.showLoading();
        
        try {
            const response = await this.api.loadPageComponents(pageId);
            
            if (response.success) {
                this.components = response.data;
                this.render();
                console.log('✅ Component tree loaded successfully');
            } else {
                this.showError('Failed to load components: ' + response.error);
            }
        } catch (error) {
            console.error('❌ Error initializing component tree:', error);
            this.showError('Failed to load component tree');
        }
    }
    
    /**
     * Render the component tree
     */
    render() {
        if (!this.components) {
            this.showError('No component data available');
            return;
        }
        
        const html = `
            <div class="component-tree-sidebar">
                <div class="component-tree-header">
                    <h3>Components</h3>
                    <div class="component-tree-stats">
                        <small>${this.components.sections?.length || 0} sections</small>
                    </div>
                </div>
                <div class="component-tree-content">
                    ${this.renderSections()}
                </div>
            </div>
        `;
        
        this.container.innerHTML = html;
        this.attachEventListeners();
    }
    
    /**
     * Render sections and their widgets
     */
    renderSections() {
        if (!this.components.sections || this.components.sections.length === 0) {
            return '<div class="component-loading">No sections found</div>';
        }
        
        return this.components.sections.map(section => `
            <div class="section-item" data-section-id="${section.id}">
                <div class="section-header" data-component-type="section" data-component-id="${section.id}">
                    <div class="section-info">
                        <h4 class="section-title">${this.escapeHtml(section.name)}</h4>
                        <small class="section-slug">${this.escapeHtml(section.slug)}</small>
                    </div>
                    <div class="section-actions">
                        <span class="widget-count">${section.widgets?.length || 0} widgets</span>
                        <i class="section-collapse-icon ri-arrow-right-s-line"></i>
                    </div>
                </div>
                <div class="section-widgets" style="display: block;">
                    ${this.renderWidgets(section.widgets || [])}
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Render widgets for a section
     */
    renderWidgets(widgets) {
        if (widgets.length === 0) {
            return '<div class="widget-list-empty">No widgets in this section</div>';
        }
        
        return `
            <ul class="widget-list">
                ${widgets.map(widget => `
                    <li class="widget-item" 
                        data-widget-id="${widget.id}" 
                        data-component-type="widget" 
                        data-component-id="${widget.id}">
                        <div class="widget-header">
                            <div class="widget-icon">
                                <i class="${widget.icon || 'ri-puzzle-line'}"></i>
                            </div>
                            <div class="widget-info">
                                <div class="widget-name">${this.escapeHtml(widget.name)}</div>
                                <div class="widget-slug">${this.escapeHtml(widget.slug)}</div>
                            </div>
                            <div class="widget-status">
                                ${widget.content_items?.length > 0 ? 
                                    `<span class="content-indicator" title="Has content">${widget.content_items.length}</span>` : 
                                    '<span class="content-indicator empty" title="No content">—</span>'
                                }
                            </div>
                        </div>
                        <div class="widget-description">
                            ${widget.description ? this.escapeHtml(widget.description) : 'No description'}
                        </div>
                    </li>
                `).join('')}
            </ul>
        `;
    }
    
    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Section toggle
        this.container.querySelectorAll('.section-header').forEach(header => {
            header.addEventListener('click', (e) => {
                if (e.target.closest('.section-actions')) {
                    this.toggleSection(header);
                } else {
                    this.selectComponent(header);
                }
            });
        });
        
        // Widget selection
        this.container.querySelectorAll('.widget-item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectComponent(item);
            });
        });
    }
    
    /**
     * Toggle section expand/collapse
     */
    toggleSection(sectionHeader) {
        const sectionItem = sectionHeader.closest('.section-item');
        const widgetsContainer = sectionItem.querySelector('.section-widgets');
        const icon = sectionHeader.querySelector('.section-collapse-icon');
        
        if (widgetsContainer.style.display === 'none') {
            widgetsContainer.style.display = 'block';
            icon.classList.add('expanded');
        } else {
            widgetsContainer.style.display = 'none';
            icon.classList.remove('expanded');
        }
    }
    
    /**
     * Select a component (section or widget)
     */
    selectComponent(element) {
        // Clear previous selection
        this.container.querySelectorAll('.selected').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Add selection to clicked element
        element.classList.add('selected');
        
        // Get component data
        const componentType = element.dataset.componentType;
        const componentId = parseInt(element.dataset.componentId);
        
        // Find component data
        let componentData = null;
        if (componentType === 'section') {
            componentData = this.components.sections.find(s => s.id === componentId);
        } else if (componentType === 'widget') {
            // Find widget in any section
            for (const section of this.components.sections) {
                const widget = section.widgets?.find(w => w.id === componentId);
                if (widget) {
                    componentData = widget;
                    break;
                }
            }
        }
        
        this.selectedComponent = {
            type: componentType,
            id: componentId,
            data: componentData,
            element: element
        };
        
        console.log('🎯 Component selected:', this.selectedComponent);
        
        // Trigger callback if set
        if (this.onComponentSelect) {
            this.onComponentSelect(this.selectedComponent);
        }
    }
    
    /**
     * Refresh component data
     */
    async refresh() {
        if (!this.pageId) return;
        
        console.log('🔄 Refreshing component tree...');
        await this.initialize(this.pageId);
    }
    
    /**
     * Update component in tree after changes
     */
    updateComponent(componentType, componentId, updatedData) {
        if (!this.components) return;
        
        if (componentType === 'section') {
            const sectionIndex = this.components.sections.findIndex(s => s.id === componentId);
            if (sectionIndex !== -1) {
                this.components.sections[sectionIndex] = { ...this.components.sections[sectionIndex], ...updatedData };
            }
        } else if (componentType === 'widget') {
            // Find and update widget
            for (const section of this.components.sections) {
                const widgetIndex = section.widgets?.findIndex(w => w.id === componentId) ?? -1;
                if (widgetIndex !== -1) {
                    section.widgets[widgetIndex] = { ...section.widgets[widgetIndex], ...updatedData };
                    break;
                }
            }
        }
        
        // Re-render if this component is currently visible
        this.render();
    }
    
    /**
     * Get currently selected component
     */
    getSelectedComponent() {
        return this.selectedComponent;
    }
    
    /**
     * Set component selection callback
     */
    setComponentSelectCallback(callback) {
        this.onComponentSelect = callback;
    }
    
    /**
     * Show loading state
     */
    showLoading() {
        this.container.innerHTML = `
            <div class="component-tree-sidebar">
                <div class="component-tree-header">
                    <h3>Components</h3>
                </div>
                <div class="component-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading components...</p>
                </div>
            </div>
        `;
    }
    
    /**
     * Show error state
     */
    showError(message) {
        this.container.innerHTML = `
            <div class="component-tree-sidebar">
                <div class="component-tree-header">
                    <h3>Components</h3>
                </div>
                <div class="component-error">
                    <p>⚠️ ${this.escapeHtml(message)}</p>
                    <button class="btn-primary" onclick="componentTree.refresh()">Retry</button>
                </div>
            </div>
        `;
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export for global use
window.ComponentTree = ComponentTree;

console.log('📦 ComponentTree module loaded');