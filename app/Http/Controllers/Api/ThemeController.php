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
     * Get theme-specific CSS for canvas injection (Phase 3.1)
     *
     * @return JsonResponse
     */
    public function getCanvasStyles(): JsonResponse
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
                'meta' => [
                    'total_size' => strlen($scopedCSS),
                    'files_count' => count($loadedFiles),
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
     * Get theme-specific JavaScript for canvas injection (Phase 3.1)
     *
     * @return JsonResponse
     */
    public function getCanvasScripts(): JsonResponse
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
                'meta' => [
                    'total_size' => strlen($canvasJS),
                    'files_count' => count($loadedFiles),
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
} 