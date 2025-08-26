/**
 * Page Builder Device Preview Manager
 * Standalone implementation for page builder responsive preview
 */
class PageBuilderDevicePreview {
    constructor() {
        this.iframe = document.getElementById("pagePreviewIframe");
        this.canvasContainer = document.getElementById("canvasContainer");
        
        this.currentDevice = 'desktop';
        this.devices = {
            desktop: { 
                width: 1480,
                height: 'auto',
                name: 'Desktop',
                viewportWidth: 1480,
                icon: 'ri-computer-line',
                zoom: 1
            },
            tablet: { 
                width: 768,
                height: 1024,
                name: 'Tablet',
                viewportWidth: 768,
                icon: 'ri-tablet-line'
            },
            mobile: { 
                width: 375,
                height: 667,
                name: 'Mobile',
                viewportWidth: 375,
                icon: 'ri-smartphone-line'
            }
        };
        
        this.init();
    }
    
    init() {
        this.setupContainer();
        this.setupDeviceToolbarHandlers();
        this.setupZoomControls();
        this.setupResizeHandler();
        this.setDevice('desktop'); // Default to desktop
        
        console.log('📱 Page Builder Device Preview initialized');
    }
    
    setupContainer() {
        // Wrap iframe in preview container if not already wrapped
        if (!this.iframe.parentElement.classList.contains('preview-container')) {
            const container = document.createElement('div');
            container.className = 'preview-container device-desktop';
            this.iframe.parentNode.insertBefore(container, this.iframe);
            container.appendChild(this.iframe);
            this.container = container;
        } else {
            this.container = this.iframe.parentElement;
        }
        
        // Ensure canvas container has proper overflow handling
        this.canvasContainer.style.overflowX = 'hidden';
        this.canvasContainer.style.overflowY = 'auto';
    }
    
    setupDeviceToolbarHandlers() {
        // Find device mode radio buttons in toolbar
        const deviceRadios = document.querySelectorAll('input[name="device-mode"]');
        
        if (deviceRadios.length === 0) {
            console.warn('⚠️ No device mode radio buttons found in toolbar');
            return;
        }
        
        // Add event listeners to device radio buttons
        deviceRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    const device = radio.value;
                    console.log(`📱 Device switched to: ${device}`);
                    this.setDevice(device);
                }
            });
        });
    }
    
    setupZoomControls() {
        // Zoom in button
        const zoomInBtn = document.querySelector('[data-action="zoom-in"]');
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', () => {
                if (this.currentDevice === 'desktop') {
                    const currentZoom = this.devices.desktop.zoom;
                    this.setDesktopZoomWithCentering(currentZoom + 0.1);
                }
            });
        }
        
        // Zoom out button
        const zoomOutBtn = document.querySelector('[data-action="zoom-out"]');
        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', () => {
                if (this.currentDevice === 'desktop') {
                    const currentZoom = this.devices.desktop.zoom;
                    this.setDesktopZoomWithCentering(currentZoom - 0.1);
                }
            });
        }
        
        // Zoom fit button
        const zoomFitBtn = document.querySelector('[data-action="zoom-fit"]');
        if (zoomFitBtn) {
            zoomFitBtn.addEventListener('click', () => {
                if (this.currentDevice === 'desktop') {
                    this.calculateOptimalZoomWithCentering();
                }
            });
        }
        
        // Zoom 100% button
        const zoom100Btn = document.querySelector('[data-action="zoom-100"]');
        if (zoom100Btn) {
            zoom100Btn.addEventListener('click', () => {
                if (this.currentDevice === 'desktop') {
                    this.setDesktopZoomWithCentering(1);
                }
            });
        }
    }
    
    setupResizeHandler() {
        window.addEventListener('resize', () => {
            if (this.currentDevice === 'desktop') {
                setTimeout(() => this.calculateOptimalZoomWithCentering(), 100);
            }
        });
    }
    
    setDevice(device) {
        if (!this.devices[device]) {
            console.warn(`Unknown device: ${device}`);
            return;
        }
        
        this.currentDevice = device;
        const deviceConfig = this.devices[device];
        
        // Remove all device classes
        this.container.className = 'preview-container';
        this.container.classList.add(`device-${device}`);
        
        if (device === 'desktop') {
            this.applyDesktopView(deviceConfig);
        } else {
            this.applyMobileView(device, deviceConfig);
        }
        
        // Force viewport in iframe
        this.setIframeViewport(deviceConfig.viewportWidth);
        
        console.log(`📱 Device preview set to: ${device}`);
    }
    
    applyDesktopView(deviceConfig) {
        // Reset any previous transforms and styling
        this.container.style.transform = 'none';
        this.container.style.padding = '0';
        this.container.style.margin = '0';
        this.container.style.display = 'block';
        this.container.style.justifyContent = 'initial';
        this.container.style.alignItems = 'initial';
        
        // Set iframe to desktop width with no margins or padding
        this.iframe.style.width = deviceConfig.width + 'px';
        this.iframe.style.height = 'auto';
        this.iframe.style.maxWidth = 'none';
        this.iframe.style.maxHeight = 'none';
        this.iframe.style.margin = '0';
        this.iframe.style.padding = '0';
        this.iframe.style.display = 'block';
        
        // Remove device frame classes that add padding
        this.container.classList.remove('device-frame', 'device-frame-tablet', 'device-frame-mobile');
        
        // Recalculate iframe height for desktop view
        this.recalculateIframeHeight();
        
        // Calculate and apply optimal zoom with proper centering
        setTimeout(() => {
            this.calculateOptimalZoomWithCentering();
        }, 100);
    }
    
    applyMobileView(device, deviceConfig) {
        // Reset zoom
        this.iframe.style.transform = 'none';
        this.iframe.style.transformOrigin = 'initial';
        
        // Set iframe to device dimensions
        this.iframe.style.width = deviceConfig.width + 'px';
        this.iframe.style.height = deviceConfig.height + 'px';
        this.iframe.style.maxWidth = deviceConfig.width + 'px';
        this.iframe.style.maxHeight = deviceConfig.height + 'px';
        
        // Add device frame
        this.container.classList.add('device-frame', `device-frame-${device}`);
        
        // Scale container if needed for smaller screens
        const containerWidth = this.canvasContainer.offsetWidth - 40;
        const deviceFrameWidth = device === 'tablet' ? 
            deviceConfig.width + 40 : deviceConfig.width + 30;
            
        if (containerWidth < deviceFrameWidth) {
            const scale = containerWidth / deviceFrameWidth;
            this.container.style.transform = `scale(${scale})`;
            this.container.style.transformOrigin = 'top center';
        } else {
            this.container.style.transform = 'none';
        }
    }
    
    recalculateIframeHeight() {
        try {
            // Access iframe document to recalculate height
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
            
            if (iframeDoc && iframeDoc.body) {
                // Remove iframe internal scrollbars for desktop view
                this.iframe.style.overflow = "hidden";
                iframeDoc.body.style.overflow = "hidden";
                
                // Force a reflow to get accurate measurements
                iframeDoc.body.offsetHeight;
                
                // Set height to fit content like the initial load
                const contentHeight = Math.max(
                    iframeDoc.body.scrollHeight,
                    iframeDoc.body.offsetHeight,
                    iframeDoc.documentElement.clientHeight,
                    iframeDoc.documentElement.scrollHeight,
                    iframeDoc.documentElement.offsetHeight
                );
                
                this.iframe.style.height = contentHeight + "px";
                
                console.log(`📏 Recalculated iframe height: ${contentHeight}px`);
            }
        } catch (e) {
            console.warn("Cross-origin restriction: can't access iframe content for height calculation:", e.message);
            // Fallback: set a reasonable default height
            this.iframe.style.height = "800px";
        }
    }
    
    calculateOptimalZoom() {
        if (this.currentDevice !== 'desktop') return;
        
        const containerWidth = this.canvasContainer.offsetWidth - 40;
        const desktopWidth = this.devices.desktop.width;
        
        // Calculate zoom to fit
        let zoom = Math.min(containerWidth / desktopWidth, 1);
        zoom = Math.max(zoom, 0.2); // Minimum zoom of 20%
        
        this.setDesktopZoom(zoom);
    }
    
    calculateOptimalZoomWithCentering() {
        if (this.currentDevice !== 'desktop') return;
        
        // Get actual available space (accounting for padding and scrollbars)
        const containerRect = this.canvasContainer.getBoundingClientRect();
        const availableWidth = containerRect.width - 60; // Extra margin for safety
        const desktopWidth = this.devices.desktop.width;
        
        console.log(`📐 Container width: ${containerRect.width}px, Available: ${availableWidth}px, Desktop width: ${desktopWidth}px`);
        
        // Calculate zoom to fit without horizontal scroll
        let zoom = availableWidth / desktopWidth;
        zoom = Math.min(zoom, 1); // Don't zoom above 100%
        zoom = Math.max(zoom, 0.1); // Minimum zoom of 10%
        
        // Apply zoom and centering
        this.setDesktopZoomWithCentering(zoom);
    }
    
    setDesktopZoom(zoom) {
        if (this.currentDevice !== 'desktop') return;
        
        zoom = Math.max(0.1, Math.min(2, zoom)); // Clamp between 10% and 200%
        
        // Apply zoom
        this.iframe.style.transform = `scale(${zoom})`;
        this.iframe.style.transformOrigin = 'top center';
        
        // Store zoom level
        this.devices.desktop.zoom = zoom;
        
        // Update zoom indicator
        this.updateZoomIndicator(zoom);
        
        console.log(`🔍 Desktop zoom set to: ${(zoom * 100).toFixed(1)}%`);
    }
    
    setDesktopZoomWithCentering(zoom) {
        if (this.currentDevice !== 'desktop') return;
        
        zoom = Math.max(0.1, Math.min(2, zoom)); // Clamp between 10% and 200%
        
        // Calculate the scaled dimensions
        const desktopWidth = this.devices.desktop.width;
        const scaledWidth = desktopWidth * zoom;
        const containerWidth = this.canvasContainer.getBoundingClientRect().width;
        
        // Apply zoom with proper transform origin - use 'top left' to prevent distortion
        this.iframe.style.transform = `scale(${zoom})`;
        this.iframe.style.transformOrigin = 'top left';
        
        // Reset all margins and padding
        this.iframe.style.margin = '0';
        this.iframe.style.padding = '0';
        
        // Calculate left margin to center the scaled iframe
        const leftMargin = Math.max(0, (containerWidth - scaledWidth) / 2);
        
        // Apply centering margin if the scaled width is smaller than container
        if (scaledWidth < containerWidth) {
            this.iframe.style.marginLeft = `${leftMargin}px`;
            this.iframe.style.marginRight = `${leftMargin}px`;
        } else {
            // If scaled width exceeds container, no margins (will have scroll)
            this.iframe.style.marginLeft = '0';
            this.iframe.style.marginRight = '0';
        }
        
        // Ensure container doesn't interfere with centering
        this.container.style.display = 'block';
        this.container.style.width = '100%';
        this.container.style.padding = '0';
        this.container.style.margin = '0';
        this.container.style.overflowX = scaledWidth > containerWidth ? 'auto' : 'hidden';
        
        // Store zoom level
        this.devices.desktop.zoom = zoom;
        
        // Update zoom indicator
        this.updateZoomIndicator(zoom);
        
        console.log(`🔍 Desktop zoom with centering: ${(zoom * 100).toFixed(1)}%, Scaled width: ${scaledWidth.toFixed(0)}px, Container: ${containerWidth.toFixed(0)}px, Left margin: ${leftMargin.toFixed(0)}px`);
    }
    
    updateZoomIndicator(zoom) {
        const zoomIndicator = document.getElementById('zoom-level');
        if (zoomIndicator) {
            const percentage = (zoom * 100).toFixed(0);
            zoomIndicator.textContent = `${percentage}%`;
            
            // Add zoom class for styling
            if (zoom < 1) {
                zoomIndicator.classList.add('zoomed-out');
            } else {
                zoomIndicator.classList.remove('zoomed-out');
            }
        }
    }
    
    setIframeViewport(viewportWidth) {
        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
            
            // Remove existing viewport meta tag
            const existingViewport = iframeDoc.querySelector('meta[name="viewport"]');
            if (existingViewport) {
                existingViewport.remove();
            }
            
            // Add new viewport meta tag with device width
            const viewportMeta = iframeDoc.createElement('meta');
            viewportMeta.name = 'viewport';
            viewportMeta.content = `width=${viewportWidth}, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0`;
            iframeDoc.head.appendChild(viewportMeta);
            
            // Force body width and override responsive CSS
            const existingStyle = iframeDoc.getElementById('device-preview-override');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            const style = iframeDoc.createElement('style');
            style.id = 'device-preview-override';
            style.textContent = `
                /* Force viewport width */
                html, body {
                    min-width: ${viewportWidth}px !important;
                    width: ${viewportWidth}px !important;
                    max-width: none !important;
                    overflow-x: hidden !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                /* Override responsive container behavior for desktop */
                ${this.currentDevice === 'desktop' ? `
                .container, .container-fluid, .container-xl, .container-lg, .container-md, .container-sm {
                    max-width: ${viewportWidth - 40}px !important;
                    width: 100% !important;
                }
                
                /* Force desktop layout - disable ALL responsive breakpoints */
                @media (max-width: 1440px) {
                    .container { max-width: ${viewportWidth - 40}px !important; }
                    
                    /* Force desktop grid behavior */
                    .col-xl-*, .col-lg-*, .col-md-*, .col-sm-*, .col-* {
                        flex: 0 0 auto !important;
                    }
                    
                    .col-xl-1 { width: 8.33333333% !important; }
                    .col-xl-2 { width: 16.66666667% !important; }
                    .col-xl-3 { width: 25% !important; }
                    .col-xl-4 { width: 33.33333333% !important; }
                    .col-xl-5 { width: 41.66666667% !important; }
                    .col-xl-6 { width: 50% !important; }
                    .col-xl-7 { width: 58.33333333% !important; }
                    .col-xl-8 { width: 66.66666667% !important; }
                    .col-xl-9 { width: 75% !important; }
                    .col-xl-10 { width: 83.33333333% !important; }
                    .col-xl-11 { width: 91.66666667% !important; }
                    .col-xl-12 { width: 100% !important; }
                }
                
                /* Disable transitions that might interfere with preview */
                * {
                    transition: none !important;
                    -webkit-transition: none !important;
                }
                ` : ''}
                
                /* Mobile/Tablet specific overrides */
                ${this.currentDevice !== 'desktop' ? `
                .container {
                    max-width: 100% !important;
                    padding: 15px !important;
                }
                ` : ''}
            `;
            iframeDoc.head.appendChild(style);
            
            console.log(`🖥️ Viewport set to ${viewportWidth}px for ${this.currentDevice} mode`);
            
        } catch (e) {
            console.warn("Cross-origin restriction: can't modify iframe viewport:", e.message);
        }
    }
    
    // Public methods
    getCurrentDevice() {
        return this.currentDevice;
    }
    
    getCurrentZoom() {
        return this.devices[this.currentDevice]?.zoom || 1;
    }
    
    isDesktop() {
        return this.currentDevice === 'desktop';
    }
    
    refresh() {
        this.setDevice(this.currentDevice);
    }
}

// Initialize device preview when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait for page builder to initialize first
    setTimeout(function() {
        console.log('📱 Initializing Device Preview...');
        window.devicePreview = new PageBuilderDevicePreview();
        console.log('✅ Device Preview initialized');
    }, 300);
});