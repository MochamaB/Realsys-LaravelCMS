# CMS Component Architecture

This document explains how Themes, Templates, Sections, Pages, and Widgets work together in the Realsys Laravel CMS System.

## Table of Contents

1. [Component Overview](#component-overview)
2. [Hierarchical Relationship](#hierarchical-relationship)
3. [Component Details](#component-details)
4. [Data Flow](#data-flow)
5. [Implementation Notes](#implementation-notes)

## Component Overview

The Realsys CMS is built on a hierarchical component architecture that separates design from content in a modular way:

- **Themes**: Visual design packages containing layouts, assets, and templates
- **Templates**: Blueprint structures defining what sections a page can have
- **Pages**: Content containers that use templates to determine their structure
- **Sections**: Component areas within templates that can contain widgets
- **Widgets**: Content display units that render specific types of content

## Hierarchical Relationship

The components are organized in a hierarchical structure:

```
Theme
  └── Template
       └── Page
            └── Section
                 └── Widget
                      └── Content
```

This hierarchy allows for maximum flexibility while maintaining a consistent structure:

1. **Themes** provide the overall visual design and contain templates
2. **Templates** define the structure of different page types
3. **Pages** use templates to determine their layout and contain sections
4. **Sections** are placeholders within templates that can contain widgets
5. **Widgets** display content within sections

## Component Details

### Themes

Themes are the top-level design containers that define the overall look and feel of the website.

**Key Characteristics:**
- Self-contained visual packages with CSS, JavaScript, and image assets
- Include layouts, templates, sections, and widget templates
- Can be switched to instantly change the entire site appearance
- Store their files in `resources/themes/{theme-name}/`

**Database Structure:**
```php
Theme
  - id
  - name
  - slug
  - path
  - active
  - settings (JSON)
```

### Templates

Templates define the structure of different page types within a theme.

**Key Characteristics:**
- Blueprint structures that define what sections a page can have
- Define the layout and positioning of sections
- Can be reused across multiple pages
- Store their files in `resources/themes/{theme-name}/templates/`

**Database Structure:**
```php
Template
  - id
  - theme_id
  - name
  - slug
  - description
  - settings (JSON)
  // Relationships
  - sections (hasMany)
  - pages (hasMany)
```

### Pages

Pages are the content containers that use templates to determine their structure.

**Key Characteristics:**
- Content instances that use a specific template
- Contain metadata like title, description, and status
- Organized in a hierarchical structure (parent/child relationships)
- Associated with a specific URL/route

**Database Structure:**
```php
Page
  - id
  - template_id
  - parent_id
  - title
  - slug
  - meta_description
  - meta_keywords
  - status
  - published_at
  - settings (JSON)
  // Relationships
  - template (belongsTo)
  - parent (belongsTo)
  - children (hasMany)
  - sections (hasMany through PageSection)
```

### Sections

Sections are component areas within templates that can contain widgets.

**Key Characteristics:**
- Defined areas within templates (header, footer, sidebar, content, etc.)
- Can contain multiple widgets
- Can have custom settings and styles
- Different section types serve different purposes

**Database Structure:**
```php
TemplateSection
  - id
  - template_id
  - name
  - slug
  - type (header, footer, sidebar, content, etc.)
  - description
  - settings (JSON)
  // Relationships
  - template (belongsTo)
  - pageSection (hasMany)
```

```php
PageSection
  - id
  - page_id
  - template_section_id
  - settings (JSON)
  // Relationships
  - page (belongsTo)
  - templateSection (belongsTo)
  - widgets (hasMany)
```

### Widgets

Widgets are content display units that render specific types of content within sections.

**Key Characteristics:**
- Modular content blocks that can be placed in sections
- Can display various types of content (text, images, forms, etc.)
- Have their own settings and can be reordered within sections
- Can be reused across different sections and pages

**Database Structure:**
```php
WidgetType
  - id
  - name
  - slug
  - description
  - settings (JSON)
```

```php
Widget
  - id
  - widget_type_id
  - page_section_id
  - name
  - position
  - settings (JSON)
  - content (JSON)
  // Relationships
  - widgetType (belongsTo)
  - pageSection (belongsTo)
```

## Data Flow

The rendering process follows this flow:

1. A URL request is received and routed to a specific page
2. The page retrieves its associated template
3. The template defines which sections are available
4. Each section loads its associated widgets
5. Widgets retrieve and display their content
6. The entire page is assembled and rendered using the theme's layout

### Rendering Process Code Example

```php
// Simplified rendering process
public function renderPage($page)
{
    // Get the page's template
    $template = $page->template;
    
    // Get the theme
    $theme = $template->theme;
    
    // Set theme assets and context
    view()->share('theme', $theme);
    
    // Prepare sections data
    $sections = [];
    foreach ($page->sections as $section) {
        $widgets = $section->widgets()->orderBy('position')->get();
        $sections[$section->templateSection->slug] = [
            'section' => $section,
            'widgets' => $widgets
        ];
    }
    
    // Render the page using the template
    return view('theme::templates.' . $template->slug, [
        'page' => $page,
        'template' => $template,
        'sections' => $sections
    ]);
}
```

## Implementation Notes

### Template System

The template system uses custom Blade directives to render sections:

```php
// Register custom Blade directives
Blade::directive('section', function ($expression) {
    return "<?php echo app('template.renderer')->renderSection($expression); ?>";
});

Blade::directive('hassection', function ($expression) {
    return "<?php if(app('template.renderer')->hasSection($expression)): ?>";
});

Blade::directive('endhassection', function () {
    return "<?php endif; ?>";
});
```

### Theme Aliases

Theme resources are accessed through aliases to maintain flexibility:

```php
// Theme asset helper
function theme_asset($path)
{
    $theme = app('theme.manager')->getActiveTheme();
    return asset('themes/' . $theme->slug . '/' . $path);
}
```

### Section Types

Standard section types include:

- `header`: Top navigation and branding
- `footer`: Bottom footer area with links and copyright
- `content`: Main content area
- `sidebar`: Side column for additional content
- `hero`: Featured image/banner area
- `custom`: Custom section type for special needs

### Widget Management

Widgets are managed through a widget registry that allows for easy extension:

```php
// Register a new widget type
$widgetRegistry->register('recent_posts', [
    'name' => 'Recent Posts',
    'description' => 'Displays a list of recent blog posts',
    'settings' => [
        'title' => [
            'type' => 'text',
            'label' => 'Widget Title',
            'default' => 'Recent Posts'
        ],
        'count' => [
            'type' => 'number',
            'label' => 'Number of Posts',
            'default' => 5
        ]
    ]
]);
```

## Extending The System

The system is designed to be extensible in various ways:

1. **New Widget Types**: Register new widget types to display different content
2. **Custom Section Types**: Create custom section types for special needs
3. **Theme Customization**: Override theme files and assets
4. **Template Extensions**: Create new templates for different page types

### Creating a Custom Widget

```php
// Register a custom widget type
$widgetRegistry->register('custom_widget', [
    'name' => 'Custom Widget',
    'description' => 'A custom widget that displays XYZ',
    'settings' => [
        // Widget settings schema
    ]
]);

// Create a widget template file in your theme
// resources/themes/your-theme/widgets/custom_widget.blade.php
```

By understanding how these components work together, you can effectively create and manage content within the Realsys CMS system.
