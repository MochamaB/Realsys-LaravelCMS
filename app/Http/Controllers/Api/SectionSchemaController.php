<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\SectionSchemaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SectionSchemaController extends Controller
{
    protected $sectionSchemaService;

    public function __construct(SectionSchemaService $sectionSchemaService)
    {
        $this->sectionSchemaService = $sectionSchemaService;
    }

    /**
     * Get all section schemas for a specific page
     *
     * @param Page $page
     * @return JsonResponse
     */
    public function getPageSectionSchemas(Page $page): JsonResponse
    {
        try {
            Log::info('Starting to load page section schemas', ['page_id' => $page->id]);
            
            $schemas = $this->sectionSchemaService->getPageSectionSchemas($page);
            
            Log::info('Successfully loaded page section schemas', [
                'page_id' => $page->id,
                'schema_count' => count($schemas)
            ]);
            
            return response()->json([
                'success' => true,
                'schemas' => $schemas,
                'count' => count($schemas),
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting page section schemas', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load page section schemas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schema for a specific section
     *
     * @param PageSection $section
     * @return JsonResponse
     */
    public function getSectionSchema(PageSection $section): JsonResponse
    {
        try {
            $schema = $this->sectionSchemaService->getSectionSchema($section);
            
            if (!$schema) {
                return response()->json([
                    'error' => 'Section schema not found',
                    'section_id' => $section->id
                ], 404);
            }

            return response()->json([
                'success' => true,
                'schema' => $schema
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting section schema', [
                'section_id' => $section->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to load section schema',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available section types for the active theme
     *
     * @return JsonResponse
     */
    public function getAvailableSectionTypes(): JsonResponse
    {
        try {
            $sectionTypes = $this->sectionSchemaService->getAvailableSectionTypes();
            
            return response()->json([
                'success' => true,
                'section_types' => $sectionTypes,
                'count' => count($sectionTypes)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting available section types: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load section types',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new section schema for GrapesJS
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createNewSectionSchema(Request $request): JsonResponse
    {
        try {
            $sectionType = $request->input('section_type', 'full-width');
            $options = $request->input('options', []);
            
            // Validate section type
            $availableTypes = $this->sectionSchemaService->getAvailableSectionTypes();
            if (!isset($availableTypes[$sectionType])) {
                return response()->json([
                    'error' => 'Invalid section type',
                    'available_types' => array_keys($availableTypes)
                ], 400);
            }

            $schema = $this->sectionSchemaService->createNewSectionSchema($sectionType, $options);
            
            // Validate the created schema
            $validationErrors = $this->sectionSchemaService->validateSectionSchema($schema);
            if (!empty($validationErrors)) {
                return response()->json([
                    'error' => 'Invalid section schema generated',
                    'validation_errors' => $validationErrors
                ], 400);
            }

            return response()->json([
                'success' => true,
                'schema' => $schema,
                'section_type' => $sectionType
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating new section schema', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to create section schema',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate a section schema
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateSectionSchema(Request $request): JsonResponse
    {
        try {
            $schema = $request->input('schema', []);
            
            if (empty($schema)) {
                return response()->json([
                    'error' => 'Schema data is required'
                ], 400);
            }

            $validationErrors = $this->sectionSchemaService->validateSectionSchema($schema);
            
            return response()->json([
                'success' => empty($validationErrors),
                'valid' => empty($validationErrors),
                'errors' => $validationErrors,
                'schema' => $schema
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating section schema', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to validate section schema',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear section schema cache
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $pageId = $request->input('page_id');
            
            $this->sectionSchemaService->clearCache($pageId);
            
            return response()->json([
                'success' => true,
                'message' => 'Section schema cache cleared',
                'page_id' => $pageId
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing section schema cache', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to clear cache',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get section schema statistics for a page
     *
     * @param Page $page
     * @return JsonResponse
     */
    public function getPageSectionStats(Page $page): JsonResponse
    {
        try {
            $schemas = $this->sectionSchemaService->getPageSectionSchemas($page);
            
            $stats = [
                'total_sections' => count($schemas),
                'active_sections' => 0,
                'total_widgets' => 0,
                'section_types' => [],
                'widget_types' => []
            ];
            
            foreach ($schemas as $schema) {
                if ($schema['is_active']) {
                    $stats['active_sections']++;
                }
                
                $stats['total_widgets'] += $schema['meta']['widget_count'];
                
                // Count section types
                $sectionType = $schema['type'];
                $stats['section_types'][$sectionType] = ($stats['section_types'][$sectionType] ?? 0) + 1;
                
                // Count widget types
                foreach ($schema['columns'] as $column) {
                    foreach ($column['widgets'] as $widget) {
                        $widgetType = $widget['widget_type'];
                        $stats['widget_types'][$widgetType] = ($stats['widget_types'][$widgetType] ?? 0) + 1;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting page section stats', [
                'page_id' => $page->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to load section statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 