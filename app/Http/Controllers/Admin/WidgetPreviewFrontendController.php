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
                
                // Get theme assets including widget-specific assets
                $assets = $this->getThemeAssets($widget->theme, $widget);
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
            
            /* Widget Editing System Styles */
            .widget-editing-mode .widget-editable {
                position: relative;
                transition: all 0.2s ease;
            }
            
            .widget-editing-mode .widget-editable:hover {
                outline: 2px solid #007bff;
                outline-offset: 2px;
                cursor: pointer;
            }
            
            .widget-editing-mode .widget-editable.selected {
                outline: 3px solid #0056b3;
                outline-offset: 2px;
                background-color: rgba(0, 123, 255, 0.05);
            }
            
            .widget-editing-mode .widget-label {
                position: absolute;
                top: -25px;
                left: 0;
                background: #007bff;
                color: white;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 500;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.2s ease;
                pointer-events: none;
            }
            
            .widget-editing-mode .widget-editable:hover .widget-label,
            .widget-editing-mode .widget-editable.selected .widget-label {
                opacity: 1;
            }
            
            .widget-editing-mode .widget-edit-button {
                position: absolute;
                top: -15px;
                right: -15px;
                background: #28a745;
                color: white;
                border: none;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                font-size: 12px;
                cursor: pointer;
                z-index: 1001;
                opacity: 0;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .widget-editing-mode .widget-editable:hover .widget-edit-button,
            .widget-editing-mode .widget-editable.selected .widget-edit-button {
                opacity: 1;
                transform: scale(1.1);
            }
            
            .widget-editing-mode .widget-edit-button:hover {
                background: #218838;
                transform: scale(1.2);
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
                
                // Initialize widget editing system if UniversalPreviewManager is available
                if (window.UniversalPreviewManager) {
                    // Enable widget editing for the preview container
                    const previewContainer = document.body;
                    window.UniversalPreviewManager.initializeWidgetEditing(previewContainer);
                    
                    // Add edit mode toggle capability
                    window.toggleWidgetEditMode = function() {
                        const isEditingEnabled = document.body.classList.contains("widget-editing-mode");
                        if (isEditingEnabled) {
                            window.UniversalPreviewManager.disableWidgetEditing(previewContainer);
                        } else {
                            window.UniversalPreviewManager.enableWidgetEditing(previewContainer);
                        }
                    };
                    
                    console.log("Widget editing system initialized for preview");
                }
                
                // Send ready message to parent if in iframe
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: "preview-ready",
                        widget_id: ' . $widget->id . ',
                        widget_count: targetWidgets.length,
                        editing_enabled: !!window.UniversalPreviewManager
                    }, "*");
                }
                
                console.log("Widget preview loaded:", {
                    widget_id: ' . $widget->id . ',
                    widget_slug: "' . $widget->slug . '",
                    device: device,
                    widgets_found: targetWidgets.length,
                    editing_system: !!window.UniversalPreviewManager
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
     * Render widget in isolation for preview
     *
     * @param Widget $widget
     * @param Request $request
     * @return Response
     */
    public function renderWidgetIsolated(Widget $widget, Request $request): Response
    {
        try {
            // Create temporary PageSectionWidget for preview
            $tempPageSectionWidget = new PageSectionWidget([
                'id' => 'preview-' . $widget->id, // Set a preview ID
                'widget_id' => $widget->id,
                'position' => 1,
                'settings' => $request->get('field_overrides', []),
                'content_query' => $this->buildContentQuery($widget, $request)
            ]);
            
            // Set the widget relationship
            $tempPageSectionWidget->setRelation('widget', $widget);
            
            // Mark as existing so Laravel doesn't try to save it
            $tempPageSectionWidget->exists = true;
            
            // Get widget field values (defaults + content overlay)
            $widgetService = app(\App\Services\WidgetService::class);
            $fieldValues = $widgetService->getWidgetFieldValues($widget, $tempPageSectionWidget);
            
            // Debug: Log the field values to see what's happening
            \Log::debug('Widget field values for preview', [
                'widget_id' => $widget->id,
                'content_item_id' => $request->get('content_item_id'),
                'content_query' => $tempPageSectionWidget->content_query,
                'field_values' => $fieldValues
            ]);
            
            // If content item is selected but no content data is showing, apply basic field mapping
            $contentItemId = $request->get('content_item_id');
            if ($contentItemId && $this->isShowingOnlyDefaults($fieldValues, $widget)) {
                $fieldValues = $this->applyBasicContentMapping($fieldValues, $contentItemId, $widget);
            }
            
            // Render widget in minimal theme context
            return $this->renderWidgetWithThemeContext($widget, $tempPageSectionWidget, $fieldValues);
            
        } catch (\Exception $e) {
            \Log::error('Isolated widget preview error: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'exception' => $e
            ]);
            
            return response()->view('admin.widgets.preview-error', [
                'error' => $e->getMessage(),
                'widget' => $widget
            ], 500);
        }
    }

    /**
     * Build content query for widget preview
     *
     * @param Widget $widget
     * @param Request $request
     * @return array
     */
    protected function buildContentQuery(Widget $widget, Request $request): array
    {
        $contentItemId = $request->get('content_item_id');
        
        if (!$contentItemId) {
            return []; // No content query = use defaults
        }
        
        $contentItem = \App\Models\ContentItem::find($contentItemId);
        if (!$contentItem) {
            return [];
        }
        
        // Build content query for specific content item (matching WidgetService expectations)
        return [
            'content_type_id' => $contentItem->content_type_id,
            'content_item_ids' => [$contentItemId], // Use the correct key that WidgetService expects
            'limit' => 1,
            'order_by' => 'created_at',
            'order_direction' => 'desc'
        ];
    }

    /**
     * Render widget with theme context
     *
     * @param Widget $widget
     * @param PageSectionWidget $pageSectionWidget
     * @param array $fieldValues
     * @return Response
     */
    protected function renderWidgetWithThemeContext(Widget $widget, PageSectionWidget $pageSectionWidget, array $fieldValues): Response
    {
        // Get theme and ensure namespace is registered
        $theme = $widget->theme;
        $templateRenderer = app(\App\Services\TemplateRenderer::class);
        $templateRenderer->ensureThemeNamespaceIsRegistered($theme);
        
        // CRITICAL: Load theme assets (CSS/JS) - this was missing!
        $themeManager = app(\App\Services\ThemeManager::class);
        $themeManager->loadThemeAssets($theme);
        
        // Prepare widget data for rendering
        $widgetData = $this->prepareWidgetForRendering($widget, $pageSectionWidget, $fieldValues);
        
        // Create minimal section context
        $sectionData = [
            'widgets' => [$widgetData],
            'theme' => $theme,
            'universalStyling' => app(\App\Services\UniversalStylingService::class)
        ];
        
        try {
            // Try to render using widget-preview section template
            $sectionView = 'theme::sections.widget-preview';
            
            if (!\View::exists($sectionView)) {
                // Fallback to default section template
                $sectionView = 'theme::sections.default';
            }
            
            $html = view($sectionView, $sectionData)->render();
            
            // Wrap in minimal HTML structure with theme assets
            return response($this->wrapInPreviewStructure($html, $theme, $widget));
            
        } catch (\Exception $e) {
            \Log::error('Widget theme rendering error: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'theme_id' => $theme->id
            ]);
            
            // Fallback to simple widget rendering
            return $this->renderWidgetFallback($widget, $fieldValues);
        }
    }

    /**
     * Prepare widget data for rendering
     *
     * @param Widget $widget
     * @param PageSectionWidget $pageSectionWidget
     * @param array $fieldValues
     * @return PageSectionWidget
     */
    protected function prepareWidgetForRendering(Widget $widget, PageSectionWidget $pageSectionWidget, array $fieldValues): PageSectionWidget
    {
        // Set the field values on the PageSectionWidget for rendering
        $pageSectionWidget->fieldValues = $fieldValues;
        
        // Ensure the widget relationship is loaded
        if (!$pageSectionWidget->relationLoaded('widget')) {
            $pageSectionWidget->setRelation('widget', $widget);
        }
        
        return $pageSectionWidget;
    }

    /**
     * Wrap widget HTML in preview structure (mimicking theme layout)
     *
     * @param string $html
     * @param \App\Models\Theme $theme
     * @param Widget $widget
     * @return string
     */
    protected function wrapInPreviewStructure(string $html, \App\Models\Theme $theme, Widget $widget): string
    {
        // Debug logging
        \Log::info('wrapInPreviewStructure called', [
            'widget_id' => $widget->id,
            'widget_name' => $widget->name,
            'html_length' => strlen($html)
        ]);
        
        // Get widget assets using the same method as frontend render
        $widgetService = app(\App\Services\WidgetService::class);
        $widgetAssets = $widgetService->collectWidgetAssets($widget);
        
        // Build CSS links exactly like theme layout does
        $themeCssLinks = '';
        if (isset($theme->css) && is_array($theme->css)) {
            foreach ($theme->css as $css) {
                $themeCssLinks .= '<link rel="stylesheet" href="' . $css . '" />' . "\n        ";
            }
        }
        
        // Build widget CSS links
        $widgetCssLinks = '';
        if (isset($widgetAssets['css']) && is_array($widgetAssets['css'])) {
            foreach ($widgetAssets['css'] as $css) {
                $widgetCssLinks .= '<link rel="stylesheet" href="' . $css . '" />' . "\n        ";
            }
        }
        
        // Build JS scripts exactly like theme layout does
        $themeJsScripts = '';
        if (isset($theme->js) && is_array($theme->js)) {
            foreach ($theme->js as $js) {
                $themeJsScripts .= '<script src="' . $js . '"></script>' . "\n        ";
            }
        }
        
        // Build widget JS scripts
        $widgetJsScripts = '';
        if (isset($widgetAssets['js']) && is_array($widgetAssets['js'])) {
            foreach ($widgetAssets['js'] as $js) {
                $widgetJsScripts .= '<script src="' . $js . '"></script>' . "\n        ";
            }
        }
        
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Widget Preview: ' . $widget->name . '</title>
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="' . asset('assets/admin/images/favicon.ico') . '">
    
    <!-- Theme CSS (exactly like theme layout) -->
    ' . $themeCssLinks . '
    
    <!-- Widget CSS Assets (exactly like theme layout) -->
    ' . $widgetCssLinks . '
    
    <!-- Preview Specific Styles -->
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .widget-preview-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .preview-header {
            background: #e9ecef;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
        .widget-content {
        padding: 20px;
    }
    
    /* Widget Editing System Styles */
    .widget-editing-mode .widget-editable {
        position: relative;
        transition: all 0.2s ease;
    }
    
    .widget-editing-mode .widget-editable:hover {
        outline: 2px solid #007bff;
        outline-offset: 2px;
        cursor: pointer;
    }
    
    .widget-editing-mode .widget-editable.selected {
        outline: 3px solid #0056b3;
        outline-offset: 2px;
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .widget-editing-mode .widget-label {
        position: absolute;
        top: -25px;
        left: 0;
        background: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }
    
    .widget-editing-mode .widget-editable:hover .widget-label,
    .widget-editing-mode .widget-editable.selected .widget-label {
        opacity: 1;
    }
    
    .widget-editing-mode .widget-edit-button {
        position: absolute;
        top: -15px;
        right: -15px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        font-size: 12px;
        cursor: pointer;
        z-index: 1001;
        opacity: 0;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .widget-editing-mode .widget-editable:hover .widget-edit-button,
    .widget-editing-mode .widget-editable.selected .widget-edit-button {
        opacity: 1;
        transform: scale(1.1);
    }
    
    .widget-editing-mode .widget-edit-button:hover {
        background: #218838;
        transform: scale(1.2);
    }
</style>
</head>
<body>
    <div class="widget-preview-container">
        <div class="preview-header">
            Preview: ' . $widget->name . ' (' . $widget->slug . ')
        </div>
        <div class="widget-content">
            ' . $html . '
        </div>
    </div>
    
    <!-- Theme JavaScript (exactly like theme layout) -->
    ' . $themeJsScripts . '
    
    <!-- Widget JavaScript Assets (exactly like theme layout) -->
    ' . $widgetJsScripts . '
    
    <!-- Widget Editing System JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Widget preview loaded - initializing editing system");
            
            // Inject CSS styles directly via JavaScript
            const style = document.createElement("style");
            style.textContent = `
                .widget-editing-mode .widget-editable {
                    position: relative;
                    transition: all 0.2s ease;
                }
                
                .widget-editing-mode .widget-editable:hover {
                    outline: 2px solid #007bff !important;
                    outline-offset: 2px;
                    cursor: pointer;
                }
                
                .widget-editing-mode .widget-editable.selected {
                    outline: 3px solid #0056b3 !important;
                    outline-offset: 2px;
                    background-color: rgba(0, 123, 255, 0.05) !important;
                }
                
                .widget-editing-mode .widget-label {
                    position: absolute;
                    top: -25px;
                    left: 0;
                    background: #007bff;
                    color: white;
                    padding: 2px 8px;
                    border-radius: 3px;
                    font-size: 11px;
                    font-weight: 500;
                    z-index: 1000;
                    opacity: 0;
                    transition: opacity 0.2s ease;
                    pointer-events: none;
                }
                
                .widget-editing-mode .widget-editable:hover .widget-label,
                .widget-editing-mode .widget-editable.selected .widget-label {
                    opacity: 1;
                }
                
                .widget-editing-mode .widget-edit-button {
                    position: absolute;
                    top: -15px;
                    right: -15px;
                    background: #28a745;
                    color: white;
                    border: none;
                    border-radius: 50%;
                    width: 30px;
                    height: 30px;
                    font-size: 12px;
                    cursor: pointer;
                    z-index: 1001;
                    opacity: 0;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .widget-editing-mode .widget-editable:hover .widget-edit-button,
                .widget-editing-mode .widget-editable.selected .widget-edit-button {
                    opacity: 1;
                    transform: scale(1.1);
                }
                
                .widget-editing-mode .widget-edit-button:hover {
                    background: #218838;
                    transform: scale(1.2);
                }
            `;
            document.head.appendChild(style);
            console.log("Widget editing CSS injected");
            
            // Add widget editing functionality
            let editingEnabled = false;
            let selectedWidget = null;
            
            // Global function to toggle edit mode (called from parent iframe)
            window.toggleWidgetEditMode = function() {
                editingEnabled = !editingEnabled;
                
                if (editingEnabled) {
                    enableWidgetEditing();
                } else {
                    disableWidgetEditing();
                }
                
                console.log("Widget editing mode:", editingEnabled ? "enabled" : "disabled");
            };
            
            function enableWidgetEditing() {
                document.body.classList.add("widget-editing-mode");
                
                // Find all widgets in the preview
                const widgets = document.querySelectorAll(".widget, [data-widget-type], [class*=\"widget-\"]");
                
                widgets.forEach(widget => {
                    enhanceWidgetForEditing(widget);
                });
                
                console.log("Enhanced", widgets.length, "widgets for editing");
            }
            
            function disableWidgetEditing() {
                document.body.classList.remove("widget-editing-mode");
                
                // Remove editing enhancements
                const editableWidgets = document.querySelectorAll(".widget-editable");
                editableWidgets.forEach(widget => {
                    widget.classList.remove("widget-editable", "selected");
                    
                    // Remove added elements
                    const label = widget.querySelector(".widget-label");
                    const button = widget.querySelector(".widget-edit-button");
                    if (label) label.remove();
                    if (button) button.remove();
                });
                
                selectedWidget = null;
            }
            
            function enhanceWidgetForEditing(widget) {
                if (widget.classList.contains("widget-editable")) return;
                
                widget.classList.add("widget-editable");
                
                // Get widget type/name
                const widgetType = widget.dataset.widgetType || 
                                 widget.className.match(/widget-([a-zA-Z0-9-]+)/)?.[1] || 
                                 "' . $widget->slug . '";
                
                // Add label
                const label = document.createElement("div");
                label.className = "widget-label";
                label.textContent = widgetType;
                widget.appendChild(label);
                
                // Add edit button
                const editButton = document.createElement("button");
                editButton.className = "widget-edit-button";
                editButton.innerHTML = "✎";
                editButton.title = "Edit Widget";
                widget.appendChild(editButton);
                
                // Add click handlers
                widget.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    selectWidget(widget);
                });
                
                editButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openWidgetEditor(widget, widgetType);
                });
            }
            
            function selectWidget(widget) {
                // Remove previous selection
                if (selectedWidget) {
                    selectedWidget.classList.remove("selected");
                }
                
                // Select new widget
                selectedWidget = widget;
                widget.classList.add("selected");
                
                console.log("Selected widget:", widget);
            }
            
            function openWidgetEditor(widget, widgetType) {
                console.log("Opening editor for widget:", widgetType);
                
                // Send message to parent window about widget editing
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: "widget-edit-requested",
                        widget_id: "' . $widget->id . '",
                        widget_type: widgetType,
                        widget_element: widget.outerHTML
                    }, "*");
                }
                
                // For now, just show an alert
                alert("Widget Editor\\n\\nWidget: " + widgetType + "\\nID: ' . $widget->id . '\\n\\nModal editing will be implemented next!");
            }
            
            // Auto-enhance widgets on load
            const initialWidgets = document.querySelectorAll(".widget, [data-widget-type], [class*=\"widget-\"]");
            console.log("Found", initialWidgets.length, "widgets in preview");
            
            // Send ready message to parent
            if (window.parent !== window) {
                window.parent.postMessage({
                    type: "preview-ready",
                    widget_id: "' . $widget->id . '",
                    widgets_found: initialWidgets.length,
                    editing_available: true
                }, "*");
            }
        });
    </script>
</body>
</html>';
    }

    /**
     * Get theme assets (CSS and JS) including widget-specific assets
     *
     * @param Theme $theme
     * @param Widget|null $widget
     * @return array
     */
    protected function getThemeAssets(Theme $theme, Widget $widget = null): array
    {
        // Use the theme's actual asset configuration
        $cssAssets = [];
        $jsAssets = [];
        
        // Get theme assets from the theme configuration
        if (isset($theme->css) && is_array($theme->css)) {
            foreach ($theme->css as $css) {
                $cssAssets[] = $css;
            }
        }
        
        if (isset($theme->js) && is_array($theme->js)) {
            foreach ($theme->js as $js) {
                $jsAssets[] = $js;
            }
        }
        
        // If no theme assets are configured, try common paths
        if (empty($cssAssets)) {
            $themePublicPath = "/assets/themes/{$theme->slug}";
            $possibleCss = [
                "{$themePublicPath}/css/bootstrap.min.css",
                "{$themePublicPath}/css/style.css",
                "{$themePublicPath}/css/responsive.css",
                "{$themePublicPath}/style.css"
            ];
            
            foreach ($possibleCss as $css) {
                if (file_exists(public_path($css))) {
                    $cssAssets[] = $css;
                }
            }
        }
        
        if (empty($jsAssets)) {
            $themePublicPath = "/assets/themes/{$theme->slug}";
            $possibleJs = [
                "{$themePublicPath}/js/jquery.min.js",
                "{$themePublicPath}/js/bootstrap.bundle.min.js",
                "{$themePublicPath}/js/main.js"
            ];
            
            foreach ($possibleJs as $js) {
                if (file_exists(public_path($js))) {
                    $jsAssets[] = $js;
                }
            }
        }
        
        // Fallback to CDN if no local assets found
        if (empty($cssAssets)) {
            $cssAssets = [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
            ];
        }
        
        if (empty($jsAssets)) {
            $jsAssets = [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
            ];
        }
        
        // Add widget-specific assets if widget is provided
        if ($widget) {
            $this->addWidgetSpecificAssets($cssAssets, $jsAssets, $widget, $theme);
        }
        
        return [
            'css' => $cssAssets,
            'js' => $jsAssets
        ];
    }

    /**
     * Add widget-specific CSS and JS assets
     *
     * @param array &$cssAssets
     * @param array &$jsAssets  
     * @param Widget $widget
     * @param Theme $theme
     * @return void
     */
    protected function addWidgetSpecificAssets(array &$cssAssets, array &$jsAssets, Widget $widget, Theme $theme): void
    {
        $widgetPath = "/assets/themes/{$theme->slug}/widgets/{$widget->slug}";
        
        // Check for widget-specific CSS
        $widgetCss = "{$widgetPath}/style.css";
        if (file_exists(public_path($widgetCss))) {
            $cssAssets[] = $widgetCss;
        }
        
        // Check for widget-specific JS
        $widgetJs = "{$widgetPath}/script.js";
        if (file_exists(public_path($widgetJs))) {
            $jsAssets[] = $widgetJs;
        }
        
        // Also check for common widget asset names
        $possibleWidgetCss = [
            "{$widgetPath}/{$widget->slug}.css",
            "{$widgetPath}/widget.css"
        ];
        
        $possibleWidgetJs = [
            "{$widgetPath}/{$widget->slug}.js", 
            "{$widgetPath}/widget.js"
        ];
        
        foreach ($possibleWidgetCss as $css) {
            if (file_exists(public_path($css)) && !in_array($css, $cssAssets)) {
                $cssAssets[] = $css;
            }
        }
        
        foreach ($possibleWidgetJs as $js) {
            if (file_exists(public_path($js)) && !in_array($js, $jsAssets)) {
                $jsAssets[] = $js;
            }
        }
    }

    /**
     * Fallback widget rendering
     *
     * @param Widget $widget
     * @param array $fieldValues
     * @return Response
     */
    protected function renderWidgetFallback(Widget $widget, array $fieldValues): Response
    {
        $html = '<div class="widget-fallback">
            <h3>' . $widget->name . '</h3>
            <div class="field-values">
                ' . collect($fieldValues)->map(function($value, $key) {
                    return "<p><strong>{$key}:</strong> " . (is_array($value) ? json_encode($value) : $value) . "</p>";
                })->implode('') . '
            </div>
        </div>';
        
        return response($this->wrapInPreviewStructure($html, $widget->theme, $widget));
    }

    /**
     * Check if widget is showing only default values (no content data)
     *
     * @param array $fieldValues
     * @param Widget $widget
     * @return bool
     */
    protected function isShowingOnlyDefaults(array $fieldValues, Widget $widget): bool
    {
        // Get the default values for comparison
        $widgetService = app(\App\Services\WidgetService::class);
        $defaultValues = $widgetService->getWidgetFieldValues($widget, null);
        
        // If field values match defaults exactly, we're showing only defaults
        return $fieldValues === $defaultValues;
    }

    /**
     * Apply basic content mapping when no explicit mappings exist
     *
     * @param array $fieldValues
     * @param int $contentItemId
     * @param Widget $widget
     * @return array
     */
    protected function applyBasicContentMapping(array $fieldValues, int $contentItemId, Widget $widget): array
    {
        try {
            $contentItem = \App\Models\ContentItem::with('fieldValues.field')->find($contentItemId);
            if (!$contentItem) {
                return $fieldValues;
            }

            \Log::debug('Applying basic content mapping', [
                'content_item_id' => $contentItemId,
                'widget_slug' => $widget->slug,
                'content_item_title' => $contentItem->title
            ]);

            // Basic mapping rules for common widget fields
            $mappingRules = [
                'title' => ['title', 'name', 'heading', 'subject'],
                'description' => ['description', 'content', 'body', 'text', 'summary'],
                'icon' => ['icon', 'image', 'picture', 'photo'],
                'link' => ['link', 'url', 'href'],
                'button_text' => ['button_text', 'cta_text', 'action_text'],
            ];

            // Always map the content item title to title field if widget has one
            if (isset($fieldValues['title'])) {
                $fieldValues['title'] = $contentItem->title;
                \Log::debug('Mapped content item title', ['title' => $contentItem->title]);
            }

            // Apply field mappings based on content item fields
            foreach ($contentItem->fieldValues as $fieldValue) {
                if (!$fieldValue->field) continue;

                $contentFieldSlug = $fieldValue->field->slug;
                $contentFieldValue = $fieldValue->getFormattedValue();

                // Try to map to widget fields using mapping rules
                foreach ($mappingRules as $widgetField => $possibleContentFields) {
                    if (isset($fieldValues[$widgetField]) && in_array($contentFieldSlug, $possibleContentFields)) {
                        $fieldValues[$widgetField] = $contentFieldValue;
                        \Log::debug('Applied basic mapping', [
                            'widget_field' => $widgetField,
                            'content_field' => $contentFieldSlug,
                            'value' => $contentFieldValue
                        ]);
                        break;
                    }
                }

                // Also try direct slug matching
                if (isset($fieldValues[$contentFieldSlug])) {
                    $fieldValues[$contentFieldSlug] = $contentFieldValue;
                    \Log::debug('Applied direct slug mapping', [
                        'field_slug' => $contentFieldSlug,
                        'value' => $contentFieldValue
                    ]);
                }
            }

            return $fieldValues;

        } catch (\Exception $e) {
            \Log::error('Error applying basic content mapping', [
                'content_item_id' => $contentItemId,
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);
            return $fieldValues;
        }
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
           // If no content items found, return empty/null instead of fallback
            if (empty($contentItems)) {
                return response()->json([
                    'success' => true,
                    'content_items' => [], // or null if you prefer
                    'widget' => [
                        'id' => $widget->id,
                        'name' => $widget->name,
                        'slug' => $widget->slug
                    ],
                    'message' => 'No content types available'
                ]);
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
