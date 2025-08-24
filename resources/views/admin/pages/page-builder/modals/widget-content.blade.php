<!-- Widget Content Modal -->
<div class="modal fade" id="widgetContentModal" tabindex="-1" aria-labelledby="widgetContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="widgetContentModalLabel">Add Widget to Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Section Info -->
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="ri-information-line me-2"></i>
                        <div>
                            <strong>Target Section:</strong> <span id="targetSectionName">Section Name</span>
                            <input type="hidden" id="targetSectionId" name="section_id">
                        </div>
                    </div>
                </div>

                <!-- Widget Selection -->
                <div class="row">
                    <!-- Widget Library -->
                    <div class="col-md-6">
                        <h6 class="mb-3">
                            <i class="ri-apps-2-line me-2"></i>Available Widgets
                        </h6>
                        
                        <!-- Widget Search -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ri-search-line"></i>
                                </span>
                                <input type="text" class="form-control" id="widgetSearchInput" placeholder="Search widgets...">
                            </div>
                        </div>

                        <!-- Widget Category Filter -->
                        <div class="mb-3">
                            <select class="form-select" id="widgetCategoryFilter">
                                <option value="">All Categories</option>
                                <option value="content">Content</option>
                                <option value="layout">Layout</option>
                                <option value="media">Media</option>
                                <option value="navigation">Navigation</option>
                                <option value="forms">Forms</option>
                            </select>
                        </div>

                        <!-- Widget List -->
                        <div class="widget-library-list" id="widgetLibraryList" style="max-height: 400px; overflow-y: auto;">
                            <!-- Loading state -->
                            <div class="text-center p-4" id="widgetLibraryLoading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading widgets...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading available widgets...</p>
                            </div>

                            <!-- Widget items will be populated by JavaScript -->
                            <div id="widgetLibraryItems"></div>
                        </div>
                    </div>

                    <!-- Widget Preview & Configuration -->
                    <div class="col-md-6">
                        <h6 class="mb-3">
                            <i class="ri-eye-line me-2"></i>Widget Preview & Settings
                        </h6>

                        <!-- No widget selected state -->
                        <div class="text-center p-5" id="noWidgetSelected">
                            <i class="ri-hand-finger-line display-4 text-muted mb-3"></i>
                            <p class="text-muted">Select a widget from the left to see preview and settings</p>
                        </div>

                        <!-- Selected widget info -->
                        <div id="selectedWidgetInfo" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <i id="selectedWidgetIcon" class="ri-puzzle-line me-2"></i>
                                        <div>
                                            <h6 class="mb-0" id="selectedWidgetName">Widget Name</h6>
                                            <small class="text-muted" id="selectedWidgetCategory">Category</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text" id="selectedWidgetDescription">Widget description will appear here.</p>
                                    
                                    <!-- Widget Settings Form -->
                                    <div id="widgetSettingsForm">
                                        <!-- Basic Settings -->
                                        <div class="mb-3">
                                            <label class="form-label">Widget Position</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <label class="form-label small">Grid Width (1-12)</label>
                                                    <input type="number" class="form-control form-control-sm" id="widgetGridWidth" min="1" max="12" value="6">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Grid Height (1-20)</label>
                                                    <input type="number" class="form-control form-control-sm" id="widgetGridHeight" min="1" max="20" value="4">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Widget-specific settings will be populated here -->
                                        <div id="widgetSpecificSettings">
                                            <!-- Dynamic content based on selected widget -->
                                        </div>
                                    </div>

                                    <!-- Widget Preview -->
                                    <div class="mt-3">
                                        <label class="form-label">Preview</label>
                                        <div class="border rounded p-3" id="widgetPreviewContainer" style="min-height: 120px; background: #f8f9fa;">
                                            <div class="text-center text-muted">
                                                <i class="ri-eye-off-line mb-2"></i>
                                                <p class="mb-0">Widget preview will appear here</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addWidgetToSectionBtn" disabled>
                    <i class="ri-add-line me-2"></i>Add Widget to Section
                </button>
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