{{-- Media Picker Modal - Global Instance --}}
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">Select Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="media-picker-container">
                    <div class="row g-0">
                        {{-- Sidebar Filters --}}
                        <div class="col-md-3 border-end">
                            <div class="p-3">
                                {{-- Search --}}
                                <div class="mb-3">
                                    <input type="text" class="form-control media-search-input" placeholder="Search media...">
                                </div>
                                
                                {{-- Media Type Filter --}}
                                <div class="mb-3">
                                    <label class="form-label">Media Type</label>
                                    <select class="form-select media-type-filter">
                                        <option value="">All Types</option>
                                        <option value="image">Images</option>
                                        <option value="video">Videos</option>
                                        <option value="audio">Audio</option>
                                        <option value="application">Documents</option>
                                    </select>
                                </div>
                                
                                {{-- Folders Filter --}}
                                <div class="mb-3">
                                    <label class="form-label">Folders</label>
                                    <div class="folder-tree">
                                        <ul class="list-unstyled mb-0">
                                            <li>
                                                <a href="#" class="folder-item d-flex align-items-center" data-folder-id="root">
                                                    <i class="ri-folder-line me-2"></i> Root
                                                </a>
                                            </li>
                                            @php
                                                $folders = App\Models\MediaFolder::whereNull('parent_id')->with('children')->get();
                                            @endphp
                                            @foreach($folders as $folder)
                                                <li>
                                                    <a href="#" class="folder-item d-flex align-items-center" data-folder-id="{{ $folder->id }}">
                                                        <i class="ri-folder-line me-2"></i> {{ $folder->name }}
                                                    </a>
                                                    @if($folder->children->count())
                                                        @include('admin.media.partials._folder_tree', ['children' => $folder->children])
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                
                                {{-- Tags Filter --}}
                                <div class="mb-3">
                                    <label class="form-label">Tags</label>
                                    <div class="tag-list">
                                        @php
                                            $tags = App\Models\MediaTag::all();
                                        @endphp
                                        @foreach($tags as $tag)
                                            <a href="#" class="badge bg-light text-dark me-1 mb-1 media-tag-filter" data-tag-id="{{ $tag->id }}">
                                                {{ $tag->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Media Grid --}}
                        <div class="col-md-9">
                            <div class="p-3">
                                <div class="media-items-container row g-2">
                                    {{-- Media items will be loaded here via AJAX --}}
                                    <div class="text-center py-5 w-100 media-loading">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading media...</p>
                                    </div>
                                </div>
                                
                                {{-- Pagination --}}
                                <div class="media-pagination d-flex justify-content-between align-items-center mt-3">
                                    <div class="pagination-info">
                                        <span class="current-page">1</span> of <span class="total-pages">1</span>
                                    </div>
                                    <div class="pagination-controls">
                                        <button type="button" class="btn btn-sm btn-outline-primary prev-page" disabled>
                                            <i class="ri-arrow-left-line"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary next-page ms-2" disabled>
                                            Next <i class="ri-arrow-right-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary confirm-media-selection">Select</button>
            </div>
        </div>
    </div>
</div>
