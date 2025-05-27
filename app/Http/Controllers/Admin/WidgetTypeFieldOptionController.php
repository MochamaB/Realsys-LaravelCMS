<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WidgetTypeField;
use App\Models\WidgetTypeFieldOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WidgetTypeFieldOptionController extends Controller
{
    /**
     * Display a listing of the field options.
     *
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function index(WidgetTypeField $field)
    {
        $options = $field->options()->orderBy('order_index', 'asc')->get();
        return view('admin.widget-types.fields.options.index', compact('field', 'options'));
    }

    /**
     * Show the form for creating a new field option.
     *
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function create(WidgetTypeField $field)
    {
        return view('admin.widget-types.fields.options.create', compact('field'));
    }

    /**
     * Store a newly created field option in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, WidgetTypeField $field)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'label' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the last option's order index
        $lastOption = $field->options()->orderBy('order_index', 'desc')->first();
        $orderIndex = $lastOption ? $lastOption->order_index + 1 : 0;

        // Create the option
        $option = new WidgetTypeFieldOption([
            'value' => $request->value,
            'label' => $request->label,
            'order_index' => $orderIndex,
        ]);

        $field->options()->save($option);

        return redirect()->route('admin.widget-types.fields.options.index', $field)
            ->with('success', 'Field option created successfully.');
    }

    /**
     * Show the form for editing the specified field option.
     *
     * @param  \App\Models\WidgetTypeField  $field
     * @param  \App\Models\WidgetTypeFieldOption  $option
     * @return \Illuminate\Http\Response
     */
    public function edit(WidgetTypeField $field, WidgetTypeFieldOption $option)
    {
        return view('admin.widget-types.fields.options.edit', compact('field', 'option'));
    }

    /**
     * Update the specified field option in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetTypeField  $field
     * @param  \App\Models\WidgetTypeFieldOption  $option
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WidgetTypeField $field, WidgetTypeFieldOption $option)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'label' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $option->update([
            'value' => $request->value,
            'label' => $request->label,
        ]);

        return redirect()->route('admin.widget-types.fields.options.index', $field)
            ->with('success', 'Field option updated successfully.');
    }

    /**
     * Remove the specified field option from storage.
     *
     * @param  \App\Models\WidgetTypeField  $field
     * @param  \App\Models\WidgetTypeFieldOption  $option
     * @return \Illuminate\Http\Response
     */
    public function destroy(WidgetTypeField $field, WidgetTypeFieldOption $option)
    {
        $option->delete();

        return redirect()->route('admin.widget-types.fields.options.index', $field)
            ->with('success', 'Field option deleted successfully.');
    }

    /**
     * Reorder the field options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, WidgetTypeField $field)
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:widget_type_field_options,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Update order indexes
        foreach ($request->order as $index => $id) {
            WidgetTypeFieldOption::where('id', $id)->update(['order_index' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
