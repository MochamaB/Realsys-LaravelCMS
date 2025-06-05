@extends('admin.layouts.master')

@section('title', 'Manage Themes')
@section('page-title', 'Manage Themes')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4" style="border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">
                <h4>Themes <span class="badge bg-primary">{{ $themes->count() }}</span></h4>
                <a href="{{ route('admin.themes.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Theme
                </a>
            </div>
            
        </div>
    </div>

    <div class="row">
        @if($themes->isEmpty())
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h4>No themes available</h4>
                        <p>You haven't added any themes yet. Click the button above to add a new theme.</p>
                    </div>
                </div>
            </div>
            @else
            @foreach($themes as $theme)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="position-relative">
                            @if($theme->screenshot_path)
                                <img src="{{ asset($theme->screenshot_path) }}" class="card-img-top" alt="{{ $theme->name }} Screenshot" style="height: 220px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                                    <i class="mdi mdi-image-outline" style="font-size: 48px;"></i>
                                </div>
                            @endif
                            
                           
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">{{ $theme->name }}</h5>
                                
                                @if($theme->is_active)
                                    <span class="badge rounded-pill border border-success text-success ms-2">Active</span>
                                @endif
                            </div>
                            <p class="card-text text-muted mb-1">
                                @if($theme->version)
                                    <small>Version: {{ $theme->version }}</small>
                                @endif
                            </p>
                            <!--p class="card-text small mb-3">
                                {{ Str::limit($theme->description, 100) }}
                            </p!-->
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.themes.show', $theme) }}" class="btn btn-primary">View Details</a>
                                
                                <div class="dropdown">
                                    <button class="btn btn-light dropdown-toggle" type="button" id="themeActionDropdown{{ $theme->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="themeActionDropdown{{ $theme->id }}">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.themes.edit', $theme) }}">
                                                <i class="mdi mdi-pencil me-1"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.themes.preview', $theme) }}" target="_blank">
                                                <i class="mdi mdi-eye me-1"></i> Preview
                                            </a>
                                        </li>
                                        
                                        @if(!$theme->is_active)
                                            <li>
                                                <form action="{{ route('admin.themes.activate', $theme) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="mdi mdi-check-circle me-1"></i> Activate
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.themes.destroy', $theme) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="mdi mdi-trash-can me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle theme deletion with confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
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
