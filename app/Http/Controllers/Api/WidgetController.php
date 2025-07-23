<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * List all available widgets (optionally filter by theme).
     */
    public function index(Request $request)
    {
        $query = Widget::query()->whereNull('deleted_at');
        if ($request->has('theme_id')) {
            $query->where('theme_id', $request->input('theme_id'));
        }
        $widgets = $query->orderBy('name')->get();
        return response()->json(['widgets' => $widgets]);
    }
    public function contentTypes($widgetId)
    {
        $widget = \App\Models\Widget::findOrFail($widgetId);
    // Get associated content types (already joined via pivot)
    $contentTypes = $widget->contentTypes()->get();
    return response()->json(['content_types' => $contentTypes]);
    }

    /**
     * Render a widget with its actual content data for the page designer
     */
    public function renderWidget(Request $request, $widgetId)
    {
        try {
            $widget = \App\Models\Widget::findOrFail($widgetId);
            $widgetService = app(\App\Services\WidgetService::class);
            
            // Get widget data with content
            $widgetData = $widgetService->prepareWidgetData($widget);
            
            // Get the theme
            $theme = $widget->theme;
            if (!$theme) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget has no associated theme'
                ], 400);
            }
            
            // Ensure theme namespace is registered
            \View::addNamespace('theme', resource_path('themes/' . $theme->slug));
            
            // Try to render the widget view
            $viewPath = $widgetData['view_path'];
            
            if (!\View::exists($viewPath)) {
                // Fallback to default widget view
                $viewPath = 'theme::widgets.default';
                if (!\View::exists($viewPath)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Widget view not found: ' . $viewPath
                    ], 404);
                }
            }
            
            // Render the widget
            $html = view($viewPath, [
                'widget' => $widgetData,
                'fields' => $widgetData['fields'] ?? [],
                'content' => $widgetData['content'] ?? [],
                'settings' => [] // Widget settings can be added here
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'widget_data' => $widgetData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering widget: ' . $e->getMessage(), [
                'widget_id' => $widgetId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to render widget: ' . $e->getMessage()
            ], 500);
        }
    }
}
