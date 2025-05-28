@extends('admin.layouts.master')

@section('title', 'Create ' . $contentType->name)

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Create {{ $contentType->name }}</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.content-types.items.index', $contentType) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
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

                    <form action="{{ route('admin.content-types.items.store', $contentType) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Basic information -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}">
                                    <small class="text-muted">Leave empty to auto-generate from title</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <!-- Dynamic content fields -->
                        <h5 class="mb-4">Content</h5>
                        
                        @foreach($fields as $field)
                            <div class="mb-4">
                                <label for="field_{{ $field->id }}" class="form-label">
                                    {{ $field->name }}
                                    @if($field->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                
                                @if($field->description)
                                    <p class="text-muted small">{{ $field->description }}</p>
                                @endif
                                
                                @switch($field->type)
                                    @case('text')
                                        <input type="text" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('textarea')
                                        <textarea class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" rows="3" {{ $field->is_required ? 'required' : '' }}>{{ old('field_' . $field->id, $field->default_value) }}</textarea>
                                        @break
                                        
                                    @case('rich_text')
                                        <textarea class="form-control rich-text-editor" id="field_{{ $field->id }}" name="field_{{ $field->id }}" rows="5" {{ $field->is_required ? 'required' : '' }}>{{ old('field_' . $field->id, $field->default_value) }}</textarea>
                                        @break
                                        
                                    @case('number')
                                        <input type="number" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('date')
                                        <input type="date" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('datetime')
                                        <input type="datetime-local" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('boolean')
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="1" {{ old('field_' . $field->id, $field->default_value) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="field_{{ $field->id }}">Yes</label>
                                        </div>
                                        @break
                                        
                                    @case('select')
                                        <select class="form-select" id="field_{{ $field->id }}" name="field_{{ $field->id }}" {{ $field->is_required ? 'required' : '' }}>
                                            <option value="">Select an option</option>
                                            @foreach($field->options as $option)
                                                <option value="{{ $option->value }}" {{ old('field_' . $field->id, $field->default_value) == $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                        
                                    @case('multiselect')
                                        <select class="form-select" id="field_{{ $field->id }}" name="field_{{ $field->id }}[]" multiple {{ $field->is_required ? 'required' : '' }}>
                                            @foreach($field->options as $option)
                                                <option value="{{ $option->value }}" {{ (is_array(old('field_' . $field->id)) && in_array($option->value, old('field_' . $field->id))) ? 'selected' : '' }}>{{ $option->label }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                        
                                    @case('image')
                                        <input type="file" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" accept="image/*" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('gallery')
                                        <input type="file" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}[]" accept="image/*" multiple {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('file')
                                        <input type="file" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('url')
                                        <input type="url" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('email')
                                        <input type="email" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('phone')
                                        <input type="tel" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('color')
                                        <input type="color" class="form-control form-control-color" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value ?: '#000000') }}" {{ $field->is_required ? 'required' : '' }}>
                                        @break
                                        
                                    @case('json')
                                        <textarea class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" rows="5" {{ $field->is_required ? 'required' : '' }}>{{ old('field_' . $field->id, $field->default_value ?: '{}') }}</textarea>
                                        @break
                                        
                                    @default
                                        <input type="text" class="form-control" id="field_{{ $field->id }}" name="field_{{ $field->id }}" value="{{ old('field_' . $field->id, $field->default_value) }}" {{ $field->is_required ? 'required' : '' }}>
                                @endswitch
                            </div>
                        @endforeach
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create {{ $contentType->name }}</button>
                            <a href="{{ route('admin.content-items.index', $contentType) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Rich text editor initialization
        $('.rich-text-editor').summernote({
            placeholder: 'Enter content here...',
            tabsize: 2,
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
        
        // Auto-generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        
        if (titleInput && slugInput) {
            titleInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    slugInput.value = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            });
        }
    });
</script>
@endpush
