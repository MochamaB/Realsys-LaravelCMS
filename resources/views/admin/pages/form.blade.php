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
                                <!-- Template Selection with Visual Selector -->
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Template <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-2">Choose a template from the active theme "{{ $activeTheme->name }}"</p>
                                    
                                    <!-- Template Change Warning -->
                                    @if(session('template_change'))
                                        <div class="alert alert-warning" role="alert">
                                            <h5 class="alert-heading"><i class="mdi mdi-alert-circle-outline me-2"></i> Template Change Warning</h5>
                                            <p>You are about to change the template from <strong>"{{ session('old_template')->name }}"</strong> to <strong>"{{ session('new_template')->name }}"</strong>.</p>
                                            <p class="mb-0">This may affect the layout and content of your page. Sections that don't exist in the new template will be removed.</p>
                                            <hr>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm_template_change" name="confirm_template_change" value="1" required>
                                                <label class="form-check-label" for="confirm_template_change">
                                                    I understand and want to proceed with the template change
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Visual Template Selector -->
                                    <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                                        @foreach($templates as $template)
                                            <div class="col">
                                                <div class="card h-100 template-card @if(old('template_id', $page->template_id ?? '') == $template->id) border-primary @endif">
                                                    <input type="radio" 
                                                           class="template-radio" 
                                                           name="template_id" 
                                                           id="template_{{ $template->id }}" 
                                                           value="{{ $template->id }}" 
                                                           @if(old('template_id', $page->template_id ?? '') == $template->id) checked @endif
                                                           required>
                                                    
                                                    <!-- Template Thumbnail -->
                                                    <div class="template-thumbnail ratio ratio-16x9">
                                                        @if($template->thumbnail_path)
                                                            <img src="{{ asset($template->thumbnail_path) }}" class="card-img-top" alt="{{ $template->name }}">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center bg-light">
                                                                <i class="mdi mdi-file-document-outline" style="font-size: 48px"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="card-body">
                                                        <h6 class="card-title">{{ $template->name }}</h6>
                                                        @if($template->description)
                                                            <p class="card-text small text-muted">{{ $template->description }}</p>
                                                        @endif
                                                        
                                                        <div class="template-sections small">
                                                            <p class="mb-1 text-muted"><strong>Sections:</strong></p>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($template->sections as $section)
                                                                    <span class="badge bg-light text-dark">{{ $section->name }}</span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Preview button -->
                                                    <div class="card-footer d-flex justify-content-between align-items-center">
                                                        @if($template->is_default)
                                                            <span class="badge bg-success">Default</span>
                                                        @else
                                                            <span></span>
                                                        @endif
                                                        <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="mdi mdi-eye me-1"></i> Preview
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    @error('template_id')
                                        <div class="invalid-feedback d-block">
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
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="is_active" 
                                               name="is_active"
                                               value="1"
                                               {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch form-switch-success">
                                        <input type="hidden" name="show_in_menu" value="0">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="show_in_menu" 
                                               name="show_in_menu"
                                               value="1"
                                               {{ old('show_in_menu', $page->show_in_menu ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_in_menu">Show in Menu</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch form-switch-success">
                                    <input type="hidden" name="is_homepage" value="0">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="is_homepage" 
                                               name="is_homepage"
                                               value="1"
                                               {{ old('is_homepage', $page->is_homepage ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_homepage">Set as Homepage</label>
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
