<div class="modal fade" id="sectionEditorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionEditorTitle">Edit Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sectionEditorForm">
                    <input type="hidden" id="section-id" name="section_id" value="">
                    <input type="hidden" id="section-position" name="position" value="">
                    
                    <div class="mb-3">
                        <label for="section-name" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="section-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="section-type" class="form-label">Section Type</label>
                        <select class="form-select" id="section-type" name="section_type">
                            <option value="full-width">Full Width</option>
                            <option value="multi-column">Multi-Column</option>
                            <option value="sidebar-left">Sidebar Left</option>
                            <option value="sidebar-right">Sidebar Right</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="column-layout" class="form-label">Column Layout</label>
                        <select class="form-select" id="column-layout" name="column_layout">
                            <option value="12">Full Width (12)</option>
                            <option value="6-6">Two Equal Columns (6-6)</option>
                            <option value="4-4-4">Three Equal Columns (4-4-4)</option>
                            <option value="3-3-3-3">Four Equal Columns (3-3-3-3)</option>
                            <option value="8-4">Wide & Narrow (8-4)</option>
                            <option value="4-8">Narrow & Wide (4-8)</option>
                            <option value="3-6-3">Sidebar, Main, Sidebar (3-6-3)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="section-description" class="form-label">Description</label>
                        <textarea class="form-control" id="section-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is-repeatable" name="is_repeatable" value="1">
                        <label class="form-check-label" for="is-repeatable">Allow Multiple Instances</label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max-widgets" class="form-label">Maximum Widgets</label>
                        <input type="number" class="form-control" id="max-widgets" name="max_widgets" min="0" value="0">
                        <div class="form-text">0 means unlimited</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="css-classes" class="form-label">CSS Classes</label>
                        <input type="text" class="form-control" id="css-classes" name="css_classes">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-section-btn">Save Section</button>
            </div>
        </div>
    </div>
</div>