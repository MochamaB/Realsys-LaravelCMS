# Widget Editing Implementation Plan

## Overview
This document outlines the comprehensive implementation plan for widget editing functionality in the RealsysCMS Page Builder, including section editing, widget property management, content item field editing, and drag-and-drop repositioning using GridStack.

## Current Architecture Analysis

### Database Structure
- **PageSection Model**: Contains GridStack positioning (`grid_x`, `grid_y`, `grid_w`, `grid_h`, `grid_id`), styling, and configuration
- **PageSectionWidget Model**: Widget instances with settings, content queries, and positioning within sections
- **ContentItem Model**: Content items with field values stored in `ContentFieldValue` model
- **ContentFieldValue Model**: Individual field values with references to content type fields

### Current Preview System
- **Section Toolbar**: Already implemented with Add Widget, Edit, Delete buttons
- **Widget Selection**: Click-to-select widgets with highlight effects
- **Message System**: Parent-iframe communication for toolbar actions
- **GridStack Integration**: Library loaded and configured for drag-and-drop

## Implementation Phases

## Phase 1: Widget Toolbar Enhancement

### 1.1 Widget Toolbar Design
**Location**: `public/assets/admin/js/live-designer/preview-helpers.js`

```javascript
// Enhanced widget toolbar with full CRUD operations
function createWidgetToolbar(widget, widgetData) {
    return `
        <div class="widget-toolbar" data-widget-instance="${widgetData.instanceId}">
            <div class="toolbar-group">
                <button class="toolbar-btn edit-widget" title="Edit Widget">
                    <i class="ri-edit-line"></i>
                </button>
                <button class="toolbar-btn copy-widget" title="Copy Widget">
                    <i class="ri-file-copy-line"></i>
                </button>
                <button class="toolbar-btn delete-widget" title="Delete Widget">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="toolbar-group">
                <button class="toolbar-btn move-widget drag-handle" title="Drag to Move">
                    <i class="ri-drag-move-2-line"></i>
                </button>
            </div>
        </div>
    `;
}
```

### 1.2 Widget Toolbar CSS
**Location**: `public/assets/admin/css/live-designer/preview-helpers.css`

```css
.widget-toolbar {
    position: absolute;
    top: -40px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 4px;
    display: flex;
    gap: 4px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
}

.preview-highlighted .widget-toolbar {
    opacity: 1;
    visibility: visible;
}

.toolbar-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f8f9fa;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.toolbar-btn:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.toolbar-btn.edit-widget { color: #0d6efd; }
.toolbar-btn.copy-widget { color: #6f42c1; }
.toolbar-btn.delete-widget { color: #dc3545; }
.toolbar-btn.drag-handle { color: #6c757d; cursor: grab; }
.toolbar-btn.drag-handle:active { cursor: grabbing; }
```

### 1.3 Enhanced Widget Selection
**Implementation**: Modify `setupWidgetInteractions()` to include toolbar creation

```javascript
function enhanceWidgetWithToolbar(widget) {
    const widgetData = {
        instanceId: widget.dataset.previewWidget,
        widgetId: widget.dataset.widgetId,
        sectionId: widget.dataset.sectionId,
        widgetName: widget.dataset.widgetName
    };
    
    // Make widget container relative for absolute toolbar positioning
    widget.style.position = 'relative';
    
    // Create and inject toolbar
    const toolbar = document.createElement('div');
    toolbar.innerHTML = createWidgetToolbar(widget, widgetData);
    widget.appendChild(toolbar.firstElementChild);
    
    // Setup toolbar event handlers
    setupWidgetToolbarHandlers(widget, widgetData);
}
```

## Phase 2: Widget CRUD Operations

### 2.1 Widget Edit Modal
**Location**: New modal in `resources/views/admin/pages/page-builder/modals/widget-edit.blade.php`

```html
<div class="modal fade" id="widgetEditModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Widget Field Values -->
                        <div id="widgetFieldsEditor">
                            <!-- Dynamic content based on widget type -->
                        </div>
                        <!-- Content Items Editor -->
                        <div id="widgetContentEditor">
                            <!-- Content selection and editing -->
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <!-- Widget Settings -->
                        <div id="widgetSettings">
                            <!-- Layout, styling, advanced settings -->
                        </div>
                        <!-- Live Preview -->
                        <div id="widgetLivePreview">
                            <!-- Real-time preview of changes -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWidgetChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>
```

### 2.2 Widget Edit Manager
**Location**: New file `public/assets/admin/js/page-builder/widget-edit-manager.js`

```javascript
class WidgetEditManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.currentWidget = null;
        this.modal = null;
        this.init();
    }
    
    async openWidgetEditor(widgetInstanceId) {
        try {
            // Load widget data
            const widgetData = await this.loadWidgetData(widgetInstanceId);
            
            // Populate modal
            this.populateEditModal(widgetData);
            
            // Show modal
            this.modal.show();
            
        } catch (error) {
            console.error('Error opening widget editor:', error);
        }
    }
    
    async loadWidgetData(widgetInstanceId) {
        const response = await fetch(`${this.apiBaseUrl}/widgets/instances/${widgetInstanceId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        });
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        return data.success ? data.data : null;
    }
    
    populateEditModal(widgetData) {
        // Populate widget fields editor
        this.populateWidgetFields(widgetData.widget_fields);
        
        // Populate content editor
        this.populateContentEditor(widgetData.content_items);
        
        // Populate settings
        this.populateWidgetSettings(widgetData.settings);
        
        // Setup live preview
        this.setupLivePreview(widgetData);
    }
}
```

### 2.3 Backend API Endpoints
**Location**: Add methods to `app/Http/Controllers/Api/PageBuilderController.php`

```php
/**
 * Get widget instance data for editing
 */
public function getWidgetInstance(PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        $widgetInstance->load([
            'widget.fieldDefinitions',
            'pageSection',
            'contentItems.fieldValues.field'
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $widgetInstance->id,
                'widget' => $widgetInstance->widget,
                'settings' => $widgetInstance->settings,
                'content_query' => $widgetInstance->content_query,
                'content_items' => $this->formatContentItems($widgetInstance),
                'widget_fields' => $this->extractWidgetFieldValues($widgetInstance),
                'positioning' => $widgetInstance->getGridPosition()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to load widget data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update widget instance
 */
public function updateWidgetInstance(Request $request, PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        $validated = $request->validate([
            'settings' => 'nullable|array',
            'content_query' => 'nullable|array',
            'widget_fields' => 'nullable|array',
            'positioning' => 'nullable|array',
            'content_items' => 'nullable|array'
        ]);
        
        // Update widget instance
        $widgetInstance->update([
            'settings' => $validated['settings'] ?? $widgetInstance->settings,
            'content_query' => $validated['content_query'] ?? $widgetInstance->content_query,
            'grid_x' => $validated['positioning']['x'] ?? $widgetInstance->grid_x,
            'grid_y' => $validated['positioning']['y'] ?? $widgetInstance->grid_y,
            'grid_w' => $validated['positioning']['w'] ?? $widgetInstance->grid_w,
            'grid_h' => $validated['positioning']['h'] ?? $widgetInstance->grid_h
        ]);
        
        // Update content items if provided
        if (isset($validated['content_items'])) {
            $this->updateWidgetContentItems($widgetInstance, $validated['content_items']);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Widget updated successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update widget: ' . $e->getMessage()
        ], 500);
    }
}
```

## Phase 3: GridStack Drag-and-Drop Integration

### 3.1 GridStack Widget Manager
**Location**: New file `public/assets/admin/js/page-builder/gridstack-widget-manager.js`

```javascript
class GridStackWidgetManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.grids = new Map(); // Track GridStack instances per section
        this.init();
    }
    
    init() {
        // Initialize GridStack for each section
        this.initializeSectionGrids();
        
        // Setup drag-and-drop handlers
        this.setupDragHandlers();
    }
    
    initializeSectionGrids() {
        const sections = document.querySelectorAll('[data-section-id]');
        
        sections.forEach(section => {
            const sectionId = section.dataset.sectionId;
            
            // Create GridStack container within section
            const gridContainer = this.createGridContainer(section);
            
            // Initialize GridStack instance
            const grid = GridStack.init({
                column: 12,
                cellHeight: 'auto',
                acceptWidgets: true,
                removable: false,
                float: false,
                animate: true,
                handle: '.drag-handle'
            }, gridContainer);
            
            // Add existing widgets to grid
            this.addExistingWidgetsToGrid(grid, sectionId);
            
            // Setup grid event handlers
            this.setupGridEventHandlers(grid, sectionId);
            
            this.grids.set(sectionId, grid);
        });
    }
    
    setupGridEventHandlers(grid, sectionId) {
        // Handle widget position changes
        grid.on('change', (event, items) => {
            this.handleWidgetPositionChange(items, sectionId);
        });
        
        // Handle drag start
        grid.on('dragstart', (event, element) => {
            element.classList.add('dragging');
        });
        
        // Handle drag stop
        grid.on('dragstop', (event, element) => {
            element.classList.remove('dragging');
        });
    }
    
    async handleWidgetPositionChange(items, sectionId) {
        try {
            const updates = items.map(item => ({
                widget_instance_id: item.el.dataset.widgetInstance,
                x: item.x,
                y: item.y,
                w: item.w,
                h: item.h
            }));
            
            await this.updateWidgetPositions(updates, sectionId);
            
        } catch (error) {
            console.error('Error updating widget positions:', error);
        }
    }
}
```

### 3.2 Drag-and-Drop CSS Enhancements
**Location**: `public/assets/admin/css/gridstack-designer.css`

```css
/* GridStack widget enhancements */
.grid-stack-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.grid-stack-item.dragging {
    transform: rotate(2deg);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    z-index: 999;
}

.grid-stack-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Drag handle styling */
.drag-handle {
    cursor: grab;
    user-select: none;
}

.drag-handle:active {
    cursor: grabbing;
}

/* Drop zones */
.grid-stack-placeholder {
    background: rgba(13, 110, 253, 0.1) !important;
    border: 2px dashed #0d6efd !important;
    border-radius: 8px;
}

/* Section grid containers */
.section-grid-container {
    min-height: 100px;
    padding: 10px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.section-grid-container.drag-over {
    background-color: rgba(13, 110, 253, 0.05);
}
```

## Phase 4: Content Item Field Editing

### 4.1 Inline Content Editor
**Location**: New component `resources/views/admin/pages/page-builder/components/inline-content-editor.blade.php`

```html
<div class="inline-content-editor" id="inlineContentEditor">
    <div class="editor-header">
        <h6 class="editor-title">Edit Content</h6>
        <div class="editor-actions">
            <button class="btn btn-sm btn-secondary" id="cancelContentEdit">Cancel</button>
            <button class="btn btn-sm btn-primary" id="saveContentEdit">Save</button>
        </div>
    </div>
    
    <div class="editor-body">
        <div class="content-item-selector">
            <select class="form-select" id="contentItemSelect">
                <!-- Dynamic options -->
            </select>
        </div>
        
        <div class="field-editors" id="contentFieldEditors">
            <!-- Dynamic field editors based on content type -->
        </div>
    </div>
</div>
```

### 4.2 Field Type Editors
**Location**: New file `public/assets/admin/js/page-builder/field-editors.js`

```javascript
class FieldEditorManager {
    static createEditor(field, currentValue) {
        switch (field.field_type) {
            case 'text':
                return `<input type="text" class="form-control" value="${currentValue || ''}" data-field-id="${field.id}">`;
                
            case 'textarea':
                return `<textarea class="form-control" rows="3" data-field-id="${field.id}">${currentValue || ''}</textarea>`;
                
            case 'rich_text':
                return this.createRichTextEditor(field, currentValue);
                
            case 'image':
                return this.createImageEditor(field, currentValue);
                
            case 'select':
                return this.createSelectEditor(field, currentValue);
                
            case 'boolean':
                return this.createBooleanEditor(field, currentValue);
                
            case 'date':
                return `<input type="date" class="form-control" value="${currentValue || ''}" data-field-id="${field.id}">`;
                
            case 'number':
                return `<input type="number" class="form-control" value="${currentValue || ''}" data-field-id="${field.id}">`;
                
            case 'json':
                return this.createJsonEditor(field, currentValue);
                
            default:
                return `<input type="text" class="form-control" value="${currentValue || ''}" data-field-id="${field.id}">`;
        }
    }
    
    static createRichTextEditor(field, currentValue) {
        return `
            <div class="rich-text-editor" data-field-id="${field.id}">
                <div class="editor-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="bold">B</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="italic">I</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="underline">U</button>
                </div>
                <div class="editor-content" contenteditable="true">${currentValue || ''}</div>
            </div>
        `;
    }
    
    static createImageEditor(field, currentValue) {
        return `
            <div class="image-editor" data-field-id="${field.id}">
                <div class="current-image">
                    ${currentValue ? `<img src="${currentValue}" alt="Current image" class="img-thumbnail">` : '<div class="no-image">No image selected</div>'}
                </div>
                <div class="image-actions">
                    <input type="file" class="form-control" accept="image/*">
                    <button type="button" class="btn btn-sm btn-danger" ${!currentValue ? 'disabled' : ''}>Remove</button>
                </div>
            </div>
        `;
    }
}
```

## Phase 5: Advanced Features

### 5.1 Widget Copy/Duplicate
**Implementation**: Add copy functionality to widget toolbar

```javascript
async function copyWidget(widgetInstanceId) {
    try {
        const response = await fetch(`${this.apiBaseUrl}/widgets/instances/${widgetInstanceId}/copy`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        });
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        
        if (data.success) {
            // Refresh preview to show duplicated widget
            this.refreshPagePreview();
            this.showSuccess('Widget copied successfully');
        }
        
    } catch (error) {
        console.error('Error copying widget:', error);
        this.showError('Failed to copy widget');
    }
}
```

### 5.2 Undo/Redo System
**Location**: New file `public/assets/admin/js/page-builder/history-manager.js`

```javascript
class HistoryManager {
    constructor() {
        this.history = [];
        this.currentIndex = -1;
        this.maxHistory = 50;
    }
    
    saveState(action, data) {
        // Remove any history after current index
        this.history = this.history.slice(0, this.currentIndex + 1);
        
        // Add new state
        this.history.push({
            action,
            data,
            timestamp: Date.now()
        });
        
        // Limit history size
        if (this.history.length > this.maxHistory) {
            this.history.shift();
        } else {
            this.currentIndex++;
        }
        
        this.updateUndoRedoButtons();
    }
    
    undo() {
        if (this.canUndo()) {
            const state = this.history[this.currentIndex];
            this.currentIndex--;
            this.applyState(state, 'undo');
            this.updateUndoRedoButtons();
        }
    }
    
    redo() {
        if (this.canRedo()) {
            this.currentIndex++;
            const state = this.history[this.currentIndex];
            this.applyState(state, 'redo');
            this.updateUndoRedoButtons();
        }
    }
}
```

### 5.3 Live Preview Updates
**Implementation**: Real-time preview updates during editing

```javascript
class LivePreviewManager {
    constructor() {
        this.debounceTimeout = null;
        this.previewIframe = document.getElementById('pagePreviewIframe');
    }
    
    updatePreview(changes, immediate = false) {
        if (immediate) {
            this.applyPreviewChanges(changes);
        } else {
            // Debounce updates
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                this.applyPreviewChanges(changes);
            }, 300);
        }
    }
    
    applyPreviewChanges(changes) {
        if (!this.previewIframe?.contentWindow) return;
        
        this.previewIframe.contentWindow.postMessage({
            type: 'update-preview',
            changes: changes
        }, '*');
    }
}
```

## Implementation Timeline

### Week 1: Foundation
- [ ] Enhance widget toolbar in preview helpers
- [ ] Create widget edit modal structure
- [ ] Implement basic widget data loading API

### Week 2: Core Editing
- [ ] Build widget edit manager
- [ ] Implement field editors for all field types
- [ ] Create content item inline editing

### Week 3: GridStack Integration
- [ ] Implement drag-and-drop functionality
- [ ] Add position persistence
- [ ] Create visual feedback for dragging

### Week 4: Advanced Features
- [ ] Add copy/duplicate functionality
- [ ] Implement undo/redo system
- [ ] Polish live preview updates

### Week 5: Testing & Polish
- [ ] Comprehensive testing
- [ ] Performance optimization
- [ ] UI/UX refinements
- [ ] Documentation

## API Routes to Add

```php
// Widget Instance Management
Route::get('/widgets/instances/{instance}', [PageBuilderController::class, 'getWidgetInstance']);
Route::put('/widgets/instances/{instance}', [PageBuilderController::class, 'updateWidgetInstance']);
Route::post('/widgets/instances/{instance}/copy', [PageBuilderController::class, 'copyWidget']);
Route::delete('/widgets/instances/{instance}', [PageBuilderController::class, 'deleteWidget']);

// Position Updates
Route::post('/sections/{section}/widgets/reorder', [PageBuilderController::class, 'reorderWidgets']);
Route::post('/widgets/instances/batch-update-positions', [PageBuilderController::class, 'batchUpdatePositions']);

// Content Item Field Updates
Route::put('/content-items/{item}/fields/{field}', [PageBuilderController::class, 'updateContentField']);
Route::post('/content-items/{item}/fields/batch-update', [PageBuilderController::class, 'batchUpdateContentFields']);
```

## File Structure

```
Implementation/
├── Backend/
│   ├── Controllers/
│   │   └── WidgetEditApiController.php
│   ├── Requests/
│   │   ├── WidgetUpdateRequest.php
│   │   └── ContentFieldUpdateRequest.php
│   └── Services/
│       ├── WidgetEditService.php
│       └── ContentFieldUpdateService.php
├── Frontend/
│   ├── JavaScript/
│   │   ├── widget-edit-manager.js
│   │   ├── gridstack-widget-manager.js
│   │   ├── field-editors.js
│   │   ├── history-manager.js
│   │   └── live-preview-manager.js
│   ├── CSS/
│   │   ├── widget-toolbar.css
│   │   ├── gridstack-enhancements.css
│   │   └── field-editors.css
│   └── Views/
│       ├── widget-edit-modal.blade.php
│       └── inline-content-editor.blade.php
└── Documentation/
    ├── API_Documentation.md
    ├── User_Guide.md
    └── Developer_Guide.md
```

This implementation plan provides a complete roadmap for implementing widget editing functionality with modern UI/UX patterns, robust backend architecture, and comprehensive CRUD operations for both widgets and content items.