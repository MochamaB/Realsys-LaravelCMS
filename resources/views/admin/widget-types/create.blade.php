@extends('admin.layouts.master')

@section('title', 'Create Widget Type')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- FilePond css -->
    <link href="{{ asset('admin/libs/filepond/filepond.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/libs/filepond/filepond-plugin-image-preview.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Create Widget Type</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.index') }}">Widget Types</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Widget Type Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.widget-types.store') }}" 
                          method="POST" 
                          id="widgetTypeForm"
                          class="row g-3">
                        @csrf

                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug') }}">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty to auto-generate from name</div>
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="component_path" class="form-label">Component Path <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('component_path') is-invalid @enderror" 
                                   id="component_path" 
                                   name="component_path" 
                                   value="{{ old('component_path') }}" 
                                   required>
                            @error('component_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Path to the blade component (e.g., widgets.text-block)</div>
                        </div>

                        <div class="col-md-6">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" 
                                   class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" 
                                   name="icon" 
                                   value="{{ old('icon') }}">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Remix icon class (e.g., ri-text)</div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Fields</h5>
                                        <button type="button" class="btn btn-success btn-sm" id="addField">
                                            <i class="ri-add-line"></i> Add Field
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="fieldsContainer">
                                        <!-- Fields will be added here dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-end">
                                <a href="{{ route('admin.widget-types.index') }}" class="btn btn-light me-1">Cancel</a>
                                <button type="submit" class="btn btn-success">Create Widget Type</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Field Template -->
    <template id="fieldTemplate">
        <div class="field-item border rounded p-3 mb-3">
            <div class="row g-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Field</h6>
                    <button type="button" class="btn btn-danger btn-sm delete-field">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="fields[__INDEX__][name]" 
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Label <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="fields[__INDEX__][label]" 
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Field Type <span class="text-danger">*</span></label>
                    <select class="form-select" 
                            name="fields[__INDEX__][field_type]" 
                            required>
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="rich_text">Rich Text</option>
                        <option value="number">Number</option>
                        <option value="email">Email</option>
                        <option value="url">URL</option>
                        <option value="image">Image</option>
                        <option value="file">File</option>
                        <option value="select">Select</option>
                        <option value="multiselect">Multi Select</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio</option>
                        <option value="date">Date</option>
                        <option value="time">Time</option>
                        <option value="datetime">DateTime</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Default Value</label>
                    <input type="text" 
                           class="form-control" 
                           name="fields[__INDEX__][default_value]">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Help Text</label>
                    <textarea class="form-control" 
                              name="fields[__INDEX__][help_text]" 
                              rows="2"></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Validation Rules</label>
                    <input type="text" 
                           class="form-control" 
                           name="fields[__INDEX__][validation_rules]" 
                           placeholder="e.g., required|max:255">
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch form-switch-success">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="fields[__INDEX__][is_required]">
                        <label class="form-check-label">Required</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch form-switch-success">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="fields[__INDEX__][is_repeatable]">
                        <label class="form-check-label">Repeatable</label>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- FilePond js -->
    <script src="{{ asset('admin/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('admin/libs/filepond/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('admin/libs/filepond/filepond-plugin-file-validate-type.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fieldsContainer = document.getElementById('fieldsContainer');
            const fieldTemplate = document.getElementById('fieldTemplate');
            const addFieldBtn = document.getElementById('addField');
            let fieldIndex = 0;

            // Add field
            addFieldBtn.addEventListener('click', function() {
                const newField = fieldTemplate.content.cloneNode(true);
                
                // Update field index
                newField.querySelectorAll('[name*="__INDEX__"]').forEach(input => {
                    input.name = input.name.replace('__INDEX__', fieldIndex);
                });

                // Add delete handler
                newField.querySelector('.delete-field').addEventListener('click', function() {
                    this.closest('.field-item').remove();
                });

                fieldsContainer.appendChild(newField);
                fieldIndex++;
            });

            // Auto-generate slug
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            nameInput.addEventListener('input', function() {
                if (!slugInput.value) {
                    slugInput.value = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '');
                }
            });
        });
    </script>
@endsection
