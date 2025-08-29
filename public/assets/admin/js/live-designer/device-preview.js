/**
 * Device Preview Manager
 * 
 * Manages responsive preview modes (desktop, tablet, mobile)
 */
class DevicePreview {
    constructor(previewContainer) {
        this.container = previewContainer;
        this.iframe = previewContainer.querySelector('iframe');
        this.canvasContainer = this.container.parentElement;
        this.currentDevice = 'desktop';
        
        this.devices = {
            desktop: { 
                width: 1520,
                height: 'auto',
                name: 'Desktop',
                viewportWidth: 1520,
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
    
    /**
     * Initialize device preview
     */
    init() {
        this.setupDeviceClasses();
        this.setupZoomControls();
        this.setupResizeHandler();
        this.setDevice('desktop'); // Default to desktop with zoom
        console.log('📱 Device Preview initialized');
    }
    
    /**
     * Set preview device
     */
    setDevice(device) {
        if (!this.devices[device]) {
            console.warn(`Unknown device: ${device}`);
            return;
        }
        
        this.currentDevice = device;
        const settings = this.devices[device];
        
        // Update container class
        this.container.className = `preview-container device-${device}`;
        
        // Apply device-specific styling
        if (device === 'desktop') {
            // For desktop, use zoom-based approach with fixed 1520px width
            this.setupDesktopViewport();
        } else {
            // For tablet/mobile, use direct dimension approach
            this.setupMobileViewport(device, settings);
        }
        
        // Add device frame if needed
        this.updateDeviceFrame(device);
        
        console.log(`📱 Device preview set to: ${device}`);
        
        // Notify iframe about device change
        this.notifyIframeDeviceChange(device);
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
     * Setup desktop viewport with zoom functionality
     */
    setupDesktopViewport() {
        const desktopSettings = this.devices.desktop;
        
        // Set iframe to fixed desktop width
        this.iframe.style.width = `${desktopSettings.width}px`;
        this.iframe.style.height = '100vh';
        this.iframe.style.maxWidth = 'none';
        this.iframe.style.maxHeight = 'none';
        
        // Calculate and apply zoom
        this.calculateAndApplyDesktopZoom();
    }
    
    /**
     * Setup mobile/tablet viewport (direct dimensions)
     */
    setupMobileViewport(device, settings) {
        // Remove any transform scaling for mobile/tablet
        this.iframe.style.transform = '';
        this.iframe.style.transformOrigin = '';
        this.container.style.overflow = 'hidden';
        
        // Apply direct dimensions
        this.iframe.style.width = `${settings.width}px`;
        this.iframe.style.height = `${settings.height}px`;
        this.iframe.style.maxWidth = `${settings.width}px`;
        this.iframe.style.maxHeight = `${settings.height}px`;
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
        
        zoom = Math.max(0.1, Math.min(2, zoom)); // Clamp between 10% and 200%
        this.devices.desktop.zoom = zoom;
        
        const containerWidth = this.canvasContainer.clientWidth;
        const desktopWidth = this.devices.desktop.width;
        const scaledWidth = desktopWidth * zoom;
        const leftMargin = Math.max(0, (containerWidth - scaledWidth) / 2);
        
        // Apply transform scaling
        this.iframe.style.transform = `scale(${zoom})`;
        this.iframe.style.transformOrigin = 'top left';
        this.iframe.style.marginLeft = `${leftMargin}px`;
        
        // Update container overflow
        this.container.style.overflow = 'visible';
        
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