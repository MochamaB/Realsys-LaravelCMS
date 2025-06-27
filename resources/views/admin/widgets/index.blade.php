@extends('admin.layouts.master')

@section('title', isset($theme) ? "Widgets for {$theme->name}" : 'All Widgets')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Widgets List</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.widgets.scan', ['theme' => $theme->id]) }}" class="btn btn-success">
                            <i class="ri-scan-line align-bottom me-1"></i> Discover Widgets
                        </a>
                        
                       
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col" style="width: 80px;">Preview</th>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Theme</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Content Types</th>
                                    <th scope="col">Fields</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="widget-list">
                                @forelse($widgets as $index => $widget)
                                    <tr data-widget-id="{{ $widget->id }}">
                                        <td>
                                            <div style="width: 70px; height: 50px;" class="d-flex justify-content-center align-items-center border rounded overflow-hidden">
                                                @php
                                                    // Check for published preview image in public directory
                                                    $previewUrl = asset("themes/{$widget->theme->slug}/widgets/{$widget->slug}/preview.png");
                                                    
                                                    // Fallback to placeholder if published preview doesn't exist
                                                    if (!file_exists(public_path("themes/{$widget->theme->slug}/widgets/{$widget->slug}/preview.png"))) {
                                                        $previewUrl = asset('assets/admin/images/widget-placeholder.png');
                                                    }
                                                @endphp
                                                <img src="{{ $previewUrl }}" alt="{{ $widget->name }}" class="img-fluid" style="max-height: 50px; max-width: 100%;">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="avatar-xs">
                                               
                                                    {{ $index + 1 }}
                                                
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($widget->icon)
                                                    <i class="{{ $widget->icon }} me-2 text-muted"></i>
                                                @endif
                                                <a href="{{ route('admin.widgets.show',$widget) }}" class="fw-medium">
                                                    {{ $widget->name }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $widget->theme->name }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ Str::limit($widget->description, 50) }}
                                        </td>
                                        <td>
                                            @forelse($widget->contentTypes as $contentType)
                                                <span class="badge bg-info">{{ $contentType->name }}</span>
                                            @empty
                                                <span class="badge bg-secondary">None</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ $widget->fieldDefinitions->count() }} fields
                                            </span>
                                        </td>
                                       
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.widgets.show',$widget) }}" 
                                                   class="btn btn-sm btn-info view-item-btn">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('admin.widgets.preview', $widget) }}" 
                                                   class="btn btn-sm btn-primary preview-item-btn">
                                                    <i class="ri-live-line"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No widgets found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $widgets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Custom js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Widget status toggle
            document.querySelectorAll('.widget-status-toggle').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const widgetId = this.dataset.widgetId;
                    const status = this.checked;
                    
                    fetch(`{{ url('admin/widgets') }}/${widgetId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
