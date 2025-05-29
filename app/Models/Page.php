<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Page extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'template_id',
        'title',
        'slug',
        'description',
        'content',
        'status',
        'parent_id',
        'is_active',
        'is_homepage',
        'show_in_menu',
        'menu_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'string',
        'is_active' => 'boolean',
        'is_homepage' => 'boolean',
        'show_in_menu' => 'boolean',
        'menu_order' => 'integer'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'published_at'
    ];

    /**
     * Get the template that owns the page.
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the sections for the page.
     */
    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('order_index');
    }

    /**
     * Get the menu items that link to this page.
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Get the user that created the page.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the admin that created the page.
     */
    public function adminCreator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the user that last updated the page.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the admin that last updated the page.
     */
    public function adminUpdater()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /**
     * Get the creator regardless of whether it's a user or admin.
     * This method determines the creator type based on the guard.
     */
    public function getCreator()
    {
        // Check if the creator is an admin
        if ($admin = $this->adminCreator()->first()) {
            return $admin;
        }

        // Otherwise return the user
        return $this->creator()->first();
    }

    /**
     * Get the updater regardless of whether it's a user or admin.
     * This method determines the updater type based on the guard.
     */
    public function getUpdater()
    {
        // Check if the updater is an admin
        if ($admin = $this->adminUpdater()->first()) {
            return $admin;
        }

        // Otherwise return the user
        return $this->updater()->first();
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the URL for this page.
     */
    public function getUrl()
    {
        if ($this->slug === 'home') {
            return url('/');
        }
        
        return url($this->slug);
    }
    
    /**
     * Check if this page has a section with the given slug
     *
     * @param string $sectionSlug
     * @return bool
     */
    public function hasSection(string $sectionSlug): bool
    {
        return app(\App\Services\PageSectionManager::class)->hasSection($this, $sectionSlug);
    }
    
    /**
     * Get a section by its slug
     *
     * @param string $sectionSlug
     * @return \App\Models\PageSection|null
     */
    public function getSection(string $sectionSlug): ?PageSection
    {
        return app(\App\Services\PageSectionManager::class)->getSection($this, $sectionSlug);
    }
    
    /**
     * Sync the page sections with the template sections
     *
     * @return array Result of the synchronization
     */
    public function syncSections(): array
    {
        return app(\App\Services\PageSectionManager::class)->syncPageSections($this);
    }
    
    /**
     * Handle template switching
     *
     * @param int $oldTemplateId The previous template ID
     * @return array Result of the synchronization
     */
    public function handleTemplateSwitching(int $oldTemplateId): array
    {
        return app(\App\Services\PageSectionManager::class)->handleTemplateSwitching($this, $oldTemplateId);
    }
    
    /**
     * Register media collections for the page
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->useDisk('public');
            
        $this->addMediaCollection('gallery')
            ->useDisk('public');
            
        $this->addMediaCollection('attachments')
            ->useDisk('public');
    }
    
    /**
     * Register media conversions for the page
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();
            
        $this->addMediaConversion('medium')
            ->width(400)
            ->height(300)
            ->nonQueued();
            
        $this->addMediaConversion('large')
            ->width(800)
            ->height(600)
            ->nonQueued();
    }
}
