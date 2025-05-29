@extends('admin.layouts.master')

@section('title', 'View Page')

@section('content')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Page Details</h5>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-success btn-sm">
                                <i class="ri-edit-2-line"></i> Edit
                            </a>
                            <a href="{{ route('admin.pages.index') }}" class="btn btn-primary btn-sm">
                                <i class="ri-arrow-go-back-line"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="ps-0" scope="row">Title :</th>
                                            <td class="text-muted">{{ $page->title }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Slug :</th>
                                            <td class="text-muted">{{ $page->slug }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Description :</th>
                                            <td class="text-muted">{{ $page->description }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Template :</th>
                                            <td class="text-muted">{{ $page->template->name ?? 'No Template' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Parent Page :</th>
                                            <td class="text-muted">{{ $page->parent->title ?? 'No Parent' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Status :</th>
                                            <td>
                                                <span class="badge badge-soft-{{ $page->is_active ? 'success' : 'danger' }}">
                                                    {{ $page->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Show in Menu :</th>
                                            <td>
                                                <span class="badge badge-soft-{{ $page->show_in_menu ? 'success' : 'danger' }}">
                                                    {{ $page->show_in_menu ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Menu Order :</th>
                                            <td class="text-muted">{{ $page->menu_order }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Created By :</th>
                                            <td class="text-muted">{{ $page->creator->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Updated By :</th>
                                            <td class="text-muted">{{ $page->updater->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Created At :</th>
                                            <td class="text-muted">{{ $page->created_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0" scope="row">Updated At :</th>
                                            <td class="text-muted">{{ $page->updated_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-4">
                                <h5 class="fs-14 mb-1">Featured Image</h5>
                                @if($page->getFirstMedia('featured'))
                                    <img src="{{ $page->getFirstMediaUrl('featured') }}" 
                                         alt="Featured Image" 
                                         class="img-fluid rounded">
                                @else
                                    <p class="text-muted">No featured image</p>
                                @endif
                            </div>

                            <div class="card border">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Meta Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Meta Title</h6>
                                        <p class="text-muted">{{ $page->meta_title ?? 'Not set' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Meta Description</h6>
                                        <p class="text-muted">{{ $page->meta_description ?? 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <h6 class="fw-semibold">Meta Keywords</h6>
                                        <p class="text-muted">{{ $page->meta_keywords ?? 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="fs-14 mb-3">Content</h5>
                        <div class="border rounded p-3">
                            {!! $page->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
