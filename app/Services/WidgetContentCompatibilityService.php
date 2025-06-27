<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\ContentType;
use App\Models\WidgetFieldDefinition;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class WidgetContentCompatibilityService
{
    /**
     * Check if a widget is compatible with a content type
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @return array Compatibility status and details
     */
    public function checkCompatibility(Widget $widget, ContentType $contentType)
    {
        // Get widget field definitions
        $widgetFields = $widget->fieldDefinitions;
        
        // Get content type fields
        $contentFields = $contentType->fields ?? collect([]);
        
        // Get widget.json content for detailed compatibility analysis
        $widgetJsonPath = resource_path("themes/{$widget->theme->slug}/widgets/{$widget->slug}/widget.json");
        $widgetConfig = [];
        
        if (file_exists($widgetJsonPath)) {
            $widgetConfig = json_decode(file_get_contents($widgetJsonPath), true) ?? [];
        }
        
        // Check if content_type_compatibility is defined in the widget.json
        $explicitlyCompatible = false;
        $compatibilityData = [];
        
        if (isset($widgetConfig['content_type_compatibility'])) {
            $compatibilityData = $widgetConfig['content_type_compatibility'];
            
            // Check if this content type is explicitly listed as compatible
            if (isset($compatibilityData['compatible_content_types'])) {
                $explicitlyCompatible = in_array($contentType->slug, $compatibilityData['compatible_content_types']) || 
                                       in_array('*', $compatibilityData['compatible_content_types']);
            }
        }
        
        // If universal compatibility is defined, it's compatible
        if ($explicitlyCompatible) {
            return [
                'compatible' => true,
                'explicitly_defined' => true,
                'mapping' => $this->getFieldMapping($widgetConfig, $contentType->slug),
                'message' => "Widget '{$widget->name}' is explicitly compatible with '{$contentType->name}' content type."
            ];
        }
        
        // Check for field compatibility if not explicitly defined
        $requiredFieldsCompatible = true;
        $missingFields = [];
        $fieldCompatibility = [];
        
        foreach ($widgetFields as $widgetField) {
            // Skip fields that aren't required for content
            if (!$widgetField->settings || !Arr::get($widgetField->settings, 'required_for_content', false)) {
                continue;
            }
            
            // Check if content type has a compatible field
            $hasCompatibleField = false;
            
            foreach ($contentFields as $contentField) {
                if ($this->areFieldTypesCompatible($widgetField->type, $contentField->type)) {
                    $hasCompatibleField = true;
                    $fieldCompatibility[$widgetField->key] = $contentField->name;
                    break;
                }
            }
            
            if (!$hasCompatibleField) {
                $requiredFieldsCompatible = false;
                $missingFields[] = $widgetField->key;
            }
        }
        
        // Generate the compatibility result
        $result = [
            'compatible' => $requiredFieldsCompatible,
            'explicitly_defined' => false,
            'field_compatibility' => $fieldCompatibility,
            'missing_fields' => $missingFields,
        ];
        
        if ($requiredFieldsCompatible) {
            $result['message'] = "Widget '{$widget->name}' appears to be compatible with '{$contentType->name}' content type based on field analysis.";
            $result['suggested_mapping'] = $this->generateFieldMappings($widget, $contentType);
        } else {
            $result['message'] = "Widget '{$widget->name}' is not fully compatible with '{$contentType->name}' content type. Missing compatible fields for: " . implode(', ', $missingFields);
        }
        
        return $result;
    }
    
    /**
     * Generate field mappings between widget fields and content type fields
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @return array Field mapping suggestions
     */
    public function generateFieldMappings(Widget $widget, ContentType $contentType)
    {
        $widgetFields = $widget->fieldDefinitions;
        $contentFields = $contentType->fields ?? collect([]);
        $mappings = [];
        
        foreach ($widgetFields as $widgetField) {
            // Skip fields that don't need content mapping
            if (!$widgetField->settings || !Arr::get($widgetField->settings, 'maps_to_content', false)) {
                continue;
            }
            
            // Handle repeater fields specially
            if ($widgetField->type === 'repeater') {
                $repeaterMappings = $this->generateRepeaterFieldMappings($widgetField, $contentFields);
                $mappings = array_merge($mappings, $repeaterMappings);
                continue;
            }
            
            // Find best matching content field by type and name similarity
            $bestMatch = $this->findBestMatchingField($widgetField, $contentFields);
            
            if ($bestMatch) {
                $mappings[$widgetField->key] = $bestMatch->name;
            }
        }
        
        return $mappings;
    }
    
    /**
     * Generate mappings for repeater field and its sub-fields
     * 
     * @param WidgetFieldDefinition $repeaterField
     * @param Collection $contentFields
     * @return array Field mappings for repeater
     */
    protected function generateRepeaterFieldMappings($repeaterField, $contentFields)
    {
        $mappings = [];
        
        // Find content repeater fields
        $contentRepeaterFields = $contentFields->filter(function($field) {
            return $field->type === 'repeater';
        });
        
        if ($contentRepeaterFields->isEmpty()) {
            return $mappings;
        }
        
        // Get repeater field's sub-fields
        $subFields = Arr::get($repeaterField->settings, 'sub_fields', []);
        
        // Find best matching content repeater field
        $bestContentRepeater = $this->findBestMatchingField($repeaterField, $contentRepeaterFields);
        
        if (!$bestContentRepeater) {
            return $mappings;
        }
        
        // Add main repeater mapping
        $mappings[$repeaterField->key] = $bestContentRepeater->name;
        
        // Map sub-fields if content repeater has sub-fields too
        $contentSubFields = Arr::get($bestContentRepeater->settings, 'sub_fields', []);
        
        foreach ($subFields as $subField) {
            foreach ($contentSubFields as $contentSubField) {
                if ($this->areFieldTypesCompatible($subField['type'], $contentSubField['type'])) {
                    $mappings["{$repeaterField->key}.{$subField['key']}"] = "{$bestContentRepeater->name}.{$contentSubField['name']}";
                    break;
                }
            }
        }
        
        return $mappings;
    }
    
    /**
     * Find the best matching content field for a widget field
     * 
     * @param WidgetFieldDefinition $widgetField
     * @param Collection $contentFields
     * @return mixed Best matching content field or null
     */
    protected function findBestMatchingField($widgetField, $contentFields)
    {
        // First try exact name match
        $exactNameMatch = $contentFields->first(function($field) use ($widgetField) {
            return $field->name === $widgetField->key;
        });
        
        if ($exactNameMatch && $this->areFieldTypesCompatible($widgetField->type, $exactNameMatch->type)) {
            return $exactNameMatch;
        }
        
        // Then try similar name match with compatible type
        $typeCompatibleFields = $contentFields->filter(function($field) use ($widgetField) {
            return $this->areFieldTypesCompatible($widgetField->type, $field->type);
        });
        
        if ($typeCompatibleFields->isEmpty()) {
            return null;
        }
        
        // Try common field patterns (title, name, content, etc.)
        $commonPatterns = $this->getCommonFieldPatterns($widgetField->key);
        
        foreach ($commonPatterns as $pattern) {
            $match = $typeCompatibleFields->first(function($field) use ($pattern) {
                return strpos($field->name, $pattern) !== false;
            });
            
            if ($match) {
                return $match;
            }
        }
        
        // Return first type-compatible field as fallback
        return $typeCompatibleFields->first();
    }
    
    /**
     * Get common field name patterns for a given field key
     * 
     * @param string $fieldKey
     * @return array Common patterns
     */
    protected function getCommonFieldPatterns($fieldKey)
    {
        // Map common widget field keys to potential content field name patterns
        $commonMappings = [
            'title' => ['title', 'name', 'heading'],
            'subtitle' => ['subtitle', 'subheading', 'summary'],
            'content' => ['content', 'description', 'body', 'text'],
            'image' => ['image', 'photo', 'picture', 'thumbnail', 'featured_image'],
            'link' => ['link', 'url', 'website'],
            'date' => ['date', 'published', 'created'],
            'author' => ['author', 'writer', 'creator'],
            'category' => ['category', 'type', 'group'],
            'tags' => ['tags', 'keywords']
        ];
        
        $patterns = $commonMappings[$fieldKey] ?? [];
        
        // Always add the original field key as a pattern
        $patterns[] = $fieldKey;
        
        return $patterns;
    }
    
    /**
     * Get field mapping from widget.json for a specific content type
     * 
     * @param array $widgetConfig
     * @param string $contentTypeSlug
     * @return array Field mappings
     */
    protected function getFieldMapping($widgetConfig, $contentTypeSlug)
    {
        $compatibilityData = $widgetConfig['content_type_compatibility'] ?? [];
        
        // Try to get specific mapping for this content type
        if (isset($compatibilityData['field_mappings'][$contentTypeSlug])) {
            return $compatibilityData['field_mappings'][$contentTypeSlug];
        }
        
        // Try to get default mapping
        if (isset($compatibilityData['field_mappings']['default'])) {
            return $compatibilityData['field_mappings']['default'];
        }
        
        // No mapping defined
        return [];
    }
    
    /**
     * Check if specific fields are compatible
     * 
     * @param string $widgetFieldType
     * @param string $contentFieldType
     * @return bool Whether fields are compatible
     */
    public function areFieldTypesCompatible($widgetFieldType, $contentFieldType)
    {
        // Direct type matches
        if ($widgetFieldType === $contentFieldType) {
            return true;
        }
        
        // Define compatibility groups
        $textTypes = ['text', 'textarea', 'wysiwyg', 'html', 'string', 'richtext'];
        $numberTypes = ['number', 'integer', 'float', 'numeric'];
        $dateTypes = ['date', 'datetime', 'time'];
        $mediaTypes = ['image', 'file', 'media', 'gallery'];
        $booleanTypes = ['boolean', 'toggle', 'checkbox'];
        $structuredTypes = ['repeater', 'group', 'flexible_content'];
        $referenceTypes = ['relationship', 'post_object', 'page_link', 'reference'];
        
        // Check if both types are in the same compatibility group
        foreach ([$textTypes, $numberTypes, $dateTypes, $mediaTypes, $booleanTypes, $structuredTypes, $referenceTypes] as $typeGroup) {
            if (in_array($widgetFieldType, $typeGroup) && in_array($contentFieldType, $typeGroup)) {
                return true;
            }
        }
        
        // Special cases
        
        // Text can accept many different types for display purposes
        if (in_array($widgetFieldType, $textTypes)) {
            return true; // Text widgets can generally display any content
        }
        
        // Some image fields can be references or actual images
        if (in_array($widgetFieldType, $mediaTypes) && in_array($contentFieldType, $referenceTypes)) {
            return true;
        }
        
        return false;
    }
}
