/**
 * PageBuilder Toolbar Handler - Detects clicks on server-generated toolbars
 *
 * This bridges the gap between static server-side toolbars and dynamic client-side actions
 * by detecting clicks on toolbar buttons and routing them through the communication system.
 */
class PageBuilderToolbarHandler {
    constructor() {
        this.communicator = null;
        this.isEnabled = true;

        this.setupClickDetection();
        console.log('🔧 PageBuilder Toolbar Handler initialized');
    }

    /**
     * Set the communicator instance
     * @param {Object} communicator - PageBuilder communicator instance
     */
    setCommunicator(communicator) {
        this.communicator = communicator;
        console.log('🔗 Toolbar handler connected to communicator');
    }

    /**
     * Setup click detection for toolbar buttons
     */
    setupClickDetection() {
        // Use event delegation to catch clicks on toolbar buttons
        document.addEventListener('click', (e) => {
            if (!this.isEnabled) return;

            // Check if clicked element is a toolbar button with data-action
            const toolbarBtn = e.target.closest('[data-action]');
            if (!toolbarBtn) return;

            // Prevent default action and stop propagation
            e.preventDefault();
            e.stopPropagation();

            this.handleToolbarClick(toolbarBtn, e);
        }, true); // Use capture phase to catch before other handlers

        console.log('👂 Toolbar click detection setup complete');
    }

    /**
     * Handle toolbar button click
     * @param {Element} button - Clicked toolbar button
     * @param {Event} event - Click event
     */
    handleToolbarClick(button, event) {
        const action = button.getAttribute('data-action');
        const componentData = this.extractComponentData(button);

        console.log(`🔧 Toolbar action clicked: ${action}`, componentData);

        // Route action through communicator if available
        if (this.communicator && this.communicator.notifyToolbarAction) {
            console.log('📤 Sending toolbar action via notifyToolbarAction:', action);
            this.communicator.notifyToolbarAction(action, componentData);
        } else if (this.communicator && this.communicator.sendMessage) {
            // Fallback for different communicator interface
            console.log('📤 Sending toolbar action via sendMessage:', action);
            this.communicator.sendMessage('toolbar:action', {
                action: action,
                component: componentData,
                timestamp: Date.now()
            });
        } else {
            console.warn('⚠️ No communicator available for toolbar action:', action);
            this.handleActionDirectly(action, componentData);
        }
    }

    /**
     * Extract component data from toolbar button context
     * @param {Element} button - Toolbar button element
     * @returns {Object} Component data
     */
    extractComponentData(button) {
        // Find the toolbar container
        const toolbar = button.closest('.pagebuilder-toolbar');
        if (!toolbar) {
            console.warn('⚠️ Could not find toolbar container for button');
            return {};
        }

        const componentType = toolbar.getAttribute('data-component-type') ||
                             toolbar.classList.contains('pagebuilder-page-toolbar') ? 'page' :
                             toolbar.classList.contains('pagebuilder-section-toolbar') ? 'section' :
                             toolbar.classList.contains('pagebuilder-widget-toolbar') ? 'widget' : 'unknown';

        // Extract IDs from button data attributes
        const pageId = button.getAttribute('data-page-id');
        const sectionId = button.getAttribute('data-section-id');
        const widgetId = button.getAttribute('data-widget-id');

        // Determine primary ID based on component type
        let primaryId = null;
        if (componentType === 'page' && pageId) primaryId = pageId;
        else if (componentType === 'section' && sectionId) primaryId = sectionId;
        else if (componentType === 'widget' && widgetId) primaryId = widgetId;

        const componentData = {
            type: componentType,
            id: primaryId,
            pageId: pageId,
            sectionId: sectionId,
            widgetId: widgetId,
            action: button.getAttribute('data-action'),
            element: toolbar.parentElement, // The component element the toolbar belongs to
            toolbar: toolbar,
            button: button
        };

        // Add component-specific metadata
        if (componentType === 'page') {
            componentData.name = `Page ${pageId}`;
        } else if (componentType === 'section') {
            componentData.name = `Section ${sectionId}`;
        } else if (componentType === 'widget') {
            componentData.name = `Widget ${widgetId}`;
        }

        return componentData;
    }

    /**
     * Handle action directly when no communicator is available (fallback)
     * @param {string} action - Action name
     * @param {Object} componentData - Component data
     */
    handleActionDirectly(action, componentData) {
        console.log(`🔧 Handling action directly: ${action}`, componentData);

        switch(action) {
            case 'add-section':
                this.handleAddSection(componentData);
                break;
            case 'edit-section':
                this.handleEditSection(componentData);
                break;
            case 'delete-section':
                this.handleDeleteSection(componentData);
                break;
            case 'edit-widget':
                this.handleEditWidget(componentData);
                break;
            case 'delete-widget':
                this.handleDeleteWidget(componentData);
                break;
            default:
                console.log(`🔧 Unknown action: ${action} - would be handled by parent`);
        }
    }

    /**
     * Handle add section action
     * @param {Object} componentData - Component data
     */
    handleAddSection(componentData) {
        console.log('➕ Add section action (direct handling)', componentData);
        alert('Add Section clicked! This would open the section templates modal in the parent window.');
    }

    /**
     * Handle edit section action
     * @param {Object} componentData - Component data
     */
    handleEditSection(componentData) {
        console.log('✏️ Edit section action (direct handling)', componentData);
        alert(`Edit Section ${componentData.id} clicked!`);
    }

    /**
     * Handle delete section action
     * @param {Object} componentData - Component data
     */
    handleDeleteSection(componentData) {
        console.log('🗑️ Delete section action (direct handling)', componentData);
        if (confirm(`Delete Section ${componentData.id}?`)) {
            alert('Section would be deleted via API call');
        }
    }

    /**
     * Handle edit widget action
     * @param {Object} componentData - Component data
     */
    handleEditWidget(componentData) {
        console.log('✏️ Edit widget action (direct handling)', componentData);
        alert(`Edit Widget ${componentData.id} clicked!`);
    }

    /**
     * Handle delete widget action
     * @param {Object} componentData - Component data
     */
    handleDeleteWidget(componentData) {
        console.log('🗑️ Delete widget action (direct handling)', componentData);
        if (confirm(`Delete Widget ${componentData.id}?`)) {
            alert('Widget would be deleted via API call');
        }
    }

    /**
     * Enable/disable toolbar click detection
     * @param {boolean} enabled - Whether detection should be enabled
     */
    setEnabled(enabled) {
        this.isEnabled = enabled;
        console.log(`🔧 Toolbar handler ${enabled ? 'enabled' : 'disabled'}`);
    }

    /**
     * Get all toolbar buttons in the current document
     * @returns {Array} Array of toolbar button elements
     */
    getAllToolbarButtons() {
        return Array.from(document.querySelectorAll('.pagebuilder-toolbar [data-action]'));
    }

    /**
     * Get statistics about detected toolbars
     * @returns {Object} Toolbar statistics
     */
    getToolbarStats() {
        const buttons = this.getAllToolbarButtons();
        const stats = {
            totalButtons: buttons.length,
            actions: {},
            components: {}
        };

        buttons.forEach(button => {
            const action = button.getAttribute('data-action');
            const toolbar = button.closest('.pagebuilder-toolbar');
            const componentType = toolbar?.getAttribute('data-component-type') || 'unknown';

            stats.actions[action] = (stats.actions[action] || 0) + 1;
            stats.components[componentType] = (stats.components[componentType] || 0) + 1;
        });

        return stats;
    }

    /**
     * Cleanup method
     */
    destroy() {
        this.setEnabled(false);
        this.communicator = null;
        console.log('🧹 Toolbar handler destroyed');
    }

    
}

// Export for use in other modules
window.PageBuilderToolbarHandler = PageBuilderToolbarHandler;

console.log('📦 PageBuilder Toolbar Handler module loaded');