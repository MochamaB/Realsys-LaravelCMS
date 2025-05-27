# Content-Driven CMS Architecture: Models (Part 1)

This document details the Laravel Eloquent models required to implement the content-driven CMS architecture. Each model is presented with its relationships and key methods.

## Content System Models

### ContentType

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'description',
        'is_system',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the fields for the content type.
     */
    public function fields()
    {
        return $this->hasMany(ContentTypeField::class)->orderBy('order_index');
    }

    /**
     * Get the content items for the content type.
     */
    public function contentItems()
    {
        return $this->hasMany(ContentItem::class);
    }

    /**
     * Get the categories associated with this content type.
     */
    public function categories()
    {
        return $this->hasMany(ContentCategory::class);
    }

    /**
     * Get the user that created the content type.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the content type.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get content type by key.
     *
     * @param string $key
     * @return ContentType|null
     */
    public static function findByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Check if the content type has any content items.
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->contentItems()->count() > 0;
    }
}
```

### ContentTypeField

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTypeField extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_type_id',
        'name',
        'key',
        'type',
        'required',
        'description',
        'validation_rules',
        'default_value',
        'order_index',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'required' => 'boolean',
    ];

    /**
     * Get the content type that owns the field.
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the field options for the field.
     */
    public function options()
    {
        return $this->hasMany(ContentTypeFieldOption::class, 'field_id')->orderBy('order_index');
    }

    /**
     * Get the field values for the field.
     */
    public function fieldValues()
    {
        return $this->hasMany(ContentFieldValue::class, 'field_id');
    }

    /**
     * Get the query filters that use this field.
     */
    public function queryFilters()
    {
        return $this->hasMany(WidgetContentQueryFilter::class, 'field_id');
    }

    /**
     * Get validation rules as an array.
     *
     * @return array
     */
    public function getValidationRulesArray()
    {
        if (empty($this->validation_rules)) {
            return [];
        }

        return explode('|', $this->validation_rules);
    }

    /**
     * Check if the field is of a specific type.
     *
     * @param string $type
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }
}
```

### ContentTypeFieldOption

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTypeFieldOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'field_id',
        'label',
        'value',
        'order_index',
    ];

    /**
     * Get the field that owns the option.
     */
    public function field()
    {
        return $this->belongsTo(ContentTypeField::class, 'field_id');
    }
}
```

### ContentItem

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ContentItem extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_type_id',
        'title',
        'slug',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the content type that owns the content item.
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the field values for the content item.
     */
    public function fieldValues()
    {
        return $this->hasMany(ContentFieldValue::class);
    }

    /**
     * Get the categories for the content item.
     */
    public function categories()
    {
        return $this->belongsToMany(ContentCategory::class, 'content_item_categories', 'content_item_id', 'category_id')->withTimestamps();
    }

    /**
     * Get related content items (source).
     */
    public function relatedFrom()
    {
        return $this->hasMany(ContentRelationship::class, 'source_item_id');
    }

    /**
     * Get related content items (target).
     */
    public function relatedTo()
    {
        return $this->hasMany(ContentRelationship::class, 'target_item_id');
    }

    /**
     * Get the user that created the content item.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the content item.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a field value by key.
     *
     * @param string $key
     * @return mixed
     */
    public function getFieldValue($key)
    {
        $field = $this->contentType->fields()->where('key', $key)->first();
        
        if (!$field) {
            return null;
        }
        
        $fieldValue = $this->fieldValues()->where('field_id', $field->id)->first();
        
        return $fieldValue ? $fieldValue->value : null;
    }

    /**
     * Set a field value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setFieldValue($key, $value)
    {
        $field = $this->contentType->fields()->where('key', $key)->first();
        
        if (!$field) {
            return;
        }
        
        $fieldValue = $this->fieldValues()->where('field_id', $field->id)->first();
        
        if ($fieldValue) {
            $fieldValue->update(['value' => $value]);
        } else {
            $this->fieldValues()->create([
                'field_id' => $field->id,
                'value' => $value,
            ]);
        }
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
        
        $this->addMediaCollection('gallery');
        
        $this->addMediaCollection('attachments');
    }

    /**
     * Scope a query to only include published content items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
```
