@php
    $fieldName = $field->is_repeatable ? "fields[{$field->id}][{$index}]" : "fields[{$field->id}]";
    $fieldId = $field->is_repeatable ? "field_{$field->id}_{$index}" : "field_{$field->id}";
@endphp

<div class="form-check form-switch form-switch-success">
    <input type="checkbox" 
           class="form-check-input @error($fieldName) is-invalid @enderror" 
           id="{{ $fieldId }}"
           name="{{ $fieldName }}"
           value="1"
           {{ old($fieldName, $value) ? 'checked' : '' }}
           @if($field->is_required) required @endif>
    <label class="form-check-label" for="{{ $fieldId }}">{{ $field->label }}</label>
</div>

@error($fieldName)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
