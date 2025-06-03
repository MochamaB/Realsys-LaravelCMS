<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageSectionManager;
use App\Services\TemplateRenderer;
use App\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    /**
     * The template renderer instance
     *
     * @var TemplateRenderer
     */
    protected $templateRenderer;
    
    /**
     * The theme manager instance
     *
     * @var ThemeManager
     */
    protected $themeManager;
    
    /**
     * The page section manager instance
     *
     * @var PageSectionManager
     */
    protected $sectionManager;
    
    /**
     * Create a new controller instance.
     *
     * @param TemplateRenderer $templateRenderer
     * @param ThemeManager $themeManager
     * @param PageSectionManager $sectionManager
     * @return void
     */
    public function __construct(
        TemplateRenderer $templateRenderer,
        ThemeManager $themeManager,
        PageSectionManager $sectionManager
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->themeManager = $themeManager;
        $this->sectionManager = $sectionManager;
    }
    
    /**
     * Display the specified page.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show(string $slug = null)
    {
        try {
            // Get the active theme
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return view('front.errors.no-theme');
            }
            
            // If no slug is provided, show the homepage
            if (is_null($slug)) {
                $page = Page::where('is_homepage', true)
                    ->where('status', 'published')
                    ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets'])
                    ->first();
                
                if (!$page) {
                    // If no homepage is set, get the first published page
                    $page = Page::where('status', 'published')
                        ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets'])
                        ->orderBy('created_at', 'asc')
                        ->first();
                }
                
                if (!$page) {
                    return view('front.errors.no-homepage');
                }
            } else {
                // Find the page by slug
                $page = Page::where('slug', $slug)
                    ->where('status', 'published')
                    ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets'])
                    ->first();
                    
                if (!$page) {
                    abort(404);
                }
            }
            
            // Check if the page's template belongs to the active theme
            if ($page->template && $page->template->theme_id !== $activeTheme->id) {
                Log::warning("Page {$page->id} is using a template from an inactive theme");
                
                // Try to find a similar template in the active theme
                $similarTemplate = $activeTheme->templates()
                    ->where('is_active', true)
                    ->first();
                    
                if (!$similarTemplate) {
                    return view('front.errors.invalid-template', [
                        'page' => $page,
                        'message' => 'This page uses a template from an inactive theme.'
                    ]);
                }
                
                // Use the similar template instead
                Log::info("Switching page {$page->id} to template {$similarTemplate->id} from the active theme");
                $page->template = $similarTemplate;
            }
            
            // Use the template renderer if the page has a template
            if ($page->template) {
                return response($this->templateRenderer->renderPage($page));
            }
            
            // Fallback to the basic view if no template is assigned
            return view('front.pages.show', [
                'page' => $page,
                'theme' => $activeTheme
            ]);
        } catch (\Exception $e) {
            Log::error('Error rendering page: ' . $e->getMessage(), [
                'slug' => $slug,
                'exception' => $e
            ]);
            
            // Temporarily showing the actual error instead of the error page
            throw $e;
            
            // return view('front.errors.page-error', [
            //     'message' => 'There was an error rendering this page.'
            // ]);
        }
    }
    
    /**
     * Resolve a URL to a page.
     * This is used by the fallback route to handle CMS pages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resolve(Request $request)
    {
        try {
            $path = trim($request->path(), '/');
            
            if (empty($path)) {
                return redirect()->route('home');
            }
            
            // Handle hierarchical pages (e.g., parent/child)
            $pathSegments = explode('/', $path);
            $slug = end($pathSegments);
            
            // Try to find the page by the final path segment
            $page = Page::where('slug', $slug)
                ->where('status', 'published')
                ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets'])
                ->first();
                
            // If found, verify that the full path matches the page hierarchy
            if ($page) {
                $correctPath = $this->getPageFullPath($page);
                
                // If paths don't match exactly, redirect to the correct URL
                if ($correctPath !== $path) {
                    return redirect($correctPath ? '/' . $correctPath : '/');
                }
                
                // Get the active theme
                $activeTheme = $this->themeManager->getActiveTheme();
                
                if (!$activeTheme) {
                    return view('front.errors.no-theme');
                }
                
                // Check if the page's template belongs to the active theme
                if ($page->template && $page->template->theme_id !== $activeTheme->id) {
                    Log::warning("Page {$page->id} is using a template from an inactive theme");
                    
                    // Try to find a similar template in the active theme
                    $similarTemplate = $activeTheme->templates()
                        ->where('is_active', true)
                        ->first();
                        
                    if (!$similarTemplate) {
                        return view('front.errors.invalid-template', [
                            'page' => $page,
                            'message' => 'This page uses a template from an inactive theme.'
                        ]);
                    }
                    
                    // Use the similar template instead
                    $page->template = $similarTemplate;
                }
                
                // Use the template renderer if the page has a template
                if ($page->template) {
                    return response($this->templateRenderer->renderPage($page));
                }
                
                // Fallback to the basic view if no template is assigned
                return view('front.pages.show', [
                    'page' => $page,
                    'theme' => $activeTheme
                ]);
            }
            
            // Page not found
            abort(404);
        } catch (\Exception $e) {
            Log::error('Error resolving page: ' . $e->getMessage(), [
                'path' => $request->path(),
                'exception' => $e
            ]);
            
            abort(500, 'Error resolving page');
    }
}

/**
 * Get the full path for a page, including parent pages
 *
 * @param Page $page The page to get the path for
 * @return string The full path (without leading/trailing slashes)
 */
protected function getPageFullPath(Page $page): string
{
    $path = $page->slug;
    $currentPage = $page;
    
    // Add parent slugs to the path
    while ($currentPage->parent_id) {
        $parent = Page::find($currentPage->parent_id);
        
        if (!$parent) {
            break;
        }
        
        $path = $parent->slug . '/' . $path;
        $currentPage = $parent;
    }
    
    return $path;
}

/**
 * Preview a page by its ID
 *
 * @param int $id The page ID
 * @return \Illuminate\Http\Response
 */
public function preview($id)
{
    // Only allow previews for authenticated users
    if (!auth()->check() && !auth('admin')->check()) {
        abort(403);
    }
    
    $page = Page::with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets'])
        ->findOrFail($id);
    
    try {
        // Get the active theme
        $activeTheme = $this->themeManager->getActiveTheme();
        
        if (!$activeTheme) {
            return view('front.errors.no-theme');
        }
        
        // Use the template renderer if the page has a template
        if ($page->template) {
            return response($this->templateRenderer->renderPage($page));
        }
        
        // Fallback to the basic view if no template is assigned
        return view('front.pages.show', [
            'page' => $page,
            'theme' => $activeTheme,
            'preview' => true
        ]);
    } catch (\Exception $e) {
        Log::error('Error previewing page: ' . $e->getMessage(), [
            'page_id' => $id,
            'exception' => $e
        ]);
        
        return view('front.errors.page-error', [
            'message' => 'There was an error previewing this page.'
        ]);
    }
}
}