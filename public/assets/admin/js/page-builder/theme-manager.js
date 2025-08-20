/**
 * Theme Manager
 * 
 * Handles loading theme assets (CSS/JS) and applying them to the canvas
 * to show the real frontend appearance during page building.
 */
class ThemeManager {
    constructor(api) {
        this.api = api;
        this.themeAssets = {
            css: [],
            js: []
        };
        this.loadedAssets = new Set(); // Track loaded assets to avoid duplicates
        this.initialized = false;
        
        console.log('🎨 Theme Manager initialized');
    }

    /**
     * Initialize theme manager and load assets
     */
    async init() {
        try {
            console.log('🔄 Initializing Theme Manager...');
            
            // Load theme assets from API
            await this.loadThemeAssets();
            
            // Apply theme assets to the canvas
            await this.applyThemeToCanvas();
            
            this.initialized = true;
            console.log('✅ Theme Manager initialized successfully');
            
        } catch (error) {
            console.error('❌ Error initializing Theme Manager:', error);
        }
    }

    /**
     * Load theme assets from the API
     */
    async loadThemeAssets() {
        try {
            console.log('🔄 Loading theme assets from API...');
            
            const response = await this.api.getThemeAssets();
            
            if (response.success && response.data) {
                this.themeAssets = response.data;
                console.log('✅ Theme assets loaded:', this.themeAssets);
                
                return this.themeAssets;
            } else {
                console.warn('⚠️ No theme assets received from API, using defaults');
                this.themeAssets = this.getDefaultAssets();
                return this.themeAssets;
            }
            
        } catch (error) {
            console.error('❌ Error loading theme assets:', error);
            console.log('📦 Using default theme assets as fallback');
            this.themeAssets = this.getDefaultAssets();
            return this.themeAssets;
        }
    }

    /**
     * Get default theme assets for fallback
     */
    getDefaultAssets() {
        return {
            css: [
                '/assets/css/bootstrap.min.css',
                '/assets/css/theme.css'
            ],
            js: [
                '/assets/js/bootstrap.bundle.min.js'
            ],
            theme: {
                name: 'Default Theme',
                version: '1.0.0'
            }
        };
    }

    /**
     * Apply theme assets to the canvas for preview
     */
    async applyThemeToCanvas() {
        try {
            console.log('🎨 Applying theme to canvas...');
            
            // Create or get theme container
            const container = this.getThemeContainer();
            
            // Load CSS assets
            if (this.themeAssets.css && this.themeAssets.css.length > 0) {
                await this.loadCSSAssets(this.themeAssets.css);
            }
            
            // Load JS assets (optional, mainly for interactive widgets)
            if (this.themeAssets.js && this.themeAssets.js.length > 0) {
                await this.loadJSAssets(this.themeAssets.js);
            }
            
            // Add theme wrapper classes to canvas
            this.applyThemeStyles(container);
            
            console.log('✅ Theme applied to canvas successfully');
            
        } catch (error) {
            console.error('❌ Error applying theme to canvas:', error);
        }
    }

    /**
     * Get or create theme container (wraps the gridstack container)
     */
    getThemeContainer() {
        const gridContainer = document.getElementById('gridStackContainer');
        if (!gridContainer) {
            throw new Error('GridStack container not found');
        }
        
        // Check if theme wrapper already exists
        let themeContainer = gridContainer.querySelector('.theme-preview-wrapper');
        
        if (!themeContainer) {
            // Create theme wrapper
            themeContainer = document.createElement('div');
            themeContainer.className = 'theme-preview-wrapper';
            themeContainer.style.cssText = `
                min-height: 100vh;
                background: white;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                line-height: 1.6;
                color: #333;
            `;
            
            // Move existing content into theme wrapper
            while (gridContainer.firstChild) {
                themeContainer.appendChild(gridContainer.firstChild);
            }
            
            gridContainer.appendChild(themeContainer);
            
            console.log('📦 Created theme preview wrapper');
        }
        
        return themeContainer;
    }

    /**
     * Load CSS assets into the page
     */
    async loadCSSAssets(cssFiles) {
        console.log('🎨 Loading CSS assets:', cssFiles);
        
        const promises = cssFiles.map(cssFile => this.loadCSSFile(cssFile));
        await Promise.allSettled(promises);
        
        console.log('✅ CSS assets loading completed');
    }

    /**
     * Load a single CSS file
     */
    async loadCSSFile(cssFile) {
        return new Promise((resolve, reject) => {
            // Skip if already loaded
            if (this.loadedAssets.has(cssFile)) {
                resolve();
                return;
            }
            
            // Check if it's a relative path and make it absolute
            const cssPath = cssFile.startsWith('/') ? cssFile : `/${cssFile}`;
            
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = cssPath;
            
            link.onload = () => {
                console.log('✅ Loaded CSS:', cssPath);
                this.loadedAssets.add(cssFile);
                resolve();
            };
            
            link.onerror = () => {
                console.warn('⚠️ Failed to load CSS:', cssPath);
                this.loadedAssets.add(cssFile); // Mark as attempted
                resolve(); // Don't reject, just continue
            };
            
            document.head.appendChild(link);
        });
    }

    /**
     * Load JS assets into the page
     */
    async loadJSAssets(jsFiles) {
        console.log('📜 Loading JS assets:', jsFiles);
        
        const promises = jsFiles.map(jsFile => this.loadJSFile(jsFile));
        await Promise.allSettled(promises);
        
        console.log('✅ JS assets loading completed');
    }

    /**
     * Load a single JS file
     */
    async loadJSFile(jsFile) {
        return new Promise((resolve, reject) => {
            // Skip if already loaded
            if (this.loadedAssets.has(jsFile)) {
                resolve();
                return;
            }
            
            // Check if it's a relative path and make it absolute
            const jsPath = jsFile.startsWith('/') ? jsFile : `/${jsFile}`;
            
            const script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = jsPath;
            
            script.onload = () => {
                console.log('✅ Loaded JS:', jsPath);
                this.loadedAssets.add(jsFile);
                resolve();
            };
            
            script.onerror = () => {
                console.warn('⚠️ Failed to load JS:', jsPath);
                this.loadedAssets.add(jsFile); // Mark as attempted
                resolve(); // Don't reject, just continue
            };
            
            document.head.appendChild(script);
        });
    }

    /**
     * Apply theme-specific styling to the container
     */
    applyThemeStyles(container) {
        // Add theme-specific classes based on loaded theme
        container.classList.add('theme-preview');
        
        if (this.themeAssets.theme?.slug) {
            container.classList.add(`theme-${this.themeAssets.theme.slug}`);
        }
        
        // Add basic layout structure classes
        container.classList.add('frontend-preview');
        
        console.log('✅ Applied theme styles to container');
    }

    /**
     * Create layout structure with header, main content, and footer
     */
    createLayoutStructure(container) {
        // Check if structure already exists
        if (container.querySelector('.layout-structure')) {
            return container.querySelector('.layout-structure');
        }
        
        const layoutStructure = document.createElement('div');
        layoutStructure.className = 'layout-structure';
        layoutStructure.innerHTML = `
            <header class="site-header">
                <!-- Header content will be populated by header sections -->
            </header>
            
            <main class="site-main">
                <div class="page-content">
                    <!-- Page sections and widgets will be rendered here -->
                </div>
            </main>
            
            <footer class="site-footer">
                <!-- Footer content will be populated by footer sections -->
            </footer>
        `;
        
        // Move existing content into main content area
        const mainContent = layoutStructure.querySelector('.page-content');
        while (container.firstChild && !container.firstChild.classList?.contains('layout-structure')) {
            mainContent.appendChild(container.firstChild);
        }
        
        container.appendChild(layoutStructure);
        
        console.log('🏗️ Created layout structure');
        return layoutStructure;
    }

    /**
     * Wrap sections in appropriate layout containers based on section type
     */
    wrapSectionInLayout(sectionElement, sectionType) {
        const sectionWrapper = document.createElement('div');
        sectionWrapper.className = `section-wrapper section-${sectionType}`;
        
        // Add container classes based on section type
        switch (sectionType) {
            case 'header':
                sectionWrapper.classList.add('header-section');
                break;
            case 'footer':
                sectionWrapper.classList.add('footer-section');
                break;
            case 'full-width':
                sectionWrapper.classList.add('full-width-section');
                break;
            default:
                sectionWrapper.classList.add('content-section', 'container');
        }
        
        // Wrap the section element
        sectionElement.parentNode?.insertBefore(sectionWrapper, sectionElement);
        sectionWrapper.appendChild(sectionElement);
        
        return sectionWrapper;
    }

    /**
     * Get current theme information
     */
    getThemeInfo() {
        return this.themeAssets.theme || { name: 'Unknown Theme', version: '1.0.0' };
    }

    /**
     * Refresh theme assets
     */
    async refresh() {
        console.log('🔄 Refreshing theme assets...');
        
        await this.loadThemeAssets();
        await this.applyThemeToCanvas();
        
        console.log('✅ Theme assets refreshed');
    }

    /**
     * Check if theme is ready
     */
    isReady() {
        return this.initialized && this.themeAssets && 
               (this.themeAssets.css?.length > 0 || this.themeAssets.js?.length > 0);
    }
}

// Export for global use
window.ThemeManager = ThemeManager;

console.log('📦 Theme Manager module loaded');