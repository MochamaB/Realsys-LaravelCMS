@extends('admin.layouts.master')

@section('title', 'Theme Details')
@section('page-title', 'Theme Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4 mb-md-0">
                            @if($theme->screenshot_path)
                                <img src="{{ asset($theme->screenshot_path) }}" class="img-fluid rounded shadow" alt="{{ $theme->name }} Screenshot">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 250px;">
                                    <i class="mdi mdi-image-outline" style="font-size: 64px;"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="mb-0">{{ $theme->name }}</h3>
                                
                                <div>
                                    @if($theme->is_active)
                                        <span class="badge bg-success me-2">Active Theme</span>
                                    @else
                                        <form action="{{ route('admin.themes.activate', $theme) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm me-2">
                                                <i class="mdi mdi-check-circle me-1"></i> Activate Theme
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('admin.themes.edit', $theme) }}" class="btn btn-primary btn-sm me-2">
                                        <i class="mdi mdi-pencil me-1"></i> Edit
                                    </a>
                                    
                                    <a href="{{ route('admin.themes.preview', $theme) }}" target="_blank" class="btn btn-info btn-sm">
                                        <i class="mdi mdi-eye me-1"></i> Preview
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <p class="text-muted mb-1">
                                    <strong>Version:</strong> {{ $theme->version ?? 'Not specified' }}
                                </p>
                                <p class="text-muted mb-1">
                                    <strong>Author:</strong> {{ $theme->author ?? 'Not specified' }}
                                </p>
                                <p class="text-muted mb-3">
                                    <strong>Slug:</strong> {{ $theme->slug }}
                                </p>
                                
                                <div class="mb-3">
                                    <h5>Description</h5>
                                    <p>{{ $theme->description ?? 'No description available.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Templates Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Available Templates</h5>
                </div>
                
                <div class="card-body">
                    @if($templates && $templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-centered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                        <tr>
                                            <td>{{ $template->name }}</td>
                                            <td><code>{{ $template->slug }}</code></td>
                                            <td>{{ Str::limit($template->description, 100) }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p>No templates are available for this theme.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
