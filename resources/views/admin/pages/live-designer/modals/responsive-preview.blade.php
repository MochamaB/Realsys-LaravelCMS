<!-- Responsive Preview Modal -->
<div class="modal fade" id="responsive-preview-modal" tabindex="-1" aria-labelledby="responsive-preview-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responsive-preview-modal-label">
                    <i class="ri-smartphone-line me-2"></i>
                    Responsive Preview
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <!-- Device Selector -->
                    <div class="btn-group" role="group" aria-label="Device selector">
                        <input type="radio" class="btn-check" name="device-type" id="device-desktop" checked>
                        <label class="btn btn-outline-primary btn-sm" for="device-desktop">
                            <i class="ri-computer-line me-1"></i>
                            Desktop
                        </label>
                        
                        <input type="radio" class="btn-check" name="device-type" id="device-tablet">
                        <label class="btn btn-outline-primary btn-sm" for="device-tablet">
                            <i class="ri-tablet-line me-1"></i>
                            Tablet
                        </label>
                        
                        <input type="radio" class="btn-check" name="device-type" id="device-mobile">
                        <label class="btn btn-outline-primary btn-sm" for="device-mobile">
                            <i class="ri-smartphone-line me-1"></i>
                            Mobile
                        </label>
                    </div>
                    
                    <!-- Custom Size Toggle -->
                    <button class="btn btn-outline-secondary btn-sm" id="custom-size-toggle">
                        <i class="ri-ruler-line me-1"></i>
                        Custom Size
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Device Frame Container -->
                <div class="device-preview-container" id="device-preview-container">
                    <!-- Custom Size Controls -->
                    <div class="custom-size-controls" id="custom-size-controls" style="display: none;">
                        <div class="d-flex align-items-center justify-content-center gap-3 p-3 bg-light border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 small">Width:</label>
                                <input type="number" class="form-control form-control-sm" id="custom-width" value="1200" min="320" max="2560" style="width: 80px;">
                                <span class="small text-muted">px</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 small">Height:</label>
                                <input type="number" class="form-control form-control-sm" id="custom-height" value="800" min="480" max="1440" style="width: 80px;">
                                <span class="small text-muted">px</span>
                            </div>
                            <button class="btn btn-primary btn-sm" id="apply-custom-size">
                                <i class="ri-check-line me-1"></i>
                                Apply
                            </button>
                        </div>
                    </div>
                    
                    <!-- Device Frame -->
                    <div class="device-frame" id="device-frame">
                        <div class="device-screen" id="device-screen">
                            <!-- Preview iframe will be injected here -->
                            <iframe id="preview-iframe" src="" frameborder="0" width="100%" height="100%"></iframe>
                        </div>
                        
                        <!-- Device Info -->
                        <div class="device-info" id="device-info">
                            <span class="device-name" id="device-name">Desktop</span>
                            <span class="device-dimensions" id="device-dimensions">1200 × 800</span>
                        </div>
                    </div>
                    
                    <!-- Loading State -->
                    <div class="preview-loading" id="preview-loading">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Loading preview...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Zoom Controls -->
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm" id="zoom-out">
                                <i class="ri-zoom-out-line"></i>
                            </button>
                            <span class="small text-muted" id="zoom-level">100%</span>
                            <button class="btn btn-outline-secondary btn-sm" id="zoom-in">
                                <i class="ri-zoom-in-line"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="zoom-fit">
                                <i class="ri-focus-3-line"></i>
                                Fit
                            </button>
                        </div>
                        
                        <!-- Orientation Toggle (for mobile/tablet) -->
                        <button class="btn btn-outline-secondary btn-sm" id="orientation-toggle" style="display: none;">
                            <i class="ri-smartphone-line me-1"></i>
                            <span id="orientation-text">Portrait</span>
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="refresh-preview">
                            <i class="ri-refresh-line me-1"></i>
                            Refresh
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Responsive Preview Modal Styles */
.device-preview-container {
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    position: relative;
    overflow: auto;
    padding: 2rem;
}

.device-frame {
    position: relative;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    transform-origin: center;
}

/* Desktop Frame */
.device-frame.desktop {
    width: 1200px;
    height: 800px;
    border: 8px solid #2c3e50;
    border-radius: 12px;
}

.device-frame.desktop::before {
    content: '';
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: #34495e;
    border-radius: 0 0 4px 4px;
}

/* Tablet Frame */
.device-frame.tablet {
    width: 768px;
    height: 1024px;
    border: 20px solid #2c3e50;
    border-radius: 24px;
    position: relative;
}

.device-frame.tablet::before {
    content: '';
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 4px;
    background: #34495e;
    border-radius: 2px;
}

.device-frame.tablet::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    border: 2px solid #34495e;
    border-radius: 50%;
    background: #2c3e50;
}

.device-frame.tablet.landscape {
    width: 1024px;
    height: 768px;
}

/* Mobile Frame */
.device-frame.mobile {
    width: 375px;
    height: 667px;
    border: 16px solid #2c3e50;
    border-radius: 20px;
    position: relative;
}

.device-frame.mobile::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 4px;
    background: #34495e;
    border-radius: 2px;
}

.device-frame.mobile::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 30px;
    border: 2px solid #34495e;
    border-radius: 50%;
    background: #2c3e50;
}

.device-frame.mobile.landscape {
    width: 667px;
    height: 375px;
}

/* Custom Size Frame */
.device-frame.custom {
    border: 4px solid #6c757d;
    border-radius: 8px;
}

/* Device Screen */
.device-screen {
    width: 100%;
    height: 100%;
    overflow: hidden;
    border-radius: inherit;
    background: #fff;
}

.device-screen iframe {
    border: none;
    border-radius: inherit;
}

/* Device Info */
.device-info {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #fff;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    font-size: 0.875rem;
}

.device-name {
    font-weight: 600;
    color: #495057;
}

.device-dimensions {
    color: #6c757d;
}

/* Loading State */
.preview-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.95);
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.preview-loading.hidden {
    display: none;
}

/* Zoom Levels */
.device-frame.zoom-50 {
    transform: scale(0.5);
}

.device-frame.zoom-75 {
    transform: scale(0.75);
}

.device-frame.zoom-100 {
    transform: scale(1);
}

.device-frame.zoom-125 {
    transform: scale(1.25);
}

.device-frame.zoom-150 {
    transform: scale(1.5);
}

/* Custom Size Controls */
.custom-size-controls {
    width: 100%;
}

/* Responsive Adjustments */
@media (max-width: 1400px) {
    .device-preview-container {
        padding: 1rem;
    }
    
    .device-frame.desktop {
        width: 1000px;
        height: 667px;
    }
}

@media (max-width: 1200px) {
    .device-frame.desktop {
        width: 800px;
        height: 533px;
    }
    
    .device-frame.tablet {
        width: 614px;
        height: 819px;
    }
    
    .device-frame.tablet.landscape {
        width: 819px;
        height: 614px;
    }
}

@media (max-width: 991.98px) {
    .device-preview-container {
        padding: 0.5rem;
    }
    
    .device-frame.desktop {
        width: 600px;
        height: 400px;
    }
    
    .device-frame.tablet {
        width: 460px;
        height: 614px;
    }
    
    .device-frame.tablet.landscape {
        width: 614px;
        height: 460px;
    }
    
    .device-frame.mobile {
        width: 300px;
        height: 533px;
    }
    
    .device-frame.mobile.landscape {
        width: 533px;
        height: 300px;
    }
}

/* Animation for device switching */
.device-frame {
    animation: deviceSwitch 0.3s ease;
}

@keyframes deviceSwitch {
    0% {
        opacity: 0;
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>

<script>
// Responsive Preview Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('responsive-preview-modal');
    const deviceFrame = document.getElementById('device-frame');
    const deviceScreen = document.getElementById('device-screen');
    const previewIframe = document.getElementById('preview-iframe');
    const deviceName = document.getElementById('device-name');
    const deviceDimensions = document.getElementById('device-dimensions');
    const previewLoading = document.getElementById('preview-loading');
    const customSizeControls = document.getElementById('custom-size-controls');
    const customSizeToggle = document.getElementById('custom-size-toggle');
    const orientationToggle = document.getElementById('orientation-toggle');
    const orientationText = document.getElementById('orientation-text');
    const zoomLevel = document.getElementById('zoom-level');
    
    let currentDevice = 'desktop';
    let currentOrientation = 'portrait';
    let currentZoom = 100;
    let isCustomSize = false;
    
    // Device configurations
    const devices = {
        desktop: {
            name: 'Desktop',
            width: 1200,
            height: 800,
            hasOrientation: false
        },
        tablet: {
            name: 'Tablet',
            width: 768,
            height: 1024,
            hasOrientation: true
        },
        mobile: {
            name: 'Mobile',
            width: 375,
            height: 667,
            hasOrientation: true
        }
    };
    
    // Initialize modal
    modal.addEventListener('show.bs.modal', function() {
        loadPreview();
        setDevice('desktop');
    });
    
    // Device type change handlers
    document.querySelectorAll('input[name="device-type"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                setDevice(this.id.replace('device-', ''));
            }
        });
    });
    
    // Custom size toggle
    customSizeToggle.addEventListener('click', function() {
        isCustomSize = !isCustomSize;
        customSizeControls.style.display = isCustomSize ? 'block' : 'none';
        
        if (isCustomSize) {
            setCustomSize();
            this.classList.add('active');
        } else {
            setDevice(currentDevice);
            this.classList.remove('active');
        }
    });
    
    // Apply custom size
    document.getElementById('apply-custom-size').addEventListener('click', function() {
        setCustomSize();
    });
    
    // Orientation toggle
    orientationToggle.addEventListener('click', function() {
        toggleOrientation();
    });
    
    // Zoom controls
    document.getElementById('zoom-in').addEventListener('click', function() {
        adjustZoom(25);
    });
    
    document.getElementById('zoom-out').addEventListener('click', function() {
        adjustZoom(-25);
    });
    
    document.getElementById('zoom-fit').addEventListener('click', function() {
        fitToScreen();
    });
    
    // Refresh preview
    document.getElementById('refresh-preview').addEventListener('click', function() {
        loadPreview();
    });
    
    // Set device function
    function setDevice(device) {
        currentDevice = device;
        isCustomSize = false;
        customSizeToggle.classList.remove('active');
        customSizeControls.style.display = 'none';
        
        const config = devices[device];
        
        // Update device frame
        deviceFrame.className = `device-frame ${device}`;
        
        // Set dimensions
        const width = currentOrientation === 'landscape' && config.hasOrientation ? config.height : config.width;
        const height = currentOrientation === 'landscape' && config.hasOrientation ? config.width : config.height;
        
        deviceFrame.style.width = width + 'px';
        deviceFrame.style.height = height + 'px';
        
        // Update info
        deviceName.textContent = config.name;
        deviceDimensions.textContent = `${width} × ${height}`;
        
        // Show/hide orientation toggle
        orientationToggle.style.display = config.hasOrientation ? 'inline-block' : 'none';
        
        // Apply zoom
        applyZoom();
    }
    
    // Set custom size function
    function setCustomSize() {
        const width = parseInt(document.getElementById('custom-width').value);
        const height = parseInt(document.getElementById('custom-height').value);
        
        deviceFrame.className = 'device-frame custom';
        deviceFrame.style.width = width + 'px';
        deviceFrame.style.height = height + 'px';
        
        deviceName.textContent = 'Custom';
        deviceDimensions.textContent = `${width} × ${height}`;
        
        orientationToggle.style.display = 'none';
        
        applyZoom();
    }
    
    // Toggle orientation function
    function toggleOrientation() {
        currentOrientation = currentOrientation === 'portrait' ? 'landscape' : 'portrait';
        orientationText.textContent = currentOrientation.charAt(0).toUpperCase() + currentOrientation.slice(1);
        
        if (currentOrientation === 'landscape') {
            deviceFrame.classList.add('landscape');
        } else {
            deviceFrame.classList.remove('landscape');
        }
        
        setDevice(currentDevice);
    }
    
    // Adjust zoom function
    function adjustZoom(delta) {
        currentZoom = Math.max(25, Math.min(200, currentZoom + delta));
        applyZoom();
    }
    
    // Apply zoom function
    function applyZoom() {
        deviceFrame.style.transform = `scale(${currentZoom / 100})`;
        zoomLevel.textContent = currentZoom + '%';
    }
    
    // Fit to screen function
    function fitToScreen() {
        const container = document.getElementById('device-preview-container');
        const containerWidth = container.clientWidth - 100; // padding
        const containerHeight = container.clientHeight - 200; // padding + controls
        
        const frameWidth = parseInt(deviceFrame.style.width);
        const frameHeight = parseInt(deviceFrame.style.height);
        
        const scaleX = containerWidth / frameWidth;
        const scaleY = containerHeight / frameHeight;
        const scale = Math.min(scaleX, scaleY, 1);
        
        currentZoom = Math.round(scale * 100);
        applyZoom();
    }
    
    // Load preview function
    function loadPreview() {
        previewLoading.classList.remove('hidden');
        
        // Get current page content from the live designer
        if (window.liveDesigner) {
            const previewUrl = window.liveDesigner.getPreviewUrl();
            previewIframe.src = previewUrl;
        } else {
            // Fallback: use current page URL
            const pageId = {{ $page->id ?? 'null' }};
            previewIframe.src = `/admin/pages/${pageId}/preview`;
        }
        
        previewIframe.onload = function() {
            setTimeout(() => {
                previewLoading.classList.add('hidden');
            }, 500);
        };
    }
});
</script>
