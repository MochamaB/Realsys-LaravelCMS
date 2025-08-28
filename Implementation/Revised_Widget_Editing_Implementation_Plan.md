# Revised Widget Editing Implementation Plan - Modal-Only Approach

## Overview
This revised plan focuses on modal-based editing for sections and widgets in the Page Builder, with proper GridStack integration and theme-aware styling. All editing operations will use modals and forms, reserving inline editing for the separate live preview implementation.

## Current Architecture Analysis

### Rendering System
- **Universal Components**: `universal-section.blade.php` and `universal-widget.blade.php` handle styling and attributes
- **UniversalStylingService**: Generates CSS classes, inline styles, and GridStack attributes
- **GridStack Integration**: Both sections and widgets support `grid_x`, `grid_y`, `grid_w`, `grid_h` positioning
- **Theme Integration**: Widget-specific CSS in `public/themes/{theme}/widgets/` directories
- **Section Configuration**: Background color, padding, margin stored as JSON in PageSection model

### Database Structure
```php
// PageSection columns for styling and positioning
'grid_x', 'grid_y', 'grid_w', 'grid_h', 'grid_id', 'grid_config',
'background_color', 'padding', 'margin', 'css_classes',
'column_span_override', 'column_offset_override'

// PageSectionWidget columns for positioning and styling  
'grid_x', 'grid_y', 'grid_w', 'grid_h', 'grid_id',
'settings', 'content_query', 'css_classes',
'padding', 'margin', 'min_height', 'max_height'
```

### Current Widget Toolbar
- Section toolbar exists with Add Widget, Edit, Delete buttons
- Widget selection via click with highlight effects
- Parent-iframe communication system established

## Revised Implementation Phases

## Phase 1: Section Configuration & Styling

### 1.1 Enhanced Section Configuration Modal
**Location**: Enhance existing `resources/views/admin/pages/page-builder/modals/section-config.blade.php`

```html
<!-- Enhanced Section Configuration Modal -->
<div class="modal fade" id="sectionConfigModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Section Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Panel - Configuration -->
                    <div class="col-lg-8">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sectionGeneral">General</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectionLayout">Layout</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectionStyling">Styling</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectionAdvanced">Advanced</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-3">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="sectionGeneral">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Section Name</label>
                                        <input type="text" class="form-control" id="sectionName">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Widget Types Allowed</label>
                                        <select class="form-select" id="widgetTypes" multiple>
                                            <option value="text">Text Widgets</option>
                                            <option value="image">Image Widgets</option>
                                            <option value="layout">Layout Widgets</option>
                                            <option value="form">Form Widgets</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Layout Tab -->
                            <div class="tab-pane fade" id="sectionLayout">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Grid Width</label>
                                        <input type="number" class="form-control" id="gridWidth" min="1" max="12" value="12">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Grid Height</label>
                                        <input type="number" class="form-control" id="gridHeight" min="1" value="4">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Grid X</label>
                                        <input type="number" class="form-control" id="gridX" min="0" value="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Grid Y</label>
                                        <input type="number" class="form-control" id="gridY" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="lockedPosition">
                                            <label class="form-check-label" for="lockedPosition">Lock Position</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Resize Handles</label>
                                        <div class="d-flex gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="n" id="resizeN">
                                                <label class="form-check-label" for="resizeN">N</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="s" id="resizeS">
                                                <label class="form-check-label" for="resizeS">S</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="e" id="resizeE">
                                                <label class="form-check-label" for="resizeE">E</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="w" id="resizeW">
                                                <label class="form-check-label" for="resizeW">W</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Styling Tab -->
                            <div class="tab-pane fade" id="sectionStyling">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Background Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="backgroundColor">
                                            <input type="text" class="form-control" id="backgroundColorHex" placeholder="#ffffff">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CSS Classes</label>
                                        <input type="text" class="form-control" id="cssClasses" placeholder="custom-class another-class">
                                    </div>
                                </div>
                                
                                <!-- Padding Controls -->
                                <div class="mt-3">
                                    <label class="form-label">Padding</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label small">Top</label>
                                            <input type="number" class="form-control form-control-sm" id="paddingTop" min="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Right</label>
                                            <input type="number" class="form-control form-control-sm" id="paddingRight" min="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Bottom</label>
                                            <input type="number" class="form-control form-control-sm" id="paddingBottom" min="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Left</label>
                                            <input type="number" class="form-control form-control-sm" id="paddingLeft" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Margin Controls -->
                                <div class="mt-3">
                                    <label class="form-label">Margin</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label small">Top</label>
                                            <input type="number" class="form-control form-control-sm" id="marginTop">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Right</label>
                                            <input type="number" class="form-control form-control-sm" id="marginRight">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Bottom</label>
                                            <input type="number" class="form-control form-control-sm" id="marginBottom">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Left</label>
                                            <input type="number" class="form-control form-control-sm" id="marginLeft">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Advanced Tab -->
                            <div class="tab-pane fade" id="sectionAdvanced">
                                <div class="mb-3">
                                    <label class="form-label">Custom CSS</label>
                                    <textarea class="form-control" id="customCSS" rows="6" 
                                              placeholder="/* Custom CSS for this section */"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Column Span Override</label>
                                        <select class="form-select" id="columnSpanOverride">
                                            <option value="">Default</option>
                                            <option value="1">1 Column</option>
                                            <option value="2">2 Columns</option>
                                            <option value="3">3 Columns</option>
                                            <option value="4">4 Columns</option>
                                            <option value="6">6 Columns</option>
                                            <option value="12">12 Columns</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Column Offset</label>
                                        <select class="form-select" id="columnOffsetOverride">
                                            <option value="">No Offset</option>
                                            <option value="1">Offset 1</option>
                                            <option value="2">Offset 2</option>
                                            <option value="3">Offset 3</option>
                                            <option value="4">Offset 4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Panel - Live Preview -->
                    <div class="col-lg-4">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-3">Live Preview</h6>
                            <div id="sectionPreview" style="min-height: 200px;">
                                <!-- Live preview of section styling -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteSectionBtn">Delete Section</button>
                <button type="button" class="btn btn-primary" id="saveSectionBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>
```

### 1.2 Section Configuration Manager
**Location**: New file `public/assets/admin/js/page-builder/section-config-manager.js`

```javascript
class SectionConfigManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.modal = null;
        this.currentSection = null;
        this.init();
    }
    
    init() {
        this.modal = new bootstrap.Modal(document.getElementById('sectionConfigModal'));
        this.setupEventHandlers();
    }
    
    async openSectionConfig(sectionId) {
        try {
            // Load section data
            const sectionData = await this.loadSectionData(sectionId);
            
            // Populate form
            this.populateForm(sectionData);
            
            // Setup live preview
            this.setupLivePreview(sectionData);
            
            // Show modal
            this.modal.show();
            
        } catch (error) {
            console.error('Error opening section config:', error);
            this.showError('Failed to load section configuration');
        }
    }
    
    async loadSectionData(sectionId) {
        const response = await fetch(`${this.apiBaseUrl}/sections/${sectionId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        });
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        return data.success ? data.data : null;
    }
    
    populateForm(sectionData) {
        // General
        document.getElementById('sectionName').value = sectionData.name || '';
        
        // Layout
        document.getElementById('gridWidth').value = sectionData.grid_w || 12;
        document.getElementById('gridHeight').value = sectionData.grid_h || 4;
        document.getElementById('gridX').value = sectionData.grid_x || 0;
        document.getElementById('gridY').value = sectionData.grid_y || 0;
        document.getElementById('lockedPosition').checked = sectionData.locked_position || false;
        
        // Styling
        document.getElementById('backgroundColor').value = sectionData.background_color || '#ffffff';
        document.getElementById('backgroundColorHex').value = sectionData.background_color || '#ffffff';
        document.getElementById('cssClasses').value = sectionData.css_classes || '';
        
        // Padding
        const padding = sectionData.padding || {top: 0, right: 0, bottom: 0, left: 0};
        document.getElementById('paddingTop').value = padding.top || 0;
        document.getElementById('paddingRight').value = padding.right || 0;
        document.getElementById('paddingBottom').value = padding.bottom || 0;
        document.getElementById('paddingLeft').value = padding.left || 0;
        
        // Margin
        const margin = sectionData.margin || {top: 0, right: 0, bottom: 0, left: 0};
        document.getElementById('marginTop').value = margin.top || 0;
        document.getElementById('marginRight').value = margin.right || 0;
        document.getElementById('marginBottom').value = margin.bottom || 0;
        document.getElementById('marginLeft').value = margin.left || 0;
        
        // Advanced
        document.getElementById('columnSpanOverride').value = sectionData.column_span_override || '';
        document.getElementById('columnOffsetOverride').value = sectionData.column_offset_override || '';
    }
    
    setupLivePreview(sectionData) {
        // Update preview as user changes values
        const previewInputs = [
            'backgroundColor', 'backgroundColorHex', 'cssClasses',
            'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
            'marginTop', 'marginRight', 'marginBottom', 'marginLeft'
        ];
        
        previewInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', () => this.updateLivePreview());
            }
        });
        
        // Initial preview update
        this.updateLivePreview();
    }
    
    updateLivePreview() {
        const preview = document.getElementById('sectionPreview');
        
        const styles = {
            backgroundColor: document.getElementById('backgroundColor').value,
            paddingTop: document.getElementById('paddingTop').value + 'px',
            paddingRight: document.getElementById('paddingRight').value + 'px',
            paddingBottom: document.getElementById('paddingBottom').value + 'px',
            paddingLeft: document.getElementById('paddingLeft').value + 'px',
            marginTop: document.getElementById('marginTop').value + 'px',
            marginRight: document.getElementById('marginRight').value + 'px',
            marginBottom: document.getElementById('marginBottom').value + 'px',
            marginLeft: document.getElementById('marginLeft').value + 'px'
        };
        
        Object.assign(preview.style, styles);
        
        // Update CSS classes
        const cssClasses = document.getElementById('cssClasses').value;
        preview.className = 'border rounded p-3 bg-light ' + cssClasses;
        
        preview.innerHTML = `
            <div class="text-center">
                <h6>Section Preview</h6>
                <p class="text-muted small">This shows how your section styling will appear</p>
            </div>
        `;
    }
    
    async saveSection() {
        try {
            const formData = this.gatherFormData();
            
            const response = await fetch(`${this.apiBaseUrl}/sections/${this.currentSection.id}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(formData)
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            
            if (data.success) {
                this.modal.hide();
                this.showSuccess('Section updated successfully');
                
                // Refresh preview
                if (window.pageBuilder && window.pageBuilder.refreshPagePreview) {
                    window.pageBuilder.refreshPagePreview();
                }
            } else {
                throw new Error(data.error || 'Failed to update section');
            }
            
        } catch (error) {
            console.error('Error saving section:', error);
            this.showError('Failed to save section changes');
        }
    }
    
    gatherFormData() {
        return {
            name: document.getElementById('sectionName').value,
            grid_w: parseInt(document.getElementById('gridWidth').value),
            grid_h: parseInt(document.getElementById('gridHeight').value),
            grid_x: parseInt(document.getElementById('gridX').value),
            grid_y: parseInt(document.getElementById('gridY').value),
            locked_position: document.getElementById('lockedPosition').checked,
            background_color: document.getElementById('backgroundColor').value,
            css_classes: document.getElementById('cssClasses').value,
            padding: {
                top: parseInt(document.getElementById('paddingTop').value) || 0,
                right: parseInt(document.getElementById('paddingRight').value) || 0,
                bottom: parseInt(document.getElementById('paddingBottom').value) || 0,
                left: parseInt(document.getElementById('paddingLeft').value) || 0
            },
            margin: {
                top: parseInt(document.getElementById('marginTop').value) || 0,
                right: parseInt(document.getElementById('marginRight').value) || 0,
                bottom: parseInt(document.getElementById('marginBottom').value) || 0,
                left: parseInt(document.getElementById('marginLeft').value) || 0
            },
            column_span_override: document.getElementById('columnSpanOverride').value || null,
            column_offset_override: document.getElementById('columnOffsetOverride').value || null
        };
    }
}
```

## Phase 2: Widget Configuration & Properties

### 2.1 Widget Configuration Modal
**Location**: New modal `resources/views/admin/pages/page-builder/modals/widget-config.blade.php`

```html
<!-- Widget Configuration Modal -->
<div class="modal fade" id="widgetConfigModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-settings-line me-2"></i>
                    <span id="widgetConfigTitle">Widget Configuration</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Panel - Configuration Tabs -->
                    <div class="col-lg-8">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#widgetFields">Widget Fields</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#widgetLayout">Layout</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#widgetStyling">Styling</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#widgetContent">Content</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-3">
                            <!-- Widget Fields Tab -->
                            <div class="tab-pane fade show active" id="widgetFields">
                                <div id="widgetFieldsContainer">
                                    <!-- Dynamic widget fields will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Layout Tab -->
                            <div class="tab-pane fade" id="widgetLayout">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Grid Width</label>
                                        <input type="number" class="form-control" id="widgetGridWidth" min="1" max="12">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Grid Height</label>
                                        <input type="number" class="form-control" id="widgetGridHeight" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Min Height</label>
                                        <input type="number" class="form-control" id="widgetMinHeight" placeholder="px">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Max Height</label>
                                        <input type="number" class="form-control" id="widgetMaxHeight" placeholder="px">
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="widgetLockedPosition">
                                            <label class="form-check-label">Lock Position</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="widgetNoResize">
                                            <label class="form-check-label">Disable Resize</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Styling Tab -->
                            <div class="tab-pane fade" id="widgetStyling">
                                <!-- Similar padding/margin controls as section -->
                                <div class="mb-3">
                                    <label class="form-label">CSS Classes</label>
                                    <input type="text" class="form-control" id="widgetCssClasses">
                                </div>
                                
                                <!-- Padding/Margin controls (similar to section) -->
                                <div class="mt-3">
                                    <label class="form-label">Padding</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetPaddingTop" min="0">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetPaddingRight" min="0">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetPaddingBottom" min="0">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetPaddingLeft" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Margin</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetMarginTop">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetMarginRight">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetMarginBottom">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" class="form-control" id="widgetMarginLeft">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Content Tab -->
                            <div class="tab-pane fade" id="widgetContent">
                                <div id="widgetContentContainer">
                                    <!-- Content selection and configuration -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Panel - Live Preview -->
                    <div class="col-lg-4">
                        <div class="sticky-top">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-3">Live Preview</h6>
                                <div id="widgetPreview" style="min-height: 300px;">
                                    <!-- Live preview of widget -->
                                </div>
                            </div>
                            
                            <div class="border rounded p-3 bg-light mt-3">
                                <h6 class="mb-3">Theme CSS</h6>
                                <div id="widgetThemeInfo">
                                    <!-- Theme-specific styling info -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="copyWidgetBtn">Copy Widget</button>
                <button type="button" class="btn btn-danger" id="deleteWidgetBtn">Delete Widget</button>
                <button type="button" class="btn btn-primary" id="saveWidgetBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>
```

### 2.2 Widget Configuration Manager
**Location**: New file `public/assets/admin/js/page-builder/widget-config-manager.js`

```javascript
class WidgetConfigManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.modal = null;
        this.currentWidget = null;
        this.currentTheme = null;
        this.init();
    }
    
    init() {
        this.modal = new bootstrap.Modal(document.getElementById('widgetConfigModal'));
        this.setupEventHandlers();
    }
    
    async openWidgetConfig(widgetInstanceId) {
        try {
            // Load widget instance data
            const widgetData = await this.loadWidgetInstanceData(widgetInstanceId);
            
            // Load theme information
            await this.loadThemeInfo();
            
            // Populate modal
            await this.populateConfigModal(widgetData);
            
            // Setup live preview
            this.setupLivePreview(widgetData);
            
            // Show modal
            this.modal.show();
            
        } catch (error) {
            console.error('Error opening widget config:', error);
            this.showError('Failed to load widget configuration');
        }
    }
    
    async loadWidgetInstanceData(widgetInstanceId) {
        const response = await fetch(`${this.apiBaseUrl}/widgets/instances/${widgetInstanceId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        });
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        return data.success ? data.data : null;
    }
    
    async loadThemeInfo() {
        const response = await fetch(`${this.apiBaseUrl}/theme/info`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            this.currentTheme = data.success ? data.data : null;
        }
    }
    
    async populateConfigModal(widgetData) {
        // Update modal title
        document.getElementById('widgetConfigTitle').textContent = 
            `Configure ${widgetData.widget.name}`;
        
        // Populate widget fields
        await this.populateWidgetFields(widgetData.widget_fields, widgetData.widget.field_definitions);
        
        // Populate layout settings
        this.populateLayoutSettings(widgetData);
        
        // Populate styling settings
        this.populateStylingSettings(widgetData);
        
        // Populate content settings
        this.populateContentSettings(widgetData);
        
        // Show theme information
        this.showThemeInfo(widgetData.widget);
    }
    
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
            const fieldElement = await this.createFieldElement(field, fieldValues[field.slug]);
            container.appendChild(fieldElement);
        }
    }
    
    async createFieldElement(field, currentValue) {
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
                fieldInput = `<div class="form-check">
                    <input class="form-check-input" type="checkbox" id="field_${field.slug}" 
                           ${currentValue ? 'checked' : ''}>
                    <label class="form-check-label" for="field_${field.slug}">
                        Enable ${field.name}
                    </label>
                </div>`;
                break;
                
            case 'image':
                fieldInput = await this.createImageField(field, currentValue);
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
        this.setupFieldChangeHandlers(fieldDiv, field);
        
        return fieldDiv;
    }
    
    async createRichTextEditor(field, currentValue) {
        // Create a simple rich text editor
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
    
    async createImageField(field, currentValue) {
        return `
            <div class="image-field">
                <div class="current-image mb-2">
                    ${currentValue ? `
                        <img src="${currentValue}" alt="Current image" class="img-thumbnail" style="max-height: 150px;">
                        <div class="mt-1">
                            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.image-field').querySelector('img').remove(); this.remove();">
                                Remove Image
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
    
    populateLayoutSettings(widgetData) {
        document.getElementById('widgetGridWidth').value = widgetData.grid_w || 12;
        document.getElementById('widgetGridHeight').value = widgetData.grid_h || 4;
        document.getElementById('widgetMinHeight').value = widgetData.min_height || '';
        document.getElementById('widgetMaxHeight').value = widgetData.max_height || '';
        
        const settings = widgetData.settings || {};
        document.getElementById('widgetLockedPosition').checked = settings.locked || false;
        document.getElementById('widgetNoResize').checked = settings.noResize || false;
    }
    
    populateStylingSettings(widgetData) {
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
    }
    
    populateContentSettings(widgetData) {
        const container = document.getElementById('widgetContentContainer');
        
        if (widgetData.widget.supports_content) {
            // Show content configuration UI
            container.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Content Type</label>
                    <select class="form-select" id="widgetContentType">
                        <option value="">Select content type...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content Query</label>
                    <textarea class="form-control" id="widgetContentQuery" rows="3" 
                              placeholder="JSON query configuration">${JSON.stringify(widgetData.content_query || {}, null, 2)}</textarea>
                </div>
            `;
            
            this.loadContentTypes();
        } else {
            container.innerHTML = `
                <div class="text-center text-muted p-4">
                    <i class="ri-file-text-line fs-1 mb-3"></i>
                    <p>This widget does not use dynamic content.</p>
                </div>
            `;
        }
    }
    
    showThemeInfo(widget) {
        const container = document.getElementById('widgetThemeInfo');
        
        if (this.currentTheme) {
            const themeWidgetPath = `/themes/${this.currentTheme.slug}/widgets/${widget.slug}/assets/custom.css`;
            
            container.innerHTML = `
                <div class="small">
                    <div class="mb-2">
                        <strong>Active Theme:</strong> ${this.currentTheme.name}
                    </div>
                    <div class="mb-2">
                        <strong>Widget Styles:</strong>
                        <a href="${themeWidgetPath}" target="_blank" class="d-block text-decoration-none small">
                            <i class="ri-external-link-line"></i> ${widget.slug}/custom.css
                        </a>
                    </div>
                    <div class="text-muted">
                        Theme-specific widget styling is automatically loaded.
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="text-muted small text-center">
                    Theme information not available
                </div>
            `;
        }
    }
}
```

## Phase 3: GridStack Drag-and-Drop Integration

### 3.1 Enhanced GridStack Manager
**Location**: Enhanced `public/assets/admin/js/page-builder/gridstack-widget-manager.js`

```javascript
class EnhancedGridStackManager {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.grids = new Map();
        this.isDragging = false;
        this.init();
    }
    
    init() {
        // Initialize GridStack for sections that support widgets
        this.initializeSectionGrids();
        
        // Setup drag handles in widget toolbars
        this.setupWidgetDragHandles();
        
        // Setup cross-section drag and drop
        this.setupCrossSectionDragDrop();
    }
    
    initializeSectionGrids() {
        // Find all sections that allow widgets
        const sections = document.querySelectorAll('[data-section-id]');
        
        sections.forEach(section => {
            const sectionId = section.dataset.sectionId;
            
            // Check if section allows widgets (this would come from section data)
            if (this.sectionAllowsWidgets(section)) {
                const grid = this.createGridForSection(section, sectionId);
                this.grids.set(sectionId, grid);
            }
        });
    }
    
    createGridForSection(sectionElement, sectionId) {
        // Create or find grid container within section
        let gridContainer = sectionElement.querySelector('.grid-stack');
        
        if (!gridContainer) {
            gridContainer = document.createElement('div');
            gridContainer.className = 'grid-stack';
            sectionElement.appendChild(gridContainer);
        }
        
        // Initialize GridStack with section-specific configuration
        const grid = GridStack.init({
            column: 12,
            cellHeight: 'auto',
            acceptWidgets: true,
            removable: '.trash', // Drag to trash area
            float: false,
            animate: true,
            handle: '.drag-handle', // Only drag from handle
            alwaysShowResizeHandle: false,
            resizable: {
                handles: 'se, sw, ne, nw'
            }
        }, gridContainer);
        
        // Add existing widgets to grid
        this.addExistingWidgetsToGrid(grid, sectionElement);
        
        // Setup grid event handlers
        this.setupGridEventHandlers(grid, sectionId);
        
        return grid;
    }
    
    addExistingWidgetsToGrid(grid, sectionElement) {
        const widgets = sectionElement.querySelectorAll('[data-page-section-widget-id]');
        
        widgets.forEach(widget => {
            const widgetData = this.extractWidgetGridData(widget);
            
            if (widgetData.x !== null) {
                // Widget has grid positioning
                grid.makeWidget(widget, {
                    x: widgetData.x,
                    y: widgetData.y,
                    w: widgetData.w,
                    h: widgetData.h,
                    id: widgetData.id
                });
            } else {
                // Widget doesn't have grid positioning, add with auto-position
                grid.makeWidget(widget);
            }
        });
    }
    
    extractWidgetGridData(widgetElement) {
        return {
            id: widgetElement.dataset.pageSectioinWidgetId,
            x: parseInt(widgetElement.dataset.gsX) || null,
            y: parseInt(widgetElement.dataset.gsY) || null,
            w: parseInt(widgetElement.dataset.gsW) || 12,
            h: parseInt(widgetElement.dataset.gsH) || 4
        };
    }
    
    setupGridEventHandlers(grid, sectionId) {
        // Handle position changes
        grid.on('change', (event, items) => {
            if (!this.isDragging) {
                this.handleWidgetPositionChange(items, sectionId);
            }
        });
        
        // Handle drag start
        grid.on('dragstart', (event, element) => {
            this.isDragging = true;
            this.showDragFeedback(element, true);
            this.showDropZones(element);
        });
        
        // Handle drag stop
        grid.on('dragstop', (event, element) => {
            this.isDragging = false;
            this.showDragFeedback(element, false);
            this.hideDropZones();
            
            // Force update positioning after drag
            setTimeout(() => {
                const items = grid.getGridItems().map(el => ({
                    el: el,
                    x: parseInt(el.getAttribute('gs-x')),
                    y: parseInt(el.getAttribute('gs-y')),
                    w: parseInt(el.getAttribute('gs-w')),
                    h: parseInt(el.getAttribute('gs-h'))
                }));
                this.handleWidgetPositionChange(items, sectionId);
            }, 100);
        });
        
        // Handle resize events
        grid.on('resizestart', (event, element) => {
            this.showResizeFeedback(element, true);
        });
        
        grid.on('resizestop', (event, element) => {
            this.showResizeFeedback(element, false);
            
            // Update widget dimensions in database
            const widgetId = element.dataset.pageSectioinWidgetId;
            const newDimensions = {
                w: parseInt(element.getAttribute('gs-w')),
                h: parseInt(element.getAttribute('gs-h'))
            };
            
            this.updateWidgetDimensions(widgetId, newDimensions);
        });
    }
    
    showDragFeedback(element, isDragging) {
        if (isDragging) {
            element.classList.add('gs-dragging');
            element.style.transform = 'rotate(2deg)';
            element.style.zIndex = '1000';
            element.style.opacity = '0.8';
        } else {
            element.classList.remove('gs-dragging');
            element.style.transform = '';
            element.style.zIndex = '';
            element.style.opacity = '';
        }
    }
    
    showDropZones(draggingElement) {
        const currentSection = draggingElement.closest('[data-section-id]');
        const allSections = document.querySelectorAll('[data-section-id]');
        
        allSections.forEach(section => {
            if (section !== currentSection && this.sectionAllowsWidgets(section)) {
                section.classList.add('drop-zone-available');
                
                // Create drop indicator if section is empty
                const widgets = section.querySelectorAll('[data-page-section-widget-id]');
                if (widgets.length === 0) {
                    this.createDropIndicator(section);
                }
            }
        });
    }
    
    hideDropZones() {
        document.querySelectorAll('.drop-zone-available').forEach(section => {
            section.classList.remove('drop-zone-available');
        });
        
        // Remove drop indicators
        document.querySelectorAll('.drop-indicator').forEach(indicator => {
            indicator.remove();
        });
    }
    
    createDropIndicator(section) {
        const indicator = document.createElement('div');
        indicator.className = 'drop-indicator';
        indicator.innerHTML = `
            <div class="text-center p-4 border border-dashed border-primary rounded">
                <i class="ri-drag-drop-line fs-1 text-primary mb-2"></i>
                <p class="text-primary mb-0">Drop widget here</p>
            </div>
        `;
        
        let gridContainer = section.querySelector('.grid-stack');
        if (!gridContainer) {
            gridContainer = section;
        }
        
        gridContainer.appendChild(indicator);
    }
    
    async handleWidgetPositionChange(items, sectionId) {
        try {
            const updates = items.map(item => ({
                widget_instance_id: item.el.dataset.pageSectioinWidgetId,
                x: item.x,
                y: item.y,
                w: item.w,
                h: item.h
            })).filter(update => update.widget_instance_id); // Only include valid widget IDs
            
            if (updates.length > 0) {
                await this.batchUpdatePositions(updates, sectionId);
            }
            
        } catch (error) {
            console.error('Error updating widget positions:', error);
            this.showError('Failed to save widget positions');
        }
    }
    
    async batchUpdatePositions(updates, sectionId) {
        const response = await fetch(`${this.apiBaseUrl}/sections/${sectionId}/widgets/batch-update-positions`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ updates })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Failed to update positions');
        }
        
        console.log('Widget positions updated successfully');
    }
    
    async updateWidgetDimensions(widgetId, dimensions) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/widgets/instances/${widgetId}/dimensions`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(dimensions)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            console.log('Widget dimensions updated successfully');
            
        } catch (error) {
            console.error('Error updating widget dimensions:', error);
            this.showError('Failed to save widget dimensions');
        }
    }
    
    sectionAllowsWidgets(sectionElement) {
        // Check if section allows widgets (this would be set from backend data)
        const sectionData = sectionElement.dataset.allowsWidgets;
        return sectionData === 'true' || sectionData === '1';
    }
    
    setupWidgetDragHandles() {
        // Add drag handles to existing widgets
        document.querySelectorAll('[data-page-section-widget-id]').forEach(widget => {
            this.addDragHandleToWidget(widget);
        });
        
        // Setup observer for dynamically added widgets
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const widgets = node.querySelectorAll ? 
                                      node.querySelectorAll('[data-page-section-widget-id]') : 
                                      (node.dataset?.pageSectioinWidgetId ? [node] : []);
                        
                        widgets.forEach(widget => {
                            this.addDragHandleToWidget(widget);
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    }
    
    addDragHandleToWidget(widgetElement) {
        // Check if widget already has a drag handle
        if (widgetElement.querySelector('.drag-handle')) {
            return;
        }
        
        // Create minimal drag handle button in the widget toolbar
        const existingToolbar = widgetElement.querySelector('.widget-toolbar');
        
        if (existingToolbar) {
            const dragHandle = document.createElement('button');
            dragHandle.className = 'toolbar-btn drag-handle';
            dragHandle.title = 'Drag to Move';
            dragHandle.innerHTML = '<i class="ri-drag-move-2-line"></i>';
            
            // Add to toolbar
            existingToolbar.appendChild(dragHandle);
        } else {
            // Create simple drag handle overlay
            const dragHandle = document.createElement('div');
            dragHandle.className = 'drag-handle widget-drag-handle';
            dragHandle.innerHTML = '<i class="ri-drag-move-2-line"></i>';
            dragHandle.style.cssText = `
                position: absolute;
                top: 5px;
                right: 5px;
                background: rgba(0,0,0,0.7);
                color: white;
                padding: 4px;
                border-radius: 4px;
                cursor: grab;
                opacity: 0;
                transition: opacity 0.2s;
                z-index: 10;
            `;
            
            widgetElement.style.position = 'relative';
            widgetElement.appendChild(dragHandle);
            
            // Show handle on hover
            widgetElement.addEventListener('mouseenter', () => {
                dragHandle.style.opacity = '1';
            });
            
            widgetElement.addEventListener('mouseleave', () => {
                if (!widgetElement.classList.contains('gs-dragging')) {
                    dragHandle.style.opacity = '0';
                }
            });
        }
    }
}
```

## Phase 4: Backend API Enhancements

### 4.1 Section Management API
**Location**: Add methods to `app/Http/Controllers/Api/PageBuilderController.php`

```php
/**
 * Get section configuration data
 */
public function getSectionConfig(PageSection $section): JsonResponse
{
    try {
        $section->load(['templateSection', 'pageSectionWidgets.widget']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'name' => $section->templateSection->name ?? 'Section',
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
                'resize_handles' => $section->resize_handles,
                'allows_widgets' => $section->allows_widgets,
                'widget_types' => $section->widget_types,
                'column_span_override' => $section->column_span_override,
                'column_offset_override' => $section->column_offset_override
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
public function updateSectionConfig(Request $request, PageSection $section): JsonResponse
{
    try {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'grid_x' => 'nullable|integer|min:0',
            'grid_y' => 'nullable|integer|min:0',
            'grid_w' => 'nullable|integer|min:1|max:12',
            'grid_h' => 'nullable|integer|min:1',
            'background_color' => 'nullable|string|max:7',
            'css_classes' => 'nullable|string|max:500',
            'padding' => 'nullable|array',
            'margin' => 'nullable|array',
            'locked_position' => 'nullable|boolean',
            'column_span_override' => 'nullable|integer|min:1|max:12',
            'column_offset_override' => 'nullable|integer|min:0|max:11'
        ]);
        
        // Update section
        $section->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Section configuration updated successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update section configuration: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Batch update widget positions within a section
 */
public function batchUpdateWidgetPositions(Request $request, PageSection $section): JsonResponse
{
    try {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.widget_instance_id' => 'required|integer|exists:page_section_widgets,id',
            'updates.*.x' => 'required|integer|min:0',
            'updates.*.y' => 'required|integer|min:0',
            'updates.*.w' => 'required|integer|min:1|max:12',
            'updates.*.h' => 'required|integer|min:1'
        ]);
        
        foreach ($validated['updates'] as $update) {
            PageSectionWidget::where('id', $update['widget_instance_id'])
                            ->where('page_section_id', $section->id)
                            ->update([
                                'grid_x' => $update['x'],
                                'grid_y' => $update['y'],
                                'grid_w' => $update['w'],
                                'grid_h' => $update['h']
                            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Widget positions updated successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update widget positions: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update widget instance dimensions
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

/**
 * Get current theme information
 */
public function getThemeInfo(): JsonResponse
{
    try {
        // Get active theme from a sample page
        $samplePage = \App\Models\Page::with('template.theme')->first();
        
        if ($samplePage && $samplePage->template && $samplePage->template->theme) {
            $theme = $samplePage->template->theme;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $theme->id,
                    'name' => $theme->name,
                    'slug' => $theme->slug,
                    'version' => $theme->version ?? '1.0.0',
                    'base_path' => "/themes/{$theme->slug}",
                    'widget_assets_path' => "/themes/{$theme->slug}/widgets"
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'error' => 'No active theme found'
        ], 404);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to get theme information: ' . $e->getMessage()
        ], 500);
    }
}
```

## Analysis of Existing JavaScript Architecture

### Current File Structure and Functionality

#### Core Architecture Files
- **`page-builder-main.js`**: Main orchestrator class that initializes all components in correct order
- **`page-builder-api.js`**: Centralized API layer handling all backend communication with proper error handling
- **`grid-manager.js`**: GridStack integration with event handling and position management
- **`section-manager.js`**: Section CRUD operations, positioning, and rendering in GridStack
- **`widget-manager.js`**: Widget CRUD operations, positioning, drag & drop functionality
- **`widget-library.js`**: Available widgets sidebar with drag & drop to sections
- **`theme-manager.js`**: Theme asset loading and application to canvas
- **`template-manager.js`**: Section template management and creation workflow

#### Additional Components
- **`device-preview.js`**: Responsive device preview functionality
- **`widget-modal-manager.js`**: 3-step widget creation modal (already implemented)
- **`field-type-defaults-service.js`**: Content item creation with defaults (already implemented)

### Integration Strategy - Enhance Existing Files

Instead of creating entirely new files, we'll **enhance the existing architecture** by adding configuration capabilities to the current managers. This approach maintains consistency and leverages the existing infrastructure.

## Revised Implementation Phases

### Phase 1: Enhanced Section Manager (Week 1-2)
**Enhance existing `section-manager.js`** to include configuration capabilities:

#### 1.1 Add Section Configuration Methods to SectionManager
```javascript
// Add to existing SectionManager class
async openSectionConfig(sectionId) {
    // Load section data via existing API
    // Show enhanced configuration modal
    // Handle save/update operations
}

async updateSectionConfiguration(sectionId, configData) {
    // Use existing API structure to update section
    // Refresh section rendering
    // Update GridStack positioning if changed
}
```

#### 1.2 Enhance Section Manager API Methods
**Update existing `page-builder-api.js`** to include section configuration endpoints:
```javascript
// Add to existing PageBuilderAPI class
async getSectionConfiguration(sectionId) {
    return await this.makeRequest('GET', `/sections/${sectionId}/config`);
}

async updateSectionConfiguration(sectionId, configData) {
    return await this.makeRequest('PUT', `/sections/${sectionId}/config`, configData);
}
```

### Phase 2: Enhanced Widget Manager (Week 3-4)
**Enhance existing `widget-manager.js`** to include configuration capabilities:

#### 2.1 Add Widget Configuration Methods to WidgetManager
```javascript
// Add to existing WidgetManager class
async openWidgetConfig(widgetInstanceId) {
    // Load widget instance data
    // Show widget configuration modal
    // Handle field editing and styling
}

async updateWidgetConfiguration(widgetInstanceId, configData) {
    // Update widget instance settings
    // Refresh widget rendering
    // Apply new styling/positioning
}
```

#### 2.2 Theme Integration Enhancement
**Enhance existing `theme-manager.js`** to provide widget-specific theme information:
```javascript
// Add to existing ThemeManager class
getWidgetThemeInfo(widgetSlug) {
    // Return theme-specific widget asset paths
    // Provide widget styling information
    // Theme customization options
}
```

### Phase 3: Enhanced Grid Manager (Week 5-6)
**Enhance existing `grid-manager.js`** with advanced positioning features:

#### 3.1 Add Advanced GridStack Features
```javascript
// Enhance existing GridManager class
setupAdvancedDragDrop() {
    // Cross-section widget movement
    // Visual feedback enhancements
    // Drag handles integration
}

handleWidgetPositionUpdate(widgetData) {
    // Batch position updates
    // Real-time persistence
    // Conflict resolution
}
```

#### 3.2 Widget Toolbar Integration
**Enhance existing `widget-manager.js`** to add toolbar functionality:
```javascript
// Add to existing WidgetManager class
addWidgetToolbar(widgetElement, widgetData) {
    // Create toolbar with edit/copy/delete/drag buttons
    // Integrate with existing preview helpers
    // Minimal styling changes to existing toolbar pattern
}
```

### Phase 4: Enhanced Widget Modal Manager (Week 7)
**The widget creation modal is already implemented** in `widget-modal-manager.js`. We'll enhance it for content management:

#### 4.1 Add Content Management to Existing Modal
```javascript
// Enhance existing WidgetModalManager class
async editContentItems(widgetInstanceId) {
    // Modal-based content item editing
    // Content type association management
    // Content query configuration
}
```

## File Enhancement Plan

### Files to Enhance (NOT create new)

#### 1. `section-manager.js` Enhancements
```javascript
class SectionManager {
    // ... existing methods ...
    
    // NEW: Configuration methods
    async openSectionConfig(sectionId) { /* ... */ }
    async updateSectionStyling(sectionId, styling) { /* ... */ }
    showSectionConfigModal(sectionData) { /* ... */ }
    
    // ENHANCED: Existing methods with styling support
    async updateSection(sectionId, updates) { 
        // Enhanced to handle styling and configuration
    }
}
```

#### 2. `widget-manager.js` Enhancements
```javascript
class WidgetManager {
    // ... existing methods ...
    
    // NEW: Configuration methods
    async openWidgetConfig(widgetInstanceId) { /* ... */ }
    async updateWidgetStyling(widgetInstanceId, styling) { /* ... */ }
    addWidgetToolbar(element, widgetData) { /* ... */ }
    
    // ENHANCED: Existing methods with configuration support
    async createWidget(sectionId, widgetId, options) {
        // Enhanced with default configuration
    }
}
```

#### 3. `grid-manager.js` Enhancements
```javascript
class GridManager {
    // ... existing methods ...
    
    // NEW: Advanced drag & drop
    setupCrossSectionDragDrop() { /* ... */ }
    handleBatchPositionUpdate(updates) { /* ... */ }
    addDragHandles(elements) { /* ... */ }
    
    // ENHANCED: Position change handling
    setupGridEvents() {
        // Enhanced with real-time persistence
    }
}
```

#### 4. `page-builder-api.js` Enhancements
```javascript
class PageBuilderAPI {
    // ... existing methods ...
    
    // NEW: Configuration endpoints
    async getSectionConfiguration(sectionId) { /* ... */ }
    async getWidgetConfiguration(widgetInstanceId) { /* ... */ }
    async batchUpdatePositions(updates) { /* ... */ }
    async getThemeInfo() { /* ... */ }
}
```

#### 5. `theme-manager.js` Enhancements
```javascript
class ThemeManager {
    // ... existing methods ...
    
    // NEW: Widget theme integration
    getWidgetThemeAssets(widgetSlug) { /* ... */ }
    loadWidgetSpecificCSS(widgetSlug) { /* ... */ }
    getThemeConfiguration() { /* ... */ }
}
```

### New Modal Files (Only)
These are the ONLY new files we need to create:

1. **`section-config-modal.blade.php`** - Enhanced section configuration modal
2. **`widget-config-modal.blade.php`** - Comprehensive widget configuration modal

## Implementation Benefits

### 1. **Architectural Consistency**
- Leverages existing well-structured class architecture
- Maintains established patterns and conventions
- Uses existing API infrastructure

### 2. **Minimal Disruption**
- No breaking changes to existing functionality
- Existing GridStack integration preserved
- Current drag & drop functionality enhanced, not replaced

### 3. **Code Reuse**
- Existing API methods extended, not duplicated
- Current event handling system preserved
- Theme management already established

### 4. **Maintainability**
- Single responsibility principle maintained
- Clear separation of concerns preserved
- Existing error handling and logging extended

## Implementation Timeline (Revised)

### Week 1: Section Manager Enhancement
- [ ] Add configuration methods to `SectionManager`
- [ ] Create enhanced section configuration modal
- [ ] Add section API endpoints to `PageBuilderAPI`
- [ ] Integrate with existing GridStack positioning

### Week 2: Widget Manager Enhancement  
- [ ] Add configuration methods to `WidgetManager`
- [ ] Create comprehensive widget configuration modal
- [ ] Add widget toolbar functionality
- [ ] Integrate theme-specific widget information

### Week 3: Grid Manager Enhancement
- [ ] Enhance `GridManager` with advanced drag & drop
- [ ] Add cross-section widget movement
- [ ] Implement batch position updates
- [ ] Add visual feedback enhancements

### Week 4: Integration & Testing
- [ ] Complete modal-based content management
- [ ] Test all enhanced functionality
- [ ] Performance optimization
- [ ] Documentation updates

## Key Features

### Modal-Only Editing Approach
- All editing operations use modals and forms
- No inline editing in page builder (reserved for live preview)
- Consistent UI/UX across all editing interfaces

### GridStack Integration
- Proper positioning persistence to `PageSectionWidget.grid_x/y/w/h`
- Cross-section widget movement
- Visual feedback during drag operations
- Respect widget and section constraints

### Theme Integration
- Theme-specific widget CSS automatically loaded
- Theme information displayed in widget config
- Widget assets path: `/themes/{theme}/widgets/{widget}/assets/custom.css`

### Section Configuration
- Complete styling control (padding, margin, background)
- GridStack positioning and constraints
- Widget type restrictions per section
- Live preview of styling changes

This revised plan focuses on robust modal-based editing with proper GridStack integration and maintains the existing architecture patterns while providing comprehensive configuration capabilities.