/**
 * Live Designer Components Integration
 * Integrates all component management functionality
 */
class LiveDesignerComponents {
    constructor(api, containers) {
        this.api = api;
        this.pageId = null;
        
        // Component managers
        this.componentTree = null;
        this.componentEditor = null;
        this.contentBrowser = null;
        
        // Containers
        this.containers = containers;
        
        console.log('🎯 LiveDesignerComponents initialized');
    }
    
    /**
     * Initialize all component managers
     */
    async initialize(pageId) {
        this.pageId = pageId;
        
        console.log('🎯 Initializing Live Designer Components for page:', pageId);
        
        try {
            // Initialize component tree
            if (this.containers.componentTree) {
                this.componentTree = new ComponentTree(this.api, this.containers.componentTree);
                await this.componentTree.initialize(pageId);
                
                // Set callback for component selection
                this.componentTree.setComponentSelectCallback((component) => {
                    this.onComponentSelect(component);
                });
            }
            
            // Initialize component editor
            if (this.containers.componentEditor) {
                this.componentEditor = new ComponentEditor(this.api, this.containers.componentEditor);
                await this.componentEditor.initialize(pageId);
                
                // Set callback for component updates
                this.componentEditor.setComponentUpdateCallback((type, id, data) => {
                    this.onComponentUpdate(type, id, data);
                });
            }
            
            // Initialize content browser
            this.contentBrowser = new ContentBrowser(this.api);
            await this.contentBrowser.initialize(pageId);
            
            // Set callback for content selection
            this.contentBrowser.setContentSelectCallback((selectedItems, component) => {
                this.onContentSelect(selectedItems, component);
            });
            
            // Make content browser globally available
            window.contentBrowser = this.contentBrowser;
            
            console.log('✅ Live Designer Components initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Live Designer Components:', error);
        }
    }
    
    /**
     * Handle component selection from tree
     */
    onComponentSelect(component) {
        console.log('🎯 Component selected:', component);
        
        if (this.componentEditor) {
            this.componentEditor.editComponent(component);
        }
    }
    
    /**
     * Handle component updates from editor
     */
    async onComponentUpdate(componentType, componentId, updateData) {
        console.log('🎯 Component updated:', componentType, componentId, updateData);
        
        try {
            // Update component tree if available
            if (this.componentTree) {
                this.componentTree.updateComponent(componentType, componentId, updateData);
            }
            
            // Refresh preview if available
            if (window.liveDesignerMain && window.liveDesignerMain.widgetInitializer) {
                if (componentType === 'widget') {
                    // Refresh specific widget
                    await this.api.refreshPageContent(this.pageId, 'widget', componentId);
                    window.liveDesignerMain.widgetInitializer.reinitializeWidgets();
                } else {
                    // Refresh full page for section changes
                    await this.api.refreshPageContent(this.pageId, 'full');
                    window.liveDesignerMain.widgetInitializer.reinitializeWidgets();
                }
            }
            
        } catch (error) {
            console.error('❌ Error handling component update:', error);
        }
    }
    
    /**
     * Handle content selection from browser
     */
    async onContentSelect(selectedItems, component) {
        console.log('🎯 Content selected:', selectedItems, 'for component:', component);
        
        if (!component || component.type !== 'widget') {
            console.warn('Content selection only supported for widgets');
            return;
        }
        
        try {
            // Update widget content query to use selected content
            const updateData = {
                component_id: component.id,
                component_type: 'widget',
                content_query: {
                    content_type_id: this.contentBrowser.currentContentType.id,
                    content_item_ids: selectedItems.map(item => item.id),
                    limit: selectedItems.length
                }
            };
            
            const response = await this.api.updateComponent(this.pageId, updateData);
            
            if (response.success) {
                console.log('✅ Widget content updated successfully');
                
                // Refresh the component editor to show new content
                const updatedComponent = {
                    ...component,
                    data: {
                        ...component.data,
                        content_query: updateData.content_query,
                        content_items: selectedItems
                    }
                };
                
                if (this.componentEditor) {
                    this.componentEditor.editComponent(updatedComponent);
                }
                
                // Refresh widget preview
                await this.onComponentUpdate('widget', component.id, updateData);
                
            } else {
                console.error('❌ Failed to update widget content:', response.error);
                alert('Failed to update widget content: ' + response.error);
            }
            
        } catch (error) {
            console.error('❌ Error updating widget content:', error);
            alert('Error updating widget content');
        }
    }
    
    /**
     * Refresh all components
     */
    async refresh() {
        console.log('🔄 Refreshing all components...');
        
        try {
            if (this.componentTree) {
                await this.componentTree.refresh();
            }
            
            // Clear editor selection
            if (this.componentEditor) {
                this.componentEditor.clear();
            }
            
        } catch (error) {
            console.error('❌ Error refreshing components:', error);
        }
    }
    
    /**
     * Get current page components data
     */
    getComponentsData() {
        return this.componentTree?.components || null;
    }
    
    /**
     * Get currently selected component
     */
    getSelectedComponent() {
        return this.componentTree?.getSelectedComponent() || null;
    }
    
    /**
     * Open content browser for a specific content type
     */
    openContentBrowser(contentTypeId, component) {
        if (this.contentBrowser) {
            this.contentBrowser.open(contentTypeId, component);
        }
    }
    
    /**
     * Destroy component managers
     */
    destroy() {
        console.log('🗑️ Destroying Live Designer Components');
        
        this.componentTree = null;
        this.componentEditor = null;
        this.contentBrowser = null;
        
        // Remove global references
        if (window.contentBrowser) {
            delete window.contentBrowser;
        }
    }
}

// Export for global use
window.LiveDesignerComponents = LiveDesignerComponents;

console.log('📦 LiveDesignerComponents integration module loaded');