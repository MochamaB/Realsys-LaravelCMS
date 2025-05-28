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
        $fields = $contentType->fields()->orderBy('order_index')->get();
        
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
        $fieldTypes = [
            'text' => 'Text',
            'textarea' => 'Text Area',
            'rich_text' => 'Rich Text',
            'number' => 'Number',
            'date' => 'Date',
            'datetime' => 'Date and Time',
            'boolean' => 'Boolean',
            'select' => 'Select',
            'multiselect' => 'Multi-select',
            'image' => 'Image',
            'gallery' => 'Gallery',
            'file' => 'File',
            'url' => 'URL',
            'email' => 'Email',
            'phone' => 'Phone',
            'color' => 'Color',
            'json' => 'JSON',
            'relation' => 'Content Relation',
        ];
        
        // Get maximum order index
        $maxOrderIndex = $contentType->fields()->max('order_index') ?? 0;
        
        return view('admin.content_type_fields.create', compact('contentType', 'fieldTypes', 'maxOrderIndex'));
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
            'key' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
            'settings' => 'nullable|json',
            'order_index' => 'nullable|integer',
        ]);
        
        // Generate key if not provided
        if (empty($validated['key'])) {
            $validated['key'] = Str::slug($validated['name']);
        }
        
        // Make sure field key is unique for this content type
        $existingField = $contentType->fields()->where('key', $validated['key'])->first();
        if ($existingField) {
            $validated['key'] = $validated['key'] . '_' . time();
        }
        
        // Set content type ID
        $validated['content_type_id'] = $contentType->id;
        
        // Set user information if available
        if (Schema::hasColumn('content_type_fields', 'created_by')) {
            $validated['created_by'] = Auth::id();
        }
        
        if (Schema::hasColumn('content_type_fields', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Default values
        $validated['is_required'] = $validated['is_required'] ?? false;
        
        // Convert settings to JSON if it's not already
        if (!empty($validated['settings']) && is_array($validated['settings'])) {
            $validated['settings'] = json_encode($validated['settings']);
        }
        
        // Create the field
        $field = ContentTypeField::create($validated);
        
        // Process options for select and multiselect fields
        if (in_array($field->type, ['select', 'multiselect']) && $request->has('options')) {
            $this->processFieldOptions($field, $request->input('options'));
        }
        
        return redirect()->route('admin.content-types.fields.index', $contentType)
            ->with('success', 'Content type field created successfully.');
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
        $fieldTypes = [
            'text' => 'Text',
            'textarea' => 'Text Area',
            'rich_text' => 'Rich Text',
            'number' => 'Number',
            'date' => 'Date',
            'datetime' => 'Date and Time',
            'boolean' => 'Boolean',
            'select' => 'Select',
            'multiselect' => 'Multi-select',
            'image' => 'Image',
            'gallery' => 'Gallery',
            'file' => 'File',
            'url' => 'URL',
            'email' => 'Email',
            'phone' => 'Phone',
            'color' => 'Color',
            'json' => 'JSON',
            'relation' => 'Content Relation',
        ];
        
        // Load options for select/multiselect fields
        if (in_array($field->type, ['select', 'multiselect'])) {
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
            'key' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
            'settings' => 'nullable|json',
            'order_index' => 'nullable|integer',
        ]);
        
        // Make sure field key is unique for this content type (excluding current field)
        $existingField = $contentType->fields()
            ->where('key', $validated['key'])
            ->where('id', '!=', $field->id)
            ->first();
            
        if ($existingField) {
            $validated['key'] = $validated['key'] . '_' . time();
        }
        
        // Set user information if available
        if (Schema::hasColumn('content_type_fields', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Default values
        $validated['is_required'] = $validated['is_required'] ?? false;
        
        // Convert settings to JSON if it's not already
        if (!empty($validated['settings']) && is_array($validated['settings'])) {
            $validated['settings'] = json_encode($validated['settings']);
        }
        
        // Update the field
        $field->update($validated);
        
        // Process options for select and multiselect fields
        if (in_array($field->type, ['select', 'multiselect']) && $request->has('options')) {
            // Delete existing options
            $field->options()->delete();
            
            // Create new options
            $this->processFieldOptions($field, $request->input('options'));
        }
        
        return redirect()->route('admin.content-types.fields.edit', [$contentType, $field])
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
            return redirect()->route('admin.content-types.fields.index', $contentType)
                ->with('error', 'Cannot delete field that has values assigned to content items.');
        }
        
        // Delete options first
        $field->options()->delete();
        
        // Delete field
        $field->delete();
        
        return redirect()->route('admin.content-types.fields.index', $contentType)
            ->with('success', 'Content type field deleted successfully.');
    }
    
    /**
     * Reorder fields for a content type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, ContentType $contentType)
    {
        $validated = $request->validate([
            'field_ids' => 'required|array',
            'field_ids.*' => 'required|integer|exists:content_type_fields,id',
        ]);
        
        $fieldIds = $validated['field_ids'];
        
        // Update order indexes
        foreach ($fieldIds as $index => $fieldId) {
            ContentTypeField::where('id', $fieldId)
                ->where('content_type_id', $contentType->id)
                ->update(['order_index' => $index]);
        }
        
        return response()->json(['success' => true]);
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
        $orderIndex = 0;
        
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }
            
            ContentTypeFieldOption::create([
                'field_id' => $field->id,
                'value' => $option['value'],
                'label' => $option['label'] ?? $option['value'],
                'order_index' => $orderIndex++,
            ]);
        }
    }
}
