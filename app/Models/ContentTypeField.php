<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTypeField extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_type_id',
        'name',
        'key',
        'type',
        'required',
        'description',
        'validation_rules',
        'default_value',
        'order_index',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'required' => 'boolean',
    ];

    /**
     * Get the content type that owns the field.
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the field options for the field.
     */
    public function options()
    {
        return $this->hasMany(ContentTypeFieldOption::class, 'field_id')->orderBy('order_index');
    }

    /**
     * Get the field values for the field.
     */
    public function fieldValues()
    {
        return $this->hasMany(ContentFieldValue::class, 'field_id');
    }

    /**
     * Get validation rules as an array.
     *
     * @return array
     */
    public function getValidationRulesArray()
    {
        if (empty($this->validation_rules)) {
            return [];
        }

        return explode('|', $this->validation_rules);
    }

    /**
     * Check if the field is of a specific type.
     *
     * @param string $type
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }
}
