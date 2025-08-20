/**
 * Live Designer API
 * Handles all API communication for the GrapesJS Live Designer
 */
class LiveDesignerAPI {
    constructor(baseUrl, csrfToken) {
        this.baseUrl = baseUrl;
        this.csrfToken = csrfToken;
        
        console.log('🔌 LiveDesignerAPI initialized with baseUrl:', baseUrl);
    }
    
    /**
     * Load complete page content for the canvas
     */
    async loadPageContent(pageId) {
        try {
            console.log('📥 Loading page content for page:', pageId);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/content`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Page content loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error loading page content:', error);
            
            // Return fallback content
            return {
                success: false,
                error: error.message,
                data: {
                    html: `<div style="padding: 2rem; text-align: center; color: #dc3545;">
                        <h1>Error Loading Content</h1>
                        <p>Could not load page content: ${error.message}</p>
                        <p>Please check the console for more details.</p>
                    </div>`,
                    assets: { css: [], js: [] }
                }
            };
        }
    }
    
    /**
     * Get iframe preview URL
     */
    getIframePreviewUrl(pageId) {
        return `${this.baseUrl}/pages/${pageId}/iframe-preview`;
    }
    
    /**
     * Load theme assets for the page
     */
    async loadAssets(pageId) {
        try {
            console.log('🎨 Loading assets for page:', pageId);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/assets`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Assets loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error loading assets:', error);
            return {
                success: false,
                error: error.message,
                data: { theme_assets: { css: [], js: [] } }
            };
        }
    }
    
    /**
     * Save page content (placeholder for future implementation)
     */
    async savePageContent(pageId, content) {
        console.log('💾 Saving page content (not implemented yet):', pageId, content);
        
        // TODO: Implement save functionality
        return {
            success: true,
            message: 'Save functionality will be implemented in Phase 3'
        };
    }
    
    /**
     * Test API connectivity
     */
    async testConnection(pageId) {
        try {
            console.log('🔍 Testing API connection...');
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/assets`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            const isConnected = response.ok;
            console.log(isConnected ? '✅ API connection successful' : '❌ API connection failed');
            
            return isConnected;
            
        } catch (error) {
            console.error('❌ API connection test failed:', error);
            return false;
        }
    }
}

// Export for global use
window.LiveDesignerAPI = LiveDesignerAPI;
