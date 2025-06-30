{{-- 
  Dynamic Content Fields Partial 
  Parameters:
  - $contentType: The content type being edited
  - $fields: Collection of fields for this content type ordered by position
  - $contentItem: The content item being edited (null for create)
  - $prefix: Prefix for form element IDs to avoid conflicts in modals
--}}

@php
    $prefix = $prefix ?? '';
    $contentItem = $contentItem ?? null;
    // Helper to get field value either from old input, content item, or default
    $getFieldValue = function($field) use ($contentItem) {
        if (old('field_' . $field->id)) {
            return old('field_' . $field->id);
        }
        
        if ($contentItem) {
            $fieldValue = $contentItem->fieldValues->where('content_type_field_id', $field->id)->first();
            if ($fieldValue) {
                return $fieldValue->value;
            }
        }
        
        return $field->default_value;
    };
@endphp

<!-- Dynamic content fields -->
<h6 class="mb-4">Enter {{ $contentType->name }} values</h6>

@foreach($fields as $field)
    <div class="mb-4">
        <label for="{{ $prefix }}field_{{ $field->id }}" class="form-label">
            {{ $field->name }}
            @if($field->is_required)
                <span class="text-danger">*</span>
            @endif
        </label>
        
        @if($field->description)
            <p class="text-muted small">{{ $field->description }}</p>
        @endif
        
        @php $fieldValue = $getFieldValue($field); @endphp
        
        @switch($field->field_type)
            @case('text')
                <input type="text" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('textarea')
                <textarea class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" rows="3" {{ $field->is_required ? 'required' : '' }}>{{ $fieldValue }}</textarea>
                @break
                
            @case('rich_text')
                <textarea class="form-control rich-text-editor" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" rows="5" {{ $field->is_required ? 'required' : '' }}>{{ $fieldValue }}</textarea>
                @break
                
            @case('number')
                <input type="number" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('date')
                <input type="date" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('datetime')
                <input type="datetime-local" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('boolean')
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="{{ $prefix }}field_{{ $field->id }}" 
                        name="field_{{ $field->id }}" value="1" {{ $fieldValue ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $prefix }}field_{{ $field->id }}">Yes</label>
                </div>
                @break
                
            @case('select')
                <select class="form-select" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" {{ $field->is_required ? 'required' : '' }}>
                    <option value="">Select an option</option>
                    @foreach($field->options()->orderBy('order_index')->get() as $option)
                        <option value="{{ $option->value }}" {{ $fieldValue == $option->value ? 'selected' : '' }}>
                            {{ $option->label }}
                        </option>
                    @endforeach
                </select>
                @break
                
            @case('multiselect')
                <select class="form-select" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}[]" multiple {{ $field->is_required ? 'required' : '' }}>
                    @foreach($field->options()->orderBy('order_index')->get() as $option)
                       @php 
                            $selectedValues = is_array($fieldValue) ? $fieldValue : json_decode($fieldValue) ?? [];
                        @endphp
                        <option value="{{ $option->value }}" 
                            {{ in_array($option->value, $selectedValues) ? 'selected' : '' }}>
                            {{ $option->label }}
                        </option>
                    @endforeach
                </select>
                @break
            @case('radio')
                <div class="form-check">
                    @foreach($field->options()->orderBy('order_index')->get() as $option)
                        <input class="form-check-input" type="radio" id="{{ $prefix }}field_{{ $field->id }}_{{ $option->id }}" 
                            name="field_{{ $field->id }}" value="{{ $option->value }}" 
                            {{ $fieldValue == $option->value ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}field_{{ $field->id }}_{{ $option->id }}">
                            {{ $option->label }}
                        </label>
                    @endforeach
                </div>
                @break
            @case('checkbox')
                <div class="form-check">
                    @foreach($field->options()->orderBy('order_index')->get() as $option)
                        <input class="form-check-input" type="checkbox" id="{{ $prefix }}field_{{ $field->id }}_{{ $option->id }}" 
                            name="field_{{ $field->id }}[]" value="{{ $option->value }}" 
                            {{ in_array($option->value, $selectedValues) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}field_{{ $field->id }}_{{ $option->id }}">
                            {{ $option->label }}
                        </label>
                    @endforeach
                </div>
                @break
                
            @case('image')
                @php
                    $selectedMediaId = null;
                    if ($contentItem) {
                        $media = $contentItem->getMedia('field_' . $field->id)->first();
                        if ($media) {
                            $selectedMediaId = $media->id;
                        }
                    }
                @endphp
                <x-media-picker
                    name="field_{{ $field->id }}"
                    label="{{ $field->label }}"
                    :multiple="false"
                    :selected="$selectedMediaId"
                    :allowedTypes="['image']"
                />
                @break
                
            @case('gallery')
            @php
                    $selectedMediaId = null;
                    if ($contentItem) {
                        $media = $contentItem->getMedia('field_' . $field->id)->first();
                        if ($media) {
                            $selectedMediaId = $media->id;
                        }
                    }
                @endphp
                <x-media-picker
                    name="field_{{ $field->id }}"
                    label="{{ $field->label }}"
                    :multiple="true"
                    :selected="$selectedMediaId"
                    :allowedTypes="['image']"
                />
                @break
                
            @case('file')
            @php
                    $selectedMediaId = null;
                    if ($contentItem) {
                        $media = $contentItem->getMedia('field_' . $field->id)->first();
                        if ($media) {
                            $selectedMediaId = $media->id;
                        }
                    }
                @endphp
                <x-media-picker
                    name="field_{{ $field->id }}"
                    label="{{ $field->label }}"
                    :multiple="true"
                    :selected="$selectedMediaId"
                    :allowedTypes="['file']"
                />
                @break
                
            @case('url')
                <input type="url" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('email')
                <input type="email" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('repeater')
                @include('admin.content_items.partials._repeater_field', [
                    'field' => $field,
                    'contentItem' => $contentItem
                ])
                @break
                
            @case('phone')
                <input type="tel" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('color')
                <input type="color" class="form-control form-control-color" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue ?: '#000000' }}" 
                    {{ $field->is_required ? 'required' : '' }}>
                @break
                
            @case('json')
                <textarea class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" rows="5" {{ $field->is_required ? 'required' : '' }}>{{ $fieldValue ?: '{}' }}</textarea>
                @break
                
            @default
                <input type="text" class="form-control" id="{{ $prefix }}field_{{ $field->id }}" 
                    name="field_{{ $field->id }}" value="{{ $fieldValue }}" 
                    {{ $field->is_required ? 'required' : '' }}>
        @endswitch
    </div>
@endforeach
