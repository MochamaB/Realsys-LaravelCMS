<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\ContentTypeField;
use App\Models\ContentTypeFieldOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContentTypeFieldController extends Controller
{
    /**
     * Display a listing of the content type fields.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function index(ContentType $contentType)
    {
        $fields = $contentType->fields()->orderBy('position')->get();
        
        return view('admin.content_type_fields.index', compact('contentType', 'fields'));
    }

    /**
     * Show the form for creating a new content type field.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function create(ContentType $contentType)
    {
        // Get field types from config and format them for the form
        $fieldTypes = [];
        foreach (config('field_types') as $type => $info) {
            $fieldTypes[$type] = $info['name'] ?? ucfirst($type);
        }
        
        // Get maximum position
        $maxPosition = $contentType->fields()->max('position') ?? 0;
        
        return view('admin.content_type_fields.create', compact('contentType', 'fieldTypes', 'maxPosition'));
    }

    /**
     * Store a newly created content type field in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, ContentType $contentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'field_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
            'settings' => 'nullable|json',
            'position' => 'nullable|integer',
            'options' => 'nullable|array',
            'options.*.value' => 'nullable|string',
            'options.*.label' => 'nullable|string',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        // Make sure field slug is unique for this content type
        $existingField = $contentType->fields()->where('slug', $validated['slug'])->first();
        if ($existingField) {
            $validated['slug'] = $validated['slug'] . '_' . time();
        }
        
        // Create the field
        $field = $contentType->fields()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'field_type' => $validated['field_type'],
            'description' => $validated['description'] ?? null,
            'is_required' => $validated['is_required'] ?? false,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'position' => $validated['position'] ?? 0,
            'settings' => $validated['settings'] ?? '{}',
        ]);
        
        // Set user information if available
        if (Schema::hasColumn('content_type_fields', 'created_by')) {
            $field->created_by = Auth::id();
            $field->save();
        }
        
        if (Schema::hasColumn('content_type_fields', 'updated_by')) {
            $field->updated_by = Auth::id();
            $field->save();
        }
        
        if (in_array($field->field_type, $this->getFieldTypesWithOptions()) && $request->has('options')) {
            $this->processFieldOptions($field, $request->input('options'));
        }
        
        return redirect()->route('admin.content-types.show', $contentType)
            ->with('success', 'Content type field created successfully.');
    }

    private function getFieldTypesWithOptions()
    {
        return collect(config('field_types'))
            ->filter(fn($type) => $type['has_options'])
            ->keys()
            ->toArray();
    }

    /**
     * Show the form for editing the specified content type field.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function edit(ContentType $contentType, ContentTypeField $field)
    {
        // Get field types from config and format them for the form
        $fieldTypes = [];
        foreach (config('field_types') as $type => $info) {
            $fieldTypes[$type] = $info['name'] ?? ucfirst($type);
        }
        
        if (in_array($field->field_type, $this->getFieldTypesWithOptions())) {
            $field->load('options');
        }
        
        return view('admin.content_type_fields.edit', compact('contentType', 'field', 'fieldTypes'));
    }

    /**
     * Update the specified content type field in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContentType $contentType, ContentTypeField $field)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
            'settings' => 'nullable|json',
            'position' => 'nullable|integer',
            'options' => 'nullable|array',
            'options.*.value' => 'nullable|string',
            'options.*.label' => 'nullable|string',
        ]);
        
        // Make sure field slug is unique for this content type (excluding current field)
        $existingField = $contentType->fields()
            ->where('slug', $validated['slug'])
            ->where('id', '!=', $field->id)
            ->first();
            
        if ($existingField) {
            $validated['slug'] = $validated['slug'] . '_' . time();
        }
        
        // Update the field
        $field->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'field_type' => $validated['field_type'],
            'description' => $validated['description'] ?? null,
            'is_required' => $validated['is_required'] ?? false,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'position' => $validated['position'] ?? $field->position,
            'settings' => $validated['settings'] ?? '{}',
        ]);
        
        // Set user information if available
        if (Schema::hasColumn('content_type_fields', 'updated_by')) {
            $field->updated_by = Auth::id();
            $field->save();
        }
        
        if (in_array($field->field_type, $this->getFieldTypesWithOptions()) && !empty($validated['options'])) {
            $field->options()->delete();
            $this->processFieldOptions($field, $validated['options']);
        }
        
        return redirect()->route('admin.content-types.show', $contentType)
            ->with('success', 'Content type field updated successfully.');
    }

    /**
     * Remove the specified content type field from storage.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentType $contentType, ContentTypeField $field)
    {
        // Check if the field has any values
        if ($field->fieldValues()->count() > 0) {
            return redirect()->route('admin.content-types.show', $contentType)
                ->with('error', 'Cannot delete field that has values assigned to content items.');
        }
        
        // Delete options first
        $field->options()->delete();
        
        // Delete field
        $field->delete();
        
        return redirect()->route('admin.content-types.show', $contentType)
            ->with('success', 'Content type field deleted successfully.');
    }
    
    
    /**
     * Process field options for select/multiselect fields.
     *
     * @param  \App\Models\ContentTypeField  $field
     * @param  array  $options
     * @return void
     */
    private function processFieldOptions(ContentTypeField $field, array $options)
    {
        $position = 0;
        
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }
            
            ContentTypeFieldOption::create([
                'field_id' => $field->id,
                'value' => $option['value'],
                'label' => $option['label'] ?? $option['value'],
                'position' => $position++,
            ]);
        }
    }
    
    /**
     * Reorder content type fields via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, ContentType $contentType)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:content_type_fields,id',
            'fields.*.position' => 'required|integer|min:1',
        ]);

        $fields = $request->input('fields');
        
        try {
            // Update each field position
            foreach ($fields as $field) {
                ContentTypeField::where('id', $field['id'])
                    ->where('content_type_id', $contentType->id)
                    ->update(['position' => $field['position']]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Field order updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field order.'
            ], 500);
        }
    }
}
