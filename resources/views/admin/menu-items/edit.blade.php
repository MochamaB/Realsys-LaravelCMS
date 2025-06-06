@extends('admin.layouts.master')

@section('title', 'Edit Menu Item')

@section('css')
<!-- Menu Item Form CSS -->
<link href="{{ asset('assets/admin/css/menu-manager.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('js')
<!-- Menu Item Form JS -->
<script src="{{ asset('assets/admin/js/menu-item-form.js') }}"></script>
@endsection

@section('content')
<div class="container-fluid">
    

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Edit Menu Item: {{ $item->label }}</h5>
                        <a href="{{ route('admin.menus.show', $menu->id) }}" class="btn btn-secondary waves-effect">
                            <i class="ri-arrow-left-line align-middle me-1"></i> Back to Menu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.menus.items.update', ['menu' => $menu->id, 'item' => $item->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="label" class="form-label">Label</label>
                                <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label', $item->label) }}" required>
                                @error('label')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="parent_id" class="form-label">Parent Item</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                    <option value="">None (Top Level)</option>
                                    @foreach($parentItems as $parentItem)
                                        <option value="{{ $parentItem->id }}" {{ old('parent_id', $item->parent_id) == $parentItem->id ? 'selected' : '' }}>{{ $parentItem->label }}</option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="link_type" class="form-label">Link Type</label>
                                <select class="form-select @error('link_type') is-invalid @enderror" id="link_type" name="link_type" required>
                                    <option value="">Select Link Type</option>
                                    <option value="url" {{ old('link_type', $item->link_type) == 'url' ? 'selected' : '' }}>URL</option>
                                    <option value="page" {{ old('link_type', $item->link_type) == 'page' ? 'selected' : '' }}>Page</option>
                                    <option value="section" {{ old('link_type', $item->link_type) == 'section' ? 'selected' : '' }}>Section</option>
                                </select>
                                @error('link_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="number" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', $item->position) }}">
                                @error('position')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="link-type-fields">
                            <!-- URL fields -->
                            <div id="url-fields" class="mb-3 d-none">
                                <label for="url" class="form-label">URL <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $item->url) }}" placeholder="https://example.com">
                                @error('url')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Page fields -->
                            <div id="page-fields" class="mb-3 d-none">
                                <label for="page_id" class="form-label">Select Page <span class="text-danger">*</span></label>
                                <select class="form-select @error('page_id') is-invalid @enderror" id="page_id" name="page_id">
                                    <option value="">Select Page</option>
                                    @foreach($pages as $page)
                                        <option value="{{ $page->id }}" {{ old('page_id', $item->page_id) == $page->id ? 'selected' : '' }}>{{ $page->title }}</option>
                                    @endforeach
                                </select>
                                @error('page_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Section fields -->
                            <div id="section-fields" class="mb-3 d-none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="page_id_for_section" class="form-label">Select Page <span class="text-danger">*</span></label>
                                        <select class="form-select mb-3" id="page_id_for_section" name="page_id_for_section">
                                            <option value="">Select Page</option>
                                            @foreach($pages as $page)
                                                <option value="{{ $page->id }}" {{ old('page_id_for_section', $item->page_id) == $page->id ? 'selected' : '' }}>{{ $page->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="section_id" class="form-label">Select Section <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id" {{ !$item->page_id ? 'disabled' : '' }}>
                                                <option value="">{{ $item->page_id ? 'Select Section' : 'Select a page first' }}</option>
                                                @if($item->section_id)
                                                    <option value="{{ $item->section_id }}" selected>Current Section</option>
                                                @endif
                                            </select>
                                            <span class="input-group-text d-none" id="section_loading">
                                                <i class="ri-loader-4-line spin"></i>
                                            </span>
                                        </div>
                                        @error('section_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="target" class="form-label">Open In</label>
                                <select class="form-select @error('target') is-invalid @enderror" id="target" name="target">
                                    <option value="_self" {{ old('target', $item->target) == '_self' ? 'selected' : '' }}>Same Window</option>
                                    <option value="_blank" {{ old('target', $item->target) == '_blank' ? 'selected' : '' }}>New Window</option>
                                </select>
                                @error('target')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5>Visibility Conditions</h5>
                            <div class="card border">
                                <div class="card-body">
                                    <p class="text-muted">Set conditions for when this menu item should be visible.</p>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="auth_required" name="visibility_conditions[auth_required]" value="1" 
                                            {{ old('visibility_conditions.auth_required', $item->visibility_conditions['auth_required'] ?? '') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auth_required">
                                            Require user to be logged in
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Menu Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Handle link type change
        $('#link_type').change(function() {
            // Hide all link type fields
            $('.link-type-field').addClass('d-none');
            
            // Show the appropriate field
            var linkType = $(this).val();
            if (linkType) {
                $('#' + linkType + '-fields').removeClass('d-none');
            }
        }).trigger('change'); // Trigger on page load
    });
</script>
@endsection
