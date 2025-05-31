@extends('admin.layouts.app')

@section('title', 'Edit Content Query')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Content Query #{{ $contentQuery->id }}</h1>
        <div>
            <a href="{{ route('widget-content-queries.preview', $contentQuery) }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm mr-2">
                <i class="fas fa-play fa-sm text-white-50"></i> Preview Results
            </a>
            <a href="{{ route('widget-content-queries.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Queries
            </a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Query Settings</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('widget-content-queries.update', $contentQuery) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="content_type_id">Content Type</label>
                            <select class="form-control @error('content_type_id') is-invalid @enderror" id="content_type_id" name="content_type_id">
                                <option value="">-- Select Content Type --</option>
                                @foreach($contentTypes as $contentType)
                                    <option value="{{ $contentType->id }}" {{ old('content_type_id', $contentQuery->content_type_id) == $contentType->id ? 'selected' : '' }}>
                                        {{ $contentType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('content_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select the type of content this query will fetch.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="order_by">Sort By</label>
                            <select class="form-control @error('order_by') is-invalid @enderror" id="order_by" name="order_by">
                                <option value="">-- Default Sorting --</option>
                                @foreach($orderOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('order_by', $contentQuery->order_by) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('order_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="order_direction">Sort Direction</label>
                            <select class="form-control @error('order_direction') is-invalid @enderror" id="order_direction" name="order_direction">
                                @foreach($directionOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('order_direction', $contentQuery->order_direction) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('order_direction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="limit">Limit (Max Items)</label>
                            <input type="number" class="form-control @error('limit') is-invalid @enderror" id="limit" name="limit" value="{{ old('limit', $contentQuery->limit) }}" min="1">
                            @error('limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Leave empty for no limit.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="offset">Offset (Skip Items)</label>
                            <input type="number" class="form-control @error('offset') is-invalid @enderror" id="offset" name="offset" value="{{ old('offset', $contentQuery->offset) }}" min="0">
                            @error('offset')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Number of items to skip from the beginning.</small>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Update Query</button>
                            <a href="{{ route('widget-content-queries.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Query Filters</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addFilterModal">
                        <i class="fas fa-plus fa-sm"></i> Add Filter
                    </button>
                </div>
                <div class="card-body">
                    @if($contentQuery->filters->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Operator</th>
                                        <th>Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contentQuery->filters as $filter)
                                        <tr>
                                            <td>
                                                @if($filter->field_id)
                                                    {{ $filter->field->name ?? 'Unknown Field' }}
                                                @else
                                                    {{ $filter->field_key ?? 'Unknown Property' }}
                                                @endif
                                            </td>
                                            <td>{{ ucwords(str_replace('_', ' ', $filter->operator)) }}</td>
                                            <td>
                                                @if(in_array($filter->operator, ['is_null', 'is_not_null']))
                                                    <em>Not applicable</em>
                                                @else
                                                    {{ Str::limit($filter->value, 30) }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('widget-content-query-filters.edit', $filter) }}" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('widget-content-query-filters.destroy', $filter) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this filter?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No filters applied to this query. Add filters to refine content selection.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Used By Widgets</h6>
                </div>
                <div class="card-body">
                    @if($contentQuery->widgets->count() > 0)
                        <ul class="list-group">
                            @foreach($contentQuery->widgets as $widget)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $widget->name }}</span>
                                    <a href="{{ route('widgets.edit', $widget) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i> Edit Widget
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info">
                            This query is not used by any widgets yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Filter Modal -->
<div class="modal fade" id="addFilterModal" tabindex="-1" role="dialog" aria-labelledby="addFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFilterModalLabel">Add Query Filter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('widget-content-query-filters.store', $contentQuery) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Filter Type</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="filterTypeField" name="filter_type" class="custom-control-input" value="field" checked>
                            <label class="custom-control-label" for="filterTypeField">Content Field</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="filterTypeProperty" name="filter_type" class="custom-control-input" value="property">
                            <label class="custom-control-label" for="filterTypeProperty">Content Property</label>
                        </div>
                    </div>
                    
                    <div id="fieldSelection" class="form-group">
                        <label for="field_id">Field</label>
                        <select class="form-control" id="field_id" name="field_id">
                            <option value="">-- Select Field --</option>
                            @if(isset($contentTypeFields))
                                @foreach($contentTypeFields as $field)
                                    <option value="{{ $field->id }}">{{ $field->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <small class="form-text text-muted">Select a field from the content type.</small>
                    </div>
                    
                    <div id="propertySelection" class="form-group" style="display: none;">
                        <label for="field_key">Property</label>
                        <select class="form-control" id="field_key" name="field_key">
                            <option value="">-- Select Property --</option>
                            <option value="title">Title</option>
                            <option value="slug">Slug</option>
                            <option value="status">Status</option>
                            <option value="created_at">Created Date</option>
                            <option value="updated_at">Updated Date</option>
                            <option value="published_at">Published Date</option>
                        </select>
                        <small class="form-text text-muted">Select a built-in property.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="operator">Operator</label>
                        <select class="form-control" id="operator" name="operator">
                            <option value="equals">Equals</option>
                            <option value="not_equals">Not Equals</option>
                            <option value="contains">Contains</option>
                            <option value="starts_with">Starts With</option>
                            <option value="ends_with">Ends With</option>
                            <option value="greater_than">Greater Than</option>
                            <option value="less_than">Less Than</option>
                            <option value="in">In List</option>
                            <option value="not_in">Not In List</option>
                            <option value="is_null">Is Empty</option>
                            <option value="is_not_null">Is Not Empty</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="valueGroup">
                        <label for="value">Value</label>
                        <input type="text" class="form-control" id="value" name="value">
                        <small class="form-text text-muted" id="valueHelp">
                            Enter the value to compare against.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="condition_group">Condition Group (Optional)</label>
                        <input type="text" class="form-control" id="condition_group" name="condition_group">
                        <small class="form-text text-muted">
                            Filters with the same group name will be combined with OR logic. Different groups use AND logic.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Filter</button>
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
        
        // Load content type fields via AJAX when content type changes
        $('#content_type_id').change(function() {
            var contentTypeId = $(this).val();
            if (contentTypeId) {
                $.ajax({
                    url: '{{ route("widget-content-query-filters.get-fields") }}',
                    type: 'GET',
                    data: { content_type_id: contentTypeId },
                    success: function(data) {
                        var fieldSelect = $('#field_id');
                        fieldSelect.empty();
                        fieldSelect.append('<option value="">-- Select Field --</option>');
                        
                        $.each(data.fields, function(index, field) {
                            fieldSelect.append('<option value="' + field.id + '">' + field.name + '</option>');
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
