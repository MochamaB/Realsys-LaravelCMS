<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Template;
use App\Models\TemplateSection;
use App\Services\SectionTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSectionController extends Controller
{
    protected $sectionTemplateService;

    public function __construct(SectionTemplateService $sectionTemplateService)
    {
        $this->sectionTemplateService = $sectionTemplateService;
    }

    /**
     * List all sections for a page with GridStack positioning data.
     */

     public function getTemplateSections(Page $page)
     {
         $templateSections = $page->templateSections()->orderBy('position')->get();
     
         return response()->json([
             'success' => true,
             'data' => $templateSections
         ]);
     }
    /**
     * Display a listing of the sections for a page.
     */
    public function index(Page $page)
    {
        try {
            // Check if enhanced theme preview is requested
            $withThemeContext = request()->get('with_theme_context', false);
            
            if ($withThemeContext) {
                return $this->indexWithThemeContext($page);
            }
            
            // Original functionality for backward compatibility
            $sections = $page->sections()
                ->with('templateSection') // Load the template section relationship
                ->orderBy('position')
                ->get()
                ->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'page_id' => $section->page_id,
                        'template_section_id' => $section->template_section_id,
                        'position' => $section->position,
                        'grid_x' => $section->grid_x,
                        'grid_y' => $section->grid_y,
                        'grid_w' => $section->grid_w,
                        'grid_h' => $section->grid_h,
                        'grid_id' => $section->grid_id,
                        'grid_config' => $section->grid_config,
                        'allows_widgets' => $section->allows_widgets,
                        'widget_types' => $section->widget_types,
                        'css_classes' => $section->css_classes,
                        'background_color' => $section->background_color,
                        'padding' => $section->padding,
                        'margin' => $section->margin,
                        'locked_position' => $section->locked_position,
                        'resize_handles' => $section->resize_handles,
                        'column_span_override' => $section->column_span_override,
                        'column_offset_override' => $section->column_offset_override,
                        // Include template section data
                        'template_section' => $section->templateSection ? [
                            'id' => $section->templateSection->id,
                            'name' => $section->templateSection->name,
                            'section_type' => $section->templateSection->section_type,
                            'column_layout' => $section->templateSection->column_layout,
                            'description' => $section->templateSection->description
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sections
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading page sections: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load page sections',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enhanced index method with theme context for GridStack builder - Hybrid Approach
     * Provides both full theme rendering and GridStack manipulation capabilities
     */
    public function indexWithThemeContext(Page $page)
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
            
            // Get services (using the same ones as frontend pipeline)
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            $widgetService = app(\App\Services\WidgetService::class);
            $themeManager = app(\App\Services\ThemeManager::class);
            $menuService = app(\App\Services\MenuService::class);
            $universalStylingService = app(\App\Services\UniversalStylingService::class);
            
            // Ensure theme namespace is registered
            $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
            
            // Load theme assets
            $themeManager->loadThemeAssets($theme);
            
            // HYBRID APPROACH: Generate full page HTML using TemplateRenderer pipeline
            $fullPageHtml = $this->generateFullPageHtml($page, $templateRenderer, $menuService, $universalStylingService);
            
            // Process sections with enhanced data for GridStack
            $sectionsData = [];
            $allAssets = ['css' => [], 'js' => []];
            
            foreach ($page->sections as $section) {
                $sectionData = [
                    'id' => $section->id,
                    'page_id' => $section->page_id,
                    'template_section_id' => $section->template_section_id,
                    'position' => $section->position,
                    'grid_x' => $section->grid_x,
                    'grid_y' => $section->grid_y,
                    'grid_w' => $section->grid_w,
                    'grid_h' => $section->grid_h,
                    'grid_id' => $section->grid_id,
                    'grid_config' => $section->grid_config,
                    'allows_widgets' => $section->allows_widgets,
                    'widget_types' => $section->widget_types,
                    'css_classes' => $section->css_classes,
                    'background_color' => $section->background_color,
                    'padding' => $section->padding,
                    'margin' => $section->margin,
                    'locked_position' => $section->locked_position,
                    'resize_handles' => $section->resize_handles,
                    'column_span_override' => $section->column_span_override,
                    'column_offset_override' => $section->column_offset_override,
                    'template_section' => $section->templateSection ? [
                        'id' => $section->templateSection->id,
                        'name' => $section->templateSection->name,
                        'section_type' => $section->templateSection->section_type,
                        'column_layout' => $section->templateSection->column_layout,
                        'description' => $section->templateSection->description
                    ] : null,
                    'widgets' => [],
                    'rendered_html' => null,
                    'section_assets' => ['css' => [], 'js' => []]
                ];
                
                // Process widgets in this section
                foreach ($section->pageSectionWidgets as $pageSectionWidget) {
                    $widget = $pageSectionWidget->widget;
                    
                    if (!$widget) continue;
                    
                    // Get widget field values
                    $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
                    
                    // Render widget with theme context
                    $widgetHtml = $this->renderWidgetWithThemeContext($widget, $fieldValues, $theme, $pageSectionWidget);
                    
                    // Collect widget assets
                    $widgetAssets = $widgetService->collectWidgetAssets($widget);
                    
                    $widgetData = [
                        'id' => $pageSectionWidget->id,
                        'widget_id' => $widget->id,
                        'widget_name' => $widget->name,
                        'widget_slug' => $widget->slug,
                        'widget_description' => $widget->description,
                        'position' => $pageSectionWidget->position,
                        'grid_x' => $pageSectionWidget->grid_x ?? 0,
                        'grid_y' => $pageSectionWidget->grid_y ?? 0,
                        'grid_w' => $pageSectionWidget->grid_w ?? 6,
                        'grid_h' => $pageSectionWidget->grid_h ?? 3,
                        'grid_id' => $pageSectionWidget->grid_id ?? "widget-{$pageSectionWidget->id}",
                        'column_position' => $pageSectionWidget->column_position,
                        'min_width' => $pageSectionWidget->min_width,
                        'max_width' => $pageSectionWidget->max_width,
                        'locked_position' => $pageSectionWidget->locked_position ?? false,
                        'resize_handles' => $pageSectionWidget->resize_handles ?? ['se', 'sw'],
                        'css_classes' => $pageSectionWidget->css_classes,
                        'padding' => $pageSectionWidget->padding,
                        'margin' => $pageSectionWidget->margin,
                        'min_height' => $pageSectionWidget->min_height,
                        'max_height' => $pageSectionWidget->max_height,
                        'settings' => $pageSectionWidget->settings,
                        'content_query' => $pageSectionWidget->content_query,
                        'field_values' => $fieldValues,
                        'rendered_html' => $widgetHtml,
                        'assets' => $widgetAssets
                    ];
                    
                    $sectionData['widgets'][] = $widgetData;
                    
                    // Collect widget assets
                    if (isset($widgetAssets['css'])) {
                        $sectionData['section_assets']['css'] = array_merge($sectionData['section_assets']['css'], $widgetAssets['css']);
                        $allAssets['css'] = array_merge($allAssets['css'], $widgetAssets['css']);
                    }
                    if (isset($widgetAssets['js'])) {
                        $sectionData['section_assets']['js'] = array_merge($sectionData['section_assets']['js'], $widgetAssets['js']);
                        $allAssets['js'] = array_merge($allAssets['js'], $widgetAssets['js']);
                    }
                }
                
                // Render entire section with theme context
                try {
                    $sectionData['rendered_html'] = $this->renderSectionWithThemeContext($section, $theme);
                } catch (\Exception $e) {
                    \Log::warning('Could not render section HTML: ' . $e->getMessage(), [
                        'section_id' => $section->id
                    ]);
                    $sectionData['rendered_html'] = '<div class="section-render-error">Section could not be rendered</div>';
                }
                
                // Remove duplicates from section assets
                $sectionData['section_assets']['css'] = array_unique($sectionData['section_assets']['css']);
                $sectionData['section_assets']['js'] = array_unique($sectionData['section_assets']['js']);
                
                $sectionsData[] = $sectionData;
            }
            
            // Collect theme assets using WidgetService method (like frontend pipeline)
            $pageWidgetAssets = $widgetService->collectPageWidgetAssets($sectionsData);
            $themeAssets = $this->collectThemeAssets($theme);
            
            // Merge assets
            $allAssets['css'] = array_merge($themeAssets['css'], $pageWidgetAssets['css'] ?? [], $allAssets['css']);
            $allAssets['js'] = array_merge($themeAssets['js'], $pageWidgetAssets['js'] ?? [], $allAssets['js']);
            
            // Remove duplicates from all assets
            $allAssets['css'] = array_unique($allAssets['css']);
            $allAssets['js'] = array_unique($allAssets['js']);
            
            // Generate theme canvas wrapper HTML for GridStack
            $canvasWrapperHtml = $this->generateThemeCanvasWrapper($theme, $page->template, $page);
            
            return response()->json([
                'success' => true,
                'data' => $sectionsData,
                'full_page_html' => $fullPageHtml, // Complete theme-rendered page
                'theme' => [
                    'id' => $theme->id,
                    'name' => $theme->name,
                    'slug' => $theme->slug,
                    'assets' => $themeAssets,
                    'canvas_wrapper' => $canvasWrapperHtml
                ],
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'template_id' => $page->template_id
                ],
                'assets' => $allAssets,
                'grid_config' => [
                    'columns' => 12,
                    'cellHeight' => 80,
                    'margin' => '10px',
                    'float' => false,
                    'animate' => true
                ],
                'metadata' => [
                    'sections_count' => count($sectionsData),
                    'total_widgets' => collect($sectionsData)->sum(fn($section) => count($section['widgets'])),
                    'render_time' => microtime(true) - LARAVEL_START,
                    'theme_context' => true,
                    'hybrid_mode' => true,
                    'frontend_pipeline_used' => true
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading page sections with theme context: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load page sections with theme context',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render widget with theme context for GridStack
     */
    protected function renderWidgetWithThemeContext($widget, $fieldValues, $theme, $pageSectionWidget = null)
    {
        try {
            $widgetService = app(\App\Services\WidgetService::class);
            
            // Get widget view path
            $viewPath = $widgetService->resolveWidgetViewPath($widget);
            
            // Prepare view data
            $viewData = [
                'widget' => $widget,
                'fields' => $fieldValues,
                'settings' => $pageSectionWidget->settings ?? [],
                'theme' => $theme,
                'pageBuilderMode' => true,
                'previewMode' => true
            ];
            
            // Render widget view
            return view($viewPath, $viewData)->render();
            
        } catch (\Exception $e) {
            \Log::warning('Could not render widget: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug
            ]);
            
            return '<div class="widget-render-error">Widget "' . $widget->name . '" could not be rendered: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Render section with theme context
     */
    protected function renderSectionWithThemeContext($section, $theme)
    {
        try {
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            $widgetService = app(\App\Services\WidgetService::class);
            
            // Get widgets for this section
            $widgetData = $widgetService->getWidgetsForSection($section->id);
            
            // Prepare section data for rendering
            $sectionData = [
                'pageSection' => $section,
                'section' => $section->templateSection,
                'widgets' => $widgetData,
                'theme' => $theme,
                'universalStyling' => app(\App\Services\UniversalStylingService::class),
                'pageBuilderMode' => true,
                'previewMode' => true
            ];
            
            // Try to render using theme section template
            $sectionSlug = $section->templateSection->slug ?? 'default';
            
            return $templateRenderer->renderSection($sectionSlug, $sectionData);
            
        } catch (\Exception $e) {
            \Log::warning('Could not render section: ' . $e->getMessage(), [
                'section_id' => $section->id
            ]);
            
            throw $e;
        }
    }

    /**
     * Collect theme assets
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
     * Generate theme canvas wrapper HTML for GridStack page builder using actual theme layout
     */
    protected function generateThemeCanvasWrapper($theme, $template, $page)
    {
        try {
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            
            // Ensure theme namespace is registered
            $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
            
            // Prepare layout data with all necessary variables for theme layout
            $layoutData = [
                'page' => $page,
                'template' => $template,
                'theme' => $theme,
                'preview_mode' => true,
                'canvas_mode' => true,
                'menus' => [], // Empty menus for preview
                'widgetAssets' => ['css' => [], 'js' => []], // Will be populated later
            ];

            // Try to use the main theme layout first
            $themeLayoutView = "{$theme->slug}::layouts.theme";
            
            if (view()->exists($themeLayoutView)) {
                // Create a custom view that extends the theme layout
                $customLayoutContent = $this->renderThemeLayoutWithContent($theme, $layoutData);
                return $customLayoutContent;
            }

            // Try alternative layout names
            $alternativeLayouts = [
                "{$theme->slug}::layouts.main",
                "{$theme->slug}::layouts.master",
                "{$theme->slug}::layouts.app"
            ];
            
            foreach ($alternativeLayouts as $layoutView) {
                if (view()->exists($layoutView)) {
                    $layoutContent = view($layoutView, array_merge($layoutData, [
                        'content' => '<div data-canvas-content><!-- Page content will be inserted here --></div>'
                    ]))->render();
                    
                    return $layoutContent;
                }
            }

            // Ultimate fallback to basic wrapper
            \Log::warning("No theme layout found for theme: {$theme->slug}, using fallback");
            return $this->getBasicCanvasWrapper($theme);

        } catch (\Exception $e) {
            \Log::warning('Could not render theme canvas wrapper: ' . $e->getMessage(), [
                'theme_slug' => $theme->slug,
                'error' => $e->getMessage()
            ]);
            return $this->getBasicCanvasWrapper($theme);
        }
    }

    /**
     * Get basic canvas wrapper as fallback
     */
    protected function getBasicCanvasWrapper($theme)
    {
        return "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$theme->name} Theme Preview</title>
    <!-- Bootstrap CSS -->
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
    <!-- Theme CSS -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .theme-layout-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .theme-header {
            background: #343a40;
            color: white;
            padding: 1rem 0;
            border-bottom: 3px solid #007bff;
        }
        .theme-content-area {
            flex: 1;
            padding: 2rem 0;
            background: #f8f9fa;
        }
        .theme-footer {
            background: #212529;
            color: #6c757d;
            padding: 1.5rem 0;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .theme-title {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }
        /* Page Content Styles */
        [data-canvas-content] {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 400px;
        }
        /* Widget Styles */
        .page-section {
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
        }
        .widget-content {
            padding: 1.5rem;
        }
        .widget-content h1, .widget-content h2, .widget-content h3 {
            color: #212529;
            margin-bottom: 1rem;
        }
        .widget-content p {
            color: #6c757d;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class=\"theme-layout-wrapper theme-{$theme->slug}\">
        <header class=\"theme-header\">
            <div class=\"container-fluid\">
                <div class=\"row align-items-center\">
                    <div class=\"col-md-8\">
                        <h1 class=\"theme-title\">{$theme->name} Theme</h1>
                        <p class=\"mb-0 text-light opacity-75\">Live Preview Mode</p>
                    </div>
                    <div class=\"col-md-4 text-end\">
                        <nav class=\"navbar-nav d-flex flex-row gap-3\">
                            <a href=\"#\" class=\"text-light text-decoration-none\">Home</a>
                            <a href=\"#\" class=\"text-light text-decoration-none\">About</a>
                            <a href=\"#\" class=\"text-light text-decoration-none\">Contact</a>
                        </nav>
                    </div>
                </div>
            </div>
        </header>
        
        <main class=\"theme-content-area\">
            <div class=\"container-fluid\">
                <div data-canvas-content>
                    <!-- Page content will be inserted here -->
                </div>
            </div>
        </main>
        
        <footer class=\"theme-footer\">
            <div class=\"container-fluid\">
                <div class=\"row\">
                    <div class=\"col-md-8\">
                        <p class=\"mb-0\">&copy; 2024 {$theme->name} Theme. Powered by RealsysCMS.</p>
                    </div>
                    <div class=\"col-md-4 text-end\">
                        <small class=\"text-muted\">Theme Preview Mode</small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap JS -->
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
</body>
</html>";
    }

    /**
     * Generate full page HTML using TemplateRenderer pipeline (like frontend)
     * This provides complete theme context with header, footer, menus, etc.
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
                'page_builder_mode' => true
            ];
            
            // Use TemplateRenderer to render the complete page
            $fullPageHtml = $templateRenderer->renderPage($page, $viewData);
            
            return $fullPageHtml;
            
        } catch (\Exception $e) {
            \Log::warning('Could not generate full page HTML using frontend pipeline: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to basic wrapper
            return $this->getBasicCanvasWrapper($page->template->theme);
        }
    }

    /**
     * Generate iframe HTML for complete theme preview using frontend pipeline
     */
    public function showFullThemePreviewIframe(Page $page)
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
            \Log::error('Error showing full theme preview iframe: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response('<html><body><h1>Error loading full theme preview</h1><p>' . $e->getMessage() . '</p></body></html>');
        }
    }

    /**
     * Get complete theme wrapper with full page content for testing
     */
    public function getThemeWrapperTest(Page $page)
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
            
            // Ensure theme namespace is registered
            $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
            
            // Load theme assets
            $themeManager->loadThemeAssets($theme);
            
            // Process sections with enhanced data
            $sectionsData = [];
            $allAssets = ['css' => [], 'js' => []];
            
            foreach ($page->sections as $section) {
                $sectionData = [
                    'id' => $section->id,
                    'template_section' => $section->templateSection ? [
                        'id' => $section->templateSection->id,
                        'name' => $section->templateSection->name,
                        'section_type' => $section->templateSection->section_type,
                    ] : null,
                    'widgets' => [],
                    'rendered_html' => null,
                ];
                
                // Process widgets in this section
                foreach ($section->pageSectionWidgets as $pageSectionWidget) {
                    $widget = $pageSectionWidget->widget;
                    
                    if (!$widget) continue;
                    
                    // Get widget field values
                    $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
                    
                    // Render widget with theme context
                    $widgetHtml = $this->renderWidgetWithThemeContext($widget, $fieldValues, $theme, $pageSectionWidget);
                    
                    // Collect widget assets
                    $widgetAssets = $widgetService->collectWidgetAssets($widget);
                    
                    $widgetData = [
                        'id' => $pageSectionWidget->id,
                        'widget_id' => $widget->id,
                        'widget_name' => $widget->name,
                        'widget_slug' => $widget->slug,
                        'rendered_html' => $widgetHtml,
                        'assets' => $widgetAssets
                    ];
                    
                    $sectionData['widgets'][] = $widgetData;
                    
                    // Collect widget assets
                    if (isset($widgetAssets['css'])) {
                        $allAssets['css'] = array_merge($allAssets['css'], $widgetAssets['css']);
                    }
                    if (isset($widgetAssets['js'])) {
                        $allAssets['js'] = array_merge($allAssets['js'], $widgetAssets['js']);
                    }
                }
                
                // Render entire section with theme context
                try {
                    $sectionData['rendered_html'] = $this->renderSectionWithThemeContext($section, $theme);
                } catch (\Exception $e) {
                    \Log::warning('Could not render section HTML: ' . $e->getMessage(), [
                        'section_id' => $section->id
                    ]);
                    $sectionData['rendered_html'] = '<div class="section-render-error">Section could not be rendered</div>';
                }
                
                $sectionsData[] = $sectionData;
            }
            
            // Collect theme assets
            $themeAssets = $this->collectThemeAssets($theme);
            $allAssets['css'] = array_merge($themeAssets['css'], $allAssets['css']);
            $allAssets['js'] = array_merge($themeAssets['js'], $allAssets['js']);
            
            // Remove duplicates from all assets
            $allAssets['css'] = array_unique($allAssets['css']);
            $allAssets['js'] = array_unique($allAssets['js']);
            
            // Generate theme canvas wrapper HTML
            $canvasWrapperHtml = $this->generateThemeCanvasWrapper($theme, $page->template, $page);
            
            return response()->json([
                'success' => true,
                'theme' => [
                    'id' => $theme->id,
                    'name' => $theme->name,
                    'slug' => $theme->slug,
                    'canvas_wrapper' => $canvasWrapperHtml,
                    'assets' => $themeAssets
                ],
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'template_id' => $page->template_id
                ],
                'sections' => $sectionsData,
                'assets' => $allAssets,
                'preview_url' => url("/admin/api/pages/{$page->id}/theme-wrapper-iframe"),
                'metadata' => [
                    'sections_count' => count($sectionsData),
                    'total_widgets' => collect($sectionsData)->sum(fn($section) => count($section['widgets'])),
                    'render_time' => microtime(true) - LARAVEL_START,
                    'theme_context' => true
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting theme wrapper test: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get theme wrapper',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show theme wrapper in an iframe with actual page content
     */
    public function showThemeWrapperIframe(Page $page)
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
            
            // Ensure theme namespace is registered
            $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
            
            // Load theme assets
            $themeManager->loadThemeAssets($theme);
            
            // Collect all assets first
            $allAssets = ['css' => [], 'js' => []];
            $themeAssets = $this->collectThemeAssets($theme);
            $allAssets['css'] = array_merge($allAssets['css'], $themeAssets['css']);
            $allAssets['js'] = array_merge($allAssets['js'], $themeAssets['js']);
            
            // Generate theme canvas wrapper HTML
            $canvasWrapperHtml = $this->generateThemeCanvasWrapper($theme, $page->template, $page);
            
            // Build actual page content from sections and widgets
            $pageContent = '<div class="page-content-wrapper">';
            
            if ($page->sections->count() > 0) {
                foreach ($page->sections as $section) {
                    $pageContent .= '<div class="page-section" data-section-id="' . $section->id . '">';
                    $pageContent .= '<h3 class="section-title">' . ($section->templateSection->name ?? 'Section') . '</h3>';
                    
                    // Render widgets in this section
                    foreach ($section->pageSectionWidgets as $pageSectionWidget) {
                        $widget = $pageSectionWidget->widget;
                        
                        if (!$widget) continue;
                        
                        try {
                            // Get widget field values
                            $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
                            
                            // Render widget with theme context
                            $widgetHtml = $this->renderWidgetWithThemeContext($widget, $fieldValues, $theme, $pageSectionWidget);
                            
                            // Collect widget assets
                            $widgetAssets = $widgetService->collectWidgetAssets($widget);
                            if (isset($widgetAssets['css'])) {
                                $allAssets['css'] = array_merge($allAssets['css'], $widgetAssets['css']);
                            }
                            if (isset($widgetAssets['js'])) {
                                $allAssets['js'] = array_merge($allAssets['js'], $widgetAssets['js']);
                            }
                            
                            $pageContent .= '<div class="widget-wrapper" data-widget-id="' . $widget->id . '">';
                            $pageContent .= '<div class="widget-content">' . $widgetHtml . '</div>';
                            $pageContent .= '</div>';
                            
                        } catch (\Exception $e) {
                            $pageContent .= '<div class="widget-error">Widget "' . $widget->name . '" could not be rendered: ' . $e->getMessage() . '</div>';
                        }
                    }
                    
                    $pageContent .= '</div>';
                }
            } else {
                $pageContent .= '
                    <div class="no-content" style="padding: 40px; text-align: center; background: #f8f9fa; margin: 20px 0; border-radius: 8px;">
                        <h3>🎨 Theme Preview</h3>
                        <p><strong>Theme:</strong> ' . $theme->name . '</p>
                        <p><strong>Page:</strong> ' . $page->title . '</p>
                        <p>No sections or widgets found on this page.</p>
                        <p class="text-muted">Add some content in the page builder to see it styled with this theme.</p>
                    </div>
                ';
            }
            
            $pageContent .= '</div>';
            
            // Remove duplicates from all assets
            $allAssets['css'] = array_unique($allAssets['css']);
            $allAssets['js'] = array_unique($allAssets['js']);
            
            // Replace content placeholder with actual page content
            $finalHtml = str_replace(
                '<div data-canvas-content>',
                '<div data-canvas-content>' . $pageContent,
                $canvasWrapperHtml
            );
            
            // If no placeholder found, append content
            if ($finalHtml === $canvasWrapperHtml) {
                $finalHtml = str_replace(
                    '</div>',
                    $pageContent . '</div>',
                    $canvasWrapperHtml
                );
            }
            
            // Add debug info about loaded assets (visible in page source)
            $debugComment = "\n<!-- Theme Assets Debug:\n";
            $debugComment .= "CSS Files (" . count($allAssets['css']) . "):\n";
            foreach ($allAssets['css'] as $css) {
                $debugComment .= "  - " . $css . "\n";
            }
            $debugComment .= "JS Files (" . count($allAssets['js']) . "):\n";
            foreach ($allAssets['js'] as $js) {
                $debugComment .= "  - " . $js . "\n";
            }
            $debugComment .= "-->\n";
            
            // Inject debug comment and assets into the HTML
            $finalHtml = str_replace('<head>', '<head>' . $debugComment, $finalHtml);
            $finalHtml = $this->injectAssetsIntoHtml($finalHtml, $allAssets);
            
            return response($finalHtml)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            \Log::error('Error showing theme wrapper iframe: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response('<html><body><h1>Error loading theme wrapper</h1><p>' . $e->getMessage() . '</p></body></html>');
        }
    }

    /**
     * Inject CSS and JS assets into HTML
     */
    protected function injectAssetsIntoHtml($html, $assets)
    {
        $cssLinks = '';
        $jsScripts = '';
        
        // Generate CSS link tags
        if (!empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $cssLinks .= '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($cssFile) . '">' . "\n";
            }
        }
        
        // Generate JS script tags
        if (!empty($assets['js'])) {
            foreach ($assets['js'] as $jsFile) {
                $jsScripts .= '<script type="text/javascript" src="' . htmlspecialchars($jsFile) . '"></script>' . "\n";
            }
        }
        
        // Inject CSS before closing head tag
        if ($cssLinks) {
            $html = str_replace('</head>', $cssLinks . '</head>', $html);
        }
        
        // Inject JS before closing body tag
        if ($jsScripts) {
            $html = str_replace('</body>', $jsScripts . '</body>', $html);
        }
        
        return $html;
    }

    /**
     * Render theme layout with custom content placeholder
     */
    protected function renderThemeLayoutWithContent($theme, $layoutData)
    {
        try {
            // Create a temporary Blade template content that extends the theme layout
            $tempViewContent = <<<BLADE
@extends('{$theme->slug}::layouts.theme')

@section('content')
<div data-canvas-content>
    <!-- Page content will be inserted here -->
</div>
@endsection
BLADE;

            // Create a temporary file for the custom view
            $tempViewPath = storage_path('framework/views/temp_theme_wrapper_' . $theme->slug . '.blade.php');
            
            // Ensure the directory exists
            $viewDir = dirname($tempViewPath);
            if (!is_dir($viewDir)) {
                mkdir($viewDir, 0755, true);
            }
            
            file_put_contents($tempViewPath, $tempViewContent);
            
            // Register the temp view and render it
            $viewName = 'temp_theme_wrapper_' . $theme->slug;
            
            // Create view factory and add path
            $viewFactory = app('view');
            $viewFactory->addLocation(storage_path('framework/views'));
            
            // Render the view
            $html = $viewFactory->make($viewName, $layoutData)->render();
            
            // Clean up the temporary file
            if (file_exists($tempViewPath)) {
                unlink($tempViewPath);
            }
            
            return $html;
            
        } catch (\Exception $e) {
            \Log::warning('Error rendering theme layout with content: ' . $e->getMessage());
            
            // Fallback: try to render theme layout directly and inject content manually
            try {
                $themeLayoutHtml = view("{$theme->slug}::layouts.theme", $layoutData)->render();
                
                // Try to find @yield('content') or similar and replace with our placeholder
                $contentPlaceholder = '<div data-canvas-content><!-- Page content will be inserted here --></div>';
                
                // Look for common content yield patterns and replace them
                $patterns = [
                    '/@yield\s*\(\s*[\'"]content[\'"]\s*\)/',
                    '/@yield\s*\(\s*[\'"]main[\'"]\s*\)/',
                    '/\{\{\s*\$content\s*\}\}/',
                    '/\{!!\s*\$content\s*!!\}/'
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $themeLayoutHtml)) {
                        return preg_replace($pattern, $contentPlaceholder, $themeLayoutHtml);
                    }
                }
                
                // If no yield found, try to inject after header
                if (strpos($themeLayoutHtml, '@include(\'theme::partials.header\')') !== false) {
                    $themeLayoutHtml = str_replace(
                        '@include(\'theme::partials.header\')',
                        '@include(\'theme::partials.header\')' . "\n" . $contentPlaceholder,
                        $themeLayoutHtml
                    );
                    return $themeLayoutHtml;
                }
                
                return $themeLayoutHtml;
                
            } catch (\Exception $innerE) {
                \Log::error('Fallback theme layout rendering also failed: ' . $innerE->getMessage());
                throw $e; // Re-throw original exception
            }
        }
    }

    /**
     * Get available section templates.
     */
    public function getTemplates()
    {
        try {
            $templates = $this->sectionTemplateService->getAllTemplates();
            
            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading section templates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load section templates',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific section template.
     */
    public function getTemplate($templateKey)
    {
        try {
            $template = $this->sectionTemplateService->getTemplate($templateKey);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => 'Template not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading section template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load section template',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new section for a page with template support.
     */
    public function store(Request $request, Page $page)
    {
        try {
            $validated = $request->validate([
                'template_key' => 'required|string',
                'name' => 'nullable|string|max:255',
                'identifier' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9-]+$/',
                    'unique:page_sections,identifier'
                ],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'grid_id' => 'nullable|string|unique:page_sections,grid_id',
                'grid_config' => 'nullable|array',
                'allows_widgets' => 'boolean',
                'widget_types' => 'nullable|array',
                'locked_position' => 'boolean',
                'resize_handles' => 'nullable|array',
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'column_span_override' => 'nullable|integer',
                'column_offset_override' => 'nullable|integer',
                'position' => 'nullable|integer'
            ]);

            // First, try to find or create a template section for this template key
            $templateSection = $this->findOrCreateTemplateSection($page->template, $validated['template_key']);

            // Set default name if not provided
            if (empty($validated['name'])) {
                $validated['name'] = $templateSection->name;
            }

            // Generate identifier if not provided
            if (empty($validated['identifier'])) {
                $validated['identifier'] = Str::slug($validated['name']);
            }

            // Set position if not provided
            if (!isset($validated['position'])) {
                $validated['position'] = $page->sections()->max('position') + 1;
            }

            // Add page ID and template section ID
            $validated['page_id'] = $page->id;
            $validated['template_section_id'] = $templateSection->id;

            // Ensure grid_id is set
            if (empty($validated['grid_id'])) {
                $validated['grid_id'] = 'section_' . time() . '_' . uniqid() . '_' . $templateSection->id;
            }

            // Set default values for required fields if not provided
            $validated['allows_widgets'] = $validated['allows_widgets'] ?? true;
            $validated['locked_position'] = $validated['locked_position'] ?? false;
            $validated['grid_x'] = $validated['grid_x'] ?? 0;
            $validated['grid_y'] = $validated['grid_y'] ?? 0;
            $validated['grid_w'] = $validated['grid_w'] ?? 12;
            $validated['grid_h'] = $validated['grid_h'] ?? 4;
            $validated['widget_types'] = $validated['widget_types'] ?? ['text', 'image', 'counter', 'gallery', 'form', 'video'];
            $validated['css_classes'] = $validated['css_classes'] ?? 'container';
            $validated['background_color'] = $validated['background_color'] ?? '#ffffff';
            $validated['padding'] = $validated['padding'] ?? ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0];
            $validated['margin'] = $validated['margin'] ?? ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0];
            $validated['resize_handles'] = $validated['resize_handles'] ?? ['se', 'sw'];

            $section = PageSection::create($validated);

            \Log::info('Section created successfully', [
                'section_id' => $section->id,
                'template_key' => $validated['template_key'],
                'template_section_id' => $templateSection->id,
                'page_id' => $page->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Section created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating section: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find or create a template section for the given template key
     * 
     * @param Template $template
     * @param string $templateKey
     * @return TemplateSection
     */
    protected function findOrCreateTemplateSection(Template $template, string $templateKey): TemplateSection
    {
        // Try to find existing template section
        $templateSection = $template->sections()
            ->where('section_type', $templateKey)
            ->first();

        if (!$templateSection) {
            // Create a new template section based on the template key
            $templateSection = $template->sections()->create([
                'name' => $this->getTemplateSectionName($templateKey),
                'slug' => Str::slug($this->getTemplateSectionName($templateKey)),
                'position' => $template->sections()->max('position') + 1,
                'section_type' => $templateKey,
                'column_layout' => $this->getDefaultColumnLayout($templateKey),
                'description' => $this->getTemplateSectionDescription($templateKey),
                'is_repeatable' => true,
                'max_widgets' => null,
                // Basic positioning
                'x' => 0,
                'y' => 0,
                'w' => 12,
                'h' => 4
            ]);

            \Log::info('Created new template section:', [
                'template_id' => $template->id,
                'template_section_id' => $templateSection->id,
                'section_type' => $templateKey,
                'name' => $templateSection->name
            ]);
        }

        return $templateSection;
    }

    /**
     * Get template section name based on template key
     * 
     * @param string $templateKey
     * @return string
     */
    protected function getTemplateSectionName(string $templateKey): string
    {
        $names = [
            'full-width' => 'Full Width Section',
            'multi-column' => 'Multi Column Section',
            'sidebar-left' => 'Sidebar Left Section',
            'sidebar-right' => 'Sidebar Right Section'
        ];

        return $names[$templateKey] ?? 'Custom Section';
    }

    /**
     * Get template section description based on template key
     * 
     * @param string $templateKey
     * @return string
     */
    protected function getTemplateSectionDescription(string $templateKey): string
    {
        $descriptions = [
            'full-width' => 'Full width single column layout',
            'multi-column' => 'Flexible multi-column layout',
            'sidebar-left' => 'Left sidebar with main content',
            'sidebar-right' => 'Right sidebar with main content'
        ];

        return $descriptions[$templateKey] ?? 'Custom section layout';
    }

    /**
     * Get default column layout based on template key
     * 
     * @param string $templateKey
     * @return string
     */
    protected function getDefaultColumnLayout(string $templateKey): string
    {
        $layouts = [
            'full-width' => '12',
            'multi-column' => '4-4-4',
            'sidebar-left' => '3-9',
            'sidebar-right' => '9-3'
        ];

        return $layouts[$templateKey] ?? '12';
    }

    /**
     * Update a section for a page.
     */
    public function update(Request $request, Page $page, PageSection $section)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'identifier' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9-]+$/',
                    'unique:page_sections,identifier,' . $section->id
                ],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'grid_id' => 'nullable|string|unique:page_sections,grid_id,' . $section->id,
                'grid_config' => 'nullable|array',
                'allows_widgets' => 'boolean',
                'widget_types' => 'nullable|array',
                'locked_position' => 'boolean',
                'resize_handles' => 'nullable|array',
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'column_span_override' => 'nullable|integer',
                'column_offset_override' => 'nullable|integer',
                'position' => 'nullable|integer'
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Section updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Page $page, PageSection $section)
    {
        try {
            // Get the template section before deleting the page section
            $templateSection = $section->templateSection;
            
            // Delete the page section
            $section->delete();
            
            // Also delete the corresponding template section if it's not used by other pages
            if ($templateSection) {
                $otherPageSections = PageSection::where('template_section_id', $templateSection->id)
                    ->where('id', '!=', $section->id)
                    ->count();
                
                if ($otherPageSections === 0) {
                    $templateSection->delete();
                    \Log::info('Template section also deleted:', [
                        'template_section_id' => $templateSection->id,
                        'section_type' => $templateSection->section_type
                    ]);
                }
            }

            \Log::info('Section deleted successfully', [
                'section_id' => $section->id,
                'template_section_id' => $templateSection->id ?? null,
                'page_id' => $page->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'page_id' => $page->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section GridStack position.
     */
    public function updateGridPosition(Request $request, PageSection $section)
    {
        try {
            $validated = $request->validate([
                'grid_x' => 'required|integer|min:0',
                'grid_y' => 'required|integer|min:0',
                'grid_w' => 'required|integer|min:1',
                'grid_h' => 'required|integer|min:1',
                'grid_id' => 'nullable|string|unique:page_sections,grid_id,' . $section->id
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'grid_position' => [
                        'x' => $section->grid_x,
                        'y' => $section->grid_y,
                        'w' => $section->grid_w,
                        'h' => $section->grid_h,
                        'grid_id' => $section->grid_id
                    ]
                ],
                'message' => 'Section position updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating section grid position: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update section position',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section styles.
     */
    public function updateStyles(Request $request, PageSection $section)
    {
        try {
            $validated = $request->validate([
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'grid_x' => 'nullable|integer|min:0|max:11',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1|max:12',
                'grid_h' => 'nullable|integer|min:1|max:20',
                'column_span_override' => 'nullable|integer',
                'column_offset_override' => 'nullable|integer'
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'section' => $section->fresh(),
                'data' => [
                    'css_classes' => $section->css_classes,
                    'background_color' => $section->background_color,
                    'padding' => $section->padding,
                    'margin' => $section->margin,
                    'grid_x' => $section->grid_x,
                    'grid_y' => $section->grid_y,
                    'grid_w' => $section->grid_w,
                    'grid_h' => $section->grid_h,
                    'column_span_override' => $section->column_span_override,
                    'column_offset_override' => $section->column_offset_override
                ],
                'message' => 'Section styles updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating section styles: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update section styles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get section widgets with GridStack positioning.
     */
    public function getSectionWidgets(PageSection $section)
    {
        try {
            $widgets = $section->pageSectionWidgets()
                ->with('widget')
                ->orderBy('position')
                ->get()
                ->map(function ($widget) {
                    return [
                        'id' => $widget->id,
                        'widget_id' => $widget->widget_id,
                        'widget_name' => $widget->widget->name ?? 'Unknown Widget',
                        'grid_position' => [
                            'x' => $widget->grid_x ?? 0,
                            'y' => $widget->grid_y ?? 0,
                            'w' => $widget->grid_w ?? 6,
                            'h' => $widget->grid_h ?? 3,
                            'grid_id' => $widget->grid_id ?? "widget-{$widget->id}"
                        ],
                        'column_position' => $widget->column_position,
                        'min_width' => $widget->min_width,
                        'max_width' => $widget->max_width,
                        'locked_position' => $widget->locked_position ?? false,
                        'resize_handles' => $widget->resize_handles ?? ['se', 'sw'],
                        'css_classes' => $widget->css_classes,
                        'padding' => $widget->padding,
                        'margin' => $widget->margin,
                        'min_height' => $widget->min_height,
                        'max_height' => $widget->max_height,
                        'settings' => $widget->settings,
                        'content_query' => $widget->content_query
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $widgets
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading section widgets: ' . $e->getMessage(), [
                'section_id' => $section->id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load section widgets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder sections for a page.
     */
    public function reorder(Request $request, Page $page)
    {
        try {
            $validated = $request->validate([
                'sections' => 'required|array',
                'sections.*.id' => 'required|exists:page_sections,id',
                'sections.*.position' => 'required|integer|min:0'
            ]);

            foreach ($validated['sections'] as $item) {
                PageSection::where('id', $item['id'])->update([
                    'position' => $item['position']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sections reordered successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reordering sections: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to reorder sections',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render a section with all its widgets for the page designer.
     */
    public function renderSection(Request $request, PageSection $section)
    {
        try {
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            $page = $section->page;
            $template = $page->template;
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page has no template'
                ], 400);
            }
            
            // Render the section using TemplateRenderer
            $html = $templateRenderer->renderSectionById($section->id, [
                'page' => $page,
                'template' => $template
            ]);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'section_id' => $section->id,
                'section_name' => $section->name ?? 'Unknown Section'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to render section: ' . $e->getMessage()
            ], 500);
        }
    }
}
