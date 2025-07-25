/**
 * Widget Component Integration for GrapesJS Page Designer
 * Phase 2.1 Integration Script
 * 
 * Integrates the Widget Component Factory with the existing page designer
 * and sets up all necessary connections and event handlers.
 */

(function() {
    'use strict';

    // Wait for page designer to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize after a short delay to ensure GrapesJS is ready
        setTimeout(initializeWidgetComponents, 1000);
    });

    /**
     * Initialize widget components integration
     */
    async function initializeWidgetComponents() {
        console.log('🚀 Initializing Widget Component Integration...');

        // Check if GrapesJS editor is available
        if (!window.editor) {
            console.warn('⚠️ GrapesJS editor not found, retrying in 2 seconds...');
            setTimeout(initializeWidgetComponents, 2000);
            return;
        }

        try {
            // Initialize Widget Component Factory
            const widgetFactory = new WidgetComponentFactory(window.editor);
            
            // Store factory globally for access
            window.widgetComponentFactory = widgetFactory;
            
            // Set up additional integrations
            setupWidgetCommands(window.editor, widgetFactory);
            setupWidgetPanels(window.editor, widgetFactory);
            setupWidgetStyles(window.editor);
            setupKeyboardShortcuts(window.editor, widgetFactory);
            
            console.log('✅ Widget Component Integration initialized successfully');
            
            // Trigger custom event for other scripts
            window.dispatchEvent(new CustomEvent('widgetComponentsReady', {
                detail: { factory: widgetFactory, editor: window.editor }
            }));
            
        } catch (error) {
            console.error('❌ Failed to initialize Widget Component Integration:', error);
            showInitializationError(error);
        }
    }

    /**
     * Set up widget-related commands
     */
    function setupWidgetCommands(editor, widgetFactory) {
        const commands = editor.Commands;

        // Command to open image picker for widgets
        commands.add('open-image-picker', {
            run(editor, sender) {
                const selected = editor.getSelected();
                if (!selected) return;

                console.log('📷 Opening image picker for widget');
                
                // Open media picker modal
                openMediaPicker((mediaUrl, mediaId) => {
                    // Update the selected component's image field
                    const traitName = sender.get('name');
                    selected.addAttributes({ [`data-${traitName}`]: mediaId });
                    
                    // Refresh widget preview
                    if (selected.loadWidgetPreview) {
                        selected.loadWidgetPreview();
                    }
                });
            }
        });

        // Command to open repeater field editor
        commands.add('open-repeater-editor', {
            run(editor, sender) {
                const selected = editor.getSelected();
                if (!selected) return;

                console.log('🔄 Opening repeater editor for widget');
                
                const widgetSlug = selected.get('widget-slug');
                const schema = widgetFactory.getSchema(widgetSlug);
                const fieldName = sender.get('name');
                
                openRepeaterEditor(schema, fieldName, (repeaterData) => {
                    // Update component with new repeater data
                    selected.addAttributes({ [`data-${fieldName}`]: JSON.stringify(repeaterData) });
                    
                    // Refresh widget preview
                    if (selected.loadWidgetPreview) {
                        selected.loadWidgetPreview();
                    }
                });
            }
        });

        // Command to refresh widget previews
        commands.add('refresh-widget-previews', {
            run(editor) {
                console.log('🔄 Refreshing all widget previews...');
                widgetFactory.refreshVisibleWidgets();
            }
        });

        // Command to clear widget cache
        commands.add('clear-widget-cache', {
            run(editor) {
                console.log('🗑️ Clearing widget cache...');
                widgetFactory.clearPreviewCache();
                
                // Show success message
                editor.runCommand('core:canvas-clear');
                setTimeout(() => {
                    showNotification('Widget cache cleared successfully', 'success');
                }, 100);
            }
        });

        // Command to refresh widget schemas
        commands.add('refresh-widget-schemas', {
            async run(editor) {
                console.log('🔄 Refreshing widget schemas...');
                
                try {
                    await widgetFactory.refreshSchemas();
                    showNotification('Widget schemas refreshed successfully', 'success');
                } catch (error) {
                    console.error('Failed to refresh schemas:', error);
                    showNotification('Failed to refresh widget schemas', 'error');
                }
            }
        });
    }

    /**
     * Set up widget-related panels
     */
    function setupWidgetPanels(editor, widgetFactory) {
        const panels = editor.Panels;

        // Add widget management buttons to the toolbar
        panels.addButton('options', {
            id: 'refresh-widgets',
            className: 'fa fa-refresh',
            command: 'refresh-widget-previews',
            attributes: { title: 'Refresh Widget Previews' }
        });

        panels.addButton('options', {
            id: 'clear-widget-cache',
            className: 'fa fa-trash',
            command: 'clear-widget-cache',
            attributes: { title: 'Clear Widget Cache' }
        });

        // Add widget statistics panel
        panels.addPanel({
            id: 'widget-stats',
            el: '.widget-stats-panel',
            buttons: [
                {
                    id: 'widget-stats-toggle',
                    className: 'fa fa-bar-chart',
                    command: 'toggle-widget-stats',
                    attributes: { title: 'Widget Statistics' }
                }
            ]
        });

        // Command to toggle widget statistics
        editor.Commands.add('toggle-widget-stats', {
            run(editor) {
                toggleWidgetStatsPanel(widgetFactory);
            }
        });
    }

    /**
     * Set up widget-specific styles
     */
    function setupWidgetStyles(editor) {
        // Add widget-specific CSS to the canvas
        const canvasDoc = editor.Canvas.getDocument();
        const styleElement = canvasDoc.createElement('style');
        styleElement.id = 'widget-component-styles';
        styleElement.textContent = `
            /* Widget Component Styles */
            .gjs-widget-component {
                position: relative;
                min-height: 50px;
            }

            .widget-loading-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                background: #f8f9fa;
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                color: #6c757d;
            }

            .loading-spinner {
                width: 30px;
                height: 30px;
                border: 3px solid #dee2e6;
                border-top: 3px solid #405189;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 15px;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .widget-error-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 30px 20px;
                background: #fff5f5;
                border: 2px dashed #f56565;
                border-radius: 8px;
                color: #c53030;
                text-align: center;
            }

            .widget-error-state .error-icon {
                font-size: 24px;
                margin-bottom: 10px;
            }

            .widget-error-state h4 {
                margin: 0 0 10px 0;
                color: #c53030;
            }

            .widget-error-state .error-message {
                margin: 0 0 15px 0;
                font-size: 14px;
                opacity: 0.8;
            }

            .widget-error-state .retry-btn {
                background: #c53030;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
            }

            .widget-error-state .retry-btn:hover {
                background: #9c2626;
            }

            /* Widget overlay styles */
            .gjs-widget-wrapper:hover .gjs-widget-overlay {
                display: block !important;
            }

            .gjs-widget-overlay {
                position: absolute;
                top: -35px;
                right: 5px;
                background: #405189;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 11px;
                z-index: 1000;
                display: none;
            }

            .gjs-widget-overlay::after {
                content: '';
                position: absolute;
                top: 100%;
                right: 10px;
                border: 5px solid transparent;
                border-top-color: #405189;
            }

            .gjs-widget-controls {
                display: flex;
                gap: 5px;
                align-items: center;
            }

            .gjs-widget-controls button {
                background: transparent;
                border: none;
                color: white;
                cursor: pointer;
                padding: 2px 4px;
                border-radius: 2px;
                font-size: 10px;
            }

            .gjs-widget-controls button:hover {
                background: rgba(255, 255, 255, 0.2);
            }

            .gjs-widget-name {
                margin-left: 8px;
                font-weight: 500;
            }
        `;
        
        canvasDoc.head.appendChild(styleElement);
    }

    /**
     * Set up keyboard shortcuts for widgets
     */
    function setupKeyboardShortcuts(editor, widgetFactory) {
        // Refresh widgets: Ctrl+R
        editor.Keymaps.add('core', 'ctrl+r', 'refresh-widget-previews');
        
        // Clear cache: Ctrl+Shift+C
        editor.Keymaps.add('core', 'ctrl+shift+c', 'clear-widget-cache');
        
        // Refresh schemas: Ctrl+Shift+R
        editor.Keymaps.add('core', 'ctrl+shift+r', 'refresh-widget-schemas');
    }

    /**
     * Open media picker modal
     */
    function openMediaPicker(callback) {
        // This would integrate with your existing media picker
        // For now, we'll simulate the selection
        console.log('📷 Media picker would open here');
        
        // Simulate media selection
        setTimeout(() => {
            const mockMediaUrl = '/assets/admin/images/placeholder.jpg';
            const mockMediaId = Math.floor(Math.random() * 1000);
            callback(mockMediaUrl, mockMediaId);
        }, 1000);
    }

    /**
     * Open repeater field editor
     */
    function openRepeaterEditor(schema, fieldName, callback) {
        console.log('🔄 Repeater editor would open here', { schema, fieldName });
        
        // This would open a modal with repeater field editor
        // For now, we'll simulate the editing
        setTimeout(() => {
            const mockRepeaterData = [
                { title: 'Item 1', description: 'Description 1' },
                { title: 'Item 2', description: 'Description 2' }
            ];
            callback(mockRepeaterData);
        }, 1000);
    }

    /**
     * Toggle widget statistics panel
     */
    function toggleWidgetStatsPanel(widgetFactory) {
        console.log('📊 Widget statistics panel would toggle here');
        
        // This would show/hide a panel with widget usage statistics
        // - Number of widgets per type
        // - Performance metrics
        // - Cache statistics
    }

    /**
     * Show notification to user
     */
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `widget-notification widget-notification-${type}`;
        notification.textContent = message;
        
        // Style the notification
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#405189',
            color: 'white',
            padding: '12px 20px',
            borderRadius: '4px',
            zIndex: '10000',
            fontSize: '14px',
            maxWidth: '300px'
        });
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    /**
     * Show initialization error
     */
    function showInitializationError(error) {
        const errorContainer = document.createElement('div');
        errorContainer.innerHTML = `
            <div style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                z-index: 10000;
                max-width: 500px;
                text-align: center;
            ">
                <h3 style="color: #dc3545; margin-bottom: 15px;">
                    Widget System Initialization Failed
                </h3>
                <p style="margin-bottom: 20px; color: #666;">
                    The widget component system could not be initialized.
                </p>
                <details style="margin-bottom: 20px; text-align: left;">
                    <summary style="cursor: pointer; font-weight: bold;">Error Details</summary>
                    <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; overflow: auto;">
${error.message}
${error.stack || ''}
                    </pre>
                </details>
                <button onclick="location.reload()" style="
                    background: #405189;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    cursor: pointer;
                ">
                    Refresh Page
                </button>
            </div>
        `;
        
        document.body.appendChild(errorContainer);
    }

    // Export functions for external use
    window.WidgetComponentIntegration = {
        showNotification,
        openMediaPicker,
        openRepeaterEditor
    };

})(); 