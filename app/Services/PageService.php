<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PageService
{
    /**
     * @var PageSectionManager
     */
    protected $sectionManager;
    
    /**
     * Create a new page service instance
     * 
     * @param PageSectionManager $sectionManager
     * @return void
     */
    public function __construct(PageSectionManager $sectionManager)
    {
        $this->sectionManager = $sectionManager;
    }
    
    /**
     * Create a new page
     * 
     * @param array $data The page data
     * @return Page The created page
     * @throws ValidationException If the data is invalid
     */
    public function createPage(array $data): Page
    {
        // Validate the template
        $this->validateTemplate($data['template_id'] ?? null);
        
        // Generate a slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        
        // Ensure slug uniqueness
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);
        
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Create the page
            $page = Page::create($data);
            
            // Sync the page sections with the template
            $this->sectionManager->syncPageSections($page);
            
            // Commit the transaction
            DB::commit();
            
            return $page;
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            
            Log::error('Failed to create page: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Update a page
     * 
     * @param Page $page The page to update
     * @param array $data The page data
     * @return Page The updated page
     * @throws ValidationException If the data is invalid
     */
    public function updatePage(Page $page, array $data): Page
    {
        // Check if the template has changed
        $oldTemplateId = $page->template_id;
        $newTemplateId = $data['template_id'] ?? $oldTemplateId;
        
        $templateChanged = $oldTemplateId != $newTemplateId;
        
        // Validate the template
        if ($templateChanged) {
            $this->validateTemplate($newTemplateId);
        }
        
        // Check slug
        if (!empty($data['slug']) && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $page->id);
        }
        
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Update the page
            $page->update($data);
            
            // Handle template switching if needed
            if ($templateChanged) {
                $page->handleTemplateSwitching($oldTemplateId);
            }
            
            // Commit the transaction
            DB::commit();
            
            return $page;
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            
            Log::error('Failed to update page: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'data' => $data,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Validate a template ID
     * 
     * @param int|null $templateId The template ID to validate
     * @return void
     * @throws ValidationException If the template ID is invalid
     */
    protected function validateTemplate(?int $templateId): void
    {
        if (!$templateId) {
            throw ValidationException::withMessages([
                'template_id' => 'A template must be selected for the page.'
            ]);
        }
        
        $template = Template::find($templateId);
        
        if (!$template) {
            throw ValidationException::withMessages([
                'template_id' => 'The selected template does not exist.'
            ]);
        }
        
        if (!$template->is_active) {
            throw ValidationException::withMessages([
                'template_id' => 'The selected template is inactive.'
            ]);
        }
        
        // Get the active theme
        $activeTheme = app(\App\Services\ThemeManager::class)->getActiveTheme();
        
        if (!$activeTheme) {
            throw ValidationException::withMessages([
                'template_id' => 'No active theme is set. Please activate a theme first.'
            ]);
        }
        
        // Verify the template belongs to the active theme
        if ($template->theme_id !== $activeTheme->id) {
            throw ValidationException::withMessages([
                'template_id' => 'The selected template does not belong to the active theme.'
            ]);
        }
    }
    
    /**
     * Ensure a slug is unique
     * 
     * @param string $slug The slug to check
     * @param int|null $excludeId ID to exclude from the check
     * @return string The unique slug
     */
    protected function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $count = 0;
        
        while (true) {
            $query = Page::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            $exists = $query->exists();
            
            if (!$exists) {
                break;
            }
            
            $count++;
            $slug = $originalSlug . '-' . $count;
        }
        
        return $slug;
    }
    
    /**
     * Get templates from the active theme only
     * 
     * @return \Illuminate\Database\Eloquent\Collection Collection of templates
     * @throws \Exception If no active theme is found
     */
    public function getActiveThemeTemplates()
    {
        $themeManager = app(\App\Services\ThemeManager::class);
        $activeTheme = $themeManager->getActiveTheme();
        
        if (!$activeTheme) {
            throw new \Exception('No active theme found. Please activate a theme first.');
        }
        
        return Template::where('theme_id', $activeTheme->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Delete a page
     * 
     * @param Page $page The page to delete
     * @return bool Whether the page was deleted
     */
    public function deletePage(Page $page): bool
    {
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Delete the page
            $result = $page->delete();
            
            // Commit the transaction
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            
            Log::error('Failed to delete page: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
}
