@extends('admin.layouts.master')

@section('title', 'Create ' . $contentType->name)

@section('content')
<div class="container-fluid">
    <!-- Page title -->


    <!-- Form card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title">Create {{ $contentType->name }}</h5>
                    <a href="{{ route('admin.content-types.show', $contentType) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Content Type
                    </a>
                </div>

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

                    @include('admin.content_items.partials._content_item_layout', [
                        'contentType' => $contentType,
                        'fields' => $fields,
                        'contentItem' => null,
                        'isModal' => false,
                        'showTabs' => true,
                        'formAction' => route('admin.content-types.items.store', $contentType),
                        'method' => 'POST'
                    ])
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
