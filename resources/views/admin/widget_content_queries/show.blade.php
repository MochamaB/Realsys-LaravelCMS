@extends('admin.layouts.app')

@section('title', 'View Content Query')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Content Query #{{ $contentQuery->id }}</h1>
        <div>
            <a href="{{ route('widget-content-queries.preview', $contentQuery) }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm mr-2">
                <i class="fas fa-play fa-sm text-white-50"></i> Preview Results
            </a>
            <a href="{{ route('widget-content-queries.edit', $contentQuery) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Query
            </a>
            <a href="{{ route('widget-content-queries.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Queries
            </a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Query Settings</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Content Type:</th>
                            <td>
                                @if($contentQuery->contentType)
                                    {{ $contentQuery->contentType->name }}
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Sort:</th>
                            <td>
                                @if($contentQuery->order_by)
                                    {{ $contentQuery->order_by }} ({{ $contentQuery->order_direction }})
                                @else
                                    <span class="text-muted">Default</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Limit:</th>
                            <td>
                                @if($contentQuery->limit)
                                    {{ $contentQuery->limit }}
                                @else
                                    <span class="text-muted">No limit</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Offset:</th>
                            <td>{{ $contentQuery->offset }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $contentQuery->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $contentQuery->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Query Filters</h6>
                </div>
                <div class="card-body">
                    @if($contentQuery->filters->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Operator</th>
                                        <th>Value</th>
                                        <th>Group</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contentQuery->filters as $filter)
                                        <tr>
                                            <td>
                                                @if($filter->field_id)
                                                    {{ $filter->field->name ?? 'Unknown Field' }}
                                                @else
                                                    {{ $filter->field_key ?? 'Unknown Property' }}
                                                @endif
                                            </td>
                                            <td>{{ ucwords(str_replace('_', ' ', $filter->operator)) }}</td>
                                            <td>
                                                @if(in_array($filter->operator, ['is_null', 'is_not_null']))
                                                    <em>Not applicable</em>
                                                @else
                                                    {{ Str::limit($filter->value, 30) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($filter->condition_group)
                                                    {{ $filter->condition_group }}
                                                @else
                                                    <span class="text-muted">Default</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No filters applied to this query.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Used By Widgets</h6>
                </div>
                <div class="card-body">
                    @if($contentQuery->widgets->count() > 0)
                        <ul class="list-group">
                            @foreach($contentQuery->widgets as $widget)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $widget->name }}</span>
                                    <a href="{{ route('widgets.edit', $widget) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info">
                            This query is not used by any widgets yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
