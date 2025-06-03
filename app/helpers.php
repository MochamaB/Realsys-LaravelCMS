<?php

if (!function_exists('theme_asset')) {
    /**
     * Generate a URL for a theme asset
     *
     * @param string $path Path to the asset relative to the theme's public directory
     * @return string Full URL to the theme asset
     */
    function theme_asset($path)
    {
        // Get the active theme slug
        $theme = app('App\Services\ThemeManager')->getActiveTheme();
        $themeSlug = $theme ? $theme->slug : 'default';
        
        // Return the asset URL
        return asset('themes/' . $themeSlug . '/' . ltrim($path, '/'));
    }
}
