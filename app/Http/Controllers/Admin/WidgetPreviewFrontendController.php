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
            
            try {
                // Get preview parameters
                $previewParams = [
                    'widget_id' => $widget->id,
                    'preview_mode' => true
                ];
                
                // Handle content integration if content_item_id is provided
                $contentItem = null;
                if ($request->has('content_item_id') && $request->get('content_item_id')) {
                    $contentItem = \App\Models\ContentItem::with('contentType')->find($request->get('content_item_id'));
                    if ($contentItem) {
                        $previewParams['content_item_id'] = $contentItem->id;
                        $previewParams['content_data'] = $contentItem->toArray();
                        
                        // Apply content-to-widget field mapping
                        $previewParams['apply_content_mapping'] = true;
                        $previewParams['content_title'] = $contentItem->title;
                        $previewParams['content_fields'] = $contentItem->fields ?? [];
                    }
                }
                
                // Add field overrides if provided
                if ($request->has('field_overrides')) {
                    $previewParams['field_overrides'] = $request->get('field_overrides');
                }
                
                // Create a new request instance with preview parameters
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
                \Log::error('Widget preview error: ' . $e->getMessage(), [
                    'widget_id' => $widget->id,
                    'exception' => $e
                ]);
                
                return view('admin.widgets.preview-error', [
                    'error' => $e->getMessage(),
                    'widget' => $widget
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Widget preview error: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'exception' => $e
            ]);
            
            return view('admin.widgets.preview-error', [
                'error' => $e->getMessage(),
                'widget' => $widget
            ]);
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
                
                // Apply content mapping if content item is provided
                const contentData = ' . json_encode($params['content_data'] ?? null) . ';
                const contentFields = ' . json_encode($params['content_fields'] ?? []) . ';
                const applyContentMapping = ' . json_encode($params['apply_content_mapping'] ?? false) . ';
                
                if (applyContentMapping && contentData) {
                    applyContentToWidget(contentData, contentFields);
                }
                
                // Apply field overrides if provided
                const fieldOverrides = ' . json_encode($params['field_overrides'] ?? '{}') . ';
                if (fieldOverrides && typeof fieldOverrides === "object") {
                    // Apply field overrides to the widget content
                    applyFieldOverridesToWidget(fieldOverrides);
                }
                
                function applyContentToWidget(contentData, contentFields) {
                    const targetWidget = document.querySelector(`[data-widget-id="' . $widget->id . '"]`);
                    if (!targetWidget) {
                        console.warn("Target widget not found for content mapping");
                        return;
                    }
                    
                    console.log("Applying content to widget:", {
                        widget_id: ' . $widget->id . ',
                        content_data: contentData,
                        content_fields: contentFields
                    });
                    
                    // Map content data to widget elements based on widget type
                    if (contentData.title) {
                        // Update title in h1, h2, h3 elements
                        const titleElements = targetWidget.querySelectorAll("h1, h2, h3, .section-title h1, .section-title h2, .section-title h3");
                        titleElements.forEach(element => {
                            element.textContent = contentData.title;
                            console.log("Updated title element:", element.tagName, "with:", contentData.title);
                        });
                    }
                    
                    if (contentData.description || contentData.content || contentData.excerpt) {
                        // Update description in p elements
                        const descText = contentData.description || contentData.content || contentData.excerpt;
                        const descElements = targetWidget.querySelectorAll("p:not(.text-muted)");
                        descElements.forEach(element => {
                            // Skip if this paragraph contains only icons or is very short (likely not content)
                            if (element.textContent.trim().length > 10) {
                                element.textContent = descText;
                                console.log("Updated description element with:", descText);
                            }
                        });
                    }
                    
                    // Apply custom content fields if available
                    if (contentFields && typeof contentFields === "object") {
                        Object.keys(contentFields).forEach(fieldKey => {
                            const fieldValue = contentFields[fieldKey];
                            
                            if (fieldKey === "title" && fieldValue) {
                                const titleElements = targetWidget.querySelectorAll("h1, h2, h3");
                                titleElements.forEach(element => {
                                    element.textContent = fieldValue;
                                });
                            }
                            
                            if (fieldKey === "description" && fieldValue) {
                                const descElements = targetWidget.querySelectorAll("p:not(.text-muted)");
                                descElements.forEach(element => {
                                    if (element.textContent.trim().length > 10) {
                                        element.textContent = fieldValue;
                                    }
                                });
                            }
                            
                            // Handle image fields
                            if (fieldValue && typeof fieldValue === "object" && fieldValue.url) {
                                const imgElements = targetWidget.querySelectorAll("img");
                                imgElements.forEach(element => {
                                    element.src = fieldValue.url;
                                    element.alt = fieldValue.alt || contentData.title || "";
                                });
                            }
                        });
                    }
                    
                    // Add visual indicator that content mapping was applied
                    targetWidget.style.position = "relative";
                    
                    // Remove any existing content indicator
                    const existingIndicator = targetWidget.querySelector(".content-mapping-indicator");
                    if (existingIndicator) {
                        existingIndicator.remove();
                    }
                    
                    // Add new content indicator
                    const indicator = document.createElement("div");
                    indicator.className = "content-mapping-indicator";
                    indicator.style.cssText = `
                        position: absolute;
                        top: -25px;
                        right: 0;
                        background: #28a745;
                        color: white;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                        font-weight: bold;
                        z-index: 1001;
                    `;
                    indicator.textContent = `Content: ${contentData.title}`;
                    targetWidget.appendChild(indicator);
                    
                    console.log("Content mapping applied successfully");
                }
                
                function applyFieldOverridesToWidget(fieldOverrides) {
                    const targetWidget = document.querySelector(`[data-widget-id="' . $widget->id . '"]`);
                    if (!targetWidget) return;
                    
                    Object.keys(fieldOverrides).forEach(fieldKey => {
                        const fieldValue = fieldOverrides[fieldKey];
                        const elements = targetWidget.querySelectorAll(`[data-field="${fieldKey}"]`);
                        
                        elements.forEach(element => {
                            element.textContent = fieldValue;
                        });
                    });
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
        
        return view('admin.content_items.preview_new', compact('contentType', 'contentItem'));
    }

    /**
     * Get available content items for widget preview
     *
     * @param Widget $widget
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContentOptions(Widget $widget)
    {
        try {
            $contentItems = [];
            
            // First try to get content items from widget's associated content types
            if ($widget->contentTypeAssociations()->exists()) {
                $contentTypes = $widget->contentTypeAssociations()->with('contentType')->get();
                
                foreach ($contentTypes as $association) {
                    $contentType = $association->contentType;
                    
                    // Get published content items of this type
                    $items = $contentType->contentItems()
                        ->where('status', 'published')
                        ->select('id', 'title', 'slug', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->limit(50)
                        ->get();
                    
                    foreach ($items as $item) {
                        $contentItems[] = [
                            'id' => $item->id,
                            'title' => $item->title,
                            'slug' => $item->slug,
                            'content_type' => $contentType->name,
                            'content_type_id' => $contentType->id,
                            'created_at' => $item->created_at->format('M j, Y')
                        ];
                    }
                }
            }
            
            // If no specific content associations or no items found, get recent content items from all types
            if (empty($contentItems)) {
                $recentItems = \App\Models\ContentItem::where('status', 'published')
                    ->with('contentType')
                    ->select('id', 'title', 'slug', 'content_type_id', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
                
                foreach ($recentItems as $item) {
                    $contentItems[] = [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'content_type' => $item->contentType->name ?? 'Unknown',
                        'content_type_id' => $item->content_type_id,
                        'created_at' => $item->created_at->format('M j, Y')
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'content_items' => $contentItems,
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting content options for widget: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load content options: ' . $e->getMessage()
            ], 500);
        }
    }
}
