/**
 * Template Designer with GridStack - Improved Version
 */
document.addEventListener('DOMContentLoaded', function() {
    // Log to verify script is running
    console.log('Template designer script loaded');
    
    // Check if grid element exists
    const gridElement = document.getElementById('main-grid-stack');
    if (!gridElement) {
        console.error('Grid element not found!');
        return;
    }
    
    // Initialize GridStack
    const grid = GridStack.init({
        column: 12,
        cellHeight: 60,
        float: false,
        disableDrag: false,
        disableResize: false,
        animate: true,
        margin: 10
    }, '#main-grid-stack');

    // Helper to get CSRF token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Function to create section content
    function createSectionContent(sectionData = {}) {
        const div = document.createElement('div');
        div.className = 'grid-stack-item-content template-section-content';
        
        const sectionName = sectionData.name || 'New Section';
        const sectionType = sectionData.section_type || 'full-width';
        const columnLayout = sectionData.column_layout || '12';
        const isLoading = sectionData.isLoading || false;
        
        div.innerHTML = `
            <div class="section-label">${sectionName}</div>
            <div class="section-controls">
                ${isLoading ? 
                    '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' : 
                    `<button class="btn btn-sm btn-outline-secondary edit-section-btn" title="Edit">
                    <i class="ri-pencil-line"></i>
                    </button>`
                }
                <button class="btn btn-sm btn-outline-danger remove-section-btn" title="Remove" ${isLoading ? 'disabled' : ''}>
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="section-meta small text-muted mt-2">Type: ${sectionType} | Layout: ${columnLayout}</div>
        `;
        return div;
    }

    // Auto-save new section to server
    function autoSaveNewSection(gridItem) {
        const templateId = document.getElementById('template-id').value;
        const gridData = grid.engine.nodes.find(n => n.el === gridItem);
        
        const sectionData = {
            template_id: templateId,
            name: 'New Section',
            section_type: 'full-width',
            column_layout: '12',
            position: gridData.y,
            description: '',
            is_repeatable: false,
            max_widgets: 0,
            css_classes: '',
            x: gridData.x,
            y: gridData.y,
            w: gridData.w,
            h: gridData.h
        };

        return fetch(`/admin/templates/${templateId}/sections`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(sectionData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.section) {
                // Update the grid item with the saved section data
                gridItem.setAttribute('data-id', data.section.id);
                gridItem.setAttribute('data-section-type', data.section.section_type);
                gridItem.setAttribute('data-column-layout', data.section.column_layout);
                gridItem.setAttribute('data-description', data.section.description || '');
                gridItem.setAttribute('data-repeatable', data.section.is_repeatable ? 'true' : 'false');
                gridItem.setAttribute('data-max-widgets', data.section.max_widgets || '0');
                gridItem.setAttribute('data-css-classes', data.section.css_classes || '');
                
                // Replace the loading content with normal content
                const newContent = createSectionContent(data.section);
                gridItem.innerHTML = '';
                gridItem.appendChild(newContent);
                
                console.log('Section auto-saved:', data.section);
                return data.section;
            } else {
                throw new Error('Failed to save section');
            }
        })
        .catch(error => {
            console.error('Error auto-saving section:', error);
            // Replace loading content with error state
            const errorContent = createSectionContent({
                name: 'Error - Click to retry',
                section_type: 'full-width',
                column_layout: '12',
                isLoading: false
            });
            gridItem.innerHTML = '';
            gridItem.appendChild(errorContent);
        });
    }

    
    // Add Section button
    const addSectionBtn = document.getElementById('add-section-btn');
    if (addSectionBtn) {
        let isAdding = false;
        
        addSectionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isAdding) return;
            isAdding = true;
            
            console.log('Add section clicked');
            
            // Calculate next Y position
            let nextY = 0;
            if (grid.engine.nodes.length > 0) {
                const maxY = Math.max(...grid.engine.nodes.map(node => node.y + node.h));
                nextY = maxY;
            }
            
            const widget = grid.addWidget({
                x: 0,
                y: nextY,
                w: 12,
                h: 3
            });
            
            // Add loading content first
            widget.appendChild(createSectionContent({ isLoading: true }));
            
            // Auto-save the new section
            autoSaveNewSection(widget).finally(() => {
                setTimeout(() => {
                    isAdding = false;
                }, 500);
            });
        });
    }

    // Update section on server
    function updateSectionOnServer(sectionData) {
        const templateId = document.getElementById('template-id').value;
        const sectionId = sectionData.section_id;
        
        return fetch(`/admin/templates/${templateId}/sections/${sectionId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(sectionData)
        })
        .then(response => response.json());
    }

    // Delete section from server
    function deleteSectionFromServer(sectionId) {
        const templateId = document.getElementById('template-id').value;
        
        return fetch(`/admin/templates/${templateId}/sections/${sectionId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => response.json());
    }

    // Event delegation for edit/delete
    document.addEventListener('click', function(e) {
        // Remove section
        if (e.target.classList.contains('remove-section-btn') || e.target.closest('.remove-section-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const gridItem = e.target.closest('.grid-stack-item');
            if (gridItem) {
                const sectionId = gridItem.getAttribute('data-id');
                
                if (sectionId) {
                    // Show confirmation
                    if (confirm('Are you sure you want to delete this section?')) {
                        // Delete from server first
                        deleteSectionFromServer(sectionId)
                            .then(data => {
                                if (data.success) {
                                    grid.removeWidget(gridItem);
                                    grid.compact();
                                } else {
                                    alert('Error deleting section from server');
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting section:', error);
                                alert('Error deleting section');
                            });
                    }
                } else {
                    // Just remove from grid if not saved yet
                grid.removeWidget(gridItem);
                    grid.compact();
                }
            }
        }
        
        // Edit section
        if (e.target.classList.contains('edit-section-btn') || e.target.closest('.edit-section-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const gridItem = e.target.closest('.grid-stack-item');
            if (gridItem) {
                openSectionEditor(gridItem);
            }
        }
        
        // System section edit
        if (e.target.classList.contains('edit-system-section-btn') || e.target.closest('.edit-system-section-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const section = e.target.closest('.system-section');
            if (section) {
                const sectionType = section.getAttribute('data-section-type');
                openSystemSectionEditor(section, sectionType);
            }
        }
    });
    
    // Section editor modal logic
    function openSectionEditor(gridItem) {
        const modalEl = document.getElementById('sectionEditorModal');
        const modal = new bootstrap.Modal(modalEl);
        const contentEl = gridItem.querySelector('.grid-stack-item-content');
        const labelEl = contentEl.querySelector('.section-label');
        const metaEl = contentEl.querySelector('.section-meta');
        const gridData = grid.engine.nodes.find(n => n.el === gridItem);
        
        // Populate form fields
        document.getElementById('section-id').value = gridItem.getAttribute('data-id') || '';
        document.getElementById('section-name').value = gridItem.getAttribute('data-name') || (labelEl ? labelEl.textContent : 'New Section');
        document.getElementById('section-type').value = gridItem.getAttribute('data-section-type') || 'full-width';
        document.getElementById('column-layout').value = gridItem.getAttribute('data-column-layout') || (gridData ? gridData.w : '12');
        document.getElementById('section-description').value = gridItem.getAttribute('data-description') || '';
        document.getElementById('is-repeatable').checked = gridItem.getAttribute('data-repeatable') === 'true';
        document.getElementById('max-widgets').value = gridItem.getAttribute('data-max-widgets') || 0;
        document.getElementById('css-classes').value = gridItem.getAttribute('data-css-classes') || '';
        
        // Remove any previous event listeners
        const saveBtn = document.getElementById('save-section-btn');
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

        // Add new event listener
        newSaveBtn.addEventListener('click', function() {
            const formData = new FormData(document.getElementById('sectionEditorForm'));
            const data = {};
            
            // Convert FormData to object
            for (const [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            // Add additional data
            data.template_id = document.getElementById('template-id').value;
            data.position = gridData ? gridData.y : 0;
            data.x = gridData ? gridData.x : 0;
            data.y = gridData ? gridData.y : 0;
            data.w = gridData ? gridData.w : 12;
            data.h = gridData ? gridData.h : 3;

            // Update section on server
            updateSectionOnServer(data)
                .then(response => {
                    if (response.success && response.section) {
                        // Update grid item attributes
                        gridItem.setAttribute('data-id', response.section.id);
                        gridItem.setAttribute('data-section-type', response.section.section_type);
                        gridItem.setAttribute('data-column-layout', response.section.column_layout);
                        gridItem.setAttribute('data-description', response.section.description || '');
                        gridItem.setAttribute('data-repeatable', response.section.is_repeatable ? 'true' : 'false');
                        gridItem.setAttribute('data-max-widgets', response.section.max_widgets || '0');
                        gridItem.setAttribute('data-css-classes', response.section.css_classes || '');
                        
                        // Update visual content
                        if (labelEl) labelEl.textContent = response.section.name;
                        gridItem.setAttribute('data-name', response.section.name);
                        if (metaEl) metaEl.textContent = `Type: ${response.section.section_type} | Layout: ${response.section.column_layout}`;
                        
                        // Resize grid item if column layout changed
                        if (response.section.column_layout !== gridData.w) {
                            grid.update(gridItem, {
                                w: parseInt(response.section.column_layout),
                                h: gridData.h
                            });
                        }
                        
            modal.hide();
                        console.log('Section updated successfully');
                    } else {
                        alert('Error updating section: ' + (response.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error updating section:', error);
                    alert('Error updating section');
                });
        });

        modal.show();
    }

    // System section editor
    function openSystemSectionEditor(section, sectionType) {
        const modalEl = document.getElementById('systemSectionEditorModal');
        const modal = new bootstrap.Modal(modalEl);
        
        // Set the section type
        document.getElementById('system-section-type').value = sectionType;
        document.getElementById('systemSectionEditorTitle').textContent = `Edit ${sectionType.charAt(0).toUpperCase() + sectionType.slice(1)} Section`;
        
        // Load existing settings (you might want to load from server)
        // For now, just show the modal
        modal.show();
        
        // Handle save button
        const saveBtn = document.getElementById('save-system-section-btn');
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
        
        newSaveBtn.addEventListener('click', function() {
            // TODO: Implement system section saving
            console.log('Saving system section:', sectionType);
            modal.hide();
        });
    }

    // Save Layout - Update positions only
    const saveLayoutBtn = document.getElementById('save-layout-btn');
    if (saveLayoutBtn) {
        saveLayoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const positions = grid.engine.nodes.map(node => ({
                id: node.el.getAttribute('data-id'),
                position: node.y,
                x: node.x,
                y: node.y,
                w: node.w,
                h: node.h
            }));

            // Check if all sections have IDs
            const unsavedSections = positions.filter(p => !p.id);
            if (unsavedSections.length > 0) {
                alert('Please wait for all sections to be saved before updating layout positions.');
                return;
            }

            const templateId = document.getElementById('template-id').value;
            
            fetch(`/admin/templates/${templateId}/sections/positions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ positions: positions })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Layout positions saved successfully!');
                } else {
                    alert('Error saving layout positions: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error saving layout:', error);
                alert('Error saving layout positions');
            });
        });
    }

    // Preview button
    const previewBtn = document.getElementById('preview-btn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const templateId = this.getAttribute('data-template-id');
            window.open(`/admin/templates/${templateId}/preview`, '_blank');
        });
    }

    // TODO: Load existing sections from backend on page load
    // You might want to implement this to load existing sections
    function loadExistingSections() {
        const templateId = document.getElementById('template-id').value;
        
        fetch(`/admin/templates/${templateId}/sections`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.sections && data.sections.length > 0) {
                    grid.removeAll(); // optional
                    data.sections.forEach(section => {
                        const widget = grid.addWidget({
                            x: section.x || 0,
                            y: section.y || section.position || 0,
                            w: section.w || section.column_layout || 12,
                            h: section.h || 3
                        });
    
                        widget.setAttribute('data-id', section.id);
                        widget.setAttribute('data-name', section.name);
                        widget.setAttribute('data-section-type', section.section_type);
                        widget.setAttribute('data-column-layout', section.column_layout);
                        widget.setAttribute('data-description', section.description || '');
                        widget.setAttribute('data-repeatable', section.is_repeatable ? 'true' : 'false');
                        widget.setAttribute('data-max-widgets', section.max_widgets || '0');
                        widget.setAttribute('data-css-classes', section.css_classes || '');
    
                        widget.appendChild(createSectionContent(section));
                    });
                } else {
                    // No existing sections — add a default one
                    const widget = grid.addWidget({
                        x: 0,
                        y: 0,
                        w: 12,
                        h: 3
                    });
                    widget.appendChild(createSectionContent({ isLoading: true }));
                    autoSaveNewSection(widget);
                }
            })
            .catch(error => {
                console.error('Error loading sections:', error);
            });
    }
    
    // Uncomment this line if you want to load existing sections
     loadExistingSections();
});
