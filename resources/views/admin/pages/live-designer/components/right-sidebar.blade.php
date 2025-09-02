<!-- Live Designer - Right Sidebar -->
<div class="designer-right-sidebar bg-white border-start" id="rightSidebar" style="height: calc(100vh - 100px); overflow-y: auto;">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3 border-bottom d-flex align-items-center justify-content-between" style="background-color: #f7b84b;">
        <h6 class="mb-0">
            <span class="sidebar-title" style="color: white;">Widget Properties</span>
        </h6>
        <button class="btn btn-outline-secondary border-0" id="rightSidebarToggleBtn" title="Collapse Sidebar">
            <i class="ri-arrow-right-line" style="color: white; font-size: 18px;"></i>
        </button>
    </div>
    
    <!-- Sidebar Content -->
    <div class="sidebar-content">
        <div class="sidebar-content-inner p-3" id="widget-editor-container">
            <div class="no-selection text-center py-5">
                <div class="mb-3">
                    <i class="ri-cursor-line" style="font-size: 3rem; color: #f7b84b;"></i>
                </div>
                <h5 class="mb-3">Select a Widget</h5>
                <p class="text-muted mb-0">Click on any widget in the preview to edit its properties and settings</p>
            </div>
        </div>
    </div>
    
    <!-- Collapsed state icons (shown when sidebar is collapsed) -->
    <div class="sidebar-collapsed-icons d-none">
        <div class="collapsed-icon-item" title="Widget Properties">
            <i class="ri-settings-line"></i>
        </div>
    </div>
</div>

<style>
/* Right Sidebar Styling - Match Left Sidebar Structure */
.designer-right-sidebar {
    width: 280px;
    transition: all 0.3s ease;
    position: relative;
}

.designer-right-sidebar .sidebar-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
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

/* Collapsed state - handled by main CSS */
#right-sidebar-container.collapsed .designer-right-sidebar {
    width: 70px;
    background-color: #f7b84b !important;
}

#right-sidebar-container.collapsed .sidebar-header {
    background-color: #f7b84b;
    color: white;
}

#right-sidebar-container.collapsed .sidebar-header h6,
#right-sidebar-container.collapsed .sidebar-header i {
    color: white !important;
}

#right-sidebar-container.collapsed .sidebar-title {
    display: none;
}

#right-sidebar-container.collapsed .sidebar-content {
    display: none;
}

#right-sidebar-container.collapsed .sidebar-collapsed-icons {
    display: flex !important;
    padding: 10px 0;
}

#right-sidebar-container.collapsed #rightSidebarToggleBtn {
    background-color: transparent !important;
    border: none !important;
    color: white !important;
}

/* Mobile adjustments */
@media (max-width: 991.98px) {
    .designer-right-sidebar {
        position: fixed;
        right: -280px;
        top: 140px;
        z-index: 1050;
        height: calc(100vh - 140px);
        box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    }
    
    .designer-right-sidebar.show {
        right: 0;
    }
    
    .collapsed .designer-right-sidebar {
        right: -70px;
    }
    
    .collapsed .designer-right-sidebar.show {
        right: 0;
        width: 70px;
    }
}
</style>