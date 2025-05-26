# Models and Relationships

This document outlines the Eloquent models and their relationships for the CMS architecture.

## Core Models

### Theme Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'screenshot_path',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public static function getActive()
    {
        return self::active()->first();
    }
}
```

### Template Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'theme_id',
        'name',
        'slug',
        'file_path',
        'description',
        'thumbnail_path',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function sections()
    {
        return $this->hasMany(TemplateSection::class)->orderBy('order_index');
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### TemplateSection Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSection extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'slug',
        'description',
        'is_required',
        'max_widgets',
        'order_index'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function pageSections()
    {
        return $this->hasMany(PageSection::class);
    }
}
```

### Page Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'template_id',
        'parent_id',
        'is_active',
        'show_in_menu',
        'menu_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean'
    ];

    // Auto-generate slug from title if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
            
            if (empty($page->meta_title)) {
                $page->meta_title = $page->title;
            }
        });
    }

    // Relationships
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('menu_order');
    }

    public function sections()
    {
        return $this->hasMany(PageSection::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    // Get widgets for a specific section
    public function getWidgetsBySection($sectionSlug)
    {
        return $this->sections()
            ->whereHas('templateSection', function($query) use ($sectionSlug) {
                $query->where('slug', $sectionSlug);
            })
            ->with(['widgets' => function($query) {
                $query->orderBy('page_widgets.order_index');
            }])
            ->first()
            ->widgets ?? collect();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }
}
```

### PageSection Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page_id',
        'template_section_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function templateSection()
    {
        return $this->belongsTo(TemplateSection::class);
    }

    public function widgets()
    {
        return $this->belongsToMany(Widget::class, 'page_widgets')
            ->withPivot('order_index')
            ->orderBy('page_widgets.order_index')
            ->withTimestamps();
    }
}
```

## Widget System Models

### WidgetType Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'component_path',
        'icon',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function fields()
    {
        return $this->hasMany(WidgetTypeField::class)->orderBy('order_index');
    }

    public function widgets()
    {
        return $this->hasMany(Widget::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### WidgetTypeField Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetTypeField extends Model
{
    protected $fillable = [
        'widget_type_id',
        'name',
        'label',
        'field_type',
        'is_required',
        'is_repeatable',
        'validation_rules',
        'help_text',
        'default_value',
        'order_index'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_repeatable' => 'boolean'
    ];

    // Relationships
    public function widgetType()
    {
        return $this->belongsTo(WidgetType::class);
    }

    public function options()
    {
        return $this->hasMany(WidgetTypeFieldOption::class)->orderBy('order_index');
    }

    public function fieldValues()
    {
        return $this->hasMany(WidgetFieldValue::class);
    }

    public function repeaterGroups()
    {
        return $this->hasMany(WidgetRepeaterGroup::class);
    }
}
```

### WidgetTypeFieldOption Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetTypeFieldOption extends Model
{
    protected $fillable = [
        'widget_type_field_id',
        'value',
        'label',
        'order_index'
    ];

    // Relationships
    public function field()
    {
        return $this->belongsTo(WidgetTypeField::class, 'widget_type_field_id');
    }
}
```

### Widget Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = [
        'widget_type_id',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function widgetType()
    {
        return $this->belongsTo(WidgetType::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(WidgetFieldValue::class);
    }

    public function repeaterGroups()
    {
        return $this->hasMany(WidgetRepeaterGroup::class)->orderBy('order_index');
    }

    public function pageSections()
    {
        return $this->belongsToMany(PageSection::class, 'page_widgets')
            ->withPivot('order_index')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Helper methods
    public function getValue($fieldName)
    {
        $field = $this->widgetType->fields()->where('name', $fieldName)->first();
        
        if (!$field) {
            return null;
        }
        
        if ($field->is_repeatable) {
            return $this->getRepeaterValues($field->id);
        }
        
        $fieldValue = $this->fieldValues()
            ->where('widget_type_field_id', $field->id)
            ->first();
            
        return $fieldValue ? $fieldValue->value : null;
    }
    
    public function getRepeaterValues($fieldId)
    {
        $groups = $this->repeaterGroups()
            ->where('widget_type_field_id', $fieldId)
            ->with('values')
            ->get();
            
        $result = [];
        
        foreach ($groups as $group) {
            $groupData = [];
            
            foreach ($group->values as $value) {
                $field = WidgetTypeField::find($value->widget_type_field_id);
                $groupData[$field->name] = $value->value;
            }
            
            $result[] = $groupData;
        }
        
        return $result;
    }
    
    // Get all data for this widget in a structured format
    public function getData()
    {
        $data = [];
        
        foreach ($this->widgetType->fields as $field) {
            if ($field->is_repeatable) {
                $data[$field->name] = $this->getRepeaterValues($field->id);
            } else {
                $data[$field->name] = $this->getValue($field->name);
            }
        }
        
        return $data;
    }

    // Render the widget
    public function render()
    {
        if (!$this->is_active) {
            return '';
        }

        $data = $this->getData();
        
        return view($this->widgetType->component_path, [
            'widget' => $this,
            'data' => $data
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### WidgetFieldValue Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetFieldValue extends Model
{
    protected $fillable = [
        'widget_id',
        'widget_type_field_id',
        'value'
    ];

    // Relationships
    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function field()
    {
        return $this->belongsTo(WidgetTypeField::class, 'widget_type_field_id');
    }
}
```

### WidgetRepeaterGroup Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetRepeaterGroup extends Model
{
    protected $fillable = [
        'widget_id',
        'widget_type_field_id',
        'order_index'
    ];

    // Relationships
    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function field()
    {
        return $this->belongsTo(WidgetTypeField::class, 'widget_type_field_id');
    }

    public function values()
    {
        return $this->hasMany(WidgetRepeaterValue::class);
    }
}
```

### WidgetRepeaterValue Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetRepeaterValue extends Model
{
    protected $fillable = [
        'widget_repeater_group_id',
        'widget_type_field_id',
        'value'
    ];

    // Relationships
    public function group()
    {
        return $this->belongsTo(WidgetRepeaterGroup::class, 'widget_repeater_group_id');
    }

    public function field()
    {
        return $this->belongsTo(WidgetTypeField::class, 'widget_type_field_id');
    }
}
```

## Menu System Models

### Menu Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'location',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }

    // Get top-level menu items
    public function topLevelItems()
    {
        return $this->items()->whereNull('parent_id')->orderBy('order_index');
    }

    // Get a menu by its location
    public static function getByLocation(string $location)
    {
        return self::where('location', $location)
            ->where('is_active', true)
            ->with(['items' => function($query) {
                $query->where('is_active', true)
                    ->orderBy('order_index')
                    ->with(['children' => function($q) {
                        $q->where('is_active', true)
                            ->orderBy('order_index');
                    }]);
            }])
            ->first();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### MenuItem Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'link_type',
        'page_id',
        'custom_url',
        'target',
        'css_class',
        'order_index',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order_index');
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    // Get the URL for this menu item
    public function getUrl()
    {
        if ($this->link_type === 'custom' && $this->custom_url) {
            return $this->custom_url;
        }

        if ($this->link_type === 'page' && $this->page) {
            return route('page.show', $this->page->slug);
        }

        return '#';
    }

    // Check if this menu item is active based on the current URL
    public function isActive()
    {
        $currentUrl = url()->current();
        $itemUrl = $this->getUrl();
        
        // If this is an exact match
        if ($currentUrl === $itemUrl) {
            return true;
        }
        
        // If this is a parent item and we're on a child page
        if ($this->children->count() > 0) {
            foreach ($this->children as $child) {
                if ($child->isActive()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

## User System Models

### User Model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    // Check if user has a specific role
    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    // Check if user has a specific permission
    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permission)) {
                return true;
            }
        }
        
        return false;
    }
}
```

### Role Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
}
```

### Permission Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
```

## Media Library Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'alt_text',
        'caption',
        'uploaded_by'
    ];

    // Relationships
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Get the full URL to the media file
    public function getUrl()
    {
        return asset('storage/' . $this->path);
    }
}
```

## Model Relationships Summary

This architecture creates a clear and maintainable structure with well-defined relationships:

1. **Theme → Templates → Template Sections**: Defines the structure of the site
2. **Pages → Page Sections → Widgets**: Contains the actual content
3. **Widget Types → Fields → Values**: Defines and stores widget content
4. **Menus → Menu Items**: Manages navigation
5. **Users → Roles → Permissions**: Handles authentication and authorization

The models include helper methods to simplify common operations, such as:
- Getting widget data in a structured format
- Rendering widgets with their appropriate views
- Checking user permissions
- Building menu structures

This approach completely eliminates JSON columns while maintaining flexibility and providing a clean API for developers to work with.
