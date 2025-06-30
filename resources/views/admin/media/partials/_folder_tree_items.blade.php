@foreach($folders as $folder)
    <div class="folder-item ms-{{ $level * 15 }}" data-id="{{ $folder->id }}">
        <div class="folder-link">
            @if($folder->children->count() > 0)
                <i class="folder-collapse ri-arrow-right-s-line me-1"></i>
            @else
                <i class="folder-spacer me-1">&nbsp;</i>
            @endif
            
            <i class="ri-folder-3-fill text-warning me-1"></i>
            <span>{{ $folder->name }}</span>
            <span class="ms-auto badge bg-secondary">{{ $folder->media_count }}</span>
            
            <div class="folder-actions ms-2">
                <button type="button" class="btn btn-sm btn-icon folder-add" title="Add Subfolder" data-id="{{ $folder->id }}">
                    <i class="ri-add-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon folder-edit" title="Edit Folder" data-id="{{ $folder->id }}" data-name="{{ $folder->name }}">
                    <i class="ri-edit-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon folder-delete" title="Delete Folder" data-id="{{ $folder->id }}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        
        @if($folder->children->count() > 0)
            <div class="folder-children" style="display: none;">
                @include('admin.media.partials._folder_tree_items', ['folders' => $folder->children, 'level' => $level + 1])
            </div>
        @endif
    </div>
@endforeach
