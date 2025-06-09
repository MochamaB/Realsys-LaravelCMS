<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\Widget;
use App\Models\WidgetFieldDefinition;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WidgetDiscoveryService
{
    /**
     * Discover and register widgets within a theme.
     */
    public function discoverAndRegisterWidgets(Theme $theme): array
    {
        $widgetsDir = resource_path("themes/{$theme->slug}/widgets");
        $result = ['new' => 0, 'updated' => 0];

        if (!File::isDirectory($widgetsDir)) {
            return $result;
        }

        // Get all widget directories
        $widgetDirs = File::directories($widgetsDir);
        
        foreach ($widgetDirs as $widgetDir) {
            // Each widget directory should contain a widget.json file
            $widgetFile = $widgetDir . '/widget.json';
            
            if (File::exists($widgetFile)) {
                $widgetResult = $this->processWidgetDirectory($widgetDir, $theme);
                
                if ($widgetResult === 'new') {
                    $result['new']++;
                } elseif ($widgetResult === 'updated') {
                    $result['updated']++;
                }
            }
        }

        return $result;
    }

    /**
     * Process a widget directory and register it in the database.
     * 
     * @param string $directory Widget directory path
     * @param Theme $theme
     * @return string 'new', 'updated', or null if skipped
     */
    protected function processWidgetDirectory(string $directory, Theme $theme): ?string
    {
        // Read and decode the widget definition
        $file = $directory . '/widget.json';
        $content = File::get($file);
        $definition = json_decode($content, true);
        
        if (!$definition || !isset($definition['name'], $definition['slug'])) {
            return null; // Invalid widget definition
        }
        
        // Get the widget slug from the directory name if not specified
        $dirName = basename($directory);
        $slug = $definition['slug'] ?? $dirName;
        
        // Check if widget already exists
        $widget = Widget::firstOrNew([
            'theme_id' => $theme->id,
            'slug' => $slug,
        ]);
        
        $isNew = !$widget->exists;
        
        // Determine view path - using self-contained structure
        // By default, it will look for view.blade.php in the widget directory
        $viewPath = $definition['view_path'] ?? "widgets.{$dirName}.view";
        
        // Update widget properties
        $widget->fill([
            'name' => $definition['name'],
            'description' => $definition['description'] ?? null,
            'icon' => $definition['icon'] ?? null,
            'view_path' => $viewPath,
        ]);
        
        $widget->save();
        
        // Process widget field definitions
        if (isset($definition['fields']) && is_array($definition['fields'])) {
            $this->processWidgetFields($widget, $definition['fields']);
        }
        
        return $isNew ? 'new' : 'updated';
    }
    
    /**
     * Process and save widget field definitions.
     */
    protected function processWidgetFields(Widget $widget, array $fields): void
    {
        $existingFields = $widget->fieldDefinitions->keyBy('slug');
        $position = 0;
        
        foreach ($fields as $field) {
            if (!isset($field['name'], $field['slug'], $field['field_type'])) {
                continue; // Skip invalid field
            }
            
            $fieldDefinition = $existingFields->get($field['slug']) ?? 
                new WidgetFieldDefinition(['widget_id' => $widget->id, 'slug' => $field['slug']]);
            
            $fieldDefinition->fill([
                'name' => $field['name'],
                'field_type' => $field['field_type'],
                'validation_rules' => $field['validation_rules'] ?? null,
                'settings' => $field['settings'] ?? null,
                'is_required' => $field['is_required'] ?? false,
                'position' => $position++,
                'description' => $field['description'] ?? null,
            ]);
            
            $fieldDefinition->save();
        }
    }
}