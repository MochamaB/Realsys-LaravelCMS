<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentFieldValue;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\ContentTypeField as Field;
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
        
        return redirect()->route('admin.content-types.show', $contentType)
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
     * @param  \App\Models\ContentItem  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(ContentType $contentType, ContentItem $item)
    {
        // Get fields for this content type
        $fields = $contentType->fields()->orderBy('position')->get();
        
        // Load field values with their field definitions for efficient access in the view
        $item->load('fieldValues.field');
        
        // Using contentItem variable name in the view for consistency with partials
        $contentItem = $item;
        
        return view('admin.content_items.edit', compact('contentType', 'contentItem', 'fields'));
    }

    /**
     * Update the specified content item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentItem  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContentType $contentType, ContentItem $item)
    {
        // Basic validation for content item
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:content_items,slug,' . $item->id,
            'status' => 'required|string|in:draft,published,archived',
        ]);
        
        // Set user information if available
        if (Schema::hasColumn('content_items', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Update content item
        $item->update($validated);
        
        // Process field values
        $this->processFieldValues($item, $contentType, $request);
        
        return redirect()->route('admin.content-types.show', $contentType)
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
            if ($field->field_type == 'repeater') {
                $this->processRepeaterField($request, $contentItem, $field);
            } else {
                $this->processSingleField($request, $contentItem, $field);
            }
        }
    }

    /**
     * Process a single field value
     * 
     * @param Request $request
     * @param ContentItem $contentItem
     * @param Field $field
     * @return void
     */
    protected function processSingleField(Request $request, ContentItem $contentItem, $field)
    {
        // Get field value from request
        $fieldValue = $request->input('field_' . $field->id);
            
        // Handle special field types
        if ($field->field_type == 'boolean') {
            $fieldValue = $request->has('field_' . $field->id) ? '1' : '0';
        } elseif ($field->field_type == 'json' && is_array($fieldValue)) {
            $fieldValue = json_encode($fieldValue);
        }

        // Handle text, textarea, number, select, boolean, date, json
        if (in_array($field->field_type, ['text', 'textarea', 'number', 'select', 'boolean', 'date', 'json'])) {
            // Save to ContentFieldValue model
            ContentFieldValue::updateOrCreate(
                [
                    'content_item_id' => $contentItem->id,
                    'content_type_field_id' => $field->id,
                ],
                [
                    'value' => $fieldValue,
                ]
            );
        }
        // Handle file uploads (old approach)
        elseif ($field->field_type == 'image' && $request->hasFile('field_' . $field->id)) {
            // Remove existing files
            $contentItem->clearMediaCollection('field_' . $field->id);
            
            $lastMediaId = null;
            
            if (is_array($request->file('field_' . $field->id))) {
                // Handle multiple files
                foreach ($request->file('field_' . $field->id) as $file) {
                    $media = $contentItem->addMedia($file)
                        ->withCustomProperties(['field_id' => $field->id])
                        ->toMediaCollection('field_' . $field->id);
                    $lastMediaId = $media->id;
                }
            } else {
                // Handle single file
                $media = $contentItem->addMedia($request->file('field_' . $field->id))
                    ->withCustomProperties(['field_id' => $field->id])
                    ->toMediaCollection('field_' . $field->id);
                $lastMediaId = $media->id;
            }
            
            // Store the media ID in ContentFieldValue for consistent lookup
            if ($lastMediaId) {
                ContentFieldValue::updateOrCreate(
                    [
                        'content_item_id' => $contentItem->id,
                        'content_type_field_id' => $field->id,
                    ],
                    [
                        'value' => $lastMediaId,
                    ]
                );
            }
        }
        // Handle media picker selection (new approach)
        elseif ($field->field_type == 'image' && $request->filled('field_' . $field->id)) {
            // First clear any existing media in this collection
            $contentItem->clearMediaCollection('field_' . $field->id);
            
            // Get the selected media ID
            $mediaId = $request->input('field_' . $field->id);
            
            // Find the existing media
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
            
            if ($media) {
                // Create a new copy of the media attached to this content item
                $newMedia = $contentItem->addMediaFromUrl($media->getUrl())
                    ->usingName($media->name)
                    ->usingFileName($media->file_name)
                    ->withCustomProperties(array_merge($media->custom_properties, ['field_id' => $field->id]))
                    ->toMediaCollection('field_' . $field->id);
                    
                // Store the media ID in ContentFieldValue for reference
                ContentFieldValue::updateOrCreate(
                    [
                        'content_item_id' => $contentItem->id,
                        'content_type_field_id' => $field->id,
                    ],
                    [
                        'value' => $newMedia->id,
                    ]
                );
            }
        }
        // Handle media removal when input is empty
        elseif ($field->field_type == 'image' && !$request->filled('field_' . $field->id)) {
            $contentItem->clearMediaCollection('field_' . $field->id);
            
            // Clear the field value
            ContentFieldValue::updateOrCreate(
                [
                    'content_item_id' => $contentItem->id,
                    'content_type_field_id' => $field->id,
                ],
                [
                    'value' => null,
                ]
            );
        }
    }

    /**
     * Process a repeater field value
     * 
     * @param Request $request
     * @param ContentItem $contentItem
     * @param Field $field
     * @return void
     */
    protected function processRepeaterField(Request $request, ContentItem $contentItem, $field)
    {
        // Get field value from request
        $fieldValue = $request->input('field_' . $field->id);
        
        // Get field configuration
        $fieldConfig = json_decode($field->options, true) ?? [];
        $subfields = $fieldConfig['subfields'] ?? [];
        
        // Create a unique collection prefix for this repeater field
        $mediaPrefix = 'field_' . $field->id . '_repeater_';
        
        // For repeater fields, normalize and encode the array of items
        if (is_array($fieldValue)) {
            // Process each repeater item
            foreach ($fieldValue as $index => $itemData) {
                if (is_array($itemData)) {
                    // Process each subfield
                    foreach ($itemData as $subFieldKey => $subFieldValue) {
                        // Find the subfield type
                        $subFieldType = null;
                        foreach ($subfields as $subfield) {
                            if ($subfield['name'] == $subFieldKey) {
                                $subFieldType = $subfield['type'];
                                break;
                            }
                        }
                        
                        // Handle image fields in repeater fields
                        if ($subFieldType === 'image') {
                            // Create a unique collection name for this repeater item's media
                            $collectionName = $mediaPrefix . $index . '_' . $subFieldKey;
                            
                            // Clear existing media for this specific repeater item field
                            $contentItem->clearMediaCollection($collectionName);
                            
                            // Handle media picker selection (media ID provided)
                            if (!empty($subFieldValue) && is_numeric($subFieldValue)) {
                                $mediaId = $subFieldValue;
                                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                                
                                if ($media) {
                                    // Create a new copy of the media attached to this content item
                                    $newMedia = $contentItem->addMediaFromUrl($media->getUrl())
                                        ->usingName($media->name)
                                        ->usingFileName($media->file_name)
                                        ->withCustomProperties(array_merge($media->custom_properties, [
                                            'field_id' => $field->id,
                                            'repeater_index' => $index,
                                            'subfield_name' => $subFieldKey
                                        ]))
                                        ->toMediaCollection($collectionName);
                                        
                                    // Store the media ID for reference in the field value
                                    $fieldValue[$index][$subFieldKey] = $newMedia->id;
                                }
                            }
                            // Handle direct file upload (UploadedFile object provided)
                            elseif ($subFieldValue instanceof \Illuminate\Http\UploadedFile) {
                                $media = $contentItem->addMedia($subFieldValue)
                                    ->withCustomProperties([
                                        'field_id' => $field->id,
                                        'repeater_index' => $index,
                                        'subfield_name' => $subFieldKey
                                    ])
                                    ->toMediaCollection($collectionName);
                                    
                                // Store the media ID for reference in the field value
                                $fieldValue[$index][$subFieldKey] = $media->id;
                            }
                            // Handle empty value (remove media)
                            else {
                                // If no media selected, remove from field value
                                unset($fieldValue[$index][$subFieldKey]);
                            }
                        }
                    }
                    
                    // Remove empty values to save space
                    $fieldValue[$index] = array_filter($itemData, function($value) {
                        return $value !== null && $value !== '';
                    });
                }
            }
            
            // Re-index array to ensure sequential keys
            $fieldValue = array_values($fieldValue);
        } else {
            // If no data, use empty array
            $fieldValue = [];
        }
        
        // Convert to JSON for storage and save to ContentFieldValue
        ContentFieldValue::updateOrCreate(
            [
                'content_item_id' => $contentItem->id,
                'content_type_field_id' => $field->id,
            ],
            [
                'value' => json_encode($fieldValue),
            ]
        );
    }
}