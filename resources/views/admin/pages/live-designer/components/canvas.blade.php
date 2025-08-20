<!-- Live Designer Canvas Area -->
<div class="live-designer-canvas" id="canvas-area">
    <!-- Loading State -->
    <div class="live-designer-loading" id="canvas-loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Initializing Live Designer...</div>
    </div>
    
    <!-- GrapesJS Editor Container -->
    <div id="gjs-editor"></div>
    
    <!-- Canvas Overlay for Debugging -->
    <div class="canvas-debug-overlay d-none" id="canvas-debug">
        <div class="debug-info">
            <h6>Canvas Debug Info</h6>
            <div class="debug-stats">
                <div>Canvas Size: <span id="canvas-size">-</span></div>
                <div>Components: <span id="component-count">0</span></div>
                <div>Selected: <span id="selected-component">None</span></div>
                <div>Device: <span id="current-device">Desktop</span></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Canvas specific styles */
.live-designer-canvas {
    position: relative;
    background: #f8f9fa;
    overflow: hidden;
}

.live-designer-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 10;
}

#gjs-editor {
    height: 100%;
    width: 100%;
}

/* Debug overlay styles */
.canvas-debug-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px;
    border-radius: 5px;
    font-size: 12px;
    z-index: 1000;
    min-width: 200px;
}

.canvas-debug-overlay h6 {
    color: #fff;
    margin-bottom: 8px;
    font-size: 14px;
}

.debug-stats div {
    margin-bottom: 4px;
}

.debug-stats span {
    color: #17a2b8;
    font-weight: bold;
}

/* Canvas responsive states */
.canvas-device-desktop {
    max-width: 100%;
}

.canvas-device-tablet {
    max-width: 768px;
    margin: 0 auto;
}

.canvas-device-mobile {
    max-width: 375px;
    margin: 0 auto;
}

/* GrapesJS canvas customization */
#gjs-editor .gjs-cv-canvas {
    background: #fff;
    border: 1px solid #e9ecef;
}

#gjs-editor .gjs-frame {
    border: none;
}

/* Loading animation */
.live-designer-loading .spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
