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
        
        this.initialized = true;
    }

    /**
     * Setup tab switching listener
     */
    setupTabListener() {
        document.addEventListener('shown.bs.tab', (e) => {
            if (e.target.getAttribute('data-bs-target') === '#preview') {
                console.log('🔄 Preview tab activated, initializing GrapesJS...');
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
        
        // Initialize GrapesJS with minimal configuration
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
            // Minimal panels - only essential tools
            panels: {
                defaults: [
                    {
                        id: 'basic-actions',
                        el: '.panel__basic-actions',
                        buttons: [
                            {
                                id: 'visibility',
                                active: true,
                                className: 'btn btn-sm btn-outline-secondary',
                                label: '<i class="ri-eye-line"></i>',
                                command: 'sw-visibility',
                            },
                            {
                                id: 'export',
                                className: 'btn btn-sm btn-outline-secondary',
                                label: '<i class="ri-code-line"></i>',
                                command: 'export-template',
                                context: 'export-template',
                            }
                        ],
                    },
                    {
                        id: 'panel-devices',
                        el: '.panel__devices',
                        buttons: [
                            {
                                id: 'device-desktop',
                                label: '<i class="ri-computer-line"></i>',
                                className: 'btn btn-sm btn-outline-primary active',
                                command: 'set-device-desktop',
                                active: true,
                                togglable: false,
                            },
                            {
                                id: 'device-tablet',
                                label: '<i class="ri-tablet-line"></i>',
                                className: 'btn btn-sm btn-outline-primary',
                                command: 'set-device-tablet',
                                togglable: false,
                            },
                            {
                                id: 'device-mobile',
                                label: '<i class="ri-smartphone-line"></i>',
                                className: 'btn btn-sm btn-outline-primary',
                                command: 'set-device-mobile',
                                togglable: false,
                            },
                        ],
                    },
                ]
            }
        });
        
        // Add device commands AFTER GrapesJS is fully initialized
        this.editor.on('load', () => {
            this.addDeviceCommands();
            console.log('✅ GrapesJS editor loaded and device commands added');
        });
        
        // Load complete page content into GrapesJS
        await this.loadCompletePageContent();
        
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
     * Load complete page content from API
     */
    async loadCompletePageContent() {
        try {
            console.log('📄 Loading complete page content...');
            
            const response = await fetch(`/admin/api/pages/${this.pageId}/render`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            console.log('📡 API Response status:', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('📄 API Response data keys:', Object.keys(data));
                
                // Handle different response formats
                let htmlContent = null;
                
                if (data.html) {
                    // API format: {html: "...", page: {...}}
                    htmlContent = data.html;
                } else if (data.success && data.html) {
                    // Alternative format: {success: true, html: "..."}
                    htmlContent = data.html;
                } else if (typeof data === 'string') {
                    // String format: direct HTML
                    htmlContent = data;
                } else {
                    console.warn('⚠️ Unexpected response format:', data);
                    throw new Error('Unexpected response format - no HTML content found');
                }
                
                if (htmlContent) {
                    // Process HTML content to fix common issues
                    const processedHtml = this.processHtmlContent(htmlContent);
                    
                    console.log('📄 Setting HTML content in GrapesJS:', {
                        'original_length': htmlContent.length,
                        'processed_length': processedHtml.length,
                        'contains_breadcrumbs': processedHtml.includes('breadcrumbs-area'),
                        'contains_counter': processedHtml.includes('counter-area'),
                        'contains_featured_bg': processedHtml.includes('featured-bg-')
                    });
                    
                    // Clear existing components first
                    this.editor.setComponents('');
                    
                    // Add theme CSS to canvas if available
                    if (this.themeAssets && this.themeAssets.css) {
                        const canvas = this.editor.Canvas;
                        const iframe = canvas.getFrameEl();
                        
                        if (iframe && iframe.contentDocument) {
                            // Remove existing theme styles
                            const existingStyles = iframe.contentDocument.querySelectorAll('style[data-gjs-theme]');
                            existingStyles.forEach(style => style.remove());
                            
                            // Remove existing canvas-specific styles
                            const existingCanvasStyles = iframe.contentDocument.querySelectorAll('style[data-gjs-canvas-fix]');
                            existingCanvasStyles.forEach(style => style.remove());
                            
                            // Add new theme styles
                            const styleEl = iframe.contentDocument.createElement('style');
                            styleEl.setAttribute('data-gjs-theme', 'true');
                            styleEl.textContent = this.themeAssets.css;
                            iframe.contentDocument.head.appendChild(styleEl);
                            
                            // Add canvas-specific CSS fixes
                            const canvasFixEl = iframe.contentDocument.createElement('style');
                            canvasFixEl.setAttribute('data-gjs-canvas-fix', 'true');
                            canvasFixEl.textContent = this.getCanvasSpecificCSS();
                            iframe.contentDocument.head.appendChild(canvasFixEl);
                            
                            console.log('✅ Theme CSS and canvas fixes injected into canvas iframe');
                        }
                    } else {
                        // If no theme CSS, still inject the canvas fixes
                        const canvas = this.editor.Canvas;
                        const iframe = canvas.getFrameEl();
                        
                        if (iframe && iframe.contentDocument) {
                            // Remove existing canvas-specific styles
                            const existingCanvasStyles = iframe.contentDocument.querySelectorAll('style[data-gjs-canvas-fix]');
                            existingCanvasStyles.forEach(style => style.remove());
                            
                            // Add canvas-specific CSS fixes
                            const canvasFixEl = iframe.contentDocument.createElement('style');
                            canvasFixEl.setAttribute('data-gjs-canvas-fix', 'true');
                            canvasFixEl.textContent = this.getCanvasSpecificCSS();
                            iframe.contentDocument.head.appendChild(canvasFixEl);
                            
                            console.log('✅ Canvas positioning fixes injected into canvas iframe');
                        }
                    }
                    
                    // Set the processed HTML content in GrapesJS
                    this.editor.setComponents(processedHtml);
                    
                    // Wait a bit for components to render, then inject JS
                    setTimeout(() => {
                        this.injectThemeJavaScript();
                        this.initializeWidgetFunctionality();
                    }, 1000);
                    
                    console.log('✅ Complete page content loaded into GrapesJS');
                } else {
                    throw new Error('No HTML content found in response');
                }
            } else {
                console.warn('⚠️ Failed to load page content:', response.status, response.statusText);
                const errorText = await response.text();
                console.error('❌ Error response:', errorText);
                this.editor.setComponents('<div style="padding: 20px; text-align: center; color: #666;">Error loading page content (HTTP ' + response.status + ')</div>');
            }
        } catch (error) {
            console.error('❌ Error loading page content:', error);
            this.editor.setComponents('<div style="padding: 20px; text-align: center; color: #666;">Error loading page content: ' + error.message + '</div>');
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
     * Get canvas-specific CSS adjustments
     */
    getCanvasSpecificCSS() {
        return `
        /* Canvas-specific positioning fixes - Force proper flow */
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
        }
        `;
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
    }
});