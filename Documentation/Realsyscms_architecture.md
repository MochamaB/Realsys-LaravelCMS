# Realsys CMS: Complete System Architecture & Logic Flow

This document provides a comprehensive overview of the Realsys CMS architecture, focusing on the core systems, their interactions, and implementation details. It serves as both a technical reference and conceptual guide to the CMS.

## Table of Contents

1. [Core System Overview](#1-core-system-overview)
2. [Theme System Architecture](#2-theme-system-architecture)
3. [Content System Architecture](#3-content-system-architecture)
4. [Widget System Architecture](#4-widget-system-architecture)
5. [Page Construction System](#5-page-construction-system)
6. [Widget-Content Query System](#6-widget-content-query-system)
7. [Layout & Dimensioning System](#7-layout--dimensioning-system)
8. [Menu System](#8-menu-system)
9. [Rendering Pipeline](#9-rendering-pipeline)
10. [System Workflows](#10-system-workflows)

## 1. Core System Overview

Realsys CMS is built on the principle of separation between content and presentation. It consists of four interconnected core systems:

### 1.1 Key Systems

1. **Theme System**: Manages visual presentation, layout, and component structure
2. **Content System**: Manages data structures and content entries
3. **Widget System**: Connects themes and content through display components
4. **Page System**: Orchestrates how everything comes together on individual pages

### 1.2 Key Entities

- **Theme**: A complete visual design package for a website
- **Template**: A page layout blueprint within a theme
- **Section**: A reusable component area within templates
- **Widget**: A display component that renders content
- **Content Type**: A structured data definition
- **Content Item**: An individual content entry
- **Page**: A specific web page with a template and content
- **Page Section**: An instance of a section on a specific page

### 1.3 System Relationships

```
Theme
  ├── Templates
  │     ├── Template Sections
  │     └── Template-specific CSS/JS
  ├── Widgets
  │     ├── Widget Views
  │     └── Widget Controllers
  └── Assets (images, global CSS/JS)

Content
  ├── Content Types
  │     ├── Content Type Fields
  │     └── Field Validation Rules
  └── Content Items
        └── Field Values

Pages
  ├── Page Metadata
  ├── Selected Template
  └── Page Sections
        └── Page Section Widgets
              └── Widget Content Queries
```

## 2. Theme System Architecture

### 2.1 Theme Structure

Each theme is a self-contained package with the following structure:

```
themes/
  ├── theme-name/
  │   ├── assets/
  │   │   ├── css/
  │   │   ├── js/
  │   │   └── images/
  │   ├── layouts/
  │   │   └── master.blade.php
  │   ├── sections/
  │   │   ├── header.blade.php
  │   │   ├── footer.blade.php
  │   │   └── [other-sections].blade.php
  │   ├── templates/
  │   │   ├── home.blade.php
  │   │   ├── blog.blade.php
  │   │   └── [other-templates].blade.php
  │   └── widgets/
  │       ├── text.blade.php
  │       ├── image.blade.php
  │       └── [other-widgets].blade.php
  └── theme-config.json
```

### 2.2 Theme Registration Process

When a theme is registered in the system:

1. The system scans the theme directory structure
2. Templates are registered in the `templates` table
3. Template sections are registered in the `template_sections` table
4. Widgets are registered in the `widgets` table
5. Theme assets are published to the public directory

### 2.3 Theme Database Schema

```
themes
  ├── id
  ├── name
  ├── slug
  ├── directory
  ├── version
  ├── author
  ├── is_active
  └── created_at/updated_at

templates
  ├── id
  ├── theme_id
  ├── name
  ├── slug
  ├── file_path
  ├── preview_image
  └── created_at/updated_at

template_sections
  ├── id
  ├── template_id
  ├── name
  ├── slug
  ├── position
  ├── section_type (full-width, multi-column, etc.)
  ├── column_layout (12, 6-6, 8-4, etc.)
  ├── is_repeatable
  └── created_at/updated_at
```

## 3. Content System Architecture

### 3.1 Content Structure

The content system manages structured data that populates the website:

```
Content Type
  ├── Fields
  │     ├── Text Fields
  │     ├── Rich Text Fields
  │     ├── Image Fields
  │     ├── File Fields
  │     ├── Date Fields
  │     ├── Boolean Fields
  │     ├── Number Fields
  │     └── Relationship Fields
  └── Validation Rules

Content Items
  └── Field Values
```

### 3.2 Content Type Registration

Content types are created by administrators and stored in the database:

1. Admin defines a content type (e.g., "Blog Post")
2. Admin adds fields to the content type
3. Each field has a type, validation rules, and settings
4. Content types are associated with compatible widgets

### 3.3 Content Database Schema

```
content_types
  ├── id
  ├── name
  ├── slug
  ├── description
  ├── icon
  └── created_at/updated_at

content_type_fields
  ├── id
  ├── content_type_id
  ├── name
  ├── slug
  ├── field_type (text, rich_text, image, etc.)
  ├── validation_rules
  ├── settings (JSON)
  ├── is_required
  ├── is_unique
  ├── position
  └── created_at/updated_at

content_items
  ├── id
  ├── content_type_id
  ├── title
  ├── slug
  ├── status (draft, published, etc.)
  ├── published_at
  ├── created_by
  ├── updated_by
  └── created_at/updated_at

content_field_values
  ├── id
  ├── content_item_id
  ├── content_type_field_id
  ├── value (or value references for complex types)
  └── created_at/updated_at
```

## 4. Widget System Architecture

### 4.1 Widget Structure

Widgets are the bridge between themes and content:

```
Widget
  ├── Definition
  │     ├── Name and Description
  │     ├── Theme Association
  │     └── Preview Image
  ├── Field Definitions
  │     ├── Required Content Fields
  │     ├── Widget Settings Fields
  │     └── Display Configuration Fields
  └── View Components
        ├── Widget View Template
        ├── Widget Controller Logic
        └── Widget-specific Assets
```

### 4.2 Widget Registration

Widgets are registered as part of theme registration:

1. The system scans the theme's widget directory
2. Each widget is registered in the `widgets` table
3. Widget field definitions are stored in `widget_field_definitions`
4. Widgets are associated with their parent theme

### 4.3 Widget Database Schema

```
widgets
  ├── id
  ├── theme_id
  ├── name
  ├── slug
  ├── description
  ├── icon
  ├── view_path
  ├── preview_image
  └── created_at/updated_at

widget_field_definitions
  ├── id
  ├── widget_id
  ├── name
  ├── slug
  ├── field_type
  ├── validation_rules
  ├── settings (JSON)
  ├── is_required
  ├── position
  └── created_at/updated_at

widget_content_type_associations
  ├── id
  ├── widget_id
  ├── content_type_id
  └── created_at/updated_at
```

## 5. Page Construction System

### 5.1 Page Structure

Pages combine templates, sections, widgets, and content:

```
Page
  ├── Metadata
  │     ├── Title, Description, Keywords
  │     ├── URL Slug
  │     └── Status (draft, published)
  ├── Template
  └── Page Sections
        ├── Section Position
        ├── Layout Configuration
        └── Section Widgets
              ├── Widget Instance
              ├── Widget Position
              ├── Widget Settings
              └── Content Query Parameters
```

### 5.2 Page Creation Process

1. Admin selects a template
2. System creates page sections for each template section
3. Admin configures each page section
4. Admin adds and configures widgets within each section
5. Admin configures content queries for each widget

### 5.3 Page Database Schema

```
pages
  ├── id
  ├── title
  ├── slug
  ├── template_id
  ├── is_homepage
  ├── meta_description
  ├── meta_keywords
  ├── status
  ├── published_at
  ├── created_by
  ├── updated_by
  └── created_at/updated_at

page_sections
  ├── id
  ├── page_id
  ├── template_section_id
  ├── position
  ├── column_span_override
  ├── column_offset_override
  ├── css_classes
  ├── background_color
  ├── background_image
  ├── padding
  ├── margin
  └── created_at/updated_at

page_section_widgets
  ├── id
  ├── page_section_id
  ├── widget_id
  ├── position
  ├── column_position (left, right, full)
  ├── settings (JSON)
  ├── content_query (JSON)
  ├── css_classes
  ├── padding
  ├── margin
  └── created_at/updated_at
```

## 6. Widget-Content Query System

### 6.1 Query System Overview

The widget-content query system is what allows widgets to dynamically display content:

```
Widget Instance
  ├── Associated Content Type
  ├── Query Parameters
  │     ├── Filters
  │     ├── Sorting
  │     ├── Pagination
  │     └── Relationships
  └── Display Settings
        ├── Layout Options
        ├── Styling Options
        └── Conditional Logic
```

### 6.2 Query Building Process

1. Each widget instance defines a content query configuration
2. This configuration specifies:
   - Which content type to query
   - Filter conditions (e.g., category, status, date range)
   - Sorting rules (e.g., newest first, alphabetical)
   - Pagination settings (e.g., items per page)
   - Related content to include (e.g., author, categories)

3. At render time, the query is executed to retrieve matching content
4. Retrieved content is passed to the widget's view template

### 6.3 Query Configuration Example

For a "Blog List" widget, a query might be configured as:

```json
{
  "content_type": "blog_post",
  "filters": [
    {"field": "status", "operator": "=", "value": "published"},
    {"field": "category_id", "operator": "=", "value": 5}
  ],
  "sort": [
    {"field": "published_at", "direction": "desc"}
  ],
  "pagination": {
    "items_per_page": 10,
    "load_more": true
  },
  "include": ["author", "categories", "featured_image"]
}
```

### 6.4 Advanced Query Features

1. **Dynamic Parameters**:
   - URL query parameters can modify widget queries
   - Example: `?category=5` could filter a blog list widget

2. **Context Awareness**:
   - Queries can include page context
   - Example: A "Related Products" widget knows the current product

3. **Aggregate Queries**:
   - Widgets can display aggregated data
   - Example: A "Category Stats" widget shows post counts by category

### 6.5 Query Execution Flow

```
Page Load
  ├── Identify Page Sections
  ├── Identify Widgets in Each Section
  └── For Each Widget:
        ├── Load Widget Configuration
        ├── Build Content Query
        ├── Execute Query Against Database
        ├── Process Retrieved Content
        └── Render Widget with Content
```

## 7. Layout & Dimensioning System

### 7.1 Multi-level Layout System

Layout definitions occur at multiple levels:

1. **Template Level**: Base grid structure
2. **Section Level**: Section dimensions and positioning
3. **Widget Level**: Widget-specific layout within sections

### 7.2 Layout Storage

Layout information is stored in both database and template files:

**Database Storage**:
- Column spans and layouts for template sections
- Position overrides for page sections
- Dimensional constraints for widgets
- Responsive behavior settings

**Template Files**:
- Grid system implementation
- Default layout structures
- CSS frameworks for positioning
- Responsive breakpoints

### 7.3 Layout Database Fields

**In Template Sections**:
```
template_sections
  ├── ...
  ├── section_type (full-width, multi-column)
  ├── column_layout (12, 6-6, 8-4)
  ├── max_widgets
  └── ...
```

**In Page Sections**:
```
page_sections
  ├── ...
  ├── column_span_override
  ├── column_offset_override
  ├── padding
  ├── margin
  ├── css_classes
  └── ...
```

**In Page Section Widgets**:
```
page_section_widgets
  ├── ...
  ├── column_position
  ├── padding
  ├── margin
  ├── min_height
  ├── max_height
  ├── css_classes
  └── ...
```

### 7.4 Responsive Behavior

The layout system includes responsive behavior definitions:

1. **Breakpoints**: Defined at theme level (SM, MD, LG, XL)
2. **Column Behavior**: How columns stack on smaller screens
3. **Visibility Rules**: Show/hide components at certain breakpoints
4. **Adaptive Sizing**: Adjusting dimensions based on screen size

## 8. Menu System

### 8.1 Menu as Specialized Content

Menus are implemented as a specialized content type with hierarchical capabilities:

```
Menu (Content Type)
  ├── Menu Items (Content Items)
  │     ├── Title
  │     ├── URL (internal or external)
  │     ├── Parent Item (for nesting)
  │     ├── Position
  │     ├── Target (same window or new tab)
  │     └── Conditions (when to show/hide)
  └── Menu Settings
        ├── Cache Duration
        ├── Mobile Behavior
        └── Authentication Requirements
```

### 8.2 Menu Widget Implementation

Menu widgets render menu content with specific display logic:

1. **Menu Widget Types**:
   - Horizontal Navigation
   - Vertical Navigation
   - Dropdown Navigation
   - Mobile Navigation
   - Mega Menu

2. **Menu Query System**:
   - Retrieves menu items in hierarchical structure
   - Applies conditional logic (e.g., show only to logged-in users)
   - Handles active state based on current page

### 8.3 Menu Database Schema

```
menus
  ├── id
  ├── name
  ├── slug
  ├── description
  └── created_at/updated_at

menu_items
  ├── id
  ├── menu_id
  ├── parent_id
  ├── title
  ├── url
  ├── target
  ├── icon
  ├── css_classes
  ├── position
  ├── conditions (JSON)
  └── created_at/updated_at
```

## 9. Rendering Pipeline

### 9.1 Rendering Process Flow

The full rendering pipeline for a page request:

```
HTTP Request
  ├── Route Resolution
  ├── Page Lookup
  ├── Authentication/Authorization Check
  ├── Page Controller
  │     ├── Load Page Model
  │     ├── Load Template
  │     └── Prepare View Data
  ├── View Rendering
  │     ├── Master Layout
  │     │     └── Template Rendering
  │     │           └── Section Rendering
  │     │                 └── Widget Rendering
  │     │                       ├── Content Query Execution
  │     │                       └── Widget View Rendering
  │     └── Asset Inclusion (CSS/JS)
  └── HTTP Response
```

### 9.2 Caching Strategy

Multiple caching layers improve performance:

1. **Page Cache**: Entire rendered pages for anonymous users
2. **Section Cache**: Individual rendered sections
3. **Widget Cache**: Individual rendered widgets
4. **Query Cache**: Results of content queries
5. **Content Cache**: Individual content items

Cache invalidation occurs when:
- Content is updated
- Widgets are reconfigured
- Page sections are modified
- Templates are updated

### 9.3 Rendering Optimizations

1. **Lazy Loading**: Content loaded only when scrolled into view
2. **Asset Bundling**: Combined and minified CSS/JS
3. **Image Optimization**: Responsive images with srcset
4. **Partial Page Updates**: AJAX for dynamic content updates

## 10. System Workflows

### 10.1 Theme Development Workflow

1. Create theme directory structure
2. Develop layouts, templates, sections, and widgets
3. Create theme configuration file
4. Register theme in CMS
5. Test with sample content
6. Activate theme

### 10.2 Content Modeling Workflow

1. Define content types based on information architecture
2. Create fields for each content type
3. Set validation rules and field settings
4. Associate content types with compatible widgets
5. Create content entry forms
6. Test content creation process

### 10.3 Page Building Workflow

1. Create new page
2. Select appropriate template
3. Configure page metadata
4. For each page section:
   - Adjust layout settings if needed
   - Add appropriate widgets
   - Configure widget settings
   - Set up content queries
5. Preview page
6. Publish when ready

### 10.4 Content Management Workflow

1. Create/edit content items
2. Fill in all required fields
3. Preview content in context
4. Set publication status and schedule
5. Publish or save as draft
6. Monitor content performance

### 10.5 System Update Process

1. Theme updates:
   - Update theme files
   - Run theme update process
   - System registers any new components

2. Content type updates:
   - Add/modify fields
   - Update validation rules
   - System handles schema migrations

3. Widget updates:
   - Update widget files
   - Run widget registration process
   - System updates widget field definitions

This document provides a comprehensive overview of the Realsys CMS architecture. For specific implementation details, please refer to the codebase documentation and API references.
