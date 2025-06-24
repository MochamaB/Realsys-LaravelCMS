{{--
    Content Type Field Form Partial
    
    Variables expected:
    - contentType: The content type model
    - formAction: The form submission URL
    - fieldTypes: Array of available field types
    - maxPosition: Maximum position value for ordering
    - field: Optional existing field for editing
    - submitButtonText: Text for the submit button
    - isModal: Whether this form is being displayed in a modal
    - showTabs: Whether to show tabbed interface (defaults to true)
--}}

@php
    // Default values
    $field = $field ?? null;
    $submitButtonText = $submitButtonText ?? 'Save Field';
    $isModal = $isModal ?? false;
    $showTabs = $showTabs ?? true; // New parameter to control tabs display
    
    // For field type selection in drag & drop 
    $selectedFieldType = $selectedFieldType ?? old('field_type', $field->field_type ?? null);
@endphp

<form action="{{ $formAction }}" method="POST" id="{{ $isModal ? 'modal-field-form' : 'field-form' }}">
    @csrf

    @if(isset($field) && request()->isMethod('put'))
        @method('PUT')
    @endif

    {{-- Tab Navigation - now controlled by $showTabs instead of $isModal --}}
    @if($showTabs)
    <ul class="nav nav-tabs" role="tablist" id="{{ $isModal ? 'modal-tabs' : 'field-tabs' }}">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#{{ $isModal ? 'modal-' : '' }}basics" role="tab" aria-controls="{{ $isModal ? 'modal-' : '' }}basics">
                <i class="ri-information-line me-1"></i> Basic Settings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#{{ $isModal ? 'modal-' : '' }}options" role="tab" aria-controls="{{ $isModal ? 'modal-' : '' }}options">
                <i class="ri-list-settings-line me-1"></i> Field Options
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#{{ $isModal ? 'modal-' : '' }}advanced" role="tab" aria-controls="{{ $isModal ? 'modal-' : '' }}advanced">
                <i class="ri-settings-3-line me-1"></i> Advanced
            </a>
        </li>
    </ul>
    @endif

    {{-- Tab Content --}}
    <div class="tab-content p-3" id="{{ $isModal ? 'modal-tab-content' : 'field-tab-content' }}">
        {{-- Basic Settings Tab --}}
        <div class="tab-pane fade show active" id="{{ $isModal ? 'modal-' : '' }}basics" role="tabpanel" aria-labelledby="{{ $isModal ? 'modal-' : '' }}basics-tab">
            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}name" class="form-label">Field Name *</label>
                <input type="text" class="form-control" id="{{ $isModal ? 'modal-' : '' }}name" name="name" 
                       value="{{ old('name', $field->name ?? '') }}" required>
                <small class="text-muted">Human-readable name for this field</small>
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}slug" class="form-label">Field Key</label>
                <input type="text" class="form-control" id="{{ $isModal ? 'modal-' : '' }}slug" name="slug" 
                       value="{{ old('slug', $field->slug ?? '') }}">
                <small class="text-muted">Machine name for this field (will be auto-generated if left empty)</small>
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}field_type" class="form-label">Field Type *</label>
                <select class="form-select" id="{{ $isModal ? 'modal-' : '' }}field_type" name="field_type" required 
                        {{ $selectedFieldType ? 'disabled' : '' }}>
                    <option value="">Select field type</option>
                    @foreach($fieldTypes as $value => $label)
                        <option value="{{ $value }}" 
                            {{ $selectedFieldType == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                
                @if($selectedFieldType)
                    <input type="hidden" name="field_type" value="{{ $selectedFieldType }}">
                @endif
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}description" class="form-label">Description</label>
                <textarea class="form-control" id="{{ $isModal ? 'modal-' : '' }}description" name="description" 
                          rows="3">{{ old('description', $field->description ?? '') }}</textarea>
                <small class="text-muted">Help text for content editors</small>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="{{ $isModal ? 'modal-' : '' }}is_required" name="is_required" 
                       value="1" {{ old('is_required', $field->is_required ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $isModal ? 'modal-' : '' }}is_required">Required Field</label>
            </div>

            <input type="hidden" name="position" value="{{ old('position', $field->position ?? ($maxPosition + 1)) }}">
        </div>

        {{-- Options Tab --}}
        <div class="tab-pane fade" id="{{ $isModal ? 'modal-' : '' }}options" role="tabpanel" aria-labelledby="{{ $isModal ? 'modal-' : '' }}options-tab">
            <div id="{{ $isModal ? 'modal-' : '' }}options-container" class="mb-3">
                <label class="form-label">Field Options</label>
                <small class="d-block text-muted mb-2">
                    Add options for select, multiselect, radio, or checkbox fields
                </small>

                <div class="{{ $isModal ? 'modal-' : '' }}options-list">
                    @if(isset($field) && $field->options->count() > 0)
                        @foreach($field->options as $index => $option)
                            <div class="row mb-2 option-row">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" 
                                           name="options[{{ $index }}][value]"
                                           value="{{ $option->value }}" 
                                           placeholder="Value">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" 
                                           name="options[{{ $index }}][label]"
                                           value="{{ $option->label }}" 
                                           placeholder="Label">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-option">
                                        <i class="ri-delete-bin-line"></i>
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
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                
                <button type="button" class="btn btn-sm btn-secondary {{ $isModal ? 'modal-' : '' }}add-option mt-2">
                    <i class="ri-add-line"></i> Add Option
                </button>
                
                <div class="alert alert-info mt-3">
                    <i class="ri-information-line me-1"></i>
                    Options are only used for field types: select, multiselect, radio, and checkbox
                </div>
            </div>
        </div>

        {{-- Advanced Tab --}}
        <div class="tab-pane fade" id="{{ $isModal ? 'modal-' : '' }}advanced" role="tabpanel" aria-labelledby="{{ $isModal ? 'modal-' : '' }}advanced-tab">
            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}validation_rules" class="form-label">Validation Rules</label>
                <input type="text" class="form-control" id="{{ $isModal ? 'modal-' : '' }}validation_rules" name="validation_rules" 
                       value="{{ old('validation_rules', $field->validation_rules ?? '') }}">
                <small class="text-muted">Laravel validation rules (e.g., "min:5|max:100")</small>
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}default_value" class="form-label">Default Value</label>
                <input type="text" class="form-control" id="{{ $isModal ? 'modal-' : '' }}default_value" name="default_value" 
                       value="{{ old('default_value', $field->default_value ?? '') }}">
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}settings" class="form-label">Advanced Settings (JSON)</label>
                <textarea class="form-control" id="{{ $isModal ? 'modal-' : '' }}settings" name="settings" rows="3">{{ old('settings', isset($field->settings) ? (is_array($field->settings) ? json_encode($field->settings, JSON_PRETTY_PRINT) : $field->settings) : '{}') }}</textarea>
                <small class="text-muted">Field-specific settings in JSON format</small>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">{{ $submitButtonText }}</button>
        @if(!$isModal)
            <a href="{{ route('admin.content-types.show', $contentType) }}#fields" class="btn btn-secondary">Cancel</a>
        @endif
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Determine if we're in modal mode
        const isModal = {{ $isModal ? 'true' : 'false' }};
        const prefix = isModal ? 'modal-' : '';
        
        // Elements for options management
        const fieldTypeSelect = document.getElementById(prefix + 'field_type');
        const optionsContainer = document.getElementById(prefix + 'options-container');
        const optionsList = document.querySelector('.' + prefix + 'options-list');
        const addOptionBtn = document.querySelector('.' + prefix + 'add-option');
        
        // Handle field type change to show/hide options
        function toggleOptionsVisibility() {
            // Field types that need options
            const optionFieldTypes = ['select', 'multiselect', 'radio', 'checkbox'];
            const fieldType = fieldTypeSelect.value;
            
            // For tab navigation
            const optionsTab = document.querySelector('a[href="#' + prefix + 'options"]');
            
            if (optionFieldTypes.includes(fieldType)) {
                if (optionsTab) {
                    optionsTab.classList.remove('disabled');
                    optionsTab.removeAttribute('tabindex');
                }
                optionsContainer.classList.remove('d-none');
            } else {
                if (optionsTab) {
                    optionsTab.classList.add('disabled');
                    optionsTab.setAttribute('tabindex', '-1');
                }
                optionsContainer.classList.add('d-none');
            }
        }
        
        // Initial check on page load
        if (fieldTypeSelect) {
            toggleOptionsVisibility();
            fieldTypeSelect.addEventListener('change', toggleOptionsVisibility);
        }
        
        // Add new option row
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', function() {
                const optionCount = document.querySelectorAll('.' + prefix + 'options-list .option-row').length;
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
                            <i class="ri-delete-bin-line"></i>
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
        }
        
        // Event delegation for existing remove option buttons
        if (optionsList) {
            optionsList.addEventListener('click', function(e) {
                if (e.target.closest('.remove-option')) {
                    e.target.closest('.option-row').remove();
                    renumberOptions();
                }
            });
        }
        
        // Function to renumber options after deletion
        function renumberOptions() {
            const optionRows = document.querySelectorAll('.' + prefix + 'options-list .option-row');
            optionRows.forEach((row, index) => {
                row.querySelectorAll('input').forEach(input => {
                    const name = input.getAttribute('name');
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                });
            });
        }
        
        // Auto-generate slug from name
        const nameInput = document.getElementById(prefix + 'name');
        const slugInput = document.getElementById(prefix + 'slug');
        
        if (nameInput && slugInput) {
            nameInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    slugInput.value = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '_')
                        .replace(/^_+|_+$/g, '');
                }
            });
        }
    });
</script>
@endpush