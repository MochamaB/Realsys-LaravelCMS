/**
 * Page Builder Preview Helper Functions - Main Coordinator
 * 
 * Main coordinator that manages specialized preview modules for Page Builder:
 * - pagebuilder-page-preview.js: Page-level interactions and selection
 * 
 * Based on Live Designer preview-helpers.js pattern
 */

// Initialize preview helpers when document is ready
(function() {
    'use strict';
    
    // Shared state object for communication between modules
    const sharedState = {
        currentSelection: {
            level: 'none', // 'none', 'page', 'section', 'widget'
            element: null,
            data: null
        },
        
        // Global deselect function
        deselectAll: function() {
            // Handle page deselection
            if (window.PageBuilderPagePreview && window.PageBuilderPagePreview.getCurrentSelection().level === 'page-selected') {
                window.PageBuilderPagePreview.deselectPage();
                return;
            }
            
            // Handle section/widget deselection
            document.querySelectorAll('.preview-highlighted').forEach(el => {
                el.classList.remove('preview-highlighted');
                el.style.boxShadow = '';
                // Remove toolbar buttons when deselecting
                const existingButtons = el.querySelector('.section-toolbar-buttons');
                if (existingButtons) {
                    existingButtons.remove();
                }
            });
            
            // Reset selection state
            this.currentSelection.level = 'none';
            this.currentSelection.element = null;
            this.currentSelection.data = null;
        }
    };
    
    // Check if we're in an iframe (preview mode)
    const isInIframe = window !== window.parent;
    
    if (isInIframe) {
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Add preview mode indicator to body
                if (document.body) {
                    document.body.setAttribute('data-preview-mode', 'pagebuilder');
                }
                initializePreviewHelpers();
            });
        } else {
            // Add preview mode indicator to body
            if (document.body) {
                document.body.setAttribute('data-preview-mode', 'pagebuilder');
            }
            initializePreviewHelpers();
        }
    }
    
    function initializePreviewHelpers() {
        console.log('🎨 Initializing Page Builder preview helpers coordinator');
        
        // First, map the page structure to DOM elements
        if (window.previewPageStructure) {
            mapStructureToDOM();
        } else {
            console.warn('⚠️ No page structure data found');
        }
        
        // Initialize specialized modules
        initializeModules();
        
        // Setup global functionality
        setupKeyboardShortcuts();
        setupResizeObserver();
        setupMessageListener();
        
        console.log('✅ Page Builder preview helpers coordinator initialized');
    }
    
    function initializeModules() {
        // Initialize page preview module
        if (window.PageBuilderPagePreview) {
            window.PageBuilderPagePreview.init(sharedState);
        } else {
            console.warn('⚠️ PageBuilderPagePreview module not loaded');
        }
    }
    
    function mapStructureToDOM() {
        // The universal components already provide the data attributes we need!
        // We just need to enhance them for Page Builder preview functionality
        
        const structure = window.previewPageStructure;
        console.log('🗺️ Using existing universal component data attributes for Page Builder...');
        
        // Page data attributes are now provided server-side in the HTML
        // No need for client-side injection since PageBuilderController adds them to the page container
        console.log(`📄 Enhanced page "${structure.page.title}" (ID: ${structure.page.id}, Template: ${structure.page.template})`);
        
        // Enhance sections with preview data
        const sections = document.querySelectorAll('section[data-section-id]');
        sections.forEach((section, idx) => {
            const sectionId = section.getAttribute('data-section-id');
            const structureSection = structure.sections.find(s => s.id == sectionId);
            
            if (structureSection) {
                section.setAttribute('data-preview-section', structureSection.id);
                section.setAttribute('data-section-name', structureSection.name);
                section.setAttribute('data-preview-type', 'section'); // Add type for page selection
                
                console.log(`📦 Enhanced section "${structureSection.name}" (ID: ${structureSection.id})`);
            }
        });
        
        // Enhance widgets with preview data
        const widgets = document.querySelectorAll('div[data-page-section-widget-id]');
        widgets.forEach((widget, idx) => {
            const instanceId = widget.getAttribute('data-page-section-widget-id');
            const widgetId = widget.getAttribute('data-widget-id');
            
            // Find this widget in the structure
            let structureWidget = null;
            let sectionId = null;
            
            structure.sections.forEach(section => {
                const foundWidget = section.widgets.find(w => w.id == instanceId);
                if (foundWidget) {
                    structureWidget = foundWidget;
                    sectionId = section.id;
                }
            });
            
            if (structureWidget) {
                // Add preview-specific attributes using the CORRECT PageSectionWidget ID
                widget.setAttribute('data-preview-widget', instanceId); // This is the key fix!
                widget.setAttribute('data-widget-instance', instanceId);
                widget.setAttribute('data-section-id', sectionId);
                widget.setAttribute('data-widget-name', structureWidget.name);
                widget.setAttribute('data-widget-icon', structureWidget.icon || 'ri-puzzle-line');
                
                console.log(`🎯 Enhanced widget "${structureWidget.name}" (instance: ${instanceId}, widget: ${widgetId})`);
            }
        });
        
        console.log(`✅ Enhanced ${widgets.length} widgets and ${sections.length} sections using existing data attributes`);
    }
    
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // ESC to deselect
            if (e.key === 'Escape') {
                sharedState.deselectAll();
            }
        });
        
        console.log('⌨️ Page Builder keyboard shortcuts enabled');
    }
    
    function setupResizeObserver() {
        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver(entries => {
                // Notify parent about size changes
                parent.postMessage({
                    type: 'preview-resized',
                    width: document.body.scrollWidth,
                    height: document.body.scrollHeight
                }, '*');
            });
            
            resizeObserver.observe(document.body);
        }
    }
    
    function setupMessageListener() {
        window.addEventListener('message', function(event) {
            // Verify message is from parent
            if (event.source !== window.parent) return;
            
            const { type, data } = event.data;
            
            switch (type) {
                case 'deselect-all':
                    sharedState.deselectAll();
                    break;
                    
                case 'device-changed':
                    handleDeviceChange(data.device, data.settings);
                    break;
                    
                case 'zoom-changed':
                    if (window.PageBuilderPagePreview) {
                        window.PageBuilderPagePreview.handleZoomChange(data.zoom);
                    }
                    break;
            }
        });
        
        console.log('📢 Page Builder message listener setup');
    }
    
    function handleDeviceChange(device, settings) {
        // Update body class for device-specific styles
        document.body.className = document.body.className.replace(/device-\w+/g, '');
        document.body.classList.add(`device-${device}`);
        
        console.log(`📱 Device changed to: ${device}`);
    }
    
    // Expose helper functions globally for parent window access
    window.pageBuilderPreviewHelpers = {
        deselectAll: function() {
            sharedState.deselectAll();
        },
        
        getCurrentSelection: function() {
            if (window.PageBuilderPagePreview && window.PageBuilderPagePreview.isPageSelected()) {
                return window.PageBuilderPagePreview.getCurrentSelection();
            }
            return sharedState.currentSelection;
        }
    };

    // Central Message Sender - All iframe actions go through here
    window.pageBuilderMessageSender = {
        /**
         * Send message to parent window with standardized format
         * @param {string} actionType - The action type identifier
         * @param {object} data - The data payload for the action
         * @param {object} options - Additional options (priority, requiresResponse, etc.)
         */
        sendToParent: function(actionType, data = {}, options = {}) {
            const message = {
                type: actionType,
                source: 'pagebuilder-iframe',
                timestamp: Date.now(),
                data: data,
                options: options
            };

            console.log(`📤 Sending message to parent: ${actionType}`, message);

            // Send to parent window
            if (window.parent && window.parent !== window) {
                window.parent.postMessage(message, '*');
            } else {
                console.warn('⚠️ No parent window found - message not sent');
            }
        },

        /**
         * Send page-related actions
         */
        page: {
            editRequested: function(pageData) {
                window.pageBuilderMessageSender.sendToParent('page-edit-requested', pageData);
            },
            
            addSectionRequested: function(pageData) {
                window.pageBuilderMessageSender.sendToParent('add-section-requested', pageData);
            },
            
            selected: function(pageData) {
                window.pageBuilderMessageSender.sendToParent('page-selected', pageData);
            },
            
            deselected: function() {
                window.pageBuilderMessageSender.sendToParent('page-deselected', {});
            }
        },

        /**
         * Send section-related actions
         */
        section: {
            editRequested: function(sectionData) {
                window.pageBuilderMessageSender.sendToParent('section-edit-requested', sectionData);
            },
            
            deleteRequested: function(sectionData) {
                window.pageBuilderMessageSender.sendToParent('section-delete-requested', sectionData);
            },
            
            moveRequested: function(sectionData) {
                window.pageBuilderMessageSender.sendToParent('section-move-requested', sectionData);
            },
            
            selected: function(sectionData) {
                window.pageBuilderMessageSender.sendToParent('section-selected', sectionData);
            }
        },

        /**
         * Send widget-related actions
         */
        widget: {
            addRequested: function(widgetData) {
                window.pageBuilderMessageSender.sendToParent('add-widget-requested', widgetData);
            },
            
            editRequested: function(widgetData) {
                window.pageBuilderMessageSender.sendToParent('widget-edit-requested', widgetData);
            },
            
            deleteRequested: function(widgetData) {
                window.pageBuilderMessageSender.sendToParent('widget-delete-requested', widgetData);
            },
            
            selected: function(widgetData) {
                window.pageBuilderMessageSender.sendToParent('widget-selected', widgetData);
            }
        }
    };
    
})();