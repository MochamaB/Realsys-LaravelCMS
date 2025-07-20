<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\ContentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContentItemController extends Controller
{
    /**
     * List all content items for a content type.
     */
    public function index($type)
    {
        $contentType = ContentType::where('slug', $type)->firstOrFail();
        $items = ContentItem::where('content_type_id', $contentType->id)->latest()->get();
        return response()->json(['items' => $items]);
    }

    /**
     * Create a new content item for a content type.
     */
    public function store(Request $request, $type)
    {
        $contentType = ContentType::where('slug', $type)->firstOrFail();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:content_items,slug',
            'status' => 'required|string|in:draft,published,archived',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        $validated['content_type_id'] = $contentType->id;
        if (Schema::hasColumn('content_items', 'created_by')) {
            $validated['created_by'] = Auth::id();
        }
        if (Schema::hasColumn('content_items', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        $item = ContentItem::create($validated);
        // Optionally: process field values if needed
        return response()->json(['success' => true, 'item' => $item]);
    }
} 