<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MenuService
{
    /**
     * Cache duration in seconds
     * 
     * @var int
     */
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Get a menu by its location
     * 
     * @param string $location
     * @param bool $useCache
     * @return Menu|null
     */
    public function getMenuByLocation(string $location, bool $useCache = true)
    {
        $cacheKey = "menu.location.{$location}";
        
        if ($useCache && Cache::has($cacheKey)) {
            Log::debug("Menu retrieved from cache", ['location' => $location]);
            return Cache::get($cacheKey);
        }
        
        $menu = Menu::getByLocationWithItems($location);
        
        if ($useCache && $menu) {
            Cache::put($cacheKey, $menu, now()->addSeconds($this->cacheDuration));
            Log::debug("Menu stored in cache", ['location' => $location, 'menu_id' => $menu->id]);
        }
        
        return $menu;
    }
    /**
     * Get all active menus with their items
     *
     * @param int|null $pageId
     * @param int|null $templateId
     * @param bool $useCache
     * @return \Illuminate\Support\Collection
     */
    public function getAllActiveMenus(?int $pageId = null, ?int $templateId = null, bool $useCache = true)
    {
        $cacheKey = "menus.all" . ($pageId ? ".page{$pageId}" : "") . ($templateId ? ".template{$templateId}" : "");
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Get all active menus with their items
        $menus = Menu::where('is_active', true)
            ->with(['rootItems' => function($query) {
                $query->where('is_active', true)->orderBy('position');
            }, 'rootItems.children' => function($query) {
                $query->where('is_active', true)->orderBy('position');
            }])
            ->get();
        
        // Process each menu
        $processedMenus = collect();
        foreach ($menus as $menu) {
            // Apply context filtering
            $menu = $this->filterMenuItemsByContext($menu, $pageId, $templateId);
            
            // Process active states
            $menu = $this->processActiveStates($menu);
            
            $processedMenus->put($menu->location, $menu);
        }
        
        if ($useCache) {
            Cache::put($cacheKey, $processedMenus, now()->addSeconds($this->cacheDuration));
        }
        
        return $processedMenus;
    }
    
    /**
     * Process menu items to mark active based on current URL
     * 
     * @param Menu|null $menu
     * @param string|null $currentUrl
     * @return Menu|null
     */
    public function processActiveStates(?Menu $menu, ?string $currentUrl = null)
    {
        if (!$menu) {
            return null;
        }
        
        if (!$currentUrl) {
            $currentUrl = url()->current();
        }
        
        foreach ($menu->rootItems as $item) {
            $item->is_current = ($item->full_url == $currentUrl);
            
            // Process children
            if ($item->children) {
                foreach ($item->children as $child) {
                    $child->is_current = ($child->full_url == $currentUrl);
                    
                    // If child is active, parent should also be marked active
                    if ($child->is_current) {
                        $item->has_active_child = true;
                    }
                }
            }
        }
        
        return $menu;
    }
    
    /**
     * Filter menu items based on context visibility conditions
     * 
     * @param Menu|null $menu
     * @param int|null $pageId
     * @param int|null $templateId
     * @return Menu|null
     */
    public function filterMenuItemsByContext(?Menu $menu, ?int $pageId = null, ?int $templateId = null)
    {
        if (!$menu) {
            return null;
        }
        
        $isAuthenticated = auth()->check();
        
        // Filter root items
        $menu->rootItems = $menu->rootItems->filter(function($item) use ($pageId, $templateId, $isAuthenticated) {
            return $item->isVisibleForContext($pageId, $templateId, $isAuthenticated);
        })->values();
        
        // Filter children of each root item
        foreach ($menu->rootItems as $item) {
            if ($item->children) {
                $item->children = $item->children->filter(function($child) use ($pageId, $templateId, $isAuthenticated) {
                    return $child->isVisibleForContext($pageId, $templateId, $isAuthenticated);
                })->values();
            }
        }
        
        return $menu;
    }
    
    /**
     * Prepare one-page navigation attributes
     * 
     * @param Menu|null $menu
     * @return Menu|null
     */
    public function prepareOnePageNavigation(?Menu $menu)
    {
        if (!$menu) {
            return null;
        }
        
        foreach ($menu->rootItems as $item) {
            if ($item->link_type === 'section') {
                $item->scrollTo = true;
                $item->dataAttributes = [
                    'data-scroll-to' => $item->section_id,
                    'data-offset' => '-100' // Can be adjusted as needed
                ];
            }
            
            // Process children
            if ($item->children) {
                foreach ($item->children as $child) {
                    if ($child->link_type === 'section') {
                        $child->scrollTo = true;
                        $child->dataAttributes = [
                            'data-scroll-to' => $child->section_id,
                            'data-offset' => '-100'
                        ];
                    }
                }
            }
        }
        
        return $menu;
    }
    
    /**
     * Get menu fully processed for display
     * 
     * @param string $location
     * @param int|null $pageId
     * @param int|null $templateId
     * @param bool $isOnePage
     * @param bool $useCache
     * @return Menu|null
     */
    public function getProcessedMenu(string $location, ?int $pageId = null, ?int $templateId = null, bool $isOnePage = false, bool $useCache = true)
    {
        $menu = $this->getMenuByLocation($location, $useCache);
        
        if (!$menu) {
            return null;
        }
        
        // Apply context filtering
        $menu = $this->filterMenuItemsByContext($menu, $pageId, $templateId);
        
        // Handle one-page navigation
        if ($isOnePage) {
            $menu = $this->prepareOnePageNavigation($menu);
        } else {
            $menu = $this->processActiveStates($menu);
        }
        
        return $menu;
    }
    
    /**
     * Clear menu cache
     * 
     * @param string|null $location
     * @return void
     */
    public function clearMenuCache(?string $location = null)
    {
        if ($location) {
            Cache::forget("menu.location.{$location}");
            Log::info("Menu cache cleared", ['location' => $location]);
        } else {
            // Common locations - extend as needed
            $locations = ['header', 'footer', 'main', 'sidebar'];
            
            foreach ($locations as $loc) {
                Cache::forget("menu.location.{$loc}");
            }
            
            Log::info("All menu caches cleared");
        }
    }
}
