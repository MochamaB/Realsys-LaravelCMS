<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'theme_id',
        'name',
        'slug',
        'file_path',
        'description',
        'thumbnail_path',
        'settings',
        'is_active',
        'is_default'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the theme that owns the template.
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Get the pages that use this template.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get the sections for this template.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class);
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
     * Set this template as the default for its theme
     * 
     * @param bool $saveImmediately Whether to save the model immediately
     * @return $this
     */
    public function setAsDefault(bool $saveImmediately = true)
    {
        // First, remove default flag from all other templates for this theme
        if ($this->theme_id) {
            static::where('theme_id', $this->theme_id)
                  ->where('id', '!=', $this->id)
                  ->update(['is_default' => false]);
        }
        
        // Set this template as default
        $this->is_default = true;
        
        if ($saveImmediately) {
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Get the path to the template file
     * 
     * @return string|null
     */
    public function getTemplatePath()
    {
        if (!$this->file_path) {
            return null;
        }
        
        // Check if the file_path is already absolute
        if (file_exists($this->file_path)) {
            return $this->file_path;
        }
        
        // If not, try to find it relative to the theme's template directory
        if ($this->theme) {
            $path = resource_path('themes/' . $this->theme->slug . '/templates/' . $this->file_path);
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
}
