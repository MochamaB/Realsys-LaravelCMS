<!-- GrapesJS Live Designer Toolbar -->
<div class="live-designer-toolbar d-sm-flex align-items-center justify-content-between bg-galaxy-transparent mb-0 ms-0 p-3">
    <div class="d-flex align-items-center">
        <!-- Toggle Left Sidebar -->
        <button class="btn btn-outline-secondary me-2" id="toggle-left-sidebar" title="Toggle Component Library">
            <i class="ri-layout-left-2-line"></i>
        </button>
        
        <!-- Toggle Right Sidebar -->
        <button class="btn btn-outline-secondary me-3" id="toggle-right-sidebar" title="Toggle Properties Panel">
            <i class="ri-layout-right-2-line"></i>
        </button>
        
        <h4 class="page-title mb-0">
            <i class="ri-brush-line me-2"></i>
            Live Designer - {{ $page->title }}
        </h4>
    </div>
    
    <!-- Device Preview Controls -->
    <div class="page-title-middle">
        <div class="btn-group" role="group" aria-label="Device preview">
            <input type="radio" class="btn-check" name="preview-mode" id="desktop-mode" checked>
            <label class="btn btn-outline-secondary btn-sm" for="desktop-mode" title="Desktop">
                <i class="ri-computer-line"></i>
            </label>
            
            <input type="radio" class="btn-check" name="preview-mode" id="tablet-mode">
            <label class="btn btn-outline-secondary btn-sm" for="tablet-mode" title="Tablet">
                <i class="ri-tablet-line"></i>
            </label>
            
            <input type="radio" class="btn-check" name="preview-mode" id="mobile-mode">
            <label class="btn btn-outline-secondary btn-sm" for="mobile-mode" title="Mobile">
                <i class="ri-smartphone-line"></i>
            </label>
        </div>
    </div>
    
    <div class="page-title-right">
        <!-- Undo/Redo -->
        <button class="btn btn-outline-secondary btn-sm me-2" id="undo-btn" title="Undo">
            <i class="ri-arrow-go-back-line"></i>
        </button>
        
        <button class="btn btn-outline-secondary btn-sm me-2" id="redo-btn" title="Redo">
            <i class="ri-arrow-go-forward-line"></i>
        </button>
        
        <!-- Clear Canvas -->
        <button class="btn btn-outline-warning btn-sm me-2" id="clear-canvas" title="Clear Canvas">
            <i class="ri-delete-bin-line"></i>
        </button>
        
        <!-- Preview Button -->
        <button class="btn btn-outline-primary btn-sm me-2" id="preview-page" title="Preview Page">
            <i class="ri-eye-line"></i>
            Preview
        </button>
        
        <!-- Save Button -->
        <button class="btn btn-success btn-sm me-2" id="save-page" title="Save Page">
            <i class="ri-save-line"></i>
            Save
        </button>
        
        <!-- Back to Pages -->
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm" title="Back to Pages">
            <i class="ri-arrow-left-line"></i>
            Back
        </a>
    </div>
</div>
