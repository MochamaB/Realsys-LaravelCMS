@extends('admin.layouts.master')

@section('title', 'Add Section')
@section('page-title', 'Add Section')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Section to Template: {{ $template->name }}</h5>
                    <div>
                        <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Sections
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.sections.store', $template) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Section Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    <small class="text-muted">This will be used to identify the section in the admin interface.</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    <small class="text-muted">A brief description of this section's purpose.</small>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Section Type <span class="text-danger">*</span></label>
                                            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                @foreach($sectionTypes as $type => $label)
                                                    <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Determines the section's purpose and default styling.</small>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="width" class="form-label">Width</label>
                                            <input type="text" id="width" name="width" class="form-control @error('width') is-invalid @enderror" value="{{ old('width') }}" placeholder="e.g., col-md-6">
                                            <small class="text-muted">CSS class for width (e.g., col-12, col-md-6). Leave empty for default width based on type.</small>
                                            @error('width')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_widgets" class="form-label">Max Widgets</label>
                                            <input type="number" id="max_widgets" name="max_widgets" class="form-control @error('max_widgets') is-invalid @enderror" value="{{ old('max_widgets') }}" min="1">
                                            <small class="text-muted">Maximum number of widgets allowed in this section. Leave empty for unlimited.</small>
                                            @error('max_widgets')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="order_index" class="form-label">Order Index</label>
                                            <input type="number" id="order_index" name="order_index" class="form-control @error('order_index') is-invalid @enderror" value="{{ old('order_index', $nextOrderIndex) }}" min="0">
                                            <small class="text-muted">Position of this section in the template. Lower numbers appear first.</small>
                                            @error('order_index')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_required" name="is_required" class="form-check-input @error('is_required') is-invalid @enderror" value="1" {{ old('is_required') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_required">Required Section</label>
                                        <small class="d-block text-muted">If checked, this section cannot be removed from pages using this template.</small>
                                    </div>
                                    @error('is_required')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Advanced Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Custom Settings (JSON)</label>
                                            <textarea name="settings" class="form-control @error('settings') is-invalid @enderror" rows="6" placeholder='{
    "custom_class": "",
    "background": "light",
    "padding": "normal"
}'>{{ old('settings') }}</textarea>
                                            <small class="text-muted">Optional JSON configuration for advanced section settings.</small>
                                            @error('settings')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            <small>These settings will be available to template developers through the section's settings property.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
