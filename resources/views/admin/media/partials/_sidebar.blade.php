<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center mb-4">
            <div class="flex-grow-1">
                <h5 class="mb-0 fs-15">Media Categories</h5>
            </div>
            <div class="flex-shrink-0">
                <button type="button" class="btn btn-sm btn-soft-primary" data-bs-toggle="offcanvas" data-bs-target="#folderSidebar">
                    <i class="ri-folders-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-soft-info" data-bs-toggle="offcanvas" data-bs-target="#tagSidebar">
                    <i class="ri-price-tag-3-line"></i>
                </button>
            </div>
        </div>
        
        <div>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.media.index') }}">
                        <i class="ri-folder-2-line align-middle fs-16 me-2"></i> <span>All Media</span>
                        <span class="badge bg-soft-success text-success ms-auto">{{ $stats['total'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.media.filter', ['type' => 'image']) }}">
                        <i class="ri-image-line align-middle fs-16 me-2"></i> <span>Images</span>
                        <span class="badge bg-soft-success text-success ms-auto">{{ $stats['images'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.media.filter', ['type' => 'video']) }}">
                        <i class="ri-video-line align-middle fs-16 me-2"></i> <span>Videos</span>
                        <span class="badge bg-soft-success text-success ms-auto">{{ $stats['videos'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.media.filter', ['type' => 'audio']) }}">
                        <i class="ri-music-2-line align-middle fs-16 me-2"></i> <span>Audio</span>
                        <span class="badge bg-soft-success text-success ms-auto">{{ $stats['audio'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.media.filter', ['type' => 'application']) }}">
                        <i class="ri-file-text-line align-middle fs-16 me-2"></i> <span>Documents</span>
                        <span class="badge bg-soft-success text-success ms-auto">{{ $stats['documents'] ?? 0 }}</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="mt-4 pt-3 border-top border-top-dashed">
            <h5 class="mb-3 fs-15">Collections</h5>
            
            <div>
                <ul class="list-unstyled mb-0 vstack gap-2">
                    @foreach($collections as $collection)
                    <li>
                        <a href="{{ route('admin.media.filter', ['collection' => $collection]) }}" class="text-muted d-flex align-items-center">
                            <i class="ri-stack-line align-middle fs-16 me-2"></i> <span>{{ ucfirst($collection) }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <div class="mt-4 pt-3 border-top border-top-dashed">
            <h5 class="mb-3 fs-15">Storage Usage</h5>
            
            <div class="card border shadow-none mb-0">
                <div class="card-body">
                    <div class="mb-2 d-flex align-items-center">
                        <h6 class="mb-0 fs-13">Media Storage </h6>
                        <div class="ms-auto fs-12 text-success">{{ number_format($stats['total'] / 1000, 1) }}k Files</div>
                    </div>
                    <div class="progress animated-progress custom-progress mb-1">
                        <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-muted mb-0 fs-12">25% of 100GB used</p>
                </div>
            </div>
        </div>
    </div>
</div>
