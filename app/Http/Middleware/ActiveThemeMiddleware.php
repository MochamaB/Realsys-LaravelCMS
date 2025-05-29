<?php

namespace App\Http\Middleware;

use App\Services\ThemeManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveThemeMiddleware
{
    /**
     * The theme manager instance.
     *
     * @var \App\Services\ThemeManager
     */
    protected $themeManager;

    /**
     * Create a new middleware instance.
     *
     * @param  \App\Services\ThemeManager  $themeManager
     * @return void
     */
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the active theme
        $activeTheme = $this->themeManager->getActiveTheme();
        
        if (!$activeTheme) {
            // If no active theme is found, redirect to a "no active theme" error page
            if ($request->is('admin/*')) {
                // Let admin routes go through
                return $next($request);
            }
            
            return response()->view('front.errors.no-theme', [], 500);
        }
        
        // Register theme view paths
        $this->themeManager->registerThemeViewPaths($activeTheme);
        
        // Share the active theme with all views
        view()->share('theme', $activeTheme);
        
        return $next($request);
    }
}
