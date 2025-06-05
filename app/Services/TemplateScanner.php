<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\Template;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemplateScanner
{
    /**
     * Scan a theme directory for template files and register them
     *
     * @param Theme $theme Theme to scan
     * @return array Array of created/found templates
     */
    public function scanAndRegisterTemplates(Theme $theme): array
    {
        $results = [
            'created' => 0,
            'existing' => 0,
            'templates' => []
        ];
        
        $themePath = resource_path("themes/{$theme->slug}");
        $templatesPath = "{$themePath}/templates";
        
        if (!File::isDirectory($templatesPath)) {
            Log::warning("Templates directory not found for theme: {$theme->name}");
            return $results;
        }
        
        // Get all blade files in templates directory
        $templateFiles = File::files($templatesPath);
        
        foreach ($templateFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            
            // Extract the filename without extension
            $filename = $file->getFilename();
            if (Str::endsWith($filename, '.blade.php')) {
                $slug = Str::of($filename)->before('.blade.php')->toString();
            } else {
                $slug = Str::of($filename)->before('.php')->toString();
            }
            
            // Skip non-standard files
            if (Str::startsWith($slug, '_') || $slug === 'app') {
                continue;
            }
            
            // Format template name
            $name = Str::of($slug)
                ->replace('-', ' ')
                ->replace('_', ' ')
                ->title()
                ->toString();
            
            // Generate relative path
            $relativePath = 'templates/' . $filename;
            
            // Check if template already exists
            $existingTemplate = Template::where('theme_id', $theme->id)
                ->where('slug', $slug)
                ->first();
            
            if (!$existingTemplate) {
                // Create new template
                $template = Template::create([
                    'theme_id' => $theme->id,
                    'name' => $name,
                    'slug' => $slug,
                    'file_path' => $relativePath,
                    'description' => "Auto-generated template for {$name}",
                    'is_active' => true
                ]);
                
                $results['created']++;
                $results['templates'][] = $template;
                
                Log::info("Created template {$name} for theme {$theme->name}");
            } else {
                $results['existing']++;
                $results['templates'][] = $existingTemplate;
            }
        }
        
        return $results;
    }
}
