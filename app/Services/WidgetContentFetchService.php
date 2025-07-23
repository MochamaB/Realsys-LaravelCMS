<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\ContentType;
use App\Models\WidgetContentTypeAssociation;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class WidgetContentFetchService
{
    /**
     * Get content items for a widget based on associations
     * 
     * @param Widget $widget
     * @param array $filters Optional filters
     * @return Collection Content items
     */
    public function getContentForWidget(Widget $widget, array $filters = [])
    {
        // Get active association
        $association = WidgetContentTypeAssociation::where('widget_id', $widget->id)
            ->where('is_active', true)
            ->with('contentType')
            ->first();
            
        if (!$association) {
            return collect([]);
        }
        
        $contentType = $association->contentType;
        $contentModel = $this->getContentModel($contentType);
        
        if (!$contentModel) {
            return collect([]);
        }
        
        // Apply filters from widget association options
        $query = $contentModel->newQuery();
        
        // Add base filters from the association options
        $this->applyBaseFilters($query, $association->options ?? []);
        
        // Add runtime filters
        $this->applyRuntimeFilters($query, $filters, $association->options ?? []);
        
        // Get the content
        $content = $query->get();
        
        // Map content to widget fields
        return $content->map(function ($item) use ($widget, $contentType, $association) {
            return $this->mapContentToWidgetFields($widget, $contentType, $item, $association->field_mappings);
        });
    }
    
    /**
     * Map content fields to widget fields
     * 
     * @param Widget $widget
     * @param ContentType $contentType
     * @param mixed $contentItem Content model instance
     * @param array $fieldMappings Field mappings from the association
     * @return array Mapped data for the widget
     */
    public function mapContentToWidgetFields(Widget $widget, ContentType $contentType, $contentItem, array $fieldMappings)
    {
        $result = [];
        $contentData = $contentItem->toArray();
        
        foreach ($fieldMappings as $widgetField => $contentField) {
            // Check if this is a nested field (contains a dot)
            if (strpos($widgetField, '.') !== false) {
                // This is a nested field, handle it separately
                $this->handleNestedField($result, $widgetField, $contentField, $contentData);
            } else {
                // Direct field mapping
                $result[$widgetField] = $this->getContentValue($contentItem, $contentField);
            }
        }
        
        return $result;
    }
    
    /**
     * Handle nested field mapping (for repeaters)
     * 
     * @param array &$result Reference to the result array being built
     * @param string $widgetField Widget field path (e.g. 'team_members.name')
     * @param string $contentField Content field path (e.g. 'staff_list.full_name')
     * @param array $contentData Content item data
     */
    protected function handleNestedField(&$result, $widgetField, $contentField, $contentData)
    {
        // Split the paths
        list($widgetParent, $widgetChild) = $this->splitFieldPath($widgetField);
        list($contentParent, $contentChild) = $this->splitFieldPath($contentField);
        
        // Make sure parent arrays are initialized
        if (!isset($result[$widgetParent])) {
            $result[$widgetParent] = [];
        }
        
        // Get the content repeater data
        $contentRepeaterData = Arr::get($contentData, $contentParent, []);
        
        // Process each item in the repeater
        if (is_array($contentRepeaterData)) {
            foreach ($contentRepeaterData as $index => $item) {
                if (!isset($result[$widgetParent][$index])) {
                    $result[$widgetParent][$index] = [];
                }
                
                // Set the nested value
                $result[$widgetParent][$index][$widgetChild] = Arr::get($item, $contentChild);
            }
        }
    }
    
    /**
     * Split a field path into parent and child parts
     * 
     * @param string $path Field path (e.g. 'team_members.name')
     * @return array [parent, child]
     */
    protected function splitFieldPath($path)
    {
        $parts = explode('.', $path, 2);
        return count($parts) === 2 ? $parts : [$parts[0], ''];
    }
    
    /**
     * Get value from content item, supporting dot notation
     * 
     * @param mixed $contentItem
     * @param string $field Field name with potential dot notation
     * @return mixed Field value
     */
    protected function getContentValue($contentItem, $field)
    {
        // If simple field, use direct access
        if (strpos($field, '.') === false) {
            return $contentItem->{$field} ?? null;
        }
        
        // For dot notation, use array get on the item as array
        return Arr::get($contentItem->toArray(), $field);
    }
    
    /**
     * Apply base filters from association options
     * 
     * @param Builder $query
     * @param array $options
     */
    protected function applyBaseFilters(Builder $query, array $options)
    {
        // Apply any defined filters from the association options
        if (isset($options['filters']) && is_array($options['filters'])) {
            foreach ($options['filters'] as $filter) {
                $this->applyFilterToQuery($query, $filter);
            }
        }
        
        // Apply sorting
        if (isset($options['sort_by'])) {
            $direction = $options['sort_direction'] ?? 'asc';
            $query->orderBy($options['sort_by'], $direction);
        }
        
        // Apply default limit
        if (isset($options['limit'])) {
            $query->limit($options['limit']);
        }
    }
    
    /**
     * Apply runtime filters on top of base filters
     * 
     * @param Builder $query
     * @param array $filters Runtime filters
     * @param array $options Association options
     */
    protected function applyRuntimeFilters(Builder $query, array $filters, array $options)
    {
        // Apply runtime limit (overrides association limit)
        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }
        
        // Apply runtime sorting (overrides association sorting)
        if (isset($filters['sort_by'])) {
            $direction = $filters['sort_direction'] ?? 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        }
        
        // Apply additional where clauses
        if (isset($filters['where']) && is_array($filters['where'])) {
            foreach ($filters['where'] as $key => $value) {
                $query->where($key, $value);
            }
        }
        
        // Apply search if provided
        if (isset($filters['search']) && isset($options['searchable_fields'])) {
            $query->where(function($q) use ($filters, $options) {
                $searchTerm = $filters['search'];
                $firstField = true;
                
                foreach ($options['searchable_fields'] as $field) {
                    if ($firstField) {
                        $q->where($field, 'LIKE', "%{$searchTerm}%");
                        $firstField = false;
                    } else {
                        $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                    }
                }
            });
        }
    }
    
    /**
     * Apply a single filter to the query
     * 
     * @param Builder $query
     * @param array $filter Filter definition
     */
    protected function applyFilterToQuery(Builder $query, array $filter)
    {
        if (!isset($filter['field']) || !isset($filter['operator'])) {
            return;
        }
        
        $field = $filter['field'];
        $operator = strtolower($filter['operator']);
        $value = $filter['value'] ?? null;
        
        switch ($operator) {
            case 'equals':
            case '=':
                $query->where($field, $value);
                break;
                
            case 'not':
            case '!=':
                $query->where($field, '!=', $value);
                break;
                
            case '>':
            case 'greater':
                $query->where($field, '>', $value);
                break;
                
            case '<':
            case 'less':
                $query->where($field, '<', $value);
                break;
                
            case '>=':
                $query->where($field, '>=', $value);
                break;
                
            case '<=':
                $query->where($field, '<=', $value);
                break;
                
            case 'like':
                $query->where($field, 'LIKE', "%{$value}%");
                break;
                
            case 'in':
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                }
                break;
                
            case 'not in':
                if (is_array($value)) {
                    $query->whereNotIn($field, $value);
                }
                break;
                
            case 'null':
                $query->whereNull($field);
                break;
                
            case 'not null':
                $query->whereNotNull($field);
                break;
                
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween($field, $value);
                }
                break;
        }
    }
    
    /**
     * Get the appropriate model class for a content type
     * 
     * @param ContentType $contentType
     * @return mixed Model class or null if not found
     */
    protected function getContentModel(ContentType $contentType)
    {
        // In our system, all content types use the ContentItem model
        // The content type differentiation happens via the content_type_id field
        return new \App\Models\ContentItem();
    }
    
    /**
     * Handle special mapping for repeater fields
     * 
     * @param array $repeaterMapping
     * @param array $contentRepeaterData
     * @return array Mapped repeater data
     */
    public function mapRepeaterFields($repeaterMapping, $contentRepeaterData)
    {
        $result = [];
        
        if (!is_array($contentRepeaterData)) {
            return $result;
        }
        
        // Group the repeater mappings by parent field
        $mappingsByParent = [];
        
        foreach ($repeaterMapping as $widgetField => $contentField) {
            if (strpos($widgetField, '.') !== false) {
                list($parent, $child) = $this->splitFieldPath($widgetField);
                
                if (!isset($mappingsByParent[$parent])) {
                    $mappingsByParent[$parent] = [];
                }
                
                $mappingsByParent[$parent][$child] = $contentField;
            }
        }
        
        // Process each item in the repeater
        foreach ($contentRepeaterData as $index => $item) {
            $mappedItem = [];
            
            foreach ($mappingsByParent as $parent => $childMappings) {
                foreach ($childMappings as $childKey => $contentPath) {
                    list($contentParent, $contentChild) = $this->splitFieldPath($contentPath);
                    
                    if (isset($item[$contentChild])) {
                        $mappedItem[$childKey] = $item[$contentChild];
                    }
                }
            }
            
            $result[] = $mappedItem;
        }
        
        return $result;
    }
}
