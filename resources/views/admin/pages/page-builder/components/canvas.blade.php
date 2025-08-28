

<div id="iframeLoader"
         style="position: absolute; 
                top: 0; left: 0; 
                width: 100%; height: 100%; 
                background: rgba(255, 255, 255, 0.8); 
                display: flex; 
                align-items: top; 
                justify-content: center; 
                z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
        
        <!-- Page Preview Iframe (Main Preview) -->
        
            <iframe 
                id="pagePreviewIframe" 
                src="/admin/api/page-builder/pages/{{ $page->id }}/rendered/iframe"
                style="width: 100%; height: auto; border: none; border-radius: 0px;margin: 0px !important;"
                frameborder="0">
            </iframe>
        
        
        <!-- GridStack Container (for future GridStack implementation) -->
        <div class="grid-stack theme-preview-container" id="gridStackContainer" data-page-id="{{ $page->id ?? '' }}" style="display: none;">
            <!-- Real rendered sections will be added here with GridStack positioning -->
        </div>
        
        <!-- Empty State (when no sections) - Initially hidden -->
        <div class="empty-canvas-state text-center py-5" id="emptyCanvasState" style="display: none;">
            <div class="empty-icon mb-3">
                <i class="ri-layout-grid-line display-1 text-muted"></i>
            </div>
            <h5 class="text-muted">Start Building Your Page</h5>
            <p class="text-muted mb-4">Drag sections from the sidebar or click the button below to get started</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionTemplatesModal">
                <i class="ri-add-line me-2"></i>Add Your First Section
            </button>
        </div>
    


<!-- GridStack Drop Zone Helper -->
<div id="gridstack-drop-preview" style="display: none; position: absolute; background: rgba(0,123,255,0.1); border: 2px dashed #007bff; z-index: 1000;"></div>
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const iframe = document.getElementById("pagePreviewIframe");
    const loader = document.getElementById("iframeLoader");

    iframe.addEventListener("load", function () {
        // Hide loader, show iframe
        loader.style.display = "none";
        iframe.style.display = "block";

        try {
            // Access iframe document
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

            // Remove iframe internal scrollbars
            iframe.style.overflow = "hidden";
            iframeDoc.body.style.overflow = "hidden";

            // Adjust height to fit content
            iframe.style.height = iframeDoc.body.scrollHeight + "px";

        } catch (e) {
            console.warn("Cross-origin restriction: can't access iframe content");
        }
    });
});
</script>
@endpush