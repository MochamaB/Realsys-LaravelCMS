<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\Widget;
use App\Models\ContentType;
use App\Services\WidgetDiscoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
        
        // Build a detailed message about discovery results
        $message = "Widget discovery completed for theme '{$theme->name}'.<br>";
        $message .= "<strong>Results:</strong><br>";
        $message .= "- {$result['new']} new widgets registered<br>";
        $message .= "- {$result['updated']} existing widgets updated<br>";
        
        if ($result['skipped'] > 0) {
            $message .= "- {$result['skipped']} widgets skipped<br>";
        }
        
        if ($result['errors'] > 0) {
            $message .= "- {$result['errors']} errors encountered<br>";
            // Add error details if available
            foreach ($result['details'] as $detail) {
                if ($detail['status'] === 'error') {
                    $message .= "&nbsp;&nbsp;Error in {$detail['widget']}: " . (isset($detail['message']) ? $detail['message'] : 'Unknown error') . "<br>";
                }
            }
            return redirect()->route('admin.widgets.index')
                ->with('warning', $message);
        }
        
        // If there were new widgets, highlight their names
        if ($result['new'] > 0) {
            $message .= "<br><strong>New widgets:</strong><br>";
            foreach ($result['details'] as $detail) {
                if ($detail['status'] === 'new') {
                    $message .= "- {$detail['widget']}<br>";
                }
            }
        }
        
        return redirect()->route('admin.widgets.index')
            ->with('success', $message);
    }

    /**
     * Show the form to edit widget code files.
     *
     * @param Widget $widget
     * @return \Illuminate\View\View
     */
    public function editWidgetCode(Widget $widget)
    {
        // Get theme path
        $theme = $widget->theme;
        $widgetPath = resource_path("themes/{$theme->slug}/widgets/{$widget->slug}");
        
        // Read widget.json file
        $jsonPath = $widgetPath . '/widget.json';
        $viewPath = $widgetPath . '/view.blade.php';
        
        $jsonContent = File::exists($jsonPath) ? File::get($jsonPath) : '';
        $viewContent = File::exists($viewPath) ? File::get($viewPath) : '';
        
        return view('admin.widgets.edit_code', compact('widget', 'jsonContent', 'viewContent'));
    }

    /**
     * Update widget code files.
     *
     * @param Request $request
     * @param Widget $widget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateWidgetCode(Request $request, Widget $widget)
    {
        $request->validate([
            'json_content' => 'required',
            'view_content' => 'required'
        ]);
        
        // Validate JSON structure
        try {
            json_decode($request->json_content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return redirect()->back()->withErrors(['json_content' => 'Invalid JSON format: ' . $e->getMessage()])->withInput();
        }
        
        $theme = $widget->theme;
        $widgetPath = resource_path("themes/{$theme->slug}/widgets/{$widget->slug}");
        
        // Ensure directory exists
        if (!File::exists($widgetPath)) {
            File::makeDirectory($widgetPath, 0755, true);
        }
        
        // Write files
        File::put($widgetPath . '/widget.json', $request->json_content);
        File::put($widgetPath . '/view.blade.php', $request->view_content);
        
        // Run widget discovery after update to refresh database
        app(WidgetDiscoveryService::class)->scanThemeWidgets($theme);
        
        return redirect()->route('admin.widgets.show', $widget)
            ->with('success', 'Widget code updated successfully.');
    }
}