@extends('admin.layouts.master')

@section('title', 'Manage Menu Items')

@section('css')
<link href="{{ asset('assets/admin/css/menu-manager.css') }}" rel="stylesheet" type="text/css" />
<style>
/* Sortable.js specific styles */
.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
}

.sortable-chosen {
    background: #e3f2fd;
}

.sortable-drag {
    opacity: 0.8;
    transform: rotate(2deg);
}

.dd-item {
    position: relative;
    display: block;
    margin: 5px 0;
    padding: 0;
    min-height: 50px;
    font-size: 13px;
    line-height: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    transition: all 0.3s ease;
}

.dd-item:hover {
    background: #f5f5f5;
    border-color: #ccc;
}

.dd-handle {
    display: block;
    height: 50px;
    margin: 0;
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
    font-weight: bold;
    border: 0;
    background: #f8f9fa;
    cursor: move;
    border-radius: 3px;
}

.dd-handle:hover {
    color: #2ea8dc;
    background: #f4f4f4;
}

.dd-list {
    display: block;
    position: relative;
    margin: 0;
    padding: 0 0 0 0px;
    list-style: none;
}

.dd-list .dd-list {
    padding-left: 30px;
}

.dd-collapsed .dd-list {
    display: none;
}

.menu-item-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.menu-item-badge {
    font-size: 11px;
    padding: 2px 6px;
}

.menu-item-actions {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    gap: 5px;
}

.menu-item-has-children i,
.menu-item-no-children i {
    width: 16px;
    color: #666;
}
</style>
@endsection

@section('js')
<!-- Sortable.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<!-- Menu Manager JS -->
<script src="{{ asset('assets/admin/js/menu-manager.js') }}"></script>

<!-- Menu Item Form JS -->
<script src="{{ asset('assets/admin/js/menu-item-form.js') }}"></script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4" style="border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">
                <h4>Manage "{{ $menu->name }}" Menu Items</h4>
                <div class="btn-group">
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary waves-effect">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> Back to Menus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Menu Items List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title flex-grow-1 mb-0">Menu Structure</h5>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-soft-primary btn-sm me-1" id="expand-all">
                                <i class="ri-add-line align-middle"></i> Expand All
                            </button>
                            <button type="button" class="btn btn-soft-primary btn-sm me-1" id="collapse-all">
                                <i class="ri-subtract-line align-middle"></i> Collapse All
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="save-menu-order" data-menu-id="{{ $menu->id }}">
                                <i class="ri-save-line align-middle me-1"></i> Save Order
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="ri-information-line me-2"></i> Drag and drop items to rearrange. Click the buttons to expand or collapse menu items. Click "Save Order" when you're done.
                    </div>
                    
                    @if($menu->rootItems->isNotEmpty())
                    <div class="dd" id="menu-items-nestable">
                        <ul class="dd-list">
                            @foreach($menu->rootItems as $item)
                                @include('admin.menus.partials.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <p class="mb-0">This menu has no items yet. Use the form on the right to add items.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Menu Item Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Menu Item</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.menus.items.store', $menu->id) }}" method="POST" id="menu-item-form">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="label" class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label') }}" required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Item</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach($allMenuItems ?? [] as $menuItem)
                                    <option value="{{ $menuItem->id }}" {{ old('parent_id') == $menuItem->id ? 'selected' : '' }}>
                                        {{ $menuItem->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link_type" class="form-label">Link Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('link_type') is-invalid @enderror" id="link_type" name="link_type" required>
                                <option value="">Select Link Type</option>
                                <option value="url" {{ old('link_type') == 'url' ? 'selected' : '' }}>URL</option>
                                <option value="page" {{ old('link_type') == 'page' ? 'selected' : '' }}>Page</option>
                                <option value="section" {{ old('link_type') == 'section' ? 'selected' : '' }}>Section</option>
                            </select>
                            @error('link_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="link-type-fields">
                            <!-- URL fields -->
                            <div id="url-fields" class="mb-3 d-none">
                                <label for="url" class="form-label">URL <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url') }}" placeholder="https://example.com">
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Page fields -->
                            <div id="page-fields" class="mb-3 d-none">
                                <label for="page_id" class="form-label">Select Page <span class="text-danger">*</span></label>
                                <select class="form-select @error('page_id') is-invalid @enderror" id="page_id" name="page_id">
                                    <option value="">Select Page</option>
                                    @foreach($pages ?? [] as $page)
                                        <option value="{{ $page->id }}" {{ old('page_id') == $page->id ? 'selected' : '' }}>
                                            {{ $page->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('page_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
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
                                                <option value="{{ $page->id }}" {{ old('page_id_for_section') == $page->id ? 'selected' : '' }}>{{ $page->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="section_id" class="form-label">Select Section <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id" disabled>
                                                <option value="">Select a page first</option>
                                            </select>
                                            <span class="input-group-text d-none" id="section_loading">
                                                <i class="ri-loader-4-line spin"></i>
                                            </span>
                                        </div>
                                        <small class="text-muted">Select a section from the selected page</small>
                                        @error('section_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <label for="target" class="form-label">Open In</label>
                                <select class="form-select @error('target') is-invalid @enderror" id="target" name="target">
                                    <option value="_self" {{ old('target', '_self') == '_self' ? 'selected' : '' }}>Same Window</option>
                                    <option value="_blank" {{ old('target', '_blank') == '_blank' ? 'selected' : '' }}>New Window</option>
                                </select>
                                @error('target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="number" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position') }}">
                                <small class="text-muted">Leave empty for auto</small>
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5>Visibility Conditions</h5>
                            <div class="card border">
                                <div class="card-body">
                                    <p class="text-muted">Set conditions for when this menu item should be visible.</p>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="auth_required" name="visibility_conditions[auth_required]" value="1" 
                                            {{ old('visibility_conditions.auth_required', '') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auth_required">
                                            Require user to be logged in
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Add Menu Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle form submission and reinitialize sortable
$(document).ready(function() {
    $('#menu-item-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Reload the page or update the menu items list
                location.reload();
            },
            error: function(xhr) {
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]);
                    });
                }
            }
        });
    });
});
</script>
@endsection