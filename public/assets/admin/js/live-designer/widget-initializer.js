/**
 * Enhanced Widget Initializer for Live Designer
 * Handles re-initialization of JavaScript widgets in GrapesJS iframe
 * with proper asset loading and dependency management
 */
class WidgetInitializer {
    constructor(editor) {
        this.editor = editor;
        this.iframe = null;
        this.iframeDocument = null;
        this.iframeWindow = null;
        this.initializationQueue = [];
        this.initialized = false;
        this.loadedAssets = new Set();
        this.widgetConfigurations = new Map();
        
        console.log('🔧 Enhanced WidgetInitializer created');
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
        console.log('🔧 Enhanced WidgetInitializer initialized');
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
     * Initialize all widgets in the iframe with proper dependency loading
     */
    async initializeAllWidgets() {
        // Enhanced iframe readiness check
        if (!await this.waitForIframeReady()) {
            console.warn('⚠️ Iframe not ready for widget initialization after waiting');
            return;
        }
        
        console.log('🚀 Starting enhanced widget initialization...');
        
        try {
            // Step 1: Extract widget configurations from HTML content
            this.extractWidgetConfigurations();
            
            // Step 2: Load required assets with dependency chain
            await this.loadRequiredAssets();
            
            // Step 3: Initialize counter widgets (no external dependencies)
            this.initializeCounterWidgets();
            
            // Step 4: Initialize slider widgets (requires jQuery + Nivo Slider)
            await this.initializeSliderWidgetsEnhanced();
            
            // Step 5: Initialize any other custom widgets
            this.initializeCustomWidgets();
            
            // Step 6: Trigger custom initialization events
            this.triggerCustomEvents();
            
            console.log('✅ Enhanced widget initialization complete');
            
        } catch (error) {
            console.error('❌ Widget initialization failed:', error);
        }
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
     * Enhanced slider widget initialization with proper dependency loading
     */
    async initializeSliderWidgetsEnhanced() {
        try {
            const sliders = this.iframeDocument.querySelectorAll('.nivoSlider');
            
            if (sliders.length === 0) {
                return;
            }
            
            console.log(`🎠 Found ${sliders.length} slider widgets, ensuring dependencies...`);
            
            // Ensure jQuery is loaded
            await this.ensureJQueryInIframe();
            
            // Ensure Nivo Slider plugin is loaded
            await this.ensureNivoSliderInIframe();
            
            // Wait a bit for assets to be fully processed
            await new Promise(resolve => setTimeout(resolve, 100));
            
            const jQuery = this.iframeWindow.jQuery || this.iframeWindow.$;
            
            if (!jQuery || !jQuery.fn.nivoSlider) {
                console.warn('⚠️ jQuery or Nivo Slider not available after loading attempts');
                return;
            }
            
            sliders.forEach(slider => {
                const sliderId = slider.id;
                
                if (!sliderId) {
                    console.warn('⚠️ Slider missing ID, skipping:', slider);
                    return;
                }
                
                try {
                    // Destroy existing slider if it exists
                    const existingSlider = jQuery('#' + sliderId).data('nivoslider');
                    if (existingSlider) {
                        existingSlider.destroy();
                    }
                    
                    // Get configuration from injected configs or iframe window
                    let config = this.widgetConfigurations.get(sliderId) || {};
                    
                    // Also check if it exists in iframe window (from @push('scripts'))
                    if (this.iframeWindow.sliderConfigs && this.iframeWindow.sliderConfigs[sliderId]) {
                        config = { ...config, ...this.iframeWindow.sliderConfigs[sliderId] };
                    }
                    
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
                    
                    console.log(`✅ Initialized slider: ${sliderId}`);
                    
                } catch (sliderError) {
                    console.warn(`⚠️ Error initializing slider ${sliderId}:`, sliderError);
                }
            });
            
            console.log(`🎠 Completed slider widget initialization`);
            
        } catch (error) {
            console.warn('⚠️ Error in enhanced slider initialization:', error);
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
    async reinitializeWidgets() {
        console.log('🔄 Force reinitializing widgets...');
        
        // Use the enhanced iframe readiness check
        if (!await this.waitForIframeReady()) {
            console.warn('⚠️ Iframe not ready for reinitializing widgets after waiting');
            return;
        }
        
        await this.initializeAllWidgets();
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
     * Extract widget configurations from HTML content
     */
    extractWidgetConfigurations() {
        try {
            // Look for script tags with widget configurations
            const scripts = this.iframeDocument.querySelectorAll('script');
            
            scripts.forEach(script => {
                const content = script.textContent || script.innerHTML;
                
                // Look for sliderConfigs pattern
                const sliderConfigMatch = content.match(/window\.sliderConfigs\s*=\s*window\.sliderConfigs\s*\|\|\s*\{\};([\s\S]*?)(?=<\/script>|$)/i);
                if (sliderConfigMatch) {
                    try {
                        // Execute the configuration in iframe context
                        this.iframeWindow.eval(content);
                        console.log('✅ Injected slider configurations into iframe');
                    } catch (evalError) {
                        console.warn('⚠️ Error executing slider config script:', evalError);
                    }
                }
            });
            
            // Store configurations locally as backup
            if (this.iframeWindow.sliderConfigs) {
                Object.entries(this.iframeWindow.sliderConfigs).forEach(([sliderId, config]) => {
                    this.widgetConfigurations.set(sliderId, config);
                });
                console.log(`📋 Extracted ${Object.keys(this.iframeWindow.sliderConfigs).length} slider configurations`);
            }
            
        } catch (error) {
            console.warn('⚠️ Error extracting widget configurations:', error);
        }
    }
    
    /**
     * Load required assets for widgets
     */
    async loadRequiredAssets() {
        try {
            console.log('📦 Loading required widget assets...');
            
            // Load theme-specific assets that widgets need
            const requiredAssets = {
                js: [
                    '/themes/miata/js/jquery-1.12.4.min.js',
                    '/themes/miata/lib/js/jquery.nivo.slider.js'
                ],
                css: [
                    '/themes/miata/lib/css/nivo-slider.css',
                    '/themes/miata/lib/css/default/default.css'
                ]
            };
            
            // Load CSS assets
            for (const cssFile of requiredAssets.css) {
                await this.loadCSSIntoIframe(cssFile);
            }
            
            // Load JS assets in order
            for (const jsFile of requiredAssets.js) {
                await this.loadJSIntoIframe(jsFile);
            }
            
            console.log('✅ Required widget assets loaded');
            
        } catch (error) {
            console.warn('⚠️ Error loading required assets:', error);
        }
    }
    
    /**
     * Ensure jQuery is loaded in iframe
     */
    async ensureJQueryInIframe() {
        if (this.iframeWindow.jQuery || this.iframeWindow.$) {
            console.log('✅ jQuery already available in iframe');
            return;
        }
        
        console.log('📦 Loading jQuery into iframe...');
        await this.loadJSIntoIframe('/themes/miata/js/jquery-1.12.4.min.js');
        
        // Wait for jQuery to be available
        await this.waitForCondition(() => !!(this.iframeWindow.jQuery || this.iframeWindow.$), 5000);
    }
    
    /**
     * Ensure Nivo Slider plugin is loaded in iframe
     */
    async ensureNivoSliderInIframe() {
        const jQuery = this.iframeWindow.jQuery || this.iframeWindow.$;
        
        if (jQuery && jQuery.fn.nivoSlider) {
            console.log('✅ Nivo Slider already available in iframe');
            return;
        }
        
        if (!jQuery) {
            await this.ensureJQueryInIframe();
        }
        
        console.log('📦 Loading Nivo Slider into iframe...');
        await this.loadCSSIntoIframe('/themes/miata/lib/css/nivo-slider.css');
        await this.loadCSSIntoIframe('/themes/miata/lib/css/default/default.css');
        await this.loadJSIntoIframe('/themes/miata/lib/js/jquery.nivo.slider.js');
        
        // Wait for Nivo Slider to be available
        await this.waitForCondition(() => !!(this.iframeWindow.jQuery && this.iframeWindow.jQuery.fn.nivoSlider), 5000);
    }
    
    /**
     * Load JavaScript file into iframe
     */
    async loadJSIntoIframe(jsPath) {
        return new Promise((resolve, reject) => {
            if (this.loadedAssets.has(jsPath)) {
                resolve();
                return;
            }
            
            try {
                const script = this.iframeDocument.createElement('script');
                script.type = 'text/javascript';
                script.src = jsPath;
                
                const timeout = setTimeout(() => {
                    console.warn(`⚠️ Timeout loading JS into iframe: ${jsPath}`);
                    this.loadedAssets.add(jsPath); // Mark as attempted
                    resolve(); // Don't reject, continue with other assets
                }, 10000); // 10 second timeout
                
                script.onload = () => {
                    clearTimeout(timeout);
                    console.log(`✅ Loaded JS into iframe: ${jsPath}`);
                    this.loadedAssets.add(jsPath);
                    resolve();
                };
                
                script.onerror = (error) => {
                    clearTimeout(timeout);
                    console.warn(`⚠️ Failed to load JS into iframe: ${jsPath}`, error);
                    this.loadedAssets.add(jsPath); // Mark as attempted
                    resolve(); // Don't reject, continue with other assets
                };
                
                this.iframeDocument.head.appendChild(script);
                
            } catch (error) {
                console.warn(`⚠️ Error creating script element for: ${jsPath}`, error);
                this.loadedAssets.add(jsPath); // Mark as attempted
                resolve(); // Don't reject, continue with other assets
            }
        });
    }
    
    /**
     * Load CSS file into iframe
     */
    async loadCSSIntoIframe(cssPath) {
        return new Promise((resolve) => {
            if (this.loadedAssets.has(cssPath)) {
                resolve();
                return;
            }
            
            try {
                const link = this.iframeDocument.createElement('link');
                link.rel = 'stylesheet';
                link.type = 'text/css';
                link.href = cssPath;
                
                const timeout = setTimeout(() => {
                    console.warn(`⚠️ Timeout loading CSS into iframe: ${cssPath}`);
                    this.loadedAssets.add(cssPath); // Mark as attempted
                    resolve(); // Don't reject, continue with other assets
                }, 10000); // 10 second timeout
                
                link.onload = () => {
                    clearTimeout(timeout);
                    console.log(`✅ Loaded CSS into iframe: ${cssPath}`);
                    this.loadedAssets.add(cssPath);
                    resolve();
                };
                
                link.onerror = (error) => {
                    clearTimeout(timeout);
                    console.warn(`⚠️ Failed to load CSS into iframe: ${cssPath}`, error);
                    this.loadedAssets.add(cssPath); // Mark as attempted
                    resolve(); // Don't reject, continue with other assets
                };
                
                this.iframeDocument.head.appendChild(link);
                
            } catch (error) {
                console.warn(`⚠️ Error creating link element for: ${cssPath}`, error);
                this.loadedAssets.add(cssPath); // Mark as attempted
                resolve(); // Don't reject, continue with other assets
            }
        });
    }
    
    /**
     * Wait for iframe to be fully ready for widget initialization
     */
    async waitForIframeReady(timeout = 10000) {
        return new Promise((resolve) => {
            const startTime = Date.now();
            
            const checkIframeReady = async () => {
                try {
                    // First check basic iframe references
                    if (!this.iframe || !this.iframeDocument || !this.iframeWindow) {
                        this.setupIframeReferences();
                    }
                    
                    // If still no references, continue checking
                    if (!this.iframe || !this.iframeDocument || !this.iframeWindow) {
                        if (Date.now() - startTime >= timeout) {
                            console.warn('⚠️ Iframe references not established within timeout');
                            resolve(false);
                            return;
                        }
                        setTimeout(checkIframeReady, 100);
                        return;
                    }
                    
                    // Check if iframe content is loaded
                    if (this.iframeDocument.readyState !== 'complete') {
                        if (Date.now() - startTime >= timeout) {
                            console.warn('⚠️ Iframe document not ready within timeout');
                            resolve(false);
                            return;
                        }
                        setTimeout(checkIframeReady, 100);
                        return;
                    }
                    
                    // Check if body exists and is accessible
                    if (!this.iframeDocument.body) {
                        if (Date.now() - startTime >= timeout) {
                            console.warn('⚠️ Iframe body not available within timeout');
                            resolve(false);
                            return;
                        }
                        setTimeout(checkIframeReady, 100);
                        return;
                    }
                    
                    // Check if there's any content (indicating GrapesJS has loaded content)
                    const hasContent = this.iframeDocument.body.children.length > 0 || 
                                     this.iframeDocument.body.textContent.trim().length > 0;
                    
                    if (!hasContent) {
                        if (Date.now() - startTime >= timeout) {
                            console.warn('⚠️ Iframe has no content within timeout');
                            resolve(false);
                            return;
                        }
                        setTimeout(checkIframeReady, 100);
                        return;
                    }
                    
                    console.log('✅ Iframe is ready for widget initialization');
                    resolve(true);
                    
                } catch (error) {
                    if (Date.now() - startTime >= timeout) {
                        console.warn('⚠️ Error checking iframe readiness:', error);
                        resolve(false);
                        return;
                    }
                    setTimeout(checkIframeReady, 100);
                }
            };
            
            checkIframeReady();
        });
    }

    /**
     * Wait for a condition with timeout
     */
    async waitForCondition(conditionFn, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            const checkCondition = () => {
                try {
                    if (conditionFn()) {
                        resolve();
                        return;
                    }
                } catch (error) {
                    // Continue checking
                }
                
                if (Date.now() - startTime >= timeout) {
                    reject(new Error(`Condition not met within ${timeout}ms`));
                    return;
                }
                
                setTimeout(checkCondition, 50);
            };
            
            checkCondition();
        });
    }
    
    /**
     * Destroy widget initializer
     */
    destroy() {
        clearTimeout(this.initializationTimeout);
        this.initialized = false;
        console.log('🗑️ Enhanced WidgetInitializer destroyed');
    }
}

// Export for use in other modules
window.WidgetInitializer = WidgetInitializer;

console.log('📦 Enhanced Widget Initializer module loaded');