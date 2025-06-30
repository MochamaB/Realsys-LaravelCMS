<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str as LaravelStr;

class StringHelperServiceProvider extends ServiceProvider
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
        // Extend Laravel's Str class with formatBytes method
        LaravelStr::macro('formatBytes', function ($bytes, $decimals = 2) {
            if ($bytes === 0) {
                return '0 Bytes';
            }
            
            $k = 1024;
            $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            $factor = floor(log($bytes) / log($k));
            
            return sprintf("%.{$decimals}f", $bytes / pow($k, $factor)) . ' ' . $sizes[$factor];
        });
    }
}
