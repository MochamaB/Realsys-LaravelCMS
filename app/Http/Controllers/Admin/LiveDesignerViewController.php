<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\WidgetType;
use Illuminate\Http\Request;

/**
 * LiveDesigner View Controller
 * 
 * This controller ONLY handles view rendering for live preview interface.
 * All business logic and API endpoints are handled by Api\LivePreviewController.
 * 
 * Purpose: Provide clean separation between view rendering and API logic.
 */
class LiveDesignerViewController extends Controller
{
    /**
     * Show live preview interface
     * 
     * Renders the live preview interface with iframe-based editing:
     * - Left Sidebar: Page structure with sections and widgets
     * - Center Canvas: Iframe preview using existing template renderer
     * - Right Sidebar: Widget editor forms with real-time updates
     * 
     * Uses existing widget system and template rendering for maximum compatibility.
     * 
     * @param Page $page The page to edit in the live preview
     * @return \Illuminate\View\View
     */
    public function show(Page $page)
    {
        // Load page with all necessary relationships for preview
        $page->load([
            'template.theme',
            'sections.templateSection',
            'sections.pageSectionWidgets.widget'
        ]);

        // Load all widget data server-side for performance
        $sectionTemplates = $this->loadAvailableSectionTemplates();
        $themeWidgets = $this->loadThemeWidgets();
        $defaultWidgets = $this->loadDefaultWidgets();

        return view('admin.pages.live-designer.show', [
            'page' => $page,
            'apiBaseUrl' => '/admin/api/live-preview',
            'pageTitle' => 'Live Preview - ' . $page->title,
            'sectionTemplates' => $sectionTemplates,
            'themeWidgets' => $themeWidgets,
            'defaultWidgets' => $defaultWidgets,
            'breadcrumbs' => [
                ['name' => 'Pages', 'url' => route('admin.pages.index')],
                ['name' => $page->title, 'url' => route('admin.pages.show', $page)],
                ['name' => 'Live Preview', 'url' => null]
            ]
        ]);
    }

    /**
     * Load available section templates for the left sidebar
     */
    private function loadAvailableSectionTemplates()
    {
        // Get active theme
        $activeTheme = \App\Models\Theme::where('is_active', true)->first();
        
        if (!$activeTheme) {
            return [];
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

        return [
            'theme' => [
                'name' => $activeTheme->name,
                'slug' => $activeTheme->slug
            ],
            'templates' => $allTemplates,
            'total_count' => count($allTemplates)
        ];
    }

    /**
     * Load theme widgets as flat array with content type info
     */
    private function loadThemeWidgets()
    {
        // Get active theme
        $activeTheme = \App\Models\Theme::where('is_active', true)->first();
        
        if (!$activeTheme) {
            return [];
        }

        // Get widgets for active theme only
        $widgets = \App\Models\Widget::where('theme_id', $activeTheme->id)
            ->with(['contentTypes', 'fieldDefinitions'])
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'icon', 'theme_id', 'slug', 'view_path']);

        // Return flat array (no category grouping) but keep current preview structure
        return $widgets->map(function($widget) use ($activeTheme) {
            return [
                'id' => $widget->id,
                'name' => $widget->name,
                'description' => $widget->description,
                'icon' => $widget->icon ?? 'ri-puzzle-line',
                'preview_image' => $this->getWidgetPreviewImage($widget, $activeTheme),
                'slug' => $widget->slug,
                'has_content_types' => $widget->contentTypes->count() > 0,
                'content_types_count' => $widget->contentTypes->count(),
                'content_types' => $widget->contentTypes->map(function($ct) {
                    return [
                        'id' => $ct->id,
                        'name' => $ct->name,
                        'slug' => $ct->slug,
                        'icon' => $ct->icon ?? 'ri-file-list-line',
                        'items_count' => $ct->contentItems()->count()
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Load default widgets (hardcoded for performance)
     */
    private function loadDefaultWidgets()
    {
        return [
            [
                'id' => 'default-1',
                'name' => 'Text Block',
                'slug' => 'text-block',
                'description' => 'Rich text content block',
                'icon' => 'ri-text',
                'category' => 'content'
            ],
            [
                'id' => 'default-2',
                'name' => 'Heading',
                'slug' => 'heading',
                'description' => 'Title and subtitle text',
                'icon' => 'ri-heading',
                'category' => 'content'
            ],
            [
                'id' => 'default-3',
                'name' => 'Image',
                'slug' => 'image',
                'description' => 'Single image display',
                'icon' => 'ri-image-line',
                'category' => 'media'
            ],
            [
                'id' => 'default-4',
                'name' => 'Button',
                'slug' => 'button',
                'description' => 'Call-to-action button',
                'icon' => 'ri-checkbox-blank-line',
                'category' => 'interactive'
            ],
            [
                'id' => 'default-5',
                'name' => 'Spacer',
                'slug' => 'spacer',
                'description' => 'Empty space divider',
                'icon' => 'ri-separator',
                'category' => 'layout'
            ],
            [
                'id' => 'default-6',
                'name' => 'Divider',
                'slug' => 'divider',
                'description' => 'Visual separator line',
                'icon' => 'ri-subtract-line',
                'category' => 'layout'
            ]
        ];
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
            
            $themeTemplates[] = [
                'key' => $filename,
                'name' => ucwords(str_replace('-', ' ', $filename)),
                'description' => "Theme section: {$filename}",
                'icon' => 'ri-layout-grid-line',
                'preview_image' => asset("themes/{$theme->slug}/sections/{$filename}/preview.png"),
                'category' => 'theme',
                'type' => 'theme'
            ];
        }

        return $themeTemplates;
    }

    /**
     * Merge and deduplicate templates (core templates take precedence)
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
        
        return array_merge($coreTemplates, $filteredThemeTemplates);
    }

    /**
     * Get content items for a specific content type (API endpoint)
     * 
     * @param \App\Models\ContentType $contentType
     * @param Request $request
     * @return JsonResponse
     */
    public function getContentItems(\App\Models\ContentType $contentType, Request $request)
    {
        try {
            $items = $contentType->contentItems()
                ->with(['fieldValues.field'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'content_type' => [
                        'id' => $contentType->id,
                        'name' => $contentType->name,
                        'slug' => $contentType->slug
                    ],
                    'items' => $items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'excerpt' => \Str::limit($item->content ?? $item->description ?? '', 60),
                            'created_at' => $item->created_at?->format('M j, Y'),
                            'thumbnail' => $this->getItemThumbnail($item)
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load content items: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load content items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content types for a specific widget
     * 
     * @param Widget $widget
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWidgetContentTypes(Widget $widget)
    {
        try {
            // Get content types associated with this widget
            $contentTypes = $widget->contentTypes()
                ->withCount('contentItems')
                ->get()
                ->map(function ($contentType) {
                    return [
                        'id' => $contentType->id,
                        'name' => $contentType->name,
                        'slug' => $contentType->slug,
                        'description' => $contentType->description,
                        'icon' => $contentType->icon ?? 'ri-folder-line',
                        'items_count' => $contentType->content_items_count ?? 0,
                        'is_active' => $contentType->is_active
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $contentTypes,
                'message' => 'Content types loaded successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading widget content types: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load content types: ' . $e->getMessage()
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
        if (!$item->fieldValues || $item->fieldValues->isEmpty()) {
            return null;
        }

        $imageField = $item->fieldValues->first(function($fv) {
            return $fv->field && 
                   in_array($fv->field->field_type ?? '', ['image', 'file']) && 
                   $fv->value &&
                   str_contains($fv->value, 'image');
        });
        
        return $imageField && $imageField->value ? asset($imageField->value) : null;
    }
}
