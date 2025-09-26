/**
 * PageBuilder ParentCommunicator - Handles parent-side communication for Page Builder
 * Based on live-designer pattern but adapted for Page Builder GridStack functionality
 */
class PageBuilderParentCommunicator {
    constructor() {
        this.iframe = null;
        this.iframeOrigin = window.location.origin;
        this.isIframeReady = false;
        this.messageQueue = [];

        // GridStack state
        this.gridStackReady = false;
        this.sectionsCount = 0;

        // Message handlers
        this.messageHandlers = new Map();

        this.setupMessageHandling();
        this.setupDefaultHandlers();

        console.log('🔗 PageBuilder Parent Communicator initialized');
    }

    /**
     * Set the iframe reference
     * @param {HTMLIFrameElement} iframe - Preview iframe element
     */
    setIframe(iframe) {
        this.iframe = iframe;

        // Listen for iframe load to establish communication
        iframe.addEventListener('load', () => {
            console.log('🔗 Iframe loaded, waiting for PageBuilder systems...');
            // Send ready confirmation to iframe
            this.sendMessage('parent:ready', {
                timestamp: Date.now()
            });
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
            if (!event.data || event.data.source !== 'pagebuilder-iframe') {
                return;
            }

            this.handleMessage(event.data);
        });
    }

    /**
     * Handle incoming messages from iframe
     */
    handleMessage(messageData) {
        const { type, data } = messageData;
        console.log(`📥 Parent received message: ${type}`, messageData);

        // Handle iframe ready
        if (type === 'iframe:ready') {
            this.isIframeReady = true;
            this.flushMessageQueue();
            console.log('✅ Iframe is ready for communication');
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
        } else {
            console.warn(`⚠️ No handler registered for message type: ${type}`);
        }
    }

    /**
     * Register message handler
     */
    registerHandler(type, handler) {
        this.messageHandlers.set(type, handler);
        console.log(`📋 Registered parent handler for: ${type}`);
    }

    /**
     * Send message to iframe
     */
    sendMessage(type, data = {}) {
        const message = {
            type: type,
            data: data,
            source: 'pagebuilder-parent',
            timestamp: Date.now()
        };

        if (!this.isIframeReady && type !== 'parent:ready') {
            this.messageQueue.push(message);
            return;
        }

        if (this.iframe && this.iframe.contentWindow) {
            this.iframe.contentWindow.postMessage(message, this.iframeOrigin);
            console.log(`📤 Parent sent message: ${type}`, message);
        }
    }

    /**
     * Flush queued messages when iframe is ready
     */
    flushMessageQueue() {
        this.messageQueue.forEach(message => {
            if (this.iframe && this.iframe.contentWindow) {
                this.iframe.contentWindow.postMessage(message, this.iframeOrigin);
            }
        });
        this.messageQueue = [];
        console.log('📨 Parent flushed message queue');
    }

    /**
     * Setup default message handlers
     */
    setupDefaultHandlers() {
        // GridStack ready handler
        this.registerHandler('gridstack:ready', (data) => {
            this.gridStackReady = true;
            this.sectionsCount = data.sectionsCount || 0;
            console.log('🎯 GridStack ready in iframe:', data);

            // Show success notification
            if (window.showToast) {
                window.showToast('GridStack initialized successfully', 'success');
            }
        });

        // Section reordered handler
        this.registerHandler('section:reordered', (data) => {
            console.log('✅ Sections reordered successfully:', data);

            // Show success notification
            if (window.showToast) {
                window.showToast('Section order updated', 'success');
            }

            // Trigger any additional parent-side updates
            this.onSectionReordered(data.sections);
        });

        // Section reorder error handler
        this.registerHandler('section:reorder-error', (data) => {
            console.error('❌ Section reorder failed:', data);

            // Show error notification
            if (window.showToast) {
                window.showToast('Failed to update section order: ' + data.error, 'error');
            }
        });

        // Component selection handler
        this.registerHandler('component:selected', (data) => {
            console.log('🎯 Component selected in iframe:', data);
            this.onComponentSelected(data);
        });

        // Toolbar action handler
        this.registerHandler('toolbar:action', (data) => {
            console.log('🔧 Toolbar action in iframe:', data);
            this.onToolbarAction(data.action, data.component);
        });
    }

    // === Event Handlers (can be overridden) ===

    /**
     * Handle section reordered event
     */
    onSectionReordered(sections) {
        // Override this method to handle section reordering on parent side
        console.log('📦 Sections reordered (parent):', sections);
    }

    /**
     * Handle component selected event
     */
    onComponentSelected(componentData) {
        // Override this method to handle component selection on parent side
        console.log('🎯 Component selected (parent):', componentData);
    }

    /**
     * Handle toolbar action event
     */
    onToolbarAction(action, componentData) {
        // Override this method to handle toolbar actions on parent side
        console.log('🔧 Toolbar action (parent):', action, componentData);

        // Default action handling
        switch (action) {
            case 'edit-section':
                this.handleEditSection(componentData);
                break;
            case 'add-section':
                this.handleAddSection(componentData);
                break;
            case 'add-widget':
                this.handleAddWidget(componentData);
                break;
            case 'delete-section':
                this.handleDeleteSection(componentData);
                break;
            case 'edit-widget':
                this.handleEditWidget(componentData);
                break;
            case 'add-widget':
                this.handleAddWidget(componentData);
                break;
            case 'delete-widget':
                this.handleDeleteWidget(componentData);
                break;
            default:
                console.warn('⚠️ Unknown toolbar action:', action);
        }
    }

    // === Default Action Handlers ===

    handleEditSection(componentData) {
        console.log('✏️ Edit section:', componentData);
        // Implement section editing logic
    }

    handleAddSection(componentData) {
        console.log('➕ Add section:', componentData);

        // Open section templates modal
        this.openSectionTemplatesModal(componentData);
    }

    handleDeleteSection(componentData) {
        console.log('🗑️ Delete section:', componentData);
        // Implement section deletion logic
    }

    handleEditWidget(componentData) {
        console.log('✏️ Edit widget:', componentData);
        // Implement widget editing logic
    }

    handleAddWidget(componentData) {
        console.log('➕ Add widget:', componentData);

        // Open add widget modal
        this.openAddWidgetModal(componentData);
    }

    handleDeleteWidget(componentData) {
        console.log('🗑️ Delete widget:', componentData);
        // Implement widget deletion logic
    }

    // === Public API ===

    /**
     * Get current GridStack state
     */
    getGridStackState() {
        return {
            ready: this.gridStackReady,
            sectionsCount: this.sectionsCount,
            iframeReady: this.isIframeReady
        };
    }

    /**
     * Request iframe to refresh GridStack
     */
    refreshGrid() {
        this.sendMessage('gridstack:refresh', {
            timestamp: Date.now()
        });
    }

    /**
     * Request component selection in iframe
     */
    selectComponent(componentType, componentId) {
        this.sendMessage('component:select', {
            type: componentType,
            id: componentId,
            timestamp: Date.now()
        });
    }

    /**
     * Open section templates modal
     * @param {Object} componentData - Component data from toolbar action
     */
    openSectionTemplatesModal(componentData) {
        console.log('🎨 Opening section templates modal for:', componentData);

        // Find the section templates modal
        const modal = document.getElementById('sectionTemplatesModal');
        if (!modal) {
            console.error('❌ Section templates modal not found');
            return;
        }

        // Store component data for later use
        if (window.pageBuilder) {
            window.pageBuilder.currentAddSectionContext = componentData;
        } else {
            window.currentAddSectionContext = componentData;
        }

        // Open the modal using Bootstrap
        try {
            // Use Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            console.log('✅ Section templates modal opened successfully');
        } catch (error) {
            console.error('❌ Failed to open section templates modal:', error);

            // Fallback: try jQuery modal if Bootstrap 5 is not available
            if (window.$ && window.$.fn.modal) {
                $(modal).modal('show');
                console.log('✅ Section templates modal opened via jQuery');
            } else {
                console.error('❌ No modal system available');
            }
        }
    }

    /**
     * Open add widget modal
     * @param {Object} componentData - Component data from toolbar action
     */
    openAddWidgetModal(componentData) {
        console.log('🧩 Opening add widget modal for:', componentData);

        // Find the add widget modal
        const modal = document.getElementById('addWidgetModal');
        if (!modal) {
            console.error('❌ Add widget modal not found');
            return;
        }

        // Store component data for later use
        if (window.pageBuilder) {
            window.pageBuilder.currentAddWidgetContext = componentData;
        } else {
            window.currentAddWidgetContext = componentData;
        }

        // Set widget context if method exists
        if (window.setAddWidgetContext && typeof window.setAddWidgetContext === 'function') {
            window.setAddWidgetContext(componentData);
        }

        // Open the modal using Bootstrap
        try {
            // Use Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            console.log('✅ Add widget modal opened successfully');
        } catch (error) {
            console.error('❌ Failed to open add widget modal:', error);

            // Fallback: try jQuery modal if Bootstrap 5 is not available
            if (window.$ && window.$.fn.modal) {
                $(modal).modal('show');
                console.log('✅ Add widget modal opened via jQuery');
            } else {
                console.error('❌ No modal system available');
            }
        }
    }
}

// Export class to global scope
window.PageBuilderParentCommunicator = PageBuilderParentCommunicator;

// Global instance
window.pageBuilderParentCommunicator = null;

console.log('📦 PageBuilder Parent Communicator module loaded');