@extends('admin.layouts.master')

@section('title', 'Create Template Section')

@section('css')
<link href="{{ asset('assets/admin/css/template-sections.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h5 class="h4">Create New Section for: {{ $template->name }}</h5>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-secondary me-2">
                <i class="ri-arrow-left-line"></i> Back to Sections
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Section Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.sections.store', $template) }}" method="POST">
                        @csrf
                        <input type="hidden" name="position" value="{{ $newSection->position ?? 0 }}">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            <small class="text-muted">Optional brief description of this section's purpose.</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="section_type" class="form-label">Section Type <span class="text-danger">*</span></label>
                            <select id="section_type" name="section_type" class="form-select section-type-select @error('section_type') is-invalid @enderror" required>
                                @foreach($sectionTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('section_type', $newSection->section_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('section_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3" id="column-layout-container">
                            <label for="column_layout" class="form-label">Column Layout</label>
                            <select id="column_layout" name="column_layout" class="form-select column-layout-select @error('column_layout') is-invalid @enderror" {{ old('section_type', $newSection->section_type) !== 'multi-column' ? 'disabled' : '' }}>
                                <option value="">-- Select Layout --</option>
                                @foreach($columnLayouts as $value => $label)
                                    <option value="{{ $value }}" {{ old('column_layout', $newSection->column_layout) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Bootstrap grid column layout.</small>
                            @error('column_layout')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" id="is_repeatable" name="is_repeatable" class="form-check-input repeatable-checkbox @error('is_repeatable') is-invalid @enderror" value="1" {{ old('is_repeatable', $newSection->is_repeatable) ? 'checked' : '' }}>
                            <label for="is_repeatable" class="form-check-label">Repeatable Section</label>
                            <small class="d-block text-muted">Allow multiple widgets in this section.</small>
                            @error('is_repeatable')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3" id="max-widgets-container" style="{{ old('is_repeatable', $newSection->is_repeatable) ? '' : 'display: none;' }}">
                            <label for="max_widgets" class="form-label">Max Widgets</label>
                            <input type="number" id="max_widgets" name="max_widgets" class="form-control @error('max_widgets') is-invalid @enderror" value="{{ old('max_widgets', $newSection->max_widgets) }}" min="1">
                            <small class="text-muted">Maximum number of widgets allowed (leave empty for unlimited).</small>
                            @error('max_widgets')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-outline-secondary">
                                <i class="ri-close-line"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-add-line"></i> Create Section
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Section type change handler - show/hide column layout
    const sectionTypeSelect = document.getElementById('section_type');
    const columnLayoutContainer = document.getElementById('column-layout-container');
    const columnLayoutSelect = document.getElementById('column_layout');
    
    sectionTypeSelect.addEventListener('change', function() {
        if (this.value === 'multi-column') {
            columnLayoutSelect.disabled = false;
            columnLayoutContainer.style.display = 'block';
        } else {
            columnLayoutSelect.disabled = true;
            // Don't hide the container, just disable the select
        }
    });
    
    // Repeatable checkbox handler - show/hide max widgets
    const repeatableCheckbox = document.getElementById('is_repeatable');
    const maxWidgetsContainer = document.getElementById('max-widgets-container');
    
    repeatableCheckbox.addEventListener('change', function() {
        maxWidgetsContainer.style.display = this.checked ? 'block' : 'none';
    });
});
</script>
@endsection
