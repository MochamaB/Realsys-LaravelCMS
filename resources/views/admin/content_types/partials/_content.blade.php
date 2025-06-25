{{-- Content Type Content Items Partial View --}}
<div class="row">
    <!-- Content Items Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-file"></i> Content Items
                </h5>
                <div>
                    <a href="{{ route('admin.content-types.items.create', $contentType) }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Create New Item
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('admin.content-types.items.create', $contentType) }}" class="btn btn-secondary">
                            <i class="bx bx-import"></i> Import
                        </a>
                        <button class="btn btn-outline-secondary" type="button" id="export-content-items">
                            <i class="bx bx-export"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($contentType->contentItems->isEmpty())
                    <div class="text-center py-5">
                        <i class="bx bx-file bx-lg text-muted mb-3"></i>
                        <h5>No Content Items Yet</h5>
                        <p class="text-muted mb-3">Start creating content for this content type</p>
                        <a href="{{ route('admin.content-types.items.create', $contentType) }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Create Your First Item
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover" id="content-items-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 25px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll" value="option1">
                                        </div>
                                    </th>
                                    <th scope="col" style="width: 40%">Title/Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Editor</th>
                                    <th scope="col">Last Updated</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contentType->contentItems as $item)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="content_items[]" value="{{ $item->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if(method_exists($item, 'getFirstMediaUrl') && $item->getFirstMediaUrl('featured_image'))
                                                    <div class="flex-shrink-0 me-3">
                                                        <img src="{{ $item->getFirstMediaUrl('featured_image', 'thumb') }}" 
                                                             alt="{{ $item->title ?? 'Item image' }}"
                                                             class="img-fluid rounded" 
                                                             style="width: 48px; height: 48px; object-fit: cover;">
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('admin.content-types.items.edit', [$contentType, $item]) }}" class="text-decoration-none">
                                                        <strong>{{ $item->title ?? $item->name ?? 'Item #' . $item->id }}</strong>
                                                    </a>
                                                    @if($item->slug)
                                                        <div>
                                                            <code class="small">{{ $item->slug }}</code>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if(property_exists($item, 'status') || isset($item->status))
                                                @if($item->status === 'published' || $item->status === 'active')
                                                    <span class="badge bg-success">Published</span>
                                                @elseif($item->status === 'draft')
                                                    <span class="badge bg-secondary">Draft</span>
                                                @elseif($item->status === 'scheduled')
                                                    <span class="badge bg-info">Scheduled</span>
                                                @elseif($item->status === 'archived')
                                                    <span class="badge bg-warning">Archived</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ ucfirst($item->status) }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Draft</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->creator)
                                                {{ $item->creator }}
                                            @else
                                                <span class="text-muted">System created</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small text-muted">
                                                {{ $item->updated_at->format('M d, Y') }}
                                                <div>{{ $item->updated_at->format('h:i A') }}</div>
                                                @if($item->updater)
                                                    <div class="small text-muted">by {{ $item->updater->name }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.content-types.items.edit', [$contentType, $item]) }}" 
                                                   class="btn btn-primary" 
                                                   title="Edit Item">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.content-types.items.show', [$contentType, $item]) }}" 
                                                   class="btn btn-info" 
                                                   title="View Item">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger delete-item-btn" 
                                                        data-item-id="{{ $item->id }}"
                                                        data-item-title="{{ $item->title ?? $item->name ?? 'Item #' . $item->id }}"
                                                        title="Delete Item">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</div>

<!-- Delete Item Confirmation Modal -->
<div class="modal fade" id="deleteItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Content Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="delete-item-title"></span>"?</p>
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle"></i> This action cannot be undone. All field values associated with this item will be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-item-form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if available
    if (typeof $.fn.DataTable !== 'undefined' && document.getElementById('content-items-table')) {
        $('#content-items-table').DataTable({
            pageLength: 10,
            order: [[2, 'desc']], // Sort by updated date descending
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search items...",
                lengthMenu: "Show _MENU_ items",
                info: "Showing _START_ to _END_ of _TOTAL_ items"
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
    }
    
    // Delete confirmation
    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemTitle = this.dataset.itemTitle;
            
            document.getElementById('delete-item-title').textContent = itemTitle;
            document.getElementById('delete-item-form').action = 
                `{{ route('admin.content-types.items.index', $contentType) }}/${itemId}`;
                
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteItemModal'));
            deleteModal.show();
        });
    });
    
    // Export functionality
    document.getElementById('export-content-items')?.addEventListener('click', function() {
        // This would normally trigger an export endpoint
        // For now just show a toast notification
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: "Export functionality will be implemented soon",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "var(--bs-info)"
            }).showToast();
        } else {
            alert("Export functionality will be implemented soon");
        }
    });
});
</script>
@endpush
