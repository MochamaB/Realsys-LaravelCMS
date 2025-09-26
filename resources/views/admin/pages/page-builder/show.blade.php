@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder - ' . $page->title)

@section('css')
<!-- Page Builder CSS -->
<link href="{{ asset('assets/admin/css/page-builder/draggable.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/admin/css/page-builder/pagebuilder-preview-helper.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/admin/css/page-builder/device-preview.css') }}" rel="stylesheet" type="text/css" />
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
        <div class="col" id="canvasContainer" style="padding:20px 0px !important; position: relative;">
            <!-- Unified Progress Bar Loader -->
            <div class="unified-page-loader" id="pageBuilderLoader" style="display: none;">
                <div class="progress-bar"></div>
                <div class="loader-message">Loading Page Builder Preview...</div>
            </div>
            
            <!-- Page Builder Preview Iframe -->
            <iframe 
                id="pageBuilderPreviewIframe" 
                src="{{ route('admin.api.page-builder.pages.preview-iframe', $page->id) }}"
                style="width: 100%; min-height: 600px; border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
                frameborder="0"
                loading="eager"
                data-page-id="{{ $page->id }}"
                data-preview-type="pagebuilder">
            </iframe>
        </div>
        
    </div>
</div>

<!-- Include Section Templates Modal -->
@include('admin.pages.page-builder.modals.section-templates')

<!-- Include Add Widget Modal -->
@include('admin.pages.page-builder.modals.addwidget')


@endsection

@push('scripts')
<!-- Page Builder Communication System -->
<script src="{{ asset('assets/admin/js/page-builder/parent-communicator.js') }}?v={{ time() }}"></script>
<!-- Page Builder Main Script -->
<script src="{{ asset('assets/admin/js/page-builder/page-builder.js') }}?v={{ time() }}"></script>
<!-- Page Builder Device Preview -->
<script src="{{ asset('assets/admin/js/page-builder/device-preview.js') }}?v={{ time() }}"></script>
<!-- Page Builder Widget Drill-Down -->
<script src="{{ asset('assets/admin/js/page-builder/widget-drill-down.js') }}?v={{ time() }}"></script>
<!-- Page Builder Add Widget Wizard -->
<script src="{{ asset('assets/admin/js/page-builder/addwidget-wizard.js') }}?v={{ time() }}"></script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Page Builder UI initialized with real data');
    console.log('📋 Available section templates:', @json($sectionTemplates ?? []));
    console.log('🎨 Available theme widgets:', @json($themeWidgets ?? []));
    console.log('🧩 Available default widgets:', @json($defaultWidgets ?? []));
});
</script>
@endpush