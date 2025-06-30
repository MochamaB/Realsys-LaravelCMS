<?php

namespace App\Services;

use App\Models\ContentType;
use App\Models\ContentTypeField;
use App\Models\Widget;
use App\Services\WidgetContentAssociationService;
use App\Services\WidgetContentCompatibilityService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentTypeGeneratorService
{
    /**
     * @var WidgetContentAssociationService
     */
    protected $associationService;
    
    /**
     * @var WidgetContentCompatibilityService
     */
    protected $compatibilityService;
    
    /**
     * Constructor
     * 
     * @param WidgetContentAssociationService $associationService
     * @param WidgetContentCompatibilityService $compatibilityService
     */
    public function __construct(
        WidgetContentAssociationService $associationService,
        WidgetContentCompatibilityService $compatibilityService
    ) {
        $this->associationService = $associationService;
        $this->compatibilityService = $compatibilityService;
    }
    
    /**
     * Generate a suggested content type structure from a widget's field definitions
     *
     * @param Widget $widget
     * @return array
     */
    public function generateContentTypeFromWidget(Widget $widget): array
    {
        try {
            $widgetFields = $widget->fieldDefinitions;
            
            if ($widgetFields->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Widget has no field definitions.',
                    'content_type' => null
                ];
            }
            
            // Create basic content type structure
            $contentTypeStructure = [
                'name' => $this->generateContentTypeName($widget),
                'slug' => Str::slug($this->generateContentTypeName($widget)),
                'description' => "Generated from {$widget->name} widget.",
                'icon' => 'bx bx-layer',
                'fields' => []
            ];
            
            // Process each widget field to create corresponding content type field
            foreach ($widgetFields as $widgetField) {
                $field = $this->convertWidgetFieldToContentField($widgetField);
                if ($field) {
                    $contentTypeStructure['fields'][] = $field;
                }
            }
            
            return [
                'success' => true,
                'message' => 'Content type structure generated successfully.',
                'content_type' => $contentTypeStructure
            ];
            
        } catch (\Exception $e) {
            Log::error('Error generating content type from widget: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'widget_name' => $widget->name
            ]);
            
            return [
                'success' => false,
                'message' => 'Error generating content type: ' . $e->getMessage(),
                'content_type' => null
            ];
        }
    }
    
    /**
     * Generate a suitable name for the content type based on widget name
     *
     * @param Widget $widget
     * @return string
     */
    protected function generateContentTypeName(Widget $widget): string
    {
        // Remove common widget-related terms
        $name = str_replace(['Widget', 'widget', 'Block', 'block'], '', $widget->name);
        $name = trim($name);
        
        // If the result is too short, use the original name with "Content" appended
        if (strlen($name) < 3) {
            return $widget->name . ' Content';
        }
        
        return $name . ' Content';
    }
    
    /**
     * Convert a widget field definition to a content type field structure
     *
     * @param object $widgetField
     * @return array|null
     */
    protected function convertWidgetFieldToContentField($widgetField): ?array
    {
        // Skip system fields that don't need content equivalents
        if (in_array($widgetField->name, ['widget_id', 'id', 'created_at', 'updated_at'])) {
            return null;
        }
        
        $fieldName = $this->generateFieldName($widgetField->name);
        $fieldType = $this->mapWidgetFieldTypeToContentFieldType($widgetField->field_type);
        
        $field = [
            'name' => $fieldName,
            'slug' => Str::slug($fieldName),
            'field_type' => $fieldType,
            'is_required' => $widgetField->is_required ?? false,
            'is_unique' => false,
            'position' => $widgetField->position ?? 0,
            'description' => $widgetField->description ?? '',
            'validation_rules' => $widgetField->validation_rules ?? '',
            'settings' => []
        ];
        
        // Parse existing settings if they exist
        $existingSettings = [];
        if (isset($widgetField->settings)) {
            if (is_string($widgetField->settings)) {
                $existingSettings = json_decode($widgetField->settings, true) ?? [];
            } elseif (is_array($widgetField->settings)) {
                $existingSettings = $widgetField->settings;
            } elseif (is_object($widgetField->settings)) {
                $existingSettings = (array) $widgetField->settings;
            }
        }
        
        // Add type-specific settings
        switch ($fieldType) {
            case 'repeater':
                // Special handling for repeater fields - process subfields
                $subfields = [];
                
                // Check for subfields in different possible locations
                $fieldList = null;
                
                // Option 1: Check if fields property exists directly
                if (isset($widgetField->fields) && is_array($widgetField->fields)) {
                    $fieldList = $widgetField->fields;
                }
                // Option 2: Check if subfields exists in settings
                elseif (isset($existingSettings['subfields']) && is_array($existingSettings['subfields'])) {
                    $fieldList = $existingSettings['subfields'];
                }
                
                // Process subfields if we found them
                if ($fieldList) {
                    foreach ($fieldList as $subfield) {
                        // Convert each subfield recursively
                        $processedSubfield = $this->convertWidgetFieldToContentField($subfield);
                        if ($processedSubfield) {
                            $subfields[] = $processedSubfield;
                        }
                    }
                }
                
                // Add subfield array to settings
                $field['settings']['subfields'] = $subfields;
                $field['settings']['min_items'] = $existingSettings['min_items'] ?? 0;
                $field['settings']['max_items'] = $existingSettings['max_items'] ?? null;
                break;
                
            case 'text':
            case 'textarea':
            case 'rich_text':
                $field['settings']['min_length'] = $existingSettings['min_length'] ?? 0;
                $field['settings']['max_length'] = $existingSettings['max_length'] ?? 255;
                break;
                
            case 'number':
                $field['settings']['min'] = $existingSettings['min'] ?? null;
                $field['settings']['max'] = $existingSettings['max'] ?? null;
                $field['settings']['step'] = $existingSettings['step'] ?? 1;
                break;
                
            case 'select':
            case 'radio':
            case 'checkbox':
                // Extract options if available
                if (isset($existingSettings['options']) && is_array($existingSettings['options'])) {
                    $field['settings']['options'] = $existingSettings['options'];
                }
                break;
                
            case 'date':
            case 'datetime':
                // Add date format settings
                $field['settings']['format'] = $existingSettings['format'] ?? 'Y-m-d';
                break;
                
            case 'image':
            case 'file':
                $field['settings']['allowed_extensions'] = $existingSettings['allowed_extensions'] ?? ['jpg', 'jpeg', 'png', 'gif'];
                $field['settings']['max_size'] = $existingSettings['max_size'] ?? 2048; // 2MB default
                break;
        }
        
        return $field;
    }
    
    /**
     * Generate a clean field name from widget field name
     *
     * @param string $widgetFieldName
     * @return string
     */
    protected function generateFieldName(string $widgetFieldName): string
    {
        return Str::snake($widgetFieldName);
    }
    
    /**
     * Generate a human-readable label from field name
     *
     * @param string $fieldName
     * @return string
     */
    protected function generateFieldLabel(string $fieldName): string
    {
        return Str::title(str_replace(['_', '-'], ' ', $fieldName));
    }
    
    /**
     * Map widget field types to content field types
     *
     * @param string|null $widgetFieldType
     * @return string
     */
    protected function mapWidgetFieldTypeToContentFieldType(?string $widgetFieldType): string
    {
        // If type is null, default to text field
        if ($widgetFieldType === null) {
            return 'text';
        }
        
        // Get all available content field types from config
        $contentFieldTypes = array_keys(config('field_types', []));
        
        // Define mapping from widget field types to content field types
        $typeMap = [
            // Basic text fields
            'text' => 'text',
            'textarea' => 'textarea',
            'rich_text' => 'rich_text',
            'wysiwyg' => 'rich_text',
            'editor' => 'rich_text',
            
            // Number and date fields
            'number' => 'number',
            'integer' => 'number',
            'float' => 'number',
            'date' => 'date',
            'datetime' => 'datetime',
            'time' => 'time',
            
            // Selection fields
            'select' => 'select',
            'dropdown' => 'select',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'toggle' => 'boolean',
            'boolean' => 'boolean',
            'switch' => 'boolean',
            
            // Media fields
            'image' => 'image',
            'picture' => 'image',
            'photo' => 'image',
            'file' => 'file',
            'document' => 'file',
            'attachment' => 'file',
            'gallery' => 'gallery',
            'images' => 'gallery',
            'photos' => 'gallery',
            
            // Special fields
            'color' => 'color',
            'email' => 'email',
            'tel' => 'phone',
            'phone' => 'phone',
            'telephone' => 'phone',
            'url' => 'url',
            'link' => 'url',
            'website' => 'url',
            'password' => 'text', // Map to text as password fields may not be appropriate in content
            
            // Complex fields
            'repeater' => 'repeater',
            'group' => 'repeater',
            'array' => 'repeater',
            'json' => 'json',
            'relation' => 'relation',
            'reference' => 'relation',
        ];
        
        // Get mapped type or default to text
        $mappedType = $typeMap[$widgetFieldType] ?? 'text';
        
        // Double check that the mapped type exists in our config, otherwise default to text
        return in_array($mappedType, $contentFieldTypes) ? $mappedType : 'text';
    }
    
    /**
     * Create a new content type from the suggested structure
     *
     * @param array $contentTypeData
     * @param Widget|null $sourceWidget The widget this content type is being created from
     * @return ContentType|null
     */
    public function createContentTypeFromStructure(array $contentTypeData, string $name = null, string $description = null, Widget $sourceWidget = null)  {
        try {
            // Extract base content type data
            $contentTypeBase = [
                'name' => $contentTypeData['name'],
                'slug' => $contentTypeData['slug'],
                'description' => $contentTypeData['description'],
                'icon' => $contentTypeData['icon'] ?? 'bx bx-layer',
                'table_name' => 'content_' . Str::snake($contentTypeData['slug'])
            ];
            
            // Create content type
            $contentType = ContentType::create($contentTypeBase);
            
            // Create fields
            if (!empty($contentTypeData['fields']) && is_array($contentTypeData['fields'])) {
                foreach ($contentTypeData['fields'] as $index => $fieldData) {
                    // Generate slug if not provided
                    $fieldSlug = $fieldData['slug'] ?? Str::slug($fieldData['name']);
                    
                    // Parse settings if it's a JSON string
                    $settings = $fieldData['settings'] ?? [];
                    if (is_string($settings)) {
                        $settings = json_decode($settings, true) ?? [];
                    }
                    ContentTypeField::create([
                        'content_type_id' => $contentType->id,
                        'name' => $fieldData['name'],
                        'slug' =>  $fieldSlug,
                        'field_type' => $fieldData['field_type'],
                        'is_required' => $fieldData['is_required'] ?? false,
                        'is_unique' => $fieldData['is_unique'] ?? false,
                        'position' => $fieldData['position'] ?? $index,
                        'description' => $fieldData['description'] ?? '',
                        'validation_rules' => $fieldData['validation_rules'] ?? '',
                        'settings' => json_encode($settings)
                    ]);
                }
            }
            
            // If this content type was created from a widget, automatically associate them
            if (!empty($sourceWidget) && $sourceWidget instanceof Widget) {
                try {
                    // Generate field mappings automatically
                    $fieldMappings = $this->compatibilityService->generateFieldMappings($sourceWidget, $contentType);
                    
                    // Create the association
                    $this->associationService->createAssociation($sourceWidget, $contentType, $fieldMappings);
                    
                    Log::info('Automatically associated content type with widget', [
                        'content_type' => $contentType->name,
                        'widget' => $sourceWidget->name
                    ]);
                } catch (\Exception $assocError) {
                    Log::warning('Failed to automatically associate content type with widget: ' . $assocError->getMessage(), [
                        'content_type_id' => $contentType->id,
                        'widget_id' => $sourceWidget->id
                    ]);
                }
            }
            
            return $contentType;
            
        } catch (\Exception $e) {
            Log::error('Error creating content type from structure: ' . $e->getMessage(), [
                'content_type_data' => $contentTypeData
            ]);
            
            return null;
        }
    }
}