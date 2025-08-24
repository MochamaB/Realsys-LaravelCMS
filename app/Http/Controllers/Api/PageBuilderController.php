<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Page Builder API Controller
 * 
 * Based on LivePreviewController pattern for consistent rendering.
 * Handles JSON API responses for the page builder system.
 */
class PageBuilderController extends Controller
{
    protected $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * Get rendered page content for Page Builder (JSON response)
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getRenderedPage(Page $page): JsonResponse
    {
        try {
            // Load all necessary relationships for template rendering (same as LivePreview)
            $page->load([
                'template.theme',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);

            // Get page structure (same as getPageStructure method)
            $structure = $this->getPageStructure($page);
            $structureData = $structure->getData(true);
            
            if (!$structureData['success']) {
                throw new \Exception('Failed to load page structure');
            }
            
            $pageStructure = $structureData['data'];
            
            // Get theme assets for page (as array for JSON response)
            $theme = $page->template->theme;
            $themeAssets = [];
            if ($theme) {
                $themeAssets = [
                    'css' => $theme->css_files ?? [],
                    'js' => $theme->js_files ?? [],
                    'base_path' => "/themes/{$theme->slug}"
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'page' => $pageStructure['page'],
                    'sections' => $pageStructure['sections'],
                    'theme_assets' => $themeAssets,
                    'rendered_html' => $this->generateFullPageHtml($page)  // Also include full HTML for iframe
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to render page: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to render page: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rendered page iframe content for Page Builder
     * Based on LivePreviewController::getPreviewIframe
     * 
     * @param Page $page
     * @return \Illuminate\Http\Response
     */
    public function getRenderedPageIframe(Page $page)
    {
        // Load all necessary relationships for template rendering
        $page->load([
            'template.theme',
            'sections.templateSection',
            'sections.pageSectionWidgets.widget'
        ]);

        // Generate the complete HTML using existing template renderer
        $html = $this->generateFullPageHtml($page);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Get page structure for Page Builder sidebar
     * Based on LivePreviewController::getPageStructure
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getPageStructure(Page $page): JsonResponse
    {
        try {
            $page->load([
                'template',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);

            $structure = [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'template' => $page->template->name ?? 'Unknown Template'
                ],
                'sections' => $page->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'name' => $section->templateSection->name ?? 'Section ' . $section->id,
                        'widgets' => $section->pageSectionWidgets->map(function ($psw) {
                            return [
                                'id' => $psw->id,
                                'widget_id' => $psw->widget_id,
                                'name' => $psw->widget->name ?? 'Unknown Widget',
                                'icon' => $psw->widget->icon ?? 'ri-puzzle-line',
                                'position' => $psw->position
                            ];
                        })->sortBy('position')->values()
                    ];
                })->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $structure
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to load page structure: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load page structure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available widgets for the widget library
     * Exact copy from LivePreviewController::getAvailableWidgets
     * 
     * @return JsonResponse
     */
    public function getAvailableWidgets(): JsonResponse
    {
        $widgets = Widget::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'icon', 'category']);

        $groupedWidgets = $widgets->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => [
                'widgets' => $groupedWidgets
            ]
        ]);
    }

    /**
     * Add new widget to a section
     * Exact copy from LivePreviewController::addWidget
     * 
     * @param PageSection $section
     * @param Request $request
     * @return JsonResponse
     */
    public function addWidget(PageSection $section, Request $request): JsonResponse
    {
        $request->validate([
            'widget_id' => 'required|exists:widgets,id'
        ]);

        try {
            $widget = Widget::findOrFail($request->widget_id);

            // Get next position in section
            $nextPosition = $section->pageSectionWidgets()->max('position') + 1;

            // Create new widget instance
            $widgetInstance = PageSectionWidget::create([
                'page_section_id' => $section->id,
                'widget_id' => $widget->id,
                'position' => $nextPosition,
                'settings' => $widget->default_settings ?? [],
                'content_query' => []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Widget added successfully',
                'data' => [
                    'widget_instance_id' => $widgetInstance->id,
                    'widget_name' => $widget->name,
                    'refresh_preview' => true,
                    'refresh_structure' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add widget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme assets for API endpoint
     * 
     * @return JsonResponse
     */
    public function getThemeAssets(): JsonResponse
    {
        try {
            // Get the active theme from a sample page to determine current theme
            $samplePage = \App\Models\Page::with('template.theme')->first();
            
            if ($samplePage && $samplePage->template && $samplePage->template->theme) {
                $theme = $samplePage->template->theme;
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'name' => $theme->name,
                        'slug' => $theme->slug,
                        'base_path' => "/themes/{$theme->slug}",
                        'css' => $theme->css_files ?? [],
                        'js' => $theme->js_files ?? []
                    ]
                ]);
            }
            
            // Fallback to defaults
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => 'Default Theme',
                    'slug' => 'default',
                    'base_path' => '/themes/default',
                    'css' => [],
                    'js' => []
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading theme assets: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => 'Default Theme',
                    'slug' => 'default',
                    'base_path' => '/themes/default',
                    'css' => [],
                    'js' => []
                ]
            ]);
        }
    }

    /**
     * Generate full page HTML for Page Builder
     * Based on LivePreviewController::generateFullPageHtml
     * 
     * @param Page $page
     * @return string
     */
    private function generateFullPageHtml(Page $page): string
    {
        // Get the rendered page content using existing template system
        $pageContent = $this->templateRenderer->renderPage($page);
        
        // Inject preview data using the existing page structure data
        $pageContent = $this->injectPreviewDataFromStructure($page, $pageContent);

        // Inject preview helper assets
        $previewAssets = $this->getPreviewAssets();

        // Build complete HTML with preview helpers
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder - ' . e($page->title) . '</title>
    
    <!-- Theme Assets -->
    ' . $this->getThemeAssetsHtml($page) . '
    
    <!-- Preview Helper Assets -->
    ' . $previewAssets . '
    
    <!-- Preview Structure Data -->
    ' . $this->getPreviewStructureScript($page) . '
</head>
<body>
    ' . $pageContent . '
</body>
</html>';

        return $html;
    }

    /**
     * Inject preview data using simple approach that leverages existing page structure
     * Exact copy from LivePreviewController::injectPreviewDataFromStructure
     * 
     * @param Page $page
     * @param string $html
     * @return string
     */
    private function injectPreviewDataFromStructure(Page $page, string $html): string
    {
        // The universal components already generate the correct data attributes!
        // We just need to add minimal preview-specific attributes for the JavaScript
        
        $patterns = [
            // Add preview attributes to existing section elements
            '/<section([^>]*data-section-id="[^"]*"[^>]*)>/i' => '<section$1 data-preview-type="section">',
            
            // Add preview attributes to existing widget elements  
            '/<div([^>]*data-page-section-widget-id="[^"]*"[^>]*)>/i' => '<div$1 data-preview-type="widget" style="position: relative; cursor: pointer; outline: 1px dashed transparent;" onmouseover="this.style.outline=\'1px dashed #007bff\'" onmouseout="this.style.outline=\'1px dashed transparent\'">',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }
        
        return $html;
    }
    
    /**
     * Generate JavaScript that contains page structure data for preview
     * Exact copy from LivePreviewController::getPreviewStructureScript
     * 
     * @param Page $page
     * @return string
     */
    private function getPreviewStructureScript(Page $page): string
    {
        // Use the existing page structure logic
        $structureResponse = $this->getPageStructure($page);
        $structureData = $structureResponse->getData(true);
        
        if ($structureData['success']) {
            $pageStructure = $structureData['data'];
            
            return '<script>
                window.previewPageStructure = ' . json_encode($pageStructure) . ';
                console.log("🏗️ Page Builder page structure loaded:", window.previewPageStructure);
            </script>';
        }
        
        return '<script>window.previewPageStructure = null;</script>';
    }

    /**
     * Get theme assets for the page
     * Exact copy from LivePreviewController::getThemeAssets
     * 
     * @param Page $page
     * @return string
     */
    private function getThemeAssetsHtml(Page $page): string
    {
        $theme = $page->template->theme;
        $assets = '';

        if ($theme) {
            // Add theme CSS
            if ($theme->css_files) {
                foreach ($theme->css_files as $cssFile) {
                    $assets .= '<link rel="stylesheet" href="' . asset($cssFile) . '">' . "\n    ";
                }
            }

            // Add theme JS
            if ($theme->js_files) {
                foreach ($theme->js_files as $jsFile) {
                    $assets .= '<script src="' . asset($jsFile) . '"></script>' . "\n    ";
                }
            }
        }

        return $assets;
    }

    /**
     * Get preview helper assets
     * Exact copy from LivePreviewController::getPreviewAssets
     * 
     * @return string
     */
    private function getPreviewAssets(): string
    {
        return '
    <!-- Preview Helper CSS -->
    <link rel="stylesheet" href="' . asset('assets/admin/css/live-designer/preview-helpers.css') . '">
    
    <!-- Preview Helper JS -->
    <script src="' . asset('assets/admin/js/live-designer/preview-helpers.js') . '"></script>
    
    <!-- Internal Preview Spacing -->
    <style>
        body {
            padding: 15px !important;
            margin: 0 !important;
            min-height: calc(100vh - 30px) !important;
        }
        
        /* Ensure proper spacing for section outlines */
        section[data-section-id] {
            margin: 8px 0;
        }
        
        /* First and last section spacing */
        section[data-section-id]:first-child {
            margin-top: 0;
        }
        
        section[data-section-id]:last-child {
            margin-bottom: 0;
        }
    </style>';
    }

}