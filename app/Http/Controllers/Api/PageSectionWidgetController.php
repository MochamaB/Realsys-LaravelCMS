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
            'css_classes' => 'nullable|string',
            'padding' => 'nullable|array',
            'margin' => 'nullable|array',
            'min_height' => 'nullable|integer|min:0',
            'max_height' => 'nullable|integer|min:0',
        ]);
        $maxPosition = $section->pageSectionWidgets()->max('position') ?? 0;
        $widget = $section->pageSectionWidgets()->create(array_merge($validated, [
            'position' => $maxPosition + 1,
        ]));
        return response()->json(['success' => true, 'widget' => $widget]);
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