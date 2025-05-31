@extends('admin.layouts.master')

@section('title', 'Edit Widget')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- FilePond css -->
    <link href="{{ asset('assets/admin/libs/filepond/filepond.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/admin/libs/filepond/filepond-plugin-image-preview.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- CKEditor css -->
    <link href="{{ asset('assets/admin/libs/ckeditor5/sample/css/sample.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Widget</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widgets.index') }}">Widgets</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Widget Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.widgets.update', $widget) }}" 
                          method="POST" 
                          id="widgetForm"
                          class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <label for="widget_type_id" class="form-label">Widget Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('widget_type_id') is-invalid @enderror" 
                                    id="widget_type_id" 
                                    name="widget_type_id" 
                                    required>
                                <option value="">Select Widget Type</option>
                                @foreach($widgetTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('widget_type_id', $widget->widget_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('widget_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="page_section_id" class="form-label">Page Section <span class="text-danger">*</span></label>
                            <select class="form-select @error('page_section_id') is-invalid @enderror" 
                                    id="page_section_id" 
                                    name="page_section_id" 
                                    required>
                                <option value="">Select Page Section</option>
                                @foreach($pageSections as $section)
                                    <option value="{{ $section->id }}" {{ old('page_section_id', $widget->page_section_id) == $section->id ? 'selected' : '' }}>
                                        {{ $section->templateSection->name ?? 'Section' }} ({{ $section->page->title ?? 'Page' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('page_section_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $widget->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content Source Toggle Switch -->
                        <div class="col-12 mt-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Content Source</h5>
                                    <div class="form-check form-switch form-switch-success">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="use_content_source" 
                                               name="use_content_source" 
                                               value="1"
                                               {{ old('use_content_source', $widget->content_query_id ? 1 : 0) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_content_source">Use Content Source</label>
                                    </div>
                                </div>
                                <div class="card-body content-source-options" style="{{ old('use_content_source', $widget->content_query_id ? 1 : 0) ? '' : 'display: none;' }}">
                                    <div class="row g-3">
                                        <div class="col-md-9">
                                            <label for="content_query_id" class="form-label">Select Content Source</label>
                                            <select class="form-select @error('content_query_id') is-invalid @enderror" 
                                                    id="content_query_id" 
                                                    name="content_query_id">
                                                <option value="">-- Select Content Source --</option>
                                                @foreach($contentQueries as $query)
                                                    <option value="{{ $query->id }}" {{ old('content_query_id', $widget->content_query_id) == $query->id ? 'selected' : '' }}>
                                                        {{ $query->contentType->name ?? 'Unknown' }} Content (ID: {{ $query->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('content_query_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Select a content source to dynamically load content into this widget.</small>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <a href="{{ route('admin.widget-content-queries.create') }}" class="btn btn-outline-primary w-100">
                                                <i class="ri-add-line"></i> Create New Source
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Display Settings -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Display Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-9">
                                            <label for="display_settings_id" class="form-label">Select Display Settings</label>
                                            <select class="form-select @error('display_settings_id') is-invalid @enderror" 
                                                    id="display_settings_id" 
                                                    name="display_settings_id">
                                                <option value="">-- Default Display --</option>
                                                @foreach($displaySettings as $setting)
                                                    <option value="{{ $setting->id }}" {{ old('display_settings_id', $widget->display_settings_id) == $setting->id ? 'selected' : '' }}>
                                                        {{ $setting->layout ?? 'Default' }} {{ $setting->view_mode ? '('.$setting->view_mode.')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('display_settings_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Select display settings to control how content appears in this widget.</small>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <a href="{{ route('admin.widget-display-settings.create') }}" class="btn btn-outline-primary w-100">
                                                <i class="ri-add-line"></i> Create New Settings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Widget Content -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Widget Content</h5>
                                </div>
                                <div class="card-body direct-content-options" style="{{ old('use_content_source', $widget->content_query_id ? 1 : 0) ? 'display: none;' : '' }}">
                                    <div id="widgetDescription" class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3"
                                                  placeholder="Enter widget description">{{ old('description', $widget->description) }}</textarea>
                                        <small class="text-muted">Optional: Add a description for this widget instance.</small>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="status" 
                                       name="status" 
                                       value="published"
                                       {{ old('status', $widget->status) === 'published' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Published</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-end">
                                <a href="{{ route('admin.widgets.index') }}" class="btn btn-light me-1">Cancel</a>
                                <button type="submit" class="btn btn-success">Update Widget</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="widgetPreview" class="d-none">
        <!-- Widget preview will be shown here -->
    </div>

    <!-- Field Templates -->
    @foreach($widget->widgetType->fields as $field)
        @if($field->is_repeatable)
            <template id="repeater_template_{{ $field->id }}">
                <div class="repeater-item border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">New Item</h6>
                        <button type="button" class="btn btn-danger btn-sm delete-repeater-item">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    @include('admin.widgets.fields.' . $field->field_type, [
                        'field' => $field,
                        'value' => null,
                        'index' => '__INDEX__'
                    ])
                </div>
            </template>
        @endif
    @endforeach
@endsection

@section('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- FilePond js -->
    <script src="{{ asset('assets/admin/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/filepond/filepond-plugin-file-validate-type.min.js') }}"></script>
    <!-- CKEditor js -->
    <script src="{{ asset('assets/admin/libs/ckeditor5/build/ckeditor.js') }}"></script>
    
    <!-- Custom js -->
    <script src="{{ asset('assets/admin/js/widgets/widget-form.js') }}"></script>
    <script src="{{ asset('assets/admin/js/widgets/widget-preview.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Content Source Toggle
            $('#use_content_source').change(function() {
                if($(this).is(':checked')) {
                    $('.content-source-options').slideDown();
                    $('#content_query_id').prop('disabled', false);
                    $('.direct-content-options').slideUp();
                } else {
                    $('.content-source-options').slideUp();
                    $('#content_query_id').prop('disabled', true);
                    $('.direct-content-options').slideDown();
                }
            });
            
            // Initialize content source toggle on page load
            if($('#use_content_source').is(':checked')) {
                $('.content-source-options').show();
                $('#content_query_id').prop('disabled', false);
                $('.direct-content-options').hide();
            } else {
                $('.content-source-options').hide();
                $('#content_query_id').prop('disabled', true);
                $('.direct-content-options').show();
            }
            
            // Initialize content source visibility
            if($('#use_content_source').is(':checked')) {
                $('.direct-content-options').hide();
            } else {
                $('.content-source-options').hide();
            }
            
            // If user selects to use content source, clear the content query ID when toggling off
            $('#use_content_source').on('change', function() {
                if(!$(this).is(':checked')) {
                    $('#content_query_id').val('');
                }
            });
        });
    </script>
@endsection
