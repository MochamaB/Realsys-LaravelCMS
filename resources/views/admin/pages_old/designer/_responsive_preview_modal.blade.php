<!-- Responsive Preview Modal -->
<div class="modal fade" id="responsivePreviewModal" tabindex="-1" aria-labelledby="responsivePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responsivePreviewModalLabel">Full Page Preview</h5>
                <div class="device-switcher me-3">
                    <button class="btn btn-sm btn-outline-secondary active" data-device="desktop" title="Desktop">
                        <i class="ri-computer-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" data-device="tablet" title="Tablet">
                        <i class="ri-tablet-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" data-device="mobile" title="Mobile">
                        <i class="ri-smartphone-line"></i>
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="preview-container" id="fullPreviewContainer">
                    <!-- Full page preview loaded here -->
                </div>
            </div>
        </div>
    </div>
</div> 