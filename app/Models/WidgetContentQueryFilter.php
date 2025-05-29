<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetContentQueryFilter extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'query_id',
        'field_id',
        'field_key',
        'operator',
        'value',
        'condition_group'
    ];

    /**
     * Get the query that owns this filter.
     */
    public function query()
    {
        return $this->belongsTo(WidgetContentQuery::class, 'query_id');
    }

    /**
     * Get the content type field this filter refers to.
     */
    public function field()
    {
        return $this->belongsTo(ContentTypeField::class, 'field_id');
    }

    /**
     * Apply this filter to a query builder instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyToQuery($query)
    {
        // If we have a field_id, we need to look up the field values
        if ($this->field_id) {
            return $this->applyFieldFilter($query);
        }
        
        // Otherwise, we're filtering on a built-in property like title, status, etc.
        return $this->applyPropertyFilter($query);
    }

    /**
     * Apply a filter for a content type field.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFieldFilter($query)
    {
        // Create a subquery to find content items with matching field values
        return $query->whereHas('fieldValues', function ($subquery) {
            $subquery->where('field_id', $this->field_id);
            
            switch ($this->operator) {
                case 'equals':
                    $subquery->where('value', $this->value);
                    break;
                case 'not_equals':
                    $subquery->where('value', '!=', $this->value);
                    break;
                case 'contains':
                    $subquery->where('value', 'like', '%' . $this->value . '%');
                    break;
                case 'starts_with':
                    $subquery->where('value', 'like', $this->value . '%');
                    break;
                case 'ends_with':
                    $subquery->where('value', 'like', '%' . $this->value);
                    break;
                case 'greater_than':
                    $subquery->where('value', '>', $this->value);
                    break;
                case 'less_than':
                    $subquery->where('value', '<', $this->value);
                    break;
                case 'in':
                    // For a comma-separated list of values
                    $values = explode(',', $this->value);
                    $subquery->whereIn('value', $values);
                    break;
                case 'not_in':
                    // For a comma-separated list of values
                    $values = explode(',', $this->value);
                    $subquery->whereNotIn('value', $values);
                    break;
                case 'is_null':
                    $subquery->whereNull('value')->orWhere('value', '');
                    break;
                case 'is_not_null':
                    $subquery->whereNotNull('value')->where('value', '!=', '');
                    break;
                default:
                    // Default to equals if operator not recognized
                    $subquery->where('value', $this->value);
            }
        });
    }

    /**
     * Apply a filter for a built-in property.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyPropertyFilter($query)
    {
        $fieldKey = $this->field_key ?? 'title'; // Default to title if no field_key is specified
        
        switch ($this->operator) {
            case 'equals':
                return $query->where($fieldKey, $this->value);
            case 'not_equals':
                return $query->where($fieldKey, '!=', $this->value);
            case 'contains':
                return $query->where($fieldKey, 'like', '%' . $this->value . '%');
            case 'starts_with':
                return $query->where($fieldKey, 'like', $this->value . '%');
            case 'ends_with':
                return $query->where($fieldKey, 'like', '%' . $this->value);
            case 'greater_than':
                return $query->where($fieldKey, '>', $this->value);
            case 'less_than':
                return $query->where($fieldKey, '<', $this->value);
            case 'in':
                // For a comma-separated list of values
                $values = explode(',', $this->value);
                return $query->whereIn($fieldKey, $values);
            case 'not_in':
                // For a comma-separated list of values
                $values = explode(',', $this->value);
                return $query->whereNotIn($fieldKey, $values);
            case 'is_null':
                return $query->whereNull($fieldKey);
            case 'is_not_null':
                return $query->whereNotNull($fieldKey);
            default:
                // Default to equals if operator not recognized
                return $query->where($fieldKey, $this->value);
        }
    }
}
