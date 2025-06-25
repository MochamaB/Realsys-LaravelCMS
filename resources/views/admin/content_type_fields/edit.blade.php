@extends('admin.layouts.master')

@section('title', 'Edit Field')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Field: {{ $field->name }}</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.content-types.show', $contentType) }}#fields" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back to Content Type
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form card -->
    <div class="row">
        <div class="col-12 col-lg-8">
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

                    @include('admin.content_type_fields.partials._form', [
                        'formAction' => route('admin.content-types.fields.update', [$contentType, $field]),
                        'contentType' => $contentType,
                        'field' => $field,
                        'fieldTypes' => $fieldTypes,
                        'submitButtonText' => 'Update Field'
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
