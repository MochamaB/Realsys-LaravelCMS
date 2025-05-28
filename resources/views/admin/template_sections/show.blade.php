@extends('admin.layouts.master')

@section('title', 'Section Details')
@section('page-title', 'Section Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Section Details: {{ $section->name }}</h5>
                    <div>
                        <a href="{{ route('admin.templates.sections.edit', [$template, $section]) }}" class="btn btn-primary me-2">
                            <i class="mdi mdi-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.templates.sections.index', $template) }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Sections
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3">Basic Information</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th class="bg-light" style="width: 180px;">ID</th>
                                    <td>{{ $section->id }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Name</th>
                                    <td>{{ $section->name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Slug</th>
                                    <td>{{ $section->slug }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Template</th>
                                    <td>
                                        <a href="{{ route('admin.templates.show', $template) }}" class="link-primary">
                                            {{ $template->name }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Type</th>
                                    <td>
                                        <span class="badge bg-primary">{{ $sectionTypes[$section->type] ?? $section->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Width</th>
                                    <td>{{ $section->width ?? 'Default' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Status</th>
                                    <td>
                                        @if($section->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                        @if($section->is_required)
                                            <span class="badge bg-danger ms-2">Required</span>
                                        @else
                                            <span class="badge bg-info ms-2">Optional</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Order Index</th>
                                    <td>{{ $section->order_index }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Max Widgets</th>
                                    <td>{{ $section->max_widgets ?? 'Unlimited' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Description</th>
                                    <td>{{ $section->description ?? 'No description' }}</td>
                                </tr>
                            </table>
                            
                            <h6 class="text-muted mb-3 mt-4">Advanced Settings</h6>
                            @if(!empty($section->settings))
                                <div class="card">
                                    <div class="card-body bg-light">
                                        <pre class="mb-0"><code>{{ json_encode($section->settings, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    No custom settings defined for this section.
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Usage Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6>Page Sections</h6>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <span class="badge bg-primary rounded-circle p-2" style="font-size: 20px;">
                                                    {{ $section->pageSections()->count() }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="mb-0">
                                                    This section is used by {{ $section->pageSections()->count() }} 
                                                    {{ Str::plural('page section', $section->pageSections()->count()) }}.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($section->pageSections()->count() > 0)
                                        <div class="mb-3">
                                            <h6>Pages Using This Section</h6>
                                            <ul class="list-group">
                                                @foreach($section->pageSections()->with('page')->take(5)->get() as $pageSection)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <a href="#" class="link-primary">{{ $pageSection->page->title }}</a>
                                                            <div class="text-muted small">{{ $pageSection->page->slug }}</div>
                                                        </div>
                                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            
                                            @if($section->pageSections()->count() > 5)
                                                <div class="mt-2 text-center">
                                                    <a href="#" class="link-primary">View all {{ $section->pageSections()->count() }} pages</a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            This section is not being used by any pages yet.
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
                                        <a href="{{ route('admin.templates.sections.edit', [$template, $section]) }}" class="btn btn-primary">
                                            <i class="mdi mdi-pencil me-1"></i> Edit Section
                                        </a>
                                        <form action="{{ route('admin.templates.sections.destroy', [$template, $section]) }}" method="POST" id="delete-section-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100" id="delete-section-btn">
                                                <i class="mdi mdi-trash-can me-1"></i> Delete Section
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
        // Handle section deletion
        const deleteSectionForm = document.getElementById('delete-section-form');
        const deleteSectionBtn = document.getElementById('delete-section-btn');
        
        if (deleteSectionForm) {
            deleteSectionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this! Pages using this section may be affected.",
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
    });
</script>
@endsection
