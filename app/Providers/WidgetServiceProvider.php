<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WidgetService;
use App\Services\ThemeManager;
use App\Services\WidgetContentAssociationService;
use App\Services\WidgetContentCompatibilityService;
use App\Services\WidgetContentFetchService;

class WidgetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WidgetService::class, function ($app) {
            return new WidgetService(
                $app->make(ThemeManager::class),
                $app->make(WidgetContentFetchService::class),
                $app->make(WidgetContentCompatibilityService::class),
                $app->make(WidgetContentAssociationService::class)
            );
        });

        // Register an alias for easier access
        $this->app->alias(WidgetService::class, 'WidgetService');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
