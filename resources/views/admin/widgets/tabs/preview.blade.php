<!-- Unified Widget Preview System -->
<div class="unified-preview-system">
    <!-- Preview Mode Controls -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-show me-2"></i>Widget Preview
                </h5>
                <div class="preview-mode-switcher">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary" id="static-preview-btn">
                            <i class="bx bx-image me-1"></i>Static
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="live-preview-btn">
                            <i class="bx bx-play-circle me-1"></i>Live Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Static Preview Content -->
            <div id="static-preview-content" class="preview-section">
                <div class="row justify-content-center p-4">
                    <div class="col-md-8 text-center">
                        @php
                            $previewPath = "/themes/{$widget->theme->slug}/widgets/{$widget->slug}/preview.png";
                            $previewExists = file_exists(public_path($previewPath));
                        @endphp
                        
                        @if($previewExists)
                            <img src="{{ asset($previewPath) }}" class="img-fluid rounded shadow-sm" alt="{{ $widget->name }} Preview" style="max-height: 500px;">
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i> No static preview image available for this widget.
                            </div>
                            <div class="border rounded p-5 bg-light text-center">
                                <i class="bx bx-image" style="font-size: 5rem; opacity: 0.2;"></i>
                                <h5 class="mt-3 text-muted">Static Preview Not Available</h5>
                            </div>
                        @endif
                        
                        <div class="mt-4">
                            <p class="text-muted">This is a static visual preview of the widget. Switch to Live Preview for interactive rendering with real data.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Preview Content -->
            <div id="live-preview-content" class="preview-section" style="display: none;">
                <!-- Content Selection (only visible in live mode) -->
                <div id="content-selection" class="border-bottom p-3 bg-light" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-file-blank me-1"></i>Content Item
                            </label>
                            <select class="form-select" id="content-select">
                                <option value="">Select content item...</option>
                                <!-- Dynamic options loaded via JavaScript -->
                            </select>
                            <small class="text-muted">Select content to preview with this widget</small>
                        </div>
                    </div>
                </div>

                <!-- Preview Controls -->
                <div class="preview-controls border-bottom p-3 bg-light">
                    <div class="row g-3">
                        <!-- Device Size Controls -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-devices me-1"></i>Device Size
                            </label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-secondary device-btn active" data-device="desktop" data-width="100%">
                                    <i class="bx bx-desktop"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary device-btn" data-device="tablet" data-width="768px">
                                    <i class="bx bx-tablet"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary device-btn" data-device="mobile" data-width="375px">
                                    <i class="bx bx-mobile"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Preview Actions -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-cog me-1"></i>Actions
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success flex-fill" id="refresh-btn">
                                    <i class="bx bx-refresh me-1"></i>Refresh
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="settings-btn" title="Settings">
                                    <i class="bx bx-cog"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="fullscreen-btn" title="Fullscreen">
                                    <i class="bx bx-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings Panel (Collapsible) -->
                    <div class="advanced-settings mt-3" id="settings-panel" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-code-alt me-1"></i>Field Values Override (JSON)
                                </label>
                                <textarea class="form-control font-monospace" id="field-overrides" rows="4" placeholder='{"title": "Custom Title", "description": "Custom description..."}'></textarea>
                                <small class="text-muted">Override specific field values for preview</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-slider me-1"></i>Settings Override (JSON)
                                </label>
                                <textarea class="form-control font-monospace" id="settings-overrides" rows="4" placeholder='{"layout": "grid", "columns": 3, "show_excerpt": true}'></textarea>
                                <small class="text-muted">Override widget settings for preview</small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm" id="apply-overrides">
                                        <i class="bx bx-check me-1"></i>Apply Overrides
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-overrides">
                                        <i class="bx bx-reset me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Status Bar -->
                <div class="preview-status-bar d-flex justify-content-between align-items-center px-3 py-2 bg-white border-bottom">
                    <div class="preview-status">
                        <span class="badge bg-secondary" id="preview-status-badge">Ready</span>
                        <span class="text-muted ms-2" id="preview-status-text">Click refresh to load preview</span>
                    </div>
                    <div class="preview-info">
                        <small class="text-muted" id="preview-metadata"></small>
                    </div>
                </div>

                <!-- Preview Container -->
                <div class="preview-container-wrapper">
                    <!-- Loading State -->
                    <div class="preview-loading text-center py-5" id="preview-loading" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-muted">Loading Preview...</h5>
                        <p class="text-muted">Rendering widget with selected content</p>
                    </div>

                    <!-- Error State -->
                    <div class="preview-error text-center py-5" id="preview-error" style="display: none;">
                        <div class="text-danger mb-3">
                            <i class="bx bx-error-circle" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-danger">Preview Error</h5>
                        <p class="text-muted" id="error-message">Failed to load preview</p>
                        <button class="btn btn-outline-primary" onclick="refreshPreview()">
                            <i class="bx bx-refresh me-1"></i>Try Again
                        </button>
                    </div>

                    <!-- Actual Preview Content -->
                    <div class="preview-frame desktop" id="preview-container">
                        <!-- Widget rendered content will be inserted here -->
                        <div class="alert alert-info text-center">
                            <i class="bx bx-info-circle me-2"></i>
                            Select a preview mode and click refresh to load preview
                        </div>
                    </div>
                </div>

                <!-- Preview Metadata Panel -->
                <div class="preview-metadata-panel border-top p-3 bg-light">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="metadata-item">
                                <label class="form-label fw-semibold mb-1">Render Time</label>
                                <div class="text-muted" id="render-time">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metadata-item">
                                <label class="form-label fw-semibold mb-1">Assets Loaded</label>
                                <div class="text-muted" id="assets-count">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metadata-item">
                                <label class="form-label fw-semibold mb-1">Cache Status</label>
                                <div class="text-muted" id="cache-status">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metadata-item">
                                <label class="form-label fw-semibold mb-1">Content Type</label>
                                <div class="text-muted" id="content-type">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles for Preview System -->
<style>
.unified-preview-system {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.preview-section {
    min-height: 400px;
}

.preview-container-wrapper {
    position: relative;
    background: #f8f9fa;
    min-height: 500px;
}

.preview-container, .preview-frame {
    transition: max-width 0.3s ease;
    margin: 0 auto;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    min-height: 500px;
    position: relative;
    padding: 20px;
}

/* Device-specific preview frame sizes */
.preview-frame.desktop {
    max-width: 100%;
}

.preview-frame.tablet {
    max-width: 768px;
}

.preview-frame.mobile {
    max-width: 375px;
}

.device-btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.preview-loading, .preview-error {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
}

.preview-content {
    padding: 20px;
    min-height: 460px;
}

.metadata-item label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.preview-status-bar {
    font-size: 0.875rem;
}

.font-monospace {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
}

.advanced-settings {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    background: #f8f9fa;
    border-radius: 0 0 0.375rem 0.375rem;
}

/* Responsive preview container */
.preview-container[data-device="desktop"] { max-width: 100%; }
.preview-container[data-device="tablet"] { max-width: 768px; }
.preview-container[data-device="mobile"] { max-width: 375px; }

/* Preview animations */
.preview-container {
    transition: all 0.3s ease;
}

.preview-loading .spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Status badge colors */
.badge.bg-loading { background-color: #ffc107 !important; }
.badge.bg-success { background-color: #198754 !important; }
.badge.bg-error { background-color: #dc3545 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for UniversalPreviewManager to be available with retry
    function initializePreview() {
        if (typeof window.universalPreviewManager === 'undefined' || typeof window.UniversalPreviewManager === 'undefined') {
            console.log('Waiting for UniversalPreviewManager...');
            setTimeout(initializePreview, 100);
            return;
        }

        // Ensure we have an instance
        if (!window.universalPreviewManager) {
            window.universalPreviewManager = new window.UniversalPreviewManager();
        }

        const previewManager = window.universalPreviewManager;
    
    // Initialize preview functionality
    const previewContainer = document.getElementById('preview-container');
    const staticPreviewBtn = document.getElementById('static-preview-btn');
    const livePreviewBtn = document.getElementById('live-preview-btn');
    const contentSelect = document.getElementById('content-select');
    const deviceBtns = document.querySelectorAll('.device-btn');
    const refreshBtn = document.getElementById('refresh-btn');
    const settingsBtn = document.getElementById('settings-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const settingsPanel = document.getElementById('settings-panel');
    const fieldOverridesTextarea = document.getElementById('field-overrides');
    const settingsOverridesTextarea = document.getElementById('settings-overrides');
    const applyOverridesBtn = document.getElementById('apply-overrides');
    const resetOverridesBtn = document.getElementById('reset-overrides');

    let currentMode = 'static';
    let currentDevice = 'desktop';
    let currentContentId = null;
    const widgetId = {{ $widget->id }};

    // Set up event listeners for preview manager
    previewManager.on('render-start', (data) => {
        showLoading(true);
        setStatus('info', 'Loading preview...');
    });

    previewManager.on('render-complete', (data) => {
        showLoading(false);
        previewManager.updatePreviewContainer('preview-container', data.data.html, {
            css: data.data.css || [],
            js: data.data.js || []
        });
        setStatus('success', `${data.type.replace('-', ' ')} preview loaded successfully`);
        updatePreviewMetadata('render-time', data.data.meta?.render_time || '< 200ms');
        updatePreviewMetadata('assets-count', (data.data.css?.length || 0) + (data.data.js?.length || 0));
        updatePreviewMetadata('cache-status', data.data.meta?.cached ? 'Cached' : 'Fresh');
    });

    previewManager.on('render-error', (data) => {
        showLoading(false);
        showError(data.error.message || 'Failed to load preview');
        setStatus('danger', 'Preview failed');
    });

    previewManager.on('content-options-loaded', (data) => {
        if (data.data.success && data.data.content_items) {
            contentSelect.innerHTML = '<option value="">Select content item...</option>';
            data.data.content_items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.title} (${item.content_type})`;
                contentSelect.appendChild(option);
            });
        }
    });

    // Initialize
    loadStaticPreview();
    loadContentOptions();

    // Event listeners
    staticPreviewBtn.addEventListener('click', () => switchMode('static'));
    livePreviewBtn.addEventListener('click', () => switchMode('live'));
    contentSelect.addEventListener('change', handleContentChange);
    refreshBtn.addEventListener('click', refreshPreview);
    settingsBtn.addEventListener('click', toggleSettings);
    fullscreenBtn.addEventListener('click', toggleFullscreen);
    applyOverridesBtn.addEventListener('click', applyOverrides);
    resetOverridesBtn.addEventListener('click', resetOverrides);

    deviceBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const device = e.target.dataset.device;
            switchDevice(device);
        });
    });

    // Debounced override application
    const debouncedApplyOverrides = debounce(applyOverrides, 1000);
    fieldOverridesTextarea.addEventListener('input', debouncedApplyOverrides);
    settingsOverridesTextarea.addEventListener('input', debouncedApplyOverrides);

    function switchMode(mode) {
        currentMode = mode;
        
        // Update buttons with Bootstrap classes
        if (mode === 'static') {
            staticPreviewBtn.classList.remove('btn-outline-primary');
            staticPreviewBtn.classList.add('btn-primary');
            livePreviewBtn.classList.remove('btn-primary');
            livePreviewBtn.classList.add('btn-outline-primary');
        } else {
            livePreviewBtn.classList.remove('btn-outline-primary');
            livePreviewBtn.classList.add('btn-primary');
            staticPreviewBtn.classList.remove('btn-primary');
            staticPreviewBtn.classList.add('btn-outline-primary');
        }
        
        // Show/hide content sections
        const staticContent = document.getElementById('static-preview-content');
        const liveContent = document.getElementById('live-preview-content');
        
        if (mode === 'static') {
            staticContent.style.display = 'block';
            liveContent.style.display = 'none';
        } else {
            staticContent.style.display = 'none';
            liveContent.style.display = 'block';
        }
        
        // Show/hide content selection (only visible in live mode)
        const contentSelection = document.getElementById('content-selection');
        if (contentSelection) {
            contentSelection.style.display = mode === 'live' ? 'block' : 'none';
        }
        
        // Load appropriate preview
        if (mode === 'static') {
            loadStaticPreview();
        } else {
            loadLivePreview();
        }
        
        setStatus('info', `Switched to ${mode} preview mode`);
    }

    function switchDevice(device) {
        currentDevice = device;
        
        // Update buttons
        deviceBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.device === device);
        });
        
        // Update preview container class
        previewContainer.className = `preview-frame ${device}`;
        
        // Refresh current preview
        refreshPreview();
        
        setStatus('info', `Switched to ${device} view`);
    }

    function handleContentChange() {
        currentContentId = contentSelect.value;
        if (currentMode === 'live') {
            loadLivePreview();
        }
    }

    function refreshPreview() {
        if (currentMode === 'static') {
            loadStaticPreview();
        } else {
            loadLivePreview();
        }
    }

    function toggleSettings() {
        const isVisible = settingsPanel.style.display !== 'none';
        settingsPanel.style.display = isVisible ? 'none' : 'block';
        settingsBtn.classList.toggle('active', !isVisible);
    }

    async function loadStaticPreview() {
        try {
            // Use frontend page preview approach - load existing page with widget filtering
            const previewUrl = `/admin/api/widgets/${widgetId}/frontend-page-preview`;
            
            // Create or update iframe for full page preview
            let iframe = previewContainer.querySelector('iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = '600px';
                iframe.style.border = 'none';
                iframe.style.borderRadius = '8px';
                previewContainer.innerHTML = '';
                previewContainer.appendChild(iframe);
            }
            
            // Load the full preview page
            iframe.src = previewUrl;
            
            // Listen for iframe load
            iframe.onload = function() {
                setStatus('success', 'Static preview loaded successfully');
                updatePreviewMetadata('render-time', '< 200ms');
                updatePreviewMetadata('assets-count', 'Full Theme Assets');
                updatePreviewMetadata('cache-status', 'Fresh');
            };
            
            iframe.onerror = function() {
                showError('Failed to load preview page');
            };
            
        } catch (error) {
            console.error('Static preview error:', error);
            showError('Network error loading static preview');
        }
    }

    async function loadLivePreview() {
        try {
            const fieldOverrides = parseJsonSafely(fieldOverridesTextarea.value);
            
            // Build preview URL with parameters for full page preview
            const params = new URLSearchParams();
            if (currentContentId) {
                params.append('content_id', currentContentId);
            }
            if (fieldOverrides && Object.keys(fieldOverrides).length > 0) {
                params.append('field_overrides', JSON.stringify(fieldOverrides));
            }
            params.append('device', currentDevice);
            
            const previewUrl = `/admin/api/widgets/${widgetId}/frontend-page-preview?${params.toString()}`;
            
            // Create or update iframe for full page preview
            let iframe = previewContainer.querySelector('iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = '600px';
                iframe.style.border = 'none';
                iframe.style.borderRadius = '8px';
                previewContainer.innerHTML = '';
                previewContainer.appendChild(iframe);
            }
            
            // Load the full preview page with content
            iframe.src = previewUrl;
            
            // Listen for iframe load
            iframe.onload = function() {
                const statusText = currentContentId ? 'Live preview with content loaded' : 'Live preview loaded';
                setStatus('success', statusText);
                updatePreviewMetadata('render-time', '< 250ms');
                updatePreviewMetadata('assets-count', 'Full Theme Assets');
                updatePreviewMetadata('cache-status', 'Fresh');
            };
            
            iframe.onerror = function() {
                showError('Failed to load live preview page');
            };
            
        } catch (error) {
            console.error('Live preview error:', error);
            showError('Network error loading live preview');
        }
    }

    async function loadContentOptions() {
        try {
            await previewManager.getWidgetContentOptions(widgetId);
        } catch (error) {
            console.error('Error loading content options:', error);
        }
    }

    function applyOverrides() {
        if (currentMode === 'live') {
            loadLivePreview();
        }
    }

    function resetOverrides() {
        fieldOverridesTextarea.value = '';
        settingsOverridesTextarea.value = '';
        if (currentMode === 'live') {
            loadLivePreview();
        }
        setStatus('info', 'Overrides reset');
    }

    function showLoading(show) {
        const loading = document.getElementById('preview-loading');
        const error = document.getElementById('preview-error');
        
        loading.style.display = show ? 'block' : 'none';
        error.style.display = 'none';
        if (show) {
            previewContainer.innerHTML = '';
        }
    }

    function showError(message) {
        showLoading(false);
        const error = document.getElementById('preview-error');
        const errorMessage = document.getElementById('error-message');
        
        error.style.display = 'block';
        errorMessage.textContent = message;
    }

    function setStatus(type, message) {
        const badge = document.getElementById('preview-status-badge');
        const text = document.getElementById('preview-status-text');
        
        badge.className = `badge bg-${type}`;
        badge.textContent = type.charAt(0).toUpperCase() + type.slice(1);
        text.textContent = message;
    }

    function updatePreviewMetadata(key, value) {
        const element = document.getElementById(key.replace('_', '-'));
        if (element) {
            element.textContent = value;
        }
    }

    function toggleFullscreen() {
        const container = previewContainer.parentElement;
        if (!document.fullscreenElement) {
            container.requestFullscreen();
            fullscreenBtn.innerHTML = '<i class="bx bx-exit-fullscreen"></i>';
        } else {
            document.exitFullscreen();
            fullscreenBtn.innerHTML = '<i class="bx bx-fullscreen"></i>';
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function parseJsonSafely(jsonString) {
        try {
            return jsonString ? JSON.parse(jsonString) : {};
        } catch (e) {
            console.warn('Invalid JSON:', jsonString);
            return {};
        }
    }
    
    } // End of initializePreview function
    
    // Start the initialization
    initializePreview();
});
</script>