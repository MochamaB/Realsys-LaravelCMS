/*
 * Media Library - Media management components
 */

document.addEventListener("DOMContentLoaded", function () {
    'use strict';

    // Initialize Dropzone
    if (document.getElementById('mediaDropzone')) {
        // Configure Dropzone
        const myDropzone = new Dropzone("#mediaDropzone", {
            url: document.getElementById('mediaDropzone').action,
            autoProcessQueue: false,
            addRemoveLinks: true,
            parallelUploads: 5,
            maxFilesize: 10, // 10MB
            acceptedFiles: "image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,video/*,audio/*,application/zip,application/x-rar-compressed",
            dictDefaultMessage: "Drop files here to upload",
            init: function () {
                const submitButton = document.getElementById("uploadMediaBtn");
                const myDropzone = this;

                submitButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (myDropzone.getQueuedFiles().length > 0) {
                        // Set collection name for all files
                        myDropzone.on("sending", function (file, xhr, formData) {
                            formData.append("collection_name", document.getElementById("collectionSelect").value);
                        });
                        
                        myDropzone.processQueue();
                    } else {
                        alert("Please add files to upload");
                    }
                });

                this.on("success", function (file, response) {
                    if (response.success) {
                        console.log("File uploaded successfully:", file.name);
                        console.log("Media object:", response.media);
                        
                        // Show success message
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed top-0 end-0 p-3 mt-5';
                        toast.style.zIndex = '1080';
                        toast.innerHTML = `
                            <div class="toast show bg-success text-white" role="alert">
                                <div class="toast-header bg-success text-white">
                                    <strong class="me-auto">Success</strong>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                                </div>
                                <div class="toast-body">
                                    File ${file.name} uploaded successfully
                                </div>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);
                    } else {
                        console.error("Upload response indicates failure:", response);
                    }
                });

                this.on("error", function (file, errorMessage, xhr) {
                    console.error("Error uploading file:", file.name);
                    console.error("Error message:", errorMessage);
                    
                    if (xhr) {
                        console.error("Server response:", xhr.responseText);
                    }
                    
                    // Show error message
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed top-0 end-0 p-3 mt-5';
                    toast.style.zIndex = '1080';
                    toast.innerHTML = `
                        <div class="toast show bg-danger text-white" role="alert">
                            <div class="toast-header bg-danger text-white">
                                <strong class="me-auto">Upload Error</strong>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                            </div>
                            <div class="toast-body">
                                Failed to upload ${file.name}: ${errorMessage}
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 5000);
                });

                // Track upload status
                let uploadSuccessCount = 0;
                let uploadErrorCount = 0;
                
                // Count successful uploads
                this.on("success", function() {
                    uploadSuccessCount++;
                });
                
                // Count failed uploads
                this.on("error", function() {
                    uploadErrorCount++;
                });
                
                this.on("queuecomplete", function () {
                    // Show summary message
                    const totalUploaded = uploadSuccessCount;
                    const totalFailed = uploadErrorCount;
                    
                    console.log(`Upload summary: ${totalUploaded} files uploaded, ${totalFailed} files failed`);
                    
                    // Only reload if at least one file was uploaded successfully
                    if (uploadSuccessCount > 0) {
                        // Close the modal
                        bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                        
                        // Show loading message
                        const loadingToast = document.createElement('div');
                        loadingToast.className = 'position-fixed top-0 end-0 p-3';
                        loadingToast.style.zIndex = '1080';
                        loadingToast.id = 'loadingToast';
                        loadingToast.innerHTML = `
                            <div class="toast show bg-info text-white" role="alert">
                                <div class="toast-body d-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Refreshing media library...
                                </div>
                            </div>
                        `;
                        document.body.appendChild(loadingToast);
                        
                        // Use a longer delay to ensure server processing is complete
                        setTimeout(function() {
                            window.location.reload();
                        }, 2500);
                    }
                    
                    // Reset counters
                    uploadSuccessCount = 0;
                    uploadErrorCount = 0;
                });
            }
        });

        // Reset dropzone when modal is closed
        document.getElementById('uploadModal').addEventListener('hidden.bs.modal', function () {
            if (myDropzone) {
                myDropzone.removeAllFiles(true);
            }
        });
    }

    // Empty state upload button
    const emptyUploadBtn = document.getElementById('emptyUploadMedia');
    if (emptyUploadBtn) {
        emptyUploadBtn.addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        });
    }

    // Empty state upload button (list view)
    const emptyUploadListBtn = document.getElementById('emptyUploadMediaList');
    if (emptyUploadListBtn) {
        emptyUploadListBtn.addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        });
    }

    // Media Detail Sidebar
    const setupMediaDetailsHandlers = () => {
        document.querySelectorAll('.show-media-details').forEach(element => {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const mediaId = this.getAttribute('data-id');
                loadMediaDetails(mediaId);
            });
        });

        document.querySelectorAll('.delete-media').forEach(element => {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const mediaId = this.getAttribute('data-id');
                deleteMedia(mediaId);
            });
        });
    };

    // Run the setup once the DOM is loaded
    setupMediaDetailsHandlers();

    // Load media details
    function loadMediaDetails(mediaId) {
        fetch(`/admin/media/${mediaId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateMediaSidebar(data.media);
                const mediaSidebar = new bootstrap.Offcanvas(document.getElementById('mediaDetailSidebar'));
                mediaSidebar.show();
            } else {
                console.error('Failed to load media details');
            }
        })
        .catch(error => {
            console.error('Error loading media details:', error);
        });
    }

    // Populate media sidebar with data
    function populateMediaSidebar(media) {
        // Set basic info
        document.getElementById('mediaTitle').textContent = media.name;
        document.getElementById('detailFileName').textContent = media.file_name;
        document.getElementById('detailFileSize').textContent = formatBytes(media.size);
        document.getElementById('detailFileType').textContent = media.mime_type;
        document.getElementById('detailCollection').textContent = media.collection_name;
        document.getElementById('detailUploadDate').textContent = formatDate(media.created_at);
        document.getElementById('detailModifiedDate').textContent = formatDate(media.updated_at);
        
        // Set form values
        document.getElementById('mediaId').value = media.id;
        document.getElementById('mediaName').value = media.name;
        document.getElementById('mediaAlt').value = media.custom_properties?.alt || '';
        document.getElementById('mediaTitle').value = media.custom_properties?.title || '';
        document.getElementById('mediaCaption').value = media.custom_properties?.caption || '';
        
        // Set URL
        document.getElementById('fileUrl').value = media.full_url;
        
        // Set download URL
        document.getElementById('detailDownloadBtn').href = media.full_url;
        document.getElementById('detailDownloadBtn').download = media.file_name;
        
        // Set delete action
        document.getElementById('detailDeleteBtn').setAttribute('data-id', media.id);
        
        // Preview content
        const previewContainer = document.querySelector('.preview-container');
        previewContainer.innerHTML = '';
        
        if (media.mime_type.startsWith('image/')) {
            // Image preview
            const img = document.createElement('img');
            img.src = media.full_url;
            img.alt = media.custom_properties?.alt || media.name;
            img.className = 'img-fluid rounded';
            img.style.maxHeight = '300px';
            previewContainer.appendChild(img);
        } else if (media.mime_type.startsWith('video/')) {
            // Video preview
            const video = document.createElement('video');
            video.src = media.full_url;
            video.controls = true;
            video.className = 'img-fluid rounded';
            video.style.maxHeight = '300px';
            previewContainer.appendChild(video);
        } else if (media.mime_type.startsWith('audio/')) {
            // Audio preview
            const audio = document.createElement('audio');
            audio.src = media.full_url;
            audio.controls = true;
            audio.className = 'w-100';
            previewContainer.appendChild(audio);
        } else if (media.mime_type === 'application/pdf') {
            // PDF icon
            const icon = document.createElement('i');
            icon.className = 'ri-file-pdf-fill display-1 text-danger';
            previewContainer.appendChild(icon);
        } else {
            // Generic file icon
            const icon = document.createElement('i');
            icon.className = 'ri-file-text-fill display-1 text-primary';
            previewContainer.appendChild(icon);
        }
        
        // Add event listeners
        document.getElementById('detailDeleteBtn').addEventListener('click', function(e) {
            e.preventDefault();
            deleteMedia(media.id);
        });
        
        document.getElementById('copyUrlBtn').addEventListener('click', function() {
            const urlInput = document.getElementById('fileUrl');
            urlInput.select();
            document.execCommand('copy');
            this.innerHTML = 'Copied!';
            setTimeout(() => {
                this.innerHTML = 'Copy';
            }, 2000);
        });
        
        // Setup form submission
        document.getElementById('mediaMetadataForm').addEventListener('submit', function(e) {
            e.preventDefault();
            updateMediaMetadata(media.id);
        });
    }

    // Update media metadata
    function updateMediaMetadata(mediaId) {
        const form = document.getElementById('mediaMetadataForm');
        const formData = new FormData(form);
        
        fetch(`/admin/media/${mediaId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Media metadata updated successfully',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                console.error('Failed to update media metadata');
            }
        })
        .catch(error => {
            console.error('Error updating media metadata:', error);
        });
    }

    // Delete media
    function deleteMedia(mediaId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/media/${mediaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close sidebar if open
                        if (document.getElementById('mediaDetailSidebar').classList.contains('show')) {
                            const mediaSidebar = bootstrap.Offcanvas.getInstance(document.getElementById('mediaDetailSidebar'));
                            mediaSidebar.hide();
                        }
                        
                        // Remove element from DOM
                        const mediaElements = document.querySelectorAll(`.media-item[data-id="${mediaId}"]`);
                        mediaElements.forEach(el => el.remove());
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Your file has been deleted.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        // Reload after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        console.error('Failed to delete media');
                    }
                })
                .catch(error => {
                    console.error('Error deleting media:', error);
                });
            }
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search');
    if (searchInput) {
        let typingTimer;
        const doneTypingInterval = 500; // ms
        
        searchInput.addEventListener('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(performSearch, doneTypingInterval);
        });
        
        searchInput.addEventListener('keydown', function() {
            clearTimeout(typingTimer);
        });
    }
    
    function performSearch() {
        const searchTerm = document.querySelector('.search').value;
        if (searchTerm.length < 2) return;
        
        window.location.href = `/admin/media?search=${encodeURIComponent(searchTerm)}`;
    }
    
    // Helper functions
    function formatBytes(bytes, decimals = 2) {
        if (!bytes) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    function formatDate(dateString) {
        if (!dateString) return '-';
        
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    // Add Str helper for blade templates
    window.Str = {
        formatBytes: formatBytes,
        limit: function(string, length) {
            if (string.length <= length) return string;
            return string.substring(0, length) + '...';
        }
    };
});
