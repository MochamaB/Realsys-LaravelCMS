<?php

namespace App\Providers;

use App\Models\Theme;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the theme manager singleton
        $this->app->singleton('theme.manager', function ($app) {
            return new \App\Services\ThemeManager();
        });
        
        // Register a helper function to get the active theme
        $this->app->bind('active.theme', function () {
            return Theme::where('is_active', true)->first();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if active theme assets are published
        $this->checkThemeAssets();
        
        // Register theme view paths
        $this->registerThemeViewPaths();
        
        // Register theme assets
        $this->registerThemeAssets();
        
        // Register Blade directives
        $this->registerBladeDirectives();
        
        // Register view composers
        $this->registerViewComposers();
    }
    
    /**
     * Check if active theme assets are published and publish them if needed
     */
    protected function checkThemeAssets(): void
    {
        // Get the active theme
        $activeTheme = $this->app->make('active.theme');
        
        if ($activeTheme) {
            // Get the theme manager
            $themeManager = $this->app->make('theme.manager');
            
            // Check if assets are published and publish if needed
            if (!$themeManager->areAssetsPublished($activeTheme)) {
                $themeManager->publishAssets($activeTheme);
            }
        }
    }
    
    /**
     * Register theme view paths
     */
    protected function registerThemeViewPaths(): void
    {
        // Get the active theme
        $activeTheme = $this->app->make('active.theme');
        
        if ($activeTheme) {
            // Add the active theme's view path to the view finder
            $themePath = resource_path('themes/' . $activeTheme->slug);
            
            if (File::exists($themePath)) {
                // Add theme templates directory to view paths
                View::addNamespace('theme', $themePath . '/templates');
                
                // Add theme sections directory to view paths
                View::addNamespace('theme.sections', $themePath . '/sections');
                
                // Add theme partials directory to view paths
                View::addNamespace('theme.partials', $themePath . '/partials');
            }
        }
    }
    
    /**
     * Register theme assets
     */
    protected function registerThemeAssets(): void
    {
        // Get the active theme
        $activeTheme = $this->app->make('active.theme');
        
        if ($activeTheme) {
            // Add a helper variable to access theme assets
            View::share('themeAssetPath', 'themes/' . $activeTheme->slug . '/assets');
        }
    }
    
    /**
     * Register Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        // Register @theme directive to get theme asset URLs
        Blade::directive('themeAsset', function ($expression) {
            return "<?php echo asset('themes/' . app('active.theme')->slug . '/assets/' . $expression); ?>";
        });
        
        // Register @themeInclude directive to include theme partials
        Blade::directive('themeInclude', function ($expression) {
            return "<?php echo \$__env->make('theme.partials.' . $expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
    }
    
    /**
     * Register view composers
     */
    protected function registerViewComposers(): void
    {
        // Share active theme with all views
        View::composer('*', function ($view) {
            $view->with('activeTheme', app('active.theme'));
        });
    }
}
