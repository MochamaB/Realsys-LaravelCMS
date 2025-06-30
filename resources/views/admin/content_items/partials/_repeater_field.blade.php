{{--
    Repeater Field Partial
    
    Variables expected:
    - field: The ContentTypeField model instance
    - contentItem: Optional ContentItem model instance for existing items
--}}

@php
    $settings = json_decode($field->settings, true) ?? [];
    $subfields = $settings['subfields'] ?? [];
    $minItems = $settings['min_items'] ?? 0;
    $maxItems = $settings['max_items'] ?? 10;
    
    $existingValues = null;
    if (isset($contentItem)) {
        $fieldValue = $contentItem->fieldValues->where('content_type_field_id', $field->id)->first();
        if ($fieldValue) {
            $existingValues = json_decode($fieldValue->value, true) ?? [];
        }
    }
@endphp

<div class="repeater-field" 
    data-field-id="{{ $field->id }}" 
    data-min-items="{{ $minItems }}" 
    data-max-items="{{ $maxItems }}">
    
    <div class="repeater-items-container">
        {{-- Existing items will be loaded here --}}
        @if($existingValues)
            @foreach($existingValues as $index => $itemData)
                <div class="repeater-item card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Item #{{ $index + 1 }}</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light move-item-up"><i class="ri-arrow-up-line"></i></button>
                            <button type="button" class="btn btn-sm btn-light move-item-down"><i class="ri-arrow-down-line"></i></button>
                            <button type="button" class="btn btn-sm btn-danger remove-repeater-item"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($subfields as $subfield)
                            <div class="mb-3">
                                <label class="form-label">{{ $subfield['label'] }}</label>
                               
                                @switch($subfield['type'])
                                    @case('text')
                                        <input type="text" class="form-control" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}]" 
                                            value="{{ $itemData[$subfield['name']] ?? '' }}"
                                            {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                        @break
                                    @case('textarea')
                                        <textarea class="form-control" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}]"
                                            {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>{{ $itemData[$subfield['name']] ?? '' }}</textarea>
                                        @break
                                    @case('number')
                                        <input type="number" class="form-control" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}]" 
                                            value="{{ $itemData[$subfield['name']] ?? '' }}"
                                            {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                        @break
                                    @case('image')
                                        @php
                                            $imageData = $itemData[$subfield['name']] ?? null;
                                            $hasImage = is_array($imageData) && isset($imageData['url']);
                                        @endphp
                                        
                                        @if($hasImage)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/'.$imageData['url']) }}" alt="{{ $imageData['name'] ?? 'Image preview' }}" 
                                                class="img-thumbnail" style="max-height: 100px;">
                                            
                                            <input type="hidden" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}][id]" value="{{ $imageData['id'] }}">
                                            <input type="hidden" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}][url]" value="{{ $imageData['url'] }}">
                                            <input type="hidden" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}][name]" value="{{ $imageData['name'] }}">
                                        </div>
                                        @endif
                                        
                                        <div class="input-group">
                                            <input type="file" class="form-control" name="field_{{ $field->id }}_{{ $index }}_{{ $subfield['name'] }}" 
                                                accept="image/*" 
                                                {{ !$hasImage && isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                            @if($hasImage)
                                                <button type="button" class="btn btn-outline-danger remove-image-btn">Remove</button>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">{{ $hasImage ? 'Upload a new file to replace the existing image' : 'Select an image to upload' }}</small>
                                        @break
                                    @case('boolean')
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="field_{{ $field->id }}_{{ $index }}_{{ $subfield['name'] }}" 
                                                name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}]" 
                                                value="1" {{ isset($itemData[$subfield['name']]) && $itemData[$subfield['name']] ? 'checked' : '' }}
                                                {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                            <label class="form-check-label" for="field_{{ $field->id }}_{{ $index }}_{{ $subfield['name'] }}">Yes</label>
                                        </div>
                                        @break
                                    @case('date')
                                        <input type="date" class="form-control" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}]"
                                            value="{{ $itemData[$subfield['name']] ?? '' }}"
                                            {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                        @break
                                    @default
                                        <input type="text" class="form-control" name="field_{{ $field->id }}[{{ $index }}][{{ $subfield['name'] }}]" 
                                            value="{{ $itemData[$subfield['name']] ?? '' }}"
                                            {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    
    {{-- Template for new items --}}
    <div class="repeater-template d-none">
        <div class="repeater-item card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">New Item</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-light move-item-up"><i class="ri-arrow-up-line"></i></button>
                    <button type="button" class="btn btn-sm btn-light move-item-down"><i class="ri-arrow-down-line"></i></button>
                    <button type="button" class="btn btn-sm btn-danger remove-repeater-item"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>
            <div class="card-body">
                @foreach($subfields as $subfield)
                    <div class="mb-3">
                        <label class="form-label">{{ $subfield['label'] }}</label>
                        @switch($subfield['type'])
                            @case('text')
                                <input type="text" class="form-control template-input" data-name="field_{{ $field->id }}[__INDEX__][{{ $subfield['name'] }}]" 
                                    {{ isset($subfield['required']) && $subfield['required'] ? 'data-required="required"' : '' }}>
                                @break
                            @case('textarea')
                                <textarea class="form-control template-input" data-name="field_{{ $field->id }}[__INDEX__][{{ $subfield['name'] }}]" 
                                    {{ isset($subfield['required']) && $subfield['required'] ? 'data-required="required"' : '' }}></textarea>
                                @break
                            @case('number')
                                <input type="number" class="form-control template-input" data-name="field_{{ $field->id }}[__INDEX__][{{ $subfield['name'] }}]" 
                                    {{ isset($subfield['required']) && $subfield['required'] ? 'data-required="required"' : '' }}>
                                @break
                            @case('image')
                                <div class="input-group">
                                    <input type="file" class="form-control template-input" 
                                        data-name="field_{{ $field->id }}___INDEX___{{ $subfield['name'] }}" 
                                        accept="image/*" 
                                        {{ isset($subfield['required']) && $subfield['required'] ? 'data-required="required"' : '' }}>
                                </div>
                                <small class="form-text text-muted">Select an image to upload</small>
                                @break
                            @case('boolean')
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input template-input" data-id="field_{{ $field->id }}___INDEX___{{ $subfield['name'] }}" 
                                        data-name="field_{{ $field->id }}[__INDEX__][{{ $subfield['name'] }}]" value="1" 
                                        {{ isset($subfield['required']) && $subfield['required'] ? 'data-required="required"' : '' }}>
                                    <label class="form-check-label template-label" data-for="field_{{ $field->id }}___INDEX___{{ $subfield['name'] }}">Yes</label>
                                </div>
                                @break
                            @case('date')
                                <input type="date" class="form-control" name="field_{{ $field->id }}[__INDEX__][{{ $subfield['name'] }}]"
                                    {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                                @break
                            @default
                                <input type="text" class="form-control" name="field_{{ $field->id }}[__INDEX__][{{ $subfield['name'] }}]"
                                    {{ isset($subfield['required']) && $subfield['required'] ? 'required' : '' }}>
                        @endswitch
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="repeater-controls">
        <button type="button" class="btn btn-primary add-repeater-item">
            <i class="ri-add-line"></i> Add Item
        </button>
        <small class="text-muted ms-2">
            @if($minItems > 0)
                Min items: {{ $minItems }}.
            @endif
            @if($maxItems > 0)
                Max items: {{ $maxItems }}.
            @endif
        </small>
    </div>
</div>
