# Widget System

This document outlines the widget system architecture for the CMS, explaining how widgets are defined, created, and rendered.

## Widget System Overview

The widget system is designed to provide a flexible way to create and manage content components that can be placed on pages. The system consists of:

1. **Widget Types**: Define the structure and fields for a type of widget
2. **Widget Type Fields**: Define the fields available for a widget type
3. **Widgets**: Instances of widget types with specific content
4. **Widget Field Values**: Store the actual content for each widget field
5. **Widget Repeater Groups**: Handle repeatable field groups (e.g., slider images)
6. **Widget Repeater Values**: Store values within repeatable groups

## Widget Type Definition

Widget types define the structure and fields for a type of widget. They are created through the admin interface and stored in the database.

### Widget Type Fields

Each widget type has a set of fields that define the data structure for widgets of that type. Fields can be:

- **Simple fields**: Text, textarea, number, boolean, select, etc.
- **Media fields**: Image, video, file, etc.
- **Repeatable fields**: Groups of fields that can be repeated (e.g., slider images)

### Example Widget Type: Slider

A slider widget type might have the following fields:

1. **Title**: Text field for the slider title
2. **Description**: Textarea field for the slider description
3. **Slides**: Repeatable field group with:
   - **Image**: Image field for the slide image
   - **Title**: Text field for the slide title
   - **Description**: Textarea field for the slide description
   - **Button Text**: Text field for the button text
   - **Button URL**: URL field for the button link

## Widget Creation Process

1. **Admin creates a widget type**:
   - Define the name, description, and component path
   - Add fields with validation rules

2. **Admin creates a widget**:
   - Select a widget type
   - Provide a name and description
   - Fill in field values

3. **Widget is placed on a page**:
   - Select a page section
   - Add the widget to the section
   - Set the display order

## Widget Data Storage

Widget data is stored in a fully relational structure:

1. **Widget**: Basic information about the widget
2. **Widget Field Values**: Simple field values
3. **Widget Repeater Groups**: Repeatable field groups
4. **Widget Repeater Values**: Values within repeatable groups

## Widget Rendering

Widgets are rendered using Blade components defined in the active theme:

1. **Widget Controller** fetches the widget data
2. **Widget Model** assembles the data in a structured format
3. **Theme Component** renders the widget with the provided data

## Widget Field Types

The system supports various field types:

1. **Text**: Single-line text input
2. **Textarea**: Multi-line text input
3. **Rich Text**: WYSIWYG editor
4. **Number**: Numeric input
5. **Boolean**: Checkbox or toggle
6. **Select**: Dropdown selection
7. **Radio**: Radio button selection
8. **Image**: Image upload and selection
9. **Gallery**: Multiple image selection
10. **File**: File upload and selection
11. **URL**: URL input
12. **Email**: Email input
13. **Date**: Date picker
14. **Time**: Time picker
15. **Color**: Color picker
16. **Icon**: Icon selection
17. **Repeater**: Repeatable field group

## Widget Admin Interface

The admin interface provides a user-friendly way to manage widgets:

1. **Widget Type Management**:
   - Create, edit, and delete widget types
   - Add, edit, and remove fields
   - Set validation rules and default values

2. **Widget Management**:
   - Create, edit, and delete widgets
   - Fill in field values
   - Preview widgets

3. **Page Widget Management**:
   - Add widgets to page sections
   - Arrange widgets within sections
   - Remove widgets from sections

## Widget Services

### WidgetTypeService

Handles widget type operations:

```php
namespace App\Services;

use App\Models\WidgetType;
use App\Models\WidgetTypeField;
use App\Models\WidgetTypeFieldOption;
use Illuminate\Support\Str;

class WidgetTypeService
{
    /**
     * Create a new widget type
     *
     * @param array $data
     * @return WidgetType
     */
    public function createWidgetType(array $data)
    {
        // Create widget type
        $widgetType = WidgetType::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? '',
            'component_path' => $data['component_path'],
            'icon' => $data['icon'] ?? 'puzzle-piece',
            'is_active' => $data['is_active'] ?? true
        ]);
        
        // Add fields if provided
        if (isset($data['fields']) && is_array($data['fields'])) {
            $this->addFieldsToWidgetType($widgetType, $data['fields']);
        }
        
        return $widgetType;
    }
    
    /**
     * Add fields to a widget type
     *
     * @param WidgetType $widgetType
     * @param array $fields
     * @return void
     */
    public function addFieldsToWidgetType(WidgetType $widgetType, array $fields)
    {
        $order = 0;
        
        foreach ($fields as $field) {
            $widgetTypeField = $widgetType->fields()->create([
                'name' => $field['name'],
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'is_required' => $field['is_required'] ?? false,
                'is_repeatable' => $field['is_repeatable'] ?? false,
                'validation_rules' => $field['validation_rules'] ?? '',
                'help_text' => $field['help_text'] ?? '',
                'default_value' => $field['default_value'] ?? null,
                'order_index' => $order++
            ]);
            
            // Add options if provided
            if (isset($field['options']) && is_array($field['options'])) {
                $this->addOptionsToField($widgetTypeField, $field['options']);
            }
        }
    }
    
    /**
     * Add options to a field
     *
     * @param WidgetTypeField $field
     * @param array $options
     * @return void
     */
    public function addOptionsToField(WidgetTypeField $field, array $options)
    {
        $order = 0;
        
        foreach ($options as $option) {
            $field->options()->create([
                'value' => $option['value'],
                'label' => $option['label'],
                'order_index' => $order++
            ]);
        }
    }
}
```

### WidgetService

Handles widget operations:

```php
namespace App\Services;

use App\Models\Widget;
use App\Models\WidgetType;
use App\Models\WidgetFieldValue;
use App\Models\WidgetRepeaterGroup;
use App\Models\WidgetRepeaterValue;
use Illuminate\Support\Facades\DB;

class WidgetService
{
    /**
     * Create a new widget
     *
     * @param array $data
     * @return Widget
     */
    public function createWidget(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create widget
            $widget = Widget::create([
                'widget_type_id' => $data['widget_type_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);
            
            // Add field values
            if (isset($data['fields']) && is_array($data['fields'])) {
                $this->saveWidgetFieldValues($widget, $data['fields']);
            }
            
            return $widget;
        });
    }
    
    /**
     * Save widget field values
     *
     * @param Widget $widget
     * @param array $fields
     * @return void
     */
    public function saveWidgetFieldValues(Widget $widget, array $fields)
    {
        $widgetType = $widget->widgetType;
        
        foreach ($fields as $fieldName => $fieldValue) {
            $field = $widgetType->fields()->where('name', $fieldName)->first();
            
            if (!$field) {
                continue;
            }
            
            if ($field->is_repeatable && is_array($fieldValue)) {
                $this->saveRepeaterFieldValues($widget, $field, $fieldValue);
            } else {
                $this->saveSingleFieldValue($widget, $field, $fieldValue);
            }
        }
    }
    
    /**
     * Save a single field value
     *
     * @param Widget $widget
     * @param WidgetTypeField $field
     * @param mixed $value
     * @return void
     */
    protected function saveSingleFieldValue(Widget $widget, $field, $value)
    {
        // Remove existing value
        $widget->fieldValues()->where('widget_type_field_id', $field->id)->delete();
        
        // Add new value
        $widget->fieldValues()->create([
            'widget_type_field_id' => $field->id,
            'value' => $value
        ]);
    }
    
    /**
     * Save repeater field values
     *
     * @param Widget $widget
     * @param WidgetTypeField $field
     * @param array $values
     * @return void
     */
    protected function saveRepeaterFieldValues(Widget $widget, $field, array $values)
    {
        // Remove existing groups and values
        $groups = $widget->repeaterGroups()->where('widget_type_field_id', $field->id)->get();
        
        foreach ($groups as $group) {
            $group->values()->delete();
            $group->delete();
        }
        
        // Add new groups and values
        foreach ($values as $index => $groupData) {
            $group = $widget->repeaterGroups()->create([
                'widget_type_field_id' => $field->id,
                'order_index' => $index
            ]);
            
            foreach ($groupData as $subFieldName => $subFieldValue) {
                $subField = $field->widgetType->fields()
                    ->where('name', "{$field->name}.{$subFieldName}")
                    ->first();
                
                if ($subField) {
                    $group->values()->create([
                        'widget_type_field_id' => $subField->id,
                        'value' => $subFieldValue
                    ]);
                }
            }
        }
    }
}
```

## Widget Form Generation

The admin interface dynamically generates forms based on widget type fields:

```php
namespace App\Services;

use App\Models\WidgetType;
use App\Models\Widget;

class WidgetFormService
{
    /**
     * Generate form fields for a widget type
     *
     * @param WidgetType $widgetType
     * @param Widget|null $widget
     * @return array
     */
    public function generateFormFields(WidgetType $widgetType, Widget $widget = null)
    {
        $fields = [];
        
        foreach ($widgetType->fields()->orderBy('order_index')->get() as $field) {
            if ($field->is_repeatable) {
                $fields[] = $this->generateRepeaterField($field, $widget);
            } else {
                $fields[] = $this->generateSingleField($field, $widget);
            }
        }
        
        return $fields;
    }
    
    /**
     * Generate a single form field
     *
     * @param WidgetTypeField $field
     * @param Widget|null $widget
     * @return array
     */
    protected function generateSingleField($field, $widget = null)
    {
        $value = null;
        
        if ($widget) {
            $fieldValue = $widget->fieldValues()
                ->where('widget_type_field_id', $field->id)
                ->first();
                
            if ($fieldValue) {
                $value = $fieldValue->value;
            }
        } else {
            $value = $field->default_value;
        }
        
        $formField = [
            'name' => $field->name,
            'label' => $field->label,
            'type' => $field->field_type,
            'required' => $field->is_required,
            'help_text' => $field->help_text,
            'value' => $value
        ];
        
        // Add options for select, radio, etc.
        if (in_array($field->field_type, ['select', 'radio', 'checkbox'])) {
            $formField['options'] = $field->options()
                ->orderBy('order_index')
                ->get()
                ->map(function ($option) {
                    return [
                        'value' => $option->value,
                        'label' => $option->label
                    ];
                })
                ->toArray();
        }
        
        return $formField;
    }
    
    /**
     * Generate a repeater form field
     *
     * @param WidgetTypeField $field
     * @param Widget|null $widget
     * @return array
     */
    protected function generateRepeaterField($field, $widget = null)
    {
        $values = [];
        
        if ($widget) {
            $groups = $widget->repeaterGroups()
                ->where('widget_type_field_id', $field->id)
                ->orderBy('order_index')
                ->get();
                
            foreach ($groups as $group) {
                $groupValues = [];
                
                foreach ($group->values as $value) {
                    $subField = $value->field;
                    $subFieldName = str_replace("{$field->name}.", '', $subField->name);
                    $groupValues[$subFieldName] = $value->value;
                }
                
                $values[] = $groupValues;
            }
        }
        
        // Get subfields
        $subFields = $field->widgetType->fields()
            ->where('name', 'like', "{$field->name}.%")
            ->orderBy('order_index')
            ->get()
            ->map(function ($subField) use ($field) {
                $subFieldName = str_replace("{$field->name}.", '', $subField->name);
                
                return [
                    'name' => $subFieldName,
                    'label' => $subField->label,
                    'type' => $subField->field_type,
                    'required' => $subField->is_required,
                    'help_text' => $subField->help_text
                ];
            })
            ->toArray();
        
        return [
            'name' => $field->name,
            'label' => $field->label,
            'type' => 'repeater',
            'required' => $field->is_required,
            'help_text' => $field->help_text,
            'subfields' => $subFields,
            'values' => $values
        ];
    }
}
```

## Widget Validation

Widget data is validated based on the field definitions:

```php
namespace App\Services;

use App\Models\WidgetType;
use Illuminate\Support\Facades\Validator;

class WidgetValidationService
{
    /**
     * Validate widget data
     *
     * @param WidgetType $widgetType
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateWidgetData(WidgetType $widgetType, array $data)
    {
        $rules = $this->generateValidationRules($widgetType);
        
        return Validator::make($data, $rules);
    }
    
    /**
     * Generate validation rules for a widget type
     *
     * @param WidgetType $widgetType
     * @return array
     */
    protected function generateValidationRules(WidgetType $widgetType)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ];
        
        foreach ($widgetType->fields as $field) {
            if ($field->is_repeatable) {
                $rules["fields.{$field->name}"] = 'array';
                
                // Get subfields
                $subFields = $widgetType->fields()
                    ->where('name', 'like', "{$field->name}.%")
                    ->get();
                
                foreach ($subFields as $subField) {
                    $subFieldName = str_replace("{$field->name}.", '', $subField->name);
                    $fieldRules = $subField->validation_rules ?: ($subField->is_required ? 'required' : 'nullable');
                    
                    $rules["fields.{$field->name}.*.{$subFieldName}"] = $fieldRules;
                }
            } else {
                $fieldRules = $field->validation_rules ?: ($field->is_required ? 'required' : 'nullable');
                $rules["fields.{$field->name}"] = $fieldRules;
            }
        }
        
        return $rules;
    }
}
```

## Widget Rendering Process

The widget rendering process involves:

1. **Fetching the widget**: The widget is loaded from the database
2. **Assembling the data**: The widget data is assembled in a structured format
3. **Rendering the component**: The theme component renders the widget with the provided data

```php
namespace App\Services;

use App\Models\Widget;
use Illuminate\Support\Facades\View;

class WidgetRenderingService
{
    /**
     * Render a widget
     *
     * @param Widget $widget
     * @return string
     */
    public function renderWidget(Widget $widget)
    {
        if (!$widget->is_active) {
            return '';
        }
        
        $data = $this->assembleWidgetData($widget);
        $componentPath = $widget->widgetType->component_path;
        
        if (!View::exists($componentPath)) {
            return "<!-- Widget component not found: {$componentPath} -->";
        }
        
        return view($componentPath, [
            'widget' => $widget,
            'data' => $data
        ])->render();
    }
    
    /**
     * Assemble widget data
     *
     * @param Widget $widget
     * @return array
     */
    protected function assembleWidgetData(Widget $widget)
    {
        return $widget->getData();
    }
}
```

## Conclusion

This widget system provides a powerful and flexible way to create and manage content components. Key benefits include:

1. **Fully relational data structure**: No JSON columns, all data stored in properly structured tables
2. **Flexible field types**: Support for various field types, including repeatable fields
3. **Theme integration**: Widgets are rendered using theme components
4. **Dynamic form generation**: Forms are generated based on widget type fields
5. **Validation**: Data is validated based on field definitions

The system is designed to be extensible, allowing for the creation of new widget types and field types without modifying the core CMS code.
