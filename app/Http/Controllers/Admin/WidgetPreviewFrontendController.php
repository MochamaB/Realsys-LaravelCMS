<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\ContentItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WidgetPreviewFrontendController extends Controller
{
    /**
     * Show widget preview by loading an existing page that contains the widget
     *
     * @param Widget $widget
     * @param Request $request
     * @return Response
     */
    public function showWidgetPreview(Widget $widget, Request $request): Response
    {
        try {
            // Find a page that uses this widget, or use the first available page
            $page = $this->findPageWithWidget($widget);
            
            if (!$page) {
                // If no page uses this widget, find any page from the same theme
                $page = Page::whereHas('template', function($query) use ($widget) {
                    $query->where('theme_id', $widget->theme_id);
                })->where('status', 'published')->first();
            }
            
            if (!$page) {
                return response()->view('admin.widgets.preview-error', [
                    'widget' => $widget,
                    'error' => 'No published page found for this widget\'s theme'
                ], 404);
            }
            
            // Get preview parameters
            $previewParams = [
                'preview_mode' => true,
                'preview_widget_id' => $widget->id,
                'preview_widget_slug' => $widget->slug,
                'content_id' => $request->input('content_id'),
                'field_overrides' => $request->input('field_overrides'),
                'device' => $request->input('device', 'desktop')
            ];
            
            // Load the page through the normal frontend route with preview parameters
            $frontendRequest = $request->duplicate();
            $frontendRequest->merge($previewParams);
            
            // Use the frontend page controller
            $frontendController = app(\App\Http\Controllers\PageController::class);
            
            // Get the page response
            $response = $frontendController->show($page->slug);
            
            // Get the response content
            $html = $response->getContent();
            
            // Add preview enhancements
            $html = $this->addPreviewEnhancements($html, $widget, $previewParams);
            
            return response($html)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            \Log::error('Widget preview frontend error', [
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
     * Find a page that contains this widget
     *
     * @param Widget $widget
     * @return Page|null
     */
    protected function findPageWithWidget(Widget $widget): ?Page
    {
        return Page::whereHas('sections.widgets', function($query) use ($widget) {
            $query->where('widgets.id', $widget->id);
        })
        ->where('status', 'published')
        ->with(['template', 'sections.widgets'])
        ->first();
    }
    
    /**
     * Add preview-specific enhancements to the HTML
     *
     * @param string $html
     * @param Widget $widget
     * @param array $params
     * @return string
     */
    protected function addPreviewEnhancements(string $html, Widget $widget, array $params): string
    {
        // Add preview CSS and JS
        $previewAssets = '
        <style>
            /* Hide all widget containers except the target widget */
            .widget-container:not([data-widget-id="' . $widget->id . '"]) {
                display: none !important;
            }
            
            /* Hide universal widgets except the target widget */
            div:not([data-widget-id="' . $widget->id . '"])[data-widget-id] {
                display: none !important;
            }
            
            /* Highlight the target widget container */
            .widget-container[data-widget-id="' . $widget->id . '"],
            div[data-widget-id="' . $widget->id . '"] {
                border: 3px solid #007bff !important;
                border-radius: 8px !important;
                position: relative !important;
                margin: 20px 0 !important;
                padding: 10px !important;
            }
            
            /* Add a preview label */
            .widget-container[data-widget-id="' . $widget->id . '"]:before,
            div[data-widget-id="' . $widget->id . '"]:before {
                content: "Preview: ' . addslashes($widget->name) . '";
                position: absolute;
                top: -30px;
                left: 0;
                background: #007bff;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: bold;
                z-index: 1000;
            }
            
            /* Hide page sections that don\'t contain the target widget */
            .page-section:not(:has([data-widget-id="' . $widget->id . '"])) {
                display: none !important;
            }
            
            /* Ensure the widget is visible even if parent sections are hidden */
            [data-widget-id="' . $widget->id . '"] {
                display: block !important;
            }
            
            /* Device simulation */
            .preview-device-mobile {
                max-width: 375px;
                margin: 0 auto;
            }
            
            .preview-device-tablet {
                max-width: 768px;
                margin: 0 auto;
            }
            
            .preview-device-desktop {
                max-width: 100%;
            }
        </style>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Add preview mode class to body
                document.body.classList.add("widget-preview-mode");
                
                // Add device class if specified
                const device = "' . ($params['device'] ?? 'desktop') . '";
                document.body.classList.add("preview-device-" + device);
                
                // Find and enhance the target widget
                const targetWidgets = document.querySelectorAll(\'.widget[data-widget-id="' . $widget->id . '"]\');
                
                if (targetWidgets.length === 0) {
                    // If no widget found with data attribute, try to find by widget class or other means
                    const allWidgets = document.querySelectorAll(".widget");
                    if (allWidgets.length > 0) {
                        // Assume the first widget is our target if no specific match
                        allWidgets[0].setAttribute("data-widget-id", "' . $widget->id . '");
                        allWidgets[0].classList.add("widget-preview-target");
                    }
                }
                
                // Apply field overrides if provided
                const fieldOverrides = ' . json_encode($params['field_overrides'] ?? '{}') . ';
                if (fieldOverrides && typeof fieldOverrides === "object") {
                    // Apply field overrides to the widget content
                    // This would need to be implemented based on widget structure
                }
                
                // Send ready message to parent if in iframe
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: "preview-ready",
                        widget_id: ' . $widget->id . ',
                        widget_count: targetWidgets.length
                    }, "*");
                }
                
                console.log("Widget preview loaded:", {
                    widget_id: ' . $widget->id . ',
                    widget_slug: "' . $widget->slug . '",
                    device: device,
                    widgets_found: targetWidgets.length
                });
            });
        </script>
        ';
        
        // Insert before closing head tag
        $html = str_replace('</head>', $previewAssets . '</head>', $html);
        
        return $html;
    }
}
