<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Models\ContentType;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Page Builder API Controller
 * 
 * Based on LivePreviewController pattern for consistent rendering.
 * Handles JSON API responses for the page builder system.
 */
class PageBuilderController extends Controller
{
    protected $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * Get rendered page content for Page Builder (JSON response)
     * 
     * @param Page $page
     * @return JsonResponse
     */
    public function getRenderedPage(Page $page): JsonResponse
    {
        try {
            // Load all necessary relationships for template rendering (same as LivePreview)
            $page->load([
                'template.theme',
                'sections.templateSection',
                'sections.pageSectionWidgets.widget'
            ]);

            // Get page structure (same as getPageStructure method)
            $structure = $this->getPageStructure($page);
            $structureData = $structure->getData(true);
            
            if (!$structureData['success']) {
                throw new \Exception('Failed to load page structure');
            }
            
            $pageStructure = $structureData['data'];
            
            // Get theme assets for page (as array for JSON response)
            $theme = $page->template->theme;
            $themeAssets = [];
            if ($theme) {
                $themeAssets = [
                    'css' => $theme->css_files ?? [],
                    'js' => $theme->js_files ?? [],
                    'base_path' => "/themes/{$theme->slug}"
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'page' => $pageStructure['page'],
                    'sections' => $pageStructure['sections'],
                    'theme_assets' => $themeAssets,
                    'rendered_html' => $this->generateFullPageHtml($page)  // Also include full HTML for iframe
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to render page: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to render page: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rendered page iframe content for Page Builder
     * Based on LivePreviewController::getPreviewIframe
     * 
     * @param Page $page
     * @return \Illuminate\Http\Response
     */
    public function getRenderedPageIframe(Page $page)
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
     * Get page structure for Page Builder sidebar
     * Based on LivePreviewController::getPageStructure
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
     * Get available widgets for the widget library
     * Updated to fix SQL error and filter by active theme only
     * 
     * @return JsonResponse
     */
    public function getAvailableWidgets(): JsonResponse
    {
        try {
            // Get active theme
            $activeTheme = \App\Models\Theme::where('is_active', true)->first();
            
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active theme found'
                ], 404);
            }

            // Get widgets for active theme only (removed non-existent 'status' and 'category' columns)
            $widgets = Widget::where('theme_id', $activeTheme->id)
                ->with(['contentTypes', 'fieldDefinitions'])
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'icon', 'theme_id', 'slug', 'view_path']);

            // Group widgets by functionality instead of category
            $groupedWidgets = $this->groupWidgetsByFunctionality($widgets, $activeTheme);

            return response()->json([
                'success' => true,
                'data' => [
                    'theme' => [
                        'name' => $activeTheme->name,
                        'slug' => $activeTheme->slug
                    ],
                    'widgets' => $groupedWidgets
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading available widgets: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load widgets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Group widgets by functionality based on name and content types
     * 
     * @param \Illuminate\Database\Eloquent\Collection $widgets
     * @param \App\Models\Theme $theme
     * @return array
     */
    private function groupWidgetsByFunctionality($widgets, $theme)
    {
        $grouped = [
            'content' => [],
            'layout' => [],
            'media' => [],
            'utility' => []
        ];

        foreach ($widgets as $widget) {
            // Determine widget category based on name and content types
            $category = $this->determineWidgetCategory($widget);
            
            // Add preview image path
            $previewImage = $this->getWidgetPreviewImage($widget, $theme);
            
            $widgetData = [
                'id' => $widget->id,
                'name' => $widget->name,
                'description' => $widget->description,
                'icon' => $widget->icon ?? 'ri-puzzle-line',
                'preview_image' => $previewImage,
                'supports_content' => $widget->contentTypes->count() > 0,
                'content_types' => $widget->contentTypes->pluck('name')->toArray(),
                'field_count' => $widget->fieldDefinitions->count(),
                'slug' => $widget->slug
            ];

            $grouped[$category][] = $widgetData;
        }

        return $grouped;
    }

    /**
     * Determine widget category based on name and content types
     * 
     * @param \App\Models\Widget $widget
     * @return string
     */
    private function determineWidgetCategory($widget)
    {
        $name = strtolower($widget->name);
        $hasContent = $widget->contentTypes->count() > 0;

        // Content widgets (have content types)
        if ($hasContent) {
            return 'content';
        }

        // Media widgets (images, videos, galleries)
        if (str_contains($name, 'image') || 
            str_contains($name, 'video') || 
            str_contains($name, 'gallery') || 
            str_contains($name, 'media')) {
            return 'media';
        }

        // Layout widgets (containers, grids, spacers)
        if (str_contains($name, 'container') || 
            str_contains($name, 'grid') || 
            str_contains($name, 'column') || 
            str_contains($name, 'row') || 
            str_contains($name, 'spacer')) {
            return 'layout';
        }

        // Default to utility
        return 'utility';
    }

    /**
     * Get widget preview image path
     * 
     * @param \App\Models\Widget $widget
     * @param \App\Models\Theme $theme
     * @return string
     */
    private function getWidgetPreviewImage($widget, $theme)
    {
        $previewPath = "themes/{$theme->slug}/widgets/{$widget->slug}/preview.png";
        
        if (file_exists(public_path($previewPath))) {
            return asset($previewPath);
        }
        
        return asset('assets/admin/images/widget-placeholder.png');
    }

    /**
     * Get content types available for a specific widget
     * 
     * @param Widget $widget
     * @return JsonResponse
     */
    public function getWidgetContentTypes(Widget $widget): JsonResponse
    {
        try {
            // Load widget with content types
            $widget->load(['contentTypes' => function($query) {
                $query->with(['fields' => function($fieldQuery) {
                    $fieldQuery->orderBy('position');
                }]);
            }]);

            $contentTypes = $widget->contentTypes->map(function($contentType) {
                return [
                    'id' => $contentType->id,
                    'name' => $contentType->name,
                    'description' => $contentType->description,
                    'icon' => $contentType->icon ?? 'ri-file-list-line',
                    'field_count' => $contentType->fields->count(),
                    'items_count' => $contentType->contentItems()->count(),
                    'slug' => $contentType->slug
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'widget' => [
                        'id' => $widget->id,
                        'name' => $widget->name,
                        'supports_content' => $contentTypes->count() > 0
                    ],
                    'content_types' => $contentTypes
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load widget content types: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load content types: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content items for a specific content type
     * 
     * @param \App\Models\ContentType $contentType
     * @param Request $request
     * @return JsonResponse
     */
    public function getContentTypeItems(\App\Models\ContentType $contentType, Request $request): JsonResponse
    {
        try {
            $query = $contentType->contentItems()
                ->with(['fieldValues.field'])
                ->orderBy('created_at', 'desc');

            // Apply search filter
            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            // Apply status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            $items = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => [
                    'content_type' => [
                        'id' => $contentType->id,
                        'name' => $contentType->name,
                        'slug' => $contentType->slug
                    ],
                    'items' => collect($items->items())->map(function($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'excerpt' => \Str::limit($item->content ?? $item->description ?? '', 100),
                            'status' => $item->status ?? 'published',
                            'created_at' => $item->created_at?->format('M j, Y'),
                            'thumbnail' => $this->getItemThumbnail($item),
                            'field_values' => $item->fieldValues ? $item->fieldValues->mapWithKeys(function($fv) {
                                return [$fv->field->slug => $fv->value];
                            }) : []
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $items->currentPage(),
                        'total_pages' => $items->lastPage(),
                        'total_items' => $items->total(),
                        'per_page' => $items->perPage(),
                        'from' => $items->firstItem(),
                        'to' => $items->lastItem()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load content type items: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load content items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query content items using filters and sorting
     * 
     * @param \App\Models\ContentType $contentType
     * @param Request $request
     * @return JsonResponse
     */
    public function queryContentItems(\App\Models\ContentType $contentType, Request $request): JsonResponse
    {
        try {
            $query = $contentType->contentItems()->with(['fieldValues.field']);

            // Apply filters
            $filters = $request->get('filters', []);
            
            if (isset($filters['status']) && $filters['status']) {
                $query->where('status', $filters['status']);
            }
            
            if (isset($filters['date_from']) && $filters['date_from']) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            
            if (isset($filters['date_to']) && $filters['date_to']) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            
            if (isset($filters['search']) && $filters['search']) {
                $query->where(function($q) use ($filters) {
                    $q->where('title', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('content', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Apply limit
            $limit = min($request->get('limit', 10), 50); // Max 50 items
            $items = $query->limit($limit)->get();

            // Preview items (first 5 for display)
            $previewItems = $items->take(5)->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'excerpt' => \Str::limit($item->content ?? $item->description ?? '', 60),
                    'status' => $item->status ?? 'published',
                    'created_at' => $item->created_at?->format('M j, Y')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'query_preview' => $previewItems,
                    'total_matches' => $items->count(),
                    'query_settings' => [
                        'filters' => $filters,
                        'sort_by' => $sortBy,
                        'sort_direction' => $sortDirection,
                        'limit' => $limit
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to query content items: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to query content items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get thumbnail for content item
     * 
     * @param mixed $item
     * @return string|null
     */
    private function getItemThumbnail($item): ?string
    {
        // Check if fieldValues relationship exists and is loaded
        if (!$item->fieldValues || $item->fieldValues->isEmpty()) {
            return null;
        }

        // Try to find an image field value
        $imageField = $item->fieldValues->first(function($fv) {
            return $fv->field && 
                   in_array($fv->field->field_type ?? '', ['image', 'file']) && 
                   $fv->value &&
                   str_contains($fv->value, 'image');
        });
        
        if ($imageField && $imageField->value) {
            return asset($imageField->value);
        }
        
        return null;
    }

    /**
     * Get widget field definitions for configuration
     * 
     * @param Widget $widget
     * @return JsonResponse
     */
    public function getWidgetFieldDefinitions(Widget $widget): JsonResponse
    {
        try {
            // Load widget with field definitions
            $widget->load(['fieldDefinitions' => function($query) {
                $query->orderBy('position')->orderBy('id');
            }]);

            $fieldDefinitions = $widget->fieldDefinitions->map(function($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'slug' => $field->slug,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'description' => $field->description,
                    'default_value' => $field->default_value,
                    'validation_rules' => is_string($field->validation_rules) 
                        ? json_decode($field->validation_rules, true) 
                        : ($field->validation_rules ?? []),
                    'settings' => is_string($field->settings) 
                        ? json_decode($field->settings, true) 
                        : ($field->settings ?? []),
                    'position' => $field->position
                ];
            });

            // Get default widget settings from the widget definition
            $defaultSettings = [
                'layout' => [
                    'width' => 12, // Full width by default
                    'height' => 'auto',
                    'alignment' => 'left'
                ],
                'styling' => [
                    'padding' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'margin' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'background_color' => '',
                    'border_radius' => 0,
                    'custom_css_class' => ''
                ],
                'advanced' => [
                    'animation' => 'none',
                    'responsive_visibility' => ['xs' => true, 'sm' => true, 'md' => true, 'lg' => true, 'xl' => true]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'widget' => [
                        'id' => $widget->id,
                        'name' => $widget->name,
                        'slug' => $widget->slug,
                        'description' => $widget->description
                    ],
                    'field_definitions' => $fieldDefinitions,
                    'default_settings' => $defaultSettings
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load widget field definitions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load widget configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================================================
    // SECTION CONFIGURATION API METHODS
    // =====================================================================

    /**
     * Get section configuration
     * 
     * @param PageSection $section
     * @return JsonResponse
     */
    public function getSectionConfiguration(PageSection $section): JsonResponse
    {
        try {
            $section->load('templateSection');
            
            // Get configuration from the section's config column or provide defaults
            $config = $section->config ? json_decode($section->config, true) : [];
            
            // Ensure we have default values
            $defaultConfig = [
                'name' => $section->templateSection->name ?? 'Section',
                'section_type' => $section->templateSection->section_type ?? 'content',
                'description' => '',
                'column_layout' => 'full-width',
                'container_type' => 'container',
                'padding_top' => '3',
                'padding_bottom' => '3',
                'margin_bottom' => '4',
                'background_type' => 'none',
                'background_color' => '#ffffff',
                'text_color' => '#000000',
                'border_style' => 'none',
                'custom_css_classes' => '',
                'section_html_id' => '',
                'visible_desktop' => true,
                'visible_tablet' => true,
                'visible_mobile' => true,
                'custom_attributes' => ''
            ];
            
            $config = array_merge($defaultConfig, $config);
            
            return response()->json([
                'success' => true,
                'data' => $config
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting section configuration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get section configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section configuration
     * 
     * @param PageSection $section
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSectionConfiguration(PageSection $section, Request $request): JsonResponse
    {
        try {
            // Validate the incoming data
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'section_type' => 'sometimes|string|in:header,content,footer,sidebar',
                'description' => 'sometimes|string|max:500',
                'column_layout' => 'sometimes|string',
                'container_type' => 'sometimes|string|in:container,container-fluid,none',
                'padding_top' => 'sometimes|string|in:0,1,3,5',
                'padding_bottom' => 'sometimes|string|in:0,1,3,5',
                'margin_bottom' => 'sometimes|string|in:0,2,4,5',
                'background_type' => 'sometimes|string|in:none,color,gradient,image',
                'background_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'border_style' => 'sometimes|string|in:none,solid,dashed,dotted',
                'custom_css_classes' => 'sometimes|string|max:500',
                'section_html_id' => 'sometimes|string|max:100',
                'visible_desktop' => 'sometimes|boolean',
                'visible_tablet' => 'sometimes|boolean',
                'visible_mobile' => 'sometimes|boolean',
                'custom_attributes' => 'sometimes|string|max:1000'
            ]);

            // Get current config
            $currentConfig = $section->config ? json_decode($section->config, true) : [];
            
            // Merge with new data
            $newConfig = array_merge($currentConfig, $validated);
            
            // Update the section
            $section->update([
                'config' => json_encode($newConfig)
            ]);
            
            // If name was provided, also update the template section name
            if (isset($validated['name']) && $section->templateSection) {
                $section->templateSection->update(['name' => $validated['name']]);
            }
            
            // Load fresh data for response
            $section->load('templateSection');
            
            return response()->json([
                'success' => true,
                'message' => 'Section configuration updated successfully',
                'data' => [
                    'section_id' => $section->id,
                    'config' => $newConfig,
                    'updated_at' => $section->updated_at->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating section configuration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update section configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a section
     * 
     * @param PageSection $section
     * @return JsonResponse
     */
    public function deleteSection(PageSection $section): JsonResponse
    {
        try {
            $sectionName = $section->templateSection->name ?? 'Section';
            $sectionId = $section->id;
            
            // Delete all widgets in this section first
            $section->pageSectionWidgets()->delete();
            
            // Delete the section
            $section->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Section '{$sectionName}' deleted successfully",
                'data' => [
                    'deleted_section_id' => $sectionId
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting section: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new section
     * 
     * @param Page $page
     * @param Request $request
     * @return JsonResponse
     */
    public function createSection(Page $page, Request $request): JsonResponse
    {
        try {
            // Validate the incoming data
            $validated = $request->validate([
                'template_key' => 'required|string',
                'name' => 'sometimes|string|max:255',
                'grid_x' => 'sometimes|integer|min:0',
                'grid_y' => 'sometimes|integer|min:0',
                'grid_w' => 'sometimes|integer|min:1|max:12',
                'grid_h' => 'sometimes|integer|min:1'
            ]);

            // Create the section
            $section = PageSection::create([
                'page_id' => $page->id,
                'template_section_id' => 1, // This would need to be determined by template_key
                'position' => PageSection::where('page_id', $page->id)->max('position') + 1,
                'grid_x' => $validated['grid_x'] ?? 0,
                'grid_y' => $validated['grid_y'] ?? 0,
                'grid_w' => $validated['grid_w'] ?? 12,
                'grid_h' => $validated['grid_h'] ?? 4,
                'config' => json_encode([
                    'name' => $validated['name'] ?? 'New Section',
                    'section_type' => 'content'
                ])
            ]);
            
            $section->load('templateSection');
            
            return response()->json([
                'success' => true,
                'message' => 'Section created successfully',
                'data' => $section
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error creating section: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update section position (for GridStack)
     * 
     * @param PageSection $section
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSectionPosition(PageSection $section, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grid_x' => 'required|integer|min:0',
                'grid_y' => 'required|integer|min:0',
                'grid_w' => 'required|integer|min:1|max:12',
                'grid_h' => 'required|integer|min:1'
            ]);

            $section->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Section position updated successfully',
                'data' => [
                    'section_id' => $section->id,
                    'grid_x' => $section->grid_x,
                    'grid_y' => $section->grid_y,
                    'grid_w' => $section->grid_w,
                    'grid_h' => $section->grid_h
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating section position: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update section position: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new widget to a section
     * Exact copy from LivePreviewController::addWidget
     * 
     * @param PageSection $section
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * Generate widget preview for review step
     * 
     * @param Widget $widget
     * @param Request $request
     * @return JsonResponse
     */
    public function previewWidget(Widget $widget, Request $request): JsonResponse
    {
        try {
            $widgetConfig = $request->widget_config ?? [];
            $selectedItems = $request->selected_items ?? [];
            $contentQuery = $request->content_query ?? null;
            $contentTypeId = $request->content_type_id;

            // Create a temporary widget configuration for preview
            $previewSettings = array_merge(
                $widget->default_settings ?? [],
                [
                    'widget_fields' => $widgetConfig['widget_fields'] ?? [],
                    'layout' => $widgetConfig['layout'] ?? [],
                    'styling' => $widgetConfig['styling'] ?? [],
                    'advanced' => $widgetConfig['advanced'] ?? []
                ]
            );

            // Generate preview HTML (simplified for now)
            $previewHtml = $this->generateWidgetPreviewHtml($widget, $previewSettings, $selectedItems, $contentQuery, $contentTypeId);

            // Create widget summary
            $widgetSummary = [
                'name' => $widget->name,
                'content_type' => $contentTypeId ? ContentType::find($contentTypeId)?->name ?? 'Unknown' : 'None',
                'items_count' => count($selectedItems) ?: ($contentQuery ? ($contentQuery['limit'] ?? 10) : 0),
                'settings_count' => count($widgetConfig['widget_fields'] ?? [])
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'preview_html' => $previewHtml,
                    'widget_summary' => $widgetSummary
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error generating widget preview: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available section templates for the left sidebar
     * 
     * @return JsonResponse
     */
    public function getAvailableSectionTemplates(): JsonResponse
    {
        try {
            // Get active theme
            $activeTheme = \App\Models\Theme::where('is_active', true)->first();
            
            if (!$activeTheme) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active theme found'
                ], 404);
            }

            // Core section templates (universal across all themes)
            $coreTemplates = [
                [
                    'key' => 'full-width',
                    'name' => 'Full Width',
                    'description' => 'Single column spanning full container width',
                    'icon' => 'ri-layout-row-line',
                    'preview_image' => asset('assets/admin/images/sections/full-width-preview.png'),
                    'category' => 'layout',
                    'type' => 'core'
                ],
                [
                    'key' => 'multi-column',
                    'name' => 'Multi Column',
                    'description' => 'Dynamic columns based on widget count (2-6 widgets)',
                    'icon' => 'ri-layout-column-line',
                    'preview_image' => asset('assets/admin/images/sections/multi-column-preview.png'),
                    'category' => 'layout',
                    'type' => 'core'
                ],
                [
                    'key' => 'sidebar-left',
                    'name' => 'Sidebar Left',
                    'description' => 'Left sidebar with main content area',
                    'icon' => 'ri-layout-left-2-line',
                    'preview_image' => asset('assets/admin/images/sections/sidebar-left-preview.png'),
                    'category' => 'layout',
                    'type' => 'core'
                ],
                [
                    'key' => 'sidebar-right',
                    'name' => 'Sidebar Right',
                    'description' => 'Right sidebar with main content area',
                    'icon' => 'ri-layout-right-2-line',
                    'preview_image' => asset('assets/admin/images/sections/sidebar-right-preview.png'),
                    'category' => 'layout',
                    'type' => 'core'
                ]
            ];

            // Discover theme-specific sections
            $themeTemplates = $this->discoverThemeSections($activeTheme);

            // Merge and deduplicate (core templates take precedence)
            $allTemplates = $this->mergeAndDeduplicateTemplates($coreTemplates, $themeTemplates);

            return response()->json([
                'success' => true,
                'data' => [
                    'theme' => [
                        'name' => $activeTheme->name,
                        'slug' => $activeTheme->slug
                    ],
                    'templates' => $allTemplates,
                    'total_count' => count($allTemplates)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading section templates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load section templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Discover theme-specific section templates
     * 
     * @param \App\Models\Theme $theme
     * @return array
     */
    private function discoverThemeSections($theme): array
    {
        $sectionPath = resource_path("themes/{$theme->slug}/sections");
        $themeTemplates = [];

        if (!is_dir($sectionPath)) {
            return $themeTemplates;
        }

        $files = glob($sectionPath . '/*.blade.php');
        
        foreach ($files as $file) {
            $filename = basename($file, '.blade.php');
            
            // Skip if it's a core template (avoid duplicates)
            if (in_array($filename, ['full-width', 'multi-column', 'sidebar-left', 'sidebar-right', 'default'])) {
                continue;
            }

            // Extract metadata from file comments or use defaults
            $metadata = $this->extractSectionMetadata($file);
            
            $themeTemplates[] = [
                'key' => $filename,
                'name' => $metadata['name'] ?? ucwords(str_replace('-', ' ', $filename)),
                'description' => $metadata['description'] ?? "Theme-specific {$filename} section layout",
                'icon' => $metadata['icon'] ?? 'ri-layout-grid-line',
                'preview_image' => $this->getSectionPreviewImage($theme, $filename),
                'category' => $metadata['category'] ?? 'theme',
                'type' => 'theme'
            ];
        }

        return $themeTemplates;
    }

    /**
     * Extract metadata from section file comments
     * 
     * @param string $filePath
     * @return array
     */
    private function extractSectionMetadata($filePath): array
    {
        $content = file_get_contents($filePath);
        $metadata = [];

        // Look for metadata in comments at the top of the file
        if (preg_match('/{{--\s*@section-name:\s*(.+?)\s*--}}/i', $content, $matches)) {
            $metadata['name'] = trim($matches[1]);
        }

        if (preg_match('/{{--\s*@section-description:\s*(.+?)\s*--}}/i', $content, $matches)) {
            $metadata['description'] = trim($matches[1]);
        }

        if (preg_match('/{{--\s*@section-icon:\s*(.+?)\s*--}}/i', $content, $matches)) {
            $metadata['icon'] = trim($matches[1]);
        }

        if (preg_match('/{{--\s*@section-category:\s*(.+?)\s*--}}/i', $content, $matches)) {
            $metadata['category'] = trim($matches[1]);
        }

        return $metadata;
    }

    /**
     * Get section preview image path
     * 
     * @param \App\Models\Theme $theme
     * @param string $sectionKey
     * @return string
     */
    private function getSectionPreviewImage($theme, $sectionKey): string
    {
        $previewPath = "themes/{$theme->slug}/sections/previews/{$sectionKey}.png";
        
        if (file_exists(public_path($previewPath))) {
            return asset($previewPath);
        }
        
        // Fallback to admin section placeholder
        return asset('assets/admin/images/sections/section-placeholder.png');
    }

    /**
     * Merge and deduplicate section templates (core takes precedence)
     * 
     * @param array $coreTemplates
     * @param array $themeTemplates
     * @return array
     */
    private function mergeAndDeduplicateTemplates($coreTemplates, $themeTemplates): array
    {
        $coreKeys = array_column($coreTemplates, 'key');
        
        // Filter out theme templates that conflict with core templates
        $filteredThemeTemplates = array_filter($themeTemplates, function($template) use ($coreKeys) {
            return !in_array($template['key'], $coreKeys);
        });

        // Merge core first, then theme templates
        return array_merge($coreTemplates, $filteredThemeTemplates);
    }

    /**
     * Generate widget preview HTML
     */
    private function generateWidgetPreviewHtml($widget, $settings, $selectedItems, $contentQuery, $contentTypeId)
    {
        // For now, create a basic preview showing widget info
        $itemCount = count($selectedItems) ?: ($contentQuery ? ($contentQuery['limit'] ?? 10) : 0);
        
        $previewHtml = '<div class="widget-preview-demo border rounded p-3">';
        $previewHtml .= '<div class="d-flex align-items-center mb-2">';
        $previewHtml .= '<i class="' . ($widget->icon ?? 'ri-puzzle-line') . ' me-2 text-primary fs-4"></i>';
        $previewHtml .= '<div>';
        $previewHtml .= '<h6 class="mb-0">' . htmlspecialchars($widget->name) . '</h6>';
        $previewHtml .= '<small class="text-muted">Widget Preview</small>';
        $previewHtml .= '</div>';
        $previewHtml .= '</div>';

        if ($itemCount > 0) {
            $previewHtml .= '<div class="alert alert-info small mb-2">';
            $previewHtml .= '<i class="ri-database-line me-1"></i>';
            $previewHtml .= 'This widget will display ' . $itemCount . ' content item(s)';
            $previewHtml .= '</div>';
        }

        // Show widget field settings if any
        $widgetFields = $settings['widget_fields'] ?? [];
        if (!empty($widgetFields)) {
            $previewHtml .= '<div class="widget-settings-preview small">';
            $previewHtml .= '<strong class="d-block mb-1">Settings:</strong>';
            foreach ($widgetFields as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $displayValue = is_bool($value) ? ($value ? 'Yes' : 'No') : htmlspecialchars((string)$value);
                    $previewHtml .= '<div><span class="text-muted">' . htmlspecialchars($key) . ':</span> ' . $displayValue . '</div>';
                }
            }
            $previewHtml .= '</div>';
        }

        // Show layout info
        $layout = $settings['layout'] ?? [];
        if (!empty($layout)) {
            $previewHtml .= '<div class="layout-preview-info small mt-2 text-muted">';
            $previewHtml .= '<i class="ri-layout-grid-line me-1"></i>';
            $previewHtml .= ($layout['width'] ?? 12) . ' columns, ' . ($layout['height'] ?? 'auto') . ' height';
            $previewHtml .= '</div>';
        }

        $previewHtml .= '</div>';

        return $previewHtml;
    }

    public function addWidget(PageSection $section, Request $request): JsonResponse
    {
        \Log::info('Widget submission received:', [
            'request_data' => $request->all(),
            'section_id' => $section->id
        ]);

        try {
            $request->validate([
                'widget_id' => 'required|exists:widgets,id',
                'widget_config' => 'sometimes|array',
                'selected_items' => 'sometimes|array',
                'content_query' => 'sometimes|nullable' // Allow object/array/null
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Widget validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            $widget = Widget::findOrFail($request->widget_id);
            $widgetConfig = $request->widget_config ?? [];
            
            // Get next position in section
            $nextPosition = $section->pageSectionWidgets()->max('position') + 1;

            // Prepare settings from widget configuration
            $settings = array_merge(
                $widget->default_settings ?? [],
                [
                    'widget_fields' => $widgetConfig['widget_fields'] ?? [],
                    'layout' => $widgetConfig['layout'] ?? [],
                    'styling' => $widgetConfig['styling'] ?? [],
                    'advanced' => $widgetConfig['advanced'] ?? []
                ]
            );

            // Prepare content query - handle different scenarios
            $contentQuery = [];
            
            // If content_query is already provided by frontend (structured correctly), use it
            if (!empty($request->content_query) && is_array($request->content_query)) {
                $contentQuery = $request->content_query;
            } 
            // Fallback: build from selected_items (legacy support)
            elseif (!empty($request->selected_items) && $request->content_type_id) {
                $contentQuery = [
                    'limit' => count($request->selected_items),
                    'filters' => [],
                    'sort_by' => 'created_at',
                    'query_type' => 'multiple', 
                    'sort_order' => 'desc',
                    'content_type_id' => (int) $request->content_type_id,
                    'content_item_ids' => array_map('intval', $request->selected_items)
                ];
            }
            // Widget that doesn't use content or no content selected
            else {
                $contentQuery = [];
            }

            // Create new widget instance with full configuration
            $widgetInstance = PageSectionWidget::create([
                'page_section_id' => $section->id,
                'widget_id' => $widget->id,
                'position' => $nextPosition,
                // GridStack positioning (using database defaults where available)
                'grid_x' => 0,      // Default: 0
                'grid_y' => 0,      // Default: 0  
                'grid_w' => 12,     // Default: 12 (full width)
                'grid_h' => 4,      // Default: 4 (as per schema)
                'grid_id' => 'widget_temp_' . uniqid(), // Required field, no default
                // Widget configuration
                'settings' => $settings,
                'content_query' => $contentQuery
            ]);

            // Generate proper grid ID after creation (widget_1, widget_2, etc.)
            $widgetInstance->update([
                'grid_id' => 'widget_' . $widgetInstance->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Widget added successfully to ' . $section->templateSection->name ?? 'section',
                'data' => [
                    'widget_instance_id' => $widgetInstance->id,
                    'widget_name' => $widget->name,
                    'section_name' => $section->templateSection->name ?? 'Section',
                    'refresh_preview' => true,
                    'refresh_structure' => true
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to add widget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme assets for API endpoint
     * 
     * @return JsonResponse
     */
    public function getThemeAssets(): JsonResponse
    {
        try {
            // Get the active theme from a sample page to determine current theme
            $samplePage = \App\Models\Page::with('template.theme')->first();
            
            if ($samplePage && $samplePage->template && $samplePage->template->theme) {
                $theme = $samplePage->template->theme;
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'name' => $theme->name,
                        'slug' => $theme->slug,
                        'base_path' => "/themes/{$theme->slug}",
                        'css' => $theme->css_files ?? [],
                        'js' => $theme->js_files ?? []
                    ]
                ]);
            }
            
            // Fallback to defaults
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => 'Default Theme',
                    'slug' => 'default',
                    'base_path' => '/themes/default',
                    'css' => [],
                    'js' => []
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading theme assets: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => 'Default Theme',
                    'slug' => 'default',
                    'base_path' => '/themes/default',
                    'css' => [],
                    'js' => []
                ]
            ]);
        }
    }

    /**
     * Generate full page HTML for Page Builder
     * Based on LivePreviewController::generateFullPageHtml
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

        // Build complete HTML with preview helpers
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder - ' . e($page->title) . '</title>
    
    <!-- Theme Assets -->
    ' . $this->getThemeAssetsHtml($page) . '
    
    <!-- Preview Helper Assets -->
    ' . $previewAssets . '
    
    <!-- Preview Structure Data -->
    ' . $this->getPreviewStructureScript($page) . '
</head>
<body>
    ' . $pageContent . '
</body>
</html>';

        return $html;
    }

    /**
     * Inject preview data using simple approach that leverages existing page structure
     * Exact copy from LivePreviewController::injectPreviewDataFromStructure
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
     * Exact copy from LivePreviewController::getPreviewStructureScript
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
                console.log("🏗️ Page Builder page structure loaded:", window.previewPageStructure);
            </script>';
        }
        
        return '<script>window.previewPageStructure = null;</script>';
    }

    /**
     * Get theme assets for the page
     * Exact copy from LivePreviewController::getThemeAssets
     * 
     * @param Page $page
     * @return string
     */
    private function getThemeAssetsHtml(Page $page): string
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
     * Exact copy from LivePreviewController::getPreviewAssets
     * 
     * @return string
     */
    private function getPreviewAssets(): string
    {
        return '
    <!-- Preview Helper CSS -->
    <link rel="stylesheet" href="' . asset('assets/admin/css/live-designer/preview-helpers.css') . '">
    
    <!-- Preview Helper JS -->
    <script src="' . asset('assets/admin/js/live-designer/preview-helpers.js') . '"></script>
    
    <!-- Internal Preview Spacing -->
    <style>
        body {
            padding: 15px !important;
            margin: 0 !important;
            min-height: calc(100vh - 30px) !important;
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
    </style>';
    }

    /**
     * Create a new content item with default field values
     * 
     * @param ContentType $contentType
     * @param Request $request
     * @return JsonResponse
     */
    public function createDefaultContentItem(ContentType $contentType, Request $request): JsonResponse
    {
        try {
            // Validate basic input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:draft,published,archived',
                'field_values' => 'nullable|array'
            ]);

            // Set defaults
            $title = $validated['title'];
            $slug = $validated['slug'] ?? \Illuminate\Support\Str::slug($title);
            $status = $validated['status'] ?? 'draft';
            
            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\ContentItem::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            // Create the content item
            $contentItem = \App\Models\ContentItem::create([
                'title' => $title,
                'slug' => $slug,
                'status' => $status,
                'content_type_id' => $contentType->id,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);

            // Load content type fields to create default field values
            $contentType->load(['fields' => function($query) {
                $query->orderBy('position')->orderBy('id');
            }]);

            // Create default field values for each field
            foreach ($contentType->fields as $field) {
                $defaultValue = $this->getDefaultFieldValue($field);
                
                if ($defaultValue !== null) {
                    \App\Models\ContentFieldValue::create([
                        'content_item_id' => $contentItem->id,
                        'content_type_field_id' => $field->id,
                        'value' => $defaultValue
                    ]);
                }
            }

            // Load the created item with its values for response
            $contentItem->load(['fieldValues.field', 'contentType']);

            // Format for response (same format as getContentTypeItems)
            $formattedItem = [
                'id' => $contentItem->id,
                'title' => $contentItem->title,
                'slug' => $contentItem->slug,
                'status' => $contentItem->status,
                'excerpt' => \Illuminate\Support\Str::limit($contentItem->title, 100),
                'created_at' => $contentItem->created_at->format('Y-m-d H:i'),
                'updated_at' => $contentItem->updated_at->format('Y-m-d H:i'),
                'thumbnail' => $this->getItemThumbnail($contentItem)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Content item created successfully',
                'data' => $formattedItem
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating default content item: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create content item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default value for a field based on its type
     * 
     * @param mixed $field
     * @return mixed
     */
    private function getDefaultFieldValue($field)
    {
        // Use field's default value if set
        if ($field->default_value !== null && $field->default_value !== '') {
            return $field->default_value;
        }

        // Generate default based on field type
        switch ($field->field_type) {
            case 'text':
                return "Default " . $field->name;
                
            case 'textarea':
                return "Default content for " . $field->name;
                
            case 'rich_text':
                return "<p>Default rich text content for " . $field->name . "</p>";
                
            case 'number':
                return 0;
                
            case 'date':
                return date('Y-m-d');
                
            case 'datetime':
                return date('Y-m-d H:i:s');
                
            case 'boolean':
                return false;
                
            case 'select':
            case 'radio':
                // Try to use first option if available
                $settings = is_string($field->settings) ? json_decode($field->settings, true) : $field->settings;
                if (isset($settings['options']) && is_array($settings['options']) && !empty($settings['options'])) {
                    return $settings['options'][0]['value'] ?? '';
                }
                return '';
                
            case 'multiselect':
            case 'checkbox':
                return json_encode([]);
                
            case 'email':
                return 'example@' . strtolower($field->name) . '.com';
                
            case 'url':
                return 'https://example.com/' . strtolower($field->slug);
                
            case 'phone':
                return '+1234567890';
                
            case 'color':
                return '#007bff';
                
            case 'json':
                return json_encode([$field->slug => 'Default ' . $field->name]);
                
            case 'image':
            case 'gallery':
            case 'file':
            case 'relation':
                return null; // These need to be set manually
                
            default:
                return "Default " . $field->name;
        }
    }

}