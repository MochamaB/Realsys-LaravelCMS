@extends('admin.layouts.master')

@section('title', 'Template: ' . $template->name)
@section('page-title', 'Template Details')
@section('css')
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/template-designer.css') }}" rel="stylesheet" />
@endsection

@section('js')
<script src="{{ asset('assets/admin/libs/gridstack/dist/gridstack-all.js') }}"></script>
<script src="{{ asset('assets/admin/js/template-designer.js') }}"></script>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">{{ $template->name }}</h4>
                    <p class="text-muted mb-0">{{ $template->description }}</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary me-2">
                        <i class="bx bx-edit"></i> Edit Template
                    </a>
                    <div class="dropdown ms-2">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="templateActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="templateActionsDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.templates.edit', $template) }}"><i class="bx bx-edit me-1"></i> Edit</a></li>
                            @if(!$template->is_default)
                                <li><a class="dropdown-item" href=""><i class="bx bx-star me-1"></i> Set as Default</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item text-danger" onclick="confirmDelete()"><i class="bx bx-trash me-1"></i> Delete</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs-custom" id="templateTab" role="tablist" style="border-bottom: 1px solid #dee2e6;">
                <li class="nav-item">
                    <button class="nav-link active" id="sections-tab" data-bs-toggle="tab" 
                          data-bs-target="#sections" type="button" role="tab">
                        <i class="bx bx-layout"></i> Sections
                        <span class="badge bg-info">{{ $template->sections->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="preview-tab" data-bs-toggle="tab" 
                          data-bs-target="#preview" type="button" role="tab">
                        <i class="bx bx-show"></i> Preview
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="templateTabContent">
        <div class="tab-pane fade show active" id="sections" role="tabpanel">
            @include('admin.templates.tabs.sections')
        </div>
        
        <div class="tab-pane fade" id="preview" role="tabpanel">
           <!--To be added-->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this template?</p>
                @if($template->pages->count() > 0)
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> This template is being used by {{ $template->pages->count() }} page(s). Deleting it may cause issues.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="{{ route('admin.templates.destroy', $template) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Template</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection