@extends('admin.layouts.master')

@section('title', 'Edit Field')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Field: {{ $field->name }}</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.content-types.fields.index', $contentType) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Fields
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form card -->
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.content-types.fields.update', [$contentType, $field]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Field Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $field->name) }}" required>
                            <small class="text-muted">Human-readable name for this field</small>
                        </div>

                        <div class="mb-3">
                            <label for="key" class="form-label">Field Key</label>
                            <input type="text" class="form-control" id="key" name="key" value="{{ old('key', $field->key) }}" required>
                            <small class="text-muted">Machine name for this field (changing this may break existing content)</small>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Field Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select field type</option>
                                @foreach($fieldTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $field->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $field->description) }}</textarea>
                            <small class="text-muted">Help text for content editors</small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', $field->is_required) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">Required Field</label>
                        </div>

                        <div class="mb-3">
                            <label for="validation_rules" class="form-label">Validation Rules</label>
                            <input type="text" class="form-control" id="validation_rules" name="validation_rules" value="{{ old('validation_rules', $field->validation_rules) }}">
                            <small class="text-muted">Laravel validation rules (e.g., "min:5|max:100")</small>
                        </div>

                        <div class="mb-3">
                            <label for="default_value" class="form-label">Default Value</label>
                            <input type="text" class="form-control" id="default_value" name="default_value" value="{{ old('default_value', $field->default_value) }}">
                        </div>

                        <input type="hidden" name="order_index" value="{{ $field->order_index }}">

                        <!-- Options for select/multiselect fields -->
                        <div id="options-container" class="mb-3 {{ in_array($field->type, ['select', 'multiselect']) ? '' : 'd-none' }}">
                            <label class="form-label">Options</label>
                            <div class="options-list">
                                @if(in_array($field->type, ['select', 'multiselect']) && $field->options && $field->options->count() > 0)
                                    @foreach($field->options as $index => $option)
                                        <div class="row mb-2 option-row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="options[{{ $index }}][value]" placeholder="Value" value="{{ $option->value }}">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="options[{{ $index }}][label]" placeholder="Label" value="{{ $option->label }}">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-option">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row mb-2 option-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="options[0][value]" placeholder="Value">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="options[0][label]" placeholder="Label">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-option">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary add-option">
                                <i class="fas fa-plus"></i> Add Option
                            </button>
                        </div>

                        <!-- Settings JSON field -->
                        <div class="mb-3">
                            <label for="settings" class="form-label">Advanced Settings (JSON)</label>
                            <textarea class="form-control" id="settings" name="settings" rows="3">{{ old('settings', $field->settings ?? '{}') }}</textarea>
                            <small class="text-muted">Field-specific settings in JSON format</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Field</button>
                        <a href="{{ route('admin.content-types.fields.index', $contentType) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const optionsContainer = document.getElementById('options-container');
        const optionsList = document.querySelector('.options-list');
        const addOptionBtn = document.querySelector('.add-option');
        
        // Show/hide options container based on field type
        typeSelect.addEventListener('change', function() {
            if (this.value === 'select' || this.value === 'multiselect') {
                optionsContainer.classList.remove('d-none');
            } else {
                optionsContainer.classList.add('d-none');
            }
        });
        
        // Add new option
        addOptionBtn.addEventListener('click', function() {
            const optionCount = document.querySelectorAll('.option-row').length;
            const newOption = document.createElement('div');
            newOption.className = 'row mb-2 option-row';
            newOption.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="options[${optionCount}][value]" placeholder="Value">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="options[${optionCount}][label]" placeholder="Label">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-option">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            optionsList.appendChild(newOption);
            
            // Add event listener to the new remove button
            newOption.querySelector('.remove-option').addEventListener('click', function() {
                this.closest('.option-row').remove();
                renumberOptions();
            });
        });
        
        // Event delegation for remove option buttons
        optionsList.addEventListener('click', function(e) {
            if (e.target.closest('.remove-option')) {
                e.target.closest('.option-row').remove();
                renumberOptions();
            }
        });
        
        // Function to renumber options after deletion
        function renumberOptions() {
            const optionRows = document.querySelectorAll('.option-row');
            optionRows.forEach((row, index) => {
                row.querySelectorAll('input').forEach(input => {
                    const name = input.getAttribute('name');
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                });
            });
        }
    });
</script>
@endpush
