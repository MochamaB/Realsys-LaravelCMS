@extends('admin.layouts.designer-layout')

@section('title', 'Page Builder: ' . $page->title)

@section('css')
<!-- GridStack Designer CSS -->
<link href="{{ asset('assets/admin/libs/gridstack/dist/gridstack.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/gridstack-designer-sections.css') }}" rel="stylesheet" />

<!-- Multi-Step Widget Modal CSS -->
<link href="{{ asset('assets/admin/css/page-builder/widget-modal.css') }}" rel="stylesheet" />



<!-- Consolidated Template & Component Styling -->
<style>
/* ===== UNIFIED PROGRESS BAR LOADER STYLES ===== */
.unified-page-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: #f8f9fa;
    z-index: 9999;
    overflow: hidden;
    transition: opacity 0.3s ease;
}

.unified-page-loader .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997, #17a2b8);
    background-size: 200% 100%;
    animation: loading 2s ease-in-out infinite;
    border-radius: 0;
    transition: width 0.3s ease;
    width: 0%;
}

.unified-page-loader.error .progress-bar {
    background: linear-gradient(90deg, #dc3545, #e74c3c);
    animation: error-pulse 1s ease-in-out infinite;
}

.unified-page-loader .progress-bar.complete {
    background: #28a745;
    animation: none;
}

.unified-page-loader .loader-message {
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    background: rgba(255, 255, 255, 0.9);
    padding: 2px 8px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

@keyframes error-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

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

<!-- Unified Loader Manager -->
<script src="{{ asset('assets/admin/js/page-builder/unified-loader-manager.js') }}"></script>

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
                <!-- Unified Progress Bar Loader -->
                <div class="unified-page-loader" id="pageBuilderLoader" style="display: none;">
                    <div class="progress-bar"></div>
                    <div class="loader-message">Loading...</div>
                </div>
                
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
        
        // Call section creation function via page builder instance
        if (window.pageBuilder && typeof window.pageBuilder.createSectionFromTemplate === 'function') {
            window.pageBuilder.createSectionFromTemplate(selectedTemplate);
        } else {
            console.error('❌ Page Builder not available or createSectionFromTemplate method not found');
            alert('Page Builder is not ready. Please refresh the page and try again.');
        }
        
        // Close modal
        bootstrap.Modal.getInstance(modal).hide();
        
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
                console.warn('⚠️ SectionManager not available, using built-in modal');
                openSectionConfigModalWithLoader(sectionData);
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
            if (result.success && result.data) {
                console.log('✅ Section configuration fetched from API');
                
                // Transform API response to match SectionManager expectations
                const sectionData = {
                    id: parseInt(sectionId),
                    template_section: {
                        name: result.data.name || `Section ${sectionId}`,
                        section_type: result.data.section_type || 'content',
                        description: result.data.description || '',
                        column_layout: result.data.column_layout || '',
                        container_type: result.data.container_type || 'container'
                    },
                    config: result.data,
                    allows_widgets: result.data.allows_widgets || true,
                    widget_types: result.data.widget_types || null,
                    position: result.data.position || 0,
                    grid_x: result.data.grid_x || 0,
                    grid_y: result.data.grid_y || 0,
                    grid_w: result.data.grid_w || 12,
                    grid_h: result.data.grid_h || 4,
                    css_classes: result.data.css_classes || '',
                    background_color: result.data.background_color || '',
                    padding: result.data.padding || '',
                    margin: result.data.margin || '',
                    locked_position: result.data.locked_position || false,
                    resize_handles: result.data.resize_handles || ''
                };
                
                console.log('🔄 Transformed section data for SectionManager:', sectionData);
                return sectionData;
            } else {
                console.warn('⚠️ API returned failure:', result.message);
            }
        } else {
            console.warn('⚠️ API request failed:', response.status, response.statusText);
        }
    } catch (error) {
        console.error('❌ Error fetching section data:', error);
    }
    
    // Fallback: create a basic section object that matches SectionManager structure
    const fallbackSection = {
        id: parseInt(sectionId),
        template_section: { 
            name: `Section ${sectionId}`,
            section_type: 'content',
            description: '',
            column_layout: 'full-width',
            container_type: 'container'
        },
        config: {},
        allows_widgets: true,
        widget_types: null,
        position: 0,
        grid_x: 0,
        grid_y: 0,
        grid_w: 12,
        grid_h: 4,
        css_classes: '',
        background_color: '',
        padding: '',
        margin: '',
        locked_position: false,
        resize_handles: ''
    };
    
    console.log('🔄 Using fallback section data:', fallbackSection);
    return fallbackSection;
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

function openEnhancedFallbackModal(sectionData) {
    console.log('📋 Opening enhanced fallback modal with data:', sectionData);
    
    // Create a more detailed Bootstrap modal for section configuration
    const modalHtml = `
        <div class="modal fade" id="enhancedSectionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-settings-line me-2"></i>Configure Section: ${sectionData.template_section?.name || 'Section'}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    <strong>Section ID:</strong> ${sectionData.id}<br>
                                    <strong>Section Type:</strong> ${sectionData.template_section?.section_type || 'content'}<br>
                                    <strong>Template:</strong> ${sectionData.template_section?.name || 'Unknown'}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Section Configuration</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Configuration options will be available here.</p>
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading configuration...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing enhanced modal
    const existingModal = document.getElementById('enhancedSectionModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('enhancedSectionModal'));
    modal.show();
    
    // Clean up on hide
    document.getElementById('enhancedSectionModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    console.log('✅ Enhanced section modal opened with API data');
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

// Section Configuration Modal Enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Show loader when opening section config modal
    function showSectionConfigLoader() {
        const loader = document.getElementById('sectionConfigLoader');
        const content = document.getElementById('sectionConfigTabContent');
        if (loader && content) {
            loader.style.display = 'block';
            content.style.display = 'none';
        }
    }
    
    function hideSectionConfigLoader() {
        const loader = document.getElementById('sectionConfigLoader');
        const content = document.getElementById('sectionConfigTabContent');
        if (loader && content) {
            loader.style.display = 'none';
            content.style.display = 'block';
        }
    }
    
    // Enhanced section config modal opener with loader
    window.openSectionConfigModalWithLoader = function(sectionData) {
        // Show the modal first
        const modal = new bootstrap.Modal(document.getElementById('sectionConfigModal'));
        modal.show();
        
        // Show loader
        showSectionConfigLoader();
        
        // Simulate loading time or actual data loading
        setTimeout(() => {
            // Populate form with section data if provided
            if (sectionData) {
                populateSectionConfigForm(sectionData);
            }
            
            // Hide loader and show content
            hideSectionConfigLoader();
        }, 800);
    };
    
    // Color picker sync functionality
    function setupColorPickerSync() {
        const colorPicker = document.getElementById('backgroundColor');
        const colorText = document.getElementById('backgroundColorText');
        
        if (colorPicker && colorText) {
            // Sync color picker to text input
            colorPicker.addEventListener('input', function() {
                colorText.value = this.value;
            });
            
            // Sync text input to color picker
            colorText.addEventListener('input', function() {
                if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) {
                    colorPicker.value = this.value;
                }
            });
        }
    }
    
    // Initialize color picker sync when modal is shown
    document.getElementById('sectionConfigModal')?.addEventListener('shown.bs.modal', function() {
        setupColorPickerSync();
    });
    
    // Function to populate section config form
    window.populateSectionConfigForm = function(sectionData) {
        // Populate basic fields
        document.getElementById('sectionId').value = sectionData.id || '';
        document.getElementById('sectionPosition').value = sectionData.position || '';
        document.getElementById('allowsWidgets').checked = sectionData.allows_widgets || false;
        
        // Grid settings
        document.getElementById('gridX').value = sectionData.grid_x || 0;
        document.getElementById('gridY').value = sectionData.grid_y || 0;
        document.getElementById('gridW').value = sectionData.grid_w || 12;
        document.getElementById('gridH').value = sectionData.grid_h || 4;
        
        // Styling settings
        document.getElementById('cssClasses').value = sectionData.css_classes || '';
        document.getElementById('backgroundColor').value = sectionData.background_color || '#ffffff';
        document.getElementById('backgroundColorText').value = sectionData.background_color || '#ffffff';
        document.getElementById('padding').value = sectionData.padding || '';
        document.getElementById('margin').value = sectionData.margin || '';
        document.getElementById('columnSpanOverride').value = sectionData.column_span_override || '';
        document.getElementById('columnOffsetOverride').value = sectionData.column_offset_override || '';
        document.getElementById('resizeHandles').value = sectionData.resize_handles || 'all';
        document.getElementById('lockedPosition').checked = sectionData.locked_position || false;
        
        // Handle widget_types JSON field
        if (sectionData.widget_types) {
            document.getElementById('widgetTypesJson').value = JSON.stringify(sectionData.widget_types);
            // TODO: Update widget types UI when implemented
        }
        
        console.log('Section config form populated:', sectionData);
    };
});

</script>
@endpush