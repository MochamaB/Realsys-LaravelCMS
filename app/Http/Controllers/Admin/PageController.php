<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::with('template')->latest()->paginate(15);
        
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $templates = Template::where('is_active', true)->get();
        $parentPages = Page::where('parent_id', null)
            ->where('status', 'published')
            ->orderBy('title')
            ->get();
        
        return view('admin.pages.create', compact('templates', 'parentPages'));
    }

    /**
     * Store a newly created page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                'unique:pages',
            ],
            'template_id' => 'required|exists:templates,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        // Set published_at to now if status is published and published_at is not set
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        
        // Set the creator
        $validated['created_by'] = Auth::guard('admin')->id();
        $validated['updated_by'] = Auth::guard('admin')->id();
        
        $page = Page::create($validated);
        
        // Create page sections for each template section
        $template = Template::findOrFail($validated['template_id']);
        foreach ($template->sections as $templateSection) {
            $page->sections()->create([
                'template_section_id' => $templateSection->id,
                'order_index' => $templateSection->order_index,
                'is_active' => true,
            ]);
        }
        
        // Handle featured image if uploaded
        if ($request->hasFile('featured_image')) {
            $page->addMediaFromRequest('featured_image')
                ->withCustomProperties(['alt' => $request->input('featured_image_alt', '')])
                ->toMediaCollection('featured_image');
        }
        
        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        $templates = Template::where('is_active', true)->get();
        $page->load('sections.widgets', 'template.sections');
        
        $parentPages = Page::where('parent_id', null)
            ->where('id', '!=', $page->id)
            ->where('status', 'published')
            ->orderBy('title')
            ->get();
        
        return view('admin.pages.edit', compact('page', 'templates', 'parentPages'));
    }

    /**
     * Update the specified page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages')->ignore($page->id),
            ],
            'template_id' => 'required|exists:templates,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        // Set published_at to now if status is published and published_at is not set
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        
        // Set the updater
        $validated['updated_by'] = Auth::guard('admin')->id();
        
        // Check if template has changed
        $templateChanged = $page->template_id != $validated['template_id'];
        
        $page->update($validated);
        
        // If template has changed, recreate page sections
        if ($templateChanged) {
            // Delete existing page sections and their widgets
            foreach ($page->sections as $section) {
                $section->widgets()->delete();
                $section->delete();
            }
            
            // Create new page sections for each template section
            $template = Template::findOrFail($validated['template_id']);
            foreach ($template->sections as $templateSection) {
                $page->sections()->create([
                    'template_section_id' => $templateSection->id,
                    'order_index' => $templateSection->order_index,
                    'is_active' => true,
                ]);
            }
        }
        
        // Handle featured image if uploaded
        if ($request->hasFile('featured_image')) {
            // Remove existing featured image
            $page->clearMediaCollection('featured_image');
            
            // Add new featured image
            $page->addMediaFromRequest('featured_image')
                ->withCustomProperties(['alt' => $request->input('featured_image_alt', '')])
                ->toMediaCollection('featured_image');
        }
        
        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        // Delete page sections and their widgets
        foreach ($page->sections as $section) {
            $section->widgets()->delete();
            $section->delete();
        }
        
        // Delete media
        $page->clearMediaCollection('featured_image');
        $page->clearMediaCollection('gallery');
        $page->clearMediaCollection('attachments');
        
        // Delete page
        $page->delete();
        
        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}
