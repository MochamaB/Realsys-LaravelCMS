# Content-Driven CMS Architecture: Models (Part 2)

This document continues the Laravel Eloquent models required to implement the content-driven CMS architecture.

## Content System Models (Continued)

### ContentFieldValue

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentFieldValue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_item_id',
        'field_id',
        'value',
    ];

    /**
     * Get the content item that owns the field value.
     */
    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the field that owns the field value.
     */
    public function field()
    {
        return $this->belongsTo(ContentTypeField::class, 'field_id');
    }

    /**
     * Get the formatted value based on field type.
     *
     * @return mixed
     */
    public function getFormattedValue()
    {
        if (!$this->field) {
            return $this->value;
        }

        switch ($this->field->type) {
            case 'date':
                return $this->value ? date('Y-m-d', strtotime($this->value)) : null;
            case 'datetime':
                return $this->value ? date('Y-m-d H:i:s', strtotime($this->value)) : null;
            case 'boolean':
                return (bool) $this->value;
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : null;
            case 'json':
                return $this->value ? json_decode($this->value, true) : null;
            default:
                return $this->value;
        }
    }
}
```

### ContentCategory

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'content_type_id',
    ];

    /**
     * Get the content type that owns the category.
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(ContentCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(ContentCategory::class, 'parent_id');
    }

    /**
     * Get the content items for the category.
     */
    public function contentItems()
    {
        return $this->belongsToMany(ContentItem::class, 'content_item_categories', 'category_id', 'content_item_id')->withTimestamps();
    }

    /**
     * Get all ancestors of the category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ancestors()
    {
        $ancestors = collect();
        $category = $this;

        while ($category->parent) {
            $category = $category->parent;
            $ancestors->push($category);
        }

        return $ancestors->reverse();
    }

    /**
     * Get all descendants of the category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }
}
```

### ContentRelationship

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentRelationship extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source_item_id',
        'target_item_id',
        'relationship_type',
        'weight',
    ];

    /**
     * Get the source content item.
     */
    public function sourceItem()
    {
        return $this->belongsTo(ContentItem::class, 'source_item_id');
    }

    /**
     * Get the target content item.
     */
    public function targetItem()
    {
        return $this->belongsTo(ContentItem::class, 'target_item_id');
    }
}
```

## Widget System Models

### WidgetContentQuery

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetContentQuery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_type_id',
        'limit',
        'offset',
        'order_by',
        'order_direction',
    ];

    /**
     * Get the content type for the query.
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the widgets that use this query.
     */
    public function widgets()
    {
        return $this->hasMany(Widget::class, 'content_query_id');
    }

    /**
     * Get the filters for the query.
     */
    public function filters()
    {
        return $this->hasMany(WidgetContentQueryFilter::class, 'query_id');
    }

    /**
     * Execute the query and get content items.
     *
     * @param bool $paginate
     * @param int|null $perPage
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute($paginate = false, $perPage = null)
    {
        if (!$this->contentType) {
            return collect();
        }

        $query = ContentItem::where('content_type_id', $this->content_type_id)
            ->with('fieldValues.field')
            ->where('status', 'published');

        // Apply filters
        foreach ($this->filters as $filter) {
            $filter->applyToQuery($query);
        }

        // Apply ordering
        if ($this->order_by) {
            $query->orderBy($this->order_by, $this->order_direction ?: 'desc');
        } else {
            $query->latest();
        }

        // Apply offset
        if ($this->offset > 0) {
            $query->offset($this->offset);
        }

        // Execute query
        if ($paginate) {
            return $query->paginate($perPage ?: $this->limit ?: 10);
        } else {
            if ($this->limit > 0) {
                $query->limit($this->limit);
            }
            return $query->get();
        }
    }
}
```

### WidgetContentQueryFilter

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetContentQueryFilter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'query_id',
        'field_id',
        'field_key',
        'operator',
        'value',
        'condition_group',
    ];

    /**
     * Get the query that owns the filter.
     */
    public function query()
    {
        return $this->belongsTo(WidgetContentQuery::class, 'query_id');
    }

    /**
     * Get the field for the filter.
     */
    public function field()
    {
        return $this->belongsTo(ContentTypeField::class, 'field_id');
    }

    /**
     * Apply the filter to a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyToQuery($query)
    {
        // If this is a direct field on ContentItem
        if (in_array($this->field_key, ['id', 'title', 'slug', 'status', 'published_at', 'created_at', 'updated_at'])) {
            return $this->applyDirectFieldFilter($query);
        }

        // If this is a custom field
        if ($this->field_id) {
            return $this->applyCustomFieldFilter($query);
        }

        return $query;
    }

    /**
     * Apply a filter on a direct field of ContentItem.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDirectFieldFilter($query)
    {
        $fieldKey = $this->field_key;
        $value = $this->value;

        switch ($this->operator) {
            case 'equals':
                return $query->where($fieldKey, $value);
            case 'not_equals':
                return $query->where($fieldKey, '!=', $value);
            case 'contains':
                return $query->where($fieldKey, 'LIKE', "%{$value}%");
            case 'starts_with':
                return $query->where($fieldKey, 'LIKE', "{$value}%");
            case 'ends_with':
                return $query->where($fieldKey, 'LIKE', "%{$value}");
            case 'greater_than':
                return $query->where($fieldKey, '>', $value);
            case 'less_than':
                return $query->where($fieldKey, '<', $value);
            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                return $query->whereIn($fieldKey, $values);
            case 'not_in':
                $values = is_array($value) ? $value : explode(',', $value);
                return $query->whereNotIn($fieldKey, $values);
            case 'is_null':
                return $query->whereNull($fieldKey);
            case 'is_not_null':
                return $query->whereNotNull($fieldKey);
            default:
                return $query;
        }
    }

    /**
     * Apply a filter on a custom field.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCustomFieldFilter($query)
    {
        $fieldId = $this->field_id;
        $value = $this->value;

        return $query->whereHas('fieldValues', function ($q) use ($fieldId, $value) {
            $q->where('field_id', $fieldId);
            
            switch ($this->operator) {
                case 'equals':
                    $q->where('value', $value);
                    break;
                case 'not_equals':
                    $q->where('value', '!=', $value);
                    break;
                case 'contains':
                    $q->where('value', 'LIKE', "%{$value}%");
                    break;
                case 'starts_with':
                    $q->where('value', 'LIKE', "{$value}%");
                    break;
                case 'ends_with':
                    $q->where('value', 'LIKE', "%{$value}");
                    break;
                case 'greater_than':
                    $q->where('value', '>', $value);
                    break;
                case 'less_than':
                    $q->where('value', '<', $value);
                    break;
                case 'in':
                    $values = is_array($value) ? $value : explode(',', $value);
                    $q->whereIn('value', $values);
                    break;
                case 'not_in':
                    $values = is_array($value) ? $value : explode(',', $value);
                    $q->whereNotIn('value', $values);
                    break;
                case 'is_null':
                    $q->whereNull('value');
                    break;
                case 'is_not_null':
                    $q->whereNotNull('value');
                    break;
            }
        });
    }
}
```
