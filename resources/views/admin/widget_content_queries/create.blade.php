@extends('admin.layouts.app')

@section('title', 'Create Content Query')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Content Query</h1>
        <a href="{{ route('widget-content-queries.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Queries
        </a>
    </div>

    @include('admin.partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Query Settings</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('widget-content-queries.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="content_type_id">Content Type</label>
                    <select class="form-control @error('content_type_id') is-invalid @enderror" id="content_type_id" name="content_type_id">
                        <option value="">-- Select Content Type --</option>
                        @foreach($contentTypes as $contentType)
                            <option value="{{ $contentType->id }}" {{ old('content_type_id') == $contentType->id ? 'selected' : '' }}>
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
                            <option value="{{ $value }}" {{ old('order_by') == $value ? 'selected' : '' }}>
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
                            <option value="{{ $value }}" {{ old('order_direction', 'desc') == $value ? 'selected' : '' }}>
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
                    <input type="number" class="form-control @error('limit') is-invalid @enderror" id="limit" name="limit" value="{{ old('limit') }}" min="1">
                    @error('limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave empty for no limit.</small>
                </div>
                
                <div class="form-group">
                    <label for="offset">Offset (Skip Items)</label>
                    <input type="number" class="form-control @error('offset') is-invalid @enderror" id="offset" name="offset" value="{{ old('offset', 0) }}" min="0">
                    @error('offset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Number of items to skip from the beginning.</small>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Create Query</button>
                    <a href="{{ route('widget-content-queries.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
