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

    /**
     * Get structured component tree for GrapesJS editor
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getPageComponents(Page $page): JsonResponse
    {
        try {
            // Load page with all necessary relationships
            $page->load([
                'template.theme',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget.contentTypeAssociations.contentType'
            ]);
            
            $theme = $page->template->theme ?? null;
            
            if (!$theme) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page has no associated theme'
                ], 400);
            }

            // Get services
            $widgetService = app(\App\Services\WidgetService::class);
            
            $componentsData = [
                'page_id' => $page->id,
                'page_title' => $page->title,
                'template_name' => $page->template->name,
                'theme_name' => $theme->name,
                'sections' => []
            ];

            foreach ($page->sections->sortBy('position') as $section) {
                $sectionData = [
                    'id' => $section->id,
                    'name' => $section->templateSection->name ?? "Section {$section->id}",
                    'slug' => $section->templateSection->slug ?? "section_{$section->id}",
                    'type' => 'section',
                    'template_section_id' => $section->template_section_id,
                    'position' => $section->position,
                    'grid_config' => $section->grid_config,
                    'css_classes' => $section->css_classes,
                    'background_color' => $section->background_color,
                    'padding' => $section->padding,
                    'margin' => $section->margin,
                    'allows_widgets' => $section->allows_widgets,
                    'widget_types' => $section->widget_types,
                    'widgets' => []
                ];

                foreach ($section->pageSectionWidgets->sortBy('position') as $pageSectionWidget) {
                    $widget = $pageSectionWidget->widget;
                    
                    if (!$widget) continue;

                    // Get widget field values (combines settings, content, defaults)
                    $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
                    
                    // Get content items if widget has content query
                    $contentItems = [];
                    if (!empty($pageSectionWidget->content_query)) {
                        $contentItems = $this->getWidgetContentItems($widget, $pageSectionWidget->content_query);
                    }

                    // Get field mappings for this widget-content type association
                    $fieldMappings = [];
                    if (!empty($pageSectionWidget->content_query['content_type_id'])) {
                        $association = $widget->contentTypeAssociations()
                            ->where('content_type_id', $pageSectionWidget->content_query['content_type_id'])
                            ->where('is_active', true)
                            ->first();
                        $fieldMappings = $association->field_mappings ?? [];
                    }

                    $widgetData = [
                        'id' => $pageSectionWidget->id,
                        'widget_id' => $widget->id,
                        'name' => $widget->name,
                        'slug' => $widget->slug,
                        'description' => $widget->description,
                        'category' => $widget->category,
                        'icon' => $widget->icon,
                        'type' => 'widget',
                        'position' => $pageSectionWidget->position,
                        'grid_config' => [
                            'x' => $pageSectionWidget->grid_x ?? 0,
                            'y' => $pageSectionWidget->grid_y ?? 0,
                            'w' => $pageSectionWidget->grid_w ?? 6,
                            'h' => $pageSectionWidget->grid_h ?? 3,
                            'id' => $pageSectionWidget->grid_id ?? "widget-{$pageSectionWidget->id}"
                        ],
                        'settings' => $pageSectionWidget->settings ?? [],
                        'content_query' => $pageSectionWidget->content_query ?? [],
                        'field_values' => $fieldValues,
                        'content_items' => $contentItems,
                        'field_mappings' => $fieldMappings,
                        'css_classes' => $pageSectionWidget->css_classes,
                        'padding' => $pageSectionWidget->padding,
                        'margin' => $pageSectionWidget->margin,
                        'min_width' => $pageSectionWidget->min_width,
                        'max_width' => $pageSectionWidget->max_width,
                        'min_height' => $pageSectionWidget->min_height,
                        'max_height' => $pageSectionWidget->max_height,
                        'locked_position' => $pageSectionWidget->locked_position ?? false,
                        'resize_handles' => $pageSectionWidget->resize_handles ?? ['se', 'sw'],
                        'available_content_types' => $widget->contentTypes->map(function($ct) {
                            return [
                                'id' => $ct->id,
                                'name' => $ct->name,
                                'slug' => $ct->slug
                            ];
                        })->toArray()
                    ];

                    $sectionData['widgets'][] = $widgetData;
                }

                $componentsData['sections'][] = $sectionData;
            }

            return response()->json([
                'success' => true,
                'data' => $componentsData
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading page components for Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load page components',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content items for a widget's content query
     */
    protected function getWidgetContentItems($widget, $contentQuery)
    {
        try {
            if (empty($contentQuery['content_type_id'])) {
                return [];
            }

            $widgetService = app(\App\Services\WidgetService::class);
            $reflection = new \ReflectionClass($widgetService);
            $method = $reflection->getMethod('getContentFromQuery');
            $method->setAccessible(true);
            
            return $method->invoke($widgetService, $widget, $contentQuery);
            
        } catch (\Exception $e) {
            Log::warning('Could not fetch widget content items: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available widgets for the component library
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getWidgets(Page $page): JsonResponse
    {
        try {
            $theme = $page->template->theme ?? null;
            
            if (!$theme) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page has no associated theme'
                ], 400);
            }

            // Get all available widgets
            $widgets = Widget::where('is_active', true)
                ->orderBy('category')
                ->orderBy('name')
                ->get();

            $widgetsByCategory = [];
            
            foreach ($widgets as $widget) {
                $category = $widget->category ?: 'General';
                
                if (!isset($widgetsByCategory[$category])) {
                    $widgetsByCategory[$category] = [];
                }
                
                $widgetsByCategory[$category][] = [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                    'description' => $widget->description,
                    'icon' => $widget->icon,
                    'category' => $widget->category,
                    'preview_html' => $this->getWidgetPreviewHtml($widget, $theme),
                    'settings_schema' => $widget->settings_schema,
                    'content_types' => $widget->contentTypes->pluck('name', 'id')->toArray()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'widgets' => $widgetsByCategory,
                    'total_count' => $widgets->count(),
                    'categories' => array_keys($widgetsByCategory)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading widgets for Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load widgets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available content types for content selection
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getContentTypes(Page $page): JsonResponse
    {
        try {
            $contentTypes = ContentType::where('is_active', true)
                ->with(['fields' => function($query) {
                    $query->orderBy('position');
                }])
                ->orderBy('name')
                ->get();

            $typesData = [];
            foreach ($contentTypes as $contentType) {
                $typesData[] = [
                    'id' => $contentType->id,
                    'name' => $contentType->name,
                    'slug' => $contentType->slug,
                    'description' => $contentType->description,
                    'fields' => $contentType->fields->map(function($field) {
                        return [
                            'id' => $field->id,
                            'name' => $field->name,
                            'slug' => $field->slug,
                            'field_type' => $field->field_type,
                            'label' => $field->label,
                            'is_required' => $field->is_required,
                            'position' => $field->position
                        ];
                    })->toArray(),
                    'content_count' => $contentType->contentItems()->where('is_published', true)->count()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'content_types' => $typesData,
                    'total_count' => $contentTypes->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading content types for Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load content types',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content items for a specific content type (Enhanced for live designer)
     * 
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     */
    public function getContentItems(Request $request, Page $page): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'content_type_id' => 'required|integer|exists:content_types,id',
                'search' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'widget_id' => 'nullable|integer|exists:widgets,id',
                'show_field_mappings' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contentTypeId = $request->input('content_type_id');
            $search = $request->input('search');
            $limit = $request->input('limit', 20);
            $currentPage = $request->input('page', 1);
            $widgetId = $request->input('widget_id');
            $showFieldMappings = $request->input('show_field_mappings', false);

            $query = ContentItem::where('content_type_id', $contentTypeId)
                ->where('is_published', true)
                ->with(['contentType', 'fieldValues.contentField']);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('excerpt', 'like', '%' . $search . '%');
                });
            }

            $totalCount = $query->count();
            $contentItems = $query->orderBy('created_at', 'desc')
                ->skip(($currentPage - 1) * $limit)
                ->take($limit)
                ->get();

            // Get content type info
            $contentType = \App\Models\ContentType::find($contentTypeId);

            // Get field mappings if widget specified
            $fieldMappings = [];
            if ($widgetId && $showFieldMappings) {
                $widget = \App\Models\Widget::find($widgetId);
                if ($widget) {
                    $association = $widget->contentTypeAssociations()
                        ->where('content_type_id', $contentTypeId)
                        ->where('is_active', true)
                        ->first();
                    $fieldMappings = $association->field_mappings ?? [];
                }
            }

            $items = [];
            foreach ($contentItems as $item) {
                $fields = [];
                foreach ($item->fieldValues as $fieldValue) {
                    $fieldData = [
                        'label' => $fieldValue->contentField->label,
                        'type' => $fieldValue->contentField->field_type,
                        'slug' => $fieldValue->contentField->slug,
                        'value' => $fieldValue->field_value
                    ];

                    // Handle media fields
                    if ($fieldValue->contentField->field_type === 'image' && is_numeric($fieldValue->field_value)) {
                        try {
                            $media = \App\Models\Media::find($fieldValue->field_value);
                            if ($media) {
                                $fieldData['media_url'] = $media->getUrl();
                                $fieldData['media_info'] = [
                                    'id' => $media->id,
                                    'name' => $media->name,
                                    'file_name' => $media->file_name,
                                    'size' => $media->size,
                                    'mime_type' => $media->mime_type
                                ];
                            }
                        } catch (\Exception $e) {
                            // Media not found, keep original value
                        }
                    }

                    $fields[$fieldValue->contentField->slug] = $fieldData;
                }

                // Handle repeater fields - parse JSON values
                foreach ($item->fieldValues as $fieldValue) {
                    if ($fieldValue->contentField->field_type === 'repeater') {
                        try {
                            $repeaterData = json_decode($fieldValue->field_value, true);
                            if (is_array($repeaterData)) {
                                $fields[$fieldValue->contentField->slug]['repeater_items'] = $repeaterData;
                            }
                        } catch (\Exception $e) {
                            // Keep original value if JSON parsing fails
                        }
                    }
                }

                $itemData = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'excerpt' => $item->excerpt,
                    'featured_image' => $item->featured_image,
                    'status' => $item->status ?? 'published',
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
                    'fields' => $fields
                ];

                // Add preview URL if available
                if (method_exists($item, 'getPreviewUrl')) {
                    $itemData['preview_url'] = $item->getPreviewUrl();
                }

                $items[] = $itemData;
            }

            $responseData = [
                'items' => $items,
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $limit,
                    'total' => $totalCount,
                    'last_page' => ceil($totalCount / $limit),
                    'has_more' => ($currentPage * $limit) < $totalCount
                ],
                'content_type' => [
                    'id' => $contentType->id,
                    'name' => $contentType->name,
                    'slug' => $contentType->slug,
                    'description' => $contentType->description
                ]
            ];

            if ($showFieldMappings && !empty($fieldMappings)) {
                $responseData['field_mappings'] = $fieldMappings;
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading content items for Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'content_type_id' => $request->input('content_type_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load content items',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save page content from GrapesJS editor
     * 
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     */
    public function savePageContent(Request $request, Page $page): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'html' => 'required|string',
                'css' => 'nullable|string',
                'components' => 'nullable|array',
                'styles' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // For now, we'll store the GrapesJS data in a JSON field
            // This is a placeholder implementation - in production you'd want to 
            // parse the components and create proper page sections and widgets
            
            $grapesData = [
                'html' => $request->input('html'),
                'css' => $request->input('css'),
                'components' => $request->input('components', []),
                'styles' => $request->input('styles', []),
                'saved_at' => now()->toISOString()
            ];

            // Store in page metadata or create a dedicated field
            $page->update([
                'grapes_data' => json_encode($grapesData),
                'updated_at' => now()
            ]);

            Log::info('Page content saved from Live Designer', [
                'page_id' => $page->id,
                'user_id' => auth()->id(),
                'components_count' => count($grapesData['components']),
                'has_css' => !empty($grapesData['css'])
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'page_id' => $page->id,
                    'saved_at' => $grapesData['saved_at'],
                    'message' => 'Page content saved successfully'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving page content from Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to save page content',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update component settings (widget settings or content query)
     * 
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     */
    public function updateComponent(Request $request, Page $page): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'component_id' => 'required|integer',
                'component_type' => 'required|string|in:section,widget',
                'settings' => 'nullable|array',
                'content_query' => 'nullable|array',
                'css_classes' => 'nullable|string',
                'padding' => 'nullable|string',
                'margin' => 'nullable|string',
                'grid_config' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $componentId = $request->input('component_id');
            $componentType = $request->input('component_type');

            if ($componentType === 'widget') {
                $component = \App\Models\PageSectionWidget::where('id', $componentId)
                    ->whereHas('pageSection', function($query) use ($page) {
                        $query->where('page_id', $page->id);
                    })
                    ->first();

                if (!$component) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Widget not found'
                    ], 404);
                }

                // Update widget settings
                $updateData = [];
                if ($request->has('settings')) {
                    $updateData['settings'] = $request->input('settings');
                }
                if ($request->has('content_query')) {
                    $updateData['content_query'] = $request->input('content_query');
                }
                if ($request->has('css_classes')) {
                    $updateData['css_classes'] = $request->input('css_classes');
                }
                if ($request->has('padding')) {
                    $updateData['padding'] = $request->input('padding');
                }
                if ($request->has('margin')) {
                    $updateData['margin'] = $request->input('margin');
                }
                if ($request->has('grid_config')) {
                    $gridConfig = $request->input('grid_config');
                    $updateData['grid_x'] = $gridConfig['x'] ?? $component->grid_x;
                    $updateData['grid_y'] = $gridConfig['y'] ?? $component->grid_y;
                    $updateData['grid_w'] = $gridConfig['w'] ?? $component->grid_w;
                    $updateData['grid_h'] = $gridConfig['h'] ?? $component->grid_h;
                    $updateData['grid_id'] = $gridConfig['id'] ?? $component->grid_id;
                }

                $component->update($updateData);

            } elseif ($componentType === 'section') {
                $component = \App\Models\PageSection::where('id', $componentId)
                    ->where('page_id', $page->id)
                    ->first();

                if (!$component) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Section not found'
                    ], 404);
                }

                // Update section settings
                $updateData = [];
                if ($request->has('css_classes')) {
                    $updateData['css_classes'] = $request->input('css_classes');
                }
                if ($request->has('padding')) {
                    $updateData['padding'] = $request->input('padding');
                }
                if ($request->has('margin')) {
                    $updateData['margin'] = $request->input('margin');
                }
                if ($request->has('grid_config')) {
                    $gridConfig = $request->input('grid_config');
                    $updateData['grid_x'] = $gridConfig['x'] ?? $component->grid_x;
                    $updateData['grid_y'] = $gridConfig['y'] ?? $component->grid_y;
                    $updateData['grid_w'] = $gridConfig['w'] ?? $component->grid_w;
                    $updateData['grid_h'] = $gridConfig['h'] ?? $component->grid_h;
                    $updateData['grid_id'] = $gridConfig['id'] ?? $component->grid_id;
                }

                $component->update($updateData);
            }

            Log::info('Component updated in Live Designer', [
                'page_id' => $page->id,
                'component_id' => $componentId,
                'component_type' => $componentType,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'component_id' => $componentId,
                    'component_type' => $componentType,
                    'updated_at' => now()->toISOString(),
                    'message' => 'Component updated successfully'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating component in Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'component_id' => $request->input('component_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update component',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get component preview after updates
     * 
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     */
    public function getComponentPreview(Request $request, Page $page): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'component_id' => 'required|integer',
                'component_type' => 'required|string|in:section,widget'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $componentId = $request->input('component_id');
            $componentType = $request->input('component_type');

            if ($componentType === 'widget') {
                $pageSectionWidget = \App\Models\PageSectionWidget::where('id', $componentId)
                    ->with(['widget', 'pageSection'])
                    ->whereHas('pageSection', function($query) use ($page) {
                        $query->where('page_id', $page->id);
                    })
                    ->first();

                if (!$pageSectionWidget) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Widget not found'
                    ], 404);
                }

                $widget = $pageSectionWidget->widget;
                $widgetService = app(\App\Services\WidgetService::class);
                $templateRenderer = app(\App\Services\TemplateRenderer::class);

                // Ensure theme namespace is registered
                $theme = $page->template->theme;
                $templateRenderer->ensureThemeNamespaceIsRegistered($theme);

                // Get updated field values
                $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);

                // Render widget HTML
                $widgetHtml = $this->renderWidgetPreview($widget, $pageSectionWidget, $fieldValues, $theme);

                // Get updated content items
                $contentItems = [];
                if (!empty($pageSectionWidget->content_query)) {
                    $contentItems = $this->getWidgetContentItems($widget, $pageSectionWidget->content_query);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'component_id' => $componentId,
                        'component_type' => $componentType,
                        'rendered_html' => $widgetHtml,
                        'field_values' => $fieldValues,
                        'content_items' => $contentItems,
                        'updated_at' => now()->toISOString()
                    ]
                ]);

            } elseif ($componentType === 'section') {
                $section = \App\Models\PageSection::where('id', $componentId)
                    ->where('page_id', $page->id)
                    ->with(['templateSection', 'pageSectionWidgets.widget'])
                    ->first();

                if (!$section) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Section not found'
                    ], 404);
                }

                // For section preview, we'd typically re-render the entire section
                // This is a simplified version - in production you might want full section rendering
                return response()->json([
                    'success' => true,
                    'data' => [
                        'component_id' => $componentId,
                        'component_type' => $componentType,
                        'message' => 'Section updated successfully',
                        'updated_at' => now()->toISOString()
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error getting component preview in Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'component_id' => $request->input('component_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get component preview',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render widget preview HTML
     */
    protected function renderWidgetPreview($widget, $pageSectionWidget, $fieldValues, $theme)
    {
        try {
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            
            // Create a minimal view data context
            $viewData = [
                'widget' => $widget,
                'field_values' => $fieldValues,
                'settings' => $pageSectionWidget->settings ?? [],
                'preview_mode' => true,
                'live_designer_mode' => true
            ];

            // Render using theme widget view
            $viewPath = "theme::widgets.{$widget->slug}.view";
            
            if (view()->exists($viewPath)) {
                return view($viewPath, $viewData)->render();
            } else {
                // Fallback preview
                return '<div class="widget-preview">' .
                       '<h4>' . $widget->name . '</h4>' .
                       '<p>Widget preview not available</p>' .
                       '</div>';
            }
            
        } catch (\Exception $e) {
            Log::warning('Could not render widget preview: ' . $e->getMessage(), [
                'widget_id' => $widget->id
            ]);
            
            return '<div class="widget-preview-error">Preview rendering failed</div>';
        }
    }

    /**
     * Refresh page content after component changes
     * 
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     */
    public function refreshPageContent(Request $request, Page $page): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'refresh_type' => 'required|string|in:full,section,widget',
                'component_id' => 'nullable|integer',
                'include_assets' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $refreshType = $request->input('refresh_type');
            $componentId = $request->input('component_id');
            $includeAssets = $request->input('include_assets', false);

            switch ($refreshType) {
                case 'full':
                    return $this->refreshFullPage($page, $includeAssets);
                    
                case 'section':
                    return $this->refreshSection($page, $componentId);
                    
                case 'widget':
                    return $this->refreshWidget($page, $componentId);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid refresh type'
                    ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error refreshing page content in Live Designer: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'refresh_type' => $request->input('refresh_type'),
                'component_id' => $request->input('component_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh page content',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh entire page content
     */
    protected function refreshFullPage(Page $page, bool $includeAssets = false): JsonResponse
    {
        try {
            // Load page with all relationships
            $page->load([
                'template.theme',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);

            $theme = $page->template->theme;
            
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
            
            // Generate full page HTML
            $fullPageHtml = $this->generateFullPageHtml($page, $templateRenderer, $menuService, $universalStylingService);

            $responseData = [
                'refresh_type' => 'full',
                'page_id' => $page->id,
                'html' => $fullPageHtml,
                'refreshed_at' => now()->toISOString()
            ];

            if ($includeAssets) {
                // Collect assets
                $sections = [];
                foreach ($page->sections as $section) {
                    $widgets = $widgetService->getWidgetsForSection($section->id);
                    $sections[$section->templateSection->slug ?? 'section_' . $section->id] = [
                        'id' => $section->id,
                        'widgets' => $widgets
                    ];
                }
                
                $allAssets = $widgetService->collectPageWidgetAssets($sections);
                $themeAssets = $this->collectThemeAssets($theme);
                
                $responseData['assets'] = [
                    'css' => array_unique(array_merge($themeAssets['css'], $allAssets['css'] ?? [])),
                    'js' => array_unique(array_merge($themeAssets['js'], $allAssets['js'] ?? []))
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Error in refreshFullPage: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refresh specific section
     */
    protected function refreshSection(Page $page, int $sectionId): JsonResponse
    {
        try {
            $section = \App\Models\PageSection::where('id', $sectionId)
                ->where('page_id', $page->id)
                ->with(['templateSection', 'pageSectionWidgets.widget'])
                ->first();

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'error' => 'Section not found'
                ], 404);
            }

            // For now, return section data - in a full implementation you might render section HTML
            $widgetService = app(\App\Services\WidgetService::class);
            $widgets = [];

            foreach ($section->pageSectionWidgets as $pageSectionWidget) {
                $widget = $pageSectionWidget->widget;
                if (!$widget) continue;

                $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
                $widgetHtml = $this->renderWidgetPreview($widget, $pageSectionWidget, $fieldValues, $page->template->theme);

                $widgets[] = [
                    'id' => $pageSectionWidget->id,
                    'widget_id' => $widget->id,
                    'name' => $widget->name,
                    'rendered_html' => $widgetHtml,
                    'field_values' => $fieldValues
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'refresh_type' => 'section',
                    'section_id' => $sectionId,
                    'section_name' => $section->templateSection->name ?? "Section {$sectionId}",
                    'widgets' => $widgets,
                    'refreshed_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in refreshSection: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refresh specific widget
     */
    protected function refreshWidget(Page $page, int $widgetId): JsonResponse
    {
        try {
            $pageSectionWidget = \App\Models\PageSectionWidget::where('id', $widgetId)
                ->with(['widget', 'pageSection'])
                ->whereHas('pageSection', function($query) use ($page) {
                    $query->where('page_id', $page->id);
                })
                ->first();

            if (!$pageSectionWidget) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget not found'
                ], 404);
            }

            $widget = $pageSectionWidget->widget;
            $widgetService = app(\App\Services\WidgetService::class);

            // Get fresh field values
            $fieldValues = $widgetService->getWidgetFieldValues($widget, $pageSectionWidget);

            // Render widget HTML
            $widgetHtml = $this->renderWidgetPreview($widget, $pageSectionWidget, $fieldValues, $page->template->theme);

            // Get content items if applicable
            $contentItems = [];
            if (!empty($pageSectionWidget->content_query)) {
                $contentItems = $this->getWidgetContentItems($widget, $pageSectionWidget->content_query);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'refresh_type' => 'widget',
                    'widget_id' => $widgetId,
                    'widget_name' => $widget->name,
                    'rendered_html' => $widgetHtml,
                    'field_values' => $fieldValues,
                    'content_items' => $contentItems,
                    'refreshed_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in refreshWidget: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get widget preview HTML for component library
     * 
     * @param Widget $widget
     * @param $theme
     * @return string
     */
    protected function getWidgetPreviewHtml(Widget $widget, $theme): string
    {
        try {
            // Generate a basic preview HTML for the widget
            // This would ideally render the widget with sample data
            
            $previewHtml = '<div class="widget-preview widget-' . $widget->slug . '">';
            $previewHtml .= '<div class="widget-preview-header">';
            $previewHtml .= '<i class="' . ($widget->icon ?: 'ri-puzzle-line') . '"></i>';
            $previewHtml .= '<span>' . $widget->name . '</span>';
            $previewHtml .= '</div>';
            $previewHtml .= '<div class="widget-preview-body">';
            $previewHtml .= $widget->description ?: 'No description available';
            $previewHtml .= '</div>';
            $previewHtml .= '</div>';
            
            return $previewHtml;
            
        } catch (\Exception $e) {
            Log::warning('Could not generate widget preview HTML: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug
            ]);
            
            return '<div class="widget-preview-error">Preview not available</div>';
        }
    }
}
