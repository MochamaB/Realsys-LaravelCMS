@extends('admin.layouts.master')

@section('title', isset($contentType) ? 'Content Items - ' . $contentType->name : 'All Content Items')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    @if(isset($contentType))
                        Content Items - {{ $contentType->name }}
                    @else
                        All Content Items
                    @endif
                </h4>

                <div class="page-title-right">
                    @if(isset($contentType))
                        <a href="{{ route('admin.content-types.items.create', $contentType) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add {{ $contentType->name }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(isset($contentType))
        <!-- Content items for specific type -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($contentItems->isEmpty())
                            <div class="text-center p-5">
                                <h4>No content items found</h4>
                                <p>Create a new {{ $contentType->name }} to get started.</p>
                                <a href="{{ route('admin.content-types.items.create', $contentType) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Add {{ $contentType->name }}
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($contentItems as $contentItem)
                                            <tr>
                                                <td>{{ $contentItem->title }}</td>
                                                <td><code>{{ $contentItem->slug }}</code></td>
                                                <td>
                                                    @if($contentItem->status == 'published')
                                                        <span class="badge bg-success">Published</span>
                                                    @elseif($contentItem->status == 'draft')
                                                        <span class="badge bg-warning">Draft</span>
                                                    @else
                                                        <span class="badge bg-danger">Archived</span>
                                                    @endif
                                                </td>
                                                <td>{{ $contentItem->created_at->format('M d, Y') }}</td>
                                                <td>{{ $contentItem->updated_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('admin.content-types.items.show', [$contentType, $contentItem]) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.content-types.items.edit', [$contentType, $contentItem]) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="{{ route('admin.content-types.items.preview', [$contentType, $contentItem]) }}" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-desktop"></i> Preview
                                                        </a>
                                                        <form action="{{ route('admin.content-types.items.destroy', [$contentType, $contentItem]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
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
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $contentItems->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- All content types and their items -->
        @if($contentTypes->isEmpty())
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <h4>No content types found</h4>
                            <p>Create content types first to manage content.</p>
                            <a href="{{ route('admin.content-types.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Create Content Type
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @foreach($contentTypes as $type)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $type->name }}</h5>
                                <a href="{{ route('admin.content-types.items.index', $type) }}" class="btn btn-sm btn-primary">
                                    View All {{ $type->name }}
                                </a>
                            </div>
                            <div class="card-body">
                                @if($type->contentItems->isEmpty())
                                    <div class="text-center py-3">
                                        <p>No {{ $type->name }} items found.</p>
                                        <a href="{{ route('admin.content-types.items.create', $type) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-plus me-2"></i> Add {{ $type->name }}
                                        </a>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Status</th>
                                                    <th>Updated</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($type->contentItems()->latest()->take(5)->get() as $item)
                                                    <tr>
                                                        <td>{{ $item->title }}</td>
                                                        <td>
                                                            @if($item->status == 'published')
                                                                <span class="badge bg-success">Published</span>
                                                            @elseif($item->status == 'draft')
                                                                <span class="badge bg-warning">Draft</span>
                                                            @else
                                                                <span class="badge bg-danger">Archived</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item->updated_at->format('M d, Y') }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('admin.content-types.items.edit', [$type, $item]) }}" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="{{ route('admin.content-types.items.show', [$type, $item]) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($type->contentItems->count() > 5)
                                        <div class="text-center mt-3">
                                            <a href="{{ route('admin.content-types.items.index', $type) }}" class="btn btn-sm btn-light">
                                                View All {{ $type->contentItems->count() }} Items
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
</div>
@endsection
