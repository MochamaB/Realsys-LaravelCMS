<!-- Add Widget Modal - Wizard Interface -->
<div class="modal fade" id="addWidgetModal" tabindex="-1" aria-labelledby="addWidgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white pb-2">
                <h5 class="modal-title text-white" id="addWidgetModalLabel">
                    <i class="ri-puzzle-line me-2"></i>Add Widget to Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                <!-- Section Info -->
                <div class="alert alert-info border-0 mb-3">
                    <i class="ri-information-line me-2"></i>
                    <strong>Add Widget to Section:</strong> Follow the steps to select and configure your widget.
                    <span id="targetSectionInfo" class="d-block mt-1 fw-normal text-muted"></span>
                </div>

                <!-- Progress Steps -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="step-item active" id="step1-indicator">
                                <div class="step-circle">1</div>
                                <div class="step-label">Select Widget</div>
                            </div>
                            <div class="step-divider"></div>
                            <div class="step-item" id="step2-indicator">
                                <div class="step-circle">2</div>
                                <div class="step-label">Content Type</div>
                            </div>
                            <div class="step-divider"></div>
                            <div class="step-item" id="step3-indicator">
                                <div class="step-circle">3</div>
                                <div class="step-label">Content Items</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Widget Selection -->
                <div class="wizard-step" id="step1-widgets" style="display: block;">
                    <h5 class="mb-3">
                        <i class="ri-puzzle-line me-2"></i>Step 1: Choose a Widget
                    </h5>

                    <!-- Widget Tabs -->
                    <ul class="nav nav-tabs mb-3" id="widgetTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="theme-widgets-tab" data-bs-toggle="tab" data-bs-target="#theme-widgets-pane" type="button" role="tab">
                                <i class="ri-palette-line me-1"></i>
                                Theme Widgets
                                <span class="badge bg-primary ms-1" id="themeWidgetsCount">{{ count($themeWidgets ?? []) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="default-widgets-tab" data-bs-toggle="tab" data-bs-target="#default-widgets-pane" type="button" role="tab">
                                <i class="ri-apps-line me-1"></i>
                                Default Widgets
                                <span class="badge bg-success ms-1" id="defaultWidgetsCount">{{ count($defaultWidgets ?? []) }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="widgetTabContent">
                        <!-- Theme Widgets Tab -->
                        <div class="tab-pane fade show active" id="theme-widgets-pane" role="tabpanel">
                            <div class="row g-3" id="themeWidgetsGrid">
                                @if(isset($themeWidgets) && count($themeWidgets) > 0)
                                    @foreach($themeWidgets as $widget)
                                    <div class="col-lg-12 col-md-12">
                                        <div class="card widget-card h-100"
                                             data-widget-id="{{ $widget['id'] }}"
                                             data-widget-slug="{{ $widget['slug'] }}"
                                             data-widget-name="{{ $widget['name'] }}"
                                             data-widget-type="theme"
                                             data-has-content-types="{{ $widget['has_content_types'] ?? false ? 'true' : 'false' }}"
                                             style="cursor: pointer; transition: all 0.2s ease;border: 1px solid #ccc;">

                                            @if($widget['preview_image'] ?? false)
                                                <div class="widget-preview">
                                                    <img src="{{ $widget['preview_image'] }}"
                                                         alt="{{ $widget['name'] }}"
                                                         style="width: 100%; height: 120px; object-fit: cover;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="d-none align-items-center justify-content-center bg-light"
                                                         style="height: 120px;">
                                                        <i class="{{ $widget['icon'] ?? 'ri-puzzle-line' }} display-4 text-primary"></i>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="widget-preview d-flex align-items-center justify-content-center bg-light"
                                                     style="height: 120px;">
                                                    <i class="{{ $widget['icon'] ?? 'ri-puzzle-line' }} display-4 text-primary"></i>
                                                </div>
                                            @endif

                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-1">{{ $widget['name'] }}</h6>
                                                <p class="card-text text-muted small mb-2">{{ $widget['description'] ?? 'No description available' }}</p>

                                                @if($widget['has_content_types'] ?? false)
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-primary text-white small">{{ $widget['content_types_count'] ?? 0 }} Content Types</span>
                                                        <small class="text-muted">Dynamic</small>
                                                    </div>
                                                @else
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-secondary text-white small">Static Widget</span>
                                                        <small class="text-muted">Ready to use</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="ri-palette-line display-1 text-muted"></i>
                                            <h5 class="text-muted mb-2">No Theme Widgets Found</h5>
                                            <p class="text-muted">No theme-specific widgets are available.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Default Widgets Tab -->
                        <div class="tab-pane fade" id="default-widgets-pane" role="tabpanel">
                            <div class="row g-3" id="defaultWidgetsGrid">
                                @if(isset($defaultWidgets) && count($defaultWidgets) > 0)
                                    @foreach($defaultWidgets as $widget)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card widget-card h-100"
                                             data-widget-id="{{ $widget['id'] }}"
                                             data-widget-slug="{{ $widget['slug'] }}"
                                             data-widget-name="{{ $widget['name'] }}"
                                             data-widget-type="default"
                                             data-has-content-types="false"
                                             style="cursor: pointer; transition: all 0.2s ease;">

                                            <div class="widget-preview d-flex align-items-center justify-content-center bg-light"
                                                 style="height: 120px;">
                                                <i class="{{ $widget['icon'] ?? 'ri-apps-line' }} display-4 text-success"></i>
                                            </div>

                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-1">{{ $widget['name'] }}</h6>
                                                <p class="card-text text-muted small mb-2">{{ $widget['description'] ?? 'No description available' }}</p>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-success text-white small">Default</span>
                                                    <small class="text-muted">{{ ucfirst($widget['category'] ?? 'general') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="ri-apps-line display-1 text-muted"></i>
                                            <h5 class="text-muted mb-2">No Default Widgets Found</h5>
                                            <p class="text-muted">No default widgets are available.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Selected Widget Info -->
                    <div class="alert alert-light border mt-3" id="selectedWidgetAlert" style="display: none;">
                        <div class="d-flex align-items-center">
                            <i class="ri-check-circle-line text-success me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Selected Widget:</strong>
                                <span id="selectedWidgetName">None</span>
                                <span class="badge ms-2" id="selectedWidgetType"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Content Type Selection -->
                <div class="wizard-step" id="step2-content-types" style="display: none;">
                    <h5 class="mb-3">
                        <i class="ri-folder-line me-2"></i>Step 2: Choose Content Type
                        <small class="text-muted">for <span id="step2-widget-name"></span></small>
                    </h5>

                    <div class="row g-3" id="modalContentTypesGrid">
                        <!-- Add New Content Type Card (Always First) -->
                        <div class="col-lg-12 col-md-12 mb-3">
                            <div class="card content-type-card h-100 border-dashed border-primary"
                                 style="cursor: pointer; transition: all 0.2s ease;"
                                 data-content-type-id="new">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="ri-add-circle-line display-4 text-primary"></i>
                                    </div>
                                    <h6 class="card-title text-primary mb-2">Create New Content Type</h6>
                                    <p class="card-text text-muted small">
                                        Add a new content type for this widget to organize your content.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Content Types (PHP Loop) -->
                        @if(isset($themeWidgetsWithData) && count($themeWidgetsWithData) > 0)
                            @foreach($themeWidgetsWithData as $widget)
                                @if(isset($widget['content_types']) && count($widget['content_types']) > 0)
                                    @foreach($widget['content_types'] as $contentType)
                                        <div class="col-lg-12 col-md-12 mb-3 modal-content-type-card"
                                             data-widget-id="{{ $widget['id'] }}"
                                             style="display: none;">
                                            <div class="card content-type-card h-100"
                                                 style="cursor: pointer; transition: all 0.2s ease;"
                                                 data-content-type-id="{{ $contentType['id'] }}">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-sm">
                                                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                                                    <i class="{{ $contentType['icon'] ?? 'ri-file-list-line' }}"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="card-title mb-1">{{ $contentType['name'] }}</h6>
                                                            <p class="card-text text-muted small mb-0">
                                                                {{ $contentType['items_count'] ?? 0 }} items available
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Step 3: Content Items Selection -->
                <div class="wizard-step" id="step3-content-items" style="display: none;">
                    <h5 class="mb-3">
                        <i class="ri-file-list-line me-2"></i>Step 3: Select Content Items
                        <small class="text-muted">from <span id="step3-content-type-name"></span></small>
                    </h5>

                    <div class="row g-3" id="modalContentItemsGrid">
                        <!-- Add New Content Item Card (Always First) -->
                        <div class="col-lg-12 col-md-6 mb-3">
                            <div class="card content-item-card h-100 border-dashed border-success"
                                 style="cursor: pointer; transition: all 0.2s ease;"
                                 data-content-item-id="new">
                                <div class="card-body text-center p-3">
                                    <div class="mb-3">
                                        <i class="ri-add-circle-line display-4 text-success"></i>
                                    </div>
                                    <h6 class="card-title text-success mb-2">Create New Item</h6>
                                    <p class="card-text text-muted small">
                                        Add new content to this content type.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Content Items (PHP Loop) -->
                        @if(isset($themeWidgetsWithData) && count($themeWidgetsWithData) > 0)
                            @foreach($themeWidgetsWithData as $widget)
                                @if(isset($widget['content_types']) && count($widget['content_types']) > 0)
                                    @foreach($widget['content_types'] as $contentType)
                                        @if(isset($contentType['items']) && count($contentType['items']) > 0)
                                            @foreach($contentType['items'] as $item)
                                                <div class="col-lg-12ccol-md-12 mb-3 modal-content-item-card"
                                                     data-widget-id="{{ $widget['id'] }}"
                                                     data-content-type-id="{{ $contentType['id'] }}"
                                                     style="display: none;">
                                                    <div class="card content-item-card h-100"
                                                         style="cursor: pointer; transition: all 0.2s ease;"
                                                         data-content-item-id="{{ $item['id'] }}">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <input type="checkbox" class="form-check-input content-item-checkbox"
                                                                           id="modal-item-{{ $item['id'] }}"
                                                                           style="margin-top: 2px;">
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="card-title mb-1">{{ $item['title'] ?? 'Untitled' }}</h6>
                                                                    <p class="card-text text-muted small mb-2">
                                                                        {{ $item['excerpt'] ?? 'No description available' }}
                                                                    </p>
                                                                    <small class="text-muted">{{ $item['status'] ?? 'draft' }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <!-- Selected Items Summary -->
                    <div class="alert alert-light border mt-3" id="selectedItemsAlert" style="display: none;">
                        <div class="d-flex align-items-center">
                            <i class="ri-checkbox-circle-line text-success me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Selected Items:</strong>
                                <span id="selectedItemsCount">0</span> items selected
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div class="widget-info">
                    <small class="text-muted">
                        <i class="ri-information-line me-1"></i>
                        <span id="stepInfo">Select a widget to continue</span>
                    </small>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline-secondary me-2" id="backButton" style="display: none;">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="nextButton" disabled>
                        <i class="ri-arrow-right-line me-2"></i>Next
                    </button>
                    <button type="button" class="btn btn-primary" id="addWidgetButton" style="display: none;">
                        <i class="ri-add-line me-2"></i>Add Widget
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS for Wizard Steps -->
<style>
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-item.active .step-circle {
    background-color: #0d6efd;
    color: white;
}

.step-item.completed .step-circle {
    background-color: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
    text-align: center;
}

.step-item.active .step-label {
    color: #0d6efd;
}

.step-item.completed .step-label {
    color: #198754;
}

.step-divider {
    flex: 1;
    height: 2px;
    background-color: #e9ecef;
    margin: 0 15px;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.step-divider.completed {
    background-color: #198754;
}

.widget-card:hover {
    border-color: #0d6efd !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
}

.widget-card.selected {
    border-color: #0d6efd !important;
    border-width: 2px !important;
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
}

.content-type-card:hover,
.content-item-card:hover {
    border-color: #0d6efd !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
}

.content-type-card.selected,
.content-item-card.selected {
    border-color: #0d6efd !important;
    border-width: 2px !important;
    background-color: rgba(13, 110, 253, 0.05);
}
</style>

@push('scripts')
<script>
// Initialize modal wizard data when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Set widget data for the modal wizard functionality
    if (window.addWidgetWizard) {
        const themeWidgetsWithData = @json($themeWidgetsWithData ?? []);
        const defaultWidgets = @json($defaultWidgets ?? []);

        window.addWidgetWizard.setWidgetData(themeWidgetsWithData, defaultWidgets);
        console.log('🧩 Add Widget Wizard data initialized with preloaded content types');
    } else {
        console.log('🧩 Waiting for addWidgetWizard...');
        // Wait for external script to load
        let attempts = 0;
        const checkInterval = setInterval(() => {
            if (window.addWidgetWizard) {
                const themeWidgetsWithData = @json($themeWidgetsWithData ?? []);
                const defaultWidgets = @json($defaultWidgets ?? []);

                window.addWidgetWizard.setWidgetData(themeWidgetsWithData, defaultWidgets);
                console.log('🧩 Add Widget Wizard data initialized (delayed) with preloaded content types');
                clearInterval(checkInterval);
            } else if (++attempts >= 20) {
                console.error('❌ addWidgetWizard not found after 20 attempts');
                clearInterval(checkInterval);
            }
        }, 100);
    }
});
</script>
@endpush