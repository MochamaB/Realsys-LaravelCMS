# Live-Designer Left-Sidebar Drag Implementation Plan

## Overview
This plan details the implementation of drag-and-drop functionality for the live-designer's left sidebar, including accordion navigation, widget drill-down integration, and drag source management. This builds upon the Core Drag Implementation Plan and integrates with the existing widget-drill-down.js system.

## Current State Analysis

### Existing Components
- **Left Sidebar Structure**: Accordion-based navigation with widget categories
- **Widget Drill-Down System**: Standalone navigation for Widgets → Content Types → Content Items
- **Velzon Theme Integration**: Cards and UI components for consistent styling
- **Unified Loader System**: Loading state management across the interface

### Integration Points
- `widget-drill-down.js` - Current drill-down navigation system
- `left-sidebar.blade.php` - Sidebar HTML structure and templates
- `show.blade.php` - Main live-designer view with initialization
- Core Drag System (from Core Drag Implementation Plan)

## Architecture Overview

### Drag Source Types
1. **Widget Categories** - Top-level accordion items
2. **Widget Items** - Individual widgets within categories
3. **Content Type Cards** - Drill-down content type selections
4. **Content Item Cards** - Drill-down content item selections

### State Management
- **Accordion State**: Track expanded/collapsed sections during drag operations
- **Drill-Down State**: Maintain navigation context (widgets/content-types/content-items)
- **Drag Context**: Current drag source type and metadata
- **Preview State**: Real-time preview updates during drag

## Implementation Components

### 1. Enhanced SidebarManager Class

**File**: `public/assets/admin/js/live-designer/sidebar-manager.js` (EXTEND EXISTING)

```javascript
class SidebarManager {
    constructor() {
        // ... existing constructor code ...
        
        // NEW: Drag functionality properties
        this.dragEnabled = false;
        this.dragSources = new Map();
        this.accordionState = new Map();
        this.livePreview = null; // Will be set via setLivePreview()
        
        this.init();
    }
    
    // NEW: Initialize drag functionality
    initializeDragSupport(livePreview) {
        this.livePreview = livePreview;
        this.dragEnabled = true;
        
        console.log('🎯 Initializing sidebar drag support...');
        
        // Initialize section template dragging
        this.initializeSectionTemplateDrag();
        
        // Initialize widget dragging (for future phases)
        this.initializeWidgetDrag();
        
        console.log('✅ Sidebar drag support initialized');
    }
    
    // NEW: Initialize section template dragging with SortableJS
    initializeSectionTemplateDrag() {
        const sectionTemplates = document.querySelectorAll('.section-template-item');
        
        sectionTemplates.forEach(template => {
            // Make section template draggable
            template.setAttribute('draggable', 'true');
            template.classList.add('draggable-section-template');
            
            // Add drag event listeners
            template.addEventListener('dragstart', (e) => this.handleSectionDragStart(e));
            template.addEventListener('dragend', (e) => this.handleSectionDragEnd(e));
        });
        
        console.log(`✅ Initialized ${sectionTemplates.length} section templates for dragging`);
    }
    
    // NEW: Initialize widget dragging with content-type detection
    initializeWidgetDrag() {
        const themeWidgets = document.querySelectorAll('.theme-widget-item');
        const defaultWidgets = document.querySelectorAll('.default-widget-item');
        
        [...themeWidgets, ...defaultWidgets].forEach(widget => {
            // Check if widget has content-types or needs default creation
            this.prepareWidgetForDrag(widget);
        });
        
        console.log(`✅ Initialized ${themeWidgets.length + defaultWidgets.length} widgets for dragging`);
    }
    
    // NEW: Prepare widget for drag by checking content-type associations
    async prepareWidgetForDrag(widget) {
        const widgetId = widget.dataset.widgetId;
        const widgetSlug = widget.dataset.widgetSlug;
        
        try {
            // Check if widget has content-type associations
            const response = await fetch(`/admin/widgets/${widgetId}/content-options`);
            const data = await response.json();
            
            if (data.success && data.content_items && data.content_items.length > 0) {
                // Widget has content-types - setup drill-down dragging
                this.setupWidgetWithContentTypes(widget, data.content_items);
            } else {
                // Widget has no content-types - setup direct dragging with default content creation
                this.setupWidgetWithoutContentTypes(widget, widgetSlug);
            }
        } catch (error) {
            console.warn('Error checking widget content options:', error);
            // Fallback to direct dragging
            this.setupWidgetWithoutContentTypes(widget, widgetSlug);
        }
    }
    
    // NEW: Setup widget that has existing content-types (drill-down approach)
    setupWidgetWithContentTypes(widget, contentItems) {
        // Add drill-down indicator
        const drillDownIndicator = document.createElement('div');
        drillDownIndicator.className = 'drill-down-indicator';
        drillDownIndicator.innerHTML = '<i class="fas fa-chevron-right"></i>';
        widget.appendChild(drillDownIndicator);
        
        // Make widget clickable for drill-down (not directly draggable)
        widget.classList.add('has-content-types');
        widget.addEventListener('click', (e) => this.handleWidgetDrillDown(e, widget));
        
        console.log(`✅ Widget ${widget.dataset.widgetSlug} setup with ${contentItems.length} content items`);
    }
    
    // NEW: Setup widget without content-types (direct drag with auto-content creation)
    setupWidgetWithoutContentTypes(widget, widgetSlug) {
        // Make widget directly draggable
        widget.setAttribute('draggable', 'true');
        widget.classList.add('draggable-widget', 'auto-content-widget');
        
        // Add visual indicator for auto-content creation
        const autoContentIndicator = document.createElement('div');
        autoContentIndicator.className = 'auto-content-indicator';
        autoContentIndicator.innerHTML = '<i class="fas fa-magic"></i>';
        autoContentIndicator.title = 'Will create default content when dropped';
        widget.appendChild(autoContentIndicator);
        
        // Add drag event listeners
        widget.addEventListener('dragstart', (e) => this.handleWidgetDragStart(e));
        widget.addEventListener('dragend', (e) => this.handleWidgetDragEnd(e));
        
        console.log(`✅ Widget ${widgetSlug} setup for direct drag with auto-content creation`);
    }
    
    // NEW: Handle widget drag start (for widgets without content-types)
    handleWidgetDragStart(e) {
        const widget = e.currentTarget;
        const widgetId = widget.dataset.widgetId;
        const widgetSlug = widget.dataset.widgetSlug;
        const widgetName = widget.querySelector('.widget-name')?.textContent || widgetSlug;
        
        const dragData = {
            type: 'widget',
            widgetId: widgetId,
            widgetSlug: widgetSlug,
            widgetName: widgetName,
            source: 'sidebar',
            requiresDefaultContent: true // Flag indicating this widget needs auto-content creation
        };
        
        // Store drag data for iframe communication
        e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'copy';
        
        // Add visual feedback
        widget.classList.add('dragging');
        
        // Notify iframe about drag start
        if (this.livePreview) {
            this.livePreview.sendMessageToIframe('sidebar-drag-start', {
                dragType: 'widget',
                dragData: dragData
            });
        }
        
        console.log('🎯 Widget drag started:', dragData);
    }
    
    // NEW: Handle widget drag end
    handleWidgetDragEnd(e) {
        const widget = e.currentTarget;
        
        // Remove visual feedback
        widget.classList.remove('dragging');
        
        // Notify iframe about drag end
        if (this.livePreview) {
            this.livePreview.sendMessageToIframe('sidebar-drag-end', {});
        }
        
        console.log('🎯 Widget drag ended');
    }
    
    // NEW: Handle section template drag start
    handleSectionDragStart(e) {
        const template = e.currentTarget;
        const templateKey = template.dataset.templateKey;
        const templateType = template.dataset.templateType;
        const templateName = template.querySelector('.template-name')?.textContent || 'Section';
        
        const dragData = {
            type: 'section-template',
            templateKey: templateKey,
            templateType: templateType,
            templateName: templateName,
            source: 'sidebar'
        };
        
        // Store drag data for iframe communication
        e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'copy';
        
        // Add visual feedback
        template.classList.add('dragging');
        
        // Notify iframe about drag start
        if (this.livePreview) {
            this.livePreview.sendMessageToIframe('sidebar-drag-start', {
                dragType: 'section-template',
                dragData: dragData
            });
        }
        
        console.log('🎯 Section template drag started:', dragData);
    }
    
    // NEW: Handle section template drag end
    handleSectionDragEnd(e) {
        const template = e.currentTarget;
        
        // Remove visual feedback
        template.classList.remove('dragging');
        
        // Notify iframe about drag end
        if (this.livePreview) {
            this.livePreview.sendMessageToIframe('sidebar-drag-end', {});
        }
        
        console.log('🎯 Section template drag ended');
    }
}
```

**Key Methods Added**:
- `initializeDragSupport()` - Setup drag functionality with LivePreview integration
- `initializeSectionTemplateDrag()` - Make section templates draggable with SortableJS properties
- `handleSectionDragStart()` - Handle drag start with data preparation and iframe notification
- `handleSectionDragEnd()` - Handle drag end with cleanup and iframe notification

### 2. Drag Source Adapters

#### WidgetCategoryDragAdapter
```javascript
class WidgetCategoryDragAdapter extends BaseDragAdapter {
    getDragData() {
        return {
            type: 'widget-category',
            categoryId: this.element.dataset.categoryId,
            categoryName: this.element.dataset.categoryName,
            widgets: this.getWidgetsInCategory()
        };
    }
}
```

#### WidgetItemDragAdapter
```javascript
class WidgetItemDragAdapter extends BaseDragAdapter {
    getDragData() {
        return {
            type: 'widget',
            widgetId: this.element.dataset.widgetId,
            widgetType: this.element.dataset.widgetType,
            categoryId: this.element.dataset.categoryId,
            settings: this.getDefaultSettings()
        };
    }
}
```

#### ContentTypeDragAdapter
```javascript
class ContentTypeDragAdapter extends BaseDragAdapter {
    getDragData() {
        return {
            type: 'content-type',
            contentTypeId: this.element.dataset.contentTypeId,
            contentTypeName: this.element.dataset.contentTypeName,
            widgetContext: this.getDrillDownContext()
        };
    }
}
```

#### ContentItemDragAdapter
```javascript
class ContentItemDragAdapter extends BaseDragAdapter {
    getDragData() {
        return {
            type: 'content-item',
            contentItemId: this.element.dataset.contentItemId,
            contentTypeId: this.element.dataset.contentTypeId,
            widgetContext: this.getDrillDownContext(),
            previewData: this.getContentPreview()
        };
    }
}
```

### 3. Accordion State Manager

**File**: `public/assets/admin/js/live-designer/accordion-state-manager.js`

```javascript
class AccordionStateManager {
    constructor() {
        this.states = new Map();
        this.observers = [];
    }
    
    saveState(accordionId) {
        const accordion = document.getElementById(accordionId);
        const expandedItems = [];
        
        accordion.querySelectorAll('.accordion-collapse.show').forEach(item => {
            expandedItems.push(item.id);
        });
        
        this.states.set(accordionId, {
            expanded: expandedItems,
            timestamp: Date.now()
        });
    }
    
    restoreState(accordionId) {
        const state = this.states.get(accordionId);
        if (!state) return;
        
        const accordion = document.getElementById(accordionId);
        
        // Collapse all items first
        accordion.querySelectorAll('.accordion-collapse').forEach(item => {
            item.classList.remove('show');
        });
        
        // Expand previously expanded items
        state.expanded.forEach(itemId => {
            const item = document.getElementById(itemId);
            if (item) {
                item.classList.add('show');
            }
        });
    }
}
```

### 4. Drill-Down Integration

#### Enhanced Widget Drill-Down Integration
Modify existing `widget-drill-down.js` to support drag operations:

```javascript
// Add to WidgetDrillDown class
initializeDragSupport() {
    this.dragManager = new LiveDesignerSidebarDrag({
        drillDownIntegration: true,
        onDragStart: (dragData) => this.handleDragStart(dragData),
        onDragEnd: (dragData) => this.handleDragEnd(dragData)
    });
}

handleDragStart(dragData) {
    // Save current drill-down state
    this.savedState = {
        currentView: this.currentView,
        currentWidget: this.currentWidget,
        currentContentType: this.currentContentType,
        breadcrumbState: this.getBreadcrumbState()
    };
}

handleDragEnd(dragData) {
    // Restore drill-down state if needed
    if (this.savedState && dragData.restoreNavigation) {
        this.restoreNavigationState(this.savedState);
    }
}
```

## Widget Drop Handling (Iframe Responsibility)

When a widget is dropped into the iframe, the **iframe implementation** (preview-helpers.js) handles:

### For Widgets WITH Content-Types (Existing Logic)
```javascript
// Iframe receives: { type: 'widget', widgetId: 123, contentItemId: 456 }
// Creates PageSectionWidget with existing content_query
```

### For Widgets WITHOUT Content-Types (New Auto-Content Logic)
```javascript
// Iframe receives: { type: 'widget', widgetId: 123, requiresDefaultContent: true }
// 1. Call API to create default content-type and content-item
// 2. Create PageSectionWidget with new content_query pointing to auto-created content
// 3. Refresh iframe to show widget with realistic default content
```

### API Endpoint for Auto-Content Creation
**Location**: `LivePreviewController` (existing controller)
**Endpoint**: `POST /admin/live-preview/widgets/{widget}/create-with-default-content`

**Logic**:
1. Check if widget has contentTypeAssociations
2. If none, create default ContentType with fields matching widget field definitions
3. Create default ContentItem with realistic sample data
4. Create contentTypeAssociation linking widget to new content-type
5. Return content_query data for PageSectionWidget creation

## Implementation Steps

### Phase 1: Section Template Drag Setup (SIMPLIFIED)
1. **Extend sidebar-manager.js** with drag functionality (NO new files)
2. **Add section template drag support** using native HTML5 drag API
3. **Integrate with LivePreview** for iframe communication
4. **Update left-sidebar.blade.php** with draggable attributes (minimal changes)

### Phase 2: Widget Drag Implementation with Auto-Content Detection
1. **Implement Widget Content-Type Detection** - Check `/admin/widgets/{id}/content-options` endpoint
2. **Setup Dual Widget Behavior**:
   - Widgets WITH content-types → Drill-down navigation (existing logic)
   - Widgets WITHOUT content-types → Direct drag with auto-content creation
3. **Add Visual Indicators**:
   - Drill-down arrow for widgets with content-types
   - Magic wand icon for widgets requiring auto-content creation
4. **Implement Widget Drag Handlers** - `handleWidgetDragStart()` and `handleWidgetDragEnd()`

### Phase 3: Drill-Down Integration
1. **Enhance widget-drill-down.js** with drag support methods
2. **Implement Content Type Drag** - Drill-down content type cards
3. **Implement Content Item Drag** - Drill-down content item cards
4. **Add state preservation** for drill-down navigation during drag

### Phase 4: Advanced Features
1. **Implement drag feedback** - Visual indicators and hover states
2. **Add drag constraints** - Valid drop zones and restrictions
3. **Implement drag cancellation** - ESC key and invalid drop handling
4. **Add accessibility support** - ARIA labels and keyboard navigation

## File Structure (SIMPLIFIED)

```
public/assets/admin/js/live-designer/
├── sidebar-manager.js              # EXTENDED with drag functionality (existing file)
├── widget-drill-down.js            # Enhanced with drag support (existing file)
└── live-preview.js                 # Enhanced with iframe drag communication (existing file)
```

**NO NEW FILES CREATED** - All functionality added to existing files

## Integration with Existing Systems

### Widget Drill-Down System
- **Preserve Navigation State**: Maintain drill-down context during drag operations
- **Restore After Drag**: Return to previous navigation state after drag completion
- **Context Awareness**: Include drill-down context in drag data

### Unified Loader System
- **Loading States**: Use unified loader for drag operation feedback
- **Error Handling**: Integrate with existing error handling patterns
- **Success Feedback**: Consistent success indicators

### Velzon Theme Integration
- **Drag Handles**: Use theme-consistent drag handle styling
- **Preview Elements**: Create drag previews using Velzon card components
- **Hover States**: Apply theme hover and active states

## Data Flow

### Drag Initiation
1. User initiates drag on sidebar element
2. Appropriate drag adapter creates drag data
3. Accordion state is saved
4. Drill-down state is preserved
5. Drag preview is created
6. Core drag service is notified

### During Drag
1. Visual feedback is provided
2. Valid drop zones are highlighted
3. Accordion state is maintained
4. Preview updates in real-time

### Drag Completion
1. Drop zone receives drag data
2. API calls are made to update backend
3. Live preview is updated
4. Accordion state is restored
5. Drill-down navigation is preserved
6. Success feedback is shown

## Error Handling

### Drag Failures
- **Network Errors**: Graceful fallback with retry options
- **Invalid Drops**: Clear feedback and state restoration
- **Timeout Handling**: Cancel drag operations after timeout

### State Recovery
- **Accordion Recovery**: Restore accordion state on errors
- **Navigation Recovery**: Return to previous drill-down state
- **UI Recovery**: Reset drag indicators and hover states

## Testing Strategy

### Unit Tests
- Drag adapter functionality
- State management operations
- Integration with core drag service

### Integration Tests
- Accordion state preservation
- Drill-down navigation integration
- Cross-component communication

### User Experience Tests
- Drag responsiveness
- Visual feedback quality
- Error recovery scenarios

## Performance Considerations

### Optimization Strategies
- **Lazy Loading**: Load drag adapters only when needed
- **Event Delegation**: Use efficient event handling
- **State Caching**: Cache accordion and drill-down states
- **Preview Optimization**: Efficient drag preview generation

### Memory Management
- **Cleanup on Destroy**: Proper event listener removal
- **State Cleanup**: Clear cached states when appropriate
- **Observer Cleanup**: Remove state observers on component destruction

## Security Considerations

### Data Validation
- **Drag Data Sanitization**: Validate all drag data before processing
- **CSRF Protection**: Include CSRF tokens in drag-related API calls
- **Permission Checks**: Verify user permissions for drag operations

### XSS Prevention
- **Template Sanitization**: Sanitize all dynamic content in drag previews
- **Data Attribute Validation**: Validate data attributes used for drag operations

## Browser Compatibility

### Supported Browsers
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Fallback Strategies
- **Touch Device Support**: Handle touch events for mobile devices
- **Keyboard Navigation**: Provide keyboard alternatives for drag operations
- **Reduced Motion**: Respect user preferences for reduced motion

## Future Enhancements

### Potential Improvements
- **Multi-Select Drag**: Support dragging multiple items simultaneously
- **Drag Grouping**: Group related items during drag operations
- **Advanced Previews**: Enhanced drag preview with live content rendering
- **Drag History**: Track and allow undo of drag operations

### Integration Opportunities
- **Page Builder Integration**: Extend drag system to page builder
- **Content Editor Integration**: Drag from sidebar to content editors
- **Media Library Integration**: Drag media items from library

## Dependencies

### Required Libraries
- SortableJS (from Core Drag Implementation Plan)
- Existing widget-drill-down.js
- Unified loader system
- Velzon theme components

### API Dependencies
- Widget management endpoints
- Content type and item endpoints
- Page section update endpoints
- Preview generation endpoints

## Conclusion

This implementation plan provides a comprehensive approach to adding drag-and-drop functionality to the live-designer's left sidebar while maintaining integration with existing systems and preserving the user experience. The modular design allows for incremental implementation and testing, ensuring stability throughout the development process.
