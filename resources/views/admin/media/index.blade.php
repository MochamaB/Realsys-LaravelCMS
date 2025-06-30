@extends('admin.layouts.master')

@section('title') Media Library @endsection

@section('css')
    <!-- Filepond css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
    <!-- Dropzone css -->
    <link href="{{ asset('assets/admin/libs/dropzone/dropzone.css') }}" rel="stylesheet" type="text/css" />
    <!-- Media Organization css -->
    <link href="{{ asset('assets/admin/css/media-organization.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
   

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Media Library</h5>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-success " id="uploadMedia">
                                <i class="ri-upload-2-fill me-1 align-bottom"></i> Upload Files
                            </button>
                            <button type="button" class="btn btn-primary " id="createFolder">
                                <i class="ri-folder-add-line me-1 align-bottom"></i> Create Folder
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <!-- Left sidebar -->
                        <div class="col-lg-3 col-md-4">
                            @include('admin.media.partials._sidebar')
                        </div>
                        <!-- End Left sidebar -->

                        <!-- Right content -->
                        <div class="col-lg-9 col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <div class="search-box">
                                        <input type="text" class="form-control search" placeholder="Search media files...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ms-3">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-light btn-sm view-btn grid-view active">
                                            <i class="ri-grid-fill"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm view-btn list-view">
                                            <i class="ri-list-check"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Files container -->
                            <div class="media-container">
                                <!-- Grid view -->
                                <div class="grid-view-container" id="grid-view">
                                    @include('admin.media.partials._grid_view')
                                </div>
                                
                                <!-- List view -->
                                <div class="list-view-container d-none" id="list-view">
                                    @include('admin.media.partials._list_view')
                                </div>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex align-items-center mt-3">
                                <div class="flex-grow-1">
                                    Showing <span class="fw-medium">{{ $mediaItems->firstItem() ?? 0 }}</span> to 
                                    <span class="fw-medium">{{ $mediaItems->lastItem() ?? 0 }}</span> of 
                                    <span class="fw-medium">{{ $mediaItems->total() ?? 0 }}</span> results
                                </div>
                                <div class="flex-shrink-0">
                                    {{ $mediaItems->links() }}
                                </div>
                            </div>
                        </div>
                        <!-- End Right content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Upload Modal -->
    @include('admin.media.partials._upload_modal')

    <!-- File Detail Sidebar -->
    @include('admin.media.partials._detail_sidebar')
    
    <!-- Folder Sidebar -->
    @include('admin.media.partials._folder_sidebar')
    
    <!-- Tag Sidebar -->
    @include('admin.media.partials._tag_sidebar')
    
    <!-- Batch Operations Toolbar -->
    @include('admin.media.partials._batch_toolbar')
    
    <!-- Batch Move Modal -->
    @include('admin.media.partials._batch_move_modal')
    
    <!-- Batch Tag Modal -->
    @include('admin.media.partials._batch_tag_modal')
@endsection

@section('js')
    <!-- filepond js -->
    <script src="{{ asset('assets/admin/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
    
    <!-- Media Organization js -->
    <script src="{{ asset('assets/admin/js/media-folders.js') }}"></script>
    <script src="{{ asset('assets/admin/js/media-tags.js') }}"></script>
    <script src="{{ asset('assets/admin/js/media-batch.js') }}"></script>
    <script src="{{ asset('assets/admin/js/media-detail-extensions.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js') }}"></script>
    
    <!-- Dropzone js -->
    <script src="{{ asset('assets/admin/libs/dropzone/dropzone-min.js') }}"></script>
    
    <!-- Media Library js -->
    <script src="{{ asset('assets/admin/js/media-library.init.js') }}"></script>

    <script>
        // Initialize the media library
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize event handlers for media details
            const setupMediaDetailsHandlers = () => {
                document.querySelectorAll('.show-media-details').forEach(element => {
                    element.addEventListener('click', function(e) {
                        e.preventDefault();
                        const mediaId = this.getAttribute('data-id');
                        loadMediaDetails(mediaId);
                    });
                });
            };
            
            // Load media details from server
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
                        // Set basic info
                        document.getElementById('mediaTitle').textContent = data.media.name;
                        document.getElementById('detailFileName').textContent = data.media.file_name;
                        document.getElementById('detailFileSize').textContent = formatBytes(data.media.size);
                        document.getElementById('detailFileType').textContent = data.media.mime_type;
                        document.getElementById('detailCollection').textContent = data.media.collection_name;
                        document.getElementById('detailUploadDate').textContent = formatDate(data.media.created_at);
                        document.getElementById('detailModifiedDate').textContent = formatDate(data.media.updated_at);
                        
                        // Set form values
                        document.getElementById('mediaId').value = data.media.id;
                        document.getElementById('mediaName').value = data.media.name;
                        document.getElementById('mediaAlt').value = data.media.custom_properties?.alt || '';
                        document.getElementById('mediaTitle').value = data.media.custom_properties?.title || '';
                        document.getElementById('mediaCaption').value = data.media.custom_properties?.caption || '';
                        
                        // Set URL
                        document.getElementById('fileUrl').value = data.media.full_url;
                        
                        // Set download URL
                        document.getElementById('detailDownloadBtn').href = data.media.full_url;
                        document.getElementById('detailDownloadBtn').download = data.media.file_name;
                        
                        // Set delete action
                        document.getElementById('detailDeleteBtn').setAttribute('data-id', data.media.id);
                        
                        // Preview content
                        const previewContainer = document.querySelector('.preview-container');
                        previewContainer.innerHTML = '';
                        
                        if (data.media.mime_type.startsWith('image/')) {
                            // Image preview
                            const img = document.createElement('img');
                            img.src = data.media.full_url;
                            img.alt = data.media.custom_properties?.alt || data.media.name;
                            img.className = 'img-fluid rounded';
                            img.style.maxHeight = '300px';
                            previewContainer.appendChild(img);
                        } else if (data.media.mime_type.startsWith('video/')) {
                            // Video preview
                            const video = document.createElement('video');
                            video.src = data.media.full_url;
                            video.controls = true;
                            video.className = 'img-fluid rounded';
                            video.style.maxHeight = '300px';
                            previewContainer.appendChild(video);
                        } else if (data.media.mime_type.startsWith('audio/')) {
                            // Audio preview
                            const audio = document.createElement('audio');
                            audio.src = data.media.full_url;
                            audio.controls = true;
                            audio.className = 'w-100';
                            previewContainer.appendChild(audio);
                        } else if (data.media.mime_type === 'application/pdf') {
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
                        
                        // Show the sidebar
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
            
            // Format bytes to human readable format
            function formatBytes(bytes, decimals = 2) {
                if (!bytes) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }
            
            // Format date
            function formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            // Initialize media details handlers
            setupMediaDetailsHandlers();
            
            // Switch between grid and list views
            document.querySelectorAll('.view-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    if (this.classList.contains('grid-view')) {
                        document.getElementById('grid-view').classList.remove('d-none');
                        document.getElementById('list-view').classList.add('d-none');
                    } else {
                        document.getElementById('grid-view').classList.add('d-none');
                        document.getElementById('list-view').classList.remove('d-none');
                    }
                });
            });

            // Handle file upload modal
            document.getElementById('uploadMedia').addEventListener('click', function() {
                var uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
                uploadModal.show();
            });
        });
    </script>
@endsection
