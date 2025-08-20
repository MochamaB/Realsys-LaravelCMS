<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Widget;
use App\Models\PageSection;
use App\Models\ContentType;
use App\Models\ContentItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Live Designer API Controller
 * 
 * Handles all API endpoints for the GrapesJS Live Designer
 * Provides preview rendering, content management, and asset handling
 */
class LiveDesignerController extends Controller
{
    /**
     * Get complete page content for GrapesJS canvas
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getPageContent(Page $page): JsonResponse
    {
        try {
            // Load page with all necessary relationships
            $page->load([
                'template.theme',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);
            
            $theme = $page->template->theme ?? null;
            
            if (!$theme) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page has no associated theme'
                ], 400);
            }

            // Get services
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            $widgetService = app(\App\Services\WidgetService::class);
            $themeManager = app(\App\Services\ThemeManager::class);
            $menuService = app(\App\Services\MenuService::class);
            $universalStylingService = app(\App\Services\UniversalStylingService::class);
            
            // Ensure theme namespace is registered
            $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
            
            // Load theme assets
            $themeManager->loadThemeAssets($theme);
            
            // Generate full page HTML using frontend pipeline
            $fullPageHtml = $this->generateFullPageHtml($page, $templateRenderer, $menuService, $universalStylingService);
            
            // Collect all assets (theme + widget assets)
            $sections = [];
            foreach ($page->sections as $section) {
                $widgets = $widgetService->getWidgetsForSection($section->id);
                $sections[$section->templateSection->slug ?? 'section_' . $section->id] = [
                    'id' => $section->id,
                    'name' => $section->name,
                    'slug' => $section->templateSection->slug ?? 'section_' . $section->id,
                    'widgets' => $widgets
                ];
            }
            
            $allAssets = $widgetService->collectPageWidgetAssets($sections);
            $themeAssets = $this->collectThemeAssets($theme);
            
            // Merge assets
            $finalAssets = [
                'css' => array_unique(array_merge($themeAssets['css'], $allAssets['css'] ?? [])),
                'js' => array_unique(array_merge($themeAssets['js'], $allAssets['js'] ?? []))
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'html' => $fullPageHtml,
                    'assets' => $finalAssets,
                    'page_id' => $page->id,
                    'theme_name' => $theme->name,
                    'template_name' => $page->template->name
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading page content for Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load page content',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get iframe preview HTML for Live Designer
     * 
     * @param Page $page
     * @return \Illuminate\Http\Response
     */
    public function getIframePreview(Page $page)
    {
        try {
            // Load page with all necessary relationships
            $page->load([
                'template.theme',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);
            
            $theme = $page->template->theme ?? null;
            
            if (!$theme) {
                return response('<html><body><h1>Error: Page has no associated theme</h1></body></html>');
            }

            // Get services
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            $widgetService = app(\App\Services\WidgetService::class);
            $themeManager = app(\App\Services\ThemeManager::class);
            $menuService = app(\App\Services\MenuService::class);
            $universalStylingService = app(\App\Services\UniversalStylingService::class);
            
            // Ensure theme namespace is registered
            $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
            
            // Load theme assets
            $themeManager->loadThemeAssets($theme);
            
            // Generate full page HTML using frontend pipeline
            $fullPageHtml = $this->generateFullPageHtml($page, $templateRenderer, $menuService, $universalStylingService);
            
            // Collect all assets (theme + widget assets)
            $sections = [];
            foreach ($page->sections as $section) {
                $widgets = $widgetService->getWidgetsForSection($section->id);
                $sections[$section->templateSection->slug ?? 'section_' . $section->id] = [
                    'id' => $section->id,
                    'name' => $section->name,
                    'slug' => $section->templateSection->slug ?? 'section_' . $section->id,
                    'widgets' => $widgets
                ];
            }
            
            $allAssets = $widgetService->collectPageWidgetAssets($sections);
            $themeAssets = $this->collectThemeAssets($theme);
            
            // Merge assets
            $finalAssets = [
                'css' => array_unique(array_merge($themeAssets['css'], $allAssets['css'] ?? [])),
                'js' => array_unique(array_merge($themeAssets['js'], $allAssets['js'] ?? []))
            ];
            
            // Inject assets into the HTML
            $finalHtml = $this->injectAssetsIntoHtml($fullPageHtml, $finalAssets);
            
            return response($finalHtml)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            Log::error('Error showing Live Designer iframe preview: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response('<html><body><h1>Error loading Live Designer preview</h1><p>' . $e->getMessage() . '</p></body></html>');
        }
    }

    /**
     * Get theme assets for Live Designer
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getAssets(Page $page): JsonResponse
    {
        try {
            $page->load(['template.theme']);
            $theme = $page->template->theme ?? null;
            
            if (!$theme) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page has no associated theme'
                ], 400);
            }

            $themeAssets = $this->collectThemeAssets($theme);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'theme_assets' => $themeAssets,
                    'theme_name' => $theme->name,
                    'theme_path' => $theme->path
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading assets for Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load assets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate full page HTML using TemplateRenderer pipeline (adapted from PageSectionController)
     */
    protected function generateFullPageHtml($page, $templateRenderer, $menuService, $universalStylingService)
    {
        try {
            // Get all active menus (like frontend pipeline)
            $menus = $menuService->getAllActiveMenus($page->id, $page->template->id);
            
            // Prepare view data exactly like TemplateRenderer does
            $viewData = [
                'page' => $page,
                'template' => $page->template,
                'theme' => $page->template->theme,
                'menus' => $menus,
                'universalStyling' => $universalStylingService,
                'preview_mode' => true,
                'live_designer_mode' => true // Distinguish from page builder mode
            ];
            
            // Use TemplateRenderer to render the complete page
            $fullPageHtml = $templateRenderer->renderPage($page, $viewData);
            
            return $fullPageHtml;
            
        } catch (\Exception $e) {
            Log::warning('Could not generate full page HTML using frontend pipeline: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to basic wrapper
            return $this->getBasicCanvasWrapper($page->template->theme);
        }
    }

    /**
     * Collect theme assets (adapted from PageSectionController)
     */
    protected function collectThemeAssets($theme)
    {
        $assets = [
            'css' => [],
            'js' => []
        ];
        
        if (isset($theme->css) && is_array($theme->css)) {
            $assets['css'] = $theme->css;
        }
        
        if (isset($theme->js) && is_array($theme->js)) {
            $assets['js'] = $theme->js;
        }
        
        return $assets;
    }

    /**
     * Inject CSS and JS assets into HTML (adapted from PageSectionController)
     */
    protected function injectAssetsIntoHtml($html, $assets)
    {
        // Inject CSS assets
        if (!empty($assets['css'])) {
            $cssLinks = '';
            foreach ($assets['css'] as $cssFile) {
                $cssLinks .= '<link rel="stylesheet" href="' . asset($cssFile) . '">' . "\n";
            }
            
            // Try to inject before closing head tag
            if (strpos($html, '</head>') !== false) {
                $html = str_replace('</head>', $cssLinks . '</head>', $html);
            } else {
                // Fallback: prepend to body
                $html = $cssLinks . $html;
            }
        }
        
        // Inject JS assets
        if (!empty($assets['js'])) {
            $jsScripts = '';
            foreach ($assets['js'] as $jsFile) {
                $jsScripts .= '<script src="' . asset($jsFile) . '"></script>' . "\n";
            }
            
            // Try to inject before closing body tag
            if (strpos($html, '</body>') !== false) {
                $html = str_replace('</body>', $jsScripts . '</body>', $html);
            } else {
                // Fallback: append to end
                $html .= $jsScripts;
            }
        }
        
        return $html;
    }

    /**
     * Get basic canvas wrapper as fallback
     */
    protected function getBasicCanvasWrapper($theme)
    {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Live Designer Preview</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px; 
                    background: #f8f9fa; 
                }
                .preview-container { 
                    background: white; 
                    padding: 20px; 
                    border-radius: 8px; 
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
                }
            </style>
        </head>
        <body>
            <div class="preview-container">
                <h1>Live Designer Preview</h1>
                <p>Theme: ' . ($theme->name ?? 'Unknown') . '</p>
                <p>This is a fallback preview. The full theme rendering is not available.</p>
            </div>
        </body>
        </html>';
    }
}
