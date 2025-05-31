@extends('admin.layouts.app')

@section('title', 'Widget Display Settings')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Widget Display Settings</h1>
        <a href="{{ route('admin.widget-display-settings.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Setting
        </a>
    </div>

    @include('admin.partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Display Settings</h6>
        </div>
        <div class="card-body">
            @if($displaySettings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Layout</th>
                                <th>View Mode</th>
                                <th>Pagination</th>
                                <th>Items Per Page</th>
                                <th>Widgets Using</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displaySettings as $setting)
                                <tr>
                                    <td>{{ $setting->id }}</td>
                                    <td>{{ $setting->layout ?? 'Default' }}</td>
                                    <td>{{ $setting->view_mode ?? 'Default' }}</td>
                                    <td>
                                        @if($setting->pagination_type)
                                            {{ ucfirst(str_replace('_', ' ', $setting->pagination_type)) }}
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($setting->items_per_page)
                                            {{ $setting->items_per_page }}
                                        @else
                                            <span class="text-muted">All</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $setting->widgets_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('widget-display-settings.edit', $setting) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('widget-display-settings.show', $setting) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('widget-display-settings.destroy', $setting) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this display setting? This may affect widgets using it.')">
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
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $displaySettings->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    No display settings found. <a href="{{ route('widget-display-settings.create') }}">Create your first display setting</a>.
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
            "paging": false,
            "searching": true,
            "ordering": true,
            "info": false,
        });
    });
</script>
@endpush
