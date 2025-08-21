/**
 * Device Preview Manager
 * 
 * Manages responsive preview modes (desktop, tablet, mobile)
 */
class DevicePreview {
    constructor(previewContainer) {
        this.container = previewContainer;
        this.iframe = previewContainer.querySelector('iframe');
        this.currentDevice = 'desktop';
        this.devices = {
            desktop: { 
                width: '100%', 
                height: '100%',
                name: 'Desktop',
                icon: 'ri-computer-line'
            },
            tablet: { 
                width: '768px', 
                height: '1024px',
                name: 'Tablet',
                icon: 'ri-tablet-line'
            },
            mobile: { 
                width: '375px', 
                height: '667px',
                name: 'Mobile',
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
        
        // Apply device styles
        if (device === 'desktop') {
            this.iframe.style.width = '100%';
            this.iframe.style.height = '100%';
            this.iframe.style.maxWidth = 'none';
            this.iframe.style.maxHeight = 'none';
        } else {
            this.iframe.style.width = settings.width;
            this.iframe.style.height = settings.height;
            this.iframe.style.maxWidth = settings.width;
            this.iframe.style.maxHeight = settings.height;
        }
        
        // Add device frame if needed
        this.updateDeviceFrame(device);
        
        console.log(`📱 Device preview set to: ${device} (${settings.width} x ${settings.height})`);
        
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
        
        // Update UI buttons
        document.querySelectorAll('.device-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.device === nextDevice);
        });
        
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
                        document.querySelector('[data-device="desktop"]')?.click();
                        break;
                    case '2':
                        e.preventDefault();
                        this.setDevice('tablet');
                        document.querySelector('[data-device="tablet"]')?.click();
                        break;
                    case '3':
                        e.preventDefault();
                        this.setDevice('mobile');
                        document.querySelector('[data-device="mobile"]')?.click();
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
        const message = `Current device: ${device.name} (${device.width} × ${device.height})`;
        
        if (window.livePreview) {
            window.livePreview.showMessage(message, 'info');
        }
    }
}

// Export for global use
window.DevicePreview = DevicePreview;