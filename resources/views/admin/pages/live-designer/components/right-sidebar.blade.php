<!-- Properties Panel Sidebar -->
<div class="h-100 d-flex flex-column">
    <!-- Sidebar Header -->
    <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ri-settings-3-line me-2"></i>
            Properties
        </h6>
        
        <!-- Collapsed Icons (shown only when sidebar is collapsed) -->
        <div class="collapsed-icons">
            <button class="collapsed-icon active" title="Style Manager" data-tab="style">
                <i class="ri-palette-line"></i>
            </button>
            <button class="collapsed-icon" title="Traits" data-tab="traits">
                <i class="ri-settings-2-line"></i>
            </button>
            <button class="collapsed-icon" title="Layers" data-tab="layers">
                <i class="ri-stack-line"></i>
            </button>
        </div>
    </div>
    
    <!-- Properties Tabs -->
    <div class="flex-shrink-0">
        <ul class="nav nav-tabs nav-tabs-custom" id="properties-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="component-editor-tab" data-bs-toggle="tab" data-bs-target="#component-editor-panel" type="button" role="tab">
                    <i class="ri-edit-line me-1"></i>
                    Editor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="style-tab" data-bs-toggle="tab" data-bs-target="#style-panel" type="button" role="tab">
                    <i class="ri-palette-line me-1"></i>
                    Style
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="traits-tab" data-bs-toggle="tab" data-bs-target="#traits-panel" type="button" role="tab">
                    <i class="ri-settings-2-line me-1"></i>
                    Traits
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="layers-tab" data-bs-toggle="tab" data-bs-target="#layers-panel" type="button" role="tab">
                    <i class="ri-stack-line me-1"></i>
                    Layers
                </button>
            </li>
        </ul>
    </div>
    
    <!-- Tab Content -->
    <div class="tab-content flex-grow-1 overflow-auto" id="properties-tab-content">
        <!-- Component Editor Panel (New) -->
        <div class="tab-pane fade show active h-100" id="component-editor-panel" role="tabpanel">
            <div id="component-editor-container" class="h-100">
                <!-- Component editor will be loaded here -->
            </div>
        </div>
        
        <!-- Style Panel -->
        <div class="tab-pane fade h-100" id="style-panel" role="tabpanel">
            <div class="p-3">
                <!-- Selected Element Info -->
                <div class="selected-element-info mb-3" id="selected-element-info" style="display: none;">
                    <div class="alert alert-info alert-sm">
                        <div class="d-flex align-items-center">
                            <i class="ri-cursor-line me-2"></i>
                            <div>
                                <div class="fw-semibold" id="selected-element-name">No element selected</div>
                                <div class="small text-muted" id="selected-element-type"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Style Manager Container -->
                <div id="style-manager">
                    <!-- GrapesJS Style Manager will be injected here -->
                    <div class="text-center py-4 text-muted">
                        <i class="ri-cursor-line fs-1 mb-2 d-block"></i>
                        <div>Select an element to edit its styles</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Traits Panel -->
        <div class="tab-pane fade h-100" id="traits-panel" role="tabpanel">
            <div class="p-3">
                <!-- Selected Element Info -->
                <div class="selected-element-info mb-3" id="selected-element-info-traits" style="display: none;">
                    <div class="alert alert-info alert-sm">
                        <div class="d-flex align-items-center">
                            <i class="ri-settings-2-line me-2"></i>
                            <div>
                                <div class="fw-semibold" id="selected-element-name-traits">No element selected</div>
                                <div class="small text-muted" id="selected-element-type-traits"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trait Manager Container -->
                <div id="trait-manager">
                    <!-- GrapesJS Trait Manager will be injected here -->
                    <div class="text-center py-4 text-muted">
                        <i class="ri-settings-2-line fs-1 mb-2 d-block"></i>
                        <div>Select an element to edit its properties</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Layers Panel -->
        <div class="tab-pane fade h-100" id="layers-panel" role="tabpanel">
            <div class="p-3">
                <!-- Layer Controls -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-stack-line me-2"></i>
                        Page Structure
                    </h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="expand-all-layers" title="Expand All">
                            <i class="ri-add-box-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="collapse-all-layers" title="Collapse All">
                            <i class="ri-subtract-line"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Layer Manager Container -->
                <div id="layer-manager">
                    <!-- GrapesJS Layer Manager will be injected here -->
                    <div class="text-center py-4 text-muted">
                        <i class="ri-stack-line fs-1 mb-2 d-block"></i>
                        <div>Loading page structure...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
  
</div>

<style>
/* Properties Panel Styling */
.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

/* Style Manager Customization */
#style-manager .gjs-sm-sector {
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 0.5rem;
}

#style-manager .gjs-sm-title {
    background: #f8f9fa;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

#style-manager .gjs-sm-property {
    margin-bottom: 0.75rem;
}

#style-manager .gjs-sm-label {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
    color: #495057;
}

#style-manager input,
#style-manager select {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

#style-manager input:focus,
#style-manager select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Trait Manager Customization */
#trait-manager .gjs-trt-trait {
    margin-bottom: 0.75rem;
}

#trait-manager .gjs-trt-label {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
    color: #495057;
}

#trait-manager input,
#trait-manager select,
#trait-manager textarea {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    width: 100%;
}

#trait-manager input:focus,
#trait-manager select:focus,
#trait-manager textarea:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Layer Manager Customization */
#layer-manager .gjs-layer {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    margin-bottom: 2px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

#layer-manager .gjs-layer:hover {
    background-color: #f8f9fa;
}

#layer-manager .gjs-layer.gjs-selected {
    background-color: #e3f2fd;
    color: #0d6efd;
}

#layer-manager .gjs-layer-title {
    font-size: 0.875rem;
    font-weight: 500;
}

#layer-manager .gjs-layer-count {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Tab customization */
.nav-tabs-custom .nav-link {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border: none;
    color: #6c757d;
}

.nav-tabs-custom .nav-link.active {
    background-color: #fff;
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
}

/* Quick action buttons */
.btn-sm {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .properties-panel {
        padding: 1rem;
    }
    
    .nav-tabs-custom .nav-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.8125rem;
    }
}
</style>
