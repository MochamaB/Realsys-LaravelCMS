@extends('admin.layouts.master')

@section('title', 'Widget Type Fields')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Dragula css -->
    <link href="{{ asset('assets/admin/libs/dragula/dragula.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Fields for "{{ $widgetType->name }}"</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.index') }}">Widget Types</a></li>
                        <li class="breadcrumb-item active">Fields</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Add New Field</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.widget-types.fields.store', $widgetType) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            <div class="invalid-feedback">Please enter a field name.</div>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="key" class="form-label">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="key" name="key" value="{{ old('key') }}" required>
                            <div class="invalid-feedback">Please enter a field key.</div>
                            <div class="form-text">This is the unique identifier for the field. Use only lowercase letters, numbers, and underscores.</div>
                            @error('key')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="field_type" class="form-label">Field Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="field_type" name="field_type" required>
                                <option value="" selected disabled>Select a field type</option>
                                <option value="text" {{ old('field_type') == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="textarea" {{ old('field_type') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                <option value="rich_text" {{ old('field_type') == 'rich_text' ? 'selected' : '' }}>Rich Text (WYSIWYG)</option>
                                <option value="number" {{ old('field_type') == 'number' ? 'selected' : '' }}>Number</option>
                                <option value="email" {{ old('field_type') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="url" {{ old('field_type') == 'url' ? 'selected' : '' }}>URL</option>
                                <option value="date" {{ old('field_type') == 'date' ? 'selected' : '' }}>Date</option>
                                <option value="time" {{ old('field_type') == 'time' ? 'selected' : '' }}>Time</option>
                                <option value="datetime" {{ old('field_type') == 'datetime' ? 'selected' : '' }}>Date & Time</option>
                                <option value="select" {{ old('field_type') == 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                                <option value="multiselect" {{ old('field_type') == 'multiselect' ? 'selected' : '' }}>Multi-select</option>
                                <option value="radio" {{ old('field_type') == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
                                <option value="checkbox" {{ old('field_type') == 'checkbox' ? 'selected' : '' }}>Checkboxes</option>
                                <option value="image" {{ old('field_type') == 'image' ? 'selected' : '' }}>Image</option>
                                <option value="file" {{ old('field_type') == 'file' ? 'selected' : '' }}>File</option>
                            </select>
                            <div class="invalid-feedback">Please select a field type.</div>
                            @error('field_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            <div class="form-text">Optional description or instructions for content editors.</div>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default_value" class="form-label">Default Value</label>
                            <input type="text" class="form-control" id="default_value" name="default_value" value="{{ old('default_value') }}">
                            <div class="form-text">Default value for this field (optional).</div>
                            @error('default_value')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="validation_rules" class="form-label">Validation Rules</label>
                            <input type="text" class="form-control" id="validation_rules" name="validation_rules" value="{{ old('validation_rules') }}">
                            <div class="form-text">Laravel validation rules (e.g., min:3|max:100).</div>
                            @error('validation_rules')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">Required Field</label>
                            </div>
                            @error('is_required')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_repeatable" name="is_repeatable" value="1" {{ old('is_repeatable') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_repeatable">Repeatable Field</label>
                            </div>
                            <div class="form-text">Allow multiple values for this field.</div>
                            @error('is_repeatable')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Add Field</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Fields List</h4>
                </div>
                <div class="card-body">
                    @if(count($fields) > 0)
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 40px;">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Key</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Required</th>
                                        <th scope="col" style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fields-list">
                                    @foreach($fields as $field)
                                    <tr data-id="{{ $field->id }}">
                                        <td>
                                            <div class="avatar-xs">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    <i class="ri-drag-move-fill"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td>{{ $field->name }}</td>
                                        <td><code>{{ $field->key }}</code></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $field->field_type)) }}</td>
                                        <td>
                                            @if($field->is_required)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-light text-dark">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-3 fs-15">
                                                <a href="#" class="link-primary edit-field" data-bs-toggle="modal" data-bs-target="#editFieldModal" 
                                                   data-id="{{ $field->id }}"
                                                   data-name="{{ $field->name }}"
                                                   data-key="{{ $field->key }}"
                                                   data-type="{{ $field->field_type }}"
                                                   data-description="{{ $field->description }}"
                                                   data-default="{{ $field->default_value }}"
                                                   data-validation="{{ $field->validation_rules }}"
                                                   data-required="{{ $field->is_required }}"
                                                   data-repeatable="{{ $field->is_repeatable }}">
                                                    <i class="ri-pencil-fill"></i>
                                                </a>

                                                @if(in_array($field->field_type, ['select', 'multiselect', 'radio', 'checkbox']))
                                                <a href="{{ route('admin.widget-types.fields.options.index', $field) }}" class="link-info" title="Manage Options">
                                                    <i class="ri-list-settings-line"></i>
                                                </a>
                                                @endif

                                                <a href="#" class="link-danger delete-field" data-id="{{ $field->id }}" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                    <i class="ri-delete-bin-5-line"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-4">
                            <div class="avatar-md mx-auto mb-4">
                                <div class="avatar-title bg-light rounded-circle text-primary fs-24">
                                    <i class="ri-file-list-3-line"></i>
                                </div>
                            </div>
                            <h5 class="mb-3">No fields found</h5>
                            <p class="text-muted mb-4">Create your first field using the form on the left.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Field Modal -->
    <div class="modal fade" id="editFieldModal" tabindex="-1" aria-labelledby="editFieldModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFieldModalLabel">Edit Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editFieldForm" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                                <div class="invalid-feedback">Please enter a field name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_key" class="form-label">Key <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_key" name="key" required>
                                <div class="invalid-feedback">Please enter a field key.</div>
                                <div class="form-text">This is the unique identifier for the field.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_field_type" class="form-label">Field Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_field_type" name="field_type" required>
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="rich_text">Rich Text (WYSIWYG)</option>
                                    <option value="number">Number</option>
                                    <option value="email">Email</option>
                                    <option value="url">URL</option>
                                    <option value="date">Date</option>
                                    <option value="time">Time</option>
                                    <option value="datetime">Date & Time</option>
                                    <option value="select">Select (Dropdown)</option>
                                    <option value="multiselect">Multi-select</option>
                                    <option value="radio">Radio Buttons</option>
                                    <option value="checkbox">Checkboxes</option>
                                    <option value="image">Image</option>
                                    <option value="file">File</option>
                                </select>
                                <div class="invalid-feedback">Please select a field type.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                                <div class="form-text">Optional description or instructions for content editors.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_default_value" class="form-label">Default Value</label>
                                <input type="text" class="form-control" id="edit_default_value" name="default_value">
                                <div class="form-text">Default value for this field (optional).</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_validation_rules" class="form-label">Validation Rules</label>
                                <input type="text" class="form-control" id="edit_validation_rules" name="validation_rules">
                                <div class="form-text">Laravel validation rules (e.g., min:3|max:100).</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="edit_is_required" name="is_required" value="1">
                                    <label class="form-check-label" for="edit_is_required">Required Field</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="edit_is_repeatable" name="is_repeatable" value="1">
                                    <label class="form-check-label" for="edit_is_repeatable">Repeatable Field</label>
                                </div>
                                <div class="form-text">Allow multiple values for this field.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Field</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this field? This will also delete all field values associated with this field, and any options if applicable. This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" action="" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Dragula js -->
    <script src="{{ asset('assets/admin/libs/dragula/dragula.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate key from name
            const nameInput = document.getElementById('name');
            const keyInput = document.getElementById('key');
            
            if (nameInput && keyInput) {
                nameInput.addEventListener('input', function() {
                    keyInput.value = this.value
                        .toLowerCase()
                        .replace(/\s+/g, '_')       // Replace spaces with underscores
                        .replace(/[^a-z0-9_]/g, '') // Remove any character that's not alphanumeric or underscore
                        .replace(/_{2,}/g, '_');    // Replace multiple underscores with a single one
                });
            }

            // Delete field
            const deleteButtons = document.querySelectorAll('.delete-field');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const fieldId = this.getAttribute('data-id');
                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = "{{ route('admin.widget-types.fields.destroy', [$widgetType, '']) }}/" + fieldId;
                });
            });

            // Edit field
            const editButtons = document.querySelectorAll('.edit-field');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const fieldId = this.getAttribute('data-id');
                    const editForm = document.getElementById('editFieldForm');
                    editForm.action = "{{ route('admin.widget-types.fields.update', [$widgetType, '']) }}/" + fieldId;
                    
                    // Populate form fields
                    document.getElementById('edit_name').value = this.getAttribute('data-name');
                    document.getElementById('edit_key').value = this.getAttribute('data-key');
                    
                    const fieldType = this.getAttribute('data-type');
                    const fieldTypeSelect = document.getElementById('edit_field_type');
                    for (let i = 0; i < fieldTypeSelect.options.length; i++) {
                        if (fieldTypeSelect.options[i].value === fieldType) {
                            fieldTypeSelect.options[i].selected = true;
                            break;
                        }
                    }
                    
                    document.getElementById('edit_description').value = this.getAttribute('data-description');
                    document.getElementById('edit_default_value').value = this.getAttribute('data-default');
                    document.getElementById('edit_validation_rules').value = this.getAttribute('data-validation');
                    document.getElementById('edit_is_required').checked = this.getAttribute('data-required') === '1';
                    document.getElementById('edit_is_repeatable').checked = this.getAttribute('data-repeatable') === '1';
                });
            });

            // Initialize Dragula for reordering fields
            if (document.getElementById('fields-list')) {
                const drake = dragula([document.getElementById('fields-list')]);
                
                drake.on('drop', function() {
                    const items = document.querySelectorAll('#fields-list tr');
                    const order = Array.from(items).map(item => item.getAttribute('data-id'));
                    
                    // Save the new order
                    fetch("{{ route('admin.widget-types.fields.order', $widgetType) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Fields reordered successfully',
                                icon: 'success',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to reorder fields',
                                icon: 'error',
                                confirmButtonClass: 'btn btn-danger w-xs me-2',
                                buttonsStyling: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred',
                            icon: 'error',
                            confirmButtonClass: 'btn btn-danger w-xs me-2',
                            buttonsStyling: false
                        });
                    });
                });
            }

            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
@endsection
