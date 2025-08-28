@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder: ' . $page->title)

@section('css')
<!-- GridStack Designer CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet" />

<!-- Multi-Step Widget Modal CSS -->
<link href="{{ asset('assets/admin/css/page-builder/widget-modal.css') }}" rel="stylesheet" />

<!-- Section Configuration Modal CSS -->
<link href="{{ asset('assets/admin/css/page-builder/section-config-modal.css') }}" rel="stylesheet" />

<!-- Consolidated Template & Component Styling -->
<style>
/* ===== CONSOLIDATED TEMPLATE ITEM STYLES ===== */
/* Overrides any conflicting styles from gridstack-designer.css */

.template-item {
    position: relative !important;
    background: #fff !important;
    border: 1px solid #e3e6f0 !important;
    border-radius: 8px !important;
    padding: 12px !important;
    margin-bottom: 8px !important;
    cursor: grab !important;
    transition: all 0.2s ease !important;
    user-select: none !important;
    text-align: left !important; /* Override center alignment from CSS file */
}

.template-item:hover {
    border-color: #5a67d8 !important;
    box-shadow: 0 2px 8px rgba(90, 103, 216, 0.15) !important;
    transform: translateY(-1px) !important;
}

.template-item:active {
    cursor: grabbing !important;
    transform: translateY(0) !important;
}

.template-item.creating {
    opacity: 0.7 !important;
    pointer-events: none !important;
}

.template-item.dragging {
    opacity: 0.5 !important;
    transform: rotate(2deg) !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Template Icon Styling */
.template-icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 28px !important;
    height: 28px !important;
    background: #f8f9fa !important;
    border-radius: 6px !important;
    color: #5a67d8 !important;
    font-size: 14px !important;
    flex-shrink: 0 !important;
}

/* Core vs Theme Template Icon Colors */
.template-item[data-template-type="core"] .template-icon {
    background: #e6fffa !important;
    color: #319795 !important;
}

.template-item[data-template-type="theme"] .template-icon {
    background: #fef5e7 !important;
    color: #d69e2e !important;
}

/* Template Name Styling */
.template-name {
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #2d3748 !important;
    line-height: 1.3 !important;
    flex-grow: 1 !important;
}

/* Drag Handle Styling */
.drag-handle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 24px !important;
    height: 24px !important;
    color: #6c757d !important;
    font-size: 14px !important;
    opacity: 0 !important;
    transition: opacity 0.2s ease !important;
    cursor: grab !important;
    flex-shrink: 0 !important;
}

.template-item:hover .drag-handle {
    opacity: 1 !important;
}

.drag-handle:hover {
    color: #5a67d8 !important;
}

.drag-handle:active {
    cursor: grabbing !important;
}

/* Loading State */
.sectionsGrid .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}

/* Component Item Alias (for backward compatibility) */
.component-item {
    /* Inherit all template-item styles */
}
</style>
@endsection

@section('js')
<!-- GridStack Libraries -->
<script src="{{ asset('assets/admin/libs/gridstack/dist/gridstack-all.js') }}"></script>
<script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>

<!-- Page Builder JS Modules -->
<script src="{{ asset('assets/admin/js/page-builder/api/page-builder-api.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/grid-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/section-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/widget-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/widget-library.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/template-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/theme-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/page-builder-main.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/admin/js/page-builder/device-preview.js') }}?v={{ time() }}"></script>

<!-- Field Type Defaults Service -->
<script src="{{ asset('assets/admin/js/page-builder/field-type-defaults-service.js') }}?v={{ time() }}"></script>

<!-- Multi-Step Widget Modal Manager -->
<script src="{{ asset('assets/admin/js/page-builder/widget-modal-manager.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<!-- Page Builder Content -->
<div class="container-fluid h-100 g-0">
    <!-- Toolbar -->
    <div class="row g-0">
        <div class="col-12 ">
            @include('admin.pages.page-builder.components.toolbar')
        </div>
    </div>
    
    <!-- Main Page Builder Interface -->
        <div class="row h-100 g-0">
        <!-- Left Sidebar -->
            <div class="col-lg-3 col-md-4 d-none d-lg-block" id="leftSidebarContainer">
                @include('admin.pages.page-builder.components.left-sidebar')
            </div>

            <!-- Main Canvas Area -->
            <div class="col" id="canvasContainer">
                @include('admin.pages.page-builder.components.canvas')
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.pages.page-builder.modals.section-config')
@include('admin.pages.page-builder.modals.widget-config')
@include('admin.pages.page-builder.modals.widget-content')
@include('admin.pages.page-builder.modals.section-templates')
@include('admin.pages.page-builder.modals.responsive-preview')
@endsection

@push('scripts')
<script>
// GridStack Page Builder - API-Driven Architecture
window.pageId = {{ $page->id }};
window.csrfToken = '{{ csrf_token() }}';

console.log('🔧 GridStack Page Builder initializing for page:', window.pageId);

document.addEventListener('DOMContentLoaded', function() {
    // Add a small delay to ensure all scripts are loaded
    setTimeout(function() {
        console.log('🚀 Starting Page Builder initialization...');
        
        // Initialize Page Builder Main Controller
        if (window.PageBuilderMain) {
            window.pageBuilder = new PageBuilderMain({
                pageId: window.pageId,
                apiBaseUrl: '/admin/api/page-builder',
                csrfToken: window.csrfToken,
                containerId: 'gridStackContainer'
            });
            
            // Initialize the page builder
            window.pageBuilder.init().then(() => {
                console.log('✅ Page Builder initialized successfully');
                
                // Initialize Field Type Defaults Service
                window.fieldTypeDefaultsService = new FieldTypeDefaultsService(
                    '/admin/api/page-builder',
                    window.csrfToken
                );
                
                // Initialize Widget Modal Manager
                window.widgetModalManager = new WidgetModalManager(
                    '/admin/api/page-builder',
                    window.csrfToken
                );
                
                // Setup iframe message listener
                setupIframeMessageListener();
                
                // Debug: Log section manager availability
                setTimeout(() => {
                    console.log('🔍 Debug: Page Builder components:', {
                        pageBuilder: !!window.pageBuilder,
                        sectionManager: !!window.pageBuilder?.sectionManager,
                        sectionsCount: window.pageBuilder?.sectionManager?.sections?.size || 0
                    });
                }, 2000);
                // Initialize sidebar toggle functionality
                initSidebarToggle();
                // Setup section templates modal
                setupSectionTemplatesModal();

            }).catch(error => {
                console.error('❌ Page Builder initialization failed:', error);
            });
        } else {
            console.error('❌ PageBuilderMain not found - check JS file loading');
        }
    }, 100); // Small delay to ensure all scripts are loaded
});

// Legacy Widget Modal Handlers - Replaced by WidgetModalManager
// These functions are kept for backward compatibility but are no longer used
// Sidebar toggle functionality
function initSidebarToggle() {
    const sidebarContainer = document.getElementById('leftSidebarContainer');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.getElementById('leftSidebar');
    
    if (!toggleBtn || !sidebarContainer) return;
    
    // Set sidebar collapsed by default on first load
    const isCollapsed = localStorage.getItem('sidebarCollapsed');
    if (isCollapsed === null) {
        // First time load - set to collapsed
        sidebarContainer.classList.add('collapsed');
        toggleBtn.querySelector('i').classList.remove('ri-arrow-left-line');
        toggleBtn.querySelector('i').classList.add('ri-arrow-right-line');
        localStorage.setItem('sidebarCollapsed', 'true');
    } else if (isCollapsed === 'true') {
        sidebarContainer.classList.add('collapsed');
        toggleBtn.querySelector('i').classList.remove('ri-arrow-left-line');
        toggleBtn.querySelector('i').classList.add('ri-arrow-right-line');
    }
    
    toggleBtn.addEventListener('click', function() {
        sidebarContainer.classList.toggle('collapsed');
        
        // Update icon
        const icon = toggleBtn.querySelector('i');
        if (sidebarContainer.classList.contains('collapsed')) {
            icon.classList.remove('ri-arrow-left-line');
            icon.classList.add('ri-arrow-right-line');
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            icon.classList.remove('ri-arrow-right-line');
            icon.classList.add('ri-arrow-left-line');
            localStorage.setItem('sidebarCollapsed', 'false');
        }
        
        // Refresh device preview after sidebar transition
        setTimeout(() => {
            if (window.devicePreview && window.devicePreview.refresh) {
                window.devicePreview.refresh();
            }
        }, 300); // Wait for CSS transition to complete
    });
}

// Setup section templates modal functionality
function setupSectionTemplatesModal() {
    console.log('🎯 Setting up section templates modal...');
    
    // Get the Add Section button from toolbar
    const addSectionBtn = document.getElementById('addSectionBtn');
    if (!addSectionBtn) {
        console.error('❌ Add Section button not found in toolbar');
        return;
    }
    
    // Override the default modal trigger to load templates dynamically
    addSectionBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('🔘 Add Section button clicked');
        
        // Load templates into modal before showing
        loadSectionTemplatesIntoModal();
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('sectionTemplatesModal'));
        modal.show();
    });
    
    // Setup modal event handlers
    setupSectionTemplateModalHandlers();
}

// Load section templates from left sidebar into modal
function loadSectionTemplatesIntoModal() {
    console.log('📋 Loading section templates into modal...');
    
    const modalGrid = document.getElementById('sectionTemplateGrid');
    const loadingState = document.getElementById('templateLoadingState');
    
    if (!modalGrid) {
        console.error('❌ Section template grid not found in modal');
        return;
    }
    
    // Show loading state
    if (loadingState) {
        loadingState.style.display = 'block';
    }
    
    // Get templates from the template manager
    if (window.pageBuilder && window.pageBuilder.templateManager) {
        const templates = window.pageBuilder.templateManager.templates;
        
        if (templates && templates.size > 0) {
            renderTemplatesInModal(templates, modalGrid, loadingState);
        } else {
            console.warn('⚠️ No templates available from template manager');
            showModalErrorState(modalGrid, loadingState);
        }
    } else {
        console.warn('⚠️ Template manager not available, using fallback templates');
        loadFallbackTemplatesInModal(modalGrid, loadingState);
    }
}

// Render templates in modal grid
function renderTemplatesInModal(templatesMap, modalGrid, loadingState) {
    console.log('🎨 Rendering templates in modal...');
    
    let templatesHtml = '<div class="row g-3">';
    
    // Collect all templates from all categories
    const allTemplates = [];
    templatesMap.forEach((categoryTemplates, category) => {
        categoryTemplates.forEach(template => {
            allTemplates.push({ ...template, category });
        });
    });
    
    // Render each template as a card
    allTemplates.forEach(template => {
        templatesHtml += createModalTemplateCard(template);
    });
    
    templatesHtml += '</div>';
    
    // Hide loading state and show templates
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    
    modalGrid.innerHTML = templatesHtml;
    
    console.log(`✅ Rendered ${allTemplates.length} templates in modal`);
}

// Create template card HTML for modal
function createModalTemplateCard(template) {
    return `
        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
            <div class="card section-template-card h-100" 
                 data-template-key="${template.key}" 
                 data-template-id="${template.id || template.key}"
                 data-section-type="${template.category}"
                 data-template-type="${template.type || 'core'}"
                 style="cursor: pointer; transition: all 0.2s ease; min-height: 200px;">
                <div class="card-body text-center d-flex flex-column justify-content-between p-3">
                    <div>
                        <i class="${template.icon || 'ri-layout-grid-line'} display-6 text-primary mb-2"></i>
                        <h6 class="card-title mb-2" style="font-size: 0.9rem;">${template.name}</h6>
                        <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.3;">${template.description || 'Section template'}</p>
                    </div>
                    <div class="template-meta small text-muted mt-auto">
                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;">${template.category}</span>
                        ${template.column_layout ? `<span class="badge bg-light text-dark ms-1" style="font-size: 0.7rem;">${template.column_layout}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Load fallback templates if template manager is not available
function loadFallbackTemplatesInModal(modalGrid, loadingState) {
    console.log('📦 Loading fallback templates in modal...');
    
    const fallbackTemplates = [
        {
            key: 'hero-banner',
            name: 'Hero Banner',
            category: 'header',
            column_layout: 'full-width',
            description: 'Full-width hero section with image and text',
            icon: 'ri-image-line'
        },
        {
            key: 'two-column',
            name: 'Two Columns',
            category: 'content',
            column_layout: '6-6',
            description: 'Two equal columns layout',
            icon: 'ri-layout-column-line'
        },
        {
            key: 'three-column',
            name: 'Three Columns',
            category: 'content',
            column_layout: '4-4-4',
            description: 'Three equal columns layout',
            icon: 'ri-layout-grid-line'
        },
        {
            key: 'full-width',
            name: 'Full Width',
            category: 'content',
            column_layout: 'full-width',
            description: 'Single full-width content area',
            icon: 'ri-layout-row-line'
        }
    ];
    
    let templatesHtml = '<div class="row g-3">';
    fallbackTemplates.forEach(template => {
        templatesHtml += createModalTemplateCard(template);
    });
    templatesHtml += '</div>';
    
    // Hide loading state and show templates
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    
    modalGrid.innerHTML = templatesHtml;
    
    console.log('✅ Fallback templates loaded in modal');
}

// Show error state in modal
function showModalErrorState(modalGrid, loadingState) {
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    
    modalGrid.innerHTML = `
        <div class="text-center py-4">
            <i class="ri-error-warning-line text-danger mb-3" style="font-size: 3rem;"></i>
            <h6 class="text-danger">Failed to Load Templates</h6>
            <p class="text-muted">Could not load section templates. Please try again.</p>
            <button class="btn btn-outline-primary" onclick="loadSectionTemplatesIntoModal()">
                <i class="ri-refresh-line me-2"></i>Retry
            </button>
        </div>
    `;
}

// Setup modal event handlers
function setupSectionTemplateModalHandlers() {
    console.log('🎧 Setting up section template modal handlers...');
    
    const modal = document.getElementById('sectionTemplatesModal');
    const addBtn = document.getElementById('addSelectedSectionBtn');
    let selectedTemplate = null;
    
    if (!modal || !addBtn) {
        console.error('❌ Modal elements not found');
        return;
    }
    
    // Handle template card selection
    modal.addEventListener('click', function(e) {
        const templateCard = e.target.closest('.section-template-card');
        if (!templateCard) return;
        
        // Remove previous selection
        modal.querySelectorAll('.section-template-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            card.style.borderWidth = '';
        });
        
        // Add selection to clicked card
        templateCard.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        templateCard.style.borderWidth = '2px';
        
        // Store selected template data
        selectedTemplate = {
            key: templateCard.dataset.templateKey,
            id: templateCard.dataset.templateId,
            sectionType: templateCard.dataset.sectionType,
            templateType: templateCard.dataset.templateType,
            name: templateCard.querySelector('.card-title').textContent
        };
        
        // Enable add button
        addBtn.disabled = false;
        
        console.log('✅ Template selected:', selectedTemplate);
    });
    
    // Handle add button click
    addBtn.addEventListener('click', function() {
        if (!selectedTemplate) {
            console.warn('⚠️ No template selected');
            return;
        }
        
        console.log('🚀 Adding section with template:', selectedTemplate);
        
        // Close modal
        bootstrap.Modal.getInstance(modal).hide();
        
        // TODO: Call page builder to create section
        // This will be implemented in the next step
        console.log('📋 Section creation will be implemented next');
        
        // Reset selection
        selectedTemplate = null;
        addBtn.disabled = true;
    });
    
    // Reset selection when modal is hidden
    modal.addEventListener('hidden.bs.modal', function() {
        selectedTemplate = null;
        addBtn.disabled = true;
        
        // Remove all selections
        modal.querySelectorAll('.section-template-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            card.style.borderWidth = '';
        });
    });
}


// Iframe Message Listener
function setupIframeMessageListener() {
    console.log('📡 Setting up iframe message listener...');
    
    window.addEventListener('message', function(event) {
        // Verify message is from our iframe
        const iframe = document.getElementById('pagePreviewIframe');
        if (!iframe || event.source !== iframe.contentWindow) {
            return;
        }
        
        console.log('📨 Received message from iframe:', event.data);
        
        const { type, data } = event.data;
        
        switch (type) {
            case 'section-selected':
                // Only store selection, don't open modal
                handleSectionSelection(data.sectionId, data.sectionName);
                break;
                
            case 'toolbar-action':
                handleToolbarAction(data);
                break;
                
            case 'widget-selected':
                console.log('🎯 Widget selected:', data);
                // Handle widget selection if needed
                break;
                
            default:
                console.log('ℹ️ Unknown message type:', type);
        }
    });
}

function handleSectionSelection(sectionId, sectionName) {
    console.log('📦 Section selected (no modal):', { sectionId, sectionName });
    
    // Store selected section info globally
    window.selectedSection = { id: sectionId, name: sectionName };
    
    // Add visual feedback or other selection handling here if needed
    console.log(`✅ Section "${sectionName}" (ID: ${sectionId}) selected`);
}

async function openSectionConfigModal(sectionId, sectionName) {
    console.log('🔧 Opening section configuration modal:', { sectionId, sectionName });
    
    // Store selected section info globally
    window.selectedSection = { id: sectionId, name: sectionName };
    
    try {
        // Fetch section data from API instead of relying on pre-loaded data
        console.log('🔍 Fetching section data from API...');
        const sectionData = await fetchSectionData(sectionId);
        
        if (sectionData) {
            console.log('✅ Section data retrieved:', sectionData);
            
            // Check if SectionManager is available
            if (window.pageBuilder && window.pageBuilder.sectionManager && 
                typeof window.pageBuilder.sectionManager.openSectionConfigModal === 'function') {
                
                try {
                    window.pageBuilder.sectionManager.openSectionConfigModal(sectionData);
                    console.log('✅ Modal opened with API data');
                } catch (error) {
                    console.error('❌ Error opening modal with SectionManager:', error);
                    openFallbackSectionModal(sectionId, sectionName);
                }
            } else {
                console.warn('⚠️ SectionManager not available, using fallback modal');
                openEnhancedFallbackModal(sectionData);
            }
        } else {
            console.error('❌ Failed to fetch section data');
            openFallbackSectionModal(sectionId, sectionName);
        }
    } catch (error) {
        console.error('❌ Error in openSectionConfigModal:', error);
        openFallbackSectionModal(sectionId, sectionName);
    }
}

async function fetchSectionData(sectionId) {
    try {
        // Use the PageBuilder API to fetch section configuration
        const response = await fetch(`/admin/api/page-builder/sections/${sectionId}/configuration`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                console.log('✅ Section configuration fetched from API');
                return result.data;
            } else {
                console.warn('⚠️ API returned failure:', result.message);
            }
        } else {
            console.warn('⚠️ API request failed:', response.status, response.statusText);
        }
    } catch (error) {
        console.error('❌ Error fetching section data:', error);
    }
    
    // Fallback: create a basic section object
    return {
        id: parseInt(sectionId),
        template_section: { 
            name: `Section ${sectionId}`,
            section_type: 'content'
        },
        config: {}
    };
}

function handleToolbarAction(actionData) {
    console.log('🔧 Toolbar action received:', actionData);
    
    const { action, elementType, elementId, elementName } = actionData;
    
    switch (action) {
        case 'add-widget':
            if (elementType === 'section') {
                openWidgetModalForSection(elementId, elementName);
            }
            break;
            
        case 'edit':
            console.log(`✏️ Edit ${elementType}: ${elementName} (ID: ${elementId})`);
            if (elementType === 'section') {
                // Open section config modal for edit action
                openSectionConfigModal(elementId, elementName);
            } else if (elementType === 'widget') {
                // Handle widget editing (for future implementation)
                console.log('🎯 Widget edit not implemented yet:', elementId);
            }
            break;
            
        case 'delete':
            console.log(`🗑️ Delete ${elementType}: ${elementName} (ID: ${elementId})`);
            // Handle delete actions
            break;
            
        default:
            console.warn('❓ Unknown toolbar action:', action);
    }
}

function openWidgetModalForSection(sectionId, sectionName) {
    console.log('🎯 Opening widget modal for section:', { sectionId, sectionName });
    
    // Use the new WidgetModalManager to open the modal
    if (window.widgetModalManager) {
        window.widgetModalManager.openForSection(sectionId, sectionName);
    } else {
        console.error('❌ WidgetModalManager not initialized');
        alert('Widget modal is not ready. Please refresh the page.');
    }
    
    console.log(`✅ Widget modal opened for section "${sectionName}"`);
}

function openFallbackSectionModal(sectionId, sectionName) {
    console.log('📋 Opening fallback section modal:', { sectionId, sectionName });
    
    // Create a simple Bootstrap modal for basic section information
    const modalHtml = `
        <div class="modal fade" id="fallbackSectionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-settings-line me-2"></i>Section: ${sectionName}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Section ID:</strong> ${sectionId}<br>
                            <strong>Section Name:</strong> ${sectionName}
                        </div>
                        <p>Section configuration modal is loading...</p>
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing fallback modal
    const existingModal = document.getElementById('fallbackSectionModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('fallbackSectionModal'));
    modal.show();
    
    // Clean up on hide
    document.getElementById('fallbackSectionModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    console.log('✅ Fallback section modal opened');
}

function getSelectedSectionInfo() {
    // Return the currently selected section from iframe communication
    return window.selectedSection || null;
}

// Legacy loadAvailableWidgets - replaced by WidgetModalManager.loadWidgetLibrary()
// function loadAvailableWidgets() { ... }

// Legacy renderWidgetLibrary - replaced by WidgetModalManager.renderWidgetLibrary()
// function renderWidgetLibrary(widgets) { ... }

// Legacy setupWidgetSelectionHandlers - replaced by WidgetModalManager handlers
// function setupWidgetSelectionHandlers() { ... }

// Legacy showWidgetDetails - replaced by WidgetModalManager.updateWidgetPreview()
// function showWidgetDetails(widgetId, widgetItem) { ... }

// Legacy setupAddWidgetHandler - replaced by WidgetModalManager.handleFinalSubmission()
// function setupAddWidgetHandler() { ... }

</script>
@endpush