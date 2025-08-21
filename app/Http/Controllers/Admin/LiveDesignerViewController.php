<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;

/**
 * LiveDesigner View Controller
 * 
 * This controller ONLY handles view rendering for live preview interface.
 * All business logic and API endpoints are handled by Api\LivePreviewController.
 * 
 * Purpose: Provide clean separation between view rendering and API logic.
 */
class LiveDesignerViewController extends Controller
{
    /**
     * Show live preview interface
     * 
     * Renders the live preview interface with iframe-based editing:
     * - Left Sidebar: Page structure with sections and widgets
     * - Center Canvas: Iframe preview using existing template renderer
     * - Right Sidebar: Widget editor forms with real-time updates
     * 
     * Uses existing widget system and template rendering for maximum compatibility.
     * 
     * @param Page $page The page to edit in the live preview
     * @return \Illuminate\View\View
     */
    public function show(Page $page)
    {
        // Load page with all necessary relationships for preview
        $page->load([
            'template.theme',
            'sections.templateSection',
            'sections.pageSectionWidgets.widget'
        ]);

        return view('admin.pages.live-designer.show', [
            'page' => $page,
            'apiBaseUrl' => '/admin/api/live-preview',
            'pageTitle' => 'Live Preview - ' . $page->title,
            'breadcrumbs' => [
                ['name' => 'Pages', 'url' => route('admin.pages.index')],
                ['name' => $page->title, 'url' => route('admin.pages.show', $page)],
                ['name' => 'Live Preview', 'url' => null]
            ]
        ]);
    }
}
