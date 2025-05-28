<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
        if (Schema::hasColumn('content_types', 'created_by')) {
            $validated['created_by'] = Auth::id();
        }
        
        if (Schema::hasColumn('content_types', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Default values
        $validated['is_system'] = false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        
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
        if (Schema::hasColumn('content_types', 'updated_by')) {
            $validated['updated_by'] = Auth::id();
        }
        
        // Default values
        $validated['is_active'] = $validated['is_active'] ?? $contentType->is_active;
        
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
        
        // Check if it's a system content type
        if ($contentType->is_system) {
            return redirect()->route('admin.content-types.index')
                ->with('error', 'Cannot delete system content type.');
        }
        
        // Delete content type
        $contentType->delete();
        
        return redirect()->route('admin.content-types.index')
            ->with('success', 'Content type deleted successfully.');
    }
}
