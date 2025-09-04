<!-- Section Templates Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="sectionTemplatesOffcanvas" aria-labelledby="sectionTemplatesOffcanvasLabel" style="width: 400px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sectionTemplatesOffcanvasLabel">
            <i class="ri-layout-grid-line me-2"></i>Choose Section Template
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="text-muted">Select a section template to add to your page. Each template provides a different layout structure.</p>
                    </div>
                </div>
                <div class="section-template-grid" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <!-- STATIC TEMPLATE CARDS - NO DYNAMIC LOADING -->
                    <div class="row g-2">
                        <!-- Header Templates -->
                        <div class="col-12 mb-2">
                            <div class="card section-template-card h-100" 
                                 data-template-key="hero-banner" 
                                 data-template-id="hero-banner"
                                 data-section-type="header"
                                 data-template-type="core"
                                 data-column-layout="full-width"
                                 style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-image-line fs-4 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fs-6">Hero Banner</h6>
                                            <p class="text-muted small mb-1">Full-width hero section with image and text</p>
                                            <div class="template-meta">
                                                <span class="badge bg-light text-dark small">header</span>
                                                <span class="badge bg-light text-dark ms-1 small">full-width</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Templates -->
                        <div class="col-12 mb-2">
                            <div class="card section-template-card h-100" 
                                 data-template-key="two-column" 
                                 data-template-id="two-column"
                                 data-section-type="content"
                                 data-template-type="core"
                                 data-column-layout="6-6"
                                 style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-layout-column-line fs-4 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fs-6">Two Columns</h6>
                                            <p class="text-muted small mb-1">Two equal columns layout</p>
                                            <div class="template-meta">
                                                <span class="badge bg-light text-dark small">content</span>
                                                <span class="badge bg-light text-dark ms-1 small">6-6</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <div class="card section-template-card h-100" 
                                 data-template-key="three-column" 
                                 data-template-id="three-column"
                                 data-section-type="content"
                                 data-template-type="core"
                                 data-column-layout="4-4-4"
                                 style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-layout-grid-line fs-4 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fs-6">Three Columns</h6>
                                            <p class="text-muted small mb-1">Three equal columns layout</p>
                                            <div class="template-meta">
                                                <span class="badge bg-light text-dark small">content</span>
                                                <span class="badge bg-light text-dark ms-1 small">4-4-4</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <div class="card section-template-card h-100" 
                                 data-template-key="full-width" 
                                 data-template-id="full-width"
                                 data-section-type="content"
                                 data-template-type="core"
                                 data-column-layout="full-width"
                                 style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-layout-row-line fs-4 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fs-6">Full Width</h6>
                                            <p class="text-muted small mb-1">Single full-width content area</p>
                                            <div class="template-meta">
                                                <span class="badge bg-light text-dark small">content</span>
                                                <span class="badge bg-light text-dark ms-1 small">full-width</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Templates -->
                        <div class="col-12 mb-2">
                            <div class="card section-template-card h-100" 
                                 data-template-key="footer-simple" 
                                 data-template-id="footer-simple"
                                 data-section-type="footer"
                                 data-template-type="core"
                                 data-column-layout="full-width"
                                 style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-layout-bottom-line fs-4 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fs-6">Simple Footer</h6>
                                            <p class="text-muted small mb-1">Basic footer section with links</p>
                                            <div class="template-meta">
                                                <span class="badge bg-light text-dark small">footer</span>
                                                <span class="badge bg-light text-dark ms-1 small">full-width</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <div class="card section-template-card h-100" 
                                 data-template-key="footer-columns" 
                                 data-template-id="footer-columns"
                                 data-section-type="footer"
                                 data-template-type="core"
                                 data-column-layout="3-3-3-3"
                                 style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-layout-grid-line fs-4 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fs-6">Four Column Footer</h6>
                                            <p class="text-muted small mb-1">Footer with four equal columns</p>
                                            <div class="template-meta">
                                                <span class="badge bg-light text-dark small">footer</span>
                                                <span class="badge bg-light text-dark ms-1 small">3-3-3-3</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="offcanvas-footer border-top p-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="button" class="btn btn-primary flex-fill" id="addSelectedSectionBtn" disabled>
                        <i class="ri-add-line me-2"></i>Add Section
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>