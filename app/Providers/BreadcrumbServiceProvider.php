<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class BreadcrumbServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('admin.partials.breadcrumb', function ($view) {
            $breadcrumbs = $this->generateBreadcrumbs();
            $view->with('breadcrumbs', $breadcrumbs);
        });
    }

    protected function generateBreadcrumbs()
    {
        $route = Route::current();
        
        if (!$route) {
            return [['title' => 'Dashboard', 'url' => route('admin.dashboard')]];
        }
        
        $name = $route->getName();
        $parameters = $route->parameters();
        
        // Skip if not admin route
        if (!Str::startsWith($name, 'admin.')) {
            return [['title' => 'Dashboard', 'url' => route('admin.dashboard')]];
        }
        
        // Remove admin. prefix
        $name = Str::replaceFirst('admin.', '', $name);
        
        // Split route name into segments
        $segments = explode('.', $name);
        
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')]
        ];
        
        $currentSegments = [];
        $previousTitle = null;
        
        // Handle special route patterns like resource controllers
        if (count($segments) > 1 && $segments[count($segments) - 1] === 'index') {
            // Remove the 'index' segment as it's redundant in breadcrumbs
            array_pop($segments);
        }
        
        foreach ($segments as $i => $segment) {
            $currentSegments[] = $segment;
            
            // Skip the final segment if it's an action like create, edit, etc.
            if ($i === count($segments) - 1 && in_array($segment, ['create', 'edit', 'show', 'preview'])) {
                $title = ucfirst($segment);
                $breadcrumbs[] = ['title' => $title, 'url' => null];
                continue;
            }
            
            // Try to generate a route for the current segments
            $routeName = 'admin.' . implode('.', $currentSegments);
            
            // Convert segment to title
            $title = $this->segmentToTitle($segment);
            
            // Skip if title is the same as the previous segment to avoid duplication
            if ($title === $previousTitle) {
                continue;
            }
            
            $previousTitle = $title;
            
            try {
                // If the route exists and has parameters
                if (Route::has($routeName)) {
                    $url = route($routeName, array_intersect_key($parameters, array_flip(
                        $this->getRouteParameterNames($routeName)
                    )));
                    
                    $breadcrumbs[] = ['title' => $title, 'url' => $url];
                } else {
                    // Try to find a parent route
                    $parentRouteName = 'admin.' . implode('.', array_slice($currentSegments, 0, -1));
                    if (Route::has($parentRouteName) && $i === count($segments) - 1) {
                        $breadcrumbs[] = ['title' => $title, 'url' => null];
                    } else {
                        $breadcrumbs[] = ['title' => $title, 'url' => null];
                    }
                }
            } catch (\Exception $e) {
                $breadcrumbs[] = ['title' => $title, 'url' => null];
            }
        }
        
        return $breadcrumbs;
    }
    
    protected function segmentToTitle($segment)
    {
        // Handle special cases
        $specialCases = [
            'items' => 'Content Items',
            'fields' => 'Fields',
            'content-types' => 'Content Types',
            'content-items' => 'Content Items',
            'all' => 'All Items',
        ];
        
        if (isset($specialCases[$segment])) {
            return $specialCases[$segment];
        }
        
        // Remove ID from model binding segments
        if (is_numeric($segment)) {
            return null;
        }
        
        // Replace dashes with spaces and title case
        return ucwords(str_replace(['-', '_'], ' ', $segment));
    }
    
    protected function getRouteParameterNames($routeName)
    {
        $route = Route::getRoutes()->getByName($routeName);
        return $route ? $route->parameterNames() : [];
    }
}
