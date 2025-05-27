# Content-Driven CMS Architecture: Models (Part 3)

This document completes the Laravel Eloquent models required to implement the content-driven CMS architecture.

## Widget System Models (Continued)

### WidgetDisplaySetting

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetDisplaySetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'layout',
        'view_mode',
        'pagination_type',
        'items_per_page',
        'empty_text',
    ];

    /**
     * Get the widgets that use these display settings.
     */
    public function widgets()
    {
        return $this->hasMany(Widget::class, 'display_settings_id');
    }

    /**
     * Get the template path for the layout.
     *
     * @return string
     */
    public function getTemplatePath()
    {
        if (empty($this->layout)) {
            return 'default';
        }

        return $this->layout;
    }
}
```

### Updated Widget Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Widget extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'widget_type_id',
        'content_query_id',
        'display_settings_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the widget type that owns the widget.
     */
    public function widgetType()
    {
        return $this->belongsTo(WidgetType::class);
    }

    /**
     * Get the content query for the widget.
     */
    public function contentQuery()
    {
        return $this->belongsTo(WidgetContentQuery::class, 'content_query_id');
    }

    /**
     * Get the display settings for the widget.
     */
    public function displaySettings()
    {
        return $this->belongsTo(WidgetDisplaySetting::class, 'display_settings_id');
    }

    /**
     * Get the page sections that use this widget.
     */
    public function pageSections()
    {
        return $this->belongsToMany(PageSection::class, 'page_widgets')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderBy('page_widgets.order_index');
    }

    /**
     * Get the field values for the widget (legacy support).
     */
    public function fieldValues()
    {
        return $this->hasMany(WidgetFieldValue::class);
    }

    /**
     * Get the user that created the widget.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the widget.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Execute the widget's content query and get the results.
     *
     * @param bool $paginate
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getContent($paginate = false)
    {
        if (!$this->contentQuery) {
            return collect();
        }

        $perPage = $this->displaySettings ? $this->displaySettings->items_per_page : null;
        
        return $this->contentQuery->execute($paginate, $perPage);
    }

    /**
     * Get the widget's content items with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedContent()
    {
        return $this->getContent(true);
    }

    /**
     * Get the template path for rendering the widget.
     *
     * @return string
     */
    public function getTemplatePath()
    {
        if (!$this->widgetType) {
            return 'widgets.default';
        }

        $layout = $this->displaySettings ? $this->displaySettings->layout : null;
        
        if ($layout) {
            return "widgets.{$this->widgetType->key}.{$layout}";
        }
        
        return "widgets.{$this->widgetType->key}.default";
    }
}
```

## Service Classes

### ContentRenderingService

```php
<?php

namespace App\Services;

use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\Widget;
use Illuminate\Support\Collection;

class ContentRenderingService
{
    /**
     * Render a single content item.
     *
     * @param ContentItem $contentItem
     * @param string $viewMode
     * @return array
     */
    public function renderContentItem(ContentItem $contentItem, $viewMode = 'full')
    {
        $renderedFields = [];

        // Get all field values with their fields
        $fieldValues = $contentItem->fieldValues()->with('field')->get();

        foreach ($fieldValues as $fieldValue) {
            $field = $fieldValue->field;
            
            if (!$field) {
                continue;
            }
            
            $renderedFields[$field->key] = $this->renderFieldValue($fieldValue, $viewMode);
        }

        return [
            'id' => $contentItem->id,
            'title' => $contentItem->title,
            'slug' => $contentItem->slug,
            'status' => $contentItem->status,
            'published_at' => $contentItem->published_at,
            'created_at' => $contentItem->created_at,
            'updated_at' => $contentItem->updated_at,
            'type' => $contentItem->contentType ? $contentItem->contentType->key : null,
            'fields' => $renderedFields,
        ];
    }

    /**
     * Render multiple content items.
     *
     * @param Collection $contentItems
     * @param string $viewMode
     * @return array
     */
    public function renderContentItems(Collection $contentItems, $viewMode = 'teaser')
    {
        $renderedItems = [];

        foreach ($contentItems as $contentItem) {
            $renderedItems[] = $this->renderContentItem($contentItem, $viewMode);
        }

        return $renderedItems;
    }

    /**
     * Render a field value based on field type.
     *
     * @param \App\Models\ContentFieldValue $fieldValue
     * @param string $viewMode
     * @return mixed
     */
    protected function renderFieldValue($fieldValue, $viewMode)
    {
        $field = $fieldValue->field;
        $value = $fieldValue->value;

        if (!$field) {
            return $value;
        }

        switch ($field->type) {
            case 'rich_text':
                return $this->renderRichText($value, $viewMode);
            case 'image':
                return $this->renderImage($fieldValue->contentItem, $field->key, $viewMode);
            case 'gallery':
                return $this->renderGallery($fieldValue->contentItem, $field->key, $viewMode);
            case 'file':
                return $this->renderFile($fieldValue->contentItem, $field->key);
            case 'reference':
                return $this->renderReference($value, $viewMode);
            default:
                return $fieldValue->getFormattedValue();
        }
    }

    /**
     * Render rich text content.
     *
     * @param string $value
     * @param string $viewMode
     * @return string
     */
    protected function renderRichText($value, $viewMode)
    {
        if ($viewMode === 'teaser') {
            // Truncate for teaser view
            $plainText = strip_tags($value);
            return strlen($plainText) > 200 ? substr($plainText, 0, 200) . '...' : $plainText;
        }
        
        return $value;
    }

    /**
     * Render an image.
     *
     * @param ContentItem $contentItem
     * @param string $key
     * @param string $viewMode
     * @return array|null
     */
    protected function renderImage($contentItem, $key, $viewMode)
    {
        $media = $contentItem->getMedia("field_{$key}")->first();
        
        if (!$media) {
            return null;
        }
        
        $conversion = $viewMode === 'teaser' ? 'thumb' : 'default';
        
        return [
            'url' => $media->getUrl($conversion),
            'original_url' => $media->getUrl(),
            'thumbnail_url' => $media->getUrl('thumb'),
            'alt' => $media->custom_properties['alt'] ?? $contentItem->title,
            'width' => $media->custom_properties['width'] ?? null,
            'height' => $media->custom_properties['height'] ?? null,
        ];
    }

    /**
     * Render a gallery of images.
     *
     * @param ContentItem $contentItem
     * @param string $key
     * @param string $viewMode
     * @return array
     */
    protected function renderGallery($contentItem, $key, $viewMode)
    {
        $media = $contentItem->getMedia("field_{$key}");
        $gallery = [];
        
        foreach ($media as $item) {
            $conversion = $viewMode === 'teaser' ? 'thumb' : 'default';
            
            $gallery[] = [
                'url' => $item->getUrl($conversion),
                'original_url' => $item->getUrl(),
                'thumbnail_url' => $item->getUrl('thumb'),
                'alt' => $item->custom_properties['alt'] ?? $contentItem->title,
                'width' => $item->custom_properties['width'] ?? null,
                'height' => $item->custom_properties['height'] ?? null,
            ];
        }
        
        return $gallery;
    }

    /**
     * Render a file.
     *
     * @param ContentItem $contentItem
     * @param string $key
     * @return array|null
     */
    protected function renderFile($contentItem, $key)
    {
        $media = $contentItem->getMedia("field_{$key}")->first();
        
        if (!$media) {
            return null;
        }
        
        return [
            'url' => $media->getUrl(),
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'human_readable_size' => $media->human_readable_size,
        ];
    }

    /**
     * Render a reference to another content item.
     *
     * @param string $value
     * @param string $viewMode
     * @return array|null
     */
    protected function renderReference($value, $viewMode)
    {
        if (empty($value)) {
            return null;
        }
        
        $contentItem = ContentItem::find($value);
        
        if (!$contentItem) {
            return null;
        }
        
        $referenceViewMode = $viewMode === 'full' ? 'teaser' : 'minimal';
        
        return $this->renderContentItem($contentItem, $referenceViewMode);
    }

    /**
     * Render a widget with its content.
     *
     * @param Widget $widget
     * @param bool $preview
     * @return array
     */
    public function renderWidget(Widget $widget, $preview = false)
    {
        // For legacy widgets
        if (!$widget->content_query_id) {
            return $this->renderLegacyWidget($widget);
        }
        
        $viewMode = $widget->displaySettings ? $widget->displaySettings->view_mode : 'teaser';
        $paginate = $widget->displaySettings && $widget->displaySettings->pagination_type !== 'none';
        
        $content = $widget->getContent($paginate);
        
        if ($content instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $renderedItems = $this->renderContentItems($content->getCollection(), $viewMode);
            
            return [
                'id' => $widget->id,
                'name' => $widget->name,
                'type' => $widget->widgetType ? $widget->widgetType->key : null,
                'items' => $renderedItems,
                'pagination' => [
                    'current_page' => $content->currentPage(),
                    'per_page' => $content->perPage(),
                    'total' => $content->total(),
                    'last_page' => $content->lastPage(),
                ],
                'template' => $widget->getTemplatePath(),
                'layout' => $widget->displaySettings ? $widget->displaySettings->layout : null,
            ];
        } else {
            $renderedItems = $this->renderContentItems($content, $viewMode);
            
            return [
                'id' => $widget->id,
                'name' => $widget->name,
                'type' => $widget->widgetType ? $widget->widgetType->key : null,
                'items' => $renderedItems,
                'template' => $widget->getTemplatePath(),
                'layout' => $widget->displaySettings ? $widget->displaySettings->layout : null,
            ];
        }
    }

    /**
     * Render a legacy widget (using field values directly).
     *
     * @param Widget $widget
     * @return array
     */
    protected function renderLegacyWidget(Widget $widget)
    {
        $renderedFields = [];
        
        // Get all field values with their fields
        $fieldValues = $widget->fieldValues()->with('field')->get();
        
        foreach ($fieldValues as $fieldValue) {
            $field = $fieldValue->field;
            
            if (!$field) {
                continue;
            }
            
            $renderedFields[$field->key] = $fieldValue->value;
        }
        
        return [
            'id' => $widget->id,
            'name' => $widget->name,
            'type' => $widget->widgetType ? $widget->widgetType->key : null,
            'fields' => $renderedFields,
            'template' => $widget->getTemplatePath(),
        ];
    }
}
```

## Model Factory Examples

To aid in testing and seeding the database, here are examples of model factories:

### ContentTypeFactory

```php
<?php

namespace Database\Factories;

use App\Models\ContentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            'name' => ucfirst($name),
            'key' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'is_system' => false,
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => function (array $attributes) {
                return $attributes['created_by'];
            },
        ];
    }

    /**
     * Indicate that the content type is a system type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function system()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_system' => true,
            ];
        });
    }
}
```

### ContentItemFactory

```php
<?php

namespace Database\Factories;

use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence();
        
        return [
            'content_type_id' => ContentType::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-1 year', '+1 month') : null,
            'created_by' => User::factory(),
            'updated_by' => function (array $attributes) {
                return $attributes['created_by'];
            },
        ];
    }

    /**
     * Indicate that the content item is published.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    /**
     * Indicate that the content item is a draft.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'published_at' => null,
            ];
        });
    }
}
```
