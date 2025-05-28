<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeManager
{
    /**
     * Get all available themes
     *
     * @return array
     */
    public function getAllThemes()
    {
        // First check the cache
        if (Cache::has('available_themes')) {
            return Cache::get('available_themes');
        }
        
        // Scan themes directory
        $themeDirs = File::directories(resource_path('themes'));
        $themes = [];
        
        foreach ($themeDirs as $themeDir) {
            $themeName = basename($themeDir);
            $themeConfigPath = $themeDir . '/theme.json';
            
            if (File::exists($themeConfigPath)) {
                $themeConfig = json_decode(File::get($themeConfigPath), true);
                
                if ($themeConfig) {
                    $themes[$themeName] = $themeConfig;
                }
            }
        }
        
        // Cache the results for 24 hours
        Cache::put('available_themes', $themes, now()->addHours(24));
        
        return $themes;
    }
    
    /**
     * Get a specific theme's configuration
     *
     * @param string $themeSlug
     * @return array|null
     */
    public function getThemeConfig($themeSlug)
    {
        $themes = $this->getAllThemes();
        return $themes[$themeSlug] ?? null;
    }
    
    /**
     * Scan and register themes that exist in the file system but not in the database
     *
     * @return array
     */
    public function scanAndRegisterThemes()
    {
        $registeredThemes = [];
        $themeConfigs = $this->getAllThemes();
        
        foreach ($themeConfigs as $slug => $config) {
            // Check if theme already exists in database
            $existingTheme = Theme::where('slug', $slug)->first();
            
            if (!$existingTheme) {
                // Check for screenshot
                $screenshotPath = $this->getThemeScreenshotPath($slug);
                
                // Create new theme record
                $theme = Theme::create([
                    'name' => $config['name'] ?? Str::title($slug),
                    'slug' => $slug,
                    'description' => $config['description'] ?? null,
                    'version' => $config['version'] ?? '1.0.0',
                    'author' => $config['author'] ?? null,
                    'screenshot_path' => $screenshotPath,
                    'is_active' => false,
                ]);
                
                $registeredThemes[] = $theme;
            }
        }
        
        return $registeredThemes;
    }
    
    /**
     * Find theme screenshot path
     *
     * @param string $themeSlug
     * @return string|null
     */
    public function getThemeScreenshotPath($themeSlug)
    {
        // Check for thumbnail.png in theme's assets/img directory
        $thumbnailPath = "themes/{$themeSlug}/assets/img/thumbnail.png";
        
        if (File::exists(public_path($thumbnailPath))) {
            return $thumbnailPath;
        }
        
        // Check for screenshot.jpg/png in theme's root directory
        $screenshotExtensions = ['jpg', 'png', 'jpeg'];
        
        foreach ($screenshotExtensions as $ext) {
            $path = resource_path("themes/{$themeSlug}/screenshot.{$ext}");
            
            if (File::exists($path)) {
                // Copy to public folder for display
                $publicPath = "uploads/themes/{$themeSlug}-screenshot.{$ext}";
                
                // Create directory if it doesn't exist
                if (!File::isDirectory(public_path('uploads/themes'))) {
                    File::makeDirectory(public_path('uploads/themes'), 0755, true);
                }
                
                File::copy($path, public_path($publicPath));
                
                return $publicPath;
            }
        }
        
        return null;
    }
    
    /**
     * Get the active theme
     *
     * @return Theme|null
     */
    public function getActiveTheme()
    {
        // Check session for preview theme
        $previewThemeId = session('preview_theme');
        
        if ($previewThemeId) {
            $previewTheme = Theme::find($previewThemeId);
            
            if ($previewTheme) {
                return $previewTheme;
            }
        }
        
        // Get the active theme from database
        return Theme::where('is_active', true)->first();
    }
    
    /**
     * Activate a theme
     *
     * @param Theme $theme
     * @return bool
     */
    public function activateTheme(Theme $theme)
    {
        // Deactivate all themes
        Theme::where('is_active', true)->update(['is_active' => false]);
        
        // Activate the selected theme
        $theme->is_active = true;
        $theme->save();
        
        // Clear theme cache
        Cache::forget('available_themes');
        
        return true;
    }
    
    /**
     * Get available templates for a theme
     *
     * @param string $themeSlug
     * @return array
     */
    public function getThemeTemplates($themeSlug)
    {
        $templatesPath = resource_path("themes/{$themeSlug}/templates");
        
        if (!File::isDirectory($templatesPath)) {
            return [];
        }
        
        $templates = [];
        $templateFiles = File::files($templatesPath);
        
        foreach ($templateFiles as $file) {
            if ($file->getExtension() === 'php' || $file->getExtension() === 'blade.php') {
                $templates[] = [
                    'name' => Str::title(str_replace(['-', '_', '.blade.php', '.php'], [' ', ' ', '', ''], $file->getFilename())),
                    'file' => $file->getFilename(),
                ];
            }
        }
        
        return $templates;
    }
}
