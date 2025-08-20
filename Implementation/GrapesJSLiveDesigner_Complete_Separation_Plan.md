# GrapesJS Live Designer - Complete Separation Implementation Plan

## Overview
This plan implements complete separation of GrapesJS live designer into its own independent system with dedicated API controller, view structure, and assets at `/admin/pages/{page}/live-designer`.

## **🎯 Complete Separation Strategy**
- **Dedicated API Controller**: `Api\LiveDesignerController` with only GrapesJS methods
- **Independent View Structure**: `live-designer/` directory with GrapesJS-specific views
- **Separate Assets**: GrapesJS-only JavaScript, CSS, and libraries
- **View-Only Controller**: `LiveDesignerViewController` for view rendering only
- **Extract from Admin**: Move `renderPageContent()` and `savePageContent()` to API layer

## **API Controller Consolidation**

### **Api\LiveDesignerController** (New Unified Controller)

#### **Methods Consolidated from Existing Controllers:**

**From PageSectionController (GrapesJS Methods - 8 methods):**
```php
// Enhanced Section Rendering for GrapesJS Canvas
public function getSectionsWithThemeContext(Page $page)                         // Enhanced sections for GrapesJS with theme rendering
public function renderSectionForCanvas(Section $section, Request $request)     // Section HTML rendering for GrapesJS canvas
public function getThemeWrapperForCanvas()                                     // Theme wrapper for GrapesJS canvas context
public function renderFullPagePreview(Page $page)                              // Complete page preview for GrapesJS
public function generateCanvasWrapper(Page $page)                              // GrapesJS canvas wrapper with theme
public function renderWidgetWithThemeContext(Widget $widget, Request $request) // Widget rendering with theme context
public function generateFullPageHtml(Page $page)                               // Complete page HTML generation
public function getThemeWrapperTest()                                          // Theme wrapper testing for GrapesJS
```

**From WidgetController (GrapesJS Methods - 12 methods):**
```php
// Enhanced Widget System for GrapesJS
public function renderWidgetPreviewForCanvas(Widget $widget, Request $request) // Enhanced widget preview with CSS scoping
public function getEnhancedWidgetBlocks()                                      // Widget blocks for GrapesJS block manager
public function enhanceWidgetPreview(Widget $widget, $html, $css)             // Apply GrapesJS-compatible enhancements
public function getThemeAssetsForPreview()                                     // Theme assets for GrapesJS preview
public function convertSchemaToGrapesJSTraits(Widget $widget)                 // Convert widget schemas to GrapesJS traits
public function mapFieldTypeToTrait($fieldType)                               // Map field types to GrapesJS traits
public function getPreviewErrorResponse($error)                               // Error handling for GrapesJS preview

// Content Integration for GrapesJS Canvas
public function renderWidgetWithContentForCanvas(Widget $widget, Request $request)  // Live preview with content
public function getWidgetContentOptionsForCanvas(Widget $widget)              // Content options for widgets
public function renderContentWithWidgetForCanvas(Request $request)            // Content-widget preview
public function prepareWidgetDataWithContentForCanvas($widget, $contentItem)  // Data preparation for canvas
public function extractContentItemDataForCanvas($contentItem)                 // Content data extraction for canvas
```

**From SectionSchemaController (All Methods - 7 methods):**
```php
// Section Schema Management for GrapesJS Block Manager
public function getPageSectionSchemas(Page $page)                             // Section schemas for GrapesJS blocks
public function getSectionSchema(Section $section)                            // Individual section schema for GrapesJS
public function getAvailableSectionTypes()                                    // Section types for GrapesJS block manager
public function createNewSectionSchema(Request $request)                      // Create new section schemas for GrapesJS
public function validateSectionSchema(Request $request)                       // Schema validation for GrapesJS
public function getPageSectionStats(Page $page)                               // Section statistics for GrapesJS
public function clearSchemaCache()                                            // Cache management for schemas
```

**From ThemeController (GrapesJS Methods - 8 methods):**
```php
// Theme Integration for GrapesJS Canvas
public function getCanvasStyles()                                              // CSS compilation and scoping for GrapesJS
public function getCanvasScripts()                                             // JavaScript compilation for GrapesJS
public function getCanvasSpecificStyles()                                      // GrapesJS-specific CSS adjustments
public function applyCSSScoping($css, $scope)                                 // CSS scoping to prevent GrapesJS conflicts
public function wrapJavaScriptForCanvas($js)                                  // JavaScript wrapper for GrapesJS safety
public function getWidgetAssetsForCanvas()                                     // Widget-specific assets for GrapesJS
public function getActiveThemeAssets()                                         // Basic theme assets (shared)
public function collectWidgetSpecificAssets($widgets)                         // Widget asset collection for canvas
```

**From Admin\PageController (Extracted Methods - 2 methods):**
```php
// Page Content Management for GrapesJS (EXTRACT from Admin controller)
public function renderPageContent(Page $page)                                 // Render page content for GrapesJS (move from Admin)
public function savePageContent(Page $page, Request $request)                 // Save GrapesJS content (move from Admin)
```

**New Methods for GrapesJS (4 methods):**
```php
// Additional GrapesJS-specific methods
public function loadPageComponents(Page $page)                                // Load page components for GrapesJS
public function validatePageContent(Request $request)                         // Content validation for GrapesJS
public function getPageAssetsForCanvas(Page $page)                           // Page-specific assets for canvas
public function optimizeCanvasPerformance(Page $page)                        // Performance optimization for GrapesJS
```

### **New API Routes for GrapesJS Live Designer:**
```php
// Replace existing mixed routes with GrapesJS-specific routes
Route::prefix('api/live-designer')->middleware('admin.auth')->group(function () {
    // Page Content Management (GrapesJS Core)
    Route::get('/pages/{page}/render', [LiveDesignerController::class, 'renderPageContent']);
    Route::post('/pages/{page}/save', [LiveDesignerController::class, 'savePageContent']);
    Route::get('/pages/{page}/components', [LiveDesignerController::class, 'loadPageComponents']);
    Route::post('/pages/{page}/validate', [LiveDesignerController::class, 'validatePageContent']);
    
    // Enhanced Section Rendering (GrapesJS Canvas)
    Route::get('/pages/{page}/sections', [LiveDesignerController::class, 'getSectionsWithThemeContext']);
    Route::post('/sections/{section}/render', [LiveDesignerController::class, 'renderSectionForCanvas']);
    Route::get('/pages/{page}/full-preview', [LiveDesignerController::class, 'renderFullPagePreview']);
    Route::get('/pages/{page}/canvas-wrapper', [LiveDesignerController::class, 'generateCanvasWrapper']);
    
    // Enhanced Widget System (GrapesJS Components)
    Route::get('/widgets/enhanced-blocks', [LiveDesignerController::class, 'getEnhancedWidgetBlocks']);
    Route::post('/widgets/{widget}/preview', [LiveDesignerController::class, 'renderWidgetPreviewForCanvas']);
    Route::post('/widgets/{widget}/render-with-content', [LiveDesignerController::class, 'renderWidgetWithContentForCanvas']);
    Route::get('/widgets/{widget}/content-options', [LiveDesignerController::class, 'getWidgetContentOptionsForCanvas']);
    Route::post('/content/render-with-widget', [LiveDesignerController::class, 'renderContentWithWidgetForCanvas']);
    Route::get('/widgets/{widget}/traits', [LiveDesignerController::class, 'convertSchemaToGrapesJSTraits']);
    
    // Section Schema Management (GrapesJS Block Manager)
    Route::get('/pages/{page}/section-schemas', [LiveDesignerController::class, 'getPageSectionSchemas']);
    Route::get('/sections/{section}/schema', [LiveDesignerController::class, 'getSectionSchema']);
    Route::get('/section-types', [LiveDesignerController::class, 'getAvailableSectionTypes']);
    Route::post('/section-schemas', [LiveDesignerController::class, 'createNewSectionSchema']);
    Route::post('/section-schemas/validate', [LiveDesignerController::class, 'validateSectionSchema']);
    
    // Theme Integration (GrapesJS Canvas Assets)
    Route::get('/theme/canvas-styles', [LiveDesignerController::class, 'getCanvasStyles']);
    Route::get('/theme/canvas-scripts', [LiveDesignerController::class, 'getCanvasScripts']);
    Route::get('/theme/wrapper', [LiveDesignerController::class, 'getThemeWrapperForCanvas']);
    Route::get('/theme/widget-assets', [LiveDesignerController::class, 'getWidgetAssetsForCanvas']);
    Route::get('/pages/{page}/assets', [LiveDesignerController::class, 'getPageAssetsForCanvas']);
});
```

## **View Structure - Complete Separation**

### **New View Directory Structure:**
```
resources/views/admin/pages/live-designer/
├── show.blade.php                              # Main GrapesJS interface (three-column layout)
├── layouts/
│   └── live-designer-layout.blade.php         # GrapesJS-specific full-screen layout
├── components/
│   ├── toolbar.blade.php                      # GrapesJS toolbar (undo/redo, device preview, save)
│   ├── left-sidebar.blade.php                 # Component library & layers panel
│   ├── canvas.blade.php                       # GrapesJS canvas container
│   └── right-sidebar.blade.php                # Properties panel (styles, traits)
├── panels/
│   ├── blocks-panel.blade.php                 # Block manager panel (sections & widgets)
│   ├── layers-panel.blade.php                 # Layer manager panel (component hierarchy)
│   ├── styles-panel.blade.php                 # Style manager panel (CSS properties)
│   └── traits-panel.blade.php                 # Trait manager panel (component settings)
├── modals/
│   ├── content-selection.blade.php            # Content selection modal for widgets
│   ├── asset-manager.blade.php                # Asset management modal (images, files)
│   └── responsive-preview.blade.php           # Responsive preview modal
└── partials/
    ├── component-library.blade.php            # Component library with enhanced widgets
    ├── widget-blocks.blade.php                # Widget blocks for GrapesJS
    └── canvas-wrapper.blade.php               # Canvas wrapper with theme context
```

### **Main GrapesJS Interface:**
**File:** `resources/views/admin/pages/live-designer/show.blade.php`
```blade
@extends('admin.pages.live-designer.layouts.live-designer-layout')

@section('page-title', 'Live Designer: ' . $page->title)

@section('css')
<!-- GrapesJS Designer CSS -->
<link href="{{ asset('assets/admin/libs/grapesjs/dist/css/grapes.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/live-designer/live-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/live-designer/canvas-styles.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/live-designer/sidebar-layout.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/live-designer/enhanced-widgets.css') }}" rel="stylesheet" />
@endsection

@section('js')
<!-- GrapesJS Libraries -->
<script src="{{ asset('assets/admin/libs/grapesjs/dist/grapes.min.js') }}"></script>

<!-- GrapesJS Live Designer JS -->
<script src="{{ asset('assets/admin/js/live-designer/api/live-designer-api.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/component-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/canvas-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/sidebar-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/enhanced-widgets.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/live-designer/live-designer-main.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="row h-100">
    <div class="col-12 p-0">
        @include('admin.pages.live-designer.components.toolbar')
    </div>
</div>

<div class="row h-100 flex-fill">
    <!-- Left Sidebar: Component Library & Layers -->
    <div class="col-lg-3 col-md-4 p-0" id="leftSidebarContainer">
        @include('admin.pages.live-designer.components.left-sidebar')
    </div>
    
    <!-- Main Canvas Area -->
    <div class="col flex-fill p-0" id="canvasContainer">
        @include('admin.pages.live-designer.components.canvas')
    </div>
    
    <!-- Right Sidebar: Properties Panel -->
    <div class="col-lg-3 col-md-4 p-0" id="rightSidebarContainer">
        @include('admin.pages.live-designer.components.right-sidebar')
    </div>
</div>

<!-- Mobile Right Sidebar (Offcanvas) -->
<div class="d-lg-none">
    @include('admin.pages.live-designer.components.right-sidebar')
</div>

<!-- Modals -->
@include('admin.pages.live-designer.modals.content-selection')
@include('admin.pages.live-designer.modals.asset-manager')
@include('admin.pages.live-designer.modals.responsive-preview')
@endsection

@push('scripts')
<script>
// GrapesJS Live Designer - API-Driven Architecture
class GrapesJSLiveDesignerAPI {
    constructor() {
        this.apiBase = '{{ $apiBaseUrl }}';
        this.pageId = {{ $page->id }};
        this.csrfToken = '{{ csrf_token() }}';
    }
    
    // Page content management
    async loadPageContent() {
        return await this.apiCall('GET', `/pages/${this.pageId}/render`);
    }
    
    async savePageContent(content) {
        return await this.apiCall('POST', `/pages/${this.pageId}/save`, content);
    }
    
    async loadPageComponents() {
        return await this.apiCall('GET', `/pages/${this.pageId}/components`);
    }
    
    // Enhanced widget system
    async getEnhancedWidgetBlocks() {
        return await this.apiCall('GET', '/widgets/enhanced-blocks');
    }
    
    async renderWidgetPreview(widgetId, settings) {
        return await this.apiCall('POST', `/widgets/${widgetId}/preview`, settings);
    }
    
    async renderWidgetWithContent(widgetId, contentData) {
        return await this.apiCall('POST', `/widgets/${widgetId}/render-with-content`, contentData);
    }
    
    async getWidgetTraits(widgetId) {
        return await this.apiCall('GET', `/widgets/${widgetId}/traits`);
    }
    
    // Section schema management
    async getPageSectionSchemas() {
        return await this.apiCall('GET', `/pages/${this.pageId}/section-schemas`);
    }
    
    async getSectionTypes() {
        return await this.apiCall('GET', '/section-types');
    }
    
    async createSectionSchema(schemaData) {
        return await this.apiCall('POST', '/section-schemas', schemaData);
    }
    
    // Theme integration
    async getCanvasStyles() {
        return await this.apiCall('GET', '/theme/canvas-styles');
    }
    
    async getCanvasScripts() {
        return await this.apiCall('GET', '/theme/canvas-scripts');
    }
    
    async getThemeWrapper() {
        return await this.apiCall('GET', '/theme/wrapper');
    }
    
    async getPageAssets() {
        return await this.apiCall('GET', `/pages/${this.pageId}/assets`);
    }
    
    // Utility method for API calls
    async apiCall(method, endpoint, data = null) {
        const url = `${this.apiBase}${endpoint}`;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`API call failed: ${response.statusText}`);
        }
        return await response.json();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize API-driven GrapesJS live designer
    window.liveDesignerAPI = new GrapesJSLiveDesignerAPI();
    
    // Initialize GrapesJS Live Designer
    if (window.LiveDesignerMain) {
        window.liveDesigner = new LiveDesignerMain({
            api: window.liveDesignerAPI,
            pageId: {{ $page->id }},
            containerId: 'gjs'
        });
        
        window.liveDesigner.init();
    }
});
</script>
@endpush
@endsection
```

### **GrapesJS Layout:**
**File:** `resources/views/admin/pages/live-designer/layouts/live-designer-layout.blade.php`
```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('page-title') - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">
    
    <!-- Bootstrap & Icons -->
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/admin/css/icons.min.css') }}" rel="stylesheet" />
    
    @yield('css')
    
    <!-- App CSS -->
    <link href="{{ asset('assets/admin/css/app.min.css') }}" rel="stylesheet" />
    
    <style>
    /* Full-screen GrapesJS live designer layout */
    body { overflow: hidden; }
    #layout-wrapper { height: 100vh; display: flex; flex-direction: column; }
    .main-content { flex: 1; margin-left: 0; overflow: hidden; }
    .page-content { padding: 0; height: 100%; display: flex; flex-direction: column; }
    .container-fluid { max-width: none; padding: 0; height: 100%; display: flex; flex-direction: column; }
    
    /* Three-column layout for GrapesJS */
    .live-designer-toolbar { height: 60px; flex-shrink: 0; background: #fff; border-bottom: 1px solid #dee2e6; }
    .live-designer-content { flex: 1; display: flex; overflow: hidden; }
    .live-designer-sidebar { width: 300px; background: #f8f9fa; overflow-y: auto; }
    .live-designer-canvas { flex: 1; background: #fff; overflow: hidden; }
    
    /* GrapesJS canvas styling */
    #gjs { height: 100% !important; width: 100% !important; }
    .gjs-cv-canvas { background: #fff !important; }
    .gjs-frame { border: 1px solid #dee2e6 !important; }
    
    /* Sidebar panels */
    .sidebar-panel { background: #fff; border-bottom: 1px solid #dee2e6; }
    .sidebar-panel-header { background: #f8f9fa; padding: 10px 15px; font-weight: 600; border-bottom: 1px solid #dee2e6; }
    .sidebar-panel-content { padding: 15px; }
    
    /* Enhanced widget blocks */
    .gjs-block { background: #fff !important; border: 1px solid #dee2e6 !important; border-radius: 6px; margin: 5px; }
    .gjs-block:hover { border-color: #007bff !important; }
    .gjs-block-label { font-size: 12px; padding: 5px; }
    </style>
</head>

<body>
    <div id="layout-wrapper">
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
    
    @yield('js')
    @stack('scripts')
</body>
</html>
```

### **GrapesJS Canvas Component:**
**File:** `resources/views/admin/pages/live-designer/components/canvas.blade.php`
```blade
<div class="live-designer-canvas">
    <!-- GrapesJS Canvas Container -->
    <div id="gjs" 
         data-page-id="{{ $page->id }}" 
         style="height: 100%; width: 100%;">
        <!-- Canvas content will be loaded by GrapesJS -->
    </div>
</div>

<!-- Hidden containers for GrapesJS panels (moved to sidebars) -->
<div style="display: none;">
    <div id="gjs-blocks-container"></div>
    <div id="gjs-layers-container"></div>
    <div id="gjs-styles-container"></div>
    <div id="gjs-traits-container"></div>
</div>
```

## **JavaScript Architecture - GrapesJS-Specific**

### **File Structure:**
```
public/assets/admin/js/live-designer/
├── live-designer-main.js                      # Main GrapesJS controller
├── component-manager.js                       # Component management & selection
├── canvas-manager.js                          # Canvas integration & theme
├── sidebar-manager.js                         # Three-column layout management
├── enhanced-widgets.js                        # Enhanced widget system
└── api/
    └── live-designer-api.js                   # API integration wrapper
```

### **Main Controller:**
**File:** `public/assets/admin/js/live-designer/live-designer-main.js`
```javascript
/**
 * GrapesJS Live Designer - Main Controller
 * 
 * Orchestrates GrapesJS editor, enhanced widgets, theme integration,
 * and three-column layout for the dedicated GrapesJS live designer.
 */
class LiveDesignerMain {
    constructor(options) {
        this.api = options.api;
        this.pageId = options.pageId;
        this.containerId = options.containerId;
        
        this.editor = null;
        this.componentManager = null;
        this.canvasManager = null;
        this.sidebarManager = null;
        this.enhancedWidgets = null;
        
        this.isInitialized = false;
        this.currentPage = null;
        
        console.log('🎨 GrapesJS Live Designer initialized');
    }
    
    async init() {
        try {
            // Load initial data
            await this.loadPageData();
            
            // Initialize GrapesJS editor
            await this.initializeGrapesJS();
            
            // Initialize managers
            this.componentManager = new ComponentManager(this.editor, this.api);
            this.canvasManager = new CanvasManager(this.editor, this.api);
            this.sidebarManager = new SidebarManager(this.editor);
            this.enhancedWidgets = new EnhancedWidgets(this.editor, this.api);
            
            // Setup integrations
            await this.setupThemeIntegration();
            await this.setupEnhancedWidgets();
            await this.setupSidebarPanels();
            
            // Setup event listeners
            this.setupEventListeners();
            
            this.isInitialized = true;
            console.log('✅ GrapesJS Live Designer ready');
            
        } catch (error) {
            console.error('❌ Failed to initialize GrapesJS Live Designer:', error);
        }
    }
    
    async loadPageData() {
        try {
            const response = await this.api.loadPageContent();
            if (response.success) {
                this.currentPage = response.page;
            }
        } catch (error) {
            console.error('Failed to load page data:', error);
        }
    }
    
    async initializeGrapesJS() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            throw new Error('GrapesJS container not found');
        }
        
        // Load theme styles and scripts
        const themeAssets = await this.api.getPageAssets();
        
        this.editor = grapesjs.init({
            container: `#${this.containerId}`,
            fromElement: false,
            width: 'auto',
            height: '100%',
            
            // Storage configuration
            storageManager: {
                type: 'remote',
                stepsBeforeSave: 1,
                urlStore: this.api.apiBase + `/pages/${this.pageId}/save`,
                urlLoad: this.api.apiBase + `/pages/${this.pageId}/render`,
                params: {
                    '_token': this.api.csrfToken
                },
                headers: {
                    'X-CSRF-TOKEN': this.api.csrfToken,
                    'Accept': 'application/json'
                }
            },
            
            // Asset manager configuration
            assetManager: {
                assets: themeAssets.assets || [],
                uploadText: 'Drop files here or click to upload',
                addBtnText: 'Add Asset',
                customFetch: async (url, options) => {
                    return await fetch(url, {
                        ...options,
                        headers: {
                            ...options.headers,
                            'X-CSRF-TOKEN': this.api.csrfToken
                        }
                    });
                }
            },
            
            // Canvas configuration
            canvas: {
                styles: themeAssets.styles || [],
                scripts: themeAssets.scripts || []
            },
            
            // Panel configuration (panels will be moved to sidebars)
            panels: {
                defaults: []
            },
            
            // Block manager will be populated with enhanced widgets
            blockManager: {
                appendTo: '#gjs-blocks-container'
            },
            
            // Layer manager
            layerManager: {
                appendTo: '#gjs-layers-container'
            },
            
            // Style manager
            styleManager: {
                appendTo: '#gjs-styles-container',
                sectors: [{
                    name: 'General',
                    buildProps: ['float', 'display', 'position', 'top', 'right', 'left', 'bottom']
                }, {
                    name: 'Typography',
                    buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing', 'color', 'line-height', 'text-align', 'text-decoration', 'text-shadow']
                }, {
                    name: 'Dimension',
                    buildProps: ['width', 'height', 'max-width', 'max-height', 'margin', 'padding']
                }, {
                    name: 'Background',
                    buildProps: ['background-color', 'background-image', 'background-size', 'background-position']
                }, {
                    name: 'Border',
                    buildProps: ['border', 'border-radius', 'box-shadow']
                }]
            },
            
            // Trait manager
            traitManager: {
                appendTo: '#gjs-traits-container'
            },
            
            // Device manager for responsive design
            deviceManager: {
                devices: [{
                    name: 'Desktop',
                    width: ''
                }, {
                    name: 'Tablet',
                    width: '768px',
                    widthMedia: '992px'
                }, {
                    name: 'Mobile',
                    width: '320px',
                    widthMedia: '768px'
                }]
            }
        });
        
        console.log('🎨 GrapesJS editor initialized');
    }
    
    async setupThemeIntegration() {
        try {
            // Load canvas styles and scripts
            const canvasStyles = await this.api.getCanvasStyles();
            const canvasScripts = await this.api.getCanvasScripts();
            
            // Inject theme styles into canvas
            if (canvasStyles.css) {
                this.editor.addComponents(`<style>${canvasStyles.css}</style>`);
            }
            
            // Setup canvas wrapper
            const wrapper = await this.api.getThemeWrapper();
            if (wrapper.html) {
                this.editor.getWrapper().set('content', wrapper.html);
            }
            
            console.log('🎨 Theme integration complete');
            
        } catch (error) {
            console.error('Failed to setup theme integration:', error);
        }
    }
    
    async setupEnhancedWidgets() {
        try {
            // Load enhanced widget blocks
            const widgetBlocks = await this.api.getEnhancedWidgetBlocks();
            
            if (widgetBlocks.success && widgetBlocks.blocks) {
                const blockManager = this.editor.BlockManager;
                
                // Add each enhanced widget as a block
                widgetBlocks.blocks.forEach(block => {
                    blockManager.add(block.id, {
                        label: block.label,
                        content: block.content,
                        category: block.category || 'Widgets',
                        attributes: { class: 'enhanced-widget-block' },
                        media: block.media || '<i class="ri-puzzle-line"></i>'
                    });
                });
            }
            
            console.log('🧩 Enhanced widgets loaded');
            
        } catch (error) {
            console.error('Failed to setup enhanced widgets:', error);
        }
    }
    
    setupSidebarPanels() {
        // Move GrapesJS panels to sidebar containers
        setTimeout(() => {
            // Move blocks to left sidebar
            const blocksContainer = document.getElementById('blocksPanel');
            if (blocksContainer) {
                const blocksPanel = document.getElementById('gjs-blocks-container');
                if (blocksPanel) {
                    blocksContainer.appendChild(blocksPanel);
                }
            }
            
            // Move layers to left sidebar
            const layersContainer = document.getElementById('layersPanel');
            if (layersContainer) {
                const layersPanel = document.getElementById('gjs-layers-container');
                if (layersPanel) {
                    layersContainer.appendChild(layersPanel);
                }
            }
            
            // Move styles to right sidebar
            const stylesContainer = document.getElementById('stylesPanel');
            if (stylesContainer) {
                const stylesPanel = document.getElementById('gjs-styles-container');
                if (stylesPanel) {
                    stylesContainer.appendChild(stylesPanel);
                }
            }
            
            // Move traits to right sidebar
            const traitsContainer = document.getElementById('traitsPanel');
            if (traitsContainer) {
                const traitsPanel = document.getElementById('gjs-traits-container');
                if (traitsPanel) {
                    traitsContainer.appendChild(traitsPanel);
                }
            }
        }, 1000);
    }
    
    setupEventListeners() {
        // Component selection events
        this.editor.on('component:selected', (component) => {
            this.handleComponentSelection(component);
        });
        
        this.editor.on('component:deselected', () => {
            this.handleComponentDeselection();
        });
        
        // Auto-save functionality
        this.editor.on('storage:store', (data) => {
            console.log('💾 Content auto-saved');
        });
        
        // Canvas update events
        this.editor.on('canvas:update', () => {
            this.canvasManager.handleCanvasUpdate();
        });
    }
    
    handleComponentSelection(component) {
        if (this.sidebarManager) {
            this.sidebarManager.showPropertiesPanel();
        }
        
        if (this.componentManager) {
            this.componentManager.handleSelection(component);
        }
        
        console.log('🎯 Component selected:', component.get('tagName'));
    }
    
    handleComponentDeselection() {
        if (this.componentManager) {
            this.componentManager.handleDeselection();
        }
        
        console.log('🎯 Component deselected');
    }
    
    async saveContent() {
        try {
            const html = this.editor.getHtml();
            const css = this.editor.getCss();
            const components = JSON.stringify(this.editor.getComponents());
            const styles = JSON.stringify(this.editor.getStyle());
            
            const response = await this.api.savePageContent({
                html,
                css,
                components,
                styles
            });
            
            if (response.success) {
                console.log('✅ Content saved successfully');
                // Show success notification
            }
            
        } catch (error) {
            console.error('❌ Failed to save content:', error);
            // Show error notification
        }
    }
}

// Global initialization
window.LiveDesignerMain = LiveDesignerMain;
window.ComponentManager = ComponentManager;
window.CanvasManager = CanvasManager;
window.SidebarManager = SidebarManager;
window.EnhancedWidgets = EnhancedWidgets;
```

## **View-Only Controller**

### **File:** `app/Http/Controllers/Admin/LiveDesignerViewController.php`
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;

class LiveDesignerViewController extends Controller
{
    /**
     * Show GrapesJS live designer interface
     * 
     * This controller ONLY handles view rendering for the GrapesJS live designer.
     * All business logic is handled by Api\LiveDesignerController.
     */
    public function show(Page $page)
    {
        return view('admin.pages.live-designer.show', [
            'page' => $page,
            'apiBaseUrl' => '/admin/api/live-designer'
        ]);
    }
}
```

## **Extract Methods from Admin Controller**

### **Remove from Admin\PageController:**
```php
// REMOVE these methods from app/Http/Controllers/Admin/PageController.php:
// - renderPageContent() (Line 382)
// - savePageContent() (Line 436)

// These methods will be moved to Api\LiveDesignerController
```

## **Implementation Steps**

### **Phase 1: Extract & Create API Controller (3-4 days)**
1. Move `renderPageContent()` and `savePageContent()` from Admin to API
2. Create `Api\LiveDesignerController.php` with consolidated methods
3. Test all API endpoints independently
4. Update API routes for GrapesJS

### **Phase 2: Create View Structure (3-4 days)**
1. Create complete `live-designer/` view directory
2. Build three-column GrapesJS layout
3. Create dedicated panels and components
4. Build canvas integration with theme

### **Phase 3: Create JavaScript Assets (4-5 days)**
1. Build modular JavaScript architecture for GrapesJS
2. Create API integration layer
3. Implement enhanced widget system
4. Build component and canvas managers
5. Create three-column sidebar manager

### **Phase 4: Integration & Testing (2-3 days)**
1. Create view controller
2. Test complete GrapesJS workflow
3. Performance optimization
4. Update designer selection modal

## **Files Created (25+ files)**

### **API Controller:**
- `app/Http/Controllers/Api/LiveDesignerController.php`

### **View Controller:**
- `app/Http/Controllers/Admin/LiveDesignerViewController.php`

### **Views (15 files):**
- `resources/views/admin/pages/live-designer/show.blade.php`
- `resources/views/admin/pages/live-designer/layouts/live-designer-layout.blade.php`
- 4 component files, 4 panel files, 3 modal files, 3 partial files

### **JavaScript (6 files):**
- Complete modular JavaScript architecture for GrapesJS

### **CSS (4 files):**
- GrapesJS-specific styling separated from other systems

### **Route Updates:**
- New API routes in `routes/admin.php`
- New view route for live designer
- Remove methods from Admin controller

## **Success Criteria**

✅ **Complete Independence**: No shared dependencies with GridStack system
✅ **Dedicated API**: All GrapesJS methods consolidated in one controller  
✅ **Real-Time Updates**: Content saving/loading with immediate feedback
✅ **Enhanced Widgets**: Advanced widget system with schemas and traits
✅ **Performance**: 60%+ faster loading with GrapesJS-only assets
✅ **Professional Interface**: Three-column layout with component library and properties

This complete separation creates a professional, feature-rich GrapesJS live designer optimized for visual content creation with enhanced widget systems and theme integration.