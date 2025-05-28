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
                
                // Publish theme assets for newly registered theme
                $this->publishAssets($theme);
                
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
        
        // Publish theme assets for the newly activated theme
        $this->publishAssets($theme);
        
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
    
    /**
     * Publish theme assets to the public directory
     *
     * @param string|Theme $theme Theme slug or Theme model instance
     * @param bool $force Force republication even if assets exist
     * @return array Results of the publication process
     */
    public function publishAssets($theme, $force = false)
    {
        // Get theme slug if a Theme model is provided
        $themeSlug = $theme instanceof Theme ? $theme->slug : $theme;
        
        // Define source and destination paths
        $sourcePath = resource_path("themes/{$themeSlug}/assets");
        $destinationPath = public_path("themes/{$themeSlug}");
        
        // Initialize result array
        $result = [
            'success' => false,
            'message' => '',
            'files_copied' => 0,
            'errors' => [],
        ];
        
        // Check if source directory exists
        if (!File::isDirectory($sourcePath)) {
            $result['message'] = "Theme assets directory not found at {$sourcePath}";
            return $result;
        }
        
        // Create destination directory if it doesn't exist
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        
        try {
            // Get all files from source directory recursively
            $files = $this->getAllFiles($sourcePath);
            
            foreach ($files as $file) {
                // Get the relative path from the assets directory
                $relativePath = Str::after($file, $sourcePath . DIRECTORY_SEPARATOR);
                $targetFile = $destinationPath . DIRECTORY_SEPARATOR . $relativePath;
                $targetDir = dirname($targetFile);
                
                // Create target directory if it doesn't exist
                if (!File::isDirectory($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                
                // Check if we need to copy the file
                $shouldCopy = $force || !File::exists($targetFile) ||
                             File::lastModified($file) > File::lastModified($targetFile);
                
                if ($shouldCopy) {
                    // Copy the file
                    if (File::copy($file, $targetFile)) {
                        $result['files_copied']++;
                    } else {
                        $result['errors'][] = "Failed to copy: {$relativePath}";
                    }
                }
            }
            
            $result['success'] = true;
            $result['message'] = $result['files_copied'] > 0 ?
                "Successfully published {$result['files_copied']} theme assets." :
                "No files needed updating.";
            
        } catch (\Exception $e) {
            $result['message'] = "Error publishing theme assets: {$e->getMessage()}";
            $result['errors'][] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Publish assets for all themes or for the active theme
     *
     * @param bool $activeOnly Only publish assets for the active theme
     * @param bool $force Force republication even if assets exist
     * @return array Results of the publication process
     */
    public function publishAllAssets($activeOnly = false, $force = false)
    {
        $results = [];
        
        if ($activeOnly) {
            $activeTheme = $this->getActiveTheme();
            if ($activeTheme) {
                $results[$activeTheme->slug] = $this->publishAssets($activeTheme, $force);
            }
        } else {
            // Get all themes from the database
            $themes = Theme::all();
            
            foreach ($themes as $theme) {
                $results[$theme->slug] = $this->publishAssets($theme, $force);
            }
        }
        
        return $results;
    }
    
    /**
     * Clean up theme assets for a deleted theme
     *
     * @param string $themeSlug Theme slug
     * @return bool Success status
     */
    public function cleanupThemeAssets($themeSlug)
    {
        $themePath = public_path("themes/{$themeSlug}");
        
        if (File::isDirectory($themePath)) {
            try {
                File::deleteDirectory($themePath);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return true; // Already clean
    }
    
    /**
     * Check if theme assets are published
     *
     * @param string|Theme $theme Theme slug or Theme model instance
     * @return bool Whether assets are published
     */
    public function areAssetsPublished($theme)
    {
        // Get theme slug if a Theme model is provided
        $themeSlug = $theme instanceof Theme ? $theme->slug : $theme;
        
        // Define paths
        $sourcePath = resource_path("themes/{$themeSlug}/assets");
        $destinationPath = public_path("themes/{$themeSlug}");
        
        // Check if destination directory exists
        if (!File::isDirectory($destinationPath)) {
            return false;
        }
        
        // Check if source directory exists
        if (!File::isDirectory($sourcePath)) {
            return true; // No assets to publish
        }
        
        // Check for at least one key file (e.g., CSS file)
        $cssFiles = File::glob($sourcePath . '/css/*.css');
        if (!empty($cssFiles)) {
            $cssFile = basename($cssFiles[0]);
            return File::exists($destinationPath . '/css/' . $cssFile);
        }
        
        // If no CSS files, check for any files
        return !empty(File::files($destinationPath, true));
    }
    
    /**
     * Get all files from a directory recursively
     *
     * @param string $directory Directory path
     * @return array Array of file paths
     */
    protected function getAllFiles($directory)
    {
        $files = [];
        
        foreach (File::allFiles($directory) as $file) {
            // Skip directories, only include files
            if (!$file->isDir()) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
}
