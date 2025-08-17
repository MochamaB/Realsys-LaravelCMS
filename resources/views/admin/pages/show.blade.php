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
<!-- GrapesJS Component System -->
<script src="{{ asset('assets/admin/js/grapejs/components/section-components.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/grapejs/components/widget-components.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/grapejs/grapejs-designer.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent mb-0">
            <div class="d-flex align-items-center">
                <!-- Toggle sidebar button moved before tabs -->
                <button class="btn btn-outline-secondary me-2" id="toggleLeftSidebarBtn" title="Toggle Widget Library">
                    <i class="ri-apps-line"></i>
                </button>
                
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
            </div>
            
            <div class="page-title-middle">
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
            
            <div class="page-title-right">
                <button class="btn btn-outline-secondary" id="toggleRightSidebarBtn" title="Toggle Properties Panel">
                    <i class="ri-settings-line"></i>
                </button>
                <button class="btn btn-outline-secondary" id="fullPreviewBtn" title="Open Full Preview">
                    <i class="ri-external-link-line"></i>
                </button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Pages
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Sidebar Column -->
    <div class="col-lg-3 col-md-4 d-none d-lg-block ps-0" id="leftSidebarContainer">
            @include('admin.pages.designer._left_sidebar')
    </div>
    
    <!-- Main Content Column -->
    <div class="main-content-area">
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
            
            <!-- Code Tab - Temporarily Removed -->
            <div class="tab-pane fade" id="code" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generated Code</h5>
                        <p class="text-muted">Code generation temporarily disabled for debugging.</p>
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
</div>

@endsection

@push('scripts')
<script>
// Global variables for GridStack and API
window.pageId = {{ $page->id }};
window.csrfToken = '{{ csrf_token() }}';

console.log('🔧 Global variables set:', {
    pageId: window.pageId,
    csrfToken: window.csrfToken ? 'Set' : 'Missing'
});

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

    // Toggle left sidebar
    const toggleLeftSidebarBtn = document.getElementById('toggleLeftSidebarBtn');
    const leftSidebarContainer = document.getElementById('leftSidebarContainer');
    
    if (toggleLeftSidebarBtn && leftSidebarContainer) {
        toggleLeftSidebarBtn.addEventListener('click', function() {
            leftSidebarContainer.classList.toggle('collapsed');
            
            // Toggle button active state
            this.classList.toggle('active');
            
            // Store sidebar state in localStorage
            const isCollapsed = leftSidebarContainer.classList.contains('collapsed');
            localStorage.setItem('leftSidebarCollapsed', isCollapsed);
            
            // Trigger resize event for designers
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300);
        });
        
        // Restore sidebar state from localStorage
        const isCollapsed = localStorage.getItem('leftSidebarCollapsed') === 'true';
        if (isCollapsed) {
            leftSidebarContainer.classList.add('collapsed');
            toggleLeftSidebarBtn.classList.add('active');
        }
    }
    
    // Setup click handlers for collapsed icons
    function setupCollapsedIconHandlers() {
        const collapsedIcons = document.querySelectorAll('.collapsed-icon-item');
        
        collapsedIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                const target = icon.getAttribute('data-target');
                if (target) {
                    // Expand the sidebar first
                    leftSidebarContainer.classList.remove('collapsed');
                    
                    // Update button state
                    if (toggleLeftSidebarBtn) {
                        toggleLeftSidebarBtn.classList.remove('active');
                    }
                    
                    // Then expand the target section
                    setTimeout(() => {
                        const targetElement = document.querySelector(target);
                        const collapseInstance = new bootstrap.Collapse(targetElement, {
                            toggle: true
                        });
                    }, 100);
                }
            });
        });
    }

    // Initialize designers when their tabs become active
    document.addEventListener('shown.bs.tab', function (e) {
        const targetId = e.target.getAttribute('data-bs-target');
        
        if (targetId === '#layout') {
            console.log('🔄 Layout Designer tab activated');
            initializeGridStackDesigner();
            
            // Update the main sidebar behavior for GridStack
            updateSidebarForTab('layout');
            
            // Initialize GridStack-specific left sidebar content
            initializeGridStackSidebar();
            
        } else if (targetId === '#preview') {
            console.log('🔄 Live Preview tab activated');
            // GrapesJS will be initialized by its own script
            
            // Update the main sidebar behavior for GrapesJS
            updateSidebarForTab('preview');
            
            // Initialize GrapesJS-specific left sidebar content
            initializeGrapesJSSidebar();
        }
    });
    
    // Function to update sidebar behavior based on active tab
    function updateSidebarForTab(tabType) {
        // Use consistent layout system for both tabs
        // No special behavior needed - both tabs use the same flexbox layout
        console.log(`🔄 Tab ${tabType} activated - using consistent layout system`);
    }
    
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
                csrfToken: window.csrfToken,
                withThemeContext: true // Enable theme context for enhanced preview
            });
        } else {
            console.log('✅ GridStack Page Builder already initialized');
        }
    }
    
    // Initialize GridStack-specific left sidebar content
    function initializeGridStackSidebar() {
        console.log('🔧 Initializing GridStack left sidebar...');
        
        // Initialize GridStack sections using the existing SectionTemplatesManager
        loadGridStackSections();
        
        // Initialize GridStack theme widgets
        loadGridStackThemeWidgets();
        
        // Clear page layers for GridStack (not used)
        const pageLayersContainer = document.getElementById('pageLayersContainer');
        if (pageLayersContainer) {
            pageLayersContainer.innerHTML = '<p class="text-muted p-2">Page layers not available in Layout Designer</p>';
        }
        
        console.log('✅ GridStack left sidebar initialized');
    }
    
    // Load GridStack sections using existing templates
    function loadGridStackSections() {
        const sectionsGrid = document.getElementById('sectionsGrid');
        if (!sectionsGrid) return;
        
        // Use the same templates as the GridStack SectionTemplatesManager
        const gridStackSections = [
            {
                id: 'full-width',
                name: 'Full Width',
                type: 'full-width',
                icon: 'ri-layout-row-line',
                description: 'A full-width section for hero content'
            },
            {
                id: 'two-columns',
                name: 'Two Columns',
                type: 'two-columns',
                icon: 'ri-layout-2-line',
                description: 'A two-column layout section'
            },
            {
                id: 'three-columns',
                name: 'Three Columns',
                type: 'three-columns',
                icon: 'ri-layout-3-line',
                description: 'A three-column layout section'
            },
            {
                id: 'sidebar-left',
                name: 'Sidebar Left',
                type: 'sidebar-left',
                icon: 'ri-layout-left-2-line',
                description: 'Content with left sidebar'
            }
        ];
        
        sectionsGrid.innerHTML = '';
        
        gridStackSections.forEach(section => {
            const sectionItem = document.createElement('div');
            sectionItem.className = 'component-item';
            sectionItem.draggable = true;
            sectionItem.dataset.sectionType = section.type;
            sectionItem.dataset.sectionId = section.id;
            sectionItem.innerHTML = `
                <i class="${section.icon}"></i>
                <div class="label">${section.name}</div>
            `;
            
            // Add drag functionality for GridStack sections
            sectionItem.addEventListener('dragstart', (e) => {
                const sectionData = {
                    type: 'section',
                    sectionType: section.type,
                    sectionId: section.id,
                    sectionName: section.name
                };
                
                console.log('🔄 Dragging section from left sidebar:', sectionData);
                e.dataTransfer.setData('text/plain', JSON.stringify(sectionData));
                sectionItem.classList.add('dragging');
            });
            
            sectionItem.addEventListener('dragend', (e) => {
                sectionItem.classList.remove('dragging');
            });
            
            sectionsGrid.appendChild(sectionItem);
        });
        
        console.log(`✅ Loaded ${gridStackSections.length} GridStack sections`);
    }
    
    // Load GridStack theme widgets into the left sidebar
    async function loadGridStackThemeWidgets() {
        const themeWidgetsGrid = document.getElementById('themeWidgetsGrid');
        if (!themeWidgetsGrid) return;
        
        try {
            console.log('🔧 Loading GridStack theme widgets...');
            
            // Wait for WidgetLibrary to be available and initialized
            let widgets = [];
            
            if (window.WidgetLibrary) {
                // If WidgetLibrary exists but widgets not loaded, load them
                if (!window.WidgetLibrary.widgets || window.WidgetLibrary.widgets.length === 0) {
                    await window.WidgetLibrary.loadAvailableWidgets();
                }
                widgets = window.WidgetLibrary.widgets || [];
            }
            
            // If no widgets from WidgetLibrary, load directly from API
            if (widgets.length === 0) {
                const response = await fetch('/admin/api/widgets/available', {
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    widgets = data.widgets || data || [];
                } else {
                    // Fallback to mock widgets if API fails
                    widgets = [
                        { id: 1, name: 'Text Widget', slug: 'text-widget', category: 'content', label: 'Text Widget' },
                        { id: 2, name: 'Image Widget', slug: 'image-widget', category: 'media', label: 'Image Widget' },
                        { id: 3, name: 'Button Widget', slug: 'button-widget', category: 'content', label: 'Button Widget' },
                        { id: 4, name: 'Counter Widget', slug: 'counter-widget', category: 'content', label: 'Counter Widget' },
                        { id: 5, name: 'Gallery Widget', slug: 'gallery-widget', category: 'media', label: 'Gallery Widget' },
                        { id: 6, name: 'Contact Form', slug: 'contact-form', category: 'form', label: 'Contact Form' }
                    ];
                }
            }
            
            renderGridStackWidgets(widgets, themeWidgetsGrid);
            
        } catch (error) {
            console.error('❌ Error loading GridStack theme widgets:', error);
            
            // Final fallback to ensure we always have widgets
            const fallbackWidgets = [
                { id: 1, name: 'Text Widget', slug: 'text-widget', category: 'content', label: 'Text Widget' },
                { id: 2, name: 'Image Widget', slug: 'image-widget', category: 'media', label: 'Image Widget' },
                { id: 3, name: 'Button Widget', slug: 'button-widget', category: 'content', label: 'Button Widget' }
            ];
            renderGridStackWidgets(fallbackWidgets, themeWidgetsGrid);
        }
    }
    
    // Render GridStack widgets in the left sidebar
    function renderGridStackWidgets(widgets, container) {
        container.innerHTML = '';
        
        widgets.forEach(widget => {
            const widgetItem = document.createElement('div');
            widgetItem.className = 'component-item';
            widgetItem.draggable = true;
            widgetItem.dataset.widgetType = widget.slug;
            widgetItem.dataset.widgetId = widget.id;
            
            // Use label, name, or fallback
            const displayName = widget.label || widget.name || 'Unknown Widget';
            
            widgetItem.innerHTML = `
                ${getGridStackWidgetIcon(widget.slug)}
                <div class="label">${displayName}</div>
            `;
            
            // Add drag and drop functionality matching the original WidgetLibrary system
            widgetItem.addEventListener('dragstart', (e) => {
                const widgetData = {
                    id: widget.id,
                    name: displayName,
                    slug: widget.slug,
                    category: widget.category || 'General',
                    label: displayName,
                    type: widget.slug
                };
                
                console.log('🔄 Dragging widget from left sidebar:', widgetData);
                e.dataTransfer.setData('text/plain', JSON.stringify(widgetData));
                widgetItem.classList.add('dragging');
            });
            
            widgetItem.addEventListener('dragend', (e) => {
                widgetItem.classList.remove('dragging');
            });
            
            container.appendChild(widgetItem);
        });
        
        console.log(`✅ Rendered ${widgets.length} GridStack theme widgets`);
    }
    
    // Get widget icon for GridStack widgets
    function getGridStackWidgetIcon(widgetType) {
        const iconMap = {
            'text-widget': '<i class="ri-text"></i>',
            'image-widget': '<i class="ri-image-line"></i>',
            'button-widget': '<i class="ri-cursor-line"></i>',
            'counter-widget': '<i class="ri-calculator-line"></i>',
            'gallery-widget': '<i class="ri-gallery-line"></i>',
            'contact-form': '<i class="ri-mail-line"></i>',
            'newsletter': '<i class="ri-newsletter-line"></i>',
            'spacer': '<i class="ri-space"></i>',
            // Add more widget type mappings
            'text': '<i class="ri-text"></i>',
            'image': '<i class="ri-image-line"></i>',
            'button': '<i class="ri-cursor-line"></i>',
            'counter': '<i class="ri-calculator-line"></i>',
            'gallery': '<i class="ri-gallery-line"></i>',
            'form': '<i class="ri-mail-line"></i>',
            'video': '<i class="ri-video-line"></i>',
            'map': '<i class="ri-map-pin-line"></i>'
        };
        
        return iconMap[widgetType] || '<i class="ri-puzzle-line"></i>';
    }
    
    // Initialize GrapesJS-specific left sidebar content
    function initializeGrapesJSSidebar() {
        console.log('🔧 Initializing GrapesJS left sidebar...');
        
        // Initialize GrapesJS sections and widgets when GrapesJS is ready
        setTimeout(() => {
            if (window.GrapesJSDesigner && window.GrapesJSDesigner.initializeSectionsGrid) {
                window.GrapesJSDesigner.initializeSectionsGrid();
            }
            
            if (window.GrapesJSDesigner && window.GrapesJSDesigner.loadThemeWidgets) {
                window.GrapesJSDesigner.loadThemeWidgets();
            }
            
            if (window.GrapesJSDesigner && window.GrapesJSDesigner.initializeLayersManager) {
                window.GrapesJSDesigner.initializeLayersManager();
            }
            
            console.log('✅ GrapesJS left sidebar initialized');
        }, 500);
    }
    
    // Check if layout tab is currently active and initialize immediately
    const activeTab = document.querySelector('#pageTab .nav-link.active');
    if (activeTab && activeTab.getAttribute('data-bs-target') === '#layout') {
        console.log('🔄 Layout tab is active, initializing GridStack immediately...');
        // Small delay to ensure all scripts are loaded
        setTimeout(() => {
            initializeGridStackDesigner();
            initializeGridStackSidebar();
        }, 100);
    }
    
    // Initialize collapsed icon handlers
    setupCollapsedIconHandlers();
    
    // Clean up any existing grapejs-mode styles on page load
    const existingGrapejsStyle = document.getElementById('grapejs-sidebar-mode');
    if (existingGrapejsStyle) {
        existingGrapejsStyle.remove();
    }
    
    // Remove grapejs-mode class from app-menu if it exists
    const appMenu = document.querySelector('.app-menu');
    if (appMenu && appMenu.classList.contains('grapejs-mode')) {
        appMenu.classList.remove('grapejs-mode');
    }
    
    // Code Tab functionality temporarily removed for debugging
    console.log('🔧 Code Tab functionality disabled for debugging');
});
</script>
@endpush

@push('styles')
<style>
    /* Sidebar toggle button styling */
    #toggleLeftSidebarBtn.active {
        background-color: #405189 !important;
        color: #fff !important;
        border-color: #405189 !important;
    }
    
    .sidebar-collapsed-icons {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
    }

    /* Section Layout Styling */
    .page-section {
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background: #fff;
        position: relative;
    }

    .section-content-wrapper {
        padding: 15px;
        width: 100%;
    }

    /* Section Grid Stack Container */
    .section-grid-stack {
        width: 100%;
        min-height: 100px;
        position: relative;
    }

    /* Full Width Section */
    .page-section[data-section-type="full-width"] .section-grid-stack {
        width: 100%;
    }

    .page-section[data-section-type="full-width"] .grid-stack-item {
        width: 100% !important;
    }

    /* Multi Column Section */
    .page-section[data-section-type="multi-column"] .section-grid-stack {
        width: 100%;
    }

    .page-section[data-section-type="multi-column"] .col-md-4 .section-grid-stack {
        width: 100%;
    }

    /* Sidebar Left Section */
    .page-section[data-section-type="sidebar-left"] .col-md-3 .section-grid-stack,
    .page-section[data-section-type="sidebar-left"] .col-md-9 .section-grid-stack {
        width: 100%;
    }

    /* Widget Drop Zone */
    .widget-drop-zone {
        width: 100%;
        min-height: 80px;
        border: 2px dashed #ccc;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .widget-drop-zone:hover {
        border-color: #007bff;
        background: #e7f3ff;
    }

    .drop-zone-content {
        text-align: center;
        color: #6c757d;
        font-size: 14px;
    }

    .drop-zone-content i {
        font-size: 24px;
        margin-bottom: 5px;
        display: block;
    }

    /* GridStack Items in Sections */
    .section-grid-stack .grid-stack-item {
        width: 100% !important;
    }

    .section-grid-stack .grid-stack-item .grid-stack-item-content {
        width: 100%;
        height: 100%;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        overflow: hidden;
    }

    /* Widget Content Styling */
    .widget-content {
        width: 100%;
        height: 100%;
    }

    .widget-content img {
        width: 100%;
        height: auto;
        max-width: 100%;
    }

    .widget-content .widget-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .widget-content .widget-text {
        font-size: 14px;
        line-height: 1.5;
    }

    /* Section Actions */
    .section-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .page-section:hover .section-actions {
        opacity: 1;
    }

    .section-action-btn {
        margin-left: 5px;
        padding: 4px 8px;
        font-size: 12px;
    }
</style>
@endpush