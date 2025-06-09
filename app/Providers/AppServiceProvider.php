<?php

namespace App\Providers;

use App\Providers\BladeServiceProvider;
use App\Providers\TemplateServiceProvider;
use App\Providers\WidgetServiceProvider;
use App\Services\MenuService;
use App\View\Components\ThemeNavigation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the Template Service Provider
        $this->app->register(TemplateServiceProvider::class);
        
        // Register the Widget Service Provider
        $this->app->register(WidgetServiceProvider::class);
        
        // Register the Blade Service Provider for template sections
        $this->app->register(BladeServiceProvider::class);
        
        // Register the Menu Service as a singleton
        $this->app->singleton(MenuService::class, function ($app) {
            return new MenuService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Register theme navigation component
        Blade::component('theme-navigation', ThemeNavigation::class);
        
        // Custom Blade directives for template sections
        Blade::directive('renderSection', function ($expression) {
            return "<?php echo app('App\\Services\\TemplateRenderer')->renderSection($expression, get_defined_vars()); ?>";
        });
        
        Blade::directive('renderAllSections', function () {
            return "<?php echo app('App\\Services\\TemplateRenderer')->renderAllSections(get_defined_vars()); ?>";
        });
        
        Blade::directive('sectionExists', function ($expression) {
            return "<?php if(app('App\\Services\\TemplateRenderer')->sectionExists($expression, get_defined_vars())): ?>";
        });
        
        Blade::directive('endsectionExists', function () {
            return "<?php endif; ?>";
        });
        
        // Improved activeRoute directive to handle multiple routes and wildcards
        Blade::directive('activeRoute', function ($routes) {
            return "<?php 
            \$routesArray = explode(',', $routes);
            \$isActive = false;
            
            // Current route name
            \$currentRoute = request()->route()->getName();
            
            foreach(\$routesArray as \$route) {
                // Clean up the route
                \$route = trim(\$route);
                
                // Handle wildcards
                if (str_ends_with(\$route, '*')) {
                    \$baseRoute = rtrim(\$route, '*');
                    if (str_starts_with(\$currentRoute, \$baseRoute)) {
                        \$isActive = true;
                        break;
                    }
                } else if (\$route === \$currentRoute) {
                    \$isActive = true;
                    break;
                }
            }
            
            echo \$isActive ? 'active' : '';
            ?>";
        });

        Blade::directive('activeRouteShow', function ($routes) {
            return "<?php 
            \$routesArray = explode(',', $routes);
            \$isActive = false;
            
            // Current route name
            \$currentRoute = request()->route()->getName();
            
            foreach(\$routesArray as \$route) {
                // Clean up the route
                \$route = trim(\$route);
                
                // Handle wildcards
                if (str_ends_with(\$route, '*')) {
                    \$baseRoute = rtrim(\$route, '*');
                    if (str_starts_with(\$currentRoute, \$baseRoute)) {
                        \$isActive = true;
                        break;
                    }
                } else if (\$route === \$currentRoute) {
                    \$isActive = true;
                    break;
                }
            }
            
            echo \$isActive ? 'show' : '';
            ?>";
        });
         //
    }
}
