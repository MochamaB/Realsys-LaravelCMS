// public/assets/admin/js/widget-manager.js

window.WidgetManager = window.WidgetManager || {};

(function() {
    // Store the current widget component for use in save
    let currentWidgetComponent = null;
    let modalInstance = null;

    // Open the widget config modal and populate content types
    window.WidgetManager.openWidgetModal = async function(component) {
        currentWidgetComponent = component;
        const modal = document.getElementById('widgetConfigModal');
        const select = document.getElementById('widgetContentTypeSelect');
        const saveBtn = document.getElementById('saveWidgetConfigBtn');
        const itemsListDiv = document.getElementById('widgetContentItemsList');
        const selectedContentItemInput = document.getElementById('selectedContentItemId');
        
        const widgetSlug = component.get('widgetSlug') || component.get('slug') || component.get('name');
        const widgetId = component.get('widgetId');

        // Set modal title
        const title = modal.querySelector('#widgetConfigModalLabel');
        if (title) {
            const widgetName = component.get('name') || 'Widget';
            title.textContent = `Configure ${widgetName}`;
        }

        // Reset modal state
        select.innerHTML = '<option value="" selected disabled>Select content type</option>';
        select.disabled = true;
        itemsListDiv.innerHTML = '';
        selectedContentItemInput.value = '';
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';

        // Create or get Bootstrap modal instance
        if (modalInstance) {
            modalInstance.dispose();
        }
        modalInstance = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });

        // Show the modal first, then load data
        modalInstance.show();

        // Fetch content types for this widget
        let url = widgetId ? `/admin/api/widgets/${widgetId}/content-types` : `/admin/api/widgets/${widgetSlug}/content-types`;
        try {
            select.innerHTML = '<option value="" selected disabled>Loading content types...</option>';
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            if (!res.ok) throw new Error('Failed to fetch content types');
            const data = await res.json();
            const types = data.content_types || data.data || [];
            
            select.innerHTML = '<option value="" selected disabled>Select content type</option>';
            
            if (types.length === 0) {
                select.innerHTML += '<option disabled>No content types available</option>';
            } else {
                types.forEach(type => {
                    const opt = document.createElement('option');
                    opt.value = type.id;
                    opt.setAttribute('data-slug', type.slug);
                    opt.textContent = type.name + (type.description ? ` - ${type.description}` : '');
                    select.appendChild(opt);
                });
                
                // If only one content type exists, auto-select it
                if (types.length === 1) {
                    select.selectedIndex = 1; // Skip the default "Select" option
                    // Use setTimeout to ensure DOM is ready
                    setTimeout(() => select.dispatchEvent(new Event('change')), 0);
                }
            }
            select.disabled = false;
        } catch (e) {
            select.innerHTML = '<option disabled>Error loading content types</option>';
            select.disabled = true;
        }

        // Listen for content type selection
        select.onchange = async function() {
            const selectedOption = select.options[select.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                itemsListDiv.innerHTML = '';
                selectedContentItemInput.value = '';
                return;
            }
            // Use the ID for the API call
            const contentTypeId = selectedOption.value;
            itemsListDiv.innerHTML = '<div class="text-muted">Loading content items...</div>';
            try {
                let url = `/admin/api/content/${contentTypeId}`;
                let res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });
                if (!res.ok) throw new Error('Failed to fetch content items');
                let data = await res.json();
                let items = data.items || data.data || [];
                if (items.length === 0) {
                    itemsListDiv.innerHTML = '<div class="alert alert-warning">No content items found for this content type.</div>';
                    selectedContentItemInput.value = '';
                } else {
                    // Render as a checkbox list (allowing multiple selections)
                    let html = '<div class="list-group">';
                    items.forEach(item => {
                        const itemId = item.id;
                        const itemTitle = item.title || item.name || 'Untitled';
                        html += `<label class='list-group-item'>
                            <input type='checkbox' name='contentItemCheckbox' value='${itemId}' class='form-check-input me-2'>
                            ${itemTitle} <span class='text-muted'>#${itemId}</span>
                        </label>`;
                    });
                    html += '</div>';
                    itemsListDiv.innerHTML = html;
                    // Add event listeners to checkboxes
                    const checkboxes = itemsListDiv.querySelectorAll("input[type='checkbox'][name='contentItemCheckbox']");
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            // Get all checked values
                            const checkedValues = Array.from(checkboxes)
                                .filter(cb => cb.checked)
                                .map(cb => cb.value);
                            selectedContentItemInput.value = checkedValues.join(',');
                        });
                    });
                    // Auto-select the first item
                    if (checkboxes.length > 0) {
                        checkboxes[0].checked = true;
                        selectedContentItemInput.value = checkboxes[0].value;
                    }
                }
            } catch (e) {
                itemsListDiv.innerHTML = '<div class="alert alert-danger">Error loading content items.</div>';
                selectedContentItemInput.value = '';
            }
        };

        // Save button handler
        saveBtn.onclick = async function() {
            const selectedTypeId = select.value;
            const selectedContentItemIds = selectedContentItemInput.value;
            if (!selectedTypeId) {
                alert('Please select a content type.');
                return;
            }
            if (!selectedContentItemIds) {
                alert('Please select at least one content item.');
                return;
            }
            
            // Disable the save button to prevent multiple clicks
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            
            // Get section ID from the parent section component
            let sectionId = null;
            let currentParent = currentWidgetComponent.parent();
            while (currentParent) {
                const parentAttrs = currentParent.get('attributes') || {};
                if (parentAttrs['data-section-id']) {
                    sectionId = parentAttrs['data-section-id'];
                    break;
                }
                currentParent = currentParent.parent();
            }
            
            if (!sectionId) {
                alert('Could not find section ID. Please try dropping the widget again.');
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                return;
            }
            
            const widgetId = currentWidgetComponent.get('widgetId');
            if (!widgetId) {
                alert('Widget ID not found. Please try dropping the widget again.');
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                return;
            }
            
            // Parse content item IDs (can be comma-separated for multiple items)
            const contentItemIds = selectedContentItemIds.split(',').map(id => parseInt(id.trim()));
            
            // Prepare the payload using content_query field
            const payload = {
                widget_id: widgetId,
                content_query: {
                    content_type_id: parseInt(selectedTypeId),
                    content_item_ids: contentItemIds,
                    limit: contentItemIds.length,
                    order_by: 'created_at',
                    order_direction: 'desc'
                },
                settings: {},
                position: 1
            };
            
            console.log('Saving PageSectionWidget:', payload);
            console.log('Section ID:', sectionId);
            
                            try {
                    const res = await fetch(`/admin/api/sections/${sectionId}/widgets`, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify(payload)
                    });
                
                const data = await res.json();
                
                if (res.ok && data.success) {
                    console.log('Widget saved successfully:', data);
                    // Use proper Bootstrap modal hiding
                    modalInstance.hide();
                } else {
                    console.error('Save failed:', data);
                    alert('Error saving widget: ' + (data.message || 'Unknown error'));
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save';
                }
            } catch (error) {
                console.error('Network error:', error);
                alert('Network error occurred while saving widget.');
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
            }
        };
    };
})(); 