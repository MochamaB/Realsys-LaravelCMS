<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\ContentItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Template;
use App\Models\TemplateSection;
use App\Services\TemplateRenderer;
use App\Services\WidgetService;
use App\Services\WidgetSchemaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WidgetPreviewPageController extends Controller
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
     * Render a full preview page with the widget
     *
     * @param Widget $widget
     * @param Request $request
     * @return Response
     */
    public function showPreviewPage(Widget $widget, Request $request): Response
    {
        try {
            // Get or create a preview page for this widget's theme
            $previewPage = $this->getOrCreatePreviewPage($widget);
            
            // Create/update the page section with the widget
            $this->setupPreviewPageSection($previewPage, $widget, $request);
            
            // Render the complete page using the frontend renderer
            $html = $this->templateRenderer->renderPage($previewPage, [
                'preview_mode' => true,
                'preview_widget_id' => $widget->id,
                'preview_widget_slug' => $widget->slug
            ]);
            
            // Add preview-specific CSS and JS for widget highlighting
            $html = $this->addPreviewEnhancements($html, $widget);
            
            return response($html)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            \Log::error('Widget preview page error', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('admin.widgets.preview-error', [
                'widget' => $widget,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get or create a preview page for the widget's theme
     *
     * @param Widget $widget
     * @return Page
     */
    protected function getOrCreatePreviewPage(Widget $widget): Page
    {
        $theme = $widget->theme;
        
        // Look for existing preview page
        $previewPage = Page::where('title', 'Widget Preview')
            ->where('slug', 'widget-preview')
            ->whereHas('template', function($query) use ($theme) {
                $query->where('theme_id', $theme->id);
            })
            ->first();
            
        if (!$previewPage) {
            // Create a new preview page
            $template = Template::where('theme_id', $theme->id)
                ->where('is_default', true)
                ->first();
                
            if (!$template) {
                $template = Template::where('theme_id', $theme->id)->first();
            }
            
            if (!$template) {
                throw new \Exception("No template found for theme: {$theme->name}");
            }
            
            $previewPage = Page::create([
                'title' => 'Widget Preview',
                'slug' => 'widget-preview',
                'template_id' => $template->id,
                'status' => 'draft', // Keep as draft so it doesn't appear in frontend
                'meta_title' => 'Widget Preview',
                'meta_description' => 'Preview page for widget testing'
            ]);
        }
        
        return $previewPage;
    }

    /**
     * Setup the page section with the widget for preview
     *
     * @param Page $previewPage
     * @param Widget $widget
     * @param Request $request
     * @return void
     */
    protected function setupPreviewPageSection(Page $previewPage, Widget $widget, Request $request): void
    {
        // Get the first template section (usually main content area)
        $templateSection = $previewPage->template->sections()->first();
        
        if (!$templateSection) {
            throw new \Exception("No template sections found for preview page");
        }
        
        // Get or create page section
        $pageSection = PageSection::firstOrCreate([
            'page_id' => $previewPage->id,
            'template_section_id' => $templateSection->id
        ], [
            'name' => 'Preview Section',
            'grid_id' => PageSection::generateUniqueGridId($templateSection->id),
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_w' => 12,
            'grid_h' => 4,
            'allows_widgets' => true,
            'position' => 1
        ]);
        
        // Clear existing widgets from this section
        PageSectionWidget::where('page_section_id', $pageSection->id)->delete();
        
        // Prepare widget settings with sample data or content data
        $settings = $this->prepareWidgetSettings($widget, $request);
        
        // Add the widget to the section
        PageSectionWidget::create([
            'page_section_id' => $pageSection->id,
            'widget_id' => $widget->id,
            'position' => 1,
            'settings' => $settings
        ]);
    }

    /**
     * Prepare widget settings for preview
     *
     * @param Widget $widget
     * @param Request $request
     * @return array
     */
    protected function prepareWidgetSettings(Widget $widget, Request $request): array
    {
        // Start with sample data from widget schema
        $settings = $this->widgetSchemaService->getWidgetSampleData($widget);
        
        // If content item is specified, map its data
        $contentId = $request->input('content_id');
        if ($contentId) {
            $contentItem = ContentItem::find($contentId);
            if ($contentItem) {
                $settings = $this->mapContentToWidgetSettings($settings, $contentItem);
            }
        }
        
        // Apply field overrides from request
        $fieldOverrides = $request->input('field_overrides', []);
        if (is_string($fieldOverrides)) {
            $fieldOverrides = json_decode($fieldOverrides, true) ?? [];
        }
        
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
    protected function mapContentToWidgetSettings(array $settings, ContentItem $contentItem): array
    {
        // Basic field mappings
        $mappings = [
            'title' => $contentItem->title,
            'description' => $contentItem->meta_description ?? $contentItem->content ?? '',
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
        
        return $settings;
    }

    /**
     * Add preview-specific enhancements to the HTML
     *
     * @param string $html
     * @param Widget $widget
     * @return string
     */
    protected function addPreviewEnhancements(string $html, Widget $widget): string
    {
        // Add preview CSS and JS
        $previewAssets = '
        <style>
            /* Preview-specific styles */
            body { 
                margin: 0; 
                padding: 0; 
                background: #f8f9fa;
            }
            
            /* Highlight the preview widget */
            .widget-preview-highlight {
                position: relative;
                box-shadow: 0 0 0 2px #007bff;
                border-radius: 4px;
            }
            
            .widget-preview-highlight::before {
                content: "' . $widget->name . ' Widget";
                position: absolute;
                top: -25px;
                left: 0;
                background: #007bff;
                color: white;
                padding: 2px 8px;
                font-size: 12px;
                border-radius: 3px;
                z-index: 1000;
            }
            
            /* Hide other sections if needed */
            .preview-hide-others .page-section:not(.preview-target) {
                opacity: 0.3;
            }
        </style>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Find and highlight the widget
                const widgets = document.querySelectorAll(".widget");
                if (widgets.length > 0) {
                    widgets[0].classList.add("widget-preview-highlight");
                }
                
                // Send ready message to parent if in iframe
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: "preview-ready",
                        widget_id: ' . $widget->id . '
                    }, "*");
                }
            });
        </script>
        ';
        
        // Insert before closing head tag
        $html = str_replace('</head>', $previewAssets . '</head>', $html);
        
        return $html;
    }
}
