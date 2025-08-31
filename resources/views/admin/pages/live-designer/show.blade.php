@extends('admin.layouts.designer-layout')

@section('title', 'Live Designer - ' . $page->title)

@section('css')
<!-- Simplified Live Designer CSS -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/simple-live-designer.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/live-designer/sidebar-layout.css') }}">

<style>
/* Simplified Live Designer Layout */

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



.preview-container {
    background: #f1f4f7;
    border-radius: 0px;
    padding: 10px;
    transition: all 0.3s ease;
    position: relative;
    overflow-x: auto;
    overflow-y: hidden;
}

.preview-container.device-desktop {
    width: 100%;
    max-width: none;
    overflow: visible;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.preview-container.device-desktop iframe {
    width: 1520px;
    transform-origin: top left;
    transition: transform 0.3s ease;
}

.preview-container.device-tablet {
    width: 768px;
    max-width: 768px;
    margin: 0 auto;
}

.preview-container.device-mobile {
    width: 375px;
    max-width: 375px;
    margin: 0 auto;
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

/* Mobile responsive - Right sidebar only */
@media (max-width: 991.98px) {
    .preview-container.device-tablet,
    .preview-container.device-mobile {
        width: 100%;
        max-width: 100%;
    }
}

.hidden {
    display: none !important;
}

/* ===============================
   SIMPLIFIED LIVE DESIGNER LAYOUT
   =============================== */

/* Main designer content - simplified 3-panel layout with flexible height */
.designer-content {
    display: flex;
    min-height: calc(100vh - 100px);
    overflow: visible;
    align-items: stretch;
    width: 100%;
}

/* Left Sidebar - Fixed width with dynamic height */
#leftSidebarContainer {
    width: 280px;
    min-width: 280px;
    flex-shrink: 0;
    transition: width 0.3s ease, min-width 0.3s ease;
    background: #fff;
    border-right: 1px solid #e9ecef;
    height: auto;
    min-height: calc(100vh - 100px);
    max-height: none;
}

#leftSidebarContainer.collapsed {
    width: 70px;
    min-width: 70px;
}

/* Canvas Container - Takes remaining space between sidebars */
#canvasContainer {
    flex: 1;
    min-width: 0;
    position: relative;
    background: #f1f4f7;
    padding: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: auto;
    min-height: calc(100vh - 100px);
}

/* Right Sidebar - Fixed width, initially collapsed */
#right-sidebar-container {
    width: 280px;
    min-width: 280px;
    flex-shrink: 0;
    transition: width 0.3s ease, min-width 0.3s ease;
    background: #fff;
    border-left: 1px solid #e9ecef;
    height: auto;
    min-height: calc(100vh - 100px);
    max-height: none;
}

#right-sidebar-container.collapsed {
    width: 70px;
    min-width: 70px;
}

/* Preview Iframe - Direct styling for device preview */
#preview-iframe {
    width: 100%;
    height: auto;
    min-height: 600px;
    border: none;
    background: #fff;
    transition: all 0.3s ease;
    transform-origin: top left;
}

/* Device Preview Classes - Applied directly to iframe with dynamic sizing */
#preview-iframe.device-desktop {
    width: 100%;
    max-width: none;
    height: auto;
    min-height: calc(100vh - 120px);
    transform: scale(1);
}

#preview-iframe.device-tablet {
    width: 768px;
    height: auto;
    min-height: 1024px;
    transform: scale(1);
    margin: 0 auto;
}

#preview-iframe.device-mobile {
    width: 375px;
    height: auto;
    min-height: 667px;
    transform: scale(1);
    margin: 0 auto;
}

/* Responsive behavior for mobile screens */
@media (max-width: 991.98px) {
    .designer-content {
        position: relative;
    }
    
    #leftSidebarContainer,
    #right-sidebar-container {
        position: fixed;
        top: 100px;
        bottom: 0;
        z-index: 1045;
        width: 320px;
        max-width: 90vw;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    #leftSidebarContainer.show,
    #right-sidebar-container.show {
        transform: translateX(0);
    }
    
    #right-sidebar-container {
        right: 0;
        left: auto;
        transform: translateX(100%);
    }
    
    #right-sidebar-container.show {
        transform: translateX(0);
    }
    
    #canvasContainer {
        width: 100%;
    }
    
    #preview-iframe.device-desktop {
        width: 100%;
        transform: scale(0.5);
    }
    
    #preview-iframe.device-tablet,
    #preview-iframe.device-mobile {
        width: 100%;
        transform: scale(1);
    }
}

/* Unified Loader Styles */
.unified-page-loader {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(2px);
}

.unified-page-loader .progress-bar {
    width: 200px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 1rem;
    position: relative;
}

.unified-page-loader .progress-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, #0d6efd, transparent);
    animation: progressSlide 1.5s infinite;
}

@keyframes progressSlide {
    0% { left: -100%; }
    100% { left: 100%; }
}

.unified-page-loader .loader-message {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}
</style>
@endsection

@section('content')
<div class="container-fluid h-100 p-0">
     <!-- Toolbar -->
     <div class="row">
        <div class="col-12 p-0">
        @include('admin.pages.live-designer.components.toolbar')
        </div>
    </div>
    <!-- Toolbar -->
  
    <!-- Main Content -->
    <div class="designer-content">
        <!-- Left Sidebar -->
        <div id="leftSidebarContainer">
            @include('admin.pages.live-designer.components.left-sidebar')
        </div>
        
        <!-- Canvas Container -->
        <div id="canvasContainer">
            <!-- Unified Progress Bar Loader -->
            <div class="unified-page-loader" id="liveDesignerLoader" style="display: none;">
                <div class="progress-bar"></div>
                <div class="loader-message">Loading...</div>
            </div>
            
            <!-- Preview Iframe (Direct) -->
            <iframe id="preview-iframe" class="device-desktop" src="{{ route('admin.api.live-preview.preview-iframe', $page) }}"></iframe>
        </div>
        
        <!-- Right Sidebar (Collapsed by Default) -->
        <div id="right-sidebar-container" class="collapsed">
            @include('admin.pages.live-designer.components.right-sidebar')
        </div>
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
<!-- Left Sidebar Component Library Modules -->
<script src="{{ asset('assets/admin/js/live-designer/unified-loader-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/template-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-library.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/default-widget-library.js') }}?v={{ time() }}"></script>
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
        pageStructureContainer: null, // No longer using page structure sidebar
        widgetEditorContainer: document.getElementById('widget-editor-container'),
        skipPageStructure: true // Skip loading page structure since we don't need it
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
    if (typeof UnifiedLoaderManager === 'undefined') {
        console.error('UnifiedLoaderManager class not found');
        return;
    }
    if (typeof TemplateManager === 'undefined') {
        console.error('TemplateManager class not found');
        return;
    }
    if (typeof WidgetLibrary === 'undefined') {
        console.error('WidgetLibrary class not found');
        return;
    }
    if (typeof DefaultWidgetLibrary === 'undefined') {
        console.error('DefaultWidgetLibrary class not found');
        return;
    }

    // Initialize managers
    const updateManager = new UpdateManager('{{ $apiBaseUrl }}', '{{ csrf_token() }}');
    const widgetFormManager = new WidgetFormManager(livePreview, updateManager);
    // Skip DevicePreview initialization as we handle it manually with simplified structure
    // const devicePreview = new DevicePreview(document.getElementById('canvasContainer'));
    
    // Initialize left sidebar component library managers
    const unifiedLoader = new UnifiedLoaderManager();
    const templateManager = new TemplateManager(null, livePreview, unifiedLoader);
    const widgetLibrary = new WidgetLibrary(livePreview, unifiedLoader);
    const defaultWidgetLibrary = new DefaultWidgetLibrary(livePreview, unifiedLoader);
    
    // Initialize left sidebar components
    await templateManager.init();
    await widgetLibrary.init();
    await defaultWidgetLibrary.init();
    
    // Device preview keyboard shortcuts handled manually below

    // Global references for debugging
    window.livePreview = livePreview;
    window.updateManager = updateManager;
    window.widgetFormManager = widgetFormManager;
    window.unifiedLoader = unifiedLoader;
    window.templateManager = templateManager;
    window.widgetLibrary = widgetLibrary;
    window.defaultWidgetLibrary = defaultWidgetLibrary;

    // Mobile sidebar toggle (right sidebar only)
    const toggleRightSidebar = document.getElementById('toggle-right-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    // Show toggle on mobile screens
    if (window.innerWidth <= 991.98) {
        if (toggleRightSidebar) toggleRightSidebar.style.display = 'block';
    }

    if (toggleRightSidebar && rightSidebar && mobileOverlay) {
        toggleRightSidebar.addEventListener('click', function() {
            rightSidebar.classList.toggle('show');
            mobileOverlay.style.display = rightSidebar.classList.contains('show') ? 'block' : 'none';
        });
    }

    if (mobileOverlay && rightSidebar) {
        mobileOverlay.addEventListener('click', function() {
            rightSidebar.classList.remove('show');
            mobileOverlay.style.display = 'none';
        });
    }

    // Device preview controls - fixed for simplified structure
    let currentZoom = 1;
    let currentDevice = 'desktop';
    
    document.querySelectorAll('input[name="preview-mode"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                const iframe = document.getElementById('preview-iframe');
                let device = 'desktop';
                
                switch(this.id) {
                    case 'desktop-mode':
                        device = 'desktop';
                        break;
                    case 'tablet-mode':
                        device = 'tablet';
                        break;
                    case 'mobile-mode':
                        device = 'mobile';
                        break;
                }
                
                // Remove all device classes and add the selected one
                iframe.className = iframe.className.replace(/device-\w+/g, '');
                iframe.classList.add(`device-${device}`);
                
                currentDevice = device;
                
                // Reset zoom when switching devices
                currentZoom = 1;
                updateZoomDisplay();
                
                // Show/hide zoom controls based on device
                const zoomControls = document.getElementById('zoom-controls');
                if (zoomControls) {
                    if (device === 'desktop') {
                        zoomControls.style.display = 'flex';
                    } else {
                        zoomControls.style.display = 'none';
                        // Remove any zoom transform for non-desktop
                        iframe.style.transform = '';
                    }
                }
                
                console.log(`📱 Device switched to: ${device}`);
            }
        });
    });

    // Zoom Controls Functionality
    function updateZoomDisplay() {
        const zoomDisplay = document.querySelector('[data-zoom-display]');
        if (zoomDisplay) {
            zoomDisplay.textContent = Math.round(currentZoom * 100) + '%';
        }
    }
    
    function applyZoom() {
        const iframe = document.getElementById('preview-iframe');
        if (iframe && currentDevice === 'desktop') {
            iframe.style.transform = `scale(${currentZoom})`;
        }
    }
    
    // Debug: Check if zoom controls exist
    console.log('🔍 Zoom controls found:', {
        zoomIn: !!document.querySelector('[data-action="zoom-in"]'),
        zoomOut: !!document.querySelector('[data-action="zoom-out"]'),
        zoomReset: !!document.querySelector('[data-action="zoom-reset"]'),
        zoomFit: !!document.querySelector('[data-action="zoom-fit"]'),
        zoomDisplay: !!document.querySelector('[data-zoom-display]')
    });
    
    // Zoom control buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-action="zoom-in"]') || e.target.closest('[data-action="zoom-in"]')) {
            console.log('🖱️ Zoom in button clicked');
            if (currentDevice === 'desktop') {
                currentZoom = Math.min(currentZoom + 0.1, 2);
                applyZoom();
                updateZoomDisplay();
                console.log(`🔍 Zoom in: ${Math.round(currentZoom * 100)}%`);
            }
        }
        
        if (e.target.matches('[data-action="zoom-out"]') || e.target.closest('[data-action="zoom-out"]')) {
            console.log('🖱️ Zoom out button clicked');
            if (currentDevice === 'desktop') {
                currentZoom = Math.max(currentZoom - 0.1, 0.1);
                applyZoom();
                updateZoomDisplay();
                console.log(`🔍 Zoom out: ${Math.round(currentZoom * 100)}%`);
            }
        }
        
        if (e.target.matches('[data-action="zoom-reset"]') || e.target.closest('[data-action="zoom-reset"]')) {
            console.log('🖱️ Zoom reset button clicked');
            if (currentDevice === 'desktop') {
                currentZoom = 1;
                applyZoom();
                updateZoomDisplay();
                console.log(`🔍 Zoom reset: 100%`);
            }
        }
        
        if (e.target.matches('[data-action="zoom-fit"]') || e.target.closest('[data-action="zoom-fit"]')) {
            console.log('🖱️ Zoom fit button clicked');
            if (currentDevice === 'desktop') {
                const iframe = document.getElementById('preview-iframe');
                const canvasContainer = document.getElementById('canvasContainer');
                
                if (iframe && canvasContainer) {
                    const containerWidth = canvasContainer.clientWidth - 20; // Account for padding
                    const iframeWidth = 1520; // Desktop width
                    currentZoom = Math.min(containerWidth / iframeWidth, 1);
                    applyZoom();
                    updateZoomDisplay();
                    console.log(`🔍 Zoom to fit: ${Math.round(currentZoom * 100)}%`);
                } else {
                    console.error('❌ Canvas container or iframe not found for zoom fit');
                }
            }
        }
    });
    
    // Initial zoom setup
    updateZoomDisplay();

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

    // Left sidebar collapse functionality
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const leftSidebarContainer = document.getElementById('leftSidebarContainer');
    
    console.log('🔍 Left sidebar elements found:', {
        sidebarToggleBtn: !!sidebarToggleBtn,
        leftSidebarContainer: !!leftSidebarContainer
    });
    
    if (sidebarToggleBtn && leftSidebarContainer) {
        sidebarToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Left sidebar toggle button clicked');
            
            leftSidebarContainer.classList.toggle('collapsed');
            
            // Update collapse button icon
            const icon = sidebarToggleBtn.querySelector('i');
            if (leftSidebarContainer.classList.contains('collapsed')) {
                icon.className = 'ri-arrow-right-line';
            } else {
                icon.className = 'ri-arrow-left-line';
            }
            
            console.log('📋 Left sidebar toggled:', leftSidebarContainer.classList.contains('collapsed') ? 'collapsed' : 'expanded');
            
            // Adjust canvas and iframe after sidebar toggle
            setTimeout(adjustCanvasWidth, 300); // Wait for CSS transition
        });
    } else {
        console.error('❌ Left sidebar elements not found:', {
            sidebarToggleBtn: sidebarToggleBtn,
            leftSidebarContainer: leftSidebarContainer
        });
    }
    
    // Right sidebar collapse functionality (match left sidebar structure)
    const rightSidebarToggleBtn = document.getElementById('rightSidebarToggleBtn');
    const rightSidebarContainer = document.getElementById('right-sidebar-container');
    
    console.log('🔍 Right sidebar elements found:', {
        rightSidebarToggleBtn: !!rightSidebarToggleBtn,
        rightSidebarContainer: !!rightSidebarContainer
    });
    
    if (rightSidebarToggleBtn && rightSidebarContainer) {
        rightSidebarToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Right sidebar toggle button clicked');
            
            rightSidebarContainer.classList.toggle('collapsed');
            
            // Update collapse button icon
            const icon = rightSidebarToggleBtn.querySelector('i');
            if (rightSidebarContainer.classList.contains('collapsed')) {
                icon.className = 'ri-arrow-left-line';
            } else {
                icon.className = 'ri-arrow-right-line';
            }
            
            console.log('🔧 Right sidebar toggled:', rightSidebarContainer.classList.contains('collapsed') ? 'collapsed' : 'expanded');
            
            // Adjust canvas and iframe after sidebar toggle
            setTimeout(adjustCanvasWidth, 300); // Wait for CSS transition
        });
        
        // Set initial icon state (since sidebar starts collapsed)
        const initialIcon = rightSidebarToggleBtn.querySelector('i');
        if (rightSidebarContainer.classList.contains('collapsed')) {
            initialIcon.className = 'ri-arrow-left-line';
        }
    } else {
        console.error('❌ Right sidebar elements not found:', {
            rightSidebarToggleBtn: rightSidebarToggleBtn,
            rightSidebarContainer: rightSidebarContainer
        });
    }
    
    // Dynamic iframe height - content-aware
    function adjustIframeHeight() {
        const iframe = document.getElementById('preview-iframe');
        const canvasContainer = document.getElementById('canvasContainer');
        
        if (iframe && canvasContainer) {
            const resizeIframe = () => {
                try {
                    // Try to get content height from iframe (if same origin)
                    let contentHeight = 600; // Default fallback height
                    
                    try {
                        if (iframe.contentDocument && iframe.contentDocument.body) {
                            const body = iframe.contentDocument.body;
                            const html = iframe.contentDocument.documentElement;
                            contentHeight = Math.max(
                                body.scrollHeight,
                                body.offsetHeight,
                                html.clientHeight,
                                html.scrollHeight,
                                html.offsetHeight
                            );
                        }
                    } catch (e) {
                        // Cross-origin restrictions, use container height
                        console.log('🔍 Using container height due to cross-origin restrictions');
                        contentHeight = Math.max(canvasContainer.clientHeight - 20, 600);
                    }
                    
                    // For desktop view, ensure minimum height based on content
                    if (iframe.classList.contains('device-desktop')) {
                        const minDesktopHeight = Math.max(contentHeight, window.innerHeight - 120);
                        iframe.style.height = `${minDesktopHeight}px`;
                    } else {
                        // For tablet/mobile, use content height with device-specific minimums
                        const deviceMinHeight = iframe.classList.contains('device-tablet') ? 1024 : 667;
                        iframe.style.height = `${Math.max(contentHeight, deviceMinHeight)}px`;
                    }
                    
                    console.log('🔧 Iframe height adjusted:', {
                        contentHeight,
                        finalHeight: iframe.style.height,
                        deviceClass: iframe.className
                    });
                    
                } catch (error) {
                    console.error('❌ Error adjusting iframe height:', error);
                    // Fallback to container height
                    iframe.style.height = `${Math.max(canvasContainer.clientHeight - 20, 600)}px`;
                }
            };
            
            // Initial resize
            setTimeout(resizeIframe, 100);
            
            // Resize on iframe load
            iframe.addEventListener('load', () => {
                setTimeout(resizeIframe, 200); // Give time for content to render
            });
            
            // Monitor content changes if possible
            if (iframe.contentDocument) {
                const observer = new MutationObserver(() => {
                    setTimeout(resizeIframe, 100);
                });
                
                try {
                    observer.observe(iframe.contentDocument.body, {
                        childList: true,
                        subtree: true,
                        attributes: true
                    });
                } catch (e) {
                    console.log('🔍 Cannot observe iframe content changes due to cross-origin restrictions');
                }
            }
            
            // Store resize function for external calls
            window.adjustIframeHeight = resizeIframe;
        }
    }
    
    // Canvas width adjustment for sidebar collapse/expand
    function adjustCanvasWidth() {
        const iframe = document.getElementById('preview-iframe');
        const canvasContainer = document.getElementById('canvasContainer');
        const leftSidebar = document.getElementById('leftSidebarContainer');
        const rightSidebar = document.getElementById('right-sidebar-container');
        
        if (iframe && canvasContainer) {
            // Calculate available width based on sidebar states
            const leftSidebarWidth = leftSidebar.classList.contains('collapsed') ? 70 : 280;
            const rightSidebarWidth = rightSidebar.classList.contains('collapsed') ? 70 : 280;
            const totalSidebarWidth = leftSidebarWidth + rightSidebarWidth;
            const availableWidth = window.innerWidth - totalSidebarWidth - 40; // Account for padding/borders
            
            console.log('🔧 Canvas width adjustment:', {
                leftSidebarWidth,
                rightSidebarWidth,
                totalSidebarWidth,
                availableWidth,
                windowWidth: window.innerWidth
            });
            
            // For desktop device view, adjust iframe scaling if needed
            if (iframe.classList.contains('device-desktop')) {
                const desiredIframeWidth = 1520; // Desktop width
                if (availableWidth < desiredIframeWidth) {
                    const scale = Math.max(0.3, availableWidth / desiredIframeWidth);
                    iframe.style.transform = `scale(${scale})`;
                    iframe.style.width = `${desiredIframeWidth}px`;
                    iframe.style.height = `${Math.max(600, (window.innerHeight - 120) / scale)}px`;
                } else {
                    iframe.style.transform = 'scale(1)';
                    iframe.style.width = '100%';
                    iframe.style.height = 'auto';
                }
            }
            
            // Trigger iframe height adjustment
            adjustIframeHeight();
        }
    }
    
    adjustIframeHeight();
    adjustCanvasWidth();
    
    // Add window resize listener for dynamic adjustments
    window.addEventListener('resize', function() {
        setTimeout(() => {
            adjustCanvasWidth();
            adjustIframeHeight();
        }, 100);
    });

    console.log('✅ Live Designer initialized');
});

// Handle window resize for mobile toggles
window.addEventListener('resize', function() {
    const toggleRightSidebar = document.getElementById('toggle-right-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (window.innerWidth <= 991.98) {
        if (toggleRightSidebar) toggleRightSidebar.style.display = 'block';
    } else {
        if (toggleRightSidebar) toggleRightSidebar.style.display = 'none';
        if (rightSidebar) rightSidebar.classList.remove('show');
        if (mobileOverlay) mobileOverlay.style.display = 'none';
    }
});
</script>
@endpush