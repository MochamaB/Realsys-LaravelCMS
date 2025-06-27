@extends('admin.layouts.master')

@section('title', 'Widget: ' . $widget->name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">{{ $widget->name }}</h4>
                    <p class="text-muted mb-0">{{ $widget->description }}</p>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs-custom "  id="widgetTab" role="tablist" style="border-bottom: 1px solid #dee2e6;">
                <li class="nav-item">
               
                    <button class="nav-link active" id="preview-tab" data-bs-toggle="tab" 
                          data-bs-target="#preview" type="button" role="tab">
                        <i class="bx bx-info-circle"></i> Preview
                    </button>
                </li>
                <li class="nav-item" role="relationship-tab">
                    <button class="nav-link" id="relationships-tab" data-bs-toggle="tab" 
                          data-bs-target="#relationships" type="button" role="tab">
                        <i class="bx bx-file"></i> Relationships
                        <span class="badge bg-success">{{ $widget->contentTypeAssociations->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="code-tab">
                    <button class="nav-link" id="code-tab" data-bs-toggle="tab" 
                          data-bs-target="#code" type="button" role="tab">
                        <i class="bx bx-code"></i> Code
                    </button>
                </li>
                <li class="nav-item" role="JSON-tab">
                    <button class="nav-link" id="JSON-tab" data-bs-toggle="tab" 
                          data-bs-target="#JSON" type="button" role="tab">
                        <i class="bx bx-code"></i> JSON
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="widgetTabContent">
        <div class="tab-pane fade show active" id="preview" role="tabpanel">
            @include('admin.widgets.tabs.preview')
        </div>
        
       
        <div class="tab-pane fade" id="relationships" role="tabpanel">
           @include('admin.widgets.tabs.relationships')
        </div>
         <div class="tab-pane fade" id="code" role="tabpanel">
            @include('admin.widgets.tabs.edit_code')
           
        </div>
         <div class="tab-pane fade" id="JSON" role="tabpanel">
            @include('admin.widgets.tabs.json_code')
        </div>
       
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabKey = 'activeWidgetTab';

    // Restore the saved tab
    const savedTab = localStorage.getItem(tabKey);
    if (savedTab) {
        const trigger = document.querySelector(`[data-bs-target="${savedTab}"]`);
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }

    // Store the active tab when changed
    document.querySelectorAll('#widgetTab button[data-bs-toggle="tab"]').forEach(button => {
        button.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) localStorage.setItem(tabKey, target);
        });
    });

    // Your delete function
    window.confirmDelete = function (itemId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `{{ route('admin.widgets.destroy',$widget) }}/${itemId}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };
});
</script>
@endpush
