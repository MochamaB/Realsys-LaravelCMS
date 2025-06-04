<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Theme extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'directory',
        'version',
        'author',
        'description',
        'is_active',
        'screenshot',
        'settings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'json'
    ];

    /**
     * Get the templates for this theme.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
    
    /**
     * Get the widgets for this theme.
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class);
    }
     /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshot')
             ->singleFile();
    }

    /**
     * Define media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(400)
             ->height(300);
    }
    public function getScreenshotPathAttribute()
    {
    // First check if we have a media item
    if ($this->hasMedia('screenshot')) {
        return $this->getFirstMediaUrl('screenshot');
    }
    
    // If no media, check for default thumbnail in theme directory
    $preferredOrder = ['webp', 'png', 'jpg', 'jpeg','gif'];
    foreach ($preferredOrder as $ext) {
    $defaultPath = "themes/{$this->slug}/img/thumbnail.{$ext}";
    if (file_exists(public_path($defaultPath))) {
        return $defaultPath;
    }
}
    
    // If neither exists, return null
    return null;
}
}
