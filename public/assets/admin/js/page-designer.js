document.addEventListener('DOMContentLoaded', function () {
    // Get the page ID from the container data attribute
    const pageId = document.getElementById('gjs').dataset.pageId;
    
    // Show loading indicator
    document.getElementById('gjs').innerHTML = '<div class="text-center p-5"><div class="spinner-border" role="status"></div><p class="mt-3">Loading page content...</p></div>';
    
    // Initialize GrapesJS with our configuration
    const editor = grapesjs.init({
        container: '#gjs',
        height: '80vh',
        width: 'auto',
        // Enable storage manager for autosaving
        storageManager: {
            type: 'remote',
            autosave: true,
            autoload: false, // We'll load content manually
            stepsBeforeSave: 3,
            urlStore: `/admin/api/pages/${pageId}/save-content`,
            urlLoad: `/admin/api/pages/${pageId}/render`,
            params: { _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        },
        // Set up canvas configuration
        canvas: {
            styles: [
                // Include theme CSS here if needed
            ],
            scripts: [
                // Include theme JS here if needed
            ],
        },
        // Set up the panels (toolbar, etc)
        panels: {
            defaults: [
                {
                    id: 'panel-top',
                    el: '.panel-top',
                    buttons: [
                        {
                            id: 'save-btn',
                            className: 'btn-save-design',
                            label: 'Save',
                            command: 'save-design',
                        },
                        {
                            id: 'preview-btn',
                            className: 'btn-preview',
                            label: 'Preview',
                            command: 'preview-design',
                        }
                    ]
                },
                // Other panels as needed
            ],
        },
        // Set up custom commands
        commands: {
            defaults: [
                {
                    id: 'save-design',
                    run: function(editor) {
                        editor.store(res => {
                            console.log('Store callback', res);
                            // Show success message
                            const successMsg = document.createElement('div');
                            successMsg.className = 'alert alert-success alert-dismissible fade show';
                            successMsg.innerHTML = 'Page content saved successfully!';
                            document.querySelector('.gjs-cv-canvas').prepend(successMsg);
                            
                            // Auto-hide after 3 seconds
                            setTimeout(() => {
                                successMsg.remove();
                            }, 3000);
                        });
                    }
                },
                {
                    id: 'preview-design',
                    run: function() {
                        // Open page preview in a new tab
                        window.open(`/preview/${pageId}`, '_blank');
                    }
                }
            ]
        }
    });
    
    // Load content from our API endpoint
    fetch(`/admin/api/pages/${pageId}/render`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading page:', data.error);
                document.getElementById('gjs').innerHTML = 
                    `<div class="alert alert-danger m-3">
                        <strong>Error:</strong> ${data.error}
                     </div>`;
                return;
            }
            
            // Load the HTML into the editor
            editor.setComponents(data.html);
            
            // Setup custom blocks for sections if we have section data
            if (data.page && data.page.sections) {
                // Here we could create custom blocks for each section type
                console.log('Page sections:', data.page.sections);
            }
            
            // Add success message
            console.log('Page loaded successfully');
        })
        .catch(error => {
            console.error('Error fetching page content:', error);
            document.getElementById('gjs').innerHTML = 
                `<div class="alert alert-danger m-3">
                    <strong>Error:</strong> Failed to load page content.
                 </div>`;
        });
    
    // Add event listeners for editor events
    editor.on('component:selected', function(component) {
        console.log('Selected component:', component.toJSON());
        // Here we could customize the style manager based on the selected component
    });
});