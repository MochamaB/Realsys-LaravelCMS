<style>
    .media-grid-item {
        aspect-ratio: 1/1;
        margin-bottom: 24px;
    }
    .media-grid-item .card {
        height: 100%;
    }
    .media-grid-item .gallery-container {
        height: calc(100% - 60px); /* Subtract the height of the info bar */
    }
    .media-grid-item .gallery-img {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }
    .media-grid-item .gallery-img.position-relative {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
    }
    .media-grid-item .bg-secondary {
        width: 100%;
        height: 100%;
    }
</style>

<div class="row">
    @forelse($mediaItems as $media)
        <div class="col-xxl-3 col-lg-4 col-md-6 col-sm-6 media-grid-item media-item" data-id="{{ $media->id }}">
            <div class="card gallery-box card-animate">
                <div class="gallery-container">
                    @if(Str::startsWith($media->mime_type, 'image/'))
                        <a href="javascript:void(0);" class="show-media-details d-flex justify-content-center align-items-center h-100" data-id="{{ $media->id }}" title="{{ $media->name }}">
                            <img class="gallery-img" src="{{ $media->getFullUrl() }}" alt="{{ $media->custom_properties['alt'] ?? $media->name }}" />
                        </a>
                    @elseif(Str::startsWith($media->mime_type, 'video/'))
                        <a href="javascript:void(0);" class="show-media-details" data-id="{{ $media->id }}">
                            <div class="gallery-img position-relative">
                                <div class="bg-secondary d-flex justify-content-center align-items-center h-100">
                                    <i class="ri-video-line fs-1 text-white"></i>
                                </div>
                                <div class="gallery-icon"><i class="ri-play-circle-line text-white"></i></div>
                            </div>
                        </a>
                    @elseif(Str::startsWith($media->mime_type, 'audio/'))
                        <a href="javascript:void(0);" class="show-media-details" data-id="{{ $media->id }}">
                            <div class="gallery-img position-relative">
                                <div class="bg-secondary d-flex justify-content-center align-items-center h-100">
                                    <i class="ri-music-2-line fs-1 text-white"></i>
                                </div>
                                <div class="gallery-icon"><i class="ri-play-circle-line text-white"></i></div>
                            </div>
                        </a>
                    @elseif(Str::startsWith($media->mime_type, 'application/pdf'))
                        <a href="javascript:void(0);" class="show-media-details" data-id="{{ $media->id }}">
                            <div class="gallery-img position-relative">
                                <div class="bg-secondary d-flex justify-content-center align-items-center h-100">
                                    <i class="ri-file-pdf-line fs-1 text-white"></i>
                                </div>
                            </div>
                        </a>
                    @else
                        <a href="javascript:void(0);" class="show-media-details" data-id="{{ $media->id }}">
                            <div class="gallery-img position-relative">
                                <div class="bg-secondary d-flex justify-content-center align-items-center h-100">
                                    <i class="ri-file-text-line fs-1 text-white"></i>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>
                <div class="box-content">
                    <div class="d-flex align-items-center mt-1">
                        <div class="flex-grow-1 text-truncate">
                            <a href="javascript:void(0);" class="show-media-details text-reset fs-15" data-id="{{ $media->id }}" title="{{ $media->name }}">
                                {{ Str::limit($media->name, 20) }}
                            </a>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="text-reset dropdown-toggle fs-16" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item show-media-details" href="javascript:void(0);" data-id="{{ $media->id }}"><i class="ri-eye-fill me-2 align-bottom text-muted"></i>View Details</a></li>
                                    <li><a class="dropdown-item" href="{{ $media->getFullUrl() }}" download="{{ $media->file_name }}"><i class="ri-download-2-line me-2 align-bottom text-muted"></i>Download</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item delete-media" href="javascript:void(0);" data-id="{{ $media->id }}">
                                            <i class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted text-sm mt-1">
                        {{ Str::formatBytes($media->size) }} · {{ $media->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center p-5">
                <div class="avatar-md mx-auto mb-4">
                    <div class="avatar-title bg-light rounded-circle text-primary">
                        <i class="ri-inbox-archive-line fs-40"></i>
                    </div>
                </div>
                <h5>No media files found</h5>
                <p class="text-muted mb-4">Get started by uploading your first media file</p>
                <button type="button" class="btn btn-success btn-sm" id="emptyUploadMedia">
                    <i class="ri-upload-2-fill me-1 align-bottom"></i> Upload Files
                </button>
            </div>
        </div>
    @endforelse
</div>
