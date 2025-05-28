@extends('admin.layouts.master')

@section('title', 'Content Type Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0">Content Type Details</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $contentType->name }}</dd>
                        <dt class="col-sm-3">Key</dt>
                        <dd class="col-sm-9"><code>{{ $contentType->key }}</code></dd>
                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $contentType->description }}</dd>
                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if($contentType->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                    <hr>
                    <h5>Fields</h5>
                    @if($contentType->fields->isEmpty())
                        <p class="text-muted">No fields defined for this content type yet.</p>
                    @else
                        <ul class="list-group mb-3">
                            @foreach($contentType->fields as $field)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $field->name }} <code>({{ $field->key }})</code> - {{ $field->type }}</span>
                                    <a href="{{ route('admin.content-types.fields.edit', [$contentType, $field]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="{{ route('admin.content-types.fields.create', $contentType) }}" class="btn btn-secondary">Add Field</a>
                    <a href="{{ route('admin.content-types.index') }}" class="btn btn-link">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
