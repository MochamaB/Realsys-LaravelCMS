<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\Widget;
use App\Models\ContentType;
use App\Services\WidgetDiscoveryService;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Display a listing of the widgets.
     */
    public function index(Theme $theme = null)
    {
        // If no theme is provided, use the active theme
        if (!$theme) {
            $theme = Theme::where('is_active', true)->first();
        }
        
        // Get widgets for the theme
        $widgets = $theme->widgets()->orderBy('name')->paginate(10);
        
        // Get all themes for the filter dropdown
        $themes = Theme::all();
        
        return view('admin.widgets.index', compact('theme', 'widgets', 'themes'));
    }

    /**
     * Display the specified widget.
     */
    public function show(Theme $theme, Widget $widget)
    {
        // Load related data
        $widget->load(['fieldDefinitions', 'contentTypes']);
        $availableContentTypes = ContentType::whereNotIn('id', $widget->contentTypes->pluck('id'))->get();

        return view('admin.widgets.show', compact('theme', 'widget', 'availableContentTypes'));
    }

    /**
     * Show the preview for a widget.
     */
    public function preview(Widget $widget)
    {
        return view('admin.widgets.preview', compact('widget'));
    }
    
    /**
     * Toggle the widget active status.
     */
    public function toggle(Widget $widget, Request $request)
    {
        $status = $request->boolean('status', false);
        $widget->update(['is_active' => $status]);
        
        return response()->json([
            'success' => true,
            'message' => "Widget '{$widget->name}' has been " . ($status ? 'activated' : 'deactivated'),
            'status' => $status
        ]);
    }

    /**
     * Scan a theme for widgets and register them in the system
     */
    public function scanThemeWidgets(Theme $theme, WidgetDiscoveryService $widgetDiscovery)
    {
        $result = $widgetDiscovery->discoverAndRegisterWidgets($theme);
        
        return redirect()->route('admin.themes.widgets.index', $theme)
            ->with('success', "Widget scan completed! {$result['new']} new widgets discovered, {$result['updated']} widgets updated.");
    }
}