# Content-Driven CMS Architecture: Overview

## Core Concept

This document outlines the architecture for refactoring the CMS to separate content from presentation. The key concept is:

- **Content**: Pure data stored in dedicated models and tables
- **Widgets**: Visual components that render content based on query parameters
- **Decoupling**: Content exists independently of how it's displayed

## System Components

### 1. Content Management System

The content management system consists of:

- **Content Types**: Define the structure of content (e.g., Post, Product, Team Member)
- **Content Fields**: Define the fields for each content type
- **Content Items**: Actual content data entries
- **Field Values**: The values for each field on a content item

### 2. Presentation System

The presentation system remains hierarchical:

- **Themes**: Overall design system with assets, templates, and global styles
- **Templates**: Page layouts within a theme
- **Pages**: Site structure with metadata and hierarchical organization
- **Page Sections**: Defined areas within page templates
- **Widgets**: Components that display content in specific ways

### 3. Content-to-Presentation Bridge

The bridge between content and presentation consists of:

- **Widget Content Queries**: Settings to determine what content to display
- **Widget Display Settings**: Settings to determine how to display the content
- **Content Renderers**: Logic to transform content items into displayable HTML

## Core Architecture Diagram

```
[CONTENT LAYER]
Content Types → Content Fields → Content Items → Field Values

[BRIDGE LAYER]
                   ↓
Widget Content Queries → Widget Display Settings

[PRESENTATION LAYER]
                   ↓
Themes → Templates → Pages → Page Sections → Widgets
```

## Database Structure

### Content Tables

- **content_types**
  - id, name, key, description, created_at, updated_at
  
- **content_type_fields**
  - id, content_type_id, name, key, type, required, options, order_index, created_at, updated_at
  
- **content_items**
  - id, content_type_id, title, slug, status, created_by, updated_by, created_at, updated_at, deleted_at
  
- **content_field_values**
  - id, content_item_id, field_id, value, created_at, updated_at

### Presentation Tables (Existing with Modifications)

- **widgets (modified)**
  - id, name, widget_type_id, ~~page_section_id~~ (removed), status, content_query_id (added), display_settings_id (added), created_by, updated_by, created_at, updated_at, deleted_at
  
- **widget_content_queries (new)**
  - id, widget_id, content_type_id, filter_settings, limit, offset, order_by, order_direction, created_at, updated_at
  
- **widget_display_settings (new)**
  - id, widget_id, layout, style, view_mode, created_at, updated_at

## Key Relationships

- Content Types have many Content Fields
- Content Types have many Content Items
- Content Items have many Field Values
- Widgets have one Content Query
- Widgets have one Display Settings
- Page Sections have many Widgets (through pivot table)

## Content Types (Initial Implementation)

1. **Basic Page Content**
   - Fields: Title, Body, Featured Image, Meta Description

2. **Blog Post**
   - Fields: Title, Body, Featured Image, Categories, Tags, Author, Publication Date

3. **Team Member**
   - Fields: Name, Position, Bio, Photo, Social Links

4. **Testimonial**
   - Fields: Author, Position, Company, Quote, Rating, Photo

5. **Service**
   - Fields: Title, Description, Icon, Features List

## Widget Types (Initial Implementation)

1. **Content Display**
   - Single content item display with configurable fields

2. **Content List**
   - List of content items with filtering and sorting options

3. **Content Grid**
   - Grid display of content items with filtering and layout options

4. **Featured Content**
   - Showcase a single content item with prominent styling

5. **Content Carousel**
   - Rotating display of multiple content items

## Migration Strategy

The migration from the current architecture to the new one will be done in phases:

1. Create the content management infrastructure
2. Add content query and display settings to widgets
3. Migrate existing widget field values to the content system
4. Update widget rendering to use content queries
5. Update admin interfaces to reflect the new architecture

Each phase will be implemented with backward compatibility to ensure the site continues to function during the transition.
