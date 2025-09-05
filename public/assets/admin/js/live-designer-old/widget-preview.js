/**
 * Widget Preview Module
 * 
 * Handles widget-specific interactions, selection, highlighting, and navigation
 * within the preview iframe environment
 */

(function() {
    'use strict';

    // Widget Preview Module
    const WidgetPreview = {
        // Initialize widget preview functionality
        init: function(sharedState) {
            this.sharedState = sharedState;
            this.setupWidgetInteractions();
            console.log('🎯 Widget preview module initialized');
        },

        // Setup widget interactions
        setupWidgetInteractions: function() {
            const widgets = document.querySelectorAll('[data-preview-widget]');
            
            widgets.forEach(widget => {
                // Make widget clickable
                widget.style.cursor = 'pointer';
                widget.setAttribute('tabindex', '0'); // Make focusable
                
                // Click handler
                widget.addEventListener('click', (e) => {
                    // Check if click is on toolbar actions
                    if (e.target.closest('.preview-toolbar-action')) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.handleToolbarAction(e.target, widget);
                        return;
                    }
                    
                    e.preventDefault();
                    e.stopPropagation();
                    this.selectWidget(
                        widget.dataset.previewWidget, // PageSectionWidget instance ID
                        widget.dataset.widgetId, // Original widget ID
                        widget.dataset.sectionId, // Section ID for context
                        widget.dataset.widgetName // Widget name for display
                    );
                });
                
                // Keyboard handler
                widget.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.selectWidget(
                            widget.dataset.previewWidget,
                            widget.dataset.widgetId,
                            widget.dataset.sectionId,
                            widget.dataset.widgetName
                        );
                    }
                });
                
                // Hover effects - using box-shadow
                widget.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('preview-highlighted')) {
                        this.style.boxShadow = '0 0 0 2px #0d6efd, 0 0 0 4px rgba(13, 110, 253, 0.2)';
                    }
                });
                
                widget.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('preview-highlighted')) {
                        this.style.boxShadow = '';
                    }
                });
                
                // Focus effects - using box-shadow
                widget.addEventListener('focus', function() {
                    this.style.boxShadow = '0 0 0 3px #0d6efd, 0 0 0 5px rgba(13, 110, 253, 0.3)';
                });
                
                widget.addEventListener('blur', function() {
                    if (!this.classList.contains('preview-highlighted')) {
                        this.style.boxShadow = '';
                    }
                });
            });
            
            console.log(`🎯 Setup interactions for ${widgets.length} widgets`);
        },

        // Select a widget
        selectWidget: function(instanceId, widgetId, sectionId, widgetName) {
            // Clear previous selections
            this.sharedState.deselectAll();
            
            // Highlight selected widget
            this.highlightWidget(instanceId);
            
            // Notify parent with correct PageSectionWidget instance ID
            parent.postMessage({
                type: 'widget-selected',
                data: { 
                    instanceId: instanceId, // PageSectionWidget ID for editing
                    widgetId: widgetId, // Original widget ID for reference
                    sectionId: sectionId, // Section context
                    widgetName: widgetName // Display name
                }
            }, '*');
            
            console.log(`✓ Widget selected: ${widgetName} (instance: ${instanceId}, widget: ${widgetId}, section: ${sectionId})`);
        },

        // Highlight a widget
        highlightWidget: function(instanceId) {
            const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
            if (widget) {
                // Remove existing highlights
                document.querySelectorAll('.preview-highlighted').forEach(el => {
                    el.classList.remove('preview-highlighted');
                    el.style.boxShadow = '';
                });
                
                // Add highlight to selected widget
                widget.classList.add('preview-highlighted');
                
                // Scroll into view with some top offset to account for toolbar
                widget.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'center'
                });
            }
        },

        // Navigate between widgets using arrow keys
        navigateWidgets: function(currentWidget, direction) {
            const widgets = Array.from(document.querySelectorAll('[data-preview-widget]'));
            const currentIndex = widgets.indexOf(currentWidget);
            
            let nextIndex;
            
            switch (direction) {
                case 'ArrowUp':
                case 'ArrowLeft':
                    nextIndex = currentIndex > 0 ? currentIndex - 1 : widgets.length - 1;
                    break;
                case 'ArrowDown':
                case 'ArrowRight':
                    nextIndex = currentIndex < widgets.length - 1 ? currentIndex + 1 : 0;
                    break;
            }
            
            if (nextIndex !== undefined && widgets[nextIndex]) {
                const widget = widgets[nextIndex];
                this.selectWidget(
                    widget.dataset.previewWidget,
                    widget.dataset.widgetId,
                    widget.dataset.sectionId,
                    widget.dataset.widgetName
                );
                widget.focus();
            }
        },

        // Scroll to a specific widget
        scrollToWidget: function(instanceId) {
            const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
            if (widget) {
                widget.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'center'
                });
            }
        },

        // Update widget state (loading, updating, etc.)
        updateWidgetState: function(instanceId, state) {
            const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
            if (!widget) return;
            
            // Remove existing state classes
            widget.classList.remove('widget-loading', 'widget-updating', 'widget-updated', 'widget-error');
            
            // Add new state class
            if (state) {
                widget.classList.add(`widget-${state}`);
                
                // Auto-remove temporary states
                if (['updated', 'error'].includes(state)) {
                    setTimeout(() => {
                        widget.classList.remove(`widget-${state}`);
                    }, 3000);
                }
            }
        },

        // Handle toolbar actions for widgets
        handleToolbarAction: function(actionElement, targetElement) {
            const action = actionElement.dataset.action;
            const elementId = targetElement.dataset.previewWidget;
            const elementName = targetElement.dataset.widgetName;
            
            console.log(`🔧 Widget toolbar action: ${action} on widget "${elementName}" (ID: ${elementId})`);
            
            switch (action) {
                case 'edit':
                    // Send toolbar action message to parent
                    parent.postMessage({
                        type: 'toolbar-action',
                        data: { 
                            action: 'edit',
                            elementType: 'widget',
                            elementId: elementId,
                            elementName: elementName
                        }
                    }, '*');
                    
                    console.log(`✏️ Edit widget: ${elementName} (ID: ${elementId}) - message sent to parent`);
                    break;
                    
                case 'copy':
                    this.handleCopyWidget(elementId, elementName);
                    break;
                    
                case 'delete':
                    this.handleDeleteWidget(elementId, elementName);
                    break;
                    
                case 'move-up':
                case 'move-down':
                    this.handleMoveWidget(elementId, action);
                    break;
                    
                default:
                    console.warn(`Unknown widget toolbar action: ${action}`);
            }
        },

        // Handle widget copy
        handleCopyWidget: function(id, name) {
            parent.postMessage({
                type: 'toolbar-action',
                data: { 
                    action: 'copy',
                    elementType: 'widget',
                    elementId: id,
                    elementName: name
                }
            }, '*');
            
            console.log(`📋 Copy widget: ${name}`);
        },

        // Handle widget delete
        handleDeleteWidget: function(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                parent.postMessage({
                    type: 'toolbar-action',
                    data: { 
                        action: 'delete',
                        elementType: 'widget',
                        elementId: id,
                        elementName: name
                    }
                }, '*');
                
                console.log(`🗑️ Delete widget: ${name}`);
            }
        },

        // Handle widget move
        handleMoveWidget: function(id, direction) {
            parent.postMessage({
                type: 'toolbar-action',
                data: { 
                    action: direction,
                    elementType: 'widget',
                    elementId: id
                }
            }, '*');
            
            console.log(`📈 Move widget ${direction}: ${id}`);
        },

        // Utility functions for external access
        getSelectedWidget: function() {
            const highlighted = document.querySelector('.preview-highlighted[data-preview-widget]');
            return highlighted ? {
                instanceId: highlighted.dataset.previewWidget,
                widgetId: highlighted.dataset.widgetId,
                sectionId: highlighted.dataset.sectionId,
                widgetName: highlighted.dataset.widgetName
            } : null;
        },

        getAllWidgets: function() {
            return Array.from(document.querySelectorAll('[data-preview-widget]'))
                .map(widget => ({
                    instanceId: widget.dataset.previewWidget,
                    widgetId: widget.dataset.widgetId,
                    sectionId: widget.dataset.sectionId,
                    widgetName: widget.dataset.widgetName
                }));
        },

        getWidgetInfo: function(instanceId) {
            const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
            if (widget) {
                const rect = widget.getBoundingClientRect();
                return {
                    instanceId: instanceId,
                    widgetId: widget.dataset.widgetId,
                    sectionId: widget.dataset.sectionId,
                    widgetName: widget.dataset.widgetName,
                    position: { x: rect.left, y: rect.top },
                    size: { width: rect.width, height: rect.height },
                    visible: rect.top >= 0 && rect.bottom <= window.innerHeight
                };
            }
            return null;
        }
    };

    // Export to global scope for main coordinator
    window.WidgetPreview = WidgetPreview;

})();
