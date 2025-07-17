<div class="container-fluid">
    <input type="hidden" id="template-id" value="{{ $template->id }}">
    <!-- Designer Header -->
    <div class="template-designer-header">
        <h5>Template Designer: {{ $template->name }}</h5>
        <div class="control-buttons">
            <button class="btn btn-primary" id="add-section-btn">Add Section</button>
            <button class="btn btn-primary" id="save-layout-btn">Save Layout</button>
            <button class="btn btn-info" id="preview-btn" data-template-id="{{ $template->id }}">Preview</button>
        </div>
    </div>
    <!-- Main Layout Area (Full Width) -->
    <div class="col-12">
        <!-- System Header Section -->
        <div class="system-section" data-section-type="header">
            <div class="section-label">Header</div>
            <div class="section-controls">
                <button class="btn btn-sm btn-outline-secondary edit-system-section-btn">
                    <i class="ri-pencil-line"></i>
                </button>
            </div>
        </div>
        <!-- Main Content Grid (Full Width) -->
        <div class="content-area">
            <div class="grid-stack" id="main-grid-stack">
                <!-- GridStack content will be generated dynamically by JavaScript -->
            </div>
        </div>
        <!-- System Footer Section -->
        <div class="system-section" data-section-type="footer">
            <div class="section-label">Footer</div>
            <div class="section-controls">
                <button class="btn btn-sm btn-outline-secondary edit-system-section-btn">
                    <i class="ri-pencil-line"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@push('styles')
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/template-designer.css') }}" rel="stylesheet" />
@endpush

@include('admin.templates.sections._section_editor')
@include('admin.templates.sections._system_section_editor')