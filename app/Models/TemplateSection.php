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
        'description',
        'settings',
        'type',
        'width',
        'is_required',
        'max_widgets',
        'order_index',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'order_index' => 'integer',
        'max_widgets' => 'integer',
        'settings' => 'array'
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
            self::TYPE_HEADER => 'Header',
            self::TYPE_FOOTER => 'Footer',
            self::TYPE_SIDEBAR => 'Sidebar',
            self::TYPE_CONTENT => 'Content',
            self::TYPE_HERO => 'Hero',
            self::TYPE_BANNER => 'Banner',
            self::TYPE_CUSTOM => 'Custom',
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
