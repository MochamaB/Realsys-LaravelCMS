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

            // Render the widget view
            $html = view($viewPath, compact('widget', 'fields', 'settings'))->render();
            
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
}

