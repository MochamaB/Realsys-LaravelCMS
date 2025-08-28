<!-- GridStack Page Builder Toolbar -->
<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent mb-0 ms-0 p-3 border-bottom: 1px solid #e9ecef;">
    <div class="d-flex align-items-center">
        
        
        <h4 class="page-title mb-0">
            <i class="ri-layout-grid-line me-2"></i>
            {{ $page->title }}
        </h4>
    </div>
    
    <!-- Device Preview Controls (for responsive testing) -->
    <div class="page-title-middle">
                <!-- Device Preview Controls -->
        <div class="btn-group" role="group" aria-label="Device Preview">
            <!-- Desktop Mode -->
            <input type="radio" class="btn-check" name="device-mode" id="desktop-mode" value="desktop" checked>
            <label class="btn btn-outline-primary" for="desktop-mode" title="Desktop Preview (Ctrl+1)">
                <i class="ri-computer-line"></i>
                <span class="d-none d-md-inline ms-1">Desktop</span>
                <span class="zoom-indicator d-none d-lg-inline ms-1" id="zoom-level">100%</span>
            </label>
            
            <!-- Tablet Mode -->
            <input type="radio" class="btn-check" name="device-mode" id="tablet-mode" value="tablet">
            <label class="btn btn-outline-primary" for="tablet-mode" title="Tablet Preview (Ctrl+2)">
                <i class="ri-tablet-line"></i>
                <span class="d-none d-md-inline ms-1">Tablet</span>
            </label>
            
            <!-- Mobile Mode -->
            <input type="radio" class="btn-check" name="device-mode" id="mobile-mode" value="mobile">
            <label class="btn btn-outline-primary" for="mobile-mode" title="Mobile Preview (Ctrl+3)">
                <i class="ri-smartphone-line"></i>
                <span class="d-none d-md-inline ms-1">Mobile</span>
            </label>
        </div>
        <!-- Optional: Zoom Controls for Desktop -->
        
    </div>
    
    <div class="page-title-right">
    <div class="btn-group ms-2" role="group" aria-label="Zoom Controls" id="zoom-controls">
            <button type="button" class="btn btn-outline-secondary " data-action="zoom-out" title="Zoom Out">
                <i class="ri-zoom-out-line"></i>
            </button>
            
            <button type="button" class="btn btn-outline-secondary " data-action="zoom-fit" title="Zoom to Fit">
                <i class="ri-focus-3-line"></i>
            </button>
            
            <button type="button" class="btn btn-outline-secondary " data-action="zoom-100" title="100% Zoom">
                <span style="font-size: 0.8em;">100%</span>
            </button>
            
            <button type="button" class="btn btn-outline-secondary " data-action="zoom-in" title="Zoom In">
                <i class="ri-zoom-in-line"></i>
            </button>
        </div>
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
