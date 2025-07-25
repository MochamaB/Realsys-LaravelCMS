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
     * The template renderer instance.
     *
     * @var \App\Services\TemplateRenderer
     */
    protected $templateRenderer;
    
    /**
     * Create a new controller instance.
     *
     * @param \App\Services\PageService $pageService
     * @param \App\Services\ThemeManager $themeManager
     * @param \App\Services\TemplateRenderer $templateRenderer
     * @return void
     */
    public function __construct(
        PageService $pageService, 
        ThemeManager $themeManager,
        \App\Services\TemplateRenderer $templateRenderer
    ) {
        $this->pageService = $pageService;
        $this->themeManager = $themeManager;
        $this->templateRenderer = $templateRenderer;
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
            
            // Remove parentPages logic to avoid SQL error
            return view('admin.pages.create', compact('templates', 'activeTheme'));
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
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'status' => 'required|in:draft,published',
                'published_at' => 'nullable|date',
                'is_homepage' => 'boolean',
            ]);
            
            $validated['created_by'] = Auth::guard('admin')->id();
            $validated['updated_by'] = Auth::guard('admin')->id();
            
            $page = $this->pageService->createPage($validated);

            if ($request->has('is_homepage') && $request->is_homepage) {
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
            }

            // After creation, redirect to the designer view (show method)
            return redirect()->route('admin.pages.show', $page)
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
        // Show the visual designer (empty for now)
        return view('admin.pages.gridstack-designer', compact('page'));
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
            
            // Load page with its relationships
            $page->load('sections.widgets', 'template.sections');
            
            // Use create view but pass the page data
            return view('admin.pages.create', compact('page', 'templates', 'activeTheme'));
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

    /**
     * Get sections for a specific page as JSON.
     *
     * @param  int  $pageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSections(Page $page)
    {
        // Get all page sections with their related template sections for the specific page
        $sections = $page->sections()->with('templateSection')->get()->map(function($section) {
            $templateSection = $section->templateSection;
            return [
                'id' => $section->id,
                'name' => $templateSection->name,
                'identifier' => $templateSection->identifier,
                'description' => $templateSection->description
            ];
        });

        return response()->json([
            'page_id' => $page->id,
            'page_title' => $page->title,
            'sections' => $sections
        ]);
    }

    /**
     * Render the page content for the GrapesJS editor.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderPageContent(Page $page)
    {
        try {
            // Load all the necessary relationships
            $page->load(['template', 'template.theme', 'sections.templateSection', 'sections.widgets']);
            
            // Verify the template exists and belongs to the active theme
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return response()->json([
                    'error' => 'No active theme found.'
                ], 500);
            }
            
            if (!$page->template || $page->template->theme_id !== $activeTheme->id) {
                return response()->json([
                    'error' => 'Page is using a template from an inactive theme.'
                ], 500);
            }
            
            // Use the template renderer to generate the HTML
            $html = $this->templateRenderer->renderPage($page);
            
            // Return the rendered HTML along with page information
            return response()->json([
                'html' => $html,
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'sections' => $page->sections->map(function($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->name,
                            'type' => $section->templateSection->section_type ?? 'default',
                            'widget_count' => $section->widgets->count()
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to render page content: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save page content from the GrapesJS editor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePageContent(Request $request, Page $page)
    {
        try {
            // Validate the incoming data
            $validated = $request->validate([
                'html' => 'required|string',
                'sections' => 'sometimes|array',
                'sections.*.id' => 'required|integer|exists:page_sections,id',
                'sections.*.content' => 'nullable|string',
                'sections.*.widgets' => 'sometimes|array',
                'sections.*.widgets.*.id' => 'sometimes|integer|exists:page_section_widgets,id',
                'sections.*.widgets.*.content' => 'nullable|string',
            ]);
            
            // Process the saved content
            // For now, just return success response
            // Later we'll implement parsing the HTML and updating the database
            
            return response()->json([
                'message' => 'Page content saved successfully',
                'page_id' => $page->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save page content: ' . $e->getMessage()
            ], 500);
        }
    }
}
