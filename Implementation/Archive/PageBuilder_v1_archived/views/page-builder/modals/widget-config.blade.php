<!-- Widget Configuration Modal -->
<div class="modal fade" id="widgetConfigModal" tabindex="-1" aria-labelledby="widgetConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="widgetConfigModalLabel">Widget Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="widgetConfigForm">
                    <input type="hidden" id="widgetId" name="widget_id">
                    <input type="hidden" id="widgetType" name="widget_type">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="widgetTitle" class="form-label">Widget Title</label>
                                <input type="text" class="form-control" id="widgetTitle" name="widget_title" placeholder="Enter widget title">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dynamic widget settings will be loaded here -->
                    <div id="widgetSettingsContainer">
                        <div class="text-center p-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading widget settings...</span>
                            </div>
                            <div class="mt-2 small text-muted">Loading widget configuration...</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteWidgetBtn">Delete Widget</button>
                <button type="button" class="btn btn-primary" id="saveWidgetBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>