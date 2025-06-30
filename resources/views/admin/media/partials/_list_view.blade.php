<div class="table-responsive">
    <table class="table table-nowrap align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">File Name</th>
                <th scope="col">Type</th>
                <th scope="col">Size</th>
                <th scope="col">Collection</th>
                <th scope="col">Upload Date</th>
                <th scope="col" style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mediaItems as $media)
                <tr class="media-item" data-id="{{ $media->id }}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <div class="avatar-title bg-light text-secondary rounded fs-24">
                                    @if(Str::startsWith($media->mime_type, 'image/'))
                                        <i class="ri-image-fill"></i>
                                    @elseif(Str::startsWith($media->mime_type, 'video/'))
                                        <i class="ri-video-fill"></i>
                                    @elseif(Str::startsWith($media->mime_type, 'audio/'))
                                        <i class="ri-music-2-fill"></i>
                                    @elseif(Str::startsWith($media->mime_type, 'application/pdf'))
                                        <i class="ri-file-pdf-fill"></i>
                                    @elseif(Str::contains($media->mime_type, 'spreadsheet') || Str::contains($media->mime_type, 'excel'))
                                        <i class="ri-file-excel-fill"></i>
                                    @elseif(Str::contains($media->mime_type, 'wordprocessingml') || Str::contains($media->mime_type, 'msword'))
                                        <i class="ri-file-word-fill"></i>
                                    @elseif(Str::contains($media->mime_type, 'presentation') || Str::contains($media->mime_type, 'powerpoint'))
                                        <i class="ri-file-ppt-fill"></i>
                                    @else
                                        <i class="ri-file-text-fill"></i>
                                    @endif
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="show-media-details text-body fw-medium" data-id="{{ $media->id }}">
                                {{ Str::limit($media->name, 30) }}
                            </a>
                        </div>
                    </td>
                    <td>{{ Str::afterLast($media->mime_type, '/') }}</td>
                    <td>{{ Str::formatBytes($media->size) }}</td>
                    <td>{{ $media->collection_name }}</td>
                    <td>{{ $media->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-fill align-middle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item show-media-details" href="javascript:void(0);" data-id="{{ $media->id }}">
                                        <i class="ri-eye-fill me-2 align-bottom text-muted"></i>View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ $media->getFullUrl() }}" download="{{ $media->file_name }}">
                                        <i class="ri-download-2-line me-2 align-bottom text-muted"></i>Download
                                    </a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item delete-media" href="javascript:void(0);" data-id="{{ $media->id }}">
                                        <i class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center p-4">
                        <div class="avatar-md mx-auto mb-4">
                            <div class="avatar-title bg-light rounded-circle text-primary">
                                <i class="ri-inbox-archive-line fs-40"></i>
                            </div>
                        </div>
                        <h5>No media files found</h5>
                        <p class="text-muted mb-4">Get started by uploading your first media file</p>
                        <button type="button" class="btn btn-success btn-sm" id="emptyUploadMediaList">
                            <i class="ri-upload-2-fill me-1 align-bottom"></i> Upload Files
                        </button>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
