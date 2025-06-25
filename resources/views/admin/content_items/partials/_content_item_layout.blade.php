{{-- 
  Content Item Layout Partial 
  Parameters:
  - $contentType: The content type being edited
  - $fields: Collection of fields for this content type
  - $contentItem: The content item being edited (null for create)
  - $isModal: Whether this form is displayed in a modal (default: false)
  - $formAction: The form submission URL
  - $method: Form method (POST for create, PUT for edit)
--}}

@php
    $isModal = $isModal ?? false;
    $contentItem = $contentItem ?? null;
    $prefix = $isModal ? 'modal-' : '';
    $formId = $prefix . 'content-item-form';
    $showTabs = $showTabs ?? true;
@endphp

<form id="{{ $formId }}" action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="content-item-form">
    @csrf
    @if(isset($method) && $method === 'PUT')
        @method('PUT')
    @endif

    <div class="row">
        {{-- Left Panel (Content Fields) - 70% --}}
        <div class="col-lg-8">
            @if ($showTabs)
                <ul class="nav nav-tabs-custom" id="{{ $prefix }}content-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="{{ $prefix }}content-tab" data-bs-toggle="tab" 
                            data-bs-target="#{{ $prefix }}content" type="button" role="tab" 
                            aria-controls="{{ $prefix }}content" aria-selected="true">Content</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="{{ $prefix }}permissions-tab" data-bs-toggle="tab" 
                            data-bs-target="#{{ $prefix }}permissions" type="button" role="tab" 
                            aria-controls="{{ $prefix }}permissions" aria-selected="false">Permissions</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="{{ $prefix }}history-tab" data-bs-toggle="tab" 
                            data-bs-target="#{{ $prefix }}history" type="button" role="tab" 
                            aria-controls="{{ $prefix }}history" aria-selected="false">History</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="{{ $prefix }}references-tab" data-bs-toggle="tab" 
                            data-bs-target="#{{ $prefix }}references" type="button" role="tab" 
                            aria-controls="{{ $prefix }}references" aria-selected="false">References</button>
                    </li>
                </ul>

                <div class="tab-content p-3 border border-top-0 mb-4">
                    <div class="tab-pane fade show active" id="{{ $prefix }}content" role="tabpanel" aria-labelledby="{{ $prefix }}content-tab">
                        @include('admin.content_items.partials._form', [
                            'contentType' => $contentType,
                            'fields' => $fields,
                            'contentItem' => $contentItem,
                            'prefix' => $prefix
                        ])
                    </div>
                    <div class="tab-pane fade" id="{{ $prefix }}permissions" role="tabpanel" aria-labelledby="{{ $prefix }}permissions-tab">
                        <p class="text-muted">Permission settings will be implemented in a future update.</p>
                    </div>
                    <div class="tab-pane fade" id="{{ $prefix }}history" role="tabpanel" aria-labelledby="{{ $prefix }}history-tab">
                        <p class="text-muted">Content history will appear here after first save.</p>
                    </div>
                    <div class="tab-pane fade" id="{{ $prefix }}references" role="tabpanel" aria-labelledby="{{ $prefix }}references-tab">
                        <p class="text-muted">Content references will appear here after relationships are established.</p>
                    </div>
                </div>
            @else
                <div class="p-3 border mb-4">
                    @include('admin.content_items.partials._form', [
                        'contentType' => $contentType,
                        'fields' => $fields,
                        'contentItem' => $contentItem,
                        'prefix' => $prefix
                    ])
                </div>
            @endif
        </div>

        {{-- Right Panel (Content Item Form & Metadata) - 30% --}}
        <div class="col-lg-4">
            @include('admin.content_items.partials._content_item_form', [
                'contentType' => $contentType,
                'contentItem' => $contentItem,
                'prefix' => $prefix
            ])
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                {{ $contentItem ? 'Update' : 'Create' }} {{ $contentType->name }}
            </button>
            @if(!$isModal)
                <a href="{{ route('admin.content-types.items.index', $contentType) }}" class="btn btn-secondary">Cancel</a>
            @endif
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('{{ $formId }}');
        
        // Initialize rich text editors
        $('.rich-text-editor').each(function() {
            $(this).summernote({
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
        });
    });
</script>
@endpush
