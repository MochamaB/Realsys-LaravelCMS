{{-- Content Type Fields Management Partial View --}}
<div class="row">
    <!-- Content Type Fields -->
    <div class="col-lg-8 order-1 order-lg-0">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class=" mb-0">
                    <i class="ri-list-check-2"></i> Content Fields
                </h5>
                <div>
                    <a href="{{ route('admin.content-types.fields.create', $contentType) }}" class="btn btn-primary">
                        <i class="ri-add-line"></i> Add Field
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Using our reusable sortable-fields component --}}
                @include('admin.content_types.partials.sortable-fields', [
                    'id' => 'content-type-fields',
                    'fields' => $contentType->fields,
                    'contentType' => $contentType,
                    'emptyMessage' => 'No fields added yet'
                ])
            </div>
        </div>
    </div>
    
    <!-- Available Field Types -->
    <div class="col-lg-4 order-0 order-lg-1 mb-4 mb-lg-0">
        <div class="card sticky-top" style="top: 80px; z-index: 100;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-stack-line"></i> Field Types
                </h5>
            </div>
            <div class="card-body p-0">
                {{-- Using our reusable sortable-field-types component --}}
                <x-sortable-field-types :id="'content-type-field-types'" :group="'fields-group'" />
            </div>
        </div>
    </div>
</div>


