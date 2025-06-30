<div class="modal fade" id="moveFolderModal" tabindex="-1" aria-labelledby="moveFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moveFolderModalLabel">Move to Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="moveToFolderForm">
                    <div class="mb-3">
                        <label for="destinationFolder" class="form-label">Select Destination Folder</label>
                        <select class="form-select" id="destinationFolder" name="folder_id">
                            <option value="root">Root (No Folder)</option>
                            @if(isset($rootFolders) && $rootFolders->count() > 0)
                                @foreach($rootFolders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                    @if($folder->children->count() > 0)
                                        @include('admin.media.partials._folder_options', ['folders' => $folder->children, 'level' => 1])
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <input type="hidden" id="selectedMediaIds" name="media_ids" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMoveBtn">Move Files</button>
            </div>
        </div>
    </div>
</div>
