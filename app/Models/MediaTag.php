<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    /**
     * Get media items that have this tag.
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_tag_media')
                    ->withTimestamps();
    }
}