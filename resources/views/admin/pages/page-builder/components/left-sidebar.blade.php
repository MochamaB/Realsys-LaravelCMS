<!-- GridStack Page Builder - Left Sidebar -->
<div class="designer-left-sidebar bg-white border-end" id="leftSidebar" style="height: calc(100vh - 100px); overflow-y: auto;border-radius: 0px;">
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
    <div class="sidebar-content">
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
                            <!-- GridStack section templates will be loaded here -->
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading sections...</span>
                                </div>
                                <div class="mt-2 small text-muted">Loading sections...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Widgets Category -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center collapsed" 
                   data-bs-toggle="collapse" 
                   href="#themeWidgetsCollapse" 
                   role="button" 
                   aria-expanded="false" 
                   aria-controls="themeWidgetsCollapse">
                    <i class="ri-palette-line nav-icon me-2"></i>
                    <span class="nav-text">Widgets</span>
                    <i class="ri-arrow-down-s-line ms-auto collapse-icon"></i>
                </a>
                <div class="collapse" id="themeWidgetsCollapse">
                    <div class="nav-item-content p-2">
                        <div class="component-grid" id="themeWidgetsGrid">
                            <!-- GridStack theme widgets will be loaded here -->
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading widgets...</span>
                                </div>
                                <div class="mt-2 small text-muted">Loading widgets...</div>
                            </div>
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
        <div class="collapsed-icon-item" data-target="#themeWidgetsCollapse" title="Widgets">
            <i class="ri-palette-line"></i>
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
}

/* Bootstrap grid override for collapsed sidebar */
#leftSidebarContainer.collapsed {
    flex: 0 0 70px !important;
    max-width: 70px !important;
}

/* Canvas container expansion when sidebar collapsed */
#leftSidebarContainer.collapsed ~ #canvasContainer {
    flex: 1 1 calc(100% - 70px) !important;
    max-width: calc(100% - 70px) !important;
}

/* Smooth transition for canvas container */
#canvasContainer {
    transition: all 0.3s ease;
}

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
    padding: 12px 16px;
    font-size: 14px;
    background: transparent;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.2s ease;
}

.designer-left-sidebar .nav-link:hover {
    background-color: #f8f9fa;
    color: #495057;
}

.designer-left-sidebar .nav-link.active {
    background-color: #e3f2fd;
    color: #1976d2;
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

/* Component grid styling */
.component-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Widget grid styling - stacked for better usability */
#themeWidgetsGrid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

/* Section grid styling - full width stacked */
#sectionsGrid {
    display: flex;
    flex-direction: column;
    gap: 8px;
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

/* Navigation content styling */
.nav-item-content {
    background-color: #fafbfc;
    border-top: 1px solid #e9ecef;
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