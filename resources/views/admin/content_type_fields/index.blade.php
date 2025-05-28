@extends('admin.layouts.master')

@section('title', 'Content Type Fields')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Fields for {{ $contentType->name }}</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.content-types.fields.create', $contentType) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add Field
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card for fields list -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Fields</h5>
                        <a href="{{ route('admin.content-types.show', $contentType) }}" class="btn btn-sm btn-secondary">
                            Back to Content Type
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($fields->isEmpty())
                        <div class="text-center p-5">
                            <h4>No fields found</h4>
                            <p>Create fields to define the structure of your content type.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Name</th>
                                        <th>Key</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-fields">
                                    @foreach($fields as $field)
                                        <tr data-id="{{ $field->id }}">
                                            <td>
                                                <i class="fas fa-grip-vertical handle cursor-move text-muted"></i>
                                            </td>
                                            <td>{{ $field->name }}</td>
                                            <td><code>{{ $field->key }}</code></td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $field->type)) }}</td>
                                            <td>
                                                @if($field->is_required)
                                                    <span class="badge bg-success">Required</span>
                                                @else
                                                    <span class="badge bg-secondary">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
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
</div>
@endsection

@push('scripts')
@if(!$fields->isEmpty())
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sortableList = document.getElementById('sortable-fields');
        new Sortable(sortableList, {
            handle: '.handle',
            animation: 150,
            onEnd: function() {
                // Get the new order of field IDs
                const fieldIds = Array.from(sortableList.querySelectorAll('tr')).map(tr => tr.dataset.id);
                
                // Send the new order to the server
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
                        // Optional: Show success message
                    }
                })
                .catch(error => {
                    console.error('Error reordering fields:', error);
                });
            }
        });
    });
</script>
@endif
@endpush
