<!-- resources/views/admin/content_types/index.blade.php -->
@extends('admin.layouts.master')

@section('title', 'Content Types')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Content Types</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.content-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add Content Type
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card for content types list -->
    <div class="row">
        <div class="col-12">
            <div class="card">
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
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.content-types.show', $contentType) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.content-types.edit', $contentType) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('admin.content-types.fields.index', $contentType) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-list"></i> Fields
                                                    </a>
                                                    <a href="{{ route('admin.content-types.items.index', $contentType) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-list"></i> Content
                                                    </a>
                                                    @if(!$contentType->is_system)
                                                        <form action="{{ route('admin.content-types.destroy', $contentType) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this content type?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
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