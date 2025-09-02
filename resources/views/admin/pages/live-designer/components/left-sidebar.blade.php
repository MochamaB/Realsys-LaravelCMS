<!-- GridStack Live Designer - Left Sidebar -->
<div class="designer-left-sidebar bg-white border-end" id="leftSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3 border-bottom d-flex align-items-center justify-content-between" style="background-color: #099885;">
        <h6 class="mb-0">
            <span class="sidebar-title" style="color: white;">Components Library</span>
        </h6>
        <button class="btn btn-outline-success border-0" id="sidebarToggleBtn" title="Collapse Sidebar">
            <i class="ri-arrow-left-line" style="color: white; font-size: 18px;"></i>
        </button>
    </div>
    
    <!-- Sidebar Content -->
    <div class="sidebar-content flex-1">
        <nav class="nav nav-pills flex-column" id="componentNavigation">
            
            <!-- Sections Category -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center collapsed" 
                   data-bs-toggle="collapse" 
                   href="#sectionsCollapse" 
                   role="button" 
                   aria-expanded="false" 
                   aria-controls="sectionsCollapse">
                    <i class="ri-layout-grid-line nav-icon me-2"></i>
                    <span class="nav-text">Page Sections</span>
                    <i class="ri-arrow-down-s-line ms-auto collapse-icon"></i>
                </a>
                <div class="collapse" id="sectionsCollapse">
                    <div class="nav-item-content p-2">
                        <div class="component-grid" id="sectionsGrid">
                            @if(isset($sectionTemplates) && isset($sectionTemplates['templates']) && count($sectionTemplates['templates']) > 0)
                                @foreach($sectionTemplates['templates'] as $template)
                                    <div class="section-template-item mb-2" 
                                         data-template-key="{{ $template['key'] }}"
                                         data-template-type="{{ $template['type'] }}">
                                        <div class="template-card border rounded p-2 draggable-section">
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $template['icon'] }} me-2 text-primary"></i>
                                                <div class="flex-grow-1">
                                                    <div class="template-name fw-bold">{{ $template['name'] }}</div>
                                                    <small class="text-muted">{{ $template['description'] }}</small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary add-section-btn" 
                                                        data-template-key="{{ $template['key'] }}"
                                                        data-template-type="{{ $template['type'] }}">
                                                    <i class="ri-add-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center p-3">
                                    <i class="ri-inbox-line text-muted mb-2" style="font-size: 2rem;"></i>
                                    <div class="text-muted small">No section templates found</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Widgets Category (From API) -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center collapsed" 
                   data-bs-toggle="collapse" 
                   href="#themeWidgetsCollapse" 
                   role="button" 
                   aria-expanded="false" 
                   aria-controls="themeWidgetsCollapse">
                    <i class="ri-palette-line nav-icon me-2"></i>
                    <span class="nav-text">Theme Widgets</span>
                    <i class="ri-arrow-down-s-line ms-auto collapse-icon"></i>
                </a>
                <div class="collapse" id="themeWidgetsCollapse">
                    <div class="nav-item-content p-2">
                        <!-- Drill-down container -->
                        <div id="widgetDrillDownContainer">
                            
                            <!-- Breadcrumb Navigation -->
                            <div class="drill-down-breadcrumb" id="drillDownBreadcrumb" style="display: none;">
                                <button class="btn btn-sm btn-outline-secondary back-btn" id="drillDownBack">
                                    <i class="ri-arrow-left-line"></i>
                                </button>
                                <span class="breadcrumb-text" id="breadcrumbText"></span>
                            </div>

                            <!-- Widgets View (Default) - Keep Current Grid Structure -->
                            <div class="widgets-view" id="widgetsView">
                                <div class="component-grid" id="themeWidgetsGrid">
                                    @if(isset($themeWidgets) && count($themeWidgets) > 0)
                                    @if(isset($themeWidgets))
                                        {{-- Debug: Check what data we have --}}
                                        <script>
                                            console.log('Server theme widgets data:', @json($themeWidgets));
                                        </script>
                                    @endif
                                        @foreach($themeWidgets as $widget)
                                            <!-- Keep existing widget item structure with drill-down button on right -->
                                            @if($widget['preview_image'])
                                                <div class="theme-widget-item @if($widget['has_content_types']) has-drill-down @endif" 
                                                     data-widget-id="{{ $widget['id'] }}" 
                                                     data-widget-slug="{{ $widget['slug'] }}"
                                                     draggable="true"
                                                     title="{{ $widget['description'] }}">
                                                    <div class="widget-preview">
                                                        <img src="{{ $widget['preview_image'] }}" alt="{{ $widget['name'] }}" 
                                                             onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'{{ $widget['icon'] }}\'></i>';">
                                                    </div>
                                                    <div class="widget-title-row d-flex justify-content-between align-items-center">
                                                        <div class="widget-title">{{ $widget['name'] }}</div>
                                                        @if($widget['has_content_types'] ?? false)
                                                            <button class="drill-down-btn expand-content-types-btn" 
                                                                    data-drill-widget-id="{{ $widget['id'] }}"
                                                                    title="View {{ $widget['content_types_count'] ?? 0 }} content types">
                                                                <i class="ri-arrow-right-line"></i>
                                                                <span class="content-count">{{ $widget['content_types_count'] ?? 0 }}</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="component-item @if($widget['has_content_types']) has-drill-down @endif" 
                                                     data-widget-id="{{ $widget['id'] }}" 
                                                     data-widget-slug="{{ $widget['slug'] }}"
                                                     draggable="true"
                                                     title="{{ $widget['description'] }}">
                                                    <i class="{{ $widget['icon'] }}"></i>
                                                    <div class="label-row d-flex justify-content-between align-items-center">
                                                        <div class="label">{{ $widget['name'] }}</div>
                                                        @if($widget['has_content_types'] ?? false)
                                                            <button class="drill-down-btn expand-content-types-btn" 
                                                                    data-drill-widget-id="{{ $widget['id'] }}"
                                                                    title="View {{ $widget['content_types_count'] ?? 0 }} content types">
                                                                <i class="ri-arrow-right-line"></i>
                                                                <span class="content-count">{{ $widget['content_types_count'] ?? 0 }}</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="text-center p-3">
                                            <i class="ri-palette-line text-muted mb-2" style="font-size: 2rem;"></i>
                                            <div class="text-muted small">No theme widgets available</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Content Types View -->
                            <div class="content-types-view" id="contentTypesView" style="display: none;">
                                <div class="content-types-grid" id="contentTypesGrid">
                                    <!-- Content types will be populated here using template -->
                                </div>
                                
                                <!-- Content Type Card Template (Hidden) -->
                                <div class="content-type-card-template d-none">
                                    <div class="card card-body border border-dashed border-primary">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm flex-shrink-0 me-3">
                                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                                    <i class="content-type-icon-placeholder"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 content-type-name"></h6>
                                                <p class="text-muted mb-0 content-type-count"></p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="ri-arrow-right-line text-muted fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Empty State Template -->
                                <div class="content-types-empty-template d-none">
                                    <div class="text-center p-4">
                                        <div class="avatar-md mx-auto mb-4">
                                            <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                                <i class="ri-folder-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="fs-16 mb-1">No Content Types Available</h5>
                                        <p class="text-muted mb-0">This widget doesn't have any content types configured.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Items View -->
                            <div class="content-items-view" id="contentItemsView" style="display: none;">
                                <div class="content-items-grid" id="contentItemsGrid">
                                    <!-- Content items will be populated here using template -->
                                </div>
                                
                                <!-- Content Item Card Template (Hidden) -->
                                <div class="content-item-card-template d-none">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm flex-shrink-0 me-3">
                                                    <img class="avatar-title rounded content-item-thumbnail" 
                                                         src="" alt="" style="width: 40px; height: 40px; object-fit: cover;">
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h6 class="mb-1 text-truncate content-item-title"></h6>
                                                    <p class="text-muted mb-0">
                                                        <span class="badge bg-success-subtle text-success content-item-status"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Empty State Template -->
                                <div class="content-items-empty-template d-none">
                                    <div class="text-center p-4">
                                        <div class="avatar-md mx-auto mb-4">
                                            <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                                <i class="ri-file-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="fs-16 mb-1">No Content Items Available</h5>
                                        <p class="text-muted mb-0">No content items found for this content type.</p>
                                    </div>
                                </div>
                                
                                <!-- Loading State Template -->
                                <div class="content-loading-template d-none">
                                    <div class="text-center p-4">
                                        <div class="spinner-border text-primary mb-3" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <h6 class="mb-1 loading-message">Loading...</h6>
                                        <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                    </div>
                                </div>
                                
                                <!-- Error State Template -->
                                <div class="content-error-template d-none">
                                    <div class="text-center p-4">
                                        <div class="avatar-md mx-auto mb-4">
                                            <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-2">
                                                <i class="ri-error-warning-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="fs-16 mb-1 text-danger">Error Loading Data</h5>
                                        <p class="text-muted mb-3 error-message">Something went wrong while loading the content.</p>
                                        <button class="btn btn-outline-primary btn-sm" onclick="widgetDrillDown.navigateBack()">
                                            <i class="ri-arrow-left-line me-1"></i>Go Back
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Default Widgets Category (Fallback) -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center collapsed" 
                   data-bs-toggle="collapse" 
                   href="#defaultWidgetsCollapse" 
                   role="button" 
                   aria-expanded="false" 
                   aria-controls="defaultWidgetsCollapse">
                    <i class="ri-apps-line nav-icon me-2"></i>
                    <span class="nav-text">Default Widgets</span>
                    <i class="ri-arrow-down-s-line ms-auto collapse-icon"></i>
                </a>
                <div class="collapse" id="defaultWidgetsCollapse">
                    <div class="nav-item-content p-2">
                        <div class="component-grid" id="defaultWidgetsGrid">
                            @if(isset($defaultWidgets) && count($defaultWidgets) > 0)
                                @foreach($defaultWidgets as $widget)
                                    <div class="component-item" 
                                         data-widget-id="{{ $widget['id'] }}" 
                                         data-widget-slug="{{ $widget['slug'] }}"
                                         data-widget-category="{{ $widget['category'] }}"
                                         draggable="true"
                                         title="{{ $widget['description'] }}">
                                        <i class="{{ $widget['icon'] }}"></i>
                                        <div class="label">{{ $widget['name'] }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center p-3">
                                    <i class="ri-apps-line text-muted mb-2" style="font-size: 2rem;"></i>
                                    <div class="text-muted small">No default widgets available</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- GridStack Templates Category -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center collapsed" 
                   data-bs-toggle="collapse" 
                   href="#templatesCollapse" 
                   role="button" 
                   aria-expanded="false" 
                   aria-controls="templatesCollapse">
                    <i class="ri-file-copy-line nav-icon me-2"></i>
                    <span class="nav-text">Templates</span>
                    <i class="ri-arrow-down-s-line ms-auto collapse-icon"></i>
                </a>
                <div class="collapse" id="templatesCollapse">
                    <div class="nav-item-content p-2">
                        <div class="component-grid" id="templatesGrid">
                            <!-- Section templates will be loaded here -->
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading templates...</span>
                                </div>
                                <div class="mt-2 small text-muted">Loading templates...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
    
    <!-- Collapsed state icons (shown when sidebar is collapsed) -->
    <div class="sidebar-collapsed-icons d-none">
        <div class="collapsed-icon-item" data-target="#sectionsCollapse" title="Sections">
            <i class="ri-layout-grid-line"></i>
        </div>
        <div class="collapsed-icon-item" data-target="#themeWidgetsCollapse" title="Theme Widgets">
            <i class="ri-palette-line"></i>
        </div>
        <div class="collapsed-icon-item" data-target="#defaultWidgetsCollapse" title="Default Widgets">
            <i class="ri-apps-line"></i>
        </div>
        <div class="collapsed-icon-item" data-target="#templatesCollapse" title="Templates">
            <i class="ri-file-copy-line"></i>
        </div>
    </div>
</div>

<style>
/* Left Sidebar Styling */
.designer-left-sidebar {
    width: 280px;
    transition: all 0.3s ease;
    position: relative;
    height: 100%;
    min-height: 100%;
    max-height: none;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

/* Canvas container smooth transition handled by main CSS */

/* Collapsed state - handled by main CSS */
#leftSidebarContainer.collapsed .designer-left-sidebar {
    width: 70px;
    background-color: #099885 !important;
}

#leftSidebarContainer.collapsed .sidebar-header {
    background-color: #099885;
    color: white;
}

#leftSidebarContainer.collapsed .sidebar-header h6,
#leftSidebarContainer.collapsed .sidebar-header i {
    color: white !important;
}

#leftSidebarContainer.collapsed .sidebar-title {
    display: none;
}

#leftSidebarContainer.collapsed .sidebar-content {
    display: none;
}

#leftSidebarContainer.collapsed .sidebar-collapsed-icons {
    display: flex !important;
    padding: 10px 0;
}

#leftSidebarContainer.collapsed #sidebarToggleBtn {
    background-color: transparent !important;
    border: none !important;
    color: white !important;
}

/* Navigation styling */
.designer-left-sidebar .nav-link {
    border: none;
    border-radius: 0;
    color: #6c757d;
    font-weight: 600;
    padding: 12px 16px;
    font-size: 14px;
    background: transparent;
    border: 1px solid #9de1d7;
    transition: all 0.2s ease;
}

.designer-left-sidebar .nav-link:hover {
    background-color: #daf4f0;
    color: #099885;
}

/* Active / expanded */
.designer-left-sidebar .nav-link:not(.collapsed) {
  background: #daf4f0;        /* light green background */
  color: #099885;             /* Bootstrap primary */
  font-weight: 600;
  border: 1px solid #9de1d7;
}

.designer-left-sidebar .nav-link.collapsed .collapse-icon {
    transform: rotate(-90deg);
}

.designer-left-sidebar .nav-link:not(.collapsed) .collapse-icon {
    transform: rotate(0deg);
}

.designer-left-sidebar .collapse-icon {
    transition: transform 0.2s ease;
    font-size: 16px;
}

.designer-left-sidebar .nav-icon {
    font-size: 18px;
    width: 20px;
    text-align: center;
}

/* Collapsed icons styling */
.sidebar-collapsed-icons {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.collapsed-icon-item {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: transparent;
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.collapsed-icon-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
}

.collapsed-icon-item i {
    font-size: 22px;
    color: white;
}

/* Component grid styling - allow natural height expansion */
.component-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: auto;
}

/* Widget list styling - full width stacked */
#themeWidgetsGrid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: auto;
}

/* Default widget grid styling */
#defaultWidgetsGrid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: auto;
}

/* Section grid styling - full width stacked */
#sectionsGrid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: auto;
}

/* Templates grid styling */
#templatesGrid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: auto;
}

.component-item {
    padding: 12px 8px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
}

.component-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    transform: translateY(-1px);
}

.component-item i {
    font-size: 24px;
    color: #6c757d;
    margin-bottom: 4px;
    display: block;
}

.component-item .label {
    font-size: 11px;
    color: #495057;
    line-height: 1.2;
}

/* Navigation content styling - allow natural height expansion */
.nav-item-content {
    background-color: #fafbfc;
    border-top: 1px solid #e9ecef;
    min-height: auto;
    height: auto;
}

/* Theme Widget Items with Preview Images */
.theme-widget-item {
    background: #fff;
    border: 1px solid #cccccc;
    border-radius: 0px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 8px;
}

.theme-widget-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    transform: translateY(-1px);
}

.theme-widget-item.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.theme-widget-item .widget-preview {
    width: 100%;
    height: 80px;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}

.theme-widget-item .widget-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.theme-widget-item .widget-preview i {
    font-size: 2rem;
    color: #6c757d;
}

.theme-widget-item .widget-title {
    font-size: 11px;
    color: #495057;
    line-height: 1.2;
    font-weight: 500;
}

/* Ensure both default and theme widgets can be dragged */
.component-item.dragging,
.theme-widget-item.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .designer-left-sidebar {
        position: fixed;
        left: -280px;
        top: 140px;
        z-index: 1050;
        height: calc(100vh - 140px);
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .designer-left-sidebar.show {
        left: 0;
    }
    
    .collapsed .designer-left-sidebar {
        left: -70px;
    }
    
    .collapsed .designer-left-sidebar.show {
        left: 0;
        width: 70px;
    }
}
</style>