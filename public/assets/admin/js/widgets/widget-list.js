// Widget List Handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle widget deletion
    document.querySelectorAll('.delete-widget').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
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
                    form.submit();
                }
            });
        });
    });

    // Handle widget status toggle
    document.querySelectorAll('.widget-status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const form = this.closest('form');
            form.submit();
        });
    });

    // Handle widget reordering
    const widgetList = document.querySelector('.widget-list');
    if (widgetList) {
        new Sortable(widgetList, {
            handle: '.widget-handle',
            animation: 150,
            onEnd: function(evt) {
                const items = evt.to.children;
                const orderData = [];
                
                Array.from(items).forEach(function(item, index) {
                    orderData.push({
                        id: item.dataset.widgetId,
                        order: index
                    });
                });

                // Update order via AJAX
                fetch('/admin/widgets/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ widgets: orderData })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Widget order updated successfully',
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            },
                            buttonsStyling: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update widget order',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                });
            }
        });
    }
});
