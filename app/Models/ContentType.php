<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContentType extends Model
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
        'icon'
    ];

    /**
     * Get the fields for the content type.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ContentTypeField::class);
    }

    /**
     * Get the content items for the content type.
     */
    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class);
    }
    
    /**
     * Get the widgets associated with this content type.
     */
    public function widgets(): BelongsToMany
    {
        return $this->belongsToMany(Widget::class, 'widget_content_type_associations')
            ->withTimestamps();
    }
    
    /**
     * Get the widget associations for this content type.
     */
    public function widgetAssociations(): HasMany
    {
        return $this->hasMany(WidgetContentTypeAssociation::class);
    }

    /**
     * Get the user that created the content type.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the content type.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get content type by key.
     *
     * @param string $key
     * @return ContentType|null
     */
    public static function findByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Check if the content type has any content items.
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->contentItems()->count() > 0;
    }
}
