/**
 * Media Detail Extensions
 * Extends the media detail sidebar with folder and tag functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize folder and tag handling in media detail sidebar
    initMediaDetailExtensions();
});

/**
 * Initialize media detail extensions for tags and folders
 */
function initMediaDetailExtensions() {
    // We need to hook into the existing loadMediaDetails function
    // Store the original function to call after our extensions
    if (window.loadMediaDetails) {
        const originalLoadMediaDetails = window.loadMediaDetails;
        
        // Override with our extended version
        window.loadMediaDetails = function(mediaId) {
            // Call the original function first
            originalLoadMediaDetails(mediaId);
            
            // Then extend with our additional functionality
            extendMediaDetailsFetch(mediaId);
        };
    }
    
    // Initialize tag selection in details sidebar
    initDetailTagSelection();
    
    // Initialize folder selection in details sidebar
    initDetailFolderSelection();
}

/**
 * Extend media details fetch to include tags and folder info
 */
function extendMediaDetailsFetch(mediaId) {
    // Fetch additional media details including tags and folder
    fetch(`/admin/media/${mediaId}?include=tags,folder`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update tags in detail sidebar
            updateDetailTags(data.media.tags || []);
            
            // Update folder in detail sidebar
            updateDetailFolder(data.media.folder || null);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

/**
 * Update tags in the detail sidebar
 */
function updateDetailTags(tags) {
    const tagsContainer = document.getElementById('detailTags');
    if (!tagsContainer) return;
    
    // Clear existing tags
    tagsContainer.innerHTML = '';
    
    if (tags.length > 0) {
        // Add each tag
        tags.forEach(tag => {
            const tagElement = document.createElement('span');
            tagElement.className = 'badge me-1 mb-1';
            tagElement.style.backgroundColor = tag.color || '#6c757d';
            tagElement.textContent = tag.name;
            
            // Add remove button
            const removeBtn = document.createElement('i');
            removeBtn.className = 'ri-close-line ms-1';
            removeBtn.style.cursor = 'pointer';
            removeBtn.addEventListener('click', function() {
                // Remove tag from media
                removeTagFromMedia(document.getElementById('mediaDetailId').value, tag.id);
            });
            
            tagElement.appendChild(removeBtn);
            tagsContainer.appendChild(tagElement);
        });
    } else {
        tagsContainer.innerHTML = '<span class="text-muted">No tags</span>';
    }
}

/**
 * Remove a tag from media
 */
function removeTagFromMedia(mediaId, tagId) {
    // Get current tags and remove the selected one
    fetch(`/admin/media/${mediaId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const currentTags = data.media.tags || [];
            const updatedTagIds = currentTags
                .filter(tag => tag.id !== tagId)
                .map(tag => tag.id);
            
            // Update media tags
            updateMediaTags(mediaId, updatedTagIds);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

/**
 * Update folder in the detail sidebar
 */
function updateDetailFolder(folder) {
    const folderDisplay = document.getElementById('detailFolder');
    if (!folderDisplay) return;
    
    if (folder) {
        folderDisplay.innerHTML = `
            <span class="badge bg-light text-dark">
                <i class="ri-folder-3-line me-1"></i>${folder.name}
            </span>
        `;
    } else {
        folderDisplay.innerHTML = `
            <span class="badge bg-light text-dark">
                <i class="ri-folder-3-line me-1"></i>Root
            </span>
        `;
    }
}

/**
 * Initialize tag selection in media detail sidebar
 */
function initDetailTagSelection() {
    // Add tag button click handler
    const addTagBtn = document.getElementById('addTagBtn');
    if (addTagBtn) {
        addTagBtn.addEventListener('click', function() {
            const mediaId = document.getElementById('mediaDetailId').value;
            showTagSelectionModal(mediaId);
        });
    }
}

/**
 * Initialize folder selection in media detail sidebar
 */
function initDetailFolderSelection() {
    // Change folder button click handler
    const changeFolderBtn = document.getElementById('changeFolderBtn');
    if (changeFolderBtn) {
        changeFolderBtn.addEventListener('click', function() {
            const mediaId = document.getElementById('mediaDetailId').value;
            showFolderSelectionModal([mediaId]);
        });
    }
}

/**
 * Show tag selection modal for a media item
 */
function showTagSelectionModal(mediaId) {
    // Create modal dynamically
    let modalHTML = `
        <div class="modal fade" id="tagSelectionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Tags</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Tags</label>
                            <select id="tagSelectionDropdown" class="form-select" multiple>
                                <option value="loading" disabled>Loading tags...</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmTagSelectionBtn">Apply Tags</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('tagSelectionModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('tagSelectionModal'));
    modal.show();
    
    // Load tags
    fetch('/admin/media-tags', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const dropdown = document.getElementById('tagSelectionDropdown');
            dropdown.innerHTML = '';
            
            data.tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.id;
                option.textContent = tag.name;
                option.dataset.color = tag.color;
                dropdown.appendChild(option);
            });
            
            // Load current tags and pre-select them
            fetch(`/admin/media/${mediaId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(mediaData => {
                if (mediaData.success && mediaData.media.tags) {
                    const currentTagIds = mediaData.media.tags.map(tag => tag.id.toString());
                    
                    Array.from(dropdown.options).forEach(option => {
                        option.selected = currentTagIds.includes(option.value);
                    });
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
    
    // Confirm button click handler
    document.getElementById('confirmTagSelectionBtn').addEventListener('click', function() {
        const dropdown = document.getElementById('tagSelectionDropdown');
        const selectedTagIds = Array.from(dropdown.selectedOptions).map(option => option.value);
        
        updateMediaTags(mediaId, selectedTagIds);
        modal.hide();
    });
}

/**
 * Show folder selection modal for media items
 */
function showFolderSelectionModal(mediaIds) {
    if (!Array.isArray(mediaIds)) {
        mediaIds = [mediaIds];
    }
    
    // Create modal dynamically
    let modalHTML = `
        <div class="modal fade" id="folderSelectionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Move to Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Destination Folder</label>
                            <select id="folderSelectionDropdown" class="form-select">
                                <option value="root">Root (No Folder)</option>
                                <option value="loading" disabled>Loading folders...</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmFolderSelectionBtn">Move</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('folderSelectionModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('folderSelectionModal'));
    modal.show();
    
    // Load folders
    fetch('/admin/media-folders', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const dropdown = document.getElementById('folderSelectionDropdown');
            
            // Keep root option and clear loading
            dropdown.innerHTML = '<option value="root">Root (No Folder)</option>';
            
            // Add folders recursively
            function addFoldersToDropdown(folders, level = 0) {
                folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    option.textContent = '—'.repeat(level) + ' ' + folder.name;
                    dropdown.appendChild(option);
                    
                    if (folder.children && folder.children.length > 0) {
                        addFoldersToDropdown(folder.children, level + 1);
                    }
                });
            }
            
            addFoldersToDropdown(data.folders);
            
            // If it's a single file, try to preselect current folder
            if (mediaIds.length === 1) {
                fetch(`/admin/media/${mediaIds[0]}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(mediaData => {
                    if (mediaData.success) {
                        const currentFolder = mediaData.media.folder;
                        dropdown.value = currentFolder ? currentFolder.id : 'root';
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
    
    // Confirm button click handler
    document.getElementById('confirmFolderSelectionBtn').addEventListener('click', function() {
        const folderId = document.getElementById('folderSelectionDropdown').value;
        
        // Use batch move function even for single item
        batchMoveMedia(mediaIds, folderId);
        modal.hide();
    });
}
