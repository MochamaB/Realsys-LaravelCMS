@extends('admin.layouts.master')

@section('title', 'Manage Menus')

@section('content')
<div class="container-fluid">
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Menus</h5>
                    <div class="page-title-right">
                        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary waves-effect waves-light">
                            <i class="ri-add-line align-middle me-1"></i> Add New Menu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menus as $menu)
                                <tr>
                                    <td>{{ $menu->id }}</td>
                                    <td>{{ $menu->name }}</td>
                                    <td>{{ $menu->location }}</td>
                                    <td>{{ $menu->items_count }}</td>
                                    <td>
                                        @if($menu->is_active)
                                            <span class="badge rounded-pill bg-success">Active</span>
                                        @else
                                            <span class="badge rounded-pill bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                         @include('admin.partials.actionbuttons', [
                                            'model' => $menu,
                                            'type' => 'inline',
                                            'itemsRoute' => 'admin.menus.items',
                                            'previewRoute' => null,
                                            'viewRoute' => 'admin.menus.show',
                                            'editRoute' => 'admin.menus.edit',
                                            'destroyRoute' => 'admin.menus.destroy',
                                            'resource' => 'menu',
                                            'resourceName' => 'menu'
                                        ])
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No menus found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
