<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetDisplaySetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'layout',
        'view_mode',
        'pagination_type',
        'items_per_page',
        'empty_text'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'items_per_page' => 'integer'
    ];

    /**
     * Get the widgets using these display settings.
     */
    public function widgets()
    {
        return $this->hasMany(Widget::class, 'display_settings_id');
    }

    /**
     * Get the view file path for the current layout and view mode.
     *
     * @return string
     */
    public function getViewPath()
    {
        $basePath = 'widgets.layouts';
        
        if ($this->layout && $this->view_mode) {
            return "{$basePath}.{$this->layout}.{$this->view_mode}";
        }
        
        if ($this->layout) {
            return "{$basePath}.{$this->layout}.default";
        }
        
        return "{$basePath}.default.default";
    }

    /**
     * Get pagination settings as an array.
     *
     * @return array
     */
    public function getPaginationSettings()
    {
        return [
            'type' => $this->pagination_type ?? 'none',
            'items_per_page' => $this->items_per_page ?? 10
        ];
    }

    /**
     * Check if pagination is enabled.
     *
     * @return bool
     */
    public function hasPagination()
    {
        return $this->pagination_type && $this->pagination_type !== 'none';
    }

    /**
     * Get the text to display when no content is found.
     *
     * @return string
     */
    public function getEmptyText()
    {
        return $this->empty_text ?? 'No content found.';
    }
}
