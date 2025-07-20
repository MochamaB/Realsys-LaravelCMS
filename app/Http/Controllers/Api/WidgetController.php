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
        $query = Widget::query()->whereNull('deleted_at')->where('is_active', true);
        if ($request->has('theme_id')) {
            $query->where('theme_id', $request->input('theme_id'));
        }
        $widgets = $query->orderBy('name')->get();
        return response()->json(['widgets' => $widgets]);
    }
}
