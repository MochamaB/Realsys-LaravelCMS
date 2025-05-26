@php
    $fieldName = $field->is_repeatable ? "fields[{$field->id}][{$index}]" : "fields[{$field->id}]";
    $fieldId = $field->is_repeatable ? "field_{$field->id}_{$index}" : "field_{$field->id}";
@endphp

<select class="form-select @error($fieldName) is-invalid @enderror" 
        id="{{ $fieldId }}"
        name="{{ $fieldName }}"
        @if($field->is_required) required @endif>
    <option value="">Select {{ $field->label }}</option>
    @foreach($field->options as $option)
        <option value="{{ $option->value }}" {{ old($fieldName, $value) == $option->value ? 'selected' : '' }}>
            {{ $option->label }}
        </option>
    @endforeach
</select>

@error($fieldName)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
