<!-- Simple Add Widget Modal -->
<div class="modal fade" id="addWidgetModal" tabindex="-1" aria-labelledby="addWidgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWidgetModalLabel">
                    <i class="ri-add-circle-line me-2"></i>Add Widget to Section
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Section Info -->
                <div class="alert alert-info mb-3">
                    <strong>Target Section:</strong> <span id="targetSectionName">Section Name</span>
                    <input type="hidden" id="targetSectionId" value="">
                </div>

                
                <!-- Selection State -->
                <div id="selectedWidgetInfo" class="alert alert-success" style="display: none;">
                    <strong>Selected:</strong> <span id="selectedWidgetName"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addWidgetBtn" disabled>
                    <i class="ri-add-line me-2"></i>Add Widget
                </button>
            </div>
        </div>
    </div>
</div>

