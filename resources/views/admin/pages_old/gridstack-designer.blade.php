<!-- GridStack Designer Content -->
<div class="gridstack-designer-container">
    <!-- Designer Toolbar -->
  

    <!-- Designer Layout -->
    <div class="row" id="designerLayout">
        <!-- Left Sidebar (Collapsible) -->
       
        
        <!-- Canvas Area (Full Width) -->
        <div class="" id="canvasContainer">
            @include('admin.pages.designer._canvas_area')
        </div>
    </div>
</div>

<!-- Right Sidebar (Offcanvas) -->
@include('admin.pages.designer._right_sidebar')

<!-- Modals -->
@include('admin.pages.designer._widget_config_modal')
@include('admin.pages.designer._section_templates_modal')
@include('admin.pages.designer._content_selection_modal')
@include('admin.pages.designer._responsive_preview_modal')
@include('admin.pages.designer._delete_confirmation_modal')

<script>
// GridStack designer is now initialized by the main show.blade.php file
// This script only handles sidebar controls and other UI interactions

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar controls only
    initializeSidebarControls();
    
    // Initialize section templates when tab becomes active
    document.addEventListener('shown.bs.tab', function (e) {
        if (e.target.getAttribute('data-bs-target') === '#layout') {
            // Re-initialize section templates when layout tab becomes active
            setTimeout(() => {
                if (!window.SectionTemplatesManager || !window.SectionTemplatesManager.container) {
                    console.log('🔄 Re-initializing Section Templates Manager for layout tab...');
                    window.SectionTemplatesManager = new SectionTemplatesManager();
                }
            }, 200);
        }
    });
    
    // Initialize sidebar toggle controls (left sidebar handled by main show.blade.php)
    function initializeSidebarControls() {
        const toggleRightSidebarBtn = document.getElementById('toggleRightSidebarBtn');
        const fullPreviewBtn = document.getElementById('fullPreviewBtn');
        
        // Toggle right sidebar
        if (toggleRightSidebarBtn) {
            toggleRightSidebarBtn.addEventListener('click', function() {
                const rightSidebar = document.getElementById('rightSidebar');
                if (rightSidebar) {
                    const offcanvas = new bootstrap.Offcanvas(rightSidebar);
                    offcanvas.show();
                }
            });
        }
        
        // Full preview
        if (fullPreviewBtn) {
            fullPreviewBtn.addEventListener('click', function() {
                const previewModal = document.getElementById('responsivePreviewModal');
                if (previewModal) {
                    const modal = new bootstrap.Modal(previewModal);
                    modal.show();
                }
            });
        }
    }
});
</script> 