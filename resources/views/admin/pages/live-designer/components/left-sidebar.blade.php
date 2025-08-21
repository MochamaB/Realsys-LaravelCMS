<!-- Live Designer - Left Sidebar -->
<div class="sidebar-container left" id="left-sidebar-container">
    <div class="designer-sidebar left" id="left-sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0">
                <i class="ri-file-list-3-line me-2"></i>
                <span class="sidebar-title">Page Structure</span>
            </h6>
            <button class="btn btn-sm btn-outline-secondary sidebar-collapse-btn" id="collapse-left-sidebar">
                <i class="ri-sidebar-unfold-line"></i>
            </button>
        </div>
        
        <!-- Sidebar Content -->
        <div class="sidebar-content" id="page-structure-container">
            <div class="loading-content text-center py-4">
                <div class="loading-spinner"></div>
                <p class="text-muted mb-0">Loading page structure...</p>
            </div>
        </div>
    </div>
    
    <!-- Collapsed state icons (shown when sidebar is collapsed) -->
    <div class="sidebar-collapsed-icons" style="display: none;">
        <div class="collapsed-icon-item" title="Page Structure">
            <i class="ri-file-list-3-line"></i>
        </div>
    </div>
</div>

<style>
/* Left Sidebar Styling */
.sidebar-container.left {
    transition: all 0.3s ease;
    position: relative;
}

.designer-sidebar.left {
    width: 320px;
    background: #fff;
    border-right: 1px solid #e9ecef;
    height: 100%;
    transition: all 0.3s ease;
    position: relative;
}

/* Collapsed state */
.sidebar-container.left.collapsed .designer-sidebar {
    width: 70px;
}

.sidebar-container.left.collapsed .sidebar-content {
    display: none;
}

.sidebar-container.left.collapsed .sidebar-title {
    display: none;
}

.sidebar-container.left.collapsed .sidebar-collapsed-icons {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    padding: 10px 0;
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fa;
    min-height: 60px;
}

.sidebar-collapse-btn {
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    border: none;
    background: transparent;
    color: #6c757d;
    transition: all 0.2s ease;
}

.sidebar-collapse-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.sidebar-container.left.collapsed .sidebar-collapse-btn {
    transform: rotate(180deg);
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
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s ease;
}

.collapsed-icon-item:hover {
    background-color: #f8f9fa;
    color: #495057;
}

.collapsed-icon-item i {
    font-size: 22px;
}

/* Sidebar Content */
.sidebar-content {
    height: calc(100% - 60px);
    overflow-y: auto;
    padding: 1rem;
}

/* Loading content */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .designer-sidebar.left {
        position: fixed;
        left: -320px;
        top: 0;
        z-index: 1050;
        height: 100vh;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .designer-sidebar.left.show {
        left: 0;
    }
    
    .sidebar-container.left.collapsed .designer-sidebar.left {
        left: -70px;
    }
    
    .sidebar-container.left.collapsed .designer-sidebar.left.show {
        left: 0;
        width: 70px;
    }
}
</style>