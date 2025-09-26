/**
 * PageBuilder IframeCommunicator - Handles iframe-side communication for Page Builder
 * Based on live-designer pattern but adapted for Page Builder GridStack functionality
 */
class PageBuilderIframeCommunicator {
    constructor() {
        this.parentWindow = window.parent;
        this.messageQueue = [];
        this.isReady = false;
        this.allowedOrigin = window.location.origin;
        this.messageHandlers = new Map();
        this.setupGridStackHandlers();

        this.setupMessageHandling();
        this.notifyReady();

        console.log('🔗 PageBuilder Iframe Communicator initialized');
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
            if (!event.data || event.data.source !== 'pagebuilder-parent') {
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
            gridStackAvailable: !!window.GridStack,
            pageGridExists: !!document.getElementById('pageGrid')
        });

        console.log('📡 Notified parent that iframe is ready');
    }

    /**
     * Send message to parent window
     */
    sendMessage(type, data = {}) {
        const message = {
            type: type,
            data: data,
            source: 'pagebuilder-iframe',
            timestamp: Date.now()
        };

        if (!this.isReady && type !== 'iframe:ready') {
            this.messageQueue.push(message);
            return;
        }

        if (this.parentWindow && this.parentWindow !== window) {
            this.parentWindow.postMessage(message, this.allowedOrigin);
            console.log(`📤 Sent message: ${type}`, message);
        }
    }

    /**
     * Handle incoming messages from parent
     */
    handleMessage(messageData) {
        const { type, data } = messageData;
        console.log(`📥 Received message: ${type}`, messageData);

        // Handle ready confirmation
        if (type === 'parent:ready') {
            this.isReady = true;
            this.flushMessageQueue();
            return;
        }

        // Call registered handler if exists
        if (this.messageHandlers.has(type)) {
            const handler = this.messageHandlers.get(type);
            try {
                handler(data, messageData);
            } catch (error) {
                console.error(`❌ Error handling message ${type}:`, error);
            }
        }
    }

    /**
     * Register message handler
     */
    registerHandler(type, handler) {
        this.messageHandlers.set(type, handler);
        console.log(`📋 Registered handler for: ${type}`);
    }

    /**
     * Flush queued messages when parent is ready
     */
    flushMessageQueue() {
        this.messageQueue.forEach(message => {
            this.parentWindow.postMessage(message, this.allowedOrigin);
        });
        this.messageQueue = [];
        console.log('📨 Flushed message queue');
    }

    // === GridStack Specific Messages ===

    /**
     * Notify parent that GridStack is ready
     */
    notifyGridStackReady(gridData) {
        this.sendMessage('gridstack:ready', gridData);
    }

    /**
     * Notify parent about section reorder
     */
    notifySectionReordered(sections) {
        this.sendMessage('section:reordered', {
            sections: sections,
            timestamp: Date.now()
        });
    }

    /**
     * Notify parent about section reorder error
     */
    notifySectionReorderError(error) {
        this.sendMessage('section:reorder-error', {
            error: error.message || error,
            timestamp: Date.now()
        });
    }

    /**
     * Notify parent about component selection
     */
    notifyComponentSelected(componentData) {
        this.sendMessage('component:selected', {
            type: componentData.type,
            id: componentData.id,
            element: componentData.element?.tagName || null,
            timestamp: Date.now()
        });
    }

    /**
     * Notify parent about toolbar action
     */
    notifyToolbarAction(action, componentData) {
        // Create a serializable copy of component data (remove DOM elements)
        const serializableComponentData = {
            type: componentData.type,
            id: componentData.id,
            name: componentData.name,
            pageId: componentData.pageId,
            sectionId: componentData.sectionId,
            widgetId: componentData.widgetId,
            action: componentData.action
        };

        this.sendMessage('toolbar:action', {
            action: action,
            component: serializableComponentData,
            timestamp: Date.now()
        });
    }

    /**
 * Setup GridStack specific message handlers
 */
setupGridStackHandlers() {
    this.registerHandler('gridstack:refresh', (data) => {
        if (window.pageBuilderGridStack) {
            window.pageBuilderGridStack.refreshAllGrids();
        }
    });
    
    this.registerHandler('gridstack:add-widget', (data) => {
        // Handle widget addition from parent
        this.handleAddWidgetFromParent(data);
    });
}

/**
 * Handle widget addition from parent
 */
handleAddWidgetFromParent(data) {
    if (window.pageBuilderGridStack && data.sectionId) {
        const sectionGrid = window.pageBuilderGridStack.sectionGrids.get(data.sectionId.toString());
        if (sectionGrid) {
            // Implementation for adding widget from parent
        }
    }
}
}

// Global instance
window.pageBuilderIframeCommunicator = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.pageBuilderIframeCommunicator = new PageBuilderIframeCommunicator();
    console.log('✅ PageBuilder Iframe Communicator ready');
});

console.log('📦 PageBuilder Iframe Communicator module loaded');