<div class="modal fade" id="batchTagModal" tabindex="-1" aria-labelledby="batchTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchTagModalLabel">Add Tags to Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="batchTagForm">
                    <div class="mb-3">
                        <label for="batchTagSelect" class="form-label">Select Tags</label>
                        <select class="form-select" id="batchTagSelect" name="tag_ids[]" multiple>
                            @if(isset($tags) && $tags->count() > 0)
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" data-color="{{ $tag->color }}">{{ $tag->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text">Selected tags will be added to all selected files.</div>
                    </div>
                    <input type="hidden" id="batchSelectedMediaIds" name="media_ids" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBatchTagBtn">Apply Tags</button>
            </div>
        </div>
    </div>
</div>
