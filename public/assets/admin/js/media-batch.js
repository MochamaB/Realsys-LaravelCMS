/**
 * Media Batch Operations
 * Handles batch selection and batch actions (delete, move, tag)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize batch selection
    initBatchSelection();
    
    // Initialize batch operations
    initBatchOperations();
    
    // Make this function available globally for reinitialization after AJAX
    window.initBatchSelection = initBatchSelection;
});

/**
 * Initialize batch selection functionality
 */
function initBatchSelection() {
    const batchToolbar = document.getElementById('batchToolbar');
    const selectedCountBadge = document.querySelector('.batch-toolbar .selected-count');
    
    if (!batchToolbar || !selectedCountBadge) return;
    
    // Add click handlers to media selection checkboxes
    document.querySelectorAll('.media-select').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Show/hide the parent selection indicator
            const mediaSelection = this.closest('.media-selection');
            if (mediaSelection) {
                if (this.checked) {
                    mediaSelection.classList.add('checked');
                } else {
                    mediaSelection.classList.remove('checked');
                }
            }
            
            // Count selected items
            const selectedCount = document.querySelectorAll('.media-select:checked').length;
            
            // Update the counter
            selectedCountBadge.textContent = selectedCount;
            
            // Show/hide the toolbar
            if (selectedCount > 0) {
                batchToolbar.classList.remove('d-none');
            } else {
                batchToolbar.classList.add('d-none');
            }
        });
    });
    
    // Cancel batch selection
    const cancelBatchBtn = document.getElementById('cancelBatchBtn');
    if (cancelBatchBtn) {
        cancelBatchBtn.addEventListener('click', function() {
            // Uncheck all checkboxes
            document.querySelectorAll('.media-select').forEach(checkbox => {
                checkbox.checked = false;
                
                // Remove checked class from parent
                const mediaSelection = checkbox.closest('.media-selection');
                if (mediaSelection) {
                    mediaSelection.classList.remove('checked');
                }
            });
            
            // Hide the toolbar
            batchToolbar.classList.add('d-none');
        });
    }
}

/**
 * Initialize batch operations (delete, move, tag)
 */
function initBatchOperations() {
    // Batch delete button
    const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
            const selectedIds = getSelectedMediaIds();
            
            if (selectedIds.length === 0) return;
            
            if (confirm(`Are you sure you want to delete ${selectedIds.length} media item${selectedIds.length > 1 ? 's' : ''}?`)) {
                batchDeleteMedia(selectedIds);
            }
        });
    }
    
    // Batch move button
    const batchMoveBtn = document.getElementById('batchMoveBtn');
    if (batchMoveBtn) {
        batchMoveBtn.addEventListener('click', function() {
            const selectedIds = getSelectedMediaIds();
            
            if (selectedIds.length === 0) return;
            
            // Set selected media IDs in the form
            document.getElementById('selectedMediaIds').value = JSON.stringify(selectedIds);
            
            // Show the move modal
            const moveFolderModal = new bootstrap.Modal(document.getElementById('moveFolderModal'));
            moveFolderModal.show();
        });
    }
    
    // Batch tag button
    const batchTagBtn = document.getElementById('batchTagBtn');
    if (batchTagBtn) {
        batchTagBtn.addEventListener('click', function() {
            const selectedIds = getSelectedMediaIds();
            
            if (selectedIds.length === 0) return;
            
            // Set selected media IDs in the form
            document.getElementById('batchSelectedMediaIds').value = JSON.stringify(selectedIds);
            
            // Show the tag modal
            const batchTagModal = new bootstrap.Modal(document.getElementById('batchTagModal'));
            batchTagModal.show();
        });
    }
    
    // Confirm move button
    const confirmMoveBtn = document.getElementById('confirmMoveBtn');
    if (confirmMoveBtn) {
        confirmMoveBtn.addEventListener('click', function() {
            const selectedIds = JSON.parse(document.getElementById('selectedMediaIds').value);
            const folderId = document.getElementById('destinationFolder').value;
            
            batchMoveMedia(selectedIds, folderId);
            
            // Hide modal
            bootstrap.Modal.getInstance(document.getElementById('moveFolderModal')).hide();
        });
    }
    
    // Confirm tag button
    const confirmBatchTagBtn = document.getElementById('confirmBatchTagBtn');
    if (confirmBatchTagBtn) {
        confirmBatchTagBtn.addEventListener('click', function() {
            const selectedIds = JSON.parse(document.getElementById('batchSelectedMediaIds').value);
            const tagSelect = document.getElementById('batchTagSelect');
            const selectedTagIds = Array.from(tagSelect.selectedOptions).map(option => option.value);
            
            batchTagMedia(selectedIds, selectedTagIds);
            
            // Hide modal
            bootstrap.Modal.getInstance(document.getElementById('batchTagModal')).hide();
        });
    }
}

/**
 * Get all selected media IDs
 */
function getSelectedMediaIds() {
    const selectedCheckboxes = document.querySelectorAll('.media-select:checked');
    return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
}

/**
 * Batch delete media via AJAX
 */
function batchDeleteMedia(mediaIds) {
    const formData = new FormData();
    formData.append('media_ids', JSON.stringify(mediaIds));
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/admin/media/batch-delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page or update UI
            window.location.reload();
        } else {
            alert(data.message || 'Error deleting media items');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete media items');
    });
}

/**
 * Batch move media to folder via AJAX
 */
function batchMoveMedia(mediaIds, folderId) {
    const formData = new FormData();
    formData.append('media_ids', JSON.stringify(mediaIds));
    formData.append('folder_id', folderId);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/admin/media/move-to-folder', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page or update UI
            window.location.reload();
        } else {
            alert(data.message || 'Error moving media items');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to move media items');
    });
}

/**
 * Batch add tags to media via AJAX
 */
function batchTagMedia(mediaIds, tagIds) {
    const formData = new FormData();
    formData.append('media_ids', JSON.stringify(mediaIds));
    formData.append('tag_ids', JSON.stringify(tagIds));
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/admin/media/batch-tag', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page or update UI
            window.location.reload();
        } else {
            alert(data.message || 'Error tagging media items');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to tag media items');
    });
}
