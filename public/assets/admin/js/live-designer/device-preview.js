/**
 * Device Preview Manager - With PageBuilder-style Intelligent Scaling
 * 
 * Manages responsive preview modes with automatic scaling and container adaptation
 */
class DevicePreview {
    constructor(options = {}) {
        // Handle both old constructor (canvasContainer, iframe) and new options object
        if (options.container || options.iframe) {
            this.canvasContainer = options.container || document.getElementById('canvasContainer');
            this.iframe = options.iframe || document.getElementById('preview-iframe');
        } else {
            // Legacy support for direct parameters
            this.canvasContainer = options || document.getElementById('canvasContainer');
            this.iframe = arguments[1] || document.getElementById('preview-iframe');
        }
        
        this.currentDevice = 'desktop';
        this.currentScale = 1; // Track current scale
        this.initialized = false;
        
        // Device configurations with actual dimensions matching PageBuilder
        this.deviceConfigs = {
            desktop: {
                width: 1440,
                height: 900,
                scale: 'auto', // Will be calculated based on container
                label: 'Desktop'
            },
            tablet: {
                width: 768,
                height: 1024,
                scale: 1,
                label: 'Tablet'
            },
            mobile: {
                width: 375,
                height: 812,
                scale: 1,
                label: 'Mobile'
            }
        };
        
        console.log('📱 Device Preview with Intelligent Scaling initialized');
    }
    
    /**
     * Initialize device preview functionality
     */
    init() {
        if (!this.iframe) {
            console.warn('⚠️ Preview iframe not found');
            return;
        }

        if (!this.canvasContainer) {
            console.warn('⚠️ Canvas container not found');
            return;
        }

        this.setupDeviceControls();
        this.setupResizeObserver();
        this.setDevice('desktop'); // Set initial device
        
        this.initialized = true;
        console.log('📱 Device preview with intelligent scaling initialized');
    }

    /**
     * Setup device control buttons
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
                
                // Log the current scale and zoom for debugging
                console.log(`📱 Device changed to: ${device}`);
                console.log(`📊 Current scale: ${this.getCurrentScale()}`);
                console.log(`🔍 Current zoom: ${this.getCurrentZoom()}`);
            });
        });
        
        // Set initial active state
        const desktopButton = document.querySelector('[data-device="desktop"]');
        if (desktopButton) {
            desktopButton.classList.add('active');
        }
        
        console.log('📱 Device controls setup complete');
    }

    /**
     * Setup resize observer to recalculate scaling when container size changes
     */
    setupResizeObserver() {
        if (window.ResizeObserver && this.canvasContainer) {
            const resizeObserver = new ResizeObserver(() => {
                this.applyDeviceScaling(this.currentDevice);
            });
            
            resizeObserver.observe(this.canvasContainer);
        } else {
            // Fallback for browsers without ResizeObserver or missing container
            window.addEventListener('resize', () => {
                this.applyDeviceScaling(this.currentDevice);
            });
        }
    }
    
    /**
     * Set device preview mode with intelligent scaling
     */
    setDevice(deviceType) {
        if (!this.iframe || !this.deviceConfigs[deviceType]) return;

        this.currentDevice = deviceType;

        // Remove all device classes
        this.iframe.classList.remove('device-desktop', 'device-tablet', 'device-mobile');
        
        // Add current device class
        this.iframe.classList.add(`device-${deviceType}`);

        // Apply scaling and dimensions
        this.applyDeviceScaling(deviceType);

        // Update device button states
        this.updateDeviceButtons(deviceType);

        // Notify iframe of device change
        this.notifyIframeDeviceChange(deviceType);

        console.log(`📱 Device switched to: ${deviceType} with intelligent scaling`);
        console.log(`📊 Applied scale: ${this.currentScale}`);
    }

    /**
     * Apply device-specific scaling and dimensions with PageBuilder logic
     */
    applyDeviceScaling(deviceType) {
        const config = this.deviceConfigs[deviceType];
        if (!config) return;

        const containerWidth = this.canvasContainer.clientWidth - 40; // Account for padding
        const containerHeight = this.canvasContainer.clientHeight - 40;

        let scale = 1;
        let displayWidth = config.width;
        let displayHeight = config.height;

        if (deviceType === 'desktop') {
            // For desktop, scale down to fit container while maintaining aspect ratio
            const scaleX = containerWidth / config.width;
            const scaleY = containerHeight / config.height;
            scale = Math.min(scaleX, scaleY, 1); // Don't scale up, only down
            
            // Minimum scale to keep it readable
            scale = Math.max(scale, 0.3);
            
            displayWidth = config.width;
            displayHeight = config.height;
        } else {
            // For tablet and mobile, use actual size or scale to fit if too large
            if (config.width > containerWidth) {
                scale = containerWidth / config.width;
                scale = Math.max(scale, 0.5); // Minimum scale
            }
            
            displayWidth = config.width;
            displayHeight = config.height;
        }

        // Store the current scale
        this.currentScale = scale;

        // Calculate proper centering
        const scaledWidth = displayWidth * scale;
        const scaledHeight = displayHeight * scale;
        const availableWidth = this.canvasContainer.clientWidth;
        const leftMargin = Math.max(0, (availableWidth - scaledWidth) / 2);

        // Apply styles to iframe with smooth transitions and proper centering
        this.iframe.style.width = `${displayWidth}px`;
        this.iframe.style.transform = `scale(${scale})`;
        this.iframe.style.transformOrigin = 'top left';
        this.iframe.style.transition = 'all 0.3s ease';
        this.iframe.style.marginLeft = `${leftMargin}px`;
        this.iframe.style.marginRight = 'auto';
        this.iframe.style.marginTop = '20px';
        this.iframe.style.marginBottom = '20px';
        this.iframe.style.display = 'block';
        
        // Set up dynamic height adjustment
        this.setupDynamicHeight();

        // Add device info display
        this.updateDeviceInfo(deviceType, displayWidth, scale);

        console.log(`📱 Applied scaling for ${deviceType}: ${displayWidth}px width, ${scale.toFixed(2)}x scale`);
    }

    /**
     * Setup dynamic height adjustment for iframe content
     */
    setupDynamicHeight() {
        // Prevent multiple setups
        if (this.heightSetupComplete) {
            return;
        }
        
        // Remove any existing load listeners to prevent duplicates
        if (this.handleIframeLoad) {
            this.iframe.removeEventListener('load', this.handleIframeLoad);
        }
        
        // Bind the method to preserve 'this' context
        this.handleIframeLoad = this.handleIframeLoad.bind(this);
        
        // Add load listener for dynamic height calculation
        this.iframe.addEventListener('load', this.handleIframeLoad);
        
        // Mark setup as complete
        this.heightSetupComplete = true;
        
        // Also try to calculate height immediately if iframe is already loaded
        if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
            this.calculateAndSetHeight();
        }
    }
    
    /**
     * Handle iframe load event
     */
    handleIframeLoad() {
        // Wait a bit for content to fully render
        setTimeout(() => {
            this.calculateAndSetHeight();
        }, 100);
    }
    
    /**
     * Calculate and set iframe height based on content
     */
    calculateAndSetHeight() {
        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
            if (!iframeDoc || !iframeDoc.body) {
                console.warn('⚠️ Cannot access iframe content for height calculation');
                return;
            }
            
            // Calculate the actual content height
            const contentHeight = Math.max(
                iframeDoc.body.scrollHeight,
                iframeDoc.body.offsetHeight,
                iframeDoc.documentElement.clientHeight,
                iframeDoc.documentElement.scrollHeight,
                iframeDoc.documentElement.offsetHeight
            );
            
            // Add some padding for safety
            const finalHeight = contentHeight + 40;
            
            // Only update if height has changed significantly (prevent continuous updates)
            const currentHeight = parseInt(this.iframe.style.height) || 0;
            if (Math.abs(finalHeight - currentHeight) > 10) {
                // Set the iframe height
                this.iframe.style.height = `${finalHeight}px`;
                
                console.log(`📏 Dynamic height updated: ${finalHeight}px (was: ${currentHeight}px, content: ${contentHeight}px)`);
                
                // Cache the height to prevent unnecessary recalculations
                this.lastCalculatedHeight = finalHeight;
                
                // Trigger a resize event for any listeners
                window.dispatchEvent(new Event('resize'));
            }
            
        } catch (error) {
            console.warn('⚠️ Cross-origin restrictions prevent height calculation, using fallback');
            // Fallback height for cross-origin iframes
            if (!this.iframe.style.height || this.iframe.style.height === 'auto') {
                this.iframe.style.height = '800px';
            }
        }
    }

    /**
     * Update device button states
     */
    updateDeviceButtons(deviceType) {
        const deviceButtons = document.querySelectorAll('[data-device]');
        deviceButtons.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-device') === deviceType);
        });
    }

    /**
     * Update device info display in toolbar
     */
    updateDeviceInfo(deviceType, width, scale) {
        const config = this.deviceConfigs[deviceType];
        
        // Find device button container - could be in various locations
        const deviceContainer = document.querySelector('.device-controls, .btn-group, [data-device]')?.parentElement;
        if (!deviceContainer) {
            console.warn('⚠️ Device button container not found');
            return;
        }

        // Remove any existing device info
        const existingInfo = deviceContainer.querySelector('.device-info-active');
        if (existingInfo) {
            existingInfo.remove();
        }

        // Only show info for desktop with scaling or when scale !== 1
        if (deviceType === 'desktop' && scale < 0.99) {
            const deviceInfo = document.createElement('div');
            deviceInfo.className = 'device-info-active badge bg-primary ms-2';
            deviceInfo.title = `${config.label}: ${width}px at ${Math.round(scale * 100)}% zoom`;
            deviceInfo.textContent = `${Math.round(scale * 100)}%`;
            
            deviceContainer.appendChild(deviceInfo);
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
                    settings: this.deviceConfigs[device],
                    scale: this.currentScale
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
     * Get current scale factor
     */
    getCurrentScale() {
        return this.currentScale;
    }

    /**
     * Get current zoom level (alias for getCurrentScale for compatibility)
     */
    getCurrentZoom() {
        return this.currentScale;
    }

    /**
     * Get zoom percentage
     */
    getZoomPercentage() {
        return Math.round(this.currentScale * 100);
    }

    /**
     * Get device settings
     */
    getDeviceSettings(device = null) {
        device = device || this.currentDevice;
        return this.deviceConfigs[device];
    }

    /**
     * Manually trigger a recalculation (useful for dynamic changes)
     */
    recalculateScaling() {
        this.applyDeviceScaling(this.currentDevice);
    }

    /**
     * Get actual iframe dimensions and scale
     */
    getIframeDimensions() {
        const rect = this.iframe.getBoundingClientRect();
        const config = this.deviceConfigs[this.currentDevice];
        
        return {
            actual: { width: config.width, height: config.height },
            displayed: { width: rect.width, height: rect.height },
            scale: this.currentScale,
            zoomPercentage: this.getZoomPercentage()
        };
    }

    /**
     * Set zoom level manually
     */
    setZoom(zoomLevel) {
        this.currentScale = zoomLevel;
        const config = this.deviceConfigs[this.currentDevice];
        
        // Apply the zoom
        this.iframe.style.transform = `scale(${zoomLevel})`;
        
        // Update device info
        this.updateDeviceInfo(this.currentDevice, config.width, zoomLevel);
        
        console.log(`🔍 Manual zoom set to: ${Math.round(zoomLevel * 100)}%`);
    }
}

// Export for global use
window.DevicePreview = DevicePreview;