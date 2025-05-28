@extends('admin.layouts.master')

@section('title', 'Create Template')
@section('page-title', 'Create Template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create New Template</h5>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Templates
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Template Information -->
                                <div class="mb-3">
                                    <label for="theme_id" class="form-label">Theme <span class="text-danger">*</span></label>
                                    <select id="theme_id" name="theme_id" class="form-select @error('theme_id') is-invalid @enderror" required>
                                        <option value="">Select Theme</option>
                                        @foreach($themes as $theme)
                                            <option value="{{ $theme->id }}" {{ old('theme_id') == $theme->id ? 'selected' : '' }}>
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
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="file_path" class="form-label">File Path <span class="text-danger">*</span></label>
                                    <input type="text" id="file_path" name="file_path" class="form-control @error('file_path') is-invalid @enderror" value="{{ old('file_path') }}" placeholder="e.g., default.blade.php" required>
                                    <small class="text-muted">Relative path to the template file in the theme's templates directory</small>
                                    @error('file_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="thumbnail" class="form-label">Thumbnail</label>
                                    <input type="file" id="thumbnail" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror">
                                    <small class="text-muted">Upload a preview image of this template (recommended size: 800x600px)</small>
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_default" name="is_default" class="form-check-input @error('is_default') is-invalid @enderror" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">Set as default template for this theme</label>
                                    </div>
                                    @error('is_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Initial Sections</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">Add initial sections to your template. You can add more sections later.</p>
                                        
                                        <div id="sections-container">
                                            <!-- Section template will be cloned here -->
                                            <div class="section-item mb-3 p-3 border rounded">
                                                <div class="mb-2">
                                                    <label class="form-label">Section Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="sections[0][name]" class="form-control" placeholder="e.g., Header">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Type</label>
                                                    <select name="sections[0][type]" class="form-select">
                                                        @foreach($sectionTypes as $type => $label)
                                                            <option value="{{ $type }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Width</label>
                                                    <input type="text" name="sections[0][width]" class="form-control" placeholder="e.g., col-12">
                                                    <small class="text-muted">Leave empty for default width</small>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="sections[0][is_required]" class="form-check-input" value="1">
                                                        <label class="form-check-label">Required Section</label>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-section" style="display:none;">Remove</button>
                                            </div>
                                        </div>
                                        
                                        <button type="button" id="add-section" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-plus-circle me-1"></i> Add Section
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Template</button>
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
        const sectionsContainer = document.getElementById('sections-container');
        const addSectionBtn = document.getElementById('add-section');
        const sectionTemplate = sectionsContainer.children[0].cloneNode(true);
        let sectionCount = 1;
        
        // Show the remove button for the first section
        sectionsContainer.querySelector('.remove-section').style.display = 'inline-block';
        
        // Add new section
        addSectionBtn.addEventListener('click', function() {
            const newSection = sectionTemplate.cloneNode(true);
            
            // Update input names with the new index
            const inputs = newSection.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.name = input.name.replace(/sections\[\d+\]/, `sections[${sectionCount}]`);
                if (input.type === 'text' || input.tagName === 'SELECT') {
                    input.value = '';
                } else if (input.type === 'checkbox') {
                    input.checked = false;
                }
            });
            
            // Show remove button
            newSection.querySelector('.remove-section').style.display = 'inline-block';
            
            // Add event listener to remove button
            newSection.querySelector('.remove-section').addEventListener('click', function() {
                newSection.remove();
            });
            
            // Append the new section
            sectionsContainer.appendChild(newSection);
            sectionCount++;
        });
        
        // Add event listener to the first section's remove button
        sectionsContainer.querySelector('.remove-section').addEventListener('click', function(e) {
            e.target.closest('.section-item').remove();
        });
    });
</script>
@endsection
