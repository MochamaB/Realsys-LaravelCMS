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
                                    <label for="file_path" class="form-label">Template File <span class="text-danger">*</span></label>
                                    <select id="file_path" name="file_path" class="form-select @error('file_path') is-invalid @enderror" required>
                                        <option value="">-- Select Template File --</option>
                                        @foreach($templateFiles as $path => $name)
                                            <option value="{{ $path }}" {{ old('file_path', $template->file_path) == $path ? 'selected' : '' }}>
                                                {{ $name }} ({{ $path }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select an existing template file from the theme's templates directory</small>
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
                                                            <span class="badge bg-primary ms-2">{{ $sectionTypes[$section->section_type] ?? 'Unknown' }}</span>
                                                            
                                                            @if($section->is_repeatable)
                                                                <span class="badge bg-info ms-1">Repeatable</span>
                                                            @endif
                                                            
                                                            @if($section->column_layout)
                                                                <div class="text-muted small">Layout: {{ $section->column_layout }}</div>
                                                            @endif
                                                            
                                                            @if($section->max_widgets)
                                                                <div class="text-muted small">Max widgets: {{ $section->max_widgets }}</div>
                                                            @endif
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeSelect = document.getElementById('theme_id');
        const templateFileSelect = document.getElementById('file_path');
        const currentFilePath = '{{ $template->file_path }}';
        
        // Theme selection change event
        themeSelect.addEventListener('change', function() {
            const themeId = this.value;
            if (!themeId) {
                // Clear template files if no theme selected
                templateFileSelect.innerHTML = '<option value="">-- Select Template File --</option>';
                return;
            }
            
            // Show loading message
            templateFileSelect.innerHTML = '<option value="">Loading template files...</option>';
            
            // Fetch template files for selected theme
            fetch(`{{ route('admin.templates.files') }}?theme_id=${themeId}`)
                .then(response => response.json())
                .then(files => {
                    // Reset the select
                    templateFileSelect.innerHTML = '<option value="">-- Select Template File --</option>';
                    
                    // Add options for each file
                    for (const [path, name] of Object.entries(files)) {
                        const option = document.createElement('option');
                        option.value = path;
                        option.textContent = `${name} (${path})`;
                        
                        // If this is the currently selected file, select it
                        if (path === currentFilePath) {
                            option.selected = true;
                        }
                        
                        templateFileSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching template files:', error);
                    templateFileSelect.innerHTML = '<option value="">Error loading files</option>';
                });
        });
    });
</script>
@endsection
