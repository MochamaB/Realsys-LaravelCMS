/**
 * Preview Helper Functions - Main Coordinator
 * 
 * Main coordinator that manages specialized preview modules:
 * - widget-preview.js: Widget interactions and selection
 * - section-preview.js: Section interactions, drag & drop, and toolbars
 * - page-preview.js: Page-level interactions and selection
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
            if (window.PagePreview && window.PagePreview.getCurrentSelection().level === 'page-selected') {
                window.PagePreview.deselectPage();
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
                    document.body.setAttribute('data-preview-mode', 'true');
                }
                initializePreviewHelpers();
            });
        } else {
            // Add preview mode indicator to body
            if (document.body) {
                document.body.setAttribute('data-preview-mode', 'true');
            }
            initializePreviewHelpers();
        }
    }
    
    function initializePreviewHelpers() {
        console.log('🎨 Initializing preview helpers coordinator');
        
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
        
        console.log('✅ Preview helpers coordinator initialized');
    }
    
    function initializeModules() {
        // Initialize widget preview module
        if (window.WidgetPreview) {
            window.WidgetPreview.init(sharedState);
        } else {
            console.warn('⚠️ WidgetPreview module not loaded');
        }
        
        // Initialize section preview module
        if (window.SectionPreview) {
            window.SectionPreview.init(sharedState);
        } else {
            console.warn('⚠️ SectionPreview module not loaded');
        }
        
        // Initialize page preview module
        if (window.PagePreview) {
            window.PagePreview.init(sharedState);
        } else {
            console.warn('⚠️ PagePreview module not loaded');
        }
    }
    
    function mapStructureToDOM() {
        // The universal components already provide the data attributes we need!
        // We just need to enhance them for preview functionality
        
        const structure = window.previewPageStructure;
        console.log('🗺️ Using existing universal component data attributes...');
        
        // Page data attributes are now provided server-side in the HTML
        // No need for client-side injection since LivePreviewController adds them to the page container
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
    
    // Widget and section interactions are now handled by their respective modules
    
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // ESC to deselect
            if (e.key === 'Escape') {
                sharedState.deselectAll();
            }
            
            // Arrow keys to navigate between widgets
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                const highlighted = document.querySelector('.preview-highlighted[data-preview-widget]');
                if (highlighted && window.WidgetPreview) {
                    e.preventDefault();
                    window.WidgetPreview.navigateWidgets(highlighted, e.key);
                }
            }
            
            // Tab to focus next widget
            if (e.key === 'Tab' && !e.shiftKey) {
                const widgets = Array.from(document.querySelectorAll('[data-preview-widget]'));
                const currentIndex = widgets.findIndex(w => w.classList.contains('preview-highlighted'));
                
                if (currentIndex >= 0 && currentIndex < widgets.length - 1 && window.WidgetPreview) {
                    e.preventDefault();
                    const widget = widgets[currentIndex + 1];
                    window.WidgetPreview.selectWidget(
                        widget.dataset.previewWidget,
                        widget.dataset.widgetId,
                        widget.dataset.sectionId,
                        widget.dataset.widgetName
                    );
                    widget.focus();
                }
            }
        });
        
        console.log('⌨️ Global keyboard shortcuts enabled');
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
                case 'highlight-widget':
                    if (window.WidgetPreview) {
                        window.WidgetPreview.highlightWidget(data.widgetId);
                    }
                    break;
                    
                case 'deselect-all':
                    sharedState.deselectAll();
                    break;
                    
                case 'device-changed':
                    handleDeviceChange(data.device, data.settings);
                    break;
                    
                case 'scroll-to-widget':
                    if (window.WidgetPreview) {
                        window.WidgetPreview.scrollToWidget(data.widgetId);
                    }
                    break;
                    
                case 'update-widget-state':
                    if (window.WidgetPreview) {
                        window.WidgetPreview.updateWidgetState(data.widgetId, data.state);
                    }
                    break;
                    
                case 'zoom-changed':
                    if (window.PagePreview) {
                        window.PagePreview.handleZoomChange(data.zoom);
                    }
                    break;
            }
        });
        
        console.log('📢 Global message listener setup');
    }
    
    // Selection functions are now handled by their respective modules
    
    // Highlighting functions are now handled by their respective modules
    
    // Toolbar creation is now handled by the section preview module
    
    // Deselection is now handled by the shared state object
    
    // Widget navigation is now handled by the widget preview module
    
    function handleDeviceChange(device, settings) {
        // Update body class for device-specific styles
        document.body.className = document.body.className.replace(/device-\w+/g, '');
        document.body.classList.add(`device-${device}`);
        
        console.log(`📱 Device changed to: ${device}`);
    }
    
    // Expose helper functions globally for parent window access
    window.previewHelpers = {
        // Delegate to appropriate modules
        highlightWidget: function(instanceId) {
            if (window.WidgetPreview) {
                window.WidgetPreview.highlightWidget(instanceId);
            }
        },
        
        highlightSection: function(sectionId) {
            if (window.SectionPreview) {
                window.SectionPreview.highlightSection(sectionId);
            }
        },
        
        deselectAll: function() {
            sharedState.deselectAll();
        },
        
        selectWidget: function(instanceId, widgetId, sectionId, widgetName) {
            if (window.WidgetPreview) {
                window.WidgetPreview.selectWidget(instanceId, widgetId, sectionId, widgetName);
            }
        },
        
        selectSection: function(sectionId, sectionName) {
            if (window.SectionPreview) {
                window.SectionPreview.selectSection(sectionId, sectionName);
            }
        },
        
        scrollToWidget: function(instanceId) {
            if (window.WidgetPreview) {
                window.WidgetPreview.scrollToWidget(instanceId);
            }
        },
        
        updateWidgetState: function(instanceId, state) {
            if (window.WidgetPreview) {
                window.WidgetPreview.updateWidgetState(instanceId, state);
            }
        },
        
        // Utility functions
        getSelectedWidget: function() {
            return window.WidgetPreview ? window.WidgetPreview.getSelectedWidget() : null;
        },
        
        getSelectedSection: function() {
            return window.SectionPreview ? window.SectionPreview.getSelectedSection() : null;
        },
        
        getAllWidgets: function() {
            return window.WidgetPreview ? window.WidgetPreview.getAllWidgets() : [];
        },
        
        getAllSections: function() {
            return window.SectionPreview ? window.SectionPreview.getAllSections() : [];
        },
        
        getWidgetInfo: function(instanceId) {
            return window.WidgetPreview ? window.WidgetPreview.getWidgetInfo(instanceId) : null;
        },
        
        getSectionInfo: function(sectionId) {
            return window.SectionPreview ? window.SectionPreview.getSectionInfo(sectionId) : null;
        },
        
        getCurrentSelection: function() {
            if (window.PagePreview && window.PagePreview.isPageSelected()) {
                return window.PagePreview.getCurrentSelection();
            }
            return sharedState.currentSelection;
        }
    };
    
})();