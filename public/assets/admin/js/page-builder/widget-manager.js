/**
 * Widget Manager
 * 
 * Handles all widget-related operations including CRUD, positioning,
 * rendering, and drag & drop functionality.
 */
class WidgetManager {
    constructor(api, sectionManager) {
        this.api = api;
        this.sectionManager = sectionManager;
        this.widgets = new Map(); // Store widgets by ID
        this.widgetElements = new Map(); // Store DOM elements by widget ID
        this.availableWidgets = new Map(); // Store available widgets for sidebar
        
        console.log('🧩 Widget Manager initialized');
    }

    /**
     * Load available widgets for the sidebar
     */
    async loadAvailableWidgets() {
        try {
            console.log('🔄 Loading available widgets...');
            
            const response = await this.api.getAvailableWidgets();
            
            if (response.success && response.data) {
                // Clear existing available widgets
                this.availableWidgets.clear();
                
                // Store available widgets by category
                Object.keys(response.data).forEach(category => {
                    response.data[category].forEach(widget => {
                        this.availableWidgets.set(widget.id, {
                            ...widget,
                            category
                        });
                    });
                });
                
                console.log(`✅ Loaded available widgets by category:`, response.data);
                return response.data;
            } else {
                console.warn('⚠️ No available widgets found');
                return {};
            }
        } catch (error) {
            console.error('❌ Error loading available widgets:', error);
            throw error;
        }
    }

    /**
     * Load widgets for a specific section
     */
    async loadSectionWidgets(sectionId) {
        try {
            console.log(`🔄 Loading widgets for section ${sectionId}...`);
            
            const response = await this.api.getSectionWidgets(sectionId);
            
            if (response.success && response.data) {
                const sectionWidgets = response.data;
                
                // Store widgets
                sectionWidgets.forEach(widget => {
                    this.widgets.set(widget.id, widget);
                });
                
                console.log(`✅ Loaded ${sectionWidgets.length} widgets for section ${sectionId}:`, sectionWidgets);
                
                // Render widgets in the section
                await this.renderSectionWidgets(sectionId, sectionWidgets);
                
                return sectionWidgets;
            } else {
                console.log(`📭 No widgets found for section ${sectionId}`);
                
                // Render empty widgets (will show "No widgets" message)
                await this.renderSectionWidgets(sectionId, []);
                
                return [];
            }
        } catch (error) {
            console.error(`❌ Error loading widgets for section ${sectionId}:`, error);
            throw error;
        }
    }

    /**
     * Create a new widget in a section
     */
    async createWidget(sectionId, widgetId, options = {}) {
        try {
            console.log('🔨 Creating new widget:', { sectionId, widgetId, options });
            
            const widgetData = {
                widget_id: widgetId,
                grid_x: options.grid_x || 0,
                grid_y: options.grid_y || 0,
                grid_w: options.grid_w || 6,
                grid_h: options.grid_h || 3,
                settings: options.settings || {},
                content_query: options.content_query || {},
                ...options
            };

            const response = await this.api.createWidget(sectionId, widgetData);
            
            if (response.success && response.data) {
                const newWidget = response.data;
                this.widgets.set(newWidget.id, newWidget);
                
                console.log('✅ Widget created:', newWidget);
                
                // Reload section widgets to show the new widget
                await this.loadSectionWidgets(sectionId);
                
                return newWidget;
            } else {
                throw new Error('Failed to create widget');
            }
        } catch (error) {
            console.error('❌ Error creating widget:', error);
            throw error;
        }
    }

    /**
     * Update widget properties
     */
    async updateWidget(widgetId, updates) {
        try {
            console.log('📝 Updating widget:', { widgetId, updates });
            
            const response = await this.api.updateWidget(widgetId, updates);
            
            if (response.success && response.data) {
                const updatedWidget = response.data;
                this.widgets.set(widgetId, updatedWidget);
                
                console.log('✅ Widget updated:', updatedWidget);
                return updatedWidget;
            } else {
                throw new Error('Failed to update widget');
            }
        } catch (error) {
            console.error('❌ Error updating widget:', error);
            throw error;
        }
    }

    /**
     * Delete a widget
     */
    async deleteWidget(widgetId) {
        try {
            console.log('🗑️ Deleting widget:', widgetId);
            
            const widget = this.widgets.get(widgetId);
            if (!widget) {
                throw new Error('Widget not found');
            }
            
            const response = await this.api.deleteWidget(widgetId);
            
            if (response.success) {
                // Remove from local storage
                this.widgets.delete(widgetId);
                this.widgetElements.delete(widgetId);
                
                console.log('✅ Widget deleted:', widgetId);
                
                // Reload section widgets to update display
                await this.loadSectionWidgets(widget.page_section_id);
                
                return true;
            } else {
                throw new Error('Failed to delete widget');
            }
        } catch (error) {
            console.error('❌ Error deleting widget:', error);
            throw error;
        }
    }

    /**
     * Update widget position
     */
    async updateWidgetPosition(widgetId, position) {
        try {
            console.log('📍 Updating widget position:', { widgetId, position });
            
            const positionData = {
                grid_x: position.x,
                grid_y: position.y,
                grid_w: position.w,
                grid_h: position.h
            };

            const response = await this.api.updateWidgetPosition(widgetId, positionData);
            
            if (response.success) {
                // Update local widget data
                const widget = this.widgets.get(widgetId);
                if (widget && widget.grid_position) {
                    widget.grid_position.x = position.x;
                    widget.grid_position.y = position.y;
                    widget.grid_position.w = position.w;
                    widget.grid_position.h = position.h;
                }
                
                console.log('✅ Widget position updated');
                return true;
            } else {
                throw new Error('Failed to update widget position');
            }
        } catch (error) {
            console.error('❌ Error updating widget position:', error);
            throw error;
        }
    }

    /**
     * Render widget preview
     */
    async renderWidget(widgetId, renderData = {}) {
        try {
            console.log('🎨 Rendering widget preview:', { widgetId, renderData });
            
            const response = await this.api.renderWidget(widgetId, renderData);
            
            if (response.success) {
                console.log('✅ Widget rendered successfully');
                return response.html || '<div class="widget-placeholder">Widget rendered</div>';
            } else {
                console.warn('⚠️ Widget render failed, using fallback');
                return response.html || '<div class="widget-error">Error rendering widget</div>';
            }
        } catch (error) {
            console.error('❌ Error rendering widget:', error);
            return '<div class="widget-error">Error rendering widget</div>';
        }
    }

    /**
     * Render widgets in section grids (matching old implementation)
     */
    async renderSectionWidgets(sectionId, widgets) {
        try {
            console.log(`🎨 Rendering ${widgets.length} widgets for section ${sectionId}`);
            
            const sectionElement = this.sectionManager.getSectionElement(sectionId);
            if (!sectionElement) {
                console.error('❌ Section element not found for ID:', sectionId);
                return;
            }

            const sectionGrids = sectionElement.querySelectorAll('.section-grid');
            
            if (widgets.length === 0) {
                // Show empty state for each grid (matching old implementation)
                sectionGrids.forEach(grid => {
                    const emptyMsg = document.createElement('div');
                    emptyMsg.className = 'text-muted text-center py-3 border border-dashed rounded';
                    emptyMsg.innerHTML = '<i class="ri-apps-line me-2"></i>No widgets';
                    grid.appendChild(emptyMsg);
                });
                return;
            }

            // For now, just add all widgets to the first/main grid (matching old implementation)
            const mainGrid = sectionGrids[0];
            if (!mainGrid) return;

            // Clear existing content
            mainGrid.innerHTML = '';

            // Render each widget with actual content
            for (const widget of widgets) {
                const widgetElement = await this.createRealWidgetElement(widget);
                mainGrid.appendChild(widgetElement);
            }
            
        } catch (error) {
            console.error('❌ Error rendering section widgets:', error);
        }
    }

    /**
     * Create widget element with real rendered content
     */
    async createRealWidgetElement(widget) {
        try {
            console.log('🎨 Creating real widget element for:', widget.id);
            
            // Get the actual rendered widget HTML
            const widgetHtml = await this.renderWidget(widget.widget_id || widget.id, {
                page_section_widget_id: widget.id
            });
            
            // Create widget wrapper with theme context
            const widgetWrapper = document.createElement('div');
            widgetWrapper.className = 'widget-preview-item mb-3';
            widgetWrapper.setAttribute('data-widget-id', widget.id);
            widgetWrapper.setAttribute('data-widget-type', widget.widget?.slug || widget.slug || 'unknown');
            
            // Add some admin styling while preserving frontend appearance
            widgetWrapper.style.cssText = `
                position: relative;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 1rem;
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                transition: all 0.2s ease;
            `;
            
            // Add hover effects
            widgetWrapper.addEventListener('mouseenter', () => {
                widgetWrapper.style.border = '1px solid #007bff';
                widgetWrapper.style.boxShadow = '0 4px 8px rgba(0,123,255,0.15)';
            });
            
            widgetWrapper.addEventListener('mouseleave', () => {
                widgetWrapper.style.border = '1px solid #e9ecef';
                widgetWrapper.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
            });
            
            // Create widget content container
            const widgetContent = document.createElement('div');
            widgetContent.className = 'widget-content';
            widgetContent.innerHTML = widgetHtml;
            
            // Create widget admin overlay (shows on hover)
            const adminOverlay = document.createElement('div');
            adminOverlay.className = 'widget-admin-overlay';
            adminOverlay.style.cssText = `
                position: absolute;
                top: 8px;
                right: 8px;
                opacity: 0;
                transition: opacity 0.2s ease;
                z-index: 10;
            `;
            
            adminOverlay.innerHTML = `
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-primary edit-widget-btn" title="Edit Widget">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-widget-btn" title="Delete Widget">
                        <i class="ri-delete-line"></i>
                    </button>
                </div>
            `;
            
            // Show overlay on hover
            widgetWrapper.addEventListener('mouseenter', () => {
                adminOverlay.style.opacity = '1';
            });
            
            widgetWrapper.addEventListener('mouseleave', () => {
                adminOverlay.style.opacity = '0';
            });
            
            // Assemble widget element
            widgetWrapper.appendChild(widgetContent);
            widgetWrapper.appendChild(adminOverlay);
            
            // Attach event handlers
            this.attachRealWidgetEvents(widgetWrapper, widget);
            
            return widgetWrapper;
            
        } catch (error) {
            console.error('❌ Error creating real widget element:', error);
            
            // Fallback to simple widget display
            return this.createOldStyleWidgetElement(widget, 0);
        }
    }

    /**
     * Attach events to real widget elements
     */
    attachRealWidgetEvents(widgetElement, widget) {
        // Edit button
        const editBtn = widgetElement.querySelector('.edit-widget-btn');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleEditWidget(widget.id);
            });
        }
        
        // Delete button
        const deleteBtn = widgetElement.querySelector('.delete-widget-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleDeleteWidget(widget.id);
            });
        }
    }

    /**
     * Create widget element for display (matching old implementation)
     */
    createOldStyleWidgetElement(widget, index) {
        const widgetDiv = document.createElement('div');
        widgetDiv.className = 'widget-item border rounded p-3 mb-2';
        widgetDiv.setAttribute('data-widget-id', widget.id);
        widgetDiv.setAttribute('data-widget-type', widget.widget?.slug || 'unknown');

        widgetDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="widget-icon me-3">
                    <i class="ri-apps-line fs-4 text-primary"></i>
                </div>
                <div class="widget-info flex-fill">
                    <h6 class="mb-1">${widget.widget?.name || widget.name || 'Unknown Widget'}</h6>
                    <small class="text-muted">
                        Type: ${widget.widget?.slug || widget.slug || 'unknown'} | 
                        Position: ${widget.grid_x || 0}, ${widget.grid_y || 0} | 
                        Size: ${widget.grid_w || 'auto'}x${widget.grid_h || 'auto'}
                    </small>
                    <div class="widget-debug" style="font-size: 10px; color: #666; margin-top: 4px;">
                        Widget ID: ${widget.id} | Data: ${JSON.stringify(widget).substring(0, 100)}...
                    </div>
                </div>
                <div class="widget-status">
                    <span class="badge bg-success">Loaded</span>
                </div>
            </div>
        `;

        return widgetDiv;
    }

    /**
     * Create widget HTML element (new implementation style)
     */
    createWidgetHTML(widget) {
        const widgetName = widget.widget_name || 'Unknown Widget';
        const widgetSlug = widget.widget_slug || 'unknown';
        const position = widget.grid_position || {};
        
        return `
            <div class="section-widget mb-3 p-3 border rounded" data-widget-id="${widget.id}">
                <div class="widget-header d-flex justify-content-between align-items-center mb-2">
                    <div class="widget-info">
                        <h6 class="mb-0">
                            <i class="ri-apps-line me-2"></i>
                            ${widgetName}
                        </h6>
                        <small class="text-muted">
                            ${widgetSlug} • Position: ${position.x || 0},${position.y || 0} • 
                            Size: ${position.w || 6}x${position.h || 3}
                        </small>
                    </div>
                    <div class="widget-controls">
                        <button class="btn btn-sm btn-outline-primary edit-widget-btn" 
                                data-widget-id="${widget.id}" title="Edit Widget">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-widget-btn ms-1" 
                                data-widget-id="${widget.id}" title="Delete Widget">
                            <i class="ri-delete-line"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-preview">
                    <div class="widget-loading text-center py-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <small class="d-block mt-1 text-muted">Loading preview...</small>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Attach event listeners to widget element
     */
    attachWidgetEvents(element, widget) {
        // Edit widget button
        const editBtn = element.querySelector('.edit-widget-btn');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleEditWidget(widget.id);
            });
        }
        
        // Delete widget button
        const deleteBtn = element.querySelector('.delete-widget-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleDeleteWidget(widget.id);
            });
        }
        
        // Load widget preview
        this.loadWidgetPreview(element, widget);
    }

    /**
     * Load widget preview in element
     */
    async loadWidgetPreview(element, widget) {
        const previewContainer = element.querySelector('.widget-preview');
        if (!previewContainer) return;
        
        try {
            const html = await this.renderWidget(widget.widget_id, {
                page_section_widget_id: widget.id
            });
            
            previewContainer.innerHTML = html;
        } catch (error) {
            previewContainer.innerHTML = `
                <div class="widget-error text-center py-2 text-muted">
                    <i class="ri-error-warning-line"></i>
                    <small class="d-block">Preview unavailable</small>
                </div>
            `;
        }
    }

    /**
     * Handle edit widget action
     */
    handleEditWidget(widgetId) {
        console.log('✏️ Edit widget requested:', widgetId);
        
        // Emit custom event for other components to handle
        document.dispatchEvent(new CustomEvent('pagebuilder:edit-widget', {
            detail: { widgetId, widget: this.widgets.get(widgetId) }
        }));
    }

    /**
     * Handle delete widget action
     */
    async handleDeleteWidget(widgetId) {
        const widget = this.widgets.get(widgetId);
        if (!widget) return;
        
        const confirmed = confirm(`Are you sure you want to delete the "${widget.widget_name}" widget?`);
        if (!confirmed) return;
        
        try {
            await this.deleteWidget(widgetId);
            
            // Emit custom event
            document.dispatchEvent(new CustomEvent('pagebuilder:widget-deleted', {
                detail: { widgetId }
            }));
            
        } catch (error) {
            alert('Failed to delete widget. Please try again.');
        }
    }

    /**
     * Handle widget drop from sidebar
     */
    async handleWidgetDrop(sectionId, widgetId, dropPosition = {}) {
        try {
            console.log('🎯 Widget drop:', { sectionId, widgetId, dropPosition });
            
            const availableWidget = this.availableWidgets.get(parseInt(widgetId));
            if (!availableWidget) {
                throw new Error('Widget not found in available widgets');
            }
            
            // Create the widget in the section
            await this.createWidget(sectionId, widgetId, {
                grid_x: dropPosition.x || 0,
                grid_y: dropPosition.y || 0,
                grid_w: dropPosition.w || 6,
                grid_h: dropPosition.h || 3,
                settings: availableWidget.default_settings || {}
            });
            
            console.log('✅ Widget dropped and created successfully');
            
        } catch (error) {
            console.error('❌ Error handling widget drop:', error);
            alert('Failed to add widget. Please try again.');
        }
    }

    /**
     * Get widget by ID
     */
    getWidget(widgetId) {
        return this.widgets.get(widgetId);
    }

    /**
     * Get available widget by ID
     */
    getAvailableWidget(widgetId) {
        return this.availableWidgets.get(widgetId);
    }

    // =====================================================================
    // HYBRID: WIDGET MANAGEMENT WITH RENDERED CONTENT (Live Preview Integration)
    // =====================================================================

    /**
     * Add widget to section and refresh with rendered content
     * This integrates with the hybrid approach using actual widget rendering
     */
    async addWidgetWithRenderedContent(sectionId, widgetId, options = {}) {
        try {
            console.log('🎨 Adding widget with rendered content:', { sectionId, widgetId, options });
            
            // Create the widget using existing API
            const newWidget = await this.createWidget(sectionId, widgetId, options);
            
            if (newWidget) {
                // Refresh the entire section with new rendered content
                await this.refreshSectionWithRenderedContent(sectionId);
                
                console.log('✅ Widget added and section content refreshed');
                return newWidget;
            }
            
        } catch (error) {
            console.error('❌ Error adding widget with rendered content:', error);
            throw error;
        }
    }

    /**
     * Refresh section with rendered content after widget changes
     */
    async refreshSectionWithRenderedContent(sectionId) {
        try {
            console.log('🔄 Refreshing section with rendered content:', sectionId);
            
            // Use the section manager's refresh method
            if (this.sectionManager && this.sectionManager.refreshSectionContent) {
                const refreshedSection = await this.sectionManager.refreshSectionContent(sectionId);
                
                // Emit event to notify other components
                document.dispatchEvent(new CustomEvent('pagebuilder:section-content-refreshed', {
                    detail: { sectionId, section: refreshedSection }
                }));
                
                return refreshedSection;
            } else {
                console.warn('⚠️ Section manager not available for content refresh');
            }
            
        } catch (error) {
            console.error('❌ Error refreshing section with rendered content:', error);
            throw error;
        }
    }

    /**
     * Handle widget drop with hybrid rendering (like Live Preview)
     */
    async handleWidgetDropWithRendering(sectionId, widgetId, dropPosition = {}) {
        try {
            console.log('🎯 Widget drop with rendering:', { sectionId, widgetId, dropPosition });
            
            const availableWidget = this.availableWidgets.get(parseInt(widgetId));
            if (!availableWidget) {
                throw new Error('Widget not found in available widgets');
            }
            
            // Create widget with rendered content refresh
            await this.addWidgetWithRenderedContent(sectionId, widgetId, {
                grid_x: dropPosition.x || 0,
                grid_y: dropPosition.y || 0,
                grid_w: dropPosition.w || 6,
                grid_h: dropPosition.h || 3,
                settings: availableWidget.default_settings || {}
            });
            
            console.log('✅ Widget dropped and rendered successfully');
            
            // Show success notification
            this.showWidgetAddedNotification(availableWidget.name);
            
        } catch (error) {
            console.error('❌ Error handling widget drop with rendering:', error);
            alert('Failed to add widget. Please try again.');
        }
    }

    /**
     * Edit widget and refresh content (connects to Live Preview editing)
     */
    async editWidgetWithRendering(widgetId) {
        try {
            console.log('✏️ Edit widget with rendering:', widgetId);
            
            const widget = this.getWidget(widgetId);
            if (!widget) {
                throw new Error('Widget not found');
            }
            
            // Emit event for widget editor modal (similar to Live Preview)
            document.dispatchEvent(new CustomEvent('pagebuilder:edit-widget', {
                detail: { 
                    widgetId: widgetId, 
                    widget: widget,
                    onSave: (updatedWidget) => {
                        this.handleWidgetUpdated(updatedWidget);
                    }
                }
            }));
            
        } catch (error) {
            console.error('❌ Error editing widget:', error);
            alert('Failed to open widget editor. Please try again.');
        }
    }

    /**
     * Handle widget updated and refresh section content
     */
    async handleWidgetUpdated(updatedWidget) {
        try {
            console.log('🔄 Widget updated, refreshing content:', updatedWidget);
            
            // Update stored widget
            this.widgets.set(updatedWidget.id, updatedWidget);
            
            // Refresh section content
            const sectionId = updatedWidget.page_section_id || updatedWidget.section_id;
            if (sectionId) {
                await this.refreshSectionWithRenderedContent(sectionId);
            }
            
            // Show success notification
            this.showWidgetUpdatedNotification(updatedWidget.widget_name || 'Widget');
            
        } catch (error) {
            console.error('❌ Error handling widget update:', error);
        }
    }

    /**
     * Delete widget and refresh section content
     */
    async deleteWidgetWithRendering(widgetId) {
        try {
            console.log('🗑️ Deleting widget with rendering:', widgetId);
            
            const widget = this.getWidget(widgetId);
            if (!widget) {
                throw new Error('Widget not found');
            }
            
            if (!confirm(`Are you sure you want to delete "${widget.widget_name || 'this widget'}"?`)) {
                return;
            }
            
            // Delete widget using existing API
            await this.deleteWidget(widgetId);
            
            // Refresh section content
            const sectionId = widget.page_section_id || widget.section_id;
            if (sectionId) {
                await this.refreshSectionWithRenderedContent(sectionId);
            }
            
            console.log('✅ Widget deleted and section refreshed');
            
        } catch (error) {
            console.error('❌ Error deleting widget:', error);
            alert('Failed to delete widget. Please try again.');
        }
    }

    /**
     * Show widget added notification
     */
    showWidgetAddedNotification(widgetName) {
        // TODO: Implement toast notification system
        console.log(`✅ Widget "${widgetName}" added successfully`);
    }

    /**
     * Show widget updated notification
     */
    showWidgetUpdatedNotification(widgetName) {
        // TODO: Implement toast notification system
        console.log(`✅ Widget "${widgetName}" updated successfully`);
    }

    /**
     * Initialize drag and drop from widget library (enhanced for hybrid approach)
     */
    initializeDragAndDrop() {
        console.log('🎯 Initializing enhanced drag and drop for widget library...');
        
        // This method will be called by the widget library to setup drag events
        document.addEventListener('pagebuilder:widget-drag-start', (e) => {
            this.handleWidgetDragStart(e.detail);
        });
        
        document.addEventListener('pagebuilder:widget-drag-end', (e) => {
            this.handleWidgetDragEnd(e.detail);
        });
        
        console.log('✅ Enhanced widget drag and drop initialized');
    }

    /**
     * Handle widget drag start
     */
    handleWidgetDragStart(dragData) {
        console.log('🎯 Widget drag started:', dragData);
        // TODO: Show drop zones, visual feedback
    }

    /**
     * Handle widget drag end
     */
    handleWidgetDragEnd(dragData) {
        console.log('🎯 Widget drag ended:', dragData);
        
        if (dragData.dropped && dragData.sectionId) {
            // Use enhanced drop handler with rendering
            this.handleWidgetDropWithRendering(
                dragData.sectionId, 
                dragData.widgetId, 
                dragData.position
            );
        }
    }
}

// Export for global use
window.WidgetManager = WidgetManager;

console.log('📦 Widget Manager module loaded');