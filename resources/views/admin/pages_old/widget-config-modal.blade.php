{{-- Widget Configuration Modal --}}
<div class="modal fade" id="widgetConfigModal" tabindex="-1" aria-labelledby="widgetConfigModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="widgetConfigModalLabel">Configure Widget</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="widgetConfigTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="associate-content-tab" data-bs-toggle="tab" data-bs-target="#associate-content" type="button" role="tab" aria-controls="associate-content" aria-selected="true">
              Associate Content
            </button>
          </li>
          <!-- Future tabs can go here -->
        </ul>
        <div class="tab-content mt-3" id="widgetConfigTabContent">
          <div class="tab-pane fade show active" id="associate-content" role="tabpanel" aria-labelledby="associate-content-tab">
            <div class="mb-3">
              <label for="widgetContentTypeSelect" class="form-label">Select Content Type</label>
              <select class="form-select" id="widgetContentTypeSelect">
                <option selected disabled>Loading content types...</option>
                <!-- Options will be populated by JS -->
              </select>
            </div>
            <div class="alert alert-info">
              Field mapping is assumed to be already done for this implementation. Selecting a content type will associate it with the widget.
            </div>
            <div id="widgetContentItemsList" class="mt-3"></div>
            <input type="hidden" id="selectedContentItemId" name="selectedContentItemId" value="">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveWidgetConfigBtn">Save</button>
      </div>
    </div>
  </div>
</div> 