<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class WidgetFieldValue extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'widget_id',
        'widget_type_field_id',
        'value',
    ];

    /**
     * Get the widget that owns this field value.
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get the field definition for this value.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(WidgetTypeField::class, 'widget_type_field_id');
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('field_files')
            ->useDisk('public');

        $this->addMediaCollection('field_images')
            ->useDisk('public');
    }
    
    /**
     * Get the formatted value based on field type.
     *
     * @return mixed
     */
    public function getFormattedValue()
    {
        if (!$this->field) {
            return $this->value;
        }
        
        $fieldType = $this->field->field_type;
        $value = $this->value;
        
        switch ($fieldType) {
            case 'wysiwyg':
                return new HtmlString($value);
                
            case 'date':
                return $value ? Carbon::parse($value)->format('Y-m-d') : null;
                
            case 'time':
                return $value ? Carbon::parse($value)->format('H:i') : null;
                
            case 'datetime':
                return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
                
            case 'select':
            case 'radio':
                if ($value) {
                    // Find the option with matching value
                    $option = $this->field->options()->where('value', $value)->first();
                    return $option ? $option->label : $value;
                }
                return null;
                
            case 'multiselect':
            case 'checkbox':
                if ($value) {
                    $values = json_decode($value, true);
                    if (is_array($values)) {
                        $options = $this->field->options()->whereIn('value', $values)->pluck('label', 'value')->toArray();
                        return array_values($options);
                    }
                }
                return [];
                
            case 'file':
            case 'image':
                // Return the media collection
                return $this->getMedia($fieldType === 'image' ? 'field_images' : 'field_files');
                
            default:
                return $value;
        }
    }
    
    /**
     * Validate the value based on field type and validation rules.
     *
     * @param mixed $value
     * @return array
     */
    public static function validateValue($value, WidgetTypeField $field)
    {
        $rules = ['nullable'];
        
        if ($field->is_required) {
            $rules = ['required'];
        }
        
        // Add field-specific validation rules
        switch ($field->field_type) {
            case 'email':
                $rules[] = 'email';
                break;
                
            case 'url':
                $rules[] = 'url';
                break;
                
            case 'number':
                $rules[] = 'numeric';
                break;
                
            case 'date':
                $rules[] = 'date';
                break;
                
            case 'time':
                $rules[] = 'date_format:H:i';
                break;
                
            case 'datetime':
                $rules[] = 'date';
                break;
                
            case 'select':
            case 'radio':
                $rules[] = 'string';
                $optionValues = $field->options()->pluck('value')->toArray();
                if (!empty($optionValues)) {
                    $rules[] = 'in:' . implode(',', $optionValues);
                }
                break;
                
            case 'multiselect':
            case 'checkbox':
                $rules[] = 'array';
                $optionValues = $field->options()->pluck('value')->toArray();
                if (!empty($optionValues)) {
                    $rules[] = 'in:' . implode(',', $optionValues);
                }
                break;
                
            case 'file':
                $rules[] = 'file';
                break;
                
            case 'image':
                $rules[] = 'image';
                break;
        }
        
        // Add any custom validation rules from the field
        if ($field->validation_rules) {
            $customRules = explode('|', $field->validation_rules);
            $rules = array_merge($rules, $customRules);
        }
        
        return [$field->name => $rules];
    }
}
