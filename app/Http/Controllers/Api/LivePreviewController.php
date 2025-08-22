<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Simple Live Preview API Controller
 * 
 * Handles JSON API responses for the simplified live preview system.
 * This controller provides endpoints for:
 * - Preview iframe content generation
 * - Widget editing and updates
 * - Page structure management
 * - Real-time content updates
 */
class LivePreviewController extends Controller
{
    protected $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * Get preview iframe content for a page
     * 
     * Returns the full HTML content that will be displayed in the preview iframe.
     * This uses the existing TemplateRenderer to ensure perfect theme compatibility.
     * 
     * @param Page $page
     * @return \Illuminate\Http\Response
     */
    public function getPreviewIframe(Page $page)
    {
        // Load all necessary relationships for template rendering
        $page->load([
            'template.theme',
            'sections.templateSection',
            'sections.pageSectionWidgets.widget'
        ]);

        // Generate the complete HTML using existing template renderer
        $html = $this->generateFullPageHtml($page);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Get page structure for sidebar navigation
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getPageStructure(Page $page): JsonResponse
    {
        try {
            $page->load([
                'template',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);

            $structure = [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'template' => $page->template->name ?? 'Unknown Template'
                ],
                'sections' => $page->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'name' => $section->templateSection->name ?? 'Section ' . $section->id,
                        'widgets' => $section->pageSectionWidgets->map(function ($psw) {
                            return [
                                'id' => $psw->id,
                                'widget_id' => $psw->widget_id,
                                'name' => $psw->widget->name ?? 'Unknown Widget',
                                'icon' => $psw->widget->icon ?? 'ri-puzzle-line',
                                'position' => $psw->position
                            ];
                        })->sortBy('position')->values()
                    ];
                })->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $structure
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to load page structure: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load page structure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get widget editor form for a specific widget instance
     * 
     * @param PageSectionWidget $instance
     * @return JsonResponse
     */
    public function getWidgetEditorForm(PageSectionWidget $instance): JsonResponse
    {
        $instance->load(['widget', 'widget.contentTypes']);

        // Get field values for the form - ensure all values are strings or safe for output
        $settings = $instance->settings ?? [];
        $contentQuery = $instance->content_query ?? [];
        
        // Flatten nested arrays to dot notation for form fields
        $flattenedSettings = [];
        foreach ($settings as $key => $value) {
            $flattenedSettings[$key] = is_array($value) ? json_encode($value) : (string)$value;
        }
        
        $flattenedContentQuery = [];
        foreach ($contentQuery as $key => $value) {
            $flattenedContentQuery[$key] = is_array($value) ? json_encode($value) : (string)$value;
        }
        
        $fieldValues = array_merge(
            $flattenedSettings,
            $flattenedContentQuery,
            [
                'css_classes' => $instance->css_classes ?? '',
                'padding' => $instance->padding ?? '',
                'margin' => $instance->margin ?? '',
                'background_color' => $instance->background_color ?? '#ffffff',
                'min_width' => $instance->min_width ?? '',
                'max_width' => $instance->max_width ?? '',
                'min_height' => $instance->min_height ?? '',
                'max_height' => $instance->max_height ?? '',
                'locked_position' => $instance->locked_position ? '1' : '0'
            ]
        );

        // Get available content types for content widgets
        $contentTypes = $instance->widget->contentTypes ?? collect();

        $formData = [
            'widget' => $instance->widget,
            'instance' => $instance,
            'fieldValues' => $fieldValues,
            'contentTypes' => $contentTypes
        ];

        $formHtml = view('admin.pages.live-designer.simple.widget-editor-form', $formData)->render();

        return response()->json([
            'success' => true,
            'data' => [
                'widget_id' => $instance->id,
                'widget_name' => $instance->widget->name,
                'html' => $formHtml
            ]
        ]);
    }

    /**
     * Update widget and return preview
     * 
     * @param PageSectionWidget $instance
     * @param Request $request
     * @return JsonResponse
     */
    public function updateWidgetPreview(PageSectionWidget $instance, Request $request): JsonResponse
    {
        try {
            // Update widget data based on the form submission
            $updateData = $request->only([
                'settings', 'content_query', 'style',
                'css_classes', 'padding', 'margin', 'background_color',
                'min_width', 'max_width', 'min_height', 'max_height',
                'locked_position'
            ]);

            // Handle settings update
            if (isset($updateData['settings'])) {
                $instance->settings = array_merge($instance->settings ?? [], $updateData['settings']);
            }

            // Handle content query update
            if (isset($updateData['content_query'])) {
                $instance->content_query = array_merge($instance->content_query ?? [], $updateData['content_query']);
            }

            // Handle style fields
            $styleFields = ['css_classes', 'padding', 'margin', 'background_color', 
                          'min_width', 'max_width', 'min_height', 'max_height'];
            
            foreach ($styleFields as $field) {
                if (isset($updateData[$field])) {
                    $instance->$field = $updateData[$field];
                }
            }

            // Handle boolean fields
            $instance->locked_position = $request->boolean('locked_position');

            $instance->save();

            return response()->json([
                'success' => true,
                'message' => 'Widget updated successfully',
                'data' => [
                    'widget_id' => $instance->id,
                    'refresh_preview' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update widget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get section editor form for a specific page section
     * 
     * @param PageSection $section
     * @return JsonResponse
     */
    public function getSectionEditorForm(PageSection $section): JsonResponse
    {
        try {
            $section->load(['templateSection', 'page']);

            $formData = [
                'section' => $section,
                'templateSection' => $section->templateSection,
                'page' => $section->page
            ];

            $formHtml = view('admin.pages.live-designer.simple.section-editor-form', $formData)->render();

            return response()->json([
                'success' => true,
                'data' => [
                    'section_id' => $section->id,
                    'section_name' => $section->templateSection->name ?? 'Section ' . $section->id,
                    'html' => $formHtml
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load section editor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section and return preview
     * 
     * @param PageSection $section
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSectionPreview(PageSection $section, Request $request): JsonResponse
    {
        try {
            // Update section data
            $updateData = $request->only([
                'css_classes', 'background_color', 'background_image',
                'padding', 'margin', 'min_height', 'max_height'
            ]);

            foreach ($updateData as $field => $value) {
                if ($value !== null) {
                    $section->$field = $value;
                }
            }

            $section->save();

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'data' => [
                    'section_id' => $section->id,
                    'refresh_preview' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new widget to a section
     * 
     * @param PageSection $section
     * @param Request $request
     * @return JsonResponse
     */
    public function addWidget(PageSection $section, Request $request): JsonResponse
    {
        $request->validate([
            'widget_id' => 'required|exists:widgets,id'
        ]);

        try {
            $widget = Widget::findOrFail($request->widget_id);

            // Get next position in section
            $nextPosition = $section->pageSectionWidgets()->max('position') + 1;

            // Create new widget instance
            $widgetInstance = PageSectionWidget::create([
                'page_section_id' => $section->id,
                'widget_id' => $widget->id,
                'position' => $nextPosition,
                'settings' => $widget->default_settings ?? [],
                'content_query' => []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Widget added successfully',
                'data' => [
                    'widget_instance_id' => $widgetInstance->id,
                    'widget_name' => $widget->name,
                    'refresh_preview' => true,
                    'refresh_structure' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add widget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available widgets for the widget library
     * 
     * @return JsonResponse
     */
    public function getAvailableWidgets(): JsonResponse
    {
        $widgets = Widget::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'icon', 'category']);

        $groupedWidgets = $widgets->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => [
                'widgets' => $groupedWidgets
            ]
        ]);
    }

    /**
     * Generate full page HTML for preview iframe
     * 
     * This method uses the existing TemplateRenderer service to ensure
     * the preview matches exactly what users see on the frontend.
     * 
     * @param Page $page
     * @return string
     */
    private function generateFullPageHtml(Page $page): string
    {
        // Get the rendered page content using existing template system
        $pageContent = $this->templateRenderer->renderPage($page);

        // Inject preview helper assets
        $previewAssets = $this->getPreviewAssets();

        // Build complete HTML with preview helpers
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Preview - ' . e($page->title) . '</title>
    
    <!-- Theme Assets -->
    ' . $this->getThemeAssets($page) . '
    
    <!-- Preview Helper Assets -->
    ' . $previewAssets . '
</head>
<body>
    ' . $pageContent . '
</body>
</html>';

        return $html;
    }

    /**
     * Get theme assets for the page
     * 
     * @param Page $page
     * @return string
     */
    private function getThemeAssets(Page $page): string
    {
        $theme = $page->template->theme;
        $assets = '';

        if ($theme) {
            // Add theme CSS
            if ($theme->css_files) {
                foreach ($theme->css_files as $cssFile) {
                    $assets .= '<link rel="stylesheet" href="' . asset($cssFile) . '">' . "\n    ";
                }
            }

            // Add theme JS
            if ($theme->js_files) {
                foreach ($theme->js_files as $jsFile) {
                    $assets .= '<script src="' . asset($jsFile) . '"></script>' . "\n    ";
                }
            }
        }

        return $assets;
    }

    /**
     * Get preview helper assets
     * 
     * @return string
     */
    private function getPreviewAssets(): string
    {
        return '
    <!-- Preview Helper CSS -->
    <link rel="stylesheet" href="' . asset('assets/admin/css/live-designer/preview-helpers.css') . '">
    
    <!-- Preview Helper JS -->
    <script src="' . asset('assets/admin/js/live-designer/preview-helpers.js') . '"></script>';
    }
}