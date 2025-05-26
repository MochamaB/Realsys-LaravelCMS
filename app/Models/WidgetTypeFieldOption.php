<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetTypeFieldOption extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'widget_type_field_id',
        'value',
        'label',
        'order_index'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_index' => 'integer'
    ];

    /**
     * Get the field that owns this option.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(WidgetTypeField::class, 'widget_type_field_id');
    }
}
