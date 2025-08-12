<?php

namespace App\Services;

use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Models\WidgetDefinition;
use App\Services\WidgetContentFetchService;
use App\Services\WidgetContentCompatibilityService;
use App\Services\WidgetContentAssociationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\WidgetFieldDefinition;
use App\Models\ContentType;
use App\Models\ContentItem;
use App\Services\UniversalStylingService;

class WidgetService
{
    /**
     * @var ThemeManager
     */
    protected $themeManager;
    
    /**
     * @var WidgetContentFetchService
     */
    protected $contentFetchService;
    
    /**
     * @var WidgetContentCompatibilityService
     */
    protected $compatibilityService;
    
    /**
     * @var WidgetContentAssociationService
     */
    protected $associationService;

    /**
     * WidgetService constructor.
     * @param ThemeManager $themeManager
     * @param WidgetContentFetchService $contentFetchService
     * @param WidgetContentCompatibilityService $compatibilityService
     * @param WidgetContentAssociationService $associationService
     */
    public function __construct(
        ThemeManager $themeManager,
        WidgetContentFetchService $contentFetchService,
        WidgetContentCompatibilityService $compatibilityService,
        WidgetContentAssociationService $associationService
    ) {
        $this->themeManager = $themeManager;
        $this->contentFetchService = $contentFetchService;
        $this->compatibilityService = $compatibilityService;
        $this->associationService = $associationService;
    }

    /**
     * Get widgets for a specific page section
     *
     * @param int $pageSectionId
     * @return array
     */
    public function getWidgetsForSection(int $pageSectionId): array
    {
        // Debug info for section widgets
        \Log::debug('Getting widgets for section', ['section_id' => $pageSectionId]);
        
        // Get widget relationships through the pivot table
        $pivotRecords = PageSectionWidget::where('page_section_id', $pageSectionId)
            ->with('widget')
            ->orderBy('position')
            ->get();
        
        \Log::debug('Pivot records found', [
            'section_id' => $pageSectionId, 
            'count' => $pivotRecords->count()
        ]);
        
        $widgetData = [];
        
        foreach ($pivotRecords as $pivot) {
            $widget = $pivot->widget;
            
            if ($widget) {
                $widgetInfo = [
                    'widget_id' => $widget->id,
                    'widget_name' => $widget->name,
                    'widget_slug' => $widget->slug
                ];
                \Log::debug('Widget found', $widgetInfo);
                
                // Pass the pivot record to get proper field values
                $widgetData[] = $this->prepareWidgetData($widget, $pivot);
            } else {
                \Log::warning('Widget not found', ['pivot_id' => $pivot->id]);
            }
        }
        
        \Log::debug('Widget data prepared', [
            'section_id' => $pageSectionId,
            'widgets_count' => count($widgetData)
        ]);
        
        return $widgetData;
    }
    
    /**
     * Prepare widget data for rendering
     *
     * @param Widget $widget
     * @param PageSectionWidget|null $pageSectionWidget
     * @return array
     */
    public function prepareWidgetData(Widget $widget, PageSectionWidget $pageSectionWidget = null): array
    {
        // Basic widget data
        $data = [
            'id' => $widget->id,
            'name' => $widget->name,
            'slug' => $widget->slug,
            'view_path' => $this->resolveWidgetViewPath($widget),
            'fields' => $this->getWidgetFieldValues($widget, $pageSectionWidget),
        ];
        
        // Add page section widget data if available
        if ($pageSectionWidget) {
            $data['position'] = $pageSectionWidget->position;
            $data['column_position'] = $pageSectionWidget->column_position;
            $data['css_classes'] = $pageSectionWidget->css_classes;
            $data['settings'] = $pageSectionWidget->settings ?? [];
            $data['content_query'] = $pageSectionWidget->content_query ?? [];
            
            // ✅ ADD: Pass PageSectionWidget instance for universal styling
            $data['pageSectionWidget'] = $pageSectionWidget;
            
            // ✅ ADD: Universal styling data
            $universalStylingService = app(UniversalStylingService::class);
            $data['universal_classes'] = $universalStylingService->buildWidgetClasses($pageSectionWidget);
            $data['universal_styles'] = $universalStylingService->buildWidgetStyles($pageSectionWidget);
            $data['grid_attributes'] = $universalStylingService->buildWidgetGridAttributes($pageSectionWidget);
        }
        
        // Add content data if this widget has content associations
        if ($widget->contentTypeAssociations()->exists()) {
            $data['content'] = $this->getWidgetContent($widget);
        }
        
        // ✅ ADD: Collect widget assets
        $data['assets'] = $this->collectWidgetAssets($widget);
        
        return $data;
    }
    
    /**
     * Get field values for a widget instance in a page section
     *
     * @param Widget $widget
     * @param PageSectionWidget|null $pageSectionWidget
     * @return array
     */
    public function getWidgetFieldValues(Widget $widget, PageSectionWidget $pageSectionWidget = null): array
    {
        $fieldValues = [];
        
        // Get field definitions for structure
        $fieldDefinitions = WidgetFieldDefinition::where('widget_id', $widget->id)
            ->orderBy('position')
            ->get();
        
        // If no page section widget provided, return defaults only
        if (!$pageSectionWidget) {
            foreach ($fieldDefinitions as $field) {
                $fieldValues[$field->slug] = $this->getDefaultFieldValue($field);
            }
            return $fieldValues;
        }
        
        // Get user-configured values from settings
        $settings = $pageSectionWidget->settings ?? [];
        
        // Get content data from content query
        $contentData = $this->getContentFromQuery($widget, $pageSectionWidget->content_query ?? []);
        
        // Process each field definition
        foreach ($fieldDefinitions as $field) {
            $fieldSlug = $field->slug;
            
            // Priority order:
            // 1. User settings (from modal configuration)
            // 2. Content data (from content query)
            // 3. Default value (from field definition)
            
            if (isset($settings[$fieldSlug])) {
                // User has configured this field
                $fieldValues[$fieldSlug] = $this->formatFieldValue($settings[$fieldSlug], $field->field_type);
            } elseif (isset($contentData[$fieldSlug])) {
                // Field populated from content query
                $fieldValues[$fieldSlug] = $this->formatFieldValue($contentData[$fieldSlug], $field->field_type);
            } else {
                // Use default value
                $fieldValues[$fieldSlug] = $this->getDefaultFieldValue($field);
            }
        }
        
        // Add any additional content data that doesn't match field definitions
        foreach ($contentData as $key => $value) {
            if (!isset($fieldValues[$key])) {
                $fieldValues[$key] = $value;
            }
        }
        
        return $fieldValues;
    }
    
    /**
     * Get default value for a field definition
     *
     * @param WidgetFieldDefinition $field
     * @return mixed
     */
    protected function getDefaultFieldValue(WidgetFieldDefinition $field)
    {
        // Check if field has default value in settings
        $settings = $field->settings ?? [];
        if (isset($settings['default_value'])) {
            return $this->formatFieldValue($settings['default_value'], $field->field_type);
        }
        
        // Return appropriate default based on field type
        switch ($field->field_type) {
            case 'boolean':
                return false;
            case 'number':
            case 'integer':
                return 0;
            case 'array':
            case 'json':
                return [];
            case 'repeater':
                return $this->generateRepeaterDefaults($field);
            case 'text':
            case 'textarea':
            case 'rich_text':
            case 'url':
            case 'email':
            case 'phone':
            default:
                return '';
        }
    }
    
    /**
     * Generate default items for repeater fields based on min_items and subfield defaults
     *
     * @param WidgetFieldDefinition $field
     * @return array
     */
    protected function generateRepeaterDefaults(WidgetFieldDefinition $field): array
    {
        $settings = $field->settings ?? [];
        $minItems = $settings['min_items'] ?? 0;
        $subfields = $settings['subfields'] ?? [];
        
        // If no min_items or subfields, return empty array
        if ($minItems <= 0 || empty($subfields)) {
            return [];
        }
        
        $defaultItems = [];
        
        // Generate the minimum required items
        for ($i = 0; $i < $minItems; $i++) {
            $item = [];
            
            // For each subfield, get its default value
            foreach ($subfields as $subfield) {
                $subfieldSlug = $subfield['slug'] ?? null;
                if (!$subfieldSlug) continue;
                
                // Get subfield default value
                if (isset($subfield['default'])) {
                    $item[$subfieldSlug] = $subfield['default'];
                } else {
                    // Fallback based on subfield type
                    $subfieldType = $subfield['field_type'] ?? 'text';
                    $item[$subfieldSlug] = $this->getDefaultValueForType($subfieldType);
                }
            }
            
            $defaultItems[] = $item;
        }
        
        \Log::debug('Generated repeater defaults', [
            'field_slug' => $field->slug,
            'min_items' => $minItems,
            'generated_items' => count($defaultItems),
            'default_items' => $defaultItems
        ]);
        
        return $defaultItems;
    }
    
    /**
     * Get default value for a specific field type
     *
     * @param string $fieldType
     * @return mixed
     */
    protected function getDefaultValueForType(string $fieldType)
    {
        switch ($fieldType) {
            case 'boolean':
                return false;
            case 'number':
            case 'integer':
                return 0;
            case 'array':
            case 'json':
                return [];
            default:
                return '';
        }
    }
    
    /**
     * Get content data from content query
     *
     * @param Widget $widget
     * @param array $contentQuery
     * @return array
     */
    protected function getContentFromQuery(Widget $widget, array $contentQuery): array
    {
        \Log::debug('getContentFromQuery called', [
            'widget_id' => $widget->id,
            'content_query' => $contentQuery,
            'content_query_type' => gettype($contentQuery),
            'content_query_empty' => empty($contentQuery)
        ]);
        
        if (empty($contentQuery)) {
            \Log::debug('Content query is empty, returning empty array');
            return [];
        }
        
        try {
            // Get content type if specified
            if (!isset($contentQuery['content_type_id'])) {
                return [];
            }
            
            $contentType = ContentType::find($contentQuery['content_type_id']);
            if (!$contentType) {
                return [];
            }
            
            // Get field mappings from widget-content association
            $association = $widget->contentTypeAssociations()
                ->where('content_type_id', $contentType->id)
                ->where('is_active', true)
                ->first();
            
            $fieldMappings = [];
            if ($association && $association->field_mappings) {
                $fieldMappings = $association->field_mappings;
                \Log::debug('Field mappings found', [
                    'widget_id' => $widget->id,
                    'content_type_id' => $contentType->id,
                    'mappings' => $fieldMappings
                ]);
            }
            
            // Build query for content items
            $query = ContentItem::where('content_type_id', $contentType->id);
                // Remove status filter for now since content is in 'draft' status
                // ->where('status', 'published');
            
            // Apply specific content item IDs if provided
            if (!empty($contentQuery['content_item_ids'])) {
                $query->whereIn('id', $contentQuery['content_item_ids']);
            }
            
            // Apply ordering
            $orderBy = $contentQuery['order_by'] ?? 'created_at';
            $orderDirection = $contentQuery['order_direction'] ?? 'desc';
            $query->orderBy($orderBy, $orderDirection);
            
            // Apply limit
            $limit = $contentQuery['limit'] ?? 1;
            $query->limit($limit);
            
            $contentItems = $query->with('fieldValues.field')->get();
            
            if ($contentItems->isEmpty()) {
                return [];
            }
            
            // For single item, return flat array with field mappings applied
            if ($limit == 1) {
                return $this->extractContentItemData($contentItems->first(), $fieldMappings);
            }
            
            // For multiple items, return array of items
            $result = [];
            foreach ($contentItems as $item) {
                $result[] = $this->extractContentItemData($item, $fieldMappings);
            }
            
            return ['items' => $result];
            
        } catch (\Exception $e) {
            \Log::error('Error fetching content from query', [
                'widget_id' => $widget->id,
                'content_query' => $contentQuery,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Extract field data from a content item
     *
     * @param ContentItem $contentItem
     * @param array $fieldMappings Optional field mappings to apply
     * @return array
     */
    protected function extractContentItemData(ContentItem $contentItem, array $fieldMappings = []): array
    {
        $data = [
            'id' => $contentItem->id,
            'title' => $contentItem->title,
            'slug' => $contentItem->slug,
            'status' => $contentItem->status,
            'published_at' => $contentItem->published_at,
            'created_at' => $contentItem->created_at,
            'updated_at' => $contentItem->updated_at,
        ];
        
        \Log::debug('Starting content item data extraction', [
            'content_item_id' => $contentItem->id,
            'has_field_mappings' => !empty($fieldMappings),
            'field_mappings' => $fieldMappings,
            'field_values_count' => $contentItem->fieldValues->count()
        ]);
        
        // If we have field mappings, process them (including repeater fields)
        if (!empty($fieldMappings)) {
            \Log::debug('Using field mappings processing');
            $processedData = $this->processFieldMappings($contentItem, $fieldMappings);
            $data = array_merge($data, $processedData);
        } else {
            \Log::debug('Using direct field processing (no mappings)');
            // No mappings, process fields directly
            $this->addDirectFieldValues($contentItem, $data);
        }
        
        \Log::debug('Final extracted data', [
            'content_item_id' => $contentItem->id,
            'data_keys' => array_keys($data),
            'data_sample' => array_slice($data, 0, 5, true)
        ]);
        
        return $data;
    }
    
    /**
     * Process field mappings including repeater fields with dot notation
     *
     * @param ContentItem $contentItem
     * @param array $fieldMappings
     * @return array
     */
    protected function processFieldMappings(ContentItem $contentItem, array $fieldMappings): array
    {
        $data = [];
        $repeaterMappings = [];
        $flatMappings = [];
        
        // First, expand any flat repeater mappings to include subfield mappings
        $expandedMappings = $this->expandRepeaterMappings($contentItem, $fieldMappings);
        
        \Log::debug('Expanded field mappings', [
            'original_mappings' => $fieldMappings,
            'expanded_mappings' => $expandedMappings
        ]);
        
        // Separate flat mappings from repeater mappings (dot notation)
        foreach ($expandedMappings as $widgetField => $contentField) {
            if (strpos($widgetField, '.') !== false || strpos($contentField, '.') !== false) {
                $repeaterMappings[$widgetField] = $contentField;
            } else {
                $flatMappings[$widgetField] = $contentField;
            }
        }
        
        \Log::debug('Separated field mappings', [
            'flat_mappings' => $flatMappings,
            'repeater_mappings' => $repeaterMappings
        ]);
        
        // Process flat mappings first
        foreach ($flatMappings as $widgetField => $contentField) {
            $value = $this->getContentFieldValue($contentItem, $contentField);
            if ($value !== null) {
                $widgetFieldKey = strtolower(str_replace(' ', '_', $widgetField));
                $data[$widgetFieldKey] = $value;
                
                \Log::debug('Applied flat field mapping', [
                    'widget_field' => $widgetFieldKey,
                    'content_field' => $contentField,
                    'value_type' => gettype($value)
                ]);
            }
        }
        
        // Process repeater mappings
        if (!empty($repeaterMappings)) {
            $repeaterData = $this->processRepeaterFieldMappings($contentItem, $repeaterMappings);
            $data = array_merge($data, $repeaterData);
        }
        
        return $data;
    }
    
    /**
     * Expand flat repeater mappings to include subfield mappings
     *
     * @param ContentItem $contentItem
     * @param array $fieldMappings
     * @return array
     */
    protected function expandRepeaterMappings(ContentItem $contentItem, array $fieldMappings): array
    {
        $expandedMappings = $fieldMappings;
        
        foreach ($fieldMappings as $widgetField => $contentField) {
            // Skip if this is already a dot notation mapping
            if (strpos($widgetField, '.') !== false || strpos($contentField, '.') !== false) {
                continue;
            }
            
            // Check if this is a repeater field mapping
            if ($this->isRepeaterFieldMapping($contentItem, $widgetField, $contentField)) {
                \Log::debug('Found flat repeater mapping, generating subfield mappings', [
                    'widget_field' => $widgetField,
                    'content_field' => $contentField
                ]);
                
                // Generate subfield mappings
                $subfieldMappings = $this->generateAutomaticSubfieldMappings($contentItem, $widgetField, $contentField);
                
                if (!empty($subfieldMappings)) {
                    $expandedMappings = array_merge($expandedMappings, $subfieldMappings);
                    
                    \Log::debug('Generated automatic subfield mappings', [
                        'widget_parent' => $widgetField,
                        'content_parent' => $contentField,
                        'subfield_mappings' => $subfieldMappings
                    ]);
                }
            }
        }
        
        return $expandedMappings;
    }
    
    /**
     * Check if a field mapping represents a repeater field
     *
     * @param ContentItem $contentItem
     * @param string $widgetField
     * @param string $contentField
     * @return bool
     */
    protected function isRepeaterFieldMapping(ContentItem $contentItem, string $widgetField, string $contentField): bool
    {
        // Check if the content field is a repeater field
        $contentTypeField = $contentItem->contentType->fields->filter(function ($field) use ($contentField) {
            return $field->slug === $contentField || $field->name === $contentField;
        })->first();
        
        if (!$contentTypeField || $contentTypeField->field_type !== 'repeater') {
            return false;
        }
        
        // Check if the widget field is also a repeater field
        // We need to get the widget from the current context
        return true; // For now, assume it's a repeater if content field is repeater
    }
    
    /**
     * Generate automatic subfield mappings for a repeater field
     *
     * @param ContentItem $contentItem
     * @param string $widgetField
     * @param string $contentField
     * @return array
     */
    protected function generateAutomaticSubfieldMappings(ContentItem $contentItem, string $widgetField, string $contentField): array
    {
        $subfieldMappings = [];
        
        // Get content field definition
        $contentTypeField = $contentItem->contentType->fields->filter(function ($field) use ($contentField) {
            return $field->slug === $contentField || $field->name === $contentField;
        })->first();
        
        if (!$contentTypeField || $contentTypeField->field_type !== 'repeater') {
            return $subfieldMappings;
        }
        
        // Parse content field subfields
        $contentSubfields = $this->parseRepeaterSubfields($contentTypeField);
        
        if (empty($contentSubfields)) {
            \Log::debug('No content subfields found for repeater', [
                'content_field' => $contentField
            ]);
            return $subfieldMappings;
        }
        
        // For automatic mapping, we'll map subfields by name/slug matching
        // This works when widget and content type have similar subfield names
        foreach ($contentSubfields as $contentSubfield) {
            $contentSubfieldName = $contentSubfield['name'] ?? $contentSubfield['slug'] ?? '';
            $contentSubfieldSlug = $contentSubfield['slug'] ?? $contentSubfield['name'] ?? '';
            
            if (empty($contentSubfieldName)) {
                continue;
            }
            
            // Create dot notation mapping
            // Widget: "Counters.icon" -> Content: "repeater.icon"
            $widgetSubfieldPath = $widgetField . '.' . $contentSubfieldSlug;
            $contentSubfieldPath = $contentField . '.' . $contentSubfieldSlug;
            
            $subfieldMappings[$widgetSubfieldPath] = $contentSubfieldPath;
            
            \Log::debug('Generated subfield mapping', [
                'widget_path' => $widgetSubfieldPath,
                'content_path' => $contentSubfieldPath,
                'subfield_type' => $contentSubfield['field_type'] ?? $contentSubfield['type'] ?? 'unknown'
            ]);
        }
        
        return $subfieldMappings;
    }
    
    /**
     * Parse repeater field subfields from field settings
     *
     * @param mixed $repeaterField
     * @return array
     */
    protected function parseRepeaterSubfields($repeaterField): array
    {
        $settings = $repeaterField->settings ?? [];
        
        // Handle different settings formats
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        } elseif (is_object($settings)) {
            $settings = (array) $settings;
        }
        
        // Check different possible locations for subfields
        $subfields = $settings['subfields'] ?? $settings['sub_fields'] ?? [];
        
        if (!is_array($subfields)) {
            return [];
        }
        
        // Normalize subfield structure
        $normalizedSubfields = [];
        foreach ($subfields as $subfield) {
            if (is_array($subfield)) {
                $normalizedSubfields[] = $subfield;
            } elseif (is_object($subfield)) {
                $normalizedSubfields[] = (array) $subfield;
            }
        }
        
        \Log::debug('Parsed repeater subfields', [
            'field_name' => $repeaterField->name ?? $repeaterField->slug ?? 'unknown',
            'subfields_count' => count($normalizedSubfields),
            'subfield_names' => array_map(function($sf) {
                return $sf['name'] ?? $sf['slug'] ?? 'unnamed';
            }, $normalizedSubfields)
        ]);
        
        return $normalizedSubfields;
    }
    
    /**
     * Process repeater field mappings with dot notation
     *
     * @param ContentItem $contentItem
     * @param array $repeaterMappings
     * @return array
     */
    protected function processRepeaterFieldMappings(ContentItem $contentItem, array $repeaterMappings): array
    {
        $data = [];
        $groupedMappings = [];
        
        // Group mappings by parent repeater field
        foreach ($repeaterMappings as $widgetField => $contentField) {
            // Parse widget field path
            $widgetParts = explode('.', $widgetField, 2);
            $widgetParent = $widgetParts[0];
            $widgetChild = $widgetParts[1] ?? null;
            
            // Parse content field path
            $contentParts = explode('.', $contentField, 2);
            $contentParent = $contentParts[0];
            $contentChild = $contentParts[1] ?? null;
            
            // Group by widget parent field
            if (!isset($groupedMappings[$widgetParent])) {
                $groupedMappings[$widgetParent] = [
                    'content_parent' => $contentParent,
                    'subfield_mappings' => []
                ];
            }
            
            // Add subfield mapping if both have children
            if ($widgetChild && $contentChild) {
                $groupedMappings[$widgetParent]['subfield_mappings'][$widgetChild] = $contentChild;
            }
        }
        
        \Log::debug('Grouped repeater mappings', [
            'grouped_mappings' => $groupedMappings
        ]);
        
        // Process each repeater field group
        foreach ($groupedMappings as $widgetParent => $mappingInfo) {
            $contentParent = $mappingInfo['content_parent'];
            $subfieldMappings = $mappingInfo['subfield_mappings'];
            
            // Extract repeater data from content item
            $repeaterData = $this->extractRepeaterData($contentItem, $contentParent);
            
            if (!empty($repeaterData) && is_array($repeaterData)) {
                // Apply subfield mappings to each repeater item
                $mappedRepeaterData = $this->mapRepeaterSubfields($contentItem, $repeaterData, $subfieldMappings, $contentParent);
                
                $widgetParentKey = strtolower(str_replace(' ', '_', $widgetParent));
                $data[$widgetParentKey] = $mappedRepeaterData;
                
                \Log::debug('Processed repeater field', [
                    'widget_parent' => $widgetParentKey,
                    'content_parent' => $contentParent,
                    'item_count' => count($mappedRepeaterData)
                ]);
            }
        }
        
        return $data;
    }
    
    /**
     * Extract repeater data from a content item field
     *
     * @param ContentItem $contentItem
     * @param string $fieldName
     * @return array
     */
    protected function extractRepeaterData(ContentItem $contentItem, string $fieldName): array
    {
        // Find the field value by field name/slug
        $fieldValue = $contentItem->fieldValues->filter(function ($fv) use ($fieldName) {
            return $fv->field && (
                $fv->field->slug === $fieldName || 
                $fv->field->name === $fieldName
            );
        })->first();
        
        if (!$fieldValue) {
            \Log::debug('Repeater field not found', ['field_name' => $fieldName]);
            return [];
        }
        
        $value = $fieldValue->getFormattedValue();
        
        // Handle different data formats
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                \Log::warning('Failed to decode repeater JSON', [
                    'field_name' => $fieldName,
                    'value' => $value,
                    'json_error' => json_last_error_msg()
                ]);
                return [];
            }
        }
        
        if (!is_array($value)) {
            \Log::debug('Repeater value is not an array', [
                'field_name' => $fieldName,
                'value_type' => gettype($value)
            ]);
            return [];
        }
        
        \Log::debug('Extracted repeater data', [
            'field_name' => $fieldName,
            'item_count' => count($value),
            'sample_item' => !empty($value) ? array_keys($value[0] ?? []) : []
        ]);
        
        return $value;
    }
    
    /**
     * Apply subfield mappings to repeater items
     *
     * @param ContentItem $contentItem
     * @param array $repeaterData
     * @param array $subfieldMappings
     * @param string $contentParentField
     * @return array
     */
    protected function mapRepeaterSubfields(ContentItem $contentItem, array $repeaterData, array $subfieldMappings, string $contentParentField): array
    {
        $mappedData = [];
        
        foreach ($repeaterData as $index => $item) {
            if (!is_array($item)) {
                \Log::warning('Repeater item is not an array', [
                    'index' => $index,
                    'item_type' => gettype($item)
                ]);
                continue;
            }
            
            $mappedItem = [];
            
            // Apply each subfield mapping
            foreach ($subfieldMappings as $widgetSubfield => $contentSubfield) {
                if (isset($item[$contentSubfield])) {
                    $value = $item[$contentSubfield];
                    
                    // Handle image fields within repeaters
                    if ($this->isImageField($contentItem, $contentParentField, $contentSubfield)) {
                        $value = $this->getRepeaterImageUrl($contentItem, $contentParentField, $index, $contentSubfield, $value);
                    }
                    
                    $mappedItem[$widgetSubfield] = $value;
                    
                    \Log::debug('Mapped repeater subfield', [
                        'index' => $index,
                        'widget_subfield' => $widgetSubfield,
                        'content_subfield' => $contentSubfield,
                        'value_type' => gettype($value)
                    ]);
                }
            }
            
            // Add any unmapped fields from the original item
            foreach ($item as $key => $value) {
                if (!in_array($key, $subfieldMappings) && !isset($mappedItem[$key])) {
                    $mappedItem[$key] = $value;
                }
            }
            
            $mappedData[] = $mappedItem;
        }
        
        return $mappedData;
    }
    
    /**
     * Check if a subfield is an image field
     *
     * @param ContentItem $contentItem
     * @param string $parentFieldName
     * @param string $subfieldName
     * @return bool
     */
    protected function isImageField(ContentItem $contentItem, string $parentFieldName, string $subfieldName): bool
    {
        // Find the parent repeater field
        $parentField = $contentItem->contentType->fields->filter(function ($field) use ($parentFieldName) {
            return $field->slug === $parentFieldName || $field->name === $parentFieldName;
        })->first();
        
        if (!$parentField || $parentField->field_type !== 'repeater') {
            return false;
        }
        
        // Parse the repeater field settings to find subfield types
        $settings = $parentField->settings ?? [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        
        $subfields = $settings['subfields'] ?? [];
        
        foreach ($subfields as $subfield) {
            $subfieldSlug = $subfield['slug'] ?? $subfield['name'] ?? '';
            $subfieldType = $subfield['field_type'] ?? $subfield['type'] ?? '';
            
            if (($subfieldSlug === $subfieldName || $subfield['name'] === $subfieldName) && $subfieldType === 'image') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get image URL for a repeater subfield
     *
     * @param ContentItem $contentItem
     * @param string $parentFieldName
     * @param int $index
     * @param string $subfieldName
     * @param mixed $value
     * @return string|null
     */
    protected function getRepeaterImageUrl(ContentItem $contentItem, string $parentFieldName, int $index, string $subfieldName, $value): ?string
    {
        // Method 1: Check if value contains a media ID
        if (!empty($value) && is_numeric($value)) {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($value);
            if ($media) {
                \Log::debug('Found repeater image via media ID', [
                    'parent_field' => $parentFieldName,
                    'index' => $index,
                    'subfield' => $subfieldName,
                    'media_id' => $value,
                    'url' => $media->getUrl()
                ]);
                return $media->getUrl();
            }
        }
        
        // Method 2: Check Spatie Media Library collections for repeater items
        // Collection name format: field_{parent_field_id}_repeater_{index}_{subfield_name}
        $parentField = $contentItem->contentType->fields->filter(function ($field) use ($parentFieldName) {
            return $field->slug === $parentFieldName || $field->name === $parentFieldName;
        })->first();
        
        if ($parentField) {
            $collectionName = "field_{$parentField->id}_repeater_{$index}_{$subfieldName}";
            
            if ($contentItem->hasMedia($collectionName)) {
                $mediaUrl = $contentItem->getFirstMediaUrl($collectionName);
                \Log::debug('Found repeater image via Spatie collection', [
                    'collection_name' => $collectionName,
                    'url' => $mediaUrl
                ]);
                return $mediaUrl;
            }
        }
        
        \Log::debug('No repeater image found', [
            'parent_field' => $parentFieldName,
            'index' => $index,
            'subfield' => $subfieldName,
            'value' => $value
        ]);
        
        return null;
    }
    
    /**
     * Add direct field values without mappings
     *
     * @param ContentItem $contentItem
     * @param array &$data
     */
    protected function addDirectFieldValues(ContentItem $contentItem, array &$data): void
    {
        foreach ($contentItem->fieldValues as $fieldValue) {
            if ($fieldValue->field) {
                $contentFieldSlug = $fieldValue->field->slug;
                $contentFieldType = $fieldValue->field->field_type;
                $value = $fieldValue->getFormattedValue();
                
                // Special handling for image fields
                if ($contentFieldType === 'image') {
                    $value = $this->getImageUrlForField($contentItem, $fieldValue, $value);
                }
                // Special handling for repeater fields
                elseif ($contentFieldType === 'repeater') {
                    $value = $this->processDirectRepeaterField($contentItem, $fieldValue, $value);
                }
                
                $data[$contentFieldSlug] = $value;
                
                \Log::debug('Added direct field value', [
                    'field_slug' => $contentFieldSlug,
                    'field_type' => $contentFieldType,
                    'value_type' => gettype($value)
                ]);
            }
        }
    }
    
    /**
     * Process repeater field without mappings (direct access)
     *
     * @param ContentItem $contentItem
     * @param ContentFieldValue $fieldValue
     * @param mixed $value
     * @return array
     */
    protected function processDirectRepeaterField(ContentItem $contentItem, $fieldValue, $value): array
    {
        // Ensure we have array data
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return [];
            }
        }
        
        if (!is_array($value)) {
            return [];
        }
        
        $parentFieldName = $fieldValue->field->slug;
        
        \Log::debug('Processing direct repeater field', [
            'parent_field' => $parentFieldName,
            'items_count' => count($value),
            'sample_item_keys' => !empty($value) ? array_keys($value[0] ?? []) : []
        ]);
        
        // Process each repeater item to handle image subfields
        foreach ($value as $index => &$item) {
            if (!is_array($item)) {
                continue;
            }
            
            \Log::debug('Processing repeater item', [
                'parent_field' => $parentFieldName,
                'index' => $index,
                'item_keys' => array_keys($item)
            ]);
            
            foreach ($item as $subfieldName => &$subfieldValue) {
                $isImage = $this->isImageField($contentItem, $parentFieldName, $subfieldName);
                
                \Log::debug('Checking subfield', [
                    'parent_field' => $parentFieldName,
                    'index' => $index,
                    'subfield_name' => $subfieldName,
                    'original_value' => $subfieldValue,
                    'is_image_field' => $isImage
                ]);
                
                if ($isImage) {
                    $originalValue = $subfieldValue;
                    $subfieldValue = $this->getRepeaterImageUrl($contentItem, $parentFieldName, $index, $subfieldName, $subfieldValue);
                    
                    \Log::debug('Processed image subfield', [
                        'parent_field' => $parentFieldName,
                        'index' => $index,
                        'subfield_name' => $subfieldName,
                        'original_value' => $originalValue,
                        'resolved_url' => $subfieldValue
                    ]);
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Get content field value by field name/slug
     *
     * @param ContentItem $contentItem
     * @param string $fieldName
     * @return mixed
     */
    protected function getContentFieldValue(ContentItem $contentItem, string $fieldName)
    {
        $fieldValue = $contentItem->fieldValues->filter(function ($fv) use ($fieldName) {
            return $fv->field && (
                $fv->field->slug === $fieldName || 
                $fv->field->name === $fieldName
            );
        })->first();
        
        if (!$fieldValue) {
            return null;
        }
        
        $value = $fieldValue->getFormattedValue();
        
        // Special handling for image fields
        if ($fieldValue->field->field_type === 'image') {
            return $this->getImageUrlForField($contentItem, $fieldValue, $value);
        }
        
        return $value;
    }
    
    /**
     * Get image URL for a field, checking both ContentFieldValue and Spatie Media attachments
     *
     * @param ContentItem $contentItem
     * @param ContentFieldValue $fieldValue
     * @param mixed $value
     * @return string|null
     */
    protected function getImageUrlForField(ContentItem $contentItem, $fieldValue, $value): ?string
    {
        // Method 1: Check if value contains a media ID (media picker approach)
        if (!empty($value) && is_numeric($value)) {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($value);
            if ($media) {
                \Log::debug('Found image via media ID reference', [
                    'media_id' => $value,
                    'url' => $media->getUrl()
                ]);
                return $media->getUrl();
            }
        }
        
        // Method 2: Check Spatie Media Library collections (direct upload approach)
        $fieldId = $fieldValue->content_type_field_id;
        $collectionName = 'field_' . $fieldId;
        
        if ($contentItem->hasMedia($collectionName)) {
            $mediaUrl = $contentItem->getFirstMediaUrl($collectionName);
            \Log::debug('Found image via Spatie Media collection', [
                'collection_name' => $collectionName,
                'url' => $mediaUrl
            ]);
            return $mediaUrl;
        }
        
        // Method 3: Check generic image collections
        if ($contentItem->hasMedia('images')) {
            $mediaUrl = $contentItem->getFirstMediaUrl('images');
            \Log::debug('Found image via generic images collection', [
                'url' => $mediaUrl
            ]);
            return $mediaUrl;
        }
    
    \Log::debug('No image found for field', [
        'field_id' => $fieldId,
        'collection_name' => $collectionName,
        'value' => $value
    ]);
    
    return null;
}

/**
 * Format a field value based on its type
 *
 * @param mixed $value
 * @param string $fieldType
 * @return mixed
 */
protected function formatFieldValue($value, string $fieldType)
{
    switch ($fieldType) {
        case 'boolean':
            return (bool) $value;
        case 'number':
        case 'integer':
            return is_numeric($value) ? (int) $value : 0;
        case 'image':
            // Convert media ID to URL if it's numeric
            if (is_numeric($value) && $value > 0) {
                return $this->convertMediaIdToUrl($value);
            }
            // If it's already a URL or path, return as is
            return $value;
            case 'number':
            case 'integer':
                return is_numeric($value) ? (int) $value : 0;
            case 'image':
                // Convert media ID to URL if it's numeric
                if (is_numeric($value) && $value > 0) {
                    return $this->convertMediaIdToUrl($value);
                }
                // If it's already a URL or path, return as is
                return $value;
            case 'json':
                if (is_string($value)) {
                    return json_decode($value, true) ?: [];
                }
                return is_array($value) ? $value : [];
            case 'array':
                if (is_string($value)) {
                    return explode(',', $value);
                }
                return is_array($value) ? $value : [];
            case 'repeater':
                if (is_string($value)) {
                    return json_decode($value, true) ?: [];
                }
                return is_array($value) ? $value : [];
            default:
                return $value;
        }
    }
    
    /**
     * Convert media ID to URL
     *
     * @param int|string $mediaId
     * @return string
     */
    protected function convertMediaIdToUrl($mediaId): string
    {
        if (empty($mediaId) || !is_numeric($mediaId)) {
            return '';
        }
        
        try {
            // Try to find the media record
            $media = \App\Models\Media::find($mediaId);
            if ($media && $media->file_path) {
                // Return the full URL to the media file
                return asset('storage/' . $media->file_path);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to convert media ID to URL', [
                'media_id' => $mediaId,
                'error' => $e->getMessage()
            ]);
        }
        
        return '';
    }
    
    /**
     * Resolve the view path for a widget
     *
     * @param Widget $widget
     * @return string
     */
    public function resolveWidgetViewPath(Widget $widget): string
    {
        $theme = $widget->theme;
        
        if (!$theme) {
            \Log::warning('Widget has no theme', ['widget_id' => $widget->id]);
            return 'widgets.default';
        }
        
        // Ensure theme namespace is registered
        $this->ensureThemeNamespaceIsRegistered($theme);
        
        // Try theme-specific widget view with namespace
        $themeWidgetView = "theme::widgets.{$widget->slug}.view";
        
        if (View::exists($themeWidgetView)) {
            \Log::debug('Using theme widget view', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'view_path' => $themeWidgetView
            ]);
            return $themeWidgetView;
        }
        
        // Try fallback to theme default widget
        $themeDefaultView = 'theme::widgets.default.view';
        if (View::exists($themeDefaultView)) {
            \Log::debug('Using theme default widget view', [
                'widget_id' => $widget->id,
                'view_path' => $themeDefaultView
            ]);
            return $themeDefaultView;
        }
        
        // Final fallback to system default
        \Log::warning('Widget view not found, using system default', [
            'widget_id' => $widget->id,
            'widget_slug' => $widget->slug,
            'theme_slug' => $theme->slug,
            'attempted_view' => $themeWidgetView
        ]);
        
        return 'widgets.default';
    }
    
    /**
     * Ensure theme namespace is registered
     *
     * @param Theme $theme
     * @return void
     */
    protected function ensureThemeNamespaceIsRegistered($theme): void
    {
        if (!$theme) {
            return;
        }
        
        $themePath = resource_path('themes/' . $theme->slug);
        
        if (is_dir($themePath)) {
            // Check if namespace is already registered by trying to resolve a view
            if (!View::exists('theme::widgets.test')) {
                \Log::debug("Registering theme namespace for {$theme->slug}");
                View::addNamespace('theme', $themePath);
            }
        }
    }
    
    /**
     * Check if a widget is compatible with a content type
     * 
     * @param Widget $widget
     * @param mixed $contentType
     * @return array
     */
    public function checkWidgetContentCompatibility($widget, $contentType): array
    {
        return $this->compatibilityService->checkCompatibility($widget, $contentType);
    }
    
    /**
     * Generate field mappings for a widget and content type
     * 
     * @param Widget $widget
     * @param mixed $contentType
     * @return array
     */
    public function generateWidgetFieldMappings($widget, $contentType): array
    {
        return $this->compatibilityService->generateFieldMappings($widget, $contentType);
    }
    
    /**
     * Check if a view exists
     *
     * @param string $view
     * @return bool
     */
    protected function viewExists(string $view): bool
    {
        return View::exists($view);
    }
    
    /**
     * Get widget content from database based on content type association
     *
     * @param Widget $widget
     * @param array $options Additional options for content fetching
     * @return array
     */
    protected function getWidgetContent(Widget $widget, array $options = []): array
    {
        try {
            // Use the specialized content fetch service
            $collection = $this->contentFetchService->getContentForWidget($widget, $options);
            
            // Convert to array format for backwards compatibility
            if ($collection) {
                return $collection->toArray();
            }
            
            return [];
            
        } catch (\Exception $e) {
            Log::error('Error fetching widget content: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug
            ]);
            
            // Fallback to legacy behavior in case of errors
            return $this->getWidgetContentLegacy($widget);
        }
    }
    
    /**
     * Legacy method for getting widget content (for backward compatibility)
     *
     * @param Widget $widget
     * @return array
     */
    protected function getWidgetContentLegacy(Widget $widget): array
    {
        $contentData = [];
        
        // Check if widget has content type association
        if (!$widget->contentTypeAssociations()->where('is_active', true)->exists()) {
            return $contentData;
        }
        
        $association = $widget->contentTypeAssociations()->where('is_active', true)->first();
        $contentType = $association->contentType;
        $contentTable = $contentType->table_name;
        
        // Different handling based on widget slug
        switch ($widget->slug) {
            case 'post-list':
                // For post lists, fetch multiple items
                $limit = $association->options['limit'] ?? 10;
                $contentData = DB::table($contentTable)
                    ->select('*')
                    ->limit($limit)
                    ->get()
                    ->toArray();
                break;
                
            case 'page-header':
            case 'featured-post':
            case 'hero-banner':
                // For single-item widgets, fetch first item
                $contentItem = DB::table($contentTable)
                    ->first();
                    
                if ($contentItem) {
                    $contentData[] = (array)$contentItem;
                }
                break;
                
            default:
                // Default behavior - fetch all items
                $contentData = DB::table($contentTable)
                    ->get()
                    ->toArray();
                break;
        }
        
        return $contentData;
    }
    
    /**
     * Fetch content from database based on widget content type associations
     *
     * @param Widget $widget
     * @return array
     */
    protected function fetchContentFromDatabase(Widget $widget): array
    {
        $content = [];
        
        // Get content type associations for this widget
        $associations = $widget->contentTypeAssociations()->with('contentType')->get();
        
        // Debug info
        $debugData = [
            'widget_id' => $widget->id,
            'widget_name' => $widget->name,
            'widget_slug' => $widget->slug,
            'association_count' => $associations->count()
        ];
        
        // Log to the Laravel debug file
        \Log::debug('Widget content debug info', $debugData);
        
        if ($associations->isEmpty()) {
            return $content;
        }
        
        // Process each content type association
        foreach ($associations as $association) {
            $contentType = $association->contentType;
            
            if (!$contentType) {
                \Log::warning('Content type not found for association', [
                    'widget_id' => $widget->id,
                    'association_id' => $association->id,
                    'content_type_id' => $association->content_type_id
                ]);
                continue;
            }
            
            // Different content handling based on widget slug
            switch ($widget->slug) {
                case 'post-list':
                    $content = $this->fetchPostListContent($contentType, $association);
                    break;
                
                case 'page-header':
                case 'hero-header':
                    $content = $this->fetchHeaderContent($contentType, $association);
                    break;
                    
                case 'contact-form':
                    $content = $this->fetchContactContent($contentType, $association);
                    break;
                    
                default:
                    // Generic content fetch
                    $content[$contentType->slug] = $this->fetchGenericContent($contentType, $association);
                    break;
            }
            
            // Log content result
            \Log::debug('Content fetch result', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'content_type' => $contentType->slug,
                'content_found' => !empty($content)
            ]);
        }
        
        return $content;
    }
    
    /**
     * Fetch post list content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchPostListContent($contentType, $association): array
    {
        // Using DB facade since we're in early implementation phase
        // In a full implementation, we'd use proper models and repositories
        $posts = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy($association->sort_field ?: 'published_at', $association->getSortDirection())
            ->limit($association->limit ?: 5)
            ->get();
        
        if ($posts->isEmpty()) {
            return [];
        }
        
        $formattedPosts = [];
        
        foreach ($posts as $post) {
            // Get field values for this post
            $fieldValues = \DB::table('content_field_values')
                ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
                ->where('content_field_values.content_item_id', $post->id)
                ->select('content_type_fields.slug', 'content_field_values.value')
                ->get()
                ->keyBy('slug')
                ->map(function ($item) {
                    return $item->value;
                })
                ->toArray();
            
            // Format the post data
            $formattedPosts[] = [
                'id' => $post->id,
                'title' => $post->title,
                'subtitle' => $fieldValues['subtitle'] ?? '',
                'excerpt' => $fieldValues['content'] ? $this->createExcerpt($fieldValues['content']) : '',
                'url' => $fieldValues['url'] ?? '/content/' . $post->slug,
                'created_at' => $post->created_at,
                'author' => ['name' => $fieldValues['author_name'] ?? 'Admin']
            ];
        }
        
        return [
            'posts' => $formattedPosts,
            'category' => null,
            'total_count' => count($formattedPosts)
        ];
    }
    
    /**
     * Fetch header content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchHeaderContent($contentType, $association): array
    {
        // Get the latest header content
        $header = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$header) {
            return [];
        }
        
        // Get field values for this header
        $fieldValues = \DB::table('content_field_values')
            ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
            ->where('content_field_values.content_item_id', $header->id)
            ->select('content_type_fields.slug', 'content_field_values.value')
            ->get()
            ->keyBy('slug')
            ->map(function ($item) {
                return $item->value;
            })
            ->toArray();
        
        return [
            'header' => [
                'title' => $header->title,
                'subtitle' => $fieldValues['subtitle'] ?? '',
                'background' => $fieldValues['background'] ?? '',
                'cta_text' => $fieldValues['cta_text'] ?? '',
                'cta_url' => $fieldValues['cta_url'] ?? ''
            ]
        ];
    }
    
    /**
     * Fetch contact content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchContactContent($contentType, $association): array
    {
        // Get the latest contact settings
        $contact = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$contact) {
            return [];
        }
        
        // Get field values for this contact
        $fieldValues = \DB::table('content_field_values')
            ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
            ->where('content_field_values.content_item_id', $contact->id)
            ->select('content_type_fields.slug', 'content_field_values.value')
            ->get()
            ->keyBy('slug')
            ->map(function ($item) {
                return $item->value;
            })
            ->toArray();
        
        return [
            'contact' => [
                'email' => $fieldValues['email'] ?? '',
                'phone' => $fieldValues['phone'] ?? '',
                'address' => $fieldValues['address'] ?? '',
                'form_recipient' => $fieldValues['form_recipient'] ?? '',
                'show_map' => filter_var($fieldValues['show_map'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'map_location' => $fieldValues['map_location'] ?? ''
            ]
        ];
    }
    
    /**
     * Fetch generic content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchGenericContent($contentType, $association): array
    {
        // Get content items based on association settings
        $items = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy($association->sort_field ?: 'created_at', $association->getSortDirection())
            ->limit($association->limit ?: 10)
            ->get();
        
        if ($items->isEmpty()) {
            return [];
        }
        
        $formattedItems = [];
        
        foreach ($items as $item) {
            // Get field values for this item
            $fieldValues = \DB::table('content_field_values')
                ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
                ->where('content_field_values.content_item_id', $item->id)
                ->select('content_type_fields.slug', 'content_field_values.value')
                ->get()
                ->keyBy('slug')
                ->map(function ($item) {
                    return $item->value;
                })
                ->toArray();
            
            // Add base item data
            $itemData = [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'url' => '/content/' . $contentType->slug . '/' . $item->slug,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'published_at' => $item->published_at,
            ];
            
            // Add all field values
            foreach ($fieldValues as $key => $value) {
                $itemData[$key] = $value;
            }
            
            $formattedItems[] = $itemData;
        }
        
        return $formattedItems;
    }
    
    /**
     * Create excerpt from HTML content
     *
     * @param string $content
     * @param int $length
     * @return string
     */
    protected function createExcerpt(string $content, int $length = 150): string
    {
        // Remove HTML tags
        $text = strip_tags($content);
        
        // Trim to length
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }
        
        return $text;
    }
    
    /**
     * Get default content for post-list widget
     *
     * @return array
     */
    protected function getDefaultPostListContent(): array
    {
        // Create sample post data
        $now = now();
        $yesterday = $now->copy()->subDay();
        $twoDaysAgo = $now->copy()->subDays(2);
        
        return [
            'posts' => [
                [
                    'id' => 1,
                    'title' => 'Getting Started with Laravel CMS',
                    'subtitle' => 'A beginner\'s guide to our CMS',
                    'excerpt' => 'Learn how to quickly set up and customize your first website using our powerful Laravel-based CMS platform.',
                    'url' => '/blog/getting-started',
                    'created_at' => $twoDaysAgo->toDateTimeString(),
                    'author' => ['name' => 'Admin']
                ],
                [
                    'id' => 2,
                    'title' => 'Working with Widgets',
                    'subtitle' => 'Create dynamic content with widgets',
                    'excerpt' => 'Widgets are a powerful way to add dynamic content to your pages. This guide explains how to use and customize them.',
                    'url' => '/blog/widgets-guide',
                    'created_at' => $yesterday->toDateTimeString(),
                    'author' => ['name' => 'Editor']
                ],
                [
                    'id' => 3,
                    'title' => 'Theming Your Website',
                    'subtitle' => 'Create beautiful custom themes',
                    'excerpt' => 'Learn how to create custom themes for your website, including templates, asset management, and responsive design principles.',
                    'url' => '/blog/theming',
                    'created_at' => $now->toDateTimeString(),
                    'author' => ['name' => 'Designer']
                ],
                [
                    'id' => 4,
                    'title' => 'Advanced Content Management',
                    'subtitle' => 'Taking your content to the next level',
                    'excerpt' => 'Discover advanced techniques for content management, including custom content types, taxonomies, and content relationships.',
                    'url' => '/blog/advanced-content',
                    'created_at' => $now->toDateTimeString(),
                    'author' => ['name' => 'Developer']
                ],
                [
                    'id' => 5,
                    'title' => 'SEO Best Practices',
                    'subtitle' => 'Optimize your site for search engines',
                    'excerpt' => 'Implement SEO best practices to improve your website\'s visibility in search engines and attract more visitors.',
                    'url' => '/blog/seo-practices',
                    'created_at' => $now->toDateTimeString(),
                    'author' => ['name' => 'Marketing']
                ],
            ],
            'category' => [
                'id' => 1,
                'name' => 'Tutorials',
                'slug' => 'tutorials',
                'url' => '/blog/category/tutorials'
            ],
            'total_count' => 5
        ];
    }
    
    /**
     * Get default content for page-header widget
     *
     * @return array
     */
    protected function getDefaultHeaderContent(): array
    {
        return [
            'header' => [
                'title' => 'Welcome to RealSys CMS',
                'subtitle' => 'A modern, flexible content management system',
                'background' => 'assets/img/home-bg.jpg',
                'cta_text' => 'Learn More',
                'cta_url' => '/about'
            ]
        ];
    }
    
    /**
     * Get default content for contact-form widget
     *
     * @return array
     */
    protected function getDefaultContactContent(): array
    {
        return [
            'contact' => [
                'email' => 'contact@example.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Main Street, Anytown, AN 12345',
                'form_recipient' => 'info@example.com',
                'show_map' => true,
                'map_location' => '40.7128,-74.0060'
            ]
        ];
    }
    
    /**
     * Collect widget assets (CSS and JS files)
     *
     * @param Widget $widget
     * @return array
     */
    public function collectWidgetAssets(Widget $widget): array
    {
        $assets = [
            'css' => [],
            'js' => []
        ];
        
        if (!$widget->theme) {
            return $assets;
        }
        
        $themeSlug = $widget->theme->slug;
        $widgetSlug = $widget->slug;
        
        // Check for custom CSS file
        $cssPath = public_path("themes/{$themeSlug}/css/widgets/{$widgetSlug}-custom.css");
        if (file_exists($cssPath)) {
            $assets['css'][] = asset("themes/{$themeSlug}/css/widgets/{$widgetSlug}-custom.css");
        }
        
        // Check for custom JS file
        $jsPath = public_path("themes/{$themeSlug}/js/widgets/{$widgetSlug}-custom.js");
        if (file_exists($jsPath)) {
            $assets['js'][] = asset("themes/{$themeSlug}/js/widgets/{$widgetSlug}-custom.js");
        }
        
        return $assets;
    }
    
    /**
     * Collect all assets for widgets used on a page
     *
     * @param array $sections Array of page sections with widgets
     * @return array
     */
    public function collectPageWidgetAssets(array $sections): array
    {
        $pageAssets = [
            'css' => [],
            'js' => []
        ];
        
        foreach ($sections as $section) {
            if (isset($section['widgets']) && is_array($section['widgets'])) {
                foreach ($section['widgets'] as $widgetData) {
                    if (isset($widgetData['assets'])) {
                        // Merge CSS assets (avoid duplicates)
                        if (isset($widgetData['assets']['css'])) {
                            foreach ($widgetData['assets']['css'] as $css) {
                                if (!in_array($css, $pageAssets['css'])) {
                                    $pageAssets['css'][] = $css;
                                }
                            }
                        }
                        
                        // Merge JS assets (avoid duplicates)
                        if (isset($widgetData['assets']['js'])) {
                            foreach ($widgetData['assets']['js'] as $js) {
                                if (!in_array($js, $pageAssets['js'])) {
                                    $pageAssets['js'][] = $js;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $pageAssets;
    }
}
