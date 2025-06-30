<tr class="media-item" data-id="{{ $media->id }}">
    <td>
        <div class="form-check mb-0">
            <input class="form-check-input media-select" type="checkbox" value="{{ $media->id }}" id="mediaListCheck{{ $media->id }}">
            <label class="form-check-label" for="mediaListCheck{{ $media->id }}"></label>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="avatar-sm me-2">
                <div class="avatar-title bg-soft-secondary rounded">
                    @if(strpos($media->mime_type, 'image/') === 0)
                        <img src="{{ $media->getUrl('thumb') }}" alt="{{ $media->name }}" class="avatar-xs rounded" />
                    @elseif(strpos($media->mime_type, 'video/') === 0)
                        <i class="ri-video-line fs-5 text-muted"></i>
                    @elseif(strpos($media->mime_type, 'audio/') === 0)
                        <i class="ri-music-2-line fs-5 text-muted"></i>
                    @elseif(strpos($media->mime_type, 'application/pdf') === 0)
                        <i class="ri-file-pdf-line fs-5 text-danger"></i>
                    @else
                        <i class="ri-file-line fs-5 text-muted"></i>
                    @endif
                </div>
            </div>
            <div>
                <a href="javascript:void(0);" class="show-media-details text-body" data-id="{{ $media->id }}">
                    <h6 class="mb-1">{{ \Illuminate\Support\Str::limit($media->name, 30) }}</h6>
                </a>
                <p class="text-muted mb-0 fs-xs">{{ $media->file_name }}</p>
            </div>
        </div>
    </td>
    <td>{{ formatBytes($media->size) }}</td>
    <td>{{ $media->mime_type }}</td>
    <td>{{ $media->created_at->format('M d, Y') }}</td>
    <td>
        @if(isset($media->tags) && $media->tags->count() > 0)
            @foreach($media->tags->take(2) as $tag)
                <span class="badge" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
            @endforeach
            @if($media->tags->count() > 2)
                <span class="badge bg-light text-dark">+{{ $media->tags->count() - 2 }}</span>
            @endif
        @endif
    </td>
    <td>
        @if($media->folder)
            <span class="text-muted">
                <i class="ri-folder-3-line me-1"></i>{{ $media->folder->name }}
            </span>
        @else
            <span class="text-muted">
                <i class="ri-folder-3-line me-1"></i>Root
            </span>
        @endif
    </td>
</tr>
