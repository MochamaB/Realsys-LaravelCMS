<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index()
    {
        $menus = Menu::withCount('items')->get();
        return view('admin.menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create()
    {
        $locations = ['header', 'footer', 'sidebar', 'main'];
        return view('admin.menus.create', compact('locations'));
    }

    /**
     * Store a newly created menu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        Menu::create($validated);

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu created successfully.');
    }

    /**
     * Show the menu and its items.
     */
    public function show(Menu $menu)
    {
        $menu->load(['rootItems.children' => function($query) {
            $query->orderBy('position');
        }]);
        
        return view('admin.menus.show', compact('menu'));
    }

    /**
     * Show the form for editing the menu.
     */
    public function edit(Menu $menu)
    {
        $locations = ['header', 'footer', 'sidebar', 'main'];
        return view('admin.menus.edit', compact('menu', 'locations'));
    }

    /**
     * Update the menu.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        $menu->update($validated);

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the menu and its items.
     */
    public function destroy(Menu $menu)
    {
        $menu->items()->delete();
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }
    
    /**
     * Display the menu items management view.
     */
    public function items(Menu $menu)
    {
        // Load the menu with its root items and nested children
        $menu->load(['rootItems.children' => function($query) {
            $query->orderBy('position');
        }]);
        
        // Get all menu items for parent selection (flat list)
        $allMenuItems = $menu->items()->orderBy('position')->get();
        
        // Get all pages for page selection
        $pages = Page::orderBy('title')->get();
        
        return view('admin.menus.items', compact('menu', 'allMenuItems', 'pages'));
    }
}