@extends('admin.layouts.app')

@section('title', 'View Display Setting')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Display Setting #{{ $displaySetting->id }}</h1>
        <div>
            <a href="{{ route('admin.widget-display-settings.edit', $displaySetting) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Setting
            </a>
            <a href="{{ route('admin.widget-display-settings.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Display Settings
            </a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Display Setting Details</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">Layout:</th>
                            <td>
                                @if($displaySetting->layout)
                                    {{ ucfirst($displaySetting->layout) }}
                                @else
                                    <span class="text-muted">Default</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>View Mode:</th>
                            <td>
                                @if($displaySetting->view_mode)
                                    {{ ucfirst($displaySetting->view_mode) }}
                                @else
                                    <span class="text-muted">Default</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Pagination Type:</th>
                            <td>
                                @if($displaySetting->pagination_type)
                                    {{ ucfirst(str_replace('_', ' ', $displaySetting->pagination_type)) }}
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Items Per Page:</th>
                            <td>
                                @if($displaySetting->items_per_page)
                                    {{ $displaySetting->items_per_page }}
                                @else
                                    <span class="text-muted">All</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Empty Results Text:</th>
                            <td>
                                @if($displaySetting->empty_text)
                                    "{{ $displaySetting->empty_text }}"
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Additional CSS Classes:</th>
                            <td>
                                @if($displaySetting->css_class)
                                    <code>{{ $displaySetting->css_class }}</code>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $displaySetting->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $displaySetting->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Widgets Using This Setting</h6>
                </div>
                <div class="card-body">
                    @if($displaySetting->widgets->count() > 0)
                        <ul class="list-group">
                            @foreach($displaySetting->widgets as $widget)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $widget->name }}</span>
                                    <a href="{{ route('widgets.edit', $widget) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i> View
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info">
                            This display setting is not used by any widgets yet.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('widget-display-settings.edit', $displaySetting) }}" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Setting
                        </a>
                        <form action="{{ route('widget-display-settings.destroy', $displaySetting) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this display setting? This may affect widgets using it.')">
                                <i class="fas fa-trash"></i> Delete Setting
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
