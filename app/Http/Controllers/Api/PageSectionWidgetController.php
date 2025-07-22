<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use Illuminate\Http\Request;

class PageSectionWidgetController extends Controller
{
    /**
     * List all widgets in a section.
     */
    public function index(PageSection $section)
    {
        $widgets = $section->pageSectionWidgets()->with('widget')->orderBy('position')->get();
        return response()->json(['widgets' => $widgets]);
    }

    /**
     * Add a widget to a section.
     */
    public function store(Request $request, PageSection $section)
    {
        $validated = $request->validate([
            'widget_id' => 'required|exists:widgets,id',
            'column_position' => 'nullable|string',
            'settings' => 'nullable|array',
            'content_query' => 'nullable|array',
            'content_query.content_type_id' => 'nullable|integer|exists:content_types,id',
            'content_query.content_item_ids' => 'nullable|array',
            'content_query.content_item_ids.*' => 'integer|exists:content_items,id',
            'content_query.limit' => 'nullable|integer|min:1|max:100',
            'content_query.order_by' => 'nullable|string',
            'content_query.order_direction' => 'nullable|string|in:asc,desc',
            'css_classes' => 'nullable|string',
            'padding' => 'nullable|array',
            'margin' => 'nullable|array',
            'min_height' => 'nullable|integer|min:0',
            'max_height' => 'nullable|integer|min:0',
            'position' => 'nullable|integer|min:1',
        ]);
        
        // Calculate position if not provided
        if (!isset($validated['position'])) {
            $maxPosition = $section->pageSectionWidgets()->max('position') ?? 0;
            $validated['position'] = $maxPosition + 1;
        }
        
        // Add the section ID
        $validated['page_section_id'] = $section->id;
        
        $widget = PageSectionWidget::create($validated);
        
        // Load the widget relationship for response
        $widget->load('widget');
        
        return response()->json([
            'success' => true, 
            'widget' => $widget,
            'message' => 'Widget added to section successfully.'
        ]);
    }

    /**
     * Update a widget in a section.
     */
    public function update(Request $request, PageSectionWidget $widget)
    {
        $validated = $request->validate([
            'column_position' => 'nullable|string',
            'css_classes' => 'nullable|string',
            'padding' => 'nullable|array',
            'margin' => 'nullable|array',
            'min_height' => 'nullable|integer|min:0',
            'max_height' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
            'content_query' => 'nullable|array',
        ]);
        $widget->update($validated);
        return response()->json(['success' => true, 'widget' => $widget]);
    }

    /**
     * Delete a widget from a section.
     */
    public function destroy(PageSectionWidget $widget)
    {
        $widget->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Reorder widgets within a section.
     */
    public function reorder(Request $request, PageSection $section)
    {
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'required|integer|exists:page_section_widgets,id',
        ]);
        foreach ($validated['positions'] as $position => $widgetId) {
            PageSectionWidget::where('id', $widgetId)
                ->where('page_section_id', $section->id)
                ->update(['position' => $position]);
        }
        return response()->json(['success' => true]);
    }
} 