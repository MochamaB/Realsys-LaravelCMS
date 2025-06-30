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
    $optionFieldTypes = collect(config('field_types'))
    ->filter(function($type) { return $type['has_options'] ?? false; })
    ->keys()
    ->all();
    $simpleFieldTypes = collect(config('field_types'))
        ->filter(function($type, $key) { 
            return !($type['has_options'] ?? false) && !in_array($key, ['repeater', 'flexible']);
        })
        ->map(function($type, $key) {
            return ['type' => $key, 'name' => $type['name'] ?? ucfirst($key), 'icon' => $type['icon'] ?? 'help-circle'];
        })
        ->values()
        ->all();
@endphp

<form action="{{ $formAction }}" method="POST" id="{{ $isModal ? 'modal-field-form' : 'field-form' }}" onsubmit="return prepareRepeaterSettings(this);" enctype="multipart/form-data">
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
        <li class="nav-item repeater-tab" style="display: none;">
            <a class="nav-link" data-bs-toggle="tab" href="#{{ $isModal ? 'modal-' : '' }}repeater" role="tab" aria-controls="{{ $isModal ? 'modal-' : '' }}repeater">
                <i class="ri-repeat-line me-1"></i> Repeater Config
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
                <label for="{{ $isModal ? 'modal-' : '' }}name" class="form-label">Field Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="{{ $isModal ? 'modal-' : '' }}name" name="name" 
                       value="{{ old('name', $field->name ?? '') }}" required>
                <small class="text-muted">Human-readable name for this field</small>
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}slug" class="form-label">Field Key/Slug <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="{{ $isModal ? 'modal-' : '' }}slug" name="slug" 
                       value="{{ old('slug', $field->slug ?? '') }}">
                <small class="text-muted">Name to match with widget key/slug for this field (will be auto-generated if left empty)</small>
            </div>

            <div class="mb-3">
                <label for="{{ $isModal ? 'modal-' : '' }}field_type" class="form-label">Field Type <span class="text-danger">*</span></label>
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

        {{-- Repeater Tab --}}
        <div class="tab-pane fade" id="{{ $isModal ? 'modal-' : '' }}repeater" role="tabpanel" aria-labelledby="{{ $isModal ? 'modal-' : '' }}repeater-tab">
            <div class="repeater-config-container">
                <!-- Subfields management -->
                <div class="subfields-container mb-4">
                    <h5>Subfields</h5>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        Define the fields that will appear in each repeater item
                    </div>
                    <div class="subfields-list sortable-list" data-group="subfields-group">
                        <!-- Subfields will be added here dynamically -->
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary add-subfield mt-2">
                        <i class="ri-add-line"></i> Add Subfield
                    </button>
                </div>
                
                <!-- Min/Max items settings -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Minimum Items</label>
                            <input type="number" class="form-control" name="repeater_min_items" value="1" min="0">
                            <small class="text-muted">Minimum number of items required</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Maximum Items</label>
                            <input type="number" class="form-control" name="repeater_max_items" value="10" min="1">
                            <small class="text-muted">Maximum number of items allowed (0 = unlimited)</small>
                        </div>
                    </div>
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
    // Function to prepare repeater settings when form is submitted
    function prepareRepeaterSettings(form) {
        const fieldTypeSelect = form.querySelector('select[name="field_type"]');
        const fieldType = fieldTypeSelect ? fieldTypeSelect.value : form.querySelector('input[name="field_type"][type="hidden"]')?.value;
        
        if (fieldType === 'repeater') {
            // Collect all subfield data
            const subfields = [];
            form.querySelectorAll('.subfield-item').forEach((item, index) => {
                const nameInput = item.querySelector('.subfield-name');
                const labelInput = item.querySelector('.subfield-label');
                const typeSelect = item.querySelector('.subfield-type');
                const requiredCheckbox = item.querySelector('input[type="checkbox"]');
                
                if (nameInput && labelInput && typeSelect) {
                    subfields.push({
                        name: nameInput.value,
                        label: labelInput.value,
                        type: typeSelect.value,
                        required: requiredCheckbox?.checked || false,
                        settings: {}
                    });
                }
            });
            
            // Get min/max values
            const minItems = parseInt(form.querySelector('input[name="repeater_min_items"]')?.value || '1');
            const maxItems = parseInt(form.querySelector('input[name="repeater_max_items"]')?.value || '10');
            
            if (subfields.length === 0) {
                alert('Please add at least one subfield to your repeater');
                return false;
            }
            
            // Create settings JSON
            const settings = {
                subfields: subfields,
                min_items: minItems,
                max_items: maxItems
            };
            
            // Find or create settings field
            let settingsField = form.querySelector('textarea[name="settings"]');
            if (!settingsField) {
                settingsField = document.createElement('textarea');
                settingsField.name = 'settings';
                form.appendChild(settingsField);
            }
            
            // Set settings JSON
            settingsField.value = JSON.stringify(settings);
        }
        
        // Continue with form submission
        return true;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Determine if we're in modal mode
        const isModal = {{ $isModal ? 'true' : 'false' }};
        const prefix = isModal ? 'modal-' : '';
        
        // Elements for options management
        const fieldTypeSelect = document.getElementById(prefix + 'field_type');
        const optionsContainer = document.getElementById(prefix + 'options-container');
        const optionsList = document.querySelector('.' + prefix + 'options-list');
        const addOptionBtn = document.querySelector('.' + prefix + 'add-option');
        
        // Handle field type change to show/hide options and repeater tabs
        function toggleOptionsVisibility() {
            // Field types that need options - from PHP config
            const optionFieldTypes =  @json($optionFieldTypes);
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
            
            // Also toggle repeater tab visibility
            toggleRepeaterVisibility();
        }
        
        // Show/hide repeater tab based on field type
        function toggleRepeaterVisibility() {
            const fieldType = fieldTypeSelect.value;
            const repeaterTab = document.querySelector('.repeater-tab');
            const repeaterTabLink = document.querySelector('a[href="#' + prefix + 'repeater"]');
            
            if (fieldType === 'repeater') {
                if (repeaterTab) repeaterTab.style.display = '';
                
                // If we're showing the repeater tab and it's not active, 
                // we might want to make it active instead of the default tab
                if (repeaterTabLink && !repeaterTabLink.classList.contains('active')) {
                    // Only auto-switch to repeater tab if form is empty (new field)
                    const nameField = document.getElementById(prefix + 'name');
                    if (nameField && !nameField.value) {
                        const tabToActivate = new bootstrap.Tab(repeaterTabLink);
                        tabToActivate.show();
                    }
                }
                
                // Load existing repeater config if present
                loadRepeaterConfigFromSettings();
            } else {
                if (repeaterTab) repeaterTab.style.display = 'none';
            }
        }
        
        // Initial check on page load
        if (fieldTypeSelect) {
            toggleOptionsVisibility();
            fieldTypeSelect.addEventListener('change', toggleOptionsVisibility);
            
            // Check if we're editing an existing repeater field
            const currentFieldType = fieldTypeSelect.value || document.querySelector('input[name="field_type"]')?.value;
            if (currentFieldType === 'repeater') {
                const repeaterTab = document.querySelector('.repeater-tab');
                if (repeaterTab) {
                    repeaterTab.style.display = '';
                }
                // Load repeater config from settings if this is an edit operation
                loadRepeaterConfigFromSettings();
            }
        }
        
        // Expose the function globally so it can be called from sortable-fields.blade.php
        window.triggerOptionsVisibilityCheck = function(fieldTypeValue) {
            // If fieldTypeSelect exists, set its value and manually trigger visibility check
            if (fieldTypeSelect) {
                // The fieldType is already set in the select or hidden input
                // Just need to run the visibility toggle
                toggleOptionsVisibility();
            }
        };
        
        // Repeater field functionality
        const addSubfieldBtn = document.querySelector('.add-subfield');
        const subfieldsContainer = document.querySelector('.subfields-list');
        
        if (addSubfieldBtn) {
            addSubfieldBtn.addEventListener('click', function() {
                addSubfield();
            });
        }
        
        // Function to add a new subfield
        function addSubfield(data = {}) {
            if (!subfieldsContainer) return;
            
            const subfieldCount = subfieldsContainer.querySelectorAll('.subfield-item').length;
            
            // Get simple field types (excluding complex types and those with options)
            const simpleFieldTypes = @json($simpleFieldTypes);
            
            const subfield = document.createElement('div');
            subfield.className = 'subfield-item card mb-3';
            subfield.innerHTML = `
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <div class="d-flex align-items-center">
                        <span class="drag-handle me-2"><i class="ri-drag-move-line"></i></span>
                        <span class="subfield-title fw-bold">${data.label || 'New Subfield'}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-subfield">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="subfields[${subfieldCount}][id]" value="${data.id || ''}">
                    
                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control subfield-label" 
                               name="subfields[${subfieldCount}][label]" 
                               value="${data.label || ''}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Name/Slug</label>
                        <input type="text" class="form-control subfield-name" 
                               name="subfields[${subfieldCount}][name]" 
                               value="${data.name || ''}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Field Type</label>
                        <select class="form-select subfield-type" 
                                name="subfields[${subfieldCount}][type]" required>
                            <option value="">Select Type</option>
                            ${Object.values(simpleFieldTypes).map(info => 
                                `<option value="${info.type}" ${data.type === info.type ? 'selected' : ''}>
                                    <i class="bx bx-${info.icon}"></i> ${info.name}
                                </option>`
                            ).join('')}
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                               id="subfield-required-${subfieldCount}" 
                               name="subfields[${subfieldCount}][required]" 
                               value="1" ${data.required ? 'checked' : ''}>
                        <label class="form-check-label" for="subfield-required-${subfieldCount}">
                            Required
                        </label>
                    </div>
                </div>
            `;
            
            subfieldsContainer.appendChild(subfield);
            
            // Add event listener for removal
            subfield.querySelector('.remove-subfield').addEventListener('click', function() {
                subfield.remove();
                renumberSubfields();
            });
            
            // Auto-generate name from label
            const labelInput = subfield.querySelector('.subfield-label');
            const nameInput = subfield.querySelector('.subfield-name');
            
            labelInput.addEventListener('blur', function() {
                if (nameInput.value === '') {
                    nameInput.value = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '_')
                        .replace(/^_+|_+$/g, '');
                }
            });
            
            // Update title when label changes
            labelInput.addEventListener('input', function() {
                const titleEl = subfield.querySelector('.subfield-title');
                if (titleEl) {
                    titleEl.textContent = this.value || 'New Subfield';
                }
            });
        }
        
        // Function to renumber subfields after deletion or reordering
        function renumberSubfields() {
            if (!subfieldsContainer) return;
            
            const subfields = subfieldsContainer.querySelectorAll('.subfield-item');
            subfields.forEach((subfield, index) => {
                subfield.querySelectorAll('[name^="subfields["]').forEach(input => {
                    const name = input.getAttribute('name');
                    input.setAttribute('name', name.replace(/subfields\[\d+\]/, `subfields[${index}]`));
                });
                
                const requiredCheckbox = subfield.querySelector('[id^="subfield-required-"]');
                if (requiredCheckbox) {
                    requiredCheckbox.id = `subfield-required-${index}`;
                    const label = subfield.querySelector(`[for^="subfield-required-"]`);
                    if (label) {
                        label.setAttribute('for', `subfield-required-${index}`);
                    }
                }
            });
        }
        
        // Load existing repeater configuration from settings JSON
        function loadRepeaterConfigFromSettings() {
            // Get the settings textarea
            const settingsField = document.getElementById(prefix + 'settings');
            if (!settingsField || !settingsField.value || !subfieldsContainer) return;
            
            try {
                // Parse the settings JSON
                const settings = JSON.parse(settingsField.value);
                
                // Clear existing subfields
                subfieldsContainer.innerHTML = '';
                
                // If we have subfields defined
                if (settings.subfields && Array.isArray(settings.subfields)) {
                    // Add each subfield
                    settings.subfields.forEach(subfield => {
                        addSubfield({
                            name: subfield.name,
                            label: subfield.label,
                            type: subfield.type,
                            required: subfield.required
                        });
                    });
                }
                
                // Set min/max items
                const minItemsInput = document.querySelector('input[name="repeater_min_items"]');
                const maxItemsInput = document.querySelector('input[name="repeater_max_items"]');
                
                if (minItemsInput && settings.min_items !== undefined) {
                    minItemsInput.value = settings.min_items;
                }
                
                if (maxItemsInput && settings.max_items !== undefined) {
                    maxItemsInput.value = settings.max_items;
                }
                
            } catch (e) {
                console.error('Error parsing repeater settings:', e);
            }
        }
        
        // Initialize sortable for subfields if SortableJS is available
        if (typeof Sortable !== 'undefined' && subfieldsContainer) {
            new Sortable(subfieldsContainer, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function() {
                    renumberSubfields();
                }
            });
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