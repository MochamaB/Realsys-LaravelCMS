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
    <meta name="csrf-token" content="' . csrf_token() . '">
    <title>Page Builder Preview - ' . e($page->title) . '</title>

    <!-- Theme Assets -->
    ' . $this->getThemeAssetsHtml($page) . '

    <!-- Page Builder Preview Assets -->
    ' . $previewAssets . '

    <!-- Page Structure Data -->
    ' . $this->getPageStructureScript($page) . '
</head>
<body>
    <!-- Page Container with Page Builder data attributes and GridStack -->
    <div data-pagebuilder-page="' . $page->id . '"
         data-page-title="' . e($page->title) . '"
         data-page-template="' . e($page->template->name ?? 'Unknown Template') . '"
         data-preview-type="page"
         class="pagebuilder-preview-container grid-stack"
         id="pageGrid">
        ' . $this->generatePageToolbar($page) . '
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
        // Add Page Builder specific data attributes and always-visible toolbars
        $patterns = [
            // Wrap sections in grid-stack-item (preserve all existing attributes including GridStack data)
            '/<section([^>]*data-section-id="([^"]*)"[^>]*)>/i' => function($matches) {
                $sectionAttributes = $matches[1];
                $sectionId = $matches[2];

                // Extract GridStack attributes from the section for the wrapper
                $gsX = $this->extractAttribute($sectionAttributes, 'data-gs-x') ?? '0';
                $gsY = $this->extractAttribute($sectionAttributes, 'data-gs-y') ?? '0';
                $gsW = $this->extractAttribute($sectionAttributes, 'data-gs-w') ?? '12';
                $gsH = $this->extractAttribute($sectionAttributes, 'data-gs-h') ?? '1';
                $gsId = $this->extractAttribute($sectionAttributes, 'data-gs-id') ?? "section_{$sectionId}";

                return '<div class="grid-stack-item"
                             data-gs-x="' . $gsX . '"
                             data-gs-y="' . $gsY . '"
                             data-gs-w="' . $gsW . '"
                             data-gs-h="' . $gsH . '"
                             data-gs-id="' . $gsId . '">
                    <div class="grid-stack-item-content">
                        <section' . $sectionAttributes . ' data-pagebuilder-section="' . $sectionId . '" data-preview-type="section">' . $this->generateSectionToolbar($sectionId);
            },

            // Close section grid-stack wrapper
            '/<\/section>/i' => '</section>
                    </div>
                </div>',

            // Add preview attributes and toolbar to existing widget elements
            '/<div([^>]*data-page-section-widget-id="([^"]*)"[^>]*)>/i' => function($matches) {
                $widgetId = $matches[2];
                return '<div' . $matches[1] . ' data-pagebuilder-widget="' . $widgetId . '" data-preview-type="widget" style="position: relative;">' . $this->generateWidgetToolbar($widgetId);
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
     * Generate always-visible page toolbar HTML
     *
     * @param Page $page
     * @return string
     */
    private function generatePageToolbar(Page $page): string
{
    return '
    <div class="pagebuilder-toolbar pagebuilder-page-toolbar" data-component-type="page" data-page-id="' . $page->id . '">
        <div class="toolbar-content">
            <div class="toolbar-left">
                <div class="component-info">
                    <div class="component-icon">
                        <i class="ri-file-text-line"></i>
                    </div>
                    <div class="component-details">
                        <div class="component-name">Page • ' . e($page->title ?? 'Unknown Template') . '</div>
                    </div>
                </div>
            </div>
            <div class="toolbar-right">
                <div class="toolbar-actions">
                   
                    <button class="toolbar-btn" data-action="add-section" data-page-id="' . $page->id . '" title="Add Section">
                        <i class="ri-add-line"></i>
                        <div class="btn-tooltip">Add Section</div>
                    </button>
                    
                </div>
            </div>
        </div>
    </div>';
}

    /**
     * Generate always-visible section toolbar HTML
     *
     * @param string $sectionId
     * @return string
     */
    private function generateSectionToolbar(string $sectionId): string
    {
        return '
        <div class="pagebuilder-toolbar pagebuilder-section-toolbar" data-component-type="section" data-section-id="' . $sectionId . '">
            <div class="toolbar-content">
                <div class="toolbar-left">
                    <div class="component-info">
                        <div class="component-icon">
                            <i class="ri-layout-grid-line"></i>
                        </div>
                        <div class="component-details">
                            <div class="component-name">Section • ID: ' . $sectionId . '</div>
                           
                        </div>
                    </div>
                </div>
                <div class="toolbar-right">
                    <div class="toolbar-actions">
                        <button class="toolbar-btn" data-action="add-widget" data-section-id="' . $sectionId . '" title="Add Widget">
                            <i class="ri-add-line"></i>
                            <div class="btn-tooltip">Add Widget</div>
                        </button>
                       
                        <button class="toolbar-btn" data-action="duplicate-section" data-section-id="' . $sectionId . '" title="Duplicate Section">
                            <i class="ri-file-copy-line"></i>
                            <div class="btn-tooltip">Duplicate</div>
                        </button>
                         <button class="toolbar-btn" data-action="move-section" data-section-id="' . $sectionId . '" title="Move Section">
                            <i class="ri-drag-move-2-line"></i>
                            <div class="btn-tooltip">Move</div>
                        </button>
                        <button class="toolbar-btn toolbar-btn-danger" data-action="delete-section" data-section-id="' . $sectionId . '" title="Delete Section">
                            <i class="ri-delete-bin-line"></i>
                            <div class="btn-tooltip">Delete</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * Generate always-visible widget toolbar HTML
     *
     * @param string $widgetId
     * @return string
     */
    private function generateWidgetToolbar(string $widgetId): string
{
    return '
    <div class="pagebuilder-toolbar pagebuilder-widget-toolbar" data-component-type="widget" data-widget-id="' . $widgetId . '">
        <div class="toolbar-content">
            <div class="toolbar-left">
                <div class="component-info">
                    <div class="component-icon">
                        <i class="ri-puzzle-line"></i>
                    </div>
                    <div class="component-details">
                        <div class="component-name">Widget • ID: ' . $widgetId . '</div>
                    </div>
                </div>
            </div>
            <div class="toolbar-right">
                <div class="toolbar-actions">
                    
                    <button class="toolbar-btn" data-action="move-widget" data-widget-id="' . $widgetId . '" title="Move Widget">
                        <i class="ri-drag-move-2-line"></i>
                        <div class="btn-tooltip">Move</div>
                    </button>
                    <button class="toolbar-btn" data-action="duplicate-widget" data-widget-id="' . $widgetId . '" title="Duplicate Widget">
                        <i class="ri-file-copy-line"></i>
                        <div class="btn-tooltip">Duplicate</div>
                    </button>
                    <button class="toolbar-btn toolbar-btn-danger" data-action="delete-widget" data-widget-id="' . $widgetId . '" title="Delete Widget">
                        <i class="ri-delete-bin-line"></i>
                        <div class="btn-tooltip">Delete</div>
                    </button>
                </div>
            </div>
        </div>
    </div>';
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
    <!-- GridStack CSS & JS (Local Assets) -->
    <link rel="stylesheet" href="' . asset('assets/admin/libs/gridstack/dist/gridstack.min.css') . '">
    <script src="' . asset('assets/admin/libs/gridstack/dist/gridstack-all.js') . '"></script>

    <!-- Page Builder Preview Helper CSS -->
    <link rel="stylesheet" href="' . asset('assets/admin/css/page-builder/pagebuilder-preview-helper.css') . '">
    <link href="' . asset('assets/admin/css/icons.min.css') . '" rel="stylesheet" type="text/css" />

    <!-- Page Builder Communication System -->
    <script src="' . asset('assets/admin/js/page-builder/iframe-communicator.js') . '"></script>
    <!-- Page Builder Toolbar Handler -->
    <script src="' . asset('assets/admin/js/page-builder/toolbar-handler.js') . '"></script>
    <!-- Page Builder GridStack JS -->
    <script src="' . asset('assets/admin/js/page-builder/pagebuilder-gridstack.js') . '"></script>

    <!-- Initialize Page Builder Iframe Systems -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("🎯 Initializing Page Builder iframe systems...");

            // Initialize iframe communicator
            if (window.PageBuilderIframeCommunicator) {
                window.pageBuilderIframeCommunicator = new PageBuilderIframeCommunicator();
                console.log("✅ Page Builder iframe communicator initialized");
            }

            // Initialize toolbar handler
            if (window.PageBuilderToolbarHandler) {
                window.pageBuilderToolbarHandler = new PageBuilderToolbarHandler();

                // Connect toolbar handler to communicator
                if (window.pageBuilderIframeCommunicator) {
                    window.pageBuilderToolbarHandler.setCommunicator(window.pageBuilderIframeCommunicator);
                    console.log("🔗 Toolbar handler connected to iframe communicator");
                }

                console.log("✅ Page Builder toolbar handler initialized");
            }

            console.log("✅ Page Builder iframe systems initialized");
        });
    </script>

    
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
     * Extract specific attribute value from attribute string
     * Helper method for regex pattern matching
     *
     * @param string $attributeString
     * @param string $attributeName
     * @return string|null
     */
    private function extractAttribute(string $attributeString, string $attributeName): ?string
    {
        $pattern = '/' . preg_quote($attributeName, '/') . '="([^"]*?)"/i';
        if (preg_match($pattern, $attributeString, $matches)) {
            return $matches[1];
        }
        return null;
    }

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