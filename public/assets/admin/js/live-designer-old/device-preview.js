/**
 * Device Preview Manager
 * 
 * Manages responsive preview modes (desktop, tablet, mobile)
 */
class DevicePreview {
    constructor(canvasContainer) {
        this.canvasContainer = canvasContainer || document.getElementById('canvasContainer');
        this.iframe = null;
        this.currentDevice = 'desktop';
        this.currentZoom = 1;
        this.initialized = false;
        
        this.devices = {
            desktop: { 
                width: 1440,
                height: 'auto',
                zoom: 1
            },
            tablet: { 
                width: 768, 
                height: 1024 
            },
            mobile: { 
                width: 375, 
                height: 667 
            }
        };
        
        this.init();
    }
    
    /**
     * Initialize device preview
     */
    init() {
        console.log('📱 Initializing Device Preview...');
        
        // Find iframe element
        this.iframe = document.getElementById('preview-iframe');
        
        if (!this.canvasContainer) {
            console.error('❌ Canvas container not found');
            return;
        }
        
        if (!this.iframe) {
            console.warn('⚠️ Iframe not found, retrying in 500ms...');
            setTimeout(() => this.init(), 500);
            return;
        }
        
        // Wait for iframe to be ready
        if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
            this.finishInit();
        } else {
            this.iframe.addEventListener('load', () => this.finishInit());
        }
    }
    
    /**
     * Finish initialization after iframe is ready
     */
    finishInit() {
        if (this.initialized) return;
        
        this.setupDeviceControls();
        this.setupZoomControls();
        this.setupResizeHandler();
        
        // Set initial device
        this.setDevice('desktop');
        
        this.initialized = true;
        console.log('✅ Device Preview initialized');
    }
    
    /**
     * Set preview device
     */
    setDevice(device) {
        if (!this.devices[device]) {
            console.warn(`Unknown device: ${device}`);
            return;
        }
        
        if (!this.iframe) {
            console.warn('⚠️ Iframe not available for device change');
            return;
        }
        
        this.currentDevice = device;
        
        // Remove all device classes
        this.iframe.classList.remove('device-desktop', 'device-tablet', 'device-mobile');
        
        // Add current device class
        this.iframe.classList.add(`device-${device}`);
        
        // Apply device-specific styling
        if (device === 'desktop') {
            this.setupDesktopViewport();
        } else {
            this.setupMobileViewport(device, this.devices[device]);
        }
        
        // Show/hide zoom controls based on device
        this.toggleZoomControls(device === 'desktop');
        
        // Notify iframe of device change
        this.notifyIframeDeviceChange(device);
        
        console.log(`📱 Device preview set to: ${device}`);
    }
    
    /**
     * Update device frame styling
     */
    updateDeviceFrame(device) {
        // Remove existing frame classes
        this.container.classList.remove('device-frame', 'device-frame-tablet', 'device-frame-mobile');
        
        // Add device-specific frame
        if (device !== 'desktop') {
            this.container.classList.add('device-frame', `device-frame-${device}`);
        }
    }
    
    /**
     * Setup device-specific CSS classes
     */
    setupDeviceClasses() {
        // Add device preview styles to document if not already present
        if (!document.getElementById('device-preview-styles')) {
            const style = document.createElement('style');
            style.id = 'device-preview-styles';
            style.textContent = `
                /* Device Frame Styles */
                .device-frame {
                    position: relative;
                    margin: 20px auto;
                    border-radius: 20px;
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
                }
                
                .device-frame-tablet {
                    background: linear-gradient(145deg, #2a2a2a 0%, #1a1a1a 100%);
                    padding: 40px 20px;
                    border-radius: 25px;
                }
                
                .device-frame-tablet::before {
                    content: '';
                    position: absolute;
                    top: 15px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 60px;
                    height: 4px;
                    background: #444;
                    border-radius: 2px;
                }
                
                .device-frame-mobile {
                    background: linear-gradient(145deg, #2a2a2a 0%, #1a1a1a 100%);
                    padding: 60px 15px 60px;
                    border-radius: 30px;
                    max-width: 405px; /* 375px + padding */
                }
                
                .device-frame-mobile::before {
                    content: '';
                    position: absolute;
                    top: 25px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 50px;
                    height: 4px;
                    background: #444;
                    border-radius: 2px;
                }
                
                .device-frame-mobile::after {
                    content: '';
                    position: absolute;
                    bottom: 25px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 40px;
                    height: 40px;
                    border: 2px solid #444;
                    border-radius: 50%;
                }
                
                /* Iframe adjustments for frames */
                .device-frame iframe {
                    border-radius: 8px;
                    overflow: hidden;
                }
                
                .device-frame-tablet iframe {
                    border-radius: 12px;
                }
                
                .device-frame-mobile iframe {
                    border-radius: 20px;
                }
                
                /* Responsive adjustments */
                @media (max-width: 991.98px) {
                    .device-frame-tablet,
                    .device-frame-mobile {
                        margin: 10px;
                        padding: 20px 10px;
                    }
                    
                    .device-frame-tablet::before,
                    .device-frame-mobile::before {
                        top: 8px;
                    }
                    
                    .device-frame-mobile::after {
                        bottom: 8px;
                    }
                }
                
                /* Smooth transitions */
                .preview-container {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }
                
                .preview-container iframe {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    /**
     * Notify iframe about device change
     */
    notifyIframeDeviceChange(device) {
        try {
            const iframeWindow = this.iframe.contentWindow;
            if (iframeWindow) {
                iframeWindow.postMessage({
                    type: 'device-changed',
                    device: device,
                    settings: this.devices[device]
                }, '*');
            }
        } catch (error) {
            // Iframe might not be loaded yet, that's okay
        }
    }
    
    /**
     * Get current device
     */
    getCurrentDevice() {
        return this.currentDevice;
    }
    
    /**
     * Get device settings
     */
    getDeviceSettings(device = null) {
        device = device || this.currentDevice;
        return this.devices[device] || null;
    }
    
    /**
     * Setup device controls (buttons)
     */
    setupDeviceControls() {
        // Find device toggle buttons
        const deviceButtons = document.querySelectorAll('[data-device]');
        
        deviceButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const device = button.getAttribute('data-device');
                this.setDevice(device);
                
                // Update active state
                deviceButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
        
        // Set initial active state
        const desktopButton = document.querySelector('[data-device="desktop"]');
        if (desktopButton) {
            desktopButton.classList.add('active');
        }
    }
    
    /**
     * Setup desktop viewport with zoom capability
     */
    setupDesktopViewport() {
        if (!this.iframe) {
            console.warn('⚠️ Iframe not available for desktop setup');
            return;
        }
        
        const desiredWidth = 1440; // Desktop width
        const availableWidth = this.canvasContainer.clientWidth;
        
        // Calculate zoom to fit if needed
        let zoom = this.currentZoom;
        if (zoom === 'fit') {
            zoom = Math.max(0.3, availableWidth / desiredWidth);
        }
        
        // Apply styling directly to iframe
        this.iframe.style.width = `${desiredWidth}px`;
        this.iframe.style.height = 'auto';
        this.iframe.style.transform = `scale(${zoom})`;
        this.iframe.style.transformOrigin = 'top left';
        
        // Adjust height to content after load
        this.adjustIframeHeight();
        this.iframe.style.margin = '0 auto';
        this.iframe.style.display = 'block';
        this.iframe.style.overflow = 'hidden';
        
        console.log(`🖥️ Desktop viewport: ${desiredWidth}px at ${(zoom * 100).toFixed(1)}% zoom`);
    }
    
    /**
     * Setup mobile viewport (tablet/mobile)
     */
    setupMobileViewport(device, settings) {
        if (!this.iframe) {
            console.warn('⚠️ Iframe not available for mobile setup');
            return;
        }
        
        // Apply direct dimensions for mobile devices
        this.iframe.style.width = `${settings.width}px`;
        this.iframe.style.height = 'auto';
        this.iframe.style.transform = 'scale(1)';
        this.iframe.style.transformOrigin = 'top center';
        
        // Adjust height to content after load
        this.adjustIframeHeight();
        this.iframe.style.margin = '0 auto';
        this.iframe.style.display = 'block';
        this.iframe.style.overflow = 'hidden';
        
        console.log(`📱 ${device} viewport: ${settings.width}x${settings.height}`);
    }
    
    /**
     * Adjust iframe height dynamically based on content
     */
    adjustIframeHeight() {
        if (!this.iframe) return;
        
        try {
            // Wait for iframe to load
            this.iframe.addEventListener('load', () => {
                try {
                    const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                    if (iframeDoc && iframeDoc.body) {
                        const contentHeight = Math.max(
                            iframeDoc.body.scrollHeight,
                            iframeDoc.body.offsetHeight,
                            iframeDoc.documentElement.clientHeight,
                            iframeDoc.documentElement.scrollHeight,
                            iframeDoc.documentElement.offsetHeight
                        );
                        
                        this.iframe.style.height = `${contentHeight}px`;
                        console.log(`📏 Iframe height adjusted to: ${contentHeight}px`);
                    }
                } catch (error) {
                    // Cross-origin restrictions - use fallback
                    this.iframe.style.height = '100vh';
                    console.log('📏 Using fallback height due to cross-origin restrictions');
                }
            });
        } catch (error) {
            console.warn('⚠️ Could not set up iframe height adjustment:', error);
        }
    }
    
    /**
     * Setup zoom controls
     */
    setupZoomControls() {
        // Find zoom control buttons
        const zoomInBtn = document.querySelector('[data-zoom="in"]');
        const zoomOutBtn = document.querySelector('[data-zoom="out"]');
        const zoomResetBtn = document.querySelector('[data-zoom="reset"]');
        const zoomFitBtn = document.querySelector('[data-zoom="fit"]');
        
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.zoomIn();
            });
        }
        
        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.zoomOut();
            });
        }
        
        if (zoomResetBtn) {
            zoomResetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.zoomReset();
            });
        }
        
        if (zoomFitBtn) {
            zoomFitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.zoomFit();
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey) {
                switch(e.key) {
                    case 'D':
                    case 'd':
                        e.preventDefault();
                        this.setDevice('desktop');
                        break;
                    case '1':
                        e.preventDefault();
                        this.setDevice('desktop');
                        break;
                    case '2':
                        e.preventDefault();
                        this.setDevice('tablet');
                        break;
                    case '3':
                        e.preventDefault();
                        this.setDevice('mobile');
                        break;
                }
            }
        });
    }
    
    /**
     * Update zoom controls visibility
     */
    updateZoomControlsVisibility(device) {
        const zoomControls = document.querySelector('.zoom-controls');
        if (zoomControls) {
            // Show zoom controls only for desktop
            zoomControls.style.display = device === 'desktop' ? 'flex' : 'none';
        }
    }
    
    /**
     * Zoom in
     */
    zoomIn() {
        if (this.currentDevice !== 'desktop') return;
        
        const zoomLevels = [0.25, 0.5, 0.75, 1, 1.25, 1.5, 2];
        const currentIndex = zoomLevels.findIndex(level => Math.abs(level - this.currentZoom) < 0.01);
        const nextIndex = Math.min(currentIndex + 1, zoomLevels.length - 1);
        
        this.currentZoom = zoomLevels[nextIndex];
        this.setupDesktopViewport();
    }
    
    /**
     * Zoom out
     */
    zoomOut() {
        if (this.currentDevice !== 'desktop') return;
        
        const zoomLevels = [0.25, 0.5, 0.75, 1, 1.25, 1.5, 2];
        const currentIndex = zoomLevels.findIndex(level => Math.abs(level - this.currentZoom) < 0.01);
        const prevIndex = Math.max(currentIndex - 1, 0);
        
        this.currentZoom = zoomLevels[prevIndex];
        this.setupDesktopViewport();
    }
    
    /**
     * Reset zoom to 100%
     */
    zoomReset() {
        if (this.currentDevice !== 'desktop') return;
        
        this.currentZoom = 1;
        this.setupDesktopViewport();
    }
    
    /**
     * Fit zoom to container
     */
    zoomFit() {
        if (this.currentDevice !== 'desktop') return;
        
        this.currentZoom = 'fit';
        this.setupDesktopViewport();
    }
    
    /**
     * Setup resize handler
     */
    setupResizeHandler() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (this.currentDevice === 'desktop') {
                    this.setupDesktopViewport();
                }
            }, 250);
        });
    }
    
    /**
     * Check if current device is mobile
     */
    isMobile() {
        return this.currentDevice === 'mobile';
    }
    
    /**
     * Check if current device is tablet
     */
    isTablet() {
        return this.currentDevice === 'tablet';
    }
    
    /**
     * Check if current device is desktop
     */
    isDesktop() {
        return this.currentDevice === 'desktop';
    }
    
    /**
     * Cycle through devices (useful for keyboard shortcuts)
     */
    cycleDevice() {
        const devices = Object.keys(this.devices);
        const currentIndex = devices.indexOf(this.currentDevice);
        const nextIndex = (currentIndex + 1) % devices.length;
        const nextDevice = devices[nextIndex];
        
        this.setDevice(nextDevice);
        
        // Update UI radio buttons
        const deviceModeMap = {
            'desktop': 'desktop-mode',
            'tablet': 'tablet-mode', 
            'mobile': 'mobile-mode'
        };
        
        const radioId = deviceModeMap[nextDevice];
        if (radioId) {
            const radioButton = document.getElementById(radioId);
            if (radioButton) {
                radioButton.checked = true;
            }
        }
        
        return nextDevice;
    }
    
    /**
     * Add keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Shift + D to cycle devices
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.cycleDevice();
            }
            
            // Ctrl/Cmd + 1/2/3 for specific devices
            if ((e.ctrlKey || e.metaKey)) {
                switch (e.key) {
                    case '1':
                        e.preventDefault();
                        this.setDevice('desktop');
                        document.getElementById('desktop-mode').checked = true;
                        break;
                    case '2':
                        e.preventDefault();
                        this.setDevice('tablet');
                        document.getElementById('tablet-mode').checked = true;
                        break;
                    case '3':
                        e.preventDefault();
                        this.setDevice('mobile');
                        document.getElementById('mobile-mode').checked = true;
                        break;
                }
            }
        });
        
        console.log('⌨️ Device preview keyboard shortcuts enabled');
        console.log('   Ctrl+Shift+D: Cycle devices');
        console.log('   Ctrl+1/2/3: Desktop/Tablet/Mobile');
    }
    
    /**
     * Show device info tooltip
     */
    showDeviceInfo() {
        const device = this.devices[this.currentDevice];
        const zoomInfo = this.currentDevice === 'desktop' ? ` @ ${Math.round(device.zoom * 100)}%` : '';
        const message = `Current device: ${device.name} (${device.width} × ${device.height})${zoomInfo}`;
        
        if (window.livePreview) {
            window.livePreview.showMessage(message, 'info');
        }
    }
    
    /**
     * Setup mobile/tablet viewport (direct dimensions)
     */
    setupMobileViewport(device, settings) {
        // Remove any transform scaling for mobile/tablet
        this.iframe.style.transform = '';
        this.iframe.style.transformOrigin = '';
        
        if (this.canvasContainer) {
            this.canvasContainer.style.overflow = 'hidden';
        }
        
        // Apply direct dimensions
        this.iframe.style.width = `${settings.width}px`;
        this.iframe.style.height = 'auto';
        this.iframe.style.maxWidth = `${settings.width}px`;
        this.iframe.style.maxHeight = 'none';
        
        // Adjust height to content after load
        this.adjustIframeHeight();
    }
    
    /**
     * Calculate and apply zoom for desktop viewport
     */
    calculateAndApplyDesktopZoom() {
        if (this.currentDevice !== 'desktop') return;
        
        const containerWidth = this.canvasContainer.clientWidth;
        const padding = 60; // Account for padding/margins
        const availableWidth = containerWidth - padding;
        const desktopWidth = this.devices.desktop.width;
        
        // Calculate zoom to fit available space
        let zoom = Math.min(availableWidth / desktopWidth, 1);
        zoom = Math.max(zoom, 0.1); // Minimum 10% zoom
        
        // Update device zoom setting
        this.devices.desktop.zoom = zoom;
        
        // Apply zoom
        this.setDesktopZoomWithCentering(zoom);
        
        console.log(`🔍 Desktop zoom calculated: ${Math.round(zoom * 100)}%`);
    }
    
    /**
     * Set desktop zoom with centering
     */
    setDesktopZoomWithCentering(zoom) {
        if (this.currentDevice !== 'desktop') return;
        
        if (!this.iframe) {
            console.warn('⚠️ Iframe not available for zoom adjustment');
            return;
        }
        
        zoom = Math.max(0.1, Math.min(2, zoom)); // Clamp between 10% and 200%
        this.devices.desktop.zoom = zoom;
        
        const containerWidth = this.canvasContainer.clientWidth;
        const desktopWidth = this.devices.desktop.width;
        const scaledWidth = desktopWidth * zoom;
        const leftMargin = Math.max(0, (containerWidth - scaledWidth) / 2);
        
        // Apply transform scaling directly to iframe
        this.iframe.style.transform = `scale(${zoom})`;
        this.iframe.style.transformOrigin = 'top left';
        this.iframe.style.marginLeft = `${leftMargin}px`;
        
        console.log(`🔍 Desktop zoom set to: ${Math.round(zoom * 100)}%, Left margin: ${leftMargin}px`);
        
        // Update zoom display if available
        this.updateZoomDisplay(zoom);
    }
    
    /**
     * Setup zoom controls
     */
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
        
        // Zoom reset button
        const zoomResetBtn = document.querySelector('[data-action="zoom-reset"]');
        if (zoomResetBtn) {
            zoomResetBtn.addEventListener('click', () => {
                if (this.currentDevice === 'desktop') {
                    this.calculateAndApplyDesktopZoom();
                }
            });
        }
        
        // Zoom to fit button
        const zoomFitBtn = document.querySelector('[data-action="zoom-fit"]');
        if (zoomFitBtn) {
            zoomFitBtn.addEventListener('click', () => {
                if (this.currentDevice === 'desktop') {
                    this.calculateAndApplyDesktopZoom();
                }
            });
        }
    }
    
    /**
     * Setup resize handler for automatic zoom adjustment
     */
    setupResizeHandler() {
        const resizeObserver = new ResizeObserver(() => {
            if (this.currentDevice === 'desktop') {
                // Recalculate zoom when container size changes
                setTimeout(() => {
                    this.calculateAndApplyDesktopZoom();
                }, 100);
            }
        });
        
        if (this.canvasContainer) {
            resizeObserver.observe(this.canvasContainer);
        }
    }
    
    /**
     * Update zoom display in toolbar
     */
    updateZoomDisplay(zoom) {
        const zoomDisplay = document.querySelector('[data-zoom-display]');
        if (zoomDisplay) {
            zoomDisplay.textContent = `${Math.round(zoom * 100)}%`;
        }
    }
    
    /**
     * Get current zoom level
     */
    getCurrentZoom() {
        return this.currentDevice === 'desktop' ? this.devices.desktop.zoom : 1;
    }
    
    /**
     * Refresh the preview (recalculate zoom if needed)
     */
    refresh() {
        if (this.currentDevice === 'desktop') {
            this.calculateAndApplyDesktopZoom();
        }
        console.log('🔄 Device preview refreshed');
    }
}

// Export for global use
window.DevicePreview = DevicePreview;