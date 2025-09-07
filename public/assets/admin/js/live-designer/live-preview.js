/**
 * Minimal Live Preview Class - Device Preview Integration Only
 * 
 * Simplified version that only handles device preview functionality
 * since the actual preview logic is in LivePreviewController
 */
class LivePreview {
    constructor(options) {
        this.options = {
            pageId: null,
            apiUrl: '/admin/api/live-preview',
            csrfToken: '',
            previewIframe: null,
            ...options
        };
        
        this.isInitialized = false;
        
        // Initialize parent communicator for new selection system
        this.parentCommunicator = null;
        
        // Device preview integration
        this.canvasContainer = document.getElementById('canvasContainer');
        this.devicePreview = null;
        
        this.init();
    }
    
    /**
     * Initialize the live preview system
     */
    async init() {
        try {
            // Initialize parent communicator first
            if (typeof ParentCommunicator !== 'undefined') {
                this.parentCommunicator = new ParentCommunicator(this);
                
                // Set iframe reference for parent communicator
                if (this.options.previewIframe) {
                    this.parentCommunicator.setIframe(this.options.previewIframe);
                }
            }
            
            // Initialize device preview functionality only if container exists
            if (this.canvasContainer) {
                this.setupDevicePreview();
            } else {
                console.warn('⚠️ Canvas container not found - device preview disabled');
            }
            
            this.isInitialized = true;
            
            console.log('✅ Live Preview initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize Live Preview:', error);
        }
    }
    
    /**
     * Callback for when iframe is ready (called by parent communicator)
     * @param {object} data - Iframe ready data
     */
    onIframeReady(data) {
        console.log('🔗 Iframe ready callback received:', data);
        
        // Sync any initial state with iframe
        this.syncInitialState();
    }
    
    /**
     * Sync initial state with iframe
     */
    syncInitialState() {
        // Sync device/zoom settings if device preview is available
        if (this.devicePreview) {
            const currentDevice = this.devicePreview.getCurrentDevice();
            const zoomLevel = this.devicePreview.getCurrentZoom();
            
            console.log(`🔄 Syncing initial state - Device: ${currentDevice}, Zoom: ${Math.round(zoomLevel * 100)}%`);
            
            if (this.parentCommunicator) {
                this.parentCommunicator.updateZoom(zoomLevel);
            }
        }
    }
    
    /**
     * Setup device preview functionality
     */
    setupDevicePreview() {
        // Initialize DevicePreview class with proper options object
        this.devicePreview = new DevicePreview({
            iframe: this.options.previewIframe,
            container: this.canvasContainer
        });
        
        // Initialize device preview
        this.devicePreview.init();
        
        // Set up event listener for device changes
        this.setupDeviceChangeListener();
        
        console.log('📱 Device preview initialized with DevicePreview class');
    }
    
    /**
     * Setup device change listener to track scale changes
     */
    setupDeviceChangeListener() {
        // Listen for device button clicks to track changes
        const deviceButtons = document.querySelectorAll('[data-device]');
        
        deviceButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Use setTimeout to ensure the device change has been processed
                setTimeout(() => {
                    const currentDevice = this.getCurrentDevice();
                    const currentZoom = this.getZoomLevel();
                    const currentScale = this.devicePreview.getCurrentScale();
                    
                    console.log('📊 Device Change Event:');
                    console.log(`  Device: ${currentDevice}`);
                    console.log(`  Scale: ${currentScale}`);
                    console.log(`  Zoom: ${Math.round(currentZoom * 100)}%`);
                    
                    // Sync with parent communicator if available
                    if (this.parentCommunicator) {
                        this.parentCommunicator.updateZoom(currentZoom);
                    }
                    
                    // Custom event for other parts of the application
                    this.triggerDeviceChangeEvent(currentDevice, currentScale, currentZoom);
                }, 100);
            });
        });
    }
    
    /**
     * Trigger custom device change event
     */
    triggerDeviceChangeEvent(device, scale, zoom) {
        const event = new CustomEvent('deviceChanged', {
            detail: {
                device: device,
                scale: scale,
                zoom: zoom,
                zoomPercentage: Math.round(zoom * 100)
            }
        });
        
        document.dispatchEvent(event);
        
        // Also trigger on window for global listening
        window.dispatchEvent(new CustomEvent('livePreviewDeviceChanged', {
            detail: {
                device: device,
                scale: scale,
                zoom: zoom,
                zoomPercentage: Math.round(zoom * 100)
            }
        }));
    }
    
    /**
     * Set preview device
     */
    setDevice(device) {
        if (this.devicePreview) {
            this.devicePreview.setDevice(device);
            
            // Sync with parent communicator
            if (this.parentCommunicator) {
                const zoomLevel = this.devicePreview.getCurrentZoom();
                this.parentCommunicator.updateZoom(zoomLevel);
            }
            
            console.log(`📱 Device set to: ${device}`);
        }
    }
    
    /**
     * Get current device
     */
    getCurrentDevice() {
        return this.devicePreview ? this.devicePreview.getCurrentDevice() : 'desktop';
    }
    
    /**
     * Get current zoom level
     */
    getZoomLevel() {
        return this.devicePreview ? this.devicePreview.getCurrentZoom() : 1;
    }
    
    /**
     * Get current scale (alias for getZoomLevel)
     */
    getCurrentScale() {
        return this.devicePreview ? this.devicePreview.getCurrentScale() : 1;
    }
    
    /**
     * Get zoom percentage
     */
    getZoomPercentage() {
        return this.devicePreview ? this.devicePreview.getZoomPercentage() : 100;
    }
    
    /**
     * Get detailed device and zoom information
     */
    getDeviceInfo() {
        if (!this.devicePreview) {
            return {
                device: 'desktop',
                scale: 1,
                zoom: 1,
                zoomPercentage: 100
            };
        }
        
        return {
            device: this.devicePreview.getCurrentDevice(),
            scale: this.devicePreview.getCurrentScale(),
            zoom: this.devicePreview.getCurrentZoom(),
            zoomPercentage: this.devicePreview.getZoomPercentage(),
            dimensions: this.devicePreview.getIframeDimensions()
        };
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        const container = document.getElementById('message-container');
        if (!container) return;
        
        const alertType = this.getBootstrapAlertType(type);
        const alertId = 'alert-' + Date.now();
        
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${alertType} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    /**
     * Get Bootstrap alert type from message type
     */
    getBootstrapAlertType(type) {
        const mapping = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return mapping[type] || 'info';
    }
    
    /**
     * Check if system is ready
     */
    isSystemReady() {
        return this.isInitialized && this.devicePreview && this.devicePreview.initialized;
    }
    
    /**
     * Cleanup method
     */
    destroy() {
        if (this.parentCommunicator) {
            this.parentCommunicator.destroy();
        }
        
        this.isInitialized = false;
        
        console.log('🧹 Live Preview destroyed');
    }
}

// Export for global use
window.LivePreview = LivePreview;