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
        
        // Safely flatten nested arrays for form fields
        $flattenedSettings = [];
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                // Handle repeater fields and complex structures
                $flattenedSettings[$key] = json_encode($value);
            } elseif (is_null($value)) {
                $flattenedSettings[$key] = '';
            } else {
                $flattenedSettings[$key] = (string)$value;
            }
        }
        
        $flattenedContentQuery = [];
        foreach ($contentQuery as $key => $value) {
            if (is_array($value)) {
                // Handle content query arrays
                $flattenedContentQuery[$key] = json_encode($value);
            } elseif (is_null($value)) {
                $flattenedContentQuery[$key] = '';
            } else {
                $flattenedContentQuery[$key] = (string)$value;
            }
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
        
        // Inject preview data using the existing page structure data
        $pageContent = $this->injectPreviewDataFromStructure($page, $pageContent);

        // Inject preview helper assets
        $previewAssets = $this->getPreviewAssets();

        // Build complete HTML with preview helpers and proper page container
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
    
    <!-- Preview Structure Data -->
    ' . $this->getPreviewStructureScript($page) . '
</head>
<body>
    <!-- Page Container with proper data attributes for preview system -->
    <div data-preview-page="' . $page->id . '" 
         data-page-title="' . e($page->title) . '" 
         data-page-template="' . e($page->template->name ?? 'Unknown Template') . '"
         data-preview-type="page"
         class="page-preview-container">
        ' . $pageContent . '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Inject preview data using simple approach that leverages existing page structure
     * 
     * @param Page $page
     * @param string $html
     * @return string
     */
    private function injectPreviewDataFromStructure(Page $page, string $html): string
    {
        // The universal components already generate the correct data attributes!
        // We just need to add minimal preview-specific attributes for the JavaScript
        
        $patterns = [
            // Add preview attributes to existing section elements
            '/<section([^>]*data-section-id="[^"]*"[^>]*)>/i' => '<section$1 data-preview-type="section">',
            
            // Add preview attributes to existing widget elements  
            '/<div([^>]*data-page-section-widget-id="[^"]*"[^>]*)>/i' => '<div$1 data-preview-type="widget" style="position: relative; cursor: pointer; outline: 1px dashed transparent;" onmouseover="this.style.outline=\'1px dashed #007bff\'" onmouseout="this.style.outline=\'1px dashed transparent\'">',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }
        
        return $html;
    }
    
    /**
     * Generate JavaScript that contains page structure data for preview
     * 
     * @param Page $page
     * @return string
     */
    private function getPreviewStructureScript(Page $page): string
    {
        // Use the existing page structure logic
        $structureResponse = $this->getPageStructure($page);
        $structureData = $structureResponse->getData(true);
        
        if ($structureData['success']) {
            $pageStructure = $structureData['data'];
            
            return '<script>
                window.previewPageStructure = ' . json_encode($pageStructure) . ';
                console.log("🏗️ Preview page structure loaded:", window.previewPageStructure);
            </script>';
        }
        
        return '<script>window.previewPageStructure = null;</script>';
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
     * Reorder sections within a page
     */
    public function reorderSections(Request $request)
    {
        try {
            $request->validate([
                'section_id' => 'required|integer|exists:page_sections,id',
                'new_position' => 'required|integer|min:0',
                'old_position' => 'required|integer|min:0'
            ]);

            $sectionId = $request->input('section_id');
            $newPosition = $request->input('new_position');
            $oldPosition = $request->input('old_position');

            \Log::info('Reordering section', [
                'section_id' => $sectionId,
                'old_position' => $oldPosition,
                'new_position' => $newPosition
            ]);

            // Find the section
            $section = \App\Models\PageSection::findOrFail($sectionId);
            
            // Get all sections for this page ordered by position
            $allSections = \App\Models\PageSection::where('page_id', $section->page_id)
                ->orderBy('position')
                ->get();

            // Reorder the sections
            $this->reorderSectionPositions($allSections, $sectionId, $newPosition);

            return response()->json([
                'success' => true,
                'message' => 'Section position updated successfully',
                'section_id' => $sectionId,
                'new_position' => $newPosition
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reordering sections: ' . $e->getMessage(), [
                'section_id' => $request->input('section_id'),
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to reorder section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone a section
     */
    public function cloneSection(\App\Models\PageSection $section)
    {
        try {
            \Log::info('Cloning section', ['section_id' => $section->id]);

            // Create a new section with copied data
            $newSection = $section->replicate();
            $newSection->name = $section->name . ' (Copy)';
            
            // Set position to be after the original section
            $newSection->position = $section->position + 1;
            $newSection->save();

            // Update positions of sections that come after
            \App\Models\PageSection::where('page_id', $section->page_id)
                ->where('position', '>', $section->position)
                ->where('id', '!=', $newSection->id)
                ->increment('position');

            // Clone all widgets in the section
            foreach ($section->widgets as $widget) {
                $newWidget = $widget->replicate();
                $newWidget->page_section_id = $newSection->id;
                $newWidget->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Section cloned successfully',
                'newSectionId' => $newSection->id,
                'originalSectionId' => $section->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error cloning section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to clone section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder section positions
     */
    protected function reorderSectionPositions($sections, $movedSectionId, $newPosition)
    {
        $sectionsArray = $sections->toArray();
        $movedSection = null;
        $movedIndex = null;

        // Find the moved section
        foreach ($sectionsArray as $index => $section) {
            if ($section['id'] == $movedSectionId) {
                $movedSection = $section;
                $movedIndex = $index;
                break;
            }
        }

        if ($movedSection === null) {
            throw new \Exception('Section not found');
        }

        // Remove the moved section from its current position
        array_splice($sectionsArray, $movedIndex, 1);

        // Insert the moved section at the new position
        array_splice($sectionsArray, $newPosition, 0, [$movedSection]);

        // Update positions in database
        foreach ($sectionsArray as $index => $section) {
            \App\Models\PageSection::where('id', $section['id'])
                ->update(['position' => $index]);
        }
    }

    /**
     * Get preview helper assets
     * 
     * @return string
     */
    private function getPreviewAssets(): string
    {
        return '
    <!-- Selection Manager CSS -->
    <link rel="stylesheet" href="' . asset('assets/admin/css/live-designer/selection-manager.css') . '">
    <link href="' . asset('assets/admin/css/icons.min.css') . '" rel="stylesheet" type="text/css" />
    
    <!-- SortableJS Library for Drag and Drop -->
     <script src="' . asset('assets/admin/libs/sortablejs/Sortable.min.js') . '"></script>
    
    <!-- Selection Manager JS Modules (load in order) -->
    <script src="' . asset('assets/admin/js/live-designer/iframe-communicator.js') . '"></script>
    <script src="' . asset('assets/admin/js/live-designer/component-detector.js') . '"></script>
    <script src="' . asset('assets/admin/js/live-designer/component-toolbar.js') . '"></script>
    <script src="' . asset('assets/admin/js/live-designer/sortable-manager.js') . '"></script>
    <script src="' . asset('assets/admin/js/live-designer/content-extractor.js') . '"></script>
    <script src="' . asset('assets/admin/js/live-designer/selection-manager.js') . '"></script>
    
    <!-- Initialize Selection Manager -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.iframeCommunicator = new IframeCommunicator();
            window.selectionManager = new SelectionManager(window.iframeCommunicator);
            console.log("🎯 Selection Manager initialized");
        });
    </script>
    
    <!-- Internal Preview Spacing -->
    <style>
        body {
            padding: 15px !important;
            margin: 0 !important;
            /* Remove fixed min-height - let content determine height */
        }
        
        /* Ensure proper spacing for section outlines */
        section[data-section-id] {
            margin: 8px 0;
        }
        
        /* First and last section spacing */
        section[data-section-id]:first-child {
            margin-top: 0;
        }
        
        section[data-section-id]:last-child {
            margin-bottom: 0;
        }
        
        
        
       
      
        
        
        
        /* First and last section spacing */
        section[data-section-id]:first-child {
            margin-top: 0;
        }
        
        section[data-section-id]:last-child {
            margin-bottom: 0;
        }
    </style>';
    }
}