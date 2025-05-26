@php
    $options = $field->options ?? collect();
    $fieldPrefix = isset($index) ? "fields[{$index}]" : "fields[__INDEX__]";
@endphp

<div class="field-options border rounded p-3 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Options</h6>
        <button type="button" class="btn btn-success btn-sm add-option">
            <i class="ri-add-line"></i> Add Option
        </button>
    </div>

    <div class="options-container">
        @foreach($options as $optionIndex => $option)
            <div class="option-item row g-2 mb-2">
                <input type="hidden" 
                       name="{{ $fieldPrefix }}[options][{{ $optionIndex }}][id]" 
                       value="{{ $option->id }}">
                
                <div class="col-5">
                    <input type="text" 
                           class="form-control" 
                           name="{{ $fieldPrefix }}[options][{{ $optionIndex }}][value]"
                           value="{{ $option->value }}"
                           placeholder="Value"
                           required>
                </div>
                
                <div class="col-5">
                    <input type="text" 
                           class="form-control" 
                           name="{{ $fieldPrefix }}[options][{{ $optionIndex }}][label]"
                           value="{{ $option->label }}"
                           placeholder="Label"
                           required>
                </div>
                
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm delete-option">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Option Template -->
<template class="option-template">
    <div class="option-item row g-2 mb-2">
        <div class="col-5">
            <input type="text" 
                   class="form-control" 
                   name="{{ $fieldPrefix }}[options][__OPTION_INDEX__][value]"
                   placeholder="Value"
                   required>
        </div>
        
        <div class="col-5">
            <input type="text" 
                   class="form-control" 
                   name="{{ $fieldPrefix }}[options][__OPTION_INDEX__][label]"
                   placeholder="Label"
                   required>
        </div>
        
        <div class="col-2">
            <button type="button" class="btn btn-danger btn-sm delete-option">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.options-container');
        const template = document.querySelector('.option-template');
        const addBtn = document.querySelector('.add-option');
        let optionIndex = {{ $options->count() }};

        // Add option
        addBtn.addEventListener('click', function() {
            const newOption = template.content.cloneNode(true);
            
            // Update option index
            newOption.querySelectorAll('[name*="__OPTION_INDEX__"]').forEach(input => {
                input.name = input.name.replace('__OPTION_INDEX__', optionIndex);
            });

            // Add delete handler
            newOption.querySelector('.delete-option').addEventListener('click', function() {
                this.closest('.option-item').remove();
            });

            container.appendChild(newOption);
            optionIndex++;
        });

        // Delete existing options
        document.querySelectorAll('.delete-option').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.option-item').remove();
            });
        });
    });
</script>
