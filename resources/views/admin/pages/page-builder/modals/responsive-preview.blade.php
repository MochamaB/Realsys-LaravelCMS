<!-- Responsive Preview Modal -->
<div class="modal fade" id="responsivePreviewModal" tabindex="-1" aria-labelledby="responsivePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responsivePreviewModalLabel">Page Preview</h5>
                <div class="btn-group ms-3" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm preview-device" data-device="desktop">
                        <i class="ri-computer-line me-1"></i>Desktop
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm preview-device" data-device="tablet">
                        <i class="ri-tablet-line me-1"></i>Tablet
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm preview-device" data-device="mobile">
                        <i class="ri-smartphone-line me-1"></i>Mobile
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="preview-container text-center" style="background: #f0f0f0; height: calc(100vh - 120px);">
                    <div class="preview-frame-wrapper d-inline-block" style="margin: 20px;">
                        <iframe id="previewFrame" 
                                src="about:blank" 
                                style="border: 1px solid #ddd; border-radius: 8px; background: white;"
                                width="100%" 
                                height="600">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>