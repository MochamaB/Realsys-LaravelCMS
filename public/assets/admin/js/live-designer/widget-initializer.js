/**
 * Universal Widget Initializer for Live Designer
 * Handles re-initialization of JavaScript widgets in GrapesJS iframe
 */
class WidgetInitializer {
    constructor(editor) {
        this.editor = editor;
        this.iframe = null;
        this.iframeDocument = null;
        this.iframeWindow = null;
        this.initializationQueue = [];
        this.initialized = false;
        
        console.log('🔧 WidgetInitializer created');
    }
    
    /**
     * Initialize the widget initializer
     */
    initialize() {
        if (this.initialized) return;
        
        // Wait for GrapesJS to be ready
        if (this.editor && typeof this.editor.on === 'function') {
            this.editor.on('load', () => {
                this.setupIframeReferences();
                this.setupContentObserver();
            });
            
            // Listen for content changes
            this.editor.on('component:add', () => {
                this.scheduleWidgetInitialization();
            });
            
            this.editor.on('component:update', () => {
                this.scheduleWidgetInitialization();
            });
        }
        
        this.initialized = true;
        console.log('🔧 WidgetInitializer initialized');
    }
    
    /**
     * Setup iframe references
     */
    setupIframeReferences() {
        try {
            const canvas = this.editor.Canvas;
            if (!canvas) return;
            
            this.iframe = canvas.getFrameEl();
            if (!this.iframe) return;
            
            this.iframeDocument = this.iframe.contentDocument || this.iframe.contentWindow.document;
            this.iframeWindow = this.iframe.contentWindow;
            
            console.log('🖼️ Iframe references established');
            
            // Initial widget initialization
            setTimeout(() => {
                this.initializeAllWidgets();
            }, 1000);
            
        } catch (error) {
            console.warn('⚠️ Failed to setup iframe references:', error);
        }
    }
    
    /**
     * Setup content observer to detect changes
     */
    setupContentObserver() {
        if (!this.iframeDocument) return;
        
        const observer = new MutationObserver((mutations) => {
            let shouldReinitialize = false;
            
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if added node contains widgets
                            if (this.containsWidgets(node)) {
                                shouldReinitialize = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldReinitialize) {
                this.scheduleWidgetInitialization();
            }
        });
        
        observer.observe(this.iframeDocument.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👁️ Content observer setup complete');
    }
    
    /**
     * Check if element contains widgets
     */
    containsWidgets(element) {
        const widgetSelectors = [
            '.widget-counter',
            '.widget-slider',
            '.nivoSlider',
            '.counter[data-count]',
            '[class*="widget-"]'
        ];
        
        return widgetSelectors.some(selector => {
            return element.matches && element.matches(selector) || 
                   element.querySelector && element.querySelector(selector);
        });
    }
    
    /**
     * Schedule widget initialization with debouncing
     */
    scheduleWidgetInitialization() {
        clearTimeout(this.initializationTimeout);
        this.initializationTimeout = setTimeout(() => {
            this.initializeAllWidgets();
        }, 500);
    }
    
    /**
     * Initialize all widgets in the iframe
     */
    initializeAllWidgets() {
        if (!this.iframeDocument || !this.iframeWindow) {
            console.warn('⚠️ Iframe not ready for widget initialization');
            return;
        }
        
        console.log('🚀 Starting widget initialization...');
        
        // Initialize counter widgets
        this.initializeCounterWidgets();
        
        // Initialize slider widgets
        this.initializeSliderWidgets();
        
        // Initialize any other custom widgets
        this.initializeCustomWidgets();
        
        // Trigger custom initialization events
        this.triggerCustomEvents();
        
        console.log('✅ Widget initialization complete');
    }
    
    /**
     * Initialize counter widgets
     */
    initializeCounterWidgets() {
        try {
            const counters = this.iframeDocument.querySelectorAll('.widget-counter .counter[data-count]');
            
            counters.forEach(counter => {
                // Skip if already animated
                if (counter.hasAttribute('data-animated')) {
                    counter.removeAttribute('data-animated');
                }
                
                const target = parseInt(counter.getAttribute('data-count'));
                const speed = parseInt(counter.getAttribute('data-speed')) || 2000;
                const increment = target / speed * 10;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        if (current > target) current = target;
                        counter.innerText = Math.ceil(current);
                        setTimeout(updateCounter, 10);
                    } else {
                        counter.innerText = target;
                        counter.setAttribute('data-animated', 'true');
                    }
                };
                
                // Start animation immediately in preview mode
                updateCounter();
            });
            
            if (counters.length > 0) {
                console.log(`🔢 Initialized ${counters.length} counter widgets`);
            }
            
        } catch (error) {
            console.warn('⚠️ Error initializing counter widgets:', error);
        }
    }
    
    /**
     * Initialize slider widgets
     */
    initializeSliderWidgets() {
        try {
            const sliders = this.iframeDocument.querySelectorAll('.widget-slider .nivoSlider');
            
            sliders.forEach(slider => {
                const sliderId = slider.id;
                
                // Check if jQuery is available in iframe
                const jQuery = this.iframeWindow.jQuery || this.iframeWindow.$;
                
                if (jQuery && jQuery.fn.nivoSlider) {
                    // Destroy existing slider if it exists
                    if (jQuery('#' + sliderId).data('nivoslider')) {
                        jQuery('#' + sliderId).data('nivoslider').destroy();
                    }
                    
                    // Get configuration from window or use defaults
                    const config = this.iframeWindow.sliderConfigs && this.iframeWindow.sliderConfigs[sliderId] 
                        ? this.iframeWindow.sliderConfigs[sliderId] 
                        : {};
                    
                    const defaultConfig = {
                        effect: 'random',
                        slices: 15,
                        boxCols: 8,
                        boxRows: 4,
                        animSpeed: 500,
                        pauseTime: 3000,
                        startSlide: 0,
                        directionNav: true,
                        controlNav: true,
                        pauseOnHover: true,
                        manualAdvance: false,
                        prevText: 'Prev',
                        nextText: 'Next'
                    };
                    
                    const finalConfig = { ...defaultConfig, ...config };
                    
                    // Initialize slider
                    jQuery('#' + sliderId).nivoSlider(finalConfig);
                }
            });
            
            if (sliders.length > 0) {
                console.log(`🎠 Initialized ${sliders.length} slider widgets`);
            }
            
        } catch (error) {
            console.warn('⚠️ Error initializing slider widgets:', error);
        }
    }
    
    /**
     * Initialize custom widgets (extensible for any widget type)
     */
    initializeCustomWidgets() {
        try {
            // Look for any elements with data-widget-init attribute
            const customWidgets = this.iframeDocument.querySelectorAll('[data-widget-init]');
            
            customWidgets.forEach(widget => {
                const initFunction = widget.getAttribute('data-widget-init');
                
                if (initFunction && this.iframeWindow[initFunction]) {
                    try {
                        this.iframeWindow[initFunction](widget);
                    } catch (error) {
                        console.warn(`⚠️ Error calling custom widget init function ${initFunction}:`, error);
                    }
                }
            });
            
            if (customWidgets.length > 0) {
                console.log(`🔧 Initialized ${customWidgets.length} custom widgets`);
            }
            
        } catch (error) {
            console.warn('⚠️ Error initializing custom widgets:', error);
        }
    }
    
    /**
     * Trigger custom events for widget initialization
     */
    triggerCustomEvents() {
        try {
            // Trigger DOMContentLoaded-like event for widgets
            const event = new this.iframeWindow.Event('widgetsReinitialized', {
                bubbles: true,
                cancelable: true
            });
            
            this.iframeDocument.dispatchEvent(event);
            
            // Also trigger a custom event that widgets can listen to
            const customEvent = new this.iframeWindow.CustomEvent('liveDesignerWidgetInit', {
                bubbles: true,
                cancelable: true,
                detail: { source: 'live-designer' }
            });
            
            this.iframeDocument.dispatchEvent(customEvent);
            
            console.log('📡 Custom widget events triggered');
            
        } catch (error) {
            console.warn('⚠️ Error triggering custom events:', error);
        }
    }
    
    /**
     * Force reinitialize all widgets (public method)
     */
    reinitializeWidgets() {
        console.log('🔄 Force reinitializing widgets...');
        this.initializeAllWidgets();
    }
    
    /**
     * Add custom widget initializer
     */
    addCustomInitializer(selector, initFunction) {
        this.initializationQueue.push({
            selector: selector,
            init: initFunction
        });
        
        console.log(`➕ Added custom initializer for: ${selector}`);
    }
    
    /**
     * Destroy widget initializer
     */
    destroy() {
        clearTimeout(this.initializationTimeout);
        this.initialized = false;
        console.log('🗑️ WidgetInitializer destroyed');
    }
}

// Export for use in other modules
window.WidgetInitializer = WidgetInitializer;
