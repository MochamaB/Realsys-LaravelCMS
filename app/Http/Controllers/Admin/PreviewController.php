<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Services\WidgetService;
use App\Services\TemplateRenderer;
use App\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\View;

class PreviewController extends Controller
{
    protected $widgetService;
    protected $templateRenderer;
    protected $themeManager;

    public function __construct(WidgetService $widgetService, TemplateRenderer $templateRenderer, ThemeManager $themeManager)
    {
        $this->widgetService = $widgetService;
        $this->templateRenderer = $templateRenderer;
        $this->themeManager = $themeManager;
    
    }

    /**
     * Render widget preview
     *
     * @param Request $request
     * @param Widget $widget
     * @return JsonResponse
     */
    public function renderWidget(Request $request, Widget $widget): JsonResponse
    {
        try {
            // Get preview data from request
            $previewData = $request->input('preview_data', []);
            $contentItemId = $request->input('content_item_id');
            
            // Prepare widget data for preview
            $widgetData = $this->prepareWidgetPreviewData($widget, $previewData, $contentItemId);
            
            // Render widget view
            $html = $this->renderWidgetView($widget, $widgetData);
            
            // Collect widget assets
            $assets = $this->widgetService->collectWidgetAssets($widget);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'assets' => $assets,
                'widget_data' => $widgetData,
                'preview_metadata' => [
                    'widget_id' => $widget->id,
                    'widget_slug' => $widget->slug,
                    'widget_name' => $widget->name,
                    'has_content' => !empty($contentItemId),
                    'preview_mode' => 'widget'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'html' => '<div class="preview-error">Preview Error: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    /**
     * Render content item preview through associated widget
     *
     * @param Request $request
     * @param ContentItem $contentItem
     * @return JsonResponse
     */
    public function renderContent(Request $request, ContentItem $contentItem): JsonResponse
{
    try {
        // Check if a specific widget is requested
        $widgetId = $request->input('widget_id');
        $previewData = $request->input('preview_data', []);
        
        if ($widgetId) {
            // Scenario 1: Specific widget requested
            $widget = Widget::find($widgetId);
            
            if (!$widget) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget not found'
                ], 404);
            }
            
            // Validate that the widget can work with this content type
            $contentType = $contentItem->contentType;
            $canUseWidget = $widget->contentTypeAssociations()
                ->where('content_type_id', $contentType->id)
                ->exists();
            
            if (!$canUseWidget) {
                return response()->json([
                    'success' => false,
                    'error' => "Widget '{$widget->name}' is not associated with content type '{$contentType->name}'"
                ], 400);
            }
            
            // Prepare widget data for specific widget scenario
            $widgetData = $this->prepareContentWidgetData(
                $contentItem, 
                $widget, 
                ['preview_data' => $previewData]
            );
            
            $previewMode = 'content_with_widget';
            
        } else {
            // Scenario 2: Auto-select widget (existing logic)
            $widget = $this->findContentWidget($contentItem);
            
            if (!$widget) {
                return response()->json([
                    'success' => false,
                    'error' => 'No widget found for this content type',
                    'html' => '<div class="preview-error">No widget associated with this content type</div>'
                ]);
            }
            
            // Prepare widget data for auto-selected widget scenario
            $widgetData = $this->prepareContentPreviewData($widget, $contentItem);
            
            // Apply any preview data overrides from request
            if (!empty($previewData)) {
                $widgetData = array_merge($widgetData, $previewData);
            }
            
            $previewMode = 'content_auto_widget';
        }
        
        // Render widget view with content data
        $html = $this->renderWidgetView($widget, $widgetData);
        
        // Collect widget assets
        $assets = $this->widgetService->collectWidgetAssets($widget);
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'assets' => $assets,
            'widget_data' => $widgetData,
            'preview_metadata' => [
                'content_item_id' => $contentItem->id,
                'content_type' => $contentItem->contentType->name,
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'widget_name' => $widget->name,
                'preview_mode' => $previewMode,
                'widget_specified' => !empty($widgetId)
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Content preview error', [
            'content_item_id' => $contentItem->id,
            'widget_id' => $request->input('widget_id'),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'html' => '<div class="preview-error">Preview Error: ' . $e->getMessage() . '</div>'
        ], 500);
    }
}

    /**
     * Get content options for widget preview
     *
     * @param Widget $widget
     * @return JsonResponse
     */
    public function getWidgetContentOptions(Widget $widget): JsonResponse
    {
        try {
            $contentItems = [];
            
            // Get associated content types
            $contentTypes = $widget->contentTypeAssociations;
            
            foreach ($contentTypes as $contentType) {
                $items = ContentItem::where('content_type_id', $contentType->id)
                    ->where('status', 'published')
                    ->limit(20)
                    ->get(['id', 'title', 'slug']);
                
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
            
            return response()->json([
                'success' => true,
                'content_items' => $contentItems
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get widget options for content preview
     *
     * @param int $contentId
     * @return JsonResponse
     */
    public function getContentWidgetOptions($contentId): JsonResponse
    {
        try {
            // Find the content item by ID
            $contentItem = ContentItem::with('contentType')->findOrFail($contentId);
            $contentType = $contentItem->contentType;
            
            // Get active theme
            $activeTheme = \App\Models\Theme::where('is_active', true)->first();
            
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active theme found'
                ], 400);
            }

            // Get widgets that are associated with this content type and belong to active theme
            $widgets = Widget::where('theme_id', $activeTheme->id)
                ->whereHas('contentTypeAssociations', function($query) use ($contentType) {
                    $query->where('content_type_id', $contentType->id);
                })
                ->select('id', 'name', 'slug', 'description')
                ->orderBy('name')
                ->get();

            $widgetOptions = $widgets->map(function($widget) {
                return [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                    'description' => $widget->description ?? 'No description available'
                ];
            });

            return response()->json([
                'success' => true,
                'widgets' => $widgetOptions,
                'content_item' => [
                    'id' => $contentItem->id,
                    'title' => $contentItem->title,
                    'content_type' => $contentType->name
                ],
                'theme' => [
                    'id' => $activeTheme->id,
                    'name' => $activeTheme->name
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare widget data for preview
     */
    protected function prepareWidgetPreviewData(Widget $widget, array $previewData, $contentItemId = null): array
    {
        // Start with basic widget data
        $widgetData = [
            'id' => $widget->id,
            'name' => $widget->name,
            'slug' => $widget->slug,
            'view_path' => $this->widgetService->resolveWidgetViewPath($widget),
            'fields' => $previewData['fields'] ?? [],
            'settings' => $previewData['settings'] ?? [],
            'assets' => $this->widgetService->collectWidgetAssets($widget)
        ];
        
        // Add content data if content item specified
        if ($contentItemId) {
            $contentItem = ContentItem::find($contentItemId);
            if ($contentItem) {
                $widgetData['content'] = $this->prepareContentData($contentItem);
            }
        }
        
        return $widgetData;
    }

    /**
     * Prepare content data for preview
     */
    protected function prepareContentPreviewData(Widget $widget, ContentItem $contentItem): array
    {
        // Map content item fields to widget fields
        $contentData = $this->prepareContentData($contentItem);
        
        return [
            'id' => $widget->id,
            'name' => $widget->name,
            'slug' => $widget->slug,
            'view_path' => $this->widgetService->resolveWidgetViewPath($widget),
            'fields' => $contentData,
            'settings' => [], // Use default settings for content preview
            'content' => $contentData,
            'assets' => $this->widgetService->collectWidgetAssets($widget)
        ];
    }

    /**
     * Prepare content item data for widget rendering
     */
    protected function prepareContentData(ContentItem $contentItem): array
    {
        $data = [
            'id' => $contentItem->id,
            'title' => $contentItem->title,
            'slug' => $contentItem->slug,
            'content' => $contentItem->content,
            'excerpt' => $contentItem->excerpt,
            'status' => $contentItem->status,
            'published_at' => $contentItem->published_at,
            'created_at' => $contentItem->created_at,
            'updated_at' => $contentItem->updated_at
        ];
        
        // Add custom fields
        if ($contentItem->custom_fields) {
            foreach ($contentItem->custom_fields as $field => $value) {
                $data[$field] = $value;
            }
        }
        
        return $data;
    }

    /**
     * Find appropriate widget for content item
     */
    protected function findContentWidget(ContentItem $contentItem): ?Widget
    {
        // First, try to find widgets associated with this content type
        $associatedWidget = $contentItem->contentType->widgets()
         //   ->where('is_active', true)
            ->first();
        
        if ($associatedWidget) {
            return $associatedWidget;
        }
        
        // Fallback to default content display widget
        return Widget::where('slug', 'default')
         //   ->where('is_active', true)
            ->first();
    }

    /**
     * Render widget view with data
     */
    protected function renderWidgetView(Widget $widget, array $widgetData): string
    {
        try {
            if ($widget->theme) {
                $this->themeManager->registerThemeViewPaths($widget->theme);
            }
            $viewPath = $widgetData['view_path'];
            
            // Prepare view data
            $viewData = [
                'widget' => $widget,
                'fields' => $widgetData['fields'] ?? [],
                'settings' => $widgetData['settings'] ?? [],
                'content' => $widgetData['content'] ?? null
            ];
            
            // Render the view
            return View::make($viewPath, $viewData)->render();
            
        } catch (\Exception $e) {
            return '<div class="preview-error">Widget rendering error: ' . $e->getMessage() . '</div>';
        }
    }
        /**
     * Render content item with selected widget
     *
     * @param Request $request
     * @param ContentItem $contentItem
     * @param Widget $widget
     * @return JsonResponse
     */
    public function renderContentWithWidget(Request $request, ContentItem $contentItem, Widget $widget): JsonResponse
    {
        try {
            // Validate that the widget can work with this content type
            $contentType = $contentItem->contentType;
            $canUseWidget = $widget->contentTypeAssociations()
                ->where('content_type_id', $contentType->id)
                ->exists();
            
            if (!$canUseWidget) {
                return response()->json([
                    'success' => false,
                    'error' => "Widget '{$widget->name}' is not associated with content type '{$contentType->name}'"
                ], 400);
            }

            // Get preview data from request
            $previewData = $request->input('preview_data', []);
            $fieldMappingOverrides = $request->input('field_mapping_overrides', []);
            $widgetSettingsOverrides = $request->input('widget_settings_overrides', []);
            
            // Prepare widget data with content item mapping
            $widgetData = $this->prepareContentWidgetData($contentItem, $widget, [
                'field_mapping_overrides' => $fieldMappingOverrides,
                'widget_settings_overrides' => $widgetSettingsOverrides,
                'preview_data' => $previewData
            ]);

            // Render the widget with content data
            $html = $this->widgetService->renderWidget($widget, $widgetData);
            
            // Collect assets
            $assets = $this->collectWidgetAssets($widget);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'assets' => $assets,
                'metadata' => [
                    'widget_id' => $widget->id,
                    'widget_name' => $widget->name,
                    'content_item_id' => $contentItem->id,
                    'content_item_title' => $contentItem->title,
                    'content_type' => $contentType->name,
                    'render_time' => microtime(true) - LARAVEL_START
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Content widget preview error', [
                'content_item_id' => $contentItem->id,
                'widget_id' => $widget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to render content with widget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare widget data with content item mapping
     *
     * @param ContentItem $contentItem
     * @param Widget $widget
     * @param array $options
     * @return array
     */
    protected function prepareContentWidgetData(ContentItem $contentItem, Widget $widget, array $options = []): array
    {
        $fieldMappingOverrides = $options['field_mapping_overrides'] ?? [];
        $widgetSettingsOverrides = $options['widget_settings_overrides'] ?? [];
        $previewData = $options['preview_data'] ?? [];

        // Start with widget's default data
        $widgetData = $this->prepareWidgetData($widget, []);

        // Apply content item field mapping
        $contentFields = $contentItem->fieldValues()->with('field')->get();
        
        foreach ($contentFields as $fieldValue) {
            $fieldSlug = $fieldValue->field->slug;
            $fieldType = $fieldValue->field->type;
            $value = $fieldValue->value;

            // Apply field mapping overrides if provided
            if (isset($fieldMappingOverrides[$fieldSlug])) {
                $targetField = $fieldMappingOverrides[$fieldSlug];
                if (isset($widgetData[$targetField])) {
                    $widgetData[$targetField] = $value;
                }
            } else {
                // Use automatic field mapping based on common field names
                $this->applyAutomaticFieldMapping($widgetData, $fieldSlug, $value, $fieldType);
            }
        }

        // Apply basic content item properties
        $widgetData['title'] = $widgetData['title'] ?? $contentItem->title;
        $widgetData['content'] = $widgetData['content'] ?? $contentItem->content ?? '';
        $widgetData['created_at'] = $contentItem->created_at->format('Y-m-d H:i:s');
        $widgetData['updated_at'] = $contentItem->updated_at->format('Y-m-d H:i:s');

        // Apply widget settings overrides
        if (!empty($widgetSettingsOverrides)) {
            $widgetData = array_merge($widgetData, $widgetSettingsOverrides);
        }

        // Apply preview data overrides
        if (!empty($previewData)) {
            $widgetData = array_merge($widgetData, $previewData);
        }

        return $widgetData;
    }

    /**
     * Apply automatic field mapping based on common field names
     *
     * @param array &$widgetData
     * @param string $fieldSlug
     * @param mixed $value
     * @param string $fieldType
     * @return void
     */
    protected function applyAutomaticFieldMapping(array &$widgetData, string $fieldSlug, $value, string $fieldType): void
    {
        // Common field mappings
        $mappings = [
            'title' => ['title', 'heading', 'name'],
            'description' => ['description', 'summary', 'excerpt'],
            'content' => ['content', 'body', 'text'],
            'image' => ['image', 'featured_image', 'thumbnail'],
            'url' => ['url', 'link', 'href'],
            'date' => ['date', 'published_at', 'created_at']
        ];

        foreach ($mappings as $widgetField => $contentFields) {
            if (in_array($fieldSlug, $contentFields) && isset($widgetData[$widgetField])) {
                $widgetData[$widgetField] = $value;
                break;
            }
        }

        // Direct field name matching as fallback
        if (isset($widgetData[$fieldSlug])) {
            $widgetData[$fieldSlug] = $value;
        }
    }

/**
 * Show content item preview page
 *
 * @param \App\Models\ContentType $contentType
 * @param \App\Models\ContentItem $item
 * @return \Illuminate\View\View
 */
public function showContentItemPreview(\App\Models\ContentType $contentType, \App\Models\ContentItem $item)
{
    // Load fields and their values
    $item->load([
        'contentType',
        'fieldValues.field',
    ]);
    
    // Use contentItem variable name for the view to maintain consistency
    $contentItem = $item;
    
    return view('admin.content_items.preview', compact('contentType', 'contentItem'));
}

/**
 * Show content item preview with full theme context
 *
 * @param \App\Models\ContentType $contentType
 * @param \App\Models\ContentItem $item
 * @param Request $request
 * @return \Illuminate\Http\Response
 */
public function showContentItemWithThemePreview(\App\Models\ContentType $contentType, \App\Models\ContentItem $item, Request $request)
{
    try {
        // Load content item with relationships
        $item->load([
            'contentType',
            'fieldValues.field',
        ]);
        
        // Get active theme
        $activeTheme = \App\Models\Theme::where('is_active', true)->first();
        
        if (!$activeTheme) {
            return response()->view('admin.content_items.preview-error', [
                'error' => 'No active theme found',
                'contentItem' => $item
            ], 500);
        }
        
        // Get widget to use for preview (from request or auto-select)
        $widgetId = $request->get('widget_id');
        if ($widgetId) {
            $widget = \App\Models\Widget::where('id', $widgetId)
                ->where('theme_id', $activeTheme->id)
                ->first();
                
            if (!$widget) {
                return response()->view('admin.content_items.preview-error', [
                    'error' => 'Selected widget not found or not compatible with active theme',
                    'contentItem' => $item
                ], 404);
            }
        } else {
            // Auto-select compatible widget
            $widget = $this->findCompatibleWidget($item, $activeTheme);
            
            if (!$widget) {
                return response()->view('admin.content_items.preview-error', [
                    'error' => 'No compatible widgets found for this content type in the active theme',
                    'contentItem' => $item
                ], 404);
            }
        }
        
        // Create preview page structure
        $previewPage = $this->createPreviewPageStructure($activeTheme, $widget, $item);
        
        // Render the complete page with theme context
        $html = $this->renderContentItemWithTheme($previewPage, $widget, $item, $request);
        
        // Add preview enhancements
        $html = $this->addContentPreviewEnhancements($html, $widget, $item);
        
        return response($html)->header('Content-Type', 'text/html');
        
    } catch (\Exception $e) {
        \Log::error('Content item theme preview error', [
            'content_item_id' => $item->id,
            'content_type_id' => $contentType->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->view('admin.content_items.preview-error', [
            'error' => $e->getMessage(),
            'contentItem' => $item
        ], 500);
    }
}

/**
 * API endpoint for content item theme preview
 *
 * @param \App\Models\ContentType $contentType
 * @param \App\Models\ContentItem $item
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getContentItemThemePreview(\App\Models\ContentType $contentType, \App\Models\ContentItem $item, Request $request)
{
    try {
        // Load content item with relationships
        $item->load([
            'contentType',
            'fieldValues.field',
        ]);
        
        // Get active theme
        $activeTheme = \App\Models\Theme::where('is_active', true)->first();
        
        if (!$activeTheme) {
            return response()->json([
                'success' => false,
                'error' => 'No active theme found'
            ], 500);
        }
        
        // Get available widgets for this content type
        $availableWidgets = \App\Models\Widget::where('theme_id', $activeTheme->id)
            ->whereHas('contentTypeAssociations', function($query) use ($item) {
                $query->where('content_type_id', $item->content_type_id);
            })
        //    ->where('is_active', true)
            ->select('id', 'name', 'slug', 'description')
            ->get();
            
        // Get selected widget or auto-select
        $widgetId = $request->get('widget_id');
        if ($widgetId) {
            $selectedWidget = $availableWidgets->find($widgetId);
        } else {
            $selectedWidget = $availableWidgets->first();
        }
        
        if (!$selectedWidget) {
            return response()->json([
                'success' => false,
                'error' => 'No compatible widgets found',
                'available_widgets' => []
            ]);
        }
        
        // Prepare widget data with content mapping
        $widgetData = $this->prepareContentWidgetData($item, $selectedWidget, [
            'preview_data' => $request->get('preview_data', [])
        ]);
        
        // Render widget with theme context
        $html = $this->renderContentWidgetWithTheme($selectedWidget, $widgetData, $activeTheme);
        
        // Collect all assets (theme + widget)
        $assets = $this->collectThemeAndWidgetAssets($activeTheme, $selectedWidget);
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'assets' => $assets,
            'widget_data' => $widgetData,
            'available_widgets' => $availableWidgets->map(function($widget) {
                return [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                    'description' => $widget->description
                ];
            }),
            'selected_widget' => [
                'id' => $selectedWidget->id,
                'name' => $selectedWidget->name,
                'slug' => $selectedWidget->slug
            ],
            'theme' => [
                'id' => $activeTheme->id,
                'name' => $activeTheme->name,
                'slug' => $activeTheme->slug
            ],
            'content_item' => [
                'id' => $item->id,
                'title' => $item->title,
                'content_type' => $item->contentType->name
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Content item theme preview API error', [
            'content_item_id' => $item->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Find a compatible widget for the content item
 *
 * @param \App\Models\ContentItem $contentItem
 * @param \App\Models\Theme $theme
 * @return \App\Models\Widget|null
 */
protected function findCompatibleWidget(\App\Models\ContentItem $contentItem, \App\Models\Theme $theme): ?\App\Models\Widget
{
    return \App\Models\Widget::where('theme_id', $theme->id)
        ->whereHas('contentTypeAssociations', function($query) use ($contentItem) {
            $query->where('content_type_id', $contentItem->content_type_id);
        })
        ->where('is_active', true)
        ->orderBy('name')
        ->first();
}

/**
 * Create a temporary page structure for preview
 *
 * @param \App\Models\Theme $theme
 * @param \App\Models\Widget $widget
 * @param \App\Models\ContentItem $contentItem
 * @return \App\Models\Page
 */
protected function createPreviewPageStructure(\App\Models\Theme $theme, \App\Models\Widget $widget, \App\Models\ContentItem $contentItem): \App\Models\Page
{
    // Get or create a default template for the theme
    $template = \App\Models\Template::where('theme_id', $theme->id)
        ->where('is_default', true)
        ->first();
        
    if (!$template) {
        $template = \App\Models\Template::where('theme_id', $theme->id)->first();
    }
    
    if (!$template) {
        throw new \Exception("No template found for theme: {$theme->name}");
    }
    
    // Create a virtual page (not saved to database)
    $previewPage = new \App\Models\Page([
        'title' => "Preview: {$contentItem->title}",
        'slug' => 'content-preview',
        'template_id' => $template->id,
        'status' => 'draft'
    ]);
    
    // Set relationships
    $previewPage->setRelation('template', $template);
    $previewPage->template->setRelation('theme', $theme);
    
    return $previewPage;
}

/**
 * Render content item with full theme context
 *
 * @param \App\Models\Page $previewPage
 * @param \App\Models\Widget $widget
 * @param \App\Models\ContentItem $contentItem
 * @param Request $request
 * @return string
 */
protected function renderContentItemWithTheme(\App\Models\Page $previewPage, \App\Models\Widget $widget, \App\Models\ContentItem $contentItem, Request $request): string
{
    // Ensure theme namespace is registered
    $theme = $previewPage->template->theme;
    $this->templateRenderer->ensureThemeNamespaceIsRegistered($theme);
    
    // Load theme assets
    $this->themeManager->loadThemeAssets($theme);
    
    // Prepare widget data with content mapping
    $widgetData = $this->prepareContentWidgetData($contentItem, $widget, [
        'preview_data' => $request->get('preview_data', [])
    ]);
    
    // Create temporary PageSectionWidget for rendering
    $tempPageSectionWidget = new \App\Models\PageSectionWidget([
        'widget_id' => $widget->id,
        'position' => 1,
        'settings' => $widgetData
    ]);
    $tempPageSectionWidget->setRelation('widget', $widget);
    $tempPageSectionWidget->exists = true;
    
    // Create section data for theme rendering
    $sectionData = [
        'widgets' => [$tempPageSectionWidget],
        'theme' => $theme,
        'universalStyling' => app(\App\Services\UniversalStylingService::class),
        'previewMode' => true,
        'contentItem' => $contentItem
    ];
    
    // Try to render using content-preview section template
    $sectionView = 'theme::sections.content-preview';
    
    if (!\View::exists($sectionView)) {
        // Fallback to default section template
        $sectionView = 'theme::sections.default';
    }
    
    $sectionHtml = view($sectionView, $sectionData)->render();
    
    // Wrap in theme layout
    $layoutData = [
        'page' => $previewPage,
        'content' => $sectionHtml,
        'theme' => $theme,
        'previewMode' => true,
        'contentItem' => $contentItem
    ];
    
    // Try theme-specific layout or fallback to default
    $layoutView = 'theme::layouts.content-preview';
    if (!\View::exists($layoutView)) {
        $layoutView = 'theme::layouts.default';
    }
    
    if (!\View::exists($layoutView)) {
        $layoutView = 'theme::layouts.master';
    }
    
    return view($layoutView, $layoutData)->render();
}

/**
 * Render content widget with theme context (for API)
 *
 * @param \App\Models\Widget $widget
 * @param array $widgetData
 * @param \App\Models\Theme $theme
 * @return string
 */
protected function renderContentWidgetWithTheme(\App\Models\Widget $widget, array $widgetData, \App\Models\Theme $theme): string
{
    // Ensure theme namespace is registered
    $this->templateRenderer->ensureThemeNamespaceIsRegistered($theme);
    
    // Create temporary PageSectionWidget
    $tempPageSectionWidget = new \App\Models\PageSectionWidget([
        'widget_id' => $widget->id,
        'settings' => $widgetData
    ]);
    $tempPageSectionWidget->setRelation('widget', $widget);
    $tempPageSectionWidget->exists = true;
    
    // Get widget field values
    $fieldValues = $this->widgetService->getWidgetFieldValues($widget, $tempPageSectionWidget);
    
    // Merge with prepared data
    $fieldValues = array_merge($fieldValues, $widgetData);
    
    // Render widget view
    $viewPath = $this->widgetService->resolveWidgetViewPath($widget);
    
    $viewData = [
        'widget' => $widget,
        'fields' => $fieldValues,
        'settings' => $widgetData,
        'theme' => $theme,
        'previewMode' => true
    ];
    
    return view($viewPath, $viewData)->render();
}

/**
 * Collect theme and widget assets
 *
 * @param \App\Models\Theme $theme
 * @param \App\Models\Widget $widget
 * @return array
 */
protected function collectThemeAndWidgetAssets(\App\Models\Theme $theme, \App\Models\Widget $widget): array
{
    $assets = [
        'css' => [],
        'js' => [],
        'theme_css' => [],
        'theme_js' => [],
        'widget_css' => [],
        'widget_js' => []
    ];
    
    // Get theme assets
    if (isset($theme->css) && is_array($theme->css)) {
        $assets['theme_css'] = $theme->css;
        $assets['css'] = array_merge($assets['css'], $theme->css);
    }
    
    if (isset($theme->js) && is_array($theme->js)) {
        $assets['theme_js'] = $theme->js;
        $assets['js'] = array_merge($assets['js'], $theme->js);
    }
    
    // Get widget assets
    $widgetAssets = $this->widgetService->collectWidgetAssets($widget);
    
    if (isset($widgetAssets['css'])) {
        $assets['widget_css'] = $widgetAssets['css'];
        $assets['css'] = array_merge($assets['css'], $widgetAssets['css']);
    }
    
    if (isset($widgetAssets['js'])) {
        $assets['widget_js'] = $widgetAssets['js'];
        $assets['js'] = array_merge($assets['js'], $widgetAssets['js']);
    }
    
    // Remove duplicates
    $assets['css'] = array_unique($assets['css']);
    $assets['js'] = array_unique($assets['js']);
    
    return $assets;
}

/**
 * Add content preview enhancements to HTML
 *
 * @param string $html
 * @param \App\Models\Widget $widget
 * @param \App\Models\ContentItem $contentItem
 * @return string
 */
protected function addContentPreviewEnhancements(string $html, \App\Models\Widget $widget, \App\Models\ContentItem $contentItem): string
{
    $previewAssets = '
    <style>
        /* Content Preview Enhancements */
        .content-preview-mode {
            position: relative;
        }
        
        .content-preview-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #343a40;
            color: white;
            padding: 10px 20px;
            z-index: 9999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .content-preview-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
        }
        
        .content-preview-label {
            background: #007bff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        
        body.content-preview-mode {
            padding-top: 60px;
        }
        
        /* Widget highlighting */
        .widget-container,
        .widget,
        [data-widget-id] {
            position: relative;
        }
        
        .widget-highlight {
            outline: 2px solid #28a745 !important;
            outline-offset: 2px;
        }
        
        .widget-highlight::before {
            content: "' . addslashes($widget->name) . ' Widget";
            position: absolute;
            top: -25px;
            left: 0;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            z-index: 1000;
        }
    </style>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Add preview mode class
            document.body.classList.add("content-preview-mode");
            
            // Add preview toolbar
            const toolbar = document.createElement("div");
            toolbar.className = "content-preview-toolbar";
            toolbar.innerHTML = `
                <div class="content-preview-info">
                    <span class="content-preview-label">CONTENT PREVIEW</span>
                    <span>Content: ' . addslashes($contentItem->title) . '</span>
                    <span>Widget: ' . addslashes($widget->name) . '</span>
                    <span>Type: ' . addslashes($contentItem->contentType->name) . '</span>
                </div>
            `;
            document.body.insertBefore(toolbar, document.body.firstChild);
            
            // Highlight widgets
            const widgets = document.querySelectorAll(".widget, [data-widget-id], [class*=\"widget-\"]");
            widgets.forEach(widget => {
                widget.classList.add("widget-highlight");
            });
            
            // Send ready message to parent if in iframe
            if (window.parent !== window) {
                window.parent.postMessage({
                    type: "content-preview-ready",
                    content_item_id: ' . $contentItem->id . ',
                    widget_id: ' . $widget->id . ',
                    widgets_found: widgets.length
                }, "*");
            }
            
            console.log("Content preview loaded:", {
                content_item_id: ' . $contentItem->id . ',
                content_title: "' . addslashes($contentItem->title) . '",
                widget_id: ' . $widget->id . ',
                widget_name: "' . addslashes($widget->name) . '",
                widgets_found: widgets.length
            });
        });
    </script>
    ';
    
    // Insert before closing head tag
    $html = str_replace('</head>', $previewAssets . '</head>', $html);
    
    return $html;
}

/**
 * Test method for debugging
 */
public function test()
{
    return response()->json([
        'success' => true,
        'message' => 'PreviewController is working',
        'timestamp' => now()
    ]);
}
}
