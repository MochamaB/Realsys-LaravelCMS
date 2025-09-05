<!-- Page Builder Toolbar -->
<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent mb-0 ms-0 p-3">
    <div class="d-flex align-items-center">
        <h5 class="page-title mb-0">
            <i class="ri-brush-line me-2"></i>
            Page Builder
        </h5>
    </div>
    
    <!-- Device Preview Controls -->
    <div class="page-title-middle">
        <div class="btn-group" role="group" aria-label="Device preview">
            <input type="radio" class="btn-check" name="preview-mode" id="desktop-mode" checked>
            <label class="btn btn-outline-secondary" for="desktop-mode" title="Desktop" data-device="desktop">
                <i class="ri-computer-line"></i>
            </label>
            
            <input type="radio" class="btn-check" name="preview-mode" id="tablet-mode">
            <label class="btn btn-outline-secondary" for="tablet-mode" title="Tablet" data-device="tablet">
                <i class="ri-tablet-line"></i>
            </label>
            
            <input type="radio" class="btn-check" name="preview-mode" id="mobile-mode">
            <label class="btn btn-outline-secondary" for="mobile-mode" title="Mobile" data-device="mobile">
                <i class="ri-smartphone-line"></i>
            </label>
        </div>
        
    </div>
    
    <div class="page-title-right">
        <!-- Undo/Redo -->
        <button class="btn btn-outline-secondary  me-2" id="undo-btn" title="Undo">
            <i class="ri-arrow-go-back-line"></i>
        </button>
        
        <button class="btn btn-outline-secondary  me-2" id="redo-btn" title="Redo">
            <i class="ri-arrow-go-forward-line"></i>
        </button>
        
        <!-- Clear Canvas -->
        <button class="btn btn-outline-warning  me-2" id="clear-canvas" title="Clear Canvas">
            <i class="ri-delete-bin-line"></i>
        </button>
        
        <!-- Add Section Button -->
        <button class="btn btn-success me-2" id="add-section" data-bs-toggle="modal" data-bs-target="#sectionTemplatesModal" title="Add Section">
            <i class="ri-add-line"></i>
            Add Section
        </button>
        
        <!-- Preview Button -->
        <button class="btn btn-outline-primary  me-2" id="preview-page" title="Preview Page">
            <i class="ri-eye-line"></i>
            Preview
        </button>
        
        <!-- Save Button -->
        <button class="btn btn-success  me-2" id="save-page" title="Save Page">
            <i class="ri-save-line"></i>
            Save
        </button>
        
        <!-- Back to Pages -->
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary1" title="Back to Pages">
            <i class="ri-arrow-left-line"></i>
            Back
        </a>
    </div>
</div>
