<!-- Content Selection Modal -->
<div class="modal fade" id="contentSelectionModal" tabindex="-1" aria-labelledby="contentSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentSelectionModalLabel">Configure Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="content-selection-container">
                    <!-- Content type selection -->
                    <div class="content-type-selection mb-4">
                        <label for="contentTypeSelect" class="form-label">Content Type</label>
                        <select class="form-select" id="contentTypeSelect" name="contentTypeSelect">
                            <option value="" selected disabled>Select content type</option>
                        </select>
                        <div class="form-text">Choose the type of content this widget will display</div>
                    </div>
                    
                    <!-- Content items -->
                    <div class="content-items-selection">
                        <label class="form-label">Content Items</label>
                        <div id="contentItemsList">
                            <!-- Content items loaded dynamically -->
                        </div>
                        <input type="hidden" id="selectedContentItemId" name="selectedContentItemId" value="">
                        <div class="form-text">Select the content items to display in this widget</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveContentSelectionBtn">Save Widget</button>
            </div>
        </div>
    </div>
</div> 