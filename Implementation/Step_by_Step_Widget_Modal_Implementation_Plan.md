# Step-by-Step Widget Modal Implementation Plan (Option A)

## Overview
Create a multi-step modal flow where users progressively configure their widget selection, making it intuitive and comprehensive. This document outlines the complete implementation plan for the 5-step widget addition modal.

## ✅ COMPLETED: SQL Error Fix
- **Fixed:** `getAvailableWidgets()` method in PageBuilderController
- **Removed:** Non-existent `category` and `status` columns
- **Added:** Active theme filtering and intelligent widget grouping by functionality
- **Result:** API now returns widgets grouped by: content, layout, media, utility

---

## Implementation Phases

### **Phase 1: Multi-Step Modal Structure (Week 1)**

#### **Step 1.1: Modal Container Update**
Convert existing single-step modal to multi-step wizard:

**Files to Modify:**
- `C:\wamp64\www\RealsysCMS\resources\views\admin\pages\page-builder\modals\widget-content.blade.php`

**Tasks:**
- Add step indicators (1/5, 2/5, etc.)
- Add navigation buttons (Previous/Next/Cancel/Finish) 
- Create container for each step with show/hide logic
- Add progress bar visual indicator

**Modal Steps Structure:**
1. **Step 1: Widget Selection** - Choose widget from library
2. **Step 2: Content Type Selection** - Choose content type (if widget supports content)
3. **Step 3: Content Item Selection** - Choose specific items or query settings
4. **Step 4: Widget Configuration** - Configure widget-specific settings
5. **Step 5: Final Review** - Preview and confirm before adding

#### **Step 1.2: Step 1 - Widget Library Display**

**API Response Structure (Already Implemented):**
```json
{
  "success": true,
  "data": {
    "theme": {
      "name": "Default Theme",
      "slug": "default"
    },
    "widgets": {
      "content": [
        {
          "id": 1,
          "name": "Blog Posts",
          "description": "Display blog posts in various layouts",
          "icon": "ri-article-line",
          "preview_image": "/themes/default/widgets/blog-posts/preview.png",
          "supports_content": true,
          "content_types": ["Post", "Article"],
          "field_count": 5,
          "slug": "blog-posts"
        }
      ],
      "layout": [...],
      "media": [...],
      "utility": [...]
    }
  }
}
```

**Tasks:**
- Create widget library grid layout with preview images
- Implement widget search and filtering
- Show widget information cards with:
  - Preview image
  - Widget name and description
  - Content type badges (if applicable)
  - Field count indicator
- Add selection state management

**Widget Library Design:**
```html
<div class="widget-library-grid">
  <div class="widget-category">
    <h6>Content Widgets</h6>
    <div class="widget-grid">
      <div class="widget-card" data-widget-id="1">
        <img src="preview.png" class="widget-preview">
        <h6>Blog Posts</h6>
        <p>Display blog posts</p>
        <div class="widget-badges">
          <span class="badge">Post</span>
          <span class="badge">5 fields</span>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

### **Phase 2: Content Type Integration (Week 2)**

#### **Step 2.1: New API Endpoints**

**Endpoint 1:** `GET /admin/api/page-builder/widgets/{widget}/content-types`
```php
public function getWidgetContentTypes(Widget $widget): JsonResponse
{
    $contentTypes = $widget->contentTypes()
        ->with(['fields'])
        ->get(['id', 'name', 'description', 'icon']);

    return response()->json([
        'success' => true,
        'data' => [
            'widget' => [
                'id' => $widget->id,
                'name' => $widget->name,
                'supports_content' => $contentTypes->count() > 0
            ],
            'content_types' => $contentTypes->map(function($ct) {
                return [
                    'id' => $ct->id,
                    'name' => $ct->name,
                    'description' => $ct->description,
                    'icon' => $ct->icon ?? 'ri-file-list-line',
                    'field_count' => $ct->fields->count(),
                    'items_count' => $ct->contentItems()->count()
                ];
            })
        ]
    ]);
}
```

#### **Step 2.2: Step 2 - Content Type Selection**

**Tasks:**
- When widget selected, check if it supports content types
- If yes, show available content types for that widget
- Allow single or multiple content type selection
- Skip this step for non-content widgets

**Content Type Display:**
- Radio buttons for single selection
- Checkboxes for multiple selection  
- Show content type description and item count
- Preview how widget will display this content type

---

### **Phase 3: Content Item Management (Week 3)**

#### **Step 3.1: Content Items API**

**Endpoint 2:** `GET /admin/api/page-builder/content-types/{type}/items`
```php
public function getContentTypeItems(ContentType $contentType, Request $request): JsonResponse
{
    $query = $contentType->contentItems()
        ->with(['fieldValues'])
        ->orderBy('created_at', 'desc');

    // Apply search filter
    if ($request->search) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    // Apply status filter
    if ($request->status) {
        $query->where('status', $request->status);
    }

    $items = $query->paginate(20);

    return response()->json([
        'success' => true,
        'data' => [
            'content_type' => [
                'id' => $contentType->id,
                'name' => $contentType->name
            ],
            'items' => $items->items()->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'excerpt' => Str::limit($item->content ?? '', 100),
                    'status' => $item->status,
                    'created_at' => $item->created_at->format('M j, Y'),
                    'thumbnail' => $this->getItemThumbnail($item)
                ];
            }),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'total_pages' => $items->lastPage(),
                'total_items' => $items->total()
            ]
        ]
    ]);
}
```

#### **Step 3.2: Step 3 - Content Item Selection**

**Two Selection Modes:**

**Mode 1: Manual Selection**
- Paginated list of content items
- Search and filter capabilities
- Checkbox selection with preview
- Show item title, date, status, thumbnail

**Mode 2: Query Builder**
- Content filters (category, tags, status, date range)
- Sorting options (newest, popular, alphabetical)
- Limit settings (show X items)
- Preview query results

**Query Builder API:** `POST /admin/api/page-builder/content-types/{type}/items/query`
```php
public function queryContentItems(ContentType $contentType, Request $request): JsonResponse
{
    $queryBuilder = new ContentQueryBuilder($contentType);
    
    $query = $queryBuilder
        ->filters($request->filters ?? [])
        ->sorting($request->sort_by ?? 'created_at', $request->sort_direction ?? 'desc')
        ->limit($request->limit ?? 10)
        ->build();

    $items = $query->get();
    
    return response()->json([
        'success' => true,
        'data' => [
            'query_preview' => $items->take(5), // Preview first 5 items
            'total_matches' => $items->count(),
            'query_settings' => $request->only(['filters', 'sort_by', 'sort_direction', 'limit'])
        ]
    ]);
}
```

---

### **Phase 4: Widget Configuration (Week 4)**

#### **Step 4.1: Field Definitions API**

**Endpoint 3:** `GET /admin/api/page-builder/widgets/{widget}/field-definitions`
```php
public function getWidgetFieldDefinitions(Widget $widget): JsonResponse
{
    $fieldDefinitions = $widget->fieldDefinitions()
        ->orderBy('position')
        ->get(['id', 'name', 'slug', 'field_type', 'validation_rules', 'settings', 'is_required', 'description']);

    return response()->json([
        'success' => true,
        'data' => [
            'widget' => [
                'id' => $widget->id,
                'name' => $widget->name
            ],
            'field_definitions' => $fieldDefinitions->map(function($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'slug' => $field->slug,
                    'type' => $field->field_type,
                    'required' => $field->is_required,
                    'description' => $field->description,
                    'settings' => $field->settings,
                    'validation_rules' => $field->validation_rules
                ];
            })
        ]
    ]);
}
```

#### **Step 4.2: Step 4 - Widget Configuration**

**Configuration Sections:**

**Widget Fields:** Dynamic fields based on WidgetFieldDefinitions
- Text inputs, textareas, selects, checkboxes
- File uploads, color pickers, date pickers
- Repeater fields, relationship fields

**Layout Settings:**
- Width (1-12 grid columns)
- Height (auto or fixed)
- Alignment (left, center, right)

**Styling Options:**
- Padding, margin settings
- Background color/image
- Border radius, borders
- Custom CSS classes

**Advanced Settings:**
- Animation effects
- Responsive visibility
- Custom attributes

---

### **Phase 5: Preview & Finalization (Week 5)**

#### **Step 5.1: Widget Preview API**

**Endpoint 4:** `POST /admin/api/page-builder/widgets/{widget}/preview`
```php
public function previewWidget(Widget $widget, Request $request): JsonResponse
{
    try {
        // Create temporary widget instance with provided settings
        $tempSettings = $request->settings ?? [];
        $tempContentQuery = $request->content_query ?? [];
        
        // Generate preview HTML using widget's view
        $previewHtml = $this->generateWidgetPreview($widget, $tempSettings, $tempContentQuery);
        
        return response()->json([
            'success' => true,
            'data' => [
                'preview_html' => $previewHtml,
                'widget_summary' => [
                    'name' => $widget->name,
                    'content_type' => $request->content_type_name ?? 'None',
                    'items_count' => $request->selected_items_count ?? 0,
                    'settings_count' => count($tempSettings)
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to generate preview: ' . $e->getMessage()
        ], 500);
    }
}
```

#### **Step 5.2: Step 5 - Final Review**

**Review Display:**
- Selected widget name and preview image
- Content type and selection summary
- Configuration summary (key settings)
- Live preview of final widget
- "Add to Section" final button

**Summary Format:**
```
Widget: Blog Posts Widget
Content: Latest 5 Posts from "News" category  
Layout: Grid (3 columns)
Styling: Card style with shadows
```

---

### **Phase 6: Frontend JavaScript Architecture**

#### **Step 6.1: Widget Modal Manager**

**File:** `C:\wamp64\www\RealsysCMS\public\assets\admin\js\page-builder\widget-modal-manager.js`

```javascript
class WidgetModalManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.currentStep = 1;
        this.totalSteps = 5;
        this.modalData = {
            selectedWidget: null,
            selectedContentType: null,
            selectedItems: [],
            contentQuery: null,
            widgetConfig: {},
            sectionId: null,
            sectionName: null
        };
        
        this.modal = null;
        this.init();
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('widgetContentModal'));
        this.setupEventListeners();
    }

    // Step navigation
    nextStep() {
        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.renderCurrentStep();
            this.updateProgressBar();
        }
    }

    previousStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.renderCurrentStep();
            this.updateProgressBar();
        }
    }

    goToStep(step) {
        if (step >= 1 && step <= this.totalSteps) {
            this.currentStep = step;
            this.renderCurrentStep();
            this.updateProgressBar();
        }
    }
    
    // Step handlers
    async handleWidgetSelection(widgetId) {
        try {
            const widget = await this.loadWidgetDetails(widgetId);
            this.modalData.selectedWidget = widget;
            
            if (widget.supports_content) {
                this.nextStep(); // Go to content type selection
            } else {
                this.goToStep(4); // Skip content steps, go to configuration
            }
        } catch (error) {
            this.showError('Failed to load widget details');
        }
    }

    async handleContentTypeSelection(contentTypeId) {
        try {
            const contentType = await this.loadContentTypeDetails(contentTypeId);
            this.modalData.selectedContentType = contentType;
            this.nextStep(); // Go to content item selection
        } catch (error) {
            this.showError('Failed to load content type');
        }
    }

    handleContentItemSelection(items) {
        this.modalData.selectedItems = items;
        this.nextStep(); // Go to widget configuration
    }

    handleContentQueryBuilder(query) {
        this.modalData.contentQuery = query;
        this.nextStep(); // Go to widget configuration
    }

    async handleWidgetConfiguration(config) {
        this.modalData.widgetConfig = config;
        await this.generatePreview();
        this.nextStep(); // Go to final review
    }

    async handleFinalSubmission() {
        try {
            const result = await this.submitWidgetToSection();
            if (result.success) {
                this.modal.hide();
                this.showSuccess('Widget added successfully!');
                // Refresh page preview
                this.refreshPagePreview();
            }
        } catch (error) {
            this.showError('Failed to add widget to section');
        }
    }
}
```

#### **Step 6.2: Integration with Existing System**

**Update:** `C:\wamp64\www\RealsysCMS\resources\views\admin\pages\page-builder\show.blade.php`

```javascript
// Initialize Widget Modal Manager
window.widgetModalManager = new WidgetModalManager(
    '/admin/api/page-builder',
    window.csrfToken
);

// Update existing openWidgetModalForSection function
function openWidgetModalForSection(sectionId, sectionName) {
    window.widgetModalManager.openForSection(sectionId, sectionName);
}
```

---

## Data Flow

```
User clicks "Add Widget"
  ↓
Load widget library for active theme
  ↓
User selects widget → Store widget info → Check content support
  ↓
[If supports content]
Load content types → User selects content type → Store content type
  ↓
Load content items/show query builder → User configures content
  ↓
Load widget field definitions → User configures widget
  ↓
Generate preview → User reviews and confirms
  ↓
Submit complete widget configuration to backend
  ↓
Add widget to section and refresh preview
```

---

## Success Metrics

- ✅ User can complete widget addition in < 2 minutes
- ✅ Clear visual feedback at each step
- ✅ Ability to go back and modify previous steps
- ✅ Live preview shows accurate final result
- ✅ Proper error handling and validation
- ✅ Mobile-responsive design
- ✅ Accessibility compliance (WCAG 2.1)

---

## File Structure

```
C:\wamp64\www\RealsysCMS\
├── app\Http\Controllers\Api\PageBuilderController.php (✅ Updated)
├── resources\views\admin\pages\page-builder\modals\
│   ├── widget-content.blade.php (🔄 To Update)
│   └── widget-steps\ (📁 New Folder)
│       ├── step-1-widget-selection.blade.php
│       ├── step-2-content-type.blade.php  
│       ├── step-3-content-items.blade.php
│       ├── step-4-configuration.blade.php
│       └── step-5-review.blade.php
├── public\assets\admin\js\page-builder\
│   ├── widget-modal-manager.js (📄 New File)
│   └── steps\ (📁 New Folder)
│       ├── widget-selection-step.js
│       ├── content-type-step.js
│       ├── content-items-step.js
│       ├── configuration-step.js
│       └── review-step.js
└── public\assets\admin\css\page-builder\
    └── widget-modal.css (📄 New File)
```

---

## Next Steps

1. **✅ COMPLETED:** Fix SQL error in `getAvailableWidgets()` method
2. **🎯 CURRENT:** Create multi-step modal structure (Phase 1)
3. **📋 PLANNED:** Implement content type integration (Phase 2)
4. **📋 PLANNED:** Build content item management (Phase 3)
5. **📋 PLANNED:** Add widget configuration (Phase 4)
6. **📋 PLANNED:** Complete preview & finalization (Phase 5)

---

## Notes

- **Theme Integration:** Only widgets from active theme are shown
- **Security:** All API endpoints include CSRF protection and admin authentication
- **Performance:** Widget preview generation is cached for better UX
- **Extensibility:** System designed to easily add new widget field types
- **Accessibility:** All modals and forms include proper ARIA labels and keyboard navigation

---

*Last Updated: January 2025*
*Status: SQL Error Fixed ✅ | Ready for Phase 1 Implementation 🚀*