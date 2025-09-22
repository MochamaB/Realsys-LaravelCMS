/**
 * ComponentToolbar - Real DOM toolbar management for selected components
 * 
 * Features:
 * - Dynamic toolbar creation and positioning
 * - Component-specific action buttons
 * - Zoom-compensated positioning and sizing
 * - Real DOM elements for better control and extensibility
 */
class ComponentToolbar {
    constructor(selectionManager) {
        this.selectionManager = selectionManager;
        this.toolbar = null;
        this.currentComponent = null;
        this.isVisible = false;
        
        // Define available actions for each component type
        this.actions = {
            page: [
                { id: 'edit', icon: 'ri-pencil-fill', label: 'Edit', variant: 'primary' },
                { id: 'settings', icon: 'bx bx-cog', label: 'Settings', variant: 'secondary' },
                { id: 'duplicate', icon: 'bx bx-copy', label: 'Duplicate', variant: 'secondary' },
                { id: 'seo', icon: 'bx bx-search-alt', label: 'SEO', variant: 'secondary' }
            ],
            section: [
                { id: 'edit', icon: 'bx bx-edit', label: 'Edit', variant: 'primary' },
                { id: 'settings', icon: 'bx bx-cog', label: 'Settings', variant: 'secondary' },
                { id: 'duplicate', icon: 'bx bx-copy', label: 'Duplicate', variant: 'secondary' },
                { id: 'delete', icon: 'bx bx-trash', label: 'Delete', variant: 'danger' }
            ],
            widget: [
                { id: 'edit', icon: 'bx bx-edit', label: 'Edit', variant: 'primary' },
                { id: 'settings', icon: 'bx bx-cog', label: 'Settings', variant: 'secondary' },
                { id: 'duplicate', icon: 'bx bx-copy', label: 'Duplicate', variant: 'secondary' },
                { id: 'delete', icon: 'bx bx-trash', label: 'Delete', variant: 'danger' }
            ]
        };
        
        console.log('🔧 Component toolbar initialized');
    }
    
    /**
     * Show toolbar for selected component
     * @param {Object} component - Selected component object
     */
    show(component) {
        this.currentComponent = component;
        
        if (this.toolbar) {
            this.hide();
        }
        
        this.createToolbar(component);
        this.positionToolbar(component);
        this.isVisible = true;
        
        console.log(`🔧 Toolbar shown for ${component.type} ${component.id}`);
    }
    
    /**
     * Hide current toolbar
     */
    hide() {
        if (this.toolbar) {
            this.toolbar.remove();
            this.toolbar = null;
        }
        
        this.currentComponent = null;
        this.isVisible = false;
        
        console.log('🔧 Toolbar hidden');
    }
    
    /**
     * Create toolbar DOM element with action buttons
     * @param {Object} component - Component to create toolbar for
     */
    createToolbar(component) {
        // Create main toolbar container
        this.toolbar = document.createElement('div');
        this.toolbar.className = `component-toolbar component-toolbar--${component.type}`;
        this.toolbar.setAttribute('data-component-type', component.type);
        this.toolbar.setAttribute('data-component-id', component.id);
        
        // Create component name section (left side)
        const nameSection = document.createElement('div');
        nameSection.className = 'toolbar-name';
        
        const icon = document.createElement('span');
        icon.className = 'toolbar-icon';
        icon.textContent = this.getComponentIcon(component.type);
        
        const title = document.createElement('span');
        title.className = 'toolbar-title';
        title.textContent = component.name;
        
        nameSection.appendChild(icon);
        nameSection.appendChild(title);
        this.toolbar.appendChild(nameSection);
        
        // Create action buttons container (right side)
        const actionsContainer = document.createElement('div');
        actionsContainer.className = 'toolbar-actions';
        
        // Add component-specific action buttons
        const actions = this.actions[component.type] || [];
        actions.forEach(action => {
            const button = this.createActionButton(action, component);
            actionsContainer.appendChild(button);
        });
        
        this.toolbar.appendChild(actionsContainer);
        
        // Append to document body
        document.body.appendChild(this.toolbar);
    }
    
    
    /**
     * Create action button element
     * @param {Object} action - Action definition
     * @param {Object} component - Component object
     * @returns {Element} Button element
     */
    createActionButton(action, component) {
        const button = document.createElement('button');
        button.className = `toolbar-btn btn-icon btn-${action.variant}`;
        button.setAttribute('data-action', action.id);
        button.setAttribute('title', action.label);
        button.type = 'button';
        
        // Add icon only (no label for icon-only design)
        if (action.icon) {
            const icon = document.createElement('i');
            icon.className = action.icon;
            button.appendChild(icon);
        }
        
        // Add click handler
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.executeAction(action.id, component);
        });
        
        return button;
    }
    
    /**
     * Create close button for toolbar
     * @returns {Element} Close button element
     */
    createCloseButton() {
        const closeButton = document.createElement('button');
        closeButton.className = 'toolbar-close';
        closeButton.type = 'button';
        closeButton.innerHTML = '✕';
        closeButton.setAttribute('title', 'Close Toolbar');
        
        closeButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.selectionManager.deselect();
        });
        
        return closeButton;
    }
    
    /**
     * Position toolbar relative to component element
     * @param {Object} component - Component object
     */
    positionToolbar(component) {
        if (!this.toolbar || !component.element) return;
        
        const elementRect = component.element.getBoundingClientRect();
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight
        };
        
        // Position toolbar at the top edge of the component
        let top = elementRect.top - 32; // Height of toolbar (24px + padding)
        let left = elementRect.left;
        let width = elementRect.width;
        
        // Adjust if toolbar would be above viewport - place it inside the component
        if (top < 10) {
            top = elementRect.top + 2; // Small offset inside component
        }
        
        // Ensure minimum width for toolbar functionality
        if (width < 200) {
            width = Math.min(200, viewport.width - left - 20);
        }
        
        // Adjust if toolbar would extend beyond right edge
        if (left + width > viewport.width - 10) {
            left = Math.max(10, viewport.width - width - 10);
        }
        
        // Apply position with scroll offset
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        this.toolbar.style.position = 'absolute';
        this.toolbar.style.top = `${top + scrollTop}px`;
        this.toolbar.style.left = `${left + scrollLeft}px`;
        this.toolbar.style.width = `${width}px`;
        this.toolbar.style.zIndex = '10000';
        
        // Remove zoom compensation - toolbar should maintain its natural size
        this.toolbar.style.transform = 'none';
    }
    
    /**
     * Execute toolbar action
     * @param {string} actionId - Action identifier
     * @param {Object} component - Component object
     */
    executeAction(actionId, component) {
        console.log(`🔧 Executing action: ${actionId} on ${component.type} ${component.id}`);
        
        // Notify iframe communicator about the action
        if (window.iframeCommunicator) {
            window.iframeCommunicator.notifyToolbarAction(actionId, component);
        }
        
        // Handle built-in actions
        switch(actionId) {
            case 'edit':
                this.handleEditAction(component);
                break;
            case 'settings':
                this.handleSettingsAction(component);
                break;
            case 'duplicate':
                this.handleDuplicateAction(component);
                break;
            case 'delete':
                this.handleDeleteAction(component);
                break;
            case 'seo':
                this.handleSEOAction(component);
                break;
            default:
                console.warn(`⚠️ Unknown action: ${actionId}`);
        }
    }
    
    /**
     * Handle edit action for component
     * @param {Object} component - Component object
     */
    handleEditAction(component) {
        // For now, just log the action
        // In a real implementation, this would open an edit modal or redirect
        console.log(`✏️ Opening editor for ${component.type} ${component.id}`);
        
        // Example: Open edit URL in parent window
        const editUrls = {
            page: `/admin/pages/${component.id}/edit`,
            section: `/admin/sections/${component.id}/edit`,
            widget: `/admin/widgets/${component.id}/edit`
        };
        
        const editUrl = editUrls[component.type];
        if (editUrl && window.parent) {
            window.parent.location.href = editUrl;
        }
    }
    
    /**
     * Handle settings action for component
     * @param {Object} component - Component object
     */
    handleSettingsAction(component) {
        console.log(`⚙️ Opening settings for ${component.type} ${component.id}`);
        
        // This would typically open a settings modal
        // For now, just extract and log current settings
        if (window.contentExtractor) {
            const content = window.contentExtractor.extractComponent(component);
            console.log('Current component data:', content);
        }
    }
    
    /**
     * Handle duplicate action for component
     * @param {Object} component - Component object
     */
    handleDuplicateAction(component) {
        console.log(`📋 Duplicating ${component.type} ${component.id}`);
        
        // This would typically make an API call to duplicate the component
        // For now, just extract the component data
        if (window.contentExtractor) {
            const content = window.contentExtractor.extractComponent(component);
            console.log('Component data for duplication:', content);
        }
    }
    
    /**
     * Handle move action for component
     * @param {Object} component - Component object
     * @param {string} direction - 'up' or 'down'
     */
    handleMoveAction(component, direction) {
        console.log(`${direction === 'up' ? '⬆️' : '⬇️'} Moving ${component.type} ${component.id} ${direction}`);
        
        // This would typically use the sortable manager
        if (window.sortableManager) {
            window.sortableManager.moveComponent(component, direction);
        }
    }
    
    /**
     * Handle delete action for component
     * @param {Object} component - Component object
     */
    handleDeleteAction(component) {
        const confirmed = confirm(`Are you sure you want to delete this ${component.type}?`);
        
        if (confirmed) {
            console.log(`🗑️ Deleting ${component.type} ${component.id}`);
            
            // Hide toolbar first
            this.hide();
            
            // Remove element from DOM (temporary - would normally be an API call)
            component.element.remove();
            
            // Deselect component
            this.selectionManager.deselect();
        }
    }
    
    /**
     * Handle SEO action for component
     * @param {Object} component - Component object
     */
    handleSEOAction(component) {
        console.log(`🔍 Opening SEO settings for ${component.type} ${component.id}`);
        
        // Only available for pages
        if (component.type === 'page') {
            const seoUrl = `/admin/pages/${component.id}/seo`;
            if (window.parent) {
                window.parent.location.href = seoUrl;
            }
        }
    }
    
    
    /**
     * Show temporary notification
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, info)
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `toolbar-notification toolbar-notification--${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            padding: 12px 16px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            font-size: 14px;
            max-width: 300px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });
        
        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
    
    /**
     * Get icon for component type
     * @param {string} type - Component type
     * @returns {string} Icon character
     */
    getComponentIcon(type) {
        const icons = {
            page: '📄',
            section: '📦',
            widget: '🧩'
        };
        
        return icons[type] || '❓';
    }
    
    /**
     * Update toolbar position when component moves or resizes
     */
    updatePosition() {
        if (this.isVisible && this.currentComponent) {
            this.positionToolbar(this.currentComponent);
        }
    }
    
    /**
     * Handle zoom changes
     * @param {Object} zoomData - Zoom level and inverse scale
     */
    handleZoomChange(zoomData) {
        if (this.isVisible && this.toolbar) {
            this.toolbar.style.transform = `scale(${zoomData.inverse})`;
            this.updatePosition();
        }
    }
    
    /**
     * Add custom action to component type
     * @param {string} componentType - Component type
     * @param {Object} action - Action definition
     */
    addCustomAction(componentType, action) {
        if (!this.actionDefinitions[componentType]) {
            this.actionDefinitions[componentType] = [];
        }
        
        this.actionDefinitions[componentType].push(action);
        console.log(`🔧 Added custom action ${action.id} to ${componentType}`);
    }
    
    /**
     * Remove custom action from component type
     * @param {string} componentType - Component type
     * @param {string} actionId - Action ID to remove
     */
    removeCustomAction(componentType, actionId) {
        if (this.actionDefinitions[componentType]) {
            this.actionDefinitions[componentType] = this.actionDefinitions[componentType]
                .filter(action => action.id !== actionId);
            console.log(`🔧 Removed custom action ${actionId} from ${componentType}`);
        }
    }
    
    /**
     * Check if toolbar is currently visible
     * @returns {boolean} True if toolbar is visible
     */
    isToolbarVisible() {
        return this.isVisible;
    }
    
    
    /**
     * Get current component that toolbar is shown for
     * @returns {Object|null} Current component or null
     */
    getCurrentComponent() {
        return this.currentComponent;
    }
    
    /**
     * Cleanup method for destroying the toolbar
     */
    destroy() {
        this.hide();
        console.log('🧹 Component toolbar destroyed');
    }
}

// Export for use in other modules
window.ComponentToolbar = ComponentToolbar;
