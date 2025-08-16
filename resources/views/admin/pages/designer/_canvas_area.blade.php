<!-- Canvas Area - Main Preview -->
<div class="designer-canvas-area" id="canvasArea">
    <div class="canvas-toolbar">
        <div class="canvas-controls">
            <button class="btn btn-sm btn-outline-secondary" id="addSectionBtn">
                <i class="ri-add-line"></i> Add Section
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="clearCanvasBtn">
                <i class="ri-delete-bin-line"></i> Clear All
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="toggleGridBtn">
                <i class="ri-layout-grid-line"></i> Grid
            </button>
        </div>
        
        <div class="canvas-info">
            <span class="canvas-stats">
                <span id="sectionCount">0</span> sections, 
                <span id="widgetCount">0</span> widgets
                <span id="loadingStatus" class="text-muted ms-2">• Loading...</span>
                <span id="debugInfo" class="text-muted ms-2" style="font-size: 11px;"></span>
            </span>
        </div>
    </div>
    
    <div class="canvas-wrapper">
        <div class="page-sections-container" id="pageSectionsContainer" data-page-id="{{ $page->id ?? '' }}">
            <!-- Sections loaded dynamically -->
            <div class="section-add-zone" id="addFirstSection">
                <div class="section-add-prompt">
                    <i class="ri-add-line"></i>
                    <span>Click to add your first section</span>
                </div>
            </div>
        </div>
    </div>
</div> 