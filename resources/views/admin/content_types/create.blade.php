@extends('admin.layouts.master')

@section('title', 'Create Content Type')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Create Content Type</h4>
                    <p class="text-muted mb-0">Define basic information for your new content type</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-plus-circle"></i> New Content Type
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.content-types.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="{{ old('name') }}" required
                                placeholder="e.g. Blog Post, Product, Event">
                            <small class="text-muted">Human-readable name for your content type</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Identifier (Slug)</label>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                value="{{ old('slug') }}"
                                placeholder="e.g. blog-post, product, event">
                            <small class="text-muted">Unique identifier used in code. Leave blank for auto-generation from name.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                rows="3" placeholder="What kind of content will this type store?">{{ old('description') }}</textarea>
                            <small class="text-muted">Help others understand the purpose of this content type</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (Optional)</label>
                            <input type="text" class="form-control" id="icon" name="icon" 
                                value="{{ old('icon') }}"
                                placeholder="e.g. bx-file, bx-store, bx-calendar">
                            <small class="text-muted">BoxIcon name to represent this content type</small>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" 
                                name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('admin.content-types.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Create and Configure Fields
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.value) {
            slugInput.value = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
});
</script>
@endpush
