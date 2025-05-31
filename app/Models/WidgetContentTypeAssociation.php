<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetContentTypeAssociation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'widget_id',
        'content_type_id',
    ];

    /**
     * Get the widget that owns this association.
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get the content type that is associated with this widget.
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }
}
