# GrapesJS Page Builder Implementation Plan - Dual Rendering System

## Overview

This plan implements a **Dual Rendering System** that maintains the existing Laravel/Blade frontend while creating a parallel GrapesJS-compatible editing environment. The key principle is: **Same Data, Two Renderers**.

## Core Architecture

### **Frontend System (Existing)**
- **Purpose**: Production website rendering
- **Technology**: Laravel + Blade + TemplateRenderer + WidgetService
- **Data Source**: Database (PageSections, PageSectionWidgets, ContentItems)
- **Output**: Optimized HTML with server-side processing

### **Builder System (New)**
- **Purpose**: Visual editing and preview
- **Technology**: GrapesJS + JavaScript Components + Preview APIs
- **Data Source**: Same database via JSON APIs
- **Output**: Interactive components for editing

## Implementation Phases

---

## **Phase 1: Data Abstraction Layer**

### **1.1 Widget Schema Definition**

**Goal**: Create standardized widget definitions that work in both systems.

**Implementation**:
```php
// New: app/Services/WidgetSchemaService.php
// Generates widget schemas for GrapesJS consumption
```

**Widget Schema Structure**:
```json
{
  "id": "counter-widget",
  "name": "Counter Statistics Widget",
  "category": "Statistics",
  "icon": "ri-bar-chart-line",
  "fields": [
    {
      "name": "counters",
      "type": "repeater",
      "label": "Counter Items",
      "subfields": [
        {"name": "icon", "type": "image", "label": "Icon"},
        {"name": "top_text", "type": "text", "label": "Label"},
        {"name": "counter_number", "type": "number", "label": "Number"}
      ]
    }
  ],
  "preview_template": "counter-preview.html",
  "grapesjs_component": "CounterComponent"
}
```

**API Endpoints**:
- `GET /admin/api/widgets/schemas` - All widget schemas
- `GET /admin/api/widgets/{id}/schema` - Single widget schema
- `GET /admin/api/widgets/{id}/fields` - Widget field definitions

### **1.2 Widget Preview API**

**Goal**: Generate widget previews that match frontend exactly but work in GrapesJS.

**Implementation**:
```php
// Enhanced: app/Http/Controllers/Api/WidgetController.php
// New method: renderWidgetPreview()
```

**Preview Rendering Logic**:
1. **Fetch Widget Data**: Use existing WidgetService to get field values
2. **Render Blade Template**: Generate HTML using existing logic
3. **CSS Scoping**: Wrap in GrapesJS-compatible containers
4. **Asset Injection**: Include necessary CSS/JS references
5. **Interactive Markers**: Add data attributes for editing

**API Endpoints**:
- `GET /admin/api/widgets/{id}/preview` - Rendered widget preview
- `POST /admin/api/widgets/{id}/preview` - Preview with custom data
- `GET /admin/api/widgets/{id}/preview-data` - Sample data for preview

### **1.3 Section Schema API**

**Goal**: Expose section structures for GrapesJS layout system.

**Section Schema Structure**:
```json
{
  "id": "hero-section",
  "name": "Hero Header",
  "type": "full-width",
  "columns": [
    {
      "class": "col-12",
      "widgets": [
        {
          "widget_id": 3,
          "widget_type": "featuredimage",
          "position": 1,
          "settings": {...},
          "content_query": {...}
        }
      ]
    }
  ]
}
```

**API Endpoints**:
- `GET /admin/api/pages/{id}/sections/schemas` - Section schemas with widget data
- `GET /admin/api/sections/{id}/schema` - Single section schema

---

## **Phase 2: GrapesJS Component System**

### **2.1 Widget Component Factory**

**Goal**: Create JavaScript components that mirror Blade widgets.

**Component Structure**:
```javascript
// New: public/assets/admin/js/components/WidgetComponents.js

class CounterWidgetComponent {
  static getDefinition() {
    return {
      type: 'counter-widget',
      model: {
        defaults: {
          tagName: 'div',
          attributes: { 'data-widget-type': 'counter' },
          components: [/* placeholder structure */]
        },
        init() {
          this.loadPreview();
        },
        async loadPreview() {
          // Fetch preview HTML from API
          // Replace placeholder with actual content
          // Maintain editability markers
        }
      }
    };
  }
}
```

**Component Features**:
- **Preview Loading**: Fetch rendered HTML from preview API
- **Edit Markers**: Maintain data attributes for configuration
- **Responsive Behavior**: Handle different device previews
- **Error Handling**: Graceful fallbacks for failed previews

### **2.2 Dynamic Component Registration**

**Goal**: Automatically register all available widgets as GrapesJS components.

**Registration Flow**:
1. **Fetch Widget Schemas**: Load all available widgets from API
2. **Generate Components**: Create GrapesJS component definitions
3. **Register Components**: Add to GrapesJS component manager
4. **Populate Block Manager**: Add to drag-and-drop palette

### **2.3 Section Component System**

**Goal**: Create section components that can contain widgets.

**Section Component Features**:
- **Drop Zones**: Accept widget components
- **Layout Management**: Handle different column layouts
- **Visual Identification**: Section headers and boundaries
- **Responsive Handling**: Different layouts per device

---

## **Phase 3: Theme Integration System**

### **3.1 Theme Asset Pipeline**

**Goal**: Inject theme styles into GrapesJS canvas for accurate preview.

**Implementation Strategy**:
```php
// Enhanced: app/Http/Controllers/Api/ThemeController.php
// New methods: getCanvasStyles(), getCanvasScripts()
```

**Asset Processing**:
1. **CSS Compilation**: Combine all theme CSS files
2. **Scope Isolation**: Prevent conflicts with GrapesJS styles
3. **Canvas Injection**: Direct iframe style injection
4. **Dynamic Loading**: Load assets based on active theme

### **3.2 Canvas Styling System**

**Goal**: Make GrapesJS canvas look identical to frontend.

**Styling Approach**:
- **CSS Injection**: Direct style injection into canvas iframe
- **Asset Prefixing**: Scope theme styles to canvas only
- **Background Handling**: Proper background image rendering
- **Responsive Styles**: Media queries for device preview

### **3.3 Font and Asset Loading**

**Goal**: Ensure all theme assets load properly in canvas.

**Asset Types**:
- **Fonts**: Web fonts and icon fonts
- **Images**: Background images and assets
- **JavaScript**: Theme-specific functionality
- **CSS**: All theme styling

---

## **Phase 4: Real-Time Preview System**

### **4.1 Change Detection**

**Goal**: Update previews when content or settings change.

**Detection Events**:
- **Widget Configuration**: Settings panel changes
- **Content Updates**: Field value modifications
- **Layout Changes**: Section/widget repositioning
- **Device Switching**: Responsive preview updates

### **4.2 Preview Refresh System**

**Goal**: Efficiently update widget previews without full reload.

**Refresh Strategy**:
1. **Debounced Updates**: Prevent excessive API calls
2. **Selective Refresh**: Only update changed widgets
3. **Loading States**: Show loading indicators during updates
4. **Error Recovery**: Handle failed preview updates

### **4.3 Auto-Save Integration**

**Goal**: Automatically save changes while editing.

**Save Strategy**:
- **Incremental Saves**: Save individual changes
- **Conflict Resolution**: Handle concurrent edits
- **Version Control**: Track change history
- **Rollback Capability**: Undo/redo functionality

---

## **Phase 5: Content Management Integration**

### **5.1 Widget Configuration System**

**Goal**: Provide rich editing interface for widget settings.

**Configuration Features**:
- **Field Type Handling**: Different input types (text, image, repeater)
- **Content Picker**: Select existing content items
- **Media Management**: Image/file selection
- **Preview Updates**: Real-time preview as settings change

### **5.2 Content Query Builder**

**Goal**: Allow editors to configure widget data sources.

**Query Builder Features**:
- **Content Type Selection**: Choose data source
- **Filter Configuration**: Set content filters
- **Sorting Options**: Configure content ordering
- **Limit Settings**: Set number of items to display

### **5.3 Field Mapping System**

**Goal**: Map content fields to widget display fields.

**Mapping Features**:
- **Automatic Mapping**: Intelligent field matching
- **Manual Override**: Custom field assignments
- **Repeater Handling**: Map repeater subfields
- **Image Resolution**: Convert media IDs to URLs

---

## **Phase 6: Advanced Features**

### **6.1 Multi-Device Editing**

**Goal**: Edit content for different device sizes.

**Device Features**:
- **Responsive Preview**: Desktop, tablet, mobile views
- **Device-Specific Content**: Hide/show elements per device
- **Responsive Settings**: Different settings per breakpoint

### **6.2 Performance Optimization**

**Goal**: Ensure smooth editing experience.

**Optimization Strategies**:
- **Preview Caching**: Cache rendered widget previews
- **Lazy Loading**: Load previews on demand
- **Asset Optimization**: Minimize CSS/JS in canvas
- **Memory Management**: Prevent memory leaks

### **6.3 Advanced Editing Tools**

**Goal**: Provide professional editing capabilities.

**Advanced Tools**:
- **Global Styles**: Site-wide styling options
- **Custom CSS**: Advanced styling capabilities
- **Animation Controls**: Add animations to elements
- **SEO Tools**: Meta descriptions, alt tags, etc.

---

## **Technical Implementation Details**

### **Database Schema Considerations**

**No Changes Required**: The dual rendering system uses existing database structure:
- `page_sections` - Section definitions
- `page_section_widgets` - Widget instances with settings
- `content_items` - Content data
- `widgets` - Widget definitions

### **API Response Formats**

**Widget Preview Response**:
```json
{
  "success": true,
  "html": "<div class='widget-counter'>...</div>",
  "css": ".widget-counter { ... }",
  "js": "// Widget-specific JavaScript",
  "data": {
    "widget_id": 1,
    "settings": {...},
    "content": {...}
  }
}
```

**Section Schema Response**:
```json
{
  "success": true,
  "section": {
    "id": 1,
    "name": "Hero Header",
    "type": "full-width",
    "widgets": [
      {
        "id": 1,
        "type": "featuredimage",
        "position": 1,
        "preview_url": "/admin/api/widgets/1/preview"
      }
    ]
  }
}
```

### **Error Handling Strategy**

**Graceful Degradation**:
- **Preview Failures**: Show widget placeholder with error message
- **API Timeouts**: Implement retry logic with exponential backoff
- **Theme Loading**: Fallback to basic styling if theme fails
- **Asset Missing**: Provide default assets for missing resources

### **Security Considerations**

**Access Control**:
- **API Authentication**: All preview APIs require admin authentication
- **Content Validation**: Validate all content before rendering
- **XSS Prevention**: Sanitize user input in previews
- **CSRF Protection**: Include CSRF tokens in all API calls

---

## **Success Metrics**

### **Technical Metrics**
- **Preview Load Time**: < 500ms for widget previews
- **Canvas Responsiveness**: < 100ms for interactions
- **Memory Usage**: < 100MB for typical editing session
- **API Response Time**: < 200ms for preview APIs

### **User Experience Metrics**
- **Visual Accuracy**: 99% match between canvas and frontend
- **Edit Workflow**: Complete page edit in < 5 minutes
- **Learning Curve**: New users productive in < 30 minutes
- **Error Rate**: < 1% preview generation failures

---

## **Migration Strategy**

### **Phase 1**: Implement APIs and basic preview system
### **Phase 2**: Add widget components and basic editing
### **Phase 3**: Integrate theme system and styling
### **Phase 4**: Add advanced features and optimization
### **Phase 5**: User testing and refinement

This dual rendering approach ensures that we maintain the robust, optimized frontend system while providing a true WYSIWYG editing experience that editors can use confidently.
