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
     * 
     * @param Theme $theme The theme to scan for widgets
     * @return array Detailed results of the discovery process
     */
    public function discoverAndRegisterWidgets(Theme $theme): array
    {
        $widgetsDir = resource_path("themes/{$theme->slug}/widgets");
        $result = [
            'new' => 0, 
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        if (!File::isDirectory($widgetsDir)) {
            Log::warning("Widget directory not found for theme '{$theme->name}': {$widgetsDir}");
            $result['details'][] = ["status" => "error", "message" => "Widget directory not found for theme: {$theme->slug}"];
            $result['errors']++;
            return $result;
        }

        // Get all widget directories
        $widgetDirs = File::directories($widgetsDir);
        Log::info("Found " . count($widgetDirs) . " widget directories in theme '{$theme->name}'");
        
        foreach ($widgetDirs as $widgetDir) {
            $dirName = basename($widgetDir);
            $widgetFile = $widgetDir . '/widget.json';
            
            // Each widget directory should contain a widget.json file
            if (File::exists($widgetFile)) {
                try {
                    $widgetResult = $this->processWidgetDirectory($widgetDir, $theme);
                    
                    if ($widgetResult === 'new') {
                        $result['new']++;
                        $result['details'][] = ["status" => "new", "widget" => $dirName];
                        Log::info("Registered new widget: {$dirName} in theme '{$theme->name}'");
                    } elseif ($widgetResult === 'updated') {
                        $result['updated']++;
                        $result['details'][] = ["status" => "updated", "widget" => $dirName];
                        Log::info("Updated existing widget: {$dirName} in theme '{$theme->name}'");
                    } elseif ($widgetResult === null) {
                        $result['skipped']++;
                        $result['details'][] = ["status" => "skipped", "widget" => $dirName];
                        Log::warning("Skipped widget: {$dirName} in theme '{$theme->name}' due to invalid configuration");
                    }
                } catch (\Exception $e) {
                    $result['errors']++;
                    $result['details'][] = ["status" => "error", "widget" => $dirName, "message" => $e->getMessage()];
                    Log::error("Error processing widget: {$dirName} in theme '{$theme->name}'. Error: {$e->getMessage()}");
                }
            } else {
                $result['skipped']++;
                $result['details'][] = ["status" => "skipped", "widget" => $dirName, "reason" => "No widget.json found"];
                Log::warning("No widget.json found for: {$dirName} in theme '{$theme->name}'");
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
        
        // Save or update widget field definitions
        if (isset($definition['fields']) && is_array($definition['fields'])) {
            $this->processWidgetFields($widget, $definition['fields']);
        }
        
        // Publish widget assets to public directory
        $this->publishWidgetAssets($theme, $slug);
        
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
                
            // Get base settings
            $settings = $field['settings'] ?? [];
            
            // Add default value if specified in widget.json
            if (isset($field['default'])) {
                $settings['default_value'] = $field['default'];
            }
            
            // Add placeholder if specified in widget.json
            if (isset($field['placeholder'])) {
                $settings['placeholder'] = $field['placeholder'];
            }
            
            // Special handling for repeater fields
            if ($field['field_type'] === 'repeater') {
                // Ensure repeater has expected structure
                $settings = $this->validateAndProcessRepeaterSettings($field);
                
                // Preserve default and placeholder for repeater fields
                if (isset($field['default'])) {
                    $settings['default_value'] = $field['default'];
                }
                if (isset($field['placeholder'])) {
                    $settings['placeholder'] = $field['placeholder'];
                }
            }
            
            $fieldDefinition->fill([
                'name' => $field['name'],
                'field_type' => $field['field_type'],
                'validation_rules' => $field['validation_rules'] ?? null,
                'settings' => $settings,
                'is_required' => $field['is_required'] ?? false,
                'position' => $position++,
                'description' => $field['description'] ?? null,
            ]);
            
            $fieldDefinition->save();
        }
    }
    
    /**
     * Validate and process repeater field settings
     * 
     * @param array $field The field configuration
     * @return array The processed settings
     */
    protected function validateAndProcessRepeaterSettings(array $field): array
    {
        $settings = $field['settings'] ?? [];
        
        // Ensure min_items and max_items are set with defaults
        $settings['min_items'] = $settings['min_items'] ?? 0;
        $settings['max_items'] = $settings['max_items'] ?? 10;
        
        // Validate that subfields exist
        if (!isset($settings['subfields']) || !is_array($settings['subfields'])) {
            $settings['subfields'] = []; // Default empty
            Log::warning("Repeater field '{$field['name']}' has no subfields defined");
            return $settings;
        }
        
        // Process each subfield to ensure it has required properties
        foreach ($settings['subfields'] as $index => $subfield) {
            // Skip invalid subfields
            if (!isset($subfield['name'], $subfield['slug'], $subfield['field_type'])) {
                Log::warning("Skipping invalid subfield at index {$index} in repeater '{$field['name']}'");
                continue;
            }
            
            // Set default values for subfields
            $settings['subfields'][$index]['is_required'] = $subfield['is_required'] ?? false;
            $settings['subfields'][$index]['description'] = $subfield['description'] ?? null;
            
            // Additional validation for nested repeaters to prevent infinite recursion
            if ($subfield['field_type'] === 'repeater') {
                Log::warning("Nested repeater field '{$subfield['name']}' found in '{$field['name']}'. This is not supported and may cause issues.");
                // Set a flag so we know this is a nested repeater
                $settings['subfields'][$index]['is_nested_repeater'] = true;
            }
        }
        
        // Handle content_type_compatibility if present
        if (isset($field['content_type_compatibility'])) {
            $settings['content_type_compatibility'] = $field['content_type_compatibility'];
        }
        
        return $settings;
    }
    
    /**
     * Publish widget assets to public directory for frontend access.
     *
     * @param Theme $theme The theme containing the widget
     * @param string $widgetSlug The widget slug
     * @return void
     */
    protected function publishWidgetAssets(Theme $theme, string $widgetSlug): void
    {
        $sourcePath = resource_path("themes/{$theme->slug}/widgets/{$widgetSlug}");
        $targetPath = public_path("themes/{$theme->slug}/widgets/{$widgetSlug}");
        
        // Create target directory if it doesn't exist
        if (!File::isDirectory($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }
        
        // Publish preview image with standard naming
        $previewImages = [
            "{$sourcePath}/widget-{$widgetSlug}.png",
            "{$sourcePath}/widget-preview.png",
            "{$sourcePath}/preview.png",
            // Also look in the assets directory
            "{$sourcePath}/assets/widget-{$widgetSlug}.png",
            "{$sourcePath}/assets/widget-preview.png", 
            "{$sourcePath}/assets/preview.png",
            "{$sourcePath}/assets/images/widget-{$widgetSlug}.png",
            "{$sourcePath}/assets/images/widget-preview.png",
            "{$sourcePath}/assets/images/preview.png"
        ];
        
        $previewFound = false;
        foreach ($previewImages as $previewImage) {
            if (File::exists($previewImage)) {
                File::copy($previewImage, "{$targetPath}/preview.png");
                Log::info("Published preview image for widget '{$widgetSlug}' from {$previewImage}");
                $previewFound = true;
                break;
            }
        }
        
        if (!$previewFound) {
            Log::warning("No preview image found for widget '{$widgetSlug}'");
        }
        
        // Copy assets directory if it exists
        $assetsPath = "{$sourcePath}/assets";
        if (File::isDirectory($assetsPath)) {
            $targetAssetsPath = "{$targetPath}/assets";
            
            // Create assets directory if it doesn't exist
            if (!File::isDirectory($targetAssetsPath)) {
                File::makeDirectory($targetAssetsPath, 0755, true);
            }
            
            // Copy all files from assets directory
            foreach (File::allFiles($assetsPath) as $file) {
                $targetFile = "{$targetAssetsPath}/" . $file->getRelativePathname();
                $targetDir = dirname($targetFile);
                
                if (!File::isDirectory($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                
                File::copy($file->getPathname(), $targetFile);
            }
        }
        
        // If we have CSS files in the assets directory, copy them to the theme's public CSS directory
        $cssFiles = glob("{$sourcePath}/assets/*.css");
        if (!empty($cssFiles)) {
            $targetCssDir = public_path("themes/{$theme->slug}/css/widgets");
            
            if (!File::isDirectory($targetCssDir)) {
                File::makeDirectory($targetCssDir, 0755, true);
            }
            
            foreach ($cssFiles as $cssFile) {
                $filename = basename($cssFile);
                File::copy($cssFile, "{$targetCssDir}/{$widgetSlug}-{$filename}");
            }
        }
        
        // If we have JS files in the assets directory, copy them to the theme's public JS directory
        $jsFiles = glob("{$sourcePath}/assets/*.js");
        if (!empty($jsFiles)) {
            $targetJsDir = public_path("themes/{$theme->slug}/js/widgets");
            
            if (!File::isDirectory($targetJsDir)) {
                File::makeDirectory($targetJsDir, 0755, true);
            }
            
            foreach ($jsFiles as $jsFile) {
                $filename = basename($jsFile);
                File::copy($jsFile, "{$targetJsDir}/{$widgetSlug}-{$filename}");
            }
        }
    }
}