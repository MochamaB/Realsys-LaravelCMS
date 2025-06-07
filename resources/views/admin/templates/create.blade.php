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
                                        <option value="">-- Select Theme --</option>
                                        @foreach($themes as $theme)
                                            <option value="{{ $theme->id }}" {{ old('theme_id') == $theme->id ? 'selected' : '' }}>
                                                {{ $theme->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('theme_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Selecting a theme will update the available template files</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="file_path" class="form-label">Template File <span class="text-danger">*</span></label>
                                    <select id="file_path" name="file_path" class="form-select @error('file_path') is-invalid @enderror" required>
                                        <option value="">-- Select Template File --</option>
                                        @foreach($templateFiles as $path => $name)
                                            <option value="{{ $path }}" {{ old('file_path') == $path ? 'selected' : '' }}>{{ $name }} ({{ $path }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select an existing template file from the theme's templates directory</small>
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
                                                    <input type="text" name="sections[0][name]" class="form-control" placeholder="e.g., Main Content">
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <label class="form-label">Section Type</label>
                                                    <select name="sections[0][section_type]" class="form-select section-type-select">
                                                        @foreach($sectionTypes as $type => $label)
                                                            <option value="{{ $type }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-2 column-layout-container">
                                                    <label class="form-label">Column Layout</label>
                                                    <select name="sections[0][column_layout]" class="form-select column-layout-select" disabled>
                                                        <option value="">Select Column Layout</option>
                                                        @php
                                                            $columnLayouts = \App\Models\TemplateSection::getColumnLayouts();
                                                        @endphp
                                                        @foreach($columnLayouts as $layout => $label)
                                                            <option value="{{ $layout }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Select how columns should be arranged in this section</small>
                                                </div>
                                                
                                                <div class="mb-2 d-flex gap-2">
                                                    <div>
                                                        <div class="form-check">
                                                            <input type="checkbox" id="is_repeatable_0" name="sections[0][is_repeatable]" class="form-check-input repeatable-checkbox" value="1">
                                                            <label class="form-check-label" for="is_repeatable_0">Repeatable Section</label>
                                                        </div>
                                                    </div>
                                                    <div class="max-widgets-container" style="display: none;">
                                                        <label class="form-label">Max Widgets</label>
                                                        <input type="number" name="sections[0][max_widgets]" class="form-control form-control-sm" min="1" value="10">
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
        const themeSelect = document.getElementById('theme_id');
        const templateFileSelect = document.getElementById('file_path');
        let sectionCount = 1;
        
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
            fetch(`{{ route('templates.files') }}?theme_id=${themeId}`)
                .then(response => response.json())
                .then(files => {
                    // Reset the select
                    templateFileSelect.innerHTML = '<option value="">-- Select Template File --</option>';
                    
                    // Add options for each file
                    for (const [path, name] of Object.entries(files)) {
                        const option = document.createElement('option');
                        option.value = path;
                        option.textContent = `${name} (${path})`;
                        templateFileSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching template files:', error);
                    templateFileSelect.innerHTML = '<option value="">Error loading files</option>';
                });
        });
        
        // Show the remove button for the first section
        sectionsContainer.querySelector('.remove-section').style.display = 'inline-block';
        
        // Function to initialize section type interactions
        function initSectionInteractions(sectionElement) {
            const typeSelect = sectionElement.querySelector('.section-type-select');
            const columnLayoutContainer = sectionElement.querySelector('.column-layout-container');
            const columnLayoutSelect = sectionElement.querySelector('.column-layout-select');
            const repeatableCheckbox = sectionElement.querySelector('.repeatable-checkbox');
            const maxWidgetsContainer = sectionElement.querySelector('.max-widgets-container');
            
            // Handle section type changes
            typeSelect.addEventListener('change', function() {
                if (this.value === 'multi-column') {
                    columnLayoutSelect.disabled = false;
                    columnLayoutSelect.value = '12'; // Default to full width
                    columnLayoutContainer.style.display = 'block';
                } else {
                    // For any other type, disable column layout selection
                    columnLayoutSelect.disabled = true;
                    
                    // For full-width, show the container but set value to 12
                    if (this.value === 'full-width') {
                        columnLayoutContainer.style.display = 'block';
                        columnLayoutSelect.value = '12'; // Full width layout
                    } else {
                        columnLayoutContainer.style.display = 'none';
                        columnLayoutSelect.value = '';
                    }
                }
            });
            
            // Handle repeatable checkbox
            repeatableCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    maxWidgetsContainer.style.display = 'block';
                } else {
                    maxWidgetsContainer.style.display = 'none';
                }
            });
            
            // Trigger initial state
            typeSelect.dispatchEvent(new Event('change'));
            repeatableCheckbox.dispatchEvent(new Event('change'));
        }
        
        // Initialize interactions for the first section
        initSectionInteractions(sectionsContainer.children[0]);
        
        // Add new section
        addSectionBtn.addEventListener('click', function() {
            const newSection = sectionTemplate.cloneNode(true);
            
            // Update input names and IDs with the new index
            const inputs = newSection.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.name = input.name.replace(/sections\[\d+\]/, `sections[${sectionCount}]`);
                if (input.id) {
                    input.id = input.id.replace(/\d+$/, sectionCount);
                }
                
                // Reset values
                if (input.type === 'text' || input.tagName === 'SELECT') {
                    input.value = '';
                } else if (input.type === 'checkbox') {
                    input.checked = false;
                } else if (input.type === 'number') {
                    input.value = '10'; // Default max widgets
                }
            });
            
            // Update labels that reference IDs
            const labels = newSection.querySelectorAll('label[for]');
            labels.forEach(label => {
                if (label.getAttribute('for')) {
                    label.setAttribute('for', label.getAttribute('for').replace(/\d+$/, sectionCount));
                }
            });
            
            // Initialize section interactions
            initSectionInteractions(newSection);
            
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
