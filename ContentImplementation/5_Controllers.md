# Content-Driven CMS Architecture: Controllers

This document details the Laravel controllers required to implement the content-driven CMS architecture. Each controller is presented with its methods and key functionality.

## Admin Controllers

### ContentTypeController

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContentTypeController extends Controller
{
    /**
     * Display a listing of the content types.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contentTypes = ContentType::orderBy('name')->paginate(15);
        
        return view('admin.content_types.index', compact('contentTypes'));
    }

    /**
     * Show the form for creating a new content type.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.content_types.create');
    }

    /**
     * Store a newly created content type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:content_types,key',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Generate key if not provided
        if (empty($validated['key'])) {
            $validated['key'] = Str::slug($validated['name']);
        }
        
        // Set user information
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        
        // Create content type
        $contentType = ContentType::create($validated);
        
        return redirect()->route('admin.content-types.edit', $contentType)
            ->with('success', 'Content type created successfully.');
    }

    /**
     * Display the specified content type.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function show(ContentType $contentType)
    {
        $contentType->load(['fields' => function ($query) {
            $query->orderBy('order_index');
        }]);
        
        return view('admin.content_types.show', compact('contentType'));
    }

    /**
     * Show the form for editing the specified content type.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function edit(ContentType $contentType)
    {
        $contentType->load(['fields' => function ($query) {
            $query->orderBy('order_index');
        }]);
        
        return view('admin.content_types.edit', compact('contentType'));
    }

    /**
     * Update the specified content type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContentType $contentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:content_types,key,' . $contentType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Set user information
        $validated['updated_by'] = Auth::id();
        
        // Update content type
        $contentType->update($validated);
        
        return redirect()->route('admin.content-types.edit', $contentType)
            ->with('success', 'Content type updated successfully.');
    }

    /**
     * Remove the specified content type from storage.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentType $contentType)
    {
        // Check if the content type has any content items
        if ($contentType->hasItems()) {
            return redirect()->route('admin.content-types.index')
                ->with('error', 'Cannot delete content type that has content items.');
        }
        
        // Delete content type
        $contentType->delete();
        
        return redirect()->route('admin.content-types.index')
            ->with('success', 'Content type deleted successfully.');
    }
}
```

### ContentTypeFieldController

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\ContentTypeField;
use Illuminate\Http\Request;
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
            'reference' => 'Content Reference',
        ];
        
        return view('admin.content_type_fields.create', compact('contentType', 'fieldTypes'));
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
            'type' => 'required|string',
            'required' => 'boolean',
            'description' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
        ]);
        
        // Generate key if not provided
        if (empty($validated['key'])) {
            $validated['key'] = Str::slug($validated['name']);
        }
        
        // Make sure the key is unique for this content type
        $keyExists = $contentType->fields()->where('key', $validated['key'])->exists();
        if ($keyExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['key' => 'This key is already in use for this content type.']);
        }
        
        // Get the highest order index
        $maxOrderIndex = $contentType->fields()->max('order_index') ?? -1;
        $validated['order_index'] = $maxOrderIndex + 1;
        
        // Create the field
        $field = $contentType->fields()->create($validated);
        
        // Handle options for select/multiselect fields
        if (in_array($validated['type'], ['select', 'multiselect']) && $request->has('options')) {
            $this->saveFieldOptions($field, $request->input('options'));
        }
        
        return redirect()->route('admin.content-types.fields.index', $contentType)
            ->with('success', 'Field created successfully.');
    }

    /**
     * Display the specified content type field.
     *
     * @param  \App\Models\ContentType  $contentType
     * @param  \App\Models\ContentTypeField  $field
     * @return \Illuminate\Http\Response
     */
    public function show(ContentType $contentType, ContentTypeField $field)
    {
        $field->load('options');
        
        return view('admin.content_type_fields.show', compact('contentType', 'field'));
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
        $field->load('options');
        
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
            'reference' => 'Content Reference',
        ];
        
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
            'type' => 'required|string',
            'required' => 'boolean',
            'description' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
        ]);
        
        // Make sure the key is unique for this content type
        $keyExists = $contentType->fields()
            ->where('key', $validated['key'])
            ->where('id', '!=', $field->id)
            ->exists();
            
        if ($keyExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['key' => 'This key is already in use for this content type.']);
        }
        
        // Update the field
        $field->update($validated);
        
        // Handle options for select/multiselect fields
        if (in_array($validated['type'], ['select', 'multiselect']) && $request->has('options')) {
            // Delete existing options
            $field->options()->delete();
            
            // Save new options
            $this->saveFieldOptions($field, $request->input('options'));
        }
        
        return redirect()->route('admin.content-types.fields.index', $contentType)
            ->with('success', 'Field updated successfully.');
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
                ->with('error', 'Cannot delete field that has values.');
        }
        
        // Delete the field
        $field->delete();
        
        return redirect()->route('admin.content-types.fields.index', $contentType)
            ->with('success', 'Field deleted successfully.');
    }

    /**
     * Update the order of fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, ContentType $contentType)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'integer|exists:content_type_fields,id',
        ]);
        
        // Update the order of fields
        foreach ($validated['fields'] as $index => $fieldId) {
            ContentTypeField::where('id', $fieldId)
                ->where('content_type_id', $contentType->id)
                ->update(['order_index' => $index]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Save field options.
     *
     * @param  \App\Models\ContentTypeField  $field
     * @param  array  $options
     * @return void
     */
    protected function saveFieldOptions(ContentTypeField $field, array $options)
    {
        $orderIndex = 0;
        
        foreach ($options as $option) {
            if (empty($option['label']) || empty($option['value'])) {
                continue;
            }
            
            $field->options()->create([
                'label' => $option['label'],
                'value' => $option['value'],
                'order_index' => $orderIndex++,
            ]);
        }
    }
}
```

### ContentItemController

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Services\ContentRenderingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContentItemController extends Controller
{
    /**
     * The content rendering service.
     *
     * @var \App\Services\ContentRenderingService
     */
    protected $contentRenderingService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\ContentRenderingService  $contentRenderingService
     * @return void
     */
    public function __construct(ContentRenderingService $contentRenderingService)
    {
        $this->contentRenderingService = $contentRenderingService;
    }

    /**
     * Display a listing of the content items.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function index(ContentType $contentType = null)
    {
        if ($contentType) {
            $contentItems = ContentItem::where('content_type_id', $contentType->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
                
            return view('admin.content_items.index', compact('contentType', 'contentItems'));
        } else {
            $contentItems = ContentItem::with('contentType')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
                
            $contentTypes = ContentType::where('is_active', true)->orderBy('name')->get();
            
            return view('admin.content_items.index', compact('contentItems', 'contentTypes'));
        }
    }

    /**
     * Show the form for creating a new content item.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function create(ContentType $contentType)
    {
        $contentType->load(['fields' => function ($query) {
            $query->orderBy('order_index');
        }]);
        
        return view('admin.content_items.create', compact('contentType'));
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:content_items,slug',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        // Set content type and user information
        $validated['content_type_id'] = $contentType->id;
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        
        // Create content item
        $contentItem = ContentItem::create($validated);
        
        // Create field values
        foreach ($contentType->fields as $field) {
            $fieldKey = 'field_' . $field->id;
            $value = $request->input($fieldKey);
            
            // Handle special field types
            if ($field->type === 'image' && $request->hasFile($fieldKey)) {
                $contentItem->addMediaFromRequest($fieldKey)
                    ->toMediaCollection("field_{$field->key}");
                
                // The value will be set by the media library
                continue;
            } elseif ($field->type === 'gallery' && $request->hasFile($fieldKey)) {
                foreach ($request->file($fieldKey) as $file) {
                    $contentItem->addMedia($file)
                        ->toMediaCollection("field_{$field->key}");
                }
                
                // The value will be set by the media library
                continue;
            } elseif ($field->type === 'file' && $request->hasFile($fieldKey)) {
                $contentItem->addMediaFromRequest($fieldKey)
                    ->toMediaCollection("field_{$field->key}");
                
                // The value will be set by the media library
                continue;
            }
            
            // Create field value
            $contentItem->fieldValues()->create([
                'field_id' => $field->id,
                'value' => $value,
            ]);
        }
        
        return redirect()->route('admin.content-types.content-items.edit', [$contentType, $contentItem])
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
        $contentItem->load([
            'contentType',
            'fieldValues.field',
            'categories',
        ]);
        
        $renderedItem = $this->contentRenderingService->renderContentItem($contentItem);
        
        return view('admin.content_items.show', compact('contentType', 'contentItem', 'renderedItem'));
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
        $contentType->load(['fields' => function ($query) {
            $query->orderBy('order_index');
        }]);
        
        $contentItem->load('fieldValues');
        
        return view('admin.content_items.edit', compact('contentType', 'contentItem'));
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:content_items,slug,' . $contentItem->id,
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);
        
        // Set user information
        $validated['updated_by'] = Auth::id();
        
        // Update content item
        $contentItem->update($validated);
        
        // Update field values
        foreach ($contentType->fields as $field) {
            $fieldKey = 'field_' . $field->id;
            $value = $request->input($fieldKey);
            
            // Handle special field types
            if ($field->type === 'image' && $request->hasFile($fieldKey)) {
                // Clear existing media
                $contentItem->clearMediaCollection("field_{$field->key}");
                
                // Add new media
                $contentItem->addMediaFromRequest($fieldKey)
                    ->toMediaCollection("field_{$field->key}");
                
                // The value will be set by the media library
                continue;
            } elseif ($field->type === 'gallery' && $request->hasFile($fieldKey)) {
                // Handle gallery updates
                if ($request->input('clear_gallery_' . $field->id)) {
                    $contentItem->clearMediaCollection("field_{$field->key}");
                }
                
                foreach ($request->file($fieldKey) as $file) {
                    $contentItem->addMedia($file)
                        ->toMediaCollection("field_{$field->key}");
                }
                
                // The value will be set by the media library
                continue;
            } elseif ($field->type === 'file' && $request->hasFile($fieldKey)) {
                // Clear existing media
                $contentItem->clearMediaCollection("field_{$field->key}");
                
                // Add new media
                $contentItem->addMediaFromRequest($fieldKey)
                    ->toMediaCollection("field_{$field->key}");
                
                // The value will be set by the media library
                continue;
            }
            
            // Update or create field value
            $fieldValue = $contentItem->fieldValues()->where('field_id', $field->id)->first();
            
            if ($fieldValue) {
                $fieldValue->update(['value' => $value]);
            } else {
                $contentItem->fieldValues()->create([
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
        
        return redirect()->route('admin.content-types.content-items.edit', [$contentType, $contentItem])
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
        // Soft delete the content item
        $contentItem->delete();
        
        return redirect()->route('admin.content-types.content-items.index', $contentType)
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
        $contentItem->load([
            'contentType',
            'fieldValues.field',
            'categories',
        ]);
        
        $renderedItem = $this->contentRenderingService->renderContentItem($contentItem);
        
        return view('admin.content_items.preview', compact('contentType', 'contentItem', 'renderedItem'));
    }
}
```
