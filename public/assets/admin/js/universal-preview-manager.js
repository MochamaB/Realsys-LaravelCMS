/**
 * Universal Preview Manager
 * Handles live preview functionality for both widgets and content items
 * Phase 4.2 - JavaScript Implementation
 */
class UniversalPreviewManager {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/admin/api',
            cacheTimeout: 300000, // 5 minutes
            retryAttempts: 3,
            retryDelay: 1000,
            autoRefresh: false,
            autoRefreshInterval: 30000,
            ...options
        };

        this.cache = new Map();
        this.activeRequests = new Map();
        this.autoRefreshTimer = null;
        this.currentPreviewData = null;
        this.eventListeners = new Map();

        this.init();
    }

    init() {
        // Initialize CSRF token for API requests
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Set up global error handler
        this.setupErrorHandler();
        
        console.log('UniversalPreviewManager initialized');
    }

    setupErrorHandler() {
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.source === 'UniversalPreviewManager') {
                console.error('UniversalPreviewManager Error:', event.reason);
                this.showError('An unexpected error occurred during preview rendering.');
            }
        });
    }

    // Event Management
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }

    off(event, callback) {
        if (this.eventListeners.has(event)) {
            const listeners = this.eventListeners.get(event);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in event listener for ${event}:`, error);
                }
            });
        }
    }

    // Cache Management
    getCacheKey(type, id, params = {}) {
        const paramString = Object.keys(params).sort().map(key => `${key}=${params[key]}`).join('&');
        return `${type}_${id}_${paramString}`;
    }

    getFromCache(key) {
        const cached = this.cache.get(key);
        if (cached && Date.now() - cached.timestamp < this.options.cacheTimeout) {
            return cached.data;
        }
        this.cache.delete(key);
        return null;
    }

    setCache(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }

    clearCache(pattern = null) {
        if (pattern) {
            for (const [key] of this.cache) {
                if (key.includes(pattern)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
        this.emit('cache-cleared', { pattern });
    }

    // API Request Methods
    async makeRequest(url, options = {}) {
        const requestKey = `${url}_${JSON.stringify(options)}`;
        
        // Check if request is already in progress
        if (this.activeRequests.has(requestKey)) {
            return this.activeRequests.get(requestKey);
        }

        const requestOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        };

        const requestPromise = this.executeRequest(url, requestOptions);
        this.activeRequests.set(requestKey, requestPromise);

        try {
            const result = await requestPromise;
            return result;
        } finally {
            this.activeRequests.delete(requestKey);
        }
    }

    async executeRequest(url, options, attempt = 1) {
        try {
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success && data.error) {
                throw new Error(data.error);
            }

            return data;
        } catch (error) {
            if (attempt < this.options.retryAttempts) {
                await this.delay(this.options.retryDelay * attempt);
                return this.executeRequest(url, options, attempt + 1);
            }
            
            error.source = 'UniversalPreviewManager';
            throw error;
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Widget Content Options API
    async getWidgetContentOptions(widgetId, useCache = true) {
        const cacheKey = this.getCacheKey('widget_content_options', widgetId);
        
        if (useCache) {
            const cached = this.getFromCache(cacheKey);
            if (cached) return cached;
        }

        try {
            const url = `${this.options.baseUrl}/widgets/${widgetId}/content-options`;
            const data = await this.makeRequest(url);
            
            if (useCache) {
                this.setCache(cacheKey, data);
            }
            
            this.emit('content-options-loaded', { widgetId, data });
            return data;
        } catch (error) {
            this.emit('error', { type: 'content-options', widgetId, error });
            throw error;
        }
    }

    // Content Widget Options API
    async getContentWidgetOptions(params = {}, useCache = true) {
        const cacheKey = this.getCacheKey('content_widget_options', 'all', params);
        
        if (useCache) {
            const cached = this.getFromCache(cacheKey);
            if (cached) return cached;
        }

        try {
            const queryString = new URLSearchParams(params).toString();
            const url = `${this.options.baseUrl}/content/widget-options${queryString ? '?' + queryString : ''}`;
            const data = await this.makeRequest(url);
            
            if (useCache) {
                this.setCache(cacheKey, data);
            }
            
            this.emit('widget-options-loaded', { params, data });
            return data;
        } catch (error) {
            this.emit('error', { type: 'widget-options', params, error });
            throw error;
        }
    }

    // Widget with Content Rendering API
    async renderWidgetWithContent(widgetId, params = {}) {
        try {
            this.emit('render-start', { type: 'widget-with-content', widgetId, params });
            
            const url = `${this.options.baseUrl}/widgets/${widgetId}/render-with-content`;
            const data = await this.makeRequest(url, {
                method: 'POST',
                body: JSON.stringify(params)
            });
            
            this.currentPreviewData = {
                type: 'widget-with-content',
                widgetId,
                params,
                data,
                timestamp: Date.now()
            };
            
            this.emit('render-complete', this.currentPreviewData);
            return data;
        } catch (error) {
            this.emit('render-error', { type: 'widget-with-content', widgetId, params, error });
            throw error;
        }
    }

    // Content with Widget Rendering API
    async renderContentWithWidget(params = {}) {
        try {
            this.emit('render-start', { type: 'content-with-widget', params });
            
            const url = `${this.options.baseUrl}/content/render-with-widget`;
            const data = await this.makeRequest(url, {
                method: 'POST',
                body: JSON.stringify(params)
            });
            
            this.currentPreviewData = {
                type: 'content-with-widget',
                params,
                data,
                timestamp: Date.now()
            };
            
            this.emit('render-complete', this.currentPreviewData);
            return data;
        } catch (error) {
            this.emit('render-error', { type: 'content-with-widget', params, error });
            throw error;
        }
    }

    // UI Helper Methods
    updatePreviewContainer(containerId, html, assets = {}) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`Preview container ${containerId} not found`);
            return;
        }

        // Update HTML content
        container.innerHTML = html;

        // Load CSS assets - handle both string and array formats
        if (assets.css) {
            this.loadCssAssets(assets.css);
        }

        // Load JS assets - handle both string and array formats
        if (assets.js) {
            this.loadJsAssets(assets.js);
        }

        this.emit('preview-updated', { containerId, html, assets });
    }

    loadCssAssets(cssData) {
        // Handle both string (inline CSS) and array (URLs) formats
        if (typeof cssData === 'string' && cssData.trim()) {
            // Inline CSS - inject as style tag
            const styleId = 'universal-preview-css-' + Date.now();
            let existingStyle = document.getElementById(styleId);
            
            if (!existingStyle) {
                existingStyle = document.createElement('style');
                existingStyle.id = styleId;
                existingStyle.type = 'text/css';
                document.head.appendChild(existingStyle);
            }
            
            existingStyle.textContent = cssData;
        } else if (Array.isArray(cssData)) {
            // Array of URLs - load as link tags
            cssData.forEach(url => {
                if (url && !document.querySelector(`link[href="${url}"]`)) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = url;
                    document.head.appendChild(link);
                }
            });
        }
    }

    loadJsAssets(jsData) {
        // Handle both string (inline JS) and array (URLs) formats
        if (typeof jsData === 'string' && jsData.trim()) {
            // Inline JS - inject as script tag
            const scriptId = 'universal-preview-js-' + Date.now();
            let existingScript = document.getElementById(scriptId);
            
            if (!existingScript) {
                existingScript = document.createElement('script');
                existingScript.id = scriptId;
                existingScript.type = 'text/javascript';
                document.head.appendChild(existingScript);
            }
            
            existingScript.textContent = jsData;
        } else if (Array.isArray(jsData)) {
            // Array of URLs - load as script tags
            jsData.forEach(url => {
                if (url && !document.querySelector(`script[src="${url}"]`)) {
                    const script = document.createElement('script');
                    script.src = url;
                    script.async = true;
                    document.head.appendChild(script);
                }
            });
        }
    }

    showLoading(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading preview...</span>
                    </div>
                    <span class="ms-3">Loading preview...</span>
                </div>
            `;
        }
    }

    showError(message, containerId = null) {
        const errorHtml = `
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bx bx-error-circle me-2"></i>
                <div>
                    <strong>Preview Error:</strong> ${message}
                </div>
            </div>
        `;

        if (containerId) {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = errorHtml;
            }
        } else {
            // Show global error notification
            this.showNotification(message, 'error');
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Auto-refresh Management
    startAutoRefresh(callback, interval = null) {
        this.stopAutoRefresh();
        
        const refreshInterval = interval || this.options.autoRefreshInterval;
        this.autoRefreshTimer = setInterval(() => {
            if (typeof callback === 'function') {
                callback();
            }
        }, refreshInterval);
        
        this.emit('auto-refresh-started', { interval: refreshInterval });
    }

    stopAutoRefresh() {
        if (this.autoRefreshTimer) {
            clearInterval(this.autoRefreshTimer);
            this.autoRefreshTimer = null;
            this.emit('auto-refresh-stopped');
        }
    }

    // Utility Methods
    getCurrentPreviewData() {
        return this.currentPreviewData;
    }

    getStats() {
        return {
            cacheSize: this.cache.size,
            activeRequests: this.activeRequests.size,
            autoRefreshActive: !!this.autoRefreshTimer,
            currentPreview: this.currentPreviewData ? this.currentPreviewData.type : null
        };
    }

    destroy() {
        this.stopAutoRefresh();
        this.clearCache();
        this.activeRequests.clear();
        this.eventListeners.clear();
        this.currentPreviewData = null;
        
        console.log('UniversalPreviewManager destroyed');
    }
}

// Global instance
window.UniversalPreviewManager = UniversalPreviewManager;

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.universalPreviewManager) {
            window.universalPreviewManager = new UniversalPreviewManager();
        }
    });
} else {
    if (!window.universalPreviewManager) {
        window.universalPreviewManager = new UniversalPreviewManager();
    }
}
