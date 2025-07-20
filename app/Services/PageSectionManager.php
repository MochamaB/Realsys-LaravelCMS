<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\Template;
use App\Models\TemplateSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PageSectionManager
{
    /**
     * Synchronize page sections with template sections
     * 
     * @param Page $page The page to synchronize
     * @return array Array of created and deleted section counts
     */
    public function syncPageSections(Page $page): array
    {
        // Get the template and its sections
        $template = $page->template;
        
        if (!$template) {
            return ['created' => 0, 'deleted' => 0];
        }
        
        $templateSections = $template->sections()->orderBy('position')->get();
        $existingSections = $page->sections;
        
        $created = 0;
        $deleted = 0;
        
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Create sections that exist in the template but not in the page
            foreach ($templateSections as $templateSection) {
                $pageSection = $existingSections->first(function ($section) use ($templateSection) {
                    return $section->template_section_id === $templateSection->id;
                });
                
                if (!$pageSection) {
                    // Create a new page section
                    PageSection::create([
                        'page_id' => $page->id,
                        'template_section_id' => $templateSection->id,
                        'name' => $templateSection->name,
                        'identifier' => $templateSection->slug,
                        'description' => $templateSection->description,
                        'position' => $templateSection->position,
                        
                    ]);
                    
                    $created++;
                }
            }
            
            // Delete sections that exist in the page but not in the template
            foreach ($existingSections as $existingSection) {
                $templateSection = $templateSections->first(function ($section) use ($existingSection) {
                    return $section->id === $existingSection->template_section_id;
                });
                
                if (!$templateSection) {
                    // Delete the page section
                    $existingSection->delete();
                    $deleted++;
                }
            }
            
            // Commit the transaction
            DB::commit();
            
            return [
                'created' => $created,
                'deleted' => $deleted
            ];
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            
            Log::error('Failed to synchronize page sections: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'template_id' => $template->id,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Handle template switching for a page
     * 
     * @param Page $page The page that switched templates
     * @param int $oldTemplateId The previous template ID
     * @return array Result of the synchronization
     */
    public function handleTemplateSwitching(Page $page, int $oldTemplateId): array
    {
        $oldTemplate = Template::find($oldTemplateId);
        $newTemplate = $page->template;
        
        if (!$oldTemplate || !$newTemplate) {
            return ['created' => 0, 'deleted' => 0, 'preserved' => 0];
        }
        
        // Map of old template section slugs to new template section IDs for content preservation
        $sectionMap = [];
        
        // Get sections from both templates
        $oldSections = $oldTemplate->sections()->orderBy('position')->get();
        $newSections = $newTemplate->sections()->orderBy('position')->get();
        
        // Map sections with matching slugs (potential content preservation)
        foreach ($oldSections as $oldSection) {
            foreach ($newSections as $newSection) {
                if ($oldSection->slug === $newSection->slug && $oldSection->type === $newSection->type) {
                    $sectionMap[$oldSection->id] = $newSection->id;
                    break;
                }
            }
        }
        
        // Now perform the synchronization with content preservation
        $result = $this->syncPageSectionsWithContentPreservation($page, $sectionMap);
        
        return $result;
    }
    
    /**
     * Synchronize page sections with content preservation
     * 
     * @param Page $page The page to synchronize
     * @param array $sectionMap Map of old template section IDs to new template section IDs
     * @return array Result of the synchronization
     */
    protected function syncPageSectionsWithContentPreservation(Page $page, array $sectionMap): array
    {
        $template = $page->template;
        
        if (!$template) {
            return ['created' => 0, 'deleted' => 0, 'preserved' => 0];
        }
        
        $templateSections = $template->sections()->orderBy('position')->get();
        $existingSections = $page->sections;
        
        $created = 0;
        $deleted = 0;
        $preserved = 0;
        
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Track which sections we've processed to avoid duplicates
            $processedSections = [];
            
            // Process existing sections for preservation
            foreach ($existingSections as $existingSection) {
                // Check if this section maps to a new template section
                $newTemplateSectionId = $sectionMap[$existingSection->template_section_id] ?? null;
                
                if ($newTemplateSectionId) {
                    // Preserve content by updating the template_section_id
                    $templateSection = $templateSections->firstWhere('id', $newTemplateSectionId);
                    
                    if ($templateSection) {
                        $existingSection->update([
                            'template_section_id' => $newTemplateSectionId,
                            'name' => $templateSection->name,
                            'identifier' => $templateSection->slug,
                            'description' => $templateSection->description,
                            'order_index' => $templateSection->order_index
                        ]);
                        
                        $preserved++;
                        $processedSections[] = $newTemplateSectionId;
                    }
                } else {
                    // Delete the section as it doesn't map to the new template
                    $existingSection->delete();
                    $deleted++;
                }
            }
            
            // Create new sections for the template sections that weren't mapped
            foreach ($templateSections as $templateSection) {
                if (!in_array($templateSection->id, $processedSections)) {
                    PageSection::create([
                        'page_id' => $page->id,
                        'template_section_id' => $templateSection->id,
                        'name' => $templateSection->name,
                        'identifier' => $templateSection->slug,
                        'description' => $templateSection->description,
                        'position' => $templateSection->position,
                        
                    ]);
                    
                    $created++;
                }
            }
            
            // Commit the transaction
            DB::commit();
            
            return [
                'created' => $created,
                'deleted' => $deleted,
                'preserved' => $preserved
            ];
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            
            Log::error('Failed to synchronize page sections with content preservation: ' . $e->getMessage(), [
                'page_id' => $page->id,
                'template_id' => $template->id,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Check if a page has a section with the given slug
     * 
     * @param Page $page The page to check
     * @param string $sectionSlug The section slug to look for
     * @return bool True if the page has the section, false otherwise
     */
    public function hasSection(Page $page, string $sectionSlug): bool
    {
        return $page->sections()
            ->whereHas('templateSection', function ($query) use ($sectionSlug) {
                $query->where('slug', $sectionSlug);
            })
            ->exists();
    }
    
    /**
     * Get a page section by its template section slug
     * 
     * @param Page $page The page to get the section from
     * @param string $sectionSlug The section slug to look for
     * @return PageSection|null The page section or null if not found
     */
    public function getSection(Page $page, string $sectionSlug): ?PageSection
    {
        return $page->sections()
            ->whereHas('templateSection', function ($query) use ($sectionSlug) {
                $query->where('slug', $sectionSlug);
            })
            ->first();
    }
}
