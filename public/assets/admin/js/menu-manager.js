$(document).ready(function() {
    let sortableInstances = [];
    
    // Initialize Sortable for drag and drop functionality
    function initializeSortable() {
        // Clear existing instances
        sortableInstances.forEach(instance => {
            if (instance && instance.destroy) {
                instance.destroy();
            }
        });
        sortableInstances = [];

        // Initialize sortable on all dd-list elements
        $('.dd-list').each(function() {
            const sortable = new Sortable(this, {
                group: 'nested-menu',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                handle: '.dd-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    console.log('Item moved:', evt);
                    // Update positions after drag
                    updateMenuStructure();
                }
            });
            sortableInstances.push(sortable);
        });
    }

    // Function to serialize menu structure like nestable did
    function serializeMenuStructure(list) {
        const items = [];
        $(list).children('.dd-item').each(function(index) {
            const item = {
                id: parseInt($(this).data('id')),
                position: index + 1
            };
            
            const childList = $(this).children('.dd-list');
            if (childList.length > 0) {
                item.children = serializeMenuStructure(childList[0]);
            }
            
            items.push(item);
        });
        return items;
    }

    // Update menu structure after drag and drop
    function updateMenuStructure() {
        const menuData = serializeMenuStructure($('#menu-items-nestable .dd-list')[0]);
        console.log('Updated menu structure:', menuData);
        
        // Store the data for when save button is clicked
        window.currentMenuStructure = menuData;
    }

    // Initialize sortable on page load
    initializeSortable();

    // Expand all menu items
    $('#expand-all').on('click', function() {
        $('.dd-item').each(function() {
            const $item = $(this);
            const $children = $item.children('.dd-list');
            if ($children.length > 0) {
                $children.show();
                $item.removeClass('dd-collapsed');
            }
        });
    });

    // Collapse all menu items
    $('#collapse-all').on('click', function() {
        $('.dd-item').each(function() {
            const $item = $(this);
            const $children = $item.children('.dd-list');
            if ($children.length > 0) {
                $children.hide();
                $item.addClass('dd-collapsed');
            }
        });
    });

    // Handle saving menu structure
    $('#save-menu-order').on('click', function() {
        var menuId = $(this).data('menu-id');
        var menuData = window.currentMenuStructure || serializeMenuStructure($('#menu-items-nestable .dd-list')[0]);
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        // Show loading indicator
        $(this).html('<i class="ri-loader-4-line spin"></i> Saving...');
        var $button = $(this);
        
        $.ajax({
            url: `/admin/menus/${menuId}/items/positions`,
            type: 'POST',
            data: {
                _token: csrfToken,
                items: menuData
            },
            success: function(response) {
                // Show success message
                if (typeof toastr !== 'undefined') {
                    toastr.success('Menu structure has been saved successfully!');
                } else {
                    alert('Menu structure has been saved successfully!');
                }
                
                // Reset button text
                $button.html('<i class="ri-save-line align-middle me-1"></i> Save Order');
            },
            error: function(xhr) {
                // Show error message
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to save menu structure. Please try again.');
                } else {
                    alert('Failed to save menu structure. Please try again.');
                }
                console.error(xhr.responseText);
                
                // Reset button text
                $button.html('<i class="ri-save-line align-middle me-1"></i> Save Order');
            }
        });
    });

    // Handle link type switching
    $('#link_type').change(function() {
        var linkType = $(this).val();
        
        // Hide all fields
        $('.link-type-fields div[id$="-fields"]').addClass('d-none');
        
        // Show selected fields
        if(linkType) {
            $('#' + linkType + '-fields').removeClass('d-none');
            console.log('Showing', '#' + linkType + '-fields');
        }
    });
    
    // Trigger change on page load if a value exists
    if($('#link_type').length) {
        $('#link_type').trigger('change');
    }

    // Handle menu item deletion
    $(document).on('click', '.delete-menu-item', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        
        if (confirm(`Are you sure you want to delete "${title}"? All child items will also be deleted.`)) {
            $('#delete-menu-item-' + id).submit();
        }
    });

    // Reinitialize sortable after AJAX operations (like adding new items)
    window.reinitializeSortable = function() {
        initializeSortable();
    };

    // Initialize Select2 for better dropdown experience if available
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%',
            placeholder: "Select an option"
        });
    }
});