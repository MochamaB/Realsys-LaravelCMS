@extends('admin.layouts.master')

@section('page-title', 'Page Designer: ' . $page->title)

@section('content')

<div class="container-fluid">
    <!-- Designer Toolbar -->
    @include('admin.pages.designer._toolbar')

    <!-- Designer Layout -->
    <div class="row" id="designerLayout">
        <!-- Left Sidebar (Collapsible) -->
        <div class="col-lg-3 col-md-4 d-none d-lg-block" id="leftSidebarContainer">
            @include('admin.pages.designer._left_sidebar')
        </div>
        
        <!-- Canvas Area (Full Width) -->
        <div class="col-lg-9 col-md-8" id="canvasContainer">
            @include('admin.pages.designer._canvas_area')
        </div>
    </div>
</div>

<!-- Right Sidebar (Offcanvas) -->
@include('admin.pages.designer._right_sidebar')

<!-- Modals -->
@include('admin.pages.designer._widget_config_modal')
@include('admin.pages.designer._section_templates_modal')
@include('admin.pages.designer._content_selection_modal')
@include('admin.pages.designer._responsive_preview_modal')
@include('admin.pages.designer._delete_confirmation_modal')

@endsection

@push('styles')
<!-- GridStack CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet">
<!-- Custom Designer CSS -->
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet">
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet">
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
<script src="{{ asset('assets/admin/js/gridstack/section-templates.js') }}"></script>

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
        window.ThemeIntegration.init(),
        window.SectionTemplatesManager.init()
    ]).then(() => {
        console.log('✅ GridStack Page Designer initialized successfully');
        
        // Initialize sidebar controls
        initializeSidebarControls();
    }).catch(error => {
        console.error('❌ Error initializing page designer:', error);
    });
    
    // Initialize sidebar toggle controls
    function initializeSidebarControls() {
        const toggleLeftSidebarBtn = document.getElementById('toggleLeftSidebarBtn');
        const toggleRightSidebarBtn = document.getElementById('toggleRightSidebarBtn');
        const fullPreviewBtn = document.getElementById('fullPreviewBtn');
        const leftSidebarContainer = document.getElementById('leftSidebarContainer');
        const canvasContainer = document.getElementById('canvasContainer');
        const designerLayout = document.getElementById('designerLayout');
        
        // Toggle left sidebar
        if (toggleLeftSidebarBtn) {
            toggleLeftSidebarBtn.addEventListener('click', function() {
                leftSidebarContainer.classList.toggle('collapsed');
                canvasContainer.classList.toggle('expanded');
                toggleLeftSidebarBtn.classList.toggle('active');
                
                // Trigger window resize to update GridStack
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            });
        }
        
        // Toggle right sidebar
        if (toggleRightSidebarBtn) {
            toggleRightSidebarBtn.addEventListener('click', function() {
                const rightSidebar = document.getElementById('rightSidebar');
                if (rightSidebar) {
                    const offcanvas = new bootstrap.Offcanvas(rightSidebar);
                    offcanvas.show();
                }
            });
        }
        
        // Full preview
        if (fullPreviewBtn) {
            fullPreviewBtn.addEventListener('click', function() {
                const url = `/admin/pages/${config.pageId}/preview`;
                window.open(url, '_blank');
            });
        }
    }
    
    // Handle delete confirmation modal
    document.addEventListener('DOMContentLoaded', function() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteSection');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (window.GridStackPageBuilder && window.GridStackPageBuilder.confirmDeleteSection) {
                    window.GridStackPageBuilder.confirmDeleteSection();
                }
            });
        }
    });
});
</script>
@endpush 