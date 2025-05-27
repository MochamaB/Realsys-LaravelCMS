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
                                        {{ $section->name }} ({{ $section->page->title }})
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

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Widget Content</h5>
                                </div>
                                <div class="card-body">
                                    <div id="widgetFields">
                                        @foreach($widget->widgetType->fields as $field)
                                            <div class="mb-3">
                                                <label for="field_{{ $field->id }}" class="form-label">
                                                    {{ $field->label }}
                                                    @if($field->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>

                                                @if($field->is_repeatable)
                                                    <div class="widget-repeater">
                                                        <div class="repeater-items">
                                                            @foreach($widget->getFieldValues($field->id) as $index => $value)
                                                                <div class="repeater-item border rounded p-3 mb-3">
                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <h6 class="mb-0">Item {{ $index + 1 }}</h6>
                                                                        <button type="button" class="btn btn-danger btn-sm delete-repeater-item">
                                                                            <i class="ri-delete-bin-line"></i>
                                                                        </button>
                                                                    </div>
                                                                    @include('admin.widgets.fields.' . $field->field_type, [
                                                                        'field' => $field,
                                                                        'value' => $value,
                                                                        'index' => $index
                                                                    ])
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-soft-success btn-sm add-repeater-item">
                                                            <i class="ri-add-line"></i> Add Item
                                                        </button>
                                                    </div>
                                                @else
                                                    @include('admin.widgets.fields.' . $field->field_type, [
                                                        'field' => $field,
                                                        'value' => $widget->getFieldValue($field->id)
                                                    ])
                                                @endif

                                                @if($field->help_text)
                                                    <div class="form-text">{{ $field->help_text }}</div>
                                                @endif
                                            </div>
                                        @endforeach
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
@endsection
