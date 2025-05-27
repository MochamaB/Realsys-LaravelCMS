@extends('admin.layouts.master')

@section('title', 'Widgets')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Widgets</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Widgets</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Widgets List</h4>
                    <div>
                        <a href="{{ route('admin.widgets.create') }}" class="btn btn-success add-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Create Widget
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
                                    <th scope="col">Type</th>
                                    <th scope="col">Page Section</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="widget-list">
                                @forelse($widgets as $index => $widget)
                                    <tr data-widget-id="{{ $widget->id }}">
                                        <td>
                                            <div class="avatar-xs">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="widget-handle me-2 cursor-move">
                                                    <i class="ri-drag-move-2-fill text-muted"></i>
                                                </span>
                                                <a href="{{ route('admin.widgets.edit', $widget) }}" class="fw-medium">
                                                    {{ $widget->name }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $widget->widgetType->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                @foreach($widget->pageSections as $pageSection)
                                                    {{ $pageSection->title }} ({{ $pageSection->page->title }})@if(!$loop->last), @endif
                                                @endforeach
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch form-switch-success">
                                                <form action="{{ route('admin.widgets.toggle', $widget) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="checkbox" 
                                                           class="form-check-input widget-status-toggle" 
                                                           {{ $widget->status === 'published' ? 'checked' : '' }}>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.widgets.edit', $widget) }}" 
                                                   class="btn btn-sm btn-success edit-item-btn">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <form action="{{ route('admin.widgets.destroy', $widget) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger delete-widget">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No widgets found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $widgets->links() }}
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
    
    <!-- Custom js -->
    <script src="{{ asset('assets/admin/js/widgets/widget-list.js') }}"></script>
@endsection
