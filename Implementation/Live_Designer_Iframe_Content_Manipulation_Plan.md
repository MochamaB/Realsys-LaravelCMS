# Live-Designer Iframe Content Manipulation Plan (Simplified)

## Overview
This plan details the implementation of drag-and-drop functionality within the live-designer's iframe content area by **extending the existing preview-helpers.js system**. Instead of creating new complex systems, this approach builds on the current working infrastructure to add drop zones, widget reordering, and real-time updates.

## Current State Analysis

### ✅ Existing Working Components
- **preview-helpers.js**: Complete selection system with cross-frame communication
- **LivePreviewController**: All necessary API endpoints already exist
- **PageSection/PageSectionWidget Models**: Support for positioning and styling
- **Template Rendering**: Server-side rendering with data attributes
- **Cross-Frame Communication**: Working postMessage system

### Integration Points
- Extend existing `preview-helpers.js` (no new files needed)
- Use existing `LivePreviewController` API endpoints
- Leverage current selection and highlighting system
- Build on existing cross-frame communication

## Simplified Architecture

### Drop Zone Types (Built on Existing Elements)
1. **Section Drop Zones** - Use existing `[data-section-id]` elements
2. **Widget Reordering** - Use existing `[data-page-section-widget-id]` elements  
3. **Empty Section Indicators** - Enhance existing empty section handling
4. **Between-Widget Drop Zones** - Add minimal drop indicators

### Communication Flow (Extend Existing System)
- **Parent → Iframe**: Extend existing message handling in `preview-helpers.js`
- **Iframe → Parent**: Use existing `parent.postMessage()` patterns from selection system
- **API Updates**: Use existing `updateWidgetPreview()` and `updateSectionPreview()` endpoints
- **LivePreview Coordination**: Extend existing `live-preview.js` message handling

### Widget Drop Handling with Auto-Content Creation

When widgets are dropped from the sidebar into the iframe, the iframe handles the drop logic:

#### For Widgets WITH Content-Types (Existing Logic)
```javascript
// Message from sidebar: { type: 'widget', widgetId: 123, contentItemId: 456 }
// Creates PageSectionWidget with existing content_query pointing to selected content
```

#### For Widgets WITHOUT Content-Types (New Auto-Content Logic)  
```javascript
// Message from sidebar: { type: 'widget', widgetId: 123, requiresDefaultContent: true }
async function handleWidgetDropWithAutoContent(dragData, dropZone) {
    try {
        // 1. Call API to create default content-type and content-item
        const response = await fetch(`/admin/live-preview/widgets/${dragData.widgetId}/create-with-default-content`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                section_id: dropZone.dataset.sectionId,
                position: calculateDropPosition(dropZone)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 2. Create PageSectionWidget with auto-created content_query
            await createPageSectionWidget({
                widget_id: dragData.widgetId,
                section_id: dropZone.dataset.sectionId,
                content_query: result.content_query, // Points to auto-created content
                position: result.position
            });
            
            // 3. Refresh iframe to show widget with realistic default content
            window.location.reload();
        }
    } catch (error) {
        console.error('Error creating widget with default content:', error);
        showErrorMessage('Failed to add widget with default content');
    }
}
```

#### API Endpoint Implementation
**Location**: `app/Http/Controllers/Api/LivePreviewController.php`
**Method**: `createWidgetWithDefaultContent(Widget $widget, Request $request)`

**Logic**:
1. Check if widget has `contentTypeAssociations`
2. If none exist, create default `ContentType` with fields matching widget `WidgetFieldDefinition`s
3. Create default `ContentItem` with realistic sample data based on field types
4. Create `WidgetContentTypeAssociation` linking widget to new content-type
5. Return `content_query` structure for `PageSectionWidget` creation

**Sample Default Content Creation**:
```php
// Auto-create ContentType: "Default Hero Section Content"
$contentType = ContentType::create([
    'name' => "Default {$widget->name} Content",
    'slug' => "default-{$widget->slug}-content",
    'description' => "Auto-generated content type for {$widget->name} widget"
]);

// Create fields matching widget field definitions
foreach ($widget->fieldDefinitions as $fieldDef) {
    ContentTypeField::create([
        'content_type_id' => $contentType->id,
        'name' => $fieldDef->name,
        'slug' => $fieldDef->slug,
        'field_type' => $fieldDef->field_type,
        'settings' => $fieldDef->settings
    ]);
}

// Create default content item with realistic sample data
$contentItem = ContentItem::create([
    'content_type_id' => $contentType->id,
    'title' => "Sample {$widget->name}",
    'slug' => "sample-{$widget->slug}-" . time(),
    'status' => 'published'
]);

// Populate field values with realistic defaults
foreach ($contentType->fields as $field) {
    ContentItemFieldValue::create([
        'content_item_id' => $contentItem->id,
        'field_id' => $field->id,
        'value' => $this->generateRealisticDefaultValue($field)
    ]);
}
```

## Detailed Explanation

### Current System Analysis

#### LivePreview Class (live-preview.js)
The `LivePreview` class manages the parent window side of the iframe communication:

- **Iframe Communication**: Handles messages from iframe via `setupIframeCommunication()`
- **Helper Injection**: Injects preview functionality via `injectPreviewHelpers()`
- **Widget Selection**: Processes widget/section selection from iframe
- **API Integration**: Uses existing endpoints for widget operations
- **Loading Management**: Provides loading states and error handling

#### Preview Helpers (preview-helpers.js)
The iframe-side script provides:

- **Component Selection**: Click-to-select widgets and sections
- **Cross-Frame Messaging**: Communicates selections back to parent
- **Visual Feedback**: Hover effects and selection highlighting
- **Structure Mapping**: Uses existing data attributes for component identification
- **Keyboard Navigation**: Arrow keys and ESC for selection management

#### Existing Data Flow
1. **Page Load**: LivePreview injects helpers into iframe
2. **User Clicks**: preview-helpers.js detects clicks on components
3. **Selection**: Cross-frame message sent to parent with component data
4. **Editor Load**: Parent loads appropriate editor form via API
5. **Updates**: Form submissions update database and refresh preview

### Integration Strategy

#### Why Extend Instead of Replace
1. **Working Foundation**: Current selection system is fully functional
2. **Proven Communication**: Cross-frame messaging already reliable
3. **API Compatibility**: Existing endpoints handle all required operations
4. **Model Support**: Database schema already supports positioning and styling
5. **No Conflicts**: No existing drag/drop code to remove

#### Drag/Drop Integration Points
1. **SortableJS Addition**: Add to existing iframe initialization
2. **Drop Zone Enhancement**: Use existing section elements as drop targets
3. **API Reuse**: Leverage existing `updateWidgetPreview()` and `addWidget()` endpoints
4. **Message Extension**: Add drag-specific messages to existing communication

## Implementation Approach (Extend Existing Files)

### 1. Enhance preview-helpers.js (Main Changes)

**Explanation**: The existing `initializePreviewHelpers()` function in preview-helpers.js already sets up component selection and cross-frame communication. We extend this function to also initialize drag/drop functionality.

```javascript
// Add to existing initializePreviewHelpers() function
function initializePreviewHelpers() {
    console.log('🎨 Initializing preview helpers');
    
    // ... existing code for selection, keyboard shortcuts, etc. ...
    
    // NEW: Add drag and drop initialization
    initializeDragAndDrop();
    
    console.log('✅ Preview helpers initialized with drag and drop');
}

// NEW: Initialize SortableJS for widget reordering
function initializeDragAndDrop() {
    const sections = document.querySelectorAll('[data-section-id]');
    
    sections.forEach(section => {
        // Initialize SortableJS on each section with custom drag handle
        new Sortable(section, {
            group: 'widgets',
            animation: 150,
            handle: '.widget-drag-handle', // Only drag handle triggers drag
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            filter: '.no-drag',
            draggable: '[data-page-section-widget-id]', // Only widgets are draggable
            onStart: handleDragStart,
            onEnd: handleDragEnd,
            onAdd: handleWidgetAdd,
            onUpdate: handleWidgetReorder
        });
    });
    
    // Listen for drag events from parent window
    window.addEventListener('message', handleParentDragMessage);
}

// NEW: Create widget selection toolbar with drag and resize controls
function createWidgetToolbar(widget) {
    // Remove existing toolbar
    const existingToolbar = widget.querySelector('.widget-toolbar');
    if (existingToolbar) {
        existingToolbar.remove();
    }
    
    const widgetId = widget.dataset.pageSectonWidgetId;
    const widgetName = widget.dataset.widgetName || 'Widget';
    
    // Create toolbar container
    const toolbar = document.createElement('div');
    toolbar.className = 'widget-toolbar';
    toolbar.innerHTML = `
        <div class="widget-toolbar-buttons">
            <button class="widget-toolbar-btn drag-handle widget-drag-handle" data-action="drag" title="Drag to reorder">
                <i class="ri-drag-move-2-fill"></i>
            </button>
            <button class="widget-toolbar-btn resize-handle" data-action="resize" title="Resize widget">
                <i class="ri-expand-diagonal-fill"></i>
            </button>
            <button class="widget-toolbar-btn" data-action="edit" title="Edit ${widgetName}">
                <i class="ri-settings-3-fill"></i>
            </button>
            <button class="widget-toolbar-btn btn-danger" data-action="delete" title="Delete ${widgetName}">
                <i class="ri-delete-bin-fill"></i>
            </button>
        </div>
    `;
    
    // Add click handlers
    toolbar.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.target.closest('.widget-toolbar-btn');
        if (button) {
            const action = button.getAttribute('data-action');
            
            switch (action) {
                case 'resize':
                    toggleResizeMode(widget);
                    break;
                case 'edit':
                case 'delete':
                    handleToolbarAction(button, widget);
                    break;
                // 'drag' action is handled by SortableJS via the handle
            }
        }
    });
    
    // Position toolbar at top-right of widget
    widget.style.position = 'relative';
    widget.appendChild(toolbar);
    
    console.log(`✅ Created toolbar for widget ${widgetId}`);
}

// NEW: Toggle resize mode for selected widget
function toggleResizeMode(widget) {
    const isResizing = widget.classList.contains('resize-mode');
    
    if (isResizing) {
        // Exit resize mode
        widget.classList.remove('resize-mode');
        removeResizeHandles(widget);
    } else {
        // Enter resize mode
        widget.classList.add('resize-mode');
        createResizeHandles(widget);
    }
}

// NEW: Create resize handles around widget
function createResizeHandles(widget) {
    // Remove existing handles
    removeResizeHandles(widget);
    
    const handles = ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w'];
    
    handles.forEach(direction => {
        const handle = document.createElement('div');
        handle.className = `resize-handle resize-${direction}`;
        handle.dataset.direction = direction;
        
        // Add resize event handlers
        handle.addEventListener('mousedown', (e) => startResize(e, widget, direction));
        
        widget.appendChild(handle);
    });
}

// NEW: Remove resize handles
function removeResizeHandles(widget) {
    const handles = widget.querySelectorAll('.resize-handle');
    handles.forEach(handle => handle.remove());
}

// NEW: Start resize operation
function startResize(e, widget, direction) {
    e.preventDefault();
    e.stopPropagation();
    
    const startX = e.clientX;
    const startY = e.clientY;
    const startWidth = widget.offsetWidth;
    const startHeight = widget.offsetHeight;
    
    function handleMouseMove(e) {
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        
        let newWidth = startWidth;
        let newHeight = startHeight;
        
        // Calculate new dimensions based on direction
        if (direction.includes('e')) newWidth = startWidth + deltaX;
        if (direction.includes('w')) newWidth = startWidth - deltaX;
        if (direction.includes('s')) newHeight = startHeight + deltaY;
        if (direction.includes('n')) newHeight = startHeight - deltaY;
        
        // Apply constraints
        newWidth = Math.max(100, newWidth); // Min width 100px
        newHeight = Math.max(50, newHeight); // Min height 50px
        
        // Apply new dimensions
        widget.style.width = newWidth + 'px';
        widget.style.height = newHeight + 'px';
    }
    
    function handleMouseUp() {
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
        
        // Save resize changes via API
        saveWidgetDimensions(widget);
    }
    
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
}

// NEW: Save widget dimensions to database
async function saveWidgetDimensions(widget) {
    const widgetId = widget.dataset.pageSectonWidgetId;
    const width = widget.style.width;
    const height = widget.style.height;
    
    try {
        const response = await fetch(`/admin/api/live-preview/widgets/${widgetId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({
                min_width: width,
                min_height: height
            })
        });
        
        if (response.ok) {
            console.log(`✅ Saved widget dimensions: ${width} x ${height}`);
            parent.postMessage({
                type: 'widget-resized',
                data: { widgetId, width, height }
            }, '*');
        } else {
            throw new Error('Failed to save widget dimensions');
        }
    } catch (error) {
        console.error('Widget resize save failed:', error);
        parent.postMessage({
            type: 'widget-resize-error',
            data: { error: error.message }
        }, '*');
    }
}

// NEW: Handle drag start events
function handleDragStart(evt) {
    const widgetId = evt.item.dataset.pageSectonWidgetId;
    const sectionId = evt.from.dataset.sectionId;
    
    // Notify parent window
    parent.postMessage({
        type: 'widget-drag-start',
        data: { widgetId, sectionId }
    }, '*');
    
    // Add visual feedback
    document.body.classList.add('dragging-active');
}

// NEW: Handle drag end events
function handleDragEnd(evt) {
    document.body.classList.remove('dragging-active');
    
    parent.postMessage({
        type: 'widget-drag-end',
        data: {}
    }, '*');
}

// NEW: Handle widget reordering within same section
function handleWidgetReorder(evt) {
    const widgetId = evt.item.dataset.pageSectonWidgetId;
    const newPosition = evt.newIndex;
    
    updateWidgetPosition(widgetId, null, newPosition);
}

// NEW: Handle widget addition from sidebar
function handleWidgetAdd(evt) {
    const widgetId = evt.item.dataset.pageSectonWidgetId;
    const sectionId = evt.to.dataset.sectionId;
    const position = evt.newIndex;
    
    updateWidgetPosition(widgetId, sectionId, position);
}

// NEW: Update widget position via API
async function updateWidgetPosition(widgetId, sectionId, position) {
    try {
        const updateData = { position };
        if (sectionId) updateData.page_section_id = sectionId;
        
        const response = await fetch(`/admin/api/live-preview/widgets/${widgetId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify(updateData)
        });
        
        if (response.ok) {
            parent.postMessage({
                type: 'widget-position-updated',
                data: { widgetId, sectionId, position }
            }, '*');
        } else {
            throw new Error('Failed to update widget position');
        }
    } catch (error) {
        console.error('Widget position update failed:', error);
        parent.postMessage({
            type: 'widget-drop-error',
            data: { error: error.message }
        }, '*');
    }
}

// NEW: Handle drag messages from parent window
function handleParentDragMessage(event) {
    if (event.source !== parent) return;
    
    const { type, data } = event.data;
    
    switch (type) {
        case 'sidebar-drag-start':
            showDropZones(data.dragType);
            break;
        case 'sidebar-drag-end':
            hideDropZones();
            break;
        case 'sidebar-widget-drop':
            handleSidebarWidgetDrop(data);
            break;
    }
}

// NEW: Show drop zones when dragging from sidebar
function showDropZones(dragType) {
    const sections = document.querySelectorAll('[data-section-id]');
    
    sections.forEach(section => {
        // Add drop zone indicators
        section.classList.add('drop-zone-active');
        
        // Create empty drop zone if section has no widgets
        const widgets = section.querySelectorAll('[data-page-section-widget-id]');
        if (widgets.length === 0) {
            const dropZone = document.createElement('div');
            dropZone.className = 'empty-section-drop-zone';
            dropZone.textContent = 'Drop widget here';
            section.appendChild(dropZone);
        }
    });
}

// NEW: Hide drop zones
function hideDropZones() {
    const sections = document.querySelectorAll('[data-section-id]');
    
    sections.forEach(section => {
        section.classList.remove('drop-zone-active');
        
        // Remove empty drop zone indicators
        const emptyZones = section.querySelectorAll('.empty-section-drop-zone');
        emptyZones.forEach(zone => zone.remove());
    });
}

// NEW: Handle widget drops from sidebar
async function handleSidebarWidgetDrop(data) {
    try {
        const response = await fetch(`/admin/api/live-preview/sections/${data.sectionId}/widgets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({
                widget_id: data.widgetId,
                content_query: data.contentQuery || null,
                settings: data.settings || {}
            })
        });
        
        if (response.ok) {
            // Refresh the preview to show new widget
            location.reload();
            
            parent.postMessage({
                type: 'widget-drop-success',
                data: { widgetId: data.widgetId, sectionId: data.sectionId }
            }, '*');
        } else {
            throw new Error('Failed to add widget');
        }
    } catch (error) {
        console.error('Widget drop failed:', error);
        parent.postMessage({
            type: 'widget-drop-error',
            data: { error: error.message }
        }, '*');
    }
}

### 2. Enhance LivePreview Class Integration

**Explanation**: The `LivePreview` class in live-preview.js manages parent-side communication. We need to extend its message handling to support drag operations from the sidebar.

**Add to existing live-preview.js**:

```javascript
// Extend existing setupIframeCommunication() method
setupIframeCommunication() {
    // ... existing message handling ...
    
    window.addEventListener('message', (event) => {
        if (event.source !== this.options.previewIframe.contentWindow) return;
        
        const { type, data } = event.data;
        
        switch (type) {
            case 'widget-selected':
                this.onWidgetSelected(data);
                break;
            case 'section-selected':
                this.onSectionSelected(data);
                break;
            // NEW: Handle drag/drop events from iframe
            case 'widget-drop-success':
                this.handleWidgetDropSuccess(data);
                break;
            case 'widget-drop-error':
                this.handleWidgetDropError(data);
                break;
            case 'widget-position-updated':
                this.handleWidgetPositionUpdate(data);
                break;
        }
    });
}

// NEW: Handle successful widget drops
handleWidgetDropSuccess(data) {
    console.log('✅ Widget drop successful:', data);
    
    // Update sidebar structure if needed
    if (window.sidebarManager) {
        window.sidebarManager.refreshStructure();
    }
    
    // Show success message
    this.showMessage('Widget added successfully', 'success');
}

// NEW: Handle widget drop errors
handleWidgetDropError(data) {
    console.error('❌ Widget drop failed:', data);
    this.showMessage('Failed to add widget: ' + data.error, 'error');
}

// NEW: Handle widget position updates
handleWidgetPositionUpdate(data) {
    console.log('📍 Widget position updated:', data);
    
    // Refresh sidebar structure to reflect new positions
    if (window.sidebarManager) {
        window.sidebarManager.refreshStructure();
    }
}

// NEW: Notify iframe about sidebar drag operations
notifyIframeDragStart(dragData) {
    this.sendMessageToIframe('sidebar-drag-start', dragData);
}

notifyIframeDragEnd() {
    this.sendMessageToIframe('sidebar-drag-end', {});
}

handleSidebarWidgetDrop(dropData) {
    this.sendMessageToIframe('sidebar-widget-drop', dropData);
}

// Helper method to send messages to iframe
sendMessageToIframe(type, data) {
    if (this.options.previewIframe?.contentWindow) {
        this.options.previewIframe.contentWindow.postMessage({
            type,
            data
        }, '*');
    }
}
```

## File Modification Summary

### Files to Modify (Extend, Don't Replace)

1. **preview-helpers.js** (~200 lines added)
   - Add `initializeDragAndDrop()` function
   - Add SortableJS initialization for sections
   - Add drop zone highlighting functions
   - Add resize handle creation and management
   - Extend existing message handling for drag events

2. **live-preview.js** (~50 lines added)
   - Extend `setupIframeCommunication()` for drag events
   - Add drag success/error handlers
   - Add position update handlers

3. **preview-helpers.css** (~100 lines added)
   - Add drop zone styling
   - Add resize handle styling
   - Add SortableJS ghost/chosen classes
   - Add drag feedback animations

4. **widget-drill-down.js** (~30 lines added)
   - Add iframe notification on drag start/end
   - Add drag data preparation for content items

### No New Files Created
- All functionality added to existing files
- Maintains current architecture and patterns
- Reduces complexity and maintenance overhead

## Cross-Frame Communication Protocol

### Message Types

#### Parent → Iframe
```javascript
// Drag initiation
{
    type: 'live-designer-drag-start',
    data: {
        dragType: 'widget|content-type|content-item',
        dragData: { /* drag source data */ },
        validDropTypes: ['section', 'widget-replace']
    }
}

// Drag movement
{
    type: 'live-designer-drag-move',
    data: {
        x: 100,
        y: 200,
        dragData: { /* current drag data */ }
    }
}

// Drag end
{
    type: 'live-designer-drag-end',
    data: {
        success: true|false,
        dropData: { /* drop result data */ }
    }
}
```

#### Iframe → Parent
```javascript
// Drop zone validation
{
    type: 'live-designer-drop-validate',
    data: {
        valid: true|false,
        dropZoneId: 'zone-123',
        reason: 'Widget type not supported'
    }
}

// Drop event
{
    type: 'live-designer-drop',
    data: {
        dropZoneId: 'zone-123',
        sectionId: 'section-456',
        position: 2,
        dropType: 'section-insert'
    }
}

// Preview update request
{
    type: 'live-designer-preview-update',
    data: {
        sectionId: 'section-456',
        updateType: 'widget-added|widget-moved|widget-removed'
    }
}
```

## Drop Zone Types

### Section Drop Zones
- **section-start**: Beginning of section (position 0)
- **section-empty**: Empty section (no widgets)
- **widget-after**: After specific widget (position n+1)
- **widget-replace**: Replace existing widget

### Widget Drop Zones
- **widget-before**: Insert before widget
- **widget-after**: Insert after widget
- **widget-replace**: Replace widget content
- **widget-settings**: Update widget settings

## Drag Validation Rules

### Widget Drops
- **Section Compatibility**: Check if widget type is allowed in section
- **Position Limits**: Validate maximum widgets per section
- **Content Requirements**: Ensure required content is available
- **Permission Checks**: Verify user can modify section

### Content Drops
- **Widget Compatibility**: Check if content type works with widget
- **Content Availability**: Verify content item exists and is accessible
- **Field Mapping**: Ensure content fields match widget requirements

## Error Handling

### Communication Errors
- **Message Timeout**: Handle failed cross-frame messages
- **Iframe Loading**: Wait for iframe ready state
- **Permission Errors**: Handle cross-origin restrictions

### Drag Operation Errors
- **Invalid Drops**: Provide clear feedback for invalid operations
- **API Failures**: Graceful fallback for backend errors
- **State Recovery**: Restore UI state after failed operations

### Preview Update Errors
- **Render Failures**: Handle template rendering errors
- **Asset Loading**: Manage missing assets or scripts
- **Content Errors**: Handle missing or invalid content

## Performance Optimization

### Message Optimization
- **Message Batching**: Batch frequent messages to reduce overhead
- **Debounced Updates**: Debounce rapid drag movements
- **Selective Updates**: Only update changed sections

### DOM Optimization
- **Virtual Drop Zones**: Create drop zones only when needed
- **Event Delegation**: Use efficient event handling
- **Memory Management**: Clean up event listeners and observers

### Preview Optimization
- **Incremental Updates**: Update only changed content
- **Asset Caching**: Cache widget assets and templates
- **Lazy Loading**: Load preview content on demand

## Security Considerations

### Cross-Frame Security
- **Origin Validation**: Verify message origins
- **Data Sanitization**: Sanitize all cross-frame data
- **Permission Checks**: Validate user permissions for operations

### Content Security
- **XSS Prevention**: Sanitize dynamic content in previews
- **CSRF Protection**: Include CSRF tokens in API calls
- **Content Validation**: Validate widget and content data

## Browser Compatibility

### Supported Features
- **postMessage API**: Cross-frame communication
- **Drag and Drop API**: Native drag support
- **CSS Grid/Flexbox**: Modern layout for drop zones
- **ES6 Modules**: Modern JavaScript features

### Fallback Strategies
- **Legacy Browser Support**: Polyfills for older browsers
- **Touch Device Support**: Touch-friendly drag operations
- **Reduced Motion**: Respect accessibility preferences

## Testing Strategy

### Unit Tests
- Cross-frame message handling
- Drop zone generation logic
- Drag validation rules
- Preview update mechanisms

### Integration Tests
- End-to-end drag operations
- Cross-frame communication reliability
- API integration and error handling
- Preview update accuracy

### User Experience Tests
- Drag responsiveness and feedback
- Visual indicator clarity
- Error message helpfulness
- Performance under load

## API Integration

### Required Endpoints

#### Widget Management
```
POST /admin/pages/{pageId}/sections/{sectionId}/widgets
PUT /admin/pages/{pageId}/sections/{sectionId}/widgets/{widgetId}
DELETE /admin/pages/{pageId}/sections/{sectionId}/widgets/{widgetId}
```

#### Position Updates
```
PATCH /admin/pages/{pageId}/sections/{sectionId}/widgets/reorder
POST /admin/pages/{pageId}/sections/{sectionId}/widgets/{widgetId}/move
```

#### Preview Generation
```
GET /admin/preview/section/{sectionId}
GET /admin/preview/widget/{widgetId}
POST /admin/preview/widget/render
```

### Request/Response Formats

#### Widget Addition
```javascript
// Request
{
    widget_type: 'content-list',
    position: 2,
    settings: {
        title: 'Recent Posts',
        limit: 5
    },
    content_query: {
        content_type_id: 1,
        filters: { status: 'published' }
    }
}

// Response
{
    success: true,
    widget: {
        id: 123,
        type: 'content-list',
        position: 2,
        html: '<div class="widget">...</div>'
    },
    section_html: '<section>...</section>'
}
```

## Future Enhancements

### Advanced Features
- **Multi-Widget Selection**: Drag multiple widgets simultaneously
- **Widget Grouping**: Group related widgets for batch operations
- **Undo/Redo System**: Track and reverse drag operations
- **Drag Templates**: Save and reuse common widget arrangements

### Performance Improvements
- **Virtual Scrolling**: Handle large numbers of widgets efficiently
- **Progressive Loading**: Load widget content as needed
- **Background Updates**: Update preview without blocking UI
- **Optimistic Updates**: Show changes immediately, sync later

### User Experience Enhancements
- **Drag Guides**: Visual guides for alignment and spacing
- **Smart Suggestions**: Suggest optimal widget placements
- **Keyboard Navigation**: Full keyboard support for drag operations
- **Voice Commands**: Accessibility support for voice control

## Dependencies

### Required Libraries
- SortableJS (from Core Drag Implementation Plan)
- Cross-frame communication utilities
- Existing preview system components
- Unified loader and error handling systems

### API Dependencies
- Page and section management endpoints
- Widget CRUD operations
- Preview generation services
- Content type and item APIs

### Browser APIs
- postMessage for cross-frame communication
- Drag and Drop API for native drag support
- Intersection Observer for drop zone detection
- ResizeObserver for responsive drop zones

## Conclusion

This implementation plan provides a comprehensive approach to adding drag-and-drop functionality within the live-designer's iframe content area. The cross-frame communication system ensures reliable operation while maintaining security, and the real-time preview updates provide immediate feedback to users. The modular design allows for incremental implementation and testing, ensuring stability and performance throughout the development process.

## Summary

This simplified plan leverages the existing live-designer infrastructure to add drag/drop and resize functionality with minimal code changes:

- **Extends existing files** instead of creating new complex systems
- **Uses existing API endpoints** without requiring backend changes
- **Builds on working selection system** and cross-frame communication
- **Integrates with current sidebar** and drill-down functionality
- **Provides complete drag/resize experience** with ~380 total lines added across 4 existing files

The approach is **implementation-ready** and maintains compatibility with all existing functionality while adding the requested drag/drop and resizing capabilities.

```
