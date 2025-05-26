@extends('admin.layouts.master')

@section('title', isset($page) ? 'Edit Page' : 'Create Page')

@section('css')
    <!-- Filepond css -->
    <link rel="stylesheet" href="{{ asset('admin/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
    
    <!-- Sweet Alert css-->
    <link href="{{ asset('admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ isset($page) ? 'Edit Page' : 'Create Page' }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
                        <li class="breadcrumb-item active">{{ isset($page) ? 'Edit' : 'Create' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ isset($page) ? route('admin.pages.update', $page) : route('admin.pages.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="pageForm">
                        @csrf
                        @if(isset($page))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label" for="title">Title</label>
                                    <input type="text" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title"
                                           value="{{ old('title', $page->title ?? '') }}"
                                           required>
                                    @error('title')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="slug">Slug</label>
                                    <input type="text" 
                                           class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" 
                                           name="slug"
                                           value="{{ old('slug', $page->slug ?? '') }}"
                                           required>
                                    @error('slug')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description"
                                              rows="3">{{ old('description', $page->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="ckeditor-classic" 
                                              name="content">{{ old('content', $page->content ?? '') }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label" for="template_id">Template</label>
                                    <select class="form-select @error('template_id') is-invalid @enderror" 
                                            id="template_id" 
                                            name="template_id"
                                            required>
                                        <option value="">Select Template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" 
                                                    {{ old('template_id', $page->template_id ?? '') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="parent_id">Parent Page</label>
                                    <select class="form-select @error('parent_id') is-invalid @enderror" 
                                            id="parent_id" 
                                            name="parent_id">
                                        <option value="">No Parent</option>
                                        @foreach($parentPages as $parentPage)
                                            <option value="{{ $parentPage->id }}" 
                                                    {{ old('parent_id', $page->parent_id ?? '') == $parentPage->id ? 'selected' : '' }}>
                                                {{ $parentPage->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch form-switch-success">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="is_active" 
                                               name="is_active"
                                               {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch form-switch-success">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="show_in_menu" 
                                               name="show_in_menu"
                                               {{ old('show_in_menu', $page->show_in_menu ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_in_menu">Show in Menu</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="menu_order">Menu Order</label>
                                    <input type="number" 
                                           class="form-control @error('menu_order') is-invalid @enderror" 
                                           id="menu_order" 
                                           name="menu_order"
                                           value="{{ old('menu_order', $page->menu_order ?? 0) }}">
                                    @error('menu_order')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="meta_title">Meta Title</label>
                                    <input type="text" 
                                           class="form-control @error('meta_title') is-invalid @enderror" 
                                           id="meta_title" 
                                           name="meta_title"
                                           value="{{ old('meta_title', $page->meta_title ?? '') }}">
                                    @error('meta_title')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="meta_description">Meta Description</label>
                                    <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                              id="meta_description" 
                                              name="meta_description"
                                              rows="3">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                                    @error('meta_description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="meta_keywords">Meta Keywords</label>
                                    <input type="text" 
                                           class="form-control @error('meta_keywords') is-invalid @enderror" 
                                           id="meta_keywords" 
                                           name="meta_keywords"
                                           value="{{ old('meta_keywords', $page->meta_keywords ?? '') }}">
                                    @error('meta_keywords')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Featured Image</label>
                                    <input type="file" 
                                           class="filepond filepond-input-multiple"
                                           name="image"
                                           accept="image/*" />
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12 text-end">
                                <a href="{{ route('admin.pages.index') }}" class="btn btn-light me-1">Cancel</a>
                                <button type="submit" class="btn btn-success" id="submit-btn">
                                    {{ isset($page) ? 'Update' : 'Create' }} Page
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- ckeditor -->
    <script src="{{ asset('admin/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>

    <!-- filepond js -->
    <script src="{{ asset('admin/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('admin/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
    <script src="{{ asset('admin/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}"></script>
    <script src="{{ asset('admin/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CKEditor
            ClassicEditor.create(document.querySelector('#ckeditor-classic'))
                .then(editor => {
                    console.log(editor);
                })
                .catch(error => {
                    console.error(error);
                });

            // FilePond
            FilePond.registerPlugin(
                FilePondPluginImagePreview,
                FilePondPluginImageExifOrientation,
                FilePondPluginFileValidateSize,
                FilePondPluginFileEncode
            );

            const inputElement = document.querySelector('input[type="file"].filepond');
            const pond = FilePond.create(inputElement);

            // Auto-generate slug from title
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');

            titleInput.addEventListener('input', function(e) {
                slugInput.value = slugify(e.target.value);
            });

            function slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');            // Trim - from end of text
            }
        });
    </script>
@endsection
