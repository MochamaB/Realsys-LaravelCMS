@extends('admin.layouts.master')

@section('title', isset($page) ? 'Edit Page: ' . $page->title : 'Create Page')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ isset($page) ? 'Edit Page: ' . $page->title : 'Create Page' }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($page) ? route('admin.pages.update', $page) : route('admin.pages.store') }}" method="POST" id="pageForm">
                        @csrf
                        @if(isset($page))
                            @method('PUT')
                        @endif
                        <div class="row">
                            <!-- Left Column (7/12 width) -->
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $page->title ?? '') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="slug">Slug</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $page->slug ?? '') }}">
                                    <small class="text-muted">Leave empty to auto-generate from title</small>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium">Template <span class="text-danger">*</span></label>
                                    <select class="form-select @error('template_id') is-invalid @enderror" name="template_id" required>
                                        <option value="">Select Template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id', $page->template_id ?? '') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('template_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                               
                            </div>

                            <!-- Right Column (5/12 width) -->
                            <div class="col-md-5">
                                <div class="card ">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Publish</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                                <option value="draft" {{ old('status', $page->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="published" {{ old('status', $page->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="published_at">Publish Date</label>
                                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" id="published_at" name="published_at" value="{{ old('published_at', isset($page) && $page->published_at ? date('Y-m-d\TH:i', strtotime($page->published_at)) : '') }}">
                                            @error('published_at')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_homepage" name="is_homepage" value="1" {{ old('is_homepage', isset($page) ? $page->is_homepage : false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_homepage">Set as homepage</label>
                                            </div>
                                            @error('is_homepage')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card  mt-3">
                                    <div class="card-header ">
                                        <h5 class="card-title mb-0">SEO Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label" for="meta_title">Meta Title</label>
                                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}">
                                            @error('meta_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="meta_description">Meta Description</label>
                                            <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                                            @error('meta_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="meta_keywords">Meta Keywords</label>
                                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords ?? '') }}">
                                            <small class="text-muted">Separate keywords with commas</small>
                                            @error('meta_keywords')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('admin.pages.index') }}" class="btn btn-light me-2">Cancel</a>
                                    <button type="submit" class="btn btn-success" id="submit-btn">{{ isset($page) ? 'Update Page' : 'Create Page' }}</button>
                                </div>
                            </div>
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
        // Auto-generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        
        titleInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                const slug = titleInput.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special chars
                    .replace(/[\s_-]+/g, '-')  // Replace spaces and underscores with hyphens
                    .replace(/^-+|-+$/g, '');   // Trim hyphens from start and end
                
                slugInput.value = slug;
            }
        });
        
        // If slug is manually changed, don't auto-update it anymore
        slugInput.addEventListener('input', function() {
            slugInput.dataset.manual = 'true';
        });
    });
</script>
@endpush