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
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                handle: '.section-handle',
                ghostClass: 'section-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    console.log('Section moved:', evt);
                    updatePositionLabels();
                }
            });
        }
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
                alert(data.message);
                
                // Restore button state
                saveBtn.innerHTML = originalBtnText;
                saveBtn.disabled = false;
            } else {
                alert('Error: ' + data.message);
                saveBtn.innerHTML = originalBtnText;
                saveBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving positions.');
            saveBtn.innerHTML = originalBtnText;
            saveBtn.disabled = false;
        });
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
                    document.getElementById('section-form').scrollIntoView();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading the section.');
            });
    }

    // Delete section
    function deleteSection(sectionId) {
        if (!confirm('Are you sure you want to delete this section?')) {
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
                alert(data.message);
                
                // Remove section from DOM
                document.getElementById(`section-${sectionId}`).remove();
                
                // If no sections left, reload page
                if (document.querySelectorAll('.dd-item').length === 0) {
                    window.location.reload();
                }
                
                // Update position labels
                updatePositionLabels();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the section.');
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
                        alert(data.message);
                        
                        // Reload page to show updated sections
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the section.');
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
    
    // Save positions button click handler
    document.getElementById('save-positions')?.addEventListener('click', function() {
        savePositions();
    });
    
    // Initialize event delegation for edit and delete buttons
    document.addEventListener('click', function(e) {
        // Edit buttons
        if (e.target.closest('.edit-section')) {
            const btn = e.target.closest('.edit-section');
            const sectionId = btn.dataset.id;
            loadSectionForEdit(sectionId);
        }
        
        // Delete buttons
        if (e.target.closest('.delete-section')) {
            const btn = e.target.closest('.delete-section');
            const sectionId = btn.dataset.id;
            deleteSection(sectionId);
        }
    });
});
