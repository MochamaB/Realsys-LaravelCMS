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
        
        // Check for explicit compatibility in widget.json if available
        $explicitlyCompatible = false;
        $explicitMapping = [];
        $widgetConfig = [];
        
        $widgetJsonPath = resource_path("themes/{$widget->theme->slug}/widgets/{$widget->slug}/widget.json");
        
        if (file_exists($widgetJsonPath)) {
            $widgetConfig = json_decode(file_get_contents($widgetJsonPath), true) ?? [];
            
            // Check if content_type_compatibility is defined in the widget.json
            if (isset($widgetConfig['content_type_compatibility'])) {
                $compatibilityData = $widgetConfig['content_type_compatibility'];
                
                // Check if this content type is explicitly listed as compatible
                if (isset($compatibilityData['compatible_content_types'])) {
                    $explicitlyCompatible = in_array($contentType->slug, $compatibilityData['compatible_content_types']) || 
                                           in_array('*', $compatibilityData['compatible_content_types']);
                    
                    if ($explicitlyCompatible) {
                        $explicitMapping = $this->getFieldMapping($widgetConfig, $contentType->slug);
                    }
                }
            }
        }
        
        // First, perform field-by-field compatibility analysis regardless of explicit config
        // This ensures we always get a complete compatibility picture
        $fieldCompatibilityResult = $this->analyzeFieldCompatibility($widget, $contentType, $widgetFields, $contentTypeFields);
        
        // If explicitly defined as compatible in widget.json, override the compatibility result
        if ($explicitlyCompatible) {
            return [
                'compatible' => true,
                'explicitly_defined' => true,
                'mapping' => $explicitMapping,
                'field_compatibility' => $fieldCompatibilityResult['field_compatibility'] ?? [],
                'message' => "Widget '{$widget->name}' is explicitly compatible with '{$contentType->name}' content type."
            ];
        }
        
        // Return the field-based compatibility analysis result
        return $fieldCompatibilityResult;
    }
    
    /**
     * Analyze field-by-field compatibility between widget and content type
     *
     * @param Widget $widget The widget to check
     * @param ContentType $contentType The content type to check against
     * @param Collection $widgetFields Collection of widget field definitions
     * @param Collection $contentTypeFields Collection of content type fields
     * @return array Compatibility result
     */
    protected function analyzeFieldCompatibility(Widget $widget, ContentType $contentType, $widgetFields, $contentTypeFields)
    {
        $requiredFieldsCompatible = true;
        $missingFields = [];
        $fieldCompatibility = [];
        
        // Get required widget fields (those marked as is_required in the database)
        $requiredWidgetFields = $widgetFields->where('is_required', true);
        
        // If no required fields, we'll do a more general compatibility check
        if ($requiredWidgetFields->isEmpty()) {
            // First, check if there are any repeater fields on both sides
            $widgetHasRepeater = $widgetFields->where('field_type', 'repeater')->count() > 0;
            $contentTypeHasRepeater = $contentTypeFields->where('field_type', 'repeater')->count() > 0;
            
            if ($widgetHasRepeater && $contentTypeHasRepeater) {
                // Check repeater compatibility
                $widgetRepeater = $widgetFields->where('field_type', 'repeater')->first();
                $contentTypeRepeater = $contentTypeFields->where('field_type', 'repeater')->first();
                
                // Generate mappings for the repeater fields
                $repeaterMappings = $this->generateRepeaterFieldMappings($widgetRepeater, $contentTypeFields->where('field_type', 'repeater'));
                
                if (!empty($repeaterMappings)) {
                    return [
                        'compatible' => true,
                        'explicitly_defined' => false,
                        'field_compatibility' => $repeaterMappings,
                        'missing_fields' => [],
                        'suggested_mapping' => $this->generateFieldMappings($widget, $contentType),
                        'message' => "Widget '{$widget->name}' has compatible repeater fields with '{$contentType->name}' content type."
                    ];
                }
            }
            
            // If repeater check didn't find compatibility, do general field check
            $minFieldsNeeded = min(3, $widgetFields->count()); // At least 3 fields or all if less than 3
            $compatibleFieldCount = 0;
            
            foreach ($widgetFields as $widgetField) {
                foreach ($contentTypeFields as $contentField) {
                    if ($this->areFieldTypesCompatible($widgetField->field_type, $contentField->field_type)) {
                        $compatibleFieldCount++;
                        $fieldCompatibility[$widgetField->name] = $contentField->name;
                        break;
                    }
                }
            }
            
            if ($compatibleFieldCount >= $minFieldsNeeded) {
                return [
                    'compatible' => true,
                    'explicitly_defined' => false,
                    'field_compatibility' => $fieldCompatibility,
                    'missing_fields' => [],
                    'suggested_mapping' => $this->generateFieldMappings($widget, $contentType),
                    'message' => "Widget '{$widget->name}' has at least {$compatibleFieldCount} compatible fields with '{$contentType->name}' content type."
                ];
            }
            
            return [
                'compatible' => false,
                'explicitly_defined' => false,
                'field_compatibility' => $fieldCompatibility,
                'missing_fields' => [],
                'message' => "Widget '{$widget->name}' does not have enough compatible fields with '{$contentType->name}' content type."
            ];
        }
        
        // Check specifically required widget fields against content type fields
        foreach ($requiredWidgetFields as $widgetField) {
            $fieldName = $widgetField->name;
            $fieldType = $widgetField->field_type;
            $matchFound = false;
            
            // Special handling for repeater fields
            if ($fieldType === 'repeater') {
                foreach ($contentTypeFields->where('field_type', 'repeater') as $contentRepeater) {
                    // Generate mappings for the repeater fields
                    $repeaterMappings = $this->generateRepeaterFieldMappings($widgetField, collect([$contentRepeater]));
                    
                    if (!empty($repeaterMappings)) {
                        $matchFound = true;
                        foreach ($repeaterMappings as $widgetPath => $contentPath) {
                            $fieldCompatibility[$widgetPath] = $contentPath;
                        }
                        break;
                    }
                }
            } 
            // Standard fields
            else {
                foreach ($contentTypeFields as $contentField) {
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
    /**
     * Generate mappings for repeater field and its sub-fields
     * 
     * @param object $repeaterField The widget repeater field
     * @param Collection $contentFields Collection of content type fields
     * @return array Field mappings for repeater
     */
    protected function generateRepeaterFieldMappings($repeaterField, $contentFields)
    {
        $mappings = [];
        
        // Find content repeater fields
        $contentRepeaterFields = $contentFields->filter(function($field) {
            return $field->field_type === 'repeater';
        });
        
        if ($contentRepeaterFields->isEmpty()) {
            return $mappings;
        }
        
        // Get repeater field's subfields with fallbacks for different formats
        $subFields = [];
        
        // First check for standardized location
        if (isset($repeaterField->settings) && isset($repeaterField->settings['subfields'])) {
            $subFields = $repeaterField->settings['subfields'];
        } 
        // Check legacy format
        elseif (isset($repeaterField->settings) && isset($repeaterField->settings['sub_fields'])) {
            $subFields = $repeaterField->settings['sub_fields'];
        }
        // Check if settings is a JSON string
        elseif (is_string($repeaterField->settings)) {
            $settingsArray = json_decode($repeaterField->settings, true);
            $subFields = $settingsArray['subfields'] ?? $settingsArray['sub_fields'] ?? [];
        }
        
        // Find best matching content repeater field
        $bestContentRepeater = $this->findBestMatchingField($repeaterField, $contentRepeaterFields);
        
        if (!$bestContentRepeater) {
            return $mappings;
        }
        
        // Add main repeater mapping
        $fieldKey = $repeaterField->name ?? $repeaterField->key ?? $repeaterField->slug ?? null;
        if (!$fieldKey) {
            return $mappings;
        }
        
        $mappings[$fieldKey] = $bestContentRepeater->name;
        
        // Map sub-fields if content repeater has sub-fields too
        $contentSubFields = [];
        
        // First check for standardized location in content field
        if (isset($bestContentRepeater->settings) && isset($bestContentRepeater->settings['subfields'])) {
            $contentSubFields = $bestContentRepeater->settings['subfields'];
        } 
        // Check legacy format
        elseif (isset($bestContentRepeater->settings) && isset($bestContentRepeater->settings['sub_fields'])) {
            $contentSubFields = $bestContentRepeater->settings['sub_fields'];
        }
        // Check if settings is a JSON string
        elseif (is_string($bestContentRepeater->settings)) {
            $settingsArray = json_decode($bestContentRepeater->settings, true);
            $contentSubFields = $settingsArray['subfields'] ?? $settingsArray['sub_fields'] ?? [];
        }
        
        foreach ($subFields as $subField) {
            // Normalize subfield to object if it's an array
            $subFieldObj = is_array($subField) ? (object)$subField : $subField;
            
            // Get field type using different possible property names
            $subFieldType = $subFieldObj->field_type ?? $subFieldObj->type ?? null;
            $subFieldName = $subFieldObj->name ?? $subFieldObj->key ?? $subFieldObj->slug ?? null;
            
            if (!$subFieldType || !$subFieldName) {
                continue;
            }
            
            foreach ($contentSubFields as $contentSubField) {
                // Normalize content subfield to object if it's an array
                $contentSubFieldObj = is_array($contentSubField) ? (object)$contentSubField : $contentSubField;
                
                // Get field type using different possible property names
                $contentSubFieldType = $contentSubFieldObj->field_type ?? $contentSubFieldObj->type ?? null;
                $contentSubFieldName = $contentSubFieldObj->name ?? $contentSubFieldObj->key ?? $contentSubFieldObj->slug ?? null;
                
                if (!$contentSubFieldType || !$contentSubFieldName) {
                    continue;
                }
                
                if ($this->areFieldTypesCompatible($subFieldType, $contentSubFieldType)) {
                    $mappings["{$fieldKey}.{$subFieldName}"] = "{$bestContentRepeater->name}.{$contentSubFieldName}";
                    
                    // Handle nested repeaters recursively
                    if ($subFieldType === 'repeater' && $contentSubFieldType === 'repeater') {
                        $nestedMappings = $this->generateNestedRepeaterMappings($subFieldObj, $contentSubFieldObj, "{$fieldKey}.{$subFieldName}", "{$bestContentRepeater->name}.{$contentSubFieldName}");
                        $mappings = array_merge($mappings, $nestedMappings);
                    }
                    
                    break;
                }
            }
        }
        
        return $mappings;
    }
    
    /**
     * Generate mappings for nested repeater fields
     * 
     * @param object $widgetRepeaterField The widget repeater field
     * @param object $contentRepeaterField The content type repeater field
     * @param string $widgetParentPath The parent path for widget field (e.g. 'main_repeater.nested_repeater')
     * @param string $contentParentPath The parent path for content field (e.g. 'content_repeater.nested_content_repeater')
     * @return array Nested field mappings
     */
    protected function generateNestedRepeaterMappings($widgetRepeaterField, $contentRepeaterField, $widgetParentPath, $contentParentPath)
    {
        $mappings = [];
        
        // Get widget repeater's subfields with fallbacks
        $widgetSubfields = [];
        
        // Check different locations for subfields
        if (isset($widgetRepeaterField->settings)) {
            if (is_array($widgetRepeaterField->settings)) {
                $widgetSubfields = $widgetRepeaterField->settings['subfields'] ?? 
                                  $widgetRepeaterField->settings['sub_fields'] ?? [];
            } elseif (is_string($widgetRepeaterField->settings)) {
                $settingsArray = json_decode($widgetRepeaterField->settings, true) ?: [];
                $widgetSubfields = $settingsArray['subfields'] ?? $settingsArray['sub_fields'] ?? [];
            } elseif (is_object($widgetRepeaterField->settings)) {
                $widgetSubfields = $widgetRepeaterField->settings->subfields ?? 
                                  $widgetRepeaterField->settings->sub_fields ?? [];
            }
        }
        
        // Get content type repeater's subfields with fallbacks
        $contentSubfields = [];
        
        // Check different locations for subfields
        if (isset($contentRepeaterField->settings)) {
            if (is_array($contentRepeaterField->settings)) {
                $contentSubfields = $contentRepeaterField->settings['subfields'] ?? 
                                    $contentRepeaterField->settings['sub_fields'] ?? [];
            } elseif (is_string($contentRepeaterField->settings)) {
                $settingsArray = json_decode($contentRepeaterField->settings, true) ?: [];
                $contentSubfields = $settingsArray['subfields'] ?? $settingsArray['sub_fields'] ?? [];
            } elseif (is_object($contentRepeaterField->settings)) {
                $contentSubfields = $contentRepeaterField->settings->subfields ?? 
                                    $contentRepeaterField->settings->sub_fields ?? [];
            }
        }
        
        // Map each subfield if types are compatible
        foreach ($widgetSubfields as $widgetSubfield) {
            // Normalize to object
            $widgetSubfieldObj = is_array($widgetSubfield) ? (object)$widgetSubfield : $widgetSubfield;
            
            // Get field properties
            $widgetSubfieldType = $widgetSubfieldObj->field_type ?? $widgetSubfieldObj->type ?? null;
            $widgetSubfieldName = $widgetSubfieldObj->name ?? $widgetSubfieldObj->key ?? $widgetSubfieldObj->slug ?? null;
            
            if (!$widgetSubfieldType || !$widgetSubfieldName) {
                continue;
            }
            
            foreach ($contentSubfields as $contentSubfield) {
                // Normalize to object
                $contentSubfieldObj = is_array($contentSubfield) ? (object)$contentSubfield : $contentSubfield;
                
                // Get field properties
                $contentSubfieldType = $contentSubfieldObj->field_type ?? $contentSubfieldObj->type ?? null;
                $contentSubfieldName = $contentSubfieldObj->name ?? $contentSubfieldObj->key ?? $contentSubfieldObj->slug ?? null;
                
                if (!$contentSubfieldType || !$contentSubfieldName) {
                    continue;
                }
                
                if ($this->areFieldTypesCompatible($widgetSubfieldType, $contentSubfieldType)) {
                    $widgetPath = "{$widgetParentPath}.{$widgetSubfieldName}";
                    $contentPath = "{$contentParentPath}.{$contentSubfieldName}";
                    
                    $mappings[$widgetPath] = $contentPath;
                    
                    // Handle deeper nesting if needed
                    if ($widgetSubfieldType === 'repeater' && $contentSubfieldType === 'repeater') {
                        $deeperMappings = $this->generateNestedRepeaterMappings(
                            $widgetSubfieldObj, 
                            $contentSubfieldObj, 
                            $widgetPath, 
                            $contentPath
                        );
                        $mappings = array_merge($mappings, $deeperMappings);
                    }
                    
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
