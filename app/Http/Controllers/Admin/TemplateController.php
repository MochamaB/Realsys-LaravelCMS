<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Theme;
use App\Models\TemplateSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    /**
     * Display a listing of the templates.
     */
    public function index(Request $request)
    {
        $query = Template::query()->with('theme');
        
        // Filter by theme if provided
        if ($request->has('theme_id') && $request->theme_id) {
            $query->where('theme_id', $request->theme_id);
        }
        
        $templates = $query->orderBy('name')->get();
        $themes = Theme::orderBy('name')->get();
        
        return view('admin.templates.index', compact('templates', 'themes'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        $sectionTypes = TemplateSection::getTypes();
        
        return view('admin.templates.create', compact('themes', 'sectionTypes'));
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|max:2048',
            'is_default' => 'boolean',
        ]);
        
        // Generate slug from name
        $slug = Str::slug($validated['name']);
        
        // Make sure slug is unique for this theme
        $count = 0;
        $originalSlug = $slug;
        while (Template::where('theme_id', $validated['theme_id'])
                      ->where('slug', $slug)
                      ->exists()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }
        
        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = $slug . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/templates'), $filename);
            $thumbnailPath = 'uploads/templates/' . $filename;
        }
        
        // Create the template
        $template = Template::create([
            'theme_id' => $validated['theme_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'file_path' => $validated['file_path'],
            'thumbnail_path' => $thumbnailPath,
            'is_active' => true,
            'is_default' => $request->has('is_default') && $request->is_default ? true : false,
        ]);
        
        // If this is set as default, make sure no other template for this theme is default
        if ($template->is_default) {
            $template->setAsDefault();
        }
        
        // If sections were provided, create them
        if ($request->has('sections') && is_array($request->sections)) {
            foreach ($request->sections as $index => $sectionData) {
                // Skip if no name provided
                if (empty($sectionData['name'])) {
                    continue;
                }
                
                $sectionSlug = Str::slug($sectionData['name']);
                
                TemplateSection::create([
                    'template_id' => $template->id,
                    'name' => $sectionData['name'],
                    'slug' => $sectionSlug,
                    'description' => $sectionData['description'] ?? null,
                    'type' => $sectionData['type'] ?? TemplateSection::TYPE_CONTENT,
                    'width' => $sectionData['width'] ?? null,
                    'is_required' => isset($sectionData['is_required']) && $sectionData['is_required'] ? true : false,
                    'max_widgets' => $sectionData['max_widgets'] ?? null,
                    'order_index' => $index,
                    'is_active' => true,
                ]);
            }
        }
        
        return redirect()->route('admin.templates.show', $template)
                ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show(Template $template)
    {
        // Load the related theme and sections
        $template->load(['theme', 'sections' => function($query) {
            $query->orderBy('order_index');
        }, 'pages']);
        
        return view('admin.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Template $template)
    {
        $themes = Theme::orderBy('name')->get();
        $template->load(['sections' => function($query) {
            $query->orderBy('order_index');
        }]);
        $sectionTypes = TemplateSection::getTypes();
        
        return view('admin.templates.edit', compact('template', 'themes', 'sectionTypes'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|max:2048',
            'is_default' => 'boolean',
        ]);
        
        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Remove old thumbnail if exists
            if ($template->thumbnail_path && File::exists(public_path($template->thumbnail_path))) {
                File::delete(public_path($template->thumbnail_path));
            }
            
            $file = $request->file('thumbnail');
            $filename = $template->slug . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/templates'), $filename);
            $template->thumbnail_path = 'uploads/templates/' . $filename;
        }
        
        // Update the template
        $template->theme_id = $validated['theme_id'];
        $template->name = $validated['name'];
        $template->description = $validated['description'] ?? null;
        $template->file_path = $validated['file_path'];
        $template->is_default = $request->has('is_default') && $request->is_default ? true : false;
        $template->save();
        
        // If this is set as default, make sure no other template for this theme is default
        if ($template->is_default) {
            $template->setAsDefault();
        }
        
        return redirect()->route('admin.templates.show', $template)
                ->with('success', 'Template updated successfully.');
    }

    /**
     * Set a template as the default for its theme.
     */
    public function setDefault(Template $template)
    {
        $template->setAsDefault();
        
        return redirect()->back()
                ->with('success', 'Template set as default for its theme.');
    }

    /**
     * Preview the specified template.
     */
    public function preview(Template $template)
    {
        $template->load(['theme', 'sections' => function($query) {
            $query->orderBy('order_index');
        }]);
        
        return view('admin.templates.preview', compact('template'));
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(Template $template)
    {
        // Check if template is in use by any pages
        $pagesCount = $template->pages()->count();
        if ($pagesCount > 0) {
            return redirect()->route('admin.templates.show', $template)
                    ->with('error', "Cannot delete template because it is used by {$pagesCount} pages.");
        }
        
        // Delete the thumbnail if exists
        if ($template->thumbnail_path && File::exists(public_path($template->thumbnail_path))) {
            File::delete(public_path($template->thumbnail_path));
        }
        
        // Delete the template and its sections (cascading)
        $template->delete();
        
        return redirect()->route('admin.templates.index')
                ->with('success', 'Template deleted successfully.');
    }
}
