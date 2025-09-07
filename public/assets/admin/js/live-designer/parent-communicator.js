/**
 * ParentCommunicator - Handles iframe messages in parent window
 * 
 * Features:
 * - Receives messages from iframe selection system
 * - Coordinates with existing parent-side managers
 * - Handles toolbar actions that need parent window interaction
 * - Bridges iframe selection system with parent functionality
 */
class ParentCommunicator {
    constructor(livePreview) {
        this.livePreview = livePreview;
        this.iframe = null;
        this.iframeOrigin = window.location.origin;
        this.isIframeReady = false;
        this.messageQueue = [];
        
        // Current selection state from iframe
        this.selectedComponent = null;
        this.selectionMode = 'select';
        
        // Message handlers
        this.messageHandlers = new Map();
        
        this.setupMessageHandling();
        console.log('🔗 Parent communicator initialized');
    }
    
    /**
     * Set the iframe reference
     * @param {HTMLIFrameElement} iframe - Preview iframe element
     */
    setIframe(iframe) {
        this.iframe = iframe;
        
        // Listen for iframe load to establish communication
        iframe.addEventListener('load', () => {
            console.log('🔗 Iframe loaded, waiting for selection system...');
        });
    }
    
    /**
     * Setup message event listener for iframe communication
     */
    setupMessageHandling() {
        window.addEventListener('message', (event) => {
            // Security: Verify origin
            if (event.origin !== this.iframeOrigin) {
                console.warn('🚫 Rejected message from unauthorized origin:', event.origin);
                return;
            }
            
            // Validate message structure
            if (!event.data || event.data.source !== 'live-preview-iframe') {
                return;
            }
            
            this.handleMessage(event.data);
        });
    }
    
    /**
     * Handle incoming messages from iframe
     * @param {object} message - Received message
     */
    handleMessage(message) {
        console.log(`🔗 Received from iframe: ${message.type}`, message.data);
        
        // Check for registered custom handlers
        if (this.messageHandlers.has(message.type)) {
            const handler = this.messageHandlers.get(message.type);
            try {
                handler(message.data);
            } catch (error) {
                console.error(`❌ Error in message handler for ${message.type}:`, error);
            }
            return;
        }
        
        // Handle built-in message types
        switch(message.type) {
            case 'iframe:ready':
                this.handleIframeReady(message.data);
                break;
                
            case 'selection-system:ready':
                this.handleSelectionSystemReady(message.data);
                break;
                
            case 'component:selected':
                this.handleComponentSelected(message.data);
                break;
                
            case 'component:deselected':
                this.handleComponentDeselected(message.data);
                break;
                
            case 'toolbar:action':
                this.handleToolbarAction(message.data);
                break;
                
            case 'component:reorder':
                this.handleComponentReorder(message.data);
                break;
                
            case 'component:content-extracted':
                this.handleContentExtracted(message.data);
                break;
                
            case 'mode:changed':
                this.handleModeChanged(message.data);
                break;
                
            case 'sortable:start':
            case 'sortable:end':
                this.handleSortableEvent(message.type, message.data);
                break;
                
            default:
                console.warn(`⚠️ Unknown message type from iframe: ${message.type}`);
        }
    }
    
    /**
     * Handle iframe ready notification
     * @param {object} data - Iframe ready data
     */
    handleIframeReady(data) {
        this.isIframeReady = true;
        console.log('🔗 Iframe communication established');
        
        // Process any queued messages
        this.processMessageQueue();
        
        // Notify LivePreview that iframe is ready
        if (this.livePreview && this.livePreview.onIframeReady) {
            this.livePreview.onIframeReady(data);
        }
    }
    
    /**
     * Handle selection system ready notification
     * @param {object} data - Selection system data
     */
    handleSelectionSystemReady(data) {
        console.log('🎯 Selection system ready in iframe:', data);
        
        // Update parent UI to reflect selection system availability
        this.updateParentUI('selection-ready', data);
        
        // Sync current device/zoom settings with iframe
        this.syncDeviceSettings();
    }
    
    /**
     * Handle component selection from iframe
     * @param {object} data - Selected component data
     */
    handleComponentSelected(data) {
        this.selectedComponent = data;
        
        console.log(`🎯 Component selected: ${data.type} ${data.id}`);
        
        // Update parent UI to show selection info
        this.updateParentUI('component-selected', data);
        
        // Update widget editor if it's a widget
        if (data.type === 'widget' && this.livePreview.widgetFormManager) {
            this.livePreview.widgetFormManager.showWidgetInfo(data);
        }
        
        // Update sidebar selection indicator
        this.updateSidebarSelection(data);
    }
    
    /**
     * Handle component deselection from iframe
     * @param {object} data - Deselected component data
     */
    handleComponentDeselected(data) {
        const previousComponent = this.selectedComponent;
        this.selectedComponent = null;
        
        console.log('🎯 Component deselected');
        
        // Update parent UI
        this.updateParentUI('component-deselected', data);
        
        // Clear widget editor if needed
        if (this.livePreview.widgetFormManager) {
            this.livePreview.widgetFormManager.clearSelection();
        }
        
        // Clear sidebar selection
        this.clearSidebarSelection();
    }
    
    /**
     * Handle toolbar action from iframe
     * @param {object} data - Action data
     */
    handleToolbarAction(data) {
        console.log(`🔧 Toolbar action: ${data.action} on ${data.component.type} ${data.component.id}`);
        
        // Route action to appropriate handler
        switch(data.action) {
            case 'edit-page':
                this.handleEditPage(data.component);
                break;
                
            case 'edit-section':
                this.handleEditSection(data.component);
                break;
                
            case 'edit-widget':
                this.handleEditWidget(data.component);
                break;
                
            case 'page-settings':
            case 'section-settings':
            case 'widget-settings':
                this.handleComponentSettings(data.component);
                break;
                
            case 'duplicate-page':
            case 'duplicate-section':
            case 'duplicate-widget':
                this.handleDuplicateComponent(data.component);
                break;
                
            case 'delete-section':
            case 'delete-widget':
                this.handleDeleteComponent(data.component);
                break;
                
            case 'page-seo':
                this.handlePageSEO(data.component);
                break;
                
            default:
                console.warn(`⚠️ Unknown toolbar action: ${data.action}`);
        }
    }
    
    /**
     * Handle component reorder from iframe
     * @param {object} data - Reorder data
     */
    handleComponentReorder(data) {
        console.log('📋 Component reordered:', data);
        
        // Use existing update manager to save the reorder
        if (this.livePreview.updateManager) {
            this.livePreview.updateManager.saveComponentOrder(data);
        }
    }
    
    /**
     * Handle content extraction from iframe
     * @param {object} data - Extracted content data
     */
    handleContentExtracted(data) {
        console.log('📤 Content extracted:', data);
        
        // Could open a modal to show extracted content
        // Or save it to templates library
        this.showContentExtractionModal(data);
    }
    
    /**
     * Handle mode change from iframe
     * @param {object} data - Mode change data
     */
    handleModeChanged(data) {
        this.selectionMode = data.currentMode;
        console.log(`🔄 Mode changed to: ${data.currentMode}`);
        
        // Update parent UI to reflect mode change
        this.updateParentUI('mode-changed', data);
    }
    
    /**
     * Handle sortable events from iframe
     * @param {string} eventType - Event type (sortable:start, sortable:end)
     * @param {object} data - Event data
     */
    handleSortableEvent(eventType, data) {
        console.log(`📋 Sortable event: ${eventType}`, data);
        
        // Update parent UI during sorting
        if (eventType === 'sortable:start') {
            this.updateParentUI('sorting-started', data);
        } else if (eventType === 'sortable:end') {
            this.updateParentUI('sorting-ended', data);
        }
    }
    
    /**
     * Send message to iframe
     * @param {string} type - Message type
     * @param {object} data - Message data
     */
    sendToIframe(type, data = {}) {
        if (!this.iframe || !this.isIframeReady) {
            // Queue message if iframe not ready
            this.messageQueue.push({ type, data });
            return;
        }
        
        const message = {
            type: type,
            data: data,
            timestamp: Date.now(),
            source: 'live-designer-parent'
        };
        
        try {
            this.iframe.contentWindow.postMessage(message, this.iframeOrigin);
            console.log(`🔗 Sent to iframe: ${type}`, data);
        } catch (error) {
            console.error('❌ Failed to send message to iframe:', error);
        }
    }
    
    /**
     * Process queued messages after iframe becomes ready
     */
    processMessageQueue() {
        this.messageQueue.forEach(message => {
            this.sendToIframe(message.type, message.data);
        });
        this.messageQueue = [];
    }
    
    /**
     * Sync device settings with iframe
     */
    syncDeviceSettings() {
        if (this.livePreview.devicePreview) {
            const currentDevice = this.livePreview.devicePreview.getCurrentDevice();
            const zoomLevel = this.livePreview.devicePreview.getZoomLevel();
            
            this.sendToIframe('zoom:changed', {
                level: zoomLevel,
                inverse: (1 / zoomLevel).toFixed(2),
                device: currentDevice
            });
        }
    }
    
    /**
     * Handle edit page action
     * @param {object} component - Page component
     */
    handleEditPage(component) {
        // Redirect to page edit form
        window.location.href = `/admin/pages/${component.id}/edit`;
    }
    
    /**
     * Handle edit section action
     * @param {object} component - Section component
     */
    handleEditSection(component) {
        // Open section edit modal or redirect
        if (this.livePreview.sectionManager) {
            this.livePreview.sectionManager.editSection(component.id);
        } else {
            window.location.href = `/admin/sections/${component.id}/edit`;
        }
    }
    
    /**
     * Handle edit widget action
     * @param {object} component - Widget component
     */
    handleEditWidget(component) {
        // Use existing widget form manager
        if (this.livePreview.widgetFormManager) {
            this.livePreview.widgetFormManager.editWidget(component.id);
        }
    }
    
    /**
     * Handle component settings action
     * @param {object} component - Component data
     */
    handleComponentSettings(component) {
        console.log(`⚙️ Opening settings for ${component.type} ${component.id}`);
        
        // Open appropriate settings modal/form
        switch(component.type) {
            case 'page':
                this.openPageSettings(component);
                break;
            case 'section':
                this.openSectionSettings(component);
                break;
            case 'widget':
                this.openWidgetSettings(component);
                break;
        }
    }
    
    /**
     * Handle duplicate component action
     * @param {object} component - Component to duplicate
     */
    handleDuplicateComponent(component) {
        console.log(`📋 Duplicating ${component.type} ${component.id}`);
        
        if (this.livePreview.updateManager) {
            this.livePreview.updateManager.duplicateComponent(component);
        }
    }
    
    /**
     * Handle delete component action
     * @param {object} component - Component to delete
     */
    handleDeleteComponent(component) {
        const confirmed = confirm(`Are you sure you want to delete this ${component.type}?`);
        
        if (confirmed && this.livePreview.updateManager) {
            this.livePreview.updateManager.deleteComponent(component);
        }
    }
    
    /**
     * Handle page SEO action
     * @param {object} component - Page component
     */
    handlePageSEO(component) {
        window.location.href = `/admin/pages/${component.id}/seo`;
    }
    
    /**
     * Update parent UI based on iframe events
     * @param {string} event - Event type
     * @param {object} data - Event data
     */
    updateParentUI(event, data) {
        // Update selection indicator in parent UI
        const selectionIndicator = document.getElementById('selection-indicator');
        if (selectionIndicator) {
            switch(event) {
                case 'component-selected':
                    selectionIndicator.textContent = `Selected: ${data.type} ${data.name}`;
                    selectionIndicator.className = `alert alert-info`;
                    break;
                case 'component-deselected':
                    selectionIndicator.textContent = 'No selection';
                    selectionIndicator.className = `alert alert-secondary`;
                    break;
                case 'mode-changed':
                    const modeIndicator = document.getElementById('mode-indicator');
                    if (modeIndicator) {
                        modeIndicator.textContent = `Mode: ${data.currentMode}`;
                    }
                    break;
            }
        }
    }
    
    /**
     * Update sidebar selection indicator
     * @param {object} component - Selected component
     */
    updateSidebarSelection(component) {
        // Remove existing selection indicators
        document.querySelectorAll('.sidebar-selected').forEach(el => {
            el.classList.remove('sidebar-selected');
        });
        
        // Add selection indicator to relevant sidebar item
        const sidebarItem = document.querySelector(`[data-${component.type}-id="${component.id}"]`);
        if (sidebarItem) {
            sidebarItem.classList.add('sidebar-selected');
        }
    }
    
    /**
     * Clear sidebar selection indicators
     */
    clearSidebarSelection() {
        document.querySelectorAll('.sidebar-selected').forEach(el => {
            el.classList.remove('sidebar-selected');
        });
    }
    
    /**
     * Show content extraction modal
     * @param {object} data - Extracted content data
     */
    showContentExtractionModal(data) {
        // Create and show modal with extracted content
        // This would integrate with existing modal system
        console.log('📤 Would show content extraction modal:', data);
    }
    
    /**
     * Open page settings modal
     * @param {object} component - Page component
     */
    openPageSettings(component) {
        // Implementation depends on existing page settings system
        console.log('⚙️ Would open page settings for:', component);
    }
    
    /**
     * Open section settings modal
     * @param {object} component - Section component
     */
    openSectionSettings(component) {
        // Implementation depends on existing section settings system
        console.log('⚙️ Would open section settings for:', component);
    }
    
    /**
     * Open widget settings modal
     * @param {object} component - Widget component
     */
    openWidgetSettings(component) {
        if (this.livePreview.widgetFormManager) {
            this.livePreview.widgetFormManager.showSettings(component.id);
        }
    }
    
    /**
     * Register custom message handler
     * @param {string} messageType - Message type to handle
     * @param {function} handler - Handler function
     */
    registerMessageHandler(messageType, handler) {
        this.messageHandlers.set(messageType, handler);
        console.log(`🔗 Registered handler for message type: ${messageType}`);
    }
    
    /**
     * Unregister message handler
     * @param {string} messageType - Message type to unregister
     */
    unregisterMessageHandler(messageType) {
        this.messageHandlers.delete(messageType);
        console.log(`🗑️ Unregistered handler for message type: ${messageType}`);
    }
    
    /**
     * Get current selection state
     * @returns {object|null} Current selected component or null
     */
    getSelectedComponent() {
        return this.selectedComponent;
    }
    
    /**
     * Get current selection mode
     * @returns {string} Current mode
     */
    getSelectionMode() {
        return this.selectionMode;
    }
    
    /**
     * Enable sort mode in iframe
     * @param {string} componentType - Type of components to sort
     */
    enableSortMode(componentType = 'section') {
        this.sendToIframe('mode:change', {
            mode: 'sort',
            componentType: componentType
        });
    }
    
    /**
     * Disable sort mode in iframe
     */
    disableSortMode() {
        this.sendToIframe('mode:change', {
            mode: 'select'
        });
    }
    
    /**
     * Select component programmatically in iframe
     * @param {string} componentType - Component type
     * @param {string} componentId - Component ID
     */
    selectComponent(componentType, componentId) {
        this.sendToIframe('component:select', {
            type: componentType,
            id: componentId
        });
    }
    
    /**
     * Deselect current component in iframe
     */
    deselectComponent() {
        this.sendToIframe('component:deselect');
    }
    
    /**
     * Update zoom level in iframe
     * @param {number} zoomLevel - New zoom level
     */
    updateZoom(zoomLevel) {
        this.sendToIframe('zoom:changed', {
            level: zoomLevel,
            inverse: (1 / zoomLevel).toFixed(2)
        });
    }
    
    /**
     * Cleanup method for destroying the communicator
     */
    destroy() {
        this.messageHandlers.clear();
        this.messageQueue = [];
        this.selectedComponent = null;
        this.isIframeReady = false;
        console.log('🧹 Parent communicator destroyed');
    }
}

// Export for use in other modules
window.ParentCommunicator = ParentCommunicator;
