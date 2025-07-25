<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Models\Theme;
use App\Services\ThemeManager;
use App\Services\WidgetSchemaService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SectionSchemaService
{
    protected $themeManager;
    protected $widgetSchemaService;
    
    public function __construct(ThemeManager $themeManager, WidgetSchemaService $widgetSchemaService)
    {
        $this->themeManager = $themeManager;
        $this->widgetSchemaService = $widgetSchemaService;
    }

    /**
     * Get all section schemas for a specific page
     *
     * @param Page $page
     * @return array
     */
    public function getPageSectionSchemas(Page $page): array
    {
        $cacheKey = 'page_section_schemas_' . $page->id;
        
        return Cache::remember($cacheKey, 1800, function () use ($page) {
            $schemas = [];
            
            Log::info('Loading page sections from database', ['page_id' => $page->id]);
            
            // Get all sections for the page with their widgets and template sections
            $sections = PageSection::where('page_id', $page->id)
                ->with(['pageSectionWidgets.widget.theme', 'templateSection'])
                ->orderBy('position')
                ->get();
            
            Log::info('Found page sections', [
                'page_id' => $page->id,
                'section_count' => $sections->count()
            ]);
            
            foreach ($sections as $section) {
                Log::info('Processing section', [
                    'section_id' => $section->id,
                    'widget_count' => $section->pageSectionWidgets->count()
                ]);
                
                try {
                    $schema = $this->convertSectionToSchema($section);
                    if ($schema) {
                        $schemas[] = $schema;
                        Log::info('Successfully converted section to schema', ['section_id' => $section->id]);
                    } else {
                        Log::warning('convertSectionToSchema returned null', ['section_id' => $section->id]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error converting section to schema', [
                        'section_id' => $section->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue processing other sections
                }
            }
            
            Log::debug('Loaded page section schemas', [
                'page_id' => $page->id,
                'section_count' => count($schemas)
            ]);
            
            return $schemas;
        });
    }

    /**
     * Get schema for a specific section
     *
     * @param PageSection $section
     * @return array|null
     */
    public function getSectionSchema(PageSection $section): ?array
    {
        try {
            // Load section with all related data
            $section->load(['pageSectionWidgets.widget.theme', 'templateSection']);
            
            return $this->convertSectionToSchema($section);
            
        } catch (\Exception $e) {
            Log::error('Error loading section schema', [
                'section_id' => $section->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convert PageSection to GrapesJS-compatible schema
     *
     * @param PageSection $section
     * @return array
     */
    protected function convertSectionToSchema(PageSection $section): array
    {
        // Get section type configuration
        $sectionType = $this->determineSectionType($section);
        $columnConfig = $this->getSectionColumnConfiguration($section, $sectionType);
        
        // Process widgets into columns
        $columns = $this->distributeWidgetsIntoColumns($section->pageSectionWidgets, $columnConfig);
        
        // Generate section name from template section or fallback
        $sectionName = "Section {$section->id}";
        if ($section->templateSection) {
            $sectionName = $section->templateSection->name ?? $sectionName;
        }
        
        return [
            'id' => $section->id,
            'name' => $sectionName,
            'type' => $sectionType,
            'template_section_id' => $section->template_section_id,
            'position' => $section->position,
            'is_active' => true, // Default to active since field doesn't exist
            'settings' => [
                'css_classes' => $section->css_classes,
                'background_color' => $section->background_color,
                'padding' => $section->padding,
                'margin' => $section->margin,
                'column_span_override' => $section->column_span_override,
                'column_offset_override' => $section->column_offset_override
            ],
            'columns' => $columns,
            'grapesjs' => [
                'component_type' => 'section',
                'block_id' => 'section-' . $section->id,
                'draggable' => true,
                'droppable' => '[data-gjs-type="widget"]',
                'resizable' => false,
                'editable' => false
            ],
            'meta' => [
                'widget_count' => $section->pageSectionWidgets->count(),
                'created_at' => $section->created_at?->toISOString(),
                'updated_at' => $section->updated_at?->toISOString()
            ]
        ];
    }

    /**
     * Determine section type based on section data
     *
     * @param PageSection $section
     * @return string
     */
    protected function determineSectionType(PageSection $section): string
    {
        // Since layout_settings field doesn't exist, determine type based on widget count
        // Check template section if available
        if ($section->template_section_id) {
            // You could load template section data here if needed
            // For now, use generic types based on widget count
        }
        
        $widgetCount = $section->pageSectionWidgets->count();
        
        // Determine type based on widget count and layout
        if ($widgetCount === 0) {
            return 'empty';
        } elseif ($widgetCount === 1) {
            return 'full-width';
        } elseif ($widgetCount === 2) {
            return 'two-column';
        } elseif ($widgetCount === 3) {
            return 'three-column';
        } elseif ($widgetCount === 4) {
            return 'four-column';
        } else {
            return 'multi-column';
        }
    }

    /**
     * Get column configuration for section type
     *
     * @param PageSection $section
     * @param string $sectionType
     * @return array
     */
    protected function getSectionColumnConfiguration(PageSection $section, string $sectionType): array
    {
        // Default column configurations
        $configurations = [
            'empty' => [['class' => 'col-12', 'widgets' => []]],
            'full-width' => [['class' => 'col-12', 'widgets' => []]],
            'two-column' => [
                ['class' => 'col-md-6', 'widgets' => []],
                ['class' => 'col-md-6', 'widgets' => []]
            ],
            'three-column' => [
                ['class' => 'col-md-4', 'widgets' => []],
                ['class' => 'col-md-4', 'widgets' => []],
                ['class' => 'col-md-4', 'widgets' => []]
            ],
            'four-column' => [
                ['class' => 'col-md-3', 'widgets' => []],
                ['class' => 'col-md-3', 'widgets' => []],
                ['class' => 'col-md-3', 'widgets' => []],
                ['class' => 'col-md-3', 'widgets' => []]
            ],
            'multi-column' => [['class' => 'col-12', 'widgets' => []]] // All in one column
        ];

        // Since layout_settings field doesn't exist, just return default configuration
        return $configurations[$sectionType] ?? $configurations['full-width'];
    }

    /**
     * Distribute widgets into columns based on configuration
     *
     * @param \Illuminate\Database\Eloquent\Collection $widgets
     * @param array $columnConfig
     * @return array
     */
    protected function distributeWidgetsIntoColumns($widgets, array $columnConfig): array
    {
        $columns = [];
        $widgetIndex = 0;
        
        // Initialize columns
        foreach ($columnConfig as $index => $config) {
            $columns[$index] = [
                'id' => $index,
                'class' => $config['class'] ?? 'col-12',
                'widgets' => []
            ];
        }
        
        // First, place widgets that have specific column positions
        $remainingWidgets = collect();
        
        foreach ($widgets as $widget) {
            if ($widget->column_position !== null) {
                // Try to find the column by position/index
                $columnIndex = is_numeric($widget->column_position) ? (int)$widget->column_position : 0;
                if (isset($columns[$columnIndex])) {
                    $columns[$columnIndex]['widgets'][] = $this->convertWidgetToColumnSchema($widget);
                } else {
                    // If column doesn't exist, add to first column
                    $columns[0]['widgets'][] = $this->convertWidgetToColumnSchema($widget);
                }
            } else {
                $remainingWidgets->push($widget);
            }
        }
        
        // Then distribute remaining widgets evenly across columns
        if ($remainingWidgets->count() > 0) {
            $widgetsPerColumn = ceil($remainingWidgets->count() / count($columnConfig));
            $widgetIndex = 0;
            
            foreach ($columnConfig as $index => $config) {
                $columnWidgets = $remainingWidgets->slice($widgetIndex, $widgetsPerColumn);
                
                foreach ($columnWidgets as $widget) {
                    $columns[$index]['widgets'][] = $this->convertWidgetToColumnSchema($widget);
                }
                
                $widgetIndex += $widgetsPerColumn;
            }
        }
        
        return array_values($columns); // Re-index the array
    }

    /**
     * Convert PageSectionWidget to column schema format
     *
     * @param PageSectionWidget $pageSectionWidget
     * @return array
     */
    protected function convertWidgetToColumnSchema(PageSectionWidget $pageSectionWidget): array
    {
        $widget = $pageSectionWidget->widget;
        
        if (!$widget) {
            Log::warning('PageSectionWidget has no associated widget', [
                'page_section_widget_id' => $pageSectionWidget->id
            ]);
            return $this->getInvalidWidgetSchema($pageSectionWidget);
        }

        // Get widget schema for additional metadata
        try {
            $widgetSchema = $this->widgetSchemaService->getWidgetSchema($widget);
        } catch (\Exception $e) {
            Log::warning('Failed to load widget schema', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);
            $widgetSchema = null;
        }
        
        return [
            'id' => $pageSectionWidget->id,
            'widget_id' => $widget->id,
            'widget_type' => $widget->slug,
            'widget_name' => $widget->name,
            'position' => $pageSectionWidget->position,
            'is_active' => true, // Default to active since field doesn't exist
            'settings' => $pageSectionWidget->settings ?? [],
            'content_query' => $pageSectionWidget->content_query ?? null, // JSON field, not relationship
            'preview_endpoint' => "/admin/api/widgets/{$widget->id}/preview?page_section_widget_id={$pageSectionWidget->id}",
            'schema' => $widgetSchema,
            'grapesjs' => [
                'component_type' => $widget->slug . '-widget',
                'block_id' => 'widget-' . $widget->slug,
                'draggable' => '[data-section-type]',
                'droppable' => false,
                'editable' => true
            ]
        ];
    }

    /**
     * Get schema for invalid/missing widget
     *
     * @param PageSectionWidget $pageSectionWidget
     * @return array
     */
    protected function getInvalidWidgetSchema(PageSectionWidget $pageSectionWidget): array
    {
        return [
            'id' => $pageSectionWidget->id,
            'widget_id' => null,
            'widget_type' => 'invalid',
            'widget_name' => 'Invalid Widget',
            'position' => $pageSectionWidget->position,
            'is_active' => false,
            'settings' => [],
            'content_query' => null,
            'preview_endpoint' => null,
            'schema' => null,
            'error' => 'Associated widget not found',
            'grapesjs' => [
                'component_type' => 'invalid-widget',
                'block_id' => 'invalid-widget',
                'draggable' => false,
                'droppable' => false,
                'editable' => false
            ]
        ];
    }

    /**
     * Get available section types for the active theme
     *
     * @return array
     */
    public function getAvailableSectionTypes(): array
    {
        $activeTheme = $this->themeManager->getActiveTheme();
        
        if (!$activeTheme) {
            return $this->getDefaultSectionTypes();
        }

        $cacheKey = 'section_types_' . $activeTheme->slug;
        
        return Cache::remember($cacheKey, 3600, function () use ($activeTheme) {
            $sectionTypes = $this->getDefaultSectionTypes();
            
            // Try to load theme-specific section types
            $themeConfigPath = resource_path("themes/{$activeTheme->slug}/theme.json");
            
            if (File::exists($themeConfigPath)) {
                $themeConfig = json_decode(File::get($themeConfigPath), true);
                
                if (isset($themeConfig['section_types']) && is_array($themeConfig['section_types'])) {
                    $sectionTypes = array_merge($sectionTypes, $themeConfig['section_types']);
                }
            }
            
            return $sectionTypes;
        });
    }

    /**
     * Get default section types
     *
     * @return array
     */
    protected function getDefaultSectionTypes(): array
    {
        return [
            'full-width' => [
                'name' => 'Full Width',
                'description' => 'Single column spanning full width',
                'columns' => [['class' => 'col-12']],
                'icon' => 'ri-layout-column-line'
            ],
            'two-column' => [
                'name' => 'Two Columns',
                'description' => 'Two equal columns',
                'columns' => [
                    ['class' => 'col-md-6'],
                    ['class' => 'col-md-6']
                ],
                'icon' => 'ri-layout-2-line'
            ],
            'three-column' => [
                'name' => 'Three Columns',
                'description' => 'Three equal columns',
                'columns' => [
                    ['class' => 'col-md-4'],
                    ['class' => 'col-md-4'],
                    ['class' => 'col-md-4']
                ],
                'icon' => 'ri-layout-3-line'
            ],
            'four-column' => [
                'name' => 'Four Columns',
                'description' => 'Four equal columns',
                'columns' => [
                    ['class' => 'col-md-3'],
                    ['class' => 'col-md-3'],
                    ['class' => 'col-md-3'],
                    ['class' => 'col-md-3']
                ],
                'icon' => 'ri-layout-4-line'
            ],
            'sidebar-left' => [
                'name' => 'Sidebar Left',
                'description' => 'Sidebar on left, content on right',
                'columns' => [
                    ['class' => 'col-md-3'],
                    ['class' => 'col-md-9']
                ],
                'icon' => 'ri-layout-left-line'
            ],
            'sidebar-right' => [
                'name' => 'Sidebar Right',
                'description' => 'Content on left, sidebar on right',
                'columns' => [
                    ['class' => 'col-md-9'],
                    ['class' => 'col-md-3']
                ],
                'icon' => 'ri-layout-right-line'
            ]
        ];
    }

    /**
     * Create a new section schema for GrapesJS
     *
     * @param string $sectionType
     * @param array $options
     * @return array
     */
    public function createNewSectionSchema(string $sectionType = 'full-width', array $options = []): array
    {
        $sectionTypes = $this->getAvailableSectionTypes();
        $typeConfig = $sectionTypes[$sectionType] ?? $sectionTypes['full-width'];
        
        return [
            'id' => 'new-section-' . uniqid(),
            'name' => $options['name'] ?? 'New Section',
            'type' => $sectionType,
            'template_section_id' => null,
            'position' => $options['position'] ?? 0,
            'is_active' => true,
            'settings' => $options['settings'] ?? [],
            'columns' => $this->initializeEmptyColumns($typeConfig['columns']),
            'grapesjs' => [
                'component_type' => 'section',
                'block_id' => 'section-' . $sectionType,
                'draggable' => true,
                'droppable' => '[data-gjs-type="widget"]',
                'resizable' => false,
                'editable' => false
            ],
            'meta' => [
                'widget_count' => 0,
                'is_new' => true,
                'created_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Initialize empty columns for new sections
     *
     * @param array $columnConfigs
     * @return array
     */
    protected function initializeEmptyColumns(array $columnConfigs): array
    {
        $columns = [];
        
        foreach ($columnConfigs as $index => $config) {
            $columns[] = [
                'id' => $index,
                'class' => $config['class'],
                'widgets' => []
            ];
        }
        
        return $columns;
    }

    /**
     * Clear section schema cache
     *
     * @param int|null $pageId
     * @return void
     */
    public function clearCache(?int $pageId = null): void
    {
        if ($pageId) {
            Cache::forget('page_section_schemas_' . $pageId);
        } else {
            // Clear all section schema caches (this is a simplified approach)
            $activeTheme = $this->themeManager->getActiveTheme();
            if ($activeTheme) {
                Cache::forget('section_types_' . $activeTheme->slug);
            }
        }
        
        Log::info('Section schema cache cleared', ['page_id' => $pageId]);
    }

    /**
     * Validate section schema structure
     *
     * @param array $schema
     * @return array Array of validation errors (empty if valid)
     */
    public function validateSectionSchema(array $schema): array
    {
        $errors = [];
        
        if (empty($schema['name'])) {
            $errors[] = 'Section name is required';
        }
        
        if (empty($schema['type'])) {
            $errors[] = 'Section type is required';
        }
        
        if (!isset($schema['columns']) || !is_array($schema['columns'])) {
            $errors[] = 'Section must have columns array';
        }
        
        // Validate columns
        if (isset($schema['columns'])) {
            foreach ($schema['columns'] as $index => $column) {
                if (empty($column['class'])) {
                    $errors[] = "Column at index {$index} is missing CSS class";
                }
                
                if (!isset($column['widgets']) || !is_array($column['widgets'])) {
                    $errors[] = "Column at index {$index} must have widgets array";
                }
            }
        }
        
        return $errors;
    }
} 