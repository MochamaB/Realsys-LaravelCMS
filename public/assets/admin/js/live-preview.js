// live-preview.js - Theme Integration Module
// This module adds live preview functionality to the existing page designer

window.LivePreview = window.LivePreview || {};

/**
 * Initialize live preview functionality
 * @param {Object} editor - The GrapesJS editor instance
 */
window.LivePreview.init = function(editor) {
    console.log('🎨 Initializing Live Preview Module...');
    
    let themeAssets = null;
    let canvasCSS = '';
    
    // Load theme assets
    async function loadThemeAssets() {
        try {
            console.log('📦 Loading theme assets...');
            
            // Load theme asset information
            const assetsResponse = await fetch('/admin/api/themes/active/assets', {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (assetsResponse.ok) {
                const assetsData = await assetsResponse.json();
                themeAssets = assetsData.theme;
                console.log('✅ Theme assets loaded:', themeAssets);
            } else {
                console.warn('⚠️ Failed to load theme assets:', assetsResponse.status);
            }

            // Load theme CSS for canvas
            const cssResponse = await fetch('/admin/api/themes/active/canvas-styles', {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (cssResponse.ok) {
                const cssData = await cssResponse.json();
                canvasCSS = cssData.css || '';
                console.log('✅ Theme CSS loaded, length:', canvasCSS.length);
            } else {
                console.warn('⚠️ Failed to load theme CSS:', cssResponse.status);
            }
            
            return { themeAssets, canvasCSS };
        } catch (error) {
            console.error('❌ Error loading theme assets:', error);
            return { themeAssets: null, canvasCSS: '' };
        }
    }
    
    // Inject theme CSS into canvas
    function injectThemeCSS() {
        if (!canvasCSS) return;
        
        console.log('💉 Injecting theme CSS into canvas...');
        
        // Method 1: Add CSS rules to the CSS composer
        try {
            editor.CssComposer.addRules(canvasCSS);
            console.log('✅ CSS injected via CssComposer');
        } catch (e) {
            console.warn('⚠️ CssComposer failed, trying direct injection:', e.message);
        }
        
        // Method 2: Direct style injection into canvas iframe
        setTimeout(() => {
            try {
                const iframe = editor.Canvas.getFrameEl();
                if (iframe && iframe.contentDocument) {
                    const doc = iframe.contentDocument;
                    let styleEl = doc.getElementById('theme-styles');
                    
                    if (!styleEl) {
                        styleEl = doc.createElement('style');
                        styleEl.id = 'theme-styles';
                        styleEl.type = 'text/css';
                        doc.head.appendChild(styleEl);
                    }
                    
                    styleEl.textContent = canvasCSS;
                    console.log('✅ CSS injected directly into canvas iframe');
                }
            } catch (e) {
                console.warn('⚠️ Direct CSS injection failed:', e.message);
            }
        }, 1000);
    }
    
    // Enhanced widget component with live rendering
    function enhanceWidgetComponent() {
        const originalWidget = editor.DomComponents.getType('widget');
        
        editor.DomComponents.addType('widget', {
            ...originalWidget,
            model: {
                ...originalWidget.model,
                loadWidgetHTML: async function(widgetId) {
                    try {
                        console.log('📦 Loading live widget HTML for ID:', widgetId);
                        
                        const response = await fetch(`/admin/api/widgets/${widgetId}/render`, {
                            headers: {
                                'X-CSRF-TOKEN': window.csrfToken,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.html) {
                                this.components(data.html);
                                console.log('✅ Live widget HTML loaded successfully');
                            } else {
                                console.warn('⚠️ Widget render failed:', data.error);
                                this.components(`<div class="alert alert-warning">${data.error || 'Failed to render widget'}</div>`);
                            }
                        } else {
                            console.error('❌ Widget render request failed:', response.status);
                            this.components('<div class="alert alert-danger">Failed to load widget</div>');
                        }
                    } catch (error) {
                        console.error('❌ Error loading widget HTML:', error);
                        this.components('<div class="alert alert-danger">Error loading widget</div>');
                    }
                },
                
                init() {
                    // Call original init if it exists
                    if (originalWidget.model.init) {
                        originalWidget.model.init.call(this);
                    }
                    
                    // Add live preview functionality
                    const widgetId = this.get('widgetId');
                    if (widgetId) {
                        this.loadWidgetHTML(widgetId);
                    }
                }
            }
        });
    }
    
    // Auto-refresh functionality
    function setupAutoRefresh() {
        let refreshTimeout;
        const refreshDelay = 1000;

        function scheduleRefresh() {
            clearTimeout(refreshTimeout);
            refreshTimeout = setTimeout(async () => {
                try {
                    console.log('🔄 Refreshing canvas content...');
                    await refreshCanvasContent();
                } catch (error) {
                    console.error('❌ Error refreshing canvas:', error);
                }
            }, refreshDelay);
        }

        async function refreshCanvasContent() {
            const widgets = editor.DomComponents.getWrapper().find('[data-widget-type="widget"]');
            
            for (let widget of widgets) {
                const widgetId = widget.get('widgetId') || widget.get('attributes')['data-widget-id'];
                if (widgetId && widget.loadWidgetHTML) {
                    await widget.loadWidgetHTML(widgetId);
                }
            }
            
            console.log('✅ Canvas content refreshed');
        }

        // Listen for changes
        editor.on('component:update', scheduleRefresh);
        editor.on('component:add', scheduleRefresh);
        editor.on('component:remove', scheduleRefresh);
        
        return refreshCanvasContent;
    }
    
    // Add refresh button to toolbar
    function addRefreshButton(refreshFunction) {
        const refreshButton = document.createElement('button');
        refreshButton.className = 'btn btn-sm btn-outline-success ms-2';
        refreshButton.innerHTML = '<i class="ri-refresh-line"></i> Live Preview';
        refreshButton.onclick = refreshFunction;
        refreshButton.title = 'Refresh live preview with theme styling';
        
        const toolbar = document.querySelector('.panel__basic-actions');
        if (toolbar) {
            toolbar.appendChild(refreshButton);
        }
    }
    
    // Initialize live preview
    async function initialize() {
        // Load theme assets
        const assets = await loadThemeAssets();
        
        // Wait for editor to be ready
        editor.on('load', function() {
            console.log('📝 Editor ready, setting up live preview...');
            
            // Inject theme CSS
            injectThemeCSS();
            
            // Enhance widget components
            enhanceWidgetComponent();
            
            // Setup auto-refresh
            const refreshFunction = setupAutoRefresh();
            
            // Add refresh button
            addRefreshButton(refreshFunction);
            
            console.log('🎉 Live Preview Module initialized successfully!');
        });
    }
    
    // Start initialization
    initialize();
};

// Auto-initialize if editor is already available
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for the main page designer to initialize
    setTimeout(() => {
        if (window.editor) {
            window.LivePreview.init(window.editor);
        }
    }, 2000);
}); 