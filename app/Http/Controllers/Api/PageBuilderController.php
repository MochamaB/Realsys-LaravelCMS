<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Models\TemplateSection;
use App\Services\ThemeManager;
use App\Services\SectionTemplateService;
use App\Services\WidgetRenderingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * GridStack Page Builder API Controller
 * 
 * Focused controller for GridStack page builder functionality.
 * Contains only the essential 14 methods needed for core GridStack operations.
 */
class PageBuilderController extends Controller
{
    protected $themeManager;
    protected $sectionTemplateService;
    protected $widgetRenderingService;

    public function __construct(
        ThemeManager $themeManager,
        SectionTemplateService $sectionTemplateService,
        WidgetRenderingService $widgetRenderingService
    ) {
        $this->themeManager = $themeManager;
        $this->sectionTemplateService = $sectionTemplateService;
        $this->widgetRenderingService = $widgetRenderingService;
    }

    // =====================================================================
    // 1. PAGE SECTIONS (5 methods)
    // =====================================================================

    /**
     * Get all sections for a page with GridStack positioning data
     */
    public function getSections(Page $page): JsonResponse
    {
        try {
            $sections = $page->sections()
                ->with('templateSection') // Load the template section relationship
                ->orderBy('position')
                ->get()
                ->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'page_id' => $section->page_id,
                        'template_section_id' => $section->template_section_id,
                        'position' => $section->position,
                        'grid_x' => $section->grid_x,
                        'grid_y' => $section->grid_y,
                        'grid_w' => $section->grid_w,
                        'grid_h' => $section->grid_h,
                        'grid_id' => $section->grid_id,
                        'grid_config' => $section->grid_config,
                        'allows_widgets' => $section->allows_widgets,
                        'widget_types' => $section->widget_types,
                        'css_classes' => $section->css_classes,
                        'background_color' => $section->background_color,
                        'padding' => $section->padding,
                        'margin' => $section->margin,
                        'locked_position' => $section->locked_position,
                        'resize_handles' => $section->resize_handles,
                        'column_span_override' => $section->column_span_override,
                        'column_offset_override' => $section->column_offset_override,
                        // Include template section data
                        'template_section' => $section->templateSection ? [
                            'id' => $section->templateSection->id,
                            'name' => $section->templateSection->name,
                            'section_type' => $section->templateSection->section_type,
                            'column_layout' => $section->templateSection->column_layout,
                            'description' => $section->templateSection->description
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sections
            ]);

        } catch (Exception $e) {
            Log::error('Error loading page sections: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load page sections',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new section for the page
     */
    public function createSection(Page $page, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'template_key' => 'required|string',
                'name' => 'nullable|string|max:255',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'position' => 'nullable|integer'
            ]);

            // Find template section
            $templateSection = TemplateSection::where('template_id', $page->template_id)
                ->where('key', $validated['template_key'])
                ->first();

            if (!$templateSection) {
                return response()->json([
                    'success' => false,
                    'error' => 'Template section not found'
                ], 404);
            }

            // Set defaults
            $validated['page_id'] = $page->id;
            $validated['template_section_id'] = $templateSection->id;
            $validated['name'] = $validated['name'] ?? $templateSection->name;
            $validated['position'] = $validated['position'] ?? ($page->sections()->max('position') + 1);
            $validated['grid_x'] = $validated['grid_x'] ?? 0;
            $validated['grid_y'] = $validated['grid_y'] ?? 0;
            $validated['grid_w'] = $validated['grid_w'] ?? 12;
            $validated['grid_h'] = $validated['grid_h'] ?? 4;
            $validated['grid_id'] = 'section_' . time() . '_' . uniqid();
            $validated['allows_widgets'] = true;

            $section = PageSection::create($validated);
            $section->load('templateSection');

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Section created successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error creating section: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section properties
     */
    public function updateSection(PageSection $section, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array'
            ]);

            $section->update($validated);
            $section->load('templateSection');

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Section updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error updating section: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a section from the page
     */
    public function deleteSection(PageSection $section): JsonResponse
    {
        try {
            // Delete associated widgets first
            $section->pageSectionWidgets()->delete();
            
            // Delete the section
            $section->delete();

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting section: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section GridStack position (x, y, w, h)
     */
    public function updateSectionGridPosition(PageSection $section, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grid_x' => 'required|integer|min:0',
                'grid_y' => 'required|integer|min:0',
                'grid_w' => 'required|integer|min:1',
                'grid_h' => 'required|integer|min:1'
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'grid_position' => [
                        'x' => $section->grid_x,
                        'y' => $section->grid_y,
                        'w' => $section->grid_w,
                        'h' => $section->grid_h
                    ]
                ],
                'message' => 'Section position updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error updating section position: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update section position',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================================
    // 2. SECTION WIDGETS (4 methods)
    // =====================================================================

    /**
     * Get all widgets in a section with GridStack positioning
     */
    public function getSectionWidgets(PageSection $section): JsonResponse
    {
        try {
            $widgets = $section->pageSectionWidgets()
                ->with('widget')
                ->orderBy('position')
                ->get()
                ->map(function ($widget) {
                    return [
                        'id' => $widget->id,
                        'widget_id' => $widget->widget_id,
                        'widget_name' => $widget->widget->name ?? 'Unknown Widget',
                        'widget_slug' => $widget->widget->slug ?? 'unknown',
                        'grid_position' => [
                            'x' => $widget->grid_x ?? 0,
                            'y' => $widget->grid_y ?? 0,
                            'w' => $widget->grid_w ?? 6,
                            'h' => $widget->grid_h ?? 3,
                            'grid_id' => $widget->grid_id ?? "widget-{$widget->id}"
                        ],
                        'position' => $widget->position,
                        'settings' => $widget->settings ?? [],
                        'content_query' => $widget->content_query ?? [],
                        'css_classes' => $widget->css_classes,
                        'widget' => $widget->widget
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $widgets
            ]);

        } catch (Exception $e) {
            Log::error('Error loading section widgets: ' . $e->getMessage(), [
                'section_id' => $section->id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load section widgets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new widget in a section
     */
    public function createWidget(PageSection $section, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'widget_id' => 'required|integer|exists:widgets,id',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'position' => 'nullable|integer',
                'settings' => 'nullable|array',
                'content_query' => 'nullable|array',
                'css_classes' => 'nullable|string'
            ]);

            // Set defaults
            $validated['page_section_id'] = $section->id;
            $validated['position'] = $validated['position'] ?? ($section->pageSectionWidgets()->max('position') + 1);
            $validated['grid_x'] = $validated['grid_x'] ?? 0;
            $validated['grid_y'] = $validated['grid_y'] ?? 0;
            $validated['grid_w'] = $validated['grid_w'] ?? 6;
            $validated['grid_h'] = $validated['grid_h'] ?? 3;
            $validated['grid_id'] = 'widget_' . time() . '_' . uniqid();

            $pageSectionWidget = PageSectionWidget::create($validated);
            $pageSectionWidget->load(['widget', 'pageSection']);

            return response()->json([
                'success' => true,
                'message' => 'Widget added to section successfully',
                'data' => $pageSectionWidget
            ], 201);

        } catch (Exception $e) {
            Log::error('Error creating widget: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update widget properties
     */
    public function updateWidget(PageSectionWidget $widget, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'settings' => 'nullable|array',
                'content_query' => 'nullable|array',
                'css_classes' => 'nullable|string',
                'position' => 'nullable|integer'
            ]);

            $widget->update($validated);
            $widget->load(['widget', 'pageSection']);

            return response()->json([
                'success' => true,
                'message' => 'Widget updated successfully',
                'data' => $widget
            ]);

        } catch (Exception $e) {
            Log::error('Error updating widget: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a widget from a section
     */
    public function deleteWidget(PageSectionWidget $widget): JsonResponse
    {
        try {
            $widget->delete();

            return response()->json([
                'success' => true,
                'message' => 'Widget deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting widget: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update widget GridStack position (x, y, w, h) - NEW METHOD
     */
    public function updateWidgetGridPosition(PageSectionWidget $widget, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grid_x' => 'required|integer|min:0',
                'grid_y' => 'required|integer|min:0',
                'grid_w' => 'required|integer|min:1',
                'grid_h' => 'required|integer|min:1'
            ]);

            $widget->update($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'grid_position' => [
                        'x' => $widget->grid_x,
                        'y' => $widget->grid_y,
                        'w' => $widget->grid_w,
                        'h' => $widget->grid_h
                    ]
                ],
                'message' => 'Widget position updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error updating widget position: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update widget position',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================================
    // 3. SIDEBAR CONTENT (2 methods)
    // =====================================================================

    /**
     * Get available widgets for drag & drop from sidebar
     */
    public function getAvailableWidgets(): JsonResponse
    {
        try {
            $widgets = Widget::where('is_active', true)
                ->select('id', 'name', 'slug', 'description', 'icon', 'category', 'schema')
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->groupBy('category')
                ->map(function ($categoryWidgets) {
                    return $categoryWidgets->map(function ($widget) {
                        return [
                            'id' => $widget->id,
                            'name' => $widget->name,
                            'slug' => $widget->slug,
                            'description' => $widget->description,
                            'icon' => $widget->icon ?? 'ri-apps-line',
                            'category' => $widget->category ?? 'General',
                            'default_settings' => $widget->schema['default_settings'] ?? []
                        ];
                    });
                });

            return response()->json([
                'success' => true,
                'data' => $widgets
            ]);

        } catch (Exception $e) {
            Log::error('Error loading available widgets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load widgets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available section templates for sidebar - NEW METHOD
     */
    public function getTemplateSections(): JsonResponse
    {
        try {
            // Get active theme template sections
            $activeTheme = $this->themeManager->getActiveTheme();
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active theme found'
                ], 404);
            }

            $templateSections = TemplateSection::where('template_id', $activeTheme->id)
                ->select('id', 'key', 'name', 'section_type', 'column_layout', 'description', 'icon')
                ->orderBy('name')
                ->get()
                ->groupBy('section_type')
                ->map(function ($sections) {
                    return $sections->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'key' => $section->key,
                            'name' => $section->name,
                            'section_type' => $section->section_type,
                            'column_layout' => $section->column_layout,
                            'description' => $section->description,
                            'icon' => $section->icon ?? 'ri-layout-grid-line'
                        ];
                    });
                });

            return response()->json([
                'success' => true,
                'data' => $templateSections
            ]);

        } catch (Exception $e) {
            Log::error('Error loading template sections: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load template sections',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================================
    // 4. RENDERING & ASSETS (2 methods)
    // =====================================================================

    /**
     * Render widget for preview in canvas
     */
    public function renderWidget(Widget $widget, Request $request): JsonResponse
    {
        try {
            // Get page section widget ID if provided
            $pageSectionWidgetId = $request->input('page_section_widget_id');
            $pageSectionWidget = null;

            if ($pageSectionWidgetId) {
                $pageSectionWidget = PageSectionWidget::with(['widget'])
                    ->find($pageSectionWidgetId);
            }

            // Use the widget rendering service to render the widget
            $html = $this->widgetRenderingService->renderWidget($widget, $pageSectionWidget);

            return response()->json([
                'success' => true,
                'html' => $html,
                'widget' => [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'slug' => $widget->slug
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error rendering widget: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'page_section_widget_id' => $request->input('page_section_widget_id')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to render widget',
                'message' => $e->getMessage(),
                'html' => '<div class="widget-error">Error rendering widget: ' . $widget->name . '</div>'
            ], 500);
        }
    }

    /**
     * Get theme CSS/JS assets for GridStack canvas
     */
    public function getThemeAssets(): JsonResponse
    {
        try {
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active theme found'
                ], 404);
            }

            // Get theme CSS files
            $cssFiles = $activeTheme->css ?? [];
            
            // Get theme JS files  
            $jsFiles = $activeTheme->js ?? [];

            // Add base theme path for relative URLs
            $themeBasePath = "/themes/{$activeTheme->slug}";

            return response()->json([
                'success' => true,
                'theme' => [
                    'name' => $activeTheme->name,
                    'slug' => $activeTheme->slug,
                    'base_path' => $themeBasePath,
                    'css' => $cssFiles,
                    'js' => $jsFiles
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting theme assets: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load theme assets',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}