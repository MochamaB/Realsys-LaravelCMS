/**
 * Theme Integration Manager for GrapesJS Page Designer
 * Phase 3.1 Implementation
 * 
 * Handles loading and injecting theme assets (CSS/JS) into the GrapesJS canvas
 * to ensure the designer preview matches the actual frontend appearance.
 */

class ThemeIntegrationManager {
    constructor(editor) {
        this.editor = editor;
        this.themeAssets = {
            css: null,
            js: null,
            loaded: false
        };
        this.injectionStatus = {
            css: false,
            js: false
        };
        
        // Configuration
        this.config = {
            apiBaseUrl: '/admin/api',
            retryAttempts: 3,
            retryDelay: 1000,
            cacheTimeout: 300000 // 5 minutes
        };
        
        console.log('🎨 Theme Integration Manager initialized');
        this.init();
    }
    
    /**
     * Initialize theme integration
     */
    async init() {
        try {
            console.log('🔄 Loading theme assets for canvas integration...');
            
            // Load theme assets
            await this.loadThemeAssets();
            
            // Wait for canvas to be ready
            this.waitForCanvas(() => {
                this.injectThemeAssets();
            });
            
            // Set up event listeners
            this.setupEventListeners();
            
            console.log('✅ Theme integration initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize theme integration:', error);
        }
    }
    
    /**
     * Load theme assets from API
     */
    async loadThemeAssets() {
        try {
            // Load CSS and JS in parallel
            const [cssResponse, jsResponse] = await Promise.all([
                this.loadThemeCSS(),
                this.loadThemeJS()
            ]);
            
            this.themeAssets.css = cssResponse;
            this.themeAssets.js = jsResponse;
            this.themeAssets.loaded = true;
            
            console.log('📦 Theme assets loaded:', {
                css_size: cssResponse?.css?.length || 0,
                js_size: jsResponse?.js?.length || 0,
                css_files: cssResponse?.files_loaded?.length || 0,
                js_files: jsResponse?.files_loaded?.length || 0
            });
            
        } catch (error) {
            console.error('❌ Failed to load theme assets:', error);
            throw error;
        }
    }
    
    /**
     * Load theme CSS from API
     */
    async loadThemeCSS() {
        try {
            // Get CSRF token
            const csrfToken = window.csrfToken || 
                            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                            document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                console.warn('⚠️ CSRF token not found, API request may fail');
            }
            
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };
            
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            console.log('🔐 Making CSS API request with headers:', Object.keys(headers));
            
            const response = await fetch(`${this.config.apiBaseUrl}/themes/active/canvas-styles`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin' // Include cookies for session authentication
            });
            
            console.log('📡 CSS API response:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                headers: Object.fromEntries(response.headers.entries())
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ CSS API error response body:', errorText);
                throw new Error(`CSS API returned ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load CSS');
            }
            
            return data;
            
        } catch (error) {
            console.error('❌ Failed to load theme CSS:', error);
            throw error;
        }
    }
    
    /**
     * Load theme JavaScript from API
     */
    async loadThemeJS() {
        try {
            // Get CSRF token
            const csrfToken = window.csrfToken || 
                            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                            document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                console.warn('⚠️ CSRF token not found, API request may fail');
            }
            
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };
            
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            console.log('🔐 Making JS API request with headers:', Object.keys(headers));
            
            const response = await fetch(`${this.config.apiBaseUrl}/themes/active/canvas-scripts`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin' // Include cookies for session authentication
            });
            
            console.log('📡 JS API response:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ JS API error response body:', errorText);
                throw new Error(`JS API returned ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load JavaScript');
            }
            
            return data;
            
        } catch (error) {
            console.error('❌ Failed to load theme JavaScript:', error);
            throw error;
        }
    }
    
    /**
     * Wait for GrapesJS canvas to be ready
     */
    waitForCanvas(callback) {
        const checkCanvas = () => {
            const canvas = this.editor.Canvas;
            const canvasElement = canvas.getElement();
            const canvasDocument = canvas.getDocument();
            
            if (canvasElement && canvasDocument) {
                console.log('🎯 Canvas is ready for theme injection');
                callback();
            } else {
                console.log('⏳ Waiting for canvas to be ready...');
                setTimeout(checkCanvas, 500);
            }
        };
        
        checkCanvas();
    }
    
    /**
     * Inject theme assets into canvas
     */
    async injectThemeAssets() {
        if (!this.themeAssets.loaded) {
            console.warn('⚠️ Theme assets not loaded yet');
            return;
        }
        
        try {
            console.log('💉 Injecting theme assets into canvas...');
            
            // Inject CSS
            if (this.themeAssets.css && this.themeAssets.css.css) {
                await this.injectCSS(this.themeAssets.css.css);
                this.injectionStatus.css = true;
                console.log('✅ Theme CSS injected successfully');
            }
            
            // Inject JavaScript
            if (this.themeAssets.js && this.themeAssets.js.js) {
                await this.injectJS(this.themeAssets.js.js);
                this.injectionStatus.js = true;
                console.log('✅ Theme JavaScript injected successfully');
            }
            
            // Only trigger a gentle refresh, don't force aggressive canvas updates
            console.log('🎨 Theme integration completed successfully');
            
        } catch (error) {
            console.error('❌ Failed to inject theme assets:', error);
        }
    }
    
    /**
     * Inject CSS into canvas
     */
    async injectCSS(css) {
        try {
            const canvasDocument = this.editor.Canvas.getDocument();
            
            // Remove existing theme styles
            const existingStyle = canvasDocument.getElementById('theme-integration-styles');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            // Create new style element
            const styleElement = canvasDocument.createElement('style');
            styleElement.id = 'theme-integration-styles';
            styleElement.type = 'text/css';
            styleElement.textContent = css;
            
            // Inject into canvas head
            canvasDocument.head.appendChild(styleElement);
            
            console.log('📝 CSS injected into canvas head');
            
        } catch (error) {
            console.error('❌ Failed to inject CSS:', error);
            throw error;
        }
    }
    
    /**
     * Inject JavaScript into canvas
     */
    async injectJS(js) {
        try {
            const canvasDocument = this.editor.Canvas.getDocument();
            
            // Remove existing theme scripts
            const existingScript = canvasDocument.getElementById('theme-integration-scripts');
            if (existingScript) {
                existingScript.remove();
            }
            
            // Create new script element
            const scriptElement = canvasDocument.createElement('script');
            scriptElement.id = 'theme-integration-scripts';
            scriptElement.type = 'text/javascript';
            scriptElement.textContent = js;
            
            // Inject into canvas head
            canvasDocument.head.appendChild(scriptElement);
            
            console.log('📝 JavaScript injected into canvas head');
            
        } catch (error) {
            console.error('❌ Failed to inject JavaScript:', error);
            throw error;
        }
    }
    
    /**
     * Refresh canvas to apply changes
     */
    refreshCanvas() {
        try {
            // Trigger canvas refresh with error handling
            const canvasElement = this.editor.Canvas.getElement();
            if (canvasElement) {
                canvasElement.style.display = 'none';
                canvasElement.offsetHeight; // Force reflow
                canvasElement.style.display = '';
            }
            
            // Trigger GrapesJS refresh events with error handling
            try {
                this.editor.trigger('canvas:update');
            } catch (error) {
                console.warn('⚠️ Error triggering canvas:update event:', error);
            }
            
            try {
                this.editor.refresh();
            } catch (error) {
                console.warn('⚠️ Error calling editor.refresh():', error);
            }
            
            console.log('🔄 Canvas refreshed');
            
        } catch (error) {
            console.error('❌ Failed to refresh canvas:', error);
            // Don't throw the error, just log it to prevent breaking the flow
        }
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Re-inject assets when canvas is updated (but avoid triggering widget refresh conflicts)
        this.editor.on('canvas:update', () => {
            if (this.themeAssets.loaded && !this.isRefreshing) {
                // Use a flag to prevent recursive refreshes
                this.isRefreshing = true;
                setTimeout(() => {
                    this.injectThemeAssets().finally(() => {
                        this.isRefreshing = false;
                    });
                }, 100);
            }
        });
        
        // Re-inject assets when components are added/removed (but be careful about timing)
        this.editor.on('component:add component:remove', () => {
            if (this.themeAssets.loaded && !this.isRefreshing) {
                // Debounce to avoid too many rapid re-injections
                clearTimeout(this.componentChangeTimeout);
                this.componentChangeTimeout = setTimeout(() => {
                    if (!this.isRefreshing) {
                        this.isRefreshing = true;
                        this.injectThemeAssets().finally(() => {
                            this.isRefreshing = false;
                        });
                    }
                }, 200);
            }
        });
        
        // Handle device changes
        this.editor.on('change:device', () => {
            if (this.themeAssets.loaded && !this.isRefreshing) {
                setTimeout(() => {
                    if (!this.isRefreshing) {
                        this.refreshCanvas();
                    }
                }, 100);
            }
        });
        
        // Initialize refresh prevention flag
        this.isRefreshing = false;
        this.componentChangeTimeout = null;
    }
    
    /**
     * Reload theme assets (useful for theme switching)
     */
    async reloadThemeAssets() {
        console.log('🔄 Reloading theme assets...');
        
        this.themeAssets.loaded = false;
        this.injectionStatus.css = false;
        this.injectionStatus.js = false;
        
        await this.loadThemeAssets();
        await this.injectThemeAssets();
        
        console.log('✅ Theme assets reloaded successfully');
    }
    
    /**
     * Get current integration status
     */
    getStatus() {
        return {
            assets_loaded: this.themeAssets.loaded,
            css_injected: this.injectionStatus.css,
            js_injected: this.injectionStatus.js,
            theme_info: this.themeAssets.css?.theme || null
        };
    }
}

// Export for global use
window.ThemeIntegrationManager = ThemeIntegrationManager;

// Auto-initialize when editor is available
document.addEventListener('DOMContentLoaded', function() {
    // Wait for editor and other systems to be available
    const initThemeIntegration = () => {
        if (window.editor) {
            // Wait a bit longer to ensure other systems are initialized
            console.log('🎨 Initializing Theme Integration Manager...');
            
            // Check if we should wait for widget components
            if (window.widgetComponentFactory) {
                console.log('🔗 Widget components detected, coordinating initialization...');
            }
            
            window.themeIntegrationManager = new ThemeIntegrationManager(window.editor);
        } else {
            console.log('⏳ Waiting for editor to be available...');
            setTimeout(initThemeIntegration, 1000);
        }
    };
    
    // Give more time for all systems to initialize
    setTimeout(initThemeIntegration, 3000);
});

console.log('📦 Theme Integration Manager module loaded'); 