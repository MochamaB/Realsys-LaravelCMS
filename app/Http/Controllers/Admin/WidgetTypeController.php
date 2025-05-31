<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WidgetType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class WidgetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        $widgetTypes = WidgetType::withCount('widgets')
            ->latest()
            ->paginate(15);
            
        return view('admin.widget-types.index', compact('widgetTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        return view('admin.widget-types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:widget_types'],
            'description' => ['nullable', 'string'],
            'component_path' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        // Set default status if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }
        
        $widgetType = WidgetType::create($validated);
        
        return redirect()->route('admin.widget-types.edit', $widgetType)
            ->with('success', 'Widget type created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function show(WidgetType $widgetType)
    {        
        $widgetType->load(['widgets']);
        
        return view('admin.widget-types.show', compact('widgetType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function edit(WidgetType $widgetType)
    {
        
        return view('admin.widget-types.edit', compact('widgetType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WidgetType $widgetType)
    {        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('widget_types')->ignore($widgetType->id)],
            'description' => ['nullable', 'string'],
            'component_path' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        // Set status
        $validated['is_active'] = $request->has('is_active');
        
        $widgetType->update($validated);
        
        return redirect()->route('admin.widget-types.edit', $widgetType)
            ->with('success', 'Widget type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function destroy(WidgetType $widgetType)
    {        
        // Check if there are any widgets using this type
        if ($widgetType->widgets()->count() > 0) {
            return redirect()->route('admin.widget-types.index')
                ->with('error', 'Cannot delete widget type that is in use by widgets!');
        }
        
        // Delete the widget type
        $widgetType->delete();
        
        return redirect()->route('admin.widget-types.index')
            ->with('success', 'Widget type deleted successfully!');
    }
    
    /**
     * Toggle the status of the specified widget type.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function toggle(WidgetType $widgetType)
    {        
        $widgetType->is_active = !$widgetType->is_active;
        $widgetType->save();
        
        return redirect()->route('admin.widget-types.index')
            ->with('success', 'Widget type status updated successfully!');
    }
}