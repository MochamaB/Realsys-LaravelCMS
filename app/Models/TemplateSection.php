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
    const TYPE_HEADER = 'header';
    const TYPE_FOOTER = 'footer';
    const TYPE_SIDEBAR = 'sidebar';
    const TYPE_CONTENT = 'content';
    const TYPE_HERO = 'hero';
    const TYPE_BANNER = 'banner';
    const TYPE_CUSTOM = 'custom';

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
            'full-width' => 'Full Width',
            'multi-column' => 'Multi-Column',
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
            '8-4' => 'Two Columns (8-4)',
            '4-8' => 'Two Columns (4-8)',
            '3-6-3' => 'Three Columns (3-6-3)',
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
        return $this->type === $type;
    }
    
    /**
     * Get the default width for this section type
     * 
     * @return string
     */
    public function getDefaultWidth(): string
    {
        switch ($this->type) {
            case self::TYPE_HEADER:
            case self::TYPE_FOOTER:
            case self::TYPE_HERO:
            case self::TYPE_BANNER:
                return 'col-12'; // Full width
                
            case self::TYPE_SIDEBAR:
                return 'col-md-4'; // Smaller width for sidebar
                
            case self::TYPE_CONTENT:
                return 'col-md-8'; // Larger width for main content
                
            default:
                return 'col-md-6'; // Default medium width
        }
    }
}
