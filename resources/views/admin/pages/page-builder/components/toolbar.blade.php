<!-- GridStack Page Builder Toolbar -->
<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent mb-0 ms-0 p-3">
    <div class="d-flex align-items-center">
        <!-- Toggle sidebar button -->
        <button class="btn btn-outline-secondary me-2" id="toggleLeftSidebarBtn" title="Toggle Widget Library">
            <i class="ri-apps-line"></i>
        </button>
        
        <h4 class="page-title mb-0">
            <i class="ri-layout-grid-line me-2"></i>
            {{ $page->title }}
        </h4>
    </div>
    
    <!-- Device Preview Controls (for responsive testing) -->
    <div class="page-title-middle">
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-secondary active" data-device="desktop" title="Desktop">
                <i class="ri-computer-line"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-device="tablet" title="Tablet">
                <i class="ri-tablet-line"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-device="mobile" title="Mobile">
                <i class="ri-smartphone-line"></i>
            </button>
        </div>
    </div>
    
    <div class="page-title-right">
        <!-- Add Section Button -->
        <button class="btn btn-success me-2" id="addSectionBtn" data-bs-toggle="modal" data-bs-target="#sectionTemplatesModal">
            <i class="ri-add-line"></i> Add Section
        </button>
        
        <!-- Preview Button -->
        <button class="btn btn-outline-secondary me-2" id="fullPreviewBtn" title="Full Preview" data-bs-toggle="modal" data-bs-target="#responsivePreviewModal">
            <i class="ri-external-link-line"></i>
        </button>
        
        <!-- Save Button -->
        <button class="btn btn-primary me-2" id="savePageBtn">
            <i class="ri-save-line"></i> Save
        </button>
        
        <!-- Back to Pages -->
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back"></i> Back to Pages
        </a>
    </div>
</div>