<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaFolderController extends Controller
{
    /**
     * Display a listing of the media folders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rootFolders = MediaFolder::withCount(['media', 'children'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
            
        // Load nested folders with their counts
        $folders = $this->loadNestedFolders($rootFolders);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'folders' => $folders
            ]);
        }
        
        return view('admin.media.folders.index', compact('folders'));
    }
    
    /**
     * Helper method to load nested folders with counts
     * 
     * @param \Illuminate\Database\Eloquent\Collection $folders
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function loadNestedFolders($folders)
    {
        foreach ($folders as $folder) {
            $children = $folder->children()->withCount(['media', 'children'])->orderBy('name')->get();
            if ($children->count() > 0) {
                $folder->children = $this->loadNestedFolders($children);
            }
        }
        
        return $folders;
    }

    /**
     * Show the form for creating a new media folder.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $folders = MediaFolder::orderBy('name')->get();
        return view('admin.media.folders.create', compact('folders'));
    }

    /**
     * Store a newly created media folder in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        // Check if folder with this name already exists under the same parent
        $existingFolder = MediaFolder::where('name', $request->input('name'))
            ->where('parent_id', $request->input('parent_id'))
            ->first();
            
        if ($existingFolder) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A folder with this name already exists at this location.'
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors(['name' => 'A folder with this name already exists at this location.'])
                ->withInput();
        }

        $folder = new MediaFolder();
        $folder->name = $request->input('name');
        $folder->slug = Str::slug($request->input('name'));
        $folder->parent_id = $request->input('parent_id');
        $folder->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'folder' => $folder->load('parent')
            ]);
        }

        return redirect()->route('admin.media-folders.index')
            ->with('success', 'Folder created successfully.');
    }

    /**
     * Show the form for editing the specified media folder.
     *
     * @param  \App\Models\MediaFolder  $mediaFolder
     * @return \Illuminate\Http\Response
     */
    public function edit(MediaFolder $mediaFolder)
    {
        $folders = MediaFolder::where('id', '!=', $mediaFolder->id)
            ->orderBy('name')
            ->get();
            
        return view('admin.media.folders.edit', compact('mediaFolder', 'folders'));
    }

    /**
     * Update the specified media folder in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MediaFolder  $mediaFolder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MediaFolder $mediaFolder)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:media_folders,id',
                function ($attribute, $value, $fail) use ($mediaFolder) {
                    // Can't set a folder as its own parent or as a child of itself
                    if ($value == $mediaFolder->id) {
                        $fail('Cannot set a folder as its own parent.');
                    } else if ($value) {
                        // Check if selected parent is a child of this folder
                        $childIds = $this->getAllChildIds($mediaFolder);
                        if (in_array($value, $childIds)) {
                            $fail('Cannot set a child folder as parent.');
                        }
                    }
                },
            ],
        ]);

        // Check if folder with this name already exists under the same parent
        $existingFolder = MediaFolder::where('name', $request->input('name'))
            ->where('parent_id', $request->input('parent_id'))
            ->where('id', '!=', $mediaFolder->id)
            ->first();
            
        if ($existingFolder) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A folder with this name already exists at this location.'
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors(['name' => 'A folder with this name already exists at this location.'])
                ->withInput();
        }

        $mediaFolder->name = $request->input('name');
        $mediaFolder->slug = Str::slug($request->input('name'));
        $mediaFolder->parent_id = $request->input('parent_id');
        $mediaFolder->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'folder' => $mediaFolder->load('parent')
            ]);
        }

        return redirect()->route('admin.media-folders.index')
            ->with('success', 'Folder updated successfully.');
    }

    /**
     * Remove the specified media folder from storage.
     *
     * @param  \App\Models\MediaFolder  $mediaFolder
     * @return \Illuminate\Http\Response
     */
    public function destroy(MediaFolder $mediaFolder)
    {
        // Count total media items in this folder and all subfolders
        $mediaCount = $mediaFolder->media()->count();
        $childFolders = $this->getAllChildIds($mediaFolder);
        
        if (!empty($childFolders)) {
            $mediaCount += \Spatie\MediaLibrary\MediaCollections\Models\Media::whereIn('folder_id', $childFolders)->count();
        }
        
        // If there are media items, move them to root (null folder)
        if ($mediaCount > 0) {
            $mediaFolder->media()->update(['folder_id' => null]);
            
            if (!empty($childFolders)) {
                \Spatie\MediaLibrary\MediaCollections\Models\Media::whereIn('folder_id', $childFolders)
                    ->update(['folder_id' => null]);
            }
        }
        
        $mediaFolder->delete(); // This will cascade and delete all child folders

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Folder deleted successfully. All media items have been moved to the root folder.'
            ]);
        }

        return redirect()->route('admin.media-folders.index')
            ->with('success', 'Folder deleted successfully. All media items have been moved to the root folder.');
    }
    
    /**
     * Helper method to get all child folder IDs recursively
     * 
     * @param \App\Models\MediaFolder $folder
     * @return array
     */
    private function getAllChildIds(MediaFolder $folder)
    {
        $childIds = [];
        
        $children = $folder->children;
        
        foreach ($children as $child) {
            $childIds[] = $child->id;
            $childIds = array_merge($childIds, $this->getAllChildIds($child));
        }
        
        return $childIds;
    }
}
