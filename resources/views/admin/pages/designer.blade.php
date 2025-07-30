<!-- GrapesJS Live Preview Designer -->
<div class="grapesjs-preview-container">
    <div class="preview-header bg-light border-bottom p-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="ri-eye-line me-2"></i>
                Live Preview Editor
            </h6>
            <div class="preview-controls">
                <button class="btn btn-sm btn-outline-secondary" id="refreshPreviewBtn" title="Refresh Preview">
                    <i class="ri-refresh-line"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" id="fullscreenPreviewBtn" title="Fullscreen Preview">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- GrapesJS Toolbar -->
    <div class="toolbar bg-white border-bottom p-2 d-flex justify-content-between align-items-center">
        <!-- Basic Actions Panel -->
        <div class="panel__basic-actions d-flex gap-2">
            <!-- GrapesJS will populate this -->
        </div>
        
        <!-- Device Panel -->
        <div class="panel__devices d-flex gap-2">
            <!-- GrapesJS will populate this -->
        </div>
    </div>
    
    <!-- GrapesJS Canvas Container -->
    <div class="preview-canvas-container" style="height: calc(100vh - 320px); overflow: hidden;">
        <!-- GrapesJS Canvas -->
        <div id="gjs" 
             data-page-id="{{ $page->id }}" 
             style="width: 100%; height: 100%; overflow: auto;">
        </div>
    </div>
</div>