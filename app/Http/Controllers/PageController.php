<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the specified page.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show(string $slug = null)
    {
        // If no slug is provided, show the homepage
        if (is_null($slug)) {
            $page = Page::where('is_homepage', true)
                ->where('status', 'published')
                ->first();
            
            if (!$page) {
                // If no homepage is set, get the first published page
                $page = Page::where('status', 'published')
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
                ->first();
                
            if (!$page) {
                abort(404);
            }
        }
        
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
            ->first();
            
        if ($page) {
            return view('front.pages.show', [
                'page' => $page
            ]);
        }
        
        abort(404);
    }
}