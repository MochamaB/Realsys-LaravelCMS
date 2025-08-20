@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder: ' . $page->title)

@section('css')
<!-- GridStack Designer CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet" />
@endsection

@section('js')
<!-- GridStack Libraries -->
<script src="{{ asset('assets/admin/libs/gridstack/dist/gridstack-all.js') }}"></script>
<script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>

<!-- Page Builder JS Modules -->
<script src="{{ asset('assets/admin/js/page-builder/api/page-builder-api.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/grid-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/section-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/widget-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/widget-library.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/template-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/theme-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/page-builder-main.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<!-- Page Builder Content -->
<div class="container-fluid h-100 p-0">
    <!-- Toolbar -->
    <div class="row">
        <div class="col-12 p-0">
            @include('admin.pages.page-builder.components.toolbar')
        </div>
    </div>
    
    <!-- Main Page Builder Interface -->
    <div class="row flex-fill">
        <!-- Left Sidebar -->
        <div class="col-lg-3 col-md-4 d-none d-lg-block p-0" id="leftSidebarContainer">
            @include('admin.pages.page-builder.components.left-sidebar')
        </div>
        
        <!-- Main Canvas Area -->
        <div class="col flex-fill p-0" id="canvasContainer">
            @include('admin.pages.page-builder.components.canvas')
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.pages.page-builder.modals.section-config')
@include('admin.pages.page-builder.modals.widget-config')
@include('admin.pages.page-builder.modals.section-templates')
@include('admin.pages.page-builder.modals.responsive-preview')
@endsection

@push('scripts')
<script>
// GridStack Page Builder - API-Driven Architecture
window.pageId = {{ $page->id }};
window.csrfToken = '{{ csrf_token() }}';

console.log('🔧 GridStack Page Builder initializing for page:', window.pageId);

document.addEventListener('DOMContentLoaded', function() {
    // Add a small delay to ensure all scripts are loaded
    setTimeout(function() {
        console.log('🚀 Starting Page Builder initialization...');
        
        // Initialize Page Builder Main Controller
        if (window.PageBuilderMain) {
            window.pageBuilder = new PageBuilderMain({
                pageId: window.pageId,
                apiBaseUrl: '/admin/api',
                csrfToken: window.csrfToken,
                containerId: 'gridStackContainer'
            });
            
            // Initialize the page builder
            window.pageBuilder.init().then(() => {
                console.log('✅ Page Builder initialized successfully');
            }).catch(error => {
                console.error('❌ Page Builder initialization failed:', error);
            });
        } else {
            console.error('❌ PageBuilderMain not found - check JS file loading');
        }
    }, 100); // Small delay to ensure all scripts are loaded
});
</script>
@endpush