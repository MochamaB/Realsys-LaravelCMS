{{-- Widget Editor Form for Simplified Live Preview --}}
@php
// Helper function to safely get field values with type checking
function getFieldValue($fieldValues, $fieldName, $default = '') {
    $value = $fieldValues[$fieldName] ?? $default;
    
    // Handle different value types safely
    if (is_array($value)) {
        // For arrays (repeater fields), return JSON string for form inputs
        return json_encode($value);
    } elseif (is_null($value)) {
        // Handle null values
        return is_string($default) ? $default : '';
    } elseif (is_bool($value)) {
        // Handle boolean values
        return $value ? '1' : '0';
    } else {
        // Convert everything else to string safely
        return (string)$value;
    }
}

// Helper function to safely check if a value is selected
function isFieldValueSelected($fieldValues, $fieldName, $compareValue, $default = '') {
    $currentValue = $fieldValues[$fieldName] ?? $default;
    
    // Handle array values (don't compare arrays directly)
    if (is_array($currentValue)) {
        return false; // Arrays are not selectable in simple select fields
    }
    
    return (string)$currentValue === (string)$compareValue;
}
@endphp
<div class="widget-editor-form" data-widget-id="{{ $instance->id }}">
    <!-- Widget Header -->
    <div class="widget-editor-header mb-3">
        <div class="d-flex align-items-center mb-2">
            <i class="{{ $widget->icon ?? 'ri-puzzle-line' }} me-2"></i>
            <h6 class="mb-0">{{ $widget->name }}</h6>
        </div>
        @if($widget->description)
        <p class="text-muted small mb-0">{{ $widget->description }}</p>
        @endif
    </div>

    <!-- Tabs -->
    <div class="widget-editor-tabs mb-3">
        <div class="btn-group w-100" role="group">
            <button type="button" class="tab-button active" data-tab="settings">
                <i class="ri-settings-line me-1"></i> Settings
            </button>
            @if($widget->contentTypes->count() > 0)
            <button type="button" class="tab-button" data-tab="content">
                <i class="ri-file-list-line me-1"></i> Content
            </button>
            @endif
            <button type="button" class="tab-button" data-tab="style">
                <i class="ri-palette-line me-1"></i> Style
            </button>
        </div>
    </div>

    <!-- Settings Tab -->
    <div class="tab-content active" data-tab="settings" data-tab-form="settings">
        @if($widget->settings_schema && count($widget->settings_schema) > 0)
            @foreach($widget->settings_schema as $field)
                <div class="form-group mb-3">
                    <label class="form-label">{{ $field['label'] ?? $field['name'] }}</label>
                    
                    @switch($field['type'])
                        @case('text')
                            <input type="text" 
                                   name="settings[{{ $field['name'] }}]" 
                                   class="form-control" 
                                   value="{{ getFieldValue($fieldValues, $field['name'], $field['default'] ?? '') }}"
                                   placeholder="{{ $field['placeholder'] ?? '' }}">
                            @break
                            
                        @case('textarea')
                            <textarea name="settings[{{ $field['name'] }}]" 
                                      class="form-control" 
                                      rows="{{ $field['rows'] ?? 3 }}"
                                      placeholder="{{ $field['placeholder'] ?? '' }}">{{ getFieldValue($fieldValues, $field['name'], $field['default'] ?? '') }}</textarea>
                            @break
                            
                        @case('number')
                            <input type="number" 
                                   name="settings[{{ $field['name'] }}]" 
                                   class="form-control" 
                                   value="{{ getFieldValue($fieldValues, $field['name'], $field['default'] ?? '') }}"
                                   min="{{ $field['min'] ?? '' }}"
                                   max="{{ $field['max'] ?? '' }}"
                                   step="{{ $field['step'] ?? '1' }}">
                            @break
                            
                        @case('select')
                            <select name="settings[{{ $field['name'] }}]" class="form-control">
                                @if(isset($field['placeholder']))
                                <option value="">{{ $field['placeholder'] }}</option>
                                @endif
                                @foreach($field['options'] ?? [] as $option)
                                    @php
                                        $value = is_array($option) ? $option['value'] : $option;
                                        $label = is_array($option) ? $option['label'] : $option;
                                        $selected = isFieldValueSelected($fieldValues, $field['name'], $value, $field['default'] ?? '');
                                    @endphp
                                    <option value="{{ $value }}" {{ $selected ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @break
                            
                        @case('checkbox')
                            <div class="form-check">
                                @php
                                    $checkboxValue = getFieldValue($fieldValues, $field['name'], $field['default'] ?? '0');
                                    $isChecked = in_array($checkboxValue, ['1', 'true', true, 1], true);
                                @endphp
                                <input type="checkbox" 
                                       name="settings[{{ $field['name'] }}]" 
                                       class="form-check-input" 
                                       value="1"
                                       {{ $isChecked ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ $field['label'] ?? $field['name'] }}
                                </label>
                            </div>
                            @break
                            
                        @case('color')
                            <div class="d-flex align-items-center">
                                <input type="color" 
                                       name="settings[{{ $field['name'] }}]" 
                                       class="form-control color-picker" 
                                       value="{{ $fieldValues[$field['name']] ?? $field['default'] ?? '#000000' }}"
                                       style="width: 60px; height: 38px; padding: 2px;">
                                <input type="text" 
                                       class="form-control ms-2" 
                                       value="{{ $fieldValues[$field['name']] ?? $field['default'] ?? '#000000' }}"
                                       readonly>
                            </div>
                            @break
                            
                        @case('url')
                            <input type="url" 
                                   name="settings[{{ $field['name'] }}]" 
                                   class="form-control" 
                                   value="{{ getFieldValue($fieldValues, $field['name'], $field['default'] ?? '') }}"
                                   placeholder="{{ $field['placeholder'] ?? 'https://example.com' }}">
                            @break
                            
                        @case('email')
                            <input type="email" 
                                   name="settings[{{ $field['name'] }}]" 
                                   class="form-control" 
                                   value="{{ getFieldValue($fieldValues, $field['name'], $field['default'] ?? '') }}"
                                   placeholder="{{ $field['placeholder'] ?? 'email@example.com' }}">
                            @break
                            
                        @default
                            <input type="text" 
                                   name="settings[{{ $field['name'] }}]" 
                                   class="form-control" 
                                   value="{{ getFieldValue($fieldValues, $field['name'], $field['default'] ?? '') }}">
                    @endswitch
                    
                    @if(isset($field['help']))
                        <small class="form-text text-muted">{{ $field['help'] }}</small>
                    @endif
                </div>
            @endforeach
        @else
            <div class="text-center py-3">
                <i class="ri-settings-line text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mb-0">No settings available for this widget</p>
            </div>
        @endif
    </div>

    <!-- Content Tab -->
    @if($widget->contentTypes->count() > 0)
    <div class="tab-content" data-tab="content" data-tab-form="content">
        <div class="content-query-builder">
            <!-- Content Type Selection -->
            <div class="form-group mb-3">
                <label class="form-label">Content Type</label>
                <select name="content_query[content_type_id]" class="form-control">
                    <option value="">Select Content Type</option>
                    @foreach($contentTypes as $contentType)
                    @php
                        $selectedContentType = getFieldValue($fieldValues, 'content_type_id', '');
                        $isSelected = isFieldValueSelected($fieldValues, 'content_type_id', $contentType->id, '');
                    @endphp
                    <option value="{{ $contentType->id }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $contentType->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Content Filters -->
            <div id="content-filters">
                @if(getFieldValue($fieldValues, 'content_type_id', ''))
                    <div class="content-filter-section">
                        <h6>Content Filters</h6>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Limit</label>
                            <input type="number" 
                                   name="content_query[limit]" 
                                   class="form-control" 
                                   value="{{ getFieldValue($fieldValues, 'limit', '5') }}" 
                                   min="1" max="50">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Order By</label>
                            <select name="content_query[order_by]" class="form-control">
                                <option value="created_at" {{ isFieldValueSelected($fieldValues, 'order_by', 'created_at', 'created_at') ? 'selected' : '' }}>Date Created</option>
                                <option value="updated_at" {{ isFieldValueSelected($fieldValues, 'order_by', 'updated_at', 'created_at') ? 'selected' : '' }}>Date Modified</option>
                                <option value="title" {{ isFieldValueSelected($fieldValues, 'order_by', 'title', 'created_at') ? 'selected' : '' }}>Title</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Order Direction</label>
                            <select name="content_query[order_direction]" class="form-control">
                                <option value="desc" {{ isFieldValueSelected($fieldValues, 'order_direction', 'desc', 'desc') ? 'selected' : '' }}>Newest First</option>
                                <option value="asc" {{ isFieldValueSelected($fieldValues, 'order_direction', 'asc', 'desc') ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Content Preview -->
            @if(getFieldValue($fieldValues, 'content_type_id', ''))
            <div class="content-preview mt-3">
                <h6>Content Preview</h6>
                <div class="content-preview-list">
                    {{-- This would show a preview of the selected content --}}
                    <small class="text-muted">Content preview will be shown here</small>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Style Tab -->
    <div class="tab-content" data-tab="style" data-tab-form="style">
        <div class="style-controls">
            <!-- CSS Classes -->
            <div class="form-group mb-3">
                <label class="form-label">CSS Classes</label>
                <input type="text" 
                       name="css_classes" 
                       class="form-control" 
                       value="{{ getFieldValue($fieldValues, 'css_classes', '') }}" 
                       placeholder="custom-class another-class">
                <small class="form-text text-muted">Add custom CSS classes separated by spaces</small>
            </div>

            <!-- Spacing -->
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Padding</label>
                        <input type="text" 
                               name="padding" 
                               class="form-control" 
                               value="{{ getFieldValue($fieldValues, 'padding', '') }}" 
                               placeholder="20px or 1rem 2rem">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Margin</label>
                        <input type="text" 
                               name="margin" 
                               class="form-control" 
                               value="{{ getFieldValue($fieldValues, 'margin', '') }}" 
                               placeholder="10px 0">
                    </div>
                </div>
            </div>

            <!-- Colors -->
            <div class="form-group mb-3">
                <label class="form-label">Background Color</label>
                <div class="d-flex align-items-center">
                    <input type="color" 
                           name="background_color" 
                           class="form-control color-picker" 
                           value="{{ getFieldValue($fieldValues, 'background_color', '#ffffff') }}"
                           style="width: 60px; height: 38px; padding: 2px;">
                    <input type="text" 
                           class="form-control ms-2" 
                           value="{{ getFieldValue($fieldValues, 'background_color', '#ffffff') }}"
                           readonly>
                </div>
            </div>

            <!-- Size Constraints -->
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Min Width</label>
                        <input type="text" 
                               name="min_width" 
                               class="form-control" 
                               value="{{ getFieldValue($fieldValues, 'min_width', '') }}" 
                               placeholder="100px or 10rem">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Max Width</label>
                        <input type="text" 
                               name="max_width" 
                               class="form-control" 
                               value="{{ getFieldValue($fieldValues, 'max_width', '') }}" 
                               placeholder="500px or 100%">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Min Height</label>
                        <input type="text" 
                               name="min_height" 
                               class="form-control" 
                               value="{{ getFieldValue($fieldValues, 'min_height', '') }}" 
                               placeholder="50px or 3rem">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Max Height</label>
                        <input type="text" 
                               name="max_height" 
                               class="form-control" 
                               value="{{ getFieldValue($fieldValues, 'max_height', '') }}" 
                               placeholder="300px or 20rem">
                    </div>
                </div>
            </div>

            <!-- Position Settings -->
            <div class="form-group mb-3">
                <label class="form-label">Position Lock</label>
                <div class="form-check">
                    <input type="checkbox" 
                           name="locked_position" 
                           class="form-check-input" 
                           value="1"
                           {{ getFieldValue($fieldValues, 'locked_position', '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Lock widget position (prevent moving)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget Actions -->
    <div class="widget-editor-actions mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-danger btn-sm delete-widget-btn" data-widget-id="{{ $instance->id }}">
                <i class="ri-delete-bin-line"></i> Delete Widget
            </button>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm duplicate-widget-btn" data-widget-id="{{ $instance->id }}">
                    <i class="ri-file-copy-line"></i> Duplicate
                </button>
                <button type="button" class="btn btn-primary btn-sm preview-widget-btn" data-widget-id="{{ $instance->id }}">
                    <i class="ri-eye-line"></i> Preview
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-sync color picker with text input
document.addEventListener('DOMContentLoaded', function() {
    const colorPickers = document.querySelectorAll('.color-picker');
    colorPickers.forEach(colorPicker => {
        const textInput = colorPicker.parentNode.querySelector('input[type="text"]');
        if (textInput) {
            colorPicker.addEventListener('input', function() {
                textInput.value = this.value;
            });
        }
    });
});
</script>