<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WidgetService;
use App\Services\WidgetSchemaService;
use App\Services\SectionSchemaService;
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

        // Register the Widget Schema Service
        $this->app->singleton(WidgetSchemaService::class, function ($app) {
            return new WidgetSchemaService(
                $app->make(ThemeManager::class)
            );
        });

        // Register the Section Schema Service
        $this->app->singleton(SectionSchemaService::class, function ($app) {
            return new SectionSchemaService(
                $app->make(ThemeManager::class),
                $app->make(WidgetSchemaService::class)
            );
        });

        // Register aliases for easier access
        $this->app->alias(WidgetService::class, 'WidgetService');
        $this->app->alias(WidgetSchemaService::class, 'WidgetSchemaService');
        $this->app->alias(SectionSchemaService::class, 'SectionSchemaService');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
