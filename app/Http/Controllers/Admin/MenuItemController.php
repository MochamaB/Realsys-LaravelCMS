<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Show the form for creating a new menu item.
     */
    public function create(Menu $menu)
    {
        $pages = Page::all();
        $menuItems = MenuItem::where('menu_id', $menu->id)->get();
        $parentItems = $menuItems->whereNull('parent_id');
        
        return view('admin.menu-items.create', compact('menu', 'pages', 'parentItems'));
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'link_type' => 'required|in:url,page,section',
            'url' => 'required_if:link_type,url|nullable|string|max:255',
            'page_id' => 'required_if:link_type,page|nullable|exists:pages,id',
            'section_id' => 'required_if:link_type,section|nullable|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'target' => 'nullable|in:_self,_blank',
            'position' => 'nullable|integer',
            'visibility_conditions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['menu_id'] = $menu->id;
        
        if (!isset($validated['position'])) {
            $validated['position'] = MenuItem::where('menu_id', $menu->id)
                ->where('parent_id', $validated['parent_id'])
                ->max('position') + 1;
        }

        MenuItem::create($validated);

        return redirect()->route('admin.menus.show', $menu)
            ->with('success', 'Menu item created successfully.');
    }

    /**
     * Show the form for editing the menu item.
     */
    public function edit(Menu $menu, MenuItem $item)
    {
        $pages = Page::all();
        $menuItems = MenuItem::where('menu_id', $menu->id)
            ->where('id', '!=', $item->id)
            ->get();
        $parentItems = $menuItems->whereNull('parent_id');
        
        return view('admin.menu-items.edit', compact('menu', 'item', 'pages', 'parentItems'));
    }

    /**
     * Update the menu item.
     */
    public function update(Request $request, Menu $menu, MenuItem $item)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'link_type' => 'required|in:url,page,section',
            'url' => 'required_if:link_type,url|nullable|string|max:255',
            'page_id' => 'required_if:link_type,page|nullable|exists:pages,id',
            'section_id' => 'required_if:link_type,section|nullable|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'target' => 'nullable|in:_self,_blank',
            'position' => 'nullable|integer',
            'visibility_conditions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()->route('admin.menus.show', $menu)
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the menu item.
     */
    public function destroy(Menu $menu, MenuItem $item)
    {
        // Delete all child items first
        $item->children()->delete();
        $item->delete();

        return redirect()->route('admin.menus.show', $menu)
            ->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Update menu item positions.
     */
    public function updatePositions(Request $request, Menu $menu)
    {
        $items = $request->input('items', []);
        
        if (!empty($items)) {
            // Process nested menu structure
            $this->updateItemsOrder($items);
            
            return response()->json([
                'success' => true,
                'message' => 'Menu item positions updated successfully.'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No menu items to update.'
        ], 400);
    }
    
    /**
     * Recursively update menu item positions.
     *
     * @param array $items
     * @param int|null $parentId
     * @param int $position
     */
    private function updateItemsOrder($items, $parentId = null, $position = 0)
    {
        foreach ($items as $item) {
            // Update current item
            MenuItem::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'position' => $position
            ]);
            
            // Process children recursively if they exist
            if (isset($item['children']) && !empty($item['children'])) {
                $this->updateItemsOrder($item['children'], $item['id'], 0);
            }
            
            $position++;
        }
    }
}