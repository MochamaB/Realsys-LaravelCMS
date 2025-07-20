@extends('admin.layouts.master')

@section('title', isset($page) ? 'Edit Page' : 'Create Page')

@section('css')
    <!-- Filepond css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
    
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    
    <!-- Template Selector CSS -->
    <style>
        .template-card {
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .template-card.border-primary {
            border-width: 2px;
        }
        
        .template-radio {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            width: 20px;
            height: 20px;
        }
        
        .template-thumbnail {
            overflow: hidden;
            background-color: #f8f9fa;
        }
        
        .template-thumbnail img {
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .template-card:hover .template-thumbnail img {
            transform: scale(1.05);
        }
        
        .template-sections {
            margin-top: 10px;
        }
    </style>
@endsection

@section('content')
    

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"></h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($page) ? route('admin.pages.update', $page) : route('admin.pages.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="pageForm">
                        @csrf
                        @if(isset($page))
                            @method('PUT')
                        @endif

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
                            <label class="form-label fw-medium">Template <span class="text-danger">*</span></label>
                            <select class="form-select @error('template_id') is-invalid @enderror" name="template_id" required>
                                <option value="">Select Template</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id', $page->template_id ?? '') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                <option value="draft" {{ old('status', $page->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $page->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
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
    <script src="{{ asset('assets/admin/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
    
    <!-- Template Selection Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle template card selection
            const templateCards = document.querySelectorAll('.template-card');
            const templateRadios = document.querySelectorAll('.template-radio');
            
            // Function to update card styling based on selection
            function updateTemplateCardSelection() {
                templateCards.forEach(card => {
                    card.classList.remove('border-primary');
                });
                
                templateRadios.forEach(radio => {
                    if (radio.checked) {
                        radio.closest('.template-card').classList.add('border-primary');
                    }
                });
            }
            
            // Add click handler to cards
            templateCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on the preview button
                    if (e.target.closest('a.btn')) return;
                    
                    const radio = this.querySelector('.template-radio');
                    radio.checked = true;
                    updateTemplateCardSelection();
                    
                    // Trigger change event for any other code that might be listening
                    const event = new Event('change');
                    radio.dispatchEvent(event);
                });
            });
            
            // Add change handler to radios
            templateRadios.forEach(radio => {
                radio.addEventListener('change', updateTemplateCardSelection);
            });
            
            // Initialize styling
            updateTemplateCardSelection();
            
            // Auto-generate slug from title
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');
            
            if (titleInput && slugInput) {
                titleInput.addEventListener('blur', function() {
                    if (slugInput.value === '' && titleInput.value !== '') {
                        // Convert to lowercase, remove special chars, replace spaces with hyphens
                        let slug = titleInput.value.toLowerCase()
                            .replace(/[^\w\s-]/g, '') // Remove special characters
                            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
                        
                        slugInput.value = slug;
                    }
                });
            }
        });
    </script>

    <!-- filepond js -->
    <script src="{{ asset('assets/admin/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

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
