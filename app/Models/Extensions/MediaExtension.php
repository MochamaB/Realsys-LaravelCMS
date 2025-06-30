<?php

namespace App\Models\Extensions;

use App\Models\MediaFolder;
use App\Models\MediaTag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait MediaExtension
{
    /**
     * Get the folder that contains this media item.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Get the tags associated with this media item.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_tag_media')->withTimestamps();
    }

    /**
     * Get folder path as breadcrumb array
     */
    public function getFolderPathAttribute(): array
    {
        $path = [];
        $folder = $this->folder;
        
        while ($folder) {
            $path[] = [
                'id' => $folder->id,
                'name' => $folder->name
            ];
            $folder = $folder->parent;
        }
        
        return array_reverse($path);
    }
}
