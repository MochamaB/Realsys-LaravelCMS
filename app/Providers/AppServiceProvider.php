<?php

namespace App\Providers;

use App\Providers\TemplateServiceProvider;
use App\Providers\WidgetServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
