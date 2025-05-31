<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetFieldDefinition extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'widget_id',
        'name',
        'slug',
        'field_type',
        'validation_rules',
        'settings',
        'is_required',
        'position',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'json',
        'is_required' => 'boolean',
    ];

    /**
     * Get the widget that owns this field definition.
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }
    
    /**
     * Get the field type details.
     *
     * @return array
     */
    public function getFieldTypeDetails(): array
    {
        $fieldTypes = [
            'text' => [
                'name' => 'Text',
                'component' => 'text-field',
            ],
            'textarea' => [
                'name' => 'Textarea',
                'component' => 'textarea-field',
            ],
            'rich_text' => [
                'name' => 'Rich Text',
                'component' => 'rich-text-field',
            ],
            'image' => [
                'name' => 'Image',
                'component' => 'image-field',
            ],
            'select' => [
                'name' => 'Select',
                'component' => 'select-field',
            ],
            'checkbox' => [
                'name' => 'Checkbox',
                'component' => 'checkbox-field',
            ],
            'date' => [
                'name' => 'Date',
                'component' => 'date-field',
            ],
            'email' => [
                'name' => 'Email',
                'component' => 'email-field',
            ],
        ];

        return $fieldTypes[$this->field_type] ?? [
            'name' => ucfirst($this->field_type),
            'component' => 'text-field',
        ];
    }
}
