@extends('admin.layouts.master')

@section('title', 'Edit Field Option')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Field Option</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.index') }}">Widget Types</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.fields.index', $field->widget_type_id) }}">Fields</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.widget-types.fields.options.index', $field) }}">Options</a></li>
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
                    <h4 class="card-title mb-0">Edit Field Option for "{{ $field->name }}"</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <h4 class="alert-heading"><i class="ri-information-line me-1"></i> Field Information</h4>
                                <p class="mb-0"><strong>Name:</strong> {{ $field->name }}</p>
                                <p class="mb-0"><strong>Type:</strong> {{ ucfirst($field->field_type) }}</p>
                                <p class="mb-0"><strong>Key:</strong> {{ $field->key }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.widget-types.fields.options.update', [$field, $option]) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="value" name="value" value="{{ old('value', $option->value) }}" required>
                                <div class="invalid-feedback">Please enter a value.</div>
                                @error('value')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-text">This is the value that will be stored in the database.</div>
                            </div>
                            <div class="col-lg-6">
                                <label for="label" class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="label" name="label" value="{{ old('label', $option->label) }}" required>
                                <div class="invalid-feedback">Please enter a label.</div>
                                @error('label')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-text">This is the label that will be displayed to the user.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ route('admin.widget-types.fields.options.index', $field) }}" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Option</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
@endsection
