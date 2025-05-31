@extends('admin.layouts.app')

@section('title', 'Preview Query Results')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Preview Query Results</h1>
        <div>
            <a href="{{ route('widget-content-queries.edit', $contentQuery) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Query
            </a>
            <a href="{{ route('widget-content-queries.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Queries
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Query: 
                @if($contentQuery->contentType)
                    {{ $contentQuery->contentType->name }}
                @else
                    Unknown Content Type
                @endif
                ({{ $contentItems->count() }} results)
            </h6>
            <span class="badge badge-info">ID: {{ $contentQuery->id }}</span>
        </div>
        <div class="card-body">
            @if($contentItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                @if($contentQuery->contentType)
                                    @foreach($contentQuery->contentType->fields as $field)
                                        <th>{{ $field->name }}</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contentItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td><span class="badge badge-{{ $item->status === 'published' ? 'success' : ($item->status === 'draft' ? 'warning' : 'secondary') }}">{{ ucfirst($item->status) }}</span></td>
                                    <td>{{ $item->created_at->format('M d, Y') }}</td>
                                    @if($contentQuery->contentType)
                                        @foreach($contentQuery->contentType->fields as $field)
                                            <td>
                                                @php
                                                    $fieldValue = $item->fieldValues->where('field_id', $field->id)->first();
                                                    $value = $fieldValue ? $fieldValue->value : null;
                                                @endphp
                                                
                                                @if($field->type === 'image' && $value)
                                                    <img src="{{ asset('storage/' . $value) }}" alt="Image" style="max-width: 100px; max-height: 50px;">
                                                @elseif($field->type === 'boolean')
                                                    {{ $value ? 'Yes' : 'No' }}
                                                @elseif($field->type === 'rich_text')
                                                    {!! Str::limit(strip_tags($value), 100) !!}
                                                @else
                                                    {{ Str::limit($value, 100) }}
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <p class="mb-0">No content items match this query. Try adjusting your query parameters or filters.</p>
                </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Query Settings</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Content Type:</dt>
                        <dd class="col-sm-8">
                            @if($contentQuery->contentType)
                                {{ $contentQuery->contentType->name }}
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Sort:</dt>
                        <dd class="col-sm-8">
                            @if($contentQuery->order_by)
                                {{ $contentQuery->order_by }} ({{ $contentQuery->order_direction }})
                            @else
                                <span class="text-muted">Default</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Limit:</dt>
                        <dd class="col-sm-8">
                            @if($contentQuery->limit)
                                {{ $contentQuery->limit }}
                            @else
                                <span class="text-muted">No limit</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Offset:</dt>
                        <dd class="col-sm-8">{{ $contentQuery->offset }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Applied Filters</h6>
                </div>
                <div class="card-body">
                    @if($contentQuery->filters->count() > 0)
                        <ul class="list-group">
                            @foreach($contentQuery->filters as $filter)
                                <li class="list-group-item">
                                    <strong>
                                        @if($filter->field_id)
                                            {{ $filter->field->name ?? 'Unknown Field' }}
                                        @else
                                            {{ $filter->field_key ?? 'Unknown Property' }}
                                        @endif
                                    </strong>
                                    <span class="mx-2">{{ ucwords(str_replace('_', ' ', $filter->operator)) }}</span>
                                    @if(!in_array($filter->operator, ['is_null', 'is_not_null']))
                                        <code>{{ $filter->value }}</code>
                                    @endif
                                    
                                    @if($filter->condition_group)
                                        <span class="badge badge-info ml-2">Group: {{ $filter->condition_group }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info">
                            No filters applied to this query.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
