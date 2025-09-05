<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Models\ContentType;
use App\Models\TemplateSection;
use App\Services\TemplateRenderer;
use App\Services\PageSectionManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Page Builder API Controller - PHASE 1 REBUILD
 * 
 * Handles JSON API responses and iframe preview for Page Builder system.
 * Phase 1: Basic iframe preview functionality
 * 
 * Based on LivePreviewController pattern but with Page Builder specific assets
 */
class PageBuilderController extends Controller
{
    protected $templateRenderer;
    protected $pageSectionManager;

    public function __construct(TemplateRenderer $templateRenderer, PageSectionManager $pageSectionManager)
    {
        $this->templateRenderer = $templateRenderer;
        $this->pageSectionManager = $pageSectionManager;
    }

    // =====================================================================
    // PHASE 1: IFRAME PREVIEW API ENDPOINTS
    // =====================================================================

    /**
     * Get page preview iframe content for Page Builder
     * Adapted from LivePreviewController but with Page Builder specific assets
     * 
     * @param Page $page
     * @return \Illuminate\Http\Response
     */
    public function getPagePreviewIframe(Page $page)
    {
        // Load all necessary relationships for template rendering
        $page->load([
            'template.theme',
            'sections.templateSection',
            'sections.pageSectionWidgets.widget'
        ]);

        // Generate the complete HTML using existing template renderer
        $html = $this->generatePageBuilderPageHtml($page);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Get page structure for Page Builder sidebar
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

    // =====================================================================
    // PRIVATE HELPER METHODS FOR IFRAME GENERATION
    // =====================================================================

    /**
     * Generate full page HTML for Page Builder iframe
     * Based on LivePreviewController but with Page Builder specific assets
     * 
     * @param Page $page
     * @return string
     */
    private function generatePageBuilderPageHtml(Page $page): string
    {
        // Get the rendered page content using existing template system
        $pageContent = $this->templateRenderer->renderPage($page);
        
        // Inject preview data for component targeting
        $pageContent = $this->injectPageBuilderPreviewData($page, $pageContent);

        // Get Page Builder specific preview assets
        $previewAssets = $this->getPageBuilderPreviewAssets();

        // Build complete HTML with preview helpers
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder Preview - ' . e($page->title) . '</title>
    
    <!-- Theme Assets -->
    ' . $this->getThemeAssetsHtml($page) . '
    
    <!-- Page Builder Preview Assets -->
    ' . $previewAssets . '
    
    <!-- Page Structure Data -->
    ' . $this->getPageStructureScript($page) . '
</head>
<body>
    <!-- Page Container with Page Builder data attributes -->
    <div data-pagebuilder-page="' . $page->id . '" 
         data-page-title="' . e($page->title) . '" 
         data-page-template="' . e($page->template->name ?? 'Unknown Template') . '"
         data-preview-type="page"
         class="pagebuilder-preview-container">
        ' . $pageContent . '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Inject Page Builder specific preview data attributes
     * 
     * @param Page $page
     * @param string $html
     * @return string
     */
    private function injectPageBuilderPreviewData(Page $page, string $html): string
    {
        // Add Page Builder specific data attributes for component targeting
        $patterns = [
            // Add preview attributes to existing section elements
            '/<section([^>]*data-section-id="([^"]*)"[^>]*)>/i' => function($matches) {
                return '<section' . $matches[1] . ' data-pagebuilder-section="' . $matches[2] . '" data-preview-type="section">';
            },
            
            // Add preview attributes to existing widget elements  
            '/<div([^>]*data-page-section-widget-id="([^"]*)"[^>]*)>/i' => function($matches) {
                return '<div' . $matches[1] . ' data-pagebuilder-widget="' . $matches[2] . '" data-preview-type="widget" style="position: relative; cursor: pointer; outline: 1px dashed transparent;" onmouseover="this.style.outline=\'1px dashed #007bff\'" onmouseout="this.style.outline=\'1px dashed transparent\'">';
            }
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            if (is_callable($replacement)) {
                $html = preg_replace_callback($pattern, $replacement, $html);
            } else {
                $html = preg_replace($pattern, $replacement, $html);
            }
        }
        
        return $html;
    }

    /**
     * Get theme assets HTML for the page
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
     * Get Page Builder specific preview assets (separate from Live Designer)
     * 
     * @return string
     */
    private function getPageBuilderPreviewAssets(): string
    {
        return '
    <!-- Page Builder Preview Helper CSS -->
    <link rel="stylesheet" href="' . asset('assets/admin/css/page-builder/pagebuilder-preview-helper.css') . '">
    <link href="' . asset('assets/admin/css/icons.min.css') . '" rel="stylesheet" type="text/css" />
    
    <!-- Page Builder Preview Helper JS (separate from live-designer) -->
    <script src="' . asset('assets/admin/js/page-builder/pagebuilder-preview-helper.js') . '"></script>
    <script src="' . asset('assets/admin/js/page-builder/pagebuilder-page-preview.js') . '"></script>
    <script src="' . asset('assets/admin/js/page-builder/pagebuilder-section-preview.js') . '"></script>
    <script src="' . asset('assets/admin/js/page-builder/pagebuilder-widget-preview.js') . '"></script>

    
    <!-- Page Builder Preview Styling -->
    <style>
        body {
            padding: 15px !important;
            margin: 0 !important;
            min-height: calc(100vh - 30px) !important;
            background-color: #f8f9fa !important;
        }
        
        /* Page Builder specific component targeting */
        [data-pagebuilder-widget] {
            position: relative;
            transition: outline 0.2s ease;
        }
        
        [data-pagebuilder-section] {
            position: relative;
            min-height: 50px;
            transition: outline 0.2s ease;
        }
        
        /* Ensure proper spacing for sections */
        section[data-section-id] {
            margin: 8px 0;
        }
        
        section[data-section-id]:first-child {
            margin-top: 0;
        }
        
        section[data-section-id]:last-child {
            margin-bottom: 0;
        }
    </style>';
    }

    /**
     * Generate JavaScript with page structure data
     * 
     * @param Page $page
     * @return string
     */
    private function getPageStructureScript(Page $page): string
    {
        // Use the existing page structure logic
        $structureResponse = $this->getPageStructure($page);
        $structureData = $structureResponse->getData(true);
        
        if ($structureData['success']) {
            $pageStructure = $structureData['data'];
            
            return '<script>
                window.pageBuilderPageStructure = ' . json_encode($pageStructure) . ';
                console.log("🏗️ Page Builder page structure loaded:", window.pageBuilderPageStructure);
            </script>';
        }
        
        return '<script>window.pageBuilderPageStructure = null;</script>';
    }

    // =====================================================================
    // LEGACY METHODS (DISABLED FOR NOW - PHASE 2+)
    // =====================================================================

    /**
     * LEGACY - Will be re-enabled in Phase 2+
     */
    private function legacyDisabled()
    {
        return response()->json([
            'success' => false,
            'message' => 'This endpoint will be re-enabled in Phase 2+',
            'current_phase' => 'Phase 1 - Iframe Preview Only'
        ], 503);
    }

    // Legacy methods - disabled for now
    public function getRenderedPage(Page $page): JsonResponse { return $this->legacyDisabled(); }
    public function getAvailableWidgets() { return $this->legacyDisabled(); }
    public function getWidgetContentTypes() { return $this->legacyDisabled(); }
    public function getContentTypeItems() { return $this->legacyDisabled(); }
    public function queryContentItems() { return $this->legacyDisabled(); }
    public function getWidgetFieldDefinitions() { return $this->legacyDisabled(); }
    public function getSectionConfiguration() { return $this->legacyDisabled(); }
    public function updateSectionConfiguration() { return $this->legacyDisabled(); }
    public function deleteSection() { return $this->legacyDisabled(); }
    public function createSection() { return $this->legacyDisabled(); }
    public function updateSectionPosition() { return $this->legacyDisabled(); }
    public function previewWidget() { return $this->legacyDisabled(); }
    public function getAvailableSectionTemplates() { return $this->legacyDisabled(); }
    public function addWidget() { return $this->legacyDisabled(); }
    public function getThemeAssets() { return $this->legacyDisabled(); }
    public function createDefaultContentItem() { return $this->legacyDisabled(); }
}