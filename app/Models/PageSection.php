<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSection extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'template_section_id',
        'layout_settings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'layout_settings' => 'json'
    ];

    /**
     * Get the page that owns this section.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the template section that defines this page section.
     */
    public function templateSection(): BelongsTo
    {
        return $this->belongsTo(TemplateSection::class);
    }
    
    /**
     * Get the widgets assigned to this page section.
     */
    public function pageSectionWidgets(): HasMany
    {
        return $this->hasMany(PageSectionWidget::class);
    }
    
    /**
     * Get the widgets associated with this page section.
     */
    public function widgets(): BelongsToMany
    {
        return $this->belongsToMany(Widget::class, 'page_section_widgets')
            ->withPivot('settings')
            ->withTimestamps();
    }

    /**
     * Get the widgets for this page section.
     */
    public function widgets(): BelongsToMany
    {
        return $this->belongsToMany(Widget::class, 'page_widgets')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderBy('page_widgets.order_index');
    }
}
