@extends('admin.layouts.master')

@section('title', 'Content Type Fields')

@section('content')
<div class="container-fluid">
    
    <!-- Card for fields list -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Fields for {{ $contentType->name }}</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" id="save-fields-order" style="display: none;">
                                <i class="fas fa-save me-1"></i> Save Order
                            </button>
                            <a href="{{ route('admin.content-types.fields.create', $contentType) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Add Field
                            </a>
                            <a href="{{ route('admin.content-types.show', $contentType) }}" class="btn btn-secondary">
                                Back to Content Type
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($fields->isEmpty())
                        <div class="text-center p-5">
                            <h4>No fields found</h4>
                            <p>Create fields to define the structure of your content type.</p>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i> Drag and drop fields to reorder. Click "Save Order" when done.
                        </div>
                        <ul class="dd-list list-unstyled" id="sortable-fields">
                            @foreach($fields as $field)
                                <li class="dd-item border rounded mb-2 p-2 d-flex align-items-center" data-id="{{ $field->id }}">
                                    <span class="dd-handle me-3 text-muted" style="cursor: move; font-size: 1.3em;">
                                        <i class="fas fa-grip-vertical"></i>
                                    </span>
                                    <div class="flex-grow-1 d-flex flex-wrap align-items-center gap-3">
                                        <div><strong>{{ $field->name }}</strong></div>
                                        <div><code>{{ $field->slug }}</code></div>
                                        <div><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $field->field_type)) }}</span></div>
                                        <div>
                                            @if($field->is_required)
                                                <span class="badge bg-success">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Optional</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ms-auto btn-group">
                                        <a href="{{ route('admin.content-types.fields.edit', [$contentType, $field]) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.content-types.fields.destroy', [$contentType, $field]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this field?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!$fields->isEmpty())
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sortableList = document.getElementById('sortable-fields');
        let currentOrder = Array.from(sortableList.querySelectorAll('.dd-item')).map(li => li.dataset.id);
        let orderChanged = false;
        const saveBtn = document.getElementById('save-fields-order');

        const sortable = new Sortable(sortableList, {
            handle: '.dd-handle',
            animation: 150,
            onEnd: function() {
                const newOrder = Array.from(sortableList.querySelectorAll('.dd-item')).map(li => li.dataset.id);
                orderChanged = JSON.stringify(newOrder) !== JSON.stringify(currentOrder);
                saveBtn.style.display = orderChanged ? '' : 'none';
            }
        });

        saveBtn.addEventListener('click', function() {
            const fieldIds = Array.from(sortableList.querySelectorAll('.dd-item')).map(li => li.dataset.id);
            saveBtn.innerHTML = '<i class=\'fas fa-spinner fa-spin me-1\'></i> Saving...';
            saveBtn.disabled = true;
            fetch('{{ route('admin.content-types.fields.reorder', $contentType) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ field_ids: fieldIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentOrder = fieldIds;
                    orderChanged = false;
                    saveBtn.style.display = 'none';
                    if (window.toastr) {
                        toastr.success('Field order saved successfully!');
                    } else {
                        alert('Field order saved successfully!');
                    }
                } else {
                    if (window.toastr) {
                        toastr.error('Failed to save field order.');
                    } else {
                        alert('Failed to save field order.');
                    }
                }
            })
            .catch(error => {
                if (window.toastr) {
                    toastr.error('Failed to save field order.');
                } else {
                    alert('Failed to save field order.');
                }
                console.error('Error reordering fields:', error);
            })
            .finally(() => {
                saveBtn.innerHTML = '<i class=\'fas fa-save me-1\'></i> Save Order';
                saveBtn.disabled = false;
            });
        });
    });
</script>
@endif
@endpush
