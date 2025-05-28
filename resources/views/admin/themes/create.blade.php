@extends('admin.layouts.master')

@section('title', 'Add New Theme')
@section('page-title', 'Add New Theme')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.themes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Theme Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">The name of your theme as it will appear in the admin panel.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">A brief description of your theme and its features.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="version" class="form-label">Version</label>
                                    <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version') }}">
                                    @error('version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">The version number of your theme (e.g., 1.0.0).</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control @error('author') is-invalid @enderror" id="author" name="author" value="{{ old('author') }}">
                                    @error('author')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">The name of the theme author or company.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="screenshot" class="form-label">Theme Screenshot</label>
                            <input type="file" class="form-control @error('screenshot') is-invalid @enderror" id="screenshot" name="screenshot">
                            @error('screenshot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Upload a screenshot or preview image of your theme. Recommended size: 1200 × 800 pixels.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.themes.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Theme</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Theme Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Required Structure</h6>
                        <p class="small mb-2">Themes should follow this structure:</p>
                        <pre class="bg-light p-2 rounded small">
theme-name/
├── theme.json
├── assets/
├── templates/
└── sections/</pre>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Theme Configuration</h6>
                        <p class="small">The theme.json file should include metadata about your theme, available templates, and sections.</p>
                    </div>
                    
                    <div>
                        <h6>Screenshot Guidelines</h6>
                        <p class="small">Upload a clear, high-quality screenshot that represents your theme's design. This image will be displayed in the theme selection interface.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
