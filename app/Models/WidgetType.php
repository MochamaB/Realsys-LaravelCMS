<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WidgetType extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'component_path',
        'icon',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the fields for this widget type.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(WidgetTypeField::class)->orderBy('order_index');
    }

    /**
     * Get the widgets of this type.
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class);
    }
}
