<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\Template;
use App\Models\Page;
use App\Models\TemplateSection;
use App\Models\Widget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ThemeMigrationService
{
    /**
     * Migrate content from old theme to new theme when activating
     */
    public function migrateToNewTheme(Theme $newTheme, Theme $oldTheme = null)
    {
        DB::beginTransaction();
        
        try {
            Log::info("Starting theme migration to: {$newTheme->name}");
            
            // Step 1: Create equivalent templates in new theme
            $templateMapping = $this->migrateTemplates($newTheme, $oldTheme);
            
            // Step 2: Create equivalent template sections
            $this->migrateTemplateSections($templateMapping);
            
            // Step 3: Update page template associations
            $this->updatePageTemplateAssociations($templateMapping);
            
            // Step 4: Handle widgets (migrate or create defaults)
            $this->migrateWidgets($newTheme, $oldTheme);
            
            DB::commit();
            Log::info("Theme migration completed successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Theme migration failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Migrate templates from old theme to new theme
     */
    private function migrateTemplates(Theme $newTheme, Theme $oldTheme = null): array
    {
        $templateMapping = [];
        
        // Get all templates currently used by pages
        $usedTemplateIds = Page::distinct('template_id')
            ->whereNotNull('template_id')
            ->pluck('template_id');
        
        if ($usedTemplateIds->isEmpty()) {
            Log::info("No templates in use by pages");
            return $this->createDefaultTemplates($newTheme);
        }
        
        $usedTemplates = Template::whereIn('id', $usedTemplateIds)->get();
        
        foreach ($usedTemplates as $oldTemplate) {
            Log::info("Processing template: {$oldTemplate->name} (slug: {$oldTemplate->slug})");
            
            if (empty($oldTemplate->slug)) {
                Log::warning("Skipping template {$oldTemplate->id} - no slug");
                continue;
            }
            
            // Check if equivalent template exists in new theme
            $newTemplate = Template::where('theme_id', $newTheme->id)
                ->where('slug', $oldTemplate->slug)
                ->first();
            
            if (!$newTemplate) {
                $newTemplate = $this->createEquivalentTemplate($newTheme, $oldTemplate);
            }
            
            $templateMapping[$oldTemplate->id] = $newTemplate->id;
            Log::info("Mapped template {$oldTemplate->id} -> {$newTemplate->id}");
        }
       
        return $templateMapping;
    }
    
    /**
     * Create equivalent template in new theme
     */
    private function createEquivalentTemplate(Theme $newTheme, Template $oldTemplate): Template
    {
        // Determine the template file path
        $filePath = $this->determineTemplateFilePath($newTheme, $oldTemplate);
        
        $newTemplate = Template::create([
            'theme_id' => $newTheme->id,
            'name' => $oldTemplate->name,
            'slug' => $oldTemplate->slug,
            'file_path' => $filePath,
            'description' => $oldTemplate->description ?: 'Migrated from previous theme',
            'is_active' => true,
        
        ]);
        
        Log::info("Created template: {$newTemplate->name} in theme {$newTheme->name}");
        return $newTemplate;
    }
    
    /**
     * Determine the best template file path for the new theme
     */
    private function determineTemplateFilePath(Theme $newTheme, Template $oldTemplate): string
    {
        $themePath = resource_path("themes/{$newTheme->slug}");
        
        // Check if new theme has a template file with the same slug
        $specificTemplate = "templates/{$oldTemplate->slug}.blade.php";
        if (file_exists("{$themePath}/{$specificTemplate}")) {
            return $specificTemplate;
        }
        
        // Check if new theme has the same file path as old template
        if (file_exists("{$themePath}/{$oldTemplate->file_path}")) {
            return $oldTemplate->file_path;
        }
        
        // Fall back to default template
        $defaultTemplate = "templates/default.blade.php";
        if (file_exists("{$themePath}/{$defaultTemplate}")) {
            return $defaultTemplate;
        }
        
        // Final fallback - assume it exists
        return "templates/page.blade.php";
    }
    
    /**
     * Create default templates for common page types
     */
    private function createDefaultTemplates(Theme $newTheme): array
    {
        $defaultTemplates = ['home', 'page', 'about', 'contact'];
        $templateMapping = [];
        
        foreach ($defaultTemplates as $slug) {
            $template = Template::firstOrCreate(
                [
                    'theme_id' => $newTheme->id,
                    'slug' => $slug
                ],
                [
                    'name' => ucfirst($slug) . ' Template',
                    'file_path' => "templates/{$slug}.blade.php",
                    'description' => "Default {$slug} template",
                    'is_active' => true,
                ]
            );
            
            Log::info("Created default template: {$template->name}");
        }
        
        return $templateMapping;
    }
    
    /**
     * Migrate template sections
     */
    private function migrateTemplateSections(array $templateMapping): void
    {
        foreach ($templateMapping as $oldTemplateId => $newTemplateId) {
            $oldSections = TemplateSection::where('template_id', $oldTemplateId)->get();
            
            foreach ($oldSections as $oldSection) {
                // Check if section already exists
                $existingSection = TemplateSection::where('template_id', $newTemplateId)
                    ->where('slug', $oldSection->slug)
                    ->first();
                
                if (!$existingSection) {
                    TemplateSection::create([
                        'template_id' => $newTemplateId,
                        'name' => $oldSection->name,
                        'slug' => $oldSection->slug,
                        'position' => $oldSection->position ?? 0,
                        'section_type' => $oldSection->section_type ?? 'full-width',
                        'column_layout' => $oldSection->column_layout,
                        'is_repeatable' => $oldSection->is_repeatable ?? false,
                        'max_widgets' => $oldSection->max_widgets,
                        'description' => $oldSection->description
                    ]);
                    
                    Log::info("Migrated section: {$oldSection->name} to new template");
                }
            }
        }
    }
    
    /**
     * Update page template associations
     */
    private function updatePageTemplateAssociations(array $templateMapping): void
    {
        foreach ($templateMapping as $oldTemplateId => $newTemplateId) {
            $updatedCount = Page::where('template_id', $oldTemplateId)
                ->update(['template_id' => $newTemplateId]);
            
            Log::info("Updated {$updatedCount} pages from template {$oldTemplateId} to {$newTemplateId}");
        }
        
        // Handle pages with invalid template references
        $this->fixOrphanedPages($templateMapping);
    }
    
    /**
     * Fix pages that have invalid template references
     */
    private function fixOrphanedPages(array $templateMapping): void
    {
        // Find pages with template_id that don't exist in current theme
        $orphanedPages = Page::whereNotNull('template_id')
            ->whereNotIn('template_id', array_values($templateMapping))
            ->get();
        
        if ($orphanedPages->count() > 0) {
            Log::warning("Found {$orphanedPages->count()} orphaned pages");
            
            // Get a default template to assign them to
            $defaultTemplate = Template::where('slug', 'default')
                ->orWhere('slug', 'page')
                ->first();
            
            if (!$defaultTemplate) {
                // Create a basic default template
                $activeTheme = Theme::where('is_active', true)->first();
                $defaultTemplate = Template::create([
                    'theme_id' => $activeTheme->id,
                    'name' => 'Default Template',
                    'slug' => 'default',
                    'file_path' => 'templates/default.blade.php',
                    'description' => 'Default template for orphaned pages',
                    'is_active' => true
                ]);
            }
            
            foreach ($orphanedPages as $page) {
                $page->update(['template_id' => $defaultTemplate->id]);
                Log::info("Assigned default template to orphaned page: {$page->title}");
            }
        }
        
        // Handle pages with null template_id
        $pagesWithoutTemplates = Page::whereNull('template_id')->get();
        
        foreach ($pagesWithoutTemplates as $page) {
            $templateSlug = $page->slug ?: 'default';
            
            $template = Template::where('slug', $templateSlug)
                ->whereHas('theme', function($q) {
                    $q->where('is_active', true);
                })
                ->first();
            
            if (!$template) {
                $template = Template::where('slug', 'default')
                    ->whereHas('theme', function($q) {
                        $q->where('is_active', true);
                    })
                    ->first();
            }
            
            if ($template) {
                $page->update(['template_id' => $template->id]);
                Log::info("Assigned template to page without template: {$page->title}");
            }
        }
    }
    
    /**
     * We skip widget migration as they are connected through page_widget_sections
     * and should be handled by a dedicated widget system implementation
     */
    private function migrateWidgets(Theme $newTheme, Theme $oldTheme = null): void
    {
        // In a future implementation, we can migrate widgets and page_widget_sections
        // but for now we'll just log that we're skipping this step
        Log::info("Widget migration skipped - should be handled by dedicated widget system");
    }
}