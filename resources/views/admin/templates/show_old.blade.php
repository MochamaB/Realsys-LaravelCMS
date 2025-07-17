@extends('admin.layouts.master')

@section('title', 'Template Details')
@section('page-title', 'Template Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Template: {{ $template->name }}</h5>
                    <div>
                        <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-info me-2">
                            <i class="ri-layout-line me-1"></i> Manage Sections
                        </a>
                        <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-info me-2" target="_blank">
                            <i class="mdi mdi-eye me-1"></i> Preview
                        </a>
                        <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary me-2">
                            <i class="mdi mdi-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Templates
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Basic Information</h6>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th class="bg-light" style="width: 140px;">ID</th>
                                            <td>{{ $template->id }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Name</th>
                                            <td>{{ $template->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Slug</th>
                                            <td>{{ $template->slug }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Theme</th>
                                            <td>
                                                <a href="{{ route('admin.themes.show', $template->theme) }}" class="link-primary">
                                                    {{ $template->theme->name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">File Path</th>
                                            <td>
                                                <code>{{ $template->file_path }}</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Description</th>
                                            <td>{{ $template->description ?? 'No description' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Status</th>
                                            <td>
                                                @if($template->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                                @if($template->is_default)
                                                    <span class="badge bg-primary ms-2">Default Template</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Preview</h6>
                                    <div class="template-preview border p-2">
                                        @if($template->thumbnail_path)
                                            <img src="{{ asset($template->thumbnail_path) }}" alt="{{ $template->name }}" class="img-fluid">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                                                <div class="text-center">
                                                    <i class="mdi mdi-file-image-outline text-muted" style="font-size: 48px;"></i>
                                                    <p class="mt-2">No preview available</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="border-bottom pb-2">Template Sections ({{ $template->sections->count() }})</h5>
                                    <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-layout-line me-1"></i> Manage All Sections
                                    </a>
                                </div>
                                
                                @if($template->sections->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        This template doesn't have any sections yet. Sections define the structure of your template.
                                        <div class="mt-2">
                                            <a href="{{ route('admin.templates.sections.create', $template) }}" class="btn btn-sm btn-success">
                                                <i class="ri-add-line me-1"></i> Add First Section
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="template-sections-preview">
                                        <ul class="list-unstyled">
                                            @php
                                                $sectionTypes = [
                                                    'full-width' => 'Full Width Section',
                                                    'multi-column' => 'Multi-Column Section',
                                                    'sidebar-left' => 'Sidebar Left Section',
                                                    'sidebar-right' => 'Sidebar Right Section',
                                                ];
                                            @endphp
                                            
                                            @foreach($template->sections->sortBy('position') as $section)
                                                @include('admin.templates.sections._section_readonly', ['section' => $section, 'sectionTypes' => $sectionTypes])
                                            @endforeach
                                        </ul>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <a href="{{ route('admin.templates.sections.create', $template) }}" class="btn btn-success">
                                            <i class="mdi mdi-plus-circle me-1"></i> Add New Section
                                        </a>
                                        <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-primary ms-2">
                                            <i class="mdi mdi-puzzle me-1"></i> Manage Sections
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Usage Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6>Pages Using This Template</h6>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <span class="badge bg-primary rounded-circle p-2" style="font-size: 20px;">
                                                    {{ $template->pages->count() }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="mb-0">
                                                    This template is used by {{ $template->pages->count() }} 
                                                    {{ Str::plural('page', $template->pages->count()) }}.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($template->pages->isNotEmpty())
                                        <div class="mb-3">
                                            <h6>Top Pages</h6>
                                            <ul class="list-group">
                                                @foreach($template->pages->take(5) as $page)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <a href="#" class="link-primary">{{ $page->title }}</a>
                                                            <div class="text-muted small">{{ $page->slug }}</div>
                                                        </div>
                                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            
                                            @if($template->pages->count() > 5)
                                                <div class="mt-2 text-center">
                                                    <a href="#" class="link-primary">View all {{ $template->pages->count() }} pages</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary">
                                            <i class="mdi mdi-pencil me-1"></i> Edit Template
                                        </a>
                                        <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-info" target="_blank">
                                            <i class="mdi mdi-eye me-1"></i> Preview Template
                                        </a>
                                        @if(!$template->is_default)
                                            <form action="{{ route('admin.templates.set-default', $template) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="mdi mdi-check-circle me-1"></i> Set as Default
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" id="delete-template-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100" id="delete-template-btn">
                                                <i class="mdi mdi-trash-can me-1"></i> Delete Template
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle template deletion
        const deleteTemplateForm = document.getElementById('delete-template-form');
        const deleteTemplateBtn = document.getElementById('delete-template-btn');
        
        if (deleteTemplateForm) {
            deleteTemplateForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this! All pages using this template may be affected.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        }
        
        // Handle section deletion
        const deleteSectionBtns = document.querySelectorAll('.delete-section');
        
        deleteSectionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Delete Section?',
                    text: "Are you sure you want to delete this section?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.closest('form').submit();
                    }
                });
            });
        });
    });
</script>
@endsection
