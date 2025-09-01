# Unified Drag System Implementation Plan
**GridStack Removal & SortableJS Migration Strategy**

**Date**: 2025-01-09  
**Purpose**: Complete migration from broken GridStack implementation to unified iframe-based drag system using SortableJS

---

## Executive Summary

The current GridStack implementation has proven too complex and unreliable, with initialization failures and container conflicts. This plan outlines a systematic migration to a unified drag system using SortableJS for iframe content and enhanced sidebar drag coordination. The approach prioritizes high-risk core functionality first, then progressively enhances the system with advanced features.

---

## Current State Assessment

### **Broken GridStack Implementation ❌**
- **grid-manager.js**: 80+ methods dedicated to GridStack - all broken
- **page-builder-main.js**: GridStack initialization commented out due to failures
- **section-manager.js**: GridStack positioning logic non-functional
- **Container Issues**: #gridStackContainer not found, initialization timing conflicts
- **CSS Conflicts**: GridStack styles conflicting with iframe layout
- **Event Conflicts**: GridStack events interfering with iframe communication

### **Working Components ✅**
- **Iframe Preview**: Real frontend rendering with toolbar actions
- **Message System**: Parent-iframe communication via postMessage
- **API Layer**: Position updates and section management endpoints
- **Sidebar Drag**: Template and widget drag event listeners
- **Database Schema**: Position fields and grid configuration support

### **Migration Benefits**
- **Eliminates Complex Dependencies**: Remove 800+ lines of GridStack code
- **Improves Reliability**: SortableJS is lighter and more stable
- **Maintains Live Preview**: Keep working iframe-based real rendering
- **Preserves API**: No backend changes required
- **Reduces Bundle Size**: Remove entire GridStack library (~200KB)

---

## Risk Assessment & Prioritization

### **🔴 CRITICAL RISK (Phase 1 Priority)**
**Section Positioning & Reordering**
- **Risk**: Core page building functionality
- **Impact**: Complete system failure if broken
- **Dependencies**: API endpoints, database position field, iframe communication
- **Mitigation**: Implement with extensive testing, fallback mechanisms

**GridStack Removal**
- **Risk**: Breaking existing references and initialization
- **Impact**: Console errors, initialization failures
- **Dependencies**: All manager classes reference GridStack
- **Mitigation**: Systematic comment-out before removal, thorough testing

### **🟡 HIGH RISK (Phase 2 Priority)**  
**Sidebar to Iframe Drag Coordination**
- **Risk**: Complex cross-frame drag operations
- **Impact**: Template and widget creation workflows broken
- **Dependencies**: Message protocol, iframe injection, drop zone detection
- **Mitigation**: Incremental implementation, extensive cross-frame testing

**Iframe Content Manipulation**
- **Risk**: Injecting drag functionality into iframe content
- **Impact**: Preview rendering could break
- **Dependencies**: Theme assets, iframe security, DOM manipulation
- **Mitigation**: Non-invasive injection, fallback to click-based operations

### **🟢 MEDIUM RISK (Phase 3 Priority)**
**Advanced Drag Features**
- **Risk**: Performance and UX enhancements
- **Impact**: Reduced user experience if missing
- **Dependencies**: Core drag system working
- **Mitigation**: Progressive enhancement approach

---

# Implementation Phases

## **Phase 1: Critical GridStack Removal (Week 1) 🔴**

### **Day 1-2: Emergency GridStack Disconnection**

#### **Step 1.1: Disable GridStack Dependencies**
**Priority**: CRITICAL
**Files**: `page-builder-main.js`, `section-manager.js`, `show.blade.php`

**Actions**:
1. **Comment out all GridStack initialization**:
   ```javascript
   // In page-builder-main.js - initializeManagers()
   // TEMPORARILY COMMENT OUT:
   // this.gridManager = new GridManager(`#${this.options.containerId}`);
   // await this.gridManager.initialize({...});
   
   // Instead use:
   this.gridManager = null; // Disable GridStack
   ```

2. **Disable GridStack method calls**:
   ```javascript
   // In page-builder-main.js
   addRenderedSectionToGrid(section) {
       // ALWAYS use fallback rendering
       this.fallbackRenderSection(section);
       return;
   }
   ```

3. **Remove GridStack CSS references**:
   ```html
   <!-- In show.blade.php - COMMENT OUT: -->
   {{-- <link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" /> --}}
   {{-- <link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" /> --}}
   ```

4. **Test basic page loading** - Ensure no console errors

#### **Step 1.2: Implement Core Section Positioning**
**Priority**: CRITICAL
**New File**: `drag-service.js`

**Implementation**:
```javascript
/**
 * CORE DRAG SERVICE
 * Replaces GridStack with simple position management
 */
class DragService {
    constructor(api, containerId) {
        this.api = api;
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
    }
    
    // CRITICAL: Section reordering
    async updateSectionPosition(sectionId, newPosition) {
        try {
            const response = await this.api.updateSection(sectionId, {
                position: newPosition
            });
            
            if (response.success) {
                console.log(`✅ Section ${sectionId} position updated to ${newPosition}`);
                return response;
            } else {
                throw new Error(response.error || 'Position update failed');
            }
        } catch (error) {
            console.error('❌ Section position update failed:', error);
            throw error;
        }
    }
    
    // CRITICAL: Simple DOM-based section rendering
    renderSectionInContainer(section) {
        const sectionElement = document.createElement('div');
        sectionElement.className = 'page-section';
        sectionElement.setAttribute('data-section-id', section.id);
        sectionElement.setAttribute('data-position', section.position);
        
        sectionElement.innerHTML = `
            <div class="section-header">
                <span class="section-title">${section.name || `Section ${section.id}`}</span>
                <div class="section-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="window.pageBuilder?.editSection(${section.id})">
                        <i class="ri-settings-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="window.pageBuilder?.deleteSection(${section.id})">
                        <i class="ri-delete-line"></i>
                    </button>
                </div>
            </div>
            <div class="section-content">
                ${section.rendered_html || '<div class="empty-section">No content</div>'}
            </div>
        `;
        
        this.container.appendChild(sectionElement);
        return sectionElement;
    }
    
    // CRITICAL: Section removal
    removeSectionFromContainer(sectionId) {
        const sectionElement = this.container.querySelector(`[data-section-id="${sectionId}"]`);
        if (sectionElement) {
            sectionElement.remove();
            console.log(`✅ Section ${sectionId} removed from container`);
        }
    }
}
```

#### **Step 1.3: Update Page Builder Main**
**Priority**: CRITICAL
**File**: `page-builder-main.js`

**Actions**:
1. **Replace GridStack initialization**:
   ```javascript
   // In initializeManagers() - REPLACE:
   // this.gridManager = new GridManager(`#${this.options.containerId}`);
   
   // WITH:
   this.dragService = new DragService(this.api, this.options.containerId);
   ```

2. **Replace section rendering**:
   ```javascript
   // In loadInitialContent() - REPLACE:
   // this.addRenderedSectionToGrid(section);
   
   // WITH:
   this.dragService.renderSectionInContainer(section);
   ```

3. **Test section loading** without GridStack dependencies

### **Day 3-4: Implement Basic Section Reordering**

#### **Step 1.4: Add SortableJS for Section Ordering**
**Priority**: CRITICAL
**File**: `drag-service.js` (enhance)

**Implementation**:
```javascript
// Add to DragService class
initializeSectionSorting() {
    if (!this.container) {
        console.error('❌ Container not found for section sorting');
        return;
    }
    
    // Initialize SortableJS for section reordering
    this.sortable = Sortable.create(this.container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        handle: '.section-header', // Only drag by header
        
        onStart: (evt) => {
            console.log('🔄 Section drag started:', evt.item.dataset.sectionId);
            this.container.classList.add('drag-active');
        },
        
        onEnd: async (evt) => {
            console.log('🔄 Section drag ended:', {
                sectionId: evt.item.dataset.sectionId,
                oldPosition: evt.oldIndex,
                newPosition: evt.newIndex
            });
            
            this.container.classList.remove('drag-active');
            
            // Update position in database
            if (evt.oldIndex !== evt.newIndex) {
                await this.handleSectionReorder(
                    evt.item.dataset.sectionId,
                    evt.oldIndex,
                    evt.newIndex
                );
            }
        }
    });
    
    console.log('✅ Section sorting initialized with SortableJS');
}

async handleSectionReorder(sectionId, oldPosition, newPosition) {
    try {
        // Calculate new position value
        const newPositionValue = this.calculateNewPosition(oldPosition, newPosition);
        
        // Update in database
        await this.updateSectionPosition(sectionId, newPositionValue);
        
        // Emit event for other components
        document.dispatchEvent(new CustomEvent('pagebuilder:section-reordered', {
            detail: { sectionId, oldPosition, newPosition, newPositionValue }
        }));
        
        console.log(`✅ Section ${sectionId} reordered successfully`);
        
    } catch (error) {
        console.error('❌ Section reorder failed:', error);
        // Revert DOM order on failure
        this.revertSectionOrder(oldPosition, newPosition);
    }
}

calculateNewPosition(oldIndex, newIndex) {
    // Simple position calculation - can be enhanced later
    return newIndex;
}

revertSectionOrder(oldIndex, newIndex) {
    // Revert DOM order if database update fails
    const sections = Array.from(this.container.children);
    const movedSection = sections[newIndex];
    
    if (oldIndex < newIndex) {
        this.container.insertBefore(movedSection, sections[oldIndex]);
    } else {
        this.container.insertBefore(movedSection, sections[oldIndex + 1]);
    }
}
```

#### **Step 1.5: Add Required CSS**
**Priority**: CRITICAL
**New File**: `public/assets/admin/css/drag-system.css`

```css
/* Replace GridStack styles with simple drag styles */
.page-section {
    margin-bottom: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    transition: all 0.2s ease;
}

.page-section:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
}

/* SortableJS drag states */
.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
}

.sortable-chosen {
    cursor: grabbing !important;
}

.sortable-drag {
    transform: rotate(2deg);
}

.drag-active {
    background: #f8f9fa;
}

.section-header {
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    cursor: grab;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header:active {
    cursor: grabbing;
}

.section-content {
    padding: 16px;
    min-height: 100px;
}

.empty-section {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 40px 20px;
}
```

#### **Step 1.6: Update Templates**
**Priority**: CRITICAL
**File**: `show.blade.php`

**Actions**:
1. **Add drag system CSS**:
   ```html
   <!-- Add after other CSS -->
   <link href="{{ asset('assets/admin/css/drag-system.css') }}" rel="stylesheet" />
   ```

2. **Add SortableJS library**:
   ```html
   <!-- Add before page builder scripts -->
   <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
   ```

3. **Update container markup** (simplify from GridStack):
   ```html
   <!-- REPLACE GridStack container with simple container -->
   <div id="pageBuilderCanvas" class="page-builder-canvas">
       <!-- Sections will be rendered here by drag-service.js -->
   </div>
   ```

### **Day 5: Critical Testing & Fallbacks**

#### **Step 1.7: Comprehensive Testing**
**Priority**: CRITICAL

**Test Cases**:
1. **Page Loading**:
   - ✅ No console errors on page load
   - ✅ Sections render in correct order
   - ✅ Section controls (edit/delete) work

2. **Section Reordering**:
   - ✅ Drag section headers to reorder
   - ✅ Position updates persist in database
   - ✅ Order maintained after page refresh

3. **Error Handling**:
   - ✅ Failed API calls revert DOM order
   - ✅ Missing sections handled gracefully
   - ✅ Fallback to click-based operations if drag fails

#### **Step 1.8: Fallback Mechanisms**
**Priority**: CRITICAL

**Implementation**:
```javascript
// In DragService class
initializeWithFallback() {
    try {
        this.initializeSectionSorting();
    } catch (error) {
        console.warn('⚠️ SortableJS initialization failed, using fallback:', error);
        this.initializeFallbackControls();
    }
}

initializeFallbackControls() {
    // Add up/down buttons as fallback
    this.container.querySelectorAll('.section-header').forEach((header, index) => {
        const fallbackControls = document.createElement('div');
        fallbackControls.className = 'fallback-controls';
        fallbackControls.innerHTML = `
            <button class="btn btn-sm btn-outline-secondary" onclick="window.pageBuilder?.moveSectionUp(${header.closest('.page-section').dataset.sectionId})">
                <i class="ri-arrow-up-line"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.pageBuilder?.moveSectionDown(${header.closest('.page-section').dataset.sectionId})">
                <i class="ri-arrow-down-line"></i>
            </button>
        `;
        header.querySelector('.section-actions').appendChild(fallbackControls);
    });
}
```

---

## **Phase 2: Iframe Drag Integration (Week 2) 🟡**

### **Day 6-8: Iframe Content Drag Implementation**

#### **Step 2.1: Create Iframe Drag Adapter**
**Priority**: HIGH
**New File**: `iframe-drag-adapter.js`

**Implementation**:
```javascript
/**
 * IFRAME DRAG ADAPTER
 * Injects drag functionality into iframe content
 */
class IframeDragAdapter {
    constructor(iframeId, dragService) {
        this.iframe = document.getElementById(iframeId);
        this.dragService = dragService;
        this.iframeDocument = null;
        this.initialized = false;
    }
    
    async initialize() {
        if (!this.iframe) {
            console.error('❌ Iframe not found for drag adapter');
            return false;
        }
        
        try {
            // Wait for iframe to load
            await this.waitForIframeLoad();
            
            // Get iframe document
            this.iframeDocument = this.iframe.contentDocument || this.iframe.contentWindow.document;
            
            // Inject drag functionality
            await this.injectDragSystem();
            
            this.initialized = true;
            console.log('✅ Iframe drag adapter initialized');
            return true;
            
        } catch (error) {
            console.error('❌ Iframe drag adapter initialization failed:', error);
            return false;
        }
    }
    
    waitForIframeLoad() {
        return new Promise((resolve, reject) => {
            if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
                resolve();
                return;
            }
            
            this.iframe.onload = () => resolve();
            this.iframe.onerror = () => reject(new Error('Iframe failed to load'));
            
            // Timeout after 10 seconds
            setTimeout(() => reject(new Error('Iframe load timeout')), 10000);
        });
    }
    
    async injectDragSystem() {
        // Inject SortableJS into iframe
        await this.injectScript('https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js');
        
        // Inject drag styles
        this.injectStyles();
        
        // Setup section sorting within iframe
        this.setupIframeSectionSorting();
        
        // Setup message communication
        this.setupMessageHandling();
    }
    
    injectScript(src) {
        return new Promise((resolve, reject) => {
            const script = this.iframeDocument.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            this.iframeDocument.head.appendChild(script);
        });
    }
    
    injectStyles() {
        const style = this.iframeDocument.createElement('style');
        style.textContent = `
            .page-section {
                position: relative;
                transition: all 0.2s ease;
            }
            
            .page-section:hover .drag-handle {
                opacity: 1;
            }
            
            .drag-handle {
                position: absolute;
                top: 10px;
                right: 10px;
                opacity: 0;
                background: rgba(0,123,255,0.9);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                cursor: grab;
                font-size: 12px;
                z-index: 1000;
                transition: opacity 0.2s ease;
            }
            
            .drag-handle:active {
                cursor: grabbing;
            }
            
            .sortable-ghost {
                opacity: 0.4;
            }
            
            .drop-indicator {
                height: 4px;
                background: #007bff;
                margin: 10px 0;
                border-radius: 2px;
                transition: all 0.2s ease;
            }
        `;
        this.iframeDocument.head.appendChild(style);
    }
    
    setupIframeSectionSorting() {
        // Find section container in iframe
        const sectionsContainer = this.iframeDocument.querySelector('.sections-container') 
                                || this.iframeDocument.querySelector('[data-sections]')
                                || this.iframeDocument.body;
        
        if (!sectionsContainer) {
            console.warn('⚠️ No sections container found in iframe');
            return;
        }
        
        // Add drag handles to existing sections
        this.addDragHandlesToSections(sectionsContainer);
        
        // Initialize SortableJS
        this.iframeSortable = this.iframeDocument.defaultView.Sortable.create(sectionsContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            
            onStart: (evt) => {
                this.sendMessageToParent({
                    type: 'drag-start',
                    sectionId: evt.item.dataset.sectionId
                });
            },
            
            onEnd: (evt) => {
                this.sendMessageToParent({
                    type: 'section-reordered',
                    sectionId: evt.item.dataset.sectionId,
                    oldPosition: evt.oldIndex,
                    newPosition: evt.newIndex
                });
            }
        });
        
        console.log('✅ Iframe section sorting initialized');
    }
    
    addDragHandlesToSections(container) {
        const sections = container.querySelectorAll('[data-section-id]');
        sections.forEach(section => {
            if (!section.querySelector('.drag-handle')) {
                const handle = this.iframeDocument.createElement('div');
                handle.className = 'drag-handle';
                handle.innerHTML = '<i class="ri-drag-move-line"></i>';
                section.appendChild(handle);
            }
        });
    }
    
    setupMessageHandling() {
        // Listen for messages from parent
        this.iframeDocument.defaultView.addEventListener('message', (event) => {
            if (event.source !== window) return;
            
            switch (event.data.type) {
                case 'refresh-sections':
                    this.refreshSections();
                    break;
                case 'highlight-drop-zones':
                    this.highlightDropZones(event.data.dragType);
                    break;
                case 'clear-drop-zones':
                    this.clearDropZones();
                    break;
            }
        });
    }
    
    sendMessageToParent(data) {
        window.parent.postMessage(data, '*');
    }
    
    refreshSections() {
        // Re-add drag handles to new sections
        const sectionsContainer = this.iframeDocument.querySelector('.sections-container') 
                                || this.iframeDocument.querySelector('[data-sections]')
                                || this.iframeDocument.body;
        
        if (sectionsContainer) {
            this.addDragHandlesToSections(sectionsContainer);
        }
    }
    
    highlightDropZones(dragType) {
        // Add visual indicators for drop zones
        const sections = this.iframeDocument.querySelectorAll('[data-section-id]');
        sections.forEach((section, index) => {
            const indicator = this.iframeDocument.createElement('div');
            indicator.className = 'drop-indicator';
            indicator.style.display = 'none';
            
            if (dragType === 'template') {
                // Show drop zones between sections for templates
                if (index === 0) {
                    section.parentNode.insertBefore(indicator, section);
                }
                const afterIndicator = indicator.cloneNode();
                section.parentNode.insertBefore(afterIndicator, section.nextSibling);
            } else if (dragType === 'widget') {
                // Show drop zones within sections for widgets
                section.appendChild(indicator);
            }
        });
        
        // Show indicators
        this.iframeDocument.querySelectorAll('.drop-indicator').forEach(indicator => {
            indicator.style.display = 'block';
        });
    }
    
    clearDropZones() {
        this.iframeDocument.querySelectorAll('.drop-indicator').forEach(indicator => {
            indicator.remove();
        });
    }
}
```

#### **Step 2.2: Integrate Iframe Adapter with Main System**
**Priority**: HIGH
**File**: `page-builder-main.js`

**Actions**:
```javascript
// Add to PageBuilderMain class
async initializeIframeDrag() {
    if (!document.getElementById('pagePreviewIframe')) {
        console.warn('⚠️ Preview iframe not found, skipping iframe drag');
        return;
    }
    
    this.iframeDragAdapter = new IframeDragAdapter('pagePreviewIframe', this.dragService);
    const initialized = await this.iframeDragAdapter.initialize();
    
    if (initialized) {
        // Listen for iframe drag events
        window.addEventListener('message', (event) => {
            if (event.data.type === 'section-reordered') {
                this.handleIframeSectionReorder(event.data);
            }
        });
        
        console.log('✅ Iframe drag integration complete');
    } else {
        console.warn('⚠️ Iframe drag initialization failed, using fallback');
    }
}

async handleIframeSectionReorder(data) {
    try {
        // Update position via drag service
        await this.dragService.handleSectionReorder(
            data.sectionId,
            data.oldPosition,
            data.newPosition
        );
        
        // Refresh parent container to match iframe order
        await this.refresh();
        
        console.log('✅ Iframe section reorder processed');
        
    } catch (error) {
        console.error('❌ Iframe section reorder failed:', error);
        // Refresh iframe to revert order
        this.refreshPreviewIframe();
    }
}
```

### **Day 9-10: Cross-Frame Drag Coordination**

#### **Step 2.3: Enhance Sidebar Drag for Iframe Targeting**
**Priority**: HIGH
**File**: `sidebar-drag-adapter.js` (new)

**Implementation**:
```javascript
/**
 * SIDEBAR DRAG ADAPTER
 * Coordinates sidebar drag operations with iframe targets
 */
class SidebarDragAdapter {
    constructor(dragService, iframeDragAdapter) {
        this.dragService = dragService;
        this.iframeDragAdapter = iframeDragAdapter;
        this.currentDragType = null;
        this.currentDragData = null;
    }
    
    initialize() {
        this.setupTemplateManagerIntegration();
        this.setupWidgetLibraryIntegration();
        this.setupCrossFrameDragDetection();
        
        console.log('✅ Sidebar drag adapter initialized');
    }
    
    setupTemplateManagerIntegration() {
        // Enhance existing template manager drag events
        document.addEventListener('template-drag-start', (event) => {
            this.currentDragType = 'template';
            this.currentDragData = event.detail;
            
            // Show drop zones in iframe
            this.showIframeDropZones('template');
        });
        
        document.addEventListener('template-drag-end', (event) => {
            this.clearIframeDropZones();
            this.currentDragType = null;
            this.currentDragData = null;
        });
    }
    
    setupWidgetLibraryIntegration() {
        // Enhance existing widget library drag events
        document.addEventListener('widget-drag-start', (event) => {
            this.currentDragType = 'widget';
            this.currentDragData = event.detail;
            
            // Show drop zones in iframe
            this.showIframeDropZones('widget');
        });
        
        document.addEventListener('widget-drag-end', (event) => {
            this.clearIframeDropZones();
            this.currentDragType = null;
            this.currentDragData = null;
        });
    }
    
    setupCrossFrameDragDetection() {
        const iframe = document.getElementById('pagePreviewIframe');
        if (!iframe) return;
        
        // Detect when drag enters iframe area
        iframe.addEventListener('dragenter', (event) => {
            if (this.currentDragType) {
                event.preventDefault();
                console.log(`🎯 ${this.currentDragType} drag entered iframe`);
                
                // Enhance drop zone visibility
                this.highlightIframeDropZones();
            }
        });
        
        iframe.addEventListener('dragleave', (event) => {
            if (this.currentDragType) {
                // Check if really leaving iframe (not just moving to child element)
                const rect = iframe.getBoundingClientRect();
                if (event.clientX < rect.left || event.clientX > rect.right ||
                    event.clientY < rect.top || event.clientY > rect.bottom) {
                    
                    console.log(`🎯 ${this.currentDragType} drag left iframe`);
                    this.normalizeIframeDropZones();
                }
            }
        });
        
        iframe.addEventListener('drop', (event) => {
            if (this.currentDragType && this.currentDragData) {
                event.preventDefault();
                this.handleIframeDrop(event);
            }
        });
    }
    
    showIframeDropZones(dragType) {
        if (this.iframeDragAdapter && this.iframeDragAdapter.initialized) {
            // Send message to iframe to show drop zones
            const iframe = document.getElementById('pagePreviewIframe');
            iframe.contentWindow.postMessage({
                type: 'highlight-drop-zones',
                dragType: dragType
            }, '*');
        }
    }
    
    clearIframeDropZones() {
        if (this.iframeDragAdapter && this.iframeDragAdapter.initialized) {
            const iframe = document.getElementById('pagePreviewIframe');
            iframe.contentWindow.postMessage({
                type: 'clear-drop-zones'
            }, '*');
        }
    }
    
    highlightIframeDropZones() {
        const iframe = document.getElementById('pagePreviewIframe');
        iframe.style.boxShadow = '0 0 20px rgba(0,123,255,0.5)';
        iframe.style.border = '2px solid #007bff';
    }
    
    normalizeIframeDropZones() {
        const iframe = document.getElementById('pagePreviewIframe');
        iframe.style.boxShadow = '';
        iframe.style.border = '';
    }
    
    async handleIframeDrop(event) {
        console.log(`🎯 Handling ${this.currentDragType} drop in iframe:`, this.currentDragData);
        
        try {
            if (this.currentDragType === 'template') {
                await this.handleTemplateDrop(event);
            } else if (this.currentDragType === 'widget') {
                await this.handleWidgetDrop(event);
            }
            
            // Clear drag state
            this.clearIframeDropZones();
            this.currentDragType = null;
            this.currentDragData = null;
            
        } catch (error) {
            console.error(`❌ ${this.currentDragType} drop failed:`, error);
            this.showDropError(error.message);
        }
    }
    
    async handleTemplateDrop(event) {
        // Calculate drop position
        const dropPosition = this.calculateDropPosition(event);
        
        // Create section from template using existing API
        const templateData = {
            key: this.currentDragData.templateKey,
            name: this.currentDragData.templateName,
            position: dropPosition
        };
        
        // Use page builder main controller
        if (window.pageBuilder && window.pageBuilder.createSectionFromTemplate) {
            await window.pageBuilder.createSectionFromTemplate(templateData);
            
            // Refresh iframe to show new section
            setTimeout(() => {
                this.refreshIframe();
            }, 1000);
        }
    }
    
    async handleWidgetDrop(event) {
        // Find target section
        const targetSection = this.findTargetSection(event);
        
        if (targetSection) {
            // Open widget modal for the target section
            if (window.widgetModalManager && window.widgetModalManager.openForSection) {
                window.widgetModalManager.openForSection(
                    targetSection.sectionId,
                    targetSection.sectionName,
                    { preselectedWidget: this.currentDragData.widgetId }
                );
            }
        } else {
            throw new Error('No target section found for widget drop');
        }
    }
    
    calculateDropPosition(event) {
        // Simple position calculation based on iframe coordinates
        const iframe = document.getElementById('pagePreviewIframe');
        const rect = iframe.getBoundingClientRect();
        const relativeY = event.clientY - rect.top;
        
        // Estimate position based on viewport height
        const estimatedPosition = Math.floor(relativeY / (rect.height / 10));
        return Math.max(0, estimatedPosition);
    }
    
    findTargetSection(event) {
        // For now, return first available section
        // This can be enhanced to find actual target section based on coordinates
        const sections = document.querySelectorAll('[data-section-id]');
        if (sections.length > 0) {
            const firstSection = sections[0];
            return {
                sectionId: firstSection.dataset.sectionId,
                sectionName: firstSection.querySelector('.section-title')?.textContent || 'Section'
            };
        }
        return null;
    }
    
    refreshIframe() {
        const iframe = document.getElementById('pagePreviewIframe');
        if (iframe) {
            iframe.contentWindow.location.reload();
        }
    }
    
    showDropError(message) {
        // Show error notification
        if (window.pageBuilder && window.pageBuilder.showNotification) {
            window.pageBuilder.showNotification('error', `Drop failed: ${message}`);
        } else {
            console.error('❌ Drop error:', message);
        }
    }
}
```

---

## **Phase 3: Sidebar Integration Enhancement (Week 3) 🟢**

### **Day 11-13: Template Manager Migration**

#### **Step 3.1: Update Template Manager for Iframe Targeting**
**Priority**: MEDIUM
**File**: `template-manager.js` (modify existing)

**Actions**:
1. **Remove GridStack dependencies**:
   ```javascript
   // REMOVE all GridStack references:
   // - setupDropZones() GridStack logic
   // - GridStack drag data format
   // - GridStack drop event handlers
   ```

2. **Add iframe targeting**:
   ```javascript
   // In setupDragAndDrop() method - ENHANCE existing:
   handleDragStart(event) {
       const templateCard = event.target.closest('.template-card');
       const templateData = {
           templateKey: templateCard.dataset.templateKey,
           templateName: templateCard.dataset.templateName,
           type: 'template'
       };
       
       // Set drag data
       event.dataTransfer.setData('text/plain', JSON.stringify(templateData));
       
       // Emit event for sidebar adapter
       document.dispatchEvent(new CustomEvent('template-drag-start', {
           detail: templateData
       }));
       
       console.log('🎯 Template drag started:', templateData);
   }
   
   handleDragEnd(event) {
       // Emit event for sidebar adapter
       document.dispatchEvent(new CustomEvent('template-drag-end', {
           detail: { success: true }
       }));
       
       console.log('🎯 Template drag ended');
   }
   ```

### **Day 14-15: Widget Library Migration**

#### **Step 3.2: Update Widget Library for Iframe Targeting**
**Priority**: MEDIUM
**File**: `widget-library.js` (modify existing)

**Actions**:
1. **Remove GridStack dependencies**:
   ```javascript
   // REMOVE GridStack references from:
   // - setupDragAndDrop() method
   // - setDragData() method
   // - handleDragEnd() method
   ```

2. **Add iframe targeting**:
   ```javascript
   // In setupDragAndDrop() method - ENHANCE existing:
   handleDragStart(event) {
       const widgetCard = event.target.closest('.widget-card');
       const widgetData = {
           widgetId: widgetCard.dataset.widgetId,
           widgetName: widgetCard.dataset.widgetName,
           type: 'widget'
       };
       
       // Set drag data
       event.dataTransfer.setData('text/plain', JSON.stringify(widgetData));
       
       // Emit event for sidebar adapter
       document.dispatchEvent(new CustomEvent('widget-drag-start', {
           detail: widgetData
       }));
       
       console.log('🎯 Widget drag started:', widgetData);
   }
   
   handleDragEnd(event) {
       // Emit event for sidebar adapter
       document.dispatchEvent(new CustomEvent('widget-drag-end', {
           detail: { success: true }
       }));
       
       console.log('🎯 Widget drag ended');
   }
   ```

---

## **Phase 4: Complete GridStack Removal (Week 4) 🟢**

### **Day 16-18: Final GridStack Cleanup**

#### **Step 4.1: Remove Grid Manager Completely**
**Priority**: LOW
**File**: `grid-manager.js`

**Actions**:
1. **Comment out entire file content**:
   ```javascript
   /*
   ===================================================================
   DEPRECATED: GridStack integration removed in favor of unified drag system
   This file is kept for reference but should not be used.
   All functionality moved to:
   - drag-service.js (core drag logic)
   - iframe-drag-adapter.js (iframe drag functionality)  
   - sidebar-drag-adapter.js (sidebar coordination)
   ===================================================================
   */
   
   // GridStack integration - DEPRECATED
   // See Implementation/Unified_Drag_System_Implementation_Plan.md
   ```

2. **Remove from imports** in other files

#### **Step 4.2: Clean Up Page Builder Main**
**Priority**: LOW
**File**: `page-builder-main.js`

**Actions**:
1. **Remove GridStack methods**:
   ```javascript
   // DELETE these methods completely:
   // - addRenderedSectionToGrid()
   // - wrapSectionForGrid()  
   // - fallbackRenderSection()
   // - GridStack event handlers
   ```

2. **Update initialization**:
   ```javascript
   // In initializeManagers() - FINAL VERSION:
   async initializeManagers() {
       console.log('🔧 Initializing managers...');
       
       // Initialize unified drag system
       this.dragService = new DragService(this.api, this.options.containerId);
       
       // Initialize other managers
       this.sectionManager = new SectionManager(this.api, this.dragService);
       this.widgetManager = new WidgetManager(this.api, this.sectionManager);
       this.widgetLibrary = new WidgetLibrary(this.api);
       this.templateManager = new TemplateManager(this.api, this.sectionManager);
       this.themeManager = new ThemeManager(this.api);
       
       // Initialize drag adapters
       await this.initializeIframeDrag();
       this.sidebarDragAdapter = new SidebarDragAdapter(this.dragService, this.iframeDragAdapter);
       this.sidebarDragAdapter.initialize();
       
       console.log('✅ All managers initialized with unified drag system');
   }
   ```

#### **Step 4.3: Remove GridStack Library Files**
**Priority**: LOW

**Actions**:
1. **Remove GridStack directory**: `public/assets/admin/libs/gridstack/`
2. **Remove GridStack CSS**: `public/assets/admin/css/gridstack-designer.css`
3. **Update package.json** (if GridStack was installed via npm)

### **Day 19-20: Advanced Features**

#### **Step 4.4: Section Resize Handles**
**Priority**: LOW
**File**: `iframe-drag-adapter.js` (enhance)

**Implementation**:
```javascript
// Add to IframeDragAdapter class
addResizeHandlesToSections() {
    const sections = this.iframeDocument.querySelectorAll('[data-section-id]');
    sections.forEach(section => {
        if (!section.querySelector('.resize-handle')) {
            const handle = this.iframeDocument.createElement('div');
            handle.className = 'resize-handle';
            handle.innerHTML = '<i class="ri-drag-move-2-line"></i>';
            
            section.appendChild(handle);
            this.setupResizeHandling(section, handle);
        }
    });
}

setupResizeHandling(section, handle) {
    let isResizing = false;
    let startY = 0;
    let startHeight = 0;
    
    handle.addEventListener('mousedown', (e) => {
        isResizing = true;
        startY = e.clientY;
        startHeight = section.offsetHeight;
        
        this.iframeDocument.addEventListener('mousemove', handleResize);
        this.iframeDocument.addEventListener('mouseup', stopResize);
    });
    
    const handleResize = (e) => {
        if (!isResizing) return;
        
        const deltaY = e.clientY - startY;
        const newHeight = Math.max(100, startHeight + deltaY);
        section.style.height = newHeight + 'px';
    };
    
    const stopResize = () => {
        if (isResizing) {
            isResizing = false;
            
            // Send height update to parent
            this.sendMessageToParent({
                type: 'section-resized',
                sectionId: section.dataset.sectionId,
                newHeight: section.offsetHeight
            });
        }
        
        this.iframeDocument.removeEventListener('mousemove', handleResize);
        this.iframeDocument.removeEventListener('mouseup', stopResize);
    };
}
```

#### **Step 4.5: Multi-Select Operations**
**Priority**: LOW
**File**: `drag-service.js` (enhance)

**Implementation**:
```javascript
// Add to DragService class
initializeMultiSelect() {
    this.selectedSections = new Set();
    
    // Ctrl+click for multi-select
    this.container.addEventListener('click', (event) => {
        if (event.ctrlKey || event.metaKey) {
            const section = event.target.closest('[data-section-id]');
            if (section) {
                this.toggleSectionSelection(section);
            }
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey || event.metaKey) {
            switch (event.key) {
                case 'a':
                    event.preventDefault();
                    this.selectAllSections();
                    break;
                case 'd':
                    event.preventDefault();
                    this.deselectAllSections();
                    break;
            }
        }
    });
}

toggleSectionSelection(sectionElement) {
    const sectionId = sectionElement.dataset.sectionId;
    
    if (this.selectedSections.has(sectionId)) {
        this.selectedSections.delete(sectionId);
        sectionElement.classList.remove('selected');
    } else {
        this.selectedSections.add(sectionId);
        sectionElement.classList.add('selected');
    }
    
    console.log(`📋 Selected sections: ${this.selectedSections.size}`);
}
```

---

## **Testing Strategy**

### **Phase 1 Testing (Critical)**
- **Automated**: Unit tests for DragService position calculations
- **Manual**: Section reordering in all browsers
- **Integration**: API endpoint testing with position updates
- **Performance**: Load testing with 50+ sections

### **Phase 2 Testing (High Risk)**
- **Cross-Frame**: Iframe security and communication testing
- **Browser Compatibility**: Safari iframe restrictions, Chrome security policies
- **Network**: Offline behavior and retry mechanisms
- **Error Recovery**: Failed API calls and DOM reversion

### **Phase 3 Testing (Medium Risk)**
- **Drag Workflows**: Template and widget creation end-to-end
- **UX Testing**: User feedback on drag operations
- **Edge Cases**: Empty pages, network failures, browser crashes

### **Phase 4 Testing (Low Risk)**
- **Performance**: Bundle size reduction verification
- **Code Quality**: ESLint, dead code removal
- **Documentation**: Implementation guides and API docs

---

## **Rollback Strategy**

### **Emergency Rollback (if Phase 1 fails)**
1. **Uncomment GridStack initialization** in page-builder-main.js
2. **Re-enable GridStack CSS** in show.blade.php
3. **Revert to fallbackRenderSection()** for all section rendering
4. **Document issues** for future investigation

### **Partial Rollback (if Phase 2-3 fails)**
1. **Keep Phase 1 improvements** (basic section reordering)
2. **Disable iframe drag integration**
3. **Fall back to click-based template/widget creation**

### **Feature Flags**
```javascript
// Add to page-builder-main.js
const FEATURE_FLAGS = {
    UNIFIED_DRAG_SYSTEM: true,
    IFRAME_DRAG: true,
    SIDEBAR_IFRAME_DRAG: false, // Can be disabled if issues occur
    MULTI_SELECT: false         // Advanced features can be toggled off
};
```

---

## **Success Metrics**

### **Technical Metrics**
- **Bundle Size**: Reduce by ~200KB (GridStack removal)
- **Initialization Time**: Improve by 50% (no GridStack setup)
- **Console Errors**: Zero GridStack-related errors
- **API Calls**: Maintain current success rates for position updates

### **User Experience**
- **Drag Responsiveness**: < 100ms visual feedback
- **Cross-Frame Lag**: < 200ms iframe to parent communication
- **Error Recovery**: Automatic DOM reversion on failed API calls
- **Browser Support**: 100% compatibility with Chrome, Firefox, Safari, Edge

### **Development Experience**
- **Code Maintainability**: 80+ fewer methods to maintain
- **Debug Simplicity**: Clear error messages and logging
- **Testing Coverage**: 90% unit test coverage for drag operations
- **Documentation**: Complete implementation and API guides

This unified approach eliminates the complexity and unreliability of GridStack while providing a more robust, maintainable drag system that works seamlessly across iframe boundaries.