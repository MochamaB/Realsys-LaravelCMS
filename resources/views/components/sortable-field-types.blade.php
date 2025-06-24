{{--
    Sortable Field Types Component
    
    Props:
    - id: Unique identifier for the field types list
    - fieldTypes: Collection or array of field types to display
    - group: The group name to connect this source with target sortable lists
--}}

@props([
    'id' => 'field-types-list',
    'fieldTypes' => config('field_types'),
    'group' => 'fields-group'
])

<div class="sortable-source-container">
    <div class="sortable-source" id="{{ $id }}" data-group="{{ $group }}">
        @foreach($fieldTypes as $type => $field)
            <div class="sortable-source-item" data-field-type="{{ $type }}">
                <div class="item-icon">
                    <i class="bx bx-{{ $field['icon'] ?? 'help-circle' }}"></i>
                </div>
                <div class="item-content">
                    <span class="item-title">{{ $field['name'] ?? $type }}</span>
                    <span class="item-subtitle">{{ $field['description'] ?? '' }}</span>
                </div>
                <div class="drag-handle">
                    <i class="ri-drag-move-line"></i>
                </div>
            </div>
        @endforeach
    </div>
    <div class="text-center mt-2">
        <small class="text-muted">Drag field types to add to content structure</small>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize this source list specifically to ensure it's properly set up
        if (window.SortableManager && typeof window.SortableManager.initializeSources === 'function') {
            window.SortableManager.initializeSources();
        }
    });
</script>
@endpush
