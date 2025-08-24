/**
 * Live Preview Main Class
 * 
 * Manages the core live preview functionality without GrapeJS complexity
 * Uses iframe-based preview with real-time updates
 */
class LivePreview {
    constructor(options) {
        this.options = {
            pageId: null,
            apiUrl: '/admin/api/live-preview',
            csrfToken: '',
            previewIframe: null,
            pageStructureContainer: null,
            widgetEditorContainer: null,
            ...options
        };
        
        this.isInitialized = false;
        this.isLoading = false;
        this.pageStructure = null;
        this.selectedWidget = null;
        
        this.init();
    }
    
    /**
     * Initialize the live preview system
     */
    async init() {
        try {
            this.showLoading('Initializing live preview...');
            
            // Setup iframe communication
            this.setupIframeCommunication();
            
            // Load initial page structure
            await this.loadPageStructure();
            
            this.isInitialized = true;
            this.hideLoading();
            
            console.log('✅ Simple Live Preview initialized successfully');
            this.showMessage('Live preview ready!', 'success');
            
        } catch (error) {
            console.error('❌ Failed to initialize Simple Live Preview:', error);
            this.showMessage('Failed to initialize live preview', 'error');
            this.hideLoading();
        }
    }
    
    /**
     * Setup iframe communication for widget selection
     */
    setupIframeCommunication() {
        // Listen for messages from preview iframe
        window.addEventListener('message', (event) => {
            if (event.source !== this.options.previewIframe.contentWindow) return;
            
            const { type, data } = event.data;
            
            switch (type) {
                case 'widget-selected':
                    this.onWidgetSelected(data);
                    break;
                case 'section-selected':
                    this.onSectionSelected(data);
                    break;
            }
        });
        
        // Setup iframe load handler
        this.options.previewIframe.addEventListener('load', () => {
            this.injectPreviewHelpers();
        });
    }
    
    /**
     * Inject helper functions into preview iframe
     */
    injectPreviewHelpers() {
        try {
            const iframeWindow = this.options.previewIframe.contentWindow;
            const iframeDoc = this.options.previewIframe.contentDocument;
            
            if (!iframeWindow || !iframeDoc) return;
            
            // Inject preview helper script
            const script = iframeDoc.createElement('script');
            script.textContent = `
                // Preview helper functions
                window.previewHelpers = {
                    highlightWidget: function(widgetId) {
                        // Remove existing highlights
                        document.querySelectorAll('.preview-highlighted').forEach(el => {
                            el.classList.remove('preview-highlighted');
                        });
                        
                        // Highlight selected widget
                        const widget = document.querySelector('[data-widget-id="' + widgetId + '"]');
                        if (widget) {
                            widget.classList.add('preview-highlighted');
                            widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    },
                    
                    addEditingIndicators: function() {
                        // Add click handlers to widgets
                        document.querySelectorAll('[data-widget-id]').forEach(widget => {
                            widget.style.cursor = 'pointer';
                            widget.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                parent.postMessage({
                                    type: 'widget-selected',
                                    data: { widgetId: this.dataset.widgetId }
                                }, '*');
                            });
                            
                            // Add hover effects
                            widget.addEventListener('mouseenter', function() {
                                this.style.outline = '2px dashed #0d6efd';
                                this.style.outlineOffset = '2px';
                            });
                            
                            widget.addEventListener('mouseleave', function() {
                                if (!this.classList.contains('preview-highlighted')) {
                                    this.style.outline = '';
                                    this.style.outlineOffset = '';
                                }
                            });
                        });
                        
                        // Add click handlers to sections
                        document.querySelectorAll('[data-section-id]').forEach(section => {
                            section.addEventListener('click', function(e) {
                                if (e.target === this) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    parent.postMessage({
                                        type: 'section-selected',
                                        data: { sectionId: this.dataset.sectionId }
                                    }, '*');
                                }
                            });
                        });
                    }
                };
                
                // Initialize editing indicators when page loads
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        window.previewHelpers.addEditingIndicators();
                    });
                } else {
                    window.previewHelpers.addEditingIndicators();
                }
                
                // Add preview-specific styles
                const style = document.createElement('style');
                style.textContent = \`
                    .preview-highlighted {
                        outline: 2px solid #0d6efd !important;
                        outline-offset: 2px;
                        position: relative;
                    }
                    
                    .preview-highlighted::after {
                        content: '✏️ Editing';
                        position: absolute;
                        top: -25px;
                        left: 0;
                        background: #0d6efd;
                        color: white;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                        font-weight: 500;
                        z-index: 1000;
                    }
                    
                    [data-widget-id] {
                        transition: outline 0.2s ease;
                    }
                \`;
                document.head.appendChild(style);
            `;
            iframeDoc.head.appendChild(script);
            
            console.log('📝 Preview helpers injected successfully');
            
        } catch (error) {
            console.warn('⚠️ Could not inject preview helpers:', error);
        }
    }
    
    /**
     * Load page structure for sidebar
     */
    async loadPageStructure() {
        try {
            const url = `${this.options.apiUrl}/page-structure/${this.options.pageId}`;
            console.log('🌍 Loading page structure from:', url);
            
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });
            
            console.log('📡 Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Response error:', errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📊 Response data:', data);
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to load page structure');
            }
            
            this.pageStructure = data.data;
            this.renderPageStructure();
            
        } catch (error) {
            console.error('Failed to load page structure:', error);
            this.showMessage('Failed to load page structure', 'error');
            
            // Show error in sidebar
            this.options.pageStructureContainer.innerHTML = `
                <div class="error-state text-center py-4">
                    <div class="mb-3">
                        <i class="ri-error-warning-line" style="font-size: 2rem; color: #dc3545;"></i>
                    </div>
                    <h6>Failed to Load</h6>
                    <p class="text-muted mb-2">Could not load page structure</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="livePreview.loadPageStructure()">
                        Retry
                    </button>
                </div>
            `;
        }
    }
    
    /**
     * Render page structure in sidebar
     */
    renderPageStructure() {
        if (!this.pageStructure) return;
        
        let html = `
            <div class="page-structure">
                <div class="page-info mb-3">
                    <h6 class="mb-1">${this.pageStructure.page_title}</h6>
                    <small class="text-muted">${this.pageStructure.sections.length} sections</small>
                </div>
        `;
        
        this.pageStructure.sections.forEach(section => {
            html += this.renderSection(section);
        });
        
        html += '</div>';
        
        this.options.pageStructureContainer.innerHTML = html;
        this.setupPageStructureHandlers();
    }
    
    /**
     * Render individual section
     */
    renderSection(section) {
        const widgetCount = section.widgets.length;
        
        let html = `
            <div class="section-item mb-3" data-section-id="${section.id}">
                <div class="section-header d-flex justify-content-between align-items-center p-2 bg-light rounded">
                    <div class="section-info">
                        <strong class="section-name">${section.name}</strong>
                        <small class="text-muted d-block">${widgetCount} widget${widgetCount !== 1 ? 's' : ''}</small>
                    </div>
                    <div class="section-actions">
                        <button class="btn btn-sm btn-outline-primary add-widget-btn" data-section-id="${section.id}">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                </div>
        `;
        
        if (section.widgets.length > 0) {
            html += '<div class="widgets-list mt-2">';
            
            section.widgets.forEach(widget => {
                html += `
                    <div class="widget-item p-2 border rounded mb-2 widget-item-clickable" data-widget-id="${widget.id}">
                        <div class="d-flex align-items-center">
                            <i class="${widget.icon || 'ri-puzzle-line'} me-2"></i>
                            <div class="flex-grow-1">
                                <div class="widget-name">${widget.name}</div>
                                <small class="text-muted">${widget.category || 'General'}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary edit-widget-btn" data-widget-id="${widget.id}">
                                <i class="ri-edit-line"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        } else {
            html += `
                <div class="empty-section text-center py-3 mt-2">
                    <div class="text-muted mb-2">
                        <i class="ri-inbox-line" style="font-size: 1.5rem;"></i>
                    </div>
                    <small class="text-muted">No widgets in this section</small>
                </div>
            `;
        }
        
        html += '</div>';
        
        return html;
    }
    
    /**
     * Setup event handlers for page structure
     */
    setupPageStructureHandlers() {
        // Widget selection handlers
        document.querySelectorAll('.widget-item-clickable').forEach(item => {
            item.addEventListener('click', (e) => {
                if (!e.target.closest('.edit-widget-btn')) {
                    const widgetId = item.dataset.widgetId;
                    this.onWidgetSelected(widgetId);
                }
            });
        });
        
        // Widget edit handlers
        document.querySelectorAll('.edit-widget-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const widgetId = btn.dataset.widgetId;
                this.onWidgetSelected(widgetId);
            });
        });
        
        // Add widget handlers
        document.querySelectorAll('.add-widget-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const sectionId = btn.dataset.sectionId;
                this.openWidgetLibrary(sectionId);
            });
        });
    }
    
    /**
     * Handle widget selection
     */
    async onWidgetSelected(widgetData) {
        try {
            // Extract data from the new structure
            const { instanceId, widgetId, sectionId, widgetName } = widgetData;
            
            console.log(`🎯 Widget selected: ${widgetName} (instance: ${instanceId}, widget: ${widgetId}, section: ${sectionId})`);
            
            // Update UI selection
            document.querySelectorAll('.widget-item').forEach(item => {
                item.classList.remove('selected');
            });
            const widget = document.querySelector(`[data-widget-id="${instanceId}"]`);
            if (widget) widget.classList.add('selected');
            
            // Highlight in preview using the instance ID
            this.highlightWidgetInPreview(instanceId);
            
            // Load widget editor form using the correct instance ID
            await this.loadWidgetEditor(instanceId);
            
            // Store selected widget data for updates
            this.selectedWidget = widgetData;
            
        } catch (error) {
            console.error('Error selecting widget:', error);
            this.showMessage('Failed to select widget', 'error');
        }
    }
    
    /**
     * Handle section selection
     */
    onSectionSelected(sectionId) {
        console.log('Section selected:', sectionId);
        // Could add section-level editing here
    }
    
    /**
     * Highlight widget in preview iframe
     */
    highlightWidgetInPreview(widgetId) {
        try {
            const iframeWindow = this.options.previewIframe.contentWindow;
            if (iframeWindow && iframeWindow.previewHelpers) {
                iframeWindow.previewHelpers.highlightWidget(widgetId);
            }
        } catch (error) {
            console.warn('Could not highlight widget in preview:', error);
        }
    }
    
    /**
     * Load widget editor form
     */
    async loadWidgetEditor(widgetId) {
        try {
            this.showWidgetEditorLoading();
            
            const response = await fetch(`${this.options.apiUrl}/widget-editor/${widgetId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to load widget editor');
            }
            
            this.options.widgetEditorContainer.innerHTML = data.data.html;
            
            // Initialize widget form manager for this widget
            if (window.widgetFormManager) {
                window.widgetFormManager.initializeWidget(widgetId);
            }
            
        } catch (error) {
            console.error('Failed to load widget editor:', error);
            this.showMessage('Failed to load widget editor', 'error');
            
            this.options.widgetEditorContainer.innerHTML = `
                <div class="error-state text-center py-4">
                    <div class="mb-3">
                        <i class="ri-error-warning-line" style="font-size: 2rem; color: #dc3545;"></i>
                    </div>
                    <h6>Failed to Load Editor</h6>
                    <p class="text-muted mb-2">Could not load widget editor</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="livePreview.loadWidgetEditor(${widgetId})">
                        Retry
                    </button>
                </div>
            `;
        }
    }
    
    /**
     * Open widget library modal
     */
    async openWidgetLibrary(sectionId) {
        try {
            const modal = new bootstrap.Modal(document.getElementById('widget-library-modal'));
            modal.show();
            
            // Load available widgets
            await this.loadAvailableWidgets(sectionId);
            
        } catch (error) {
            console.error('Failed to open widget library:', error);
            this.showMessage('Failed to open widget library', 'error');
        }
    }
    
    /**
     * Load available widgets for library
     */
    async loadAvailableWidgets(sectionId) {
        try {
            const response = await fetch(`${this.options.apiUrl}/widgets/available`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to load widgets');
            }
            
            this.renderWidgetLibrary(data.data.widgets, sectionId);
            
        } catch (error) {
            console.error('Failed to load available widgets:', error);
            document.getElementById('widget-library-content').innerHTML = `
                <div class="error-state text-center py-4">
                    <i class="ri-error-warning-line" style="font-size: 2rem; color: #dc3545;"></i>
                    <h6>Failed to Load Widgets</h6>
                    <p class="text-muted">Could not load available widgets</p>
                </div>
            `;
        }
    }
    
    /**
     * Render widget library
     */
    renderWidgetLibrary(widgetsByCategory, sectionId) {
        let html = '<div class="widget-library">';
        
        Object.entries(widgetsByCategory).forEach(([category, widgets]) => {
            html += `
                <div class="widget-category mb-4">
                    <h6 class="category-title border-bottom pb-2 mb-3">${category}</h6>
                    <div class="row">
            `;
            
            widgets.forEach(widget => {
                html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="widget-card border rounded p-3 h-100">
                            <div class="text-center mb-2">
                                <i class="${widget.icon || 'ri-puzzle-line'}" style="font-size: 2rem; color: #0d6efd;"></i>
                            </div>
                            <h6 class="widget-title text-center mb-2">${widget.name}</h6>
                            <p class="widget-description text-muted small text-center mb-3">${widget.description || 'No description available'}</p>
                            <button class="btn btn-primary btn-sm w-100 add-widget-to-section" 
                                    data-widget-id="${widget.id}" 
                                    data-section-id="${sectionId}">
                                Add Widget
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        document.getElementById('widget-library-content').innerHTML = html;
        
        // Setup add widget handlers
        document.querySelectorAll('.add-widget-to-section').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const widgetId = btn.dataset.widgetId;
                const targetSectionId = btn.dataset.sectionId;
                
                await this.addWidgetToSection(widgetId, targetSectionId);
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('widget-library-modal')).hide();
            });
        });
    }
    
    /**
     * Add widget to section
     */
    async addWidgetToSection(widgetId, sectionId) {
        try {
            this.showLoading('Adding widget...');
            
            const response = await fetch(`${this.options.apiUrl}/sections/${sectionId}/add-widget`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                },
                body: JSON.stringify({
                    widget_id: widgetId
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to add widget');
            }
            
            // Reload preview and page structure
            await this.reloadPreview();
            await this.loadPageStructure();
            
            this.showMessage('Widget added successfully!', 'success');
            
        } catch (error) {
            console.error('Failed to add widget:', error);
            this.showMessage('Failed to add widget', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Reload preview iframe
     */
    async reloadPreview() {
        return new Promise((resolve) => {
            const iframe = this.options.previewIframe;
            const onLoad = () => {
                iframe.removeEventListener('load', onLoad);
                resolve();
            };
            iframe.addEventListener('load', onLoad);
            iframe.src = iframe.src; // Reload iframe
        });
    }
    
    /**
     * Show widget editor loading state
     */
    showWidgetEditorLoading() {
        this.options.widgetEditorContainer.innerHTML = `
            <div class="loading-content text-center py-4">
                <div class="loading-spinner"></div>
                <p>Loading widget editor...</p>
            </div>
        `;
    }
    
    /**
     * Show loading overlay
     */
    showLoading(message = 'Loading...') {
        const overlay = document.getElementById('preview-loading');
        if (overlay) {
            overlay.querySelector('p').textContent = message;
            overlay.classList.remove('hidden');
        }
        this.isLoading = true;
    }
    
    /**
     * Hide loading overlay
     */
    hideLoading() {
        const overlay = document.getElementById('preview-loading');
        if (overlay) {
            overlay.classList.add('hidden');
        }
        this.isLoading = false;
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        const container = document.getElementById('message-container');
        if (!container) return;
        
        const messageElement = document.createElement('div');
        messageElement.className = `alert alert-${this.getBootstrapAlertType(type)} alert-dismissible fade show`;
        messageElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        container.appendChild(messageElement);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (messageElement.parentNode) {
                bootstrap.Alert.getInstance(messageElement)?.close();
            }
        }, 3000);
    }
    
    /**
     * Get Bootstrap alert type from message type
     */
    getBootstrapAlertType(type) {
        const mapping = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return mapping[type] || 'info';
    }
}

// Export for global use
window.LivePreview = LivePreview;