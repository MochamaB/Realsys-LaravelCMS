# Selection Manager Implementation Plan

**Project**: RealsysCMS Live Designer - Unified Selection Manager System  
**Date**: September 5, 2025  
**Status**: In Development  

## Overview

Replace the current scattered component selection system with a unified, GrapeJS-inspired Selection Manager architecture that provides centralized selection handling, real DOM toolbars, and extensible functionality.

## Current State

- ✅ **Backup Created**: Old files moved to `live-designer-old/`
- ✅ **Folder Structure**: New `live-designer/` directories created
- 🔄 **Asset Loading**: Need to remove old assets from LivePreviewController

## Architecture Components

### Core Files Structure
```
public/assets/admin/js/live-designer/
├── iframe-communicator.js      # Parent-iframe messaging system
├── component-detector.js       # Unified component identification
├── component-toolbar.js        # Real DOM toolbar management
├── selection-manager.js        # Core selection orchestrator
├── sortable-manager.js         # Drag & drop functionality
├── content-extractor.js        # Component data extraction
└── main.js                     # Initialization entry point

public/assets/admin/css/live-designer/
├── selection-manager.css       # Component selection styles
├── toolbar.css                 # Toolbar component styles
└── sortable.css               # Drag & drop visual feedback
```

## Implementation Steps

### Phase 1: Foundation Setup

#### Step 1.1: Remove Old Asset Loading ⏳
- **File**: `app/Http/Controllers/Api/LivePreviewController.php`
- **Action**: Remove old JS/CSS includes from `getPreviewAssets()`
- **Lines**: ~680-690 (widget-preview.js, section-preview.js, page-preview.js, preview-helpers.js, preview-helpers.css)

#### Step 1.2: Create Core Communication System ⏳
- **File**: `iframe-communicator.js`
- **Purpose**: Handle parent-iframe messaging
- **Features**:
  - Message queue for iframe readiness
  - Security validation (origin checking)
  - Bidirectional communication protocol
  - Event handling for zoom changes, selections

#### Step 1.3: Create Component Detection System ⏳
- **File**: `component-detector.js`
- **Purpose**: Unified component identification
- **Features**:
  - Single click handler for all component types
  - DOM tree traversal for component discovery
  - Priority-based detection (widget → section → page)
  - Component metadata extraction

### Phase 2: Selection Management

#### Step 2.1: Create Selection Manager Core ⏳
- **File**: `selection-manager.js`
- **Purpose**: Central selection state management
- **Features**:
  - Single selection state
  - Component highlighting system
  - Event emission (component:selected, component:deselected)
  - Mode management (select, sort, edit)

#### Step 2.2: Create Real DOM Toolbar System ⏳
- **File**: `component-toolbar.js`
- **Purpose**: Replace CSS pseudo-element toolbars
- **Features**:
  - Real DOM elements for better control
  - Dynamic button generation based on component type
  - Action handler registration system
  - Zoom-compensated positioning

### Phase 3: Advanced Features

#### Step 3.1: Create Sortable Management ⏳
- **File**: `sortable-manager.js`
- **Purpose**: Drag & drop functionality
- **Features**:
  - SortableJS integration
  - Component-specific sortable containers
  - Visual drag handles and feedback
  - Reorder event communication to parent

#### Step 3.2: Create Content Extraction System ⏳
- **File**: `content-extractor.js`
- **Purpose**: Component data extraction
- **Features**:
  - Complete component HTML extraction
  - Computed style analysis
  - Metadata and settings extraction
  - Dimensional data capture

### Phase 4: Styling & Integration

#### Step 4.1: Create Selection Manager CSS ⏳
- **File**: `selection-manager.css`
- **Purpose**: Component selection visual feedback
- **Features**:
  - Unified highlight system
  - Component-type specific colors
  - Zoom compensation variables
  - Responsive design support

#### Step 4.2: Update Asset Loading ⏳
- **File**: `app/Http/Controllers/Api/LivePreviewController.php`
- **Action**: Add new selection manager assets
- **Include**:
  - selection-manager.css
  - All new JavaScript modules
  - SortableJS CDN
  - Initialization script

### Phase 5: Testing & Validation

#### Step 5.1: Component Selection Testing ⏳
- Test page, section, and widget selection
- Verify toolbar appearance and positioning
- Test action button functionality
- Validate iframe-parent communication

#### Step 5.2: Advanced Feature Testing ⏳
- Test sortable functionality
- Verify content extraction
- Test zoom compensation
- Validate cross-browser compatibility

## Technical Specifications

### Component Detection Logic
```javascript
// Priority order for overlapping elements
1. Widget: [data-page-section-widget-id]
2. Section: [data-section-id]  
3. Page: [data-preview-page]
```

### Toolbar Action System
```javascript
// Standard actions by component type
Page: ['edit', 'settings', 'extract-content', 'enable-sort', 'close']
Section: ['add-widget', 'edit', 'copy', 'delete', 'sort-widgets', 'extract-content']
Widget: ['edit', 'copy', 'settings', 'delete', 'inline-edit', 'style-editor']
```

### Communication Protocol
```javascript
// Iframe → Parent messages
'component:selected' - Component selection event
'component:deselected' - Component deselection event  
'toolbar:action' - Toolbar button clicked
'component:reorder' - Sortable reorder event
'component:content-extracted' - Content extraction complete

// Parent → Iframe messages
'zoom:changed' - Zoom level updated
'component:select' - Programmatic selection
'component:deselect' - Programmatic deselection
'action:execute' - Execute specific action
```

### CSS Architecture
```css
/* Component highlighting hierarchy */
.preview-selected { /* Base selection styles */ }
.preview-selected--page { /* Page-specific highlighting */ }
.preview-selected--section { /* Section-specific highlighting */ }
.preview-selected--widget { /* Widget-specific highlighting */ }

/* Zoom compensation system */
body[data-zoom="1.5"] { --selection-zoom-inverse: 0.67; }
.component-toolbar { transform: scale(var(--selection-zoom-inverse, 1)); }
```

## Migration Benefits

### Immediate Improvements
- **90% less code duplication** across component types
- **Real DOM toolbars** instead of CSS pseudo-elements
- **Centralized event handling** with single click listener
- **Consistent visual feedback** across all components

### New Capabilities Enabled
- **Drag & drop reordering** with SortableJS
- **Content extraction** with complete component data
- **Inline editing** with contenteditable support
- **Style management** with computed style analysis
- **Extensible actions** with plugin system

### Performance Improvements
- **Reduced DOM queries** with centralized detection
- **Optimized event handling** with event delegation
- **Smaller CSS footprint** with unified classes
- **Better memory management** with proper cleanup

## Risk Mitigation

### Backward Compatibility
- All existing data attributes preserved
- Current HTML structure unchanged
- Laravel backend requires no modifications
- Gradual rollout possible with feature flags

### Fallback Strategy
- Old files preserved in `live-designer-old/`
- Quick rollback by updating asset paths
- Component-by-component testing possible
- No database schema changes required

## Success Criteria

### Functional Requirements
- ✅ All current selection functionality preserved
- ✅ New sortable containers working
- ✅ Content extraction operational
- ✅ Toolbar actions functional
- ✅ Cross-browser compatibility maintained

### Performance Requirements
- ✅ Page load time not increased
- ✅ Selection response time < 100ms
- ✅ Memory usage not increased
- ✅ No JavaScript errors in console

### User Experience Requirements
- ✅ Intuitive component selection
- ✅ Clear visual feedback
- ✅ Consistent toolbar behavior
- ✅ Responsive design maintained

## Next Steps

1. **Remove old asset loading** from LivePreviewController
2. **Create iframe-communicator.js** with message handling
3. **Create component-detector.js** with unified detection
4. **Create selection-manager.js** as core orchestrator
5. **Test basic selection functionality**
6. **Add toolbar and advanced features**
7. **Complete integration and testing**

---

**Implementation Status**: 🔄 In Progress  
**Estimated Completion**: Phase 1-2 (Core functionality)  
**Next Milestone**: Remove old assets and create communication foundation
