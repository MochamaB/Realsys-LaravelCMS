@php
    $fieldName = $field->is_repeatable ? "fields[{$field->id}][{$index}][]" : "fields[{$field->id}][]";
    $fieldId = $field->is_repeatable ? "field_{$field->id}_{$index}" : "field_{$field->id}";
    $selectedValues = old($fieldName, is_array($value) ? $value : explode(',', $value ?? ''));
@endphp

<select class="form-select @error($fieldName) is-invalid @enderror" 
        id="{{ $fieldId }}"
        name="{{ $fieldName }}"
        multiple
        @if($field->is_required) required @endif>
    @foreach($field->options as $option)
        <option value="{{ $option->value }}" {{ in_array($option->value, $selectedValues) ? 'selected' : '' }}>
            {{ $option->label }}
        </option>
    @endforeach
</select>

@error($fieldName)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
