<!-- resources/views/admin/content_types/index.blade.php -->
@extends('admin.layouts.master')

@section('title', 'Content Types')
@push('css')
.dropdown-menu {
    z-index: 1000 !important;
}
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    display: none;
}

.dropdown-menu.show {
    display: block;
}
@endpush

@section('content')
<!-- Page title is now handled by the breadcrumb component -->

   
    <!-- Card for content types list -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Content Types</h4>
                    <div>
                        <a href="{{ route('admin.content-types.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add Content Type
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($contentTypes->isEmpty())
                        <div class="text-center p-5">
                            <h4>No content types found</h4>
                            <p>Create a new content type to get started.</p>
                        </div>
                    @else
                        <div class="">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Icon</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Fields</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contentTypes as $contentType)
                                    <tr class="clickable-row" 
                                            data-href="{{ route('admin.content-types.show', $contentType->id) }}"
                                            style="cursor: pointer;">
                                            <td>{{ $loop->iteration }}</td>
                                            <td><i class="bx bx-{{ $contentType->icon }}"></i></td>
                                            <td>{{ $contentType->name }}</td>
                                            <td>{{ Str::limit($contentType->description, 50) }}</td>
                                            <td>{{ $contentType->fields->count() }}</td>
                                            <td>
                                                @if($contentType->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $contentType->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-fill align-middle"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $contentType->id }}">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.content-types.show', $contentType) }}">
                                                                <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.content-types.edit', $contentType) }}">
                                                                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.content-types.fields.index', $contentType) }}">
                                                                <i class="ri-list-check align-bottom me-2 text-muted"></i> Fields
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.content-types.items.index', $contentType) }}">
                                                                <i class="ri-list-unordered align-bottom me-2 text-muted"></i> Content
                                                            </a>
                                                        </li>
                                                        @if(!$contentType->is_system)
                                                            <li class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('admin.content-types.destroy', $contentType) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item remove-item-btn" onclick="return confirm('Are you sure you want to delete this content type?');">
                                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $contentTypes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection