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
     * Load structured component tree for the page
     */
    async loadPageComponents(pageId) {
        try {
            console.log('🌳 Loading page components for page:', pageId);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/components`, {
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
            console.log('✅ Page components loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error loading page components:', error);
            return {
                success: false,
                error: error.message,
                data: { sections: [] }
            };
        }
    }

    /**
     * Load available widgets for component library
     */
    async loadWidgets(pageId) {
        try {
            console.log('🧩 Loading widgets for page:', pageId);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/widgets`, {
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
            console.log('✅ Widgets loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error loading widgets:', error);
            return {
                success: false,
                error: error.message,
                data: { widgets: {} }
            };
        }
    }

    /**
     * Load available content types
     */
    async loadContentTypes(pageId) {
        try {
            console.log('📚 Loading content types for page:', pageId);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/content-types`, {
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
            console.log('✅ Content types loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error loading content types:', error);
            return {
                success: false,
                error: error.message,
                data: { content_types: [] }
            };
        }
    }

    /**
     * Load content items for a specific content type
     */
    async loadContentItems(pageId, contentTypeId, options = {}) {
        try {
            console.log('📄 Loading content items for page:', pageId, 'content type:', contentTypeId);
            
            const params = new URLSearchParams({
                content_type_id: contentTypeId,
                ...options
            });
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/content-items?${params}`, {
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
            console.log('✅ Content items loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error loading content items:', error);
            return {
                success: false,
                error: error.message,
                data: { items: [], pagination: {}, content_type: {} }
            };
        }
    }

    /**
     * Update component (section or widget) settings
     */
    async updateComponent(pageId, componentData) {
        try {
            console.log('✏️ Updating component for page:', pageId, componentData);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/components`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(componentData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Component updated successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error updating component:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Get component preview after updates
     */
    async getComponentPreview(pageId, componentId, componentType) {
        try {
            console.log('👀 Getting component preview for page:', pageId, 'component:', componentId);
            
            const params = new URLSearchParams({
                component_id: componentId,
                component_type: componentType
            });
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/components/preview?${params}`, {
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
            console.log('✅ Component preview loaded successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error getting component preview:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Refresh page content (full page, section, or widget)
     */
    async refreshPageContent(pageId, refreshType, componentId = null, includeAssets = false) {
        try {
            console.log('🔄 Refreshing page content:', pageId, refreshType, componentId);
            
            const requestData = {
                refresh_type: refreshType,
                include_assets: includeAssets
            };
            
            if (componentId) {
                requestData.component_id = componentId;
            }
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/refresh`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(requestData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Page content refreshed successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error refreshing page content:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Save page content
     */
    async savePageContent(pageId, content) {
        try {
            console.log('💾 Saving page content for page:', pageId);
            
            const response = await fetch(`${this.baseUrl}/pages/${pageId}/save`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(content)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Page content saved successfully:', data);
            
            return data;
            
        } catch (error) {
            console.error('❌ Error saving page content:', error);
            return {
                success: false,
                error: error.message
            };
        }
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
