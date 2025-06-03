<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
// WidgetType model no longer used in new architecture
use App\Models\PageSection;
use App\Models\WidgetContentQuery;
use App\Models\WidgetDisplaySetting;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WidgetController extends Controller
{
    /**
     * Display a listing of the widgets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get the active theme
        $activeTheme = Theme::where('is_active', true)->first();
        
        $query = Widget::with([
            'theme', 
            'pageSections.page', 
            'pageSections.templateSection',
            'contentTypes',
            'fieldDefinitions'
        ]);
        
        // Always filter by the active theme
        if ($activeTheme) {
            $query->where('theme_id', $activeTheme->id);
        }
        
        $widgets = $query->latest()->paginate(15);
        
        return view('admin.widgets.index', compact('widgets', 'activeTheme'));
    }

    /**
     * Show the form for creating a new widget.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $widgetTypes = WidgetType::all();
        $pageSections = PageSection::with(['page', 'templateSection'])->get();
        $contentQueries = WidgetContentQuery::with('contentType')->get();
        $displaySettings = WidgetDisplaySetting::all();
        
        return view('admin.widgets.create', compact('widgetTypes', 'pageSections', 'contentQueries', 'displaySettings'));
    }

    /**
     * Store a newly created widget in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'widget_type_id' => 'required|exists:widget_types,id',
            'page_section_id' => 'required|exists:page_sections,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'content_query_id' => 'nullable|exists:widget_content_queries,id',
            'display_settings_id' => 'nullable|exists:widget_display_settings,id',
            'use_content_source' => 'nullable|boolean',
        ]);
        
        // Get the widget type
        $widgetType = WidgetType::findOrFail($validated['widget_type_id']);
        
        // Get the page section
        $pageSection = PageSection::findOrFail($validated['page_section_id']);
        
        // Set the order index to be the last in the section
        $lastWidget = $pageSection->widgets()->orderBy('order_index', 'desc')->first();
        $orderIndex = $lastWidget ? $lastWidget->order_index + 1 : 0;
        
        // If use_content_source is not checked, set content_query_id to null
        if (!$request->has('use_content_source')) {
            $validated['content_query_id'] = null;
        }
        
        // Set the creator
        $validated['created_by'] = Auth::guard('admin')->id();
        $validated['updated_by'] = Auth::guard('admin')->id();
        $validated['order_index'] = $orderIndex;
        
        // Create the widget
        $widget = Widget::create($validated);
        
        return redirect()->route('admin.widgets.edit', $widget)
            ->with('success', 'Widget created successfully.');
    }

    /**
     * Display the specified widget.
     *
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Http\Response
     */
    public function show(Widget $widget)
    {
        $widget->load(['widgetType', 'pageSections.page', 'contentQuery', 'displaySettings']);
        
        return view('admin.widgets.show', compact('widget'));
    }

    /**
     * Show the form for editing the specified widget.
     *
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Http\Response
     */
    public function edit(Widget $widget)
    {
        $widget->load(['widgetType', 'contentQuery', 'displaySettings']);
        $widgetTypes = WidgetType::all();
        $pageSections = PageSection::with(['page', 'templateSection'])->get();
        $contentQueries = WidgetContentQuery::with('contentType')->get();
        $displaySettings = WidgetDisplaySetting::all();
        
        return view('admin.widgets.edit', compact('widget', 'widgetTypes', 'pageSections', 'contentQueries', 'displaySettings'));
    }

    /**
     * Update the specified widget in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Widget $widget)
    {
        $validated = $request->validate([
            'widget_type_id' => 'required|exists:widget_types,id',
            'page_section_id' => 'required|exists:page_sections,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'content_query_id' => 'nullable|exists:widget_content_queries,id',
            'display_settings_id' => 'nullable|exists:widget_display_settings,id',
            'use_content_source' => 'nullable|boolean',
        ]);
        
        // If use_content_source is not checked, set content_query_id to null
        if (!$request->has('use_content_source')) {
            $validated['content_query_id'] = null;
        }
        
        // Check if page section has changed
        $pageSectionChanged = $widget->page_section_id != $validated['page_section_id'];
        
        // Set the updater
        $validated['updated_by'] = Auth::guard('admin')->id();
        
        // Update the widget
        $widget->update($validated);
        
        // If page section has changed, update the order index
        if ($pageSectionChanged) {
            $pageSection = PageSection::findOrFail($validated['page_section_id']);
            $lastWidget = $pageSection->widgets()->orderBy('order_index', 'desc')->first();
            $orderIndex = $lastWidget ? $lastWidget->order_index + 1 : 0;
            $widget->update(['order_index' => $orderIndex]);
        }
        
        return redirect()->route('admin.widgets.edit', $widget)
            ->with('success', 'Widget updated successfully.');
    }

    /**
     * Remove the specified widget from storage.
     *
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Http\Response
     */
    public function destroy(Widget $widget)
    {
        // Delete media
        $widget->clearMediaCollections();
        
        // Delete widget
        $widget->delete();
        
        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget deleted successfully.');
    }

    /**
     * Toggle the status of the specified widget.
     *
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Http\Response
     */
    public function toggle(Widget $widget)
    {
        $widget->status = $widget->status === 'published' ? 'draft' : 'published';
        $widget->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Widget status updated successfully.',
            'status' => $widget->status
        ]);
    }

    /**
     * Preview the specified widget.
     *
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Http\Response
     */
    public function preview(Widget $widget)
    {
        return view('admin.widgets.preview', [
            'widget' => $widget,
            'data' => $widget->getData(),
        ]);
    }
}
