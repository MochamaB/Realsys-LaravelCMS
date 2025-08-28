<!-- Section Templates Modal -->
<div class="modal fade" id="sectionTemplatesModal" tabindex="-1" aria-labelledby="sectionTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionTemplatesModalLabel">Choose Section Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="text-muted">Select a section template to add to your page. Each template provides a different layout structure.</p>
                    </div>
                </div>
                <div class="section-template-grid" id="sectionTemplateGrid" style="max-height: 60vh; overflow-y: auto;">
                    <!-- Loading state -->
                    <div class="text-center py-4" id="templateLoadingState">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading templates...</span>
                        </div>
                        <p class="text-muted">Loading section templates...</p>
                    </div>
                    <!-- Templates will be loaded dynamically here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addSelectedSectionBtn" disabled>
                    <i class="ri-add-line me-2"></i>Add Section
                </button>
            </div>
        </div>
    </div>
</div>