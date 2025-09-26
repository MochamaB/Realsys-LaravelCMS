/**
 * Page Builder Message Router
 * 
 * Central message router for handling communication between iframe and parent
 */
class PageBuilderMessageRouter {
    constructor() {
        this.actionHandlers = new Map();
        this.messageHistory = [];
        this.setupMessageListener();
        
        console.log('🎯 Page Builder Message Router initialized');
    }

    /**
     * Register a handler for specific action type
     */
    registerHandler(actionType, handler) {
        console.log(`📋 Registering handler for: ${actionType}`);
        this.actionHandlers.set(actionType, handler);
    }

    /**
     * Setup window message listener
     */
    setupMessageListener() {
        window.addEventListener('message', (event) => {
            // Verify message is from our iframe
            if (event.data && event.data.source === 'pagebuilder-iframe') {
                this.handleMessage(event);
            }
        });

        console.log('👂 Message listener setup complete');
    }

    /**
     * Handle incoming message from iframe
     */
    handleMessage(event) {
        const { type, data, timestamp } = event.data;
        
        console.log(`📥 Received message: ${type}`, event.data);
        
        // Store message in history for debugging
        this.messageHistory.push({
            type,
            data,
            timestamp,
            handled: false,
            handledAt: null
        });

        // Find and execute handler
        const handler = this.actionHandlers.get(type);
        if (handler) {
            try {
                handler(data, event);
                console.log(`✅ Action handled: ${type}`);
                
                // Mark as handled in history
                const historyItem = this.messageHistory[this.messageHistory.length - 1];
                historyItem.handled = true;
                historyItem.handledAt = Date.now();
            } catch (error) {
                console.error(`❌ Error handling action ${type}:`, error);
            }
        } else {
            console.warn(`⚠️ No handler registered for action: ${type}`);
        }
    }

    /**
     * Get message history for debugging
     */
    getMessageHistory() {
        return this.messageHistory;
    }

    /**
     * Clear message history
     */
    clearMessageHistory() {
        this.messageHistory = [];
    }
}

/**
 * Page Builder Main Script
 * 
 * Handles essential iframe loading states and coordinates message routing
 */
class PageBuilderMain {
    constructor() {
        this.iframe = null;
        this.loader = null;
        
        console.log('📦 Page Builder Main initialized');
    }

    /**
     * Initialize iframe preview functionality
     */
    initIframePreview() {
        this.iframe = document.getElementById('pageBuilderPreviewIframe');
        this.loader = document.getElementById('pageBuilderLoader');
        
        if (!this.iframe) {
            console.warn('⚠️ Page Builder iframe not found');
            return;
        }

        this.setupIframeEvents();
        console.log('🖼️ Page Builder iframe preview initialized');
    }

    /**
     * Setup iframe event listeners
     */
    setupIframeEvents() {
        // Show loader while iframe loads
        this.showLoader('Loading Page Builder Preview...');
        
        // Handle successful iframe load
        this.iframe.addEventListener('load', () => {
            console.log('✅ Page Builder iframe loaded successfully');
            this.hideLoader();
        });
        
        // Handle iframe load errors
        this.iframe.addEventListener('error', () => {
            console.error('❌ Page Builder iframe failed to load');
            this.showLoader('Failed to load preview - Check console for details');
        });
    }

    /**
     * Show the simple loader
     */
    showLoader(message = 'Loading...') {
        if (this.loader) {
            const messageEl = this.loader.querySelector('.loader-message');
            if (messageEl) {
                messageEl.textContent = message;
            }
            this.loader.style.display = 'flex';
        } else {
            console.warn('⚠️ Loader element not found - cannot show loader message:', message);
        }
    }

    /**
     * Hide the simple loader
     */
    hideLoader() {
        if (this.loader) {
            this.loader.style.display = 'none';
        }
    }

    /**
     * Refresh iframe preview
     */
    refreshPreview() {
        if (this.iframe) {
            console.log('🔄 Refreshing Page Builder preview...');
            this.showLoader('Refreshing preview...');
            this.iframe.src = this.iframe.src; // Force reload
        }
    }

    /**
     * Get iframe document (for future component targeting)
     */
    getIframeDocument() {
        if (this.iframe && this.iframe.contentDocument) {
            return this.iframe.contentDocument;
        }
        return null;
    }

    /**
     * Check if iframe is loaded and ready
     */
    isIframeReady() {
        return this.iframe && this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete';
    }
}

// Export for global access
window.PageBuilderMain = PageBuilderMain;

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Page Builder systems initializing...');
    
    // Initialize message router first
    const messageRouter = new PageBuilderMessageRouter();
    window.pageBuilderMessageRouter = messageRouter;

    // Initialize parent communicator first
    if (window.PageBuilderParentCommunicator) {
        window.pageBuilderParentCommunicator = new PageBuilderParentCommunicator();

        const iframe = document.getElementById('pageBuilderPreviewIframe');
        if (iframe) {
            window.pageBuilderParentCommunicator.setIframe(iframe);
            console.log('🔗 Parent communicator connected to iframe');
        }

        console.log('✅ Page Builder parent communicator initialized');
    } else {
        console.warn('⚠️ PageBuilderParentCommunicator class not found');
    }

    // Register toolbar action handler for iframe:ready message
    messageRouter.registerHandler('iframe:ready', (data) => {
        console.log('✅ Iframe is ready:', data);
        // Iframe is now ready to receive messages
    });
    
    // Initialize main iframe functionality
    if (window.PageBuilderMain) {
        const pageBuilderMain = new PageBuilderMain();
        pageBuilderMain.initIframePreview();
        window.pageBuilderMain = pageBuilderMain;
    }
    
    // Initialize device preview system
    if (window.PageBuilderDevicePreview) {
        const devicePreview = new PageBuilderDevicePreview();
        devicePreview.init();
        window.pageBuilderDevicePreview = devicePreview;
    }

    console.log('✅ Page Builder systems initialized');
});

console.log('📦 Page Builder Main script loaded');