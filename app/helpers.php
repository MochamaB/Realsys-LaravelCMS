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

if (!function_exists('getSectionColorClass')) {
    /**
     * Get the color class for a section type
     * 
     * @param string $sectionType
     * @return string
     */
    function getSectionColorClass($sectionType) {
        return match ($sectionType) {
            'full-width' => 'primary',
            'multi-column' => 'info',
            'sidebar-left' => 'success',
            'sidebar-right' => 'warning',
            default => 'secondary',
        };
    }
}

if (!function_exists('getLoggedInAdmin')) {
    /**
     * Get the currently logged in admin user
     */
    function getLoggedInAdmin()
    {
        return auth()->guard('admin')->user();
    }
}

if (!function_exists('getLoggedInUser')) {
    /**
     * Get the currently logged in web user
     */
    function getLoggedInUser()
    {
        return auth()->guard('web')->user();
    }
}

if (!function_exists('isAdminLoggedIn')) {
    /**
     * Check if admin is logged in
     */
    function isAdminLoggedIn()
    {
        return auth()->guard('admin')->check();
    }
}

if (!function_exists('isUserLoggedIn')) {
    /**
     * Check if user is logged in
     */
    function isUserLoggedIn()
    {
        return auth()->guard('web')->check();
    }
}
