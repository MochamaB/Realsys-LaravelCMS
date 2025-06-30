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
     * Tracks which content fields have already been mapped to avoid duplicates
     *
     * @var array
     */
    protected $mappedContentFields = [];
    /**
     * Check if a widget is compatible with a content type
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @return array Compatibility status and details
     */
    public function checkCompatibility(Widget $widget, ContentType $contentType)
    {
        // Get widget fields
        $widgetFields = $widget->fieldDefinitions;
        
        // Get content type fields
        $contentTypeFields = $contentType->fields;
        
        // If widget has no fields, it's technically compatible with any content type
        if ($widgetFields->isEmpty()) {
            return [
                'compatible' => true,
                'explicitly_defined' => false,
                'mapping' => [],
                'message' => "Widget '{$widget->name}' has no fields that require content."
            ];
        }
        
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
        
        // Get required widget fields (those marked as is_required in the database)
        $requiredWidgetFields = $widgetFields->where('is_required', true);
        
        // If no required fields, we'll do a more general compatibility check
        if ($requiredWidgetFields->isEmpty()) {
            // Just check if we have enough fields of compatible types
            $minFieldsNeeded = min(3, $widgetFields->count()); // At least 3 fields or all if less than 3
            $compatibleFieldCount = 0;
            
            foreach ($widgetFields as $widgetField) {
                foreach ($contentTypeFields as $contentField) {
                    if ($this->areFieldTypesCompatible($widgetField->field_type, $contentField->field_type)) {
                        $compatibleFieldCount++;
                        break;
                    }
                }
            }
            
            if ($compatibleFieldCount >= $minFieldsNeeded) {
                return [
                    'compatible' => true,
                    'explicitly_defined' => false,
                    'mapping' => [],
                    'message' => "Widget '{$widget->name}' has at least {$compatibleFieldCount} compatible fields with '{$contentType->name}' content type."
                ];
            }
            
            return [
                'compatible' => false,
                'explicitly_defined' => false,
                'mapping' => [],
                'message' => "Widget '{$widget->name}' does not have enough compatible fields with '{$contentType->name}' content type."
            ];
        }
        
        // Check specifically required widget fields against content type fields
        foreach ($requiredWidgetFields as $widgetField) {
            $fieldName = $widgetField->name;
            $fieldType = $widgetField->field_type;
            $matchFound = false;
            
            // Look for matching field in content type by name or slug
            foreach ($contentTypeFields as $contentField) {
                // Try to match by name or slug (case insensitive)
                $nameMatches = strtolower($contentField->name) === strtolower($fieldName);
                $slugMatches = strtolower($contentField->slug) === strtolower($fieldName);
                
                if ($nameMatches || $slugMatches) {
                    // Check field type compatibility
                    if ($this->areFieldTypesCompatible($fieldType, $contentField->field_type)) {
                        $matchFound = true;
                        $fieldCompatibility[$fieldName] = $contentField->name;
                        break;
                    }
                }
            }
            
            if (!$matchFound) {
                $requiredFieldsCompatible = false;
                $missingFields[] = $fieldName;
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
     * Generate field mappings between a widget and a content type
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @return array
     */
    public function generateFieldMappings(Widget $widget, ContentType $contentType)
    {
        // Reset mapped content fields for this generation
        $this->mappedContentFields = [];
        
        // Check if we have predefined mappings in the widget.json
        $widgetJsonPath = resource_path("themes/{$widget->theme->slug}/widgets/{$widget->slug}/widget.json");
        $widgetConfig = [];
        
        if (file_exists($widgetJsonPath)) {
            $widgetConfig = json_decode(file_get_contents($widgetJsonPath), true) ?? [];
        }
        
        // Use predefined mappings if available
        $predefinedMappings = $this->getFieldMapping($widgetConfig, $contentType->slug);
        if (!empty($predefinedMappings)) {
            return $predefinedMappings;
        }
        
        // Get widget fields
        $widgetFields = $widget->fieldDefinitions;
        
        // Get content type fields
        $contentFields = $contentType->fields;
        
        $mappings = [];
        $mappedContentFields = []; // Keep track of already mapped content fields
        
        // First, prioritize required widget fields
        $requiredWidgetFields = $widgetFields->where('is_required', true);
        
        // Process required fields first
        foreach ($requiredWidgetFields as $widgetField) {
            $widgetFieldName = $widgetField->name;
            $widgetFieldSlug = $widgetField->slug;
            $mappedFields = $this->mappedContentFields; // Create a copy for closure
            
            // First, try to find exact name match
            $exactMatch = $contentFields->first(function ($field) use ($widgetFieldName, $widgetFieldSlug, $mappedFields) {
                return !in_array($field->id, $mappedFields) && (
                    strtolower($field->name) === strtolower($widgetFieldName) ||
                    strtolower($field->slug) === strtolower($widgetFieldName) ||
                    strtolower($field->name) === strtolower($widgetFieldSlug) ||
                    strtolower($field->slug) === strtolower($widgetFieldSlug)
                );
            });
            
            if ($exactMatch) {
                $mappings[$widgetFieldName] = $exactMatch->name;
                $mappedContentFields[] = $exactMatch->id;
                continue;
            }
            
            // Second, try to find similar name (contains)
            $similarMatch = $contentFields->first(function ($field) use ($widgetFieldName, $widgetFieldSlug, $mappedFields) {
                return !in_array($field->id, $mappedFields) && (
                    str_contains(strtolower($field->name), strtolower($widgetFieldName)) ||
                    str_contains(strtolower($field->slug), strtolower($widgetFieldName)) ||
                    str_contains(strtolower($field->name), strtolower($widgetFieldSlug)) ||
                    str_contains(strtolower($field->slug), strtolower($widgetFieldSlug)) ||
                    str_contains(strtolower($widgetFieldName), strtolower($field->name)) ||
                    str_contains(strtolower($widgetFieldSlug), strtolower($field->name))
                );
            });
            
            if ($similarMatch) {
                $mappings[$widgetFieldName] = $similarMatch->name;
                $mappedContentFields[] = $similarMatch->id;
                continue;
            }
            
            // Third, try to find compatible field types
            $compatibleField = $contentFields->first(function ($field) use ($widgetField, $mappedFields) {
                return !in_array($field->id, $mappedFields) && 
                       $this->areFieldTypesCompatible($widgetField->field_type, $field->field_type);
            });
            
            if ($compatibleField) {
                $mappings[$widgetFieldName] = $compatibleField->name;
                $this->mappedContentFields[] = $compatibleField->id;
                continue;
            }
        }
        
        // Then process non-required fields if available capacity
        $nonRequiredWidgetFields = $widgetFields->where('is_required', false);
        
        foreach ($nonRequiredWidgetFields as $widgetField) {
            // Skip if we've already mapped all content fields
            if (count($this->mappedContentFields) >= $contentFields->count()) {
                break;
            }
            
            $widgetFieldName = $widgetField->name;
            $mappedFields = $this->mappedContentFields; // Create a copy for closure
            
            // First, try to find exact name match
            $exactMatch = $contentFields->first(function ($field) use ($widgetFieldName, $mappedFields) {
                return !in_array($field->id, $mappedFields) && (
                    strtolower($field->name) === strtolower($widgetFieldName) ||
                    strtolower($field->slug) === strtolower($widgetFieldName)
                );
            });
            
            if ($exactMatch) {
                $mappings[$widgetFieldName] = $exactMatch->name;
                $this->mappedContentFields[] = $exactMatch->id;
                continue;
            }
            
            // Second, try to find compatible field types
            $compatibleField = $contentFields->first(function ($field) use ($widgetField, $mappedFields) {
                return !in_array($field->id, $mappedFields) && 
                       $this->areFieldTypesCompatible($widgetField->field_type, $field->field_type);
            });
            
            if ($compatibleField) {
                $mappings[$widgetFieldName] = $compatibleField->name;
                $this->mappedContentFields[] = $compatibleField->id;
                continue;
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
     * Check if two field types are compatible
     * 
     * @param string $widgetFieldType
     * @param string $contentFieldType
     * @return bool
     */
    protected function areFieldTypesCompatible($widgetFieldType, $contentFieldType)
    {
        // Exact match is always compatible
        if (strtolower($widgetFieldType) === strtolower($contentFieldType)) {
            return true;
        }
        
        // Get field types from config if available
        $fieldTypes = config('field_types', []);
        
        // Define compatibility groups based on common field type characteristics
        $textTypes = ['text', 'textarea', 'rich_text', 'wysiwyg', 'html', 'string', 'richtext', 'markdown', 'code', 'url', 'email'];
        $numberTypes = ['number', 'integer', 'float', 'numeric', 'decimal', 'currency'];
        $dateTypes = ['date', 'datetime', 'time'];
        $mediaTypes = ['image', 'file', 'media', 'gallery', 'video', 'audio'];
        $booleanTypes = ['boolean', 'toggle', 'checkbox', 'switch'];
        $selectTypes = ['select', 'multiselect', 'radio', 'checkbox', 'dropdown'];
        $locationTypes = ['map', 'location', 'address', 'coordinates'];
        $referenceTypes = ['relationship', 'post_object', 'page_link', 'reference', 'content_reference'];
        
        // Normalize field types to lowercase for comparison
        $widgetFieldType = strtolower($widgetFieldType);
        $contentFieldType = strtolower($contentFieldType);
        
        // Check if both types are in the same compatibility group
        $typeGroups = [
            $textTypes, 
            $numberTypes, 
            $dateTypes, 
            $mediaTypes, 
            $booleanTypes, 
            $selectTypes,
            $locationTypes,
            $referenceTypes
        ];
        
        foreach ($typeGroups as $typeGroup) {
            if (in_array($widgetFieldType, $typeGroup) && in_array($contentFieldType, $typeGroup)) {
                return true;
            }
        }
        
        // Special cases for broader compatibility
        
        // Text fields are generally compatible with most other types for display purposes
        if (in_array($widgetFieldType, $textTypes)) {
            // Text widgets can generally display most content except complex types
            return !in_array($contentFieldType, array_merge($mediaTypes, $locationTypes));
        }
        
        // Rich text can display content from most text-based fields
        if ($widgetFieldType === 'rich_text' || $widgetFieldType === 'wysiwyg') {
            return in_array($contentFieldType, $textTypes);
        }
        
        // Media fields can accept URLs or references
        if (in_array($widgetFieldType, $mediaTypes) && 
            (in_array($contentFieldType, $referenceTypes) || $contentFieldType === 'url')) {
            return true;
        }
        
        // Select fields can accept boolean values
        if (in_array($widgetFieldType, $selectTypes) && in_array($contentFieldType, $booleanTypes)) {
            return true;
        }
        
        return false;
    }
}
