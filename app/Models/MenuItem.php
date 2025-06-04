<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'link_type',
        'url',
        'target',
        'page_id',
        'section_id',
        'visibility_conditions',
        'position',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visibility_conditions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_url',
    ];

    /**
     * Get the menu that this item belongs to.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('position');
    }

    /**
     * Get associated page.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the full URL for this menu item based on its link type.
     *
     * @return string
     */
    public function getFullUrlAttribute(): string
    {
        switch ($this->link_type) {
            case 'page':
                if ($this->page) {
                    return url($this->page->slug ?? '/');
                }
                return '#';
            
            case 'section':
                return $this->section_id ? '#' . $this->section_id : '#';
            
            case 'url':
            case 'external':
            default:
                return $this->url ?? '#';
        }
    }

    /**
     * Check if this menu item should be visible based on conditions.
     *
     * @param int|null $pageId
     * @param int|null $templateId
     * @param bool $isAuthenticated
     * @return bool
     */
    public function isVisibleForContext(?int $pageId = null, ?int $templateId = null, bool $isAuthenticated = false): bool
    {
        // If no visibility conditions are set, always show
        if (empty($this->visibility_conditions)) {
            return true;
        }
        
        $conditions = $this->visibility_conditions;
        
        // Check page-specific conditions
        if (!empty($conditions['page_ids']) && !in_array($pageId, $conditions['page_ids'])) {
            return false;
        }
        
        // Check template conditions
        if (!empty($conditions['template_ids']) && !in_array($templateId, $conditions['template_ids'])) {
            return false;
        }
        
        // Check excluded pages
        if (!empty($conditions['excluded_page_ids']) && in_array($pageId, $conditions['excluded_page_ids'])) {
            return false;
        }
        
        // Check excluded templates
        if (!empty($conditions['excluded_template_ids']) && in_array($templateId, $conditions['excluded_template_ids'])) {
            return false;
        }
        
        // Check authentication requirement
        if (!empty($conditions['auth_required']) && $conditions['auth_required'] && !$isAuthenticated) {
            return false;
        }
        
        return true;
    }
}
