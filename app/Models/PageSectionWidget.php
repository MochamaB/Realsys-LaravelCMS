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
        // GridStack Widget Positioning
        'grid_x',
        'grid_y',
        'grid_w',
        'grid_h',
        'grid_id',
        'column_position',
        // Widget configuration
        'settings',
        'content_query',
        // Styling fields
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
     * Get the GridStack position as an array.
     *
     * @return array
     */
    public function getGridPosition(): array
    {
        return [
            'x' => $this->grid_x,
            'y' => $this->grid_y,
            'w' => $this->grid_w,
            'h' => $this->grid_h
        ];
    }

    /**
     * Set the GridStack position.
     *
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @return void
     */
    public function setGridPosition(int $x, int $y, int $w, int $h): void
    {
        $this->update([
            'grid_x' => $x,
            'grid_y' => $y,
            'grid_w' => $w,
            'grid_h' => $h
        ]);
    }

    /**
     * Generate a unique GridStack ID for this widget.
     *
     * @return string
     */
    public function generateGridId(): string
    {
        $gridId = 'widget_' . $this->id;
        $this->update(['grid_id' => $gridId]);
        return $gridId;
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

    /**
     * Check if this widget can be moved.
     *
     * @return bool
     */
    public function canMove(): bool
    {
        $settings = $this->settings ?? [];
        return !($settings['locked'] ?? false);
    }

    /**
     * Check if this widget can be resized.
     *
     * @return bool
     */
    public function canResize(): bool
    {
        $settings = $this->settings ?? [];
        return !($settings['noResize'] ?? false);
    }

    /**
     * Get the resize handles for this widget.
     *
     * @return array
     */
    public function getResizeHandles(): array
    {
        $settings = $this->settings ?? [];
        return $settings['resizeHandles'] ?? ['se', 'sw', 'ne', 'nw'];
    }

    /**
     * Get the minimum width constraint.
     *
     * @return int|null
     */
    public function getMinWidth(): ?int
    {
        $settings = $this->settings ?? [];
        return $settings['minWidth'] ?? null;
    }

    /**
     * Get the maximum width constraint.
     *
     * @return int|null
     */
    public function getMaxWidth(): ?int
    {
        $settings = $this->settings ?? [];
        return $settings['maxWidth'] ?? null;
    }
}
