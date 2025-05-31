@extends('admin.layouts.app')

@section('title', 'Edit Display Setting')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Display Setting #{{ $displaySetting->id }}</h1>
        <a href="{{ route('admin.widget-display-settings.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Display Settings
        </a>
    </div>

    @include('admin.partials.alerts')

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Display Setting Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.widget-display-settings.update', $displaySetting) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="layout">Layout</label>
                            <select class="form-control @error('layout') is-invalid @enderror" id="layout" name="layout">
                                <option value="">-- Default Layout --</option>
                                <option value="grid" {{ old('layout', $displaySetting->layout) == 'grid' ? 'selected' : '' }}>Grid</option>
                                <option value="list" {{ old('layout', $displaySetting->layout) == 'list' ? 'selected' : '' }}>List</option>
                                <option value="carousel" {{ old('layout', $displaySetting->layout) == 'carousel' ? 'selected' : '' }}>Carousel</option>
                                <option value="tabs" {{ old('layout', $displaySetting->layout) == 'tabs' ? 'selected' : '' }}>Tabs</option>
                                <option value="accordion" {{ old('layout', $displaySetting->layout) == 'accordion' ? 'selected' : '' }}>Accordion</option>
                                <option value="masonry" {{ old('layout', $displaySetting->layout) == 'masonry' ? 'selected' : '' }}>Masonry</option>
                            </select>
                            @error('layout')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Choose how content items will be arranged.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="view_mode">View Mode</label>
                            <select class="form-control @error('view_mode') is-invalid @enderror" id="view_mode" name="view_mode">
                                <option value="">-- Default View Mode --</option>
                                <option value="teaser" {{ old('view_mode', $displaySetting->view_mode) == 'teaser' ? 'selected' : '' }}>Teaser</option>
                                <option value="card" {{ old('view_mode', $displaySetting->view_mode) == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="full" {{ old('view_mode', $displaySetting->view_mode) == 'full' ? 'selected' : '' }}>Full</option>
                                <option value="compact" {{ old('view_mode', $displaySetting->view_mode) == 'compact' ? 'selected' : '' }}>Compact</option>
                                <option value="featured" {{ old('view_mode', $displaySetting->view_mode) == 'featured' ? 'selected' : '' }}>Featured</option>
                            </select>
                            @error('view_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Determine how much detail to show for each content item.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="pagination_type">Pagination Type</label>
                            <select class="form-control @error('pagination_type') is-invalid @enderror" id="pagination_type" name="pagination_type">
                                <option value="">-- No Pagination --</option>
                                <option value="numbered" {{ old('pagination_type', $displaySetting->pagination_type) == 'numbered' ? 'selected' : '' }}>Numbered Pages</option>
                                <option value="load_more" {{ old('pagination_type', $displaySetting->pagination_type) == 'load_more' ? 'selected' : '' }}>Load More Button</option>
                                <option value="infinite_scroll" {{ old('pagination_type', $displaySetting->pagination_type) == 'infinite_scroll' ? 'selected' : '' }}>Infinite Scroll</option>
                            </select>
                            @error('pagination_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Choose how users will navigate through multiple pages of content.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="items_per_page">Items Per Page</label>
                            <input type="number" class="form-control @error('items_per_page') is-invalid @enderror" id="items_per_page" name="items_per_page" value="{{ old('items_per_page', $displaySetting->items_per_page) }}" min="1" {{ $displaySetting->pagination_type ? '' : 'disabled' }}>
                            @error('items_per_page')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Number of items to display per page. Leave empty to show all items.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="empty_text">Empty Results Text</label>
                            <textarea class="form-control @error('empty_text') is-invalid @enderror" id="empty_text" name="empty_text" rows="2">{{ old('empty_text', $displaySetting->empty_text) }}</textarea>
                            @error('empty_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Text to display when no content items match the query.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="css_class">Additional CSS Classes</label>
                            <input type="text" class="form-control @error('css_class') is-invalid @enderror" id="css_class" name="css_class" value="{{ old('css_class', $displaySetting->css_class) }}">
                            @error('css_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional CSS classes to add to the widget container.</small>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Update Display Setting</button>
                            <a href="{{ route('widget-display-settings.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Used By Widgets</h6>
                </div>
                <div class="card-body">
                    @if($displaySetting->widgets->count() > 0)
                        <ul class="list-group">
                            @foreach($displaySetting->widgets as $widget)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $widget->name }}</span>
                                    <a href="{{ route('widgets.edit', $widget) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info">
                            This display setting is not used by any widgets yet.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Created:</strong> {{ $displaySetting->created_at->format('M d, Y H:i') }}
                    </div>
                    <div>
                        <strong>Last Updated:</strong> {{ $displaySetting->updated_at->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Show/hide items per page based on pagination type
        $('#pagination_type').change(function() {
            if ($(this).val() === '') {
                $('#items_per_page').prop('disabled', true);
            } else {
                $('#items_per_page').prop('disabled', false);
            }
        });
    });
</script>
@endpush
