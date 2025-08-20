<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;

/**
 * LiveDesigner View Controller
 * 
 * This controller ONLY handles view rendering for the GrapesJS live designer.
 * All business logic and API endpoints are handled by Api\LiveDesignerController.
 * 
 * Purpose: Provide a clean separation between view rendering and API logic
 * for the GrapesJS-based live designer system.
 */
class LiveDesignerViewController extends Controller
{
    /**
     * Show GrapesJS live designer interface
     * 
     * Renders the main GrapesJS live designer interface with three-column layout:
     * - Left Sidebar: Component library with enhanced widgets
     * - Center Canvas: GrapesJS editor with theme integration  
     * - Right Sidebar: Properties panel and settings
     * 
     * @param Page $page The page to edit in the live designer
     * @return \Illuminate\View\View
     */
    public function show(Page $page)
    {
        // Load page with necessary relationships for the designer
        $page->load([
            'template.theme',
            'template.sections',
            'sections.widgets'
        ]);

        return view('admin.pages.live-designer.show', [
            'page' => $page,
            'apiBaseUrl' => '/admin/api/live-designer',
            'pageTitle' => 'Live Designer - ' . $page->title,
            'breadcrumbs' => [
                ['name' => 'Pages', 'url' => route('admin.pages.index')],
                ['name' => $page->title, 'url' => route('admin.pages.show', $page)],
                ['name' => 'Live Designer', 'url' => null]
            ]
        ]);
    }
}
