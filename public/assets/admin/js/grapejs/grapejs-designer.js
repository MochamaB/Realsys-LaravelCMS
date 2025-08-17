/**
 * GrapesJS Live Preview Designer
 * Handles the live preview functionality in the GrapesJS tab
 */
class GrapesJSDesigner {
    constructor() {
        this.editor = null;
        this.themeAssets = null;
        this.pageId = null;
        this.csrfToken = null;
        this.initialized = false;
        
        // Widget management properties
        this.currentWidget = null;
        this.modalInstance = null;
        this.isSaving = false;
        this.isLoading = false;
    }

    /**
     * Initialize the GrapesJS designer
     * @param {Object} options - Configuration options
     * @param {number} options.pageId - The page ID
     * @param {string} options.csrfToken - CSRF token for API requests
     */
    init(options = {}) {
        this.pageId = options.pageId;
        this.csrfToken = options.csrfToken;
        
        console.log('🔄 Initializing GrapesJS Live Preview...');
        
        // Setup tab event listener
        this.setupTabListener();
        
        // Setup preview controls
        this.setupPreviewControls();
        
        // Setup sidebar controls
        this.setupSidebarControls();
        
        // Setup content selection modal
        this.setupContentSelectionModal();
        
        this.initialized = true;
    }

    /**
     * Setup tab switching listener with loading indicator and sidebar management
     */
    setupTabListener() {
        // Listen for tab clicks (before show)
        document.addEventListener('show.bs.tab', (e) => {
            if (e.target.getAttribute('data-bs-target') === '#preview') {
                console.log('🔄 Live Preview tab being activated...');
                this.showTabLoadingIndicator(true);
                // Initialize Live Preview sidebar with sections and widgets
                this.initializeLivePreviewSidebar();
            } else {
                // If switching away from preview, reset sidebar
                this.resetSidebarForOtherTabs();
            }
        });
        
        // Listen for tab shown (after show)
        document.addEventListener('shown.bs.tab', (e) => {
            if (e.target.getAttribute('data-bs-target') === '#preview') {
                console.log('🔄 Live Preview tab activated - Style & Content Editing Mode');
                this.initializeGrapesJS();
            }
        });
    }

    /**
     * Setup preview control buttons
     */
    setupPreviewControls() {
        const refreshBtn = document.getElementById('refreshPreviewBtn');
        const fullscreenBtn = document.getElementById('fullscreenPreviewBtn');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                if (this.editor) {
                    this.loadCompletePageContent();
                }
            });
        }
        
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                const canvas = document.getElementById('gjs');
                if (canvas) {
                    if (document.fullscreenElement) {
                        document.exitFullscreen();
                    } else {
                        canvas.requestFullscreen();
                    }
                }
            });
        }
    }

    /**
     * Setup sidebar control functionality
     */
    setupSidebarControls() {
        // GrapesJS Sidebar Toggle
        const toggleBtn = document.getElementById('toggle-grapesjs-sidebar');
        const sidebar = document.getElementById('grapesjs-sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                
                // Update button icon
                const icon = toggleBtn.querySelector('i');
                if (sidebar.classList.contains('collapsed')) {
                    icon.className = 'ri-sidebar-unfold-line';
                    toggleBtn.title = 'Expand Sidebar';
                } else {
                    icon.className = 'ri-sidebar-fold-line';
                    toggleBtn.title = 'Collapse Sidebar';
                }
            });
        }
        
        // Save button functionality
        const saveBtn = document.getElementById('gjs-save-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveContent();
            });
        }
        
        // Preview button functionality
        const previewBtn = document.getElementById('gjs-preview-btn');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => {
                this.openPreview();
            });
        }
    }

    /**
     * Save GrapesJS content
     */
    async saveContent() {
        if (!this.editor) {
            console.warn('⚠️ Editor not initialized');
            return;
        }
        
        try {
            const saveBtn = document.getElementById('gjs-save-btn');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="ri-loader-4-line"></i> Saving...';
            }
            
            const html = this.editor.getHtml();
            const css = this.editor.getCss();
            
            const response = await fetch(`/admin/api/pages/${this.pageId}/grapejs-content`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    html: html,
                    css: css
                })
            });
            
            if (response.ok) {
                console.log('✅ Content saved successfully');
                
                // Show success feedback
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="ri-check-line"></i> Saved';
                    setTimeout(() => {
                        saveBtn.innerHTML = '<i class="ri-save-line"></i> <span>Save</span>';
                        saveBtn.disabled = false;
                    }, 2000);
                }
            } else {
                throw new Error('Failed to save content');
            }
        } catch (error) {
            console.error('❌ Error saving content:', error);
            
            const saveBtn = document.getElementById('gjs-save-btn');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="ri-error-warning-line"></i> Error';
                setTimeout(() => {
                    saveBtn.innerHTML = '<i class="ri-save-line"></i> <span>Save</span>';
                    saveBtn.disabled = false;
                }, 2000);
            }
        }
    }

    /**
     * Initialize Live Preview sidebar
     */
    initializeLivePreviewSidebar() {
        if (window.LivePreviewSidebar) {
            window.LivePreviewSidebar.init();
        } else {
            console.warn('⚠️ LivePreviewSidebar not available');
        }
    }

    /**
     * Reset sidebar for other tabs
     */
    resetSidebarForOtherTabs() {
        if (window.LivePreviewSidebar) {
            window.LivePreviewSidebar.expandSidebar();
            window.LivePreviewSidebar.reset();
        }
    }

    /**
     * Open preview in new window
     */
    openPreview() {
        const previewUrl = `/pages/${this.pageId}`;
        window.open(previewUrl, '_blank');
    }

    /**
     * Show/hide tab loading indicator
     */
    showTabLoadingIndicator(show) {
        const previewTab = document.getElementById('preview');
        const grapesJSContainer = document.querySelector('.grapesjs-designer-container');
        
        // Target the actual GrapesJS container instead of the tab
        const targetContainer = grapesJSContainer || previewTab;
        if (!targetContainer) {
            console.warn('⚠️ No target container found for loading indicator');
            return;
        }
        
        let loadingOverlay = targetContainer.querySelector('.tab-loading-overlay');
        
        if (show) {
            if (!loadingOverlay) {
                // Create loading overlay for the container
                loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'tab-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-95';
                loadingOverlay.style.zIndex = '9999';
                loadingOverlay.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-primary">Loading Live Preview</h5>
                        <p class="text-muted mb-0">Please wait while we initialize the visual editor...</p>
                    </div>
                `;
                
                // Ensure the container has relative positioning
                const currentPosition = window.getComputedStyle(targetContainer).position;
                if (currentPosition === 'static') {
                    targetContainer.style.position = 'relative';
                }
                
                targetContainer.appendChild(loadingOverlay);
                console.log('✅ Loading overlay created and added to container');
            }
            loadingOverlay.style.display = 'flex';
            console.log('✅ Loading overlay shown');
        } else {
            if (loadingOverlay) {
                // Force remove the overlay completely
                loadingOverlay.remove();
                console.log('✅ Loading overlay removed completely');
            } else {
                console.warn('⚠️ Loading overlay not found for removal');
            }
        }
    }

    /**
     * Initialize GrapesJS editor
     */
    async initializeGrapesJS() {
        // Destroy existing editor if it exists
        if (this.editor) {
            try {
                // Check if editor has destroy method (GrapesJS v0.21+)
                if (typeof this.editor.destroy === 'function') {
                    this.editor.destroy();
                } else if (typeof this.editor.remove === 'function') {
                    this.editor.remove();
                }
            } catch (error) {
                console.warn('⚠️ Error destroying existing editor:', error);
            }
            this.editor = null;
        }
        
        // Load theme assets first
        await this.loadThemeAssets();
        
        // Initialize GrapesJS with custom configuration
        this.editor = grapesjs.init({
            container: '#gjs',
            height: '100%',
            width: 'auto',
            storageManager: false,
            fromElement: true,
            // Canvas configuration for better rendering
            canvas: {
                styles: this.themeAssets ? [this.themeAssets.css] : [],
                scripts: []
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
                        name: 'Mobile portrait',
                        width: '375px',
                        widthMedia: '768px',
                    }
                ]
            },
            // Remove default panels
            panels: {
                defaults: []
            },
            // Configure managers to use external containers
            blockManager: {
                appendTo: '#gjs-blocks-container',
                blocks: [] // Start with no default blocks
            },
            layerManager: {
                appendTo: '#gjs-layers-container',
            },
            styleManager: {
                appendTo: '#gjs-styles-container',
            },
            traitManager: {
                appendTo: '#gjs-traits-container',
            },
            // Disable default plugins that add unwanted blocks
            plugins: [],
            pluginsOpts: {}
        });
        
        // Add device commands AFTER GrapesJS is fully initialized
        this.editor.on('load', () => {
            this.addDeviceCommands();
            this.setupComponentSystem();
            
            // Setup drop handling with a delay to ensure iframe is ready
            setTimeout(() => {
                this.setupCanvasDropHandling();
            }, 500);
            
            console.log('✅ GrapesJS editor loaded and device commands added');
        });
        
        // Load complete page content into GrapesJS
        await this.loadCompletePageContent();
        
        // Setup drop handling multiple times to ensure it works
        setTimeout(() => {
            console.log('🔄 Re-setting up canvas drop handling after content load...');
            this.setupCanvasDropHandling();
        }, 1000);
        
        setTimeout(() => {
            console.log('🔄 Final canvas drop handling setup...');
            this.setupCanvasDropHandling();
        }, 2000);
        
        // Hide the tab loading indicator once everything is loaded
        setTimeout(() => {
            this.showTabLoadingIndicator(false);
            console.log('✅ GrapesJS initialization completed, hiding loader');
        }, 2500); // Increased delay to ensure everything is rendered
        
        console.log('✅ GrapesJS Live Preview initialized');
    }

    /**
     * Add device switching commands to GrapesJS
     */
    addDeviceCommands() {
        const commands = this.editor.Commands;
        
        // Remove existing commands first to avoid conflicts
        ['set-device-desktop', 'set-device-tablet', 'set-device-mobile'].forEach(cmd => {
            if (commands.has(cmd)) {
                commands.remove(cmd);
            }
        });
        
        // Add desktop command
        commands.add('set-device-desktop', {
            run: (editor) => {
                try {
                    const deviceManager = editor.DeviceManager;
                    if (deviceManager) {
                        deviceManager.select('Desktop');
                    }
                    this.updateDeviceButtons('device-desktop');
                    console.log('✅ Switched to Desktop view');
                } catch (error) {
                    console.warn('⚠️ Error switching to desktop:', error);
                }
            }
        });
        
        // Add tablet command
        commands.add('set-device-tablet', {
            run: (editor) => {
                try {
                    const deviceManager = editor.DeviceManager;
                    if (deviceManager) {
                        deviceManager.select('Tablet');
                    }
                    this.updateDeviceButtons('device-tablet');
                    console.log('✅ Switched to Tablet view');
                } catch (error) {
                    console.warn('⚠️ Error switching to tablet:', error);
                }
            }
        });
        
        // Add mobile command
        commands.add('set-device-mobile', {
            run: (editor) => {
                try {
                    const deviceManager = editor.DeviceManager;
                    if (deviceManager) {
                        deviceManager.select('Mobile portrait');
                    }
                    this.updateDeviceButtons('device-mobile');
                    console.log('✅ Switched to Mobile view');
                } catch (error) {
                    console.warn('⚠️ Error switching to mobile:', error);
                }
            }
        });
        
        console.log('✅ Device commands added to GrapesJS');
    }

    /**
     * Setup GrapesJS component system
     */
    setupComponentSystem() {
        try {
            // Register section and widget components
            registerSectionComponents(this.editor);
            registerWidgetComponents(this.editor);
            
            // Setup block manager with available widgets
            this.setupBlockManager();
            
            console.log('✅ GrapesJS component system initialized');
        } catch (error) {
            console.error('❌ Error setting up component system:', error);
        }
    }

    /**
     * Setup block manager (Live Preview mode - styling focused)
     */
    setupBlockManager() {
        // Show info messages instead of actual components (no adding in Live Preview)
        this.initializeSectionsGrid();
        this.loadThemeWidgets();
        
        // Initialize layers manager for element selection and styling
        this.initializeLayersManager();
        
        console.log('✅ Live Preview block manager setup - focused on styling');
    }
    
    /**
     * Show sections info (Live Preview is for styling only)
     */
    initializeSectionsGrid() {
        const sectionsGrid = document.getElementById('sectionsGrid');
        if (!sectionsGrid) return;
        
        sectionsGrid.innerHTML = `
            <div class="p-3 text-center text-muted">
                <i class="ri-layout-line fs-4 mb-2 d-block"></i>
                <p class="mb-1"><strong>Live Preview Mode</strong></p>
                <p class="small mb-0">Use <strong>Layout Designer</strong> tab to add sections</p>
            </div>
        `;
        
        console.log('✅ Live Preview mode - section adding disabled');
    }
    
    /**
     * Initialize layers manager in left sidebar (for element selection and styling)
     */
    initializeLayersManager() {
        const layersContainer = document.getElementById('pageLayersContainer');
        if (layersContainer && this.editor) {
            // Add header for layers panel
            layersContainer.innerHTML = `
                <div class="p-2 border-bottom bg-light">
                    <small class="text-muted fw-bold">ELEMENT LAYERS</small>
                </div>
                <div id="gjs-layers-content"></div>
            `;
            
            // Configure the layer manager to use the content container
            const layerManager = this.editor.LayerManager;
            const layersContent = layersContainer.querySelector('#gjs-layers-content');
            if (layersContent) {
                layerManager.render(layersContent);
                console.log('✅ Layers manager initialized for element selection');
            }
        }
    }

    /**
     * Clear theme widgets (Live Preview is for styling only)
     */
    async loadThemeWidgets() {
        const themeWidgetsGrid = document.getElementById('themeWidgetsGrid');
        if (themeWidgetsGrid) {
            themeWidgetsGrid.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="ri-information-line fs-4 mb-2 d-block"></i>
                    <p class="mb-1"><strong>Live Preview Mode</strong></p>
                    <p class="small mb-0">Use <strong>Layout Designer</strong> tab to add widgets and sections</p>
                </div>
            `;
        }
        
        console.log('✅ Live Preview mode - widget adding disabled');
    }
    
    /**
     * Render GrapesJS widgets in the left sidebar
     */
    renderGrapesJSWidgets(widgets, container) {
        container.innerHTML = '';
        
        widgets.forEach(widget => {
            const widgetItem = document.createElement('div');
            widgetItem.className = 'component-item';
            widgetItem.draggable = true;
            widgetItem.dataset.widgetType = widget.slug;
            widgetItem.dataset.widgetId = widget.id;
            
            // Use robust name fallback like GridStack
            const displayName = widget.label || widget.name || 'Unknown Widget';
            
            widgetItem.innerHTML = `
                ${this.getWidgetIcon(widget.slug)}
                <div class="label">${displayName}</div>
            `;
            
            // Add drag and drop functionality with proper data format
            widgetItem.addEventListener('dragstart', (e) => {
                const widgetData = {
                    type: 'widget',
                    id: widget.id,
                    name: displayName,
                    slug: widget.slug,
                    category: widget.category || 'General',
                    label: displayName
                };
                
                console.log('🔄 Dragging widget from GrapesJS sidebar:', widgetData);
                e.dataTransfer.setData('text/plain', JSON.stringify(widgetData));
                widgetItem.classList.add('dragging');
            });
            
            widgetItem.addEventListener('dragend', (e) => {
                widgetItem.classList.remove('dragging');
            });
            
            // Add click functionality to test modal (for debugging)
            widgetItem.addEventListener('click', () => {
                console.log('💆 Widget clicked:', displayName);
                const widgetData = {
                    type: 'widget',
                    id: widget.id,
                    name: displayName,
                    slug: widget.slug,
                    category: widget.category || 'General',
                    label: displayName
                };
                // Test modal functionality
                this.handleWidgetDropToCanvas(widgetData);
            });
            
            container.appendChild(widgetItem);
        });
        
        console.log(`✅ Rendered ${widgets.length} GrapesJS theme widgets`);
    }

    /**
     * Load widget blocks from API (deprecated - kept for compatibility)
     */
    async loadWidgetBlocks() {
        try {
            const response = await fetch('/admin/api/widgets/available', {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                const widgets = data.widgets || data || [];
                const blockManager = this.editor.BlockManager;
                
                widgets.forEach(widget => {
                    blockManager.add(`widget-${widget.slug}`, {
                        label: widget.name,
                        category: widget.category || 'Widgets',
                        content: {
                            type: `${widget.slug}-widget`,
                            attributes: {
                                'data-widget-type': widget.slug,
                                'data-widget-id': widget.id
                            }
                        },
                        media: this.getWidgetIcon(widget.slug)
                    });
                });
                
                console.log(`✅ Loaded ${widgets.length} widget blocks`);
            }
        } catch (error) {
            console.error('❌ Failed to load widget blocks:', error);
        }
    }

    /**
     * Get widget icon based on widget type (synchronized with GridStack)
     */
    getWidgetIcon(widgetType) {
        const iconMap = {
            'text-widget': '<i class="ri-text"></i>',
            'image-widget': '<i class="ri-image-line"></i>',
            'button-widget': '<i class="ri-cursor-line"></i>',
            'counter-widget': '<i class="ri-calculator-line"></i>',
            'gallery-widget': '<i class="ri-gallery-line"></i>',
            'contact-form': '<i class="ri-mail-line"></i>',
            'newsletter': '<i class="ri-newsletter-line"></i>',
            'spacer': '<i class="ri-space"></i>',
            // Add more widget type mappings (synchronized with GridStack)
            'text': '<i class="ri-text"></i>',
            'image': '<i class="ri-image-line"></i>',
            'button': '<i class="ri-cursor-line"></i>',
            'counter': '<i class="ri-calculator-line"></i>',
            'gallery': '<i class="ri-gallery-line"></i>',
            'form': '<i class="ri-mail-line"></i>',
            'video': '<i class="ri-video-line"></i>',
            'map': '<i class="ri-map-pin-line"></i>',
            'testimonial': '<i class="ri-chat-quote-line"></i>'
        };
        
        return iconMap[widgetType] || '<i class="ri-puzzle-line"></i>';
    }

    /**
     * Update device button states
     */
    updateDeviceButtons(activeDevice) {
        const buttons = document.querySelectorAll('.panel__devices .btn');
        buttons.forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-primary');
        });
        
        const activeBtn = document.querySelector(`#${activeDevice}`);
        if (activeBtn) {
            activeBtn.classList.remove('btn-outline-primary');
            activeBtn.classList.add('active', 'btn-primary');
        }
    }

    /**
     * Load theme CSS and JS assets
     */
    async loadThemeAssets() {
        try {
            console.log('🎨 Loading theme assets...');
            
            // Load theme CSS and JS
            const [cssResponse, jsResponse] = await Promise.all([
                fetch('/admin/api/themes/active/canvas-styles', {
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                }),
                fetch('/admin/api/themes/active/canvas-scripts', {
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                })
            ]);
            
            if (cssResponse.ok && jsResponse.ok) {
                const cssData = await cssResponse.json();
                const jsData = await jsResponse.json();
                
                this.themeAssets = {
                    css: cssData.css || '',
                    js: jsData.js || '',
                    files: {
                        css: cssData.files_loaded || [],
                        js: jsData.files_loaded || []
                    }
                };
                
                console.log('✅ Theme assets loaded:', {
                    css_size: this.themeAssets.css.length,
                    js_size: this.themeAssets.js.length,
                    css_files: this.themeAssets.files.css.length,
                    js_files: this.themeAssets.files.js.length
                });
                
                // Inject CSS into GrapesJS canvas immediately
                this.injectThemeCSS();
                
            } else {
                console.warn('⚠️ Failed to load theme assets');
                this.themeAssets = { css: '', js: '', files: { css: [], js: [] } };
            }
        } catch (error) {
            console.error('❌ Error loading theme assets:', error);
            this.themeAssets = { css: '', js: '', files: { css: [], js: [] } };
        }
    }

    /**
     * Inject theme CSS into GrapesJS canvas
     */
    injectThemeCSS() {
        if (!this.themeAssets || !this.themeAssets.css) {
            console.warn('⚠️ No theme CSS to inject');
            return;
        }

        try {
            // Create a style element for the theme CSS
            const styleElement = document.createElement('style');
            styleElement.id = 'theme-css-injection';
            styleElement.textContent = this.themeAssets.css;
            
            // Remove existing theme CSS if present
            const existingStyle = document.getElementById('theme-css-injection');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            // Inject into the GrapesJS canvas
            const canvas = document.getElementById('gjs');
            if (canvas) {
                canvas.appendChild(styleElement);
                console.log('✅ Theme CSS injected into canvas');
            } else {
                // Fallback: inject into head
                document.head.appendChild(styleElement);
                console.log('✅ Theme CSS injected into head (fallback)');
            }
        } catch (error) {
            console.error('❌ Error injecting theme CSS:', error);
        }
    }

    /**
     * Process HTML content to fix common issues
     */
    processHtmlContent(htmlContent) {
        try {
            // Create a temporary DOM to process the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlContent;
            
            // Fix positioning issues - remove static positioning that GrapesJS adds
            const allElements = tempDiv.querySelectorAll('*');
            allElements.forEach(element => {
                const style = element.getAttribute('style');
                if (style && style.includes('position: static')) {
                    // Remove position: static to allow proper positioning
                    const newStyle = style.replace(/position:\s*static[;]?\s*/g, '');
                    element.setAttribute('style', newStyle);
                }
                
                // Ensure sections maintain proper display and flow
                if (element.tagName === 'SECTION' || element.classList.contains('cms-section-container') || element.classList.contains('section-wrapper')) {
                    const currentStyle = element.getAttribute('style') || '';
                    
                    // Remove any positioning that breaks normal flow
                    let cleanStyle = currentStyle
                        .replace(/position:\s*static[;]?\s*/gi, '')
                        .replace(/position:\s*absolute[;]?\s*/gi, '')
                        .replace(/position:\s*fixed[;]?\s*/gi, '');
                    
                    // Ensure proper display and flow
                    if (!cleanStyle.includes('display:')) {
                        cleanStyle += '; display: block;';
                    }
                    if (!cleanStyle.includes('position:')) {
                        cleanStyle += '; position: relative;';
                    }
                    
                    // Ensure sections don't overlap
                    if (!cleanStyle.includes('z-index:')) {
                        cleanStyle += '; z-index: 1;';
                    }
                    
                    // Clean up any double semicolons or spaces
                    cleanStyle = cleanStyle.replace(/;+/g, ';').replace(/^\s*;\s*/, '').trim();
                    
                    element.setAttribute('style', cleanStyle);
                }
            });
            
            // Fix image sources - convert relative to absolute URLs
            const images = tempDiv.querySelectorAll('img');
            images.forEach(img => {
                const src = img.getAttribute('src');
                if (src && src.startsWith('/') && !src.startsWith('//')) {
                    // Convert relative URL to absolute
                    img.setAttribute('src', window.location.origin + src);
                }
            });
            
            // Fix CSS background-image URLs
            const elementsWithBg = tempDiv.querySelectorAll('[style*="background"]');
            elementsWithBg.forEach(el => {
                const style = el.getAttribute('style');
                if (style && style.includes('url(') && !style.includes('http')) {
                    const updatedStyle = style.replace(/url\(([^)]+)\)/g, (match, url) => {
                        // Remove quotes if present
                        const cleanUrl = url.replace(/['"]/g, '');
                        if (cleanUrl.startsWith('/') && !cleanUrl.startsWith('//')) {
                            return `url(${window.location.origin}${cleanUrl})`;
                        }
                        return match;
                    });
                    el.setAttribute('style', updatedStyle);
                }
            });
            
            // Ensure proper Bootstrap classes are preserved
            const sections = tempDiv.querySelectorAll('section');
            sections.forEach(section => {
                // Ensure sections have proper spacing
                if (!section.classList.contains('py-5') && !section.classList.contains('ptb-140')) {
                    section.style.paddingTop = section.style.paddingTop || '40px';
                    section.style.paddingBottom = section.style.paddingBottom || '40px';
                }
            });
            
            return tempDiv.innerHTML;
        } catch (error) {
            console.warn('⚠️ Error processing HTML content:', error);
            return htmlContent;
        }
    }

    /**
     * Load complete page content from API using hybrid frontend pipeline
     */
    async loadCompletePageContent() {
        try {
            console.log('📄 Loading complete page content with frontend pipeline...');
            
            // Use hybrid API with theme context for frontend-accurate rendering
            const response = await fetch(`/admin/api/pages/${this.pageId}/sections?with_theme_context=true`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            console.log('📡 Hybrid API Response status:', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('📄 Hybrid API Response data keys:', Object.keys(data));
                console.log('📄 Hybrid API metadata:', data.metadata);
                
                // Handle hybrid API response format
                let htmlContent = null;
                let assetsData = null;
                let themeData = null;
                
                if (data.success && data.full_page_html) {
                    // Hybrid API format: {success: true, full_page_html: "...", data: [...], assets: {...}, theme: {...}}
                    htmlContent = data.full_page_html;
                    assetsData = data.assets;
                    themeData = data.theme;
                    console.log('✅ Using frontend-rendered HTML from hybrid API');
                } else if (data.full_page_html) {
                    // Alternative format without success flag
                    htmlContent = data.full_page_html;
                    assetsData = data.assets;
                    themeData = data.theme;
                    console.log('✅ Using frontend-rendered HTML from hybrid API (alt format)');
                } else {
                    console.warn('⚠️ Hybrid API did not return full_page_html, falling back to old method');
                    return this.loadCompletePageContentFallback();
                }
                
                if (htmlContent) {
                    // Process HTML content to fix common issues
                    const processedHtml = this.processHtmlContent(htmlContent);
                    
                    console.log('📄 Setting frontend-rendered HTML content in GrapesJS:', {
                        'original_length': htmlContent.length,
                        'processed_length': processedHtml.length,
                        'contains_breadcrumbs': processedHtml.includes('breadcrumbs-area'),
                        'contains_counter': processedHtml.includes('counter-area'),
                        'contains_featured_bg': processedHtml.includes('featured-bg-'),
                        'has_header': processedHtml.includes('header') || processedHtml.includes('navbar'),
                        'has_footer': processedHtml.includes('footer'),
                        'hybrid_mode': data.metadata?.hybrid_mode,
                        'frontend_pipeline_used': data.metadata?.frontend_pipeline_used
                    });
                    
                    // Clear existing components first
                    this.editor.setComponents('');
                    
                    // Inject theme assets from hybrid API (enhanced method)
                    this.injectHybridAPIAssets(assetsData, themeData);
                    
                    // Set the processed HTML content in GrapesJS
                    this.editor.setComponents(processedHtml);
                    
                    // Wait a bit for components to render, then inject additional functionality
                    setTimeout(() => {
                        this.injectHybridAPIJavaScript(assetsData);
                        this.extractAndInjectWidgetConfigurations(processedHtml);
                        this.initializeWidgetFunctionality();
                    }, 1000);
                    
                    console.log('✅ Frontend-accurate page content loaded into GrapesJS with hybrid API');
                } else {
                    throw new Error('No HTML content found in hybrid API response');
                }
            } else {
                console.warn('⚠️ Failed to load page content from hybrid API:', response.status, response.statusText);
                console.log('🔄 Falling back to old rendering method...');
                return this.loadCompletePageContentFallback();
            }
        } catch (error) {
            console.error('❌ Error loading page content from hybrid API:', error);
            console.log('🔄 Falling back to old rendering method...');
            return this.loadCompletePageContentFallback();
        }
    }

    /**
     * Fallback method using old API for backwards compatibility
     */
    async loadCompletePageContentFallback() {
        try {
            console.log('📄 Loading page content using fallback method...');
            
            const response = await fetch(`/admin/api/pages/${this.pageId}/render`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                // Handle different response formats (old API)
                let htmlContent = null;
                
                if (data.html) {
                    htmlContent = data.html;
                } else if (data.success && data.html) {
                    htmlContent = data.html;
                } else if (typeof data === 'string') {
                    htmlContent = data;
                } else {
                    throw new Error('Unexpected response format - no HTML content found');
                }
                
                if (htmlContent) {
                    const processedHtml = this.processHtmlContent(htmlContent);
                    
                    console.log('📄 Setting fallback HTML content in GrapesJS:', {
                        'original_length': htmlContent.length,
                        'processed_length': processedHtml.length,
                        'fallback_mode': true
                    });
                    
                    this.editor.setComponents('');
                    
                    // Use old theme asset injection method
                    this.injectLegacyThemeAssets();
                    
                    this.editor.setComponents(processedHtml);
                    
                    setTimeout(() => {
                        this.injectThemeJavaScript();
                        this.extractAndInjectWidgetConfigurations(processedHtml);
                        this.initializeWidgetFunctionality();
                    }, 1000);
                    
                    console.log('✅ Fallback page content loaded into GrapesJS');
                } else {
                    throw new Error('No HTML content found in fallback response');
                }
            } else {
                throw new Error(`Fallback API failed: HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('❌ Error in fallback method:', error);
            this.editor.setComponents('<div style="padding: 20px; text-align: center; color: #666;">Error loading page content: ' + error.message + '</div>');
        }
    }

    /**
     * Inject theme assets from hybrid API response
     */
    injectHybridAPIAssets(assetsData, themeData) {
        try {
            console.log('🎨 Injecting theme assets from hybrid API...');
            
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            if (iframe && iframe.contentDocument) {
                // Remove existing styles
                const existingStyles = iframe.contentDocument.querySelectorAll('style[data-gjs-theme], style[data-gjs-canvas-fix], style[data-gjs-hybrid]');
                existingStyles.forEach(style => style.remove());
                
                // Inject CSS assets from hybrid API
                if (assetsData && assetsData.css && Array.isArray(assetsData.css)) {
                    assetsData.css.forEach((cssFile, index) => {
                        const linkEl = iframe.contentDocument.createElement('link');
                        linkEl.rel = 'stylesheet';
                        linkEl.href = cssFile;
                        linkEl.setAttribute('data-gjs-hybrid', 'css');
                        iframe.contentDocument.head.appendChild(linkEl);
                    });
                    console.log(`✅ Injected ${assetsData.css.length} CSS files from hybrid API`);
                }
                
                // Inject theme CSS if available
                if (themeData && themeData.assets && themeData.assets.css) {
                    if (Array.isArray(themeData.assets.css)) {
                        themeData.assets.css.forEach((cssFile, index) => {
                            const linkEl = iframe.contentDocument.createElement('link');
                            linkEl.rel = 'stylesheet';
                            linkEl.href = cssFile;
                            linkEl.setAttribute('data-gjs-theme', 'css');
                            iframe.contentDocument.head.appendChild(linkEl);
                        });
                        console.log(`✅ Injected ${themeData.assets.css.length} theme CSS files`);
                    } else if (typeof themeData.assets.css === 'string') {
                        // If theme CSS is a string, inject as style tag
                        const styleEl = iframe.contentDocument.createElement('style');
                        styleEl.setAttribute('data-gjs-theme', 'inline');
                        styleEl.textContent = themeData.assets.css;
                        iframe.contentDocument.head.appendChild(styleEl);
                        console.log('✅ Injected inline theme CSS');
                    }
                }
                
                // Add canvas-specific CSS fixes
                const canvasFixEl = iframe.contentDocument.createElement('style');
                canvasFixEl.setAttribute('data-gjs-canvas-fix', 'true');
                canvasFixEl.textContent = this.getCanvasSpecificCSS();
                iframe.contentDocument.head.appendChild(canvasFixEl);
                
                console.log('✅ Hybrid API assets injected into canvas iframe');
            } else {
                console.warn('⚠️ Could not access iframe for asset injection');
            }
        } catch (error) {
            console.error('❌ Error injecting hybrid API assets:', error);
            // Fallback to legacy method
            this.injectLegacyThemeAssets();
        }
    }

    /**
     * Inject JavaScript assets from hybrid API
     */
    injectHybridAPIJavaScript(assetsData) {
        try {
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            if (iframe && iframe.contentDocument && assetsData && assetsData.js && Array.isArray(assetsData.js)) {
                // Remove existing hybrid JS
                const existingScripts = iframe.contentDocument.querySelectorAll('script[data-gjs-hybrid]');
                existingScripts.forEach(script => script.remove());
                
                // Inject JS files from hybrid API
                assetsData.js.forEach((jsFile, index) => {
                    const scriptEl = iframe.contentDocument.createElement('script');
                    scriptEl.src = jsFile;
                    scriptEl.setAttribute('data-gjs-hybrid', 'js');
                    iframe.contentDocument.body.appendChild(scriptEl);
                });
                
                console.log(`✅ Injected ${assetsData.js.length} JS files from hybrid API`);
            }
        } catch (error) {
            console.warn('⚠️ Could not inject hybrid API JavaScript:', error);
        }
    }

    /**
     * Legacy theme asset injection method (fallback)
     */
    injectLegacyThemeAssets() {
        try {
            if (this.themeAssets && this.themeAssets.css) {
                const canvas = this.editor.Canvas;
                const iframe = canvas.getFrameEl();
                
                if (iframe && iframe.contentDocument) {
                    // Remove existing theme styles
                    const existingStyles = iframe.contentDocument.querySelectorAll('style[data-gjs-theme]');
                    existingStyles.forEach(style => style.remove());
                    
                    // Add new theme styles
                    const styleEl = iframe.contentDocument.createElement('style');
                    styleEl.setAttribute('data-gjs-theme', 'legacy');
                    styleEl.textContent = this.themeAssets.css;
                    iframe.contentDocument.head.appendChild(styleEl);
                    
                    // Add canvas-specific CSS fixes
                    const canvasFixEl = iframe.contentDocument.createElement('style');
                    canvasFixEl.setAttribute('data-gjs-canvas-fix', 'true');
                    canvasFixEl.textContent = this.getCanvasSpecificCSS();
                    iframe.contentDocument.head.appendChild(canvasFixEl);
                    
                    console.log('✅ Legacy theme assets injected');
                }
            } else {
                // If no theme CSS, still inject the canvas fixes
                const canvas = this.editor.Canvas;
                const iframe = canvas.getFrameEl();
                
                if (iframe && iframe.contentDocument) {
                    const canvasFixEl = iframe.contentDocument.createElement('style');
                    canvasFixEl.setAttribute('data-gjs-canvas-fix', 'true');
                    canvasFixEl.textContent = this.getCanvasSpecificCSS();
                    iframe.contentDocument.head.appendChild(canvasFixEl);
                    
                    console.log('✅ Canvas positioning fixes injected (legacy mode)');
                }
            }
        } catch (error) {
            console.error('❌ Error in legacy theme asset injection:', error);
        }
    }

    /**
     * Inject theme JavaScript into the canvas
     */
    injectThemeJavaScript() {
        try {
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            if (iframe && iframe.contentDocument && this.themeAssets && this.themeAssets.js) {
                // Remove existing theme scripts
                const existingScripts = iframe.contentDocument.querySelectorAll('script[data-gjs-theme]');
                existingScripts.forEach(script => script.remove());
                
                // Add new theme script
                const scriptEl = iframe.contentDocument.createElement('script');
                scriptEl.setAttribute('data-gjs-theme', 'true');
                scriptEl.textContent = this.themeAssets.js;
                iframe.contentDocument.body.appendChild(scriptEl);
                
                console.log('✅ Theme JS injected into canvas iframe');
            }
        } catch (error) {
            console.warn('⚠️ Could not inject theme JS:', error);
        }
    }

    /**
     * Initialize widget-specific functionality
     */
    initializeWidgetFunctionality() {
        try {
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            if (iframe && iframe.contentDocument) {
                const doc = iframe.contentDocument;
                
                // Initialize counter animations
                this.initializeCounters(doc);
                
                // Initialize slider widgets
                this.initializeSliders(doc);
                
                // Initialize any other widget-specific functionality
                console.log('✅ Widget functionality initialized');
            }
        } catch (error) {
            console.warn('⚠️ Error initializing widget functionality:', error);
        }
    }

    /**
     * Initialize counter widgets
     */
    initializeCounters(doc) {
        try {
            const counters = doc.querySelectorAll('.counter[data-count]');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const speed = parseInt(counter.getAttribute('data-speed')) || 2000;
                
                if (!isNaN(target)) {
                    // Simple counter animation
                    let current = 0;
                    const increment = target / (speed / 50);
                    
                    const animate = () => {
                        if (current < target) {
                            current += increment;
                            if (current > target) current = target;
                            counter.textContent = Math.ceil(current);
                            setTimeout(animate, 50);
                        } else {
                            counter.textContent = target;
                        }
                    };
                    
                    animate();
                }
            });
            
            console.log('✅ Counter widgets initialized:', counters.length);
        } catch (error) {
            console.warn('⚠️ Error initializing counters:', error);
        }
    }

    /**
     * Extract widget configurations from HTML and inject into iframe
     */
    extractAndInjectWidgetConfigurations(htmlContent) {
        try {
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            if (!iframe || !iframe.contentDocument) {
                console.warn('⚠️ Cannot access iframe for widget configuration injection');
                return;
            }
            
            // Extract slider configurations from the HTML
            const sliderConfigMatches = htmlContent.match(/window\.sliderConfigs\s*=\s*{[\s\S]*?};/g);
            
            if (sliderConfigMatches) {
                sliderConfigMatches.forEach(configMatch => {
                    try {
                        // Inject the configuration into the iframe
                        const script = iframe.contentDocument.createElement('script');
                        script.textContent = `
                            if (!window.sliderConfigs) {
                                window.sliderConfigs = {};
                            }
                            ${configMatch}
                        `;
                        iframe.contentDocument.head.appendChild(script);
                        
                        console.log('✅ Injected slider configuration into iframe');
                    } catch (error) {
                        console.warn('⚠️ Error injecting slider config:', error);
                    }
                });
            }
            
            // Extract individual slider configurations
            const individualConfigMatches = htmlContent.match(/window\.sliderConfigs\['[^']+'\]\s*=\s*{[\s\S]*?};/g);
            
            if (individualConfigMatches) {
                // First ensure sliderConfigs object exists
                const initScript = iframe.contentDocument.createElement('script');
                initScript.textContent = `
                    if (!window.sliderConfigs) {
                        window.sliderConfigs = {};
                    }
                `;
                iframe.contentDocument.head.appendChild(initScript);
                
                individualConfigMatches.forEach(configMatch => {
                    try {
                        const script = iframe.contentDocument.createElement('script');
                        script.textContent = configMatch;
                        iframe.contentDocument.head.appendChild(script);
                        
                        console.log('✅ Injected individual slider configuration into iframe');
                    } catch (error) {
                        console.warn('⚠️ Error injecting individual slider config:', error);
                    }
                });
            }
            
            console.log('✅ Widget configurations extracted and injected');
            
        } catch (error) {
            console.warn('⚠️ Error extracting widget configurations:', error);
        }
    }

    /**
     * Initialize slider widgets in GrapesJS iframe
     */
    initializeSliders(doc) {
        try {
            // First ensure jQuery is available
            const jqueryCheck = () => {
                return doc.defaultView && doc.defaultView.jQuery && typeof doc.defaultView.jQuery === 'function';
            };
            
            // Check for nivo slider plugin
            const nivoSliderCheck = () => {
                return jqueryCheck() && doc.defaultView.jQuery.fn.nivoSlider;
            };
            
            // Find all slider elements
            const sliders = doc.querySelectorAll('.nivoSlider');
            
            if (sliders.length === 0) {
                console.log('📄 No slider widgets found');
                return;
            }
            
            console.log(`🎠 Found ${sliders.length} slider widget(s), checking dependencies...`);
            
            // If jQuery isn't loaded, try to inject it
            if (!jqueryCheck()) {
                this.ensureJQueryInIframe(doc, () => {
                    this.ensureNivoSliderInIframe(doc, () => {
                        this.initializeSliderElements(doc, sliders);
                    });
                });
            } else if (!nivoSliderCheck()) {
                this.ensureNivoSliderInIframe(doc, () => {
                    this.initializeSliderElements(doc, sliders);
                });
            } else {
                // Both dependencies available, initialize immediately
                this.initializeSliderElements(doc, sliders);
            }
            
        } catch (error) {
            console.warn('⚠️ Error initializing sliders:', error);
        }
    }

    /**
     * Ensure jQuery is available in iframe
     */
    ensureJQueryInIframe(doc, callback) {
        try {
            // Check if jQuery is already being loaded
            const existingJQuery = doc.querySelector('script[src*="jquery"]');
            if (existingJQuery) {
                // Wait for it to load
                existingJQuery.onload = callback;
                return;
            }
            
            // Load jQuery from theme assets or CDN
            const script = doc.createElement('script');
            script.src = '/themes/miata/lib/js/jquery.min.js'; // Adjust path as needed
            script.onload = callback;
            script.onerror = () => {
                console.warn('⚠️ Failed to load jQuery, trying CDN...');
                const cdnScript = doc.createElement('script');
                cdnScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                cdnScript.onload = callback;
                cdnScript.onerror = () => {
                    console.error('❌ Failed to load jQuery from CDN');
                };
                doc.head.appendChild(cdnScript);
            };
            doc.head.appendChild(script);
            
            console.log('📦 Loading jQuery for slider functionality...');
        } catch (error) {
            console.error('❌ Error loading jQuery:', error);
        }
    }

    /**
     * Ensure Nivo Slider plugin is available in iframe
     */
    ensureNivoSliderInIframe(doc, callback) {
        try {
            // Check if Nivo Slider is already being loaded
            const existingNivo = doc.querySelector('script[src*="nivo.slider"]');
            if (existingNivo) {
                existingNivo.onload = callback;
                return;
            }
            
            // Load Nivo Slider plugin
            const script = doc.createElement('script');
            script.src = '/themes/miata/lib/js/jquery.nivo.slider.js';
            script.onload = callback;
            script.onerror = () => {
                console.error('❌ Failed to load Nivo Slider plugin');
            };
            doc.head.appendChild(script);
            
            // Also load Nivo Slider CSS
            const link = doc.createElement('link');
            link.rel = 'stylesheet';
            link.href = '/themes/miata/lib/css/nivo-slider.css';
            doc.head.appendChild(link);
            
            console.log('📦 Loading Nivo Slider plugin for slider functionality...');
        } catch (error) {
            console.error('❌ Error loading Nivo Slider:', error);
        }
    }

    /**
     * Initialize slider elements once dependencies are loaded
     */
    initializeSliderElements(doc, sliders) {
        try {
            const $ = doc.defaultView.jQuery;
            
            if (!$ || !$.fn.nivoSlider) {
                console.error('❌ jQuery or Nivo Slider not available for initialization');
                return;
            }
            
            sliders.forEach((slider, index) => {
                const sliderId = slider.id;
                
                if (!sliderId) {
                    console.warn('⚠️ Slider missing ID, skipping...');
                    return;
                }
                
                // Get configuration from window.sliderConfigs or use default
                const sliderConfigs = doc.defaultView.sliderConfigs || {};
                const config = sliderConfigs[sliderId] || {};
                
                // Default configuration for sliders
                const defaultConfig = {
                    effect: 'random',
                    slices: 15,
                    boxCols: 8,
                    boxRows: 4,
                    animSpeed: 500,
                    pauseTime: 5000,
                    startSlide: 0,
                    directionNav: true,
                    controlNav: true,
                    pauseOnHover: true,
                    manualAdvance: false,
                    prevText: 'Prev',
                    nextText: 'Next'
                };
                
                // Merge configurations
                const finalConfig = { ...defaultConfig, ...config };
                
                // Initialize the slider
                $(slider).nivoSlider(finalConfig);
                
                console.log(`✅ Initialized slider: ${sliderId}`);
            });
            
            console.log(`✅ All ${sliders.length} slider widget(s) initialized`);
            
        } catch (error) {
            console.error('❌ Error initializing slider elements:', error);
        }
    }

    /**
     * Get canvas-specific CSS adjustments
     */
    getCanvasSpecificCSS() {
        return `/* Canvas-specific positioning fixes - Force proper flow */
*[style*="position: static"], 
*[style*="position:static"] {
    position: relative !important;
    display: block !important;
}

/* Ensure sections maintain normal document flow */
section, 
.cms-section-container,
.section-wrapper,
.breadcrumbs-area,
.counter-area,
.featured-bg-1,
.featured-bg-2,
.featured-bg-3 {
    position: relative !important;
    display: block !important;
    width: 100% !important;
    clear: both !important;
    z-index: 1 !important;
}

/* Remove any problematic positioning that GrapesJS adds */
.gjs-selected, .gjs-hovered {
    position: relative !important;
}

/* Force proper layout flow for all elements */
body {
    margin: 0;
    padding: 0;
    font-family: inherit;
    background: #ffffff;
    overflow-x: hidden;
}

/* Ensure containers don't break layout */
.container, .container-fluid {
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
    margin-left: auto;
    margin-right: auto;
    display: block !important;
}

/* Fix background attachments for canvas */
*[style*='background-attachment: fixed'] {
    background-attachment: scroll !important;
}

/* Ensure sections have proper spacing and don't overlap */
section {
    position: relative !important;
    z-index: 1;
    margin-bottom: 0;
    display: block !important;
    width: 100%;
    min-height: 50px;
}

/* Fix any absolute/fixed positioning issues */
.fixed-top, .fixed-bottom, 
.position-absolute, .position-fixed {
    position: relative !important;
    top: auto !important;
    bottom: auto !important;
    left: auto !important;
    right: auto !important;
}

/* Ensure proper image rendering */
img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* Force proper stacking context */
.row {
    display: flex !important;
    flex-wrap: wrap !important;
    margin-left: -15px;
    margin-right: -15px;
}

.col, .col-1, .col-2, .col-3, .col-4, .col-5, .col-6,
.col-7, .col-8, .col-9, .col-10, .col-11, .col-12,
.col-md, .col-md-1, .col-md-2, .col-md-3, .col-md-4,
.col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9,
.col-md-10, .col-md-11, .col-md-12,
.col-lg, .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4,
.col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9,
.col-lg-10, .col-lg-11, .col-lg-12 {
    position: relative !important;
    width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

/* Canvas-specific responsive adjustments */
@media (max-width: 768px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
}

/* GrapesJS integration styles */
.gjs-selected {
    outline: 2px solid #405189 !important;
}

.gjs-hovered {
    outline: 1px dashed #405189 !important;
}

/* Ensure proper vertical spacing between sections */
section + section {
    margin-top: 0 !important;
}`;
    }

    /**
     * Add section to canvas
     */
    addSectionToCanvas(content) {
        if (!this.editor) return;
        
        try {
            // Get current components
            const wrapper = this.editor.getComponents();
            
            // Add the new section at the end
            wrapper.add(content);
            
            console.log('✅ Section added to canvas');
        } catch (error) {
            console.error('❌ Error adding section to canvas:', error);
        }
    }
    
    /**
     * Add widget to canvas
     */
    addWidgetToCanvas(content) {
        if (!this.editor) return;
        
        try {
            // Find the selected component or get the wrapper
            const selected = this.editor.getSelected();
            const target = selected || this.editor.getComponents();
            
            // Add the widget
            target.add(content);
            
            console.log('✅ Widget added to canvas');
        } catch (error) {
            console.error('❌ Error adding widget to canvas:', error);
        }
    }
    
    /**
     * Setup canvas drop prevention for Live Preview mode
     */
    setupCanvasDropHandling() {
        if (!this.editor) {
            console.warn('⚠️ Editor not available for drop prevention setup');
            return;
        }
        
        console.log('🔧 Setting up drop prevention for Live Preview mode...');
        
        try {
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            // Setup on iframe if available
            if (iframe) {
                const checkIframeReady = () => {
                    try {
                        if (iframe.contentDocument && iframe.contentDocument.body) {
                            const iframeDoc = iframe.contentDocument;
                            const iframeBody = iframeDoc.body;
                            
                            this.addDropPrevention(iframeBody, 'iframe-body');
                            this.addDropPrevention(iframeDoc, 'iframe-document');
                            console.log('✅ Drop prevention added to iframe');
                            return true;
                        }
                        return false;
                    } catch (error) {
                        console.warn('⚠️ Error accessing iframe content:', error);
                        return false;
                    }
                };
                
                if (!checkIframeReady()) {
                    setTimeout(() => checkIframeReady(), 1000);
                }
            }
            
            // Also setup on main canvas element
            const canvasElement = canvas.getElement();
            if (canvasElement) {
                this.addDropPrevention(canvasElement, 'canvas-element');
            }
            
            // Setup on main GJS container as fallback
            const gjsContainer = document.getElementById('gjs');
            if (gjsContainer) {
                this.addDropPrevention(gjsContainer, 'gjs-container');
            }
            
            console.log('✅ Drop prevention setup complete');
            
        } catch (error) {
            console.error('❌ Error setting up drop prevention:', error);
        }
    }
    
    /**
     * Setup primary drop handlers on GrapesJS canvas
     */
    setupDropHandlers() {
        try {
            const canvas = this.editor.Canvas;
            const iframe = canvas.getFrameEl();
            
            console.log('🔧 Setting up drop handlers - Canvas:', canvas, 'Iframe:', iframe);
            
            // Setup on iframe if available
            if (iframe) {
                // Wait for iframe to be ready
                const checkIframeReady = () => {
                    try {
                        if (iframe.contentDocument && iframe.contentDocument.body) {
                            const iframeDoc = iframe.contentDocument;
                            const iframeBody = iframeDoc.body;
                            
                            this.addDropListeners(iframeBody, 'iframe-body');
                            console.log('✅ Drop handlers added to iframe body');
                            
                            // Also add to the document itself
                            this.addDropListeners(iframeDoc, 'iframe-document');
                            console.log('✅ Drop handlers added to iframe document');
                            
                            return true;
                        } else {
                            console.log('⚠️ Iframe content not ready yet, will retry...');
                            return false;
                        }
                    } catch (error) {
                        console.warn('⚠️ Error accessing iframe content:', error);
                        return false;
                    }
                };
                
                // Try immediately
                if (!checkIframeReady()) {
                    // If not ready, try again after a delay
                    setTimeout(() => {
                        if (!checkIframeReady()) {
                            console.warn('⚠️ Iframe still not ready after delay');
                        }
                    }, 1000);
                }
            } else {
                console.warn('⚠️ No iframe found in canvas');
            }
            
            // Also setup on the main canvas element
            const canvasElement = canvas.getElement();
            if (canvasElement) {
                this.addDropListeners(canvasElement, 'canvas-element');
                console.log('✅ Drop handlers added to canvas element');
            } else {
                console.warn('⚠️ No canvas element found');
            }
            
        } catch (error) {
            console.error('❌ Error setting up drop handlers:', error);
        }
    }
    
    /**
     * Setup fallback drop handlers on main container
     */
    setupFallbackDropHandlers() {
        const gjsContainer = document.getElementById('gjs');
        if (gjsContainer) {
            this.addDropListeners(gjsContainer, 'gjs-container');
            console.log('✅ Fallback drop handlers added to GJS container');
        }
    }
    
    /**
     * Add drop event listeners to an element
     */
    addDropListeners(element, elementName) {
        element.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'copy';
            console.log(`📋 Dragover on ${elementName}`);
        });
        
        element.addEventListener('dragenter', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log(`📋 Dragenter on ${elementName}`);
        });
        
        element.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            console.log(`📋 Drop event on ${elementName}`);
            
            try {
                const data = e.dataTransfer.getData('text/plain');
                console.log('📋 Drop data received:', data);
                
                if (!data) {
                    console.warn('⚠️ No drag data received');
                    return;
                }
                
                const draggedData = JSON.parse(data);
                console.log('📋 Parsed drag data:', draggedData);
                
                if (draggedData.type === 'section') {
                    console.log('📋 Processing section drop');
                    this.handleSectionDrop(draggedData);
                } else if (draggedData.type === 'widget') {
                    console.log('📋 Processing widget drop');
                    this.handleWidgetDropToCanvas(draggedData);
                } else {
                    console.warn('⚠️ Unknown drag type:', draggedData.type);
                }
                
            } catch (error) {
                console.error('❌ Error handling drop on', elementName, ':', error);
            }
        });
    }
    
    /**
     * Handle section drop
     */
    handleSectionDrop(draggedData) {
        const sections = [
            {
                id: 'full-width',
                content: '<section class="cms-section full-width-section" data-section-type="full-width"><div class="container-fluid"><div class="row"><div class="col-12"><p>Full Width Section</p></div></div></div></section>'
            },
            {
                id: 'two-columns',
                content: '<section class="cms-section two-column-section" data-section-type="two-columns"><div class="container"><div class="row"><div class="col-md-6"><p>Column 1</p></div><div class="col-md-6"><p>Column 2</p></div></div></div></section>'
            },
            {
                id: 'three-columns',
                content: '<section class="cms-section three-column-section" data-section-type="three-columns"><div class="container"><div class="row"><div class="col-md-4"><p>Column 1</p></div><div class="col-md-4"><p>Column 2</p></div><div class="col-md-4"><p>Column 3</p></div></div></div></section>'
            },
            {
                id: 'sidebar-left',
                content: '<section class="cms-section sidebar-section" data-section-type="sidebar-layout"><div class="container"><div class="row"><div class="col-md-8"><p>Main Content</p></div><div class="col-md-4"><p>Sidebar</p></div></div></div></section>'
            }
        ];
        
        const section = sections.find(s => s.id === draggedData.sectionId);
        if (section) {
            console.log('✅ Adding section to canvas:', section.id);
            this.addSectionToCanvas(section.content);
        } else {
            console.warn('⚠️ Section not found:', draggedData.sectionId);
        }
    }

    /**
     * Setup content selection modal
     */
    setupContentSelectionModal() {
        const modal = document.getElementById('contentSelectionModal');
        if (!modal) {
            console.warn('⚠️ Content selection modal not found');
            return;
        }

        const saveBtn = modal.querySelector('#saveContentSelectionBtn');
        if (saveBtn) {
            // Remove existing event listeners to prevent duplicates
            saveBtn.removeEventListener('click', this.saveWidgetWithContentHandler);
            
            // Create a bound handler function
            this.saveWidgetWithContentHandler = this.saveWidgetWithContent.bind(this);
            
            // Add new event listener
            saveBtn.addEventListener('click', this.saveWidgetWithContentHandler);
            
            console.log('✅ GrapesJS content selection modal setup complete');
        }
    }

    /**
     * Handle widget drop to canvas
     */
    async handleWidgetDropToCanvas(widgetData) {
        try {
            console.log('🎯 Handling widget drop to GrapesJS canvas:', widgetData);
            
            // Store widget data for content selection
            this.currentWidget = {
                ...widgetData,
                target: 'grapesjs-canvas'
            };
            
            // Open content selection modal
            this.openContentSelectionModal(widgetData);
            
        } catch (error) {
            console.error('❌ Error handling widget drop to canvas:', error);
            this.showError('Failed to process widget drop');
        }
    }

    /**
     * Open content selection modal
     */
    async openContentSelectionModal(widgetData) {
        const modal = document.getElementById('contentSelectionModal');
        const modalTitle = document.getElementById('contentSelectionModalLabel');
        const contentTypeSelect = document.getElementById('contentTypeSelect');
        const contentItemsList = document.getElementById('contentItemsList');
        const selectedContentItemInput = document.getElementById('selectedContentItemId');
        
        if (!modal) {
            console.error('❌ Content selection modal not found');
            return;
        }

        // Set modal title
        if (modalTitle) {
            modalTitle.textContent = `Configure ${widgetData.name || widgetData.label || 'Widget'}`;
        }

        // Reset modal state
        contentTypeSelect.innerHTML = '<option value="" selected disabled>Select content type</option>';
        contentTypeSelect.disabled = true;
        contentItemsList.innerHTML = '';
        selectedContentItemInput.value = '';

        // Show modal
        this.modalInstance = new bootstrap.Modal(modal);
        this.modalInstance.show();

        // Load content types for this widget
        await this.loadContentTypes(widgetData, contentTypeSelect, contentItemsList, selectedContentItemInput);
    }

    /**
     * Load content types for widget
     */
    async loadContentTypes(widgetData, contentTypeSelect, contentItemsList, selectedContentItemInput) {
        try {
            contentTypeSelect.innerHTML = '<option value="" selected disabled>Loading content types...</option>';
            
            const widgetId = widgetData.id;
            const url = `/admin/api/widgets/${widgetId}/content-types`;
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch content types');
            }
            
            const data = await response.json();
            const types = data.content_types || data.data || [];
            
            contentTypeSelect.innerHTML = '<option value="" selected disabled>Select content type</option>';
            
            if (types.length === 0) {
                contentTypeSelect.innerHTML += '<option disabled>No content types available</option>';
            } else {
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.setAttribute('data-slug', type.slug);
                    option.textContent = type.name + (type.description ? ` - ${type.description}` : '');
                    contentTypeSelect.appendChild(option);
                });
                
                // If only one content type exists, auto-select it
                if (types.length === 1) {
                    contentTypeSelect.selectedIndex = 1;
                    setTimeout(() => contentTypeSelect.dispatchEvent(new Event('change')), 0);
                }
            }
            
            contentTypeSelect.disabled = false;
            
            // Listen for content type selection
            contentTypeSelect.onchange = async function() {
                const selectedOption = contentTypeSelect.options[contentTypeSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    contentItemsList.innerHTML = '';
                    selectedContentItemInput.value = '';
                    return;
                }
                
                const contentTypeId = selectedOption.value;
                await this.loadContentItems(contentTypeId, contentItemsList, selectedContentItemInput);
            }.bind(this);
            
        } catch (error) {
            console.error('❌ Error loading content types:', error);
            contentTypeSelect.innerHTML = '<option disabled>Error loading content types</option>';
            contentTypeSelect.disabled = true;
        }
    }

    /**
     * Load content items for selected content type
     */
    async loadContentItems(contentTypeId, contentItemsList, selectedContentItemInput) {
        try {
            contentItemsList.innerHTML = '<div class="text-muted">Loading content items...</div>';
            
            const url = `/admin/api/content/${contentTypeId}`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch content items');
            }
            
            const data = await response.json();
            const items = data.items || data.data || [];
            
            if (items.length === 0) {
                contentItemsList.innerHTML = '<div class="alert alert-warning">No content items found for this content type.</div>';
                selectedContentItemInput.value = '';
            } else {
                // Render as a checkbox list (allowing multiple selections)
                let html = '<div class="list-group">';
                items.forEach(item => {
                    const itemId = item.id;
                    const itemTitle = item.title || item.name || 'Untitled';
                    html += `<label class='list-group-item'>
                        <input type='checkbox' name='contentItemCheckbox' value='${itemId}' class='form-check-input me-2'>
                        ${itemTitle} <span class='text-muted'>#${itemId}</span>
                    </label>`;
                });
                html += '</div>';
                contentItemsList.innerHTML = html;
                
                // Add event listeners to checkboxes
                const checkboxes = contentItemsList.querySelectorAll("input[type='checkbox'][name='contentItemCheckbox']");
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        // Get all checked values
                        const checkedValues = Array.from(checkboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.value);
                        selectedContentItemInput.value = checkedValues.join(',');
                    });
                });
                
                // Auto-select the first item
                if (checkboxes.length > 0) {
                    checkboxes[0].checked = true;
                    selectedContentItemInput.value = checkboxes[0].value;
                }
            }
            
        } catch (error) {
            console.error('❌ Error loading content items:', error);
            contentItemsList.innerHTML = '<div class="alert alert-danger">Error loading content items.</div>';
            selectedContentItemInput.value = '';
        }
    }

    /**
     * Save widget with selected content
     */
    async saveWidgetWithContent() {
        // Prevent multiple simultaneous saves
        if (this.isSaving) {
            console.log('⚠️ Save already in progress, ignoring duplicate request');
            return;
        }

        if (!this.currentWidget) {
            this.showError('No widget data available');
            return;
        }

        const modal = document.getElementById('contentSelectionModal');
        const contentTypeSelect = modal.querySelector('#contentTypeSelect');
        const selectedContentItemInput = modal.querySelector('#selectedContentItemId');
        const saveBtn = modal.querySelector('#saveContentSelectionBtn');

        const selectedTypeId = contentTypeSelect.value;
        const selectedContentItemIds = selectedContentItemInput.value;

        if (!selectedTypeId) {
            this.showError('Please select a content type.');
            return;
        }

        if (!selectedContentItemIds) {
            this.showError('Please select at least one content item.');
            return;
        }

        // Set saving flag
        this.isSaving = true;
        console.log('🔄 Starting GrapesJS widget save process...');

        // Show loading indicator on live preview
        this.showLivePreviewLoading(true);

        // Disable save button
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            // Parse content item IDs
            const contentItemIds = selectedContentItemIds.split(',').map(id => parseInt(id.trim()));

            console.log('📝 Creating PageSectionWidget for GrapesJS with data:', {
                widgetId: this.currentWidget.id,
                contentTypeId: selectedTypeId,
                contentItemIds: contentItemIds
            });

            // Create PageSectionWidget (for GrapesJS, we create a virtual section)
            const pageSectionWidget = await this.createPageSectionWidget(this.currentWidget, contentItemIds, selectedTypeId);

            if (pageSectionWidget) {
                // Add widget to GrapesJS canvas
                await this.addWidgetToGrapesJSCanvas(pageSectionWidget);

                // Close modal
                this.modalInstance.hide();

                // Reload live preview to show the newly added widget
                await this.reloadLivePreview();

                // Show success message
                this.showSuccess('Widget added successfully to GrapesJS');

                console.log('✅ GrapesJS widget created and added to canvas:', pageSectionWidget);
            }

        } catch (error) {
            console.error('❌ Error saving GrapesJS widget:', error);
            this.showError('Failed to save widget');
        } finally {
            // Clear saving flag
            this.isSaving = false;
            
            // Hide loading indicator
            this.showLivePreviewLoading(false);
            
            // Re-enable save button
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Widget';
            
            console.log('🔄 GrapesJS widget save process completed');
        }
    }

    /**
     * Create PageSectionWidget in database (adapted for GrapesJS)
     */
    async createPageSectionWidget(widgetData, contentItemIds, contentTypeId) {
        try {
            console.log('📝 Creating PageSectionWidget for GrapesJS:', { widgetData, contentItemIds, contentTypeId });

            // For GrapesJS, we create a virtual section or use the page directly
            const widgetPayload = {
                page_id: this.pageId, // Direct page association for GrapesJS widgets
                widget_id: widgetData.id,
                position: 1, // Will be calculated by backend
                grid_x: 0,
                grid_y: 0,
                grid_w: 12, // Full width for GrapesJS widgets
                grid_h: 4, // Default height
                grid_id: `grapesjs_widget_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                column_position: 0,
                settings: {
                    widget_type: widgetData.type || widgetData.slug,
                    widget_name: widgetData.name || widgetData.label,
                    width: 'full',
                    order: 0,
                    locked: false,
                    grapesjs_widget: true, // Flag to identify GrapesJS widgets
                    target: 'grapesjs-canvas'
                },
                content_query: {
                    content_type_id: parseInt(contentTypeId),
                    content_item_ids: contentItemIds,
                    query_type: 'multiple',
                    filters: {},
                    sort_by: 'created_at',
                    sort_order: 'desc',
                    limit: contentItemIds.length
                },
                css_classes: '',
                padding: { top: 10, bottom: 10, left: 10, right: 10 },
                margin: { top: 0, bottom: 20, left: 0, right: 0 },
                min_height: null,
                max_height: null
            };

            const response = await fetch('/admin/api/page-section-widgets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(widgetPayload)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to create widget');
            }

            const result = await response.json();
            return result.data;

        } catch (error) {
            console.error('❌ Error creating PageSectionWidget for GrapesJS:', error);
            throw error;
        }
    }

    /**
     * Add widget to GrapesJS canvas
     */
    async addWidgetToGrapesJSCanvas(pageSectionWidget) {
        try {
            console.log('🎨 Adding widget to GrapesJS canvas:', pageSectionWidget);
            
            // Create widget HTML content
            const widgetHtml = await this.createGrapesJSWidgetHtml(pageSectionWidget);
            
            // Add to GrapesJS canvas
            const wrapper = this.editor.getComponents();
            wrapper.add(widgetHtml);
            
            console.log('✅ Widget added to GrapesJS canvas');
            
        } catch (error) {
            console.error('❌ Error adding widget to GrapesJS canvas:', error);
        }
    }

    /**
     * Create widget HTML for GrapesJS canvas
     */
    async createGrapesJSWidgetHtml(pageSectionWidget) {
        const widgetType = pageSectionWidget.settings.widget_type;
        const widgetName = pageSectionWidget.settings.widget_name;
        const widgetId = pageSectionWidget.widget_id;

        try {
            console.log('🎨 Rendering GrapesJS widget content for:', {
                widgetId: widgetId,
                widgetType: widgetType,
                pageSectionWidgetId: pageSectionWidget.id
            });

            const apiUrl = `/admin/api/widgets/${widgetId}/render?page_section_widget_id=${pageSectionWidget.id}`;
            console.log('🌐 Calling API URL:', apiUrl);

            // Fetch actual widget HTML from API
            const response = await fetch(apiUrl, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            console.log('📡 API Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('📦 API Response data:', data);
                
                if (data.success && data.html) {
                    console.log('✅ GrapesJS widget content rendered successfully');
                    
                    // For GrapesJS, we wrap the content in a component-friendly structure
                    return `
                        <div class="grapesjs-widget-container" data-widget-type="${widgetType}" data-page-section-widget-id="${pageSectionWidget.id}" data-widget-id="${widgetId}">
                            ${data.html}
                        </div>
                    `;
                } else {
                    console.warn('⚠️ GrapesJS widget render failed:', data.error || 'No error message provided');
                    return this.createFallbackGrapesJSWidgetHtml(pageSectionWidget, data.error || 'Failed to render widget');
                }
            } else {
                const errorText = await response.text();
                console.error('❌ GrapesJS widget render request failed:', response.status);
                console.error('❌ Error response:', errorText);
                return this.createFallbackGrapesJSWidgetHtml(pageSectionWidget, `Failed to load widget (HTTP ${response.status})`);
            }

        } catch (error) {
            console.error('❌ Error rendering GrapesJS widget content:', error);
            return this.createFallbackGrapesJSWidgetHtml(pageSectionWidget, 'Error loading widget: ' + error.message);
        }
    }

    /**
     * Create fallback widget HTML for GrapesJS when rendering fails
     */
    createFallbackGrapesJSWidgetHtml(pageSectionWidget, errorMessage) {
        const widgetType = pageSectionWidget.settings.widget_type;
        const widgetName = pageSectionWidget.settings.widget_name;

        return `
            <div class="grapesjs-widget-container" data-widget-type="${widgetType}" data-page-section-widget-id="${pageSectionWidget.id}">
                <div class="text-center p-4 border border-dashed border-secondary rounded">
                    <i class="ri-apps-line fs-1 text-muted"></i>
                    <h6 class="mt-2">${widgetName}</h6>
                    <small class="text-muted">${errorMessage}</small>
                </div>
            </div>
        `;
    }

    /**
     * Show/hide loading indicator on live preview
     */
    showLivePreviewLoading(show) {
        let loadingOverlay = document.getElementById('grapesjs-loading-overlay');
        
        if (show) {
            if (!loadingOverlay) {
                // Create loading overlay
                loadingOverlay = document.createElement('div');
                loadingOverlay.id = 'grapesjs-loading-overlay';
                loadingOverlay.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75';
                loadingOverlay.style.zIndex = '9999';
                loadingOverlay.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Updating preview...</div>
                    </div>
                `;
                
                const canvas = document.getElementById('gjs');
                if (canvas) {
                    canvas.style.position = 'relative';
                    canvas.appendChild(loadingOverlay);
                }
            }
            loadingOverlay.style.display = 'flex';
        } else {
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        }
    }

    /**
     * Reload live preview after widget save
     */
    async reloadLivePreview() {
        try {
            console.log('🔄 Reloading GrapesJS live preview...');
            
            // Reload the complete page content
            await this.loadCompletePageContent();
            
            console.log('✅ GrapesJS live preview reloaded successfully');
            
        } catch (error) {
            console.error('❌ Error reloading GrapesJS live preview:', error);
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        console.log('✅ Success:', message);
        // You can implement a proper notification system here
        // For now, we'll use a simple alert or console log
    }

    /**
     * Show error message
     */
    showError(message) {
        console.error('❌ Error:', message);
        alert(message);
    }
    
    /**
     * Collapse left sidebar for Live Preview mode
     */
    collapseLeftSidebar() {
        const leftSidebarContainer = document.getElementById('leftSidebarContainer');
        const toggleBtn = document.getElementById('toggleLeftSidebarBtn');
        
        if (leftSidebarContainer && !leftSidebarContainer.classList.contains('collapsed')) {
            leftSidebarContainer.classList.add('collapsed');
            
            if (toggleBtn) {
                toggleBtn.classList.add('active');
            }
            
            console.log('✅ Left sidebar collapsed for Live Preview mode');
        }
    }
    
    /**
     * Expand left sidebar when leaving Live Preview mode
     */
    expandLeftSidebar() {
        const leftSidebarContainer = document.getElementById('leftSidebarContainer');
        const toggleBtn = document.getElementById('toggleLeftSidebarBtn');
        
        if (leftSidebarContainer && leftSidebarContainer.classList.contains('collapsed')) {
            leftSidebarContainer.classList.remove('collapsed');
            
            if (toggleBtn) {
                toggleBtn.classList.remove('active');
            }
            
            console.log('✅ Left sidebar expanded');
        }
    }
    
    /**
     * Show modal directing user to Layout Designer tab
     */
    showLayoutDesignerModal(itemType) {
        const modalHtml = `
            <div class="modal fade" id="layoutDesignerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="ri-information-line me-2"></i>
                                Live Preview Mode
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <div class="mb-4">
                                <i class="ri-layout-${itemType === 'widget' ? 'grid' : 'row'}-line text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="mb-3">Cannot Add ${itemType === 'widget' ? 'Widgets' : 'Sections'} in Live Preview</h6>
                            <p class="text-muted mb-4">
                                Live Preview mode is for editing content and styling only.<br>
                                To add ${itemType === 'widget' ? 'widgets' : 'sections'}, please use the <strong>Layout Designer</strong> tab.
                            </p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="button" class="btn btn-primary" onclick="switchToLayoutDesigner()">
                                    <i class="ri-layout-line me-1"></i>
                                    Go to Layout Designer
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Stay in Live Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if present
        const existingModal = document.getElementById('layoutDesignerModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('layoutDesignerModal'));
        modal.show();
        
        // Add global function to switch tabs
        window.switchToLayoutDesigner = () => {
            const layoutTab = document.querySelector('[data-bs-target="#layout"]');
            if (layoutTab) {
                new bootstrap.Tab(layoutTab).show();
                modal.hide();
            }
        };
        
        // Clean up modal after hiding
        document.getElementById('layoutDesignerModal').addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                const modalElement = document.getElementById('layoutDesignerModal');
                if (modalElement) {
                    modalElement.remove();
                }
                delete window.switchToLayoutDesigner;
            }, 300);
        });
    }

    /**
     * Test widget modal (for debugging)
     */
    testWidgetModal() {
        console.log('🧪 Testing widget modal...');
        const testWidget = {
            id: 1,
            name: 'Test Widget',
            slug: 'test-widget',
            type: 'widget',
            category: 'test'
        };
        this.handleWidgetDropToCanvas(testWidget);
    }

    /**
     * Debug drop handlers (for debugging)
     */
    debugDropHandlers() {
        console.log('🔍 Debugging drop handlers...');
        console.log('Editor:', this.editor);
        if (this.editor) {
            const canvas = this.editor.Canvas;
            console.log('Canvas:', canvas);
            const iframe = canvas.getFrameEl();
            console.log('Iframe:', iframe);
            if (iframe && iframe.contentDocument) {
                console.log('Iframe document:', iframe.contentDocument);
                console.log('Iframe body:', iframe.contentDocument.body);
            }
        }
        const gjsContainer = document.getElementById('gjs');
        console.log('GJS Container:', gjsContainer);
    }

    /**
     * Destroy the GrapesJS editor
     */
    destroy() {
        if (this.editor) {
            try {
                if (typeof this.editor.destroy === 'function') {
                    this.editor.destroy();
                } else if (typeof this.editor.remove === 'function') {
                    this.editor.remove();
                }
            } catch (error) {
                console.warn('⚠️ Error destroying editor:', error);
            }
            this.editor = null;
        }
        this.initialized = false;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the page designer
    const pageIdElement = document.querySelector('#gjs[data-page-id]');
    if (pageIdElement) {
        const pageId = pageIdElement.getAttribute('data-page-id');
        
        // Initialize GrapesJS Designer
        window.GrapesJSDesigner = new GrapesJSDesigner();
        window.GrapesJSDesigner.init({
            pageId: pageId,
            csrfToken: window.csrfToken
        });
        
        // Add debugging functions to window for console testing
        window.testGrapesJSModal = () => window.GrapesJSDesigner.testWidgetModal();
        window.debugGrapesJSDropHandlers = () => window.GrapesJSDesigner.debugDropHandlers();
        window.testGrapesJSDropHandlers = () => {
            console.log('🧪 Testing drop handlers setup...');
            window.GrapesJSDesigner.setupCanvasDropHandling();
        };
        window.testGrapesJSHybridAPI = () => {
            console.log('🧪 Testing hybrid API...');
            window.GrapesJSDesigner.loadCompletePageContent();
        };
        window.testGrapesJSFallback = () => {
            console.log('🧪 Testing fallback method...');
            window.GrapesJSDesigner.loadCompletePageContentFallback();
        };
        window.testSliderInitialization = () => {
            console.log('🧪 Testing slider initialization...');
            const canvas = window.GrapesJSDesigner.editor?.Canvas;
            const iframe = canvas?.getFrameEl();
            if (iframe && iframe.contentDocument) {
                window.GrapesJSDesigner.initializeSliders(iframe.contentDocument);
            } else {
                console.warn('⚠️ No iframe available for slider testing');
            }
        };
        window.debugSliderElements = () => {
            console.log('🧪 Debugging slider elements...');
            const canvas = window.GrapesJSDesigner.editor?.Canvas;
            const iframe = canvas?.getFrameEl();
            if (iframe && iframe.contentDocument) {
                const doc = iframe.contentDocument;
                const sliders = doc.querySelectorAll('.nivoSlider');
                console.log('Sliders found:', sliders.length);
                console.log('jQuery available:', !!doc.defaultView.jQuery);
                console.log('Nivo Slider available:', !!(doc.defaultView.jQuery && doc.defaultView.jQuery.fn.nivoSlider));
                console.log('Slider configs:', doc.defaultView.sliderConfigs);
                sliders.forEach((slider, i) => {
                    console.log(`Slider ${i}:`, {
                        id: slider.id,
                        classes: slider.className,
                        images: slider.querySelectorAll('img').length
                    });
                });
            }
        };
        
        console.log('🧪 Debug functions available:', {
            testGrapesJSModal: 'testGrapesJSModal()',
            debugDropHandlers: 'debugGrapesJSDropHandlers()',
            testDropHandlers: 'testGrapesJSDropHandlers()',
            testHybridAPI: 'testGrapesJSHybridAPI()',
            testFallback: 'testGrapesJSFallback()',
            testSliderInit: 'testSliderInitialization()',
            debugSliders: 'debugSliderElements()'
        });
    }
});