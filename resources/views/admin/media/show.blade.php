@extends('admin.layouts.master')

@section('title') Media Details @endsection

@section('css')
<!-- Additional CSS for media detail page -->
@endsection

@section('content')
    @component('admin.partials.breadcrumb')
        @slot('li_1') Dashboard @endslot
        @slot('li_2') Media Library @endslot
        @slot('title') Media Details @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">{{ $media->name }}</h5>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.media.index') }}" class="btn btn-light btn-sm">
                                <i class="ri-arrow-left-line align-middle"></i> Back to Media Library
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="media-preview mb-4 text-center">
                                @if(Str::startsWith($media->mime_type, 'image/'))
                                    <img src="{{ $media->getFullUrl() }}" alt="{{ $media->custom_properties['alt'] ?? $media->name }}" class="img-fluid rounded" style="max-height: 500px;">
                                @elseif(Str::startsWith($media->mime_type, 'video/'))
                                    <video controls class="img-fluid rounded" style="max-height: 500px;">
                                        <source src="{{ $media->getFullUrl() }}" type="{{ $media->mime_type }}">
                                        Your browser does not support the video tag.
                                    </video>
                                @elseif(Str::startsWith($media->mime_type, 'audio/'))
                                    <div class="p-4 bg-light rounded text-center mb-3">
                                        <i class="ri-music-2-line display-1 text-primary"></i>
                                    </div>
                                    <audio controls class="w-100">
                                        <source src="{{ $media->getFullUrl() }}" type="{{ $media->mime_type }}">
                                        Your browser does not support the audio element.
                                    </audio>
                                @elseif($media->mime_type === 'application/pdf')
                                    <div class="p-4 bg-light rounded text-center">
                                        <i class="ri-file-pdf-line display-1 text-danger"></i>
                                        <p class="mt-3 mb-0">PDF Document</p>
                                    </div>
                                @else
                                    <div class="p-4 bg-light rounded text-center">
                                        <i class="ri-file-text-line display-1 text-primary"></i>
                                        <p class="mt-3 mb-0">{{ Str::afterLast($media->mime_type, '/') }} File</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="card border shadow-none mb-3">
                                <div class="card-header border-bottom p-3">
                                    <h6 class="card-title mb-0">Actions</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-lg-6">
                                            <a href="{{ $media->getFullUrl() }}" download="{{ $media->file_name }}" class="btn btn-light w-100">
                                                <i class="ri-download-2-line align-bottom me-1"></i> Download
                                            </a>
                                        </div>
                                        <div class="col-lg-6">
                                            <button type="button" class="btn btn-danger w-100 delete-media" data-id="{{ $media->id }}">
                                                <i class="ri-delete-bin-line align-bottom me-1"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border shadow-none">
                                <div class="card-header border-bottom p-3">
                                    <h6 class="card-title mb-0">Media URL</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="fileUrl" value="{{ $media->getFullUrl() }}" readonly>
                                        <button class="btn btn-primary" type="button" id="copyUrlBtn">Copy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- File Information -->
                            <div class="card border shadow-none mb-3">
                                <div class="card-header border-bottom p-3">
                                    <h6 class="card-title mb-0">File Information</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <th class="ps-0 text-muted" scope="row">File Name:</th>
                                                    <td class="text-end">{{ $media->file_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0 text-muted" scope="row">File Type:</th>
                                                    <td class="text-end">{{ $media->mime_type }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0 text-muted" scope="row">File Size:</th>
                                                    <td class="text-end">{{ number_format($media->size / 1024, 2) }} KB</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0 text-muted" scope="row">Collection:</th>
                                                    <td class="text-end">{{ $media->collection_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0 text-muted" scope="row">Created:</th>
                                                    <td class="text-end">{{ $media->created_at->format('M d, Y \a\t h:i A') }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0 text-muted" scope="row">Updated:</th>
                                                    <td class="text-end">{{ $media->updated_at->format('M d, Y \a\t h:i A') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Media Metadata Form -->
                            <div class="card border shadow-none mb-3">
                                <div class="card-header border-bottom p-3">
                                    <h6 class="card-title mb-0">Edit Metadata</h6>
                                </div>
                                <div class="card-body p-3">
                                    <form id="mediaMetadataForm" method="POST" action="{{ route('admin.media.update', $media->id) }}">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $media->name }}" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="alt" class="form-label">Alt Text</label>
                                            <input type="text" class="form-control" id="alt" name="alt" value="{{ $media->custom_properties['alt'] ?? '' }}">
                                            <div class="form-text">Alternative text for screen readers</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title Attribute</label>
                                            <input type="text" class="form-control" id="title" name="title" value="{{ $media->custom_properties['title'] ?? '' }}">
                                            <div class="form-text">Text shown on hover</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="caption" class="form-label">Caption</label>
                                            <textarea class="form-control" id="caption" name="caption" rows="3">{{ $media->custom_properties['caption'] ?? '' }}</textarea>
                                            <div class="form-text">Displayed below the image</div>
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Update Metadata</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Copy URL button
    document.getElementById('copyUrlBtn').addEventListener('click', function() {
        const urlInput = document.getElementById('fileUrl');
        urlInput.select();
        document.execCommand('copy');
        this.textContent = 'Copied!';
        setTimeout(() => {
            this.textContent = 'Copy';
        }, 2000);
    });
    
    // Delete button
    document.querySelector('.delete-media').addEventListener('click', function() {
        const mediaId = this.getAttribute('data-id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form element for DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/media/${mediaId}`;
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                // Add method spoofing
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                // Append to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection
