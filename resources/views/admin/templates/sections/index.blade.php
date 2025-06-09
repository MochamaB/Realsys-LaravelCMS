@extends('admin.layouts.master')

@section('title', 'Manage Template Sections')

@section('css')
<link href="{{ asset('assets/admin/css/template-sections.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('js')
<script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/template-sections.js') }}"></script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h5 class="h4">Manage Template Sections: {{ $template->name }}</h5>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Templates
            </a>
            <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-info" target="_blank">
                <i class="bi bi-eye"></i> Preview Template
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Template Sections</h5>
                </div>
                <div class="card-body">
                    @if ($template->sections->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No sections have been created yet for this template.
                            Use the form on the left to create your first section.
                        </div>
                    @else
                        <div id="sections-container" data-base-url="{{ url('admin/templates/'.$template->id) }}" class="alert alert-info mb-3">
                            <i class="ri-information-line me-2"></i> Drag and drop sections to rearrange. Click "Save Section Positions" when you're done.
                        </div>
                        <div class="dd" id="menu-items-nestable">
                            <ul id="sections-sortable" class="dd-list">
                                @foreach($template->sections->sortBy('position') as $section)
                                    @include('admin.templates.sections._section')
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-3">
                            <button id="save-positions" class="btn btn-success" data-url="{{ route('admin.templates.sections.positions', ['template' => $template->id]) }}">
                                <i class="ri-save-line align-middle me-1"></i> Save Section Positions
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @include('admin.templates.sections._form', ['section' => $newSection])
        </div>

    </div>
</div>
@endsection
