<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bx bx-list-ul"></i> Widget Field Definitions
        </h5>
    </div>
    <div class="card-body">
        @if($widget->fieldDefinitions->isEmpty())
            <div class="alert alert-info mb-0">
                <i class="bx bx-info-circle me-1"></i>
                This widget has no field definitions defined in its widget.json file.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Name</th>
                            <th width="15%">Slug</th>
                            <th width="15%">Field Type</th>
                            <th width="30%">Description</th>
                            <th width="15%">Properties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($widget->fieldDefinitions->sortBy('position') as $index => $field)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $field->name }}</strong>
                                </td>
                                <td><code>{{ $field->slug }}</code></td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bx bx-{{ config('field_types.' . $field->field_type . '.icon', 'help-circle') }} me-1"></i>
                                        {{ config('field_types.' . $field->field_type . '.name', ucfirst($field->field_type)) }}
                                    </span>
                                    @if($field->field_type === 'repeater' && $field->settings && isset($field->settings['subfields']) && is_array($field->settings['subfields']))
                                        <div class="mt-2 small">
                                            <strong>Subfields:</strong> <br>
                                            @foreach($field->settings['subfields'] as $index => $subField)
                                                {{ $index > 0 ? ' ' : '' }}
                                                <span class="text-secondary">{{ $subField['name'] ?? 'Unnamed' }}</span>
                                                <small>({{ $subField['field_type'] ?? $subField['type'] ?? 'text' }})</small><br>
                                            @endforeach
                                            
                                            @if(isset($field->settings['min_items']) || isset($field->settings['max_items']))
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="bx bx-info-circle"></i>
                                                        {{ isset($field->settings['min_items']) ? 'Min: '.$field->settings['min_items'] : '' }}
                                                        {{ isset($field->settings['min_items']) && isset($field->settings['max_items']) ? ', ' : '' }}
                                                        {{ isset($field->settings['max_items']) ? 'Max: '.$field->settings['max_items'] : '' }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $field->description ?? 'No description' }}</td>
                                <td>
                                    @if($field->is_required)
                                        <span class="badge bg-danger">Required</span>
                                    @endif
                                    
                                    @if($field->settings && is_array($field->settings) && !empty($field->settings))
                                        <span class="badge bg-secondary" data-bs-toggle="tooltip" title="Has custom settings">
                                            <i class="bx bx-cog"></i> Settings
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            
                            {{-- Repeater field subfields are now displayed in the field type column --}}
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <p class="text-muted"><small><i class="bx bx-info-circle me-1"></i> Field definitions are defined in the widget.json file and are used for mapping content to this widget.</small></p>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .repeater-subfield td {
        background-color: #f8f9fa;
        border-top: none;
    }
    
    .repeater-constraints td {
        font-size: 0.875rem;
        font-style: italic;
        color: #6c757d;
    }
</style>
@endpush
