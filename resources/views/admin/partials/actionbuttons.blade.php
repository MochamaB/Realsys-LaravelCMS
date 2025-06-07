@props([
    'model',
    'type' => 'inline', // 'inline' or 'dropdown'
    'viewRoute' => null,
    'editRoute' => null,
    'destroyRoute' => null,
    'itemsRoute' => null,
    'previewRoute' => null,
    'resource' => null,
    'resourceName' => null, // Human-readable name for the modal
])

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal{{ $model->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this {{ $resourceName ?? $resource ?? 'item' }}?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route($destroyRoute, $model->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    @if($itemsRoute)
        <a href="{{ route($itemsRoute, $model->id) }}" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="Manage Items">
            <i class="ri-list-check"></i>
            @if($type === 'inline') 
                Manage 
                @if($itemsRoute === 'admin.templates.sections.index')
                    Sections 
                @else
                    Items 
                @endif
            @endif
        </a>
    @endif

    @if($previewRoute)
        <a href="{{ route($previewRoute, $model->id) }}" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" title="Preview" @if(str_contains($previewRoute, 'preview')) target="_blank" @endif>
            <i class="ri-eye-line"></i>
            @if($type === 'inline') Preview @endif
        </a>
    @endif

    @if($viewRoute)
        <a href="{{ route($viewRoute, $model->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
            <i class="ri-file-text-line"></i>
            @if($type === 'inline')  @endif
        </a>
    @endif

    @if($type === 'inline')
        @if($editRoute)
            <a href="{{ route($editRoute, $model->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                <i class="ri-pencil-line"></i>
                @if($type === 'inline')  @endif
            </a>
        @endif

        @if($destroyRoute)
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $model->id }}">
                <i class="ri-delete-bin-line"></i>
                @if($type === 'inline')  @endif
            </button>
        @endif
    @else
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="actionDropdown{{ $model->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $model->id }}">
                @if($itemsRoute)
                    <li>
                        <a class="dropdown-item" href="{{ route($itemsRoute, $model->id) }}">
                            <i class="ri-list-check me-1"></i> Manage Items
                        </a>
                    </li>
                @endif

                @if($previewRoute)
                    <li>
                        <a class="dropdown-item" href="{{ route($previewRoute, $model->id) }}" @if(str_contains($previewRoute, 'preview')) target="_blank" @endif>
                            <i class="ri-eye-line me-1"></i> Preview
                        </a>
                    </li>
                @endif

                @if($viewRoute)
                    <li>
                        <a class="dropdown-item" href="{{ route($viewRoute, $model->id) }}">
                            <i class="ri-file-text-line me-1"></i> Details
                        </a>
                    </li>
                @endif

                @if($editRoute)
                    <li>
                        <a class="dropdown-item" href="{{ route($editRoute, $model->id) }}">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                    </li>
                @endif

                @if($destroyRoute)
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $model->id }}">
                            <i class="ri-delete-bin-line me-1"></i> Delete
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
</div>