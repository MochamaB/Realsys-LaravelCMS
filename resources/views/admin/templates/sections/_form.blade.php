<div class="card mb-4">
    <div class="card-header  d-flex justify-content-between align-items-center">
        <h5 class="mb-0" id="form-title">Create New Section</h5>
    </div>
    <div class="card-body">
        <form id="section-form" method="POST">
            @csrf
            <input type="hidden" id="editing" name="id" value="">
            <input type="hidden" id="position" name="position" value="{{ $section->position ?? 0 }}">
            
            <div class="mb-3">
                <label for="name" class="form-label">Section Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" value="{{ $section->name ?? '' }}" required>
                <div class="invalid-feedback">Please provide a section name.</div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="2">{{ $section->description ?? '' }}</textarea>
                <small class="text-muted">Optional brief description of this section's purpose.</small>
            </div>
            
            <div class="mb-3">
                <label for="section_type" class="form-label">Section Type <span class="text-danger">*</span></label>
                <select id="section_type" name="section_type" class="form-select section-type-select" required>
                    @foreach($sectionTypes as $value => $label)
                        <option value="{{ $value }}" {{ ($section->section_type ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-3" id="column-layout-container">
                <label for="column_layout" class="form-label">Column Layout</label>
                <select id="column_layout" name="column_layout" class="form-select column-layout-select" {{ ($section->section_type ?? '') !== 'multi-column' ? 'disabled' : '' }}>
                    <option value="">-- Select Layout --</option>
                    @foreach($columnLayouts as $value => $label)
                        <option value="{{ $value }}" {{ ($section->column_layout ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Bootstrap grid column layout.</small>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" id="is_repeatable" name="is_repeatable" class="form-check-input repeatable-checkbox" {{ ($section->is_repeatable ?? false) ? 'checked' : '' }}>
                <label for="is_repeatable" class="form-check-label">Repeatable Section</label>
                <small class="d-block text-muted">Allow multiple widgets in this section.</small>
            </div>
            
            <div class="mb-3" id="max-widgets-container" style="{{ ($section->is_repeatable ?? false) ? '' : 'display: none;' }}">
                <label for="max_widgets" class="form-label">Max Widgets</label>
                <input type="number" id="max_widgets" name="max_widgets" class="form-control" value="{{ $section->max_widgets ?? '' }}" min="1">
                <small class="text-muted">Maximum number of widgets allowed (leave empty for unlimited).</small>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button type="button" id="cancel-btn" class="btn btn-outline-secondary" style="display: none;">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="submit" id="submit-btn" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Section
                </button>
            </div>
        </form>
    </div>
</div>
