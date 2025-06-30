<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaTagController extends Controller
{
    /**
     * Display a listing of the media tags.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = MediaTag::withCount('media')->orderBy('name')->get();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'tags' => $tags
            ]);
        }
        
        return view('admin.media.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new media tag.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.media.tags.create');
    }

    /**
     * Store a newly created media tag in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:media_tags',
            'color' => 'nullable|string|size:7|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $tag = new MediaTag();
        $tag->name = $request->input('name');
        $tag->slug = Str::slug($request->input('name'));
        $tag->color = $request->input('color', '#6c757d'); // Default color if none provided
        $tag->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tag' => $tag
            ]);
        }

        return redirect()->route('admin.media-tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the form for editing the specified media tag.
     *
     * @param  \App\Models\MediaTag  $mediaTag
     * @return \Illuminate\Http\Response
     */
    public function edit(MediaTag $mediaTag)
    {
        return view('admin.media.tags.edit', compact('mediaTag'));
    }

    /**
     * Update the specified media tag in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MediaTag  $mediaTag
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MediaTag $mediaTag)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('media_tags')->ignore($mediaTag->id),
            ],
            'color' => 'nullable|string|size:7|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $mediaTag->name = $request->input('name');
        $mediaTag->slug = Str::slug($request->input('name'));
        $mediaTag->color = $request->input('color', '#6c757d');
        $mediaTag->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tag' => $mediaTag
            ]);
        }

        return redirect()->route('admin.media-tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified media tag from storage.
     *
     * @param  \App\Models\MediaTag  $mediaTag
     * @return \Illuminate\Http\Response
     */
    public function destroy(MediaTag $mediaTag)
    {
        $mediaTag->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully.'
            ]);
        }

        return redirect()->route('admin.media-tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
