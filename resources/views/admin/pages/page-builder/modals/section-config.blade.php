<!-- Section Configuration Modal -->
<div class="modal fade" id="sectionConfigModal" tabindex="-1" aria-labelledby="sectionConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header modalheaderbackground">
                <h5 class="modal-title text-white" id="sectionConfigModalLabel">
                    <i class="ri-settings-3-line me-2"></i>Section Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="background-color: white;" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loading indicator -->
                <div id="sectionConfigLoader" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading section configuration...</p>
                </div>

                <!-- Alert Messages -->
                <div id="sectionConfigAlert" class="alert alert-dismissible fade show d-none" role="alert">
                    <i class="ri-information-line me-2"></i>
                    <span id="alertMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs-custom mb-3" id="sectionConfigTabs" role="tablist" style="border-bottom: 1px solid #dee2e6;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="configuration-tab" data-bs-toggle="tab" 
                              data-bs-target="#configuration" type="button" role="tab" aria-controls="configuration" aria-selected="true">
                            <i class="ri-settings-3-line me-1"></i>Configuration
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="styling-tab" data-bs-toggle="tab" 
                              data-bs-target="#styling" type="button" role="tab" aria-controls="styling" aria-selected="false">
                            <i class="ri-palette-line me-1"></i>Styling & Grid
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="sectionConfigTabContent">
                    <!-- Configuration Tab -->
                    <div class="tab-pane fade show active" id="configuration" role="tabpanel" aria-labelledby="configuration-tab">
                        <form id="sectionConfigForm">
                            <input type="hidden" id="sectionId" name="section_id">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="sectionName" class="form-label">Section Name</label>
                                    <input type="text" class="form-control" id="sectionName" name="section_name" 
                                           placeholder="Enter section name">
                                    <div class="form-text">Display name for this section</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="sectionPosition" class="form-label">Position</label>
                                    <input type="number" class="form-control" id="sectionPosition" name="position" 
                                           placeholder="Enter position order" min="0">
                                    <div class="form-text">Order of this section on the page</div>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allowsWidgets" name="allows_widgets" checked>
                                        <label class="form-check-label" for="allowsWidgets">
                                            <strong>Allows Widgets</strong>
                                        </label>
                                        <div class="form-text">Enable widgets to be added to this section</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12">
                                    <label for="widgetTypes" class="form-label">Allowed Widget Types</label>
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        <div class="form-text mb-2">Select which widget types can be added to this section:</div>
                                        <div id="widgetTypesContainer">
                                            <!-- Widget types will be loaded dynamically -->
                                            <div class="text-muted text-center py-2">
                                                <i class="ri-loader-4-line spin"></i> Loading widget types...
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="widgetTypesJson" name="widget_types">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Styling & Grid Tab -->
                    <!-- Styling & Grid Tab -->
                    <div class="tab-pane fade" id="styling" role="tabpanel" aria-labelledby="styling-tab">
                        <div class="row">
                            <!-- Grid Settings Section -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="ri-layout-grid-line me-1"></i>Grid Settings
                                    </h6>
                                    
                                    <!-- Grid Position Inputs - Vertical Stack -->
                                    <div class="mb-3">
                                        <label for="gridX" class="form-label">Grid X</label>
                                        <input type="number" class="form-control" id="gridX" name="grid_x" 
                                            placeholder="0" min="0" max="11">
                                        <div class="form-text">Horizontal position (0-11)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gridY" class="form-label">Grid Y</label>
                                        <input type="number" class="form-control" id="gridY" name="grid_y" 
                                            placeholder="0" min="0">
                                        <div class="form-text">Vertical position</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gridW" class="form-label">Grid Width</label>
                                        <input type="number" class="form-control" id="gridW" name="grid_w" 
                                            placeholder="12" min="1" max="12">
                                        <div class="form-text">Width (1-12 columns)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gridH" class="form-label">Grid Height</label>
                                        <input type="number" class="form-control" id="gridH" name="grid_h" 
                                            placeholder="4" min="1">
                                        <div class="form-text">Height in grid units</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="columnSpanOverride" class="form-label">Column Span Override</label>
                                        <input type="text" class="form-control" id="columnSpanOverride" name="column_span_override" 
                                            placeholder="e.g., col-md-6">
                                        <div class="form-text">Bootstrap column classes</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="columnOffsetOverride" class="form-label">Column Offset Override</label>
                                        <input type="text" class="form-control" id="columnOffsetOverride" name="column_offset_override" 
                                            placeholder="e.g., offset-md-2">
                                        <div class="form-text">Bootstrap offset classes</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="lockedPosition" name="locked_position">
                                            <label class="form-check-label" for="lockedPosition">
                                                <strong>Lock Position</strong>
                                            </label>
                                            <div class="form-text">Prevent dragging/resizing in editor</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Styling Settings Section -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="ri-palette-line me-1"></i>Styling Settings
                                    </h6>
                                    
                                    <!-- Styling Inputs - Vertical Stack -->
                                    <div class="mb-3">
                                        <label for="cssClasses" class="form-label">CSS Classes</label>
                                        <input type="text" class="form-control" id="cssClasses" name="css_classes" 
                                            placeholder="custom-class another-class">
                                        <div class="form-text">Space-separated CSS classes</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="backgroundColor" class="form-label">Background Color</label>
                                        <div class="d-flex gap-2">
                                            <input type="color" class="form-control form-control-color" id="backgroundColor" name="background_color" value="#ffffff">
                                            <input type="text" class="form-control" id="backgroundColorText" placeholder="#ffffff" value="#ffffff">
                                        </div>
                                    </div>
                                    
                                    <!-- Padding Controls -->
                                    <div class="mb-3">
                                        <label class="form-label">Padding</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="paddingTop" name="padding_top" 
                                                        placeholder="0" min="0">
                                                    <div class="form-text text-center">Top</div>
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="paddingBottom" name="padding_bottom" 
                                                        placeholder="0" min="0">
                                                    <div class="form-text text-center">Bottom</div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-6">
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="paddingLeft" name="padding_left" 
                                                        placeholder="0" min="0">
                                                    <div class="form-text text-center">Left</div>
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="paddingRight" name="padding_right" 
                                                        placeholder="0" min="0">
                                                    <div class="form-text text-center">Right</div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="form-text">Padding values in pixels</div>
                                    </div>
                                    
                                    <!-- Margin Controls -->
                                    <div class="mb-3">
                                        <label class="form-label">Margin</label>
                                        <div class="row g-2">
                                        <div class="col-6">
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm" id="marginTop" name="margin_top" 
                                                    placeholder="0" min="0">
                                                <div class="form-text text-center">Top</div>
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm" id="marginBottom" name="margin_bottom" 
                                                    placeholder="0" min="0">
                                                <div class="form-text text-center">Bottom</div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm" id="marginLeft" name="margin_left" 
                                                    placeholder="0" min="0">
                                                <div class="form-text text-center">Left</div>
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm" id="marginRight" name="margin_right" 
                                                    placeholder="0" min="0">
                                                <div class="form-text text-center">Right</div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="resizeHandles" class="form-label">Resize Handles</label>
                                        <select class="form-select" id="resizeHandles" name="resize_handles">
                                            <option value="all">All directions</option>
                                            <option value="se">Southeast only</option>
                                            <option value="e">East only</option>
                                            <option value="s">South only</option>
                                            <option value="none">None (no resize)</option>
                                        </select>
                                        <div class="form-text">Which handles allow resizing</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
            
            <div class="modal-footer d-flex justify-content-between align-items-center flex-nowrap">
                <button type="button" class="btn btn-outline-danger flex-shrink-0" id="deleteSectionBtn">
                    <i class="ri-delete-bin-line me-1"></i>Delete Section
                </button>
                <div class="d-flex flex-nowrap gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSectionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="saveSpinner" role="status" aria-hidden="true"></span>
                        <i class="ri-save-line me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
