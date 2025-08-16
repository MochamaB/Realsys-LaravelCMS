/**
 * Universal Preview Manager
 * Handles live preview functionality for widgets and content items
 * Part of the Unified Live Preview System (Phase 4.1)
 */
class UniversalPreviewManager {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/admin/preview',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            defaultTimeout: 10000,
            retryAttempts: 3,
            ...options
        };
        
        this.cache = new Map();
        this.activeRequests = new Map();
        this.eventListeners = new Map();
        
        // Widget editing state
        this.editingMode = false;
        this.selectedWidget = null;
        this.widgetElements = new Map();
        
        this.init();
    }
    
    /**
     * Initialize the preview manager
     */
    init() {
        // Set up global error handling
        this.setupErrorHandling();
        
        // Initialize preview containers
        this.initializePreviewContainers();
        
        console.log('UniversalPreviewManager initialized');
    }
    
    /**
     * Set up global error handling
     */
    setupErrorHandling() {
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.source === 'UniversalPreviewManager') {
                console.error('Preview Manager Error:', event.reason);
                this.showError('Preview system error occurred');
                event.preventDefault();
            }
        });
    }
    
    /**
     * Initialize preview containers on the page
     */
    initializePreviewContainers() {
        document.querySelectorAll('[data-preview-container]').forEach(container => {
            this.setupPreviewContainer(container);
        });
    }
    
    /**
     * Set up a preview container
     */
    setupPreviewContainer(container) {
        const type = container.dataset.previewType;
        const id = container.dataset.previewId;
        
        if (!type || !id) {
            console.warn('Preview container missing required data attributes:', container);
            return;
        }
        
        // Add loading state
        container.classList.add('preview-container');
        
        // Set up auto-refresh if specified
        const autoRefresh = container.dataset.autoRefresh;
        if (autoRefresh && parseInt(autoRefresh) > 0) {
            this.setupAutoRefresh(container, parseInt(autoRefresh));
        }
    }
    
    /**
     * Render widget preview
     */
    async renderWidget(widgetId, options = {}) {
        const {
            contentId = null,
            fields = {},
            settings = {},
            container = null,
            useCache = true
        } = options;
        
        try {
            const cacheKey = this.getCacheKey('widget', widgetId, { contentId, fields, settings });
            
            // Check cache first
            if (useCache && this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < 300000) { // 5 minutes
                    return this.displayPreview(cached.data, container);
                }
            }
            
            // Cancel any existing request for this widget
            this.cancelRequest(cacheKey);
            
            const url = contentId 
                ? `${this.options.baseUrl}/widget/${widgetId}/content/${contentId}`
                : `${this.options.baseUrl}/widget/${widgetId}`;
            
            const requestData = {
                preview_data: {
                    fields: fields,
                    settings: settings
                },
                content_item_id: contentId,
                preview_mode: true
            };
            
            const response = await this.makeRequest(url, requestData, cacheKey);
            
            // Cache the response
            if (useCache) {
                this.cache.set(cacheKey, {
                    data: response,
                    timestamp: Date.now()
                });
            }
            
            return this.displayPreview(response, container);
            
        } catch (error) {
            console.error('Widget preview error:', error);
            throw this.createError('Failed to render widget preview', error);
        }
    }
    
    /**
     * Render content item preview
     */
    async renderContent(contentId, options = {}) {
        const {
            widgetId = null,
            fields = {},
            container = null,
            useCache = true
        } = options;
        
        try {
            const cacheKey = this.getCacheKey('content', contentId, { widgetId, fields });
            
            // Check cache first
            if (useCache && this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < 300000) { // 5 minutes
                    return this.displayPreview(cached.data, container);
                }
            }
            
            // Cancel any existing request for this content
            this.cancelRequest(cacheKey);
            
            const url = widgetId 
                ? `${this.options.baseUrl}/content/${contentId}/widget/${widgetId}`
                : `${this.options.baseUrl}/content/${contentId}`;
            
            const requestData = {
                preview_data: {
                    fields: fields
                },
                widget_id: widgetId,
                preview_mode: true
            };
            
            const response = await this.makeRequest(url, requestData, cacheKey);
            
            // Cache the response
            if (useCache) {
                this.cache.set(cacheKey, {
                    data: response,
                    timestamp: Date.now()
                });
            }
            
            return this.displayPreview(response, container);
            
        } catch (error) {
            console.error('Content preview error:', error);
            throw this.createError('Failed to render content preview', error);
        }
    }
    
    /**
     * Get widget content options
     */
    async getWidgetContentOptions(widgetId) {
        try {
            const url = `${this.options.baseUrl}/widget/${widgetId}/content-options`;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return await response.json();
            
        } catch (error) {
            console.error('Get widget content options error:', error);
            throw this.createError('Failed to get widget content options', error);
        }
    }
    
    /**
     * Get content widget options
     */
    async getContentWidgetOptions(contentId) {
        try {
            const url = `${this.options.baseUrl}/content/${contentId}/widget-options`;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return await response.json();
            
        } catch (error) {
            console.error('Get content widget options error:', error);
            throw this.createError('Failed to get content widget options', error);
        }
    }
    
    /**
     * Make HTTP request with retry logic
     */
    async makeRequest(url, data, requestKey) {
        const controller = new AbortController();
        this.activeRequests.set(requestKey, controller);
        
        let lastError;
        
        for (let attempt = 1; attempt <= this.options.retryAttempts; attempt++) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.options.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data),
                    signal: controller.signal,
                    timeout: this.options.defaultTimeout
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                this.activeRequests.delete(requestKey);
                return result;
                
            } catch (error) {
                lastError = error;
                
                if (error.name === 'AbortError') {
                    throw error; // Don't retry aborted requests
                }
                
                if (attempt < this.options.retryAttempts) {
                    await this.delay(1000 * attempt); // Exponential backoff
                }
            }
        }
        
        this.activeRequests.delete(requestKey);
        throw lastError;
    }
    
    /**
     * Display preview in container
     */
    displayPreview(response, container) {
        if (!response.success) {
            throw new Error(response.message || 'Preview rendering failed');
        }
        
        if (container) {
            // Update container content
            container.innerHTML = response.html;
            
            // Load CSS assets
            if (response.assets && response.assets.css) {
                this.loadCssAssets(response.assets.css);
            }
            
            // Load JS assets and initialize
            if (response.assets && response.assets.js) {
                this.loadJsAssets(response.assets.js).then(() => {
                    this.initializeWidgetScripts(container, response.metadata);
                    
                    // Enable widget editing after content is loaded
                    this.initializeWidgetEditing(container);
                });
            }
            
            // Trigger preview updated event
            this.triggerEvent('previewUpdated', {
                container: container,
                response: response
            });
        }
        
        return response;
    }
    
    /**
     * Load CSS assets dynamically
     */
    loadCssAssets(cssUrls) {
        cssUrls.forEach(url => {
            if (!document.querySelector(`link[href="${url}"]`)) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = url;
                document.head.appendChild(link);
            }
        });
    }
    
    /**
     * Load JS assets dynamically
     */
    async loadJsAssets(jsUrls) {
        const promises = jsUrls.map(url => {
            return new Promise((resolve, reject) => {
                if (document.querySelector(`script[src="${url}"]`)) {
                    resolve();
                    return;
                }
                
                const script = document.createElement('script');
                script.src = url;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        });
        
        await Promise.all(promises);
    }
    
    /**
     * Initialize widget scripts after loading
     */
    initializeWidgetScripts(container, metadata) {
        // Look for widget-specific initialization
        const widgets = container.querySelectorAll('[data-widget-type]');
        
        widgets.forEach(widget => {
            const widgetType = widget.dataset.widgetType;
            const initFunction = window[`init${widgetType.charAt(0).toUpperCase() + widgetType.slice(1)}Widget`];
            
            if (typeof initFunction === 'function') {
                try {
                    initFunction(widget, metadata);
                } catch (error) {
                    console.warn(`Failed to initialize ${widgetType} widget:`, error);
                }
            }
        });
    }
    
    /**
     * Set up auto-refresh for a container
     */
    setupAutoRefresh(container, interval) {
        const refreshId = setInterval(() => {
            const type = container.dataset.previewType;
            const id = container.dataset.previewId;
            
            if (type === 'widget') {
                this.renderWidget(id, { container: container, useCache: false });
            } else if (type === 'content') {
                this.renderContent(id, { container: container, useCache: false });
            }
        }, interval * 1000);
        
        // Store refresh ID for cleanup
        container.dataset.refreshId = refreshId;
    }
    
    /**
     * Cancel active request
     */
    cancelRequest(requestKey) {
        const controller = this.activeRequests.get(requestKey);
        if (controller) {
            controller.abort();
            this.activeRequests.delete(requestKey);
        }
    }
    
    /**
     * Generate cache key
     */
    getCacheKey(type, id, options = {}) {
        const optionsStr = JSON.stringify(options);
        return `${type}_${id}_${btoa(optionsStr)}`;
    }
    
    /**
     * Create standardized error
     */
    createError(message, originalError = null) {
        const error = new Error(message);
        error.source = 'UniversalPreviewManager';
        error.originalError = originalError;
        return error;
    }
    
    /**
     * Show error message to user
     */
    showError(message) {
        // This can be customized based on your notification system
        console.error('Preview Error:', message);
        
        // You can integrate with your existing notification system here
        if (window.showNotification) {
            window.showNotification(message, 'error');
        }
    }
    
    /**
     * Utility delay function
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Event system
     */
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
    
    triggerEvent(event, data = {}) {
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
    
    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }
    
    /**
     * Enable widget editing mode
     */
    enableEditingMode(container = null) {
        this.editingMode = true;
        
        // Add editing mode class to container or body
        const target = container || document.body;
        target.classList.add('widget-editing-mode');
        
        // Initialize widget detection
        this.initializeWidgetEditing(target);
        
        console.log('Widget editing mode enabled');
        this.emit('editing-mode-enabled', { container: target });
    }
    
    /**
     * Disable widget editing mode
     */
    disableEditingMode(container = null) {
        this.editingMode = false;
        this.selectedWidget = null;
        
        // Remove editing mode class
        const target = container || document.body;
        target.classList.remove('widget-editing-mode');
        
        // Clear widget selections
        this.clearWidgetSelection();
        
        console.log('Widget editing mode disabled');
        this.emit('editing-mode-disabled', { container: target });
    }
    
    /**
     * Initialize widget editing functionality
     */
    initializeWidgetEditing(container) {
        // Find all widgets in the container
        this.detectWidgets(container);
        
        // Set up event listeners for widget interaction
        this.setupWidgetEventListeners(container);
    }
    
    /**
     * Detect widgets in the preview content
     */
    detectWidgets(container) {
        // Look for common widget patterns
        const widgetSelectors = [
            '[data-widget-id]',
            '[data-widget-name]',
            '.widget',
            '[class*="widget-"]'
        ];
        
        widgetSelectors.forEach(selector => {
            const widgets = container.querySelectorAll(selector);
            widgets.forEach(widget => this.enhanceWidgetElement(widget));
        });
    }
    
    /**
     * Enhance widget element for editing
     */
    enhanceWidgetElement(element) {
        // Add editing classes and attributes
        element.classList.add('widget-editable');
        
        // Get widget information
        const widgetId = element.dataset.widgetId || this.generateWidgetId();
        const widgetName = element.dataset.widgetName || this.extractWidgetName(element);
        const widgetType = element.dataset.widgetType || this.detectWidgetType(element);
        
        // Store widget data
        element.dataset.widgetId = widgetId;
        element.dataset.widgetName = widgetName;
        element.dataset.widgetType = widgetType;
        
        // Create and add widget label
        const label = this.createWidgetLabel(widgetName, widgetType);
        element.appendChild(label);
        
        // Store reference
        this.widgetElements.set(widgetId, element);
        
        console.log(`Enhanced widget: ${widgetName} (${widgetId})`);
    }
    
    /**
     * Create widget label element
     */
    createWidgetLabel(name, type = 'content') {
        const label = document.createElement('div');
        label.className = 'widget-label';
        label.dataset.widgetType = type;
        
        const icon = this.getWidgetTypeIcon(type);
        label.innerHTML = `
            <span class="widget-info-badge">
                <i class="${icon}"></i>
                ${name}
            </span>
        `;
        
        return label;
    }
    
    /**
     * Get icon for widget type
     */
    getWidgetTypeIcon(type) {
        const icons = {
            'content': 'bx bx-file-blank',
            'layout': 'bx bx-layout',
            'media': 'bx bx-image',
            'form': 'bx bx-form',
            'navigation': 'bx bx-menu',
            'social': 'bx bx-share-alt'
        };
        
        return icons[type] || 'bx bx-widget';
    }
    
    /**
     * Set up event listeners for widget interaction
     */
    setupWidgetEventListeners(container) {
        // Click handler for widget selection
        container.addEventListener('click', (e) => {
            if (!this.editingMode) return;
            
            const widget = e.target.closest('.widget-editable');
            if (widget) {
                e.preventDefault();
                e.stopPropagation();
                this.selectWidget(widget);
            }
        });
        
        // Prevent default link behavior in editing mode
        container.addEventListener('click', (e) => {
            if (this.editingMode && (e.target.tagName === 'A' || e.target.closest('a'))) {
                e.preventDefault();
            }
        });
    }
    
    /**
     * Select a widget
     */
    selectWidget(element) {
        // Clear previous selection
        this.clearWidgetSelection();
        
        // Select new widget
        element.classList.add('widget-selected');
        this.selectedWidget = element;
        
        const widgetId = element.dataset.widgetId;
        const widgetName = element.dataset.widgetName;
        
        console.log(`Selected widget: ${widgetName} (${widgetId})`);
        this.emit('widget-selected', { 
            element, 
            widgetId, 
            widgetName,
            widgetType: element.dataset.widgetType
        });
    }
    
    /**
     * Clear widget selection
     */
    clearWidgetSelection() {
        if (this.selectedWidget) {
            this.selectedWidget.classList.remove('widget-selected');
            this.selectedWidget = null;
        }
        
        // Remove selection from all widgets
        document.querySelectorAll('.widget-selected').forEach(widget => {
            widget.classList.remove('widget-selected');
        });
    }
    
    /**
     * Generate unique widget ID
     */
    generateWidgetId() {
        return 'widget-' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Extract widget name from element
     */
    extractWidgetName(element) {
        // Try various methods to get widget name
        if (element.dataset.widgetName) return element.dataset.widgetName;
        if (element.title) return element.title;
        
        // Extract from class names
        const classList = Array.from(element.classList);
        const widgetClass = classList.find(cls => cls.startsWith('widget-'));
        if (widgetClass) {
            return widgetClass.replace('widget-', '').replace('-', ' ');
        }
        
        return 'Unknown Widget';
    }
    
    /**
     * Detect widget type from element
     */
    detectWidgetType(element) {
        const classList = Array.from(element.classList);
        
        // Check for specific widget type classes
        if (classList.some(cls => cls.includes('content'))) return 'content';
        if (classList.some(cls => cls.includes('layout'))) return 'layout';
        if (classList.some(cls => cls.includes('media') || cls.includes('image'))) return 'media';
        if (classList.some(cls => cls.includes('form'))) return 'form';
        if (classList.some(cls => cls.includes('nav'))) return 'navigation';
        
        return 'content'; // Default type
    }
    
    /**
     * Destroy the preview manager
     */
    destroy() {
        // Disable editing mode
        this.disableEditingMode();
        
        // Cancel all active requests
        this.activeRequests.forEach((controller, key) => {
            controller.abort();
        });
        this.activeRequests.clear();
        
        // Clear cache
        this.cache.clear();
        
        // Clear event listeners
        this.eventListeners.clear();
        
        // Clear widget references
        this.widgetElements.clear();
        
        // Clear auto-refresh intervals
        document.querySelectorAll('[data-refresh-id]').forEach(container => {
            const refreshId = container.dataset.refreshId;
            if (refreshId) {
                clearInterval(parseInt(refreshId));
            }
        });
        
        console.log('UniversalPreviewManager destroyed');
    }
}

// Export for use in modules or make globally available
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UniversalPreviewManager;
} else {
    window.UniversalPreviewManager = UniversalPreviewManager;
    
    // Create global instance when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.universalPreviewManager) {
                window.universalPreviewManager = new UniversalPreviewManager();
            }
        });
    } else {
        // DOM is already ready
        if (!window.universalPreviewManager) {
            window.universalPreviewManager = new UniversalPreviewManager();
        }
    }
}
