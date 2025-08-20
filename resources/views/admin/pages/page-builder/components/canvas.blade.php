<!-- GridStack Page Builder Canvas -->
<div class="page-builder-canvas" id="canvasArea">
    <div class="canvas-header border-bottom p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="canvas-info">
                <span class="canvas-stats text-muted">
                    <span id="sectionCount">0</span> sections, 
                    <span id="widgetCount">0</span> widgets
                    <span id="loadingStatus" class="ms-2">• Ready</span>
                </span>
            </div>
            
            <div class="canvas-controls">
                <button class="btn btn-sm btn-outline-secondary me-2" id="toggleGridBtn" title="Toggle Grid">
                    <i class="ri-layout-grid-line"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary me-2" id="clearCanvasBtn" title="Clear All">
                    <i class="ri-delete-bin-line"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="canvasSettingsBtn" title="Canvas Settings">
                    <i class="ri-settings-line"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="canvas-wrapper p-3" style="background: #f8f9fa; min-height: calc(100vh - 160px);">
        <!-- GridStack Container -->
        <div class="grid-stack" id="gridStackContainer" data-page-id="{{ $page->id ?? '' }}">
            <!-- GridStack items will be added here dynamically -->
        </div>
        
        <!-- Empty State (when no sections) - Initially hidden -->
        <div class="empty-canvas-state text-center py-5" id="emptyCanvasState" style="display: none;">
            <div class="empty-icon mb-3">
                <i class="ri-layout-grid-line display-1 text-muted"></i>
            </div>
            <h5 class="text-muted">Start Building Your Page</h5>
            <p class="text-muted mb-4">Drag sections from the sidebar or click the button below to get started</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionTemplatesModal">
                <i class="ri-add-line me-2"></i>Add Your First Section
            </button>
        </div>
    </div>
</div>

<!-- GridStack Drop Zone Helper -->
<div id="gridstack-drop-preview" style="display: none; position: absolute; background: rgba(0,123,255,0.1); border: 2px dashed #007bff; z-index: 1000;"></div>