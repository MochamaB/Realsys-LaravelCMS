<!-- Right Sidebar - Properties Panel (Offcanvas) -->
<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="rightSidebar" data-bs-backdrop="false">
    <div class="offcanvas-header bg-primary p-3">
        <h5 class="offcanvas-title text-white" id="rightSidebarTitle">Properties</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="properties-content">
            <!-- Element Properties -->
            <div class="properties-section" id="elementProperties" style="display: none;">
                <div class="section-header p-3 border-bottom">
                    <h6 class="section-title mb-0">Element Properties</h6>
                </div>
                <div class="section-body p-3">
                    <div id="widgetPropertiesContainer">
                        <!-- Properties loaded dynamically -->
                    </div>
                </div>
            </div>
            
            <!-- Content Management -->
            <div class="properties-section" id="contentManagement" style="display: none;">
                <div class="section-header p-3 border-bottom">
                    <h6 class="section-title mb-0">Content Management</h6>
                </div>
                <div class="section-body p-3">
                    <div id="contentSelectionContainer">
                        <!-- Content selection loaded dynamically -->
                    </div>
                </div>
            </div>
            
            <!-- Style Editor -->
            <div class="properties-section" id="styleEditor" style="display: none;">
                <div class="section-header p-3 border-bottom">
                    <h6 class="section-title mb-0">Style Editor</h6>
                </div>
                <div class="section-body p-3">
                    <div id="styleEditorContainer">
                        <!-- Style editor loaded dynamically -->
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div class="properties-empty" id="propertiesEmpty">
                <div class="text-center text-muted py-4">
                    <i class="ri-cursor-line fs-1"></i>
                    <p>Select a widget or section to edit its properties</p>
                </div>
            </div>
        </div>
    </div>
</div> 