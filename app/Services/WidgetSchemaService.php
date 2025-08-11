<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WidgetSchemaService
{
    protected $themeManager;
    
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Get all widget schemas for the active theme
     *
     * @return array
     */
    public function getAllWidgetSchemas(): array
    {
        $cacheKey = 'widget_schemas_' . $this->themeManager->getActiveTheme()->slug;
        
        return Cache::remember($cacheKey, 3600, function () {
            $schemas = [];
            $activeTheme = $this->themeManager->getActiveTheme();
            
            if (!$activeTheme) {
                Log::warning('No active theme found for widget schemas');
                return [];
            }

            // Get all widgets for the active theme
            $widgets = Widget::where('theme_id', $activeTheme->id)->get();
            
            foreach ($widgets as $widget) {
                $schema = $this->getWidgetSchema($widget);
                if ($schema) {
                    $schemas[] = $schema;
                }
            }
            
            Log::debug('Loaded widget schemas', [
                'theme' => $activeTheme->slug,
                'widget_count' => count($schemas)
            ]);
            
            return $schemas;
        });
    }

    /**
     * Get schema for a specific widget
     *
     * @param Widget $widget
     * @return array|null
     */
    public function getWidgetSchema(Widget $widget): ?array
    {
        try {
            $theme = $widget->theme;
            if (!$theme) {
                Log::warning('Widget has no theme', ['widget_id' => $widget->id]);
                return null;
            }

            $widgetJsonPath = $this->getWidgetJsonPath($theme, $widget);
            
            if (!File::exists($widgetJsonPath)) {
                Log::warning('Widget JSON file not found', [
                    'widget_id' => $widget->id,
                    'path' => $widgetJsonPath
                ]);
                return null;
            }

            $jsonContent = File::get($widgetJsonPath);
            $widgetDefinition = json_decode($jsonContent, true);
            
            if (!$widgetDefinition) {
                Log::error('Invalid JSON in widget definition', [
                    'widget_id' => $widget->id,
                    'path' => $widgetJsonPath
                ]);
                return null;
            }

            // Convert to GrapesJS-compatible schema
            return $this->convertToGrapesJSSchema($widget, $widgetDefinition);
            
        } catch (\Exception $e) {
            Log::error('Error loading widget schema', [
                'widget_id' => $widget->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get widget schema by widget ID
     *
     * @param int $widgetId
     * @return array|null
     */
    public function getWidgetSchemaById(int $widgetId): ?array
    {
        $widget = Widget::find($widgetId);
        if (!$widget) {
            return null;
        }
        
        return $this->getWidgetSchema($widget);
    }

    /**
     * Convert widget.json definition to GrapesJS-compatible schema
     *
     * @param Widget $widget
     * @param array $definition
     * @return array
     */
    protected function convertToGrapesJSSchema(Widget $widget, array $definition): array
    {
        return [
            'id' => $widget->id,
            'database_id' => $widget->id,
            'name' => $definition['name'] ?? $widget->name,
            'slug' => $definition['slug'] ?? $widget->slug,
            'description' => $definition['description'] ?? '',
            'icon' => $definition['icon'] ?? 'ri-widget-line',
            'category' => $this->determineCategory($definition),
            'fields' => $this->convertFields($definition['fields'] ?? []),
            'settings' => $this->convertSettings($definition['settings'] ?? []),
            'component_type' => $widget->slug . '-widget',
            'preview_endpoint' => "/admin/api/widgets/{$widget->id}/preview",
            'theme' => [
                'id' => $widget->theme->id,
                'slug' => $widget->theme->slug,
                'name' => $widget->theme->name
            ],
            'grapesjs' => [
                'block_id' => 'widget-' . $widget->slug,
                'component_type' => $widget->slug . '-widget',
                'draggable' => '[data-section-type]',
                'droppable' => false,
                'resizable' => false
            ]
        ];
    }

    /**
     * Convert widget fields to GrapesJS-compatible format
     *
     * @param array $fields
     * @return array
     */
    protected function convertFields(array $fields): array
    {
        $convertedFields = [];
        
        foreach ($fields as $field) {
            $convertedField = [
                'name' => $field['name'] ?? '',
                'slug' => $field['slug'] ?? '',
                'type' => $this->mapFieldType($field['field_type'] ?? 'text'),
                'original_type' => $field['field_type'] ?? 'text',
                'label' => $field['name'] ?? '',
                'description' => $field['description'] ?? '',
                'required' => $field['is_required'] ?? false,
                'position' => $field['position'] ?? 0,
            ];

            // Handle repeater fields
            if ($field['field_type'] === 'repeater') {
                $convertedField['repeater'] = true;
                $convertedField['settings'] = $field['settings'] ?? [];
                $convertedField['subfields'] = $this->convertSubfields($field['settings']['subfields'] ?? []);
                $convertedField['min_items'] = $field['settings']['min_items'] ?? 1;
                $convertedField['max_items'] = $field['settings']['max_items'] ?? 10;
            }

            // Handle select fields
            if ($field['field_type'] === 'select' && isset($field['options'])) {
                $convertedField['options'] = $field['options'];
            }

            $convertedFields[] = $convertedField;
        }
        
        return $convertedFields;
    }

    /**
     * Convert repeater subfields
     *
     * @param array $subfields
     * @return array
     */
    protected function convertSubfields(array $subfields): array
    {
        $convertedSubfields = [];
        
        foreach ($subfields as $subfield) {
            $convertedSubfields[] = [
                'name' => $subfield['name'] ?? '',
                'slug' => $subfield['slug'] ?? '',
                'type' => $this->mapFieldType($subfield['field_type'] ?? 'text'),
                'original_type' => $subfield['field_type'] ?? 'text',
                'label' => $subfield['name'] ?? '',
                'description' => $subfield['description'] ?? '',
                'required' => $subfield['is_required'] ?? false,
            ];
        }
        
        return $convertedSubfields;
    }

    /**
     * Convert widget settings to GrapesJS-compatible format
     *
     * @param array $settings
     * @return array
     */
    protected function convertSettings(array $settings): array
    {
        $convertedSettings = [];
        
        foreach ($settings as $key => $setting) {
            // Handle both array format and object format
            if (is_array($setting)) {
                $convertedSettings[] = [
                    'name' => $setting['name'] ?? $key,
                    'slug' => $key,
                    'type' => $this->mapFieldType($setting['field_type'] ?? 'text'),
                    'original_type' => $setting['field_type'] ?? 'text',
                    'label' => $setting['name'] ?? $key,
                    'description' => $setting['description'] ?? '',
                    'required' => $setting['is_required'] ?? false,
                    'default' => $setting['default'] ?? null,
                    'options' => $setting['options'] ?? null,
                ];
            }
        }
        
        return $convertedSettings;
    }

    /**
     * Map widget field types to GrapesJS-compatible types
     *
     * @param string $fieldType
     * @return string
     */
    protected function mapFieldType(string $fieldType): string
    {
        $typeMap = [
            'text' => 'text',
            'textarea' => 'textarea',
            'number' => 'number',
            'email' => 'email',
            'url' => 'url',
            'color' => 'color',
            'date' => 'date',
            'image' => 'image',
            'file' => 'file',
            'select' => 'select',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'repeater' => 'repeater',
            'wysiwyg' => 'textarea', // Map to textarea for now
            'json' => 'textarea',
        ];

        return $typeMap[$fieldType] ?? 'text';
    }

    /**
     * Determine widget category from definition
     *
     * @param array $definition
     * @return string
     */
    protected function determineCategory(array $definition): string
    {
        // Check if category is explicitly defined
        if (isset($definition['category'])) {
            return $definition['category'];
        }

        // Determine category based on widget name/slug
        $name = strtolower($definition['name'] ?? '');
        $slug = strtolower($definition['slug'] ?? '');
        
        if (str_contains($name, 'counter') || str_contains($slug, 'counter')) {
            return 'Statistics';
        }
        
        if (str_contains($name, 'image') || str_contains($slug, 'image') || str_contains($name, 'featured')) {
            return 'Media';
        }
        
        if (str_contains($name, 'team') || str_contains($slug, 'team')) {
            return 'People';
        }
        
        if (str_contains($name, 'slider') || str_contains($slug, 'slider')) {
            return 'Media';
        }
        
        if (str_contains($name, 'header') || str_contains($slug, 'header')) {
            return 'Content';
        }
        
        return 'General';
    }

    /**
     * Get the path to widget.json file
     *
     * @param Theme $theme
     * @param Widget $widget
     * @return string
     */
    protected function getWidgetJsonPath(Theme $theme, Widget $widget): string
    {
        return resource_path("themes/{$theme->slug}/widgets/{$widget->slug}/widget.json");
    }

    /**
     * Get sample data for a widget based on its schema
     *
     * @param Widget $widget
     * @return array
     */
    public function getWidgetSampleData(Widget $widget): array
    {
        $schema = $this->getWidgetSchema($widget);
        if (!$schema) {
            return [];
        }

        $sampleData = [];
        
        foreach ($schema['fields'] as $field) {
            $sampleData[$field['slug']] = $this->generateSampleValue($field);
        }
        
        return $sampleData;
    }

    /**
     * Generate sample value for a field
     *
     * @param array $field
     * @return mixed
     */
    public function generateSampleValue(array $field)
    {
        $fieldType = $field['field_type'] ?? $field['type'] ?? 'text';
        switch ($fieldType) {
            case 'text':
                return 'Sample ' . $field['label'];
            case 'number':
                return 42;
            case 'email':
                return 'sample@example.com';
            case 'url':
                return 'https://example.com';
            case 'color':
                return '#405189';
            case 'image':
                return asset('assets/admin/images/placeholder.jpg');
            case 'repeater':
                $sampleItems = [];
                $settings = $field['settings'] ?? [];
                $minItems = $settings['min_items'] ?? $field['min_items'] ?? 1;
                $subfields = $settings['subfields'] ?? $field['subfields'] ?? [];
                
                for ($i = 0; $i < $minItems; $i++) {
                    $sampleItem = [];
                    foreach ($subfields as $subfield) {
                        $sampleItem[$subfield['slug']] = $this->generateSampleValue($subfield);
                    }
                    $sampleItems[] = $sampleItem;
                }
                
                return $sampleItems;
            default:
                return 'Sample value';
        }
    }

    /**
     * Clear widget schema cache
     *
     * @param string|null $themeSlug
     * @return void
     */
    public function clearCache(?string $themeSlug = null): void
    {
        if ($themeSlug) {
            Cache::forget('widget_schemas_' . $themeSlug);
        } else {
            // Clear all widget schema caches
            $themes = Theme::all();
            foreach ($themes as $theme) {
                Cache::forget('widget_schemas_' . $theme->slug);
            }
        }
        
        Log::info('Widget schema cache cleared', ['theme' => $themeSlug ?? 'all']);
    }

    /**
     * Validate widget schema structure
     *
     * @param array $schema
     * @return array Array of validation errors (empty if valid)
     */
    public function validateSchema(array $schema): array
    {
        $errors = [];
        
        if (empty($schema['name'])) {
            $errors[] = 'Widget name is required';
        }
        
        if (empty($schema['slug'])) {
            $errors[] = 'Widget slug is required';
        }
        
        if (!is_array($schema['fields'])) {
            $errors[] = 'Widget fields must be an array';
        }
        
        foreach ($schema['fields'] as $index => $field) {
            if (empty($field['name'])) {
                $errors[] = "Field at index {$index} is missing name";
            }
            
            if (empty($field['slug'])) {
                $errors[] = "Field at index {$index} is missing slug";
            }
        }
        
        return $errors;
    }
} 