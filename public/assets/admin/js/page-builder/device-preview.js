/**
 * Page Builder Device Preview - With Zoom Scaling
 * 
 * Handles device switching with proper zoom scaling to show actual device dimensions
 */
class PageBuilderDevicePreview {
    constructor() {
        this.iframe = null;
        this.currentDevice = 'desktop';
        this.canvasContainer = null;
        
        // Device configurations with actual dimensions and scaling
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
        
        console.log('📱 Page Builder Device Preview - With Zoom Scaling initialized');
    }

    /**
     * Initialize device preview functionality
     */
    init() {
        this.iframe = document.getElementById('pageBuilderPreviewIframe');
        this.canvasContainer = document.getElementById('canvasContainer');
        
        if (!this.iframe) {
            console.warn('⚠️ Page Builder iframe not found');
            return;
        }

        if (!this.canvasContainer) {
            console.warn('⚠️ Canvas container not found');
            return;
        }

        this.setupDeviceControls();
        this.setupResizeObserver();
        this.setDevice('desktop'); // Set initial device
        
        console.log('📱 Device preview with zoom scaling initialized');
    }

    /**
     * Setup device control buttons
     */
    setupDeviceControls() {
        const deviceRadios = document.querySelectorAll('input[name="preview-mode"]');
        
        deviceRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    const label = document.querySelector(`label[for="${e.target.id}"]`);
                    if (label) {
                        const device = label.getAttribute('data-device');
                        if (device) {
                            this.setDevice(device);
                        }
                    }
                }
            });
        });

        const deviceLabels = document.querySelectorAll('label[data-device]');
        deviceLabels.forEach(label => {
            label.addEventListener('click', (e) => {
                const device = label.getAttribute('data-device');
                this.setDevice(device);
            });
        });
        
        console.log(`📱 Device controls setup complete`);
    }

    /**
     * Setup resize observer to recalculate scaling when container size changes
     */
    setupResizeObserver() {
        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver(() => {
                // Recalculate scaling for current device
                this.applyDeviceScaling(this.currentDevice);
            });
            
            resizeObserver.observe(this.canvasContainer);
        } else {
            // Fallback for browsers without ResizeObserver
            window.addEventListener('resize', () => {
                this.applyDeviceScaling(this.currentDevice);
            });
        }
    }

    /**
     * Set device preview mode with zoom scaling
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

        // Update radio button
        this.updateRadioButton(deviceType);

        console.log(`📱 Device switched to: ${deviceType} with scaling`);
    }

    /**
     * Apply device-specific scaling and dimensions
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

        // Apply styles to iframe
        this.iframe.style.width = `${displayWidth}px`;
        this.iframe.style.height = `${displayHeight}px`;
        this.iframe.style.transform = `scale(${scale})`;
        this.iframe.style.transformOrigin = 'top center';
        this.iframe.style.transition = 'all 0.3s ease';

        // Update container to accommodate scaled content
        const scaledHeight = displayHeight * scale;
        this.iframe.style.marginBottom = `${Math.max(0, (displayHeight - scaledHeight))}px`;

        // Add device info display
        this.updateDeviceInfo(deviceType, displayWidth, scale);

        console.log(`📱 Applied scaling for ${deviceType}: ${displayWidth}px width, ${scale.toFixed(2)}x scale`);
    }

    /**
     * Update device info display in toolbar by replacing active device button
     */
    updateDeviceInfo(deviceType, width, scale) {
        const config = this.deviceConfigs[deviceType];
        
        // Find the btn-group container
        const btnGroup = document.querySelector('.page-title-middle .btn-group');
        if (!btnGroup) {
            console.warn('⚠️ Device button group not found in toolbar');
            return;
        }

        // Show all device buttons first
        const allLabels = btnGroup.querySelectorAll('label[data-device]');
        const allInputs = btnGroup.querySelectorAll('input[name="preview-mode"]');
        
        allLabels.forEach(label => {
            label.style.display = 'inline-flex';
        });
        allInputs.forEach(input => {
            input.style.display = 'block';
        });

        // Remove any existing device info
        const existingInfo = btnGroup.querySelector('.device-info-active');
        if (existingInfo) {
            existingInfo.remove();
        }

        // Find and hide the active device's input and label
        const activeLabel = btnGroup.querySelector(`label[data-device="${deviceType}"]`);
        const activeInput = btnGroup.querySelector(`#${activeLabel?.getAttribute('for')}`);
        
        if (activeLabel && activeInput) {
            // Hide the active device's button
            activeLabel.style.display = 'none';
            activeInput.style.display = 'none';

            // Create device info display to replace the active button
            const deviceInfo = document.createElement('div');
            deviceInfo.className = 'device-info-active btn btn-primary d-flex align-items-center gap-2';
            deviceInfo.style.cursor = 'pointer';
            deviceInfo.title = `Current: ${config.label} (${width}px${scale !== 1 ? ', ' + Math.round(scale * 100) + '%' : ''})`;
            deviceInfo.innerHTML = `
                <i class="ri-${this.getDeviceIcon(deviceType)}"></i>
                <span class="fw-medium">${config.label}</span>
                <span class="small">${width}px</span>
                ${scale !== 1 ? `<span class="badge bg-light text-dark ms-1">${Math.round(scale * 100)}%</span>` : ''}
            `;

            // Make it clickable to show device options or settings
            deviceInfo.addEventListener('click', () => {
                // Could open device settings or cycle through devices
                console.log(`📱 Current device: ${config.label}`);
            });

            // Insert the device info where the active button was
            activeLabel.parentNode.insertBefore(deviceInfo, activeLabel);
        }
    }

    /**
     * Get device icon class
     */
    getDeviceIcon(deviceType) {
        const icons = {
            desktop: 'computer-line',
            tablet: 'tablet-line',
            mobile: 'smartphone-line'
        };
        return icons[deviceType] || 'computer-line';
    }

    /**
     * Update radio button state programmatically
     */
    updateRadioButton(deviceType) {
        const targetRadio = document.querySelector(`label[data-device="${deviceType}"]`);
        if (targetRadio) {
            const radioId = targetRadio.getAttribute('for');
            const radioInput = document.getElementById(radioId);
            if (radioInput && !radioInput.checked) {
                radioInput.checked = true;
            }
        }
    }

    /**
     * Get current device configuration
     */
    getCurrentDevice() {
        return {
            type: this.currentDevice,
            config: this.deviceConfigs[this.currentDevice]
        };
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
        const scale = parseFloat(this.iframe.style.transform.replace(/scale\(([^)]+)\)/, '$1')) || 1;
        
        return {
            actual: { width: config.width, height: config.height },
            displayed: { width: rect.width, height: rect.height },
            scale: scale
        };
    }
}

// Export for global access
window.PageBuilderDevicePreview = PageBuilderDevicePreview;

console.log('📱 Page Builder Device Preview with Zoom Scaling loaded');