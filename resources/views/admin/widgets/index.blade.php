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
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 250px;">
                            <select class="form-select" id="themeFilter">
                                <option value="">All Themes</option>
                                @foreach($themes as $theme)
                                    <option value="{{ $theme->id }}" {{ request()->get('theme') == $theme->id ? 'selected' : '' }}>
                                        {{ $theme->name }}{{ $theme->active ? ' (Active)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary" type="button" id="applyThemeFilter">Filter</button>
                        </div>
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
                                    <th scope="col">Content Source</th>
                                    <th scope="col">Display Settings</th>
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
                                            @if($widget->content_query_id)
                                                <a href="{{ route('admin.widget-content-queries.show', $widget->content_query_id) }}" class="badge bg-info text-decoration-none">
                                                    <i class="ri-filter-line"></i> {{ $widget->contentQuery->contentType->name ?? 'Content Source' }}
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">No Content Source</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($widget->display_settings_id)
                                                <a href="{{ route('admin.widget-display-settings.show', $widget->display_settings_id) }}" class="badge bg-success text-decoration-none">
                                                    <i class="ri-layout-line"></i> {{ $widget->displaySettings->layout ?? 'Display Settings' }}
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">Default Display</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($widget->pageSections->count() > 0)
                                                @foreach($widget->pageSections as $pageSection)
                                                    <span class="badge bg-info">
                                                        {{ $pageSection->templateSection->name ?? 'Section' }} ({{ $pageSection->page->title ?? 'Page' }})
                                                    </span>
                                                    @if(!$loop->last)<br>@endif
                                                @endforeach
                                            @else
                                                <span class="badge bg-warning">Not placed</span>
                                            @endif
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
                                        <td colspan="8" class="text-center">No widgets found</td>
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
