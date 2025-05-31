@extends('admin.layouts.master')

@section('title', 'Widget Types')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Widget Types List</h4>
                    <div>
                        <a href="{{ route('admin.widget-types.create') }}" class="btn btn-success add-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Create Widget Type
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">Widgets</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($widgetTypes as $type)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.widget-types.edit', $type) }}" class="fw-medium">
                                                {{ $type->name }}
                                            </a>
                                        </td>
                                        <td>{{ $type->slug }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $type->widgets_count }} widgets</span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch form-switch-success">
                                                <form action="{{ route('admin.widget-types.toggle', $type) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="checkbox" 
                                                           class="form-check-input widget-type-status-toggle" 
                                                           {{ $type->is_active ? 'checked' : '' }}>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.widget-types.edit', $type) }}" 
                                                   class="btn btn-sm btn-success edit-item-btn">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <form action="{{ route('admin.widget-types.destroy', $type) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger delete-widget-type">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No widget types found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $widgetTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <!-- Custom js -->
    <script src="{{ asset('admin/js/widgets/widget-list.js') }}"></script>
@endsection
