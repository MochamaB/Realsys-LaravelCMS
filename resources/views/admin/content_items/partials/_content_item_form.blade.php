{{-- 
  Content Item Form Partial 
  Parameters:
  - $contentType: The content type being edited
  - $contentItem: The content item being edited (null for create)
  - $prefix: Prefix for form element IDs to avoid conflicts in modals
--}}

@php
    $prefix = $prefix ?? '';
    $contentItem = $contentItem ?? null;
@endphp

<!-- Content Item Core Details -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ $contentType->name }} Details</h5>
    </div>
    <div class="card-body">
        <!-- Title -->
        <div class="mb-3">
            <label for="{{ $prefix }}title" class="form-label">Title</label>
            <input type="text" class="form-control" id="{{ $prefix }}title" name="title" 
                value="{{ old('title', $contentItem->title ?? '') }}" required>
        </div>
        
      
        
        <!-- Slug -->
        <div class="mb-3">
            <label for="{{ $prefix }}slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="{{ $prefix }}slug" name="slug" 
                value="{{ old('slug', $contentItem->slug ?? '') }}">
            <small class="text-muted">Leave empty to auto-generate from title</small>
        </div>
          <!-- Status -->
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" required>
                <option value="draft" {{ old('status', $contentItem->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $contentItem->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ old('status', $contentItem->status ?? '') == 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>
        
    </div>
</div>

<!-- Publication Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">Actions</h6>
    </div>
    <div class="card-body">
        <!-- Language Selection -->
        <div class="mb-3">
            <label for="{{ $prefix }}language" class="form-label">Language</label>
            <select class="form-select" id="{{ $prefix }}language" name="language">
                <option value="en" selected>English</option>
                <option value="fr">French</option>
                <option value="es">Spanish</option>
            </select>
        </div>

        <!-- Preview Button -->
        <div class="mb-3">
            @if($contentItem)
                <a href="{{ route('admin.content-types.items.preview', [$contentType, $contentItem]) }}" 
                   target="_blank" class="btn btn-secondary w-100">
                    <i class="fas fa-eye me-1"></i> Preview
                </a>
            @else
                <button type="button" class="btn btn-secondary w-100 disabled">
                    <i class="fas fa-eye me-1"></i> Preview (Save First)
                </button>
            @endif
        </div>

        <!-- Publication Actions -->
        <div class="d-grid gap-2">
           
            <button type="button" class="btn btn-success" id="{{ $prefix }}publish-btn">
                <i class="fas fa-paper-plane me-1"></i> Publish
            </button>
           
        </div>
    </div>
</div>

<!-- Workflow Information -->
@if($contentItem)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">Information</h6>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <small class="text-muted">Created by</small>
            <p>{{ $contentItem->creator->name ?? 'System' }} on {{ $contentItem->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div class="mb-0">
            <small class="text-muted">Last updated by</small>
            <p>{{ $contentItem->updater->name ?? 'System' }} on {{ $contentItem->updated_at->format('M d, Y H:i') }}</p>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.getElementById('{{ $prefix }}title');
        const slugInput = document.getElementById('{{ $prefix }}slug');
        
        // Auto-generate slug from title
        if (titleInput && slugInput) {
            titleInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    slugInput.value = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            });
        }

        // Publish button action (example)
        const publishBtn = document.getElementById('{{ $prefix }}publish-btn');
        if (publishBtn) {
            publishBtn.addEventListener('click', function() {
                // Set status to published
                const statusSelect = document.getElementById('{{ $prefix }}status');
                if (statusSelect) {
                    statusSelect.value = 'published';
                }
                
                // Submit the form
                document.getElementById('{{ $prefix }}content-item-form').submit();
            });
        }
    });
</script>
@endpush
