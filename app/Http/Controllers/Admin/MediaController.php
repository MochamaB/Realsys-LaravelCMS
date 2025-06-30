<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Models\MediaTag;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Get media for the media picker component.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function picker(Request $request)
    {
        $query = Media::with(['tags', 'folder'])->latest();
        $perPage = $request->input('per_page', 24);
        
        // Apply filters
        if ($request->has('type')) {
            $query->where('mime_type', 'like', $request->type . '%');
        }
        
        if ($request->has('collection_name')) {
            $query->where('collection_name', $request->collection_name);
        }
        
        // Filter by folder
        if ($request->has('folder_id')) {
            if ($request->folder_id === 'root' || $request->folder_id === '0') {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $request->folder_id);
            }
        }
        
        // Filter by tag
        if ($request->has('tag_id')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('media_tags.id', $request->tag_id);
            });
        }
        
        // Search by name or filename
        if ($request->has('search') && !empty($request->search)) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('file_name', 'like', $search);
            });
        }
        
        // Get media items with pagination
        $media = $query->paginate($perPage);
        
        // Get all folders for the folder filter
        $folders = MediaFolder::whereNull('parent_id')
            ->with('allChildren')
            ->get();
        
        // Get all tags for the tag filter
        $tags = MediaTag::orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'media' => $media,
            'folders' => $folders,
            'tags' => $tags,
        ]);
    }
    /**
     * Display a listing of the media.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Media::query();
        
        // Apply filters if provided
        if ($request->has('type')) {
            $query->where('mime_type', 'like', $request->type . '%');
        }
        
        if ($request->has('collection_name')) {
            $query->where('collection_name', $request->collection_name);
        }
        
        // Filter by folder if specified
        if ($request->has('folder_id')) {
            if ($request->folder_id === 'root' || $request->folder_id === '0') {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $request->folder_id);
            }
        }
        
        // Filter by tag if specified
        if ($request->has('tag_id')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('media_tags.id', $request->tag_id);
            });
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }
        
        // Get the media items with pagination
        $mediaItems = $query->orderBy('created_at', 'desc')
                           ->paginate(24);
        
        // Get collection statistics
        $stats = [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'documents' => Media::where('mime_type', 'like', 'application/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'audio' => Media::where('mime_type', 'like', 'audio/%')->count(),
        ];
        
        // Define media collections
        $collections = Media::select('collection_name')
                          ->distinct()
                          ->pluck('collection_name');
        
        // Get folders for sidebar navigation
        $rootFolders = MediaFolder::withCount('media')
                              ->whereNull('parent_id')
                              ->orderBy('name')
                              ->get();
                              
        // Get all tags
        $tags = MediaTag::withCount('media')
                    ->orderBy('name')
                    ->get();
        
        return view('admin.media.index', compact('mediaItems', 'stats', 'collections', 'rootFolders', 'tags'));
    }

    /**
     * Store a newly uploaded media file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'file' => 'required|file|max:10240', // 10MB max
        'collection_name' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    try {
        $file = $request->file('file');
        $collection = $request->input('collection_name', 'default');
        
        // Instead of using a temporary model, add the file directly
        // to the media library as an unattached media item
        $media = \App\Models\Media::create([]);
        
        $media->name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $media->file_name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Generate a path for the file in the media disk
        $mediaPath = $collection . '/' . date('Y-m-d') . '/' . $media->file_name;
        
        // Move the uploaded file to the media disk
        $path = Storage::disk('media')->putFileAs(
            dirname($mediaPath),
            $file,
            $media->file_name
        );
        
        // Set the media properties
        $media->collection_name = $collection;
        $media->mime_type = $file->getMimeType();
        $media->size = $file->getSize();
        $media->disk = 'media';
        $media->conversions_disk = 'media';
        $media->manipulations = [];
        $media->custom_properties = [
            'alt' => $request->input('alt', ''),
            'title' => $request->input('title', ''),
            'caption' => $request->input('caption', ''),
        ];
        $media->responsive_images = [];
        $media->order_column = \App\Models\Media::max('order_column') + 1;
        
        // Assign to folder if specified
        if ($request->has('folder_id') && $request->folder_id != 'root' && $request->folder_id != '0') {
            $media->folder_id = $request->folder_id;
        }
        
        $media->save();
        
        // If it's an image, generate conversions
        if (Str::startsWith($media->mime_type, 'image/')) {
            $media->registerMediaConversions();
        }
        
        return response()->json([
            'success' => true,
            'media' => $media
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Display the specified media item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $media = Media::findOrFail($id);
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'collection_name' => $media->collection_name,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                    'custom_properties' => $media->custom_properties,
                    'full_url' => $media->getFullUrl()
                ]
            ]);
        }
        
        return view('admin.media.show', compact('media'));
    }

    /**
     * Update the specified media item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $media = Media::findOrFail($id);
        
        // Update name if provided
        if ($request->has('name')) {
            $media->name = $request->name;
        }
        
        // Update custom properties
        $customProperties = $media->custom_properties ?? [];
        
        if ($request->has('alt')) {
            $customProperties['alt'] = $request->alt;
        }
        
        if ($request->has('title')) {
            $customProperties['title'] = $request->title;
        }
        
        if ($request->has('caption')) {
            $customProperties['caption'] = $request->caption;
        }
        
        $media->custom_properties = $customProperties;
        $media->save();
        
        return response()->json([
            'success' => true,
            'media' => $media
        ]);
    }

    /**
     * Remove the specified media item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        
        try {
            $media->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get media files by type or collection
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        $query = Media::query();
        
        if ($request->has('type')) {
            $query->where('mime_type', 'like', $request->type . '%');
        }
        
        if ($request->has('collection')) {
            $query->where('collection_name', $request->collection);
        }
        
        $mediaItems = $query->orderBy('created_at', 'desc')
                           ->paginate(24);
        
        return response()->json($mediaItems);
    }
    
    /**
     * Search media by name or filename
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $request->search;
        
        $mediaItems = Media::where('name', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orderBy('created_at', 'desc')
                        ->paginate(24);
        
        return response()->json($mediaItems);
    }
    
    /**
     * Update tags associated with a media item
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTags(Request $request, $id)
    {
        $media = Media::findOrFail($id);
        $tagIds = $request->input('tag_ids', []);
        
        // Sync tags to the media item
        $media->tags()->sync($tagIds);
        
        // Get the updated tag list
        $tags = $media->tags;
        
        return response()->json([
            'success' => true,
            'tags' => $tags,
            'message' => 'Tags updated successfully'
        ]);
    }
    
    /**
     * Move media items to a folder
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function moveToFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        
        $mediaIds = $request->input('media_ids');
        $folderId = $request->input('folder_id');
        
        // If folder_id is null or 'root', set to null to place in root folder
        if ($folderId === 'root' || $folderId === '0' || $folderId === 0) {
            $folderId = null;
        }
        
        // Update all specified media items to the new folder
        Media::whereIn('id', $mediaIds)->update(['folder_id' => $folderId]);
        
        $folderName = $folderId ? MediaFolder::find($folderId)->name : 'Root';
        
        return response()->json([
            'success' => true,
            'message' => count($mediaIds) . ' items moved to ' . $folderName . ' folder'
        ]);
    }
    
    /**
     * Batch delete multiple media items
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batchDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        
        $mediaIds = $request->input('media_ids');
        
        try {
            // Delete all specified media items
            $count = 0;
            foreach ($mediaIds as $id) {
                $media = Media::find($id);
                if ($media) {
                    $media->delete();
                    $count++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $count . ' media items deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Batch add tags to multiple media items
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batchTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:media_tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        
        $mediaIds = $request->input('media_ids');
        $tagIds = $request->input('tag_ids');
        
        // Add tags to all selected media
        foreach ($mediaIds as $id) {
            $media = Media::find($id);
            if ($media) {
                // Use attach to add tags without removing existing ones
                $media->tags()->syncWithoutDetaching($tagIds);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Tags added to ' . count($mediaIds) . ' media items'
        ]);
    }
}