@extends('admin.layouts.master')

@section('page-title', 'Page Designer: ' . $page->title)

@section('css')
<link href="{{ asset('assets/admin/libs/grapesjs/dist/css/grapes.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/page-designer.css') }}" rel="stylesheet" />
@endsection

@section('js')
<script>
    // Make CSRF token available to JavaScript
    window.csrfToken = '{{ csrf_token() }}';
</script>
<script src="{{ asset('assets/admin/libs/grapesjs/dist/grapes.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/page-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-designer.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/widget-manager.js') }}?v={{ time() }}"></script>

<!-- Phase 2.1 Widget Component System -->
<script src="{{ asset('assets/admin/js/components/WidgetComponentFactory.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/widget-component-integration.js') }}?v={{ time() }}"></script>

<!-- Phase 2.3 Section Component System -->
<script src="{{ asset('assets/admin/js/components/SectionComponentFactory.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/section-component-integration.js') }}?v={{ time() }}"></script>

<!-- Phase 3.1 Theme Integration System -->
<script src="{{ asset('assets/admin/js/theme-integration.js') }}?v={{ time() }}"></script>
@endsection



@section('content')

<!-- Remove container-fluid margins by using negative margin hack -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12 px-0" style="margin-left: -12px; margin-right: -12px;">
            <div class="card m-0" style="border-radius: 0; border-left: 0; border-right: 0;">
                <div class="card-body p-0">
                    <!-- Flex container for sidebar and canvas -->
                    <div class="d-flex" style="height: calc(100vh - 220px); overflow: hidden;">
                        
                        <!-- Sidebar -->
                        <div class="panel__blocks bg-light" 
                             style="width: 250px; overflow-y: auto; border-right: 1px solid #e9ebec;">
                            
                            <!-- Sidebar Header -->
                            <div class="sidebar-header p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="ri-layout-3-line me-2"></i>
                                        Page Builder
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-ghost-secondary" id="sidebar-toggle">
                                        <i class="ri-arrow-left-s-line"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Sidebar Content -->
                            <div class="sidebar-content" id="sidebar-content">
                                <!-- GrapesJS blocks will be rendered here -->
                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div style="flex-grow: 1; display: flex; flex-direction: column;">
                            
                            <!-- Top Toolbar -->
                            <div class="toolbar bg-white border-bottom p-2 d-flex justify-content-between align-items-center">
                                <!-- Basic Actions Panel -->
                                <div class="panel__basic-actions d-flex gap-2">
                                    <!-- GrapesJS will populate this -->
                                </div>
                                
                                <!-- Device Panel -->
                                <div class="panel__devices d-flex gap-2">
                                    <!-- GrapesJS will populate this -->
                                </div>
                            </div>
                            
                            <!-- GrapesJS Canvas -->
                            <div id="gjs" 
                                 data-page-id="{{ $page->id }}" 
                                 style="flex-grow: 1; height: 100%; overflow: auto;">
                            </div>
                            
                        </div>
                        
                        <!-- Right Sidebar for Properties -->
                        <div class="properties-sidebar bg-light" 
                             style="width: 300px; overflow-y: auto; border-left: 1px solid #e9ebec;">
                            
                            <!-- Properties Header -->
                            <div class="properties-header p-3 border-bottom">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-settings-3-line me-2"></i>
                                    Properties
                                </h6>
                            </div>
                            
                            <!-- Properties Content -->
                            <div class="properties-content">
                                <!-- Layers Manager -->
                                <div class="property-section">
                                    <div class="property-header p-2 border-bottom bg-light">
                                        <small class="fw-semibold text-muted">LAYERS</small>
                                    </div>
                                    <div class="layers-container p-2">
                                        <!-- GrapesJS Layers will be rendered here -->
                                    </div>
                                </div>
                                
                                <!-- Style Manager -->
                                <div class="property-section">
                                    <div class="property-header p-2 border-bottom bg-light">
                                        <small class="fw-semibold text-muted">STYLES</small>
                                    </div>
                                    <div class="styles-container p-2">
                                        <!-- GrapesJS Styles will be rendered here -->
                                    </div>
                                </div>
                                
                                <!-- Trait Manager -->
                                <div class="property-section">
                                    <div class="property-header p-2 border-bottom bg-light">
                                        <small class="fw-semibold text-muted">SETTINGS</small>
                                    </div>
                                    <div class="traits-container p-2">
                                        <!-- GrapesJS Traits will be rendered here -->
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.pages.widget-config-modal')
@endsection