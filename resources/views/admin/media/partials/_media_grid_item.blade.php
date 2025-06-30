<div class="gallery-box card media-item" data-id="{{ $media->id }}">
    <div class="gallery-container">
        <div class="media-selection">
            <div class="form-check mb-0">
                <input class="form-check-input media-select" type="checkbox" value="{{ $media->id }}" id="mediaCheck{{ $media->id }}">
                <label class="form-check-label" for="mediaCheck{{ $media->id }}"></label>
            </div>
        </div>
        <a class="image-popup show-media-details" href="javascript:void(0);" data-id="{{ $media->id }}">
            @if(strpos($media->mime_type, 'image/') === 0)
                <img class="gallery-img img-fluid mx-auto" src="{{ $media->getUrl('thumb') }}" alt="{{ $media->name }}" />
            @elseif(strpos($media->mime_type, 'video/') === 0)
                <div class="gallery-img video-thumb d-flex align-items-center justify-content-center">
                    <i class="ri-video-line display-4 text-muted"></i>
                </div>
            @elseif(strpos($media->mime_type, 'audio/') === 0)
                <div class="gallery-img audio-thumb d-flex align-items-center justify-content-center">
                    <i class="ri-music-2-line display-4 text-muted"></i>
                </div>
            @elseif(strpos($media->mime_type, 'application/pdf') === 0)
                <div class="gallery-img pdf-thumb d-flex align-items-center justify-content-center">
                    <i class="ri-file-pdf-line display-4 text-danger"></i>
                </div>
            @else
                <div class="gallery-img file-thumb d-flex align-items-center justify-content-center">
                    <i class="ri-file-line display-4 text-muted"></i>
                </div>
            @endif
            <div class="gallery-overlay">
                <h5 class="overlay-caption">{{ $media->name }}</h5>
            </div>
        </a>
    </div>
    <div class="box-content">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <a href="javascript:void(0);" class="show-media-details text-body" data-id="{{ $media->id }}">
                    <h5 class="mb-1">{{ \Illuminate\Support\Str::limit($media->name, 15) }}</h5>
                </a>
                <p class="text-muted mb-0">
                    <small>{{ formatBytes($media->size) }}</small>
                </p>
            </div>
            <div class="flex-shrink-0 d-flex gap-1">
                @if(isset($media->tags) && $media->tags->count() > 0)
                    <div class="media-tags">
                        @foreach($media->tags->take(2) as $tag)
                            <span class="badge" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                        @endforeach
                        @if($media->tags->count() > 2)
                            <span class="badge bg-light text-dark">+{{ $media->tags->count() - 2 }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
