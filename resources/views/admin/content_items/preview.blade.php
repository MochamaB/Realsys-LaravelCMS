@extends('admin.layouts.master')

@section('title', 'Preview: ' . $contentItem->title)

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Preview: {{ $contentItem->title }}</h4>

                <div class="page-title-right">
                    <div class="btn-group">
                        <a href="{{ route('admin.content-types.items.edit', [$contentType, $contentItem]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.content-types.items.show', [$contentType, $contentItem]) }}" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                        <a href="{{ route('admin.content-types.items.index', $contentType) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Preview controls -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3 mb-md-0">
                                <label class="form-label">Content Status</label>
                                <div>
                                    @if($contentItem->status == 'published')
                                        <span class="badge bg-success">Published</span>
                                    @elseif($contentItem->status == 'draft')
                                        <span class="badge bg-warning">Draft</span>
                                    @else
                                        <span class="badge bg-danger">Archived</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3 mb-md-0">
                                <label class="form-label">Preview Mode</label>
                                <select id="preview-mode" class="form-select">
                                    <option value="desktop" selected>Desktop</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="mobile">Mobile</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3 mb-md-0">
                                <label class="form-label">Theme</label>
                                <select id="preview-theme" class="form-select">
                                    <option value="default" selected>Default Theme</option>
                                    <!-- Add other themes here if available -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Preview frame -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Content Preview</h5>
                </div>
                <div class="card-body p-0">
                    <div id="preview-container" class="preview-desktop bg-light">
                        <div class="preview-content">
                            <!-- Content item preview -->
                            <div class="content-preview p-4">
                                <h1 class="mb-4">{{ $contentItem->title }}</h1>
                                
                                @foreach($contentItem->fieldValues as $fieldValue)
                                    @switch($fieldValue->field->type)
                                        @case('rich_text')
                                            <div class="mb-4">
                                                {!! $fieldValue->value !!}
                                            </div>
                                            @break
                                            
                                        @case('image')
                                            @if($contentItem->getMedia('field_' . $fieldValue->field->id)->count() > 0)
                                                <div class="mb-4">
                                                    <img src="{{ $contentItem->getFirstMediaUrl('field_' . $fieldValue->field->id) }}" alt="Image" style="max-width: 100%;" class="img-fluid">
                                                </div>
                                            @endif
                                            @break
                                            
                                        @case('gallery')
                                            @if($contentItem->getMedia('field_' . $fieldValue->field->id)->count() > 0)
                                                <div class="mb-4 row">
                                                    @foreach($contentItem->getMedia('field_' . $fieldValue->field->id) as $media)
                                                        <div class="col-md-4 mb-3">
                                                            <img src="{{ $media->getUrl() }}" alt="Gallery image" class="img-fluid rounded">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break
                                            
                                        @case('textarea')
                                            <div class="mb-4">
                                                {{ $fieldValue->value }}
                                            </div>
                                            @break
                                            
                                        @case('json')
                                            @php
                                                $jsonData = json_decode($fieldValue->value, true);
                                                if (is_array($jsonData) && isset($jsonData['html'])) {
                                                    echo '<div class="mb-4">' . $jsonData['html'] . '</div>';
                                                }
                                            @endphp
                                            @break
                                            
                                        @default
                                            @if(in_array($fieldValue->field->key, ['body', 'content', 'description', 'excerpt']))
                                                <div class="mb-4">
                                                    {{ $fieldValue->value }}
                                                </div>
                                            @endif
                                    @endswitch
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #preview-container {
        border: 1px solid #dee2e6;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 600px;
        position: relative;
    }
    
    .preview-desktop {
        width: 100%;
    }
    
    .preview-tablet {
        width: 768px;
        margin: 0 auto;
    }
    
    .preview-mobile {
        width: 375px;
        margin: 0 auto;
    }
    
    .preview-content {
        background-color: white;
        height: 100%;
        overflow-y: auto;
    }
    
    .content-preview {
        max-width: 1200px;
        margin: 0 auto;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const previewModeSelect = document.getElementById('preview-mode');
        const previewContainer = document.getElementById('preview-container');
        
        // Change preview size based on mode selection
        previewModeSelect.addEventListener('change', function() {
            previewContainer.classList.remove('preview-desktop', 'preview-tablet', 'preview-mobile');
            previewContainer.classList.add('preview-' + this.value);
        });
    });
</script>
@endpush
