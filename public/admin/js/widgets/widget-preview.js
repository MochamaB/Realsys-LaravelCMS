// Widget Preview Handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle widget type selection
    const widgetTypeSelect = document.querySelector('#widget_type_id');
    const previewContainer = document.querySelector('#widgetPreview');
    
    if (widgetTypeSelect && previewContainer) {
        widgetTypeSelect.addEventListener('change', function() {
            const typeId = this.value;
            if (!typeId) return;

            // Load widget type preview
            fetch(`/admin/widget-types/${typeId}/preview`)
                .then(response => response.text())
                .then(html => {
                    previewContainer.innerHTML = html;
                    
                    // Re-initialize any needed plugins
                    previewContainer.querySelectorAll('.widget-rich-text').forEach(function(element) {
                        ClassicEditor
                            .create(element)
                            .catch(error => {
                                console.error(error);
                            });
                    });

                    previewContainer.querySelectorAll('.widget-image-upload').forEach(function(element) {
                        FilePond.create(element);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    previewContainer.innerHTML = '<div class="alert alert-danger">Failed to load widget preview</div>';
                });
        });
    }

    // Handle live preview updates
    document.querySelectorAll('.widget-field').forEach(function(field) {
        field.addEventListener('input', function() {
            const previewElement = document.querySelector(`[data-preview-for="${this.id}"]`);
            if (previewElement) {
                previewElement.textContent = this.value;
            }
        });
    });
});
