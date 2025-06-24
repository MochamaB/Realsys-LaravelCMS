@extends('admin.layouts.master')

@section('title', 'Content Type: ' . $contentType->name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">{{ $contentType->name }}</h4>
                    <p class="text-muted mb-0">{{ $contentType->description }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.content-types.edit', $contentType) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> Edit Content Type
                    </a>
                    <a href="{{ route('admin.content-types.items.create', $contentType) }}" class="btn btn-success">
                        <i class="bx bx-plus"></i> Create Content Item
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs-custom "  id="contentTypeTab" role="tablist" style="border-bottom: 1px solid #dee2e6;">
                <li class="nav-item">
               
                    <button class="nav-link active" id="properties-tab" data-bs-toggle="tab" 
                          data-bs-target="#properties" type="button" role="tab">
                        <i class="bx bx-info-circle"></i> Properties
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="fields-tab" data-bs-toggle="tab" 
                          data-bs-target="#fields" type="button" role="tab">
                        <i class="bx bx-list-ul"></i> Fields 
                        <span class="badge bg-primary">{{ $contentType->fields->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="content-tab" data-bs-toggle="tab" 
                          data-bs-target="#content" type="button" role="tab">
                        <i class="bx bx-file"></i> Content Items 
                        <span class="badge bg-success">{{ $contentType->contentItems->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="relationships-tab" data-bs-toggle="tab" 
                          data-bs-target="#relationships" type="button" role="tab">
                        <i class="bx bx-link"></i> Relationships
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="contentTypeTabContent">
        <div class="tab-pane fade show active" id="properties" role="tabpanel">
        @include('admin.content_types.partials._properties', ['contentType' => $contentType])
        </div>
        <div class="tab-pane fade" id="fields" role="tabpanel">
            @include('admin.content_types.partials._fields', ['contentType' => $contentType])
        </div>
        <div class="tab-pane fade" id="content" role="tabpanel">
            @include('admin.content_types.partials._content', ['contentType' => $contentType])
        </div>
        <div class="tab-pane fade" id="relationships" role="tabpanel">
            {{-- Will implement this tab next --}}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabKey = 'activeContentTypeTab';

    // Restore the saved tab
    const savedTab = localStorage.getItem(tabKey);
    if (savedTab) {
        const trigger = document.querySelector(`[data-bs-target="${savedTab}"]`);
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }

    // Store the active tab when changed
    document.querySelectorAll('#contentTypeTab button[data-bs-toggle="tab"]').forEach(button => {
        button.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) localStorage.setItem(tabKey, target);
        });
    });

    // Your delete function
    window.confirmDelete = function (itemId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `{{ route('admin.content-types.items.index', $contentType) }}/${itemId}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };
});
</script>
@endpush
