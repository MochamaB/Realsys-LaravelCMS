@extends('admin.layouts.designer-layout')

@section('title', 'Live Designer - ' . $page->title)

@section('css')
<!-- Simplified Live Designer CSS -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/simple-live-designer.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/sidebar-layout.css') }}">

<style>
/* Simplified Live Designer Layout */

.designer-toolbar {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    flex-shrink: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.device-controls {
    display: flex;
    gap: 0.5rem;
}

.device-btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.device-btn:hover {
    background: #f8f9fa;
}

.device-btn.active {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}

.designer-actions {
    display: flex;
    gap: 0.5rem;
}

.designer-content {
    flex: 1;
    display: flex;
    overflow: hidden;
}



.canvas-container {
    background: #f1f4f7;
    border-radius: 0px;
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    overflow-x: auto;
    overflow-y: hidden;
}


/* Loading state */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-content {
    text-align: center;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile responsive - Right sidebar only */
@media (max-width: 991.98px) {
}

.hidden {
    display: none !important;
}

/* ===============================
   SIMPLIFIED LIVE DESIGNER LAYOUT
   =============================== */

/* Main designer content - Bootstrap grid based layout */
.container-fluid.h-100 {
    padding: 0 !important;
    margin: 0 !important;
}

.row.h-100 {
    min-height: 100vh;
    margin: 0 !important;
    display: flex;
    flex-wrap: nowrap;
}

.row.h-100 > .col-auto,
.row.h-100 > .col {
    padding: 0 !important;
}

/* Left Sidebar - Fixed width with dynamic height */
#leftSidebarContainer {
    width: 280px;
    min-width: 280px;
    flex-shrink: 0;
    transition: width 0.3s ease, min-width 0.3s ease;
    background: #fff;
    border-right: 1px solid #e9ecef;
    overflow-y: auto;
    min-height: 100vh;
}

#leftSidebarContainer.collapsed {
    width: 70px;
    min-width: 70px;
}

/* Canvas Container - Takes remaining space between sidebars */
#canvasContainer {
    position: relative;
    background: #f1f4f7;
    padding: 10px;
    transition: all 0.3s ease;
    flex: 1;
    min-height: 100vh;
    overflow-y: auto;
}

/* Right Sidebar - Fixed width, touches right edge */
#right-sidebar-container {
    width: 280px;
    min-width: 280px;
    flex-shrink: 0;
    transition: width 0.3s ease, min-width 0.3s ease;
    background: #fff;
    border-left: 1px solid #e9ecef;
    margin-right: 0 !important;
    padding-right: 0 !important;
    overflow-y: auto;
    min-height: 100vh;
}

#right-sidebar-container.collapsed {
    width: 70px;
    min-width: 70px;
}

/* Preview Iframe - Dynamic height with no scrollbar */
#preview-iframe {
    width: 100%;
    height: auto;
    border: none;
    display: block;
    margin: 0 auto;
    overflow: hidden;
    background: #fff;
}

/* Device preview styling is now handled by device-preview.js */

/* Responsive behavior for mobile screens */
@media (max-width: 991.98px) {
    .row.h-100 {
        position: relative;
    }
    
    #leftSidebarContainer,
    #right-sidebar-container {
        position: fixed;
        top: 100px;
        bottom: 0;
        z-index: 1045;
        width: 320px;
        max-width: 90vw;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        height: calc(100vh - 100px);
    }
    
    #leftSidebarContainer.show,
    #right-sidebar-container.show {
        transform: translateX(0);
    }
    
    #right-sidebar-container {
        right: 0;
        left: auto;
        transform: translateX(100%);
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    #right-sidebar-container.show {
        transform: translateX(0);
    }
    
    #canvasContainer {
        width: 100%;
    }
    
    #preview-iframe {
        width: 100%;
    }
}

/* Unified Loader Styles */
.unified-page-loader {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(2px);
}

.unified-page-loader .progress-bar {
    width: 200px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 1rem;
    position: relative;
}

.unified-page-loader .progress-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, #0d6efd, transparent);
    animation: progressSlide 1.5s infinite;
}

@keyframes progressSlide {
    0% { left: -100%; }
    100% { left: 100%; }
}

.unified-page-loader .loader-message {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Drill-down button styles */
.drill-down-btn {
    background: #0d6efd;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    flex-shrink: 0;
}

.drill-down-btn:hover {
    background: #0b5ed7;
    transform: scale(1.1);
}

.drill-down-btn i {
    font-size: 10px;
    line-height: 1;
}

.drill-down-btn .content-count {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

/* Widget title row adjustments */
.widget-title-row,
.label-row {
    padding: 0.5rem;
    margin: 0;
}

.widget-title,
.label {
    flex: 1;
    margin-right: 0.5rem;
}

/* Ensure proper spacing for widgets with drill-down */
.theme-widget-item.has-drill-down .widget-title,
.component-item.has-drill-down .label {
    margin-right: 0.75rem;
}

/* Content Types and Items Grid Styles */
.content-types-grid,
.content-items-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
    padding: 1rem;
}

.content-type-card,
.content-item-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.content-type-card:hover,
.content-item-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    transform: translateY(-1px);
}

/* Content Type Card Styles */
.content-type-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.content-type-info {
    flex: 1;
}

.content-type-name {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

.content-type-count {
    font-size: 0.875rem;
    color: #6c757d;
}

.content-type-arrow {
    color: #6c757d;
    font-size: 1rem;
    flex-shrink: 0;
}

/* Content Item Card Styles */
.content-item-thumbnail {
    width: 50px;
    height: 50px;
    border-radius: 0.375rem;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.content-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.content-item-info {
    flex: 1;
}

.content-item-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.content-item-status {
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 1rem;
    text-transform: uppercase;
    font-weight: 600;
    display: inline-block;
}

.content-item-status[data-status="published"],
.content-item-status:contains("published") {
    background: #d1e7dd;
    color: #0f5132;
}

.content-item-status[data-status="draft"],
.content-item-status:contains("draft") {
    background: #fff3cd;
    color: #664d03;
}

/* View transitions */
.widgets-view,
.content-types-view,
.content-items-view {
    transition: opacity 0.3s ease;
}

.content-types-view,
.content-items-view {
    display: none;
}

/* Breadcrumb navigation */
#drillDownBreadcrumb {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    display: none;
    align-items: center;
    gap: 0.5rem;
}

#drillDownBack {
    background: none;
    border: none;
    color: #0d6efd;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

#drillDownBack:hover {
    background: rgba(13, 110, 253, 0.1);
}

#breadcrumbText {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}
</style>
@endsection

@section('content')
<div class="container-fluid h-100 p-0">
    <!-- Toolbar -->
    <div class="row g-0">
        <div class="col-12 p-0">
            @include('admin.pages.live-designer.components.toolbar')
        </div>
    </div>
    
    <!-- Main Content - Three Column Layout -->
    <div class="row g-0 h-100">
        <!-- Left Sidebar -->
        <div class="col-auto" id="leftSidebarContainer">
            @include('admin.pages.live-designer.components.left-sidebar')
        </div>
        
        <!-- Canvas Container -->
        <div class="col" id="canvasContainer" style="padding:20px 0px !important">
            <!-- Unified Progress Bar Loader -->
            <div class="unified-page-loader" id="liveDesignerLoader" style="display: none;">
                <div class="progress-bar"></div>
                <div class="loader-message">Loading...</div>
            </div>
            
            <!-- Preview Iframe (Direct) -->
            <iframe id="preview-iframe" 
            src="{{ route('admin.api.live-preview.preview-iframe', $page) }}" 
            scrolling="no">
            </iframe>
        </div>
        
        <!-- Right Sidebar (Collapsed by Default) -->
        <div class="col-auto collapsed" id="right-sidebar-container">
            @include('admin.pages.live-designer.components.right-sidebar')
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="modal-backdrop fade" id="mobile-overlay" style="display: none;"></div>

<!-- Widget Library Modal -->
<div class="modal fade" id="widget-library-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="widget-library-content">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                        <p>Loading widgets...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1060;"></div>
@endsection

@section('js')
<!-- Live Designer JavaScript -->
<script src="{{ asset('assets/admin/js/live-designer/live-preview.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-form-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/device-preview.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/update-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/sidebar-manager.js') }}?v={{ time() }}"></script>
<!-- Left Sidebar Component Library Modules -->
<script src="{{ asset('assets/admin/js/live-designer/unified-loader-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/template-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-drill-down.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-library.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/default-widget-library.js') }}?v={{ time() }}"></script>
@endsection

@push('scripts')
<script>
// Initialize Simplified Live Designer
document.addEventListener('DOMContentLoaded', async function() {
    // Initialize the live preview system
    const livePreview = new LivePreview({
        pageId: {{ $page->id }},
        apiUrl: '{{ $apiBaseUrl }}',
        csrfToken: '{{ csrf_token() }}',
        previewIframe: document.getElementById('preview-iframe'),
        pageStructureContainer: null, // No longer using page structure sidebar
        widgetEditorContainer: document.getElementById('widget-editor-container'),
        skipPageStructure: true // Skip loading page structure since we don't need it
    });

    // Check if all required classes are available
    if (typeof UpdateManager === 'undefined') {
        console.error('UpdateManager class not found');
        return;
    }
    if (typeof WidgetFormManager === 'undefined') {
        console.error('WidgetFormManager class not found');
        return;
    }
    if (typeof DevicePreview === 'undefined') {
        console.error('DevicePreview class not found');
        return;
    }
    if (typeof UnifiedLoaderManager === 'undefined') {
        console.error('UnifiedLoaderManager class not found');
        return;
    }
    if (typeof TemplateManager === 'undefined') {
        console.error('TemplateManager class not found');
        return;
    }
    if (typeof WidgetLibrary === 'undefined') {
        console.error('WidgetLibrary class not found');
        return;
    }
    if (typeof DefaultWidgetLibrary === 'undefined') {
        console.error('DefaultWidgetLibrary class not found');
        return;
    }

    // Initialize managers
    const updateManager = new UpdateManager('{{ $apiBaseUrl }}', '{{ csrf_token() }}');
    const widgetFormManager = new WidgetFormManager(livePreview, updateManager);
    // Initialize Device Preview
    const devicePreview = new DevicePreview();
    
    console.log('✅ Live Designer initialized');

    // Initialize left sidebar component library managers (simplified - no loading)
    const unifiedLoader = new UnifiedLoaderManager();
    const templateManager = new TemplateManager(null, livePreview, unifiedLoader);
    const widgetLibrary = new WidgetLibrary(livePreview, unifiedLoader);
    const defaultWidgetLibrary = new DefaultWidgetLibrary(livePreview, unifiedLoader);
    
    // Initialize left sidebar components (server-rendered, just setup interactions)
    await templateManager.init();
    await defaultWidgetLibrary.init();
    
    console.log('✅ Live Designer components initialized (widget drill-down handled separately)');
    
    // Device preview is now fully handled by device-preview.js

    // Global references for debugging
    window.livePreview = livePreview;
    window.updateManager = updateManager;
    window.widgetFormManager = widgetFormManager;
    window.unifiedLoader = unifiedLoader;
    window.templateManager = templateManager;
    window.widgetLibrary = widgetLibrary;
    window.defaultWidgetLibrary = defaultWidgetLibrary;


    // Mobile sidebar toggle (right sidebar only)
    const toggleRightSidebar = document.getElementById('toggle-right-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    // Show toggle on mobile screens
    if (window.innerWidth <= 991.98) {
        if (toggleRightSidebar) toggleRightSidebar.style.display = 'block';
    }

    if (toggleRightSidebar && rightSidebar && mobileOverlay) {
        toggleRightSidebar.addEventListener('click', function() {
            rightSidebar.classList.toggle('show');
            mobileOverlay.style.display = rightSidebar.classList.contains('show') ? 'block' : 'none';
        });
    }

    if (mobileOverlay && rightSidebar) {
        mobileOverlay.addEventListener('click', function() {
            rightSidebar.classList.remove('show');
            mobileOverlay.style.display = 'none';
        });
    }


    // Action buttons
    const previewPageBtn = document.getElementById('preview-page');
    if (previewPageBtn) {
        previewPageBtn.addEventListener('click', function() {
            const previewUrl = '{{ route("admin.pages.show", $page) }}';
            window.open(previewUrl, '_blank');
        });
    }

    const savePageBtn = document.getElementById('save-page');
    if (savePageBtn) {
        savePageBtn.addEventListener('click', function() {
            livePreview.showMessage('Changes are saved automatically!', 'success');
        });
    }

    

    console.log('✅ Live Designer initialized');
});

</script>
@endpush