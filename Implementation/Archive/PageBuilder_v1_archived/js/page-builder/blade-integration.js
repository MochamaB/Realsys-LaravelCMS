/**
 * BLADE INTEGRATION TEMPORARY FILE
 * ================================
 * 
 * This file contains JavaScript functions extracted from show.blade.php 
 * during the modal fix refactoring process. This is a temporary holding 
 * file - functions will be redistributed to proper locations.
 * 
 * EXTRACTED FUNCTIONS:
 * - initializePageBuilder() - Core initialization
 * - setupAddWidgetButtonHandlers() - Widget button event handling
 * - setupIframeMessageListener() - Cross-frame communication
 * - setupSectionTemplatesModal() - Template selection functionality
 * - initSidebarToggle() - Sidebar collapse/expand
 * - All modal management functions
 * - All event listeners and DOM handlers
 */

// Global initialization flag
window.widgetModalManagerReady = false;

/**
 * MAIN PAGE BUILDER INITIALIZATION
 * Replaces the old scattered initialization logic
 */
function initializePageBuilder() {
    console.log('🔧 Initializing Page Builder...');
    
    // Wait for all required classes to be available
    const checkClasses = () => {
        const requiredClasses = [
            'PageBuilderMain',
            'WidgetModalManager', 
            'FieldTypeDefaultsService'
        ];
        
        const missingClasses = requiredClasses.filter(className => typeof window[className] !== 'function');
        
        if (missingClasses.length === 0) {
            try {
                console.log('🚀 All required classes available, starting initialization...');
                
                // Initialize Page Builder Main Controller
                if (!window.pageBuilder) {
                    window.pageBuilder = new PageBuilderMain({
                        pageId: window.pageId,
                        apiBaseUrl: '/admin/api/page-builder',
                        csrfToken: window.csrfToken,
                        containerId: 'gridStackContainer'
                    });
                    
                    console.log('✅ PageBuilderMain instance created');
                }
                
                // Initialize the page builder
                window.pageBuilder.init().then(() => {
                    console.log('✅ Page Builder initialized successfully');
                    
                    // Initialize Field Type Defaults Service
                    if (!window.fieldTypeDefaultsService) {
                        window.fieldTypeDefaultsService = new FieldTypeDefaultsService(
                            '/admin/api/page-builder',
                            window.csrfToken
                        );
                        console.log('✅ Field Type Defaults Service initialized');
                    }
                    
                    // Initialize Widget Modal Manager - SINGLE INSTANCE ONLY
                    if (!window.widgetModalManager) {
                        window.widgetModalManager = new WidgetModalManager(
                            '/admin/api/page-builder',
                            window.csrfToken
                        );
                        window.widgetModalManagerReady = true;
                        console.log('✅ Widget Modal Manager initialized');
                    }
                    
                    // Initialize other components
                    setupIframeMessageListener();
                    setupAddWidgetButtonHandlers();
                    setupStaticSectionTemplatesModal();
                    initSidebarToggle();
                    
                    // Debug logging
                    setTimeout(() => {
                        console.log('🔍 Debug: Page Builder components:', {
                            pageBuilder: !!window.pageBuilder,
                            sectionManager: !!window.pageBuilder?.sectionManager,
                            sectionsCount: window.pageBuilder?.sectionManager?.sections?.size || 0
                        });
                    }, 2000);
                    
                    console.log('✅ Page Builder initialization complete');
                    
                }).catch(error => {
                    console.error('❌ Page Builder initialization failed:', error);
                });
                
            } catch (error) {
                console.error('❌ Page Builder initialization failed:', error);
            }
        } else {
            console.log('⏳ Waiting for classes:', missingClasses.join(', '));
            setTimeout(checkClasses, 100);
        }
    };
    
    checkClasses();
}

/**
 * WIDGET BUTTON EVENT HANDLERS
 * Handles all "Add Widget" button clicks from various sources
 */
function setupAddWidgetButtonHandlers() {
    console.log('🎯 Setting up Add Widget button handlers...');
    
    // Method 1: Direct button click handlers
    document.addEventListener('click', function(e) {
        // Handle Add Widget buttons
        if (e.target.matches('[data-action="add-widget"]') || 
            e.target.closest('[data-action="add-widget"]')) {
            
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('[data-action="add-widget"]') || e.target;
            const sectionId = button.dataset.sectionId || button.getAttribute('data-section-id');
            const sectionName = button.dataset.sectionName || button.getAttribute('data-section-name') || 'Section';
            
            console.log('🎯 Add Widget button clicked:', { sectionId, sectionName });
            
            if (sectionId) {
                // Use centralized widget modal manager
                if (window.widgetModalManager && window.widgetModalManager.openForSection) {
                    window.widgetModalManager.openForSection(sectionId, sectionName);
                } else {
                    console.error('❌ Widget Modal Manager not available');
                }
            } else {
                console.error('❌ No section ID found for Add Widget button');
            }
        }
        
        // Handle Add Section buttons - use create-on-demand pattern like section-config modal
        if (e.target.matches('#addSectionBtn, #addFirstSectionBtn') || 
            e.target.closest('#addSectionBtn, #addFirstSectionBtn')) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🏗️ Add Section button clicked - testing modal functionality');
            
            // DIAGNOSTIC: Try a simple test modal first
            createAndShowTestModal();
            return;
        }
        
        // No special handling needed for other modal triggers - let Bootstrap handle them natively
    });
    
    // Method 2: Listen for toolbar actions from iframe
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'toolbar-action') {
            const { action, elementType, elementId, elementName } = event.data.data;
            
            console.log('📨 Toolbar action received:', { action, elementType, elementId, elementName });
            
            if (action === 'add-widget' && elementType === 'section') {
                // Use centralized widget modal manager
                if (window.widgetModalManager && window.widgetModalManager.openForSection) {
                    window.widgetModalManager.openForSection(elementId, elementName);
                } else {
                    console.error('❌ Widget Modal Manager not available for toolbar action');
                }
            }
        }
    });
}

/**
 * IFRAME MESSAGE LISTENER
 * Handles communication between parent window and iframe
 */
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

function handleToolbarAction(actionData) {
    console.log('🔧 Toolbar action received:', actionData);
    
    const { action, elementType, elementId, elementName } = actionData;
    
    switch (action) {
        case 'add-widget':
            if (elementType === 'section') {
                // Use centralized widget modal manager
                if (window.widgetModalManager && window.widgetModalManager.openForSection) {
                    window.widgetModalManager.openForSection(elementId, elementName);
                } else {
                    console.error('❌ Widget Modal Manager not available for toolbar action');
                }
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

/**
 * STATIC SECTION TEMPLATES MODAL FUNCTIONALITY
 * Simple setup for static templates (no dynamic loading)
 */
function setupStaticSectionTemplatesModal() {
    console.log('🎯 Setting up static section templates modal...');
    
    // Setup modal event handlers only (templates are already in HTML)
    setupSectionTemplateModalHandlers();
}

/**
 * OPEN SECTION TEMPLATES OFFCANVAS
 * Uses Bootstrap offcanvas for better UX
 */
function openSectionTemplatesModal() {
    console.log('🏗️ Opening section templates offcanvas...');
    
    try {
        // Get the offcanvas element (note: still using same function name for compatibility)
        const offcanvas = document.getElementById('sectionTemplatesOffcanvas');
        if (!offcanvas) {
            throw new Error('Section templates offcanvas not found');
        }
        
        console.log('📋 Offcanvas element found, creating Bootstrap offcanvas instance');
        
        // Create fresh Bootstrap offcanvas instance (create-on-demand pattern)
        const bsOffcanvas = new bootstrap.Offcanvas(offcanvas, {
            backdrop: true,
            keyboard: true,
            scroll: false
        });
        
        // Show offcanvas
        bsOffcanvas.show();
        
        console.log('✅ Section templates offcanvas opened successfully');
        
        // Log offcanvas state for debugging
        setTimeout(() => {
            console.log('🔍 Offcanvas state after opening:', {
                isShown: offcanvas.classList.contains('show'),
                display: window.getComputedStyle(offcanvas).display,
                visibility: offcanvas.style.visibility
            });
        }, 500);
        
    } catch (error) {
        console.error('❌ Error opening section templates offcanvas:', error);
        alert('Failed to open section templates. Please try again.');
    }
}

// All dynamic loading functions removed - templates are now static in HTML

// Setup offcanvas event handlers
function setupSectionTemplateModalHandlers() {
    console.log('🎧 Setting up section template offcanvas handlers...');
    
    const offcanvas = document.getElementById('sectionTemplatesOffcanvas');
    const addBtn = document.getElementById('addSelectedSectionBtn');
    let selectedTemplate = null;
    
    if (!offcanvas || !addBtn) {
        console.error('❌ Offcanvas elements not found');
        return;
    }
    
    // Handle template card selection
    offcanvas.addEventListener('click', function(e) {
        const templateCard = e.target.closest('.section-template-card');
        if (!templateCard) return;
        
        // Remove previous selection
        offcanvas.querySelectorAll('.section-template-card').forEach(card => {
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
            columnLayout: templateCard.dataset.columnLayout,
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
        
        // Close offcanvas
        bootstrap.Offcanvas.getInstance(offcanvas).hide();
        
        // Reset selection
        selectedTemplate = null;
        addBtn.disabled = true;
    });
    
    // Reset selection when offcanvas is hidden
    offcanvas.addEventListener('hidden.bs.offcanvas', function() {
        selectedTemplate = null;
        addBtn.disabled = true;
        
        // Remove all selections
        offcanvas.querySelectorAll('.section-template-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            card.style.borderWidth = '';
        });
    });
}

/**
 * SIDEBAR TOGGLE FUNCTIONALITY
 */
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

/**
 * SECTION CONFIGURATION MODAL FUNCTIONS
 */
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

/**
 * SECTION CONFIG MODAL ENHANCEMENTS
 * Functions for section configuration modal with loader
 */
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

// Make functions available globally
window.initializePageBuilder = initializePageBuilder;