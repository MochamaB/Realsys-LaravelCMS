<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Template;
use App\Models\TemplateSection;
use App\Services\SectionTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSectionController extends Controller
{
    protected $sectionTemplateService;

    public function __construct(SectionTemplateService $sectionTemplateService)
    {
        $this->sectionTemplateService = $sectionTemplateService;
    }

    /**
     * List all sections for a page with GridStack positioning data.
     */

     public function getTemplateSections(Page $page)
     {
         $templateSections = $page->templateSections()->orderBy('position')->get();
     
         return response()->json([
             'success' => true,
             'data' => $templateSections
         ]);
     }
    /**
     * Display a listing of the sections for a page.
     */
    public function index(Page $page)
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
                        'template_section' => [
                            'id' => $section->templateSection->id,
                            'name' => $section->templateSection->name,
                            'section_type' => $section->templateSection->section_type,
                            'column_layout' => $section->templateSection->column_layout,
                            'description' => $section->templateSection->description
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sections
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading page sections: ' . $e->getMessage(), [
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
     * Get available section templates.
     */
    public function getTemplates()
    {
        try {
            $templates = $this->sectionTemplateService->getAllTemplates();
            
            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading section templates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load section templates',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific section template.
     */
    public function getTemplate($templateKey)
    {
        try {
            $template = $this->sectionTemplateService->getTemplate($templateKey);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => 'Template not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading section template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load section template',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new section for a page with template support.
     */
    public function store(Request $request, Page $page)
    {
        try {
            $validated = $request->validate([
                'template_key' => 'required|string',
                'name' => 'nullable|string|max:255',
                'identifier' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9-]+$/',
                    'unique:page_sections,identifier'
                ],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'grid_id' => 'nullable|string|unique:page_sections,grid_id',
                'grid_config' => 'nullable|array',
                'allows_widgets' => 'boolean',
                'widget_types' => 'nullable|array',
                'locked_position' => 'boolean',
                'resize_handles' => 'nullable|array',
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'column_span_override' => 'nullable|integer',
                'column_offset_override' => 'nullable|integer',
                'position' => 'nullable|integer'
            ]);

            // First, try to find or create a template section for this template key
            $templateSection = $this->findOrCreateTemplateSection($page->template, $validated['template_key']);

            // Set default name if not provided
            if (empty($validated['name'])) {
                $validated['name'] = $templateSection->name;
            }

            // Generate identifier if not provided
            if (empty($validated['identifier'])) {
                $validated['identifier'] = Str::slug($validated['name']);
            }

            // Set position if not provided
            if (!isset($validated['position'])) {
                $validated['position'] = $page->sections()->max('position') + 1;
            }

            // Add page ID and template section ID
            $validated['page_id'] = $page->id;
            $validated['template_section_id'] = $templateSection->id;

            // Ensure grid_id is set
            if (empty($validated['grid_id'])) {
                $validated['grid_id'] = 'section_' . time() . '_' . uniqid() . '_' . $templateSection->id;
            }

            // Set default values for required fields if not provided
            $validated['allows_widgets'] = $validated['allows_widgets'] ?? true;
            $validated['locked_position'] = $validated['locked_position'] ?? false;
            $validated['grid_x'] = $validated['grid_x'] ?? 0;
            $validated['grid_y'] = $validated['grid_y'] ?? 0;
            $validated['grid_w'] = $validated['grid_w'] ?? 12;
            $validated['grid_h'] = $validated['grid_h'] ?? 4;
            $validated['widget_types'] = $validated['widget_types'] ?? ['text', 'image', 'counter', 'gallery', 'form', 'video'];
            $validated['css_classes'] = $validated['css_classes'] ?? 'container';
            $validated['background_color'] = $validated['background_color'] ?? '#ffffff';
            $validated['padding'] = $validated['padding'] ?? ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0];
            $validated['margin'] = $validated['margin'] ?? ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0];
            $validated['resize_handles'] = $validated['resize_handles'] ?? ['se', 'sw'];

            $section = PageSection::create($validated);

            \Log::info('Section created successfully', [
                'section_id' => $section->id,
                'template_key' => $validated['template_key'],
                'template_section_id' => $templateSection->id,
                'page_id' => $page->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Section created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating section: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find or create a template section for the given template key
     * 
     * @param Template $template
     * @param string $templateKey
     * @return TemplateSection
     */
    protected function findOrCreateTemplateSection(Template $template, string $templateKey): TemplateSection
    {
        // Try to find existing template section
        $templateSection = $template->sections()
            ->where('section_type', $templateKey)
            ->first();

        if (!$templateSection) {
            // Create a new template section based on the template key
            $templateSection = $template->sections()->create([
                'name' => $this->getTemplateSectionName($templateKey),
                'slug' => Str::slug($this->getTemplateSectionName($templateKey)),
                'position' => $template->sections()->max('position') + 1,
                'section_type' => $templateKey,
                'column_layout' => $this->getDefaultColumnLayout($templateKey),
                'description' => $this->getTemplateSectionDescription($templateKey),
                'is_repeatable' => true,
                'max_widgets' => null,
                // Basic positioning
                'x' => 0,
                'y' => 0,
                'w' => 12,
                'h' => 4
            ]);

            \Log::info('Created new template section:', [
                'template_id' => $template->id,
                'template_section_id' => $templateSection->id,
                'section_type' => $templateKey,
                'name' => $templateSection->name
            ]);
        }

        return $templateSection;
    }

    /**
     * Get template section name based on template key
     * 
     * @param string $templateKey
     * @return string
     */
    protected function getTemplateSectionName(string $templateKey): string
    {
        $names = [
            'full-width' => 'Full Width Section',
            'multi-column' => 'Multi Column Section',
            'sidebar-left' => 'Sidebar Left Section',
            'sidebar-right' => 'Sidebar Right Section'
        ];

        return $names[$templateKey] ?? 'Custom Section';
    }

    /**
     * Get template section description based on template key
     * 
     * @param string $templateKey
     * @return string
     */
    protected function getTemplateSectionDescription(string $templateKey): string
    {
        $descriptions = [
            'full-width' => 'Full width single column layout',
            'multi-column' => 'Flexible multi-column layout',
            'sidebar-left' => 'Left sidebar with main content',
            'sidebar-right' => 'Right sidebar with main content'
        ];

        return $descriptions[$templateKey] ?? 'Custom section layout';
    }

    /**
     * Get default column layout based on template key
     * 
     * @param string $templateKey
     * @return string
     */
    protected function getDefaultColumnLayout(string $templateKey): string
    {
        $layouts = [
            'full-width' => '12',
            'multi-column' => '4-4-4',
            'sidebar-left' => '3-9',
            'sidebar-right' => '9-3'
        ];

        return $layouts[$templateKey] ?? '12';
    }

    /**
     * Update a section for a page.
     */
    public function update(Request $request, Page $page, PageSection $section)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'identifier' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9-]+$/',
                    'unique:page_sections,identifier,' . $section->id
                ],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'grid_id' => 'nullable|string|unique:page_sections,grid_id,' . $section->id,
                'grid_config' => 'nullable|array',
                'allows_widgets' => 'boolean',
                'widget_types' => 'nullable|array',
                'locked_position' => 'boolean',
                'resize_handles' => 'nullable|array',
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'column_span_override' => 'nullable|integer',
                'column_offset_override' => 'nullable|integer',
                'position' => 'nullable|integer'
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Section updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Page $page, PageSection $section)
    {
        try {
            // Get the template section before deleting the page section
            $templateSection = $section->templateSection;
            
            // Delete the page section
            $section->delete();
            
            // Also delete the corresponding template section if it's not used by other pages
            if ($templateSection) {
                $otherPageSections = PageSection::where('template_section_id', $templateSection->id)
                    ->where('id', '!=', $section->id)
                    ->count();
                
                if ($otherPageSections === 0) {
                    $templateSection->delete();
                    \Log::info('Template section also deleted:', [
                        'template_section_id' => $templateSection->id,
                        'section_type' => $templateSection->section_type
                    ]);
                }
            }

            \Log::info('Section deleted successfully', [
                'section_id' => $section->id,
                'template_section_id' => $templateSection->id ?? null,
                'page_id' => $page->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'page_id' => $page->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete section',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section GridStack position.
     */
    public function updateGridPosition(Request $request, PageSection $section)
    {
        try {
            $validated = $request->validate([
                'grid_x' => 'required|integer|min:0',
                'grid_y' => 'required|integer|min:0',
                'grid_w' => 'required|integer|min:1',
                'grid_h' => 'required|integer|min:1',
                'grid_id' => 'nullable|string|unique:page_sections,grid_id,' . $section->id
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'grid_position' => [
                        'x' => $section->grid_x,
                        'y' => $section->grid_y,
                        'w' => $section->grid_w,
                        'h' => $section->grid_h,
                        'grid_id' => $section->grid_id
                    ]
                ],
                'message' => 'Section position updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating section grid position: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update section position',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section styles.
     */
    public function updateStyles(Request $request, PageSection $section)
    {
        try {
            $validated = $request->validate([
                'css_classes' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:50',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'column_span_override' => 'nullable|integer',
                'column_offset_override' => 'nullable|integer'
            ]);

            $section->update($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'css_classes' => $section->css_classes,
                    'background_color' => $section->background_color,
                    'padding' => $section->padding,
                    'margin' => $section->margin,
                    'column_span_override' => $section->column_span_override,
                    'column_offset_override' => $section->column_offset_override
                ],
                'message' => 'Section styles updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating section styles: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update section styles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get section widgets with GridStack positioning.
     */
    public function getSectionWidgets(PageSection $section)
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
                        'grid_position' => [
                            'x' => $widget->grid_x ?? 0,
                            'y' => $widget->grid_y ?? 0,
                            'w' => $widget->grid_w ?? 6,
                            'h' => $widget->grid_h ?? 3,
                            'grid_id' => $widget->grid_id ?? "widget-{$widget->id}"
                        ],
                        'column_position' => $widget->column_position,
                        'min_width' => $widget->min_width,
                        'max_width' => $widget->max_width,
                        'locked_position' => $widget->locked_position ?? false,
                        'resize_handles' => $widget->resize_handles ?? ['se', 'sw'],
                        'css_classes' => $widget->css_classes,
                        'padding' => $widget->padding,
                        'margin' => $widget->margin,
                        'min_height' => $widget->min_height,
                        'max_height' => $widget->max_height,
                        'settings' => $widget->settings,
                        'content_query' => $widget->content_query
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $widgets
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading section widgets: ' . $e->getMessage(), [
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
     * Reorder sections for a page.
     */
    public function reorder(Request $request, Page $page)
    {
        try {
            $validated = $request->validate([
                'sections' => 'required|array',
                'sections.*.id' => 'required|exists:page_sections,id',
                'sections.*.position' => 'required|integer|min:0'
            ]);

            foreach ($validated['sections'] as $item) {
                PageSection::where('id', $item['id'])->update([
                    'position' => $item['position']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sections reordered successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reordering sections: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to reorder sections',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render a section with all its widgets for the page designer.
     */
    public function renderSection(Request $request, PageSection $section)
    {
        try {
            $templateRenderer = app(\App\Services\TemplateRenderer::class);
            $page = $section->page;
            $template = $page->template;
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page has no template'
                ], 400);
            }
            
            // Render the section using TemplateRenderer
            $html = $templateRenderer->renderSectionById($section->id, [
                'page' => $page,
                'template' => $template
            ]);
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'section_id' => $section->id,
                'section_name' => $section->name ?? 'Unknown Section'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering section: ' . $e->getMessage(), [
                'section_id' => $section->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to render section: ' . $e->getMessage()
            ], 500);
        }
    }
}
