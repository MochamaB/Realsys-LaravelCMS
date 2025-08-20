<!-- Asset Manager Modal -->
<div class="modal fade" id="asset-manager-modal" tabindex="-1" aria-labelledby="asset-manager-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asset-manager-modal-label">
                    <i class="ri-folder-image-line me-2"></i>
                    Asset Manager
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Panel - Folders and Filters -->
                    <div class="col-md-3">
                        <!-- Upload Area -->
                        <div class="mb-4">
                            <button class="btn btn-primary w-100" id="upload-assets-btn">
                                <i class="ri-upload-cloud-line me-1"></i>
                                Upload Assets
                            </button>
                            <input type="file" id="asset-upload-input" multiple accept="image/*,video/*,.pdf,.doc,.docx" style="display: none;">
                        </div>
                        
                        <!-- Folders -->
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">
                                <i class="ri-folder-line me-2"></i>
                                Folders
                            </h6>
                            <div class="folder-tree" id="folder-tree">
                                <div class="folder-item active" data-folder-id="">
                                    <i class="ri-folder-open-line me-2"></i>
                                    All Assets
                                </div>
                                <!-- Folders will be loaded here -->
                            </div>
                        </div>
                        
                        <!-- File Type Filter -->
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">
                                <i class="ri-filter-line me-2"></i>
                                File Type
                            </h6>
                            <div class="file-type-filters">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="image" id="filter-images" checked>
                                    <label class="form-check-label" for="filter-images">
                                        <i class="ri-image-line me-1"></i>
                                        Images
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="video" id="filter-videos" checked>
                                    <label class="form-check-label" for="filter-videos">
                                        <i class="ri-video-line me-1"></i>
                                        Videos
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="document" id="filter-documents" checked>
                                    <label class="form-check-label" for="filter-documents">
                                        <i class="ri-file-text-line me-1"></i>
                                        Documents
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="other" id="filter-other" checked>
                                    <label class="form-check-label" for="filter-other">
                                        <i class="ri-file-line me-1"></i>
                                        Other
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Panel - Asset Grid -->
                    <div class="col-md-9">
                        <!-- Search and View Controls -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="flex-grow-1 me-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="ri-search-line"></i>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Search assets..." id="asset-search">
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <!-- View Mode Toggle -->
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="view-mode" id="grid-view" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="grid-view">
                                        <i class="ri-grid-line"></i>
                                    </label>
                                    <input type="radio" class="btn-check" name="view-mode" id="list-view">
                                    <label class="btn btn-outline-secondary btn-sm" for="list-view">
                                        <i class="ri-list-check"></i>
                                    </label>
                                </div>
                                
                                <!-- Sort Options -->
                                <select class="form-select form-select-sm" id="asset-sort" style="width: auto;">
                                    <option value="created_at_desc">Newest First</option>
                                    <option value="created_at_asc">Oldest First</option>
                                    <option value="name_asc">Name A-Z</option>
                                    <option value="name_desc">Name Z-A</option>
                                    <option value="size_desc">Largest First</option>
                                    <option value="size_asc">Smallest First</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Asset Grid -->
                        <div class="asset-grid" id="asset-grid">
                            <!-- Assets will be loaded here -->
                            <div class="text-center py-5" id="asset-loading">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2">Loading assets...</div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small" id="asset-count">
                                <!-- Asset count will be shown here -->
                            </div>
                            <nav aria-label="Asset pagination">
                                <ul class="pagination pagination-sm mb-0" id="asset-pagination">
                                    <!-- Pagination will be generated here -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                
                <!-- Selected Asset Preview -->
                <div class="mt-4 pt-4 border-top" id="selected-asset-preview" style="display: none;">
                    <h6 class="fw-semibold mb-3">
                        <i class="ri-eye-line me-2"></i>
                        Selected Asset
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="asset-preview-image" id="asset-preview-image">
                                <!-- Preview image will be shown here -->
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="asset-details" id="asset-details">
                                <!-- Asset details will be shown here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="select-asset-btn" disabled>
                    <i class="ri-check-line me-1"></i>
                    Select Asset
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Asset Manager Styles */
.folder-tree {
    max-height: 200px;
    overflow-y: auto;
}

.folder-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-radius: 4px;
    margin-bottom: 2px;
    transition: background-color 0.2s ease;
    font-size: 0.875rem;
}

.folder-item:hover {
    background-color: #f8f9fa;
}

.folder-item.active {
    background-color: #e3f2fd;
    color: #0d6efd;
}

.file-type-filters .form-check {
    margin-bottom: 0.5rem;
}

.file-type-filters .form-check-label {
    font-size: 0.875rem;
    cursor: pointer;
}

/* Asset Grid */
.asset-grid {
    min-height: 400px;
    max-height: 500px;
    overflow-y: auto;
}

.asset-grid.grid-mode {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
}

.asset-grid.list-mode {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* Asset Items */
.asset-item {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
}

.asset-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
    transform: translateY(-2px);
}

.asset-item.selected {
    border-color: #0d6efd;
    background-color: #e3f2fd;
}

/* Grid Mode Asset Items */
.asset-grid.grid-mode .asset-item {
    text-align: center;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.asset-grid.grid-mode .asset-thumbnail {
    width: 100%;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.asset-grid.grid-mode .asset-icon {
    font-size: 2rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.asset-grid.grid-mode .asset-name {
    font-size: 0.75rem;
    font-weight: 500;
    color: #495057;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.asset-grid.grid-mode .asset-size {
    font-size: 0.6875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* List Mode Asset Items */
.asset-grid.list-mode .asset-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
}

.asset-grid.list-mode .asset-thumbnail {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 1rem;
    flex-shrink: 0;
}

.asset-grid.list-mode .asset-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
    margin-right: 1rem;
    flex-shrink: 0;
    font-size: 1.25rem;
    color: #6c757d;
}

.asset-grid.list-mode .asset-info {
    flex-grow: 1;
}

.asset-grid.list-mode .asset-name {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
}

.asset-grid.list-mode .asset-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Asset Preview */
.asset-preview-image {
    text-align: center;
}

.asset-preview-image img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.asset-preview-icon {
    width: 100px;
    height: 100px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 3rem;
    color: #6c757d;
}

.asset-details {
    font-size: 0.875rem;
}

.asset-detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f8f9fa;
}

.asset-detail-label {
    font-weight: 600;
    color: #495057;
}

.asset-detail-value {
    color: #6c757d;
    text-align: right;
}

/* Upload Progress */
.upload-progress {
    margin-top: 1rem;
}

.upload-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.upload-item-name {
    flex-grow: 1;
    font-size: 0.875rem;
    margin-right: 1rem;
}

.upload-item-progress {
    width: 100px;
    margin-right: 1rem;
}

.upload-item-status {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 767.98px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .asset-grid.grid-mode {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 0.75rem;
    }
    
    .asset-grid.list-mode .asset-item {
        padding: 0.5rem;
    }
    
    .asset-grid.list-mode .asset-thumbnail,
    .asset-grid.list-mode .asset-icon {
        width: 32px;
        height: 32px;
        margin-right: 0.75rem;
    }
}
</style>
