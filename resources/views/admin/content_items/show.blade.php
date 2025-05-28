@extends('admin.layouts.master')

@section('title', $contentItem->title)

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $contentItem->title }}</h4>

                <div class="page-title-right">
                    <div class="btn-group">
                        <a href="{{ route('admin.content-types.items.edit', [$contentType, $contentItem]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.content-types.items.preview', [$contentType, $contentItem]) }}" class="btn btn-secondary">
                            <i class="fas fa-desktop me-1"></i> Preview
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
        <!-- Content item details -->
        <div class="col-md-4 col-xl-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $contentItem->id }}</dd>
                        
                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">{{ $contentItem->contentType->name }}</dd>
                        
                        <dt class="col-sm-4">Slug</dt>
                        <dd class="col-sm-8"><code>{{ $contentItem->slug }}</code></dd>
                        
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($contentItem->status == 'published')
                                <span class="badge bg-success">Published</span>
                            @elseif($contentItem->status == 'draft')
                                <span class="badge bg-warning">Draft</span>
                            @else
                                <span class="badge bg-danger">Archived</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $contentItem->created_at->format('M d, Y H:i') }}</dd>
                        
                        <dt class="col-sm-4">Updated</dt>
                        <dd class="col-sm-8">{{ $contentItem->updated_at->format('M d, Y H:i') }}</dd>
                        
                        @if(isset($contentItem->created_by))
                            <dt class="col-sm-4">Author</dt>
                            <dd class="col-sm-8">{{ $contentItem->creator ? $contentItem->creator->name : 'Unknown' }}</dd>
                        @endif
                    </dl>
                </div>
                
                <div class="card-footer text-center">
                    <form action="{{ route('admin.content-types.items.destroy', [$contentType, $contentItem]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Content item fields -->
        <div class="col-md-8 col-xl-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Content</h5>
                </div>
                <div class="card-body">
                    @foreach($contentItem->fieldValues as $fieldValue)
                        <div class="mb-4">
                            <h6 class="fw-bold">{{ $fieldValue->field->name }}</h6>
                            
                            @switch($fieldValue->field->type)
                                @case('rich_text')
                                    <div class="border rounded p-3 bg-light">
                                        {!! $fieldValue->value !!}
                                    </div>
                                    @break
                                    
                                @case('image')
                                    @if($contentItem->getMedia('field_' . $fieldValue->field->id)->count() > 0)
                                        <div class="mb-2">
                                            <img src="{{ $contentItem->getFirstMediaUrl('field_' . $fieldValue->field->id) }}" alt="Image" style="max-height: 300px; max-width: 100%;" class="border rounded">
                                        </div>
                                    @else
                                        <p class="text-muted"><em>No image uploaded</em></p>
                                    @endif
                                    @break
                                    
                                @case('gallery')
                                    @if($contentItem->getMedia('field_' . $fieldValue->field->id)->count() > 0)
                                        <div class="mb-2 d-flex flex-wrap gap-2">
                                            @foreach($contentItem->getMedia('field_' . $fieldValue->field->id) as $media)
                                                <img src="{{ $media->getUrl() }}" alt="Gallery image" style="height: 100px; width: auto;" class="border rounded">
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted"><em>No images uploaded</em></p>
                                    @endif
                                    @break
                                    
                                @case('file')
                                    @if($contentItem->getMedia('field_' . $fieldValue->field->id)->count() > 0)
                                        <a href="{{ $contentItem->getFirstMediaUrl('field_' . $fieldValue->field->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file me-1"></i> {{ $contentItem->getFirstMedia('field_' . $fieldValue->field->id)->file_name }}
                                        </a>
                                    @else
                                        <p class="text-muted"><em>No file uploaded</em></p>
                                    @endif
                                    @break
                                    
                                @case('boolean')
                                    <p>{{ $fieldValue->value ? 'Yes' : 'No' }}</p>
                                    @break
                                    
                                @case('select')
                                    @php
                                        $option = $fieldValue->field->options->where('value', $fieldValue->value)->first();
                                    @endphp
                                    <p>{{ $option ? $option->label : $fieldValue->value }}</p>
                                    @break
                                    
                                @case('multiselect')
                                    @php
                                        $values = json_decode($fieldValue->value, true) ?? [];
                                        $options = $fieldValue->field->options->whereIn('value', $values);
                                    @endphp
                                    @if(count($values) > 0)
                                        <ul class="mb-0">
                                            @foreach($options as $option)
                                                <li>{{ $option->label }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted"><em>None selected</em></p>
                                    @endif
                                    @break
                                    
                                @case('json')
                                    <pre class="border rounded p-3 bg-light"><code>{{ json_encode(json_decode($fieldValue->value), JSON_PRETTY_PRINT) }}</code></pre>
                                    @break
                                    
                                @case('color')
                                    <div class="d-flex align-items-center">
                                        <div style="width: 24px; height: 24px; background-color: {{ $fieldValue->value }}; border-radius: 4px; border: 1px solid #ccc; margin-right: 8px;"></div>
                                        <span>{{ $fieldValue->value }}</span>
                                    </div>
                                    @break
                                    
                                @default
                                    <p>{{ $fieldValue->value }}</p>
                            @endswitch
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
