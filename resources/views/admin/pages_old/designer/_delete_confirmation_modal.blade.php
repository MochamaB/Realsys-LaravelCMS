<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSectionModalLabel">
                    <i class="ri-delete-bin-line text-danger"></i>
                    Delete Section
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this section?</p>
                <p class="text-muted small">This action cannot be undone. The section and all its widgets will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSection">
                    <i class="ri-delete-bin-line"></i>
                    Delete Section
                </button>
            </div>
        </div>
    </div>
</div> 