<!-- Widget Content Modal - Multi-Step -->
<div class="modal fade" id="widgetContentModal" tabindex="-1" aria-labelledby="widgetContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center flex-grow-1">
                    <h5 class="modal-title me-4" id="widgetContentModalLabel">Add Widget to Section</h5>
                    
                    <!-- Step Indicator -->
                    <div class="widget-modal-steps d-flex align-items-center">
                        <div class="step-indicator active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-label d-none d-md-inline">Widget</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-label d-none d-md-inline">Content</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-label d-none d-md-inline">Items</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" data-step="4">
                            <span class="step-number">4</span>
                            <span class="step-label d-none d-md-inline">Config</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" data-step="5">
                            <span class="step-number">5</span>
                            <span class="step-label d-none d-md-inline">Review</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hidden inputs for modal data -->
                <input type="hidden" id="targetSectionId" name="section_id">
                <input type="hidden" id="selectedWidgetData" name="widget_data">
                
                <!-- Section Info Bar -->
                <div class="alert alert-info mb-4 d-flex align-items-center">
                    <i class="ri-information-line me-2"></i>
                    <div>
                        <strong>Target Section:</strong> <span id="targetSectionName">Section Name</span>
                    </div>
                </div>

                <!-- Multi-Step Content Container -->
                <div class="widget-modal-content">
                    
                    <!-- Step 1: Widget Selection -->
                    <div class="modal-step active" id="step1" data-step="1">
                        <div class="step-header mb-4">
                            <h6 class="step-title">
                                <i class="ri-apps-2-line me-2"></i>Choose a Widget
                            </h6>
                            <p class="step-description text-muted">Select a widget from your active theme to add to this section.</p>
                        </div>

                        <div class="row">
                            <!-- Widget Library -->
                            <div class="col-lg-8">
                                <!-- Search and Filter Bar -->
                                <div class="widget-controls mb-4">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="ri-search-line"></i>
                                                </span>
                                                <input type="text" class="form-control" id="widgetSearchInput" placeholder="Search widgets...">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" id="widgetCategoryFilter">
                                                <option value="">All Categories</option>
                                                <option value="content">Content</option>
                                                <option value="layout">Layout</option>
                                                <option value="media">Media</option>
                                                <option value="utility">Utility</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Widget Library Grid -->
                                <div class="widget-library-container">
                                    <!-- Loading state -->
                                    <div class="text-center p-5" id="widgetLibraryLoading">
                                        <div class="spinner-border text-primary mb-3" role="status">
                                            <span class="visually-hidden">Loading widgets...</span>
                                        </div>
                                        <p class="text-muted">Loading available widgets from your theme...</p>
                                    </div>

                                    <!-- Widget Grid -->
                                    <div id="widgetLibraryGrid" style="display: none;">
                                        <!-- Widget categories will be populated by JavaScript -->
                                    </div>
                                    
                                    <!-- No results state -->
                                    <div class="text-center p-5" id="widgetNoResults" style="display: none;">
                                        <i class="ri-search-line display-4 text-muted mb-3"></i>
                                        <p class="text-muted">No widgets found matching your criteria</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Widget Preview Panel -->
                            <div class="col-lg-4">
                                <div class="widget-preview-panel">
                                    <!-- No selection state -->
                                    <div class="text-center p-4" id="noWidgetSelected">
                                        <i class="ri-hand-finger-line display-4 text-muted mb-3"></i>
                                        <p class="text-muted">Select a widget to see details and preview</p>
                                    </div>

                                    <!-- Selected widget preview -->
                                    <div id="selectedWidgetPreview" style="display: none;">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center">
                                                    <i id="selectedWidgetIcon" class="ri-puzzle-line me-2 text-primary"></i>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0" id="selectedWidgetName">Widget Name</h6>
                                                        <small class="text-muted" id="selectedWidgetCategory">Category</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text" id="selectedWidgetDescription">Widget description.</p>
                                                
                                                <div class="widget-info">
                                                    <div class="info-item d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Content Types:</span>
                                                        <div id="selectedWidgetContentTypes">
                                                            <!-- Content type badges -->
                                                        </div>
                                                    </div>
                                                    <div class="info-item d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Fields:</span>
                                                        <span id="selectedWidgetFieldCount" class="badge bg-secondary">0</span>
                                                    </div>
                                                    <div class="info-item d-flex justify-content-between">
                                                        <span class="text-muted">Supports Content:</span>
                                                        <span id="selectedWidgetSupportsContent" class="badge bg-info">No</span>
                                                    </div>
                                                </div>

                                                <!-- Widget Preview Image -->
                                                <div class="mt-3">
                                                    <label class="form-label small">Preview:</label>
                                                    <div class="widget-preview-image text-center p-3 border rounded" style="background: #f8f9fa;">
                                                        <img id="selectedWidgetPreviewImage" src="" alt="Widget Preview" class="img-fluid" style="max-height: 120px; display: none;">
                                                        <div id="selectedWidgetPreviewPlaceholder" class="text-muted">
                                                            <i class="ri-image-line display-6 mb-2"></i>
                                                            <p class="mb-0 small">Preview not available</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Content Type Selection (Initially Hidden) -->
                    <div class="modal-step" id="step2" data-step="2" style="display: none;">
                        <div class="step-header mb-4">
                            <h6 class="step-title">
                                <i class="ri-file-list-line me-2"></i>Choose Content Type
                            </h6>
                            <p class="step-description text-muted">Select the type of content this widget will display.</p>
                        </div>
                        <div id="contentTypeSelection">
                            <!-- Loading state -->
                            <div class="text-center p-5" id="contentTypeLoading">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading content types...</span>
                                </div>
                                <p class="text-muted">Loading available content types...</p>
                            </div>

                            <!-- Content Type Grid -->
                            <div id="contentTypeGrid" style="display: none;">
                                <div class="row g-3">
                                    <!-- Content type items will be populated by JavaScript -->
                                </div>
                            </div>
                            
                            <!-- No content types state -->
                            <div class="text-center p-5" id="noContentTypes" style="display: none;">
                                <i class="ri-file-list-line display-4 text-muted mb-3"></i>
                                <h6 class="text-muted">No Content Types Available</h6>
                                <p class="text-muted mb-0">This widget doesn't require content types or none have been configured yet.</p>
                            </div>

                            <!-- Selected Content Type Info -->
                            <div id="selectedContentTypeInfo" class="mt-4" style="display: none;">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i id="selectedContentTypeIcon" class="ri-file-list-line me-3 fs-4"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Selected Content Type: <span id="selectedContentTypeName">Content Type</span></h6>
                                        <p class="mb-0 small text-muted" id="selectedContentTypeDescription">Content type description</p>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary me-1"><span id="selectedContentTypeFieldCount">0</span> fields</span>
                                            <span class="badge bg-info"><span id="selectedContentTypeItemCount">0</span> items</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Content Items (Initially Hidden) -->
                    <div class="modal-step" id="step3" data-step="3" style="display: none;">
                        <div class="step-header mb-4">
                            <h6 class="step-title">
                                <i class="ri-list-check me-2"></i>Select Content Items
                            </h6>
                            <p class="step-description text-muted">Choose specific content items or configure automatic content selection.</p>
                        </div>
                        
                        <!-- Selection Mode Tabs -->
                        <div class="content-selection-modes mb-4">
                            <ul class="nav nav-pills" id="contentSelectionTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="manual-tab" data-bs-toggle="pill" data-bs-target="#manual-selection" type="button" role="tab" aria-controls="manual-selection" aria-selected="true">
                                        <i class="ri-hand-finger-line me-2"></i>Manual Selection
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="query-tab" data-bs-toggle="pill" data-bs-target="#query-builder" type="button" role="tab" aria-controls="query-builder" aria-selected="false">
                                        <i class="ri-search-line me-2"></i>Query Builder
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="contentSelectionTabContent">
                            <!-- Manual Selection Tab -->
                            <div class="tab-pane fade show active" id="manual-selection" role="tabpanel" aria-labelledby="manual-tab">
                                <div id="manualSelectionContainer">
                                    <!-- Search and Filter Controls -->
                                    <div class="content-controls mb-3">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ri-search-line"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="contentSearchInput" placeholder="Search content items...">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select" id="contentStatusFilter">
                                                    <option value="">All Status</option>
                                                    <option value="published">Published</option>
                                                    <option value="draft">Draft</option>
                                                    <option value="private">Private</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Loading state -->
                                    <div class="text-center p-5" id="contentItemsLoading">
                                        <div class="spinner-border text-primary mb-3" role="status">
                                            <span class="visually-hidden">Loading content items...</span>
                                        </div>
                                        <p class="text-muted">Loading available content items...</p>
                                    </div>

                                    <!-- Content Items List -->
                                    <div id="contentItemsList" style="display: none;">
                                        <div class="content-items-container" style="max-height: 400px; overflow-y: auto;">
                                            <!-- Content items will be populated by JavaScript -->
                                        </div>
                                        
                                        <!-- Pagination -->
                                        <div id="contentItemsPagination" class="mt-3">
                                            <!-- Pagination will be populated by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- No content items state -->
                                    <div class="text-center p-5" id="noContentItems" style="display: none;">
                                        <i class="ri-file-list-line display-4 text-muted mb-3"></i>
                                        <h6 class="text-muted">No Content Items Found</h6>
                                        <p class="text-muted mb-0">No items found for this content type or matching your search criteria.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Query Builder Tab -->
                            <div class="tab-pane fade" id="query-builder" role="tabpanel" aria-labelledby="query-tab">
                                <div id="queryBuilderContainer">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <!-- Query Configuration -->
                                            <div class="query-config">
                                                <h6 class="mb-3">Content Query Configuration</h6>
                                                
                                                <!-- Filters -->
                                                <div class="filter-section mb-4">
                                                    <label class="form-label fw-bold">Filters</label>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Status</label>
                                                            <select class="form-select" id="queryStatusFilter">
                                                                <option value="">Any Status</option>
                                                                <option value="published">Published</option>
                                                                <option value="draft">Draft</option>
                                                                <option value="private">Private</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Date Range</label>
                                                            <select class="form-select" id="queryDateFilter">
                                                                <option value="">Any Time</option>
                                                                <option value="today">Today</option>
                                                                <option value="week">This Week</option>
                                                                <option value="month">This Month</option>
                                                                <option value="year">This Year</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small">Search Keywords</label>
                                                            <input type="text" class="form-control" id="querySearchKeywords" placeholder="Search in title and content...">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Sorting -->
                                                <div class="sort-section mb-4">
                                                    <label class="form-label fw-bold">Sorting</label>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Sort By</label>
                                                            <select class="form-select" id="querySortBy">
                                                                <option value="created_at">Date Created</option>
                                                                <option value="updated_at">Date Modified</option>
                                                                <option value="title">Title</option>
                                                                <option value="status">Status</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Sort Direction</label>
                                                            <select class="form-select" id="querySortDirection">
                                                                <option value="desc">Newest First</option>
                                                                <option value="asc">Oldest First</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Limit -->
                                                <div class="limit-section mb-4">
                                                    <label class="form-label fw-bold">Limit</label>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Number of Items</label>
                                                            <select class="form-select" id="queryLimit">
                                                                <option value="5">5 items</option>
                                                                <option value="10" selected>10 items</option>
                                                                <option value="15">15 items</option>
                                                                <option value="20">20 items</option>
                                                                <option value="30">30 items</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Preview Button -->
                                                <button type="button" class="btn btn-outline-primary" id="previewQueryBtn">
                                                    <i class="ri-eye-line me-2"></i>Preview Results
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <!-- Query Preview -->
                                            <div class="query-preview-panel">
                                                <h6 class="mb-3">Query Preview</h6>
                                                
                                                <!-- Loading state -->
                                                <div class="text-center p-3" id="queryPreviewLoading" style="display: none;">
                                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                                        <span class="visually-hidden">Loading preview...</span>
                                                    </div>
                                                    <p class="text-muted small">Generating preview...</p>
                                                </div>
                                                
                                                <!-- Preview Results -->
                                                <div id="queryPreviewResults">
                                                    <div class="text-center p-3 text-muted">
                                                        <i class="ri-eye-line display-6 mb-2"></i>
                                                        <p class="small mb-0">Click "Preview Results" to see what content will be selected</p>
                                                    </div>
                                                </div>
                                                
                                                <!-- Query Summary -->
                                                <div id="querySummary" class="mt-3" style="display: none;">
                                                    <div class="card card-body bg-light">
                                                        <h6 class="card-title small">Query Summary</h6>
                                                        <div class="query-summary-content">
                                                            <!-- Summary content will be populated by JavaScript -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Items Summary -->
                        <div id="selectedItemsSummary" class="mt-4" style="display: none;">
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="ri-check-line me-3 fs-4"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Content Selection Complete</h6>
                                    <p class="mb-0 small" id="selectionSummaryText">Selection summary</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Widget Configuration (Initially Hidden) -->
                    <div class="modal-step" id="step4" data-step="4" style="display: none;">
                        <div class="step-header mb-4">
                            <h6 class="step-title">
                                <i class="ri-settings-line me-2"></i>Configure Widget
                            </h6>
                            <p class="step-description text-muted">Customize the widget settings, layout, and appearance.</p>
                        </div>
                        
                        <div id="widgetConfiguration">
                            <!-- Loading state -->
                            <div class="text-center p-5" id="configurationLoading">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading configuration...</span>
                                </div>
                                <p class="text-muted">Loading widget configuration options...</p>
                            </div>

                            <!-- Configuration Sections -->
                            <div id="configurationContent" style="display: none;">
                                <div class="configuration-sections">
                                    <!-- Widget Fields Section -->
                                    <div class="configuration-section mb-4" id="widgetFieldsSection">
                                        <div class="section-header mb-3">
                                            <h6 class="section-title d-flex align-items-center">
                                                <i class="ri-input-method-line me-2 text-primary"></i>
                                                Widget Settings
                                                <span class="badge bg-light text-dark ms-2" id="fieldCount">0 fields</span>
                                            </h6>
                                            <p class="section-description text-muted small mb-0">Configure widget-specific options and content.</p>
                                        </div>
                                        <div class="widget-fields-container">
                                            <!-- Dynamic fields will be populated here -->
                                        </div>
                                    </div>

                                    <!-- Layout Section -->
                                    <div class="configuration-section mb-4" id="layoutSection">
                                        <div class="section-header mb-3">
                                            <h6 class="section-title d-flex align-items-center">
                                                <i class="ri-layout-grid-line me-2 text-success"></i>
                                                Layout & Positioning
                                            </h6>
                                            <p class="section-description text-muted small mb-0">Control how the widget is positioned and sized.</p>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small">Width (Grid Columns)</label>
                                                <select class="form-select" id="layoutWidth">
                                                    <option value="3">3 columns (25%)</option>
                                                    <option value="4">4 columns (33%)</option>
                                                    <option value="6">6 columns (50%)</option>
                                                    <option value="8">8 columns (67%)</option>
                                                    <option value="9">9 columns (75%)</option>
                                                    <option value="12" selected>12 columns (100%)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Height</label>
                                                <select class="form-select" id="layoutHeight">
                                                    <option value="auto" selected>Auto Height</option>
                                                    <option value="small">Small (200px)</option>
                                                    <option value="medium">Medium (400px)</option>
                                                    <option value="large">Large (600px)</option>
                                                    <option value="custom">Custom</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Alignment</label>
                                                <select class="form-select" id="layoutAlignment">
                                                    <option value="left" selected>Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right">Right</option>
                                                    <option value="justify">Justify</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Styling Section -->
                                    <div class="configuration-section mb-4" id="stylingSection">
                                        <div class="section-header mb-3">
                                            <h6 class="section-title d-flex align-items-center">
                                                <i class="ri-palette-line me-2 text-warning"></i>
                                                Styling & Appearance
                                            </h6>
                                            <p class="section-description text-muted small mb-0">Customize colors, spacing, and visual appearance.</p>
                                        </div>
                                        <div class="styling-tabs">
                                            <ul class="nav nav-pills nav-sm mb-3" id="stylingTabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="spacing-tab" data-bs-toggle="pill" data-bs-target="#spacing-panel" type="button" role="tab">Spacing</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="background-tab" data-bs-toggle="pill" data-bs-target="#background-panel" type="button" role="tab">Background</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="border-tab" data-bs-toggle="pill" data-bs-target="#border-panel" type="button" role="tab">Border</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="stylingTabContent">
                                                <!-- Spacing Panel -->
                                                <div class="tab-pane fade show active" id="spacing-panel" role="tabpanel">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Padding</label>
                                                            <div class="spacing-controls">
                                                                <div class="row g-2">
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="paddingTop" placeholder="Top" min="0" max="100" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="paddingRight" placeholder="Right" min="0" max="100" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="paddingBottom" placeholder="Bottom" min="0" max="100" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="paddingLeft" placeholder="Left" min="0" max="100" value="0">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Margin</label>
                                                            <div class="spacing-controls">
                                                                <div class="row g-2">
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="marginTop" placeholder="Top" min="0" max="100" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="marginRight" placeholder="Right" min="0" max="100" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="marginBottom" placeholder="Bottom" min="0" max="100" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="number" class="form-control form-control-sm" id="marginLeft" placeholder="Left" min="0" max="100" value="0">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Background Panel -->
                                                <div class="tab-pane fade" id="background-panel" role="tabpanel">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Background Color</label>
                                                            <div class="input-group">
                                                                <input type="color" class="form-control form-control-color" id="backgroundColor" value="#ffffff" title="Choose background color">
                                                                <input type="text" class="form-control" id="backgroundColorHex" placeholder="#ffffff">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Opacity</label>
                                                            <input type="range" class="form-range" id="backgroundOpacity" min="0" max="100" value="100">
                                                            <div class="d-flex justify-content-between">
                                                                <small class="text-muted">Transparent</small>
                                                                <small class="text-muted">Opaque</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Border Panel -->
                                                <div class="tab-pane fade" id="border-panel" role="tabpanel">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Border Radius</label>
                                                            <input type="range" class="form-range" id="borderRadius" min="0" max="50" value="0">
                                                            <div class="text-center">
                                                                <small class="text-muted"><span id="borderRadiusValue">0</span>px</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Custom CSS Class</label>
                                                            <input type="text" class="form-control" id="customCssClass" placeholder="custom-widget-class">
                                                            <small class="form-text text-muted">Add custom CSS classes for advanced styling</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Advanced Section -->
                                    <div class="configuration-section" id="advancedSection">
                                        <div class="section-header mb-3">
                                            <h6 class="section-title d-flex align-items-center">
                                                <i class="ri-magic-line me-2 text-info"></i>
                                                Advanced Options
                                            </h6>
                                            <p class="section-description text-muted small mb-0">Animation effects and responsive settings.</p>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small">Animation Effect</label>
                                                <select class="form-select" id="animationEffect">
                                                    <option value="none" selected>No Animation</option>
                                                    <option value="fade-in">Fade In</option>
                                                    <option value="slide-up">Slide Up</option>
                                                    <option value="slide-left">Slide Left</option>
                                                    <option value="scale-in">Scale In</option>
                                                    <option value="bounce-in">Bounce In</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Responsive Visibility</label>
                                                <div class="responsive-controls">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="visibleXs" checked>
                                                        <label class="form-check-label small" for="visibleXs">
                                                            <i class="ri-smartphone-line"></i> XS
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="visibleSm" checked>
                                                        <label class="form-check-label small" for="visibleSm">
                                                            <i class="ri-tablet-line"></i> SM
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="visibleMd" checked>
                                                        <label class="form-check-label small" for="visibleMd">
                                                            <i class="ri-laptop-line"></i> MD
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="visibleLg" checked>
                                                        <label class="form-check-label small" for="visibleLg">
                                                            <i class="ri-computer-line"></i> LG
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="visibleXl" checked>
                                                        <label class="form-check-label small" for="visibleXl">
                                                            <i class="ri-tv-2-line"></i> XL
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuration Summary -->
                                <div id="configurationSummary" class="mt-4">
                                    <div class="alert alert-light border">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-information-line me-2 text-info"></i>
                                            <div>
                                                <h6 class="mb-1">Configuration Complete</h6>
                                                <p class="mb-0 small text-muted" id="configurationSummaryText">Widget configuration is ready for review.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No fields state -->
                            <div class="text-center p-5" id="noConfigurationFields" style="display: none;">
                                <i class="ri-settings-3-line display-4 text-muted mb-3"></i>
                                <h6 class="text-muted">No Configuration Required</h6>
                                <p class="text-muted mb-0">This widget doesn't have any configurable options.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review & Confirm (Initially Hidden) -->
                    <div class="modal-step" id="step5" data-step="5" style="display: none;">
                        <div class="step-header mb-4">
                            <h6 class="step-title">
                                <i class="ri-check-double-line me-2"></i>Review & Confirm
                            </h6>
                            <p class="step-description text-muted">Review your widget configuration and confirm to add it to the section.</p>
                        </div>
                        <div id="widgetReview">
                            <!-- Review summary will be loaded dynamically -->
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="prevStepBtn" disabled>
                        <i class="ri-arrow-left-line me-2"></i>Previous
                    </button>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="nextStepBtn" disabled>
                        Next<i class="ri-arrow-right-line ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="addWidgetToSectionBtn" style="display: none;">
                        <i class="ri-add-line me-2"></i>Add Widget to Section
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Widget Item Template (hidden, used by JavaScript) -->
<template id="widgetItemTemplate">
    <div class="widget-library-item border rounded p-3 mb-2" style="cursor: pointer; transition: all 0.2s;" data-widget-id="">
        <div class="d-flex align-items-center">
            <i class="widget-icon ri-puzzle-line me-3 fs-4 text-primary"></i>
            <div class="flex-grow-1">
                <h6 class="widget-name mb-1">Widget Name</h6>
                <p class="widget-description text-muted mb-0 small">Widget description</p>
                <span class="widget-category badge bg-secondary mt-1">Category</span>
            </div>
        </div>
    </div>
</template>