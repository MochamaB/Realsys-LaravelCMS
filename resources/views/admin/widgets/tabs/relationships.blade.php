<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bx bx-link-alt me-2"></i> Content Type Relationships
        </h5>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#associateContentTypeModal">
                <i class="bx bx-plus me-1"></i> Associate Content Type
            </button>
            <button type="button" class="btn btn-success" id="suggestContentTypeBtn">
                <i class="bx bx-bulb me-1"></i> Add Content Type
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($widget->contentTypeAssociations->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Content Type</th>
                            <th>Status</th>
                            <th>Field Mappings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($widget->contentTypeAssociations as $association)
                            <tr class="clickable-row" 
                                data-href="{{ route('admin.content-types.show', $association->contentType->id) }}"
                                style="cursor: pointer;">
                                <td>
                                    <strong>{{ $association->contentType->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $association->contentType->description }}</small>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-association" 
                                               type="checkbox" 
                                               id="association-{{ $association->id }}" 
                                               data-association-id="{{ $association->id }}"
                                               {{ $association->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="association-{{ $association->id }}">
                                            {{ $association->is_active ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary view-mappings-btn"
                                            data-association-id="{{ $association->id }}"
                                            data-bs-toggle="modal" data-bs-target="#viewMappingsModal">
                                        <i class="bx bx-code me-1"></i> View Mappings
                                    </button>
                                    <div class="mapping-data" id="mapping-data-{{ $association->id }}" style="display: none;">
                                        @json($association->field_mappings)
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.content-types.show', $association->contentType->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="View Content Type">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary edit-mapping-btn"
                                                data-association-id="{{ $association->id }}"
                                                data-content-type-name="{{ $association->contentType->name }}"
                                                data-bs-toggle="modal" data-bs-target="#editMappingModal">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.widgets.associations.delete', $association->id) }}" 
                                              method="POST" 
                                              class="d-inline delete-association-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Modals for viewing and editing mappings -->
            <div class="modal fade" id="viewMappingsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Field Mappings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h6>Widget Fields to Content Type Fields</h6>
                            <div class="table-responsive">
                                <table class="table table-sm" id="mappings-table">
                                    <thead>
                                        <tr>
                                            <th>Widget Field</th>
                                            <th>Content Field</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Will be populated via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="editMappingModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Field Mappings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="edit-mappings-form" action="" method="POST">
                                @csrf
                                @method('PUT')
                                <div id="mapping-form-fields">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Mappings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-0">
                <i class="bx bx-info-circle me-1"></i>
                This widget has no content type associations yet. Click the "Add Content Type" button to associate this widget with content.
            </div>
        @endif
    </div>
</div>

<!-- Modal for adding content type association -->
<div class="modal fade" id="associateContentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Associate Content Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="associate-content-type-form" action="{{ route('admin.widgets.associations.create', $widget->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="content_type_id" class="form-label">Content Type</label>
                        <select class="form-select" id="content_type_id" name="content_type_id" required>
                            <option value="">Select Content Type</option>
                            @foreach($availableContentTypes as $contentType)
                                <option value="{{ $contentType->id }}">{{ $contentType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                        <input type="hidden" name="auto_map" value="0">
                            <input class="form-check-input" type="checkbox" id="auto_map" name="auto_map" checked>
                            <label class="form-check-label" for="auto_map">
                                Auto-generate field mappings
                            </label>
                        </div>
                        <small class="text-muted">
                            When enabled, the system will attempt to automatically map widget fields to content type fields.
                        </small>
                    </div>
                    
                    <div id="field-mappings-preview" class="mb-3" style="display:none;">
                        <h6 class="border-bottom pb-2 mb-3">Field Mappings Preview</h6>
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            Review the field mappings below. You can make adjustments before finalizing the association.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Widget Field</th>
                                        <th>Field Type</th>
                                        <th>Content Field</th>
                                        <th>Field Type</th>
                                    </tr>
                                </thead>
                                <tbody id="mapping-preview-rows">
                                    <!-- Will be populated via JavaScript -->
                                    <tr>
                                        <td colspan="4" class="text-center">Select a content type to preview mappings</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Hidden container for field mapping data -->
                        <div id="field-mappings-data" style="display:none;">
                            <!-- Will hold JSON data for mappings -->
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" id="preview-mappings-btn" class="btn btn-info">
                            <i class="bx bx-show me-1"></i> Preview Mappings
                        </button>
                        
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Associate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Content Type Suggestion Modal -->
<div class="modal fade" id="suggestContentTypeModal" tabindex="-1" aria-labelledby="suggestContentTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suggestContentTypeModalLabel">Suggested Content Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="contentTypeLoadingSpinner">
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Generating content type suggestion...</span>
                    </div>
                </div>
                
                <div id="contentTypeSuggestionError" class="alert alert-danger d-none">
                    <i class="bx bx-error-circle me-2"></i>
                    <span id="suggestionErrorMessage">Unable to generate content type suggestion.</span>
                </div>
                
                <div id="contentTypeSuggestionContent" class="d-none">
                    <div class="alert alert-info mb-4">
                        <i class="bx bx-info-circle me-2"></i>
                        This content type has been automatically generated based on the widget's field definitions.
                        Feel free to modify it before saving.
                    </div>
                    
                    <form id="createContentTypeForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contentTypeName" class="form-label">Content Type Name</label>
                                    <input type="text" class="form-control" id="contentTypeName" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contentTypeSlug" class="form-label">Slug</label>
                                    <input type="text" class="form-control" id="contentTypeSlug" name="slug" required>
                                    <small class="text-muted">URL-friendly identifier (auto-generated)</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contentTypeDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="contentTypeDescription" name="description" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Fields</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 20%">Name</th>
                                        <th style="width: 15%">Type</th>
                                        <th style="width: 25%">Label</th>
                                        <th style="width: 10%">Required</th>
                                        <th style="width: 30%">Settings</th>
                                    </tr>
                                </thead>
                                <tbody id="contentTypeFields">
                                    <!-- Fields will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createContentTypeBtn" disabled>Create Content Type</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Handle viewing field mappings
        const viewMappingButtons = document.querySelectorAll('.view-mappings-btn');
        viewMappingButtons.forEach(button => {
            button.addEventListener('click', function() {
                const associationId = this.getAttribute('data-association-id');
                const mappingDataElement = document.getElementById('mapping-data-' + associationId);
                const mappingsTable = document.getElementById('mappings-table').querySelector('tbody');
                
                // Clear previous mappings
                mappingsTable.innerHTML = '';
                
                if (mappingDataElement) {
                    try {
                        const mappings = JSON.parse(mappingDataElement.textContent);
                        
                        // Add rows for each mapping
                        Object.entries(mappings).forEach(([widgetField, contentField]) => {
                            const row = document.createElement('tr');
                            
                            const widgetFieldCell = document.createElement('td');
                            widgetFieldCell.textContent = widgetField;
                            widgetFieldCell.classList.add('font-monospace');
                            
                            const contentFieldCell = document.createElement('td');
                            contentFieldCell.textContent = contentField;
                            contentFieldCell.classList.add('font-monospace');
                            
                            row.appendChild(widgetFieldCell);
                            row.appendChild(contentFieldCell);
                            mappingsTable.appendChild(row);
                        });
                    } catch (e) {
                        console.error('Error parsing field mappings:', e);
                    }
                }
            });
        });
        
        // Handle editing field mappings
        const editMappingButtons = document.querySelectorAll('.edit-mapping-btn');
        editMappingButtons.forEach(button => {
            button.addEventListener('click', function() {
                const associationId = this.getAttribute('data-association-id');
                const contentTypeName = this.getAttribute('data-content-type-name');
                const mappingDataElement = document.getElementById('mapping-data-' + associationId);
                const formFieldsContainer = document.getElementById('mapping-form-fields');
                
                // Set form action
                const form = document.getElementById('edit-mappings-form');
                form.action = `/admin/widgets/associations/${associationId}/update`;
                
                // Clear previous form fields
                formFieldsContainer.innerHTML = '';
                
                // Add title
                const title = document.createElement('h6');
                title.textContent = `Edit mappings for ${contentTypeName}`;
                formFieldsContainer.appendChild(title);
                
                // Add hidden input for association ID
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'association_id';
                hiddenInput.value = associationId;
                formFieldsContainer.appendChild(hiddenInput);
                
                if (mappingDataElement) {
                    try {
                        const mappings = JSON.parse(mappingDataElement.textContent);
                        
                        // Add form fields for each mapping
                        Object.entries(mappings).forEach(([widgetField, contentField]) => {
                            const formGroup = document.createElement('div');
                            formGroup.classList.add('mb-3', 'row');
                            
                            const labelCol = document.createElement('div');
                            labelCol.classList.add('col-sm-5');
                            
                            const label = document.createElement('label');
                            label.classList.add('form-label', 'font-monospace');
                            label.textContent = widgetField;
                            labelCol.appendChild(label);
                            
                            const inputCol = document.createElement('div');
                            inputCol.classList.add('col-sm-7');
                            
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.classList.add('form-control');
                            input.name = `mappings[${widgetField}]`;
                            input.value = contentField;
                            inputCol.appendChild(input);
                            
                            formGroup.appendChild(labelCol);
                            formGroup.appendChild(inputCol);
                            formFieldsContainer.appendChild(formGroup);
                        });
                    } catch (e) {
                        console.error('Error parsing field mappings for editing:', e);
                    }
                }
            });
        });
        
        // Handle association toggle switches
        const toggleSwitches = document.querySelectorAll('.toggle-association');
        toggleSwitches.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const associationId = this.getAttribute('data-association-id');
                const isActive = this.checked;
                
                // Send AJAX request to toggle the association status
                fetch(`/admin/widgets/associations/${associationId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_active: isActive
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // Revert the toggle if the operation failed
                        this.checked = !isActive;
                    }
                    
                    // Update the label
                    this.nextElementSibling.textContent = isActive ? 'Active' : 'Inactive';
                })
                .catch(error => {
                    console.error('Error toggling association status:', error);
                    // Revert the toggle on error
                    this.checked = !isActive;
                });
            });
        });
        
        // Confirm deletion of associations
        const deleteForms = document.querySelectorAll('.delete-association-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to remove this content type association? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
        
        // Handle delete association confirmation
        const deleteAssociationForms = document.querySelectorAll('.delete-association-form');
        deleteAssociationForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to remove this content type association?')) {
                    this.submit();
                }
            });
        });

        // Preview Mappings Functionality
        const previewMappingsBtn = document.getElementById('preview-mappings-btn');
        const contentTypeSelect = document.getElementById('content_type_id');
        const autoMapCheckbox = document.getElementById('auto_map');
        const fieldMappingsPreview = document.getElementById('field-mappings-preview');
        const mappingPreviewRows = document.getElementById('mapping-preview-rows');
        const fieldMappingsData = document.getElementById('field-mappings-data');
        
        if (previewMappingsBtn) {
            previewMappingsBtn.addEventListener('click', function() {
                const contentTypeId = contentTypeSelect.value;
                
                if (!contentTypeId) {
                    alert('Please select a content type first');
                    return;
                }
                
                // Show loading state
                mappingPreviewRows.innerHTML = '<tr><td colspan="4" class="text-center"><i class="bx bx-loader bx-spin me-2"></i> Generating field mappings...</td></tr>';
                fieldMappingsPreview.style.display = 'block';
                
                // Create FormData for the request
                const formData = new FormData();
                formData.append('content_type_id', contentTypeId);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                // Make AJAX call to preview endpoint
                fetch('{{ route("admin.widgets.associations.preview-mappings", $widget->id) }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show the mappings table
                        fieldMappingsPreview.style.display = 'block';
                        
                        // Clear the table
                        mappingPreviewRows.innerHTML = '';
                        
                        // Store the raw mappings for form submission
                        fieldMappingsData.textContent = JSON.stringify(data.raw_mappings);
                        
                        // Add each mapping to the table
                        if (data.mappings.length > 0) {
                            data.mappings.forEach(function(mapping) {
                                const row = document.createElement('tr');
                                
                                // Widget field
                                const widgetFieldCell = document.createElement('td');
                                widgetFieldCell.textContent = mapping.widget_field;
                                widgetFieldCell.classList.add('font-monospace');
                                row.appendChild(widgetFieldCell);
                                
                                // Widget field type
                                const widgetFieldTypeCell = document.createElement('td');
                                const widgetFieldTypeBadge = document.createElement('span');
                                widgetFieldTypeBadge.classList.add('badge', 'bg-light', 'text-dark');
                                widgetFieldTypeBadge.textContent = mapping.widget_field_type;
                                widgetFieldTypeCell.appendChild(widgetFieldTypeBadge);
                                row.appendChild(widgetFieldTypeCell);
                                
                                // Content field select
                                const contentFieldCell = document.createElement('td');
                                const select = document.createElement('select');
                                select.classList.add('form-select', 'form-select-sm', 'mapping-field-select');
                                select.dataset.widgetField = mapping.widget_field;
                                
                                const option = document.createElement('option');
                                option.value = mapping.content_field;
                                option.textContent = mapping.content_field;
                                option.selected = true;
                                select.appendChild(option);
                                
                                contentFieldCell.appendChild(select);
                                row.appendChild(contentFieldCell);
                                
                                // Content field type
                                const contentFieldTypeCell = document.createElement('td');
                                const contentFieldTypeBadge = document.createElement('span');
                                contentFieldTypeBadge.classList.add('badge', 'bg-light', 'text-dark');
                                contentFieldTypeBadge.textContent = mapping.content_field_type;
                                contentFieldTypeCell.appendChild(contentFieldTypeBadge);
                                row.appendChild(contentFieldTypeCell);
                                
                                mappingPreviewRows.appendChild(row);
                            });
                        } else {
                            mappingPreviewRows.innerHTML = '<tr><td colspan="4" class="text-center">No compatible field mappings found</td></tr>';
                        }
                    } else {
                        // Show error message
                        mappingPreviewRows.innerHTML = `<tr><td colspan="4" class="text-danger">${data.message}</td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching mapping preview:', error);
                    mappingPreviewRows.innerHTML = `<tr><td colspan="4" class="text-danger">Failed to generate field mappings</td></tr>`;
                });
            });
        }
        
        // Handle form submission with mappings
        const associateContentTypeForm = document.getElementById('associate-content-type-form');
        if (associateContentTypeForm) {
            associateContentTypeForm.addEventListener('submit', function(e) {
                // If preview was shown and has data, include the mappings
                if (fieldMappingsData.textContent) {
                    try {
                        const mappings = JSON.parse(fieldMappingsData.textContent);
                        
                        // Update mappings based on any user changes in the selects
                        document.querySelectorAll('.mapping-field-select').forEach(select => {
                            const widgetField = select.dataset.widgetField;
                            const contentField = select.value;
                            mappings[widgetField] = contentField;
                        });
                        
                        // Add the mappings to the form data as hidden field
                        const mappingsField = document.createElement('input');
                        mappingsField.type = 'hidden';
                        mappingsField.name = 'field_mappings';
                        mappingsField.value = JSON.stringify(mappings);
                        this.appendChild(mappingsField);
                    } catch (e) {
                        console.error('Error handling mappings data:', e);
                    }
                }
            });
        }
        
        // Content type select and auto-map checkbox events
        if (contentTypeSelect) {
            contentTypeSelect.addEventListener('change', function() {
                // Clear any existing preview
                if (fieldMappingsPreview) {
                    fieldMappingsPreview.style.display = 'none';
                }
                if (fieldMappingsData) {
                    fieldMappingsData.textContent = '';
                }
                
                // If auto-map is off and there's a selection, show preview automatically
                if (autoMapCheckbox && !autoMapCheckbox.checked && this.value) {
                    previewMappingsBtn.click();
                }
            });
        }
        
        if (autoMapCheckbox) {
            autoMapCheckbox.addEventListener('change', function() {
                // When turning off auto-map with content type selected, show preview
                if (!this.checked && contentTypeSelect.value) {
                    previewMappingsBtn.click();
                }
            });
        }
        
        // Content Type Suggestion Functionality
        const suggestContentTypeBtn = document.getElementById('suggestContentTypeBtn');
        const contentTypeLoadingSpinner = document.getElementById('contentTypeLoadingSpinner');
        const contentTypeSuggestionError = document.getElementById('contentTypeSuggestionError');
        const contentTypeSuggestionContent = document.getElementById('contentTypeSuggestionContent');
        const suggestionErrorMessage = document.getElementById('suggestionErrorMessage');
        const createContentTypeBtn = document.getElementById('createContentTypeBtn');
        const suggestModal = new bootstrap.Modal(document.getElementById('suggestContentTypeModal'));
        
        if (suggestContentTypeBtn) {
            suggestContentTypeBtn.addEventListener('click', function() {
                // Reset modal state
                contentTypeLoadingSpinner.classList.remove('d-none');
                contentTypeSuggestionError.classList.add('d-none');
                contentTypeSuggestionContent.classList.add('d-none');
                createContentTypeBtn.disabled = true;
                
                // Show the modal
                suggestModal.show();
                
                // Fetch content type suggestion
                fetch('{{ route("admin.widgets.suggest-content-type", $widget->id) }}')
                    .then(response => response.json())
                    .then(data => {
                        contentTypeLoadingSpinner.classList.add('d-none');
                        
                        if (data.success) {
                            // Populate form with suggestion data
                            const contentType = data.content_type;
                            
                            document.getElementById('contentTypeName').value = contentType.name;
                            document.getElementById('contentTypeSlug').value = contentType.slug;
                            document.getElementById('contentTypeDescription').value = contentType.description || '';
                            
                            // Populate fields table
                            const fieldsTable = document.getElementById('contentTypeFields');
                            fieldsTable.innerHTML = '';
                            
                            contentType.fields.forEach((field, index) => {
                                const row = document.createElement('tr');
                                
                                // Name field
                                let cell = document.createElement('td');
                                let input = document.createElement('input');
                                input.type = 'text';
                                input.className = 'form-control';
                                input.name = `content_type[fields][${index}][name]`;
                                input.value = field.name;
                                input.required = true;
                                input.readOnly = true; // <-- Make the input readonly
                                cell.appendChild(input);
                                row.appendChild(cell);
                                
                                // Type field
                                cell = document.createElement('td');
                                let select = document.createElement('select');
                                select.className = 'form-select';
                                select.name = `content_type[fields][${index}][field_type]`;
                                select.disabled = true; // Prevent editing

                                const fieldTypes = ['text', 'textarea', 'rich_text', 'number', 'email', 'url', 'date', 'datetime', 'select', 'radio', 'checkbox', 'boolean', 'image', 'file', 'gallery', 'repeater'];

                                fieldTypes.forEach(type => {
                                    let option = document.createElement('option');
                                    option.value = type;
                                    option.textContent = type.replace('_', ' ');
                                    option.selected = type === field.field_type;
                                    select.appendChild(option);
                                });

                                cell.appendChild(select);

                                // Add hidden input to preserve value during form submission
                                let hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = select.name;
                                hidden.value = field.field_type;
                                cell.appendChild(hidden);

                                row.appendChild(cell);

                                
                                // Label field
                                cell = document.createElement('td');
                                input = document.createElement('input');
                                input.type = 'text';
                                input.className = 'form-control';
                                input.name = `content_type[fields][${index}][label]`;
                                // Generate a label from the name since we don't have a label field anymore
                                const fieldLabel = field.name ? field.name.replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '';
                                input.value = fieldLabel;
                                cell.appendChild(input);
                                row.appendChild(cell);
                                
                                // Required field
                                cell = document.createElement('td');
                                let checkDiv = document.createElement('div');
                                checkDiv.className = 'form-check me-2';
                                
                                input = document.createElement('input');
                                input.type = 'checkbox';
                                input.className = 'form-check-input';
                                input.name = `content_type[fields][${index}][is_required]`; // Changed from required to is_required
                                input.id = `field-required-${index}`;
                                input.checked = field.is_required; // Changed from field.required to field.is_required
                                checkDiv.appendChild(input);
                                
                                let label = document.createElement('label');
                                label.className = 'form-check-label ml-2';
                                label.htmlFor = `field-required-${index}`;
                                label.textContent = 'Required';
                                checkDiv.appendChild(label);
                                
                                cell.appendChild(checkDiv);
                                row.appendChild(cell);
                                
                                // Settings field (simplified for readability)
                                cell = document.createElement('td');
                                let settingsPreview = document.createElement('div');
                                settingsPreview.className = 'small text-muted';
                                
                                // Create a hidden input to store the JSON settings
                                let hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = `content_type[fields][${index}][settings]`;
                                hiddenInput.value = JSON.stringify(field.settings || {});
                                cell.appendChild(hiddenInput);
                                
                                // Show a preview of the settings
                                if (field.settings) {
                                    let settingsList = [];
                                    Object.entries(field.settings).forEach(([key, value]) => {
                                        if (key === 'subfields' && Array.isArray(value)) {
                                            // Special handling for repeater subfields
                                            let subfieldItems = [];
                                            value.forEach(subfield => {
                                                subfieldItems.push(`${subfield.name} (${subfield.field_type || subfield.type})`);
                                            });
                                            settingsList.push(`${key}: [${subfieldItems.join(', ')}]`);
                                        } else if (typeof value !== 'object') {
                                            settingsList.push(`${key}: ${value}`);
                                        } else {
                                            // For other objects, try to show something more descriptive
                                            settingsList.push(`${key}: ${JSON.stringify(value).substring(0, 20)}...`);
                                        }
                                    });
                                    
                                    if (settingsList.length > 0) {
                                        settingsPreview.textContent = settingsList.join(', ');
                                    } else {
                                        settingsPreview.textContent = 'No settings';
                                    }
                                } else {
                                    settingsPreview.textContent = 'No settings';
                                }
                                
                                cell.appendChild(settingsPreview);
                                row.appendChild(cell);
                                
                                fieldsTable.appendChild(row);
                            });
                            
                            contentTypeSuggestionContent.classList.remove('d-none');
                            createContentTypeBtn.disabled = false;
                        } else {
                            // Show error
                            contentTypeSuggestionError.classList.remove('d-none');
                            suggestionErrorMessage.textContent = data.message || 'Unable to generate content type suggestion.';
                        }
                    })
                    .catch(error => {
                        contentTypeLoadingSpinner.classList.add('d-none');
                        contentTypeSuggestionError.classList.remove('d-none');
                        suggestionErrorMessage.textContent = 'An unexpected error occurred while generating the content type suggestion.';
                        console.error('Error:', error);
                    });
            });
        }
        
        // Handle creating the content type
        if (createContentTypeBtn) {
            createContentTypeBtn.addEventListener('click', function() {
                const form = document.getElementById('createContentTypeForm');
                
                // Get form data
                const formData = new FormData();
                
                // Add base fields
                formData.append('content_type[name]', document.getElementById('contentTypeName').value);
                formData.append('content_type[slug]', document.getElementById('contentTypeSlug').value);
                formData.append('content_type[description]', document.getElementById('contentTypeDescription').value);
                
                // Add all visible field rows
                const fieldRows = document.querySelectorAll('#contentTypeFields tr');
                fieldRows.forEach((row, rowIndex) => {
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        if (input.name) {
                            if (input.type === 'checkbox') {
                                formData.append(input.name, input.checked ? '1' : '0');
                            } else {
                                formData.append(input.name, input.value);
                            }
                        }
                    });
                });
                
                // Set button to loading state
                createContentTypeBtn.disabled = true;
                createContentTypeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
                
                // Submit the form via AJAX
                fetch('{{ route("admin.widgets.create-content-type", $widget->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alert(data.message || 'Content type created successfully!');
                        
                        // Close the modal
                        suggestModal.hide();
                        
                        // Redirect if provided
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            // Otherwise reload the page
                            window.location.reload();
                        }
                    } else {
                        // Show error
                        alert(data.message || 'Failed to create content type.');
                        createContentTypeBtn.disabled = false;
                        createContentTypeBtn.textContent = 'Create Content Type';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred while creating the content type.');
                    createContentTypeBtn.disabled = false;
                    createContentTypeBtn.textContent = 'Create Content Type';
                });
            });
        }
    });
</script>
@endpush
