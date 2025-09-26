# GridStack.js Integration Plan for Page Builder

## **Current Structure Analysis**

### **Current HTML Hierarchy:**
```html
<div class="pagebuilder-preview-container" data-pagebuilder-page="1">
  <!-- Page Toolbar -->
  <div class="pagebuilder-page-toolbar">...</div>

  <!-- Sections (from $pageContent) -->
  <section data-pagebuilder-section="1" data-section-id="1">
    <!-- Section Toolbar -->
    <div class="pagebuilder-section-toolbar">...</div>

    <!-- Widgets -->
    <div data-pagebuilder-widget="1" data-page-section-widget-id="1">
      <!-- Widget Toolbar -->
      <div class="pagebuilder-widget-toolbar">...</div>
      <!-- Widget content -->
    </div>
  </section>
</div>
```

### **Existing GridStack Data Attributes (from actual section HTML):**
Based on `externalfiles\codepreview.txt`, sections and widgets already have GridStack data attributes:

**Section Level:**
```html
<section class="cms-section section-full-width container-fluid"
         data-gs-x="0" data-gs-y="0" data-gs-w="12" data-gs-h="3"
         data-gs-id="section_1753641046_688670566ea19_1">
```

**Widget Level:**
```html
<div class="cms-widget widget-slider"
     data-gs-x="0" data-gs-y="0" data-gs-w="6" data-gs-h="3"
     data-gs-id="widget_1754415380703_byodgr4eo">
```

## **GridStack.js Integration Strategy**

### **Phase 1: Page-Level Grid (Sections as Grid Items)**

**Goal**: Make sections sortable within the page container

#### **1.1 HTML Structure Changes:**
- **Page Container**: Add `grid-stack` class to `.pagebuilder-preview-container`
- **Section Wrapper**: Sections already have GridStack data attributes - need to wrap in `grid-stack-item`
- **Content Wrapper**: Wrap section content in `grid-stack-item-content`

**Target Structure:**
```html
<div class="pagebuilder-preview-container grid-stack" id="pageGrid" data-pagebuilder-page="1">
  <!-- Page Toolbar (outside grid) -->
  <div class="pagebuilder-page-toolbar">...</div>

  <!-- Section as Grid Item (utilizing existing data-gs-* attributes) -->
  <div class="grid-stack-item"
       data-gs-x="0" data-gs-y="0" data-gs-w="12" data-gs-h="3"
       data-gs-id="section_1753641046_688670566ea19_1">
    <div class="grid-stack-item-content">
      <section class="cms-section section-full-width container-fluid"
               data-pagebuilder-section="1" data-section-id="1">
        <!-- Section Toolbar -->
        <div class="pagebuilder-section-toolbar">...</div>
        <!-- Section content with widgets -->
      </section>
    </div>
  </div>
</div>
```

#### **1.2 Implementation Steps:**
1. **Add GridStack Assets**: Include GridStack CSS/JS in `getPageBuilderPreviewAssets()`
2. **Modify Section Injection**: Update `injectPageBuilderPreviewData()` to:
   - Extract existing `data-gs-*` attributes from sections
   - Wrap sections in `grid-stack-item` with those attributes
   - Add `grid-stack` class to page container
3. **Initialize Grid**: Create JavaScript to initialize page-level GridStack
4. **Test Section Sorting**: Verify sections can be dragged and reordered

### **Phase 2: Section-Level Grids (Widgets as Grid Items)**

**Goal**: Make widgets sortable within each section

#### **2.1 HTML Structure Changes:**
- **Section Content**: Add `grid-stack section-grid` class to widget container areas
- **Widget Wrapper**: Widgets already have GridStack data attributes - need to wrap in `grid-stack-item`
- **Content Wrapper**: Wrap widget content in `grid-stack-item-content`

**Target Structure:**
```html
<div class="grid-stack-item-content">
  <section class="cms-section" data-pagebuilder-section="1">
    <!-- Section Toolbar -->
    <div class="pagebuilder-section-toolbar">...</div>

    <!-- Widget Grid Container -->
    <div class="section-content">
      <div class="row">
        <div class="col-12">
          <!-- Widget Grid Container -->
          <div class="grid-stack section-grid" id="sectionGrid-1">
            <!-- Widget as Grid Item (utilizing existing data-gs-* attributes) -->
            <div class="grid-stack-item"
                 data-gs-x="0" data-gs-y="0" data-gs-w="6" data-gs-h="3"
                 data-gs-id="widget_1754415380703_byodgr4eo">
              <div class="grid-stack-item-content">
                <div class="cms-widget widget-slider"
                     data-pagebuilder-widget="1"
                     data-page-section-widget-id="1">
                  <!-- Widget Toolbar -->
                  <div class="pagebuilder-widget-toolbar">...</div>
                  <!-- Widget content -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
```

#### **2.2 Implementation Steps:**
1. **Modify Widget Injection**: Update `injectPageBuilderPreviewData()` to:
   - Extract existing `data-gs-*` attributes from widgets
   - Wrap widgets in `grid-stack-item` with those attributes
   - Add `grid-stack section-grid` class to widget container areas
2. **Initialize Nested Grids**: Create JavaScript to initialize section-level GridStacks
3. **Test Widget Sorting**: Verify widgets can be dragged within sections

## **Technical Implementation Details**

### **1. Asset Integration**
**In `getPageBuilderPreviewAssets()` method:**
```html
<!-- GridStack CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@latest/dist/gridstack.min.css">

<!-- GridStack JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/gridstack@latest/dist/gridstack-all.min.js"></script>
```

### **2. PHP Pattern Updates**
**Modify `injectPageBuilderPreviewData()` patterns:**

**Phase 1 - Section Wrapping:**
```php
'/<section([^>]*data-section-id="([^"]*)"[^>]*data-gs-x="([^"]*)"[^>]*data-gs-y="([^"]*)"[^>]*data-gs-w="([^"]*)"[^>]*data-gs-h="([^"]*)"[^>]*data-gs-id="([^"]*)"[^>]*)>/i' => function($matches) {
    $sectionId = $matches[2];
    $gsX = $matches[3];
    $gsY = $matches[4];
    $gsW = $matches[5];
    $gsH = $matches[6];
    $gsId = $matches[7];

    return '<div class="grid-stack-item"
                 data-gs-x="' . $gsX . '"
                 data-gs-y="' . $gsY . '"
                 data-gs-w="' . $gsW . '"
                 data-gs-h="' . $gsH . '"
                 data-gs-id="' . $gsId . '">
        <div class="grid-stack-item-content">
            <section' . $matches[1] . ' data-pagebuilder-section="' . $sectionId . '">'
            . $this->generateSectionToolbar($sectionId);
},
```

**Phase 2 - Widget Wrapping:**
```php
'/<div([^>]*data-page-section-widget-id="([^"]*)"[^>]*data-gs-x="([^"]*)"[^>]*data-gs-y="([^"]*)"[^>]*data-gs-w="([^"]*)"[^>]*data-gs-h="([^"]*)"[^>]*data-gs-id="([^"]*)"[^>]*)>/i' => function($matches) {
    $widgetId = $matches[2];
    $gsX = $matches[3];
    $gsY = $matches[4];
    $gsW = $matches[5];
    $gsH = $matches[6];
    $gsId = $matches[7];

    return '<div class="grid-stack-item"
                 data-gs-x="' . $gsX . '"
                 data-gs-y="' . $gsY . '"
                 data-gs-w="' . $gsW . '"
                 data-gs-h="' . $gsH . '"
                 data-gs-id="' . $gsId . '">
        <div class="grid-stack-item-content">
            <div' . $matches[1] . ' data-pagebuilder-widget="' . $widgetId . '">'
            . $this->generateWidgetToolbar($widgetId);
},
```

### **3. Page Container Grid Integration**
**Modify `generatePageBuilderPageHtml()` method:**
```php
<!-- Page Container with Page Builder data attributes and GridStack -->
<div data-pagebuilder-page="' . $page->id . '"
     data-page-title="' . e($page->title) . '"
     data-page-template="' . e($page->template->name ?? 'Unknown Template') . '"
     data-preview-type="page"
     class="pagebuilder-preview-container grid-stack" id="pageGrid">
    ' . $this->generatePageToolbar($page) . '
    ' . $pageContent . '
</div>
```

### **4. JavaScript Initialization**
**New file: `pagebuilder-gridstack.js`**

```javascript
// Phase 1: Initialize page-level grid
function initPageGrid() {
    const pageGrid = GridStack.init({
        cellHeight: 'auto',
        acceptWidgets: false,
        removable: false,
        handle: '.pagebuilder-section-toolbar',
        column: 12,
        margin: 10
    }, '#pageGrid');

    pageGrid.on('change', (event, items) => {
        // Handle section reordering
        updateSectionOrder(items);
        console.log('Sections reordered:', items);
    });

    return pageGrid;
}

// Phase 2: Initialize section-level grids
function initSectionGrids() {
    const sectionGrids = [];

    document.querySelectorAll('.section-grid').forEach(grid => {
        const sectionGrid = GridStack.init({
            cellHeight: 'auto',
            acceptWidgets: true,
            handle: '.pagebuilder-widget-toolbar',
            column: 12,
            margin: 5
        }, grid);

        sectionGrid.on('change', (event, items) => {
            // Handle widget reordering
            updateWidgetOrder(items);
            console.log('Widgets reordered:', items);
        });

        sectionGrids.push(sectionGrid);
    });

    return sectionGrids;
}

// Update section order via API
function updateSectionOrder(items) {
    const orderData = items.map(item => ({
        id: item.id,
        x: item.x,
        y: item.y,
        w: item.w,
        h: item.h
    }));

    // Send to backend API
    fetch('/admin/api/page-builder/sections/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({ sections: orderData })
    }).catch(console.error);
}

// Update widget order via API
function updateWidgetOrder(items) {
    const orderData = items.map(item => ({
        id: item.id,
        x: item.x,
        y: item.y,
        w: item.w,
        h: item.h
    }));

    // Send to backend API
    fetch('/admin/api/page-builder/widgets/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({ widgets: orderData })
    }).catch(console.error);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Phase 1: Initialize page-level grid for sections
    const pageGrid = initPageGrid();

    // Phase 2: Initialize section-level grids for widgets
    const sectionGrids = initSectionGrids();

    console.log('<¯ GridStack initialized:', {
        pageGrid,
        sectionGrids: sectionGrids.length
    });
});
```

### **5. CSS Adjustments**
**Update toolbar positioning for drag handles:**
```css
/* GridStack overrides for Page Builder */
.pagebuilder-preview-container.grid-stack {
    background: #f8f9fa;
    padding: 20px;
}

/* Section grid styling */
.section-grid {
    background: transparent;
    min-height: 100px;
}

/* Toolbar as drag handle styling */
.pagebuilder-section-toolbar:hover,
.pagebuilder-widget-toolbar:hover {
    cursor: grab;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.pagebuilder-section-toolbar:active,
.pagebuilder-widget-toolbar:active {
    cursor: grabbing;
}

/* Grid item styling */
.grid-stack-item-content {
    background: white;
    border-radius: 8px;
    overflow: visible; /* Allow toolbars to show outside */
}

/* Ensure proper z-index layering */
.pagebuilder-toolbar {
    z-index: 1001; /* Above GridStack elements */
}
```

## **Key Considerations**

### **1. Existing GridStack Data Preservation**
- Sections already have `data-gs-*` attributes that define their grid position
- Widgets already have `data-gs-*` attributes that define their grid position
- Implementation must preserve and utilize these existing attributes
- No need to generate new GridStack positioning data

### **2. Nested Grid Structure**
- Page container becomes the main GridStack container
- Each section contains its own GridStack container for widgets
- Two-level hierarchy: Page ’ Sections ’ Widgets

### **3. Drag Handle Integration**
- Always-visible toolbars serve as drag handles
- Section toolbar = drag handle for section movement
- Widget toolbar = drag handle for widget movement
- Toolbars must be positioned to not interfere with GridStack functionality

## **Benefits of This Approach**

1. **Leverages Existing Data**: Uses existing GridStack attributes from sections/widgets
2. **Incremental Implementation**: Test each level independently
3. **Visual Feedback**: Always-visible toolbars show drag targets clearly
4. **Nested Grid Support**: Full hierarchy of page ’ sections ’ widgets
5. **Existing Infrastructure**: Leverages current toolbar and data attribute system
6. **Easy Debugging**: Clear visual boundaries and data attributes
7. **API Integration**: Prepared for backend persistence of grid changes

## **Testing Strategy**

### **Phase 1 Testing:**
- Verify GridStack CSS/JS loads correctly in iframe
- Test page container becomes GridStack container
- Drag sections to reorder within page
- Verify section GridStack data attributes are preserved
- Ensure section toolbars remain functional as drag handles

### **Phase 2 Testing:**
- Test section containers become GridStack containers
- Drag widgets within sections
- Drag widgets between sections (if supported)
- Verify widget GridStack data attributes are preserved
- Ensure nested grid interactions don't conflict
- Test API calls for persisting grid changes

## **Implementation Files to Modify**

1. **PageBuilderController.php**:
   - `getPageBuilderPreviewAssets()` - Add GridStack assets
   - `generatePageBuilderPageHtml()` - Add grid-stack class to page container
   - `injectPageBuilderPreviewData()` - Wrap sections/widgets in grid-stack-item

2. **pagebuilder-preview-helper.css**:
   - Add GridStack integration styles
   - Update toolbar positioning for drag handles

3. **New file: pagebuilder-gridstack.js**:
   - GridStack initialization
   - Event handlers for drag/drop
   - API communication for persistence

This approach provides a solid foundation for drag-and-drop functionality while maintaining the existing always-visible toolbar system and leveraging the existing GridStack data structure already present in the CMS.