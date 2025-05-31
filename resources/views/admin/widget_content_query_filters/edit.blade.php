@extends('admin.layouts.app')

@section('title', 'Edit Query Filter')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Query Filter</h1>
        <a href="{{ route('widget-content-queries.edit', $filter->query) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Query
        </a>
    </div>

    @include('admin.partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Settings</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('widget-content-query-filters.update', $filter) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label>Filter Type</label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="filterTypeField" name="filter_type" class="custom-control-input" value="field" {{ $filter->field_id ? 'checked' : '' }}>
                        <label class="custom-control-label" for="filterTypeField">Content Field</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="filterTypeProperty" name="filter_type" class="custom-control-input" value="property" {{ $filter->field_key ? 'checked' : '' }}>
                        <label class="custom-control-label" for="filterTypeProperty">Content Property</label>
                    </div>
                </div>
                
                <div id="fieldSelection" class="form-group" {{ $filter->field_key ? 'style="display: none;"' : '' }}>
                    <label for="field_id">Field</label>
                    <select class="form-control @error('field_id') is-invalid @enderror" id="field_id" name="field_id">
                        <option value="">-- Select Field --</option>
                        @foreach($contentTypeFields as $field)
                            <option value="{{ $field->id }}" {{ old('field_id', $filter->field_id) == $field->id ? 'selected' : '' }}>
                                {{ $field->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('field_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Select a field from the content type.</small>
                </div>
                
                <div id="propertySelection" class="form-group" {{ $filter->field_id ? 'style="display: none;"' : '' }}>
                    <label for="field_key">Property</label>
                    <select class="form-control @error('field_key') is-invalid @enderror" id="field_key" name="field_key">
                        <option value="">-- Select Property --</option>
                        <option value="title" {{ old('field_key', $filter->field_key) == 'title' ? 'selected' : '' }}>Title</option>
                        <option value="slug" {{ old('field_key', $filter->field_key) == 'slug' ? 'selected' : '' }}>Slug</option>
                        <option value="status" {{ old('field_key', $filter->field_key) == 'status' ? 'selected' : '' }}>Status</option>
                        <option value="created_at" {{ old('field_key', $filter->field_key) == 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="updated_at" {{ old('field_key', $filter->field_key) == 'updated_at' ? 'selected' : '' }}>Updated Date</option>
                        <option value="published_at" {{ old('field_key', $filter->field_key) == 'published_at' ? 'selected' : '' }}>Published Date</option>
                    </select>
                    @error('field_key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Select a built-in property.</small>
                </div>
                
                <div class="form-group">
                    <label for="operator">Operator</label>
                    <select class="form-control @error('operator') is-invalid @enderror" id="operator" name="operator">
                        <option value="equals" {{ old('operator', $filter->operator) == 'equals' ? 'selected' : '' }}>Equals</option>
                        <option value="not_equals" {{ old('operator', $filter->operator) == 'not_equals' ? 'selected' : '' }}>Not Equals</option>
                        <option value="contains" {{ old('operator', $filter->operator) == 'contains' ? 'selected' : '' }}>Contains</option>
                        <option value="starts_with" {{ old('operator', $filter->operator) == 'starts_with' ? 'selected' : '' }}>Starts With</option>
                        <option value="ends_with" {{ old('operator', $filter->operator) == 'ends_with' ? 'selected' : '' }}>Ends With</option>
                        <option value="greater_than" {{ old('operator', $filter->operator) == 'greater_than' ? 'selected' : '' }}>Greater Than</option>
                        <option value="less_than" {{ old('operator', $filter->operator) == 'less_than' ? 'selected' : '' }}>Less Than</option>
                        <option value="in" {{ old('operator', $filter->operator) == 'in' ? 'selected' : '' }}>In List</option>
                        <option value="not_in" {{ old('operator', $filter->operator) == 'not_in' ? 'selected' : '' }}>Not In List</option>
                        <option value="is_null" {{ old('operator', $filter->operator) == 'is_null' ? 'selected' : '' }}>Is Empty</option>
                        <option value="is_not_null" {{ old('operator', $filter->operator) == 'is_not_null' ? 'selected' : '' }}>Is Not Empty</option>
                    </select>
                    @error('operator')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group" id="valueGroup" {{ in_array($filter->operator, ['is_null', 'is_not_null']) ? 'style="display: none;"' : '' }}>
                    <label for="value">Value</label>
                    <input type="text" class="form-control @error('value') is-invalid @enderror" id="value" name="value" value="{{ old('value', $filter->value) }}">
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted" id="valueHelp">
                        @if(in_array($filter->operator, ['in', 'not_in']))
                            Enter comma-separated values.
                        @else
                            Enter the value to compare against.
                        @endif
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="condition_group">Condition Group (Optional)</label>
                    <input type="text" class="form-control @error('condition_group') is-invalid @enderror" id="condition_group" name="condition_group" value="{{ old('condition_group', $filter->condition_group) }}">
                    @error('condition_group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Filters with the same group name will be combined with OR logic. Different groups use AND logic.
                    </small>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Update Filter</button>
                    <a href="{{ route('widget-content-queries.edit', $filter->query) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle between field and property selection
        $('input[name="filter_type"]').change(function() {
            if ($(this).val() === 'field') {
                $('#fieldSelection').show();
                $('#propertySelection').hide();
                $('#field_key').val('');
            } else {
                $('#fieldSelection').hide();
                $('#propertySelection').show();
                $('#field_id').val('');
            }
        });
        
        // Toggle value field based on operator
        $('#operator').change(function() {
            var operator = $(this).val();
            if (operator === 'is_null' || operator === 'is_not_null') {
                $('#valueGroup').hide();
            } else {
                $('#valueGroup').show();
                
                // Update help text based on operator
                if (operator === 'in' || operator === 'not_in') {
                    $('#valueHelp').text('Enter comma-separated values.');
                } else {
                    $('#valueHelp').text('Enter the value to compare against.');
                }
            }
        });
    });
</script>
@endpush
