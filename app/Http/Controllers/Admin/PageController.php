<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Template;
use App\Services\PageService;
use App\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    /**
     * The page service instance.
     *
     * @var \App\Services\PageService
     */
    protected $pageService;
    
    /**
     * The theme manager instance.
     *
     * @var \App\Services\ThemeManager
     */
    protected $themeManager;
    
    /**
     * Create a new controller instance.
     *
     * @param \App\Services\PageService $pageService
     * @param \App\Services\ThemeManager $themeManager
     * @return void
     */
    public function __construct(PageService $pageService, ThemeManager $themeManager)
    {
        $this->pageService = $pageService;
        $this->themeManager = $themeManager;
    }
    
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
        try {
            // Get templates from the active theme only
            $templates = $this->pageService->getActiveThemeTemplates();
            
            // Get the active theme
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return redirect()->route('admin.themes.index')
                    ->with('error', 'No active theme found. Please activate a theme first.');
            }
            
            $parentPages = Page::where('parent_id', null)
                ->where('status', 'published')
                ->orderBy('title')
                ->get();
            
            return view('admin.pages.create', compact('templates', 'parentPages', 'activeTheme'));
        } catch (\Exception $e) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'Error loading page creation form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9-]+$/',
                    Rule::unique('pages')->ignore($request->id),
                ],
                'template_id' => 'required|exists:templates,id',
                'parent_id' => 'nullable|exists:pages,id',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'status' => 'required|in:draft,published',
                'published_at' => 'nullable|date',
                'is_homepage' => 'boolean',
            ]);
            
            // Set the creator
            $validated['created_by'] = Auth::guard('admin')->id();
            $validated['updated_by'] = Auth::guard('admin')->id();
            
            // Create the page using our service
            $page = $this->pageService->createPage($validated);

            // If this page is marked as homepage, unset all other homepage flags
            if ($request->has('is_homepage') && $request->is_homepage) {
                // Set all other pages' is_homepage to false
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
            }

            // Handle featured image if uploaded
            if ($request->hasFile('featured_image')) {
                $page->addMediaFromRequest('featured_image')
                    ->withCustomProperties(['alt' => $request->input('featured_image_alt', '')])
                    ->toMediaCollection('featured_image');
            }
        
            return redirect()->route('admin.pages.edit', $page)
                ->with('success', 'Page created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create page: ' . $e->getMessage())
                ->withInput();
        }
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
        try {
            // Get templates from the active theme only
            $templates = $this->pageService->getActiveThemeTemplates();
            
            // Get the active theme
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return redirect()->route('admin.themes.index')
                    ->with('error', 'No active theme found. Please activate a theme first.');
            }
            
            $page->load('sections.widgets', 'template.sections');
            
            $parentPages = Page::where('parent_id', null)
                ->where('id', '!=', $page->id)
                ->where('status', 'published')
                ->orderBy('title')
                ->get();
            
            return view('admin.pages.edit', compact('page', 'templates', 'parentPages', 'activeTheme'));
        } catch (\Exception $e) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'Error loading page edit form: ' . $e->getMessage());
        }
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
        try {
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
                'parent_id' => 'nullable|exists:pages,id',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'status' => 'required|in:draft,published',
                'published_at' => 'nullable|date',
                'confirm_template_change' => 'sometimes|boolean',
                'is_homepage' => 'boolean',
            ]);
            
            // Check if template has changed
            $templateChanged = $page->template_id != $validated['template_id'];
            
            // If template has changed and user hasn't confirmed, return with warning
            if ($templateChanged && !isset($validated['confirm_template_change'])) {
                $oldTemplate = Template::find($page->template_id);
                $newTemplate = Template::find($validated['template_id']);
                
                return redirect()->back()
                    ->with('warning', 'Changing the template may affect your page content. Please confirm this change.')
                    ->with('template_change', true)
                    ->with('old_template', $oldTemplate)
                    ->with('new_template', $newTemplate)
                    ->withInput();
            }
            
            // Set the updater
            $validated['updated_by'] = Auth::guard('admin')->id();
            
            // Remove the confirmation flag if present
            unset($validated['confirm_template_change']);
            
            // Update the page using our service
            $page = $this->pageService->updatePage($page, $validated);

            // If this page is marked as homepage, unset all other homepage flags
            if ($request->has('is_homepage') && $request->is_homepage) {
                // Set all other pages' is_homepage to false
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
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
            
            // If template was changed, show a success message with details
            if ($templateChanged) {
                return redirect()->route('admin.pages.edit', $page)
                    ->with('success', 'Page updated successfully. The template has been changed and page sections have been updated.');
            }
            
            return redirect()->route('admin.pages.edit', $page)
                ->with('success', 'Page updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update page: ' . $e->getMessage())
                ->withInput();
        }
    }
    // Add this method to PageController.php
    public function toggleHomepage($id)
    {
        $page = Page::findOrFail($id);
        
        // Only published pages can be set as homepage
        if ($page->status !== 'published') {
            return back()->with('error', 'Only published pages can be set as homepage.');
        }
        
        // Set all pages' is_homepage to false first
        Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
        
        // Set this page as homepage
        $page->is_homepage = true;
        $page->save();
        
        return back()->with('success', 'Homepage has been updated successfully.');
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
