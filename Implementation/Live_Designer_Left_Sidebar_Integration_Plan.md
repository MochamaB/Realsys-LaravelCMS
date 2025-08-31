O# Live Designer Left Sidebar Integration Plan

## Overview
This document outlines the implementation plan for integrating the left sidebar from page-builder into the live-designer, creating a dual sidebar layout (left for components, right for properties) with responsive collapse functionality.

## Current State Analysis

### Page-Builder Left Sidebar Components
- **Location**: `resources/views/admin/pages/page-builder/components/left-sidebar.blade.php`
- **Width**: 280px (collapses to 70px)
- **Sections**: 
  - Page Sections (`#sectionsCollapse`)
  - Widgets (`#themeWidgetsCollapse`) 
  - Templates (`#templatesCollapse`)
- **JavaScript**: TemplateManager, WidgetLibrary, UnifiedLoaderManager
- **APIs**: 
  - `/admin/api/page-builder/section-templates`
  - `/admin/api/page-builder/widgets/available`
  - `/admin/api/page-builder/theme/assets`

### Live-Designer Current State
- **Layout**: Single right sidebar (properties/styling)
- **Canvas**: Full-width iframe preview with zoom
- **Missing**: Component library, section templates, widget library

## Implementation Plan

### Phase 1: Layout Structure Updates

#### 1.1 Update Main Layout Structure
**File**: `resources/views/admin/pages/live-designer/show.blade.php`

**Current Structure**:
```html
<div class="designer-content">
    <div class="col" id="canvasContainer">  <!-- Full width canvas -->
    <div class="col-lg-3" id="right-sidebar-container">  <!-- Right sidebar -->
</div>
```

**New Structure**:
```html
<div class="designer-content">
    <!-- Left Sidebar -->
    <div class="col-lg-3 col-md-4 d-none d-lg-block" id="leftSidebarContainer">
        @include('admin.pages.live-designer.components.left-sidebar')
    </div>
    
    <!-- Canvas (Adjustable) -->
    <div class="col" id="canvasContainer">
        <!-- Unified Progress Bar Loader -->
        <div class="unified-page-loader" id="liveDesignerLoader" style="display: none;">
            <div class="progress-bar"></div>
            <div class="loader-message">Loading...</div>
        </div>
        
        <!-- Existing canvas content -->
    </div>
    
    <!-- Right Sidebar -->
    <div class="col-lg-3" id="right-sidebar-container">
        <!-- Existing right sidebar content -->
    </div>
</div>
```

#### 1.2 Create Left Sidebar Component
**File**: `resources/views/admin/pages/live-designer/components/left-sidebar.blade.php`

```html
<div class="designer-left-sidebar h-100 d-flex flex-column" id="left-sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 sidebar-title">
                <i class="ri-stack-line me-2"></i>Components
            </h6>
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="close-left-sidebar">
                <i class="ri-close-line"></i>
            </button>
        </div>
    </div>

    <!-- Sidebar Content -->
    <div class="sidebar-content flex-1 overflow-auto">
        <!-- Page Sections -->
        <div class="accordion" id="leftSidebarAccordion">
            <!-- Section Templates -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="sectionsHeading">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#sectionsCollapse" aria-expanded="true">
                        <i class="ri-layout-grid-line me-2"></i>
                        <span class="sidebar-text">Section Templates</span>
                        <span class="badge bg-primary ms-auto me-2" id="sectionsCount">0</span>
                    </button>
                </h2>
                <div id="sectionsCollapse" class="accordion-collapse collapse show" 
                     data-bs-parent="#leftSidebarAccordion">
                    <div class="accordion-body p-2">
                        <div class="component-grid" id="sectionsGrid">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="small mt-2 mb-0">Loading templates...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Widgets -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="themeWidgetsHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#themeWidgetsCollapse" aria-expanded="false">
                        <i class="ri-puzzle-2-line me-2"></i>
                        <span class="sidebar-text">Widgets</span>
                        <span class="badge bg-success ms-auto me-2" id="widgetsCount">0</span>
                    </button>
                </h2>
                <div id="themeWidgetsCollapse" class="accordion-collapse collapse" 
                     data-bs-parent="#leftSidebarAccordion">
                    <div class="accordion-body p-2">
                        <div class="component-grid" id="themeWidgetsGrid">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="small mt-2 mb-0">Loading widgets...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsed View (Icons Only) -->
    <div class="collapsed-sidebar-content d-none">
        <div class="collapsed-icon-item" data-bs-toggle="tooltip" title="Section Templates">
            <i class="ri-layout-grid-line"></i>
        </div>
        <div class="collapsed-icon-item" data-bs-toggle="tooltip" title="Widgets">
            <i class="ri-puzzle-2-line"></i>
        </div>
    </div>
</div>
```

### Phase 2: CSS Integration

#### 2.1 Add Left Sidebar Styles
**File**: `resources/views/admin/pages/live-designer/show.blade.php` (CSS section)

```css
/* ===== DUAL SIDEBAR LAYOUT STYLES ===== */
.designer-content {
    display: flex;
    height: calc(100vh - 80px); /* Account for toolbar height */
}

/* Left Sidebar Styles */
.designer-left-sidebar {
    width: 280px;
    background: #fff;
    border-right: 1px solid #e9ecef;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-x: hidden;
    overflow-y: auto;
    flex-shrink: 0;
}

.designer-left-sidebar.collapsed {
    width: 70px;
}

.designer-left-sidebar .sidebar-header {
    flex-shrink: 0;
    background: #f8f9fa;
}

.designer-left-sidebar .sidebar-content {
    flex: 1;
    overflow-y: auto;
}

/* Hide text when collapsed */
.designer-left-sidebar.collapsed .sidebar-text,
.designer-left-sidebar.collapsed .badge,
.designer-left-sidebar.collapsed .sidebar-title {
    opacity: 0;
    width: 0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.designer-left-sidebar.collapsed .sidebar-content {
    display: none;
}

.designer-left-sidebar.collapsed .collapsed-sidebar-content {
    display: block !important;
    padding: 1rem 0;
}

/* Collapsed Icons */
.collapsed-icon-item {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    margin: 0.5rem auto;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #6c757d;
}

.collapsed-icon-item:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

/* Component Grid Styles */
.component-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.template-item,
.widget-item {
    position: relative;
    background: #fff;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    padding: 0.75rem;
    cursor: grab;
    transition: all 0.2s ease;
    user-select: none;
}

.template-item:hover,
.widget-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.15);
    transform: translateY(-1px);
}

.template-item:active,
.widget-item:active {
    cursor: grabbing;
    transform: translateY(0);
}

.template-item .item-icon,
.widget-item .item-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #f8f9fa;
    border-radius: 6px;
    color: #0d6efd;
    font-size: 16px;
    flex-shrink: 0;
    margin-bottom: 0.5rem;
}

.template-item .item-name,
.widget-item .item-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #2d3748;
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.template-item .item-description,
.widget-item .item-description {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.3;
}

/* Canvas adjustments for dual sidebar */
#canvasContainer {
    flex: 1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 300px;
    position: relative;
}

/* Right sidebar adjustments */
#right-sidebar-container {
    width: 280px;
    flex-shrink: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#right-sidebar-container.collapsed {
    width: 0;
    overflow: hidden;
}

/* Responsive behavior */
@media (max-width: 1199.98px) {
    /* Hide left sidebar on smaller screens initially */
    #leftSidebarContainer {
        display: none !important;
    }
}

@media (max-width: 991.98px) {
    /* Mobile overlay behavior */
    .designer-left-sidebar {
        position: fixed;
        left: -280px;
        top: 80px; /* Account for toolbar */
        height: calc(100vh - 80px);
        z-index: 1050;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .designer-left-sidebar.show {
        left: 0;
    }
    
    /* Mobile overlay for left sidebar */
    .left-sidebar-overlay {
        position: fixed;
        top: 80px;
        left: 0;
        width: 100%;
        height: calc(100vh - 80px);
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1049;
        display: none;
    }
    
    .left-sidebar-overlay.show {
        display: block;
    }
    
    /* Adjust right sidebar for mobile */
    #right-sidebar-container {
        width: 280px;
    }
}

/* Accordion customizations */
.designer-left-sidebar .accordion-button {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    border: none;
    background: transparent;
    box-shadow: none;
}

.designer-left-sidebar .accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.designer-left-sidebar .accordion-button::after {
    margin-left: auto;
}

.designer-left-sidebar .accordion-body {
    padding: 0.5rem;
}

/* Loading states */
.component-grid .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}

/* Smooth transitions */
* {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
```

#### 2.2 Copy Unified Loader Styles
Copy the unified loader styles from page-builder's show.blade.php:

```css
/* ===== UNIFIED PROGRESS BAR LOADER STYLES ===== */
.unified-page-loader {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: #f8f9fa;
    z-index: 9999;
    overflow: hidden;
    transition: opacity 0.3s ease;
}

.unified-page-loader .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997, #17a2b8);
    background-size: 200% 100%;
    animation: loading 2s ease-in-out infinite;
    border-radius: 0;
    transition: width 0.3s ease;
    width: 0%;
}

.unified-page-loader.error .progress-bar {
    background: linear-gradient(90deg, #dc3545, #e74c3c);
    animation: error-pulse 1s ease-in-out infinite;
}

.unified-page-loader .progress-bar.complete {
    background: #28a745;
    animation: none;
}

.unified-page-loader .loader-message {
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    background: rgba(255, 255, 255, 0.9);
    padding: 2px 8px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

@keyframes error-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
```

### Phase 3: JavaScript Module Integration

#### 3.1 Copy JavaScript Modules
**New Files to Create**:

1. **Copy UnifiedLoaderManager**:
   - Source: `public/assets/admin/js/page-builder/unified-loader-manager.js`
   - Destination: `public/assets/admin/js/live-designer/unified-loader-manager.js`

2. **Adapt TemplateManager**:
   - Source: `public/assets/admin/js/page-builder/template-manager.js`
   - Destination: `public/assets/admin/js/live-designer/template-manager.js`

3. **Adapt WidgetLibrary**:
   - Source: `public/assets/admin/js/page-builder/widget-library.js`
   - Destination: `public/assets/admin/js/live-designer/widget-library.js`

4. **Create LeftSidebarManager**:
   - New file: `public/assets/admin/js/live-designer/left-sidebar-manager.js`

#### 3.2 LiveDesigner TemplateManager Adaptations
**File**: `public/assets/admin/js/live-designer/template-manager.js`

Key changes needed:
```javascript
class LiveDesignerTemplateManager {
    constructor(apiUrl, csrfToken, livePreview, unifiedLoader = null) {
        this.apiUrl = apiUrl;
        this.csrfToken = csrfToken;
        this.livePreview = livePreview; // Reference to live preview instance
        this.unifiedLoader = unifiedLoader;
        this.templates = new Map();
        
        // Change API endpoint to live-designer specific
        this.endpoints = {
            templates: `${apiUrl}/live-designer/section-templates`,
            createSection: `${apiUrl}/live-designer/sections/from-template`
        };
    }
    
    // Override to work with live-preview instead of GridStack
    async handleTemplateClick(templateData) {
        try {
            if (this.unifiedLoader) {
                this.unifiedLoader.show('createSection', 'Creating section from template...', 10);
            }
            
            // Create section via live-designer API
            const response = await fetch(this.endpoints.createSection, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    template_key: templateData.key,
                    page_id: this.livePreview.options.pageId
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                
                if (this.unifiedLoader) {
                    this.unifiedLoader.setProgress(80);
                    this.unifiedLoader.updateMessage('Refreshing preview...');
                }
                
                // Refresh the live preview iframe
                await this.livePreview.refreshPreview();
                
                if (this.unifiedLoader) {
                    this.unifiedLoader.setProgress(100);
                    this.unifiedLoader.hide('createSection');
                }
                
                console.log('✅ Section created from template');
            } else {
                throw new Error('Failed to create section from template');
            }
        } catch (error) {
            console.error('❌ Error creating section:', error);
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('createSection', 'Failed to create section');
            }
        }
    }
    
    // Remove drag functionality (use click-to-add instead)
    setupDragAndDrop() {
        // For live-preview, we'll use click-to-add instead of drag & drop
        // This is simpler and works better with iframe-based preview
        console.log('📱 Using click-to-add for live preview (drag disabled)');
    }
}
```

#### 3.3 LiveDesigner WidgetLibrary Adaptations
**File**: `public/assets/admin/js/live-designer/widget-library.js`

Similar adaptations for widget library:
```javascript
class LiveDesignerWidgetLibrary {
    constructor(apiUrl, csrfToken, livePreview, unifiedLoader = null) {
        this.apiUrl = apiUrl;
        this.csrfToken = csrfToken;
        this.livePreview = livePreview;
        this.unifiedLoader = unifiedLoader;
        
        this.endpoints = {
            widgets: `${apiUrl}/live-designer/widgets/available`
        };
    }
    
    // Adapt widget rendering for live-preview context
    renderWidgets(widgets) {
        const container = document.getElementById('themeWidgetsGrid');
        if (!container) return;
        
        let html = '';
        widgets.forEach(widget => {
            html += `
                <div class="widget-item" data-widget-id="${widget.id}" data-widget-slug="${widget.slug}">
                    <div class="item-icon">
                        <i class="${widget.icon || 'ri-puzzle-line'}"></i>
                    </div>
                    <div class="item-name">${widget.name}</div>
                    <div class="item-description">${widget.description || ''}</div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Setup click handlers for widgets
        this.setupWidgetClickHandlers();
    }
    
    setupWidgetClickHandlers() {
        const container = document.getElementById('themeWidgetsGrid');
        if (!container) return;
        
        container.addEventListener('click', (e) => {
            const widgetItem = e.target.closest('.widget-item');
            if (widgetItem) {
                const widgetId = widgetItem.dataset.widgetId;
                const widgetSlug = widgetItem.dataset.widgetSlug;
                
                // Open widget modal (similar to existing live-preview behavior)
                if (this.livePreview.widgetModalManager) {
                    this.livePreview.widgetModalManager.openForNewWidget(widgetSlug);
                }
            }
        });
    }
}
```

#### 3.4 LeftSidebarManager
**File**: `public/assets/admin/js/live-designer/left-sidebar-manager.js`

```javascript
class LeftSidebarManager {
    constructor(livePreview) {
        this.livePreview = livePreview;
        this.unifiedLoader = new UnifiedLoaderManager();
        this.templateManager = null;
        this.widgetLibrary = null;
        this.isCollapsed = localStorage.getItem('liveDesignerLeftSidebarCollapsed') === 'true';
        this.isMobile = window.innerWidth < 992;
        
        // DOM elements
        this.sidebar = document.getElementById('left-sidebar');
        this.container = document.getElementById('leftSidebarContainer');
        this.toggleBtn = null;
        
        console.log('🏗️ Left Sidebar Manager initialized');
    }
    
    async init() {
        try {
            // Initialize managers
            await this.initializeManagers();
            
            // Setup UI
            this.setupToggleFunctionality();
            this.setupResponsiveBehavior();
            this.setupMobileOverlay();
            
            // Apply initial state
            this.applyInitialState();
            
            console.log('✅ Left Sidebar Manager ready');
        } catch (error) {
            console.error('❌ Error initializing left sidebar:', error);
        }
    }
    
    async initializeManagers() {
        const apiUrl = '/admin/api';
        const csrfToken = this.livePreview.options?.csrfToken || window.csrfToken;
        
        // Initialize Template Manager
        this.templateManager = new LiveDesignerTemplateManager(
            apiUrl, csrfToken, this.livePreview, this.unifiedLoader
        );
        
        // Initialize Widget Library
        this.widgetLibrary = new LiveDesignerWidgetLibrary(
            apiUrl, csrfToken, this.livePreview, this.unifiedLoader
        );
        
        // Load content
        await Promise.all([
            this.templateManager.init(),
            this.widgetLibrary.init()
        ]);
    }
    
    setupToggleFunctionality() {
        // Create toggle button for toolbar
        this.createToggleButton();
        
        // Setup toggle event
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => {
                this.toggle();
            });
        }
    }
    
    createToggleButton() {
        const toolbar = document.querySelector('.page-title-box');
        if (!toolbar) return;
        
        const leftSection = toolbar.querySelector('.d-flex.align-items-center');
        if (!leftSection) return;
        
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'btn btn-outline-secondary me-3';
        toggleBtn.id = 'toggle-left-sidebar';
        toggleBtn.title = 'Toggle Components Panel';
        toggleBtn.innerHTML = '<i class="ri-layout-left-2-line"></i>';
        
        // Insert after existing toggle button
        const existingToggle = leftSection.querySelector('#toggle-right-sidebar');
        if (existingToggle) {
            existingToggle.parentNode.insertBefore(toggleBtn, existingToggle.nextSibling);
        } else {
            leftSection.appendChild(toggleBtn);
        }
        
        this.toggleBtn = toggleBtn;
    }
    
    setupResponsiveBehavior() {
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        this.handleResize(); // Initial check
    }
    
    setupMobileOverlay() {
        // Create mobile overlay
        const overlay = document.createElement('div');
        overlay.className = 'left-sidebar-overlay';
        overlay.id = 'left-sidebar-overlay';
        document.body.appendChild(overlay);
        
        // Close on overlay click
        overlay.addEventListener('click', () => {
            this.hide();
        });
        
        // Close button in sidebar
        const closeBtn = document.getElementById('close-left-sidebar');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.hide();
            });
        }
    }
    
    toggle() {
        if (this.isMobile) {
            this.sidebar.classList.contains('show') ? this.hide() : this.show();
        } else {
            this.isCollapsed ? this.expand() : this.collapse();
        }
    }
    
    collapse() {
        if (this.isMobile) return;
        
        this.sidebar.classList.add('collapsed');
        this.isCollapsed = true;
        localStorage.setItem('liveDesignerLeftSidebarCollapsed', 'true');
        
        // Update toggle button icon
        if (this.toggleBtn) {
            this.toggleBtn.querySelector('i').className = 'ri-layout-left-line';
        }
        
        console.log('📱 Left sidebar collapsed');
    }
    
    expand() {
        if (this.isMobile) return;
        
        this.sidebar.classList.remove('collapsed');
        this.isCollapsed = false;
        localStorage.setItem('liveDesignerLeftSidebarCollapsed', 'false');
        
        // Update toggle button icon
        if (this.toggleBtn) {
            this.toggleBtn.querySelector('i').className = 'ri-layout-left-2-line';
        }
        
        console.log('📱 Left sidebar expanded');
    }
    
    show() {
        if (!this.isMobile) return;
        
        this.sidebar.classList.add('show');
        document.getElementById('left-sidebar-overlay').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    hide() {
        if (!this.isMobile) return;
        
        this.sidebar.classList.remove('show');
        document.getElementById('left-sidebar-overlay').classList.remove('show');
        document.body.style.overflow = '';
    }
    
    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth < 992;
        
        if (wasMobile !== this.isMobile) {
            // Reset states when switching between mobile/desktop
            this.sidebar.classList.remove('show', 'collapsed');
            document.getElementById('left-sidebar-overlay').classList.remove('show');
            document.body.style.overflow = '';
            
            if (!this.isMobile && this.isCollapsed) {
                this.sidebar.classList.add('collapsed');
            }
        }
    }
    
    applyInitialState() {
        if (!this.isMobile && this.isCollapsed) {
            this.collapse();
        }
    }
    
    // Public API
    getTemplateManager() {
        return this.templateManager;
    }
    
    getWidgetLibrary() {
        return this.widgetLibrary;
    }
    
    refresh() {
        // Refresh sidebar content
        if (this.templateManager) {
            this.templateManager.init();
        }
        if (this.widgetLibrary) {
            this.widgetLibrary.init();
        }
    }
}

// Export for global use
window.LeftSidebarManager = LeftSidebarManager;
```

### Phase 4: API Integration

#### 4.1 Create Live-Designer API Routes
**File**: `routes/admin.php`

Add new routes for live-designer:
```php
// Live Designer API Routes
Route::prefix('live-designer')->group(function() {
    Route::get('section-templates', [LiveDesignerController::class, 'getSectionTemplates']);
    Route::get('widgets/available', [LiveDesignerController::class, 'getAvailableWidgets']);
    Route::post('sections/from-template', [LiveDesignerController::class, 'createSectionFromTemplate']);
    Route::get('theme/assets', [LiveDesignerController::class, 'getThemeAssets']);
});
```

#### 4.2 Create LiveDesignerController Methods
**File**: `app/Http/Controllers/Admin/LiveDesignerController.php`

```php
public function getSectionTemplates()
{
    try {
        // Reuse the same logic from PageBuilderController
        $pageBuilderController = new PageBuilderController();
        return $pageBuilderController->getSectionTemplates();
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load section templates',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getAvailableWidgets()
{
    try {
        // Reuse the same logic from PageBuilderController
        $pageBuilderController = new PageBuilderController();
        return $pageBuilderController->getAvailableWidgets();
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load widgets',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function createSectionFromTemplate(Request $request)
{
    try {
        $templateKey = $request->input('template_key');
        $pageId = $request->input('page_id');
        
        // Reuse the same logic from PageBuilderController
        $pageBuilderController = new PageBuilderController();
        return $pageBuilderController->createSection($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create section from template',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getThemeAssets()
{
    try {
        // Reuse the same logic from PageBuilderController
        $pageBuilderController = new PageBuilderController();
        return $pageBuilderController->getThemeAssets();
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load theme assets',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

### Phase 5: Integration with Live-Preview

#### 5.1 Update Live-Preview Initialization
**File**: `resources/views/admin/pages/live-designer/show.blade.php`

Update the JavaScript section to include new modules and initialization:

```html
@section('js')
<!-- Existing scripts -->
<script src="{{ asset('assets/admin/js/live-designer/live-preview.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/device-preview.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/update-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-form-manager.js') }}"></script>

<!-- NEW: Left sidebar modules -->
<script src="{{ asset('assets/admin/js/live-designer/unified-loader-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/template-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-library.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/left-sidebar-manager.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Initialize managers (existing)
        const livePreview = new LivePreview({...});
        const updateManager = new UpdateManager('{{ $apiBaseUrl }}', '{{ csrf_token() }}');
        const widgetFormManager = new WidgetFormManager(livePreview, updateManager);
        const devicePreview = new DevicePreview(document.getElementById('preview-container'));
        
        // NEW: Initialize left sidebar
        const leftSidebarManager = new LeftSidebarManager(livePreview);
        await leftSidebarManager.init();
        
        // Setup device preview
        devicePreview.setupKeyboardShortcuts();
        
        // Global references
        window.livePreview = livePreview;
        window.updateManager = updateManager;
        window.widgetFormManager = widgetFormManager;
        window.devicePreview = devicePreview;
        window.leftSidebarManager = leftSidebarManager; // NEW
        
        // Enhanced LivePreview with left sidebar reference
        livePreview.leftSidebarManager = leftSidebarManager;
        
        // Setup device and sidebar controls (existing + new)
        setupDeviceAndSidebarControls();
        
        console.log('✅ Live Designer with dual sidebars initialized');
        
    } catch (error) {
        console.error('❌ Live Designer initialization failed:', error);
    }
});

function setupDeviceAndSidebarControls() {
    // Existing device preview controls
    document.querySelectorAll('input[name="preview-mode"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                // ... existing device switching logic
                
                // Show/hide zoom controls
                const zoomControls = document.getElementById('zoom-controls');
                if (zoomControls) {
                    zoomControls.style.display = device === 'desktop' ? 'flex' : 'none';
                }
            }
        });
    });
    
    // Right sidebar toggle (existing)
    const toggleRightSidebar = document.getElementById('toggle-right-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    // ... existing right sidebar logic
    
    // Left sidebar toggle is handled by LeftSidebarManager
}

// Add refresh method to LivePreview for integration
LivePreview.prototype.refreshPreview = function() {
    return new Promise((resolve) => {
        const iframe = this.options.previewIframe;
        if (iframe) {
            const currentSrc = iframe.src;
            const separator = currentSrc.includes('?') ? '&' : '?';
            iframe.src = `${currentSrc}${separator}_t=${Date.now()}`;
            
            iframe.onload = () => {
                console.log('✅ Live preview refreshed');
                resolve();
            };
        } else {
            resolve();
        }
    });
};
</script>
@endsection
```

#### 5.2 Add Script Loading Order
Update the script loading section to include all required dependencies:

```html
<!-- Dependencies (order matters) -->
<script src="{{ asset('assets/admin/js/live-designer/unified-loader-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/live-preview.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/template-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-library.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/left-sidebar-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/device-preview.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/update-manager.js') }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/widget-form-manager.js') }}"></script>
```

### Phase 6: Testing and Refinements

#### 6.1 Testing Checklist
- [ ] Left sidebar loads templates and widgets
- [ ] Left sidebar collapse/expand works on desktop
- [ ] Left sidebar mobile overlay works
- [ ] Dual sidebar responsive behavior
- [ ] Template click creates sections
- [ ] Widget click opens widget modal
- [ ] Canvas adjusts width with sidebar states
- [ ] Zoom controls work with left sidebar
- [ ] Right sidebar properties still work
- [ ] Mobile experience is usable

#### 6.2 Performance Optimizations
- Lazy load sidebar content
- Debounce resize handlers
- Optimize CSS transitions
- Cache API responses where appropriate

#### 6.3 Error Handling
- API failure fallbacks
- Loading state management
- Network offline handling
- User feedback for errors

## Implementation Timeline

### Week 1: Foundation
- **Day 1-2**: Phase 1 (Layout Structure)
- **Day 3-4**: Phase 2 (CSS Integration)
- **Day 5**: Testing basic layout and responsiveness

### Week 2: JavaScript Integration
- **Day 1-2**: Phase 3.1-3.2 (Copy and adapt modules)
- **Day 3-4**: Phase 3.3-3.4 (LeftSidebarManager)
- **Day 5**: Testing JavaScript functionality

### Week 3: API and Full Integration
- **Day 1-2**: Phase 4 (API Integration)
- **Day 3-4**: Phase 5 (Live-Preview Integration)
- **Day 5**: End-to-end testing

### Week 4: Polish and Optimization
- **Day 1-3**: Phase 6 (Testing and refinements)
- **Day 4-5**: Performance optimization and bug fixes

## Risk Mitigation

### High Risk Items
1. **Dual sidebar responsive behavior**: Complex CSS and JavaScript coordination
2. **API compatibility**: Ensuring live-designer APIs work with existing data
3. **Canvas width calculations**: Proper iframe scaling with dynamic sidebar widths

### Mitigation Strategies
1. **Progressive Enhancement**: Start with desktop, add mobile responsiveness incrementally
2. **API Reuse**: Leverage existing PageBuilderController methods to minimize API changes
3. **Fallback Mechanisms**: Click-to-add as fallback when drag-and-drop fails

## Success Criteria

### Functional Requirements
- ✅ Left sidebar displays section templates and widgets
- ✅ Templates can be added to create new sections
- ✅ Widgets can be selected to open widget modal
- ✅ Dual sidebar collapse/expand functionality
- ✅ Responsive behavior on mobile devices
- ✅ Canvas adjusts properly with sidebar state changes

### Performance Requirements
- ✅ Left sidebar loads within 2 seconds
- ✅ Smooth animations (60fps) for collapse/expand
- ✅ No visual layout shift during sidebar operations
- ✅ Memory usage remains reasonable with all components loaded

### User Experience Requirements
- ✅ Intuitive dual sidebar navigation
- ✅ Consistent with page-builder component library UX
- ✅ Proper mobile touch interaction
- ✅ Clear visual feedback for all user actions
- ✅ Accessible keyboard navigation support

## Post-Implementation

### Maintenance
- Monitor API performance with increased usage
- Gather user feedback on dual sidebar workflow
- Update component library as new widgets/templates are added

### Future Enhancements
- Drag and drop from sidebar to specific iframe areas
- Template and widget search/filtering
- Custom component categories
- Favorites/recently used components
- Component preview on hover

---

**Document Version**: 1.0  
**Created**: 2025-08-29  
**Last Updated**: 2025-08-29  
**Status**: Ready for Implementation