<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetTypeField extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'widget_type_id',
        'name',
        'label',
        'field_type',
        'is_required',
        'is_repeatable',
        'validation_rules',
        'help_text',
        'default_value',
        'order_index'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'is_repeatable' => 'boolean',
        'order_index' => 'integer'
    ];

    /**
     * Get the widget type that owns this field.
     */
    public function widgetType(): BelongsTo
    {
        return $this->belongsTo(WidgetType::class);
    }

    /**
     * Get the options for this field.
     */
    public function options(): HasMany
    {
        return $this->hasMany(WidgetTypeFieldOption::class)->orderBy('order_index');
    }

    /**
     * Get the field values for this field.
     */
    public function values(): HasMany
    {
        return $this->hasMany(WidgetFieldValue::class);
    }

    /**
     * Get the repeater values for this field.
     */
    public function repeaterValues(): HasMany
    {
        return $this->hasMany(WidgetRepeaterValue::class);
    }
}
