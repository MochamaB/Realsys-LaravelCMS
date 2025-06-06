@extends('admin.layouts.master')

@section('title', 'Menu Details')

@section('css')
<link href="{{ asset('assets/admin/css/menu-manager.css') }}" rel="stylesheet" type="text/css" />
@if(file_exists(public_path('assets/admin/libs/nestable2/jquery.nestable.min.css')))
<link href="{{ asset('assets/admin/libs/nestable2/jquery.nestable.min.css') }}" rel="stylesheet" type="text/css" />
@endif
@endsection

@section('content')
<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4" style="border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">
                    <h4>Menu: {{ $menu->name }}</h4>
                    <div class="d-flex gap-2"> <!-- Changed from btn-group to d-flex gap-2 -->
                        
                        <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-primary waves-effect waves-light">
                            <i class="ri-pencil-line align-middle me-1"></i> Edit Menu
                        </a>
                        <a href="{{ route('admin.menus.items', $menu->id) }}" class="btn btn-info waves-effect waves-light">
                            <i class="ri-list-check-2 align-middle me-1"></i> Manage Menu Items
                        </a>
                        <a href="{{ route('admin.menus.items.create', $menu->id) }}" class="btn btn-success waves-effect waves-light">
                            <i class="ri-add-line align-middle me-1"></i> Add Menu Item
                        </a>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary waves-effect">
                            <i class="ri-arrow-left-line align-middle me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong> {{ $menu->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Location:</strong> {{ ucfirst($menu->location) }}
                    </div>
                    <div class="mb-3">
                        <strong>Slug:</strong> {{ $menu->slug }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> 
                        @if($menu->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong> 
                        <p>{{ $menu->description ?? 'No description' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Structure</h5>
                </div>
                <div class="card-body">
                    @if($menu->rootItems->isNotEmpty())
                        <div class="dd" id="menu-items-nestable">
                            <ol class="dd-list">
                                @foreach($menu->rootItems as $item)
                                    @include('admin.menus.partials.menu-item', ['item' => $item])
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No menu items found. <a href="{{ route('admin.menus.items.create', $menu->id) }}">Create your first menu item</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/admin/libs/nestable2/jquery.nestable.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/menu-manager.js') }}"></script>
@endsection
