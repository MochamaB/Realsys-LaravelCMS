@extends('admin.layouts.master')

@section('title', 'Preview: ' . $contentItem->title)

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    <i class="bx bx-show me-2"></i>Preview: {{ $contentItem->title }}
                </h4>

                <div class="page-title-right">
                    <div class="btn-group">
                        <a href="{{ route('admin.content-types.items.edit', [$contentType, $contentItem]) }}" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.content-types.items.show', [$contentType, $contentItem]) }}" class="btn btn-info">
                            <i class="bx bx-show me-1"></i> View Details
                        </a>
                        <a href="{{ route('admin.content-types.items.index', $contentType) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Content Preview System -->
    <div class="unified-content-preview-system">
        <!-- Preview Mode Controls -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-file-blank me-2"></i>Content Preview
                    </h5>
                    <div class="preview-mode-switcher">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="content-only-btn">
                                <i class="bx bx-file me-1"></i>Content Only
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="widget-preview-btn">
                                <i class="bx bx-widget me-1"></i>With Widget
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Content Only Preview -->
                <div id="content-only-preview" class="preview-section">
                    <!-- Content Status and Info -->
                    <div class="content-info-bar border-bottom p-3 bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <div>
                                    @if($contentItem->status == 'published')
                                        <span class="badge bg-success">
                                            <i class="bx bx-check-circle me-1"></i>Published
                                        </span>
                                    @elseif($contentItem->status == 'draft')
                                        <span class="badge bg-warning">
                                            <i class="bx bx-edit me-1"></i>Draft
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bx bx-archive me-1"></i>Archived
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Content Type</label>
                                <div class="text-muted">{{ $contentType->name }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Created</label>
                                <div class="text-muted">{{ $contentItem->created_at->format('M j, Y') }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Last Modified</label>
                                <div class="text-muted">{{ $contentItem->updated_at->format('M j, Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Preview -->
                    <div class="content-preview-container p-4">
                        <div class="content-preview-wrapper">
                            <h1 class="content-title mb-4">{{ $contentItem->title }}</h1>
                            
                            @if($contentItem->fieldValues->count() > 0)
                                <div class="content-fields">
                                    @foreach($contentItem->fieldValues as $fieldValue)
                                        <div class="field-preview mb-4" data-field-type="{{ $fieldValue->field->type }}">
                                            @switch($fieldValue->field->type)
                                                @case('rich_text')
                                                    <div class="rich-text-content">
                                                        {!! $fieldValue->value !!}
                                                    </div>
                                                    @break
                                                    
                                                @case('image')
                                                    @if($contentItem->getMedia('field_' . $fieldValue->field->id)->count() > 0)
                                                        <div class="image-content">
                                                            <img src="{{ $contentItem->getFirstMediaUrl('field_' . $fieldValue->field->id) }}" 
                                                                 alt="{{ $fieldValue->field->label }}" 
                                                                 class="img-fluid rounded shadow-sm">
                                                        </div>
                                                    @endif
                                                    @break
                                                    
                                                @case('text')
                                                @case('textarea')
                                                    @if($fieldValue->value)
                                                        <div class="text-content">
                                                            <h6 class="field-label text-muted">{{ $fieldValue->field->label }}</h6>
                                                            <p>{{ $fieldValue->value }}</p>
                                                        </div>
                                                    @endif
                                                    @break
                                                    
                                                @default
                                                    @if($fieldValue->value)
                                                        <div class="default-content">
                                                            <h6 class="field-label text-muted">{{ $fieldValue->field->label }}</h6>
                                                            <div>{{ $fieldValue->value }}</div>
                                                        </div>
                                                    @endif
                                            @endswitch
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    This content item has no field values to display.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Widget Preview Content -->
                <div id="widget-preview-content" class="preview-section" style="display: none;">
                    <!-- Widget Preview Controls -->
                    <div class="widget-preview-controls border-bottom p-3 bg-light">
                        <div class="row g-3">
                            <!-- Widget Selection -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-widget me-1"></i>Select Widget
                                </label>
                                <select class="form-select" id="widget-select">
                                    <option value="">Choose a widget...</option>
                                    <!-- Dynamic options loaded via JavaScript -->
                                </select>
                                <small class="text-muted">Select a widget to preview this content with</small>
                            </div>

                            <!-- Device Size Controls -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-devices me-1"></i>Device Size
                                </label>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-secondary device-btn active" data-size="desktop" data-width="100%">
                                        <i class="bx bx-desktop"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary device-btn" data-size="tablet" data-width="768px">
                                        <i class="bx bx-tablet"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary device-btn" data-size="mobile" data-width="375px">
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
                                    <button type="button" class="btn btn-success flex-fill" id="refresh-widget-preview-btn">
                                        <i class="bx bx-refresh me-1"></i>Refresh
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="widget-settings-toggle-btn" title="Settings">
                                        <i class="bx bx-cog"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="widget-fullscreen-btn" title="Fullscreen">
                                        <i class="bx bx-fullscreen"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Widget Settings Panel (Collapsible) -->
                        <div class="advanced-widget-settings mt-3" id="advanced-widget-settings" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bx bx-code-alt me-1"></i>Widget Settings Override (JSON)
                                    </label>
                                    <textarea class="form-control font-monospace" id="widget-settings-override" rows="4" placeholder='{"layout": "grid", "columns": 3, "show_excerpt": true}'></textarea>
                                    <small class="text-muted">Override widget settings for preview</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bx bx-map me-1"></i>Field Mapping Override (JSON)
                                    </label>
                                    <textarea class="form-control font-monospace" id="field-mapping-override" rows="4" placeholder='{"widget_title": "content_title", "widget_description": "content_excerpt"}'></textarea>
                                    <small class="text-muted">Override field mappings between content and widget</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Widget Preview Container -->
                    <div class="widget-preview-container-wrapper">
                        <div class="widget-preview-container" id="content-widget-preview-container">
                            <!-- Widget Selection Prompt -->
                            <div class="widget-selection-prompt text-center py-5" id="widget-selection-prompt">
                                <div class="text-muted mb-3">
                                    <i class="bx bx-widget" style="font-size: 4rem; opacity: 0.3;"></i>
                                </div>
                                <h5 class="text-muted">Select a Widget</h5>
                                <p class="text-muted">Choose a widget from the dropdown above to see how this content looks when rendered through that widget.</p>
                            </div>

                            <!-- Loading State -->
                            <div class="widget-preview-loading text-center py-5" id="widget-preview-loading" style="display: none;">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="text-muted">Loading Widget Preview...</h5>
                                <p class="text-muted">Rendering content through selected widget</p>
                            </div>

                            <!-- Actual Widget Preview Content -->
                            <div class="widget-preview-content" id="widget-preview-rendered-content" style="display: none;">
                                <!-- Widget rendered content will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles for Content Preview System -->
<style>
.unified-content-preview-system {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.preview-section {
    min-height: 500px;
}

.content-preview-wrapper {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.content-title {
    color: #2c3e50;
    font-weight: 600;
    line-height: 1.3;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 1rem;
}

.field-preview {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
    margin-left: 0.5rem;
}

.field-preview[data-field-type="rich_text"] {
    border-left-color: #28a745;
}

.field-preview[data-field-type="image"] {
    border-left-color: #17a2b8;
}

.field-preview[data-field-type="text"] {
    border-left-color: #ffc107;
}

.field-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.widget-preview-container-wrapper {
    position: relative;
    background: #f8f9fa;
    min-height: 500px;
}

.widget-preview-container {
    transition: max-width 0.3s ease;
    margin: 0 auto;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    min-height: 500px;
    position: relative;
}

.device-btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.font-monospace {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
}

.advanced-widget-settings {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    background: #f8f9fa;
}

/* Responsive widget preview container */
.widget-preview-container[data-device="desktop"] { max-width: 100%; }
.widget-preview-container[data-device="tablet"] { max-width: 768px; }
.widget-preview-container[data-device="mobile"] { max-width: 375px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for UniversalPreviewManager to be available with retry (same pattern as working widget preview)
    function initializeContentPreview() {
        if (typeof window.universalPreviewManager === 'undefined' || typeof window.UniversalPreviewManager === 'undefined') {
            console.log('Waiting for UniversalPreviewManager...');
            setTimeout(initializeContentPreview, 100);
            return;
        }

        // Ensure we have an instance
        if (!window.universalPreviewManager) {
            window.universalPreviewManager = new window.UniversalPreviewManager();
        }

        const previewManager = window.universalPreviewManager;
    
    // Initialize content preview functionality
    const contentOnlyBtn = document.getElementById('content-only-btn');
    const widgetPreviewBtn = document.getElementById('widget-preview-btn');
    const contentOnlyPreview = document.getElementById('content-only-preview');
    const widgetPreviewContent = document.getElementById('widget-preview-content');
    const widgetSelect = document.getElementById('widget-select');
    const deviceBtns = document.querySelectorAll('.device-btn');
    const refreshWidgetBtn = document.getElementById('refresh-widget-preview-btn');
    const widgetSettingsBtn = document.getElementById('widget-settings-toggle-btn');
    const widgetFullscreenBtn = document.getElementById('widget-fullscreen-btn');
    const widgetPreviewContainer = document.getElementById('widget-preview-rendered-content');
    const widgetLoadingContainer = document.getElementById('widget-preview-loading');

    let currentMode = 'content-only';
    let currentDevice = 'desktop';
    let currentWidgetId = null;
    const contentItemId = {{ $contentItem->id ?? 'null' }};

    // Set up event listeners for preview manager
    previewManager.on('render-start', (data) => {
        if (data.type === 'content-with-widget') {
            showWidgetLoading(true);
        }
    });

    previewManager.on('render-complete', (data) => {
        if (data.type === 'content-with-widget') {
            showWidgetLoading(false);
            previewManager.updatePreviewContainer('widget-preview-rendered-content', data.data.html, {
                css: data.data.css || [],
                js: data.data.js || []
            });
            widgetPreviewContainer.style.display = 'block';
        }
    });

    previewManager.on('render-error', (data) => {
        if (data.type === 'content-with-widget') {
            showWidgetLoading(false);
            showWidgetError(data.error.message || 'Failed to load widget preview');
        }
    });

    previewManager.on('widget-options-loaded', (data) => {
        if (data.data.success && data.data.widgets) {
            widgetSelect.innerHTML = '<option value="">Choose a widget...</option>';
            data.data.widgets.forEach(widget => {
                const option = document.createElement('option');
                option.value = widget.id;
                option.textContent = `${widget.name} (${widget.theme_name})`;
                widgetSelect.appendChild(option);
            });
        }
    });

    // Initialize
    loadWidgetOptions();

    // Event listeners
    contentOnlyBtn.addEventListener('click', () => switchMode('content-only'));
    widgetPreviewBtn.addEventListener('click', () => switchMode('widget-preview'));
    widgetSelect.addEventListener('change', handleWidgetChange);
    refreshWidgetBtn.addEventListener('click', refreshWidgetPreview);
    widgetSettingsBtn.addEventListener('click', toggleWidgetSettings);
    widgetFullscreenBtn.addEventListener('click', toggleWidgetFullscreen);

    deviceBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const device = e.target.closest('.device-btn').dataset.size;
            switchDevice(device);
        });
    });

    function switchMode(mode) {
        currentMode = mode;
        
        // Update buttons
        contentOnlyBtn.classList.toggle('btn-primary', mode === 'content-only');
        contentOnlyBtn.classList.toggle('btn-outline-primary', mode !== 'content-only');
        widgetPreviewBtn.classList.toggle('btn-primary', mode === 'widget-preview');
        widgetPreviewBtn.classList.toggle('btn-outline-primary', mode !== 'widget-preview');
        
        // Show/hide content sections
        contentOnlyPreview.style.display = mode === 'content-only' ? 'block' : 'none';
        widgetPreviewContent.style.display = mode === 'widget-preview' ? 'block' : 'none';
        
        // Load widget preview if switching to widget mode
        if (mode === 'widget-preview' && currentWidgetId) {
            loadWidgetPreview();
        }
    }

    function switchDevice(device) {
        currentDevice = device;
        
        // Update buttons
        deviceBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.size === device);
        });
        
        // Update preview container
        const container = document.querySelector('.widget-preview-container');
        if (container) {
            container.setAttribute('data-device', device);
        }
        
        // Refresh widget preview if active
        if (currentMode === 'widget-preview' && currentWidgetId) {
            loadWidgetPreview();
        }
    }

    function handleWidgetChange() {
        currentWidgetId = widgetSelect.value;
        if (currentWidgetId && currentMode === 'widget-preview') {
            loadWidgetPreview();
        }
    }

    function refreshWidgetPreview() {
        if (currentWidgetId && currentMode === 'widget-preview') {
            loadWidgetPreview();
        }
    }

    function toggleWidgetSettings() {
        const advancedSettings = document.getElementById('advanced-widget-settings');
        if (advancedSettings) {
            const isVisible = advancedSettings.style.display !== 'none';
            advancedSettings.style.display = isVisible ? 'none' : 'block';
            widgetSettingsBtn.classList.toggle('active', !isVisible);
        }
    }

    function toggleWidgetFullscreen() {
        const container = widgetPreviewContainer.parentElement;
        if (!document.fullscreenElement) {
            container.requestFullscreen();
            widgetFullscreenBtn.innerHTML = '<i class="bx bx-exit-fullscreen"></i>';
        } else {
            document.exitFullscreen();
            widgetFullscreenBtn.innerHTML = '<i class="bx bx-fullscreen"></i>';
        }
    }

    async function loadWidgetOptions() {
        if (!contentItemId) return;
        
        try {
            const response = await previewManager.getContentWidgetOptions(contentItemId);
            if (response.success && response.widgets) {
                populateWidgetSelect(response.widgets);
            }
        } catch (error) {
            console.error('Error loading widget options:', error);
        }
    }

    function populateWidgetSelect(widgets) {
        if (!widgetSelect) return;
        
        // Clear existing options except the first one
        widgetSelect.innerHTML = '<option value="">Select a widget...</option>';
        
        // Add widget options
        widgets.forEach(widget => {
            const option = document.createElement('option');
            option.value = widget.id;
            option.textContent = widget.name;
            option.title = widget.description;
            widgetSelect.appendChild(option);
        });
    }

    async function loadWidgetPreview() {
        if (!currentWidgetId || !contentItemId) return;
        
        try {
            // Get any field mapping overrides from advanced settings
            const fieldMappingTextarea = document.getElementById('field-mapping-override');
            const widgetSettingsTextarea = document.getElementById('widget-settings-override');
            
            const fieldMappingOverrides = fieldMappingTextarea ? parseJsonSafely(fieldMappingTextarea.value) : {};
            const widgetSettingsOverrides = widgetSettingsTextarea ? parseJsonSafely(widgetSettingsTextarea.value) : {};
            
            await previewManager.renderContentWithWidget({
                content_item_id: contentItemId,
                widget_id: currentWidgetId,
                field_mapping_overrides: fieldMappingOverrides,
                widget_settings_overrides: widgetSettingsOverrides,
                device: currentDevice
            });
        } catch (error) {
            console.error('Widget preview error:', error);
            showWidgetError('Failed to load widget preview');
        }
    }

    function showWidgetLoading(show) {
        widgetLoadingContainer.style.display = show ? 'block' : 'none';
        if (!show) {
            const errorContainer = document.getElementById('widget-preview-error');
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }
        if (show) {
            widgetPreviewContainer.style.display = 'none';
        }
    }

    function showWidgetError(message) {
        showWidgetLoading(false);
        let errorContainer = document.getElementById('widget-preview-error');
        
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'widget-preview-error';
            errorContainer.className = 'alert alert-danger d-flex align-items-center';
            errorContainer.innerHTML = `
                <i class="bx bx-error-circle me-2"></i>
                <div>
                    <strong>Widget Preview Error:</strong> 
                    <span id="widget-error-message">${message}</span>
                </div>
            `;
            widgetPreviewContainer.parentNode.insertBefore(errorContainer, widgetPreviewContainer);
        } else {
            errorContainer.style.display = 'block';
            const errorMessage = errorContainer.querySelector('#widget-error-message');
            if (errorMessage) {
                errorMessage.textContent = message;
            }
        }
        
        widgetPreviewContainer.style.display = 'none';
    }

    function parseJsonSafely(jsonString) {
        try {
            return jsonString ? JSON.parse(jsonString) : {};
        } catch (e) {
            console.warn('Invalid JSON:', jsonString);
            return {};
        }
    }
    
    } // End of initializeContentPreview function
    
    // Start the initialization
    initializeContentPreview();
});
</script>

@endsection
