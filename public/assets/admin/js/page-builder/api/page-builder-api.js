/**
 * PageBuilder API Layer
 * 
 * Handles all communication with the PageBuilder API endpoints.
 * Provides a clean interface for CRUD operations and error handling.
 */
class PageBuilderAPI {
    constructor(config = {}) {
        this.baseUrl = config.baseUrl || '/admin/api';
        this.csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
        this.pageId = config.pageId;
        
        // Default headers
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
        };

        console.log('🔗 PageBuilder API initialized:', {
            baseUrl: this.baseUrl,
            pageId: this.pageId,
            hasCsrf: !!this.csrfToken
        });
    }

    // =====================================================================
    // SECTION API METHODS
    // =====================================================================

    /**
     * Get all sections for the current page
     */
    async getSections() {
        return await this.makeRequest('GET', `/pages/${this.pageId}/sections`);
    }

    /**
     * Create a new section
     */
    async createSection(sectionData) {
        return await this.makeRequest('POST', `/pages/${this.pageId}/sections`, sectionData);
    }

    /**
     * Update section properties
     */
    async updateSection(sectionId, sectionData) {
        return await this.makeRequest('PUT', `/sections/${sectionId}`, sectionData);
    }

    /**
     * Delete a section
     */
    async deleteSection(sectionId) {
        return await this.makeRequest('DELETE', `/sections/${sectionId}`);
    }

    /**
     * Update section GridStack position
     */
    async updateSectionPosition(sectionId, positionData) {
        return await this.makeRequest('PATCH', `/sections/${sectionId}/position`, positionData);
    }

    // =====================================================================
    // WIDGET API METHODS
    // =====================================================================

    /**
     * Get all widgets in a section
     */
    async getSectionWidgets(sectionId) {
        return await this.makeRequest('GET', `/page-sections/${sectionId}/widgets`);
    }

    /**
     * Create a new widget in a section
     */
    async createWidget(sectionId, widgetData) {
        return await this.makeRequest('POST', `/sections/${sectionId}/widgets`, widgetData);
    }

    /**
     * Update widget properties
     */
    async updateWidget(widgetId, widgetData) {
        return await this.makeRequest('PUT', `/widgets/${widgetId}`, widgetData);
    }

    /**
     * Delete a widget
     */
    async deleteWidget(widgetId) {
        return await this.makeRequest('DELETE', `/widgets/${widgetId}`);
    }

    /**
     * Update widget GridStack position
     */
    async updateWidgetPosition(widgetId, positionData) {
        return await this.makeRequest('PATCH', `/widgets/${widgetId}/position`, positionData);
    }

    /**
     * Render widget for preview
     */
    async renderWidget(widgetId, renderData = {}) {
        return await this.makeRequest('POST', `/widgets/${widgetId}/render`, renderData);
    }

    // =====================================================================
    // SIDEBAR CONTENT API METHODS
    // =====================================================================

    /**
     * Get available widgets for drag & drop
     */
    async getAvailableWidgets() {
        return await this.makeRequest('GET', '/widgets/available');
    }

    /**
     * Get available section templates
     */
    async getTemplateSections() {
        return await this.makeRequest('GET', '/templates/sections');
    }

    // =====================================================================
    // THEME & ASSETS API METHODS
    // =====================================================================

    /**
     * Get theme assets for canvas
     */
    async getThemeAssets() {
        return await this.makeRequest('GET', '/theme/assets');
    }

    // =====================================================================
    // CORE HTTP METHODS
    // =====================================================================

    /**
     * Make HTTP request with error handling
     */
    async makeRequest(method, endpoint, data = null) {
        const url = `${this.baseUrl}${endpoint}`;
        
        try {
            console.log(`🌐 API ${method}: ${url}`, data ? { data } : '');
            
            const options = {
                method,
                headers: { ...this.defaultHeaders }
            };

            // Add request body for non-GET requests
            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            console.log(`✅ API ${method} Success:`, result);
            return result;

        } catch (error) {
            console.error(`❌ API ${method} Error for ${url}:`, error);
            
            // Provide helpful context for common issues
            if (error.message.includes('Error resolving page')) {
                console.log('💡 Note: Template sections are the same as page sections - this is expected');
            }
            
            throw new ApiError(error.message, method, url, data);
        }
    }
}

/**
 * Custom API Error class
 */
class ApiError extends Error {
    constructor(message, method, url, requestData = null) {
        super(message);
        this.name = 'ApiError';
        this.method = method;
        this.url = url;
        this.requestData = requestData;
        this.timestamp = new Date().toISOString();
    }

    toString() {
        return `${this.name}: ${this.method} ${this.url} - ${this.message}`;
    }
}

// Export classes for global use
window.PageBuilderAPI = PageBuilderAPI;
window.ApiError = ApiError;

console.log('📦 PageBuilder API module loaded');