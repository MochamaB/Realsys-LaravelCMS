<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Define helper functions for Blade views
        Blade::directive('sectionColor', function ($expression) {
            return "<?php echo getSectionColorClass($expression); ?>";
        });
    }
}
