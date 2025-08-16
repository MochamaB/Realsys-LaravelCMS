<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    protected $themeManager;

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Get active theme assets for GrapesJS canvas
     *
     * @return JsonResponse
     */
    public function getActiveThemeAssets(): JsonResponse
    {
        try {
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return response()->json([
                    'error' => 'No active theme found'
                ], 404);
            }

            // Get theme CSS files
            $cssFiles = [];
            if (isset($activeTheme->css) && is_array($activeTheme->css)) {
                $cssFiles = $activeTheme->css;
            }

            // Get theme JS files  
            $jsFiles = [];
            if (isset($activeTheme->js) && is_array($activeTheme->js)) {
                $jsFiles = $activeTheme->js;
            }

            // Add base theme path for relative URLs
            $themeBasePath = "/themes/{$activeTheme->slug}";

            return response()->json([
                'success' => true,
                'theme' => [
                    'name' => $activeTheme->name,
                    'slug' => $activeTheme->slug,
                    'base_path' => $themeBasePath,
                    'css' => $cssFiles,
                    'js' => $jsFiles,
                    'fonts' => [], // Can be extended later if needed
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting theme assets: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to load theme assets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme-specific CSS for canvas injection with widget support
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCanvasStyles(Request $request): JsonResponse
    {
        try {
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'css' => '',
                    'message' => 'No active theme found'
                ], 404);
            }

            // Get theme configuration
            $themeConfig = $this->getThemeConfiguration($activeTheme);
            
            // Combine all theme CSS into a single response
            $combinedCSS = '';
            $loadedFiles = [];
            
            if (isset($themeConfig['css']) && is_array($themeConfig['css'])) {
                foreach ($themeConfig['css'] as $cssFile) {
                    $cssPath = public_path($cssFile);
                    if (file_exists($cssPath)) {
                        $combinedCSS .= "/* File: {$cssFile} */\n";
                        $combinedCSS .= file_get_contents($cssPath) . "\n\n";
                        $loadedFiles[] = $cssFile;
                    } else {
                        \Log::warning("CSS file not found: {$cssFile}");
                    }
                }
            }

            // Add widget-specific CSS if widget IDs are provided
            $widgetIds = $request->get('widget_ids', []);
            if (!empty($widgetIds)) {
                $widgetAssets = $this->getWidgetAssets($activeTheme, $widgetIds);
                if (!empty($widgetAssets['css'])) {
                    $combinedCSS .= "\n/* Widget-Specific CSS */\n";
                    $combinedCSS .= $widgetAssets['css'];
                    $loadedFiles = array_merge($loadedFiles, $widgetAssets['css_files']);
                }
            }

            // Add canvas-specific CSS adjustments for Phase 3.1
            $canvasCSS = $this->getCanvasSpecificStyles();
            
            // Combine everything
            $finalCSS = $combinedCSS . $canvasCSS;
            
            // Apply CSS scoping to prevent conflicts with GrapesJS
            $scopedCSS = $this->applyCSSScoping($finalCSS);

            return response()->json([
                'success' => true,
                'css' => $scopedCSS,
                'theme' => [
                    'id' => $activeTheme->id,
                    'name' => $activeTheme->name,
                    'slug' => $activeTheme->slug
                ],
                'files_loaded' => $loadedFiles,
                'widgets' => [
                    'requested_ids' => $widgetIds,
                    'processed_count' => !empty($widgetIds) ? count(array_intersect($widgetIds, \App\Models\Widget::where('theme_id', $activeTheme->id)->pluck('id')->toArray())) : 0
                ],
                'meta' => [
                    'total_size' => strlen($scopedCSS),
                    'files_count' => count($loadedFiles),
                    'widget_assets_included' => !empty($widgetIds),
                    'generated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting canvas styles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load canvas styles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme-specific JavaScript for canvas injection with widget support
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCanvasScripts(Request $request): JsonResponse
    {
        try {
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'js' => '',
                    'message' => 'No active theme found'
                ], 404);
            }

            // Get theme configuration
            $themeConfig = $this->getThemeConfiguration($activeTheme);
            
            // Combine all theme JavaScript
            $combinedJS = '';
            $loadedFiles = [];
            
            if (isset($themeConfig['js']) && is_array($themeConfig['js'])) {
                foreach ($themeConfig['js'] as $jsFile) {
                    $jsPath = public_path($jsFile);
                    if (file_exists($jsPath)) {
                        $combinedJS .= "/* File: {$jsFile} */\n";
                        $combinedJS .= file_get_contents($jsPath) . "\n\n";
                        $loadedFiles[] = $jsFile;
                    } else {
                        \Log::warning("JS file not found: {$jsFile}");
                    }
                }
            }

            // Add widget-specific JavaScript if widget IDs are provided
            $widgetIds = $request->get('widget_ids', []);
            if (!empty($widgetIds)) {
                $widgetAssets = $this->getWidgetAssets($activeTheme, $widgetIds);
                if (!empty($widgetAssets['js'])) {
                    $combinedJS .= "\n/* Widget-Specific JavaScript */\n";
                    $combinedJS .= $widgetAssets['js'];
                    $loadedFiles = array_merge($loadedFiles, $widgetAssets['js_files']);
                }
            }

            // Add canvas-specific JavaScript wrapper
            $canvasJS = $this->wrapJavaScriptForCanvas($combinedJS);

            return response()->json([
                'success' => true,
                'js' => $canvasJS,
                'theme' => [
                    'id' => $activeTheme->id,
                    'name' => $activeTheme->name,
                    'slug' => $activeTheme->slug
                ],
                'files_loaded' => $loadedFiles,
                'widgets' => [
                    'requested_ids' => $widgetIds,
                    'processed_count' => !empty($widgetIds) ? count(array_intersect($widgetIds, \App\Models\Widget::where('theme_id', $activeTheme->id)->pluck('id')->toArray())) : 0
                ],
                'meta' => [
                    'total_size' => strlen($canvasJS),
                    'files_count' => count($loadedFiles),
                    'widget_assets_included' => !empty($widgetIds),
                    'generated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting canvas scripts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load canvas scripts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme configuration from theme.json or database
     *
     * @param Theme $theme
     * @return array
     */
    protected function getThemeConfiguration($theme): array
    {
        // Try to get from theme.json file first
        $themeJsonPath = resource_path("themes/{$theme->slug}/theme.json");
        
        if (file_exists($themeJsonPath)) {
            $config = json_decode(file_get_contents($themeJsonPath), true);
            if ($config) {
                return $config;
            }
        }
        
        // Fallback to database/default structure
        return [
            'name' => $theme->name,
            'slug' => $theme->slug,
            'css' => $theme->css ?? [],
            'js' => $theme->js ?? []
        ];
    }

    /**
     * Get canvas-specific CSS styles for Phase 3.1
     *
     * @return string
     */
    protected function getCanvasSpecificStyles(): string
    {
        return "
        /* Phase 3.1: Canvas-specific adjustments */
        body {
            margin: 0;
            padding: 0;
            font-family: inherit;
            background: #ffffff;
        }
        
        /* Ensure proper layout in canvas */
        .container {
            max-width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }
        
        /* Fix background attachments for canvas */
        *[style*='background-attachment: fixed'] {
            background-attachment: scroll !important;
        }
        
        /* Ensure sections have proper spacing */
        section {
            position: relative;
            z-index: 1;
        }
        
        /* Fix any fixed positioning issues */
        .fixed-top, .fixed-bottom {
            position: relative !important;
        }
        
        /* Ensure proper image rendering */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Canvas-specific responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
        }
        
        /* GrapesJS integration styles */
        .gjs-selected {
            outline: 2px solid #405189 !important;
        }
        
        .gjs-hovered {
            outline: 1px dashed #405189 !important;
        }
        ";
    }

    /**
     * Apply CSS scoping to prevent conflicts with GrapesJS
     *
     * @param string $css
     * @return string
     */
    protected function applyCSSScoping(string $css): string
    {
        // For now, return as-is. In the future, we could add CSS scoping
        // to prevent conflicts with GrapesJS interface styles
        return $css;
    }

    /**
     * Wrap JavaScript for safe canvas execution
     *
     * @param string $js
     * @return string
     */
    protected function wrapJavaScriptForCanvas(string $js): string
    {
        return "
        /* Phase 3.1: Canvas JavaScript Wrapper */
        (function() {
            'use strict';
            
            // Ensure we're in the canvas context
            if (typeof window.grapesjs !== 'undefined') {
                console.log('Loading theme JavaScript in GrapesJS canvas...');
            }
            
            // Original theme JavaScript
            {$js}
            
            // Canvas-specific initialization
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Theme JavaScript loaded in canvas');
            });
            
        })();
        ";
    }

    /**
     * Get widget-specific assets for canvas injection
     *
     * @param Theme $theme
     * @param array $widgetIds
     * @return array
     */
    protected function getWidgetAssets(Theme $theme, array $widgetIds): array
    {
        $combinedCSS = '';
        $combinedJS = '';
        $cssFiles = [];
        $jsFiles = [];

        // Get widgets by IDs
        $widgets = \App\Models\Widget::whereIn('id', $widgetIds)
            ->where('theme_id', $theme->id)
            ->get();

        foreach ($widgets as $widget) {
            // Add widget-specific CSS and JS assets using the same logic as WidgetPreviewFrontendController
            $widgetAssets = $this->collectWidgetSpecificAssets($widget, $theme);
            
            if (!empty($widgetAssets['css'])) {
                $combinedCSS .= "\n/* Widget: {$widget->name} ({$widget->slug}) */\n";
                $combinedCSS .= $widgetAssets['css'] . "\n";
                $cssFiles = array_merge($cssFiles, $widgetAssets['css_files']);
            }
            
            if (!empty($widgetAssets['js'])) {
                $combinedJS .= "\n/* Widget: {$widget->name} ({$widget->slug}) */\n";
                $combinedJS .= $widgetAssets['js'] . "\n";
                $jsFiles = array_merge($jsFiles, $widgetAssets['js_files']);
            }
        }

        return [
            'css' => $combinedCSS,
            'js' => $combinedJS,
            'css_files' => $cssFiles,
            'js_files' => $jsFiles,
            'widgets_processed' => $widgets->count()
        ];
    }

    /**
     * Collect widget-specific assets (reusing WidgetPreviewFrontendController logic)
     *
     * @param Widget $widget
     * @param Theme $theme
     * @return array
     */
    protected function collectWidgetSpecificAssets(\App\Models\Widget $widget, Theme $theme): array
    {
        $cssContent = '';
        $jsContent = '';
        $cssFiles = [];
        $jsFiles = [];

        // Widget asset paths to check (same as WidgetPreviewFrontendController)
        $possibleWidgetCss = [
            "/themes/{$theme->slug}/css/widgets/{$widget->slug}-custom.css",
            "/themes/{$theme->slug}/widgets/{$widget->slug}/style.css",
            "/themes/{$theme->slug}/widgets/{$widget->slug}/{$widget->slug}.css",
            "/themes/{$theme->slug}/widgets/{$widget->slug}/widget.css"
        ];

        $possibleWidgetJs = [
            "/themes/{$theme->slug}/js/widgets/{$widget->slug}-custom.js",
            "/themes/{$theme->slug}/widgets/{$widget->slug}/script.js",
            "/themes/{$theme->slug}/widgets/{$widget->slug}/{$widget->slug}.js",
            "/themes/{$theme->slug}/widgets/{$widget->slug}/widget.js"
        ];

        // Load CSS files
        foreach ($possibleWidgetCss as $cssPath) {
            $fullPath = public_path($cssPath);
            if (file_exists($fullPath)) {
                $cssContent .= "/* File: {$cssPath} */\n";
                $cssContent .= file_get_contents($fullPath) . "\n\n";
                $cssFiles[] = $cssPath;
            }
        }

        // Load JS files
        foreach ($possibleWidgetJs as $jsPath) {
            $fullPath = public_path($jsPath);
            if (file_exists($fullPath)) {
                $jsContent .= "/* File: {$jsPath} */\n";
                $jsContent .= file_get_contents($fullPath) . "\n\n";
                $jsFiles[] = $jsPath;
            }
        }

        return [
            'css' => $cssContent,
            'js' => $jsContent,
            'css_files' => $cssFiles,
            'js_files' => $jsFiles
        ];
    }
} 