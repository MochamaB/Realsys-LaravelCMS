/**
 * Media Folders Management
 * Handles folder tree navigation, folder CRUD operations
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize folder tree functionality
    initFolderTree();
    
    // Initialize folder creation, editing and deletion
    initFolderOperations();
});

/**
 * Initialize folder tree navigation and interactions
 */
function initFolderTree() {
    // Handle folder tree expansion/collapse
    document.querySelectorAll('.folder-collapse').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const folderItem = this.closest('.folder-item');
            const childrenContainer = folderItem.querySelector('.folder-children');
            
            if (childrenContainer) {
                if (childrenContainer.style.display === 'none') {
                    childrenContainer.style.display = 'block';
                    this.classList.add('expanded');
                } else {
                    childrenContainer.style.display = 'none';
                    this.classList.remove('expanded');
                }
            }
        });
    });
    
    // Handle folder selection/navigation
    document.querySelectorAll('.folder-link').forEach(element => {
        element.addEventListener('click', function(e) {
            // Skip if clicked on action buttons or collapse icon
            if (e.target.closest('.folder-actions') || e.target.closest('.folder-collapse')) {
                return;
            }
            
            // Set active folder
            document.querySelectorAll('.folder-item').forEach(item => {
                item.classList.remove('active');
            });
            
            this.closest('.folder-item').classList.add('active');
            
            // Load media from this folder
            const folderId = this.closest('.folder-item').dataset.id;
            loadMediaByFolder(folderId);
        });
    });
}

/**
 * Initialize folder CRUD operations
 */
function initFolderOperations() {
    // Create root folder button
    const createRootFolderBtn = document.getElementById('createRootFolderBtn');
    if (createRootFolderBtn) {
        createRootFolderBtn.addEventListener('click', function() {
            showFolderModal('create', { parentId: null });
        });
    }
    
    // Create subfolder buttons
    document.querySelectorAll('.folder-add').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const folderId = this.dataset.id;
            showFolderModal('create', { parentId: folderId });
        });
    });
    
    // Edit folder buttons
    document.querySelectorAll('.folder-edit').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const folderId = this.dataset.id;
            const folderName = this.dataset.name;
            showFolderModal('edit', { id: folderId, name: folderName });
        });
    });
    
    // Delete folder buttons
    document.querySelectorAll('.folder-delete').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const folderId = this.dataset.id;
            if (confirm('Are you sure you want to delete this folder? Files in this folder will be moved to the parent folder.')) {
                deleteFolder(folderId);
            }
        });
    });
}

/**
 * Show folder creation/edit modal
 */
function showFolderModal(mode, data = {}) {
    // Create modal dynamically (or use existing one)
    let modalHTML = `
        <div class="modal fade" id="folderModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${mode === 'create' ? 'Create New Folder' : 'Edit Folder'}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="folderForm">
                            <div class="mb-3">
                                <label for="folderName" class="form-label">Folder Name</label>
                                <input type="text" class="form-control" id="folderName" required 
                                    value="${data.name || ''}">
                            </div>
                            <input type="hidden" id="folderId" value="${data.id || ''}">
                            <input type="hidden" id="parentFolderId" value="${data.parentId || ''}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveFolderBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('folderModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('folderModal'));
    modal.show();
    
    // Save button handler
    document.getElementById('saveFolderBtn').addEventListener('click', function() {
        const folderName = document.getElementById('folderName').value;
        
        if (!folderName.trim()) {
            alert('Folder name is required');
            return;
        }
        
        if (mode === 'create') {
            createFolder(folderName, data.parentId);
        } else {
            updateFolder(data.id, folderName);
        }
        
        modal.hide();
    });
}

/**
 * Create new folder via AJAX
 */
function createFolder(name, parentId) {
    const formData = new FormData();
    formData.append('name', name);
    formData.append('parent_id', parentId || '');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/admin/media-folders', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the folder tree
            refreshFolderTree();
        } else {
            alert(data.message || 'Error creating folder');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create folder');
    });
}

/**
 * Update folder via AJAX
 */
function updateFolder(id, name) {
    const formData = new FormData();
    formData.append('name', name);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'PUT');
    
    fetch(`/admin/media-folders/${id}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the folder tree
            refreshFolderTree();
        } else {
            alert(data.message || 'Error updating folder');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update folder');
    });
}

/**
 * Delete folder via AJAX
 */
function deleteFolder(id) {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'DELETE');
    
    fetch(`/admin/media-folders/${id}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the folder tree
            refreshFolderTree();
            
            // If we were viewing this folder, switch to root folder
            const currentFolder = document.querySelector('.folder-item.active');
            if (currentFolder && currentFolder.dataset.id === id) {
                loadMediaByFolder('root');
            }
        } else {
            alert(data.message || 'Error deleting folder');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete folder');
    });
}

/**
 * Refresh folder tree via AJAX
 */
function refreshFolderTree() {
    fetch('/admin/media-folders')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // We would normally update the DOM here but
                // for simplicity, we'll reload the page
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

/**
 * Load media items filtered by folder
 */
function loadMediaByFolder(folderId) {
    let url = '/admin/media/filter?';
    
    if (folderId === 'root') {
        url += 'folder_id=root';
    } else {
        url += `folder_id=${folderId}`;
    }
    
    // Load media content via AJAX
    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update grid view
            if (document.getElementById('grid-view')) {
                document.getElementById('grid-view').innerHTML = data.gridHtml;
            }
            
            // Update list view
            if (document.getElementById('list-view')) {
                document.getElementById('list-view').innerHTML = data.listHtml;
            }
            
            // Reinitialize media item handlers
            if (window.setupMediaDetailsHandlers) {
                window.setupMediaDetailsHandlers();
            }
            
            // Reinitialize batch selection if available
            if (window.initBatchSelection) {
                window.initBatchSelection();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
