/**
 * RealsysCMS Repeater Fields JavaScript
 * Manages the dynamic behavior of repeater fields in content forms
 */

document.addEventListener('DOMContentLoaded', function() {
    initRepeaterFields();

    /**
     * Initialize all repeater fields on the page
     */
    function initRepeaterFields() {
        document.querySelectorAll('.repeater-field').forEach(repeaterField => {
            const fieldId = repeaterField.dataset.fieldId;
            const minItems = parseInt(repeaterField.dataset.minItems || 0);
            const maxItems = parseInt(repeaterField.dataset.maxItems || 10);
            
            const itemsContainer = repeaterField.querySelector('.repeater-items-container');
            const template = repeaterField.querySelector('.repeater-template').innerHTML;
            const addBtn = repeaterField.querySelector('.add-repeater-item');
            
            // Make sure we have at least min items
            const currentItemCount = itemsContainer.querySelectorAll('.repeater-item').length;
            if (currentItemCount < minItems) {
                for (let i = currentItemCount; i < minItems; i++) {
                    addRepeaterItem(itemsContainer, template, i);
                }
            }
            
            // Add button event listener
            addBtn.addEventListener('click', function() {
                const currentItemCount = itemsContainer.querySelectorAll('.repeater-item').length;
                
                if (maxItems > 0 && currentItemCount >= maxItems) {
                    // Show error or disable button if we're at max items
                    alert(`Maximum of ${maxItems} items allowed.`);
                    return;
                }
                
                addRepeaterItem(itemsContainer, template, currentItemCount);
            });
            
            // Item removal and reordering (delegation)
            itemsContainer.addEventListener('click', function(event) {
                const target = event.target;
                
                // Handle remove button clicks
                if (target.closest('.remove-repeater-item')) {
                    const item = target.closest('.repeater-item');
                    const currentItemCount = itemsContainer.querySelectorAll('.repeater-item').length;
                    
                    if (currentItemCount <= minItems) {
                        alert(`Minimum of ${minItems} items required.`);
                        return;
                    }
                    
                    item.remove();
                    renumberRepeaterItems(itemsContainer);
                }
                
                // Handle image removal button clicks
                if (target.closest('.remove-image-btn')) {
                    const button = target.closest('.remove-image-btn');
                    const imageContainer = button.closest('.mb-3');
                    const previewContainer = imageContainer.querySelector('.mb-2');
                    
                    if (previewContainer) {
                        // Add a hidden input to mark the image as removed
                        const hiddenInputs = previewContainer.querySelectorAll('input[type="hidden"]');
                        if (hiddenInputs.length > 0) {
                            // Get the name from the first input and extract the base name
                            const nameAttr = hiddenInputs[0].getAttribute('name');
                            const baseName = nameAttr.substring(0, nameAttr.lastIndexOf('['));
                            
                            // Create a marker input to indicate this image should be removed
                            const removeMarker = document.createElement('input');
                            removeMarker.type = 'hidden';
                            removeMarker.name = baseName + '[_remove]';
                            removeMarker.value = '1';
                            imageContainer.appendChild(removeMarker);
                        }
                        
                        // Hide the preview
                        previewContainer.style.display = 'none';
                        
                        // Make the file input required if the original field was required
                        const fileInput = imageContainer.querySelector('input[type="file"]');
                        if (fileInput && fileInput.hasAttribute('data-required')) {
                            fileInput.setAttribute('required', 'required');
                        }
                        
                        // Change the button text
                        button.textContent = 'Undo Remove';
                        button.classList.remove('btn-outline-danger');
                        button.classList.add('btn-outline-success');
                        button.classList.remove('remove-image-btn');
                        button.classList.add('restore-image-btn');
                    }
                }
                
                // Handle restore image button clicks
                if (target.closest('.restore-image-btn')) {
                    const button = target.closest('.restore-image-btn');
                    const imageContainer = button.closest('.mb-3');
                    const previewContainer = imageContainer.querySelector('.mb-2');
                    
                    if (previewContainer) {
                        // Remove the marker input
                        const removeMarker = imageContainer.querySelector('input[name$="[_remove]"]');
                        if (removeMarker) {
                            removeMarker.remove();
                        }
                        
                        // Show the preview again
                        previewContainer.style.display = 'block';
                        
                        // Make the file input not required again
                        const fileInput = imageContainer.querySelector('input[type="file"]');
                        if (fileInput) {
                            fileInput.removeAttribute('required');
                        }
                        
                        // Change the button text back
                        button.textContent = 'Remove';
                        button.classList.remove('btn-outline-success');
                        button.classList.add('btn-outline-danger');
                        button.classList.remove('restore-image-btn');
                        button.classList.add('remove-image-btn');
                    }
                }
                
                // Handle move up button clicks
                if (target.closest('.move-item-up')) {
                    const item = target.closest('.repeater-item');
                    const prevItem = item.previousElementSibling;
                    
                    if (prevItem && prevItem.classList.contains('repeater-item')) {
                        itemsContainer.insertBefore(item, prevItem);
                        renumberRepeaterItems(itemsContainer);
                    }
                }
                
                // Handle move down button clicks
                if (target.closest('.move-item-down')) {
                    const item = target.closest('.repeater-item');
                    const nextItem = item.nextElementSibling;
                    
                    if (nextItem && nextItem.classList.contains('repeater-item')) {
                        itemsContainer.insertBefore(nextItem, item);
                        renumberRepeaterItems(itemsContainer);
                    }
                }
            });
            
            // Initialize drag-and-drop if SortableJS is available
            if (typeof Sortable !== 'undefined') {
                new Sortable(itemsContainer, {
                    animation: 150,
                    handle: '.card-header',
                    onEnd: function() {
                        renumberRepeaterItems(itemsContainer);
                    }
                });
            }
        });
    }

    /**
     * Add a new repeater item to the container
     * @param {HTMLElement} container - Container for repeater items
     * @param {String} template - HTML template for new items
     * @param {Number} index - Index for the new item
     */
    function addRepeaterItem(container, template, index) {
        // Replace placeholder index with actual index
        const itemHtml = template.replace(/__INDEX__/g, index);
        
        // Create temporary div to parse HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = itemHtml;
        const newItem = tempDiv.firstElementChild;
        
        // Update title to show the item number
        const cardTitle = newItem.querySelector('.card-title');
        if (cardTitle) {
            cardTitle.textContent = `Item #${index + 1}`;
        }
        
        // Convert template inputs to real inputs by moving data-attributes to actual attributes
        newItem.querySelectorAll('.template-input').forEach(input => {
            // Set name attribute from data-name
            if (input.hasAttribute('data-name')) {
                const name = input.getAttribute('data-name').replace(/__INDEX__/g, index);
                input.setAttribute('name', name);
                input.removeAttribute('data-name');
                input.classList.remove('template-input');
            }
            
            // Set required attribute if needed
            if (input.hasAttribute('data-required')) {
                input.setAttribute('required', 'required');
                input.removeAttribute('data-required');
            }
            
            // Set ID attribute if needed
            if (input.hasAttribute('data-id')) {
                const id = input.getAttribute('data-id').replace(/__INDEX__/g, index);
                input.setAttribute('id', id);
                input.removeAttribute('data-id');
            }
        });
        
        // Update labels
        newItem.querySelectorAll('.template-label').forEach(label => {
            if (label.hasAttribute('data-for')) {
                const forAttr = label.getAttribute('data-for').replace(/__INDEX__/g, index);
                label.setAttribute('for', forAttr);
                label.removeAttribute('data-for');
                label.classList.remove('template-label');
            }
        });
        
        // Append the new item to the container
        container.appendChild(newItem);
    }

    /**
     * Renumber repeater items after reordering or removal
     * @param {HTMLElement} container - Container for repeater items
     */
    function renumberRepeaterItems(container) {
        const items = container.querySelectorAll('.repeater-item');
        
        items.forEach((item, index) => {
            // Update title
            const cardTitle = item.querySelector('.card-title');
            if (cardTitle) {
                cardTitle.textContent = `Item #${index + 1}`;
            }
            
            // Update input names with new index
            item.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
                
                // Update IDs for checkboxes and labels
                const id = input.getAttribute('id');
                if (id) {
                    const newId = id.replace(/_\d+_/, `_${index}_`);
                    input.setAttribute('id', newId);
                    
                    // Find and update the corresponding label
                    const label = item.querySelector(`label[for="${id}"]`);
                    if (label) {
                        label.setAttribute('for', newId);
                    }
                }
            });
        });
    }
});
