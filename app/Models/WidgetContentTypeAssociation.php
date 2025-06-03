<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ContentType;

class WidgetContentTypeAssociation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'widget_id',
        'content_type',
        'filter_condition',
        'sort_field',
        'sort_direction',
        'limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filter_condition' => 'json',
        'limit' => 'integer',
    ];

    // Sort direction method moved to bottom of class

    /**
     * Get the widget that owns this association
     *
     * @return BelongsTo
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get the filter conditions as array
     *
     * @return array
     */
    public function getFilterConditions(): array
    {
        return is_array($this->filter_condition) ? $this->filter_condition : [];
    }

    /**
     * Get the sort direction ensuring it's either asc or desc
     *
     * @return string
     */
    public function getSortDirection(): string
    {
        $direction = strtolower($this->sort_direction ?? 'desc');
        return in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
    }
    
    /**
     * Get the content type associated with this widget content type association
     *
     * @return BelongsTo
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }
}
