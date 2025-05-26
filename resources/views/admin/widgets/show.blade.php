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
                <h4 class="card-title mb-0">Field Values</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    @foreach($widget->fieldValues as $fieldValue)
                                        <tr>
                                            <th scope="row" width="20%">{{ $fieldValue->field->label }}</th>
                                            <td>
                                                @switch($fieldValue->field->type)
                                                    @case('image')
                                                        @if($fieldValue->getFirstMedia('field_images'))
                                                            <img src="{{ $fieldValue->getFirstMediaUrl('field_images') }}" 
                                                                 alt="{{ $fieldValue->field->label }}"
                                                                 class="img-thumbnail" style="max-width: 200px">
                                                        @endif
                                                        @break
                                                    @case('file')
                                                        @if($fieldValue->getFirstMedia('field_files'))
                                                            <a href="{{ $fieldValue->getFirstMediaUrl('field_files') }}" 
                                                               target="_blank"
                                                               class="btn btn-sm btn-primary">
                                                                <i class="ri-download-2-line"></i> Download File
                                                            </a>
                                                        @endif
                                                        @break
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
