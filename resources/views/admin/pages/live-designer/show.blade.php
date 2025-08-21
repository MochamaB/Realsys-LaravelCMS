@extends('admin.layouts.designer-layout')

@section('title', 'Live Designer - ' . $page->title)

@section('css')
<!-- Simplified Live Designer CSS -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/simple-live-designer.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/sidebar-layout.css') }}">

<style>
/* Simplified Live Designer Layout */
.simple-live-designer {
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.designer-toolbar {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    flex-shrink: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.device-controls {
    display: flex;
    gap: 0.5rem;
}

.device-btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.device-btn:hover {
    background: #f8f9fa;
}

.device-btn.active {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}

.designer-actions {
    display: flex;
    gap: 0.5rem;
}

.designer-content {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.designer-sidebar {
    width: 300px;
    background: #f8f9fa;
    flex-shrink: 0;
    overflow-y: auto;
    border-right: 1px solid #e9ecef;
}

.designer-sidebar.right {
    border-right: none;
    border-left: 1px solid #e9ecef;
}

.designer-preview {
    flex: 1;
    background: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px;
    overflow: auto;
    position: relative;
}

.preview-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.preview-container.device-desktop {
    width: 100%;
    max-width: none;
}

.preview-container.device-tablet {
    width: 768px;
    max-width: 768px;
}

.preview-container.device-mobile {
    width: 375px;
    max-width: 375px;
}

#preview-iframe {
    width: 100%;
    border: none;
    background: #fff;
    min-height: 600px;
    height: 100%;
}

/* Loading state */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-content {
    text-align: center;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile responsive */
@media (max-width: 991.98px) {
    .designer-sidebar {
        position: fixed;
        top: 0;
        height: 100vh;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .designer-sidebar.show {
        transform: translateX(0);
    }
    
    .designer-sidebar.right {
        right: 0;
        transform: translateX(100%);
    }
    
    .designer-sidebar.right.show {
        transform: translateX(0);
    }
    
    .preview-container.device-tablet,
    .preview-container.device-mobile {
        width: 100%;
        max-width: 100%;
    }
}

.hidden {
    display: none !important;
}
</style>
@endsection

@section('content')
<div class="simple-live-designer">
    <!-- Toolbar -->
    @include('admin.pages.live-designer.components.toolbar')
    
    <!-- Main Content -->
    <div class="designer-content">
        <!-- Left Sidebar -->
        @include('admin.pages.live-designer.components.left-sidebar')
        
        <!-- Preview Area -->
        <div class="designer-preview">
            <div class="preview-container device-desktop" id="preview-container">
                <iframe id="preview-iframe" src="{{ route('admin.api.live-preview.preview-iframe', $page) }}"></iframe>
            </div>
            
            <!-- Loading overlay -->
            <div class="loading-overlay hidden" id="preview-loading">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <p>Updating preview...</p>
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar -->
        @include('admin.pages.live-designer.components.right-sidebar')
    </div>
</div>

<!-- Mobile Overlay -->
<div class="modal-backdrop fade" id="mobile-overlay" style="display: none;"></div>

<!-- Widget Library Modal -->
<div class="modal fade" id="widget-library-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="widget-library-content">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                        <p>Loading widgets...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1060;"></div>
@endsection

@section('js')
<!-- Live Designer JavaScript -->
<script src="{{ asset('assets/admin/js/live-designer/live-preview.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-form-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/device-preview.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/update-manager.js') }}?v={{ time() }}"></script>
@endsection

@push('scripts')
<script>
// Initialize Simplified Live Designer
document.addEventListener('DOMContentLoaded', async function() {
    // Initialize the live preview system
    const livePreview = new LivePreview({
        pageId: {{ $page->id }},
        apiUrl: '{{ $apiBaseUrl }}',
        csrfToken: '{{ csrf_token() }}',
        previewIframe: document.getElementById('preview-iframe'),
        pageStructureContainer: document.getElementById('page-structure-container'),
        widgetEditorContainer: document.getElementById('widget-editor-container')
    });

    // Check if all required classes are available
    if (typeof UpdateManager === 'undefined') {
        console.error('UpdateManager class not found');
        return;
    }
    if (typeof WidgetFormManager === 'undefined') {
        console.error('WidgetFormManager class not found');
        return;
    }
    if (typeof DevicePreview === 'undefined') {
        console.error('DevicePreview class not found');
        return;
    }

    // Initialize managers
    const updateManager = new UpdateManager('{{ $apiBaseUrl }}', '{{ csrf_token() }}');
    const widgetFormManager = new WidgetFormManager(livePreview, updateManager);
    const devicePreview = new DevicePreview(document.getElementById('preview-container'));

    // Global references for debugging
    window.livePreview = livePreview;
    window.updateManager = updateManager;
    window.widgetFormManager = widgetFormManager;
    window.devicePreview = devicePreview;

    // Mobile sidebar toggles
    const toggleLeftSidebar = document.getElementById('toggle-left-sidebar');
    const toggleRightSidebar = document.getElementById('toggle-right-sidebar');
    const leftSidebar = document.getElementById('left-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    // Check if elements exist before adding event listeners
    if (!toggleLeftSidebar || !toggleRightSidebar || !leftSidebar || !rightSidebar || !mobileOverlay) {
        console.error('Missing required DOM elements for mobile sidebar toggles');
    }
    
    if (window.innerWidth <= 991.98) {
        if (toggleLeftSidebar) toggleLeftSidebar.style.display = 'block';
        if (toggleRightSidebar) toggleRightSidebar.style.display = 'block';
    }

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

    // Device preview controls
    document.querySelectorAll('.device-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const device = this.dataset.device;
            devicePreview.setDevice(device);
            
            // Update active button
            document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Action buttons
    const previewPageBtn = document.getElementById('preview-page');
    if (previewPageBtn) {
        previewPageBtn.addEventListener('click', function() {
            const previewUrl = '{{ route("admin.pages.show", $page) }}';
            window.open(previewUrl, '_blank');
        });
    }

    const savePageBtn = document.getElementById('save-page');
    if (savePageBtn) {
        savePageBtn.addEventListener('click', function() {
            livePreview.showMessage('Changes are saved automatically!', 'success');
        });
    }

    // Sidebar collapse functionality
    const collapseLeftBtn = document.getElementById('collapse-left-sidebar');
    const collapseRightBtn = document.getElementById('collapse-right-sidebar');
    const leftSidebarContainer = document.getElementById('left-sidebar-container');
    const rightSidebarContainer = document.getElementById('right-sidebar-container');
    
    if (collapseLeftBtn && leftSidebarContainer) {
        collapseLeftBtn.addEventListener('click', function() {
            leftSidebarContainer.classList.toggle('collapsed');
        });
    }
    
    if (collapseRightBtn && rightSidebarContainer) {
        collapseRightBtn.addEventListener('click', function() {
            rightSidebarContainer.classList.toggle('collapsed');
        });
    }
    
    // Dynamic iframe height
    function adjustIframeHeight() {
        const iframe = document.getElementById('preview-iframe');
        if (iframe) {
            iframe.addEventListener('load', function() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    const height = iframeDoc.body.scrollHeight;
                    iframe.style.height = Math.max(height, 600) + 'px';
                } catch (e) {
                    // Cross-origin restrictions - use default height
                    iframe.style.height = '800px';
                }
            });
        }
    }
    
    adjustIframeHeight();

    console.log('✅ Live Designer initialized');
});

// Handle window resize for mobile toggles
window.addEventListener('resize', function() {
    const toggleButtons = document.querySelectorAll('#toggle-left-sidebar, #toggle-right-sidebar');
    if (window.innerWidth <= 991.98) {
        toggleButtons.forEach(btn => btn.style.display = 'block');
    } else {
        toggleButtons.forEach(btn => btn.style.display = 'none');
        document.getElementById('left-sidebar').classList.remove('show');
        document.getElementById('right-sidebar').classList.remove('show');
        document.getElementById('mobile-overlay').style.display = 'none';
    }
});
</script>
@endpush