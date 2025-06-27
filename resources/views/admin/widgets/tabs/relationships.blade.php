<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bx bx-link-alt me-2"></i> Content Type Relationships
        </h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#associateContentTypeModal">
            <i class="bx bx-plus me-1"></i> Add Content Type
        </button>
    </div>
    <div class="card-body">
        @if($widget->contentTypeAssociations->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Content Type</th>
                            <th>Status</th>
                            <th>Field Mappings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($widget->contentTypeAssociations as $association)
                            <tr>
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
    <div class="modal-dialog">
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
                            <input class="form-check-input" type="checkbox" id="auto_map" name="auto_map" checked>
                            <label class="form-check-label" for="auto_map">
                                Auto-generate field mappings
                            </label>
                        </div>
                        <small class="text-muted">
                            When enabled, the system will attempt to automatically map widget fields to content type fields.
                        </small>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Associate</button>
                    </div>
                </form>
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
    });
</script>
@endpush
