<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\ContentType;
use App\Models\WidgetContentTypeAssociation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WidgetContentAssociationService
{
    protected $compatibilityService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(WidgetContentCompatibilityService $compatibilityService)
    {
        $this->compatibilityService = $compatibilityService;
    }

    /**
     * Associate a widget with a content type
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @param array $fieldMappings
     * @param array $options Additional options (query filters, etc)
     * @return WidgetContentTypeAssociation
     */
    public function createAssociation(Widget $widget, ContentType $contentType, array $fieldMappings, array $options = [])
    {
        // Check if association already exists
        $existing = WidgetContentTypeAssociation::where('widget_id', $widget->id)
            ->where('content_type_id', $contentType->id)
            ->first();
            
        if ($existing) {
            // Update existing association
            return $this->updateMappings($existing, $fieldMappings, $options);
        }
        
        // Validate compatibility before creating association
        $compatibilityCheck = $this->compatibilityService->checkCompatibility($widget, $contentType);
        
        if (!$compatibilityCheck['compatible']) {
            throw new \Exception("Widget '{$widget->name}' is not compatible with content type '{$contentType->name}': " . 
                ($compatibilityCheck['message'] ?? 'Unknown compatibility issue'));
        }
        
        // Create new association
        return WidgetContentTypeAssociation::create([
            'widget_id' => $widget->id,
            'content_type_id' => $contentType->id,
            'field_mappings' => $fieldMappings,
            'options' => $options,
            'is_active' => true,
        ]);
    }
    
    /**
     * Update field mappings for an existing association
     * 
     * @param WidgetContentTypeAssociation $association
     * @param array $fieldMappings
     * @param array $options Additional options (query filters, etc)
     * @return WidgetContentTypeAssociation
     */
    public function updateMappings(WidgetContentTypeAssociation $association, array $fieldMappings, array $options = [])
    {
        // Merge options if provided, otherwise keep existing
        if (!empty($options)) {
            $mergedOptions = array_merge($association->options ?? [], $options);
            $association->options = $mergedOptions;
        }
        
        $association->field_mappings = $fieldMappings;
        $association->save();
        
        return $association;
    }
    
    /**
     * Remove an association between widget and content type
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @return bool Success status
     */
    public function removeAssociation(Widget $widget, ContentType $contentType)
    {
        return WidgetContentTypeAssociation::where('widget_id', $widget->id)
            ->where('content_type_id', $contentType->id)
            ->delete();
    }
    
    /**
     * Get all compatible content types for a widget
     * 
     * @param Widget $widget
     * @return array Content types with compatibility data
     */
    public function getCompatibleContentTypes(Widget $widget)
    {
        $contentTypes = ContentType::all();
        $result = [];
        
        foreach ($contentTypes as $contentType) {
            $compatibilityData = $this->compatibilityService->checkCompatibility($widget, $contentType);
            
            if ($compatibilityData['compatible']) {
                $result[] = [
                    'content_type' => $contentType,
                    'compatibility' => $compatibilityData,
                    'association' => WidgetContentTypeAssociation::where('widget_id', $widget->id)
                                        ->where('content_type_id', $contentType->id)
                                        ->first()
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Get all current content type associations for a widget
     * 
     * @param Widget $widget
     * @return Collection Association records with content types
     */
    public function getWidgetAssociations(Widget $widget)
    {
        return WidgetContentTypeAssociation::where('widget_id', $widget->id)
            ->with('contentType')
            ->get();
    }
    
    /**
     * Set the active association for a widget instance
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @param bool $exclusive If true, deactivates all other associations
     * @return bool Success status
     */
    public function setActiveAssociation(Widget $widget, ContentType $contentType, $exclusive = true)
    {
        try {
            DB::beginTransaction();
            
            // If exclusive, deactivate all other associations
            if ($exclusive) {
                WidgetContentTypeAssociation::where('widget_id', $widget->id)
                    ->update(['is_active' => false]);
            }
            
            // Activate the specified association
            $affected = WidgetContentTypeAssociation::where('widget_id', $widget->id)
                ->where('content_type_id', $contentType->id)
                ->update(['is_active' => true]);
                
            DB::commit();
            return $affected > 0;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to set active association: " . $e->getMessage());
            return false;
        }
    }
}
