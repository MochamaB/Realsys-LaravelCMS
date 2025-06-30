<?php

namespace App\Models;

use App\Models\Extensions\MediaExtension;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Media extends SpatieMedia
{
    use MediaExtension;
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
    ];
}
