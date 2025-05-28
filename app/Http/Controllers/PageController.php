<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * The template renderer instance
     *
     * @var TemplateRenderer
     */
    protected $templateRenderer;
    
    /**
     * Create a new controller instance.
     *
     * @param TemplateRenderer $templateRenderer
     * @return void
     */
    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }
    
    /**
     * Display the specified page.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show(string $slug = null)
    {
        // If no slug is provided, show the homepage
        if (is_null($slug)) {
            $page = Page::where('is_homepage', true)
                ->where('status', 'published')
                ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets.widgetType'])
                ->first();
            
            if (!$page) {
                // If no homepage is set, get the first published page
                $page = Page::where('status', 'published')
                    ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets.widgetType'])
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
                ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets.widgetType'])
                ->first();
                
            if (!$page) {
                abort(404);
            }
        }
        
        // Use the template renderer if the page has a template
        if ($page->template) {
            return response($this->templateRenderer->renderPage($page));
        }
        
        // Fallback to the old view if no template is assigned
        return view('front.pages.show', [
            'page' => $page
        ]);
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
        $path = trim($request->path(), '/');
        
        if (empty($path)) {
            return redirect()->route('home');
        }
        
        $page = Page::where('slug', $path)
            ->where('status', 'published')
            ->with(['template', 'template.theme', 'sections.templateSection', 'sections.widgets.widgetType'])
            ->first();
            
        if ($page) {
            // Use the template renderer if the page has a template
            if ($page->template) {
                return response($this->templateRenderer->renderPage($page));
            }
            
            // Fallback to the old view if no template is assigned
            return view('front.pages.show', [
                'page' => $page
            ]);
        }
        
        abort(404);
    }
}