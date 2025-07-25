@extends('admin.layouts.master')

@section('page-title', 'Page Designer: ' . $page->title)

@section('content')

<div class="container-fluid">
        <!-- Designer Toolbar -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="designer-toolbar">
                    <div class="toolbar-left">
                        <button class="btn btn-primary" id="savePageBtn">
                            <i class="ri-save-line"></i> Save Page
                        </button>
                        <button class="btn btn-outline-secondary" id="previewBtn">
                            <i class="ri-eye-line"></i> Preview
                        </button>
                    </div>
                    
                    <div class="toolbar-center">
                        <div class="device-switcher">
                            <button class="btn btn-sm btn-outline-secondary active" data-device="desktop" title="Desktop">
                                <i class="ri-computer-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" data-device="tablet" title="Tablet">
                                <i class="ri-tablet-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" data-device="mobile" title="Mobile">
                                <i class="ri-smartphone-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="toolbar-right">
                        <button class="btn btn-outline-info" id="undoBtn" disabled>
                            <i class="ri-arrow-go-back-line"></i> Undo
                        </button>
                        <button class="btn btn-outline-info" id="redoBtn" disabled>
                            <i class="ri-arrow-go-forward-line"></i> Redo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Designer Layout -->
        <div class="row">
            <!-- Widget Library Sidebar -->
            <div class="col-md-3">
                <div class="widget-library-panel">
                    <div class="panel-header">
                        <h6 class="panel-title">
                            <i class="ri-apps-line"></i> Widget Library
                        </h6>
                        <div class="panel-controls">
                            <button class="btn btn-sm btn-outline-secondary" id="refreshWidgetsBtn">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="panel-body">
                        <!-- Widget Categories -->
                        <div class="widget-categories">
                            <div class="category-section">
                                <h6 class="category-title">Content Widgets</h6>
                                <div class="widget-grid" id="contentWidgets">
                                    <!-- Content widgets loaded dynamically -->
                                </div>
                            </div>
                            
                            <div class="category-section">
                                <h6 class="category-title">Layout Widgets</h6>
                                <div class="widget-grid" id="layoutWidgets">
                                    <!-- Layout widgets loaded dynamically -->
                                </div>
                            </div>
                            
                            <div class="category-section">
                                <h6 class="category-title">Media Widgets</h6>
                                <div class="widget-grid" id="mediaWidgets">
                                    <!-- Media widgets loaded dynamically -->
                                </div>
                            </div>
                            
                            <div class="category-section">
                                <h6 class="category-title">Form Widgets</h6>
                                <div class="widget-grid" id="formWidgets">
                                    <!-- Form widgets loaded dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="col-md-6">
                <div class="canvas-container">
                    <div class="canvas-toolbar">
                        <div class="canvas-controls">
                            <button class="btn btn-sm btn-outline-secondary" id="addSectionBtn">
                                <i class="ri-add-line"></i> Add Section
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="clearCanvasBtn">
                                <i class="ri-delete-bin-line"></i> Clear All
                            </button>
                        </div>
                        
                        <div class="canvas-info">
                            <span class="canvas-stats">
                                <span id="sectionCount">0</span> sections, 
                                <span id="widgetCount">0</span> widgets
                            </span>
                        </div>
                    </div>
                    
                    <div class="canvas-wrapper">
                        <div class="page-sections-container" id="pageSectionsContainer" data-page-id="{{ $page->id }}">
                            <!-- Sections loaded dynamically -->
                            <div class="section-add-zone" id="addFirstSection">
                                <div class="section-add-prompt">
                                    <i class="ri-add-line"></i>
                                    <span>Click to add your first section</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Panel -->
            <div class="col-md-3">
                <div class="properties-panel" id="propertiesPanel" style="display: none;">
                    <div class="panel-header">
                        <h6 class="panel-title">
                            <i class="ri-settings-line"></i> Properties
                        </h6>
                        <button class="btn btn-sm btn-outline-secondary" id="closePropertiesBtn">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    
                    <div class="panel-body">
                        <div id="widgetPropertiesContainer">
                            <!-- Properties loaded dynamically -->
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div class="properties-empty" id="propertiesEmpty">
                    <div class="text-center text-muted py-4">
                        <i class="ri-cursor-line fs-1"></i>
                        <p>Select a widget or section to edit its properties</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Widget Configuration Modal -->
<div class="modal fade" id="widgetConfigModal" tabindex="-1" aria-labelledby="widgetConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="widgetConfigModalLabel">Configure Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="widgetConfigForm">
                    <!-- Dynamic form content loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWidgetConfigBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Section Templates Modal -->
<div class="modal fade" id="sectionTemplatesModal" tabindex="-1" aria-labelledby="sectionTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionTemplatesModalLabel">Choose Section Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="section-template-grid" id="sectionTemplateGrid">
                    <!-- Section templates loaded dynamically -->
                </div>
            </div>
        </div>
</div>


@endsection

@push('styles')
<!-- GridStack CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet">
<!-- Custom Designer CSS -->
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- GridStack JS -->
<script src="{{ asset('assets/admin/libs/gridstack/dist/gridstack-all.js') }}"></script>
<!-- SortableJS for section reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<!-- Custom Designer JS -->
<script src="{{ asset('assets/admin/js/gridstack/gridstack-page-builder.js') }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/widget-library.js') }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/widget-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/theme-integration.js') }}"></script>

<script>
// Initialize the page designer when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Configuration for the page builder
    const config = {
        pageId: {{ $page->id }},
        apiBaseUrl: '/admin/api',
        csrfToken: '{{ csrf_token() }}',
        theme: '{{ $page->template->theme->slug ?? "default" }}'
    };
    
    // Initialize all components
    Promise.all([
        window.GridStackPageBuilder.init(config),
        window.WidgetLibrary.init(),
        window.WidgetManager.init(),
        window.ThemeIntegration.init()
    ]).then(() => {
        console.log('✅ GridStack Page Designer initialized successfully');
    }).catch(error => {
        console.error('❌ Error initializing page designer:', error);
    });
});
</script>
@endpush 