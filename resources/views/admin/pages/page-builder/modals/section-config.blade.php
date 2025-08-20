<!-- Section Configuration Modal -->
<div class="modal fade" id="sectionConfigModal" tabindex="-1" aria-labelledby="sectionConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionConfigModalLabel">Section Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sectionConfigForm">
                    <input type="hidden" id="sectionId" name="section_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sectionName" class="form-label">Section Name</label>
                                <input type="text" class="form-control" id="sectionName" name="section_name" placeholder="Enter section name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sectionType" class="form-label">Section Type</label>
                                <select class="form-select" id="sectionType" name="section_type">
                                    <option value="full-width">Full Width</option>
                                    <option value="two-columns">Two Columns</option>
                                    <option value="three-columns">Three Columns</option>
                                    <option value="sidebar-left">Sidebar Left</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sectionBackgroundColor" class="form-label">Background Color</label>
                                <input type="color" class="form-control form-control-color" id="sectionBackgroundColor" name="background_color" value="#ffffff">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sectionPadding" class="form-label">Padding</label>
                                <select class="form-select" id="sectionPadding" name="padding">
                                    <option value="none">None</option>
                                    <option value="small">Small</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sectionCustomCSS" class="form-label">Custom CSS</label>
                        <textarea class="form-control" id="sectionCustomCSS" name="custom_css" rows="3" placeholder="Enter custom CSS for this section"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteSectionBtn">Delete Section</button>
                <button type="button" class="btn btn-primary" id="saveSectionBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>