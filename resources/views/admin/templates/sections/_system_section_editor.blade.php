<div class="modal fade" id="systemSectionEditorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="systemSectionEditorTitle">Edit System Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="systemSectionEditorForm">
                    <input type="hidden" id="system-section-type" name="section_type" value="">
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="system-section-enabled" checked>
                            <label class="form-check-label" for="system-section-enabled">Enable Section</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="system-section-height" class="form-label">Height (px)</label>
                        <input type="number" class="form-control" id="system-section-height" name="height" min="50" value="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="system-section-css" class="form-label">CSS Classes</label>
                        <input type="text" class="form-control" id="system-section-css" name="css_classes">
                    </div>
                    
                    <div class="mb-3">
                        <label for="system-section-template" class="form-label">Content Template</label>
                        <select class="form-select" id="system-section-template" name="template">
                            <option value="default">Default</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-system-section-btn">Save Settings</button>
            </div>
        </div>
    </div>
</div>