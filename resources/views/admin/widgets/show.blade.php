@extends('admin.layouts.master')

@section('title', 'View Widget')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">View Widget</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.widgets.index') }}">Widgets</a></li>
                    <li class="breadcrumb-item active">View Widget</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Widget Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" width="20%">Name</th>
                                        <td>{{ $widget->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Type</th>
                                        <td>{{ $widget->widgetType->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Description</th>
                                        <td>{{ $widget->description }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td>
                                            <span class="badge bg-{{ $widget->status === 'published' ? 'success' : 'warning' }}">
                                                {{ ucfirst($widget->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Page Sections</th>
                                        <td>
                                            @foreach($widget->pageSections as $section)
                                                <span class="badge bg-info">
                                                    {{ $section->name }} ({{ $section->page->title }})
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Widget Data</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" width="20%">Name</th>
                                        <td>{{ $widget->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Description</th>
                                        <td>{{ $widget->description }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Type</th>
                                        <td>{{ $widget->widgetType->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td>
                                            <span class="badge bg-{{ $widget->status === 'published' ? 'success' : 'warning' }}">
                                                {{ ucfirst($widget->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($widget->contentQuery)
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Content Source</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" width="20%">Content Type</th>
                                        <td>{{ $widget->contentQuery->contentType->name ?? 'Not specified' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Limit</th>
                                        <td>{{ $widget->contentQuery->limit ?? 'No limit' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Order By</th>
                                        <td>
                                            {{ $widget->contentQuery->order_by ?? 'Default' }}
                                            ({{ $widget->contentQuery->order_direction ?? 'ASC' }})
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if($widget->displaySettings)
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Display Settings</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" width="20%">Layout</th>
                                        <td>{{ $widget->displaySettings->layout ?? 'Default' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">View Mode</th>
                                        <td>{{ $widget->displaySettings->view_mode ?? 'Default' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Pagination</th>
                                        <td>{{ $widget->displaySettings->pagination_type ?? 'None' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Items Per Page</th>
                                        <td>{{ $widget->displaySettings->items_per_page ?? 'Not specified' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
                                                    @case('rich_text')
                                                        {!! $fieldValue->value !!}
                                                        @break
                                                    @default
                                                        {{ $fieldValue->value }}
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
