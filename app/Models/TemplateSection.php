<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSection extends Model
{
    use SoftDeletes;
    
    /**
     * Section type constants
     */
    const TYPE_FULL_WIDTH = 'full-width';
    const TYPE_MULTI_COLUMN = 'multi-column';
    const TYPE_SIDEBAR_LEFT = 'sidebar-left';
    const TYPE_SIDEBAR_RIGHT = 'sidebar-right';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'name',
        'slug',
        'position',
        'x',
        'y',
        'w',
        'h',
        'section_type',
        'column_layout',
        'description',
        'is_repeatable',
        'max_widgets'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_repeatable' => 'boolean',
        'position' => 'integer',
        'max_widgets' => 'integer'
    ];

    /**
     * Get the template that owns this section.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the page sections that use this template section.
     */
    public function pageSections(): HasMany
    {
        return $this->hasMany(PageSection::class);
    }
    
    /**
     * Get a setting value
     * 
     * @param string $key The setting key
     * @param mixed $default Default value if key is not found
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Set a setting value
     * 
     * @param string $key The setting key
     * @param mixed $value The setting value
     * @return $this
     */
    public function setSetting(string $key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }
    
    /**
     * Get all available section types
     * 
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_FULL_WIDTH => 'Full Width Section',
            self::TYPE_MULTI_COLUMN => 'Multi-Column Section',
            self::TYPE_SIDEBAR_LEFT => 'Sidebar Left Section',
            self::TYPE_SIDEBAR_RIGHT => 'Sidebar Right Section',
        ];
    }
    
    /**
     * Get all available column layouts
     * 
     * @return array
     */
    public static function getColumnLayouts(): array
    {
        return [
            '12' => 'Full Width (12)',
            '6-6' => 'Two Equal Columns (6-6)',
            '4-4-4' => 'Three Equal Columns (4-4-4)',
            '3-3-3-3' => 'Four Equal Columns (3-3-3-3)',
            '8-4' => 'Wide & Narrow (8-4)',
            '4-8' => 'Narrow & Wide (4-8)',
            '3-6-3' => 'Sidebar, Main, Sidebar (3-6-3)',
        ];
    } 
    /**
     * Check if this section is of a specific type
     * 
     * @param string $type The type to check against
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->section_type === $type;
    }
    
    /**
     * Get the default width for this section type
     * 
     * @return string
     */
    public function getDefaultWidth(): string
    {
        switch ($this->section_type) {
            case self::TYPE_FULL_WIDTH:
                return 'col-12'; // Full width
                
            case self::TYPE_SIDEBAR_LEFT:
            case self::TYPE_SIDEBAR_RIGHT:
                return 'col-md-4'; // Smaller width for sidebar
                
            case self::TYPE_MULTI_COLUMN:
                // For multi-column, the width depends on the column layout
                $layout = $this->column_layout ?? '12';
                if ($layout === '12') {
                    return 'col-12';
                } else {
                    // Return a reasonable default
                    return 'col-md-6';
                }
                
            default:
                return 'col-md-6'; // Default medium width
        }
    }
}
