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
            widgetEditorContainer: null,
            ...options
        };
        
        this.isInitialized = false;
        this.selectedWidget = null;
        
        this.init();
    }
    
    /**
     * Initialize the live preview system
     */
    async init() {
        try {
            // Setup iframe communication
            this.setupIframeCommunication();
            
            // Setup handlers for server-rendered page structure
            this.setupPageStructureHandlers();
            
            this.isInitialized = true;
            
            console.log('✅ Live Preview initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize Live Preview:', error);
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
                case 'section-drag-start':
                    this.onSectionDragStart(data);
                    break;
                case 'section-drag-end':
                    this.onSectionDragEnd(data);
                    break;
                case 'section-drag-mode':
                    this.onSectionDragMode(data);
                    break;
                case 'section-cloned':
                    this.onSectionCloned(data);
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
            console.error('⚠️ Could not inject preview helpers:', error);
        }
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
            
            // Widgets are now loaded server-side, just show modal
            
        } catch (error) {
            console.error('Failed to open widget library:', error);
            this.showMessage('Failed to open widget library', 'error');
        }
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
            
            // Reload preview
            await this.reloadPreview();
            
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
     * Handle section drag start event from iframe
     */
    onSectionDragStart(data) {
        console.log('🎯 Section drag started in iframe:', data);
        this.showMessage('Section drag started...', 'info');
        
        // Optional: Add visual feedback to parent interface
        document.body.classList.add('section-dragging');
    }
    
    /**
     * Handle section drag end event from iframe
     */
    onSectionDragEnd(data) {
        console.log('🎯 Section drag ended in iframe:', data);
        
        if (data.positionChanged) {
            this.showMessage(`Section moved from position ${data.oldPosition} to ${data.newPosition}`, 'success');
        } else {
            this.showMessage('Section position unchanged', 'info');
        }
        
        // Remove visual feedback
        document.body.classList.remove('section-dragging');
    }
    
    /**
     * Handle section drag mode activation from iframe
     */
    onSectionDragMode(data) {
        console.log('🎯 Section drag mode activated:', data);
        
        if (data.active) {
            this.showMessage('Drag mode activated - you can now drag the section', 'info');
        }
    }
    
    /**
     * Handle section cloned event from iframe
     */
    onSectionCloned(data) {
        console.log('📋 Section cloned successfully:', data);
        this.showMessage(`Section "${data.sectionName}" cloned successfully`, 'success');
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
     * Get current zoom level
     */
    getCurrentZoom() {
        const iframe = document.getElementById('preview-iframe');
        if (iframe && iframe.style.transform) {
            const match = iframe.style.transform.match(/scale\(([\d.]+)\)/);
            return match ? parseFloat(match[1]) : 1;
        }
        return 1;
    }

    /**
     * Notify iframe of zoom changes
     */
    notifyIframeZoomChange(zoomLevel) {
        const iframe = document.getElementById('preview-iframe');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({
                type: 'zoom-changed',
                zoom: zoomLevel
            }, '*');
        }
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