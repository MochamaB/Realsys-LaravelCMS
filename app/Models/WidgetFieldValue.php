<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
}
