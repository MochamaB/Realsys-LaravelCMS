<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.media.upload') }}" method="POST" class="dropzone" id="mediaDropzone">
                    @csrf
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                    <div class="dz-message needsclick">
                        <div class="mb-3">
                            <i class="display-4 text-muted ri-upload-cloud-2-line"></i>
                        </div>
                        <h5>Drop files here or click to upload</h5>
                        <span class="text-muted fs-13">Supported formats: Images, Documents, Videos, Audio</span><br>
                        <span class="text-muted fs-13">Max file size: 10MB</span>
                    </div>
                </form>

                <div class="mt-4">
                    <div class="mb-3">
                        <label for="collectionSelect" class="form-label">Media Collection</label>
                        <select class="form-select" id="collectionSelect">
                            <option value="default">Default</option>
                            <option value="images">Images</option>
                            <option value="repeater_images">Repeater Images</option>
                            <option value="documents">Documents</option>
                            <option value="videos">Videos</option>
                            <option value="audio">Audio</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="uploadMediaBtn">Upload</button>
            </div>
        </div>
    </div>
</div>
