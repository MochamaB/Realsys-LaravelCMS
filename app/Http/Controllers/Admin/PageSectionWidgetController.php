<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use Illuminate\Http\Request;

class PageSectionWidgetController extends Controller
{
    /**
     * Display a listing of widgets for a page section.
     */
    public function index(Page $page, PageSection $section)
    {
        // Verify the section belongs to the page
        if ($section->page_id !== $page->id) {
            abort(404);
        }
        
        $widgets = $section->widgets()->with('widget')->orderBy('position')->get();
        $availableWidgets = Widget::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.pages.sections.widgets.index', compact('page', 'section', 'widgets', 'availableWidgets'));
    }

    /**
     * Store a newly created widget in the page section.
     */
    public function store(Request $request, Page $page, PageSection $section)
    {
        // Verify the section belongs to the page
        if ($section->page_id !== $page->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'widget_id' => 'required|exists:widgets,id',
            'column_position' => 'nullable|string',
        ]);
        
        // Get max position for proper ordering
        $maxPosition = $section->widgets()->max('position') ?? 0;
        
        $widget = $section->widgets()->create([
            'widget_id' => $validated['widget_id'],
            'position' => $maxPosition + 1,
            'column_position' => $validated['column_position'] ?? 'full',
        ]);
        
        return redirect()
            ->route('pages.sections.widgets.edit', [$page, $section, $widget])
            ->with('success', 'Widget added successfully. Configure it now.');
    }

    /**
     * Show the form for editing the specified widget.
     */
    public function edit(Page $page, PageSection $section, PageSectionWidget $widget)
    {
        // Verify the relationships
        if ($section->page_id !== $page->id || $widget->page_section_id !== $section->id) {
            abort(404);
        }
        
        $widget->load('widget.fieldDefinitions');
        
        return view('admin.pages.sections.widgets.edit', compact('page', 'section', 'widget'));
    }

    /**
     * Update the specified widget in storage.
     */
    public function update(Request $request, Page $page, PageSection $section, PageSectionWidget $widget)
    {
        // Verify the relationships
        if ($section->page_id !== $page->id || $widget->page_section_id !== $section->id) {
            abort(404);
        }
        
        // Basic widget positioning and style validation
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
        
        return redirect()
            ->route('pages.sections.widgets.index', [$page, $section])
            ->with('success', 'Widget updated successfully.');
    }

    /**
     * Remove the specified widget from the page section.
     */
    public function destroy(Page $page, PageSection $section, PageSectionWidget $widget)
    {
        // Verify the relationships
        if ($section->page_id !== $page->id || $widget->page_section_id !== $section->id) {
            abort(404);
        }
        
        $widget->delete();
        
        return redirect()
            ->route('pages.sections.widgets.index', [$page, $section])
            ->with('success', 'Widget removed successfully.');
    }

    /**
     * Update widget positions within a section.
     */
    public function updatePositions(Request $request, Page $page, PageSection $section)
    {
        // Verify the section belongs to the page
        if ($section->page_id !== $page->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'required|integer|exists:page_section_widgets,id',
        ]);
        
        foreach ($validated['positions'] as $position => $widgetId) {
            PageSectionWidget::where('id', $widgetId)
                ->where('page_section_id', $section->id)
                ->update(['position' => $position]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Widget positions updated successfully.'
        ]);
    }
}