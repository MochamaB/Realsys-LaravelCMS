@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder: ' . $page->title)

@section('css')
<!-- GridStack Designer CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet" />

<!-- Multi-Step Widget Modal CSS -->
<link href="{{ asset('assets/admin/css/page-builder/widget-modal.css') }}" rel="stylesheet" />

<!-- Section Configuration Modal CSS -->
<link href="{{ asset('assets/admin/css/page-builder/section-config-modal.css') }}" rel="stylesheet" />

<!-- Consolidated Template & Component Styling -->
<style>
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
            <div class="col" id="canvasContainer">
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
                apiBaseUrl: '/admin/api/page-builder',
                csrfToken: window.csrfToken,
                containerId: 'gridStackContainer'
            });
            
            // Initialize the page builder
            window.pageBuilder.init().then(() => {
                console.log('✅ Page Builder initialized successfully');
                
                // Initialize Field Type Defaults Service
                window.fieldTypeDefaultsService = new FieldTypeDefaultsService(
                    '/admin/api/page-builder',
                    window.csrfToken
                );
                
                // Initialize Widget Modal Manager
                window.widgetModalManager = new WidgetModalManager(
                    '/admin/api/page-builder',
                    window.csrfToken
                );
                
                // Setup iframe message listener
                setupIframeMessageListener();
                                // Initialize sidebar toggle functionality
                                initSidebarToggle();

            }).catch(error => {
                console.error('❌ Page Builder initialization failed:', error);
            });
        } else {
            console.error('❌ PageBuilderMain not found - check JS file loading');
        }
    }, 100); // Small delay to ensure all scripts are loaded
});

// Legacy Widget Modal Handlers - Replaced by WidgetModalManager
// These functions are kept for backward compatibility but are no longer used
// Sidebar toggle functionality
function initSidebarToggle() {
    const sidebarContainer = document.getElementById('leftSidebarContainer');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.getElementById('leftSidebar');
    
    if (!toggleBtn || !sidebarContainer) return;
    
    // Set sidebar collapsed by default on first load
    const isCollapsed = localStorage.getItem('sidebarCollapsed');
    if (isCollapsed === null) {
        // First time load - set to collapsed
        sidebarContainer.classList.add('collapsed');
        toggleBtn.querySelector('i').classList.remove('ri-arrow-left-line');
        toggleBtn.querySelector('i').classList.add('ri-arrow-right-line');
        localStorage.setItem('sidebarCollapsed', 'true');
    } else if (isCollapsed === 'true') {
        sidebarContainer.classList.add('collapsed');
        toggleBtn.querySelector('i').classList.remove('ri-arrow-left-line');
        toggleBtn.querySelector('i').classList.add('ri-arrow-right-line');
    }
    
    toggleBtn.addEventListener('click', function() {
        sidebarContainer.classList.toggle('collapsed');
        
        // Update icon
        const icon = toggleBtn.querySelector('i');
        if (sidebarContainer.classList.contains('collapsed')) {
            icon.classList.remove('ri-arrow-left-line');
            icon.classList.add('ri-arrow-right-line');
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            icon.classList.remove('ri-arrow-right-line');
            icon.classList.add('ri-arrow-left-line');
            localStorage.setItem('sidebarCollapsed', 'false');
        }
        
        // Refresh device preview after sidebar transition
        setTimeout(() => {
            if (window.devicePreview && window.devicePreview.refresh) {
                window.devicePreview.refresh();
            }
        }, 300); // Wait for CSS transition to complete
    });
}


// Iframe Message Listener
function setupIframeMessageListener() {
    console.log('📡 Setting up iframe message listener...');
    
    window.addEventListener('message', function(event) {
        // Verify message is from our iframe
        const iframe = document.getElementById('pagePreviewIframe');
        if (!iframe || event.source !== iframe.contentWindow) {
            return;
        }
        
        console.log('📨 Received message from iframe:', event.data);
        
        const { type, data } = event.data;
        
        switch (type) {
            case 'section-selected':
                handleSectionSelected(data.sectionId, data.sectionName);
                break;
                
            case 'toolbar-action':
                handleToolbarAction(data);
                break;
                
            case 'widget-selected':
                console.log('🎯 Widget selected:', data);
                // Handle widget selection if needed
                break;
                
            default:
                console.log('ℹ️ Unknown message type:', type);
        }
    });
}

function handleSectionSelected(sectionId, sectionName) {
    console.log('📦 Section selected:', { sectionId, sectionName });
    
    // Store selected section info globally
    window.selectedSection = { id: sectionId, name: sectionName };
    
    // You can add additional section selection UI updates here
    console.log(`✅ Section "${sectionName}" (ID: ${sectionId}) is now selected`);
}

function handleToolbarAction(actionData) {
    console.log('🔧 Toolbar action received:', actionData);
    
    const { action, elementType, elementId, elementName } = actionData;
    
    switch (action) {
        case 'add-widget':
            if (elementType === 'section') {
                openWidgetModalForSection(elementId, elementName);
            }
            break;
            
        case 'edit':
            console.log(`✏️ Edit ${elementType}: ${elementName} (ID: ${elementId})`);
            // Handle edit actions
            break;
            
        case 'delete':
            console.log(`🗑️ Delete ${elementType}: ${elementName} (ID: ${elementId})`);
            // Handle delete actions
            break;
            
        default:
            console.warn('❓ Unknown toolbar action:', action);
    }
}

function openWidgetModalForSection(sectionId, sectionName) {
    console.log('🎯 Opening widget modal for section:', { sectionId, sectionName });
    
    // Use the new WidgetModalManager to open the modal
    if (window.widgetModalManager) {
        window.widgetModalManager.openForSection(sectionId, sectionName);
    } else {
        console.error('❌ WidgetModalManager not initialized');
        alert('Widget modal is not ready. Please refresh the page.');
    }
    
    console.log(`✅ Widget modal opened for section "${sectionName}"`);
}

function getSelectedSectionInfo() {
    // Return the currently selected section from iframe communication
    return window.selectedSection || null;
}

// Legacy loadAvailableWidgets - replaced by WidgetModalManager.loadWidgetLibrary()
// function loadAvailableWidgets() { ... }

// Legacy renderWidgetLibrary - replaced by WidgetModalManager.renderWidgetLibrary()
// function renderWidgetLibrary(widgets) { ... }

// Legacy setupWidgetSelectionHandlers - replaced by WidgetModalManager handlers
// function setupWidgetSelectionHandlers() { ... }

// Legacy showWidgetDetails - replaced by WidgetModalManager.updateWidgetPreview()
// function showWidgetDetails(widgetId, widgetItem) { ... }

// Legacy setupAddWidgetHandler - replaced by WidgetModalManager.handleFinalSubmission()
// function setupAddWidgetHandler() { ... }

</script>
@endpush