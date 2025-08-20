@extends('admin.layouts.designer-layout')

@section('title', 'Live Designer - ' . $page->title)

@section('css')
<!-- GrapesJS Core CSS -->
<link rel="stylesheet" href="{{ asset('assets/admin/libs/grapesjs/dist/css/grapes.min.css') }}">

<!-- Live Designer Custom CSS -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/live-designer.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/canvas-styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/sidebar-layout.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/enhanced-widgets.css') }}">

<style>
/* Live Designer Layout - Full Height */
.live-designer-container {
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.live-designer-header {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    flex-shrink: 0;
}

.live-designer-content {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.live-designer-left-sidebar {
    width: 280px;
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    flex-shrink: 0;
    overflow-y: auto;
}

.live-designer-canvas {
    flex: 1;
    background: #fff;
    position: relative;
    overflow: hidden;
}

.live-designer-right-sidebar {
    width: 300px;
    background: #f8f9fa;
    border-left: 1px solid #e9ecef;
    flex-shrink: 0;
    overflow-y: auto;
}

/* GrapesJS Editor Container */
#gjs-editor {
    height: 100%;
    width: 100%;
}

/* Mobile Responsive */
@media (max-width: 991.98px) {
    .live-designer-left-sidebar,
    .live-designer-right-sidebar {
        position: fixed;
        top: 0;
        height: 100vh;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .live-designer-left-sidebar.show,
    .live-designer-right-sidebar.show {
        transform: translateX(0);
    }
    
    .live-designer-right-sidebar {
        right: 0;
        transform: translateX(100%);
    }
    
    .live-designer-canvas {
        width: 100%;
    }
}

/* Loading State */
.live-designer-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 1000;
}

.live-designer-loading.hidden {
    display: none;
}
</style>
@endsection

@section('content')

<div class="live-designer-container">
    <!-- Header Toolbar -->
    <div class="row">
        <div class="col-12 p-0">
            @include('admin.pages.live-designer.components.toolbar')
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="live-designer-content">
        <!-- Left Sidebar - Component Library -->
        <div class="live-designer-left-sidebar" id="left-sidebar">
            @include('admin.pages.live-designer.components.left-sidebar')
        </div>
        
        <!-- Canvas Area -->
        @include('admin.pages.live-designer.components.canvas')
        
        <!-- Right Sidebar - Properties Panel -->
        <div class="live-designer-right-sidebar" id="right-sidebar">
            @include('admin.pages.live-designer.components.right-sidebar')
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="modal-backdrop fade" id="mobile-overlay" style="display: none;"></div>

<!-- Modals -->
@include('admin.pages.live-designer.modals.content-selection')
@include('admin.pages.live-designer.modals.asset-manager')
@include('admin.pages.live-designer.modals.responsive-preview')
@endsection

@section('js')
<!-- GrapesJS Core -->
<script src="{{ asset('assets/admin/libs/grapesjs/dist/grapes.min.js') }}"></script>

<!-- Live Designer JavaScript -->
<script src="{{ asset('assets/admin/js/live-designer/api/live-designer-api.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-initializer.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/component-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/canvas-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/sidebar-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/enhanced-widgets.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/live-designer-main.js') }}?v={{ time() }}"></script>
@endsection

@push('scripts')
<script>
// Initialize Live Designer
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Live Designer with page data
    const liveDesigner = new LiveDesignerMain({
        pageId: {{ $page->id }},
        apiBaseUrl: '{{ $apiBaseUrl }}',
        csrfToken: '{{ csrf_token() }}',
        pageData: @json($page),
        canvasSelector: '#gjs-editor',
        loadingSelector: '#canvas-loading'
    });
    
    // Mobile sidebar toggles
    const toggleLeftSidebar = document.getElementById('toggle-left-sidebar');
    const toggleRightSidebar = document.getElementById('toggle-right-sidebar');
    const leftSidebar = document.getElementById('left-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (toggleLeftSidebar && leftSidebar && mobileOverlay) {
        toggleLeftSidebar.addEventListener('click', function() {
            leftSidebar.classList.toggle('show');
            mobileOverlay.style.display = leftSidebar.classList.contains('show') ? 'block' : 'none';
        });
    }
    
    if (toggleRightSidebar && rightSidebar && mobileOverlay) {
        toggleRightSidebar.addEventListener('click', function() {
            rightSidebar.classList.toggle('show');
            mobileOverlay.style.display = rightSidebar.classList.contains('show') ? 'block' : 'none';
        });
    }
    
    if (mobileOverlay && leftSidebar && rightSidebar) {
        mobileOverlay.addEventListener('click', function() {
            leftSidebar.classList.remove('show');
            rightSidebar.classList.remove('show');
            mobileOverlay.style.display = 'none';
        });
    }
    
    // Preview mode toggles
    const previewModes = document.querySelectorAll('input[name="preview-mode"]');
    previewModes.forEach(mode => {
        mode.addEventListener('change', function() {
            if (this.checked) {
                liveDesigner.setPreviewMode(this.id.replace('-mode', ''));
            }
        });
    });
    
    // Action buttons
    const previewButton = document.getElementById('preview-page');
    const saveButton = document.getElementById('save-page');
    
    if (previewButton) {
        previewButton.addEventListener('click', function() {
            liveDesigner.previewPage();
        });
    }
    
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            liveDesigner.savePage();
        });
    }
    
    // Global reference for debugging
    window.liveDesigner = liveDesigner;
});
</script>
@endpush
