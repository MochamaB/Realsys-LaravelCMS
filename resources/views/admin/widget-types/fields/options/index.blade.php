@extends('admin.layouts.master')

@section('title', 'Field Options')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Dragula css -->
    <link href="{{ asset('assets/admin/libs/dragula/dragula.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Field Options for "{{ $field->name }}"</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.index') }}">Widget Types</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.fields.index', $field->widget_type_id) }}">Fields</a></li>
                        <li class="breadcrumb-item active">Options</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Field Options</h4>
                    <div>
                        <a href="{{ route('admin.widget-types.fields.options.create', $field) }}" class="btn btn-success add-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Add Option
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <h4 class="alert-heading"><i class="ri-information-line me-1"></i> Field Information</h4>
                                <p class="mb-0"><strong>Name:</strong> {{ $field->name }}</p>
                                <p class="mb-0"><strong>Type:</strong> {{ ucfirst($field->field_type) }}</p>
                                <p class="mb-0"><strong>Key:</strong> {{ $field->key }}</p>
                                <p class="mb-0"><strong>Required:</strong> {{ $field->is_required ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>

                    @if(count($options) > 0)
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 40px;">#</th>
                                        <th scope="col">Value</th>
                                        <th scope="col">Label</th>
                                        <th scope="col" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="options-list">
                                    @foreach($options as $option)
                                    <tr data-id="{{ $option->id }}">
                                        <td>
                                            <div class="avatar-xs">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    <i class="ri-drag-move-fill"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td>{{ $option->value }}</td>
                                        <td>{{ $option->label }}</td>
                                        <td>
                                            <div class="hstack gap-3 fs-15">
                                                <a href="{{ route('admin.widget-types.fields.options.edit', [$field, $option]) }}" class="link-primary">
                                                    <i class="ri-pencil-fill"></i>
                                                </a>
                                                <a href="#" class="link-danger delete-option" data-id="{{ $option->id }}" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                    <i class="ri-delete-bin-5-line"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-4">
                            <div class="avatar-md mx-auto mb-4">
                                <div class="avatar-title bg-light rounded-circle text-primary fs-24">
                                    <i class="ri-file-list-3-line"></i>
                                </div>
                            </div>
                            <h5 class="mb-3">No options found</h5>
                            <p class="text-muted mb-4">Create your first option by clicking the button below.</p>
                            <a href="{{ route('admin.widget-types.fields.options.create', $field) }}" class="btn btn-success">
                                <i class="ri-add-line align-bottom me-1"></i> Add Option
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this field option? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" action="" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Dragula js -->
    <script src="{{ asset('assets/admin/libs/dragula/dragula.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete option
            const deleteButtons = document.querySelectorAll('.delete-option');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const optionId = this.getAttribute('data-id');
                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = "{{ route('admin.widget-types.fields.options.destroy', [$field, '']) }}/" + optionId;
                });
            });

            // Initialize Dragula for reordering options
            if (document.getElementById('options-list')) {
                const drake = dragula([document.getElementById('options-list')]);
                
                drake.on('drop', function() {
                    const items = document.querySelectorAll('#options-list tr');
                    const order = Array.from(items).map(item => item.getAttribute('data-id'));
                    
                    // Save the new order
                    fetch("{{ route('admin.widget-types.fields.options.reorder', $field) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Options reordered successfully',
                                icon: 'success',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to reorder options',
                                icon: 'error',
                                confirmButtonClass: 'btn btn-danger w-xs me-2',
                                buttonsStyling: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred',
                            icon: 'error',
                            confirmButtonClass: 'btn btn-danger w-xs me-2',
                            buttonsStyling: false
                        });
                    });
                });
            }
        });
    </script>
@endsection
