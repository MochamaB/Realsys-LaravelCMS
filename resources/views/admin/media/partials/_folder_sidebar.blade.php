<div class="offcanvas offcanvas-start" tabindex="-1" id="folderSidebar" aria-labelledby="folderSidebarLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="folderSidebarLabel">Folders</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Media Folders</h6>
            <button class="btn btn-sm btn-soft-primary" id="createRootFolderBtn">
                <i class="ri-add-line"></i> New Root Folder
            </button>
        </div>
        
        <div class="folder-tree">
            <!-- Root folder -->
            <div class="folder-item active" data-id="root">
                <div class="folder-link">
                    <i class="ri-folder-3-fill text-warning me-1"></i>
                    <span>All Files</span>
                    <span class="ms-auto badge bg-secondary">{{ $stats['total'] ?? 0 }}</span>
                </div>
            </div>
            
            <!-- Folder tree structure -->
            @if(isset($rootFolders) && $rootFolders->count() > 0)
                @include('admin.media.partials._folder_tree_items', ['folders' => $rootFolders, 'level' => 0])
            @endif
        </div>
    </div>
</div>
