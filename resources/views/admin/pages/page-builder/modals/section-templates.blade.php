<!-- Section Templates Modal -->
<div class="modal fade" id="sectionTemplatesModal" tabindex="-1" aria-labelledby="sectionTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                <div class="section-template-grid" id="sectionTemplateGrid">
                    <!-- Section templates loaded dynamically -->
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card section-template-card" data-template-type="full-width">
                                <div class="card-body text-center">
                                    <i class="ri-layout-row-line display-4 text-primary mb-3"></i>
                                    <h6>Full Width</h6>
                                    <p class="text-muted small">A full-width section for hero content</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card section-template-card" data-template-type="two-columns">
                                <div class="card-body text-center">
                                    <i class="ri-layout-2-line display-4 text-primary mb-3"></i>
                                    <h6>Two Columns</h6>
                                    <p class="text-muted small">A two-column layout section</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card section-template-card" data-template-type="three-columns">
                                <div class="card-body text-center">
                                    <i class="ri-layout-3-line display-4 text-primary mb-3"></i>
                                    <h6>Three Columns</h6>
                                    <p class="text-muted small">A three-column layout section</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>