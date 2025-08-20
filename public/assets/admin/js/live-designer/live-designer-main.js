/**
 * Live Designer Main - GrapesJS Integration
 * 
 * This is the main entry point for the GrapesJS Live Designer system.
 * It initializes the editor, manages the API integration, and coordinates
 * between the various manager components.
 */

class LiveDesignerMain {
    constructor(options = {}) {
        this.options = {
            pageId: null,
            apiBaseUrl: '/admin/api/live-designer',
            csrfToken: '',
            pageData: null,
            canvasSelector: '#gjs-editor',
            loadingSelector: '#canvas-loading',
            ...options
        };
        
        this.editor = null;
        this.api = null;
        this.componentManager = null;
        this.canvasManager = null;
        this.sidebarManager = null;
        this.enhancedWidgets = null;
        
        this.isInitialized = false;
        this.isLoading = false;
        this.currentDevice = 'desktop';
        
        this.init();
    }
    
    /**
     * Initialize the Live Designer
     */
    async init() {
        try {
            this.showLoading('Initializing Live Designer...');
            
            // Initialize API
            this.api = new LiveDesignerAPI(this.options.apiBaseUrl, this.options.csrfToken);
            
            // Initialize GrapesJS Editor
            await this.initializeEditor();
            
            // Initialize managers
            this.initializeManagers();
            
            // Load page content
            await this.loadPageContent();
            
            // Setup event listeners
            this.setupEventListeners();
            
            this.isInitialized = true;
            this.hideLoading();
            
            console.log('✅ Live Designer initialized successfully');
            this.showMessage('Live Designer ready!', 'success');
            
        } catch (error) {
            console.error('❌ Failed to initialize Live Designer:', error);
            this.showMessage('Failed to initialize Live Designer', 'error');
            this.hideLoading();
        }
    }
    
    /**
     * Initialize GrapesJS Editor
     */
    async initializeEditor() {
        // Check if GrapesJS is available
        if (typeof grapesjs === 'undefined') {
            console.warn('⚠️ GrapesJS not loaded, using placeholder mode');
            this.initializePlaceholderEditor();
            return;
        }
        
        const editorConfig = {
            container: this.options.canvasSelector,
            height: '100%',
            width: 'auto',
            
            // Storage configuration (disabled for testing)
            storageManager: false,
            
            // Asset manager configuration
            assetManager: {
                upload: `${this.options.apiBaseUrl}/assets/upload`,
                uploadName: 'files',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            },
            
            // Style manager configuration
            styleManager: {
                appendTo: '#style-manager',
                sectors: [
                    {
                        name: 'Dimension',
                        open: false,
                        buildProps: ['width', 'min-height', 'padding'],
                        properties: [
                            'width',
                            'height',
                            'max-width',
                            'min-height',
                            'margin',
                            'padding'
                        ]
                    },
                    {
                        name: 'Typography',
                        open: false,
                        buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing', 'color', 'line-height'],
                        properties: [
                            'font-family',
                            'font-size',
                            'font-weight',
                            'letter-spacing',
                            'color',
                            'line-height',
                            'text-align',
                            'text-decoration',
                            'text-shadow'
                        ]
                    },
                    {
                        name: 'Decorations',
                        open: false,
                        buildProps: ['opacity', 'background-color', 'border-radius', 'border', 'box-shadow', 'background'],
                        properties: [
                            'opacity',
                            'background-color',
                            'border-radius',
                            'border',
                            'box-shadow',
                            'background'
                        ]
                    },
                    {
                        name: 'Extra',
                        open: false,
                        buildProps: ['transition', 'perspective', 'transform'],
                        properties: [
                            'transition',
                            'perspective',
                            'transform'
                        ]
                    }
                ]
            },
            
            // Layer manager configuration
            layerManager: {
                appendTo: '#layer-manager'
            },
            
            // Trait manager configuration
            traitManager: {
                appendTo: '#trait-manager'
            },
            
            // Block manager configuration (handled by ComponentManager)
            blockManager: {
                appendTo: '#component-blocks'
            },
            
            // Device manager configuration
            deviceManager: {
                devices: [
                    {
                        name: 'Desktop',
                        width: '',
                    },
                    {
                        name: 'Tablet',
                        width: '768px',
                        widthMedia: '992px',
                    },
                    {
                        name: 'Mobile',
                        width: '375px',
                        widthMedia: '768px',
                    }
                ]
            },
            
            // Canvas configuration
            canvas: {
                styles: [
                    // Include theme styles
                    '/assets/admin/css/live-designer/canvas-styles.css'
                ],
                scripts: []
            },
            
            // Panels configuration - disable default panels
            panels: {
                defaults: []
            },
            
            // Plugin configuration
            plugins: [],
            pluginsOpts: {}
        };
        
        // Initialize GrapesJS
        this.editor = grapesjs.init(editorConfig);
        
        // Wait for editor to be ready
        return new Promise((resolve) => {
            this.editor.on('load', () => {
                console.log('📝 GrapesJS Editor loaded');
                resolve();
            });
        });
    }
    
    /**
     * Initialize placeholder editor for testing
     */
    initializePlaceholderEditor() {
        const canvasElement = document.querySelector(this.options.canvasSelector);
        if (canvasElement) {
            canvasElement.innerHTML = `
                <div style="padding: 2rem; text-align: center; background: #f8f9fa; height: 100%; display: flex; align-items: center; justify-content: center;">
                    <div>
                        <h3 style="color: #0d6efd; margin-bottom: 1rem;">
                            <i class="ri-palette-line" style="margin-right: 0.5rem;"></i>
                            Live Designer Preview
                        </h3>
                        <p style="color: #6c757d; margin-bottom: 1.5rem;">
                            GrapesJS will be loaded here once the API is connected.
                        </p>
                        <div style="background: #fff; border: 2px dashed #e9ecef; border-radius: 8px; padding: 2rem; margin: 1rem 0;">
                            <h4 style="color: #495057;">Page Content Area</h4>
                            <p style="color: #6c757d;">This is where your page content will be displayed and edited.</p>
                        </div>
                        <small style="color: #6c757d;">
                            UI components are ready for testing!
                        </small>
                    </div>
                </div>
            `;
        }
        
        // Create mock editor object
        this.editor = {
            on: () => {},
            setComponents: () => {},
            setStyle: () => {},
            getHtml: () => '<div>Mock HTML</div>',
            getCss: () => 'body { margin: 0; }',
            getComponents: () => [],
            getStyles: () => [],
            getDeviceManager: () => ({
                get: () => ({ name: 'Desktop' })
            }),
            setDevice: () => {},
            getWrapper: () => ({
                find: () => []
            }),
            destroy: () => {}
        };
        
        console.log('🎭 Placeholder editor initialized');
    }
    
    /**
     * Initialize manager components
     */
    initializeManagers() {
        // Initialize Widget Initializer
        this.widgetInitializer = new WidgetInitializer(this.editor);
        this.widgetInitializer.initialize();
        
        // Initialize Component Manager
        this.componentManager = new ComponentManager(this.editor, this.api);
        
        // Initialize Canvas Manager
        this.canvasManager = new CanvasManager(this.editor, this.api);
        
        // Initialize Sidebar Manager
        this.sidebarManager = new SidebarManager(this.editor, this.api);
        
        // Initialize Enhanced Widgets
        this.enhancedWidgets = new EnhancedWidgets(this.editor, this.api);
        
        console.log('🔧 Managers initialized');
    }
    
    /**
     * Load page content into the editor
     */
    async loadPageContent() {
        try {
            this.showLoading('Loading page content...');
            
            // Test API connection first
            const isConnected = await this.api.testConnection(this.options.pageId);
            if (!isConnected) {
                throw new Error('API connection failed');
            }
            
            // Load page content from API
            const response = await this.api.loadPageContent(this.options.pageId);
            
            if (!response.success) {
                throw new Error(response.error || 'Failed to load page content');
            }
            
            const { html, assets } = response.data;
            
            // If we have a real GrapesJS editor, load content
            if (this.editor && typeof this.editor.setComponents === 'function') {
                // Set HTML content
                if (html) {
                    this.editor.setComponents(html);
                }
                
                // Inject CSS assets into the canvas
                if (assets && assets.css && assets.css.length > 0) {
                    this.injectCanvasAssets(assets);
                }
                
                // Initialize widgets after content and assets are loaded
                setTimeout(() => {
                    if (this.widgetInitializer) {
                        this.widgetInitializer.reinitializeWidgets();
                    }
                }, 1500);
                
                console.log('📄 Page content loaded into GrapesJS editor');
            } else {
                // For placeholder mode, update the canvas directly
                this.updatePlaceholderCanvas(html, assets);
                
                // Initialize widgets in placeholder mode too
                setTimeout(() => {
                    this.initializePlaceholderWidgets();
                }, 1000);
                
                console.log('📄 Page content loaded in placeholder mode');
            }
            
            this.showMessage('Page content loaded successfully', 'success');
            
        } catch (error) {
            console.error('❌ Failed to load page content:', error);
            this.showMessage(`Failed to load page content: ${error.message}`, 'error');
            
            // Load fallback content
            this.loadFallbackContent();
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Editor events
        this.editor.on('component:selected', (component) => {
            this.onComponentSelected(component);
        });
        
        this.editor.on('component:deselected', (component) => {
            this.onComponentDeselected(component);
        });
        
        this.editor.on('storage:start', () => {
            this.showMessage('Saving...', 'info');
        });
        
        this.editor.on('storage:end', () => {
            this.showMessage('Saved successfully!', 'success');
        });
        
        this.editor.on('storage:error', (error) => {
            console.error('Storage error:', error);
            this.showMessage('Failed to save', 'error');
        });
        
        // Custom events
        document.addEventListener('contentSelected', (event) => {
            this.onContentSelected(event.detail);
        });
        
        document.addEventListener('assetSelected', (event) => {
            this.onAssetSelected(event.detail);
        });
        
        console.log('👂 Event listeners setup');
    }
    
    /**
     * Handle component selection
     */
    onComponentSelected(component) {
        // Update selected element info in sidebars
        const elementInfo = {
            name: component.getName() || component.get('tagName'),
            type: component.get('type'),
            id: component.getId(),
            classes: component.getClasses().join(' ')
        };
        
        // Update UI
        this.updateSelectedElementInfo(elementInfo);
        
        console.log('🎯 Component selected:', elementInfo);
    }
    
    /**
     * Handle component deselection
     */
    onComponentDeselected(component) {
        this.clearSelectedElementInfo();
        console.log('❌ Component deselected');
    }
    
    /**
     * Handle content selection from modal
     */
    onContentSelected(data) {
        const { contentItem, widgetId } = data;
        
        // Apply content to widget
        if (this.enhancedWidgets) {
            this.enhancedWidgets.applyContentToWidget(widgetId, contentItem);
        }
        
        console.log('📝 Content selected:', data);
    }
    
    /**
     * Handle asset selection from modal
     */
    onAssetSelected(data) {
        const { asset, targetElement } = data;
        
        // Apply asset to element
        if (targetElement && asset) {
            this.applyAssetToElement(targetElement, asset);
        }
        
        console.log('🖼️ Asset selected:', data);
    }
    
    /**
     * Set preview mode (desktop, tablet, mobile)
     */
    setPreviewMode(mode) {
        this.currentDevice = mode;
        
        const device = this.editor.getDeviceManager().get(mode);
        if (device) {
            this.editor.setDevice(mode);
            console.log(`📱 Preview mode set to: ${mode}`);
        }
    }
    
    /**
     * Preview the page
     */
    previewPage() {
        const previewUrl = this.getPreviewUrl();
        window.open(previewUrl, '_blank');
    }
    
    /**
     * Save the page
     */
    async savePage() {
        try {
            this.showLoading('Saving page...');
            
            const html = this.editor.getHtml();
            const css = this.editor.getCss();
            const components = this.editor.getComponents();
            const styles = this.editor.getStyles();
            
            await this.api.savePageContent({
                html,
                css,
                components: JSON.stringify(components),
                styles: JSON.stringify(styles)
            });
            
            this.showMessage('Page saved successfully!', 'success');
            
        } catch (error) {
            console.error('❌ Failed to save page:', error);
            this.showMessage('Failed to save page', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Get preview URL
     */
    getPreviewUrl() {
        return `/admin/pages/${this.options.pageId}/preview`;
    }
    
    /**
     * Update selected element info in UI
     */
    updateSelectedElementInfo(elementInfo) {
        const infoElements = document.querySelectorAll('[id^="selected-element-"]');
        
        infoElements.forEach(element => {
            element.style.display = 'block';
            
            const nameElement = element.querySelector('[id$="-name"]');
            const typeElement = element.querySelector('[id$="-type"]');
            
            if (nameElement) nameElement.textContent = elementInfo.name;
            if (typeElement) typeElement.textContent = elementInfo.type;
        });
    }
    
    /**
     * Clear selected element info
     */
    clearSelectedElementInfo() {
        const infoElements = document.querySelectorAll('[id^="selected-element-"]');
        
        infoElements.forEach(element => {
            element.style.display = 'none';
        });
    }
    
    /**
     * Apply asset to element
     */
    applyAssetToElement(elementId, asset) {
        const component = this.editor.getWrapper().find(`#${elementId}`)[0];
        
        if (component) {
            if (asset.type === 'image') {
                component.addAttributes({ src: asset.url });
            } else if (asset.type === 'video') {
                component.addAttributes({ src: asset.url });
            }
            
            console.log('🎨 Asset applied to element');
        }
    }
    
    /**
     * Show loading state
     */
    showLoading(message = 'Loading...') {
        const loadingElement = document.querySelector(this.options.loadingSelector);
        if (loadingElement) {
            loadingElement.querySelector('div:last-child').textContent = message;
            loadingElement.classList.remove('hidden');
        }
        this.isLoading = true;
    }
    
    /**
     * Hide loading state
     */
    hideLoading() {
        const loadingElement = document.querySelector(this.options.loadingSelector);
        if (loadingElement) {
            loadingElement.classList.add('hidden');
        }
        this.isLoading = false;
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        const messageElement = document.createElement('div');
        messageElement.className = `live-designer-message ${type}`;
        messageElement.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="ri-${this.getMessageIcon(type)}-line me-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(messageElement);
        
        // Show message
        setTimeout(() => messageElement.classList.add('show'), 100);
        
        // Hide message after 3 seconds
        setTimeout(() => {
            messageElement.classList.remove('show');
            setTimeout(() => messageElement.remove(), 300);
        }, 3000);
    }
    
    /**
     * Get message icon based on type
     */
    getMessageIcon(type) {
        switch (type) {
            case 'success': return 'check-circle';
            case 'error': return 'error-warning';
            case 'info': return 'information';
            default: return 'information';
        }
    }
    
    /**
     * Get editor instance
     */
    getEditor() {
        return this.editor;
    }
    
    /**
     * Get API instance
     */
    getAPI() {
        return this.api;
    }
    
    /**
     * Inject CSS assets into GrapesJS canvas
     */
    injectCanvasAssets(assets) {
        if (!this.editor || !assets) return;
        
        try {
            const canvas = this.editor.Canvas;
            if (!canvas) return;
            
            const iframe = canvas.getFrameEl();
            if (!iframe || !iframe.contentDocument) return;
            
            const doc = iframe.contentDocument;
            const head = doc.head || doc.getElementsByTagName('head')[0];
            
            // Inject CSS files
            if (assets.css && Array.isArray(assets.css)) {
                assets.css.forEach(cssFile => {
                    const link = doc.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = cssFile.startsWith('http') ? cssFile : `/${cssFile.replace(/^\//, '')}`;
                    head.appendChild(link);
                });
                
                console.log('💄 Injected CSS assets into canvas:', assets.css);
            }
            
            // Inject JS files
            if (assets.js && Array.isArray(assets.js)) {
                assets.js.forEach(jsFile => {
                    const script = doc.createElement('script');
                    script.src = jsFile.startsWith('http') ? jsFile : `/${jsFile.replace(/^\//, '')}`;
                    script.async = true;
                    head.appendChild(script);
                });
                
                console.log('⚡ Injected JS assets into canvas:', assets.js);
            }
            
        } catch (error) {
            console.warn('⚠️ Failed to inject canvas assets:', error);
        }
    }
    
    /**
     * Update placeholder canvas with real content
     */
    updatePlaceholderCanvas(html, assets) {
        const canvasElement = document.querySelector(this.options.canvasSelector);
        if (!canvasElement) return;
        
        // Create iframe for better isolation
        const iframe = document.createElement('iframe');
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = 'none';
        iframe.style.background = '#fff';
        
        canvasElement.innerHTML = '';
        canvasElement.appendChild(iframe);
        
        // Write content to iframe
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        
        let fullHtml = html;
        
        // Add CSS assets to the HTML
        if (assets && assets.css && Array.isArray(assets.css)) {
            const cssLinks = assets.css.map(cssFile => {
                const href = cssFile.startsWith('http') ? cssFile : `/${cssFile.replace(/^\//, '')}`;
                return `<link rel="stylesheet" href="${href}">`;
            }).join('\n');
            
            if (fullHtml.includes('</head>')) {
                fullHtml = fullHtml.replace('</head>', `${cssLinks}\n</head>`);
            } else {
                fullHtml = `${cssLinks}\n${fullHtml}`;
            }
        }
        
        // Add JS assets to the HTML
        if (assets && assets.js && Array.isArray(assets.js)) {
            const jsScripts = assets.js.map(jsFile => {
                const src = jsFile.startsWith('http') ? jsFile : `/${jsFile.replace(/^\//, '')}`;
                return `<script src="${src}"></script>`;
            }).join('\n');
            
            if (fullHtml.includes('</body>')) {
                fullHtml = fullHtml.replace('</body>', `${jsScripts}\n</body>`);
            } else {
                fullHtml = `${fullHtml}\n${jsScripts}`;
            }
        }
        
        iframeDoc.open();
        iframeDoc.write(fullHtml);
        iframeDoc.close();
        
        console.log('🖼️ Updated placeholder canvas with real content');
    }
    
    /**
     * Load fallback content when API fails
     */
    loadFallbackContent() {
        const canvasElement = document.querySelector(this.options.canvasSelector);
        if (!canvasElement) return;
        
        canvasElement.innerHTML = `
            <div style="padding: 2rem; text-align: center; background: #f8f9fa; height: 100%; display: flex; align-items: center; justify-content: center;">
                <div>
                    <h3 style="color: #dc3545; margin-bottom: 1rem;">
                        <i class="ri-error-warning-line" style="margin-right: 0.5rem;"></i>
                        Failed to Load Content
                    </h3>
                    <p style="color: #6c757d; margin-bottom: 1rem;">
                        Could not load page content from the API.
                    </p>
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        <i class="ri-refresh-line me-1"></i>
                        Retry
                    </button>
                </div>
            </div>
        `;
        
        console.log('🔄 Loaded fallback content');
    }
    
    /**
     * Initialize widgets in placeholder mode
     */
    initializePlaceholderWidgets() {
        const canvasElement = document.querySelector(this.options.canvasSelector);
        if (!canvasElement) return;
        
        const iframe = canvasElement.querySelector('iframe');
        if (!iframe || !iframe.contentDocument) return;
        
        const iframeDoc = iframe.contentDocument;
        const iframeWindow = iframe.contentWindow;
        
        try {
            // Initialize counter widgets
            const counters = iframeDoc.querySelectorAll('.widget-counter .counter[data-count]');
            counters.forEach(counter => {
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
                
                updateCounter();
            });
            
            // Initialize slider widgets
            const sliders = iframeDoc.querySelectorAll('.widget-slider .nivoSlider');
            const jQuery = iframeWindow.jQuery || iframeWindow.$;
            
            if (jQuery && jQuery.fn.nivoSlider) {
                sliders.forEach(slider => {
                    const sliderId = slider.id;
                    
                    if (jQuery('#' + sliderId).data('nivoslider')) {
                        jQuery('#' + sliderId).data('nivoslider').destroy();
                    }
                    
                    const defaultConfig = {
                        effect: 'random',
                        slices: 15,
                        animSpeed: 500,
                        pauseTime: 3000,
                        directionNav: true,
                        controlNav: true,
                        pauseOnHover: true
                    };
                    
                    jQuery('#' + sliderId).nivoSlider(defaultConfig);
                });
            }
            
            console.log('🔧 Placeholder widgets initialized');
            
        } catch (error) {
            console.warn('⚠️ Error initializing placeholder widgets:', error);
        }
    }
    
    /**
     * Destroy the live designer
     */
    destroy() {
        if (this.editor) {
            this.editor.destroy();
        }
        
        // Clean up managers
        if (this.widgetInitializer) this.widgetInitializer.destroy();
        if (this.componentManager) this.componentManager.destroy();
        if (this.canvasManager) this.canvasManager.destroy();
        if (this.sidebarManager) this.sidebarManager.destroy();
        if (this.enhancedWidgets) this.enhancedWidgets.destroy();
        
        console.log('🗑️ Live Designer destroyed');
    }
}

// Export for global use
window.LiveDesignerMain = LiveDesignerMain;
