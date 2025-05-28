# Template System Implementation

## Overview

The Template System is the second major component in our CMS implementation flow, following the Theme System. Templates define the overall structure and layout of pages within a theme, specifying where content and widgets can be placed.

## Current State Analysis

Based on the existing codebase and our implementation plan, we need to analyze the current state of template-related components:

1. **Template Model**: We need to verify if a Template model exists and what properties it has.
2. **Template Controller**: We need to check if a TemplateController exists or needs to be created.
3. **Template Views**: We need to determine if admin views for template management exist.
4. **Template-Theme Relationship**: We need to understand how templates are associated with themes.

## Relationship with Other Components

### Templates and Themes

- Each template **belongs to** a specific theme
- A theme can have **multiple templates**
- Templates inherit design elements and styles from their parent theme
- Templates are stored in the `templates` directory within a theme's directory

### Templates and Pages

- A template defines the structure that a page will use
- Multiple pages can use the same template
- When a page selects a template, it inherits the template's sections
- Templates define the available regions where content can be placed

### Templates and Sections

- Templates define **sections** (content areas)
- Each section can contain multiple widgets
- Sections have specific roles (header, footer, sidebar, main content, etc.)
- Section definitions include positions, sizes, and behavioral properties

### Templates and Widgets

- Templates don't directly interact with widgets
- Instead, templates define sections, and widgets are placed within those sections
- Templates may include default widgets in certain sections

## Core Components

### 1. Template Model

**Fields:**
- id (primary key)
- theme_id (foreign key to themes table)
- name (human-readable name)
- slug (URL-friendly identifier)
- file_path (path to the template file within theme directory)
- description (optional description)
- is_default (boolean indicating if this is the default template for its theme)
- settings (JSON field for template-specific settings)
- created_at/updated_at timestamps

**Relationships:**
- Belongs to a Theme
- Has many TemplateSections
- Has many Pages (that use this template)

### 2. TemplateSection Model

**Fields:**
- id (primary key)
- template_id (foreign key to templates table)
- name (human-readable name)
- key (unique identifier within the template)
- description (optional description)
- position (ordering within template)
- settings (JSON field for section-specific settings like width, height, etc.)
- created_at/updated_at timestamps

**Relationships:**
- Belongs to a Template
- Has many PageSections (instances of this section on pages)

### 3. TemplateController

Manages CRUD operations for templates, including:
- Listing templates (with filtering by theme)
- Creating new templates
- Editing template properties
- Viewing template details
- Setting a default template for a theme
- Duplicating templates
- Importing/exporting templates

### 4. TemplateSectionController

Manages sections within templates:
- Adding sections to templates
- Editing section properties
- Reordering sections
- Removing sections from templates

## Implementation Steps

### 1. Create/Update Database Migrations and Models

First, we need to ensure we have the proper database structure:
- Create or update the templates table
- Create or update the template_sections table
- Implement relationships between models

### 2. Implement Template and Section Controllers

Next, we need to implement the controllers for managing templates and their sections:
- Create/update TemplateController with all necessary methods
- Create/update TemplateSectionController for section management
- Implement validation rules for template and section data

### 3. Create Template Administration Views

Develop the admin interface for template management:
- Templates index view (list of templates, filterable by theme)
- Template create/edit form
- Template details view, showing sections and usage stats
- Section management interface (add, edit, remove sections)
- Template preview functionality

### 4. Implement Template Rendering System

Create the functionality to render templates in the frontend:
- Template file loading mechanism
- Section rendering
- Template inheritance (for nested templates)
- Template caching for performance

### 5. Create Template-Page Integration

Implement the connection between templates and pages:
- Add template selection to page create/edit forms
- Handle template switching for existing pages
- Sync page sections when template changes

### 6. Develop Template Settings Interface

Create a UI for managing template-specific settings:
- Global template settings
- Per-section settings
- Default widget settings for sections

## UI Design and User Flow

### Template Management UI

1. **Templates Index:**
   - List of templates grouped by theme
   - Thumbnail preview for each template
   - Filter by theme, name, usage status
   - Actions: Create, Edit, View, Set Default, Delete

2. **Template Creation:**
   - Select parent theme
   - Name and describe the template
   - Choose base template to clone (optional)
   - Define initial sections or import from file

3. **Template Detail View:**
   - Preview of template structure
   - List of all sections with properties
   - Usage statistics (which pages use this template)
   - Section management tools (add, edit, reorder)

4. **Section Management:**
   - Drag-and-drop interface for arranging sections
   - Properties panel for each section
   - Section type selection (header, footer, sidebar, content)
   - Default widget assignment

### Page-Template Integration UI

1. **Template Selection in Page Editor:**
   - Visual selection of available templates
   - Preview of template structure before selection
   - Warning when changing templates on existing pages

2. **Section Configuration within Pages:**
   - Inherited from template but customizable per page
   - Widget placement within sections
   - Section visibility toggles

## File Structure

Templates will be stored in the theme directory with this structure:

```
themes/
  theme-name/
    templates/
      default.blade.php
      home.blade.php
      blog.blade.php
      product.blade.php
    sections/
      header.blade.php
      footer.blade.php
      sidebar.blade.php
      ...
```

## Implementation Considerations

### Template Inheritance

Templates may inherit from parent templates, allowing for:
- Base templates with common structure
- Specialized templates that extend base templates
- Override specific sections while inheriting others

### Template Versions

Consider implementing template versioning to:
- Track changes to templates over time
- Allow rollback to previous versions
- Preview changes before publishing

### Template Import/Export

Provide functionality to:
- Export templates for reuse in other installations
- Import templates from files or repositories
- Share templates between themes

### Responsive Considerations

Ensure templates handle:
- Different screen sizes and devices
- Conditional display of sections based on viewport
- Mobile-specific section arrangements

## Next Steps

After implementing the Template System, we'll proceed to:

1. Page System implementation
2. Section System implementation
3. Widget System implementation
4. Content-to-presentation integration

This progressive approach ensures each component builds upon the previous one, creating a cohesive content management and presentation system.
