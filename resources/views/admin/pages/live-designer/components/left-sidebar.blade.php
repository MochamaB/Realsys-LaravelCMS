<!-- Component Library Sidebar -->
<div class="h-100 d-flex flex-column">
    <!-- Sidebar Header -->
    <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="ri-apps-2-line me-2"></i>
            Components
        </h6>
        
        <!-- Collapsed Icons (shown only when sidebar is collapsed) -->
        <div class="collapsed-icons">
            <button class="collapsed-icon active" title="Widgets" data-tab="widgets">
                <i class="ri-widget-2-line"></i>
            </button>
            <button class="collapsed-icon" title="Sections" data-tab="sections">
                <i class="ri-layout-grid-line"></i>
            </button>
            <button class="collapsed-icon" title="Elements" data-tab="elements">
                <i class="ri-html5-line"></i>
            </button>
        </div>
    </div>
    
    <!-- Component Tabs -->
    <div class="flex-shrink-0">
        <ul class="nav nav-tabs nav-tabs-custom justify-content-center" id="component-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="components-tab" data-bs-toggle="tab" data-bs-target="#components-panel" type="button" role="tab" title="Components">
                    <i class="ri-node-tree"></i>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="widgets-tab" data-bs-toggle="tab" data-bs-target="#widgets-panel" type="button" role="tab" title="Widgets">
                    <i class=" ri-t-box-line"></i>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="elements-tab" data-bs-toggle="tab" data-bs-target="#elements-panel" type="button" role="tab" title="Elements">
                    <i class="ri-html5-line"></i>
                </button>
            </li>
        </ul>
    </div>
    
    <!-- Tab Content -->
    <div class="tab-content flex-grow-1 overflow-auto" id="component-tab-content">
        <!-- Component Tree Panel (New) -->
        <div class="tab-pane fade show active h-100" id="components-panel" role="tabpanel">
            <div id="component-tree-container" class="h-100">
                <!-- Component tree will be loaded here -->
            </div>
        </div>
        
        <!-- Widgets Panel -->
        <div class="tab-pane fade h-100" id="widgets-panel" role="tabpanel">
            <div class="p-3">
                <!-- Widget Search -->
                <div class="mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search widgets..." id="widget-search">
                    </div>
                </div>
                
                <!-- Widget Categories -->
                <div class="accordion accordion-flush" id="widget-categories">
                    <!-- Content Widgets -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#content-widgets">
                                <i class="ri-file-text-line me-2"></i>
                                Content Widgets
                            </button>
                        </h2>
                        <div id="content-widgets" class="accordion-collapse collapse show">
                            <div class="accordion-body p-0">
                                <div class="widget-list" data-category="content">
                                    <!-- Widgets will be loaded here via JavaScript -->
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="small text-muted mt-2">Loading widgets...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Layout Widgets -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#layout-widgets">
                                <i class="ri-layout-line me-2"></i>
                                Layout Widgets
                            </button>
                        </h2>
                        <div id="layout-widgets" class="accordion-collapse collapse">
                            <div class="accordion-body p-0">
                                <div class="widget-list" data-category="layout">
                                    <!-- Widgets will be loaded here via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Media Widgets -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#media-widgets">
                                <i class="ri-image-line me-2"></i>
                                Media Widgets
                            </button>
                        </h2>
                        <div id="media-widgets" class="accordion-collapse collapse">
                            <div class="accordion-body p-0">
                                <div class="widget-list" data-category="media">
                                    <!-- Widgets will be loaded here via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Interactive Widgets -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#interactive-widgets">
                                <i class="ri-cursor-line me-2"></i>
                                Interactive Widgets
                            </button>
                        </h2>
                        <div id="interactive-widgets" class="accordion-collapse collapse">
                            <div class="accordion-body p-0">
                                <div class="widget-list" data-category="interactive">
                                    <!-- Widgets will be loaded here via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sections Panel -->
        <div class="tab-pane fade h-100" id="sections-panel" role="tabpanel">
            <div class="p-3">
                <!-- Section Search -->
                <div class="mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search sections..." id="section-search">
                    </div>
                </div>
                
                <!-- Section Templates -->
                <div class="section-templates">
                    <h6 class="fw-semibold mb-3">
                        <i class="ri-layout-grid-line me-2"></i>
                        Section Templates
                    </h6>
                    
                    <div class="section-list">
                        <!-- Sections will be loaded here via JavaScript -->
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="small text-muted mt-2">Loading sections...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Elements Panel -->
        <div class="tab-pane fade h-100" id="elements-panel" role="tabpanel">
            <div class="p-3">
                <!-- Basic Elements -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">
                        <i class="ri-html5-line me-2"></i>
                        Basic Elements
                    </h6>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="element-item" data-element="text" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-text"></i>
                                </div>
                                <div class="element-name">Text</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="image" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-image-line"></i>
                                </div>
                                <div class="element-name">Image</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="button" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-cursor-line"></i>
                                </div>
                                <div class="element-name">Button</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="link" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-link"></i>
                                </div>
                                <div class="element-name">Link</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="video" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-video-line"></i>
                                </div>
                                <div class="element-name">Video</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="map" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-map-pin-line"></i>
                                </div>
                                <div class="element-name">Map</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Layout Elements -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">
                        <i class="ri-layout-line me-2"></i>
                        Layout Elements
                    </h6>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="element-item" data-element="container" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-layout-2-line"></i>
                                </div>
                                <div class="element-name">Container</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="row" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-layout-row-line"></i>
                                </div>
                                <div class="element-name">Row</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="column" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-layout-column-line"></i>
                                </div>
                                <div class="element-name">Column</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="element-item" data-element="divider" draggable="true">
                                <div class="element-icon">
                                    <i class="ri-separator"></i>
                                </div>
                                <div class="element-name">Divider</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Widget and Element Items */
.widget-item, .element-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    cursor: grab;
    transition: all 0.2s ease;
    user-select: none;
}

.widget-item:hover, .element-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
    transform: translateY(-1px);
}

.widget-item:active, .element-item:active {
    cursor: grabbing;
    transform: scale(0.98);
}

.widget-item .widget-icon, .element-item .element-icon {
    width: 32px;
    height: 32px;
    background: #f8f9fa;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    color: #6c757d;
}

.widget-item .widget-name, .element-item .element-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #495057;
    text-align: center;
}

.widget-item .widget-description {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: center;
    margin-top: 0.25rem;
}

/* Element items in grid layout */
.element-item {
    text-align: center;
    padding: 0.5rem;
}

.element-item .element-icon {
    margin: 0 auto 0.25rem;
    width: 28px;
    height: 28px;
}

.element-item .element-name {
    font-size: 0.75rem;
}

/* Accordion customization */
.accordion-button {
    font-size: 0.875rem;
    padding: 0.75rem 1rem;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #0d6efd;
}

/* Search input */
#widget-search, #section-search {
    border-radius: 6px;
}

/* Loading states */
.widget-list:empty::after,
.section-list:empty::after {
    content: "No items found";
    display: block;
    text-align: center;
    color: #6c757d;
    font-size: 0.875rem;
    padding: 2rem 1rem;
}
</style>
