@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder - ' . $page->title)

@section('css')
<!-- Page Builder Draggable CSS -->
<link href="{{ asset('assets/admin/css/page-builder/draggable.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid h-100 p-0">
    <!-- Toolbar -->
    <div class="row g-0">
        <div class="col-12 p-0">
            @include('admin.pages.page-builder.components.toolbar')
        </div>
    </div>
    
    <!-- Main Content - Three Column Layout -->
    <div class="row g-0 h-100">
        <!-- Left Sidebar -->
        <div class="col-auto" id="leftSidebarContainer">
            @include('admin.pages.page-builder.components.left-sidebar')
        </div>
        
        <!-- Canvas Container -->
        <div class="col" id="canvasContainer" style="padding:20px 0px !important">
            <!-- Unified Progress Bar Loader -->
            <div class="unified-page-loader" id="liveDesignerLoader" style="display: none;">
                <div class="progress-bar"></div>
                <div class="loader-message">Loading...</div>
            </div>
            
            <!-- Preview Iframe (Direct) -->
           
        </div>
        
    </div>
</div>

<!-- Include Section Templates Modal -->
@include('admin.pages.page-builder.modals.section-templates')

@endsection

@push('scripts')
<!-- Page Builder Widget Drill-Down -->
<script src="{{ asset('assets/admin/js/page-builder/widget-drill-down.js') }}?v={{ time() }}"></script>
<!-- Page Builder Section Drag Handler -->
<script src="{{ asset('assets/admin/js/page-builder/drag-handler.js') }}?v={{ time() }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Page Builder UI initialized with real data');
    console.log('📋 Available section templates:', @json($sectionTemplates ?? []));
    console.log('🎨 Available theme widgets:', @json($themeWidgets ?? []));
    console.log('🧩 Available default widgets:', @json($defaultWidgets ?? []));
});
</script>
@endpush