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
}
