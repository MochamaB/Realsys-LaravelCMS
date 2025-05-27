<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WidgetType;
use App\Models\WidgetTypeField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetTypeFieldController extends Controller
{
    /**
     * Display a listing of the widget type fields.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function index(WidgetType $widgetType)
    {
        $fields = $widgetType->fields()->orderBy('order_index', 'asc')->get();
        
        return view('admin.widget-types.fields.index', compact('widgetType', 'fields'));
    }

    /**
     * Show the form for creating a new widget type field.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function create(WidgetType $widgetType)
    {
        // Get available field types
        $fieldTypes = [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'wysiwyg' => 'Rich Text Editor',
            'select' => 'Select Dropdown',
            'multiselect' => 'Multi-Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons',
            'file' => 'File Upload',
            'image' => 'Image Upload',
            'date' => 'Date Picker',
            'time' => 'Time Picker',
            'datetime' => 'Date & Time Picker',
            'number' => 'Number',
            'color' => 'Color Picker',
            'url' => 'URL',
            'email' => 'Email',
            'tel' => 'Telephone'
        ];
        
        return view('admin.widget-types.fields.create', compact('widgetType', 'fieldTypes'));
    }

    /**
     * Store a newly created widget type field in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, WidgetType $widgetType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'string', 'max:50'],
            'is_required' => ['sometimes', 'boolean'],
            'is_repeatable' => ['sometimes', 'boolean'],
            'help_text' => ['nullable', 'string'],
            'default_value' => ['nullable', 'string'],
            'validation_rules' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
        ]);
        
        // Get the next order index
        $maxOrderIndex = $widgetType->fields()->max('order_index') ?? 0;
        
        // Create the field
        $field = new WidgetTypeField([
            'name' => $validated['name'],
            'label' => $validated['label'],
            'field_type' => $validated['field_type'],
            'is_required' => $request->has('is_required'),
            'is_repeatable' => $request->has('is_repeatable'),
            'help_text' => $validated['help_text'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'order_index' => $maxOrderIndex + 1
        ]);
        
        $widgetType->fields()->save($field);
        
        // Handle options for select, multiselect, checkbox, radio fields
        if (in_array($validated['field_type'], ['select', 'multiselect', 'checkbox', 'radio']) && isset($validated['options'])) {
            foreach ($validated['options'] as $index => $optionLabel) {
                $field->options()->create([
                    'label' => $optionLabel,
                    'value' => Str::slug($optionLabel),
                    'order_index' => $index
                ]);
            }
        }
        
        return redirect()->route('admin.widget-types.edit', $widgetType)
            ->with('success', 'Field added successfully!');
    }

    /**
     * Show the form for editing the specified widget type field.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function edit(WidgetType $widgetType, WidgetTypeField $field)
    {
        // Check if the field belongs to the widget type
        if ($field->widget_type_id !== $widgetType->id) {
            return redirect()->route('admin.widget-types.edit', $widgetType)
                ->with('error', 'Field does not belong to this widget type!');
        }
        
        // Get available field types
        $fieldTypes = [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'wysiwyg' => 'Rich Text Editor',
            'select' => 'Select Dropdown',
            'multiselect' => 'Multi-Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons',
            'file' => 'File Upload',
            'image' => 'Image Upload',
            'date' => 'Date Picker',
            'time' => 'Time Picker',
            'datetime' => 'Date & Time Picker',
            'number' => 'Number',
            'color' => 'Color Picker',
            'url' => 'URL',
            'email' => 'Email',
            'tel' => 'Telephone'
        ];
        
        // Load options for select/checkbox/radio fields
        $field->load('options');
        
        return view('admin.widget-types.fields.edit', compact('widgetType', 'field', 'fieldTypes'));
    }

    /**
     * Update the specified widget type field in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetType  $widgetType
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WidgetType $widgetType, WidgetTypeField $field)
    {
        // Check if the field belongs to the widget type
        if ($field->widget_type_id !== $widgetType->id) {
            return redirect()->route('admin.widget-types.edit', $widgetType)
                ->with('error', 'Field does not belong to this widget type!');
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'string', 'max:50'],
            'is_required' => ['sometimes', 'boolean'],
            'is_repeatable' => ['sometimes', 'boolean'],
            'help_text' => ['nullable', 'string'],
            'default_value' => ['nullable', 'string'],
            'validation_rules' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
        ]);
        
        // Update the field
        $field->update([
            'name' => $validated['name'],
            'label' => $validated['label'],
            'field_type' => $validated['field_type'],
            'is_required' => $request->has('is_required'),
            'is_repeatable' => $request->has('is_repeatable'),
            'help_text' => $validated['help_text'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
        ]);
        
        // Handle options for select, multiselect, checkbox, radio fields
        if (in_array($validated['field_type'], ['select', 'multiselect', 'checkbox', 'radio'])) {
            // Delete existing options
            $field->options()->delete();
            
            // Add new options
            if (isset($validated['options'])) {
                foreach ($validated['options'] as $index => $optionLabel) {
                    $field->options()->create([
                        'label' => $optionLabel,
                        'value' => Str::slug($optionLabel),
                        'order_index' => $index
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.widget-types.edit', $widgetType)
            ->with('success', 'Field updated successfully!');
    }

    /**
     * Remove the specified widget type field from storage.
     *
     * @param  \App\Models\WidgetType  $widgetType
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function destroy(WidgetType $widgetType, WidgetTypeField $field)
    {
        // Check if the field belongs to the widget type
        if ($field->widget_type_id !== $widgetType->id) {
            return redirect()->route('admin.widget-types.edit', $widgetType)
                ->with('error', 'Field does not belong to this widget type!');
        }
        
        // Delete the field options first
        $field->options()->delete();
        
        // Delete the field
        $field->delete();
        
        return redirect()->route('admin.widget-types.edit', $widgetType)
            ->with('success', 'Field deleted successfully!');
    }

    /**
     * Update the order of widget type fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetType  $widgetType
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, WidgetType $widgetType)
    {
        $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['required', 'integer', 'exists:widget_type_fields,id']
        ]);
        
        $fields = $request->input('fields');
        
        foreach ($fields as $index => $fieldId) {
            $field = WidgetTypeField::find($fieldId);
            
            // Make sure the field belongs to this widget type
            if ($field && $field->widget_type_id === $widgetType->id) {
                $field->update(['order_index' => $index]);
            }
        }
        
        return response()->json(['success' => true]);
    }
}