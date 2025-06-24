/**
 * Sortable Manager
 * A reusable utility for handling sortable lists across the application
 */
document.addEventListener('DOMContentLoaded', function() {
    let sortableInstances = [];
    
    /**
     * Initialize all sortable lists on page
     */
    function initializeSortable() {
        // Clean up existing instances
        sortableInstances.forEach(instance => {
            if (instance && instance.destroy) {
                instance.destroy();
            }
        });
        sortableInstances = [];

        // Initialize sortable on all lists with sortable-list class
        document.querySelectorAll('.sortable-list').forEach(list => {
            const id = list.id;
            const group = list.getAttribute('data-group') || 'shared-group';
            const handle = list.getAttribute('data-handle') || '.dd-handle';
            const nestedEnabled = list.getAttribute('data-nested') === 'true';
            
            // Create sortable instance
            const sortable = new Sortable(list, {
                group: nestedEnabled ? { name: group, pull: true, put: true } : group,
                animation: 150,
                handle: handle,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                swapThreshold: 0.65,
                fallbackOnBody: true,
                onEnd: function(evt) {
                    // Trigger custom event that can be listened to by specific implementations
                    const detail = { 
                        from: evt.from, 
                        to: evt.to, 
                        oldIndex: evt.oldIndex, 
                        newIndex: evt.newIndex,
                        item: evt.item
                    };
                    list.dispatchEvent(new CustomEvent('sortableUpdate', { detail }));
                    
                    // Update order numbers if present
                    updateOrderNumbers(evt.to);
                }
            });
            
            sortableInstances.push(sortable);
        });

        // Initialize draggable sources (for items that can be dragged to sortable lists)
        document.querySelectorAll('.sortable-source').forEach(source => {
            const group = source.getAttribute('data-group') || 'shared-group';
            const sortable = new Sortable(source, {
                group: { name: group, pull: 'clone', put: false },
                sort: false,
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onStart: function(evt) {
                    // Mark item as a clone
                    evt.item.setAttribute('data-is-clone', 'true');
                },
                onEnd: function(evt) {
                    // Handle drop events for source items
                    if (evt.to !== evt.from && evt.item.getAttribute('data-is-clone') === 'true') {
                        // A source item was dropped into a target list
                        const detail = { 
                            sourceItem: evt.item,
                            sourceList: evt.from,
                            targetList: evt.to
                        };
                        
                        // Dispatch custom event on both source element and document level (for global listeners)
                        source.dispatchEvent(new CustomEvent('sourceItemDropped', { detail }));
                        document.dispatchEvent(new CustomEvent('sourceItemDropped', { detail }));
                        
                        // Remove clone unless specified otherwise
                        const keepClone = source.getAttribute('data-keep-clone') === 'true';
                        if (!keepClone && evt.item.parentNode) {
                            evt.item.parentNode.removeChild(evt.item);
                        }
                    }
                }
            });
            
            sortableInstances.push(sortable);
        });
    }
    
    /**
     * Update order numbers in a sorted list (if they exist)
     */
    function updateOrderNumbers(list) {
        const orderElements = list.querySelectorAll('.item-order-number');
        orderElements.forEach((el, index) => {
            el.textContent = index + 1;
        });
    }
    
    /**
     * Serialize a sortable list into a structured array
     */
    function serializeSortableList(list) {
        const items = [];
        
        list.querySelectorAll(':scope > .sortable-item').forEach((item, index) => {
            const id = parseInt(item.dataset.id);
            const orderData = {
                id: id,
                position: index + 1
            };
            
            // Check for nested lists
            const nestedList = item.querySelector(':scope > .dd-list');
            if (nestedList) {
                orderData.children = serializeSortableList(nestedList);
            }
            
            items.push(orderData);
        });
        
        return items;
    }

    /**
     * Save sortable order via AJAX
     */
    function saveSortableOrder(listId) {
        const list = document.getElementById(listId);
        if (!list) return;
        
        const saveUrl = list.getAttribute('data-save-url');
        if (!saveUrl) {
            console.error('No save URL provided for sortable list:', listId);
            return;
        }
        
        const orderData = serializeSortableList(list);
        
        // Show saving indicator
        const saveBtn = document.querySelector(`button[data-target="${listId}"]`);
        if (saveBtn) {
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Saving...';
            saveBtn.disabled = true;
        }
        
        // Send the order data to server
        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ items: orderData })
        })
        .then(response => response.json())
        .then(data => {
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="ri-check-line me-1"></i> Saved';
                setTimeout(() => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }, 1500);
            }
            
            // Show success message
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: data.message || "Order updated successfully",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: { background: "#28a745" }
                }).showToast();
            }
        })
        .catch(error => {
            console.error('Error saving sort order:', error);
            
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="ri-error-warning-line me-1"></i> Error';
                setTimeout(() => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }, 2000);
            }
            
            // Show error message
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: "Error saving order",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right", 
                    style: { background: "#dc3545" }
                }).showToast();
            }
        });
    }
    
    // Initialize sortable lists
    initializeSortable();
    
    // Add event listener for save buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.save-sortable-order') || e.target.closest('.save-sortable-order')) {
            const button = e.target.matches('.save-sortable-order') ? e.target : e.target.closest('.save-sortable-order');
            const targetId = button.getAttribute('data-target');
            saveSortableOrder(targetId);
        }
    });
    
    // Listen for dynamic content changes
    const observer = new MutationObserver(function() {
        // Reinitialize sortable when DOM changes that might add new sortable elements
        initializeSortable();
    });
    
    // Start observing for targeted DOM changes
    if (document.getElementById('app')) {
        observer.observe(document.getElementById('app'), { 
            childList: true, 
            subtree: true 
        });
    }
    
    // Make the sortable manager available globally
    window.SortableManager = {
        initialize: initializeSortable,
        serializeList: serializeSortableList,
        saveOrder: saveSortableOrder
    };
});
