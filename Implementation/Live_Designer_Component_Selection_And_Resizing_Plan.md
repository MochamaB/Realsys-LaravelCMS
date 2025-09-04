# Live-Designer Component Selection and Resizing Plan

## Overview
This plan addresses the missing components from the unified drag system: component selection mechanics, resizing functionality, and detailed SortableJS integration. It explains how users interact with sections and widgets in the iframe, how selection triggers different interaction modes, and how resizing works alongside drag-and-drop.

## Problem Statement

### Current Gaps
1. **No Resizing System**: SortableJS only handles position/order, not size/styling
2. **Missing Selection Mechanics**: No explanation of how users select components
3. **Unclear SortableJS Integration**: How it works cross-frame with iframe content
4. **No CSS Manipulation**: How to change padding, margins, dimensions in real-time

### Requirements
- **Visual Selection System**: Click to select sections/widgets with visual feedback
- **Dual Interaction Modes**: Drag for positioning, resize for dimensions/styling
- **Real-Time CSS Updates**: Live preview of style changes
- **Cross-Frame Coordination**: Selection and resize events between parent and iframe
- **Responsive Handling**: Different settings per breakpoint

## Architecture Overview

### Component Interaction States
1. **Default State**: Normal preview mode, no interaction
2. **Selection Mode**: Component selected, showing resize handles and style options
3. **Drag Mode**: Component being repositioned (SortableJS)
4. **Resize Mode**: Component being resized (custom resize system)
5. **Style Edit Mode**: CSS properties being modified

### Integration Approach
- **SortableJS**: Handles drag/drop positioning only
- **Custom Resize System**: Handles size and CSS property changes
- **Selection Manager**: Coordinates between modes and cross-frame communication
- **Style Manager**: Real-time CSS manipulation and persistence

## Implementation Components

### 1. Component Selection System

#### IframeSelectionManager
**File**: `public/assets/admin/js/live-designer/iframe-selection-manager.js`

```javascript
class IframeSelectionManager {
    constructor(iframeDocument, parentWindow) {
        this.document = iframeDocument;
        this.parentWindow = parentWindow;
        this.selectedElement = null;
        this.selectionOverlay = null;
        this.resizeHandles = [];
        this.interactionMode = 'default'; // default, drag, resize, style-edit
        
        this.init();
    }
    
    init() {
        this.createSelectionOverlay();
        this.bindSelectionEvents();
        this.setupCrossFrameComm();
    }
    
    bindSelectionEvents() {
        // Click to select components
        this.document.addEventListener('click', (e) => {
            const component = this.findSelectableComponent(e.target);
            if (component) {
                e.preventDefault();
                e.stopPropagation();
                this.selectComponent(component);
            }
        });
        
        // Escape to deselect
        this.document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.deselectComponent();
            }
        });
        
        // Prevent default drag on selected elements
        this.document.addEventListener('dragstart', (e) => {
            if (this.isComponentSelected(e.target)) {
                e.preventDefault();
            }
        });
    }
    
    findSelectableComponent(element) {
        // Find nearest section or widget
        return element.closest('[data-section-id], [data-widget-id]');
    }
    
    selectComponent(element) {
        this.deselectComponent(); // Clear previous selection
        
        this.selectedElement = element;
        this.showSelectionOverlay(element);
        this.createResizeHandles(element);
        this.notifyParentOfSelection(element);
        
        // Add selected class for styling
        element.classList.add('live-designer-selected');
    }
    
    showSelectionOverlay(element) {
        const rect = element.getBoundingClientRect();
        
        this.selectionOverlay.style.display = 'block';
        this.selectionOverlay.style.left = rect.left + 'px';
        this.selectionOverlay.style.top = rect.top + 'px';
        this.selectionOverlay.style.width = rect.width + 'px';
        this.selectionOverlay.style.height = rect.height + 'px';
    }
    
    createResizeHandles(element) {
        const rect = element.getBoundingClientRect();
        const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
        
        handles.forEach(position => {
            const handle = this.createResizeHandle(position, rect);
            this.resizeHandles.push(handle);
            this.document.body.appendChild(handle);
        });
    }
}
```

#### Selection Overlay Styling
**File**: `public/assets/admin/js/live-designer/iframe-styles/selection-overlay.css`

```css
.live-designer-selection-overlay {
    position: absolute;
    border: 2px solid #007bff;
    background: rgba(0, 123, 255, 0.1);
    pointer-events: none;
    z-index: 9998;
    display: none;
}

.live-designer-selected {
    outline: 2px solid #007bff !important;
    outline-offset: 2px;
}

.live-designer-resize-handle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: #007bff;
    border: 1px solid #fff;
    cursor: pointer;
    z-index: 9999;
}

.live-designer-resize-handle.nw { cursor: nw-resize; }
.live-designer-resize-handle.n { cursor: n-resize; }
.live-designer-resize-handle.ne { cursor: ne-resize; }
.live-designer-resize-handle.e { cursor: e-resize; }
.live-designer-resize-handle.se { cursor: se-resize; }
.live-designer-resize-handle.s { cursor: s-resize; }
.live-designer-resize-handle.sw { cursor: sw-resize; }
.live-designer-resize-handle.w { cursor: w-resize; }
```

### 2. Resize System (Custom Implementation)

#### ComponentResizer
**File**: `public/assets/admin/js/live-designer/component-resizer.js`

```javascript
class ComponentResizer {
    constructor(selectionManager, styleManager) {
        this.selectionManager = selectionManager;
        this.styleManager = styleManager;
        this.isResizing = false;
        this.resizeData = null;
        this.originalStyles = null;
    }
    
    initializeResize(handle, element) {
        this.isResizing = true;
        this.resizeData = {
            handle: handle.dataset.position,
            element: element,
            startRect: element.getBoundingClientRect(),
            startMouse: { x: event.clientX, y: event.clientY }
        };
        
        // Store original styles for restoration
        this.originalStyles = {
            width: element.style.width,
            height: element.style.height,
            padding: element.style.padding,
            margin: element.style.margin
        };
        
        this.bindResizeEvents();
        this.notifyParentResizeStart();
    }
    
    handleResize(event) {
        if (!this.isResizing || !this.resizeData) return;
        
        const deltaX = event.clientX - this.resizeData.startMouse.x;
        const deltaY = event.clientY - this.resizeData.startMouse.y;
        
        const newDimensions = this.calculateNewDimensions(deltaX, deltaY);
        this.applyResize(newDimensions);
        this.updateSelectionOverlay();
    }
    
    calculateNewDimensions(deltaX, deltaY) {
        const { handle, startRect } = this.resizeData;
        let newWidth = startRect.width;
        let newHeight = startRect.height;
        
        switch (handle) {
            case 'se': // Southeast - most common
                newWidth = startRect.width + deltaX;
                newHeight = startRect.height + deltaY;
                break;
            case 'e': // East - width only
                newWidth = startRect.width + deltaX;
                break;
            case 's': // South - height only
                newHeight = startRect.height + deltaY;
                break;
            // Add other handle calculations...
        }
        
        // Apply constraints
        newWidth = Math.max(50, newWidth); // Minimum width
        newHeight = Math.max(30, newHeight); // Minimum height
        
        return { width: newWidth, height: newHeight };
    }
    
    applyResize(dimensions) {
        const element = this.resizeData.element;
        
        // Apply new dimensions
        element.style.width = dimensions.width + 'px';
        element.style.height = dimensions.height + 'px';
        
        // Trigger style manager update
        this.styleManager.updateElementStyles(element, {
            width: dimensions.width + 'px',
            height: dimensions.height + 'px'
        });
    }
}
```

### 3. Style Management System

#### LiveStyleManager
**File**: `public/assets/admin/js/live-designer/live-style-manager.js`

```javascript
class LiveStyleManager {
    constructor(iframeDocument, apiClient) {
        this.document = iframeDocument;
        this.apiClient = apiClient;
        this.styleCache = new Map();
        this.pendingUpdates = new Map();
        this.updateDebounceTimer = null;
    }
    
    updateElementStyles(element, styles) {
        const elementId = this.getElementId(element);
        
        // Apply styles immediately for visual feedback
        Object.assign(element.style, styles);
        
        // Cache for API update
        if (!this.styleCache.has(elementId)) {
            this.styleCache.set(elementId, {});
        }
        
        Object.assign(this.styleCache.get(elementId), styles);
        
        // Debounced API update
        this.scheduleStyleUpdate(elementId);
    }
    
    scheduleStyleUpdate(elementId) {
        this.pendingUpdates.set(elementId, true);
        
        clearTimeout(this.updateDebounceTimer);
        this.updateDebounceTimer = setTimeout(() => {
            this.flushStyleUpdates();
        }, 500); // 500ms debounce
    }
    
    async flushStyleUpdates() {
        const updates = [];
        
        for (const [elementId, _] of this.pendingUpdates) {
            const styles = this.styleCache.get(elementId);
            const element = this.document.querySelector(`[data-element-id="${elementId}"]`);
            
            if (element && styles) {
                updates.push({
                    element_type: this.getElementType(element),
                    element_id: this.getElementDataId(element),
                    styles: styles
                });
            }
        }
        
        if (updates.length > 0) {
            try {
                await this.apiClient.updateStyles(updates);
                this.pendingUpdates.clear();
            } catch (error) {
                console.error('Failed to update styles:', error);
                // Could implement retry logic here
            }
        }
    }
    
    getElementType(element) {
        if (element.dataset.sectionId) return 'section';
        if (element.dataset.widgetId) return 'widget';
        return 'unknown';
    }
}
```

### 4. SortableJS Integration Details

#### Hybrid Drag System
**File**: `public/assets/admin/js/live-designer/hybrid-drag-system.js`

```javascript
class HybridDragSystem {
    constructor(iframeDocument, selectionManager, resizer) {
        this.document = iframeDocument;
        this.selectionManager = selectionManager;
        this.resizer = resizer;
        this.sortableInstances = new Map();
        this.dragMode = null; // 'position' or 'resize'
    }
    
    initializeSortable() {
        // Initialize SortableJS on each section for widget reordering
        const sections = this.document.querySelectorAll('[data-section-id]');
        
        sections.forEach(section => {
            const sortable = new Sortable(section, {
                group: 'widgets',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                
                // Only allow drag if not in resize mode
                onChoose: (evt) => {
                    if (this.resizer.isResizing) {
                        evt.preventDefault();
                        return false;
                    }
                    this.dragMode = 'position';
                },
                
                onStart: (evt) => {
                    this.handleDragStart(evt);
                },
                
                onEnd: (evt) => {
                    this.handleDragEnd(evt);
                    this.dragMode = null;
                },
                
                // Custom filter to exclude resize handles
                filter: '.live-designer-resize-handle, .live-designer-selection-overlay',
                
                // Prevent dragging when resize handles are visible
                onMove: (evt) => {
                    if (this.selectionManager.selectedElement === evt.dragged) {
                        return false; // Prevent drag of selected element
                    }
                }
            });
            
            this.sortableInstances.set(section.dataset.sectionId, sortable);
        });
    }
    
    handleDragStart(evt) {
        // Hide selection overlay during drag
        this.selectionManager.hideSelectionOverlay();
        
        // Notify parent window
        this.notifyParent('drag-start', {
            elementType: this.getElementType(evt.item),
            elementId: this.getElementId(evt.item),
            fromSection: evt.from.dataset.sectionId
        });
    }
    
    handleDragEnd(evt) {
        // Update positions via API
        this.updateElementPosition(evt);
        
        // Restore selection if same element
        if (this.selectionManager.selectedElement === evt.item) {
            this.selectionManager.showSelectionOverlay(evt.item);
        }
        
        // Notify parent window
        this.notifyParent('drag-end', {
            elementType: this.getElementType(evt.item),
            elementId: this.getElementId(evt.item),
            toSection: evt.to.dataset.sectionId,
            newIndex: evt.newIndex
        });
    }
    
    // Disable SortableJS when in resize mode
    disableDrag() {
        this.sortableInstances.forEach(sortable => {
            sortable.option('disabled', true);
        });
    }
    
    enableDrag() {
        this.sortableInstances.forEach(sortable => {
            sortable.option('disabled', false);
        });
    }
}
```

### 5. Cross-Frame Coordination

#### InteractionModeManager
**File**: `public/assets/admin/js/live-designer/interaction-mode-manager.js`

```javascript
class InteractionModeManager {
    constructor(parentWindow, iframeWindow) {
        this.parentWindow = parentWindow;
        this.iframeWindow = iframeWindow;
        this.currentMode = 'default';
        this.modeStack = [];
    }
    
    switchMode(newMode, context = {}) {
        const previousMode = this.currentMode;
        this.modeStack.push(previousMode);
        this.currentMode = newMode;
        
        // Notify both parent and iframe of mode change
        this.notifyModeChange(newMode, previousMode, context);
        
        // Apply mode-specific behaviors
        this.applyModeSettings(newMode);
    }
    
    applyModeSettings(mode) {
        switch (mode) {
            case 'selection':
                this.enableSelection();
                this.disableDrag();
                break;
                
            case 'drag':
                this.disableSelection();
                this.enableDrag();
                this.disableResize();
                break;
                
            case 'resize':
                this.disableSelection();
                this.disableDrag();
                this.enableResize();
                break;
                
            case 'style-edit':
                this.enableSelection();
                this.disableDrag();
                this.disableResize();
                break;
                
            default: // 'default'
                this.enableSelection();
                this.enableDrag();
                this.disableResize();
                break;
        }
    }
    
    notifyModeChange(newMode, previousMode, context) {
        const message = {
            type: 'interaction-mode-change',
            data: {
                newMode,
                previousMode,
                context,
                timestamp: Date.now()
            }
        };
        
        // Notify parent window
        this.parentWindow.postMessage(message, '*');
        
        // Notify iframe if we're in parent
        if (this.iframeWindow) {
            this.iframeWindow.postMessage(message, '*');
        }
    }
}
```

## Integration with Preview-Helpers

### Enhanced Preview Integration
The existing preview-helpers system needs enhancement to support the new interaction modes:

```javascript
// Enhancement to existing preview-helpers
class EnhancedPreviewHelpers {
    constructor(originalHelpers) {
        this.original = originalHelpers;
        this.interactionMode = 'default';
        this.selectionManager = null;
        this.resizer = null;
    }
    
    initializeInteractiveMode() {
        // Initialize selection and resize systems
        this.selectionManager = new IframeSelectionManager(document, window.parent);
        this.resizer = new ComponentResizer(this.selectionManager, this.styleManager);
        this.hybridDrag = new HybridDragSystem(document, this.selectionManager, this.resizer);
        
        // Initialize SortableJS for positioning
        this.hybridDrag.initializeSortable();
        
        // Set up mode coordination
        this.modeManager = new InteractionModeManager(window.parent, null);
    }
    
    // Override original refresh method to preserve interactions
    refreshPreview() {
        const selectedElement = this.selectionManager?.selectedElement;
        const selectedId = selectedElement?.dataset.elementId;
        
        // Call original refresh
        this.original.refreshPreview();
        
        // Restore selection after refresh
        if (selectedId) {
            setTimeout(() => {
                const newElement = document.querySelector(`[data-element-id="${selectedId}"]`);
                if (newElement) {
                    this.selectionManager.selectComponent(newElement);
                }
            }, 100);
        }
    }
}
```

## API Endpoints for Style Updates

### Required Backend Endpoints

```php
// Add to existing admin routes
Route::patch('/admin/pages/{page}/sections/{section}/styles', [LiveDesignerController::class, 'updateSectionStyles']);
Route::patch('/admin/pages/{page}/widgets/{widget}/styles', [LiveDesignerController::class, 'updateWidgetStyles']);
Route::post('/admin/pages/{page}/styles/batch', [LiveDesignerController::class, 'batchUpdateStyles']);
```

### Style Update API Format

```javascript
// Batch style update request
{
    updates: [
        {
            element_type: 'section',
            element_id: 'section-123',
            styles: {
                'padding-top': '20px',
                'padding-bottom': '20px',
                'background-color': '#f8f9fa'
            }
        },
        {
            element_type: 'widget',
            element_id: 'widget-456',
            styles: {
                'width': '300px',
                'height': '200px',
                'margin': '10px'
            }
        }
    ]
}
```

## Implementation Phases

### Phase 1: Selection System (Week 1)
1. Create IframeSelectionManager with click-to-select
2. Implement selection overlay and visual feedback
3. Add cross-frame selection communication
4. Test selection in iframe environment

### Phase 2: Resize System (Week 2)
1. Implement ComponentResizer with resize handles
2. Create LiveStyleManager for CSS manipulation
3. Add real-time style updates and API integration
4. Test resize operations and style persistence

### Phase 3: SortableJS Integration (Week 3)
1. Implement HybridDragSystem combining SortableJS + custom resize
2. Add interaction mode management
3. Coordinate between drag and resize modes
4. Test drag positioning alongside resize functionality

### Phase 4: Enhanced Preview Integration (Week 4)
1. Enhance existing preview-helpers with interactive capabilities
2. Add mode switching and state preservation
3. Implement comprehensive error handling and recovery
4. Complete end-to-end testing

## Conclusion

This plan addresses the critical missing components:

1. **Component Selection**: Click-to-select with visual feedback and cross-frame coordination
2. **Resizing System**: Custom resize implementation with real-time CSS updates
3. **SortableJS Integration**: Hybrid approach using SortableJS for positioning, custom system for resizing
4. **Interaction Modes**: Coordinated modes for selection, drag, resize, and style editing
5. **Preview Integration**: Enhanced preview-helpers supporting interactive operations

The system provides a complete interaction framework where users can:
- **Click to select** sections/widgets
- **Drag to reposition** using SortableJS
- **Resize to adjust dimensions** using custom resize handles
- **Edit styles** with real-time preview updates
- **Switch between modes** seamlessly

This creates a comprehensive visual editing experience within the live-designer iframe.
