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
        'position',
        // GridStack Section Positioning
        'grid_x',
        'grid_y',
        'grid_w',
        'grid_h',
        'grid_id',
        'grid_config',
        'allows_widgets',
        'widget_types',
        // Styling fields
        'column_span_override',
        'column_offset_override',
        'css_classes',
        'background_color',
        'padding',
        'margin',
        'locked_position',
        'resize_handles'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'grid_config' => 'json',
        'widget_types' => 'json',
        'padding' => 'json',
        'margin' => 'json',
        'resize_handles' => 'json',
        'allows_widgets' => 'boolean',
        'locked_position' => 'boolean'
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
            ->withPivot('settings', 'position', 'content_query')
            ->withTimestamps()
            ->orderBy('page_section_widgets.position', 'asc');
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
     * Generate a unique GridStack ID for this section.
     *
     * @return string
     */
    public function generateGridId(): string
    {
        $gridId = 'section_' . time() . '_' . uniqid() . '_' . $this->id;
        $this->update(['grid_id' => $gridId]);
        return $gridId;
    }

    /**
     * Generate a unique GridStack ID for a new section.
     *
     * @param int $templateSectionId
     * @return string
     */
    public static function generateUniqueGridId(int $templateSectionId): string
    {
        return 'section_' . time() . '_' . uniqid() . '_' . $templateSectionId;
    }

    /**
     * Check if this section can accept widgets.
     *
     * @return bool
     */
    public function canAcceptWidget(): bool
    {
        return $this->allows_widgets;
    }

    /**
     * Check if this section can be resized.
     *
     * @return bool
     */
    public function canResize(): bool
    {
        return !$this->locked_position;
    }

    /**
     * Get the default GridStack configuration.
     *
     * @return array
     */
    public function getDefaultGridConfig(): array
    {
        return [
            'columns' => 12,
            'cellHeight' => 80,
            'margin' => '10px',
            'float' => false,
            'resizable' => [
                'handles' => $this->resize_handles ?? ['se', 'sw', 'ne', 'nw']
            ]
        ];
    }

    /**
     * Get the allowed widget types for this section.
     *
     * @return array
     */
    public function getAllowedWidgetTypes(): array
    {
        return $this->widget_types ?? ['text', 'image', 'layout', 'form'];
    }
}
