# Content Type & Widget Implementation Plan

## Overview

This document outlines the step-by-step implementation plan for creating a robust content type and widget system that supports the visual page builder. The content types and widgets will provide the data structure and functionality needed for the page builder while remaining accessible to power users who need more control.

## Core Philosophy

- **Support visual page builder**: Content types and widgets should work seamlessly with the visual interface
- **Default content types**: Pre-built content types for common use cases
- **Flexible widget system**: Widgets that can display any content type
- **Advanced user control**: Detailed configuration for power users
- **Theme integration**: Widgets that work across all themes

---

## Phase 1: Content Type System Foundation

### 1.1 Enhanced Content Type Management
**Goal**: Improve the content type management interface to match the template sections pattern

**Implementation Steps**:
1. **Update Content Type Show Page**
   ```php
   // Enhanced ContentTypeController@show
   public function show(ContentType $contentType)
   {
       $contentType->load([
           'fields' => function ($query) {
               $query->orderBy('position');
           },
           'contentItems' => function ($query) {
               $query->latest()->limit(5);
           },
           'widgets'
       ]);
       
       return view('admin.content_types.show', compact('contentType'));
   }
   ```

2. **Create Enhanced Show View**
   - Content type details section
   - **"Manage Fields" button** (like template sections)
   - **"Create Content Items" button**
   - Fields preview with edit/delete actions
   - Content items count and recent items
   - Associated widgets list

3. **Update Content Type Index**
   - Add content items count
   - Add fields count
   - Add associated widgets count
   - Quick actions for field management

### 1.2 Content Type Fields Management Interface
**Goal**: Create a drag-and-drop field management interface similar to template sections

**Implementation Steps**:
1. **Enhanced Fields Index Page**
   ```php
   // Enhanced ContentTypeFieldController@index
   public function index(ContentType $contentType)
   {
       $fields = $contentType->fields()->orderBy('position')->get();
       $fieldTypes = $this->getFieldTypes();
       
       return view('admin.content_type_fields.index', compact('contentType', 'fields', 'fieldTypes'));
   }
   ```

2. **Create Drag-and-Drop Interface**
   - Sortable field list using SortableJS
   - Field preview cards with type icons
   - Quick edit/delete actions
   - Field reordering with AJAX updates

3. **Enhanced Field Creation/Editing**
   - Field type selection with previews
   - Field validation rules builder
   - Field settings configuration
   - Field options management (for select/multiselect)

4. **Field Types to Implement**:
   ```
   - Text (single line, multi-line, rich text)
   - Number (integer, decimal, range)
   - Date/Time (date, time, datetime)
   - Boolean (checkbox, radio)
   - Select (dropdown, multi-select, radio group)
   - Image (single image, gallery, file)
   - URL/Link (internal, external)
   - Email/Phone
   - Color
   - JSON (for complex data)
   - Relation (to other content types)
   ```

### 1.3 Content Items Management
**Goal**: Create dynamic content item forms and management interface

**Implementation Steps**:
1. **Dynamic Form Generation**
   ```php
   // ContentItemController@create
   public function create(ContentType $contentType)
   {
       $fields = $contentType->fields()->orderBy('position')->get();
       
       return view('admin.content_items.create', compact('contentType', 'fields'));
   }
   ```

2. **Create Dynamic Form Builder**
   - Generate form fields based on content type definition
   - Handle different field types with appropriate inputs
   - Implement field validation
   - Add field help text and examples

3. **Content Items Management Interface**
   - Content items listing with search and filter
   - Content item preview and editing
   - Content item status management (draft, published, archived)
   - Bulk operations (delete, publish, archive)

4. **Content Item Forms**
   - Dynamic form generation based on content type fields
   - Rich text editor for text fields
   - Image upload and management
   - Date/time pickers
   - Color pickers
   - Relation selectors

---

## Phase 2: Widget System Implementation

### 2.1 Default Widget Library
**Goal**: Create a comprehensive library of default widgets for all themes

**Implementation Steps**:
1. **Create Default Widget Types**
   ```php
   // Default widget types that work across all themes
   $defaultWidgets = [
       'text' => [
           'name' => 'Text Block',
           'description' => 'Display text content with formatting',
           'content_types' => ['text', 'rich_text'],
           'settings' => ['text_align', 'font_size', 'color']
       ],
       'image' => [
           'name' => 'Image',
           'description' => 'Display single or multiple images',
           'content_types' => ['image', 'gallery'],
           'settings' => ['size', 'alignment', 'lightbox']
       ],
       'slider' => [
           'name' => 'Image Slider',
           'description' => 'Display images in a carousel',
           'content_types' => ['gallery'],
           'settings' => ['autoplay', 'speed', 'navigation']
       ],
       'hero' => [
           'name' => 'Hero Section',
           'description' => 'Full-width hero with background image',
           'content_types' => ['hero_content'],
           'settings' => ['height', 'background', 'overlay']
       ],
       'gallery' => [
           'name' => 'Gallery Grid',
           'description' => 'Display images in a grid layout',
           'content_types' => ['gallery'],
           'settings' => ['columns', 'spacing', 'lightbox']
       ],
       'map' => [
           'name' => 'Map',
           'description' => 'Display Google Maps',
           'content_types' => ['location'],
           'settings' => ['zoom', 'style', 'markers']
       ],
       'form' => [
           'name' => 'Contact Form',
           'description' => 'Display contact or newsletter form',
           'content_types' => ['form_config'],
           'settings' => ['fields', 'styling', 'redirect']
       ],
       'testimonials' => [
           'name' => 'Testimonials',
           'description' => 'Display customer testimonials',
           'content_types' => ['testimonial'],
           'settings' => ['layout', 'autoplay', 'navigation']
       ],
       'blog_list' => [
           'name' => 'Blog List',
           'description' => 'Display blog posts in a list',
           'content_types' => ['blog_post'],
           'settings' => ['posts_per_page', 'layout', 'pagination']
       ],
       'social_media' => [
           'name' => 'Social Media',
           'description' => 'Display social media feeds',
           'content_types' => ['social_config'],
           'settings' => ['platforms', 'posts_count', 'styling']
       ]
   ];
   ```

2. **Widget Registration System**
   ```php
   // WidgetService
   class WidgetService
   {
       public function registerDefaultWidgets()
       {
           foreach ($this->defaultWidgets as $slug => $config) {
               Widget::updateOrCreate(
                   ['slug' => $slug],
                   [
                       'name' => $config['name'],
                       'description' => $config['description'],
                       'widget_type' => 'default',
                       'settings_schema' => json_encode($config['settings']),
                       'content_types' => $config['content_types']
                   ]
               );
           }
       }
   }
   ```

3. **Widget Content Type Associations**
   - Create associations between widgets and content types
   - Allow multiple content types per widget
   - Widget-specific content type requirements

### 2.2 Widget Configuration Interface
**Goal**: Create an intuitive widget configuration interface

**Implementation Steps**:
1. **Widget Settings Panel**
   ```php
   // Widget configuration interface
   class WidgetSettingsController
   {
       public function configure(Widget $widget, PageSection $section)
       {
           $contentTypes = ContentType::whereIn('slug', $widget->content_types)->get();
           $settings = $widget->getSettingsSchema();
           
           return view('admin.widgets.configure', compact('widget', 'section', 'contentTypes', 'settings'));
       }
   }
   ```

2. **Dynamic Settings Forms**
   - Generate settings forms based on widget schema
   - Content type selection for data source
   - Widget-specific configuration options
   - Live preview of settings changes

3. **Widget Content Query Builder**
   - Visual query builder for content selection
   - Filter conditions (status, date, category, etc.)
   - Sorting options (date, title, custom)
   - Pagination settings
   - Limit and offset controls

### 2.3 Widget Rendering System
**Goal**: Create a flexible widget rendering system

**Implementation Steps**:
1. **Widget View Resolution**
   ```php
   // WidgetRenderer
   class WidgetRenderer
   {
       public function render(Widget $widget, array $settings = [], array $content = [])
       {
           $viewPath = $this->resolveWidgetView($widget);
           $data = $this->prepareWidgetData($widget, $settings, $content);
           
           return view($viewPath, $data)->render();
       }
       
       protected function resolveWidgetView(Widget $widget)
       {
           // Try theme-specific widget view first
           $themeView = "theme::widgets.{$widget->slug}";
           if (View::exists($themeView)) {
               return $themeView;
           }
           
           // Fall back to default widget view
           return "widgets.{$widget->slug}";
       }
   }
   ```

2. **Widget View Templates**
   - Create view templates for each widget type
   - Responsive design support
   - Theme customization support
   - Widget-specific styling options

3. **Content Query Execution**
   ```php
   // ContentQueryService
   class ContentQueryService
   {
       public function executeQuery(array $queryConfig)
       {
           $contentType = ContentType::where('slug', $queryConfig['content_type'])->first();
           
           $query = ContentItem::where('content_type_id', $contentType->id);
           
           // Apply filters
           if (isset($queryConfig['filters'])) {
               $query = $this->applyFilters($query, $queryConfig['filters']);
           }
           
           // Apply sorting
           if (isset($queryConfig['sort'])) {
               $query = $this->applySorting($query, $queryConfig['sort']);
           }
           
           // Apply pagination
           if (isset($queryConfig['limit'])) {
               $query->limit($queryConfig['limit']);
           }
           
           return $query->get();
       }
   }
   ```

---

## Phase 3: Integration with Visual Page Builder

### 3.1 Widget Library Integration
**Goal**: Integrate widgets with the visual page builder

**Implementation Steps**:
1. **Widget Library Sidebar**
   - Display available widgets in page builder
   - Widget categories and search
   - Widget preview thumbnails
   - Drag-and-drop to add widgets

2. **Widget Configuration in Page Builder**
   - Inline widget configuration
   - Content type selection
   - Settings panel integration
   - Live preview of changes

3. **Widget Content Editing**
   - Inline content editing for widgets
   - Content type form integration
   - Real-time content updates
   - Content validation

### 3.2 Content Type Integration
**Goal**: Integrate content types with the page builder

**Implementation Steps**:
1. **Content Type Selection**
   - Content type picker in widget configuration
   - Content type preview and description
   - Content type field mapping
   - Content type validation

2. **Dynamic Content Forms**
   - Generate forms based on content type fields
   - Inline form editing in page builder
   - Form validation and error handling
   - Content preview and formatting

3. **Content Management Integration**
   - Link to content management from page builder
   - Content item creation and editing
   - Content item status management
   - Content item reuse across pages

---

## Phase 4: Advanced Features

### 4.1 Content Type Relationships
**Goal**: Implement relationships between content types

**Implementation Steps**:
1. **Relationship Field Type**
   - One-to-one relationships
   - One-to-many relationships
   - Many-to-many relationships
   - Relationship display options

2. **Related Content Widgets**
   - Widgets that display related content
   - Relationship navigation
   - Related content filtering
   - Related content pagination

### 4.2 Widget Templates
**Goal**: Create reusable widget templates

**Implementation Steps**:
1. **Widget Template System**
   - Save widget configurations as templates
   - Widget template library
   - Widget template categories
   - Widget template sharing

2. **Widget Template Management**
   - Template creation and editing
   - Template application to widgets
   - Template versioning
   - Template import/export

### 4.3 Advanced Widget Features
**Goal**: Add advanced widget capabilities

**Implementation Steps**:
1. **Widget Conditions**
   - Conditional widget display
   - User role-based visibility
   - Date/time-based visibility
   - Custom condition rules

2. **Widget Caching**
   - Widget content caching
   - Cache invalidation rules
   - Cache performance optimization
   - Cache debugging tools

---

## Implementation Timeline

### Week 1-2: Content Type System Enhancement
- Update content type show page
- Create enhanced fields management interface
- Implement drag-and-drop field reordering

### Week 3-4: Content Items Management
- Create dynamic form generation
- Implement content items CRUD
- Add content item status management

### Week 5-6: Default Widget Library
- Create default widget types
- Implement widget registration system
- Create widget content type associations

### Week 7-8: Widget Configuration
- Create widget settings interface
- Implement content query builder
- Add widget configuration forms

### Week 9-10: Widget Rendering
- Create widget rendering system
- Implement widget view templates
- Add content query execution

### Week 11-12: Page Builder Integration
- Integrate widgets with page builder
- Add content type integration
- Implement inline editing

### Week 13-14: Advanced Features
- Add content type relationships
- Implement widget templates
- Add advanced widget features

### Week 15-16: Testing & Optimization
- Performance testing and optimization
- Bug fixes and refinements
- Documentation and training materials

---

## Technical Requirements

### Database Schema Updates
```sql
-- Enhanced content_type_fields table
ALTER TABLE content_type_fields ADD COLUMN position INT DEFAULT 0;
ALTER TABLE content_type_fields ADD COLUMN field_options JSON;

-- Enhanced widgets table
ALTER TABLE widgets ADD COLUMN widget_type VARCHAR(50) DEFAULT 'default';
ALTER TABLE widgets ADD COLUMN settings_schema JSON;
ALTER TABLE widgets ADD COLUMN content_types JSON;

-- Widget templates table
CREATE TABLE widget_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    widget_id BIGINT UNSIGNED,
    settings JSON,
    content_query JSON,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Frontend Requirements
- **JavaScript**: Alpine.js for reactive components
- **Drag-and-Drop**: SortableJS for field reordering
- **Form Builder**: Dynamic form generation
- **Rich Text Editor**: TinyMCE or CKEditor
- **Image Upload**: File upload with preview

### Backend Requirements
- **Laravel**: Core framework
- **JSON Handling**: For flexible content storage
- **Query Builder**: For content queries
- **File Management**: For image and file uploads
- **Caching**: For performance optimization

---

## Success Metrics

### Content Type System
- **Field Management**: Users can easily add, edit, and reorder fields
- **Content Creation**: Users can create content items without technical knowledge
- **Content Reuse**: Content can be reused across multiple pages and widgets

### Widget System
- **Widget Library**: Comprehensive library of useful widgets
- **Widget Configuration**: Intuitive widget configuration interface
- **Widget Performance**: Fast widget rendering and content loading

### Integration
- **Page Builder Integration**: Seamless integration with visual page builder
- **Content Type Integration**: Easy content type selection and configuration
- **User Experience**: Intuitive workflow for both casual and power users

---

## Conclusion

This content type and widget implementation plan provides a solid foundation for the visual page builder while maintaining the flexibility and power needed for complex content management. By creating default content types and widgets, we ensure that users can start building pages immediately while still having access to advanced features when needed.

The implementation prioritizes user experience and integration with the visual page builder, making the CMS both powerful and easy to use. 