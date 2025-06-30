<div class="offcanvas offcanvas-start" tabindex="-1" id="tagSidebar" aria-labelledby="tagSidebarLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="tagSidebarLabel">Media Tags</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Manage Tags</h6>
            <button class="btn btn-sm btn-soft-primary" id="createTagBtn">
                <i class="ri-add-line"></i> New Tag
            </button>
        </div>
        
        <form id="createTagForm" class="mb-4 d-none">
            <div class="mb-3">
                <label for="tagName" class="form-label">Tag Name</label>
                <input type="text" class="form-control" id="tagName" name="name" required>
            </div>
            <div class="mb-3">
                <label for="tagColor" class="form-label">Color</label>
                <input type="color" class="form-control form-control-color" id="tagColor" name="color" value="#6c757d">
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-sm btn-primary">Save Tag</button>
                <button type="button" class="btn btn-sm btn-light ms-2" id="cancelTagBtn">Cancel</button>
            </div>
        </form>
        
        <div id="tagList" class="tag-list">
            @if(isset($tags) && $tags->count() > 0)
                @foreach($tags as $tag)
                <div class="tag-item d-flex align-items-center mb-2" data-id="{{ $tag->id }}">
                    <span class="tag-color me-2" style="background-color: {{ $tag->color }};"></span>
                    <span class="tag-name flex-grow-1">{{ $tag->name }}</span>
                    <span class="badge bg-secondary me-2">{{ $tag->media_count }}</span>
                    <div class="tag-actions">
                        <button type="button" class="btn btn-sm btn-icon tag-edit" title="Edit Tag" data-id="{{ $tag->id }}" data-name="{{ $tag->name }}" data-color="{{ $tag->color }}">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon tag-delete" title="Delete Tag" data-id="{{ $tag->id }}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                            <i class="ri-price-tag-3-line"></i>
                        </div>
                    </div>
                    <h5>No Tags Created</h5>
                    <p class="text-muted">Create tags to help organize your media files</p>
                </div>
            @endif
        </div>
    </div>
</div>
