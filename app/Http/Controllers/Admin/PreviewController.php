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
            // Find associated widget or use default
            $widget = $this->findContentWidget($contentItem);
            
            if (!$widget) {
                return response()->json([
                    'success' => false,
                    'error' => 'No widget found for this content type',
                    'html' => '<div class="preview-error">No widget associated with this content type</div>'
                ]);
            }
            
            // Prepare widget data with content item data
            $widgetData = $this->prepareContentPreviewData($widget, $contentItem);
            
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
                    'preview_mode' => 'content'
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
     * @param ContentItem $contentItem
     * @return JsonResponse
     */
    public function getContentWidgetOptions(ContentItem $contentItem): JsonResponse
    {
        try {
            // Get widgets associated with this content type
            $associatedWidgets = $contentItem->contentType->widgets()
                ->where('is_active', true)
                ->get(['id', 'name', 'slug', 'description']);
            
            // Get default content display widgets
            $defaultWidgets = Widget::whereHas('contentTypeAssociations', function ($query) use ($contentItem) {
                $query->where('content_type_id', $contentItem->content_type_id);
            })
            ->where('is_active', true)
            ->get(['id', 'name', 'slug', 'description']);
            
            $widgetOptions = [
                'associated' => $associatedWidgets->map(function ($widget) {
                    return [
                        'id' => $widget->id,
                        'name' => $widget->name,
                        'slug' => $widget->slug,
                        'description' => $widget->description
                    ];
                }),
                'available' => $defaultWidgets->map(function ($widget) {
                    return [
                        'id' => $widget->id,
                        'name' => $widget->name,
                        'slug' => $widget->slug,
                        'description' => $widget->description
                    ];
                })
            ];
            
            return response()->json([
                'success' => true,
                'widget_options' => $widgetOptions,
                'recommended_widget' => $associatedWidgets->first() ?? $defaultWidgets->first()
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
            ->where('is_active', true)
            ->first();
        
        if ($associatedWidget) {
            return $associatedWidget;
        }
        
        // Fallback to default content display widget
        return Widget::where('slug', 'default')
            ->where('is_active', true)
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
     * Test controller method for debugging
     *
     * @return JsonResponse
     */
    public function testController(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'PreviewController is working',
            'timestamp' => now()->toISOString(),
            'services' => [
                'widgetService' => class_basename($this->widgetService),
                'templateRenderer' => class_basename($this->templateRenderer)
            ]
        ]);
    }
}
