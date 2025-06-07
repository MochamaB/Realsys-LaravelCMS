@extends('admin.layouts.master')

@section('title', 'Templates')
@section('page-title', 'Templates')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Templates for "{{ $activeTheme->name }}" Theme</h5>
                    <div>
                        <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Template
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    @if($templates->isEmpty())
                        <div class="text-center p-4">
                            <p>No templates found. Create your first template to get started.</p>
                            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Template
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Thumbnail</th>
                                        <th>Name</th>
                                        <th>Theme</th>
                                        <th>Sections</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                        <tr>
                                            <td>{{ $template->id }}</td>
                                            <td style="width: 80px;">
                                                @if($template->thumbnail_path)
                                                    <img src="{{ asset($template->thumbnail_path) }}" alt="{{ $template->name }}" class="img-thumbnail" style="max-width: 60px;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                        <i class="mdi mdi-file-outline text-muted" style="font-size: 24px;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $template->name }}</strong>
                                                @if($template->is_default)
                                                    <span class="badge bg-success ms-2">Default</span>
                                                @endif
                                                <div class="text-muted small">{{ $template->slug }}</div>
                                            </td>
                                            <td>
                                                {{ $template->theme->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                               {{ $template->sections->count() }} sections
                                            </td>
                                            <td>
                                                @if($template->is_active)
                                                    <span class="badge rounded-pill bg-success">Active</span>
                                                @else
                                                    <span class="badge rounded-pill bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('admin.partials.actionbuttons', [
                                                    'model' => $template,
                                                    'type' => 'inline',
                                                    'itemsRoute' => 'admin.templates.sections.index',
                                                    'previewRoute' => 'admin.templates.preview',
                                                    'viewRoute' => 'admin.templates.show',
                                                    'editRoute' => 'admin.templates.edit',
                                                    'destroyRoute' => 'admin.templates.destroy',
                                                    'resource' => 'template',
                                                    'resourceName' => 'template'
                                                ])

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Handle template deletion with confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this! Pages using this template may be affected.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
