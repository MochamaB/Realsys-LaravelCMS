<!-- GrapesJS Designer Content -->
<div class="grapesjs-designer-container" style="height: calc(100vh - 140px); position: relative; width: 100%;">
    <!-- GrapesJS Canvas -->
    <div id="gjs" 
         data-page-id="{{ $page->id }}" 
         style="width: 100%; height: 100%; background: #fff; padding: 0;">
    </div>
</div>

<!-- Hidden elements for GrapesJS panels (now moved to left and right sidebars) -->
<div style="display: none;">
    <!-- Sections will go to left sidebar sectionsGrid -->
    <div id="gjs-blocks-container"></div>
    
    <!-- Theme widgets will go to left sidebar themeWidgetsGrid -->
    <div id="gjs-theme-widgets-container"></div>
    
    <!-- Layers will go to left sidebar pageLayersContainer -->
    <div id="gjs-layers-container"></div>
    
    <!-- Styles and traits will go to right sidebar -->
    <div id="gjs-styles-container"></div>
    <div id="gjs-traits-container"></div>
</div>

<!-- GrapesJS Content Selection Modal (shared with GridStack) -->
@include('admin.pages.designer._content_selection_modal')