# Complete Widget Editing Implementation Plan
## JavaScript Architecture Enhancement Approach

## Executive Summary

This plan details implementing comprehensive widget and section editing functionality by **enhancing existing JavaScript classes** rather than creating new ones. The current page builder architecture is well-structured with proper separation of concerns, API abstraction, and GridStack integration. We will extend this existing foundation to add modal-based configuration capabilities while maintaining architectural consistency.

## Current JavaScript Architecture Analysis

### Core Architecture Overview
The page builder uses a modular class-based architecture with clear separation of concerns:

```
PageBuilderMain (Orchestrator)
├── PageBuilderAPI (API Layer)
├── GridManager (GridStack Integration)
├── SectionManager (Section Operations)
├── WidgetManager (Widget Operations)
├── WidgetLibrary (Available Widgets Sidebar)
├── ThemeManager (Theme Asset Management)
├── TemplateManager (Section Templates)
├── DevicePreview (Responsive Preview)
├── WidgetModalManager (3-Step Widget Creation)
└── FieldTypeDefaultsService (Content Creation)
```

### Existing File Functionality

#### 1. `page-builder-main.js` - System Orchestrator
- **Purpose**: Initializes all components in correct order
- **Key Features**: 
  - Component lifecycle management
  - Global event handling
  - Loading state management
  - Error handling coordination

#### 2. `page-builder-api.js` - API Abstraction Layer  
- **Purpose**: Centralized API communication with error handling
- **Key Features**:
  - Section CRUD operations
  - Widget CRUD operations
  - Position update methods
  - Theme asset loading
  - Standardized request/response handling

#### 3. `grid-manager.js` - GridStack Integration
- **Purpose**: Manages GridStack library and drag & drop
- **Key Features**:
  - GridStack initialization and configuration
  - Position change event handling
  - Drag & drop functionality
  - Custom event dispatching for position changes

#### 4. `section-manager.js` - Section Operations
- **Purpose**: Complete section lifecycle management
- **Key Features**:
  - Section loading and rendering
  - Section creation from templates
  - Position updates via GridStack
  - Section-to-DOM mapping

#### 5. `widget-manager.js` - Widget Operations
- **Purpose**: Widget lifecycle and rendering management
- **Key Features**:
  - Widget CRUD operations
  - Section-widget association management
  - Widget rendering in sections
  - Available widgets management

#### 6. `theme-manager.js` - Theme Asset Management
- **Purpose**: Theme loading and canvas application
- **Key Features**:
  - Theme asset loading (CSS/JS)
  - Canvas styling application
  - Theme information retrieval

#### 7. `widget-modal-manager.js` - Widget Creation Modal (Already Complete)
- **Purpose**: 3-step widget creation workflow
- **Key Features**:
  - Widget selection
  - Content type selection  
  - Content item selection/creation
  - Default configuration application

## Implementation Strategy: Enhance Existing Architecture

### Core Principle: **Extend, Don't Replace**

Instead of creating new managers, we will add configuration capabilities to existing classes:

1. **SectionManager** → Add section configuration methods
2. **WidgetManager** → Add widget configuration and toolbar methods  
3. **GridManager** → Add advanced drag & drop features
4. **PageBuilderAPI** → Add configuration endpoints
5. **ThemeManager** → Add widget-specific theme information

## Phase 1: Section Configuration Enhancement

### 1.1 Enhanced Section Manager (`section-manager.js`)

Add configuration capabilities to the existing `SectionManager` class:

```javascript
class SectionManager {
    // ... existing methods preserved ...

    /**
     * NEW: Open section configuration modal
     */
    async openSectionConfig(sectionId) {
        try {
            console.log('🔧 Opening section configuration:', sectionId);
            
            // Load section configuration via existing API
            const sectionData = await this.api.getSectionConfiguration(sectionId);
            
            if (sectionData.success) {
                // Show configuration modal
                this.showSectionConfigModal(sectionData.data);
            } else {
                throw new Error('Failed to load section configuration');
            }
        } catch (error) {
            console.error('Error opening section config:', error);
            this.showError('Failed to open section configuration');
        }
    }

    /**
     * NEW: Show section configuration modal
     */
    showSectionConfigModal(sectionData) {
        // Initialize modal if not exists
        if (!this.sectionConfigModal) {
            this.sectionConfigModal = new bootstrap.Modal(document.getElementById('sectionConfigModal'));
        }

        // Store current section data
        this.currentConfigSection = sectionData;

        // Populate modal form
        this.populateSectionConfigForm(sectionData);

        // Setup live preview
        this.setupSectionConfigPreview(sectionData);

        // Show modal
        this.sectionConfigModal.show();
    }

    /**
     * NEW: Populate section configuration form
     */
    populateSectionConfigForm(sectionData) {
        // General settings
        document.getElementById('sectionName').value = sectionData.name || '';
        document.getElementById('sectionType').value = sectionData.section_type || '';

        // Layout settings
        document.getElementById('gridWidth').value = sectionData.grid_w || 12;
        document.getElementById('gridHeight').value = sectionData.grid_h || 4;
        document.getElementById('gridX').value = sectionData.grid_x || 0;
        document.getElementById('gridY').value = sectionData.grid_y || 0;
        document.getElementById('lockedPosition').checked = sectionData.locked_position || false;

        // Styling settings
        document.getElementById('backgroundColor').value = sectionData.background_color || '#ffffff';
        document.getElementById('cssClasses').value = sectionData.css_classes || '';

        // Padding settings
        const padding = sectionData.padding || {top: 0, right: 0, bottom: 0, left: 0};
        document.getElementById('paddingTop').value = padding.top || 0;
        document.getElementById('paddingRight').value = padding.right || 0;
        document.getElementById('paddingBottom').value = padding.bottom || 0;
        document.getElementById('paddingLeft').value = padding.left || 0;

        // Margin settings
        const margin = sectionData.margin || {top: 0, right: 0, bottom: 0, left: 0};
        document.getElementById('marginTop').value = margin.top || 0;
        document.getElementById('marginRight').value = margin.right || 0;
        document.getElementById('marginBottom').value = margin.bottom || 0;
        document.getElementById('marginLeft').value = margin.left || 0;
    }

    /**
     * NEW: Setup live preview for section configuration
     */
    setupSectionConfigPreview(sectionData) {
        const previewInputs = [
            'backgroundColor', 'cssClasses',
            'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
            'marginTop', 'marginRight', 'marginBottom', 'marginLeft'
        ];

        // Remove existing listeners
        previewInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.removeEventListener('input', this.updateSectionPreview);
                input.addEventListener('input', () => this.updateSectionPreview());
            }
        });

        // Initial preview
        this.updateSectionPreview();
    }

    /**
     * NEW: Update section live preview
     */
    updateSectionPreview() {
        const preview = document.getElementById('sectionPreview');
        if (!preview) return;

        const styles = {
            backgroundColor: document.getElementById('backgroundColor')?.value || '#ffffff',
            paddingTop: (document.getElementById('paddingTop')?.value || 0) + 'px',
            paddingRight: (document.getElementById('paddingRight')?.value || 0) + 'px',
            paddingBottom: (document.getElementById('paddingBottom')?.value || 0) + 'px',
            paddingLeft: (document.getElementById('paddingLeft')?.value || 0) + 'px',
            marginTop: (document.getElementById('marginTop')?.value || 0) + 'px',
            marginRight: (document.getElementById('marginRight')?.value || 0) + 'px',
            marginBottom: (document.getElementById('marginBottom')?.value || 0) + 'px',
            marginLeft: (document.getElementById('marginLeft')?.value || 0) + 'px'
        };

        Object.assign(preview.style, styles);

        const cssClasses = document.getElementById('cssClasses')?.value || '';
        preview.className = 'section-preview ' + cssClasses;

        preview.innerHTML = `
            <div class="preview-content">
                <h6>Section Preview</h6>
                <p class="text-muted small">Preview of section styling</p>
                <div class="preview-widget-placeholder">
                    <span class="text-muted">Widgets will appear here</span>
                </div>
            </div>
        `;
    }

    /**
     * NEW: Save section configuration
     */
    async saveSectionConfiguration() {
        try {
            const formData = this.gatherSectionConfigData();
            
            // Validate required fields
            if (!formData.name?.trim()) {
                throw new Error('Section name is required');
            }

            // Update via existing API
            const response = await this.api.updateSectionConfiguration(
                this.currentConfigSection.id, 
                formData
            );

            if (response.success) {
                // Hide modal
                this.sectionConfigModal.hide();
                
                // Refresh section in existing system
                await this.refreshSection(this.currentConfigSection.id);
                
                // Show success message
                this.showSuccess('Section configuration updated successfully');
                
                // Refresh page preview if available
                if (window.pageBuilder && window.pageBuilder.refreshPagePreview) {
                    window.pageBuilder.refreshPagePreview();
                }
            } else {
                throw new Error(response.error || 'Failed to update section configuration');
            }
            
        } catch (error) {
            console.error('Error saving section configuration:', error);
            this.showError('Failed to save section configuration: ' + error.message);
        }
    }

    /**
     * NEW: Gather form data for section configuration
     */
    gatherSectionConfigData() {
        return {
            name: document.getElementById('sectionName')?.value || '',
            section_type: document.getElementById('sectionType')?.value || '',
            grid_w: parseInt(document.getElementById('gridWidth')?.value) || 12,
            grid_h: parseInt(document.getElementById('gridHeight')?.value) || 4,
            grid_x: parseInt(document.getElementById('gridX')?.value) || 0,
            grid_y: parseInt(document.getElementById('gridY')?.value) || 0,
            locked_position: document.getElementById('lockedPosition')?.checked || false,
            background_color: document.getElementById('backgroundColor')?.value || '',
            css_classes: document.getElementById('cssClasses')?.value || '',
            padding: {
                top: parseInt(document.getElementById('paddingTop')?.value) || 0,
                right: parseInt(document.getElementById('paddingRight')?.value) || 0,
                bottom: parseInt(document.getElementById('paddingBottom')?.value) || 0,
                left: parseInt(document.getElementById('paddingLeft')?.value) || 0
            },
            margin: {
                top: parseInt(document.getElementById('marginTop')?.value) || 0,
                right: parseInt(document.getElementById('marginRight')?.value) || 0,
                bottom: parseInt(document.getElementById('marginBottom')?.value) || 0,
                left: parseInt(document.getElementById('marginLeft')?.value) || 0
            }
        };
    }

    /**
     * NEW: Refresh section after configuration update
     */
    async refreshSection(sectionId) {
        try {
            // Reload section data
            const section = this.sections.get(sectionId);
            if (section) {
                // Update section via existing updateSection method
                const updatedSection = await this.loadSection(sectionId);
                this.sections.set(sectionId, updatedSection);
                
                // Re-render section in grid if exists
                const sectionElement = this.sectionElements.get(sectionId);
                if (sectionElement && this.gridManager) {
                    await this.renderSectionInGrid(updatedSection, sectionElement);
                }
            }
        } catch (error) {
            console.error('Error refreshing section:', error);
        }
    }

    /**
     * ENHANCED: Initialize method to setup configuration handlers
     */
    async init() {
        // ... existing initialization code ...

        // Setup section configuration event handlers
        this.setupSectionConfigHandlers();
    }

    /**
     * NEW: Setup section configuration event handlers
     */
    setupSectionConfigHandlers() {
        // Listen for section configuration requests
        document.addEventListener('pagebuilder:open-section-config', (event) => {
            const sectionId = event.detail.sectionId;
            this.openSectionConfig(sectionId);
        });

        // Setup modal form handlers
        const saveBtn = document.getElementById('saveSectionBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveSectionConfiguration());
        }

        const deleteBtn = document.getElementById('deleteSectionBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteSectionWithConfirmation());
        }
    }

    /**
     * NEW: Delete section with confirmation
     */
    async deleteSectionWithConfirmation() {
        if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
            try {
                await this.deleteSection(this.currentConfigSection.id);
                this.sectionConfigModal.hide();
                this.showSuccess('Section deleted successfully');
            } catch (error) {
                this.showError('Failed to delete section: ' + error.message);
            }
        }
    }
}
```

### 1.2 Enhanced API Layer (`page-builder-api.js`)

Add section configuration endpoints to existing `PageBuilderAPI` class:

```javascript
class PageBuilderAPI {
    // ... existing methods preserved ...

    // =====================================================================
    // NEW: SECTION CONFIGURATION API METHODS
    // =====================================================================

    /**
     * Get section configuration data
     */
    async getSectionConfiguration(sectionId) {
        return await this.makeRequest('GET', `/sections/${sectionId}/configuration`);
    }

    /**
     * Update section configuration
     */
    async updateSectionConfiguration(sectionId, configData) {
        return await this.makeRequest('PUT', `/sections/${sectionId}/configuration`, configData);
    }

    /**
     * Get section styling options
     */
    async getSectionStylingOptions() {
        return await this.makeRequest('GET', '/sections/styling-options');
    }

    // =====================================================================
    // ENHANCED: EXISTING SECTION METHODS WITH CONFIGURATION SUPPORT
    // =====================================================================

    /**
     * ENHANCED: Update section to support configuration data
     */
    async updateSection(sectionId, sectionData) {
        // Enhanced to handle both basic updates and configuration updates
        return await this.makeRequest('PUT', `/sections/${sectionId}`, sectionData);
    }
}
```

### 1.3 Section Configuration Modal (`section-config-modal.blade.php`)

Create enhanced section configuration modal:

```html
<!-- Section Configuration Modal -->
<div class="modal fade" id="sectionConfigModal" tabindex="-1" aria-labelledby="sectionConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionConfigModalLabel">
                    <i class="ri-settings-3-line me-2"></i>
                    Section Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Panel - Configuration Forms -->
                    <div class="col-lg-8">
                        <ul class="nav nav-tabs" id="sectionConfigTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                                        data-bs-target="#general" type="button" role="tab">
                                    <i class="ri-settings-2-line me-1"></i> General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="layout-tab" data-bs-toggle="tab" 
                                        data-bs-target="#layout" type="button" role="tab">
                                    <i class="ri-layout-grid-line me-1"></i> Layout
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="styling-tab" data-bs-toggle="tab" 
                                        data-bs-target="#styling" type="button" role="tab">
                                    <i class="ri-palette-line me-1"></i> Styling
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" 
                                        data-bs-target="#advanced" type="button" role="tab">
                                    <i class="ri-code-line me-1"></i> Advanced
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-3" id="sectionConfigTabContent">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sectionName" class="form-label fw-bold">Section Name</label>
                                            <input type="text" class="form-control" id="sectionName" 
                                                   placeholder="Enter section name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sectionType" class="form-label fw-bold">Section Type</label>
                                            <select class="form-select" id="sectionType">
                                                <option value="header">Header</option>
                                                <option value="content">Content</option>
                                                <option value="sidebar">Sidebar</option>
                                                <option value="footer">Footer</option>
                                                <option value="custom">Custom</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="sectionDescription" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control" id="sectionDescription" rows="3" 
                                                      placeholder="Optional section description"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout Tab -->
                            <div class="tab-pane fade" id="layout" role="tabpanel">
                                <h6 class="mb-3">GridStack Positioning</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="gridWidth" class="form-label">Width (Columns)</label>
                                            <input type="number" class="form-control" id="gridWidth" 
                                                   min="1" max="12" value="12">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="gridHeight" class="form-label">Height (Units)</label>
                                            <input type="number" class="form-control" id="gridHeight" 
                                                   min="1" value="4">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="gridX" class="form-label">X Position</label>
                                            <input type="number" class="form-control" id="gridX" 
                                                   min="0" max="11" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="gridY" class="form-label">Y Position</label>
                                            <input type="number" class="form-control" id="gridY" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3 mt-4">Layout Options</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="lockedPosition">
                                            <label class="form-check-label" for="lockedPosition">
                                                Lock Position (Disable dragging)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="allowWidgets" checked>
                                            <label class="form-check-label" for="allowWidgets">
                                                Allow Widgets in Section
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Styling Tab -->
                            <div class="tab-pane fade" id="styling" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="backgroundColor" class="form-label fw-bold">Background Color</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" 
                                                       id="backgroundColor" value="#ffffff">
                                                <input type="text" class="form-control" id="backgroundColorHex" 
                                                       placeholder="#ffffff">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cssClasses" class="form-label fw-bold">CSS Classes</label>
                                            <input type="text" class="form-control" id="cssClasses" 
                                                   placeholder="custom-class another-class">
                                            <div class="form-text">Space-separated CSS class names</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Padding Controls -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Padding</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label small">Top</label>
                                            <input type="number" class="form-control" id="paddingTop" min="0" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Right</label>
                                            <input type="number" class="form-control" id="paddingRight" min="0" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Bottom</label>
                                            <input type="number" class="form-control" id="paddingBottom" min="0" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Left</label>
                                            <input type="number" class="form-control" id="paddingLeft" min="0" value="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Margin Controls -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Margin</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label small">Top</label>
                                            <input type="number" class="form-control" id="marginTop" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Right</label>
                                            <input type="number" class="form-control" id="marginRight" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Bottom</label>
                                            <input type="number" class="form-control" id="marginBottom" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Left</label>
                                            <input type="number" class="form-control" id="marginLeft" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Tab -->
                            <div class="tab-pane fade" id="advanced" role="tabpanel">
                                <div class="mb-3">
                                    <label for="customCSS" class="form-label fw-bold">Custom CSS</label>
                                    <textarea class="form-control" id="customCSS" rows="6" 
                                              placeholder="/* Custom CSS for this section */"></textarea>
                                    <div class="form-text">Custom CSS will be applied only to this section</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sectionId" class="form-label fw-bold">Section ID</label>
                                            <input type="text" class="form-control" id="sectionId" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gridId" class="form-label fw-bold">Grid ID</label>
                                            <input type="text" class="form-control" id="gridId" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Live Preview -->
                    <div class="col-lg-4">
                        <div class="sticky-top">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ri-eye-line me-1"></i> Live Preview
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="sectionPreview" class="section-preview border rounded p-3" 
                                         style="min-height: 200px; background: #f8f9fa;">
                                        <div class="preview-content text-center">
                                            <h6>Section Preview</h6>
                                            <p class="text-muted small">Preview of section styling</p>
                                            <div class="preview-widget-placeholder bg-light border border-dashed rounded p-3 mt-3">
                                                <span class="text-muted">Widgets will appear here</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ri-information-line me-1"></i> Section Info
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="small">
                                        <div class="mb-2">
                                            <strong>Current Widgets:</strong> 
                                            <span id="widgetCount">0</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Section Type:</strong> 
                                            <span id="currentSectionType">-</span>
                                        </div>
                                        <div class="mb-0">
                                            <strong>Grid Position:</strong> 
                                            <span id="gridPosition">0,0 (12x4)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="deleteSectionBtn">
                    <i class="ri-delete-bin-line me-1"></i> Delete Section
                </button>
                <button type="button" class="btn btn-primary" id="saveSectionBtn">
                    <i class="ri-save-line me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
```

## Phase 2: Widget Configuration Enhancement

### 2.1 Enhanced Widget Manager (`widget-manager.js`)

Add configuration and toolbar capabilities to existing `WidgetManager` class:

```javascript
class WidgetManager {
    // ... existing methods preserved ...

    /**
     * NEW: Open widget configuration modal
     */
    async openWidgetConfig(widgetInstanceId) {
        try {
            console.log('🔧 Opening widget configuration:', widgetInstanceId);
            
            // Load widget instance data via existing API
            const widgetData = await this.api.getWidgetConfiguration(widgetInstanceId);
            
            if (widgetData.success) {
                // Show configuration modal
                this.showWidgetConfigModal(widgetData.data);
            } else {
                throw new Error('Failed to load widget configuration');
            }
        } catch (error) {
            console.error('Error opening widget config:', error);
            this.showError('Failed to open widget configuration');
        }
    }

    /**
     * NEW: Show widget configuration modal
     */
    showWidgetConfigModal(widgetData) {
        // Initialize modal if not exists
        if (!this.widgetConfigModal) {
            this.widgetConfigModal = new bootstrap.Modal(document.getElementById('widgetConfigModal'));
        }

        // Store current widget data
        this.currentConfigWidget = widgetData;

        // Update modal title
        document.getElementById('widgetConfigTitle').textContent = `Configure ${widgetData.widget.name}`;

        // Populate modal form
        this.populateWidgetConfigForm(widgetData);

        // Setup live preview
        this.setupWidgetConfigPreview(widgetData);

        // Show modal
        this.widgetConfigModal.show();
    }

    /**
     * NEW: Populate widget configuration form
     */
    async populateWidgetConfigForm(widgetData) {
        // Populate widget fields
        await this.populateWidgetFields(widgetData.field_values, widgetData.widget.field_definitions);

        // Layout settings
        document.getElementById('widgetGridWidth').value = widgetData.grid_w || 12;
        document.getElementById('widgetGridHeight').value = widgetData.grid_h || 4;
        document.getElementById('widgetMinHeight').value = widgetData.min_height || '';
        document.getElementById('widgetMaxHeight').value = widgetData.max_height || '';

        // Styling settings
        document.getElementById('widgetCssClasses').value = widgetData.css_classes || '';

        // Padding
        const padding = widgetData.padding || {top: 0, right: 0, bottom: 0, left: 0};
        document.getElementById('widgetPaddingTop').value = padding.top || 0;
        document.getElementById('widgetPaddingRight').value = padding.right || 0;
        document.getElementById('widgetPaddingBottom').value = padding.bottom || 0;
        document.getElementById('widgetPaddingLeft').value = padding.left || 0;

        // Margin
        const margin = widgetData.margin || {top: 0, right: 0, bottom: 0, left: 0};
        document.getElementById('widgetMarginTop').value = margin.top || 0;
        document.getElementById('widgetMarginRight').value = margin.right || 0;
        document.getElementById('widgetMarginBottom').value = margin.bottom || 0;
        document.getElementById('widgetMarginLeft').value = margin.left || 0;

        // Content settings if applicable
        if (widgetData.widget.supports_content) {
            this.populateWidgetContentSettings(widgetData);
        }

        // Show theme information
        this.showWidgetThemeInfo(widgetData.widget);
    }

    /**
     * NEW: Populate widget fields dynamically
     */
    async populateWidgetFields(fieldValues, fieldDefinitions) {
        const container = document.getElementById('widgetFieldsContainer');
        container.innerHTML = '';

        if (!fieldDefinitions || fieldDefinitions.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted p-4">
                    <i class="ri-settings-3-line fs-1 mb-3"></i>
                    <p>This widget has no configurable fields.</p>
                </div>
            `;
            return;
        }

        for (const field of fieldDefinitions) {
            const fieldElement = await this.createFieldEditor(field, fieldValues[field.slug]);
            container.appendChild(fieldElement);
        }
    }

    /**
     * NEW: Create field editor based on field type
     */
    async createFieldEditor(field, currentValue) {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'mb-3';
        fieldDiv.setAttribute('data-field-slug', field.slug);

        let fieldInput = '';
        const isRequired = field.is_required;
        const requiredText = isRequired ? '<span class="text-danger">*</span>' : '';

        switch (field.field_type) {
            case 'text':
                fieldInput = `<input type="text" class="form-control" id="field_${field.slug}" 
                            value="${currentValue || ''}" ${isRequired ? 'required' : ''}>`;
                break;

            case 'textarea':
                fieldInput = `<textarea class="form-control" id="field_${field.slug}" rows="3" 
                            ${isRequired ? 'required' : ''}>${currentValue || ''}</textarea>`;
                break;

            case 'rich_text':
                fieldInput = await this.createRichTextEditor(field, currentValue);
                break;

            case 'number':
                const min = field.settings?.min || '';
                const max = field.settings?.max || '';
                fieldInput = `<input type="number" class="form-control" id="field_${field.slug}" 
                            value="${currentValue || ''}" ${min ? `min="${min}"` : ''} 
                            ${max ? `max="${max}"` : ''} ${isRequired ? 'required' : ''}>`;
                break;

            case 'select':
                const options = field.settings?.options || [];
                const optionsHtml = options.map(option => 
                    `<option value="${option.value}" ${currentValue === option.value ? 'selected' : ''}>${option.label}</option>`
                ).join('');
                fieldInput = `<select class="form-select" id="field_${field.slug}" ${isRequired ? 'required' : ''}>
                    <option value="">Choose...</option>
                    ${optionsHtml}
                </select>`;
                break;

            case 'boolean':
                fieldInput = `<div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="field_${field.slug}" 
                           ${currentValue ? 'checked' : ''}>
                    <label class="form-check-label" for="field_${field.slug}">
                        Enable ${field.name}
                    </label>
                </div>`;
                break;

            case 'image':
                fieldInput = await this.createImageEditor(field, currentValue);
                break;

            default:
                fieldInput = `<input type="text" class="form-control" id="field_${field.slug}" 
                            value="${currentValue || ''}" ${isRequired ? 'required' : ''}>`;
                break;
        }

        fieldDiv.innerHTML = `
            <label class="form-label fw-bold" for="field_${field.slug}">
                ${field.name} ${requiredText}
            </label>
            ${fieldInput}
            ${field.description ? `<div class="form-text">${field.description}</div>` : ''}
        `;

        // Setup field change handlers for live preview
        this.setupFieldChangeHandler(fieldDiv, field);

        return fieldDiv;
    }

    /**
     * NEW: Create rich text editor
     */
    async createRichTextEditor(field, currentValue) {
        return `
            <div class="rich-text-editor border rounded" style="min-height: 120px;">
                <div class="editor-toolbar border-bottom p-2 bg-light">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" data-command="bold" title="Bold">
                            <i class="ri-bold"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-command="italic" title="Italic">
                            <i class="ri-italic"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-command="underline" title="Underline">
                            <i class="ri-underline"></i>
                        </button>
                    </div>
                    <div class="btn-group btn-group-sm ms-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" data-command="insertUnorderedList" title="Bullet List">
                            <i class="ri-list-unordered"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-command="insertOrderedList" title="Numbered List">
                            <i class="ri-list-ordered"></i>
                        </button>
                    </div>
                </div>
                <div class="editor-content p-3" contenteditable="true" data-field-slug="${field.slug}" 
                     style="min-height: 80px; outline: none;">${currentValue || ''}</div>
            </div>
        `;
    }

    /**
     * NEW: Create image editor
     */
    async createImageEditor(field, currentValue) {
        return `
            <div class="image-field">
                <div class="current-image mb-2">
                    ${currentValue ? `
                        <img src="${currentValue}" alt="Current image" class="img-thumbnail" style="max-height: 150px;">
                        <div class="mt-1">
                            <button type="button" class="btn btn-sm btn-danger remove-image-btn">
                                <i class="ri-delete-bin-line"></i> Remove Image
                            </button>
                        </div>
                    ` : `
                        <div class="no-image text-center p-4 border border-dashed rounded">
                            <i class="ri-image-line fs-1 text-muted"></i>
                            <p class="text-muted mb-0">No image selected</p>
                        </div>
                    `}
                </div>
                <input type="file" class="form-control" id="field_${field.slug}" accept="image/*">
            </div>
        `;
    }

    /**
     * NEW: Add widget toolbar to widget element
     */
    addWidgetToolbar(widgetElement, widgetData) {
        // Check if toolbar already exists
        if (widgetElement.querySelector('.widget-toolbar')) {
            return;
        }

        // Create toolbar container
        const toolbar = document.createElement('div');
        toolbar.className = 'widget-toolbar';
        toolbar.setAttribute('data-widget-instance', widgetData.id);
        
        toolbar.innerHTML = `
            <div class="toolbar-group">
                <button class="toolbar-btn edit-widget" title="Edit Widget" data-action="edit">
                    <i class="ri-edit-line"></i>
                </button>
                <button class="toolbar-btn copy-widget" title="Copy Widget" data-action="copy">
                    <i class="ri-file-copy-line"></i>
                </button>
                <button class="toolbar-btn delete-widget" title="Delete Widget" data-action="delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="toolbar-group">
                <button class="toolbar-btn move-widget drag-handle" title="Drag to Move" data-action="drag">
                    <i class="ri-drag-move-2-line"></i>
                </button>
            </div>
        `;

        // Style toolbar
        toolbar.style.cssText = `
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 4px;
            display: flex;
            gap: 4px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        `;

        // Make widget container relative
        widgetElement.style.position = 'relative';

        // Add toolbar to widget
        widgetElement.appendChild(toolbar);

        // Setup toolbar event handlers
        this.setupWidgetToolbarHandlers(toolbar, widgetData);

        // Show toolbar on widget hover
        widgetElement.addEventListener('mouseenter', () => {
            toolbar.style.opacity = '1';
            toolbar.style.visibility = 'visible';
        });

        widgetElement.addEventListener('mouseleave', () => {
            if (!widgetElement.classList.contains('widget-selected')) {
                toolbar.style.opacity = '0';
                toolbar.style.visibility = 'hidden';
            }
        });
    }

    /**
     * NEW: Setup widget toolbar event handlers
     */
    setupWidgetToolbarHandlers(toolbar, widgetData) {
        toolbar.addEventListener('click', async (e) => {
            e.stopPropagation();
            
            const button = e.target.closest('.toolbar-btn');
            if (!button) return;

            const action = button.dataset.action;
            
            switch (action) {
                case 'edit':
                    await this.openWidgetConfig(widgetData.id);
                    break;
                    
                case 'copy':
                    await this.copyWidget(widgetData.id);
                    break;
                    
                case 'delete':
                    await this.deleteWidgetWithConfirmation(widgetData.id);
                    break;
                    
                case 'drag':
                    // Drag functionality handled by GridManager
                    break;
            }
        });
    }

    /**
     * NEW: Copy widget functionality
     */
    async copyWidget(widgetInstanceId) {
        try {
            const response = await this.api.copyWidget(widgetInstanceId);
            
            if (response.success) {
                // Refresh the section to show copied widget
                const widget = this.widgets.get(widgetInstanceId);
                if (widget && widget.section_id) {
                    await this.loadSectionWidgets(widget.section_id);
                }
                
                this.showSuccess('Widget copied successfully');
            } else {
                throw new Error(response.error || 'Failed to copy widget');
            }
        } catch (error) {
            console.error('Error copying widget:', error);
            this.showError('Failed to copy widget: ' + error.message);
        }
    }

    /**
     * NEW: Delete widget with confirmation
     */
    async deleteWidgetWithConfirmation(widgetInstanceId) {
        if (confirm('Are you sure you want to delete this widget? This action cannot be undone.')) {
            try {
                await this.deleteWidget(widgetInstanceId);
                this.showSuccess('Widget deleted successfully');
            } catch (error) {
                this.showError('Failed to delete widget: ' + error.message);
            }
        }
    }

    /**
     * NEW: Save widget configuration
     */
    async saveWidgetConfiguration() {
        try {
            const formData = this.gatherWidgetConfigData();
            
            const response = await this.api.updateWidgetConfiguration(
                this.currentConfigWidget.id,
                formData
            );

            if (response.success) {
                // Hide modal
                this.widgetConfigModal.hide();
                
                // Refresh widget in existing system
                await this.refreshWidget(this.currentConfigWidget.id);
                
                // Show success message
                this.showSuccess('Widget configuration updated successfully');
                
                // Refresh page preview if available
                if (window.pageBuilder && window.pageBuilder.refreshPagePreview) {
                    window.pageBuilder.refreshPagePreview();
                }
            } else {
                throw new Error(response.error || 'Failed to update widget configuration');
            }
            
        } catch (error) {
            console.error('Error saving widget configuration:', error);
            this.showError('Failed to save widget configuration: ' + error.message);
        }
    }

    /**
     * NEW: Gather widget configuration form data
     */
    gatherWidgetConfigData() {
        // Gather widget field values
        const fieldValues = {};
        const fieldElements = document.querySelectorAll('[data-field-slug]');
        
        fieldElements.forEach(element => {
            const fieldSlug = element.dataset.fieldSlug;
            let value;
            
            if (element.type === 'checkbox') {
                value = element.checked;
            } else if (element.contentEditable === 'true') {
                value = element.innerHTML;
            } else {
                value = element.value;
            }
            
            fieldValues[fieldSlug] = value;
        });

        return {
            field_values: fieldValues,
            grid_w: parseInt(document.getElementById('widgetGridWidth')?.value) || 12,
            grid_h: parseInt(document.getElementById('widgetGridHeight')?.value) || 4,
            min_height: document.getElementById('widgetMinHeight')?.value || null,
            max_height: document.getElementById('widgetMaxHeight')?.value || null,
            css_classes: document.getElementById('widgetCssClasses')?.value || '',
            padding: {
                top: parseInt(document.getElementById('widgetPaddingTop')?.value) || 0,
                right: parseInt(document.getElementById('widgetPaddingRight')?.value) || 0,
                bottom: parseInt(document.getElementById('widgetPaddingBottom')?.value) || 0,
                left: parseInt(document.getElementById('widgetPaddingLeft')?.value) || 0
            },
            margin: {
                top: parseInt(document.getElementById('widgetMarginTop')?.value) || 0,
                right: parseInt(document.getElementById('widgetMarginRight')?.value) || 0,
                bottom: parseInt(document.getElementById('widgetMarginBottom')?.value) || 0,
                left: parseInt(document.getElementById('widgetMarginLeft')?.value) || 0
            }
        };
    }

    /**
     * ENHANCED: Render widget method to include toolbar
     */
    async renderWidget(widget, container) {
        // ... existing render logic preserved ...

        // Add widget toolbar after rendering
        this.addWidgetToolbar(widgetElement, widget);

        // ... rest of existing render logic ...
    }

    /**
     * ENHANCED: Initialize method to setup configuration handlers  
     */
    async init() {
        // ... existing initialization code ...

        // Setup widget configuration event handlers
        this.setupWidgetConfigHandlers();
    }

    /**
     * NEW: Setup widget configuration event handlers
     */
    setupWidgetConfigHandlers() {
        // Listen for widget configuration requests
        document.addEventListener('pagebuilder:open-widget-config', (event) => {
            const widgetInstanceId = event.detail.widgetInstanceId;
            this.openWidgetConfig(widgetInstanceId);
        });

        // Setup modal form handlers
        const saveBtn = document.getElementById('saveWidgetBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveWidgetConfiguration());
        }

        const copyBtn = document.getElementById('copyWidgetBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => this.copyWidget(this.currentConfigWidget.id));
        }

        const deleteBtn = document.getElementById('deleteWidgetBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteWidgetWithConfirmation(this.currentConfigWidget.id));
        }
    }
}
```

### 2.2 Enhanced API Layer for Widgets

Add widget configuration endpoints to existing `PageBuilderAPI` class:

```javascript
class PageBuilderAPI {
    // ... existing methods preserved ...

    // =====================================================================
    // NEW: WIDGET CONFIGURATION API METHODS
    // =====================================================================

    /**
     * Get widget instance configuration data
     */
    async getWidgetConfiguration(widgetInstanceId) {
        return await this.makeRequest('GET', `/widgets/instances/${widgetInstanceId}/configuration`);
    }

    /**
     * Update widget instance configuration
     */
    async updateWidgetConfiguration(widgetInstanceId, configData) {
        return await this.makeRequest('PUT', `/widgets/instances/${widgetInstanceId}/configuration`, configData);
    }

    /**
     * Copy widget instance
     */
    async copyWidget(widgetInstanceId) {
        return await this.makeRequest('POST', `/widgets/instances/${widgetInstanceId}/copy`);
    }

    /**
     * Get widget field definitions
     */
    async getWidgetFieldDefinitions(widgetId) {
        return await this.makeRequest('GET', `/widgets/${widgetId}/field-definitions`);
    }

    /**
     * Update widget field values
     */
    async updateWidgetFieldValues(widgetInstanceId, fieldValues) {
        return await this.makeRequest('PUT', `/widgets/instances/${widgetInstanceId}/field-values`, { field_values: fieldValues });
    }
}
```

### 2.3 Widget Configuration Modal (`widget-config-modal.blade.php`)

Create comprehensive widget configuration modal:

```html
<!-- Widget Configuration Modal -->
<div class="modal fade" id="widgetConfigModal" tabindex="-1" aria-labelledby="widgetConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="widgetConfigModalLabel">
                    <i class="ri-settings-line me-2"></i>
                    <span id="widgetConfigTitle">Widget Configuration</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Panel - Configuration Tabs -->
                    <div class="col-lg-8">
                        <ul class="nav nav-tabs" id="widgetConfigTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fields-tab" data-bs-toggle="tab" 
                                        data-bs-target="#widgetFields" type="button" role="tab">
                                    <i class="ri-input-field me-1"></i> Widget Fields
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="layout-tab" data-bs-toggle="tab" 
                                        data-bs-target="#widgetLayout" type="button" role="tab">
                                    <i class="ri-layout-line me-1"></i> Layout
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="styling-tab" data-bs-toggle="tab" 
                                        data-bs-target="#widgetStyling" type="button" role="tab">
                                    <i class="ri-palette-line me-1"></i> Styling
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="content-tab" data-bs-toggle="tab" 
                                        data-bs-target="#widgetContent" type="button" role="tab">
                                    <i class="ri-file-text-line me-1"></i> Content
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-3" id="widgetConfigTabContent">
                            <!-- Widget Fields Tab -->
                            <div class="tab-pane fade show active" id="widgetFields" role="tabpanel">
                                <div id="widgetFieldsContainer">
                                    <!-- Dynamic widget fields will be populated here -->
                                </div>
                            </div>

                            <!-- Layout Tab -->
                            <div class="tab-pane fade" id="widgetLayout" role="tabpanel">
                                <h6 class="mb-3">Grid Dimensions</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="widgetGridWidth" class="form-label">Width (Columns)</label>
                                            <input type="number" class="form-control" id="widgetGridWidth" 
                                                   min="1" max="12" value="12">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="widgetGridHeight" class="form-label">Height (Units)</label>
                                            <input type="number" class="form-control" id="widgetGridHeight" 
                                                   min="1" value="4">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="widgetMinHeight" class="form-label">Min Height (px)</label>
                                            <input type="number" class="form-control" id="widgetMinHeight" 
                                                   placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="widgetMaxHeight" class="form-label">Max Height (px)</label>
                                            <input type="number" class="form-control" id="widgetMaxHeight" 
                                                   placeholder="None">
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3 mt-4">Layout Options</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="widgetLockedPosition">
                                            <label class="form-check-label" for="widgetLockedPosition">
                                                Lock Position
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="widgetNoResize">
                                            <label class="form-check-label" for="widgetNoResize">
                                                Disable Resize
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Styling Tab -->
                            <div class="tab-pane fade" id="widgetStyling" role="tabpanel">
                                <div class="mb-3">
                                    <label for="widgetCssClasses" class="form-label fw-bold">CSS Classes</label>
                                    <input type="text" class="form-control" id="widgetCssClasses" 
                                           placeholder="custom-class another-class">
                                    <div class="form-text">Space-separated CSS class names</div>
                                </div>

                                <!-- Padding Controls -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Padding</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label small">Top</label>
                                            <input type="number" class="form-control" id="widgetPaddingTop" min="0" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Right</label>
                                            <input type="number" class="form-control" id="widgetPaddingRight" min="0" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Bottom</label>
                                            <input type="number" class="form-control" id="widgetPaddingBottom" min="0" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Left</label>
                                            <input type="number" class="form-control" id="widgetPaddingLeft" min="0" value="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Margin Controls -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Margin</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label small">Top</label>
                                            <input type="number" class="form-control" id="widgetMarginTop" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Right</label>
                                            <input type="number" class="form-control" id="widgetMarginRight" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Bottom</label>
                                            <input type="number" class="form-control" id="widgetMarginBottom" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Left</label>
                                            <input type="number" class="form-control" id="widgetMarginLeft" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Tab -->
                            <div class="tab-pane fade" id="widgetContent" role="tabpanel">
                                <div id="widgetContentContainer">
                                    <!-- Content configuration will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Live Preview & Theme Info -->
                    <div class="col-lg-4">
                        <div class="sticky-top">
                            <!-- Live Preview -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ri-eye-line me-1"></i> Live Preview
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="widgetPreview" class="widget-preview border rounded p-3" 
                                         style="min-height: 200px; background: #f8f9fa;">
                                        <div class="text-center text-muted">
                                            <i class="ri-loader-4-line spinner-border spinner-border-sm"></i>
                                            <p class="mt-2 mb-0">Loading preview...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Theme Information -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ri-palette-line me-1"></i> Theme Info
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="widgetThemeInfo" class="small">
                                        <!-- Theme information will be populated here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Widget Information -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ri-information-line me-1"></i> Widget Info
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="small">
                                        <div class="mb-2">
                                            <strong>Widget Type:</strong> 
                                            <span id="widgetTypeName">-</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Section:</strong> 
                                            <span id="widgetSectionName">-</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Grid Size:</strong> 
                                            <span id="widgetGridSize">12x4</span>
                                        </div>
                                        <div class="mb-0">
                                            <strong>Content Type:</strong> 
                                            <span id="widgetContentType">None</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="copyWidgetBtn">
                    <i class="ri-file-copy-line me-1"></i> Copy Widget
                </button>
                <button type="button" class="btn btn-danger" id="deleteWidgetBtn">
                    <i class="ri-delete-bin-line me-1"></i> Delete Widget
                </button>
                <button type="button" class="btn btn-primary" id="saveWidgetBtn">
                    <i class="ri-save-line me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
```

## Phase 3: Enhanced Grid Manager

### 3.1 Enhanced Grid Manager (`grid-manager.js`)

Add advanced drag & drop capabilities to existing `GridManager` class:

```javascript
class GridManager {
    // ... existing methods preserved ...

    /**
     * ENHANCED: Initialize method with advanced drag & drop
     */
    async initialize(options = {}) {
        // ... existing initialization code preserved ...

        // Setup advanced drag & drop features
        this.setupAdvancedDragDrop();
        
        // Setup cross-section widget movement
        this.setupCrossSectionDragDrop();
        
        // Setup batch position updates
        this.setupBatchPositionUpdates();
    }

    /**
     * NEW: Setup advanced drag & drop features
     */
    setupAdvancedDragDrop() {
        if (!this.grid) return;

        // Enhanced drag start handler
        this.grid.on('dragstart', (event, element) => {
            console.log('🎯 Advanced drag start:', element);
            
            this.isDragging = true;
            this.draggedElement = element;
            
            // Add visual feedback
            this.showDragFeedback(element, true);
            
            // Show drop zones for cross-section movement
            this.showCrossSectionDropZones(element);
            
            // Emit custom event
            document.dispatchEvent(new CustomEvent('pagebuilder:drag-start', {
                detail: { element, type: this.getDragElementType(element) }
            }));
        });

        // Enhanced drag stop handler
        this.grid.on('dragstop', (event, element) => {
            console.log('🎯 Advanced drag stop:', element);
            
            this.isDragging = false;
            
            // Remove visual feedback
            this.showDragFeedback(element, false);
            
            // Hide drop zones
            this.hideCrossSectionDropZones();
            
            // Process position update
            setTimeout(() => {
                this.processDraggedElementPosition(element);
            }, 50);
            
            // Emit custom event
            document.dispatchEvent(new CustomEvent('pagebuilder:drag-stop', {
                detail: { element, type: this.getDragElementType(element) }
            }));
            
            this.draggedElement = null;
        });

        // Enhanced resize handlers
        this.grid.on('resizestart', (event, element) => {
            this.showResizeFeedback(element, true);
        });

        this.grid.on('resizestop', (event, element) => {
            this.showResizeFeedback(element, false);
            this.processResizedElement(element);
        });
    }

    /**
     * NEW: Setup cross-section drag & drop
     */
    setupCrossSectionDragDrop() {
        // Find all potential drop zones (other sections)
        const allSections = document.querySelectorAll('[data-section-id]');
        
        allSections.forEach(section => {
            this.setupSectionAsDropZone(section);
        });
    }

    /**
     * NEW: Setup section as drop zone
     */
    setupSectionAsDropZone(sectionElement) {
        const sectionId = sectionElement.dataset.sectionId;
        
        // Create drop zone overlay
        const dropZone = document.createElement('div');
        dropZone.className = 'cross-section-drop-zone';
        dropZone.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(13, 110, 253, 0.1);
            border: 2px dashed #0d6efd;
            border-radius: 8px;
            display: none;
            z-index: 999;
            pointer-events: none;
        `;
        
        dropZone.innerHTML = `
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                       text-align: center; pointer-events: none;">
                <i class="ri-drag-drop-line fs-1 text-primary"></i>
                <p class="text-primary mb-0 fw-bold">Drop Here</p>
            </div>
        `;

        // Add to section (make section relative if needed)
        if (getComputedStyle(sectionElement).position === 'static') {
            sectionElement.style.position = 'relative';
        }
        sectionElement.appendChild(dropZone);

        // Setup drop handling
        this.setupDropHandling(sectionElement, dropZone, sectionId);
    }

    /**
     * NEW: Setup drop handling for section
     */
    setupDropHandling(sectionElement, dropZone, sectionId) {
        let dragEnterCounter = 0;

        sectionElement.addEventListener('dragenter', (e) => {
            if (this.isDragging && this.draggedElement) {
                dragEnterCounter++;
                dropZone.style.display = 'block';
                e.preventDefault();
            }
        });

        sectionElement.addEventListener('dragleave', (e) => {
            if (this.isDragging) {
                dragEnterCounter--;
                if (dragEnterCounter === 0) {
                    dropZone.style.display = 'none';
                }
            }
        });

        sectionElement.addEventListener('dragover', (e) => {
            if (this.isDragging) {
                e.preventDefault();
            }
        });

        sectionElement.addEventListener('drop', (e) => {
            if (this.isDragging && this.draggedElement) {
                e.preventDefault();
                dragEnterCounter = 0;
                dropZone.style.display = 'none';
                
                this.handleCrossSectionDrop(this.draggedElement, sectionId);
            }
        });
    }

    /**
     * NEW: Handle cross-section widget drop
     */
    async handleCrossSectionDrop(element, targetSectionId) {
        try {
            const elementType = this.getDragElementType(element);
            const elementId = this.getElementId(element);

            if (elementType === 'widget') {
                await this.moveWidgetToSection(elementId, targetSectionId);
            } else if (elementType === 'section') {
                // Handle section reordering if needed
                console.log('Section reordering not implemented yet');
            }
        } catch (error) {
            console.error('Error handling cross-section drop:', error);
            this.showError('Failed to move element to new section');
        }
    }

    /**
     * NEW: Move widget to different section
     */
    async moveWidgetToSection(widgetInstanceId, targetSectionId) {
        try {
            // Call API to move widget
            const response = await this.api.moveWidgetToSection(widgetInstanceId, targetSectionId);
            
            if (response.success) {
                // Refresh both sections
                await this.refreshSectionWidgets(targetSectionId);
                
                // Find and refresh source section
                const sourceSection = this.findWidgetSourceSection(widgetInstanceId);
                if (sourceSection) {
                    await this.refreshSectionWidgets(sourceSection);
                }
                
                this.showSuccess('Widget moved successfully');
            } else {
                throw new Error(response.error || 'Failed to move widget');
            }
        } catch (error) {
            console.error('Error moving widget:', error);
            this.showError('Failed to move widget: ' + error.message);
        }
    }

    /**
     * NEW: Setup batch position updates
     */
    setupBatchPositionUpdates() {
        this.positionUpdateQueue = [];
        this.positionUpdateTimeout = null;
        
        // Debounced batch processing
        this.processBatchPositionUpdates = this.debounce(() => {
            if (this.positionUpdateQueue.length > 0) {
                this.executeBatchPositionUpdate([...this.positionUpdateQueue]);
                this.positionUpdateQueue = [];
            }
        }, 500);
    }

    /**
     * NEW: Add position update to queue
     */
    queuePositionUpdate(elementId, elementType, position) {
        // Remove existing update for same element
        this.positionUpdateQueue = this.positionUpdateQueue.filter(
            update => !(update.elementId === elementId && update.elementType === elementType)
        );
        
        // Add new update
        this.positionUpdateQueue.push({
            elementId,
            elementType,
            position
        });
        
        // Process batch
        this.processBatchPositionUpdates();
    }

    /**
     * NEW: Execute batch position update
     */
    async executeBatchPositionUpdate(updates) {
        try {
            const response = await this.api.batchUpdatePositions(updates);
            
            if (response.success) {
                console.log(`✅ Batch updated ${updates.length} positions`);
            } else {
                throw new Error(response.error || 'Batch position update failed');
            }
        } catch (error) {
            console.error('Error in batch position update:', error);
            // Don't show error to user for position updates - they're automatic
        }
    }

    /**
     * NEW: Show drag visual feedback
     */
    showDragFeedback(element, isDragging) {
        if (isDragging) {
            element.style.transform = 'rotate(2deg) scale(1.02)';
            element.style.boxShadow = '0 8px 25px rgba(0,0,0,0.3)';
            element.style.zIndex = '1000';
            element.style.opacity = '0.9';
            element.classList.add('dragging-element');
        } else {
            element.style.transform = '';
            element.style.boxShadow = '';
            element.style.zIndex = '';
            element.style.opacity = '';
            element.classList.remove('dragging-element');
        }
    }

    /**
     * NEW: Show resize visual feedback
     */
    showResizeFeedback(element, isResizing) {
        if (isResizing) {
            element.classList.add('resizing-element');
            element.style.outline = '2px solid #0d6efd';
        } else {
            element.classList.remove('resizing-element');
            element.style.outline = '';
        }
    }

    /**
     * NEW: Show cross-section drop zones
     */
    showCrossSectionDropZones(draggedElement) {
        const currentSection = draggedElement.closest('[data-section-id]');
        const allDropZones = document.querySelectorAll('.cross-section-drop-zone');
        
        allDropZones.forEach(dropZone => {
            const section = dropZone.closest('[data-section-id]');
            
            // Show drop zones for other sections
            if (section && section !== currentSection) {
                section.classList.add('drop-zone-available');
            }
        });
    }

    /**
     * NEW: Hide cross-section drop zones
     */
    hideCrossSectionDropZones() {
        const allDropZones = document.querySelectorAll('.cross-section-drop-zone');
        const availableZones = document.querySelectorAll('.drop-zone-available');
        
        allDropZones.forEach(dropZone => {
            dropZone.style.display = 'none';
        });
        
        availableZones.forEach(zone => {
            zone.classList.remove('drop-zone-available');
        });
    }

    /**
     * NEW: Process dragged element position
     */
    processDraggedElementPosition(element) {
        const elementType = this.getDragElementType(element);
        const elementId = this.getElementId(element);
        
        if (elementId) {
            const position = {
                x: parseInt(element.getAttribute('gs-x')) || 0,
                y: parseInt(element.getAttribute('gs-y')) || 0,
                w: parseInt(element.getAttribute('gs-w')) || 12,
                h: parseInt(element.getAttribute('gs-h')) || 4
            };
            
            // Queue for batch update
            this.queuePositionUpdate(elementId, elementType, position);
        }
    }

    /**
     * NEW: Process resized element
     */
    processResizedElement(element) {
        const elementType = this.getDragElementType(element);
        const elementId = this.getElementId(element);
        
        if (elementId) {
            const dimensions = {
                w: parseInt(element.getAttribute('gs-w')) || 12,
                h: parseInt(element.getAttribute('gs-h')) || 4
            };
            
            // Update dimensions immediately (separate from position updates)
            this.updateElementDimensions(elementId, elementType, dimensions);
        }
    }

    /**
     * NEW: Update element dimensions
     */
    async updateElementDimensions(elementId, elementType, dimensions) {
        try {
            let response;
            
            if (elementType === 'widget') {
                response = await this.api.updateWidgetDimensions(elementId, dimensions);
            } else if (elementType === 'section') {
                response = await this.api.updateSectionDimensions(elementId, dimensions);
            }
            
            if (response && response.success) {
                console.log(`✅ Updated ${elementType} dimensions:`, dimensions);
            }
        } catch (error) {
            console.error(`Error updating ${elementType} dimensions:`, error);
        }
    }

    /**
     * NEW: Get drag element type (widget or section)
     */
    getDragElementType(element) {
        if (element.dataset.pageSectioinWidgetId) {
            return 'widget';
        } else if (element.dataset.sectionId) {
            return 'section';
        }
        return 'unknown';
    }

    /**
     * NEW: Get element ID
     */
    getElementId(element) {
        return element.dataset.pageSectioinWidgetId || element.dataset.sectionId || null;
    }

    /**
     * NEW: Debounce utility
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * ENHANCED: Setup grid events to include advanced features
     */
    setupGridEvents() {
        // ... existing event setup code preserved ...
        
        // Add enhanced change handler
        this.grid.on('change', (event, items) => {
            console.log('📍 GridStack change event (enhanced):', items);
            
            // Process each changed item
            items.forEach(item => {
                const element = item.el;
                const elementType = this.getDragElementType(element);
                const elementId = this.getElementId(element);
                
                if (elementId) {
                    // Emit position change event
                    document.dispatchEvent(new CustomEvent('pagebuilder:element-position-changed', {
                        detail: {
                            elementId,
                            elementType,
                            position: { x: item.x, y: item.y, w: item.w, h: item.h }
                        }
                    }));
                    
                    // Queue for batch update if not currently dragging
                    if (!this.isDragging) {
                        this.queuePositionUpdate(elementId, elementType, {
                            x: item.x, y: item.y, w: item.w, h: item.h
                        });
                    }
                }
            });
        });
    }
}
```

### 3.2 Enhanced API Layer for GridStack

Add GridStack-specific endpoints to existing `PageBuilderAPI` class:

```javascript
class PageBuilderAPI {
    // ... existing methods preserved ...

    // =====================================================================
    // NEW: GRIDSTACK POSITIONING API METHODS
    // =====================================================================

    /**
     * Batch update multiple element positions
     */
    async batchUpdatePositions(updates) {
        return await this.makeRequest('POST', '/gridstack/batch-update-positions', { updates });
    }

    /**
     * Move widget to different section
     */
    async moveWidgetToSection(widgetInstanceId, targetSectionId) {
        return await this.makeRequest('POST', `/widgets/instances/${widgetInstanceId}/move-to-section`, {
            target_section_id: targetSectionId
        });
    }

    /**
     * Update widget dimensions
     */
    async updateWidgetDimensions(widgetInstanceId, dimensions) {
        return await this.makeRequest('PUT', `/widgets/instances/${widgetInstanceId}/dimensions`, dimensions);
    }

    /**
     * Update section dimensions
     */
    async updateSectionDimensions(sectionId, dimensions) {
        return await this.makeRequest('PUT', `/sections/${sectionId}/dimensions`, dimensions);
    }

    /**
     * Get GridStack configuration for section
     */
    async getGridStackConfig(sectionId) {
        return await this.makeRequest('GET', `/sections/${sectionId}/gridstack-config`);
    }
}
```

## Phase 4: Backend API Implementation

### 4.1 Section Configuration API Endpoints

Add to existing `PageBuilderController.php`:

```php
/**
 * Get section configuration data
 */
public function getSectionConfiguration(PageSection $section): JsonResponse
{
    try {
        $section->load(['templateSection', 'pageSectionWidgets.widget']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'name' => $section->templateSection->name ?? 'Section',
                'section_type' => $section->templateSection->section_type ?? 'content',
                'grid_x' => $section->grid_x,
                'grid_y' => $section->grid_y,
                'grid_w' => $section->grid_w,
                'grid_h' => $section->grid_h,
                'grid_id' => $section->grid_id,
                'background_color' => $section->background_color,
                'css_classes' => $section->css_classes,
                'padding' => $section->padding,
                'margin' => $section->margin,
                'locked_position' => $section->locked_position,
                'allows_widgets' => $section->allows_widgets,
                'widget_types' => $section->widget_types,
                'widget_count' => $section->pageSectionWidgets->count()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to load section configuration: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update section configuration
 */
public function updateSectionConfiguration(Request $request, PageSection $section): JsonResponse
{
    try {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'section_type' => 'nullable|string|max:50',
            'grid_x' => 'nullable|integer|min:0',
            'grid_y' => 'nullable|integer|min:0',
            'grid_w' => 'nullable|integer|min:1|max:12',
            'grid_h' => 'nullable|integer|min:1',
            'background_color' => 'nullable|string|max:7',
            'css_classes' => 'nullable|string|max:500',
            'padding' => 'nullable|array',
            'padding.top' => 'nullable|integer|min:0',
            'padding.right' => 'nullable|integer|min:0',
            'padding.bottom' => 'nullable|integer|min:0',
            'padding.left' => 'nullable|integer|min:0',
            'margin' => 'nullable|array',
            'margin.top' => 'nullable|integer',
            'margin.right' => 'nullable|integer',
            'margin.bottom' => 'nullable|integer',
            'margin.left' => 'nullable|integer',
            'locked_position' => 'nullable|boolean'
        ]);
        
        // Update section
        $section->update(array_filter($validated));
        
        return response()->json([
            'success' => true,
            'message' => 'Section configuration updated successfully',
            'data' => $section->fresh()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update section configuration: ' . $e->getMessage()
        ], 500);
    }
}
```

### 4.2 Widget Configuration API Endpoints

```php
/**
 * Get widget instance configuration data
 */
public function getWidgetConfiguration(PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        $widgetInstance->load([
            'widget.fieldDefinitions' => function($query) {
                $query->orderBy('position')->orderBy('id');
            },
            'pageSection'
        ]);
        
        // Get field values from settings
        $fieldValues = [];
        $settings = $widgetInstance->settings ?? [];
        
        if (isset($settings['field_values'])) {
            $fieldValues = $settings['field_values'];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $widgetInstance->id,
                'widget' => [
                    'id' => $widgetInstance->widget->id,
                    'name' => $widgetInstance->widget->name,
                    'slug' => $widgetInstance->widget->slug,
                    'description' => $widgetInstance->widget->description,
                    'supports_content' => $widgetInstance->widget->supports_content ?? false,
                    'field_definitions' => $widgetInstance->widget->fieldDefinitions
                ],
                'field_values' => $fieldValues,
                'grid_x' => $widgetInstance->grid_x,
                'grid_y' => $widgetInstance->grid_y,
                'grid_w' => $widgetInstance->grid_w,
                'grid_h' => $widgetInstance->grid_h,
                'css_classes' => $widgetInstance->css_classes,
                'padding' => $widgetInstance->padding,
                'margin' => $widgetInstance->margin,
                'min_height' => $widgetInstance->min_height,
                'max_height' => $widgetInstance->max_height,
                'settings' => $widgetInstance->settings,
                'content_query' => $widgetInstance->content_query,
                'section_name' => $widgetInstance->pageSection->templateSection->name ?? 'Unknown Section'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to load widget configuration: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update widget instance configuration
 */
public function updateWidgetConfiguration(Request $request, PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        $validated = $request->validate([
            'field_values' => 'nullable|array',
            'grid_w' => 'nullable|integer|min:1|max:12',
            'grid_h' => 'nullable|integer|min:1',
            'css_classes' => 'nullable|string|max:500',
            'padding' => 'nullable|array',
            'margin' => 'nullable|array',
            'min_height' => 'nullable|integer|min:0',
            'max_height' => 'nullable|integer|min:0'
        ]);
        
        // Update basic properties
        $updateData = array_filter($validated, function($key) {
            return !in_array($key, ['field_values']);
        }, ARRAY_FILTER_USE_KEY);
        
        // Handle field values in settings
        if (isset($validated['field_values'])) {
            $settings = $widgetInstance->settings ?? [];
            $settings['field_values'] = $validated['field_values'];
            $updateData['settings'] = $settings;
        }
        
        $widgetInstance->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Widget configuration updated successfully',
            'data' => $widgetInstance->fresh()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update widget configuration: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Copy widget instance
 */
public function copyWidget(PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        // Create copy of widget instance
        $copyData = $widgetInstance->toArray();
        unset($copyData['id'], $copyData['created_at'], $copyData['updated_at']);
        
        // Adjust position to avoid overlap
        $copyData['grid_x'] = min(11, ($copyData['grid_x'] ?? 0) + 1);
        $copyData['grid_y'] = ($copyData['grid_y'] ?? 0) + 1;
        
        // Generate new grid ID
        $copyData['grid_id'] = 'widget_copy_' . time() . '_' . uniqid();
        
        $newWidget = PageSectionWidget::create($copyData);
        
        return response()->json([
            'success' => true,
            'message' => 'Widget copied successfully',
            'data' => $newWidget->load(['widget', 'pageSection'])
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to copy widget: ' . $e->getMessage()
        ], 500);
    }
}
```

### 4.3 GridStack Positioning API Endpoints

```php
/**
 * Batch update element positions
 */
public function batchUpdatePositions(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.elementId' => 'required|integer',
            'updates.*.elementType' => 'required|string|in:widget,section',
            'updates.*.position' => 'required|array',
            'updates.*.position.x' => 'required|integer|min:0',
            'updates.*.position.y' => 'required|integer|min:0',
            'updates.*.position.w' => 'required|integer|min:1|max:12',
            'updates.*.position.h' => 'required|integer|min:1'
        ]);
        
        foreach ($validated['updates'] as $update) {
            $elementId = $update['elementId'];
            $elementType = $update['elementType'];
            $position = $update['position'];
            
            if ($elementType === 'widget') {
                PageSectionWidget::where('id', $elementId)->update([
                    'grid_x' => $position['x'],
                    'grid_y' => $position['y'],
                    'grid_w' => $position['w'],
                    'grid_h' => $position['h']
                ]);
            } elseif ($elementType === 'section') {
                PageSection::where('id', $elementId)->update([
                    'grid_x' => $position['x'],
                    'grid_y' => $position['y'],
                    'grid_w' => $position['w'],
                    'grid_h' => $position['h']
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Positions updated successfully',
            'updated_count' => count($validated['updates'])
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update positions: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Move widget to different section
 */
public function moveWidgetToSection(Request $request, PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        $validated = $request->validate([
            'target_section_id' => 'required|integer|exists:page_sections,id'
        ]);
        
        $targetSectionId = $validated['target_section_id'];
        
        // Update widget's section
        $widgetInstance->update([
            'page_section_id' => $targetSectionId,
            'grid_x' => 0, // Reset position in new section
            'grid_y' => 0
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Widget moved to new section successfully',
            'data' => $widgetInstance->fresh(['widget', 'pageSection'])
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to move widget: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update widget dimensions
 */
public function updateWidgetDimensions(Request $request, PageSectionWidget $widgetInstance): JsonResponse
{
    try {
        $validated = $request->validate([
            'w' => 'required|integer|min:1|max:12',
            'h' => 'required|integer|min:1'
        ]);
        
        $widgetInstance->update([
            'grid_w' => $validated['w'],
            'grid_h' => $validated['h']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Widget dimensions updated successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update widget dimensions: ' . $e->getMessage()
        ], 500);
    }
}
```

### 4.4 API Routes Addition

Add to existing `routes/admin.php`:

```php
// Section Configuration Routes
Route::get('/sections/{section}/configuration', [App\Http\Controllers\Api\PageBuilderController::class, 'getSectionConfiguration']);
Route::put('/sections/{section}/configuration', [App\Http\Controllers\Api\PageBuilderController::class, 'updateSectionConfiguration']);

// Widget Configuration Routes
Route::get('/widgets/instances/{instance}/configuration', [App\Http\Controllers\Api\PageBuilderController::class, 'getWidgetConfiguration']);
Route::put('/widgets/instances/{instance}/configuration', [App\Http\Controllers\Api\PageBuilderController::class, 'updateWidgetConfiguration']);
Route::post('/widgets/instances/{instance}/copy', [App\Http\Controllers\Api\PageBuilderController::class, 'copyWidget']);

// GridStack Positioning Routes
Route::post('/gridstack/batch-update-positions', [App\Http\Controllers\Api\PageBuilderController::class, 'batchUpdatePositions']);
Route::post('/widgets/instances/{instance}/move-to-section', [App\Http\Controllers\Api\PageBuilderController::class, 'moveWidgetToSection']);
Route::put('/widgets/instances/{instance}/dimensions', [App\Http\Controllers\Api\PageBuilderController::class, 'updateWidgetDimensions']);
Route::put('/sections/{section}/dimensions', [App\Http\Controllers\Api\PageBuilderController::class, 'updateSectionDimensions']);
```

## Implementation Timeline

### Week 1: Section Configuration Enhancement
- [ ] **Day 1-2**: Add section configuration methods to `SectionManager` class
- [ ] **Day 3**: Create section configuration modal template
- [ ] **Day 4**: Add section configuration API endpoints to `PageBuilderAPI` class
- [ ] **Day 5**: Implement backend section configuration endpoints

### Week 2: Widget Configuration Enhancement
- [ ] **Day 1-2**: Add widget configuration methods to `WidgetManager` class
- [ ] **Day 3**: Create widget configuration modal template with field editors
- [ ] **Day 4**: Add widget toolbar functionality to existing widgets
- [ ] **Day 5**: Implement backend widget configuration endpoints

### Week 3: GridStack Enhancement
- [ ] **Day 1-2**: Enhance `GridManager` with advanced drag & drop features
- [ ] **Day 3**: Implement cross-section widget movement
- [ ] **Day 4**: Add batch position updates and visual feedback
- [ ] **Day 5**: Implement backend GridStack positioning endpoints

### Week 4: Integration, Testing & Polish
- [ ] **Day 1**: Integration testing of all enhanced components
- [ ] **Day 2**: Cross-browser compatibility testing
- [ ] **Day 3**: Performance optimization and error handling
- [ ] **Day 4**: User experience improvements and polish
- [ ] **Day 5**: Documentation and deployment preparation

## Key Implementation Benefits

### 1. **Architectural Consistency**
- All enhancements build upon existing class structure
- Maintains established patterns and conventions
- Leverages existing API infrastructure and error handling

### 2. **Minimal Code Duplication**
- Extends existing methods rather than creating new ones
- Reuses existing GridStack integration
- Builds on established theme management system

### 3. **Backward Compatibility**
- All existing functionality preserved
- No breaking changes to current implementations
- Progressive enhancement approach

### 4. **Performance Optimization**
- Batch position updates reduce API calls
- Debounced operations prevent excessive requests
- Efficient DOM manipulation using existing patterns

### 5. **User Experience**
- Modal-only editing approach (no inline editing)
- Comprehensive configuration options
- Visual feedback during drag operations
- Cross-section widget movement capabilities

This implementation plan provides a complete, well-structured approach to adding comprehensive editing capabilities while maintaining the high quality and consistency of the existing codebase.