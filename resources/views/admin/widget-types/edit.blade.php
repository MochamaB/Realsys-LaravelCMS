@extends('admin.layouts.master')

@section('title', 'Edit Widget Type')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- FilePond css -->
    <link href="{{ asset('assets/admin/libs/filepond/filepond.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/admin/libs/filepond/filepond-plugin-image-preview.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
   
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Widget Type Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.widget-types.update', $widgetType) }}" 
                          method="POST" 
                          id="widgetTypeForm"
                          class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $widgetType->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug', $widgetType->slug) }}">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $widgetType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="component_path" class="form-label">Component Path <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('component_path') is-invalid @enderror" 
                                   id="component_path" 
                                   name="component_path" 
                                   value="{{ old('component_path', $widgetType->component_path) }}" 
                                   required>
                            @error('component_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Path to the blade component (e.g., widgets.text-block)</div>
                        </div>

                        <div class="col-md-6">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" 
                                   class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" 
                                   name="icon" 
                                   value="{{ old('icon', $widgetType->icon) }}">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Remix icon class (e.g., ri-text)</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', $widgetType->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-end">
                                <a href="{{ route('admin.widget-types.index') }}" class="btn btn-light me-1">Cancel</a>
                                <button type="submit" class="btn btn-success">Update Widget Type</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- FilePond js -->
    <script src="{{ asset('assets/admin/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond/filepond-plugin-file-validate-type.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        });
    </script>
@endsection
