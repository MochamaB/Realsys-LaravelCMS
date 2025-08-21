/**
 * Preview Helper Functions
 * 
 * JavaScript helpers that get injected into the preview iframe
 * These functions enable widget selection and interaction in the preview
 */

// Initialize preview helpers when document is ready
(function() {
    'use strict';
    
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
        console.log('🎨 Initializing preview helpers');
        
        // Setup widget interactions
        setupWidgetInteractions();
        
        // Setup section interactions
        setupSectionInteractions();
        
        // Setup keyboard shortcuts
        setupKeyboardShortcuts();
        
        // Setup resize observer for responsive updates
        setupResizeObserver();
        
        // Listen for messages from parent
        setupMessageListener();
        
        console.log('✅ Preview helpers initialized');
    }
    
    function setupWidgetInteractions() {
        const widgets = document.querySelectorAll('[data-widget-id]');
        
        widgets.forEach(widget => {
            // Make widget clickable
            widget.style.cursor = 'pointer';
            widget.setAttribute('tabindex', '0'); // Make focusable
            
            // Click handler
            widget.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                selectWidget(this.dataset.widgetId);
            });
            
            // Keyboard handler
            widget.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectWidget(this.dataset.widgetId);
                }
            });
            
            // Hover effects
            widget.addEventListener('mouseenter', function() {
                if (!this.classList.contains('preview-highlighted')) {
                    this.style.outline = '2px dashed #0d6efd';
                    this.style.outlineOffset = '2px';
                }
            });
            
            widget.addEventListener('mouseleave', function() {
                if (!this.classList.contains('preview-highlighted')) {
                    this.style.outline = '';
                    this.style.outlineOffset = '';
                }
            });
            
            // Focus effects
            widget.addEventListener('focus', function() {
                this.style.outline = '3px solid #0d6efd';
                this.style.outlineOffset = '2px';
            });
            
            widget.addEventListener('blur', function() {
                if (!this.classList.contains('preview-highlighted')) {
                    this.style.outline = '';
                    this.style.outlineOffset = '';
                }
            });
        });
        
        console.log(`🎯 Setup interactions for ${widgets.length} widgets`);
    }
    
    function setupSectionInteractions() {
        const sections = document.querySelectorAll('[data-section-id]');
        
        sections.forEach(section => {
            section.addEventListener('click', function(e) {
                // Only trigger if clicking the section itself, not a child widget
                if (e.target === this || !e.target.closest('[data-widget-id]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    selectSection(this.dataset.sectionId);
                }
            });
            
            // Add visual feedback for empty sections
            if (section.children.length === 0 || !section.querySelector('[data-widget-id]')) {
                section.classList.add('empty-section');
            }
        });
        
        console.log(`📦 Setup interactions for ${sections.length} sections`);
    }
    
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // ESC to deselect
            if (e.key === 'Escape') {
                deselectAll();
            }
            
            // Arrow keys to navigate between widgets
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                const highlighted = document.querySelector('.preview-highlighted');
                if (highlighted) {
                    e.preventDefault();
                    navigateWidgets(highlighted, e.key);
                }
            }
            
            // Tab to focus next widget
            if (e.key === 'Tab' && !e.shiftKey) {
                const widgets = Array.from(document.querySelectorAll('[data-widget-id]'));
                const currentIndex = widgets.findIndex(w => w.classList.contains('preview-highlighted'));
                
                if (currentIndex >= 0 && currentIndex < widgets.length - 1) {
                    e.preventDefault();
                    selectWidget(widgets[currentIndex + 1].dataset.widgetId);
                    widgets[currentIndex + 1].focus();
                }
            }
        });
        
        console.log('⌨️ Keyboard shortcuts enabled');
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
                    highlightWidget(data.widgetId);
                    break;
                    
                case 'deselect-all':
                    deselectAll();
                    break;
                    
                case 'device-changed':
                    handleDeviceChange(data.device, data.settings);
                    break;
                    
                case 'scroll-to-widget':
                    scrollToWidget(data.widgetId);
                    break;
                    
                case 'update-widget-state':
                    updateWidgetState(data.widgetId, data.state);
                    break;
            }
        });
        
        console.log('📢 Message listener setup');
    }
    
    function selectWidget(widgetId) {
        // Clear previous selections
        deselectAll();
        
        // Highlight selected widget
        highlightWidget(widgetId);
        
        // Notify parent
        parent.postMessage({
            type: 'widget-selected',
            data: { widgetId: widgetId }
        }, '*');
        
        console.log(`✓ Widget selected: ${widgetId}`);
    }
    
    function selectSection(sectionId) {
        // Clear previous selections
        deselectAll();
        
        // Notify parent
        parent.postMessage({
            type: 'section-selected',
            data: { sectionId: sectionId }
        }, '*');
        
        console.log(`✓ Section selected: ${sectionId}`);
    }
    
    function highlightWidget(widgetId) {
        const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
        if (widget) {
            // Remove existing highlights
            document.querySelectorAll('.preview-highlighted').forEach(el => {
                el.classList.remove('preview-highlighted');
                el.style.outline = '';
                el.style.outlineOffset = '';
            });
            
            // Add highlight to selected widget
            widget.classList.add('preview-highlighted');
            
            // Scroll into view
            widget.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
    }
    
    function deselectAll() {
        document.querySelectorAll('.preview-highlighted').forEach(el => {
            el.classList.remove('preview-highlighted');
            el.style.outline = '';
            el.style.outlineOffset = '';
        });
    }
    
    function navigateWidgets(currentWidget, direction) {
        const widgets = Array.from(document.querySelectorAll('[data-widget-id]'));
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
            selectWidget(widgets[nextIndex].dataset.widgetId);
            widgets[nextIndex].focus();
        }
    }
    
    function handleDeviceChange(device, settings) {
        // Update body class for device-specific styles
        document.body.className = document.body.className.replace(/device-\w+/g, '');
        document.body.classList.add(`device-${device}`);
        
        console.log(`📱 Device changed to: ${device}`);
    }
    
    function scrollToWidget(widgetId) {
        const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
        if (widget) {
            widget.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
    }
    
    function updateWidgetState(widgetId, state) {
        const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
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
    }
    
    // Expose helper functions globally for parent window access
    window.previewHelpers = {
        highlightWidget,
        deselectAll,
        selectWidget,
        scrollToWidget,
        updateWidgetState,
        
        // Utility functions
        getSelectedWidget() {
            const highlighted = document.querySelector('.preview-highlighted');
            return highlighted ? highlighted.dataset.widgetId : null;
        },
        
        getAllWidgets() {
            return Array.from(document.querySelectorAll('[data-widget-id]'))
                .map(widget => widget.dataset.widgetId);
        },
        
        getWidgetInfo(widgetId) {
            const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
            if (widget) {
                const rect = widget.getBoundingClientRect();
                return {
                    id: widgetId,
                    position: { x: rect.left, y: rect.top },
                    size: { width: rect.width, height: rect.height },
                    visible: rect.top >= 0 && rect.bottom <= window.innerHeight
                };
            }
            return null;
        }
    };
    
})();