<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'description',
        'is_system',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the fields for the content type.
     */
    public function fields()
    {
        return $this->hasMany(ContentTypeField::class)->orderBy('order_index');
    }

    /**
     * Get the content items for the content type.
     */
    public function contentItems()
    {
        return $this->hasMany(ContentItem::class);
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
