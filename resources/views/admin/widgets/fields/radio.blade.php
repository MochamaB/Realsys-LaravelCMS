@php
    $fieldName = $field->is_repeatable ? "fields[{$field->id}][{$index}]" : "fields[{$field->id}]";
    $fieldId = $field->is_repeatable ? "field_{$field->id}_{$index}" : "field_{$field->id}";
@endphp

<div class="@error($fieldName) is-invalid @enderror">
    @foreach($field->options as $option)
        <div class="form-check">
            <input type="radio" 
                   class="form-check-input" 
                   id="{{ $fieldId }}_{{ $loop->index }}"
                   name="{{ $fieldName }}"
                   value="{{ $option->value }}"
                   {{ old($fieldName, $value) == $option->value ? 'checked' : '' }}
                   @if($field->is_required) required @endif>
            <label class="form-check-label" for="{{ $fieldId }}_{{ $loop->index }}">
                {{ $option->label }}
            </label>
        </div>
    @endforeach
</div>

@error($fieldName)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
