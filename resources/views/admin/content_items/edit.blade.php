@extends('admin.layouts.master')

@section('title', 'Edit ' . $contentType->name)

@section('content')
<div class="container-fluid">
    <!-- Page title -->



    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title">Edit {{ $contentType->name }}</h5>
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
                        'contentItem' => $contentItem,
                        'isModal' => false,
                        'showTabs' => true,
                        'formAction' => route('admin.content-types.items.update', ['contentType' => $contentType, 'item' => $contentItem]),
                        'method' => 'PUT'
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/repeater-fields.js') }}"></script>
@endsection


