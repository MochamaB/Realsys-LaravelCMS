<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="mediaDetailSidebar">
    <div class="offcanvas-header bg-primary p-3">
        <h5 class="offcanvas-title text-white" id="mediaTitle">File Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="file-detail-content p-4">
            <!-- Preview -->
            <div class="text-center mb-4 preview-container">
                <!-- Dynamic content will be inserted here -->
            </div>
            
            <!-- File Info -->
            <div class="card border shadow-none mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="card-title mb-0">File Information</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">File Name</p>
                            <p class="mb-0 fs-14" id="detailFileName">-</p>
                        </div>
                        <div class="col-6 mb-3">
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Size</p>
                            <p class="mb-0 fs-14" id="detailFileSize">-</p>
                        </div>
                        <div class="col-6 mb-3">
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Type</p>
                            <p class="mb-0 fs-14" id="detailFileType">-</p>
                        </div>
                        <div class="col-6 mb-3">
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Collection</p>
                            <p class="mb-0 fs-14" id="detailCollection">-</p>
                        </div>
                        <div class="col-6 mb-3">
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Added On</p>
                            <p class="mb-0 fs-14" id="detailUploadDate">-</p>
                        </div>
                        <div class="col-6 mb-3">
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Last Modified</p>
                            <p class="mb-0 fs-14" id="detailModifiedDate">-</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Organization (Tags & Folder) -->
            <div class="card border shadow-none mb-3">
                <div class="card-header border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Organization</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Tags</label>
                            <button type="button" id="addTagBtn" class="btn btn-sm btn-light">
                                <i class="ri-add-line align-bottom"></i> Add Tags
                            </button>
                        </div>
                        <input type="hidden" id="mediaDetailId" value="">
                        <div id="detailTags" class="media-tags-container">
                            <span class="text-muted">No tags</span>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Folder</label>
                            <button type="button" id="changeFolderBtn" class="btn btn-sm btn-light">
                                <i class="ri-folder-transfer-line align-bottom"></i> Change
                            </button>
                        </div>
                        <div id="detailFolder">
                            <span class="badge bg-light text-dark">
                                <i class="ri-folder-3-line me-1"></i>Root
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Metadata -->
            <div class="card border shadow-none mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="card-title mb-0">Media Metadata</h6>
                </div>
                <div class="card-body p-3">
                    <form id="mediaMetadataForm" class="needs-validation" novalidate>
                        <input type="hidden" id="mediaId" name="mediaId">
                        <div class="mb-3">
                            <label for="mediaName" class="form-label">Title</label>
                            <input type="text" class="form-control" id="mediaName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="mediaAlt" class="form-label">Alt Text</label>
                            <input type="text" class="form-control" id="mediaAlt" name="alt">
                        </div>
                        <div class="mb-3">
                            <label for="mediaTitle" class="form-label">Title Attribute</label>
                            <input type="text" class="form-control" id="mediaTitle" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="mediaCaption" class="form-label">Caption</label>
                            <textarea class="form-control" id="mediaCaption" name="caption" rows="3"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update Metadata</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card border shadow-none mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="card-title mb-0">Actions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-lg-6">
                            <a href="#" id="detailDownloadBtn" class="btn btn-light w-100">
                                <i class="ri-download-2-line align-bottom me-1"></i> Download
                            </a>
                        </div>
                        <div class="col-lg-6">
                            <a href="#" id="detailDeleteBtn" class="btn btn-danger w-100">
                                <i class="ri-delete-bin-line align-bottom me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copy URL -->
            <div class="card border shadow-none">
                <div class="card-header border-bottom p-3">
                    <h6 class="card-title mb-0">Media URL</h6>
                </div>
                <div class="card-body p-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="fileUrl" readonly>
                        <button class="btn btn-primary" type="button" id="copyUrlBtn">Copy</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
