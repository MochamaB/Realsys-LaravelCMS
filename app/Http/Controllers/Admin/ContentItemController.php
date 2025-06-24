<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentFieldValue;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\ContentTypeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContentItemController extends Controller
{
    /**
     * Display a listing of all content items across all types.
     *
     * @return \Illuminate\Http\Response
     */
    public function allItems()
    {
        // Get all content types and their items
        $contentTypes = ContentType::where('is_active', true)->get();
        
        // Use the existing index view instead of a separate all_index view
        return view('admin.content_items.index', compact('contentTypes'));
    }

    /**
     * Display a listing of the content items for a specific type.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function index(ContentType $contentType)
    {
        // Get items for specific content type
        $contentItems = ContentItem::where('content_type_id', $contentType->id)
            ->latest()
            ->paginate(15);
            
        return view('admin.content_items.index', compact('contentItems', 'contentType'));
    }

    /**
     * Show the form for creating a new content item.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function create(ContentType $contentType)
    {
        // Get fields for this content type
        $fields = $contentType->fields()->orderBy('position')->get();
        
        return view('admin.content_items.create', compact('contentType', 'fields'));
    }

    /**
     * Store a newly created content item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, ContentType $contentType)
    {
        // Basic validation for content item
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:content_items,slug',
            'status' => 'required|string|in:draft,published,archived',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        // Set content type ID
        $validated['content_type_id'] = $contentType->id;
        
        // Set user information if available
        if (Schema::hasColumn('content_items', 'created_by')) {
            $validated['created_by'] = Auth::id();
        }
        
        if (Schema::hasColumn('content_items', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Create content item
        $contentItem = ContentItem::create($validated);
        
        // Process field values
        $this->processFieldValues($contentItem, $contentType, $request);
        
        return redirect()->route('admin.content-items.edit', [$contentType, $contentItem])
            ->with('success', 'Content item created successfully.');
    }

    /**
     * Display the specified content item.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentItem  $contentItem
     * @return \Illuminate\Http\Response
     */
    public function show(ContentType $contentType, ContentItem $contentItem)
    {
        // Load fields and their values
        $contentItem->load([
            'contentType',
            'fieldValues.field',
        ]);
        
        return view('admin.content_items.show', compact('contentType', 'contentItem'));
    }

    /**
     * Show the form for editing the specified content item.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentItem  $contentItem
     * @return \Illuminate\Http\Response
     */
    public function edit(ContentType $contentType, ContentItem $contentItem)
    {
        // Get fields for this content type
        $fields = $contentType->fields()->orderBy('position')->get();
        
        // Load field values
        $contentItem->load('fieldValues');
        
        // Organize field values by field ID for easier access
        $fieldValuesMap = [];
        foreach ($contentItem->fieldValues as $fieldValue) {
            $fieldValuesMap[$fieldValue->field_id] = $fieldValue;
        }
        
        return view('admin.content_items.edit', compact('contentType', 'contentItem', 'fields', 'fieldValuesMap'));
    }

    /**
     * Update the specified content item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentItem  $contentItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContentType $contentType, ContentItem $contentItem)
    {
        // Basic validation for content item
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:content_items,slug,' . $contentItem->id,
            'status' => 'required|string|in:draft,published,archived',
        ]);
        
        // Set user information if available
        if (Schema::hasColumn('content_items', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Update content item
        $contentItem->update($validated);
        
        // Process field values
        $this->processFieldValues($contentItem, $contentType, $request);
        
        return redirect()->route('admin.content-items.edit', [$contentType, $contentItem])
            ->with('success', 'Content item updated successfully.');
    }

    /**
     * Remove the specified content item from storage.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentItem  $contentItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentType $contentType, ContentItem $contentItem)
    {
        // Delete field values first
        $contentItem->fieldValues()->delete();
        
        // Delete content item
        $contentItem->delete();
        
        return redirect()->route('admin.content-items.index', $contentType)
            ->with('success', 'Content item deleted successfully.');
    }
    
    /**
     * Preview the specified content item.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentItem  $contentItem
     * @return \Illuminate\Http\Response
     */
    public function preview(ContentType $contentType, ContentItem $contentItem)
    {
        // Load fields and their values
        $contentItem->load([
            'contentType',
            'fieldValues.field',
        ]);
        
        return view('admin.content_items.preview', compact('contentType', 'contentItem'));
    }
    
    /**
     * Process field values from request.
     *
     * @param  \App\Models\ContentItem  $contentItem
     * @param  \App\Models\ContentType  $contentType
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function processFieldValues(ContentItem $contentItem, ContentType $contentType, Request $request)
    {
        // Get all fields for this content type
        $fields = $contentType->fields;
        
        foreach ($fields as $field) {
            // Get field value from request
            $fieldValue = $request->input('field_' . $field->id);
            
            // Handle special field types
            if ($field->type == 'boolean') {
                $fieldValue = $request->has('field_' . $field->id) ? '1' : '0';
            } elseif ($field->type == 'json' && is_array($fieldValue)) {
                $fieldValue = json_encode($fieldValue);
            }
            
            // Find existing field value or create new one
            $contentFieldValue = ContentFieldValue::updateOrCreate(
                [
                    'content_item_id' => $contentItem->id,
                    'field_id' => $field->id,
                ],
                [
                    'value' => $fieldValue,
                ]
            );
            
            // Handle media uploads
            if (in_array($field->type, ['image', 'gallery', 'file']) && $request->hasFile('field_' . $field->id)) {
                if ($field->type == 'gallery') {
                    // Handle multiple files
                    foreach ($request->file('field_' . $field->id) as $file) {
                        $contentItem->addMedia($file)
                            ->withCustomProperties(['field_id' => $field->id])
                            ->toMediaCollection('field_' . $field->id);
                    }
                } else {
                    // Handle single file
                    $contentItem->addMedia($request->file('field_' . $field->id))
                        ->withCustomProperties(['field_id' => $field->id])
                        ->toMediaCollection('field_' . $field->id);
                }
            }
        }
    }
}
