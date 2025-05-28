@extends('admin.layouts.master')

@section('title', 'Edit Theme')
@section('page-title', 'Edit Theme')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.themes.update', $theme) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Theme Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $theme->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $theme->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="version" class="form-label">Version</label>
                                    <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $theme->version) }}">
                                    @error('version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control @error('author') is-invalid @enderror" id="author" name="author" value="{{ old('author', $theme->author) }}">
                                    @error('author')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="screenshot" class="form-label">Theme Screenshot</label>
                            
                            @if($theme->screenshot_path)
                                <div class="mb-3">
                                    <img src="{{ asset($theme->screenshot_path) }}" class="img-thumbnail" style="max-height: 200px;" alt="{{ $theme->name }} Screenshot">
                                    <div class="form-text">Current screenshot</div>
                                </div>
                            @endif
                            
                            <input type="file" class="form-control @error('screenshot') is-invalid @enderror" id="screenshot" name="screenshot">
                            @error('screenshot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Upload a new screenshot to replace the current one. Leave empty to keep the existing screenshot.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.themes.show', $theme) }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Theme</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Theme Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Theme Details</h6>
                        <p class="small mb-1">
                            <strong>Slug:</strong> {{ $theme->slug }}
                        </p>
                        <p class="small mb-1">
                            <strong>Created:</strong> {{ $theme->created_at->format('M d, Y') }}
                        </p>
                        <p class="small mb-1">
                            <strong>Last Updated:</strong> {{ $theme->updated_at->format('M d, Y') }}
                        </p>
                        <p class="small mb-1">
                            <strong>Status:</strong> 
                            @if($theme->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <h6>Theme Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.themes.show', $theme) }}" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-eye me-1"></i> View Details
                            </a>
                            
                            <a href="{{ route('admin.themes.preview', $theme) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="mdi mdi-web me-1"></i> Preview Theme
                            </a>
                            
                            @if(!$theme->is_active)
                                <form action="{{ route('admin.themes.activate', $theme) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                        <i class="mdi mdi-check-circle me-1"></i> Activate Theme
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
