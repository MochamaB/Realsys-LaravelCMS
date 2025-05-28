@extends('admin.layouts.master')

@section('title', 'Template Sections')
@section('page-title', 'Template Sections')

@section('css')
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/admin/libs/dragula/dragula.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .section-item {
            cursor: move;
        }
        .section-item:hover {
            background-color: #f8f9fa;
        }
        .section-item.gu-mirror {
            background-color: #f8f9fa;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sections for Template: {{ $template->name }}</h5>
                    <div>
                        <a href="{{ route('admin.templates.sections.create', $template) }}" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Section
                        </a>
                        <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-info ms-2">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Template
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    @if($template->thumbnail_path)
                                        <img src="{{ asset($template->thumbnail_path) }}" alt="{{ $template->name }}" class="img-thumbnail" style="max-width: 100px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 100px; height: 75px;">
                                            <i class="mdi mdi-file-outline text-muted" style="font-size: 36px;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>{{ $template->name }}</h5>
                                    <p class="text-muted mb-0">{{ $template->description ?? 'No description' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($template->sections->isEmpty())
                        <div class="text-center p-4">
                            <p>No sections found for this template.</p>
                            <a href="{{ route('admin.templates.sections.create', $template) }}" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Section
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info mb-4">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Drag and drop sections to reorder them. Changes will be saved automatically.
                        </div>
                        
                        <div id="sections-container">
                            @foreach($template->sections->sortBy('order_index') as $section)
                                <div class="section-item card mb-3" data-id="{{ $section->id }}" data-order="{{ $section->order_index }}">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-1 text-center">
                                                <i class="mdi mdi-drag-horizontal-variant text-muted" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="col-md-3">
                                                <h5 class="card-title mb-0">{{ $section->name }}</h5>
                                                <div class="text-muted small">{{ $section->slug }}</div>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge bg-primary">{{ $sectionTypes[$section->type] ?? $section->type }}</span>
                                            </div>
                                            <div class="col-md-2">
                                                <div>
                                                    <strong>Width:</strong> <span class="text-muted">{{ $section->width ?? 'Default' }}</span>
                                                </div>
                                                <div>
                                                    <strong>Max Widgets:</strong> <span class="text-muted">{{ $section->max_widgets ?? 'Unlimited' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                @if($section->is_required)
                                                    <span class="badge bg-danger">Required</span>
                                                @else
                                                    <span class="badge bg-secondary">Optional</span>
                                                @endif
                                                @if($section->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning">Inactive</span>
                                                @endif
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <a href="{{ route('admin.templates.sections.edit', [$template, $section]) }}" class="btn btn-sm btn-primary me-1">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.templates.sections.destroy', [$template, $section]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger delete-section">
                                                        <i class="mdi mdi-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/dragula/dragula.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize delete confirmation
            const deleteBtns = document.querySelectorAll('.delete-section');
            
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Delete Section?',
                        text: "Are you sure you want to delete this section? This action cannot be undone.",
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
            
            // Initialize drag and drop ordering
            const sectionsContainer = document.getElementById('sections-container');
            
            if (sectionsContainer && sectionsContainer.children.length > 0) {
                const drake = dragula([sectionsContainer]);
                
                drake.on('drop', function() {
                    // Update order_index for each section
                    const sections = sectionsContainer.querySelectorAll('.section-item');
                    const sectionData = [];
                    
                    sections.forEach((section, index) => {
                        const id = section.dataset.id;
                        sectionData.push({
                            id: id,
                            order_index: index
                        });
                        
                        // Update data-order attribute
                        section.dataset.order = index;
                    });
                    
                    // Send order update to server
                    fetch('{{ route("admin.templates.sections.order", $template) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            sections: sectionData
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success toast
                            Swal.fire({
                                title: 'Success',
                                text: 'Section order updated successfully',
                                icon: 'success',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating section order:', error);
                        
                        // Show error toast
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update section order',
                            icon: 'error',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
                });
            }
        });
    </script>
@endsection
