@extends('admin.layouts.master')

@section('title', 'Page Sections')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Page Sections - {{ $page->title }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
                        <li class="breadcrumb-item active">Sections</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Sections List</h4>
                    <div>
                        <a href="{{ route('admin.pages.sections.create', $page) }}" class="btn btn-success add-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Add Section
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Template Section</th>
                                    <th scope="col">Identifier</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="section-list">
                                @forelse($sections as $index => $section)
                                    <tr data-section-id="{{ $section->id }}">
                                        <td>
                                            <div class="avatar-xs">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="section-handle me-2 cursor-move">
                                                    <i class="ri-drag-move-2-fill text-muted"></i>
                                                </span>
                                                <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="fw-medium">
                                                    {{ $section->name }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $section->templateSection->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ $section->identifier }}</code>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch form-switch-success">
                                                <form action="{{ route('admin.pages.sections.toggle', [$page, $section]) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="checkbox" 
                                                           class="form-check-input section-status-toggle" 
                                                           {{ $section->is_active ? 'checked' : '' }}>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" 
                                                   class="btn btn-sm btn-success edit-item-btn">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <form action="{{ route('admin.pages.sections.destroy', [$page, $section]) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger delete-section">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No sections found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Sortable js -->
    <script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle section deletion
            document.querySelectorAll('.delete-section').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        customClass: {
                            confirmButton: 'btn btn-danger me-3',
                            cancelButton: 'btn btn-light'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Handle section status toggle
            document.querySelectorAll('.section-status-toggle').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const form = this.closest('form');
                    form.submit();
                });
            });

            // Handle section reordering
            const sectionList = document.querySelector('.section-list');
            if (sectionList) {
                new Sortable(sectionList, {
                    handle: '.section-handle',
                    animation: 150,
                    onEnd: function(evt) {
                        const items = evt.to.children;
                        const orderData = [];
                        
                        Array.from(items).forEach(function(item, index) {
                            orderData.push({
                                id: item.dataset.sectionId,
                                order: index
                            });
                        });

                        // Update order via AJAX
                        fetch('{{ route("admin.pages.sections.reorder", $page) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ sections: orderData })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Section order updated successfully',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    },
                                    buttonsStyling: false
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to update section order',
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        });
                    }
                });
            }
        });
    </script>
@endsection
