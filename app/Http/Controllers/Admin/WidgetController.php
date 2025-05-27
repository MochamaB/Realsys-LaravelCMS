<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\WidgetType;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WidgetController extends Controller
{
    /**
     * Display a listing of the widgets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $widgets = Widget::with(['widgetType.fields', 'pageSections.page'])->latest()->paginate(15);
        
        return view('admin.widgets.index', compact('widgets'));
    }

    /**
     * Show the form for creating a new widget.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $widgetTypes = WidgetType::all();
        $pageSections = PageSection::with('page')->get();
        
        return view('admin.widgets.create', compact('widgetTypes', 'pageSections'));
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
        ]);
        
        // Get the widget type
        $widgetType = WidgetType::findOrFail($validated['widget_type_id']);
        
        // Get the page section
        $pageSection = PageSection::findOrFail($validated['page_section_id']);
        
        // Set the order index to be the last in the section
        $lastWidget = $pageSection->widgets()->orderBy('order_index', 'desc')->first();
        $orderIndex = $lastWidget ? $lastWidget->order_index + 1 : 0;
        
        // Set the creator
        $validated['created_by'] = Auth::guard('admin')->id();
        $validated['updated_by'] = Auth::guard('admin')->id();
        $validated['order_index'] = $orderIndex;
        
        // Create the widget
        $widget = Widget::create($validated);
        
        // Create field values for each field in the widget type
        foreach ($widgetType->fields as $field) {
            $fieldKey = 'field_' . $field->id;
            $value = $request->input($fieldKey, '');
            
            // Handle file uploads
            if ($field->type === 'image' && $request->hasFile($fieldKey)) {
                $media = $widget->addMediaFromRequest($fieldKey)
                    ->toMediaCollection('widget_' . $field->key);
                $value = $media->id;
            } elseif ($field->type === 'file' && $request->hasFile($fieldKey)) {
                $media = $widget->addMediaFromRequest($fieldKey)
                    ->toMediaCollection('widget_' . $field->key);
                $value = $media->id;
            }
            
            // Create the field value
            $widget->fieldValues()->create([
                'field_id' => $field->id,
                'value' => $value,
            ]);
        }
        
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
        $widget->load(['widgetType.fields', 'fieldValues', 'pageSections.page']);
        
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
        $widget->load(['widgetType.fields', 'fieldValues', 'pageSections.page']);
        $widgetTypes = WidgetType::all();
        $pageSections = PageSection::with('page')->get();
        
        return view('admin.widgets.edit', compact('widget', 'widgetTypes', 'pageSections'));
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
        ]);
        
        // Check if widget type has changed
        $widgetTypeChanged = $widget->widget_type_id != $validated['widget_type_id'];
        
        // Check if page section has changed
        $pageSectionChanged = $widget->page_section_id != $validated['page_section_id'];
        
        // Set the updater
        $validated['updated_by'] = Auth::guard('admin')->id();
        
        // Update the widget
        $widget->update($validated);
        
        // If widget type has changed, delete existing field values and create new ones
        if ($widgetTypeChanged) {
            // Delete existing field values
            $widget->fieldValues()->delete();
            
            // Get the new widget type
            $widgetType = WidgetType::findOrFail($validated['widget_type_id']);
            
            // Create field values for each field in the new widget type
            foreach ($widgetType->fields as $field) {
                $fieldKey = 'field_' . $field->id;
                $value = $request->input($fieldKey, '');
                
                // Handle file uploads
                if ($field->type === 'image' && $request->hasFile($fieldKey)) {
                    $media = $widget->addMediaFromRequest($fieldKey)
                        ->toMediaCollection('widget_' . $field->key);
                    $value = $media->id;
                } elseif ($field->type === 'file' && $request->hasFile($fieldKey)) {
                    $media = $widget->addMediaFromRequest($fieldKey)
                        ->toMediaCollection('widget_' . $field->key);
                    $value = $media->id;
                }
                
                // Create the field value
                $widget->fieldValues()->create([
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        } else {
            // Update existing field values
            $widgetType = $widget->widgetType;
            
            foreach ($widgetType->fields as $field) {
                $fieldKey = 'field_' . $field->id;
                $fieldValue = $widget->fieldValues()->where('field_id', $field->id)->first();
                
                if ($fieldValue) {
                    $value = $request->input($fieldKey, $fieldValue->value);
                    
                    // Handle file uploads
                    if (($field->type === 'image' || $field->type === 'file') && $request->hasFile($fieldKey)) {
                        // Remove existing media
                        $widget->clearMediaCollection('widget_' . $field->key);
                        
                        // Add new media
                        $media = $widget->addMediaFromRequest($fieldKey)
                            ->toMediaCollection('widget_' . $field->key);
                        $value = $media->id;
                    }
                    
                    $fieldValue->update(['value' => $value]);
                } else {
                    $value = $request->input($fieldKey, '');
                    
                    // Handle file uploads
                    if ($field->type === 'image' && $request->hasFile($fieldKey)) {
                        $media = $widget->addMediaFromRequest($fieldKey)
                            ->toMediaCollection('widget_' . $field->key);
                        $value = $media->id;
                    } elseif ($field->type === 'file' && $request->hasFile($fieldKey)) {
                        $media = $widget->addMediaFromRequest($fieldKey)
                            ->toMediaCollection('widget_' . $field->key);
                        $value = $media->id;
                    }
                    
                    // Create the field value
                    $widget->fieldValues()->create([
                        'field_id' => $field->id,
                        'value' => $value,
                    ]);
                }
            }
        }
        
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
        // Delete field values
        $widget->fieldValues()->delete();
        
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
