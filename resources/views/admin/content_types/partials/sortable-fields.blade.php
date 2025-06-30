{{--
    Sortable Fields Partial
    
    Variables expected:
    - id: Unique identifier for the fields list
    - fields: Collection of fields to be displayed
    - contentType: The content type object
    - emptyMessage: Message to display when the list is empty
--}}

{{-- No @props needed - variables are passed directly when included --}}

<div class="sortable-fields-container">
    @if($fields->isEmpty())
        <div class="sortable-empty-state">
            <i class="ri-file-list-3-line ri-3x text-muted mb-3"></i>
            <h5>{{ $emptyMessage }}</h5>
            <p class="text-muted mb-3">Drag field types here to add new fields</p>
            <a href="{{ route('admin.content-types.fields.create', $contentType) }}" class="btn btn-primary">
                <i class="ri-add-line"></i> Add Your First Field
            </a>
        </div>
    @else
        <div class="dd-list sortable-list" id="{{ $id }}" data-group="fields-group" data-save-url="{{ route('admin.content-types.fields.reorder', $contentType) }}">
            @foreach($fields->sortBy('position') as $field)
                <li class="dd-item sortable-item" data-id="{{ $field->id }}">
                    <div class="dd-handle">
                        <div class="item-content">
                            <div class="field-item-title">
                                <div class="item-icon">
                                    <i class="bx bx-{{ config('field_types.' . $field->field_type . '.icon', 'help-circle') }}"></i>
                                </div>
                                <div class="field-item-info">
                                    <span class="item-title">{{ $field->name }}</span>
                                    <div class="item-badges">
                                        <code class="item-badge">{{ $field->slug }}</code>
                                        <span class="item-badge">{{ config('field_types.' . $field->field_type . '.name', $field->field_type) }}</span>
                                        
                                        @if($field->required)
                                            <span class="badge bg-danger">Required</span>
                                        @endif
                                    </div>
                                    @if($field->hint)
                                        <small class="text-muted">{{ $field->hint }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="item-actions">
                            <a href="{{ route('admin.content-types.fields.edit', [$contentType, $field]) }}" 
                               class="btn btn-sm btn-soft-primary" 
                               title="Edit Field"
                               onclick="event.stopPropagation();">
                                <i class="ri-pencil-line"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-soft-danger delete-field-btn" 
                                    data-field-id="{{ $field->id }}" 
                                    data-field-name="{{ $field->name }}"
                                    title="Delete Field"
                                    onclick="event.stopPropagation();">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </li>
            @endforeach
        </div>
        
        <div class="sortable-drop-area mt-3" id="fields-drop-area" data-group="fields-group">
            <p class="text-center mb-0"><i class="ri-drag-drop-line"></i> Drop new fields here</p>
        </div>
        
        <div class="text-end mt-3">
            <button type="button" class="btn btn-primary save-sortable-order" data-target="{{ $id }}">
                <i class="ri-save-line me-1"></i> Save Order
            </button>
        </div>
    @endif
</div>

{{-- Field Add Modal --}}
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    // Convert field types to proper format
                    $fieldTypeOptions = [];
                    foreach (config('field_types') as $type => $info) {
                        $fieldTypeOptions[$type] = $info['name'] ?? ucfirst($type);
                    }
                @endphp
                
                @include('admin.content_type_fields.partials._form', [
                    'contentType' => $contentType,
                    'formAction' => route('admin.content-types.fields.store', $contentType),
                    'fieldTypes' => $fieldTypeOptions,
                    'maxPosition' => $fields->count(),
                    'submitButtonText' => 'Create Field',
                    'isModal' => true,
                    'showTabs' => true, // Enable tabs in modal
                    'selectedFieldType' => null
                ])
            </div>
        </div>
    </div>
</div>

{{-- Field Delete Modal --}}
<div class="modal fade" id="deleteFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete field "<span id="delete-field-name"></span>"?</p>
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-1"></i> This will delete all content data stored in this field across all content items!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-field-form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Field</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto generate slug from name in the add field modal
    document.getElementById('modal-name')?.addEventListener('input', function() {
        const slugField = document.getElementById('modal-slug');
        if (slugField) {
            slugField.value = this.value
                .toLowerCase()
                .replace(/\s+/g, '_')
                .replace(/[^a-z0-9_]/g, '');
        }
    });
    
    // Handle field type drop to show add modal
    document.addEventListener('sourceItemDropped', function(e) {
        if (e.detail && e.detail.sourceItem) {
            const fieldType = e.detail.sourceItem.getAttribute('data-field-type');
            if (fieldType) {
                showAddFieldModal(fieldType);
            }
        }
    });
    
    // Setup delete field modal triggers
    document.querySelectorAll('.delete-field-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const fieldId = this.getAttribute('data-field-id');
            const fieldName = this.getAttribute('data-field-name');
            
            document.getElementById('delete-field-name').textContent = fieldName;
            document.getElementById('delete-field-form').action = 
                `{{ url('admin/content-types') }}/${{{ $contentType->id }}}/fields/${fieldId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteFieldModal'));
            modal.show();
        });
    });
    
    // Function to show add field modal
    function showAddFieldModal(fieldType) {
        // Get field type name from the config data displayed in source items
        const fieldTypeElements = document.querySelectorAll(`[data-field-type="${fieldType}"]`);
        let fieldTypeName = fieldType;
        
        if (fieldTypeElements.length > 0) {
            const nameElement = fieldTypeElements[0].querySelector('.item-title');
            if (nameElement) {
                fieldTypeName = nameElement.textContent;
            }
        }
        
        // Reset the form first
        const modalForm = document.querySelector('#modal-field-form');
        if (modalForm) {
            modalForm.reset();
        }
        
        // Update the modal title
        document.querySelector('#addFieldModal .modal-title').textContent = `Add ${fieldTypeName} Field`;
        
        // If there's a select dropdown for field type, select the correct option and disable it
        const fieldTypeSelect = document.querySelector('#modal-field-form #modal-field_type');
        if (fieldTypeSelect) {
            // Select the option
            Array.from(fieldTypeSelect.options).forEach(option => {
                if (option.value === fieldType) {
                    option.selected = true;
                }
            });
            
            // Disable the select since we're pre-selecting a field type
            fieldTypeSelect.disabled = true;
        }
        
        // Ensure we have a hidden field to carry the field_type value when the select is disabled
        let hiddenFieldType = document.querySelector('#modal-field-form input[name="field_type"][type="hidden"]');
        
        if (!hiddenFieldType && fieldTypeSelect && fieldTypeSelect.disabled) {
            hiddenFieldType = document.createElement('input');
            hiddenFieldType.type = 'hidden';
            hiddenFieldType.name = 'field_type';
            fieldTypeSelect.parentNode.appendChild(hiddenFieldType);
        }
        
        if (hiddenFieldType) {
            hiddenFieldType.value = fieldType;
        }
        
        // Manually trigger the options visibility check
        // This ensures fields with has_options=true in config have their options tab accessible
        setTimeout(() => {
            if (typeof window.triggerOptionsVisibilityCheck === 'function') {
                // The function might be defined in the modal's scope after it loads
                window.triggerOptionsVisibilityCheck(fieldType);
            } else {
                // Direct DOM manipulation if the function isn't available
                const optionFieldTypes = @json(collect(config('field_types'))->filter(function($type) { return $type['has_options'] ?? false; })->keys());
                
                const optionsTab = document.querySelector('a[href="#modal-options"]');
                const optionsContainer = document.getElementById('modal-options-container');
                
                if (optionFieldTypes.includes(fieldType)) {
                    if (optionsTab) {
                        optionsTab.classList.remove('disabled');
                        optionsTab.removeAttribute('tabindex');
                    }
                    if (optionsContainer) {
                        optionsContainer.classList.remove('d-none');
                    }
                }
            }
        }, 100); // Small delay to ensure modal is fully initialized
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('addFieldModal'));
        modal.show();
        
        // Initialize tabs after modal is shown (Bootstrap needs modal to be visible for tabs to work properly)
        modal._element.addEventListener('shown.bs.modal', function() {
            // Ensure the first tab is active
            const firstTab = document.querySelector('#modal-tabs .nav-link:first-child');
            if (firstTab && !firstTab.classList.contains('active')) {
                const tab = new bootstrap.Tab(firstTab);
                tab.show();
            }
        });
    }
    
    // Make click on field type item show add modal
    document.querySelectorAll('.sortable-source-item').forEach(item => {
        item.addEventListener('click', function() {
            const fieldType = this.getAttribute('data-field-type');
            showAddFieldModal(fieldType);
        });
    });
    
    // Handle form submission in modal with AJAX to prevent page reload
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'modal-field-form') {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Creating...';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addFieldModal'));
                    modal.hide();
                    
                    // Reload page to show new field
                    window.location.reload();
                } else {
                    // Handle validation errors
                    console.error('Form validation failed:', data.errors);
                    // You can display errors here if needed
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }
    });
});
</script>
@endpush