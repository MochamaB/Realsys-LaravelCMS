# GridStack.js Page Builder Implementation Plan

## 📋 Overview

This plan outlines the **complete replacement of GrapesJS with GridStack.js** for the RealsysCMS page builder. The implementation follows a **logical, sequential approach** where each step builds upon the previous ones, ensuring stable development and thorough testing.

**Key Benefits of GridStack.js:**
- ✅ **Simpler Architecture** - Less complex than GrapesJS
- ✅ **Better Performance** - Lightweight and fast
- ✅ **Grid-based Layout** - Perfect for responsive design
- ✅ **Easy Integration** - Works seamlessly with existing APIs
- ✅ **True WYSIWYG** - Exact frontend rendering in designer

## 🏗️ Architecture Overview

### 📐 Layout Structure (Inspired by Media Library)

The GridStack designer follows a **responsive layout pattern** inspired by the media library, maximizing the preview canvas while keeping all tools accessible:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Designer Toolbar                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│  Left Sidebar  │           Canvas Area           │ Right Sidebar (Offcanvas) │
│  (Collapsible) │        (Full Width)            │                         │
│                │                                 │                         │
│  • Widget Lib  │                                 │ • Properties Panel      │
│  • Section Lib │                                 │ • Content Manager      │
│  • Templates   │                                 │ • Style Editor         │
│                │                                 │                         │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Layout Features:**
- **Maximized Canvas**: Canvas takes 70-85% of available width
- **Offcanvas Sidebars**: Right sidebar slides in when needed
- **Collapsible Left Sidebar**: Toggle for mobile/tablet views
- **Responsive Design**: Adapts to all screen sizes
- **Professional UX**: Follows established admin patterns

**File Structure:**
```
resources/views/admin/pages/designer/
├── _toolbar.blade.php              # Designer toolbar
├── _left_sidebar.blade.php         # Widget library & section templates
├── _canvas_area.blade.php          # Main preview canvas
├── _right_sidebar.blade.php        # Properties & content panels
├── _widget_config_modal.blade.php  # Widget configuration modal
├── _section_templates_modal.blade.php # Section template selection
├── _content_selection_modal.blade.php # Content selection interface
└── _responsive_preview_modal.blade.php # Full preview in new tab
```

### 🔧 Technical Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        GridStack Page Builder                               │
├─────────────────────────────────────────────────────────────────────────────┤
│ [Sections Management] │     [GridStack Canvas]      │  [Properties Panel]   │
│ ┌─────────────────┐   │  ┌─────────────────────────┐  │  ┌───────────────┐   │
│ │ ► Section 1     │   │  │ Section 1: Hero        │  │  │ Widget Props  │   │
│ │ ► Section 2     │   │  │ ┌─────┐ ┌─────┐       │  │  │ - Content     │   │
│ │ ► Add Section   │   │  │ │ Hero│ │Image│       │  │  │ - Style       │   │
│ │                 │   │  │ └─────┘ └─────┘       │  │  │ - Position    │   │
│ │ Widget Library: │   │  │ Section 2: Content    │  │  │               │   │
│ │ • Text Widget   │   │  │ ┌─────────────┐       │  │  │ Section Props │   │
│ │ • Image Widget  │   │  │ │ Text Block  │       │  │  │ - Background  │   │
│ │ • Counter      │   │  │ └─────────────┘       │  │  │ - Spacing     │   │
│ └─────────────────┘   │  └─────────────────────────┘  │  └───────────────┘   │
├─────────────────────────────────────────────────────────────────────────────┤
│                     Existing APIs (Unchanged)                              │
│  • Page Sections API    • Widget Preview API    • Theme Assets API         │
│  • Widget Schema API    • Content Management    • Database Models          │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Implementation Phases

### ✅ Phase 1: Foundation Setup (COMPLETED)
**Goal**: Set up basic GridStack infrastructure and routing
**Status**: ✅ **IMPLEMENTED**

### 🔄 Phase 2: Section Management System  
**Goal**: Build the core section system - sections are the containers for widgets
**Status**: ⚠️ **PARTIALLY IMPLEMENTED**

### 🔄 Phase 3: Widget Library & Drag-Drop
**Goal**: Create widget library and basic drag-drop to sections
**Status**: ⚠️ **PARTIALLY IMPLEMENTED**

### ⏳ Phase 4: Widget Configuration System
**Goal**: Add widget editing, configuration, and content management
**Status**: 🔧 **IN PROGRESS**

### ⏳ Phase 5: Live Preview & Theme Integration
**Goal**: Real widget previews with theme styling
**Status**: 📋 **PLANNED**

### ⏳ Phase 6: Advanced Features & Polish
**Goal**: Responsive design, save/load, performance optimization
**Status**: 📋 **PLANNED**

---

## Phase 1: Foundation Setup ✅ COMPLETED

### Step 1.1: Install GridStack.js Dependencies ✅ COMPLETED
**Files Created:**
- ✅ `public/assets/admin/libs/gridstack/dist/` (GridStack files)

### Step 1.2: Create GridStack Designer View ✅ COMPLETED  
**Files Created:**
- ✅ `resources/views/admin/pages/gridstack-designer.blade.php`
- ✅ Complete HTML structure with sidebar, canvas, properties panel

### Step 1.3: Create Designer CSS ✅ COMPLETED
**Files Created:**
- ✅ `public/assets/admin/css/gridstack-designer.css`
- ✅ Full styling for all UI components

### Step 1.4: Update Routes ✅ COMPLETED
**Files Modified:**
- ✅ `routes/admin.php` - Added GridStack designer route

### Step 1.5: Update PageController ✅ COMPLETED
**Files Modified:**  
- ✅ `app/Http/Controllers/Admin/PageController.php` - Added gridstackDesigner method

**✅ Testing Phase 1:**
- ✅ Navigate to `/admin/pages/{id}/gridstack-designer`
- ✅ Should see designer interface with sidebar, canvas, properties panel
- ✅ GridStack CSS and JS should load without errors

---

## Phase 2: Section Management System

### Step 2.1: Create Section Data Structure ⚠️ NEEDS IMPLEMENTATION
**Goal**: Define how sections are stored and managed in the database with GridStack positioning

**🏗️ Architecture Decision: Two-Level Grid System**
- **PageSection**: Section containers (main GridStack level)
- **PageSectionWidget**: Widget placement within sections (nested GridStack level)

**Files to Create/Modify:**
- 📝 Database migration for `page_sections` table updates
- 📝 Database migration for `page_section_widgets` table updates
- 📝 `PageSection` model enhancements
- 📝 `PageSectionWidget` model enhancements

**📊 Enhanced Database Structure:**

**PageSection (Section Container Level):**
```sql
page_sections:
-- Existing fields
- id, page_id, template_section_id
- name, position
- background_color, padding, margin, css_classes
- column_span_override, column_offset_override
- created_at, updated_at

-- NEW: GridStack Section Positioning
- grid_x          // Section X position in page grid
- grid_y          // Section Y position in page grid  
- grid_w          // Section width in grid units (default: 12)
- grid_h          // Section height in grid units
- grid_id         // Unique GridStack section ID
- grid_config     // JSON: {columns: 12, cellHeight: 80, etc.}
- allows_widgets  // Boolean: Can contain widgets?
- widget_types    // JSON: Allowed widget types ['text', 'image']
```

**PageSectionWidget (Widget Positioning Level):**
```sql
page_section_widgets:
-- Existing fields
- id, page_section_id, widget_id
- settings, content_query
- css_classes, padding, margin, min_height, max_height
- created_at, updated_at

-- NEW: GridStack Widget Positioning
- grid_x          // Widget X position within section
- grid_y          // Widget Y position within section
- grid_w          // Widget width in grid units
- grid_h          // Widget height in grid units
- grid_id         // Unique GridStack widget ID
- column_position // For multi-column section layouts
- min_width       // Minimum grid width constraint
- max_width       // Maximum grid width constraint
- locked_position // Boolean: Prevent dragging
- resize_handles  // JSON: ['se', 'sw'] - Resize handle config
```

**🔄 Migration Strategy:**
1. **Add new GridStack fields** to both tables
2. **Migrate existing position data** to grid format
3. **Generate grid_id values** for existing records
4. **Set default grid configurations** for sections

**🧪 Test Step 2.1:**
- ✅ Database migrations run successfully
- ✅ New GridStack fields exist in both tables
- ✅ Existing data migrated to grid format
- ✅ Model relationships work with new fields
- ✅ Grid IDs are unique and properly formatted

### Step 2.1a: Create Database Migrations ⚠️ NEEDS IMPLEMENTATION
**Goal**: Add GridStack positioning fields to database tables

**Files to Create:**
- 📝 `database/migrations/add_gridstack_fields_to_page_sections_table.php`
- 📝 `database/migrations/add_gridstack_fields_to_page_section_widgets_table.php`
- 📝 `database/migrations/migrate_existing_position_data.php`

**Migration Details:**
```php
// PageSection GridStack fields
Schema::table('page_sections', function (Blueprint $table) {
    $table->integer('grid_x')->default(0)->after('position');
    $table->integer('grid_y')->default(0)->after('grid_x');
    $table->integer('grid_w')->default(12)->after('grid_y');
    $table->integer('grid_h')->default(4)->after('grid_w');
    $table->string('grid_id')->unique()->after('grid_h');
    $table->json('grid_config')->nullable()->after('grid_id');
    $table->boolean('allows_widgets')->default(true)->after('grid_config');
    $table->json('widget_types')->nullable()->after('allows_widgets');
});

// PageSectionWidget GridStack fields
Schema::table('page_section_widgets', function (Blueprint $table) {
    $table->integer('grid_x')->default(0)->after('content_query');
    $table->integer('grid_y')->default(0)->after('grid_x');
    $table->integer('grid_w')->default(6)->after('grid_y');
    $table->integer('grid_h')->default(3)->after('grid_w');
    $table->string('grid_id')->after('grid_h');
    $table->integer('column_position')->nullable()->after('grid_id');
    $table->integer('min_width')->nullable()->after('column_position');
    $table->integer('max_width')->nullable()->after('min_width');
    $table->boolean('locked_position')->default(false)->after('max_width');
    $table->json('resize_handles')->nullable()->after('locked_position');
});
```

**🧪 Test Step 2.1a:**
- ✅ Migrations run without errors
- ✅ All new fields added with correct types
- ✅ Default values applied to existing records
- ✅ Unique constraints work for grid_id fields

### Step 2.1b: Update Model Classes ⚠️ NEEDS IMPLEMENTATION
**Goal**: Enhance model classes with GridStack functionality

**Files to Modify:**
- 📝 `app/Models/PageSection.php`
- 📝 `app/Models/PageSectionWidget.php`

**Model Enhancements:**
```php
// PageSection.php additions
protected $fillable = [
    // ... existing fields
    'grid_x', 'grid_y', 'grid_w', 'grid_h', 'grid_id',
    'grid_config', 'allows_widgets', 'widget_types'
];

protected $casts = [
    'grid_config' => 'array',
    'widget_types' => 'array',
    'allows_widgets' => 'boolean'
];

// GridStack helper methods
public function getGridPosition(): array
public function setGridPosition(int $x, int $y, int $w, int $h): void
public function generateGridId(): string
public function canAcceptWidget(string $widgetType): bool

// PageSectionWidget.php additions
protected $fillable = [
    // ... existing fields
    'grid_x', 'grid_y', 'grid_w', 'grid_h', 'grid_id',
    'column_position', 'min_width', 'max_width', 
    'locked_position', 'resize_handles'
];

protected $casts = [
    'resize_handles' => 'array',
    'locked_position' => 'boolean'
];

// GridStack helper methods
public function getGridPosition(): array
public function setGridPosition(int $x, int $y, int $w, int $h): void
public function generateGridId(): string
public function canResize(): bool
```

**🧪 Test Step 2.1b:**
- ✅ Models load without errors
- ✅ GridStack helper methods work correctly
- ✅ Casting works for JSON fields
- ✅ Relationships still function properly

### Step 2.2: Create Section Templates ⚠️ NEEDS IMPLEMENTATION  
**Goal**: Predefined section layouts with GridStack configurations

**Files to Create:**
- 📝 `app/Services/SectionTemplateService.php`
- 📝 `config/section_templates.php`

**Enhanced Template Structure:**
```php
// Section templates with GridStack configs
'single-column' => [
    'name' => 'Single Column',
    'icon' => 'ri-layout-column-line',
    'description' => 'Full width single column layout',
    'grid_config' => [
        'column' => 12,
        'cellHeight' => 80,
        'verticalMargin' => 10,
        'horizontalMargin' => 10,
        'minRow' => 1,
        'acceptWidgets' => true,
        'resizable' => ['handles' => 'se, sw'],
        'animate' => true
    ],
    'default_size' => ['w' => 12, 'h' => 4],
    'widget_constraints' => [
        'allowed_types' => ['text', 'image', 'counter', 'gallery'],
        'max_widgets' => null,
        'default_widget_size' => ['w' => 6, 'h' => 3]
    ]
],
'two-columns' => [
    'name' => 'Two Columns',
    'icon' => 'ri-layout-2-line', 
    'description' => '50/50 two column layout',
    'grid_config' => [
        'column' => 12,
        'cellHeight' => 80,
        'float' => false, // Prevent floating for column structure
    ],
    'default_size' => ['w' => 12, 'h' => 4],
    'widget_constraints' => [
        'column_layout' => true,
        'columns' => [
            ['span' => 6, 'offset' => 0],
            ['span' => 6, 'offset' => 6]
        ]
    ]
]
```

**🧪 Test Step 2.2:**
- ✅ Templates load from configuration
- ✅ GridStack configurations are valid
- ✅ Template constraints work properly
- ✅ Section creation uses template defaults

### Step 2.3: Implement Section Management UI ⚠️ PARTIALLY IMPLEMENTED
**Goal**: Section creation, editing, reordering, deletion with GridStack integration

**Files Modified:**
- ⚠️ `public/assets/admin/js/gridstack/gridstack-page-builder.js`

**Enhanced Features:**
- ✅ Section drag handles for reordering
- 📝 **NEW**: Section-level GridStack positioning
- 📝 **NEW**: Nested GridStack instances for widgets
- 📝 **NEW**: Section template integration with GridStack configs
- 📝 **NEW**: Section constraints and widget type filtering
- ⚠️ Section templates modal (needs backend integration)
- ⚠️ Section add zones between existing sections  
- ⚠️ Section properties panel (background, spacing, CSS, GridStack settings)
- ⚠️ Section deletion with confirmation

**GridStack Implementation Strategy:**
```javascript
// Main page GridStack for sections
const pageGridStack = GridStack.init('#page-sections-container', {
    column: 1,        // Single column for sections
    cellHeight: 'auto', // Auto height for sections
    verticalMargin: 20,
    acceptWidgets: false, // Sections only, not widgets
    resizable: false,     // Sections resize via content
    float: false         // Maintain section order
});

// Individual section GridStacks for widgets
sections.forEach(section => {
    const sectionGrid = GridStack.init(`#section-${section.id}-widgets`, {
        ...section.grid_config, // Use template configuration
        acceptWidgets: section.allows_widgets
    });
});
```

**🧪 Test Step 2.3:**
- ✅ Page-level GridStack manages sections
- ✅ Each section has its own widget GridStack
- ✅ Section templates create proper GridStack configs
- ✅ Widget type constraints enforced
- ✅ Section reordering updates grid positions
- ✅ Nested GridStack instances work independently

### Step 2.4: Create Section API Endpoints ⚠️ NEEDS IMPLEMENTATION
**Goal**: Backend APIs for section management with GridStack positioning

**Files to Create/Modify:**
- 📝 `app/Http/Controllers/Api/PageSectionController.php` (enhance existing)
- 📝 `app/Http/Controllers/Api/PageSectionWidgetController.php` (enhance existing)

**Enhanced API Endpoints:**
```php
// Section Management
GET    /admin/api/pages/{page}/sections           // List sections with grid data
POST   /admin/api/pages/{page}/sections           // Create section with template
PUT    /admin/api/pages/{page}/sections/{section} // Update section
DELETE /admin/api/pages/{page}/sections/{section} // Delete section
PATCH  /admin/api/page-sections/{section}/style   // Update section styles
PATCH  /admin/api/page-sections/{section}/grid    // Update GridStack position
GET    /admin/api/page-sections/{section}/widgets // Get section widgets with grid data

// Widget Management  
PATCH  /admin/api/page-section-widgets/{widget}/grid    // Update widget grid position
GET    /admin/api/page-section-widgets/{widget}         // Get widget with grid data
POST   /admin/api/page-sections/{section}/widgets       // Add widget to section
DELETE /admin/api/page-section-widgets/{widget}         // Remove widget

// Template Management
GET    /admin/api/section-templates                     // List available templates
GET    /admin/api/section-templates/{template}          // Get template configuration
```

**API Response Structure:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Hero Section",
    "grid_position": {
      "x": 0, "y": 0, "w": 12, "h": 4,
      "grid_id": "section-1"
    },
    "grid_config": {
      "column": 12,
      "cellHeight": 80,
      "acceptWidgets": true
    },
    "widgets": [
      {
        "id": 1,
        "widget_id": 2,
        "grid_position": {
          "x": 0, "y": 0, "w": 6, "h": 3,
          "grid_id": "widget-1"
        }
      }
    ]
  }
}
```

**🧪 Test Step 2.4:**
- ✅ All API endpoints return proper JSON responses
- ✅ GridStack position data included in responses
- ✅ Section operations update database correctly
- ✅ Widget positioning within sections works
- ✅ API validation prevents invalid grid positions
- ✅ Template API provides GridStack configurations

---

## Phase 3: Widget Library & Drag-Drop

### Step 3.1: Create Widget Library ✅ PARTIALLY IMPLEMENTED
**Goal**: Display available widgets in categorized sidebar with GridStack integration

**Files Modified:**
- ✅ `public/assets/admin/js/gridstack/widget-library.js` (completed)

**Current Status:**
- ✅ Widget loading with API fallback to mock data
- ✅ Category-based widget organization
- ✅ Drag and drop setup
- ✅ Error handling for API failures

**🧪 Test Step 3.1:**
- ✅ Widget sidebar shows widgets in categories (Content, Media, Layout, Form)
- ✅ Widgets are draggable from sidebar
- ✅ Console shows proper loading messages

### Step 3.2: Implement Widget Drag-Drop to Sections ⚠️ NEEDS ENHANCEMENT
**Goal**: Drag widgets from library into section's nested GridStack areas

**Files Modified:**
- ⚠️ `public/assets/admin/js/gridstack/gridstack-page-builder.js` (needs GridStack integration)

**Enhanced Requirements for Two-Level System:**
- 📝 **Widget Drop Target**: Sections' nested GridStack instances (not main page grid)
- 📝 **Section Detection**: Identify which section widget is dropped into
- 📝 **Grid Position Calculation**: Calculate position within section's coordinate system
- 📝 **Widget Constraints**: Respect section's `widget_types` and `allows_widgets` settings
- 📝 **Database Persistence**: Save widget with proper `page_section_id` and grid position

**GridStack Integration Strategy:**
```javascript
// Enhanced widget drop handling
class WidgetDropHandler {
    constructor(pageBuilder) {
        this.pageBuilder = pageBuilder;
        this.setupSectionDropZones();
    }
    
    setupSectionDropZones() {
        // Each section's GridStack accepts widgets
        this.pageBuilder.sections.forEach(section => {
            const sectionGrid = section.gridStackInstance;
            
            sectionGrid.on('dropped', (event, previousWidget, newWidget) => {
                this.handleWidgetDrop(event, newWidget, section);
            });
        });
    }
    
    async handleWidgetDrop(event, widgetElement, targetSection) {
        const widgetData = this.extractWidgetData(event);
        
        // Check section constraints
        if (!targetSection.canAcceptWidget(widgetData.type)) {
            this.rejectDrop(widgetElement, 'Widget type not allowed in this section');
            return;
        }
        
        // Create database record
        const pageSectionWidget = await this.createPageSectionWidget({
            page_section_id: targetSection.id,
            widget_id: widgetData.id,
            grid_x: widgetElement.gridstackNode.x,
            grid_y: widgetElement.gridstackNode.y,
            grid_w: widgetElement.gridstackNode.w,
            grid_h: widgetElement.gridstackNode.h,
            grid_id: this.generateWidgetGridId()
        });
        
        // Update widget element with database ID
        widgetElement.setAttribute('data-page-section-widget-id', pageSectionWidget.id);
        
        // Load real widget preview
        await this.loadWidgetPreview(widgetElement, pageSectionWidget);
    }
}
```

**🧪 Test Step 3.2:**  
- ✅ Drag widget from sidebar to specific section drop zone
- ✅ Widget appears in correct section's GridStack
- ✅ Widget position calculated relative to section (not page)
- ✅ Widget saves to database with correct `page_section_id`
- ✅ Section constraints prevent invalid widget types
- ✅ Multiple sections can contain widgets independently

### Step 3.3: Enhance Widget GridStack Integration ⚠️ NEEDS IMPLEMENTATION
**Goal**: Full GridStack functionality for widgets within sections

**Files to Enhance:**
- 📝 `public/assets/admin/js/gridstack/gridstack-page-builder.js`
- 📝 `public/assets/admin/js/gridstack/widget-library.js`

**Enhanced Features:**
- 📝 **Widget Resizing**: GridStack resize handles update database
- 📝 **Widget Repositioning**: Drag within section updates grid position
- 📝 **Cross-Section Movement**: Drag widget between sections
- 📝 **Widget Constraints**: Respect `min_width`, `max_width`, `locked_position`
- 📝 **Grid Snapping**: Proper grid alignment and collision detection
- 📝 **Visual Feedback**: Section highlights when widget dragged over

**Widget GridStack Event Handling:**
```javascript
// Enhanced widget event handling
setupWidgetGridStackEvents(sectionGridStack, sectionId) {
    // Widget position changed
    sectionGridStack.on('change', async (event, items) => {
        for (const item of items) {
            await this.updateWidgetGridPosition(item.el, {
                grid_x: item.x,
                grid_y: item.y,
                grid_w: item.w,
                grid_h: item.h
            });
        }
    });
    
    // Widget moved between sections
    sectionGridStack.on('dropped', async (event, previousWidget, newWidget) => {
        if (previousWidget && previousWidget.grid !== newWidget.grid) {
            await this.moveWidgetBetweenSections(
                newWidget.el, 
                previousWidget.grid.sectionId,
                newWidget.grid.sectionId
            );
        }
    });
    
    // Widget resize constraints
    sectionGridStack.on('resizestart', (event, el) => {
        const widget = this.getWidgetData(el);
        const constraints = {
            minW: widget.min_width || 1,
            maxW: widget.max_width || 12,
            minH: 1,
            maxH: 10
        };
        
        sectionGridStack.update(el, constraints);
    });
}
```

**🧪 Test Step 3.3:**
- ✅ Widget resize handles work and update database
- ✅ Widget repositioning within section persists
- ✅ Drag widget between sections transfers ownership
- ✅ Widget constraints prevent invalid sizes
- ✅ Grid snapping provides smooth UX
- ✅ Visual feedback shows valid drop zones

### Step 3.4: Implement Widget Database Persistence ⚠️ NEEDS IMPLEMENTATION
**Goal**: Real-time persistence of widget GridStack positions

**Files to Create/Modify:**
- 📝 `app/Http/Controllers/Api/PageSectionWidgetController.php` (enhance)
- 📝 Widget position update API endpoints

**Database Operations:**
```php
// Widget grid position updates
class PageSectionWidgetController extends Controller 
{
    public function updateGridPosition(Request $request, PageSectionWidget $widget)
    {
        $request->validate([
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0', 
            'grid_w' => 'required|integer|min:1|max:12',
            'grid_h' => 'required|integer|min:1|max:10',
        ]);
        
        $widget->update([
            'grid_x' => $request->grid_x,
            'grid_y' => $request->grid_y,
            'grid_w' => $request->grid_w,
            'grid_h' => $request->grid_h,
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $widget->fresh()
        ]);
    }
    
    public function moveToSection(Request $request, PageSectionWidget $widget)
    {
        $request->validate([
            'new_section_id' => 'required|exists:page_sections,id',
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0',
        ]);
        
        // Check if new section allows this widget type
        $newSection = PageSection::find($request->new_section_id);
        if (!$newSection->canAcceptWidget($widget->widget->slug)) {
            return response()->json([
                'success' => false,
                'message' => 'Widget type not allowed in target section'
            ], 422);
        }
        
        $widget->update([
            'page_section_id' => $request->new_section_id,
            'grid_x' => $request->grid_x,
            'grid_y' => $request->grid_y,
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $widget->fresh()
        ]);
    }
}
```

**Required API Endpoints:**
```php
PATCH /admin/api/page-section-widgets/{widget}/grid-position  // Update position
PATCH /admin/api/page-section-widgets/{widget}/move-section   // Move between sections
GET   /admin/api/page-section-widgets/{widget}/constraints    // Get widget constraints
```

**🧪 Test Step 3.4:**
- ✅ Widget position changes save to database immediately
- ✅ Widget movement between sections updates `page_section_id`
- ✅ API validates grid positions and constraints
- ✅ Optimistic UI updates with proper error handling
- ✅ Widget constraints API provides frontend validation data

### Step 3.5: Add Widget Visual Enhancements ⚠️ NEEDS IMPLEMENTATION
**Goal**: Professional widget appearance and user feedback

**Files to Enhance:**
- 📝 `public/assets/admin/css/gridstack-designer.css`
- 📝 Widget placeholder templates

**Enhanced Visual Features:**
- 📝 **Widget Type Icons**: Visual indicators for different widget types
- 📝 **Section Boundaries**: Clear visual separation between sections
- 📝 **Drop Zone Indicators**: Highlight valid drop areas
- 📝 **Resize Handles**: Custom styled GridStack resize handles
- 📝 **Widget Status**: Loading, error, and configured states
- 📝 **Grid Guides**: Snap-to-grid visual feedback

**CSS Enhancements:**
```css
/* Widget type-specific styling */
.grid-stack-item[data-widget-type="text"] {
    border-left: 4px solid #007bff;
}

.grid-stack-item[data-widget-type="image"] {
    border-left: 4px solid #28a745;
}

.grid-stack-item[data-widget-type="counter"] {
    border-left: 4px solid #ffc107;
}

/* Section drop zone feedback */
.section-grid-stack.widget-drag-over {
    background: rgba(0, 123, 255, 0.1);
    border: 2px dashed #007bff;
}

.section-grid-stack.widget-drop-invalid {
    background: rgba(220, 53, 69, 0.1);
    border: 2px dashed #dc3545;
}

/* GridStack resize handle customization */
.grid-stack-item > .ui-resizable-handle {
    background: #007bff;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.grid-stack-item:hover > .ui-resizable-handle {
    opacity: 1;
}

/* Widget constraint indicators */
.grid-stack-item.has-constraints::after {
    content: "⚠️";
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ffc107;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
```

**🧪 Test Step 3.5:**
- ✅ Widget types visually distinguishable
- ✅ Section boundaries clearly defined
- ✅ Drop zones provide clear feedback
- ✅ Resize handles are intuitive and responsive
- ✅ Widget states (loading/error/configured) are clear
- ✅ Grid guides help with alignment

---

## Phase 4: Widget Configuration System 🔧 IN PROGRESS

### Step 4.1: Create Widget Manager ✅ PARTIALLY IMPLEMENTED
**Goal**: Widget configuration modal with dynamic forms

**Files Created:**
- ✅ `public/assets/admin/js/gridstack/widget-manager.js` (completed)

**Current Status:**
- ✅ Widget manager initialization
- ✅ Modal event handling
- ✅ Widget schema loading
- ✅ Dynamic form rendering
- ✅ Content type integration
- ✅ Save/load configuration

**🧪 Test Step 4.1:**
- ✅ Click widget edit button opens configuration modal
- ⚠️ Modal loads widget schema dynamically (needs API endpoint)
- ⚠️ Form fields render based on widget type (needs widget schemas)
- ⚠️ Content type selection populates items (needs API endpoints)

### Step 4.2: Create Widget Schema System ⚠️ NEEDS IMPLEMENTATION
**Goal**: Define widget configuration schemas and API endpoints

**Files to Create:**
- 📝 Widget schema API endpoints
- 📝 Widget configuration validation
- 📝 Widget field type definitions

**Required Endpoints:**
```php
GET /admin/api/widgets/{widget}/schema              // Get widget schema
GET /admin/api/widgets/{widget}/content-types       // Get content types for widget
GET /admin/api/page-section-widgets/{id}            // Get widget instance data
PUT /admin/api/page-section-widgets/{id}            // Update widget instance
```

**🧪 Test Step 4.2:**
- Widget schemas load properly from API
- Form fields render correctly for each widget type
- Widget configuration saves and persists

### Step 4.3: Enhanced Widget Content Management System ⚠️ NEEDS IMPLEMENTATION
**Goal**: Professional content selection interface leveraging existing APIs and services

**🎯 Core Feature: Transform basic dropdown content selection into sophisticated content management using existing backend logic**

**✅ Leverage Existing APIs:**
- `GET /admin/api/widgets/{widget}/content-types` - Already available
- `GET /admin/api/content/{type}` - Already available  
- `POST /admin/api/widgets/{widget}/preview` - Already available
- `GET /admin/api/widgets/{widget}/schema` - Already available
- `WidgetService` & `WidgetSchemaService` - Already implemented

**Files to Create/Modify:**
- 📝 `public/assets/admin/js/gridstack/widget-content-manager.js` (NEW - Frontend only)
- 📝 `public/assets/admin/css/widget-content-selection.css` (NEW - Styling only)
- 📝 Enhanced widget-manager.js content selection UI (MODIFY existing)
- 📝 Add missing API endpoints to existing controllers (ENHANCE existing)

**📊 Enhanced Content Query Structure:**
```json
{
  "mode": "dynamic", // "manual", "dynamic", "mixed"
  "content_type_id": 2,
  "query_config": {
    "type": "latest", // "latest", "featured", "category", "custom"
    "limit": 5,
    "order_by": "created_at",
    "order_direction": "desc",
    "filters": {
      "status": "published",
      "category_ids": [1, 3, 5],
      "featured": true,
      "date_range": {
        "from": "2024-01-01",
        "to": "2024-12-31"
      }
    }
  },
  "manual_overrides": {
    "include_ids": [12, 15], // Force include these items
    "exclude_ids": [8, 20],  // Force exclude these items
    "pinned_positions": {
      "1": 15, // Pin item 15 to position 1
      "3": 12  // Pin item 12 to position 3
    }
  },
  "display_options": {
    "show_excerpt": true,
    "excerpt_length": 150,
    "show_featured_image": true,
    "image_size": "medium",
    "show_date": true,
    "show_author": false
  }
}
```

#### Step 4.3a: Create Enhanced Content Selection UI ⚠️ NEEDS IMPLEMENTATION
**Goal**: Replace basic dropdowns with rich, visual content selection interface using existing APIs

**✅ Use Existing API Endpoints:**
- `GET /admin/api/widgets/{widget}/content-types` - For content type selection
- `GET /admin/api/content/{type}` - For content items (enhance with filters)

**Enhanced UI Components:**
- 📝 **Visual Content Type Picker**: Card-based selection with icons and metadata
- 📝 **Multi-Mode Interface**: Radio buttons for Manual/Dynamic/Mixed selection
- 📝 **Rich Content Item Display**: Thumbnails, titles, excerpts, metadata
- 📝 **Advanced Search & Filtering**: Real-time search, category filters, date ranges
- 📝 **Drag-and-Drop Reordering**: Sortable selected items list
- 📝 **Bulk Selection Tools**: Select all, clear all, invert selection

**Frontend Implementation (JavaScript):**
```javascript
// Enhanced Content Selection Manager (leverages existing APIs)
class ContentSelectionManager {
    constructor(widgetId, containerId) {
        this.widgetId = widgetId;
        this.container = document.getElementById(containerId);
        this.currentContentType = null;
        this.selectedItems = [];
        this.contentCache = new Map();
    }
    
    // Use existing API: GET /admin/api/widgets/{widget}/content-types
    async loadContentTypes() {
        const response = await fetch(`/admin/api/widgets/${this.widgetId}/content-types`, {
            headers: {
                'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        this.renderContentTypePicker(data.content_types);
    }
    
    // Use existing API: GET /admin/api/content/{type} (enhance with query params)
    async loadContentItems(contentTypeId, filters = {}) {
        const params = new URLSearchParams({
            limit: filters.limit || 20,
            page: filters.page || 1,
            search: filters.search || '',
            status: filters.status || 'published',
            order_by: filters.order_by || 'created_at',
            order_direction: filters.order_direction || 'desc'
        });
        
        const response = await fetch(`/admin/api/content/${contentTypeId}?${params}`, {
            headers: {
                'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        this.renderContentItems(data.items);
        return data;
    }
    
    // Use existing API: POST /admin/api/widgets/{widget}/preview
    async updatePreview(contentQuery) {
        const response = await fetch(`/admin/api/widgets/${this.widgetId}/preview`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content_query: contentQuery,
                preview_mode: true
            })
        });
        
        const result = await response.json();
        this.updateWidgetPreview(result.html);
        return result;
    }
}
```

**🧪 Test Step 4.3a:**
- ✅ Visual content type picker uses existing `/admin/api/widgets/{widget}/content-types`
- ✅ Content item grid uses existing `/admin/api/content/{type}` with enhanced filters
- ✅ Search functionality filters items in real-time
- ✅ Category and status filters work correctly
- ✅ Pagination handles large content sets efficiently
- ✅ Bulk selection tools work as expected
- ✅ Selected items panel shows current selections

#### Step 4.3b: Implement Dynamic Query Builder ⚠️ NEEDS IMPLEMENTATION
**Goal**: Add intelligent content querying using existing ContentItem model and APIs

**✅ Enhance Existing API Endpoint:**
- Enhance `GET /admin/api/content/{type}` to support advanced filtering
- Add query parameters for dynamic queries (latest, featured, category, custom)

**API Enhancement (Add to existing ContentItemController):**
```php
// Enhance existing App\Http\Controllers\Api\ContentItemController::index()
public function index(Request $request, $type)
{
    $contentType = ContentType::where('id', $type)->orWhere('slug', $type)->firstOrFail();
    
    $query = ContentItem::where('content_type_id', $contentType->id);
    
    // Add dynamic query support
    if ($request->has('query_mode')) {
        switch ($request->query_mode) {
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'featured':
                $query->where('featured', true)->orderBy('featured_at', 'desc');
                break;
            case 'category':
                if ($request->has('category_ids')) {
                    $query->whereIn('category_id', $request->category_ids);
                }
                break;
        }
    }
    
    // Add filtering support
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->has('search')) {
        $query->where(function($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('excerpt', 'like', '%' . $request->search . '%');
        });
    }
    
    if ($request->has('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    
    if ($request->has('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }
    
    // Add ordering support
    $orderBy = $request->get('order_by', 'created_at');
    $orderDirection = $request->get('order_direction', 'desc');
    $query->orderBy($orderBy, $orderDirection);
    
    // Add pagination support
    $limit = min($request->get('limit', 20), 100); // Max 100 items
    $items = $query->limit($limit)->with(['media', 'author'])->get();
    
    return response()->json([
        'items' => $items,
        'query_info' => [
            'total_available' => $query->count(),
            'items_returned' => $items->count(),
            'query_mode' => $request->get('query_mode', 'manual'),
            'applied_filters' => $request->only(['status', 'search', 'category_ids', 'date_from', 'date_to'])
        ]
    ]);
}
```

**🧪 Test Step 4.3b:**
- ✅ Latest items mode uses enhanced API with `query_mode=latest`
- ✅ Featured items mode uses `query_mode=featured`
- ✅ Category filtering works with `category_ids` parameter
- ✅ Custom query builder uses multiple filter parameters
- ✅ Date range queries work with `date_from` and `date_to`
- ✅ Query preview shows accurate item counts and samples
- ✅ Query configuration saves to content_query JSON field

#### Step 4.3c: Add Real-Time Content Preview ⚠️ NEEDS IMPLEMENTATION
**Goal**: Immediate visual feedback using existing widget preview API

**✅ Use Existing API:**
- `POST /admin/api/widgets/{widget}/preview` - Already supports content_query parameter

**Real-Time Preview Integration:**
```javascript
// Real-time preview using existing API
class ContentPreviewManager {
    constructor(widgetElement, widgetId) {
        this.widgetElement = widgetElement;
        this.widgetId = widgetId;
        this.previewDebouncer = this.debounce(this.updatePreview.bind(this), 500);
    }
    
    // Use existing preview API with enhanced content_query
    async updatePreview(contentQuery) {
        try {
            // Use existing POST /admin/api/widgets/{widget}/preview
            const response = await fetch(`/admin/api/widgets/${this.widgetId}/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.GridStackPageBuilder.config.csrfToken
                },
                body: JSON.stringify({
                    content_query: contentQuery,
                    preview_mode: true
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update widget preview immediately
                this.updateWidgetPreview(result.html);
                
                // Update query info display
                this.updateQueryInfo(result.query_info);
            }
        } catch (error) {
            console.error('Preview update failed:', error);
            this.showPreviewError();
        }
    }
}
```

**🧪 Test Step 4.3c:**
- ✅ Widget preview updates immediately using existing preview API
- ✅ Query result preview shows accurate sample content
- ✅ Content selection changes are debounced for smooth performance
- ✅ Preview loading states provide clear user feedback
- ✅ Error states handle API failures gracefully
- ✅ Preview mode toggle works correctly

#### Step 4.3d: Enhance Existing Services for Query Resolution ⚠️ NEEDS IMPLEMENTATION
**Goal**: Enhance existing WidgetService to handle advanced content queries

**✅ Enhance Existing Service:**
- Modify existing `App\Services\WidgetService::getWidgetFieldValues()` method
- Add query resolution logic to existing service structure

**Service Enhancement:**
```php
// Enhance existing App\Services\WidgetService
class WidgetService 
{
    // Enhance existing getWidgetFieldValues method
    public function getWidgetFieldValues(Widget $widget, ?PageSectionWidget $pageSectionWidget = null): array
    {
        $fieldValues = [];
        
        if ($pageSectionWidget && !empty($pageSectionWidget->content_query)) {
            // Use enhanced content query resolution
            $fieldValues = $this->resolveContentQuery($pageSectionWidget->content_query);
        } else {
            // Fallback to existing logic
            $fieldValues = $this->getDefaultFieldValues($widget);
        }
        
        return $fieldValues;
    }
    
    // Add new method to existing service
    protected function resolveContentQuery(array $contentQuery): array
    {
        $contentType = ContentType::find($contentQuery['content_type_id']);
        if (!$contentType) {
            return [];
        }
        
        $query = ContentItem::where('content_type_id', $contentType->id)
                           ->where('status', 'published');
        
        // Handle different query modes
        switch ($contentQuery['mode']) {
            case 'manual':
                if (!empty($contentQuery['manual_selection']['content_item_ids'])) {
                    $query->whereIn('id', $contentQuery['manual_selection']['content_item_ids']);
                }
                break;
                
            case 'dynamic':
                $query = $this->applyDynamicQueryFilters($query, $contentQuery['query_config']);
                break;
                
            case 'mixed':
                $query = $this->applyDynamicQueryFilters($query, $contentQuery['query_config']);
                
                // Apply manual overrides
                if (!empty($contentQuery['manual_overrides']['exclude_ids'])) {
                    $query->whereNotIn('id', $contentQuery['manual_overrides']['exclude_ids']);
                }
                break;
        }
        
        $items = $query->with(['media', 'fieldValues.field'])->get();
        
        return $this->formatContentItemsForWidget($items);
    }
    
    // Add helper method to existing service
    protected function applyDynamicQueryFilters($query, array $config)
    {
        switch ($config['type']) {
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'featured':
                $query->where('featured', true)->orderBy('featured_at', 'desc');
                break;
            case 'category':
                if (!empty($config['filters']['category_ids'])) {
                    $query->whereIn('category_id', $config['filters']['category_ids']);
                }
                break;
        }
        
        // Apply limit
        if (isset($config['limit'])) {
            $query->limit($config['limit']);
        }
        
        return $query;
    }
}
```

**Required API Endpoints (Add to existing routes):**
```php
// Add to existing routes/admin.php API section
Route::get('/content-types', [App\Http\Controllers\Api\ContentTypeController::class, 'index']); // List all content types
Route::get('/content-types/{id}/stats', [App\Http\Controllers\Api\ContentItemController::class, 'getStats']); // Get content type stats
```

**🧪 Test Step 4.3d:**
- ✅ Enhanced WidgetService resolves all query modes correctly
- ✅ Dynamic queries return properly filtered and sorted results
- ✅ Manual selection preserves item order
- ✅ Mixed queries apply overrides correctly
- ✅ Existing widget rendering continues to work
- ✅ API endpoints return consistent response formats

**🎯 Overall Test Step 4.3:**
- ✅ **Leverage Existing APIs**: All functionality uses existing backend infrastructure
- ✅ **Enhanced Frontend UI**: Professional interface replaces basic dropdowns
- ✅ **Multiple Query Modes**: Manual, dynamic, and mixed modes work correctly
- ✅ **Real-Time Preview**: Widget updates immediately using existing preview API
- ✅ **Advanced Filtering**: Search, categories, date ranges function properly
- ✅ **Performance**: Debounced updates provide smooth UX
- ✅ **Data Persistence**: Enhanced content_query structure saves correctly
- ✅ **Minimal Backend Changes**: Only enhance existing services, don't create new ones