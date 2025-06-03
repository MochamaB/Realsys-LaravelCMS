<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSectionWidget extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_section_id',
        'widget_id',
        'position',
        'column_position',
        'settings',
        'content_query',
        'css_classes',
        'padding',
        'margin',
        'min_height',
        'max_height'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'json',
        'content_query' => 'json',
        'padding' => 'json',
        'margin' => 'json',
    ];

    /**
     * Get the page section that owns this widget.
     */
    public function pageSection(): BelongsTo
    {
        return $this->belongsTo(PageSection::class);
    }

    /**
     * Get the widget for this page section widget.
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }
    
    /**
     * Get the display width based on settings.
     *
     * @return string
     */
    public function getWidthClass(): string
    {
        $settings = $this->settings ?? [];
        $width = $settings['width'] ?? 'full';
        
        $widthClasses = [
            'full' => 'col-span-12',
            'half' => 'col-span-6',
            'third' => 'col-span-4',
            'quarter' => 'col-span-3',
            'two-thirds' => 'col-span-8',
            'three-quarters' => 'col-span-9',
        ];
        
        return $widthClasses[$width] ?? 'col-span-12';
    }
    
    /**
     * Get the order position.
     *
     * @return int
     */
    public function getOrder(): int
    {
        $settings = $this->settings ?? [];
        return $settings['order'] ?? 0;
    }
}
