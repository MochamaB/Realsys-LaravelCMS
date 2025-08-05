@extends('admin.layouts.master')

@section('title', 'Pages')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
 

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Pages List</h4>
                <div>
                    <a href="{{ route('admin.pages.create') }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> Create Page
                    </a>
                </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">Template</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Created</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pages as $page)
                                    <tr class="clickable-row" 
                                        data-href="{{ route('admin.pages.show', $page->id) }}"
                                        style="cursor: pointer;">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">{{ $page->title }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $page->slug }}</td>
                                        <td>{{ $page->template->name ?? 'No Template' }}</td>
                                        <td>
                                            {{ $page->status}}
                                           
                                        </td>
                                        <td>{{ $page->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <div class="view">
                                                    <a href="{{ route('admin.pages.show', $page->id) }}" class="btn btn-sm btn-soft-info">
                                                        <i class="ri-eye-fill align-bottom"></i>
                                                    </a>
                                                </div>
                                                <div class="edit">
                                                    <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-soft-success">
                                                        <i class="ri-pencil-fill align-bottom"></i>
                                                    </a>
                                                </div>
                                                <div class="remove">
                                                    <button class="btn btn-sm btn-soft-danger remove-item-btn" data-page-id="{{ $page->id }}">
                                                        <i class="ri-delete-bin-fill align-bottom"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No pages found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $pages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            document.querySelectorAll('.remove-item-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const pageId = this.getAttribute('data-page-id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                        cancelButtonClass: 'btn btn-danger w-xs mt-2',
                        confirmButtonText: 'Yes, delete it!',
                        buttonsStyling: false,
                        showCloseButton: true
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            // Create and submit a form to delete the page
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/admin/pages/${pageId}`;
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
