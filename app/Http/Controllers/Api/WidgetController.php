<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\PageSectionWidget;
use App\Services\WidgetService;
use App\Services\WidgetSchemaService;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WidgetController extends Controller
{
    protected $widgetService;
    protected $widgetSchemaService;
    protected $templateRenderer;

    public function __construct(
        WidgetService $widgetService, 
        WidgetSchemaService $widgetSchemaService,
        TemplateRenderer $templateRenderer
    ) {
        $this->widgetService = $widgetService;
        $this->widgetSchemaService = $widgetSchemaService;
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * List all available widgets (legacy method for old JS)
     */
    public function index(Request $request)
    {
        $query = Widget::query()->whereNull('deleted_at');
        if ($request->has('theme_id')) {
            $query->where('theme_id', $request->input('theme_id'));
        }
        $widgets = $query->orderBy('name')->get();
        return response()->json(['widgets' => $widgets]);
    }

    public function contentTypes($widgetId)
    {
        $widget = \App\Models\Widget::findOrFail($widgetId);
        // Get associated content types (already joined via pivot)
        $contentTypes = $widget->contentTypes()->get();
        return response()->json(['content_types' => $contentTypes]);
    }

    /**
     * Get all widget schemas for the active theme
     *
     * @return JsonResponse
     */
    public function getWidgetSchemas(): JsonResponse
    {
        try {
            $schemas = $this->widgetSchemaService->getAllWidgetSchemas();
            
            return response()->json([
                'success' => true,
                'schemas' => $schemas,
                'count' => count($schemas)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting widget schemas: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load widget schemas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schema for a specific widget
     *
     * @param Widget $widget
     * @return JsonResponse
     */
    public function getWidgetSchema(Widget $widget): JsonResponse
    {
        try {
            $schema = $this->widgetSchemaService->getWidgetSchema($widget);
            
            if (!$schema) {
                return response()->json([
                    'error' => 'Widget schema not found',
                    'widget_id' => $widget->id
                ], 404);
            }

            return response()->json([
                'success' => true,
                'schema' => $schema
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting widget schema', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to load widget schema',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample data for a widget
     *
     * @param Widget $widget
     * @return JsonResponse
     */
    public function getWidgetSampleData(Widget $widget): JsonResponse
    {
        try {
            $sampleData = $this->widgetSchemaService->getWidgetSampleData($widget);
            
            return response()->json([
                'success' => true,
                'sample_data' => $sampleData,
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting widget sample data', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to generate sample data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render a widget for the canvas with real data
     *
     * @param Widget $widget
     * @param Request $request
     * @return JsonResponse
     */
    public function renderWidget(Widget $widget, Request $request): JsonResponse
    {
        try {
            // Get page section widget ID if provided
            $pageSectionWidgetId = $request->input('page_section_widget_id');
            $pageSectionWidget = null;
            $useCustomData = false; // Add missing variable

            if ($pageSectionWidgetId) {
                // Get the PageSectionWidget record
                $pageSectionWidget = PageSectionWidget::with(['widget.theme'])
                    ->where('id', $pageSectionWidgetId)
                    ->first();
            }

            // Get widget field values using WidgetService
            $fieldValues = $this->widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
            
            // Get widget settings
            $settings = $pageSectionWidget ? ($pageSectionWidget->settings ?? []) : [];

            // Add debugging for content query
            if ($pageSectionWidget) {
                \Log::debug('PageSectionWidget content query data', [
                    'page_section_widget_id' => $pageSectionWidget->id,
                    'content_query' => $pageSectionWidget->content_query,
                    'content_query_type' => gettype($pageSectionWidget->content_query),
                    'settings' => $settings,
                    'field_values_count' => count($fieldValues),
                    'field_values_keys' => array_keys($fieldValues),
                    'field_values_sample' => array_slice($fieldValues, 0, 3, true)
                ]);
                
                // Additional debugging for Featured Image widget
                if ($widget->slug === 'featuredimage') {
                    \Log::debug('Featured Image Widget Debug', [
                        'widget_id' => $widget->id,
                        'widget_slug' => $widget->slug,
                        'content_query' => $pageSectionWidget->content_query,
                        'field_values' => $fieldValues,
                        'settings' => $settings,
                        'has_title' => isset($fieldValues['title']),
                        'has_image' => isset($fieldValues['image']),
                        'has_caption' => isset($fieldValues['caption']),
                        'has_link_url' => isset($fieldValues['link_url']),
                        'title_value' => $fieldValues['title'] ?? 'NOT_SET',
                        'image_value' => $fieldValues['image'] ?? 'NOT_SET',
                        'caption_value' => $fieldValues['caption'] ?? 'NOT_SET',
                        'link_url_value' => $fieldValues['link_url'] ?? 'NOT_SET'
                    ]);
                }
            }

            // Resolve widget view path
            $viewPath = $this->widgetService->resolveWidgetViewPath($widget);

            \Log::debug('Rendering widget for canvas', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'view_path' => $viewPath,
                'field_values_count' => count($fieldValues),
                'has_page_section_widget' => $pageSectionWidget !== null
            ]);

            // Check if view exists
            if (!view()->exists($viewPath)) {
                return response()->json([
                    'error' => 'Widget view not found',
                    'view_path' => $viewPath,
                    'html' => $this->getFallbackWidgetHTML($widget)
                ], 404);
            }

            // Render the widget view with all required variables
            $fields = $fieldValues; // Alias for widget views
            $html = view($viewPath, compact('widget', 'fields', 'settings', 'useCustomData'))->render();
            
            \Log::debug('Widget HTML rendered', [
                'widget_id' => $widget->id,
                'html_length' => strlen($html),
                'html_preview' => substr(strip_tags($html), 0, 200),
                'contains_image_url' => strpos($html, 'storage/media') !== false,
                'contains_title' => !empty($fieldValues['title']) && strpos($html, $fieldValues['title']) !== false
            ]);

            return response()->json([
                'success' => true,
                'html' => $html,
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                ],
                'field_values' => $fieldValues,
                'settings' => $settings,
                'debug' => [
                    'view_path' => $viewPath,
                    'use_custom_data' => $useCustomData,
                    'has_page_section_widget' => !is_null($pageSectionWidget)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error rendering widget for canvas', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to render widget',
                'message' => $e->getMessage(),
                'html' => $this->getFallbackWidgetHTML($widget)
            ], 500);
        }
    }

    /**
     * Render widget preview for GrapesJS with enhanced features
     * Implements Phase 1.2 requirements: CSS scoping, asset injection, interactive markers
     *
     * @param Widget $widget
     * @param Request $request
     * @return JsonResponse
     */
    public function renderWidgetPreview(Widget $widget, Request $request): JsonResponse
    {
        try {
            // Get custom data if provided (for POST requests)
            $customData = $request->input('preview_data', []);
            $useCustomData = !empty($customData);
            
            // Get page section widget ID if provided
            $pageSectionWidgetId = $request->input('page_section_widget_id');
            $pageSectionWidget = null;

            if ($pageSectionWidgetId && !$useCustomData) {
                $pageSectionWidget = PageSectionWidget::with(['widget.theme'])
                    ->find($pageSectionWidgetId);
            }

            // Get widget field values - use custom data or database data
            if ($useCustomData) {
                $fieldValues = $this->processCustomPreviewData($widget, $customData);
                $settings = $customData['settings'] ?? [];
            } else {
                \Log::debug('Loading widget field values from database', [
                    'widget_id' => $widget->id,
                    'widget_slug' => $widget->slug,
                    'page_section_widget_id' => $pageSectionWidget ? $pageSectionWidget->id : null,
                    'content_query' => $pageSectionWidget ? $pageSectionWidget->content_query : null
                ]);
                
                $fieldValues = $this->widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
                $settings = $pageSectionWidget ? ($pageSectionWidget->settings ?? []) : [];
                
                \Log::debug('Widget field values loaded', [
                    'widget_id' => $widget->id,
                    'field_values_count' => count($fieldValues),
                    'field_values_keys' => array_keys($fieldValues),
                    'field_values_sample' => array_slice($fieldValues, 0, 3, true),
                    'settings_count' => count($settings)
                ]);
            }

            // If no real data, use sample data
            if (empty($fieldValues) && !$useCustomData) {
                \Log::debug('No field values found, using sample data', [
                    'widget_id' => $widget->id
                ]);
                $fieldValues = $this->widgetSchemaService->getWidgetSampleData($widget);
                \Log::debug('Sample data loaded', [
                    'widget_id' => $widget->id,
                    'sample_data_keys' => array_keys($fieldValues)
                ]);
            }

            // Resolve widget view path
            $viewPath = $this->widgetService->resolveWidgetViewPath($widget);

            \Log::debug('Rendering widget preview for GrapesJS', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'view_path' => $viewPath,
                'use_custom_data' => $useCustomData,
                'field_values_count' => count($fieldValues)
            ]);

            // Check if view exists
            if (!view()->exists($viewPath)) {
                return $this->getPreviewErrorResponse($widget, 'Widget view not found: ' . $viewPath);
            }

            // Render the widget view
            $rawHtml = view($viewPath, [
                'widget' => $widget,
                'fields' => $fieldValues,
                'settings' => $settings
            ])->render();

            // Apply Phase 1.2 enhancements
            $enhancedHtml = $this->enhanceWidgetPreview($rawHtml, $widget, $fieldValues, $settings);
            
            // Get theme assets for preview
            $themeAssets = $this->getThemeAssetsForPreview($widget);
            
            // Get widget schema for metadata
            $widgetSchema = $this->widgetSchemaService->getWidgetSchema($widget);

            return response()->json([
                'success' => true,
                'html' => $enhancedHtml,
                'css' => $themeAssets['css'],
                'js' => $themeAssets['js'],
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                    'category' => $widgetSchema['category'] ?? 'General'
                ],
                'data' => [
                    'field_values' => $fieldValues,
                    'settings' => $settings,
                    'schema' => $widgetSchema,
                    'preview_mode' => $useCustomData ? 'custom' : 'database'
                ],
                'meta' => [
                    'view_path' => $viewPath,
                    'render_time' => microtime(true) - LARAVEL_START,
                    'cache_key' => $this->generatePreviewCacheKey($widget, $fieldValues, $settings)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error rendering widget preview', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getPreviewErrorResponse($widget, $e->getMessage());
        }
    }

    /**
     * Process custom preview data for widget rendering
     *
     * @param Widget $widget
     * @param array $customData
     * @return array
     */
    protected function processCustomPreviewData(Widget $widget, array $customData): array
    {
        $processedData = [];
        $schema = $this->widgetSchemaService->getWidgetSchema($widget);
        
        if (!$schema) {
            return $customData['fields'] ?? [];
        }

        // Process each field according to its schema
        foreach ($schema['fields'] as $field) {
            $fieldSlug = $field['slug'];
            $value = $customData['fields'][$fieldSlug] ?? null;
            
            if ($value !== null) {
                $processedData[$fieldSlug] = $this->processFieldValue($field, $value);
            }
        }

        return $processedData;
    }

    /**
     * Process individual field value based on field type
     *
     * @param array $field
     * @param mixed $value
     * @return mixed
     */
    protected function processFieldValue(array $field, $value)
    {
        switch ($field['type']) {
            case 'image':
                // Convert media ID to URL if needed
                if (is_numeric($value)) {
                    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($value);
                    return $media ? $media->getUrl() : $value;
                }
                break;
                
            case 'repeater':
                // Process repeater items
                if (is_array($value)) {
                    return array_map(function($item) use ($field) {
                        $processedItem = [];
                        foreach ($field['subfields'] as $subfield) {
                            $subValue = $item[$subfield['slug']] ?? null;
                            if ($subValue !== null) {
                                $processedItem[$subfield['slug']] = $this->processFieldValue($subfield, $subValue);
                            }
                        }
                        return $processedItem;
                    }, $value);
                }
                break;
        }

        return $value;
    }

    /**
     * Enhance widget HTML with GrapesJS-compatible features
     * Implements: CSS scoping, interactive markers, responsive containers
     *
     * @param string $html
     * @param Widget $widget
     * @param array $fieldValues
     * @param array $settings
     * @return string
     */
    protected function enhanceWidgetPreview(string $html, Widget $widget, array $fieldValues, array $settings): string
    {
        // Create GrapesJS-compatible wrapper with interactive markers
        $widgetId = $widget->id;
        $widgetSlug = $widget->slug;
        
        // Add data attributes for GrapesJS editing
        $dataAttributes = [
            'data-gjs-type' => 'widget',
            'data-widget-id' => $widgetId,
            'data-widget-slug' => $widgetSlug,
            'data-widget-name' => $widget->name,
            'data-gjs-editable' => 'false',
            'data-gjs-selectable' => 'true',
            'data-gjs-hoverable' => 'true',
            'data-gjs-draggable' => '[data-section-type]',
            'data-gjs-droppable' => 'false'
        ];

        $attributeString = '';
        foreach ($dataAttributes as $key => $value) {
            $attributeString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        // Wrap in GrapesJS-compatible container with CSS scoping
        $enhancedHtml = '
        <div class="gjs-widget-wrapper widget-' . $widgetSlug . '-wrapper"' . $attributeString . '>
            <div class="gjs-widget-container">
                <div class="gjs-widget-content" data-widget-content="true">
                    ' . $html . '
                </div>
                <div class="gjs-widget-overlay" data-widget-overlay="true" style="display: none;">
                    <div class="gjs-widget-controls">
                        <button class="gjs-widget-edit-btn" data-action="edit-widget" title="Configure Widget">
                            <i class="ri-settings-line"></i>
                        </button>
                        <button class="gjs-widget-delete-btn" data-action="delete-widget" title="Delete Widget">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="gjs-widget-info">
                        <span class="gjs-widget-name">' . htmlspecialchars($widget->name) . '</span>
                    </div>
                </div>
            </div>
        </div>';

        return $enhancedHtml;
    }

    /**
     * Get theme assets for widget preview
     *
     * @param Widget $widget
     * @return array
     */
    protected function getThemeAssetsForPreview(Widget $widget): array
    {
        try {
            $theme = $widget->theme;
            if (!$theme) {
                return ['css' => '', 'js' => ''];
            }

            $themeConfig = json_decode(file_get_contents(resource_path("themes/{$theme->slug}/theme.json")), true);
            
            $css = '';
            $js = '';

            // Compile CSS files
            if (isset($themeConfig['css']) && is_array($themeConfig['css'])) {
                foreach ($themeConfig['css'] as $cssFile) {
                    $cssPath = public_path($cssFile);
                    if (file_exists($cssPath)) {
                        $css .= file_get_contents($cssPath) . "\n";
                    }
                }
            }

            // Add widget-specific CSS scoping
            $css .= "
                /* GrapesJS Widget Preview Scoping */
                .gjs-widget-wrapper {
                    position: relative;
                    margin: 10px 0;
                    border: 2px dashed transparent;
                    transition: border-color 0.2s ease;
                }
                .gjs-widget-wrapper:hover {
                    border-color: #405189;
                }
                .gjs-widget-wrapper.gjs-selected {
                    border-color: #405189;
                    box-shadow: 0 0 0 1px #405189;
                }
                .gjs-widget-overlay {
                    position: absolute;
                    top: -30px;
                    right: 0;
                    background: #405189;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    z-index: 1000;
                }
                .gjs-widget-controls {
                    display: inline-flex;
                    gap: 5px;
                }
                .gjs-widget-controls button {
                    background: transparent;
                    border: none;
                    color: white;
                    cursor: pointer;
                    padding: 2px;
                }
                .gjs-widget-controls button:hover {
                    background: rgba(255,255,255,0.2);
                }
            ";

            // Compile JS files (basic inclusion)
            if (isset($themeConfig['js']) && is_array($themeConfig['js'])) {
                foreach ($themeConfig['js'] as $jsFile) {
                    $jsPath = public_path($jsFile);
                    if (file_exists($jsPath)) {
                        $js .= file_get_contents($jsPath) . "\n";
                    }
                }
            }

            return ['css' => $css, 'js' => $js];

        } catch (\Exception $e) {
            \Log::warning('Error loading theme assets for preview', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);
            
            return ['css' => '', 'js' => ''];
        }
    }

    /**
     * Generate cache key for widget preview
     *
     * @param Widget $widget
     * @param array $fieldValues
     * @param array $settings
     * @return string
     */
    protected function generatePreviewCacheKey(Widget $widget, array $fieldValues, array $settings): string
    {
        return 'widget_preview_' . $widget->id . '_' . md5(serialize([$fieldValues, $settings]));
    }

    /**
     * Get error response for preview failures
     *
     * @param Widget $widget
     * @param string $error
     * @return JsonResponse
     */
    protected function getPreviewErrorResponse(Widget $widget, string $error): JsonResponse
    {
        $fallbackHtml = $this->getFallbackWidgetHTML($widget, $error);
        
        return response()->json([
            'success' => false,
            'error' => $error,
            'html' => $this->enhanceWidgetPreview($fallbackHtml, $widget, [], []),
            'css' => $this->getBasicPreviewCSS(),
            'js' => '',
            'widget' => [
                'id' => $widget->id,
                'name' => $widget->name,
                'slug' => $widget->slug
            ],
            'data' => [
                'field_values' => [],
                'settings' => [],
                'preview_mode' => 'error'
            ]
        ], 500);
    }

    /**
     * Get basic CSS for error previews
     *
     * @return string
     */
    protected function getBasicPreviewCSS(): string
    {
        return "
            .widget-fallback {
                padding: 20px;
                border: 2px dashed #dc3545;
                border-radius: 5px;
                text-align: center;
                background: #f8f9fa;
                color: #dc3545;
            }
        ";
    }

    /**
     * Get a list of available widgets for the block manager
     *
     * @return JsonResponse
     */
    public function getAvailableWidgets(): JsonResponse
    {
        try {
            $widgets = Widget::with(['theme'])
                ->whereHas('theme', function($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('name')
                ->get();

            $widgetBlocks = $widgets->map(function($widget) {
                return [
                    'id' => $widget->id,
                    'label' => $widget->name,
                    'category' => $widget->category ?? 'General',
                    'content' => [
                        'tagName' => 'div',
                        'attributes' => [
                            'data-widget-type' => 'widget',
                            'data-widget-id' => $widget->id,
                            'data-widget-slug' => $widget->slug,
                            'class' => 'widget-block'
                        ],
                        'components' => [
                            [
                                'tagName' => 'div',
                                'attributes' => ['class' => 'widget-placeholder'],
                                'content' => $widget->name
                            ]
                        ]
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'widgets' => $widgetBlocks
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting available widgets: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load available widgets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enhanced widget blocks with schema information for GrapesJS
     *
     * @return JsonResponse
     */
    public function getEnhancedWidgetBlocks(): JsonResponse
    {
        try {
            $schemas = $this->widgetSchemaService->getAllWidgetSchemas();
            
            $enhancedBlocks = collect($schemas)->map(function($schema) {
                return [
                    'id' => $schema['grapesjs']['block_id'],
                    'label' => $schema['name'],
                    'category' => $schema['category'],
                    'content' => [
                        'type' => $schema['grapesjs']['component_type'],
                        'tagName' => 'div',
                        'attributes' => [
                            'data-widget-type' => 'widget',
                            'data-widget-id' => $schema['database_id'],
                            'data-widget-slug' => $schema['slug'],
                            'class' => 'widget-component ' . $schema['slug'] . '-widget'
                        ],
                        'components' => [
                            [
                                'tagName' => 'div',
                                'attributes' => ['class' => 'widget-preview-container'],
                                'content' => 'Loading ' . $schema['name'] . '...'
                            ]
                        ],
                        'traits' => $this->convertSchemaToTraits($schema),
                        'schema' => $schema
                    ],
                    'media' => '<i class="' . $schema['icon'] . '"></i>',
                    'activate' => true
                ];
            });

            return response()->json([
                'success' => true,
                'blocks' => $enhancedBlocks->values(),
                'count' => $enhancedBlocks->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting enhanced widget blocks: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load enhanced widget blocks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert widget schema to GrapesJS traits
     *
     * @param array $schema
     * @return array
     */
    protected function convertSchemaToTraits(array $schema): array
    {
        $traits = [];
        
        foreach ($schema['fields'] as $field) {
            $trait = [
                'name' => $field['slug'],
                'label' => $field['label'],
                'type' => $this->mapFieldTypeToTrait($field['type']),
                'required' => $field['required']
            ];

            // Add options for select fields
            if ($field['type'] === 'select' && isset($field['options'])) {
                $trait['options'] = $field['options'];
            }

            // Handle repeater fields differently
            if ($field['type'] === 'repeater') {
                $trait['type'] = 'button';
                $trait['text'] = 'Configure ' . $field['label'];
                $trait['command'] = 'open-repeater-editor';
            }

            $traits[] = $trait;
        }

        return $traits;
    }

    /**
     * Map schema field types to GrapesJS trait types
     *
     * @param string $fieldType
     * @return string
     */
    protected function mapFieldTypeToTrait(string $fieldType): string
    {
        $traitMap = [
            'text' => 'text',
            'textarea' => 'textarea',
            'number' => 'number',
            'email' => 'text',
            'url' => 'text',
            'color' => 'color',
            'date' => 'text',
            'image' => 'text', // Will be enhanced later
            'file' => 'text',
            'select' => 'select',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'repeater' => 'button'
        ];

        return $traitMap[$fieldType] ?? 'text';
    }

    /**
     * Get fallback HTML for widget rendering errors
     *
     * @param Widget $widget
     * @param string|null $error
     * @return string
     */
    protected function getFallbackWidgetHTML(Widget $widget, ?string $error = null): string
    {
        return view('admin.widgets.fallback', [
            'widget' => $widget,
            'error' => $error ?? 'Widget could not be rendered'
        ])->render();
    }

    /**
     * Test method to render existing widget
     */
    public function testExistingWidget(): JsonResponse
    {
        try {
            // Test with the existing PageSectionWidget ID 1
            $pageSectionWidget = PageSectionWidget::with(['widget.theme'])->find(1);
            
            if (!$pageSectionWidget) {
                return response()->json(['error' => 'PageSectionWidget not found'], 404);
            }
            
            $widget = $pageSectionWidget->widget;
            
            // Get widget field values using WidgetService
            $fieldValues = $this->widgetService->getWidgetFieldValues($widget, $pageSectionWidget);
            
            // Get widget settings
            $settings = $pageSectionWidget->settings ?? [];
            
            // Resolve widget view path
            $viewPath = $this->widgetService->resolveWidgetViewPath($widget);
            
            \Log::debug('Testing existing widget', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'page_section_widget_id' => $pageSectionWidget->id,
                'content_query' => $pageSectionWidget->content_query,
                'field_values' => $fieldValues,
                'view_path' => $viewPath
            ]);
            
            return response()->json([
                'success' => true,
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                ],
                'page_section_widget' => [
                    'id' => $pageSectionWidget->id,
                    'content_query' => $pageSectionWidget->content_query,
                    'settings' => $settings
                ],
                'field_values' => $fieldValues,
                'view_path' => $viewPath
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error testing existing widget', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to test widget',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test method to debug Featured Image widget
     */
    public function testFeaturedImageWidget(): JsonResponse
    {
        try {
            // Find the Featured Image widget
            $widget = Widget::where('slug', 'featuredimage')->first();
            
            if (!$widget) {
                return response()->json([
                    'error' => 'Featured Image widget not found'
                ], 404);
            }
            
            // Get all PageSectionWidget records for this widget
            $pageSectionWidgets = PageSectionWidget::where('widget_id', $widget->id)->get();
            
            $debugData = [];
            
            foreach ($pageSectionWidgets as $psw) {
                $contentQuery = $psw->content_query ?? [];
                $settings = $psw->settings ?? [];
                
                // Get field values
                $fieldValues = $this->widgetService->getWidgetFieldValues($widget, $psw);
                
                $debugData[] = [
                    'page_section_widget_id' => $psw->id,
                    'page_section_id' => $psw->page_section_id,
                    'content_query' => $contentQuery,
                    'settings' => $settings,
                    'field_values' => $fieldValues,
                    'has_title' => isset($fieldValues['title']),
                    'has_image' => isset($fieldValues['image']),
                    'title_value' => $fieldValues['title'] ?? 'NOT_SET',
                    'image_value' => $fieldValues['image'] ?? 'NOT_SET'
                ];
            }
            
            // Get all content types
            $contentTypes = \App\Models\ContentType::all();
            
            // Get all content items
            $contentItems = \App\Models\ContentItem::with('fieldValues.field')->get();
            
            return response()->json([
                'success' => true,
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug
                ],
                'page_section_widgets' => $debugData,
                'content_types' => $contentTypes->map(function($ct) {
                    return [
                        'id' => $ct->id,
                        'name' => $ct->name,
                        'slug' => $ct->slug
                    ];
                }),
                'content_items' => $contentItems->map(function($ci) {
                    return [
                        'id' => $ci->id,
                        'title' => $ci->title,
                        'content_type_id' => $ci->content_type_id,
                        'status' => $ci->status,
                        'field_values_count' => $ci->fieldValues->count()
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to debug Featured Image widget',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // PHASE 4.1: UNIFIED LIVE PREVIEW SYSTEM
    // ========================================

    /**
     * Get content items that can be used with this widget for preview
     *
     * @param Widget $widget
     * @return JsonResponse
     */
    public function getWidgetContentOptions(Widget $widget): JsonResponse
    {
        try {
            $contentItems = [];
            
            // Get associated content types for this widget
            $contentTypes = $widget->contentTypeAssociations;
            
            \Log::debug('Getting widget content options', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'content_types_count' => $contentTypes->count()
            ]);
            
            foreach ($contentTypes as $contentType) {
                $items = \App\Models\ContentItem::where('content_type_id', $contentType->id)
                    ->where('status', 'published')
                    ->limit(20)
                    ->get(['id', 'title', 'slug']);
                
                \Log::debug('Found content items for type', [
                    'content_type' => $contentType->name,
                    'items_count' => $items->count()
                ]);
                
                foreach ($items as $item) {
                    $contentItems[] = [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'content_type' => $contentType->name,
                        'content_type_id' => $contentType->id
                    ];
                }
            }
            
            // If no associated content types, get some sample content items
            if (empty($contentItems)) {
                \Log::debug('No associated content types, getting sample items');
                
                $sampleItems = \App\Models\ContentItem::where('status', 'published')
                    ->with('contentType')
                    ->limit(10)
                    ->get(['id', 'title', 'slug', 'content_type_id']);
                
                foreach ($sampleItems as $item) {
                    $contentItems[] = [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'content_type' => $item->contentType->name ?? 'Unknown',
                        'content_type_id' => $item->content_type_id
                    ];
                }
            }
            
            \Log::debug('Returning content options', [
                'total_items' => count($contentItems)
            ]);
            
            return response()->json([
                'success' => true,
                'content_items' => $contentItems,
                'debug' => [
                    'widget_id' => $widget->id,
                    'associated_content_types' => $contentTypes->count(),
                    'total_items' => count($contentItems)
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting widget content options', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get widgets that can render a specific content type for preview
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getContentWidgetOptions(Request $request): JsonResponse
    {
        try {
            $contentTypeId = $request->input('content_type_id');
            $contentItemId = $request->input('content_item_id');
            
            if (!$contentTypeId && !$contentItemId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Either content_type_id or content_item_id is required'
                ], 400);
            }
            
            // Get content type ID from content item if needed
            if ($contentItemId && !$contentTypeId) {
                $contentItem = \App\Models\ContentItem::findOrFail($contentItemId);
                $contentTypeId = $contentItem->content_type_id;
            }
            
            \Log::debug('Getting content widget options', [
                'content_type_id' => $contentTypeId,
                'content_item_id' => $contentItemId
            ]);
            
            // Get widgets associated with this content type
            $widgets = Widget::whereHas('contentTypeAssociations', function ($query) use ($contentTypeId) {
                $query->where('content_type_id', $contentTypeId);
            })->get(['id', 'name', 'slug', 'description']);
            
            // If no associated widgets, get all available widgets as fallback
            if ($widgets->isEmpty()) {
                \Log::debug('No associated widgets found, getting all available widgets');
                $widgets = Widget::whereNull('deleted_at')
                    ->limit(20)
                    ->get(['id', 'name', 'slug', 'description']);
            }
            
            $widgetOptions = $widgets->map(function ($widget) {
                return [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                    'description' => $widget->description ?? 'No description available'
                ];
            });
            
            \Log::debug('Returning widget options', [
                'total_widgets' => $widgetOptions->count()
            ]);
            
            return response()->json([
                'success' => true,
                'widgets' => $widgetOptions,
                'debug' => [
                    'content_type_id' => $contentTypeId,
                    'total_widgets' => $widgetOptions->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting content widget options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render widget with specific content item for live preview
     *
     * @param Widget $widget
     * @param Request $request
     * @return JsonResponse
     */
    public function renderWidgetWithContent(Widget $widget, Request $request): JsonResponse
    {
        try {
            $contentItemId = $request->input('content_item_id');
            $fieldOverrides = $request->input('field_overrides', []);
            $settingsOverrides = $request->input('settings_overrides', []);
            $useGrapesJS = $request->input('use_grapesjs', false);
            
            \Log::debug('Rendering widget with content', [
                'widget_id' => $widget->id,
                'content_item_id' => $contentItemId,
                'use_grapesjs' => $useGrapesJS,
                'has_field_overrides' => !empty($fieldOverrides),
                'has_settings_overrides' => !empty($settingsOverrides)
            ]);
            
            // Get content item if specified
            $contentItem = null;
            if ($contentItemId) {
                $contentItem = \App\Models\ContentItem::with(['fieldValues.field', 'contentType'])->find($contentItemId);
                if (!$contentItem) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Content item not found'
                    ], 404);
                }
            }
            
            // Prepare widget data with content item context
            $fieldValues = $this->prepareWidgetDataWithContent($widget, $contentItem, $fieldOverrides);
            $settings = array_merge($settingsOverrides, []); // Default settings can be added here
            
            // Resolve widget view path
            $viewPath = $this->widgetService->resolveWidgetViewPath($widget);
            
            \Log::debug('Widget view resolution', [
                'widget_id' => $widget->id,
                'view_path' => $viewPath,
                'field_values_count' => count($fieldValues)
            ]);
            
            // Check if view exists
            if (!view()->exists($viewPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget view not found',
                    'view_path' => $viewPath,
                    'html' => $this->getFallbackWidgetHTML($widget, 'View not found: ' . $viewPath)
                ], 404);
            }
            
            // Render the widget view
            $rawHtml = view($viewPath, [
                'widget' => $widget,
                'fields' => $fieldValues,
                'settings' => $settings,
                'contentItem' => $contentItem
            ])->render();
            
            // Apply GrapesJS enhancements if requested
            $html = $useGrapesJS 
                ? $this->enhanceWidgetPreview($rawHtml, $widget, $fieldValues, $settings)
                : $rawHtml;
            
            // Get theme assets
            $assets = $this->widgetService->collectWidgetAssets($widget);
            
            \Log::debug('Widget rendered successfully', [
                'widget_id' => $widget->id,
                'html_length' => strlen($html),
                'assets_count' => count($assets['css']) + count($assets['js'])
            ]);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'assets' => $assets,
                'metadata' => [
                    'widget_id' => $widget->id,
                    'widget_slug' => $widget->slug,
                    'widget_name' => $widget->name,
                    'content_item_id' => $contentItemId,
                    'content_title' => $contentItem ? $contentItem->title : null,
                    'has_content' => !is_null($contentItem),
                    'preview_mode' => 'widget_with_content',
                    'render_time' => round((microtime(true) - LARAVEL_START) * 1000, 2),
                    'assets_count' => count($assets['css']) + count($assets['js']),
                    'use_grapesjs' => $useGrapesJS,
                    'cached' => false
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering widget with content', [
                'widget_id' => $widget->id,
                'content_item_id' => $request->input('content_item_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'html' => $this->getFallbackWidgetHTML($widget, $e->getMessage())
            ], 500);
        }
    }

    /**
     * Render content item through a specific widget for live preview
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function renderContentWithWidget(Request $request): JsonResponse
    {
        try {
            $contentItemId = $request->input('content_item_id');
            $widgetId = $request->input('widget_id');
            $settingsOverrides = $request->input('settings_overrides', []);
            $fieldMappingOverrides = $request->input('field_mapping_overrides', []);
            $useGrapesJS = $request->input('use_grapesjs', false);
            
            if (!$contentItemId || !$widgetId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Both content_item_id and widget_id are required'
                ], 400);
            }
            
            \Log::debug('Rendering content with widget', [
                'content_item_id' => $contentItemId,
                'widget_id' => $widgetId,
                'use_grapesjs' => $useGrapesJS,
                'has_settings_overrides' => !empty($settingsOverrides),
                'has_field_mapping_overrides' => !empty($fieldMappingOverrides)
            ]);
            
            // Get content item and widget
            $contentItem = \App\Models\ContentItem::with(['fieldValues.field', 'contentType'])->find($contentItemId);
            $widget = Widget::find($widgetId);
            
            if (!$contentItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'Content item not found'
                ], 404);
            }
            
            if (!$widget) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget not found'
                ], 404);
            }
            
            // Prepare widget data from content item
            $fieldValues = $this->prepareWidgetDataFromContent($widget, $contentItem, $fieldMappingOverrides);
            $settings = array_merge($settingsOverrides, []);
            
            // Resolve widget view path
            $viewPath = $this->widgetService->resolveWidgetViewPath($widget);
            
            \Log::debug('Content-widget rendering', [
                'content_item_id' => $contentItemId,
                'widget_id' => $widgetId,
                'view_path' => $viewPath,
                'field_values_count' => count($fieldValues)
            ]);
            
            // Check if view exists
            if (!view()->exists($viewPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget view not found',
                    'view_path' => $viewPath,
                    'html' => $this->getFallbackWidgetHTML($widget, 'View not found: ' . $viewPath)
                ], 404);
            }
            
            // Render the widget view with content data
            $rawHtml = view($viewPath, [
                'widget' => $widget,
                'fields' => $fieldValues,
                'settings' => $settings,
                'contentItem' => $contentItem
            ])->render();
            
            // Apply GrapesJS enhancements if requested
            $html = $useGrapesJS 
                ? $this->enhanceWidgetPreview($rawHtml, $widget, $fieldValues, $settings)
                : $rawHtml;
            
            // Get theme assets
            $assets = $this->widgetService->collectWidgetAssets($widget);
            
            \Log::debug('Content rendered through widget successfully', [
                'content_item_id' => $contentItemId,
                'widget_id' => $widgetId,
                'html_length' => strlen($html),
                'assets_count' => count($assets['css']) + count($assets['js'])
            ]);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'assets' => $assets,
                'metadata' => [
                    'content_item_id' => $contentItemId,
                    'content_title' => $contentItem->title,
                    'content_type' => $contentItem->contentType->name,
                    'widget_id' => $widgetId,
                    'widget_slug' => $widget->slug,
                    'widget_name' => $widget->name,
                    'preview_mode' => 'content_with_widget',
                    'render_time' => round((microtime(true) - LARAVEL_START) * 1000, 2),
                    'assets_count' => count($assets['css']) + count($assets['js']),
                    'field_mappings_count' => count($fieldMappingOverrides),
                    'use_grapesjs' => $useGrapesJS,
                    'cached' => false
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering content with widget', [
                'content_item_id' => $request->input('content_item_id'),
                'widget_id' => $request->input('widget_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'html' => '<div class="preview-error">Content rendering error: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    /**
     * Prepare widget field values with content item data
     *
     * @param Widget $widget
     * @param \App\Models\ContentItem|null $contentItem
     * @param array $fieldOverrides
     * @return array
     */
    protected function prepareWidgetDataWithContent(Widget $widget, $contentItem = null, array $fieldOverrides = []): array
    {
        // Start with sample data to ensure repeater fields have content
        $fieldValues = $this->widgetSchemaService->getWidgetSampleData($widget);
        
        // If we have a content item, try to map its data to widget fields
        if ($contentItem) {
            $contentData = $this->extractContentItemData($contentItem);
            
            // Apply intelligent field mapping (this will override sample data where applicable)
            $fieldValues = $this->applyContentToWidgetMapping($fieldValues, $contentData);
        }
        
        // Apply any field overrides (highest priority)
        if (!empty($fieldOverrides)) {
            $fieldValues = array_merge($fieldValues, $fieldOverrides);
        }
        
        // Ensure all repeater fields have at least sample data if they're empty
        $fieldValues = $this->ensureRepeaterFieldsHaveData($widget, $fieldValues);
        
        return $fieldValues;
    }

    /**
     * Prepare widget field values from content item data
     *
     * @param Widget $widget
     * @param \App\Models\ContentItem $contentItem
     * @param array $fieldMappingOverrides
     * @return array
     */
    protected function prepareWidgetDataFromContent(Widget $widget, $contentItem, array $fieldMappingOverrides = []): array
    {
        // Extract content item data
        $contentData = $this->extractContentItemData($contentItem);
        
        // Get widget schema for field mapping
        $widgetSchema = $this->widgetSchemaService->getWidgetSchema($widget);
        $widgetFields = $widgetSchema['fields'] ?? [];
        
        $fieldValues = [];
        
        // Apply intelligent field mapping
        foreach ($widgetFields as $widgetField) {
            $fieldName = $widgetField['name'];
            
            // Check for explicit field mapping override
            if (isset($fieldMappingOverrides[$fieldName])) {
                $contentFieldName = $fieldMappingOverrides[$fieldName];
                $fieldValues[$fieldName] = $contentData[$contentFieldName] ?? null;
                continue;
            }
            
            // Apply automatic field mapping based on field names and types
            $fieldValues[$fieldName] = $this->mapContentFieldToWidget($widgetField, $contentData);
        }
        
        // Add content item metadata
        $fieldValues['_content_item_id'] = $contentItem->id;
        $fieldValues['_content_title'] = $contentItem->title;
        $fieldValues['_content_type'] = $contentItem->contentType->name;
        
        return $fieldValues;
    }

    /**
     * Extract field data from content item
     *
     * @param \App\Models\ContentItem $contentItem
     * @return array
     */
    protected function extractContentItemData($contentItem): array
    {
        $data = [
            'title' => $contentItem->title,
            'slug' => $contentItem->slug,
            'status' => $contentItem->status,
            'created_at' => $contentItem->created_at,
            'updated_at' => $contentItem->updated_at
        ];
        
        // Extract field values
        foreach ($contentItem->fieldValues as $fieldValue) {
            $fieldName = $fieldValue->field->slug ?? $fieldValue->field->name;
            $data[$fieldName] = $fieldValue->value;
        }
        
        return $data;
    }

    /**
     * Ensure repeater fields have data for preview rendering
     *
     * @param Widget $widget
     * @param array $fieldValues
     * @return array
     */
    protected function ensureRepeaterFieldsHaveData(Widget $widget, array $fieldValues): array
    {
        $schema = $this->widgetSchemaService->getWidgetSchema($widget);
        if (!$schema || !isset($schema['fields'])) {
            return $fieldValues;
        }

        foreach ($schema['fields'] as $field) {
            if ($field['field_type'] === 'repeater' && empty($fieldValues[$field['slug']])) {
                // Generate sample data for empty repeater fields
                $fieldValues[$field['slug']] = $this->widgetSchemaService->generateSampleValue($field);
            }
        }

        return $fieldValues;
    }

    /**
     * Apply intelligent content-to-widget field mapping
     *
     * @param array $widgetFields
     * @param array $contentData
     * @return array
     */
    protected function applyContentToWidgetMapping(array $widgetFields, array $contentData): array
    {
        $mappedFields = $widgetFields;
        
        // Common field mappings
        $commonMappings = [
            'title' => ['title', 'name', 'heading'],
            'description' => ['description', 'content', 'excerpt', 'summary'],
            'image' => ['image', 'featured_image', 'thumbnail'],
            'url' => ['url', 'link', 'permalink'],
            'date' => ['date', 'created_at', 'published_at']
        ];
        
        // Apply simple field mappings
        foreach ($commonMappings as $widgetField => $contentFields) {
            if (isset($mappedFields[$widgetField])) {
                foreach ($contentFields as $contentField) {
                    if (isset($contentData[$contentField]) && !empty($contentData[$contentField])) {
                        $mappedFields[$widgetField] = $contentData[$contentField];
                        break;
                    }
                }
            }
        }
        
        // Handle repeater fields - look for content data that could populate repeater subfields
        foreach ($mappedFields as $fieldKey => $fieldValue) {
            if (is_array($fieldValue) && $this->isRepeaterField($fieldValue)) {
                $mappedFields[$fieldKey] = $this->mapContentToRepeaterField($fieldValue, $contentData);
            }
        }
        
        return $mappedFields;
    }
    
    /**
     * Check if a field value represents a repeater field structure
     *
     * @param mixed $fieldValue
     * @return bool
     */
    protected function isRepeaterField($fieldValue): bool
    {
        if (!is_array($fieldValue)) {
            return false;
        }
        
        // Check if it's an array of arrays (typical repeater structure)
        if (empty($fieldValue)) {
            return false;
        }
        
        // If the first element is an array with subfields, it's likely a repeater
        $firstElement = reset($fieldValue);
        return is_array($firstElement) && !empty($firstElement);
    }
    
    /**
     * Map content data to repeater field structure
     *
     * @param array $repeaterField
     * @param array $contentData
     * @return array
     */
    protected function mapContentToRepeaterField(array $repeaterField, array $contentData): array
    {
        // If we already have data in the repeater field, return it
        if (!empty($repeaterField)) {
            return $repeaterField;
        }
        
        // For counter widget specifically, try to create sample counter data from content
        if (isset($contentData['title']) || isset($contentData['description'])) {
            return [
                [
                    'icon' => $contentData['image'] ?? '/assets/admin/images/default-icon.png',
                    'top_text' => $contentData['title'] ?? 'Sample Counter',
                    'counter_number' => $this->extractNumberFromContent($contentData) ?? 100
                ],
                [
                    'icon' => '/assets/admin/images/default-icon.png',
                    'top_text' => 'Related Items',
                    'counter_number' => rand(50, 200)
                ]
            ];
        }
        
        return $repeaterField;
    }
    
    /**
     * Extract a number from content data for counter widgets
     *
     * @param array $contentData
     * @return int|null
     */
    protected function extractNumberFromContent(array $contentData): ?int
    {
        // Look for any numeric values in the content
        foreach ($contentData as $value) {
            if (is_numeric($value)) {
                return (int) $value;
            }
            
            if (is_string($value)) {
                // Extract numbers from strings
                preg_match('/\d+/', $value, $matches);
                if (!empty($matches)) {
                    return (int) $matches[0];
                }
            }
        }
        
        return null;
    }

    /**
     * Map individual content field to widget field
     *
     * @param array $widgetField
     * @param array $contentData
     * @return mixed
     */
    protected function mapContentFieldToWidget(array $widgetField, array $contentData)
    {
        $fieldName = $widgetField['name'];
        $fieldType = $widgetField['type'] ?? 'text';
        
        // Direct field name match
        if (isset($contentData[$fieldName])) {
            return $contentData[$fieldName];
        }
        
        // Type-based intelligent mapping
        switch ($fieldType) {
            case 'image':
                return $contentData['image'] ?? $contentData['featured_image'] ?? $contentData['thumbnail'] ?? null;
                
            case 'rich_text':
            case 'textarea':
                return $contentData['content'] ?? $contentData['description'] ?? $contentData['excerpt'] ?? null;
                
            case 'text':
                if ($fieldName === 'title') {
                    return $contentData['title'] ?? $contentData['name'] ?? null;
                }
                return $contentData[$fieldName] ?? null;
                
            case 'url':
                return $contentData['url'] ?? $contentData['link'] ?? null;
                
            case 'date':
                return $contentData['date'] ?? $contentData['created_at'] ?? null;
                
            default:
                return $contentData[$fieldName] ?? null;
        }
    }
}

