@php
    $fieldName = $field->is_repeatable ? "fields[{$field->id}][{$index}]" : "fields[{$field->id}]";
    $fieldId = $field->is_repeatable ? "field_{$field->id}_{$index}" : "field_{$field->id}";
@endphp

<input type="file" 
       class="widget-image-upload @error($fieldName) is-invalid @enderror" 
       id="{{ $fieldId }}"
       name="{{ $fieldName }}"
       accept="image/*"
       data-value="{{ old($fieldName, $value) }}"
       @if($field->is_required) required @endif>

@error($fieldName)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
