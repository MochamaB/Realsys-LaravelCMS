@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder: ' . $page->title)

@section('css')
<!-- GridStack Designer CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet" />

<!-- Multi-Step Widget Modal CSS -->
<link href="{{ asset('assets/admin/css/page-builder/widget-modal.css') }}" rel="stylesheet" />



<!-- Consolidated Template & Component Styling -->
<style>
/* ===== UNIFIED PROGRESS BAR LOADER STYLES ===== */
.unified-page-loader {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: #f8f9fa;
    z-index: 9999;
    overflow: hidden;
    transition: opacity 0.3s ease;
}

.unified-page-loader .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997, #17a2b8);
    background-size: 200% 100%;
    animation: loading 2s ease-in-out infinite;
    border-radius: 0;
    transition: width 0.3s ease;
    width: 0%;
}

.unified-page-loader.error .progress-bar {
    background: linear-gradient(90deg, #dc3545, #e74c3c);
    animation: error-pulse 1s ease-in-out infinite;
}

.unified-page-loader .progress-bar.complete {
    background: #28a745;
    animation: none;
}

.unified-page-loader .loader-message {
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    background: rgba(255, 255, 255, 0.9);
    padding: 2px 8px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

@keyframes error-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* ===== CONSOLIDATED TEMPLATE ITEM STYLES ===== */
/* Overrides any conflicting styles from gridstack-designer.css */

.template-item {
    position: relative !important;
    background: #fff !important;
    border: 1px solid #e3e6f0 !important;
    border-radius: 8px !important;
    padding: 12px !important;
    margin-bottom: 8px !important;
    cursor: grab !important;
    transition: all 0.2s ease !important;
    user-select: none !important;
    text-align: left !important; /* Override center alignment from CSS file */
}

.template-item:hover {
    border-color: #5a67d8 !important;
    box-shadow: 0 2px 8px rgba(90, 103, 216, 0.15) !important;
    transform: translateY(-1px) !important;
}

.template-item:active {
    cursor: grabbing !important;
    transform: translateY(0) !important;
}

.template-item.creating {
    opacity: 0.7 !important;
    pointer-events: none !important;
}

.template-item.dragging {
    opacity: 0.5 !important;
    transform: rotate(2deg) !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Template Icon Styling */
.template-icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 28px !important;
    height: 28px !important;
    background: #f8f9fa !important;
    border-radius: 6px !important;
    color: #5a67d8 !important;
    font-size: 14px !important;
    flex-shrink: 0 !important;
}

/* Core vs Theme Template Icon Colors */
.template-item[data-template-type="core"] .template-icon {
    background: #e6fffa !important;
    color: #319795 !important;
}

.template-item[data-template-type="theme"] .template-icon {
    background: #fef5e7 !important;
    color: #d69e2e !important;
}

/* Template Name Styling */
.template-name {
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #2d3748 !important;
    line-height: 1.3 !important;
    flex-grow: 1 !important;
}

/* Drag Handle Styling */
.drag-handle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 24px !important;
    height: 24px !important;
    color: #6c757d !important;
    font-size: 14px !important;
    opacity: 0 !important;
    transition: opacity 0.2s ease !important;
    cursor: grab !important;
    flex-shrink: 0 !important;
}

.template-item:hover .drag-handle {
    opacity: 1 !important;
}

.drag-handle:hover {
    color: #5a67d8 !important;
}

.drag-handle:active {
    cursor: grabbing !important;
}

/* Loading State */
.sectionsGrid .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}

/* Component Item Alias (for backward compatibility) */
.component-item {
    /* Inherit all template-item styles */
}
</style>
@endsection

@section('js')
<!-- GridStack Libraries -->
<script src="{{ asset('assets/admin/libs/gridstack/dist/gridstack-all.js') }}"></script>
<script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>

<!-- Unified Loader Manager -->
<script src="{{ asset('assets/admin/js/page-builder/unified-loader-manager.js') }}"></script>

<!-- Page Builder JS Modules -->
<script src="{{ asset('assets/admin/js/page-builder/api/page-builder-api.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/grid-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/section-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/widget-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/widget-library.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/template-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/theme-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/page-builder-main.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/device-preview.js') }}?v={{ time() }}"></script>

<!-- Field Type Defaults Service -->
<script src="{{ asset('assets/admin/js/page-builder/field-type-defaults-service.js') }}?v={{ time() }}"></script>

<!-- Multi-Step Widget Modal Manager -->
<script src="{{ asset('assets/admin/js/page-builder/widget-modal-manager.js') }}?v={{ time() }}"></script>

@endsection

@section('content')
<!-- Page Builder Content -->
<div class="container-fluid h-100 g-0">
    <!-- Toolbar -->
    <div class="row g-0">
        <div class="col-12 ">
            @include('admin.pages.page-builder.components.toolbar')
        </div>
    </div>
    
    <!-- Main Page Builder Interface -->
        <div class="row h-100 g-0">
        <!-- Left Sidebar -->
            <div class="col-lg-3 col-md-4 d-none d-lg-block" id="leftSidebarContainer">
                @include('admin.pages.page-builder.components.left-sidebar')
            </div>

            <!-- Main Canvas Area -->
            <div class="col" id="canvasContainer" style="position: relative;">
                <!-- Unified Progress Bar Loader -->
                <div class="unified-page-loader" id="pageBuilderLoader" style="display: none;">
                    <div class="progress-bar"></div>
                    <div class="loader-message">Loading...</div>
                </div>
                
                @include('admin.pages.page-builder.components.canvas')
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.pages.page-builder.modals.section-config')
@include('admin.pages.page-builder.modals.widget-config')
@include('admin.pages.page-builder.modals.widget-content')
@include('admin.pages.page-builder.modals.section-templates')
@include('admin.pages.page-builder.modals.responsive-preview')
@endsection

@push('scripts')
<script>
// Page Builder - Simplified initialization
window.pageId = {{ $page->id }};
window.csrfToken = '{{ csrf_token() }}';

// Initialize Page Builder with modal support
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Page Builder initializing for page:', window.pageId);
    
    // Initialize core systems
    if (typeof window.initializePageBuilder === 'function') {
        window.initializePageBuilder();
    } else {
        console.error('❌ Page builder initialization function not found');
    }
});
</script>
<script src="{{ asset('assets/admin/js/page-builder/blade-integration.js') }}"></script>
@endpush