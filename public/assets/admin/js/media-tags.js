/**
 * Media Tags Management
 * Handles tag CRUD operations and tag assignment to media
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tag creation, editing and deletion
    initTagOperations();
    
    // Initialize tag form toggle
    initTagFormToggle();
});

/**
 * Initialize tag form toggle
 */
function initTagFormToggle() {
    const createTagBtn = document.getElementById('createTagBtn');
    const createTagForm = document.getElementById('createTagForm');
    const cancelTagBtn = document.getElementById('cancelTagBtn');
    
    if (createTagBtn && createTagForm && cancelTagBtn) {
        createTagBtn.addEventListener('click', function() {
            createTagForm.classList.remove('d-none');
            createTagBtn.classList.add('d-none');
            
            // Reset the form
            document.getElementById('tagName').value = '';
            document.getElementById('tagColor').value = '#6c757d';
        });
        
        cancelTagBtn.addEventListener('click', function() {
            createTagForm.classList.add('d-none');
            createTagBtn.classList.remove('d-none');
        });
        
        // Submit new tag
        createTagForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('tagName').value;
            const color = document.getElementById('tagColor').value;
            
            if (name.trim() !== '') {
                createTag(name, color);
            }
        });
    }
}

/**
 * Initialize tag operations (edit, delete)
 */
function initTagOperations() {
    // Edit tag buttons
    document.querySelectorAll('.tag-edit').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const tagId = this.dataset.id;
            const tagName = this.dataset.name;
            const tagColor = this.dataset.color;
            
            showTagModal('edit', { id: tagId, name: tagName, color: tagColor });
        });
    });
    
    // Delete tag buttons
    document.querySelectorAll('.tag-delete').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const tagId = this.dataset.id;
            
            if (confirm('Are you sure you want to delete this tag? It will be removed from all media items.')) {
                deleteTag(tagId);
            }
        });
    });
}

/**
 * Show tag edit modal
 */
function showTagModal(mode, data = {}) {
    // Create modal dynamically
    let modalHTML = `
        <div class="modal fade" id="tagModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tag</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editTagForm">
                            <div class="mb-3">
                                <label for="editTagName" class="form-label">Tag Name</label>
                                <input type="text" class="form-control" id="editTagName" required value="${data.name || ''}">
                            </div>
                            <div class="mb-3">
                                <label for="editTagColor" class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color" id="editTagColor" value="${data.color || '#6c757d'}">
                            </div>
                            <input type="hidden" id="editTagId" value="${data.id || ''}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveTagBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('tagModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('tagModal'));
    modal.show();
    
    // Save button handler
    document.getElementById('saveTagBtn').addEventListener('click', function() {
        const tagId = document.getElementById('editTagId').value;
        const tagName = document.getElementById('editTagName').value;
        const tagColor = document.getElementById('editTagColor').value;
        
        if (!tagName.trim()) {
            alert('Tag name is required');
            return;
        }
        
        updateTag(tagId, tagName, tagColor);
        modal.hide();
    });
}

/**
 * Create new tag via AJAX
 */
function createTag(name, color) {
    const formData = new FormData();
    formData.append('name', name);
    formData.append('color', color);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/admin/media-tags', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide the form and show create button
            document.getElementById('createTagForm').classList.add('d-none');
            document.getElementById('createTagBtn').classList.remove('d-none');
            
            // Refresh tag list
            refreshTagList();
        } else {
            alert(data.message || 'Error creating tag');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create tag');
    });
}

/**
 * Update tag via AJAX
 */
function updateTag(id, name, color) {
    const formData = new FormData();
    formData.append('name', name);
    formData.append('color', color);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'PUT');
    
    fetch(`/admin/media-tags/${id}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh tag list
            refreshTagList();
        } else {
            alert(data.message || 'Error updating tag');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update tag');
    });
}

/**
 * Delete tag via AJAX
 */
function deleteTag(id) {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'DELETE');
    
    fetch(`/admin/media-tags/${id}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh tag list
            refreshTagList();
        } else {
            alert(data.message || 'Error deleting tag');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete tag');
    });
}

/**
 * Update media tags via AJAX
 */
function updateMediaTags(mediaId, tagIds) {
    const formData = new FormData();
    formData.append('tag_ids', JSON.stringify(tagIds));
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch(`/admin/media/${mediaId}/tags`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload media details
            if (window.loadMediaDetails) {
                window.loadMediaDetails(mediaId);
            }
        } else {
            alert(data.message || 'Error updating tags');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update tags');
    });
}

/**
 * Refresh tag list via AJAX
 * For simplicity, we're reloading the page
 */
function refreshTagList() {
    window.location.reload();
}
