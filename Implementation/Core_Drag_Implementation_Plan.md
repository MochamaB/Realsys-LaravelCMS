# Core Drag Implementation Plan
**Shared Foundation for Page-Builder & Live-Designer**

**Date**: 2025-01-09  
**Purpose**: Define core drag system components shared by both page-builder and live-designer systems

---

## Executive Summary

This plan establishes the foundational drag system components that will be shared between page-builder and live-designer. The core system provides position management, API integration, and basic drag functionality that both systems can extend.

---

## Core Architecture

### **Shared Components**
- **DragService**: Position calculations and API communication
- **MessageBus**: Cross-frame communication protocol
- **DragStateManager**: Drag operation state tracking
- **PositionCalculator**: Mathematical position calculations
- **APIClient**: Unified API interface for drag operations

### **System-Specific Extensions**
- **Page-Builder**: Extends core for GridStack replacement
- **Live-Designer**: Extends core for real-time preview integration

---

## Core Files Structure

```
public/assets/admin/js/shared/drag-system/
├── core/
│   ├── drag-service.js          # Base drag functionality
│   ├── message-bus.js           # Cross-frame communication
│   ├── drag-state-manager.js    # State tracking
│   ├── position-calculator.js   # Position math
│   └── api-client.js            # API integration
├── adapters/
│   ├── base-sidebar-adapter.js  # Base sidebar drag logic
│   └── base-iframe-adapter.js   # Base iframe drag logic
└── utils/
    ├── drag-utils.js            # Utility functions
    └── constants.js             # Shared constants
```

---

## Implementation Details

### **Phase 1: Core Foundation (Week 1)**

#### **Step 1.1: DragService Base Class**
**File**: `drag-service.js`
**Priority**: CRITICAL

```javascript
/**
 * CORE DRAG SERVICE
 * Base class for all drag operations - shared by both systems
 */
class DragService {
    constructor(apiClient, options = {}) {
        this.apiClient = apiClient;
        this.options = {
            containerId: null,
            dragHandle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            animation: 150,
            ...options
        };
        
        this.container = null;
        this.sortable = null;
        this.dragStateManager = new DragStateManager();
        this.positionCalculator = new PositionCalculator();
        this.messageBus = new MessageBus();
        this.interactionMode = 'default'; // default, drag, resize, style-edit
        
        console.log('🎯 Core DragService initialized');
    }
    
    // CORE: Initialize drag system
    async initialize() {
        if (this.options.containerId) {
            this.container = document.getElementById(this.options.containerId);
            if (!this.container) {
                throw new Error(`Container not found: ${this.options.containerId}`);
            }
            
            await this.setupSortable();
        }
        
        // Initialize cross-frame communication if in iframe
        if (window.parent !== window) {
            this.initializeCrossFrameComm();
        }
        
        console.log('✅ DragService initialized successfully');
    }
    
    // CORE: SortableJS Integration - How we actually use SortableJS
    async setupSortable() {
        if (!this.container) return;
        
        // Import SortableJS dynamically
        const { default: Sortable } = await import('/assets/admin/js/sortable.min.js');
        
        this.sortable = new Sortable(this.container, {
            ...this.options,
            
            // CRITICAL: Only handle positioning, not resizing
            onChoose: (evt) => this.handleDragStart(evt),
            onStart: (evt) => this.handleDragBegin(evt),
            onMove: (evt) => this.handleDragMove(evt),
            onEnd: (evt) => this.handleDragEnd(evt),
            
            // Filter out resize handles and selection overlays
            filter: '.live-designer-resize-handle, .live-designer-selection-overlay, .no-drag',
            
            // Prevent dragging when in resize mode
            onMove: (evt) => {
                if (this.interactionMode === 'resize' || this.interactionMode === 'style-edit') {
                    return false; // Block drag during resize/style operations
                }
                return this.validateDragMove(evt);
            }
        });
        
        console.log('🎯 SortableJS initialized for container:', this.container.id);
    }
    
    // CORE: Cross-frame communication setup
    initializeCrossFrameComm() {
        window.addEventListener('message', (event) => {
            if (event.source !== window.parent) return;
            
            const { type, data } = event.data;
            if (type?.startsWith('live-designer-drag')) {
                this.handleCrossFrameMessage(type, data);
            }
        });
    }
    
    // CORE: Handle drag end (SortableJS onEnd)
    async handleDragEnd(evt) {
        try {
            // Remove drag styling
            evt.item.classList.remove('dragging');
            
            // Process the drag result
            const result = await this.processDragEnd(evt);
            this.dragStateManager.endDrag(result);
            
            // Restore selection indicators if needed
            this.restoreSelectionIndicators(evt.item);
            
            // Switch back to default mode
            this.setInteractionMode('default');
            
            // Notify other systems
            this.messageBus.publish('drag:end', {
                result,
                element: evt.item,
                targetContainer: evt.to
            });
            
            // Cross-frame notification
            if (window.parent !== window) {
                window.parent.postMessage({
                    type: 'live-designer-drag-end',
                    data: {
                        success: result.success,
                        elementType: this.getElementType(evt.item),
                        elementId: this.getElementId(evt.item),
                        newPosition: result.newPosition
                    }
                }, '*');
            }
            
            console.log('✅ Drag completed:', result);
            return result;
        } catch (error) {
            console.error('❌ Drag operation failed:', error);
            this.handleDragError(error, evt);
            throw error;
        }
    }
    
    // CORE: Set interaction mode (coordinates with resize system)
    setInteractionMode(mode) {
        const previousMode = this.interactionMode;
        this.interactionMode = mode;
        
        // Apply mode-specific behaviors
        switch (mode) {
            case 'drag':
                this.enableDragMode();
                break;
            case 'resize':
                this.disableDragMode();
                break;
            case 'style-edit':
                this.disableDragMode();
                break;
            default: // 'default'
                this.enableDragMode();
                break;
        }
        
        // Notify mode change
        this.messageBus.publish('mode:change', {
            newMode: mode,
            previousMode: previousMode
        });
    }
    
    // CORE: Enable/disable drag functionality
    enableDragMode() {
        if (this.sortable) {
            this.sortable.option('disabled', false);
        }
    }
    
    disableDragMode() {
        if (this.sortable) {
            this.sortable.option('disabled', true);
        }
    }
    
    // CORE: Selection indicator management
    hideSelectionIndicators() {
        const overlays = document.querySelectorAll('.live-designer-selection-overlay');
        overlays.forEach(overlay => overlay.style.display = 'none');
    }
    
    restoreSelectionIndicators(element) {
        if (element.classList.contains('live-designer-selected')) {
            // Restore selection overlay for this element
            const overlay = document.querySelector('.live-designer-selection-overlay');
            if (overlay) {
                overlay.style.display = 'block';
                this.updateSelectionOverlay(element, overlay);
            }
        }
    }
    
    // CORE: Cross-frame message handling
    handleCrossFrameMessage(type, data) {
        switch (type) {
            case 'live-designer-drag-mode-change':
                this.setInteractionMode(data.mode);
                break;
            case 'live-designer-drag-disable':
                this.disableDragMode();
                break;
            case 'live-designer-drag-enable':
                this.enableDragMode();
                break;
        }
    }
    
    // CORE: Handle drag start (when user begins dragging)
    handleDragStart(evt) {
        // Switch to drag mode
        this.setInteractionMode('drag');
        
        const dragData = this.extractDragData(evt.item);
        this.dragStateManager.startDrag(dragData);
        
        // Notify other systems about drag start
        this.messageBus.publish('drag:start', {
            dragData,
            element: evt.item,
            sourceContainer: evt.from
        });
        
        console.log('🎯 Drag started:', dragData);
        return dragData;
    }
    
    // CORE: Handle actual drag begin (SortableJS onStart)
    handleDragBegin(evt) {
        // Hide any selection overlays during drag
        this.hideSelectionIndicators();
        
        // Add drag-specific styling
        evt.item.classList.add('dragging');
        
        // Cross-frame notification if in iframe
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'live-designer-drag-begin',
                data: {
                    elementType: this.getElementType(evt.item),
                    elementId: this.getElementId(evt.item)
                }
            }, '*');
        }
    }
    
    // CORE: Handle drag movement validation
    handleDragMove(evt) {
        // Validate if this drag operation is allowed
        const isValid = this.validateDragOperation(evt);
        
        if (!isValid) {
            return false; // Block the drag
        }
        
        // Update drag state
        this.dragStateManager.updateDragPosition({
            from: evt.from,
            to: evt.to,
            oldIndex: evt.oldIndex,
            newIndex: evt.newIndex
        });
        
        return true;
    }
    
    // CORE: Validate drag operations
    validateDragOperation(evt) {
        // Check if target container accepts this type of element
        const draggedType = this.getElementType(evt.dragged);
        const targetContainer = evt.to;
        
        // Example validations:
        if (draggedType === 'widget' && !targetContainer.dataset.sectionId) {
            return false; // Widgets can only be dropped in sections
        }
        
        if (targetContainer.classList.contains('no-drop')) {
            return false; // Respect no-drop zones
        }
        
        return true;
    }
    
    // CORE: Update drag position
    updatePosition(newIndex) {
        if (this.isDragging) {
            this.dragStateManager.updatePosition(newIndex);
        }
    }
    
    // CORE: Position update
    async updatePosition(itemId, oldIndex, newIndex) {
        const newPosition = this.positionCalculator.calculate(oldIndex, newIndex);
        
        const response = await this.apiClient.updatePosition(itemId, {
            position: newPosition,
            oldIndex: oldIndex,
            newIndex: newIndex
        });
        
        if (!response.success) {
            throw new Error(response.error || 'Position update failed');
        }
        
        return response;
    }
    
    // CORE: Revert drag operation on failure
    revertDragOperation(evt) {
        const { oldIndex, newIndex } = evt;
        const sections = Array.from(this.container.children);
        const movedSection = sections[newIndex];
        
        if (oldIndex < newIndex) {
            this.container.insertBefore(movedSection, sections[oldIndex]);
        } else {
            this.container.insertBefore(movedSection, sections[oldIndex + 1]);
        }
        
        console.log('🔄 Drag operation reverted');
    }
    
    // EXTENSION POINTS: Override in subclasses
    onDragStart(evt) { /* Override in subclass */ }
    onDragEnd(evt, success) { /* Override in subclass */ }
    handleDragMove(evt) { /* Override in subclass */ }
    
    // CORE: Cleanup
    destroy() {
        if (this.sortable) {
            this.sortable.destroy();
        }
        this.dragStateManager.reset();
    }
}
```

#### **Step 1.2: MessageBus for Cross-Frame Communication**
**File**: `message-bus.js`
**Priority**: CRITICAL

```javascript
/**
 * MESSAGE BUS
 * Handles cross-frame communication for drag operations
 */
class MessageBus {
    constructor() {
        this.listeners = new Map();
        this.setupMessageListener();
        
        console.log('📡 MessageBus initialized');
    }
    
    // CORE: Setup message listener
    setupMessageListener() {
        window.addEventListener('message', (event) => {
            this.handleMessage(event);
        });
    }
    
    // CORE: Handle incoming messages
    handleMessage(event) {
        const { type, data } = event.data;
        
        if (this.listeners.has(type)) {
            const callbacks = this.listeners.get(type);
            callbacks.forEach(callback => {
                try {
                    callback(data, event);
                } catch (error) {
                    console.error(`❌ Message handler error for ${type}:`, error);
                }
            });
        }
    }
    
    // CORE: Subscribe to message type
    subscribe(type, callback) {
        if (!this.listeners.has(type)) {
            this.listeners.set(type, []);
        }
        this.listeners.get(type).push(callback);
        
        console.log(`📡 Subscribed to message type: ${type}`);
    }
    
    // CORE: Unsubscribe from message type
    unsubscribe(type, callback) {
        if (this.listeners.has(type)) {
            const callbacks = this.listeners.get(type);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        }
    }
    
    // CORE: Send message to iframe
    sendToIframe(iframeId, type, data) {
        const iframe = document.getElementById(iframeId);
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({ type, data }, '*');
            console.log(`📡 Message sent to iframe ${iframeId}:`, type);
        }
    }
    
    // CORE: Send message to parent
    sendToParent(type, data) {
        if (window.parent !== window) {
            window.parent.postMessage({ type, data }, '*');
            console.log(`📡 Message sent to parent:`, type);
        }
    }
    
    // CORE: Broadcast message to all listeners
    broadcast(type, data) {
        if (this.listeners.has(type)) {
            const callbacks = this.listeners.get(type);
            callbacks.forEach(callback => callback(data));
            console.log(`📡 Broadcast message:`, type);
        }
    }
}
```

#### **Step 1.3: DragStateManager**
**File**: `drag-state-manager.js`
**Priority**: HIGH

```javascript
/**
 * DRAG STATE MANAGER
 * Tracks drag operation state across the application
 */
class DragStateManager {
    constructor() {
        this.reset();
        console.log('📊 DragStateManager initialized');
    }
    
    // CORE: Reset all state
    reset() {
        this.isDragging = false;
        this.dragType = null; // 'section', 'widget', 'template'
        this.dragData = null;
        this.startPosition = null;
        this.currentPosition = null;
        this.dragElement = null;
        this.dropTargets = [];
    }
    
    // CORE: Start drag operation
    startDrag(itemId, startIndex, dragType = 'section', additionalData = {}) {
        this.isDragging = true;
        this.dragType = dragType;
        this.startPosition = startIndex;
        this.dragData = {
            itemId: itemId,
            startIndex: startIndex,
            ...additionalData
        };
        
        // Emit drag start event
        this.emitStateChange('drag-start');
        
        console.log('🔄 Drag state started:', this.dragData);
    }
    
    // CORE: Update drag position
    updatePosition(newIndex) {
        if (this.isDragging) {
            this.currentPosition = newIndex;
            this.emitStateChange('drag-move');
        }
    }
    
    // CORE: End drag operation
    endDrag(success = true) {
        const wasActive = this.isDragging;
        
        if (wasActive) {
            this.emitStateChange(success ? 'drag-success' : 'drag-error');
        }
        
        this.reset();
        
        if (wasActive) {
            console.log(`🔄 Drag state ended: ${success ? 'success' : 'error'}`);
        }
    }
    
    // CORE: Add drop target
    addDropTarget(target) {
        this.dropTargets.push(target);
    }
    
    // CORE: Remove drop target
    removeDropTarget(target) {
        const index = this.dropTargets.indexOf(target);
        if (index > -1) {
            this.dropTargets.splice(index, 1);
        }
    }
    
    // CORE: Get current state
    getState() {
        return {
            isDragging: this.isDragging,
            dragType: this.dragType,
            dragData: this.dragData,
            startPosition: this.startPosition,
            currentPosition: this.currentPosition,
            dropTargets: this.dropTargets
        };
    }
    
    // CORE: Emit state change event
    emitStateChange(eventType) {
        const event = new CustomEvent(`dragstate:${eventType}`, {
            detail: this.getState()
        });
        document.dispatchEvent(event);
    }
}
```

#### **Step 1.4: PositionCalculator**
**File**: `position-calculator.js`
**Priority**: MEDIUM

```javascript
/**
 * POSITION CALCULATOR
 * Handles mathematical calculations for drag positioning
 */
class PositionCalculator {
    constructor() {
        console.log('🧮 PositionCalculator initialized');
    }
    
    // CORE: Calculate new position based on indices
    calculate(oldIndex, newIndex, items = []) {
        // Simple index-based calculation
        if (items.length === 0) {
            return newIndex;
        }
        
        // More sophisticated calculation with existing positions
        return this.calculateWithExistingPositions(oldIndex, newIndex, items);
    }
    
    // CORE: Calculate with existing position values
    calculateWithExistingPositions(oldIndex, newIndex, items) {
        const sortedItems = items.sort((a, b) => a.position - b.position);
        
        if (newIndex === 0) {
            // Moving to first position
            return sortedItems[0].position - 1;
        }
        
        if (newIndex >= sortedItems.length) {
            // Moving to last position
            return sortedItems[sortedItems.length - 1].position + 1;
        }
        
        // Moving between items
        const prevItem = sortedItems[newIndex - 1];
        const nextItem = sortedItems[newIndex];
        
        return (prevItem.position + nextItem.position) / 2;
    }
    
    // CORE: Normalize positions to prevent floating point issues
    normalizePositions(items) {
        const sortedItems = items.sort((a, b) => a.position - b.position);
        
        return sortedItems.map((item, index) => ({
            ...item,
            position: (index + 1) * 10 // 10, 20, 30, etc.
        }));
    }
    
    // CORE: Calculate drop position from coordinates
    calculateDropPosition(event, container) {
        const rect = container.getBoundingClientRect();
        const relativeY = event.clientY - rect.top;
        const containerHeight = rect.height;
        
        // Calculate percentage position
        const percentage = Math.max(0, Math.min(1, relativeY / containerHeight));
        
        return {
            percentage: percentage,
            pixelOffset: relativeY,
            estimatedIndex: Math.floor(percentage * container.children.length)
        };
    }
}
```

#### **Step 1.5: APIClient**
**File**: `api-client.js`
**Priority**: HIGH

```javascript
/**
 * API CLIENT
 * Unified API interface for drag operations
 */
class APIClient {
    constructor(baseUrl = '/admin/api', csrfToken = null) {
        this.baseUrl = baseUrl;
        this.csrfToken = csrfToken || this.getCSRFToken();
        
        console.log('🌐 APIClient initialized');
    }
    
    // CORE: Get CSRF token
    getCSRFToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }
    
    // CORE: Make API request
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                ...options.headers
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }
            
            return data;
            
        } catch (error) {
            console.error(`❌ API request failed: ${endpoint}`, error);
            throw error;
        }
    }
    
    // CORE: Update item position
    async updatePosition(itemId, positionData) {
        return await this.request(`/items/${itemId}/position`, {
            method: 'PUT',
            body: JSON.stringify(positionData)
        });
    }
    
    // CORE: Update section position
    async updateSectionPosition(sectionId, positionData) {
        return await this.request(`/sections/${sectionId}/position`, {
            method: 'PUT',
            body: JSON.stringify(positionData)
        });
    }
    
    // CORE: Create section from template
    async createSectionFromTemplate(templateData) {
        return await this.request('/sections/from-template', {
            method: 'POST',
            body: JSON.stringify(templateData)
        });
    }
    
    // CORE: Add widget to section
    async addWidgetToSection(sectionId, widgetData) {
        return await this.request(`/sections/${sectionId}/widgets`, {
            method: 'POST',
            body: JSON.stringify(widgetData)
        });
    }
    
    // CORE: Batch position updates
    async batchUpdatePositions(updates) {
        return await this.request('/positions/batch', {
            method: 'PUT',
            body: JSON.stringify({ updates })
        });
    }
}
```

---

## Integration Points

### **System Extensions**
Both page-builder and live-designer will extend these core classes:

```javascript
// Page-Builder Extension
class PageBuilderDragService extends DragService {
    onDragStart(evt) {
        // Page-builder specific logic
    }
}

// Live-Designer Extension  
class LiveDesignerDragService extends DragService {
    onDragStart(evt) {
        // Live-designer specific logic
    }
}
```

### **Shared Constants**
**File**: `constants.js`

```javascript
export const DRAG_TYPES = {
    SECTION: 'section',
    WIDGET: 'widget', 
    TEMPLATE: 'template',
    CONTENT_TYPE: 'content-type',
    CONTENT_ITEM: 'content-item'
};

export const MESSAGE_TYPES = {
    DRAG_START: 'drag-start',
    DRAG_END: 'drag-end',
    SECTION_REORDERED: 'section-reordered',
    WIDGET_DROPPED: 'widget-dropped',
    TEMPLATE_DROPPED: 'template-dropped'
};

export const CSS_CLASSES = {
    GHOST: 'sortable-ghost',
    CHOSEN: 'sortable-chosen',
    DRAG_ACTIVE: 'drag-active',
    DROP_TARGET: 'drop-target',
    DROP_INDICATOR: 'drop-indicator'
};
```

---

## Testing Strategy

### **Unit Tests**
- **DragService**: Position calculations, API calls
- **MessageBus**: Message routing and handling
- **PositionCalculator**: Mathematical accuracy
- **APIClient**: Request formatting and error handling

### **Integration Tests**
- **Cross-frame communication**: Message passing
- **API integration**: Position updates
- **State management**: Drag state consistency

### **Browser Compatibility**
- **Chrome, Firefox, Safari, Edge**: Full functionality
- **Mobile browsers**: Touch drag support
- **Iframe security**: Cross-origin restrictions

---

## Success Metrics

### **Performance**
- **Initialization**: < 100ms for core system
- **Drag responsiveness**: < 50ms visual feedback
- **API calls**: < 500ms position updates
- **Memory usage**: < 5MB for core system

### **Reliability**
- **Error recovery**: 100% revert on failed operations
- **State consistency**: Zero state corruption
- **Cross-frame**: 99% message delivery success
- **Browser support**: 100% compatibility target browsers
