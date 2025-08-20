<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageBuilderViewController extends Controller
{
    /**
     * Show GridStack page builder interface
     * 
     * This controller ONLY handles view rendering for the GridStack page builder.
     * All business logic will be handled by Api\PageBuilderController (to be created).
     */
    public function show(Page $page)
    {
        return view('admin.pages.page-builder.show', [
            'page' => $page,
            'apiBaseUrl' => '/admin/api/page-builder'
        ]);
    }
}