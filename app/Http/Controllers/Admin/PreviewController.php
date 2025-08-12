<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\ContentItem;
use App\Models\Widget;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    /**
     * Show the unified content item preview page
     *
     * @param ContentType $contentType
     * @param ContentItem $contentItem
     * @return \Illuminate\View\View
     */
    public function contentItem(ContentType $contentType, ContentItem $contentItem)
    {
        // Load fields and their values
        $contentItem->load([
            'contentType',
            'fieldValues.field',
        ]);
        
        return view('admin.content_items.preview_new', compact('contentType', 'contentItem'));
    }

    /**
     * Show the widget preview page
     *
     * @param Widget $widget
     * @return \Illuminate\View\View
     */
    public function widget(Widget $widget)
    {
        $widget->load(['contentTypes']);
        
        return view('admin.widgets.tabs.preview', compact('widget'));
    }
}
