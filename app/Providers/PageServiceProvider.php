<?php

namespace App\Providers;

use App\Services\PageSectionManager;
use App\Services\PageService;
use Illuminate\Support\ServiceProvider;

class PageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PageSectionManager::class, function ($app) {
            return new PageSectionManager();
        });
        
        $this->app->singleton(PageService::class, function ($app) {
            return new PageService(
                $app->make(PageSectionManager::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
