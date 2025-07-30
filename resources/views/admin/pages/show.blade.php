@extends('admin.layouts.designer-layout')

@section('page-title', 'Page Designer: ' . $page->title)

@section('css')
<!-- GridStack Designer CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet" />

<!-- GrapesJS Designer CSS -->
<link href="{{ asset('assets/admin/libs/grapesjs/dist/css/grapes.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/page-designer.css') }}" rel="stylesheet" />
@endsection

@section('js')
<script>
    // Make CSRF token available to JavaScript
    window.csrfToken = '{{ csrf_token() }}';
    window.pageId = {{ $page->id }};
</script>

<!-- GridStack Libraries -->
<script src="{{ asset('assets/admin/libs/gridstack/dist/gridstack-all.js') }}"></script>
<script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>

<!-- GridStack Designer JS -->
<script src="{{ asset('assets/admin/js/gridstack/gridstack-page-builder.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/widget-library.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/widget-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/section-templates.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/gridstack/theme-integration.js') }}?v={{ time() }}"></script>

<!-- GrapesJS Libraries (Minimal) -->
<script src="{{ asset('assets/admin/libs/grapesjs/dist/grapes.min.js') }}"></script>

<!-- GrapesJS Designer JS (Minimal) -->
<script src="{{ asset('assets/admin/js/grapejs/page-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/grapejs/grapejs-designer.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                            <ul class="nav nav-tabs-custom" id="pageTab" role="tablist" style="border-bottom: 1px solid #dee2e6;">
                                <li class="nav-item">
                                    <button class="nav-link active" id="layout-tab" data-bs-toggle="tab" 
                                        data-bs-target="#layout" type="button" role="tab">
                                        <i class="bx bx-layout"></i> Layout Designer
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="preview-tab" data-bs-toggle="tab" 
                                        data-bs-target="#preview" type="button" role="tab">
                                        <i class="bx bx-edit"></i> Live Preview
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" 
                                        data-bs-target="#permissions" type="button" role="tab">
                                        <i class="bx bx-list-ul"></i> Permissions
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="code-tab" data-bs-toggle="tab" 
                                        data-bs-target="#code" type="button" role="tab">
                                        <i class="bx bx-code"></i> Code
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="json-tab" data-bs-toggle="tab" 
                                        data-bs-target="#json" type="button" role="tab">
                                        <i class="bx bx-code"></i> JSON
                                    </button>
                                </li>
                            </ul>

                                <div class="page-title-right">
                                    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back"></i> Back to Pages
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
<div class="row">

   
    <div class="tab-content" id="pageTabContent">
        <!-- Layout Designer Tab (GridStack) -->
        <div class="tab-pane fade show active" id="layout" role="tabpanel">
            @include('admin.pages.gridstack-designer')
        </div>
        
        <!-- Live Preview Tab (GrapesJS) -->
        <div class="tab-pane fade" id="preview" role="tabpanel">
            @include('admin.pages.designer')
        </div>
        
        <!-- Permissions Tab -->
        <div class="tab-pane fade" id="permissions" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Page Permissions</h5>
                    <p class="text-muted">Permissions management coming soon...</p>
                </div>
            </div>
        </div>
        
        <!-- Code Tab -->
        <div class="tab-pane fade" id="code" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Page Code</h5>
                    <p class="text-muted">Code view coming soon...</p>
                </div>
            </div>
        </div>
        
        <!-- JSON Tab -->
        <div class="tab-pane fade" id="json" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Page JSON</h5>
                    <p class="text-muted">JSON view coming soon...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabKey = 'activePageTab';
    
    // Initialize tab switching with proper event handling
    const tabButtons = document.querySelectorAll('#pageTab button[data-bs-toggle="tab"]');
    
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                localStorage.setItem(tabKey, target);
                
                // Trigger resize events for the active designer
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            }
        });
    });

    // Restore active tab from localStorage
    const savedTab = localStorage.getItem(tabKey);
    if (savedTab) {
        const trigger = document.querySelector(`[data-bs-target="${savedTab}"]`);
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }

    // Initialize designers when their tabs become active
    document.addEventListener('shown.bs.tab', function (e) {
        const targetId = e.target.getAttribute('data-bs-target');
        
        if (targetId === '#layout') {
            console.log('🔄 Layout Designer tab activated');
            initializeGridStackDesigner();
        } else if (targetId === '#preview') {
            console.log('🔄 Live Preview tab activated');
            // GrapesJS will be initialized by its own script
        }
    });
    
    // Initialize layout tab by default if no saved tab
    if (!savedTab) {
        console.log('🔄 Initializing default layout tab...');
        const layoutTab = document.querySelector('[data-bs-target="#layout"]');
        if (layoutTab) {
            new bootstrap.Tab(layoutTab).show();
        }
    }
    
    // Initialize GridStack immediately if layout tab is active (default or restored)
    function initializeGridStackDesigner() {
        console.log('🔧 Initializing GridStack Page Builder...');
        if (window.GridStackPageBuilder && !window.GridStackPageBuilder.initialized) {
            window.GridStackPageBuilder.init({
                pageId: window.pageId,
                apiBaseUrl: '/admin/api',
                csrfToken: window.csrfToken
            });
        } else {
            console.log('✅ GridStack Page Builder already initialized');
        }
    }
    
    // Check if layout tab is currently active and initialize immediately
    const activeTab = document.querySelector('#pageTab .nav-link.active');
    if (activeTab && activeTab.getAttribute('data-bs-target') === '#layout') {
        console.log('🔄 Layout tab is active, initializing GridStack immediately...');
        // Small delay to ensure all scripts are loaded
        setTimeout(() => {
            initializeGridStackDesigner();
        }, 100);
    }
});
</script>
@endpush
