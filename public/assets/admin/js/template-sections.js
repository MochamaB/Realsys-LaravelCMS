$(document).ready(function() {
    let sectionsListSortable;
    
    // Initialize Sortable for drag and drop functionality
    function initializeSortable() {
        // Clear existing instances
        if (sectionsListSortable && sectionsListSortable.destroy) {
            sectionsListSortable.destroy();
        }

        // Initialize sortable on the sections list
        const sectionsList = document.querySelector('#sections-sortable');
        if (sectionsList) {
            sectionsListSortable = new Sortable(sectionsList, {
                group: 'template-sections',
                animation: 200,
                fallbackOnBody: true,
                swapThreshold: 0.5, // Reduced for better responsiveness
                direction: 'vertical',
                // Remove handle restriction to make entire section draggable
                // handle: '.section-handle', 
                ghostClass: 'section-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                forceFallback: false,
                scroll: true,
                scrollSensitivity: 30,
                scrollSpeed: 10,
                bubbleScroll: true,
                
                // Enhanced drag detection
                delay: 100,
                delayOnTouchOnly: true,
                touchStartThreshold: 5,
                
                onStart: function(evt) {
                    console.log('Drag started:', evt);
                    // Add visual feedback when dragging starts
                    evt.item.classList.add('is-dragging');
                    document.body.classList.add('sorting-active');
                },
                
                onEnd: function(evt) {
                    console.log('Section moved:', evt);
                    // Remove visual feedback
                    evt.item.classList.remove('is-dragging');
                    document.body.classList.remove('sorting-active');
                    updatePositionLabels();
                },
                
                onMove: function(evt) {
                    // Optional: Add logic to prevent certain moves
                    return true;
                },
                
                onChoose: function(evt) {
                    // When item is chosen for dragging
                    evt.item.classList.add('sortable-chosen');
                }
            });
        }
    }

    // Enhanced cursor and hover states
    function initializeDragCursors() {
        const sections = document.querySelectorAll('.dd-item');
        
        sections.forEach(section => {
            // Add cursor pointer to entire section
            section.style.cursor = 'grab';
            
            // Handle mouse events for better UX
            section.addEventListener('mousedown', function() {
                this.style.cursor = 'grabbing';
            });
            
            section.addEventListener('mouseup', function() {
                this.style.cursor = 'grab';
            });
            
            section.addEventListener('mouseleave', function() {
                this.style.cursor = 'grab';
            });
            
            // Prevent dragging when clicking on buttons
            const buttons = section.querySelectorAll('.edit-section, .delete-section');
            buttons.forEach(button => {
                button.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                });
                
                button.style.cursor = 'pointer';
            });
        });
    }

    // Update position labels for all sections
    function updatePositionLabels() {
        const sections = document.querySelectorAll('.dd-item');
        sections.forEach((section, index) => {
            section.querySelector('.section-position').textContent = `Position: ${index + 1}`;
        });
    }

    // Save positions of sections
    function savePositions() {
        const sections = document.querySelectorAll('.dd-item');
        const positions = Array.from(sections).map(section => section.dataset.id);
        
        const saveBtn = document.getElementById('save-positions');
        const originalBtnText = saveBtn.innerHTML;
        
        // Update button to show loading
        saveBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Saving...';
        saveBtn.disabled = true;
        
        const url = saveBtn.dataset.url;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ positions: positions })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(data.message, 'success');
                
                // Restore button state
                saveBtn.innerHTML = originalBtnText;
                saveBtn.disabled = false;
            } else {
                showNotification('Error: ' + data.message, 'error');
                saveBtn.innerHTML = originalBtnText;
                saveBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while saving positions.', 'error');
            saveBtn.innerHTML = originalBtnText;
            saveBtn.disabled = false;
        });
    }

    // Enhanced notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.sortable-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `sortable-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;
        
        notification.innerHTML = `
            <i class="ri-${type === 'success' ? 'check-circle' : 'error-warning'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Load section for editing
    function loadSectionForEdit(sectionId) {
        const baseUrl = document.getElementById('sections-container').dataset.baseUrl;
        fetch(`${baseUrl}/sections/${sectionId}/get`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const section = data.section;
                    
                    // Update form fields
                    document.getElementById('name').value = section.name;
                    document.getElementById('description').value = section.description || '';
                    document.getElementById('section_type').value = section.section_type;
                    document.getElementById('column_layout').value = section.column_layout;
                    document.getElementById('is_repeatable').checked = section.is_repeatable;
                    document.getElementById('max_widgets').value = section.max_widgets || '';
                    document.getElementById('position').value = section.position;
                    document.getElementById('editing').value = section.id;
                    
                    // Update form title and submit button
                    document.getElementById('form-title').textContent = 'Edit Section';
                    document.getElementById('submit-btn').textContent = 'Update Section';
                    document.getElementById('cancel-btn').style.display = 'inline-block';
                    
                    // Trigger change events to update UI
                    document.getElementById('section_type').dispatchEvent(new Event('change'));
                    document.getElementById('is_repeatable').dispatchEvent(new Event('change'));
                    
                    // Scroll to form
                    document.getElementById('section-form').scrollIntoView({ behavior: 'smooth' });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while loading the section.', 'error');
            });
    }

    // Delete section
    function deleteSection(sectionId) {
        if (!confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
            return;
        }
        
        const baseUrl = document.getElementById('sections-container').dataset.baseUrl;
        const url = `${baseUrl}/sections/${sectionId}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(data.message, 'success');
                
                // Remove section from DOM with animation
                const sectionElement = document.querySelector(`[data-id="${sectionId}"]`);
                if (sectionElement) {
                    sectionElement.style.transition = 'opacity 0.3s ease';
                    sectionElement.style.opacity = '0';
                    
                    setTimeout(() => {
                        sectionElement.remove();
                        
                        // If no sections left, reload page
                        if (document.querySelectorAll('.dd-item').length === 0) {
                            window.location.reload();
                        } else {
                            // Update position labels
                            updatePositionLabels();
                        }
                    }, 300);
                }
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the section.', 'error');
        });
    }

    // Function to initialize section form interactions
    function initSectionForm() {
        const sectionTypeSelect = document.getElementById('section_type');
        const columnLayoutContainer = document.getElementById('column-layout-container');
        const columnLayoutSelect = document.getElementById('column_layout');
        const isRepeatableCheckbox = document.getElementById('is_repeatable');
        const maxWidgetsContainer = document.getElementById('max-widgets-container');
        const sectionForm = document.getElementById('section-form');
        const editingInput = document.getElementById('editing');
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('submit-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        
        if (!sectionTypeSelect) return;
        
        // Handle section type changes
        sectionTypeSelect.addEventListener('change', function() {
            if (this.value === 'multi-column') {
                columnLayoutSelect.disabled = false;
                columnLayoutSelect.value = '12'; // Default to full width
                columnLayoutContainer.style.display = 'block';
            } else {
                // For any other type, disable column layout selection
                columnLayoutSelect.disabled = true;
                
                // For full-width, show the container but set value to 12
                if (this.value === 'full-width') {
                    columnLayoutContainer.style.display = 'block';
                    columnLayoutSelect.value = '12'; // Full width layout
                } else {
                    columnLayoutContainer.style.display = 'none';
                    columnLayoutSelect.value = '';
                }
            }
        });
        
        // Handle repeatable checkbox
        if (isRepeatableCheckbox) {
            isRepeatableCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    maxWidgetsContainer.style.display = 'block';
                } else {
                    maxWidgetsContainer.style.display = 'none';
                }
            });
        }
        
        // Cancel button handler
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                resetForm();
            });
        }
        
        // Section form submit
        if (sectionForm) {
            sectionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const isEditing = editingInput.value !== '';
                const baseUrl = document.getElementById('sections-container').dataset.baseUrl;
                const url = isEditing 
                    ? `${baseUrl}/sections/${editingInput.value}`
                    : `${baseUrl}/sections`;
                const method = isEditing ? 'PUT' : 'POST';
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Saving...';
                
                // Send AJAX request
                fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification(data.message, 'success');
                        
                        // Reload page to show updated sections
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = isEditing ? 'Update Section' : 'Create Section';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while saving the section.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = isEditing ? 'Update Section' : 'Create Section';
                });
            });
        }
        
        // Initial trigger
        if (sectionTypeSelect) sectionTypeSelect.dispatchEvent(new Event('change'));
        if (isRepeatableCheckbox) isRepeatableCheckbox.dispatchEvent(new Event('change'));
    }

    // Reset form to create new section
    function resetForm() {
        const form = document.getElementById('section-form');
        if (!form) return;
        
        form.reset();
        
        document.getElementById('editing').value = '';
        document.getElementById('form-title').textContent = 'Create New Section';
        document.getElementById('submit-btn').textContent = 'Create Section';
        document.getElementById('cancel-btn').style.display = 'none';
        
        // Trigger change events to reset UI
        document.getElementById('section_type').dispatchEvent(new Event('change'));
        document.getElementById('is_repeatable').dispatchEvent(new Event('change'));
    }

    // Initialize everything on document ready
    initializeSortable();
    initSectionForm();
    initializeDragCursors();
    
    // Save positions button click handler
    document.getElementById('save-positions')?.addEventListener('click', function() {
        savePositions();
    });
    
    // Initialize event delegation for edit and delete buttons
    document.addEventListener('click', function(e) {
        // Edit buttons
        if (e.target.closest('.edit-section')) {
            e.stopPropagation(); // Prevent dragging when clicking edit
            const btn = e.target.closest('.edit-section');
            const sectionId = btn.dataset.id;
            loadSectionForEdit(sectionId);
        }
        
        // Delete buttons
        if (e.target.closest('.delete-section')) {
            e.stopPropagation(); // Prevent dragging when clicking delete
            const btn = e.target.closest('.delete-section');
            const sectionId = btn.dataset.id;
            deleteSection(sectionId);
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // ESC key to cancel editing
        if (e.key === 'Escape') {
            const editingInput = document.getElementById('editing');
            if (editingInput && editingInput.value !== '') {
                resetForm();
            }
        }
    });
});