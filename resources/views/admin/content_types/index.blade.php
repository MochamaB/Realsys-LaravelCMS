<!-- resources/views/admin/content_types/index.blade.php -->
@extends('admin.layouts.master')

@section('title', 'Content Types')
@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<!-- Page title is now handled by the breadcrumb component -->
<div class="container-fluid">
   
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
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Key</th>
                                        <th>Description</th>
                                        <th>Fields</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contentTypes as $contentType)
                                        <tr>
                                            <td>{{ $contentType->name }}</td>
                                            <td><code>{{ $contentType->key }}</code></td>
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
                                                            <button class="btn btn-soft-secondary btn-sm " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-fill align-middle"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
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
</div>
@endsection