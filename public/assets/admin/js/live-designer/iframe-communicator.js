/**
 * IframeCommunicator - Handles bidirectional messaging between iframe and parent window
 * 
 * Features:
 * - Secure origin validation
 * - Message queuing for iframe readiness
 * - Event handling for zoom changes and selections
 * - Bidirectional communication protocol
 */
class IframeCommunicator {
    constructor() {
        this.parentWindow = window.parent;
        this.messageQueue = [];
        this.isReady = false;
        this.allowedOrigin = window.location.origin;
        this.messageHandlers = new Map();
        
        this.setupMessageHandling();
        this.notifyReady();
    }
    
    /**
     * Setup message event listener for parent-iframe communication
     */
    setupMessageHandling() {
        window.addEventListener('message', (event) => {
            // Security: Verify origin
            if (event.origin !== this.allowedOrigin) {
                console.warn('🚫 Rejected message from unauthorized origin:', event.origin);
                return;
            }
            
            // Validate message structure
            if (!event.data || event.data.source !== 'live-designer-parent') {
                return;
            }
            
            this.handleMessage(event.data);
        });
    }
    
    /**
     * Notify parent window that iframe is ready for communication
     */
    notifyReady() {
        this.sendMessage('iframe:ready', {
            url: window.location.href,
            timestamp: Date.now(),
            userAgent: navigator.userAgent
        });
        
        this.isReady = true;
        console.log('📡 Iframe communicator ready');
        
        // Process any queued messages
        this.processMessageQueue();
    }
    
    /**
     * Send message to parent window
     * @param {string} type - Message type
     * @param {object} data - Message data
     */
    sendMessage(type, data = {}) {
        const message = {
            type: type,
            data: data,
            timestamp: Date.now(),
            source: 'live-preview-iframe'
        };
        
        if (!this.isReady && type !== 'iframe:ready') {
            this.messageQueue.push(message);
            return;
        }
        
        try {
            this.parentWindow.postMessage(message, this.allowedOrigin);
            console.log(`📤 Sent message: ${type}`, data);
        } catch (error) {
            console.error('❌ Failed to send message to parent:', error);
        }
    }
    
    /**
     * Process queued messages after iframe becomes ready
     */
    processMessageQueue() {
        this.messageQueue.forEach(message => {
            this.sendMessage(message.type, message.data);
        });
        this.messageQueue = [];
    }
    
    /**
     * Handle incoming messages from parent window
     * @param {object} message - Received message
     */
    handleMessage(message) {
        console.log(`📥 Received message: ${message.type}`, message.data);
        
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
            case 'zoom:changed':
                this.handleZoomChange(message.data);
                break;
                
            case 'component:select':
                this.handleComponentSelect(message.data);
                break;
                
            case 'component:deselect':
                this.handleComponentDeselect();
                break;
                
            case 'action:execute':
                this.handleActionExecute(message.data);
                break;
                
            case 'mode:change':
                this.handleModeChange(message.data);
                break;
                
            case 'enable-sort-mode':
                this.handleEnableSortMode(message.data);
                break;
                
            case 'disable-sort-mode':
                this.handleDisableSortMode(message.data);
                break;
                
            default:
                console.warn(`⚠️ Unknown message type: ${message.type}`);
        }
    }
    
    /**
     * Handle zoom level changes from parent
     * @param {object} zoomData - Zoom level and inverse scale
     */
    handleZoomChange(zoomData) {
        document.body.setAttribute('data-zoom', zoomData.level);
        document.body.style.setProperty('--selection-zoom-inverse', zoomData.inverse);
        
        // Notify selection manager if available
        if (window.selectionManager) {
            window.selectionManager.handleZoomChange(zoomData);
        }
        
        console.log(`🔍 Zoom changed to ${zoomData.level}x (inverse: ${zoomData.inverse})`);
    }
    
    /**
     * Handle programmatic component selection from parent
     * @param {object} componentData - Component to select
     */
    handleComponentSelect(componentData) {
        if (!window.selectionManager) {
            console.warn('⚠️ Selection manager not available for component selection');
            return;
        }
        
        const element = this.findComponentElement(componentData);
        if (element) {
            const component = window.selectionManager.detector.createComponentObject(
                componentData.type, 
                element
            );
            window.selectionManager.select(component);
        } else {
            console.warn(`⚠️ Component not found: ${componentData.type} ${componentData.id}`);
        }
    }
    
    /**
     * Handle programmatic component deselection from parent
     */
    handleComponentDeselect() {
        if (window.selectionManager) {
            window.selectionManager.deselect();
        }
    }
    
    /**
     * Handle action execution requests from parent
     * @param {object} actionData - Action to execute
     */
    handleActionExecute(actionData) {
        if (!window.selectionManager) {
            console.warn('⚠️ Selection manager not available for action execution');
            return;
        }
        
        // Execute action on currently selected component
        if (window.selectionManager.selectedComponent) {
            window.selectionManager.toolbar.executeAction(
                actionData.action, 
                window.selectionManager.selectedComponent
            );
        }
    }
    
    /**
     * Handle mode changes (select, sort, edit)
     * @param {object} modeData - New mode information
     */
    handleModeChange(modeData) {
        if (!window.selectionManager) {
            console.warn('⚠️ Selection manager not available for mode change');
            return;
        }
        
        switch(modeData.mode) {
            case 'sort':
                window.selectionManager.enableSortMode(modeData.componentType);
                break;
            case 'select':
                window.selectionManager.disableSortMode();
                break;
            case 'edit':
                // Handle edit mode if implemented
                break;
        }
    }
    
    /**
     * Handle enable sort mode request from parent
     * @param {object} sortData - Sort mode configuration
     */
    handleEnableSortMode(sortData) {
        console.log(`🔄 Enabling ${sortData.sortableType} sorting mode for container ${sortData.containerId}`);
        
        // Check if SortableManager is available
        if (!window.sortableManager) {
            console.error('❌ SortableManager not available');
            this.sendSortModeResponse(sortData, false, 'SortableManager not found');
            return;
        }
        
        try {
            // Enable sorting with the new unified approach
            const success = window.sortableManager.enableSorting({
                sortableType: sortData.sortableType,
                containerId: sortData.containerId,
                itemType: sortData.itemType
            });
            
            if (success) {
                // Add sort mode class to body
                document.body.classList.add('mode-sort');
                document.body.classList.remove('mode-select');
                
                // Deselect any currently selected components
                if (window.selectionManager) {
                    window.selectionManager.deselect();
                }
                
                console.log(`✅ ${sortData.sortableType} sorting enabled successfully`);
                this.sendSortModeResponse(sortData, true, 'Sort mode enabled');
            } else {
                console.error(`❌ Failed to enable ${sortData.sortableType} sorting`);
                this.sendSortModeResponse(sortData, false, 'Failed to enable sorting');
            }
        } catch (error) {
            console.error('❌ Error enabling sort mode:', error);
            this.sendSortModeResponse(sortData, false, error.message);
        }
    }
    
    /**
     * Handle disable sort mode request from parent
     * @param {object} sortData - Sort mode configuration
     */
    handleDisableSortMode(sortData) {
        console.log(`🔄 Disabling ${sortData.componentType} sorting mode`);
        
        try {
            // Disable sorting if SortableManager is available
            if (window.sortableManager) {
                window.sortableManager.disable(sortData.componentType);
            }
            
            // Remove sort mode class from body
            document.body.classList.remove('mode-sort');
            document.body.classList.add('mode-select');
            
            console.log(`✅ ${sortData.componentType} sorting disabled successfully`);
            this.sendSortModeResponse(sortData, true, 'Sort mode disabled');
        } catch (error) {
            console.error('❌ Error disabling sort mode:', error);
            this.sendSortModeResponse(sortData, false, error.message);
        }
    }
    
    /**
     * Send sort mode operation response to parent
     * @param {object} sortData - Original sort data
     * @param {boolean} success - Whether operation succeeded
     * @param {string} message - Response message
     */
    sendSortModeResponse(sortData, success, message) {
        this.sendMessage('sort-mode-response', {
            componentType: sortData.componentType,
            success: success,
            message: message,
            timestamp: Date.now()
        });
        
        // Execute callback if provided
        if (sortData.callback && typeof sortData.callback === 'function') {
            sortData.callback(success);
        }
    }
    
    /**
     * Find component element in DOM by type and ID
     * @param {object} componentData - Component data with type and id
     * @returns {Element|null} Found element or null
     */
    findComponentElement(componentData) {
        const selectors = {
            page: `[data-preview-page="${componentData.id}"]`,
            section: `[data-section-id="${componentData.id}"]`,
            widget: `[data-page-section-widget-id="${componentData.id}"]`
        };
        
        const selector = selectors[componentData.type];
        return selector ? document.querySelector(selector) : null;
    }
    
    /**
     * Register custom message handler
     * @param {string} messageType - Message type to handle
     * @param {function} handler - Handler function
     */
    registerMessageHandler(messageType, handler) {
        this.messageHandlers.set(messageType, handler);
        console.log(`📋 Registered handler for message type: ${messageType}`);
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
     * Send component selection event to parent
     * @param {object} componentData - Selected component data
     */
    notifyComponentSelected(componentData) {
        this.sendMessage('component:selected', {
            type: componentData.type,
            id: componentData.id,
            name: componentData.name,
            metadata: componentData.metadata,
            timestamp: Date.now()
        });
    }
    
    /**
     * Send component deselection event to parent
     * @param {object} componentData - Deselected component data
     */
    notifyComponentDeselected(componentData) {
        this.sendMessage('component:deselected', {
            type: componentData.type,
            id: componentData.id,
            timestamp: Date.now()
        });
    }
    
    /**
     * Send toolbar action event to parent
     * @param {string} action - Action performed
     * @param {object} componentData - Component the action was performed on
     */
    notifyToolbarAction(action, componentData) {
        this.sendMessage('toolbar:action', {
            action: action,
            component: {
                type: componentData.type,
                id: componentData.id,
                name: componentData.name,
                metadata: componentData.metadata
            },
            timestamp: Date.now()
        });
    }
    
    /**
     * Send component reorder event to parent
     * @param {object} reorderData - Reorder event data
     */
    notifyComponentReorder(reorderData) {
        this.sendMessage('component:reorder', {
            ...reorderData,
            timestamp: Date.now()
        });
    }
    
    /**
     * Send content extraction event to parent
     * @param {object} contentData - Extracted content data
     */
    notifyContentExtracted(contentData) {
        this.sendMessage('component:content-extracted', {
            component: contentData,
            timestamp: Date.now()
        });
    }
    
    /**
     * Send inline edit event to parent
     * @param {object} editData - Inline edit data
     */
    notifyInlineEdit(editData) {
        this.sendMessage('component:inline-edited', {
            ...editData,
            timestamp: Date.now()
        });
    }
    
    /**
     * Send style editor request to parent
     * @param {object} styleData - Component and style data
     */
    notifyStyleEditorRequest(styleData) {
        this.sendMessage('component:open-style-editor', {
            ...styleData,
            timestamp: Date.now()
        });
    }
    
    /**
     * Cleanup method for destroying the communicator
     */
    destroy() {
        this.messageHandlers.clear();
        this.messageQueue = [];
        this.isReady = false;
        console.log('🧹 Iframe communicator destroyed');
    }
}

// Export for use in other modules
window.IframeCommunicator = IframeCommunicator;
