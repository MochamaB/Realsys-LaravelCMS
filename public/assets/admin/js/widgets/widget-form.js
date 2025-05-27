// Widget Form Handling
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CKEditor for rich text fields
    document.querySelectorAll('.widget-rich-text').forEach(function(element) {
        ClassicEditor
            .create(element)
            .catch(error => {
                console.error(error);
            });
    });

    // Initialize FilePond for image uploads
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginFileValidateType
    );

    document.querySelectorAll('.widget-image-upload').forEach(function(element) {
        FilePond.create(element, {
            allowMultiple: false,
            acceptedFileTypes: ['image/*'],
            server: {
                url: '/admin/media',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }
        });
    });

    // Handle repeater fields
    document.querySelectorAll('.widget-repeater').forEach(function(repeater) {
        const addButton = repeater.querySelector('.add-repeater-item');
        const container = repeater.querySelector('.repeater-items');
        const template = repeater.querySelector('.repeater-template');

        if (addButton && container && template) {
            addButton.addEventListener('click', function() {
                const newItem = template.content.cloneNode(true);
                const index = container.children.length;
                
                // Update IDs and names
                newItem.querySelectorAll('[name]').forEach(function(input) {
                    input.name = input.name.replace('__INDEX__', index);
                    input.id = input.id.replace('__INDEX__', index);
                });

                // Add delete button functionality
                const deleteBtn = newItem.querySelector('.delete-repeater-item');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', function() {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'No, cancel!',
                            customClass: {
                                confirmButton: 'btn btn-danger me-3',
                                cancelButton: 'btn btn-light'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteBtn.closest('.repeater-item').remove();
                            }
                        });
                    });
                }

                container.appendChild(newItem);
            });
        }
    });

    // Form validation
    const form = document.querySelector('#widgetForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            let isValid = true;
            form.querySelectorAll('[required]').forEach(function(input) {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (isValid) {
                form.submit();
            }
        });
    }
});
