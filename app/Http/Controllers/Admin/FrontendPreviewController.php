<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\ContentItem;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\TemplateSection;
use App\Services\TemplateRenderer;
use App\Services\WidgetService;
use App\Services\WidgetSchemaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FrontendPreviewController extends Controller
{
    protected $templateRenderer;
    protected $widgetService;
    protected $widgetSchemaService;

    public function __construct(
        TemplateRenderer $templateRenderer,
        WidgetService $widgetService,
        WidgetSchemaService $widgetSchemaService
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->widgetService = $widgetService;
        $this->widgetSchemaService = $widgetSchemaService;
    }

    /**
     * Render widget preview using frontend rendering system
     *
     * @param Widget $widget
     * @param Request $request
     * @return JsonResponse
     */
    public function renderWidgetPreview(Widget $widget, Request $request): JsonResponse
    {
        try {
            // Render widget directly using widget service
            $html = $this->renderWidgetDirectly($widget, $request);
            
            // Collect widget assets
            $assets = $this->widgetService->collectWidgetAssets($widget);
            
            // Get theme assets
            $themeAssets = $this->getThemeAssets($widget);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'css' => $themeAssets['css'],
                'js' => $themeAssets['js'],
                'widget_assets' => $assets,
                'meta' => [
                    'render_time' => '< 100ms',
                    'cached' => false,
                    'renderer' => 'frontend'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Frontend preview error', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to render widget preview: ' . $e->getMessage(),
                'html' => '<div class="alert alert-danger">Preview Error: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    /**
     * Render widget with content item using frontend rendering
     *
     * @param Widget $widget
     * @param Request $request
     * @return JsonResponse
     */
    public function renderWidgetWithContent(Widget $widget, Request $request): JsonResponse
    {
        try {
            $contentId = $request->input('content_id');
            $contentItem = null;
            
            if ($contentId) {
                $contentItem = ContentItem::find($contentId);
            }
            
            // Render widget directly with content
            $html = $this->renderWidgetDirectly($widget, $request, $contentItem);
            
            // Collect assets
            $assets = $this->widgetService->collectWidgetAssets($widget);
            $themeAssets = $this->getThemeAssets($widget);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'css' => $themeAssets['css'],
                'js' => $themeAssets['js'],
                'widget_assets' => $assets,
                'content_item' => $contentItem ? [
                    'id' => $contentItem->id,
                    'title' => $contentItem->title,
                    'type' => $contentItem->content_type
                ] : null,
                'meta' => [
                    'render_time' => '< 150ms',
                    'cached' => false,
                    'renderer' => 'frontend'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Frontend preview with content error', [
                'widget_id' => $widget->id,
                'content_id' => $request->input('content_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to render widget with content: ' . $e->getMessage(),
                'html' => '<div class="alert alert-danger">Preview Error: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    /**
     * Render widget directly using widget service instead of section rendering
     *
     * @param Widget $widget
     * @param Request $request
     * @param ContentItem|null $contentItem
     * @return string
     */
    protected function renderWidgetDirectly(Widget $widget, Request $request, ContentItem $contentItem = null): string
    {
        // Prepare widget settings
        $settings = $this->prepareWidgetSettings($widget, $request, $contentItem);
        
        // Create a temporary PageSectionWidget for the widget service
        $pageSectionWidget = new PageSectionWidget([
            'widget_id' => $widget->id,
            'position' => 1,
            'settings' => $settings
        ]);
        $pageSectionWidget->widget = $widget;
        
        // Get widget data using the widget service
        $widgetData = $this->widgetService->prepareWidgetData($widget, $pageSectionWidget);
        
        // Get the widget view path
        $viewPath = $this->widgetService->resolveWidgetViewPath($widget);
        
        if (!$viewPath || !\View::exists($viewPath)) {
            throw new \Exception("Widget view not found: {$viewPath}");
        }
        
        // Render the widget view directly
        return \View::make($viewPath, $widgetData)->render();
    }

    /**
     * Prepare widget settings with default or content data
     *
     * @param Widget $widget
     * @param Request $request
     * @param ContentItem|null $contentItem
     * @return array
     */
    protected function prepareWidgetSettings(Widget $widget, Request $request, ContentItem $contentItem = null): array
    {
        // Start with sample data
        $settings = $this->widgetSchemaService->getWidgetSampleData($widget);
        
        // If content item provided, map its data to widget fields
        if ($contentItem) {
            $settings = $this->mapContentToWidget($settings, $contentItem);
        }
        
        // Apply any field overrides from request
        $fieldOverrides = $request->input('field_overrides', []);
        if (!empty($fieldOverrides)) {
            $settings = array_merge($settings, $fieldOverrides);
        }
        
        return $settings;
    }

    /**
     * Map content item data to widget settings
     *
     * @param array $settings
     * @param ContentItem $contentItem
     * @return array
     */
    protected function mapContentToWidget(array $settings, ContentItem $contentItem): array
    {
        // Basic field mappings
        $mappings = [
            'title' => $contentItem->title,
            'description' => $contentItem->meta_description ?? '',
            'content' => $contentItem->content ?? '',
            'image' => $contentItem->featured_image ?? '',
            'url' => url("/content/{$contentItem->slug}"),
            'date' => $contentItem->created_at->format('Y-m-d')
        ];
        
        // Apply mappings where widget fields exist
        foreach ($mappings as $field => $value) {
            if (array_key_exists($field, $settings) && !empty($value)) {
                $settings[$field] = $value;
            }
        }
        
        // Handle repeater fields - for counter widget, create sample counters from content
        foreach ($settings as $key => $value) {
            if (is_array($value) && empty($value)) {
                // This might be a repeater field, populate with sample data
                $settings[$key] = $this->generateRepeaterSampleData($key, $contentItem);
            }
        }
        
        return $settings;
    }

    /**
     * Generate sample data for repeater fields
     *
     * @param string $fieldKey
     * @param ContentItem $contentItem
     * @return array
     */
    protected function generateRepeaterSampleData(string $fieldKey, ContentItem $contentItem): array
    {
        // For counter widget
        if ($fieldKey === 'counters') {
            return [
                [
                    'icon' => '/assets/admin/images/default-icon.png',
                    'top_text' => $contentItem->title ?? 'Sample Counter',
                    'counter_number' => 100
                ],
                [
                    'icon' => '/assets/admin/images/default-icon.png', 
                    'top_text' => 'Related Items',
                    'counter_number' => 25
                ]
            ];
        }
        
        return [];
    }

    /**
     * Get theme assets for preview
     *
     * @param Widget $widget
     * @return array
     */
    protected function getThemeAssets(Widget $widget): array
    {
        try {
            $theme = $widget->theme;
            $themeConfigPath = resource_path("themes/{$theme->slug}/theme.json");
            
            if (!file_exists($themeConfigPath)) {
                return ['css' => '', 'js' => ''];
            }
            
            $themeConfig = json_decode(file_get_contents($themeConfigPath), true);
            
            $css = '';
            $js = '';
            
            // Load CSS files
            if (isset($themeConfig['css'])) {
                foreach ($themeConfig['css'] as $cssFile) {
                    $cssPath = public_path($cssFile);
                    if (file_exists($cssPath)) {
                        $css .= file_get_contents($cssPath) . "\n";
                    }
                }
            }
            
            // Load JS files
            if (isset($themeConfig['js'])) {
                foreach ($themeConfig['js'] as $jsFile) {
                    $jsPath = public_path($jsFile);
                    if (file_exists($jsPath)) {
                        $js .= file_get_contents($jsPath) . "\n";
                    }
                }
            }
            
            return ['css' => $css, 'js' => $js];
            
        } catch (\Exception $e) {
            \Log::error('Error loading theme assets', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);
            
            return ['css' => '', 'js' => ''];
        }
    }
}
