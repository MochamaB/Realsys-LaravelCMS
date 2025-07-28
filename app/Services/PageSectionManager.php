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
                    try {
                        // Map template section data to page section data
                        $pageSectionData = $this->mapTemplateSectionToPageSection($templateSection, $page);
                        
                        // Validate that all required fields are present
                        $requiredFields = [
                            'page_id', 'template_section_id', 'position', 
                            'grid_x', 'grid_y', 'grid_w', 'grid_h', 'grid_id',
                            'grid_config', 'allows_widgets', 'widget_types',
                            'css_classes', 'background_color', 'padding', 'margin',
                            'locked_position', 'resize_handles'
                        ];
                        
                        $missingFields = array_diff($requiredFields, array_keys($pageSectionData));
                        if (!empty($missingFields)) {
                            Log::error('Missing required fields for page section creation:', [
                                'missing_fields' => $missingFields,
                                'template_section_id' => $templateSection->id,
                                'page_id' => $page->id
                            ]);
                            throw new \Exception('Missing required fields: ' . implode(', ', $missingFields));
                        }
                        
                        // Create a new page section
                        $newPageSection = PageSection::create($pageSectionData);
                        
                        Log::info('Page section created successfully:', [
                            'page_section_id' => $newPageSection->id,
                            'template_section_id' => $templateSection->id,
                            'page_id' => $page->id
                        ]);
                        
                        $created++;
                    } catch (\Exception $e) {
                        Log::error('Failed to create page section:', [
                            'template_section_id' => $templateSection->id,
                            'page_id' => $page->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
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

    /**
     * Map template section data to page section data
     * 
     * @param TemplateSection $templateSection
     * @param Page $page
     * @return array
     */
    protected function mapTemplateSectionToPageSection(TemplateSection $templateSection, Page $page): array
    {
        // Map basic positioning from template to GridStack positioning
        $gridPosition = $this->mapPositionToGridStack($templateSection);
        
        // Map section type to GridStack configuration
        $gridConfig = $this->mapSectionTypeToGridConfig($templateSection);
        
        // Map column layout to widget constraints
        $widgetTypes = $this->mapSectionTypeToWidgetTypes($templateSection);
        
        // Map styling based on section type
        $styling = $this->mapSectionTypeToStyling($templateSection);
        
        $pageSectionData = [
            'page_id' => $page->id,
            'template_section_id' => $templateSection->id,
            'position' => $templateSection->position,
            
            // GridStack positioning
            'grid_x' => $gridPosition['x'],
            'grid_y' => $gridPosition['y'],
            'grid_w' => $gridPosition['w'],
            'grid_h' => $gridPosition['h'],
            'grid_id' => PageSection::generateUniqueGridId($templateSection->id),
            'grid_config' => $gridConfig,
            
            // Widget configuration
            'allows_widgets' => true,
            'widget_types' => $widgetTypes,
            
            // Styling
            'css_classes' => $styling['css_classes'],
            'background_color' => $styling['background_color'],
            'padding' => $styling['padding'],
            'margin' => $styling['margin'],
            'locked_position' => false,
            'resize_handles' => $styling['resize_handles'],
            
            // Column overrides (if applicable)
            'column_span_override' => $styling['column_span_override'] ?? null,
            'column_offset_override' => $styling['column_offset_override'] ?? null,
        ];

        // Log the data being created for debugging
        Log::info('Creating page section with data:', [
            'template_section_id' => $templateSection->id,
            'page_id' => $page->id,
            'grid_id' => $pageSectionData['grid_id'],
            'data_keys' => array_keys($pageSectionData)
        ]);

        return $pageSectionData;
    }

    /**
     * Map template section positioning to GridStack positioning
     * 
     * @param TemplateSection $templateSection
     * @return array
     */
    protected function mapPositionToGridStack(TemplateSection $templateSection): array
    {
        // Convert template positioning to GridStack positioning
        return [
            'x' => $templateSection->x ?? 0,
            'y' => $templateSection->y ?? 0,
            'w' => $templateSection->w ?? 12,
            'h' => $templateSection->h ?? 4,
        ];
    }

    /**
     * Map section type to GridStack configuration
     * 
     * @param TemplateSection $templateSection
     * @return array
     */
    protected function mapSectionTypeToGridConfig(TemplateSection $templateSection): array
    {
        $baseConfig = [
            'column' => 12,
            'cellHeight' => 80,
            'verticalMargin' => 10,
            'horizontalMargin' => 10,
            'acceptWidgets' => true,
            'animate' => true,
            'float' => false,
            'resizable' => [
                'handles' => ['se', 'sw']
            ]
        ];

        switch ($templateSection->section_type) {
            case TemplateSection::TYPE_FULL_WIDTH:
                $baseConfig['float'] = false;
                $baseConfig['resizable']['handles'] = ['se', 'sw'];
                break;
                
            case TemplateSection::TYPE_MULTI_COLUMN:
                $baseConfig['float'] = true;
                $baseConfig['resizable']['handles'] = ['se', 'sw', 'ne', 'nw'];
                break;
                
            case TemplateSection::TYPE_SIDEBAR_LEFT:
            case TemplateSection::TYPE_SIDEBAR_RIGHT:
                $baseConfig['float'] = false;
                $baseConfig['resizable']['handles'] = ['se', 'sw'];
                break;
        }

        return $baseConfig;
    }

    /**
     * Map section type to allowed widget types
     * 
     * @param TemplateSection $templateSection
     * @return array
     */
    protected function mapSectionTypeToWidgetTypes(TemplateSection $templateSection): array
    {
        $baseWidgetTypes = ['text', 'image', 'counter', 'gallery', 'form', 'video'];
        
        switch ($templateSection->section_type) {
            case TemplateSection::TYPE_SIDEBAR_LEFT:
            case TemplateSection::TYPE_SIDEBAR_RIGHT:
                // Add navigation widgets for sidebars
                $baseWidgetTypes[] = 'navigation';
                break;
        }
        
        return $baseWidgetTypes;
    }

    /**
     * Map section type to styling configuration
     * 
     * @param TemplateSection $templateSection
     * @return array
     */
    protected function mapSectionTypeToStyling(TemplateSection $templateSection): array
    {
        $baseStyling = [
            'css_classes' => 'container',
            'background_color' => '#ffffff',
            'padding' => ['top' => 40, 'bottom' => 40, 'left' => 0, 'right' => 0],
            'margin' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
            'resize_handles' => ['se', 'sw']
        ];

        switch ($templateSection->section_type) {
            case TemplateSection::TYPE_FULL_WIDTH:
                $baseStyling['css_classes'] = 'container-fluid';
                $baseStyling['padding'] = ['top' => 40, 'bottom' => 40, 'left' => 0, 'right' => 0];
                break;
                
            case TemplateSection::TYPE_MULTI_COLUMN:
                $baseStyling['css_classes'] = 'container';
                $baseStyling['background_color'] = '#f8f9fa';
                $baseStyling['resize_handles'] = ['se', 'sw', 'ne', 'nw'];
                break;
                
            case TemplateSection::TYPE_SIDEBAR_LEFT:
            case TemplateSection::TYPE_SIDEBAR_RIGHT:
                $baseStyling['css_classes'] = 'container';
                $baseStyling['background_color'] = '#ffffff';
                $baseStyling['sidebar_background'] = '#f8f9fa';
                break;
        }

        return $baseStyling;
    }
}
