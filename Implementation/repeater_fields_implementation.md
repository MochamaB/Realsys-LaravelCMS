# Repeater Fields Implementation in Laravel CMS

## Overview

This document outlines the implementation strategy for repeater fields in the RealsysCMS content management system. Repeater fields allow content editors to create dynamic, repeatable groups of fields, which is essential for flexible content modeling.

## Database Structure

The current database schema already supports repeater fields with minimal changes:

### ContentTypeField Model

```php
protected $fillable = [
    'content_type_id',
    'name',
    'slug',
    'field_type',  // Will be 'repeater'
    'is_required',
    'is_unique',
    'position',
    'description',
    'validation_rules',
    'settings'     // JSON column for storing repeater configuration
];

protected $casts = [
    'is_required' => 'boolean',
    'settings' => 'json',  // Already properly cast to JSON
];
```

## Repeater Field Structure

A repeater field uses the `settings` JSON column to define its subfields and configuration:

```json
{
  "subfields": [
    {
      "name": "icon",
      "type": "image",
      "label": "Icon",
      "required": true,
      "settings": {}
    },
    {
      "name": "title",
      "type": "text",
      "label": "Title",
      "required": true,
      "settings": {}
    },
    {
      "name": "count_value",
      "type": "number",
      "label": "Count Value",
      "required": true,
      "settings": {}
    }
  ],
  "min_items": 1,
  "max_items": 10
}
```

## Implementation Requirements

### 1. ContentTypeField Management

- Update `ContentTypeFieldController` to support creating and editing repeater fields
- Add validation for repeater field configurations
- Implement subfield management UI for adding/editing/reordering subfields

### 2. Content Field Value Storage

For a content item using a repeater field, the data should be stored in the `ContentFieldValue` model as a JSON array:

```json
[
  {
    "icon": "path/to/image.jpg",
    "title": "Donations",
    "count_value": 1
  },
  {
    "icon": "path/to/image2.jpg",
    "title": "Members",
    "count_value": 5
  }
]
```

### 3. UI Components Needed

1. **Admin Field Editor**
   - Repeater field configuration UI
   - Subfield management (add/edit/delete/reorder)
   - Type selection for subfields
   - Settings configuration for each subfield

2. **Content Entry Form**
   - Dynamic adding/removing of repeater items
   - Item reordering functionality
   - Validation for each subfield
   - Support for different subfield types (text, image, etc.)

3. **Field Rendering**
   - Template component for rendering repeater fields in forms
   - JavaScript for managing repeater instances
   - Validation handling for repeater fields

## Implementation Steps

### Phase 1: Model & Controller Updates

1. **Update ContentTypeFieldController**
   - Add support for creating repeater fields
   - Implement subfield validation
   - Handle JSON serialization/deserialization

2. **Create RepeaterFieldService**
   - Service to manage repeater field logic
   - Methods for validating repeater structure
   - Methods for processing repeater field data

### Phase 2: Admin UI Components

1. **Field Type Editor**
   - Create Blade component for repeater field configuration
   - Add JavaScript for managing subfields in the admin UI
   - Implement drag-and-drop reordering for subfields

2. **Form Components**
   - Create repeater field form components
   - Add JavaScript for dynamic item management
   - Implement validation for repeater items

### Phase 3: Content Entry UI

1. **Content Form Builder**
   - Update to handle repeater field rendering
   - Add support for nested validation

2. **Data Processing**
   - Implement data transformation for repeater fields
   - Add support for file uploads within repeaters
   - Ensure proper JSON storage/retrieval

## Widget Integration

To connect widgets with repeater fields in content types:

1. Update `ContentTypeGeneratorService` to properly handle repeater fields from widget definitions
2. Ensure field mapping supports subfield mapping for repeaters
3. Update `WidgetContentFetchService` to process repeater field data correctly

## Frontend Rendering

For rendering content with repeater fields:

1. Add a helper to iterate through repeater items
2. Support accessing nested fields within Blade templates
3. Add utility functions for common repeater operations

## Migration from Widget Field Structure

When generating content types from widget definitions that use repeater fields:

1. Map widget repeater structure to content type repeater structure
2. Preserve all subfield information
3. Maintain validation rules and settings

## UI/UX Considerations

1. Clear labeling for repeater instances
2. Collapsible repeater items for better UX
3. Confirmation before item deletion
4. Preview of repeater items in listing views

## Testing Strategy

1. Unit tests for repeater field validation
2. Integration tests for repeater field CRUD operations
3. E2E tests for repeater field UI interactions
