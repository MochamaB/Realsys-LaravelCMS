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
        <div class="row h-100">
        <!-- Left Sidebar -->
            <div class="col-lg-3 col-md-4 d-none d-lg-block p-0" id="leftSidebarContainer">
                @include('admin.pages.page-builder.components.left-sidebar')
            </div>

            <!-- Main Canvas Area -->
            <div class="col p-0" id="canvasContainer">
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
                
                // Setup widget modal handlers
                setupWidgetModalHandlers();
                
                // Setup iframe message listener
                setupIframeMessageListener();
            }).catch(error => {
                console.error('❌ Page Builder initialization failed:', error);
            });
        } else {
            console.error('❌ PageBuilderMain not found - check JS file loading');
        }
    }, 100); // Small delay to ensure all scripts are loaded
});

// Widget Modal Handlers
function setupWidgetModalHandlers() {
    console.log('🎯 Setting up widget modal handlers...');
    
    // Add Widget button handler (from section toolbar)
    const addWidgetBtn = document.getElementById('addWidgetToSectionBtn');
    const widgetModal = new bootstrap.Modal(document.getElementById('widgetContentModal'));
    
    if (addWidgetBtn) {
        addWidgetBtn.addEventListener('click', function() {
            console.log('🎯 Opening widget selection modal...');
            
            // Get selected section info (you'll need to implement getSelectedSection)
            const selectedSection = getSelectedSectionInfo();
            
            if (selectedSection) {
                // Populate modal with section info
                document.getElementById('targetSectionName').textContent = selectedSection.name;
                document.getElementById('targetSectionId').value = selectedSection.id;
                
                // Load available widgets
                loadAvailableWidgets();
                
                // Show modal
                widgetModal.show();
            } else {
                alert('Please select a section first');
            }
        });
    }
    
    // Modal widget selection handlers
    setupWidgetSelectionHandlers();
    
    // Add widget to section handler
    setupAddWidgetHandler();
}

function getSelectedSectionInfo() {
    // This should return info about the currently selected section
    // For now, return mock data - you can implement proper section selection later
    return {
        id: 1,
        name: 'Header Section'
    };
}

function loadAvailableWidgets() {
    console.log('📦 Loading available widgets...');
    
    const loadingEl = document.getElementById('widgetLibraryLoading');
    const itemsEl = document.getElementById('widgetLibraryItems');
    
    // Show loading state
    loadingEl.style.display = 'block';
    itemsEl.innerHTML = '';
    
    // Make API call to get widgets
    if (window.pageBuilder && window.pageBuilder.api) {
        window.pageBuilder.api.getAvailableWidgets()
            .then(response => {
                loadingEl.style.display = 'none';
                
                if (response.success && response.data.widgets) {
                    renderWidgetLibrary(response.data.widgets);
                } else {
                    itemsEl.innerHTML = '<div class="text-center p-3 text-muted">No widgets available</div>';
                }
            })
            .catch(error => {
                console.error('❌ Error loading widgets:', error);
                loadingEl.style.display = 'none';
                itemsEl.innerHTML = '<div class="text-center p-3 text-danger">Error loading widgets</div>';
            });
    }
}

function renderWidgetLibrary(widgets) {
    const itemsEl = document.getElementById('widgetLibraryItems');
    const template = document.getElementById('widgetItemTemplate');
    
    itemsEl.innerHTML = '';
    
    // Iterate through widget categories
    Object.keys(widgets).forEach(category => {
        // Add category header
        const categoryHeader = document.createElement('div');
        categoryHeader.className = 'widget-category-header mb-2';
        categoryHeader.innerHTML = `<h6 class="text-muted text-uppercase small">${category}</h6>`;
        itemsEl.appendChild(categoryHeader);
        
        // Add widgets in this category
        widgets[category].forEach(widget => {
            const widgetItem = template.content.cloneNode(true);
            const widgetDiv = widgetItem.querySelector('.widget-library-item');
            
            widgetDiv.dataset.widgetId = widget.id;
            widgetDiv.querySelector('.widget-icon').className = `widget-icon ${widget.icon || 'ri-puzzle-line'} me-3 fs-4 text-primary`;
            widgetDiv.querySelector('.widget-name').textContent = widget.name;
            widgetDiv.querySelector('.widget-description').textContent = widget.description || 'No description available';
            widgetDiv.querySelector('.widget-category').textContent = category;
            
            itemsEl.appendChild(widgetItem);
        });
    });
}

function setupWidgetSelectionHandlers() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.widget-library-item')) {
            const widgetItem = e.target.closest('.widget-library-item');
            const widgetId = widgetItem.dataset.widgetId;
            
            // Remove previous selection
            document.querySelectorAll('.widget-library-item').forEach(item => {
                item.classList.remove('border-primary', 'bg-light');
            });
            
            // Highlight selected widget
            widgetItem.classList.add('border-primary', 'bg-light');
            
            // Show widget details and enable add button
            showWidgetDetails(widgetId, widgetItem);
            document.getElementById('addWidgetToSectionBtn').disabled = false;
        }
    });
}

function showWidgetDetails(widgetId, widgetItem) {
    const noWidgetEl = document.getElementById('noWidgetSelected');
    const widgetInfoEl = document.getElementById('selectedWidgetInfo');
    
    // Hide no selection state
    noWidgetEl.style.display = 'none';
    widgetInfoEl.style.display = 'block';
    
    // Populate widget details
    const widgetName = widgetItem.querySelector('.widget-name').textContent;
    const widgetDescription = widgetItem.querySelector('.widget-description').textContent;
    const widgetCategory = widgetItem.querySelector('.widget-category').textContent;
    const widgetIcon = widgetItem.querySelector('.widget-icon').className;
    
    document.getElementById('selectedWidgetName').textContent = widgetName;
    document.getElementById('selectedWidgetDescription').textContent = widgetDescription;
    document.getElementById('selectedWidgetCategory').textContent = widgetCategory;
    document.getElementById('selectedWidgetIcon').className = widgetIcon;
    
    // Store selected widget ID for later use
    widgetInfoEl.dataset.selectedWidgetId = widgetId;
}

function setupAddWidgetHandler() {
    const addBtn = document.querySelector('#widgetContentModal #addWidgetToSectionBtn');
    
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const sectionId = document.getElementById('targetSectionId').value;
            const widgetId = document.getElementById('selectedWidgetInfo').dataset.selectedWidgetId;
            const gridWidth = document.getElementById('widgetGridWidth').value || 6;
            const gridHeight = document.getElementById('widgetGridHeight').value || 4;
            
            if (sectionId && widgetId) {
                console.log('🎯 Adding widget to section:', { sectionId, widgetId, gridWidth, gridHeight });
                
                // Disable button during API call
                addBtn.disabled = true;
                addBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Adding...';
                
                // Make API call to add widget to section
                fetch(`/admin/api/page-builder/sections/${sectionId}/add-widget`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        widget_id: widgetId,
                        grid_width: gridWidth,
                        grid_height: gridHeight
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Widget added successfully:', data);
                        
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('widgetContentModal')).hide();
                        
                        // Show success message
                        if (typeof toastr !== 'undefined') {
                            toastr.success(data.message || 'Widget added successfully');
                        } else {
                            alert(data.message || 'Widget added successfully');
                        }
                        
                        // Refresh iframe preview if needed
                        if (data.data && data.data.refresh_preview) {
                            const iframe = document.getElementById('pagePreviewIframe');
                            if (iframe) {
                                iframe.src = iframe.src; // Reload iframe
                            }
                        }
                    } else {
                        console.error('❌ Failed to add widget:', data);
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.message || 'Failed to add widget');
                        } else {
                            alert(data.message || 'Failed to add widget');
                        }
                    }
                })
                .catch(error => {
                    console.error('❌ API Error adding widget:', error);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Network error while adding widget');
                    } else {
                        alert('Network error while adding widget');
                    }
                })
                .finally(() => {
                    // Re-enable button
                    addBtn.disabled = false;
                    addBtn.innerHTML = '<i class="ri-add-line me-2"></i>Add Widget to Section';
                });
            } else {
                alert('Please select both a section and a widget');
            }
        });
    }
}

</script>
@endpush