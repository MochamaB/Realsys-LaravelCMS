@extends('admin.layouts.app')

@section('title', 'Widget Content Queries')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Widget Content Queries</h1>
        <a href="{{ route('widget-content-queries.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Query
        </a>
    </div>

    @include('admin.partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Manage Content Queries</h6>
        </div>
        <div class="card-body">
            @if($queries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Content Type</th>
                                <th>Filters</th>
                                <th>Sort</th>
                                <th>Limit</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queries as $query)
                                <tr>
                                    <td>{{ $query->id }}</td>
                                    <td>
                                        @if($query->contentType)
                                            {{ $query->contentType->name }}
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $query->filters->count() }}</td>
                                    <td>
                                        @if($query->order_by)
                                            {{ $query->order_by }} ({{ $query->order_direction }})
                                        @else
                                            <span class="text-muted">Default</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($query->limit)
                                            {{ $query->limit }}
                                        @else
                                            <span class="text-muted">No limit</span>
                                        @endif
                                    </td>
                                    <td>{{ $query->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('widget-content-queries.show', $query) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('widget-content-queries.preview', $query) }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <a href="{{ route('widget-content-queries.edit', $query) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('widget-content-queries.destroy', $query) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this query?')">
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
                {{ $queries->links() }}
            @else
                <div class="alert alert-info">
                    No content queries found. <a href="{{ route('widget-content-queries.create') }}">Create your first content query</a>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "ordering": true,
            "paging": false,
            "info": false,
            "searching": true
        });
    });
</script>
@endpush
