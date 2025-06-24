<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentTypeField extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_type_id',
        'name',
        'slug',
        'field_type',
        'is_required',
        'is_unique',
        'position',
        'description',
        'validation_rules',
        'settings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * Get the content type that owns the field.
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the field values for this field.
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(ContentFieldValue::class, 'content_type_field_id');
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
        return $this->field_type === $type;
    }
    
    /**
     * Get the options for this field (used for select, radio, checkbox types)
     */
    public function options(): HasMany
    {
        return $this->hasMany(ContentTypeFieldOption::class, 'field_id');
    }
}
