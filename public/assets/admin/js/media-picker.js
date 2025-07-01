/**
 * Media Picker JS - Core Functionality
 * RealsysCMS Media Picker component for content integration
 */
// Global references
let mediaPickerModal = null;
let currentMediaPickerField = null;

// Media state
let mediaItems = [];
let currentPage = 1;
let totalPages = 1;
let selectedMediaIds = [];
let isMultipleSelection = false;

// Filters state
let filters = {
    folder_id: null,
    tag_id: null,
    type: null,
    search: null
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize media picker on all fields
    initMediaPickers();
    
    /**
     * Initialize all media pickers on the page
     */
    function initMediaPickers() {
        // Setup modal reference
        const modalElement = document.getElementById('mediaPickerModal');
        if (modalElement) {
            mediaPickerModal = new bootstrap.Modal(modalElement);
            
            // Setup confirm button
            document.querySelector('.confirm-media-selection').addEventListener('click', confirmMediaSelection);
        }
        
        // Setup open buttons
        document.querySelectorAll('.open-media-picker').forEach(button => {
            button.addEventListener('click', openMediaPicker);
        });
        
        // Setup remove buttons
        document.querySelectorAll('.remove-selected-media').forEach(button => {
            button.addEventListener('click', removeSelectedMedia);
        });
    }
    
    /**
     * Open the media picker modal
     */
    function openMediaPicker(e) {
        // Get the field container
        currentMediaPickerField = e.target.closest('.media-picker-field');
        
        // Check if multiple selection is enabled
        isMultipleSelection = currentMediaPickerField.dataset.multiple === 'true';
        
        // Get current selection
        const input = currentMediaPickerField.querySelector('.media-picker-input');
        selectedMediaIds = input.value ? input.value.split(',').map(id => parseInt(id, 10)) : [];
        
        // Reset filters
        resetFilters();
        
        // Load media items
        loadMediaItems();
        
        // Open modal
        mediaPickerModal.show();
    }
    
    /**
     * Reset all filters to default state
     */
    function resetFilters() {
        filters = {
            folder_id: null,
            tag_id: null,
            type: null,
            search: null
        };
        
        currentPage = 1;
        
        // Reset UI
        document.querySelector('.media-search-input').value = '';
        document.querySelector('.media-type-filter').value = '';
        
        // Reset active states
        document.querySelectorAll('.folder-item.active').forEach(item => {
            item.classList.remove('active');
        });
        
        document.querySelectorAll('.media-tag-filter.active').forEach(item => {
            item.classList.remove('active');
        });
        
        // Set root folder as active
        const rootFolder = document.querySelector('.folder-item[data-folder-id="root"]');
        if (rootFolder) {
            rootFolder.classList.add('active');
        }
    }
    
    /**
     * Load media items with current filters and page
     */
    function loadMediaItems() {
        // Show loading
        const container = document.querySelector('.media-items-container');
        container.innerHTML = '<div class="text-center py-5 w-100"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading media...</p></div>';
        
        // Build query params
        const params = new URLSearchParams();
        params.append('page', currentPage);
        
        // Add filters
        if (filters.folder_id) params.append('folder_id', filters.folder_id);
        if (filters.tag_id) params.append('tag_id', filters.tag_id);
        if (filters.type) params.append('type', filters.type);
        if (filters.search) params.append('search', filters.search);
        
        // Fetch media
        fetch('/admin/media-picker?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store media items
                    mediaItems = data.media.data;
                    
                    // Update pagination
                    currentPage = data.media.current_page;
                    totalPages = data.media.last_page;
                    updatePagination();
                    
                    // Render media items
                    renderMediaItems();
                }
            })
            .catch(error => {
                console.error('Error loading media:', error);
                container.innerHTML = '<div class="alert alert-danger m-3">Failed to load media items.</div>';
            });
    }
    
    /**
     * Render media items in the grid
     */
    function renderMediaItems() {
        const container = document.querySelector('.media-items-container');
        
        if (mediaItems.length === 0) {
            container.innerHTML = '<div class="text-center py-5 w-100">No media items found. Try adjusting your filters.</div>';
            return;
        }
        
        let html = '';
        
        mediaItems.forEach(media => {
            // Check if this media is selected
            const isSelected = selectedMediaIds.includes(media.id);
            const selectedClass = isSelected ? 'selected' : '';
            
            // Create thumbnail based on media type
            let thumbnail = '';
            if (media.mime_type.startsWith('image/')) {
                thumbnail = `<img src="${media.full_url}" class="img-fluid media-thumbnail" alt="${media.name}">`;
            } else if (media.mime_type.startsWith('video/')) {
                thumbnail = '<div class="media-icon"><i class="ri-video-line"></i></div>';
            } else if (media.mime_type.startsWith('audio/')) {
                thumbnail = '<div class="media-icon"><i class="ri-music-line"></i></div>';
            } else if (media.mime_type.startsWith('application/pdf')) {
                thumbnail = '<div class="media-icon"><i class="ri-file-pdf-line"></i></div>';
            } else {
                thumbnail = '<div class="media-icon"><i class="ri-file-line"></i></div>';
            }
            
            html += `
                <div class="col-md-3 col-6">
                    <div class="media-item card ${selectedClass}" data-media-id="${media.id}">
                        <div class="media-item-thumbnail card-img-top">
                            ${thumbnail}
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text text-truncate small mb-0">${media.name}</p>
                        </div>
                        ${isSelected ? '<div class="selected-badge"><i class="ri-check-line"></i></div>' : ''}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Add click event to media items
        document.querySelectorAll('.media-item').forEach(item => {
            item.addEventListener('click', handleMediaItemClick);
        });
    }
    
    /**
     * Handle click on a media item
     */
    function handleMediaItemClick(e) {
        const mediaItem = e.currentTarget;
        const mediaId = parseInt(mediaItem.dataset.mediaId, 10);
        
        if (isMultipleSelection) {
            // Toggle selection
            const index = selectedMediaIds.indexOf(mediaId);
            
            if (index === -1) {
                // Add to selection
                selectedMediaIds.push(mediaId);
                mediaItem.classList.add('selected');
                mediaItem.innerHTML += '<div class="selected-badge"><i class="ri-check-line"></i></div>';
            } else {
                // Remove from selection
                selectedMediaIds.splice(index, 1);
                mediaItem.classList.remove('selected');
                mediaItem.querySelector('.selected-badge').remove();
            }
        } else {
            // Single selection mode
            selectedMediaIds = [mediaId];
            
            // Update UI
            document.querySelectorAll('.media-item').forEach(item => {
                item.classList.remove('selected');
                const badge = item.querySelector('.selected-badge');
                if (badge) badge.remove();
            });
            
            mediaItem.classList.add('selected');
            mediaItem.innerHTML += '<div class="selected-badge"><i class="ri-check-line"></i></div>';
        }
    }
    
    /**
     * Update pagination UI
     */
    function updatePagination() {
        document.querySelector('.current-page').textContent = currentPage;
        document.querySelector('.total-pages').textContent = totalPages;
        
        // Update buttons state
        const prevButton = document.querySelector('.prev-page');
        const nextButton = document.querySelector('.next-page');
        
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;
        
        // Add click events
        prevButton.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                loadMediaItems();
            }
        };
        
        nextButton.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                loadMediaItems();
            }
        };
    }
    
    /**
     * Confirm media selection and update field
     */
    function confirmMediaSelection() {
        if (!currentMediaPickerField) return;
        
        const input = currentMediaPickerField.querySelector('.media-picker-input');
        
        // Set selected media IDs to input
        input.value = selectedMediaIds.join(',');
        
        // Update preview
        updateMediaPreview();
        
        // Close modal
        mediaPickerModal.hide();
    }
    
    /**
     * Update media preview based on selection
     */
    function updateMediaPreview() {
        if (!currentMediaPickerField) return;
        
        // Find selected media items
        const selectedMedia = mediaItems.filter(media => selectedMediaIds.includes(media.id));
        
        if (isMultipleSelection) {
            // Multiple selection preview
            const previewContainer = currentMediaPickerField.querySelector('.selected-media-multiple');
            previewContainer.innerHTML = '';
            
            selectedMedia.forEach(media => {
                const isImage = media.mime_type.startsWith('image/');
                const thumbnail = isImage 
                    ? `<img src="${media.full_url}" class="img-fluid" style="height: 60px; width: auto;">` 
                    : `<div class="media-icon" style="height: 60px;"><i class="ri-file-line"></i></div>`;
                
                const itemHtml = `
                    <div class="col-auto selected-media-item" data-media-id="${media.id}">
                        <div class="position-relative border rounded p-1">
                            <button type="button" class="btn-close remove-selected-media position-absolute top-0 end-0 bg-light rounded-circle m-1"></button>
                            ${thumbnail}
                        </div>
                    </div>
                `;
                
                previewContainer.innerHTML += itemHtml;
            });
            
            // Reattach remove event listeners
            previewContainer.querySelectorAll('.remove-selected-media').forEach(button => {
                button.addEventListener('click', removeSelectedMedia);
            });
        } else {
            // Single selection preview
            const previewContainer = currentMediaPickerField.querySelector('.selected-media-container');
            const mediaImage = previewContainer.querySelector('.selected-media-image');
            const mediaName = previewContainer.querySelector('.selected-media-name');
            
            if (selectedMediaIds.length > 0 && selectedMedia.length > 0) {
                const media = selectedMedia[0];
                
                if (media.mime_type.startsWith('image/')) {
                    mediaImage.src = media.full_url;
                    mediaImage.style.display = '';
                } else {
                    mediaImage.style.display = 'none';
                }
                
                mediaName.textContent = media.name;
                previewContainer.style.display = '';
            } else {
                previewContainer.style.display = 'none';
            }
        }
        
        // Update button text
        const button = currentMediaPickerField.querySelector('.open-media-picker');
        button.innerHTML = selectedMediaIds.length > 0 
            ? '<i class="ri-image-edit-line me-1"></i> Change Media' 
            : '<i class="ri-image-add-line me-1"></i> Select Media';
    }
    
    /**
     * Remove a selected media item
     */
    function removeSelectedMedia(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const mediaField = e.target.closest('.media-picker-field');
        const input = mediaField.querySelector('.media-picker-input');
        
        if (mediaField.dataset.multiple === 'true') {
            // Multiple selection mode
            const mediaItem = e.target.closest('.selected-media-item');
            const mediaId = parseInt(mediaItem.dataset.mediaId, 10);
            
            // Remove from selected IDs
            const mediaIds = input.value.split(',').map(id => parseInt(id, 10));
            const filteredIds = mediaIds.filter(id => id !== mediaId);
            
            // Update input
            input.value = filteredIds.join(',');
            
            // Remove item from preview
            mediaItem.remove();
        } else {
            // Single selection mode
            input.value = '';
            
            const previewContainer = mediaField.querySelector('.selected-media-container');
            previewContainer.style.display = 'none';
        }
        
        // Update button text
        const button = mediaField.querySelector('.open-media-picker');
        button.innerHTML = '<i class="ri-image-add-line me-1"></i> Select Media';
    }
    
    // No need for duplicate initialization
});
