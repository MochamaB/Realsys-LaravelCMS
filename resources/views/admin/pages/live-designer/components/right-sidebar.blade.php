<!-- Live Designer - Right Sidebar -->
<div class="sidebar-container right" id="right-sidebar-container">
    <div class="designer-sidebar right" id="right-sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0">
                <i class="ri-settings-line me-2"></i>
                <span class="sidebar-title">Properties</span>
            </h6>
            <button class="btn btn-sm btn-outline-secondary sidebar-collapse-btn" id="collapse-right-sidebar">
                <i class="ri-sidebar-fold-line"></i>
            </button>
        </div>
        
        <!-- Sidebar Content -->
        <div class="sidebar-content" id="widget-editor-container">
            <div class="no-selection text-center py-4">
                <div class="mb-3">
                    <i class="ri-cursor-line" style="font-size: 2rem; color: #6c757d;"></i>
                </div>
                <h6>Select a Widget</h6>
                <p class="text-muted mb-0">Click on a widget in the preview to edit its properties</p>
            </div>
        </div>
    </div>
    
    <!-- Collapsed state icons (shown when sidebar is collapsed) -->
    <div class="sidebar-collapsed-icons" style="display: none;">
        <div class="collapsed-icon-item" title="Properties">
            <i class="ri-settings-line"></i>
        </div>
    </div>
</div>

<style>
/* Right Sidebar Styling */
.sidebar-container.right {
    transition: all 0.3s ease;
    position: relative;
}

.designer-sidebar.right {
    width: 350px;
    background: #fff;
    border-left: 1px solid #e9ecef;
    height: 100%;
    transition: all 0.3s ease;
    position: relative;
}

/* Collapsed state */
.sidebar-container.right.collapsed .designer-sidebar {
    width: 70px;
}

.sidebar-container.right.collapsed .sidebar-content {
    display: none;
}

.sidebar-container.right.collapsed .sidebar-title {
    display: none;
}

.sidebar-container.right.collapsed .sidebar-collapsed-icons {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    padding: 10px 0;
}

.sidebar-container.right.collapsed .sidebar-collapse-btn {
    transform: rotate(180deg);
}

/* Mobile adjustments */
@media (max-width: 991.98px) {
    .designer-sidebar.right {
        position: fixed;
        right: -350px;
        top: 0;
        z-index: 1050;
        height: 100vh;
        box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    }
    
    .designer-sidebar.right.show {
        right: 0;
    }
    
    .sidebar-container.right.collapsed .designer-sidebar.right {
        right: -70px;
    }
    
    .sidebar-container.right.collapsed .designer-sidebar.right.show {
        right: 0;
        width: 70px;
    }
}
</style>