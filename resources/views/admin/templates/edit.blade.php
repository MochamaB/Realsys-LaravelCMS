@extends('admin.layouts.master')

@section('title', 'Edit Template')
@section('page-title', 'Edit Template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Template: {{ $template->name }}</h5>
                    <div>
                        <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-info me-2">
                            <i class="mdi mdi-eye me-1"></i> View Details
                        </a>
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Templates
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.update', $template) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Template Information -->
                                <div class="mb-3">
                                    <label for="theme_id" class="form-label">Theme <span class="text-danger">*</span></label>
                                    <select id="theme_id" name="theme_id" class="form-select @error('theme_id') is-invalid @enderror" required>
                                        <option value="">Select Theme</option>
                                        @foreach($themes as $theme)
                                            <option value="{{ $theme->id }}" {{ old('theme_id', $template->theme_id) == $theme->id ? 'selected' : '' }}>
                                                {{ $theme->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('theme_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="file_path" class="form-label">File Path <span class="text-danger">*</span></label>
                                    <input type="text" id="file_path" name="file_path" class="form-control @error('file_path') is-invalid @enderror" value="{{ old('file_path', $template->file_path) }}" placeholder="e.g., default.blade.php" required>
                                    <small class="text-muted">Relative path to the template file in the theme's templates directory</small>
                                    @error('file_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $template->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="thumbnail" class="form-label">Thumbnail</label>
                                    <input type="file" id="thumbnail" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror">
                                    <small class="text-muted">Upload a preview image of this template (recommended size: 800x600px)</small>
                                    @if($template->thumbnail_path)
                                        <div class="mt-2">
                                            <img src="{{ asset($template->thumbnail_path) }}" alt="{{ $template->name }}" class="img-thumbnail" style="max-width: 200px;">
                                            <p class="text-muted small">Current thumbnail. Upload a new one to replace it.</p>
                                        </div>
                                    @endif
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_default" name="is_default" class="form-check-input @error('is_default') is-invalid @enderror" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">Set as default template for this theme</label>
                                    </div>
                                    @error('is_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Template Sections</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($template->sections->isEmpty())
                                            <p class="text-muted">This template has no sections yet.</p>
                                        @else
                                            <ul class="list-group">
                                                @foreach($template->sections as $section)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $section->name }}</strong>
                                                            <span class="badge bg-primary ms-2">{{ $sectionTypes[$section->type] ?? 'Unknown' }}</span>
                                                            @if($section->is_required)
                                                                <span class="badge bg-danger ms-1">Required</span>
                                                            @endif
                                                            <div class="text-muted small">{{ $section->width ?? 'Default width' }}</div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        
                                        <div class="mt-3">
                                            <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-sm btn-primary">
                                                <i class="mdi mdi-puzzle-plus me-1"></i> Manage Sections
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
