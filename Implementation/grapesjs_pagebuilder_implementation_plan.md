# GrapesJS Page Builder Implementation Plan
## Complete Visual Page Builder Integration

### =¨ Executive Summary

This document outlines the comprehensive plan to complete the GrapesJS visual page builder implementation, building on the universal styling system defined in the Visual Page Builder Plan. This plan focuses on integrating GridStack's existing widget library into GrapesJS, implementing missing editing capabilities, and creating a seamless visual editing experience.

---

## =Ê Current State Analysis

###  What's Working
- **GrapesJS Core**: Basic GrapesJS initialization and canvas
- **CSS Positioning Fix**: Sections now render correctly in live preview
- **GridStack Foundation**: Complete widget library and drag-drop system
- **Database Integration**: Full data models with styling fields
- **API Endpoints**: Basic save/load functionality exists

### L What's Missing
- **Widget Library Integration**: GridStack widgets not available in GrapesJS
- **Component System**: GrapesJS components for sections and widgets
- **Visual Editing**: No direct manipulation of widget properties
- **Style Panel Integration**: Database styling fields not editable
- **Two-Way Sync**: Changes in GrapesJS not reflected in GridStack
- **Template Integration**: Section templates not available in GrapesJS

---

## <¯ Implementation Strategy

### Phase 1: GrapesJS Component System (Week 1)
**Priority: CRITICAL**

#### 1.1 Create Section Components for GrapesJS
**Files to Create/Modify:**
- `public/assets/admin/js/grapejs/components/section-components.js` (NEW)
- `public/assets/admin/js/grapejs/grapejs-designer.js` (MODIFY)

**Implementation:**

**1.1.1 Section Component Definitions**
```javascript
// public/assets/admin/js/grapejs/components/section-components.js

export default function registerSectionComponents(editor) {
    const domComponents = editor.DomComponents;
    
    // Base Section Component
    domComponents.addType('cms-section', {
        model: {
            defaults: {
                tagName: 'section',
                classes: ['cms-section'],
                attributes: {
                    'data-section-type': 'default'
                },
                traits: [
                    {
                        type: 'text',
                        name: 'data-section-id',
                        label: 'Section ID'
                    },
                    {
                        type: 'select',
                        name: 'data-section-type',
                        label: 'Section Type',
                        options: [
                            { value: 'full-width', name: 'Full Width' },
                            { value: 'multi-column', name: 'Multi Column' },
                            { value: 'sidebar-left', name: 'Sidebar Left' },
                            { value: 'sidebar-right', name: 'Sidebar Right' }
                        ]
                    },
                    {
                        type: 'text',
                        name: 'css_classes',
                        label: 'CSS Classes'
                    },
                    {
                        type: 'color',
                        name: 'background_color',
                        label: 'Background Color'
                    }
                ],
                // GridStack position data
                grid_x: 0,
                grid_y: 0,
                grid_w: 12,
                grid_h: 4
            },
            
            init() {
                this.on('change:attributes change:classes', this.handleStyleChange);
                this.setupGridStackIntegration();
            },
            
            handleStyleChange() {
                const sectionId = this.get('attributes')['data-section-id'];
                if (sectionId) {
                    this.updateDatabaseStyling(sectionId);
                }
            },
            
            setupGridStackIntegration() {
                // Sync with GridStack positions
                this.on('change:grid_x change:grid_y change:grid_w change:grid_h', () => {
                    this.syncWithGridStack();
                });
            },
            
            updateDatabaseStyling(sectionId) {
                const stylingData = {
                    css_classes: this.getClasses().join(' '),
                    background_color: this.getStyle()['background-color'],
                    grid_x: this.get('grid_x'),
                    grid_y: this.get('grid_y'),
                    grid_w: this.get('grid_w'),
                    grid_h: this.get('grid_h')
                };
                
                // API call to update section styling
                fetch(`/admin/api/sections/${sectionId}/styling`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify(stylingData)
                });
            }
        },
        
        view: {
            onRender() {
                this.el.classList.add('gjs-section-component');
                this.setupDropZone();
            },
            
            setupDropZone() {
                // Allow widgets to be dropped into sections
                this.el.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    this.el.classList.add('drop-zone-active');
                });
                
                this.el.addEventListener('dragleave', () => {
                    this.el.classList.remove('drop-zone-active');
                });
            }
        }
    });
    
    // Full Width Section Component
    domComponents.addType('full-width-section', {
        extend: 'cms-section',
        model: {
            defaults: {
                attributes: {
                    'data-section-type': 'full-width'
                },
                classes: ['cms-section', 'full-width-section']
            }
        }
    });
    
    // Multi Column Section Component
    domComponents.addType('multi-column-section', {
        extend: 'cms-section',
        model: {
            defaults: {
                attributes: {
                    'data-section-type': 'multi-column'
                },
                classes: ['cms-section', 'multi-column-section'],
                components: [
                    {
                        tagName: 'div',
                        classes: ['container-fluid'],
                        components: [
                            {
                                tagName: 'div',
                                classes: ['row'],
                                components: [
                                    {
                                        tagName: 'div',
                                        classes: ['col-md-6'],
                                        attributes: { 'data-column': '1' }
                                    },
                                    {
                                        tagName: 'div',
                                        classes: ['col-md-6'],
                                        attributes: { 'data-column': '2' }
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        }
    });
}
```

#### 1.2 Create Widget Components for GrapesJS
**Files to Create/Modify:**
- `public/assets/admin/js/grapejs/components/widget-components.js` (NEW)

**Implementation:**

**1.2.1 Base Widget Component**
```javascript
// public/assets/admin/js/grapejs/components/widget-components.js

export default function registerWidgetComponents(editor) {
    const domComponents = editor.DomComponents;
    
    // Base Widget Component
    domComponents.addType('cms-widget', {
        model: {
            defaults: {
                tagName: 'div',
                classes: ['cms-widget'],
                draggable: '.cms-section, [data-column]',
                droppable: false,
                attributes: {
                    'data-widget-type': 'base'
                },
                traits: [
                    {
                        type: 'text',
                        name: 'data-widget-id',
                        label: 'Widget ID'
                    },
                    {
                        type: 'text',
                        name: 'css_classes',
                        label: 'CSS Classes'
                    },
                    {
                        type: 'text',
                        name: 'padding',
                        label: 'Padding'
                    },
                    {
                        type: 'text',
                        name: 'margin',
                        label: 'Margin'
                    }
                ],
                // GridStack position data
                grid_x: 0,
                grid_y: 0,
                grid_w: 6,
                grid_h: 2
            },
            
            init() {
                this.on('change:attributes change:classes', this.handleStyleChange);
                this.loadWidgetData();
            },
            
            loadWidgetData() {
                const widgetId = this.get('attributes')['data-widget-id'];
                if (widgetId) {
                    this.fetchWidgetContent(widgetId);
                }
            },
            
            fetchWidgetContent(widgetId) {
                fetch(`/admin/api/widgets/${widgetId}/content`)
                    .then(response => response.json())
                    .then(data => {
                        this.set('content', data.rendered_content);
                        this.updateTraits(data.fields);
                    });
            },
            
            updateTraits(fields) {
                const dynamicTraits = fields.map(field => ({
                    type: this.getTraitType(field.type),
                    name: field.name,
                    label: field.label,
                    value: field.value
                }));
                
                this.set('traits', [...this.get('traits'), ...dynamicTraits]);
            },
            
            getTraitType(fieldType) {
                const typeMap = {
                    'text': 'text',
                    'textarea': 'textarea',
                    'select': 'select',
                    'checkbox': 'checkbox',
                    'color': 'color',
                    'number': 'number'
                };
                return typeMap[fieldType] || 'text';
            },
            
            handleStyleChange() {
                const widgetId = this.get('attributes')['data-widget-id'];
                if (widgetId) {
                    this.updateDatabaseStyling(widgetId);
                }
            },
            
            updateDatabaseStyling(widgetId) {
                const stylingData = {
                    css_classes: this.getClasses().join(' '),
                    padding: this.getTrait('padding')?.get('value'),
                    margin: this.getTrait('margin')?.get('value'),
                    grid_x: this.get('grid_x'),
                    grid_y: this.get('grid_y'),
                    grid_w: this.get('grid_w'),
                    grid_h: this.get('grid_h')
                };
                
                fetch(`/admin/api/widgets/${widgetId}/styling`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify(stylingData)
                });
            }
        },
        
        view: {
            onRender() {
                this.el.classList.add('gjs-widget-component');
                this.setupWidgetToolbar();
            },
            
            setupWidgetToolbar() {
                // Add widget-specific toolbar buttons
                this.el.addEventListener('click', (e) => {
                    if (e.target === this.el) {
                        this.showWidgetEditor();
                    }
                });
            },
            
            showWidgetEditor() {
                // Open widget-specific editing panel
                const widgetId = this.model.get('attributes')['data-widget-id'];
                window.GrapesJSWidgetEditor.open(widgetId, this.model);
            }
        }
    });
    
    // Text Widget Component
    domComponents.addType('text-widget', {
        extend: 'cms-widget',
        model: {
            defaults: {
                attributes: {
                    'data-widget-type': 'text'
                },
                classes: ['cms-widget', 'text-widget'],
                traits: [
                    {
                        type: 'textarea',
                        name: 'content',
                        label: 'Text Content'
                    },
                    {
                        type: 'select',
                        name: 'text_align',
                        label: 'Text Alignment',
                        options: [
                            { value: 'left', name: 'Left' },
                            { value: 'center', name: 'Center' },
                            { value: 'right', name: 'Right' },
                            { value: 'justify', name: 'Justify' }
                        ]
                    }
                ]
            }
        }
    });
    
    // Image Widget Component
    domComponents.addType('image-widget', {
        extend: 'cms-widget',
        model: {
            defaults: {
                attributes: {
                    'data-widget-type': 'image'
                },
                classes: ['cms-widget', 'image-widget'],
                traits: [
                    {
                        type: 'text',
                        name: 'src',
                        label: 'Image URL'
                    },
                    {
                        type: 'text',
                        name: 'alt',
                        label: 'Alt Text'
                    },
                    {
                        type: 'select',
                        name: 'image_fit',
                        label: 'Image Fit',
                        options: [
                            { value: 'cover', name: 'Cover' },
                            { value: 'contain', name: 'Contain' },
                            { value: 'fill', name: 'Fill' }
                        ]
                    }
                ]
            }
        }
    });
}
```

#### 1.3 Integrate Components into GrapesJS Designer
**File:** `public/assets/admin/js/grapejs/grapejs-designer.js`

**Modifications:**
```javascript
// Add to imports at top of file
import registerSectionComponents from './components/section-components.js';
import registerWidgetComponents from './components/widget-components.js';

// Add to initializeGrapesJS method after editor initialization
registerSectionComponents(this.editor);
registerWidgetComponents(this.editor);

// Add component registration to setupBlockManager
setupBlockManager() {
    const blockManager = this.editor.BlockManager;
    
    // Section Blocks
    blockManager.add('full-width-section', {
        label: 'Full Width Section',
        category: 'Sections',
        content: {
            type: 'full-width-section',
            attributes: {
                'data-section-type': 'full-width'
            }
        }
    });
    
    blockManager.add('multi-column-section', {
        label: 'Multi Column Section',
        category: 'Sections',
        content: {
            type: 'multi-column-section',
            attributes: {
                'data-section-type': 'multi-column'
            }
        }
    });
    
    // Widget Blocks (integrate from GridStack)
    this.loadWidgetBlocks();
}

// Add widget loading method
async loadWidgetBlocks() {
    try {
        const response = await fetch('/admin/api/widgets/available');
        const widgets = await response.json();
        const blockManager = this.editor.BlockManager;
        
        widgets.forEach(widget => {
            blockManager.add(`widget-${widget.slug}`, {
                label: widget.name,
                category: widget.category || 'Widgets',
                content: {
                    type: `${widget.slug}-widget`,
                    attributes: {
                        'data-widget-type': widget.slug,
                        'data-widget-id': widget.id
                    }
                },
                media: widget.icon || '<i class="fas fa-puzzle-piece"></i>'
            });
        });
    } catch (error) {
        console.error('Failed to load widget blocks:', error);
    }
}
```

---

### Phase 2: Widget Library Integration (Week 2)
**Priority: HIGH**

#### 2.1 Create Widget Block Manager
**Files to Create/Modify:**
- `public/assets/admin/js/grapejs/managers/widget-block-manager.js` (NEW)

**Implementation:**

```javascript
// public/assets/admin/js/grapejs/managers/widget-block-manager.js

export class WidgetBlockManager {
    constructor(editor) {
        this.editor = editor;
        this.blockManager = editor.BlockManager;
        this.widgets = [];
        this.categories = new Set();
    }
    
    async initialize() {
        await this.loadWidgets();
        this.createCategories();
        this.createWidgetBlocks();
        this.setupDragHandlers();
    }
    
    async loadWidgets() {
        try {
            const response = await fetch('/admin/api/widgets/available');
            const data = await response.json();
            this.widgets = data.widgets || [];
            
            // Extract categories
            this.widgets.forEach(widget => {
                if (widget.category) {
                    this.categories.add(widget.category);
                }
            });
        } catch (error) {
            console.error('Failed to load widgets:', error);
            this.widgets = [];
        }
    }
    
    createCategories() {
        // Create block categories
        this.categories.forEach(category => {
            this.blockManager.getCategories().add({
                id: category.toLowerCase(),
                label: category
            });
        });
        
        // Add default categories if none exist
        if (this.categories.size === 0) {
            ['Content', 'Layout', 'Media', 'Form'].forEach(cat => {
                this.blockManager.getCategories().add({
                    id: cat.toLowerCase(),
                    label: cat
                });
            });
        }
    }
    
    createWidgetBlocks() {
        this.widgets.forEach(widget => {
            this.createWidgetBlock(widget);
        });
    }
    
    createWidgetBlock(widget) {
        const blockId = `widget-${widget.slug}`;
        
        this.blockManager.add(blockId, {
            label: widget.name,
            category: widget.category?.toLowerCase() || 'content',
            content: this.generateWidgetContent(widget),
            media: this.getWidgetIcon(widget),
            attributes: {
                class: 'widget-block',
                'data-widget-slug': widget.slug
            }
        });
    }
    
    generateWidgetContent(widget) {
        return {
            type: 'cms-widget',
            attributes: {
                'data-widget-id': widget.id,
                'data-widget-type': widget.slug,
                'data-widget-name': widget.name
            },
            classes: ['cms-widget', `widget-${widget.slug}`],
            content: widget.preview_content || `<div class="widget-placeholder">${widget.name}</div>`
        };
    }
    
    getWidgetIcon(widget) {
        if (widget.icon) {
            return `<i class="${widget.icon}"></i>`;
        }
        
        // Default icons based on widget type
        const iconMap = {
            'text': 'fas fa-font',
            'image': 'fas fa-image',
            'button': 'fas fa-hand-pointer',
            'form': 'fas fa-mb-form',
            'video': 'fas fa-video',
            'gallery': 'fas fa-images',
            'map': 'fas fa-map-marker-alt'
        };
        
        const iconClass = iconMap[widget.slug] || 'fas fa-puzzle-piece';
        return `<i class="${iconClass}"></i>`;
    }
    
    setupDragHandlers() {
        // Handle widget dragging from block manager
        this.editor.on('block:drag:start', (block) => {
            if (block.get('id').startsWith('widget-')) {
                this.handleWidgetDragStart(block);
            }
        });
        
        this.editor.on('block:drag:stop', (component, block) => {
            if (block && block.get('id').startsWith('widget-')) {
                this.handleWidgetDragStop(component, block);
            }
        });
    }
    
    handleWidgetDragStart(block) {
        // Add visual feedback for valid drop zones
        this.editor.Canvas.getBody().querySelectorAll('.cms-section, [data-column]')
            .forEach(el => el.classList.add('widget-drop-zone'));
    }
    
    handleWidgetDragStop(component, block) {
        // Remove visual feedback
        this.editor.Canvas.getBody().querySelectorAll('.widget-drop-zone')
            .forEach(el => el.classList.remove('widget-drop-zone'));
        
        // Initialize the newly added widget
        if (component) {
            this.initializeDroppedWidget(component);
        }
    }
    
    async initializeDroppedWidget(component) {
        const widgetId = component.get('attributes')['data-widget-id'];
        
        if (widgetId) {
            try {
                // Load widget content and configuration
                const response = await fetch(`/admin/api/widgets/${widgetId}/render`);
                const data = await response.json();
                
                // Update component with rendered content
                component.set('content', data.rendered_content);
                
                // Add dynamic traits based on widget fields
                this.addWidgetTraits(component, data.fields);
                
            } catch (error) {
                console.error('Failed to initialize widget:', error);
            }
        }
    }
    
    addWidgetTraits(component, fields) {
        const existingTraits = component.get('traits') || [];
        const dynamicTraits = fields.map(field => this.createTraitFromField(field));
        
        component.set('traits', [...existingTraits, ...dynamicTraits]);
    }
    
    createTraitFromField(field) {
        const traitConfig = {
            type: this.mapFieldTypeToTrait(field.type),
            name: field.name,
            label: field.label || field.name,
            value: field.value || field.default_value
        };
        
        // Add options for select fields
        if (field.type === 'select' && field.options) {
            traitConfig.options = field.options.map(option => ({
                value: option.value,
                name: option.label || option.value
            }));
        }
        
        return traitConfig;
    }
    
    mapFieldTypeToTrait(fieldType) {
        const typeMap = {
            'text': 'text',
            'textarea': 'textarea',
            'select': 'select',
            'checkbox': 'checkbox',
            'radio': 'radio',
            'color': 'color',
            'number': 'number',
            'url': 'text',
            'email': 'text',
            'date': 'text'
        };
        
        return typeMap[fieldType] || 'text';
    }
}
```

#### 2.2 Style Panel Integration
**Files to Create/Modify:**
- `public/assets/admin/js/grapejs/panels/style-panel-integration.js` (NEW)

**Implementation:**

```javascript
// public/assets/admin/js/grapejs/panels/style-panel-integration.js

export class StylePanelIntegration {
    constructor(editor) {
        this.editor = editor;
        this.styleManager = editor.StyleManager;
        this.selectorManager = editor.SelectorManager;
    }
    
    initialize() {
        this.setupStyleSectors();
        this.setupCustomProperties();
        this.setupComponentStyleHandlers();
    }
    
    setupStyleSectors() {
        this.styleManager.addSector('cms-layout', {
            name: 'CMS Layout',
            open: true,
            properties: [
                {
                    type: 'integer',
                    name: 'grid-column-start',
                    property: 'grid-column-start',
                    label: 'Grid X',
                    min: 1,
                    max: 12
                },
                {
                    type: 'integer',
                    name: 'grid-row-start',
                    property: 'grid-row-start',
                    label: 'Grid Y',
                    min: 1
                },
                {
                    type: 'integer',
                    name: 'grid-column-end',
                    property: 'grid-column-end',
                    label: 'Grid Width',
                    min: 2,
                    max: 13
                },
                {
                    type: 'integer',
                    name: 'grid-row-end',
                    property: 'grid-row-end',
                    label: 'Grid Height',
                    min: 2
                }
            ]
        });
        
        this.styleManager.addSector('cms-spacing', {
            name: 'CMS Spacing',
            open: false,
            properties: [
                {
                    type: 'composite',
                    name: 'cms-padding',
                    property: 'padding',
                    label: 'Padding',
                    properties: [
                        { name: 'padding-top', property: 'padding-top' },
                        { name: 'padding-right', property: 'padding-right' },
                        { name: 'padding-bottom', property: 'padding-bottom' },
                        { name: 'padding-left', property: 'padding-left' }
                    ]
                },
                {
                    type: 'composite',
                    name: 'cms-margin',
                    property: 'margin',
                    label: 'Margin',
                    properties: [
                        { name: 'margin-top', property: 'margin-top' },
                        { name: 'margin-right', property: 'margin-right' },
                        { name: 'margin-bottom', property: 'margin-bottom' },
                        { name: 'margin-left', property: 'margin-left' }
                    ]
                }
            ]
        });
        
        this.styleManager.addSector('cms-constraints', {
            name: 'Size Constraints',
            open: false,
            properties: [
                {
                    type: 'integer',
                    name: 'min-height',
                    property: 'min-height',
                    label: 'Min Height',
                    units: ['px', 'em', 'rem', '%'],
                    unit: 'px'
                },
                {
                    type: 'integer',
                    name: 'max-height',
                    property: 'max-height',
                    label: 'Max Height',
                    units: ['px', 'em', 'rem', '%'],
                    unit: 'px'
                }
            ]
        });
    }
    
    setupCustomProperties() {
        // Add custom CSS classes property
        this.styleManager.addProperty('cms-spacing', {
            type: 'text',
            name: 'cms-classes',
            label: 'CSS Classes',
            property: 'cms-classes',
            functionName: 'handleCSSClasses'
        });
    }
    
    setupComponentStyleHandlers() {
        // Listen for style changes on CMS components
        this.editor.on('component:styleUpdate', (component) => {
            if (this.isCMSComponent(component)) {
                this.syncStyleToDatabase(component);
            }
        });
        
        // Listen for trait changes
        this.editor.on('component:update:traits', (component) => {
            if (this.isCMSComponent(component)) {
                this.syncTraitsToDatabase(component);
            }
        });
    }
    
    isCMSComponent(component) {
        const type = component.get('type');
        return type && (type.includes('cms-') || type.includes('-widget') || type.includes('-section'));
    }
    
    async syncStyleToDatabase(component) {
        const componentType = component.get('type');
        const attributes = component.get('attributes');
        
        if (componentType.includes('section')) {
            await this.syncSectionStyle(component, attributes);
        } else if (componentType.includes('widget')) {
            await this.syncWidgetStyle(component, attributes);
        }
    }
    
    async syncSectionStyle(component, attributes) {
        const sectionId = attributes['data-section-id'];
        if (!sectionId) return;
        
        const styles = component.getStyle();
        const classes = component.getClasses();
        
        const stylingData = {
            css_classes: classes.join(' '),
            background_color: styles['background-color'],
            padding: this.extractSpacing(styles, 'padding'),
            margin: this.extractSpacing(styles, 'margin'),
            // Grid positioning from CSS Grid
            grid_x: this.parseGridValue(styles['grid-column-start']) - 1,
            grid_y: this.parseGridValue(styles['grid-row-start']) - 1,
            grid_w: this.parseGridValue(styles['grid-column-end']) - this.parseGridValue(styles['grid-column-start']),
            grid_h: this.parseGridValue(styles['grid-row-end']) - this.parseGridValue(styles['grid-row-start'])
        };
        
        try {
            await fetch(`/admin/api/sections/${sectionId}/styling`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify(stylingData)
            });
        } catch (error) {
            console.error('Failed to sync section styling:', error);
        }
    }
    
    async syncWidgetStyle(component, attributes) {
        const widgetId = attributes['data-widget-id'];
        if (!widgetId) return;
        
        const styles = component.getStyle();
        const classes = component.getClasses();
        
        const stylingData = {
            css_classes: classes.join(' '),
            padding: this.extractSpacing(styles, 'padding'),
            margin: this.extractSpacing(styles, 'margin'),
            min_height: styles['min-height'],
            max_height: styles['max-height']
        };
        
        try {
            await fetch(`/admin/api/widgets/${widgetId}/styling`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify(stylingData)
            });
        } catch (error) {
            console.error('Failed to sync widget styling:', error);
        }
    }
    
    extractSpacing(styles, property) {
        return {
            top: styles[`${property}-top`],
            right: styles[`${property}-right`],
            bottom: styles[`${property}-bottom`],
            left: styles[`${property}-left`]
        };
    }
    
    parseGridValue(value) {
        return parseInt(value) || 0;
    }
    
    async syncTraitsToDatabase(component) {
        const traits = component.get('traits');
        const attributes = component.get('attributes');
        const widgetId = attributes['data-widget-id'];
        
        if (!widgetId || !traits) return;
        
        const traitValues = {};
        traits.forEach(trait => {
            traitValues[trait.get('name')] = trait.get('value');
        });
        
        try {
            await fetch(`/admin/api/widgets/${widgetId}/fields`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ fields: traitValues })
            });
        } catch (error) {
            console.error('Failed to sync widget traits:', error);
        }
    }
}
```

---

### Phase 3: GridStack Integration & Synchronization (Week 3)
**Priority: HIGH**

#### 3.1 Two-Way Sync System
**Files to Create/Modify:**
- `public/assets/admin/js/grapejs/sync/gridstack-sync.js` (NEW)

**Implementation:**

```javascript
// public/assets/admin/js/grapejs/sync/gridstack-sync.js

export class GridStackSync {
    constructor(grapesJSInstance, gridStackInstance) {
        this.grapesJS = grapesJSInstance;
        this.gridStack = gridStackInstance;
        this.syncInProgress = false;
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // GrapesJS to GridStack sync
        this.grapesJS.editor.on('component:add', (component) => {
            if (!this.syncInProgress && this.isCMSComponent(component)) {
                this.syncToGridStack(component);
            }
        });
        
        this.grapesJS.editor.on('component:remove', (component) => {
            if (!this.syncInProgress && this.isCMSComponent(component)) {
                this.removeFromGridStack(component);
            }
        });
        
        this.grapesJS.editor.on('component:update', (component) => {
            if (!this.syncInProgress && this.isCMSComponent(component)) {
                this.updateGridStackItem(component);
            }
        });
        
        // GridStack to GrapesJS sync
        if (this.gridStack && this.gridStack.grid) {
            this.gridStack.grid.on('added', (event, items) => {
                if (!this.syncInProgress) {
                    this.syncToGrapesJS(items, 'add');
                }
            });
            
            this.gridStack.grid.on('removed', (event, items) => {
                if (!this.syncInProgress) {
                    this.syncToGrapesJS(items, 'remove');
                }
            });
            
            this.gridStack.grid.on('change', (event, items) => {
                if (!this.syncInProgress) {
                    this.syncToGrapesJS(items, 'update');
                }
            });
        }
    }
    
    isCMSComponent(component) {
        const type = component.get('type');
        return type && (type.includes('cms-') || type.includes('-widget') || type.includes('-section'));
    }
    
    async syncToGridStack(component) {
        this.syncInProgress = true;
        
        try {
            const componentType = component.get('type');
            const attributes = component.get('attributes');
            
            if (componentType.includes('section')) {
                await this.addSectionToGridStack(component, attributes);
            } else if (componentType.includes('widget')) {
                await this.addWidgetToGridStack(component, attributes);
            }
        } catch (error) {
            console.error('Failed to sync to GridStack:', error);
        } finally {
            this.syncInProgress = false;
        }
    }
    
    async addSectionToGridStack(component, attributes) {
        const sectionType = attributes['data-section-type'] || 'full-width';
        const gridPosition = this.getComponentGridPosition(component);
        
        // Create GridStack item for section
        const gridItem = {
            x: gridPosition.x,
            y: gridPosition.y,
            w: gridPosition.w,
            h: gridPosition.h,
            id: attributes['data-section-id'] || this.generateId('section'),
            content: this.generateSectionGridStackContent(sectionType, attributes)
        };
        
        if (this.gridStack && this.gridStack.grid) {
            this.gridStack.grid.addWidget(gridItem);
        }
    }
    
    async addWidgetToGridStack(component, attributes) {
        const widgetType = attributes['data-widget-type'];
        const widgetId = attributes['data-widget-id'];
        const gridPosition = this.getComponentGridPosition(component);
        
        // Create GridStack item for widget
        const gridItem = {
            x: gridPosition.x,
            y: gridPosition.y,
            w: gridPosition.w,
            h: gridPosition.h,
            id: widgetId,
            content: await this.generateWidgetGridStackContent(widgetType, widgetId, attributes)
        };
        
        if (this.gridStack && this.gridStack.grid) {
            this.gridStack.grid.addWidget(gridItem);
        }
    }
    
    getComponentGridPosition(component) {
        const styles = component.getStyle();
        
        return {
            x: this.parseGridValue(styles['grid-column-start']) - 1 || 0,
            y: this.parseGridValue(styles['grid-row-start']) - 1 || 0,
            w: (this.parseGridValue(styles['grid-column-end']) - this.parseGridValue(styles['grid-column-start'])) || 6,
            h: (this.parseGridValue(styles['grid-row-end']) - this.parseGridValue(styles['grid-row-start'])) || 2
        };
    }
    
    parseGridValue(value) {
        return parseInt(value) || 0;
    }
    
    generateSectionGridStackContent(sectionType, attributes) {
        return `
            <div class="gridstack-section ${sectionType}" data-section-id="${attributes['data-section-id']}">
                <div class="section-header">
                    <i class="fas fa-columns"></i>
                    <span>${sectionType.replace('-', ' ').toUpperCase()}</span>
                </div>
                <div class="section-content">
                    Drop widgets here
                </div>
            </div>
        `;
    }
    
    async generateWidgetGridStackContent(widgetType, widgetId, attributes) {
        try {
            // Get widget preview from API
            const response = await fetch(`/admin/api/widgets/${widgetId}/preview`);
            const data = await response.json();
            
            return `
                <div class="gridstack-widget ${widgetType}" data-widget-id="${widgetId}">
                    <div class="widget-header">
                        <i class="${this.getWidgetIcon(widgetType)}"></i>
                        <span>${data.name || widgetType}</span>
                    </div>
                    <div class="widget-preview">
                        ${data.preview_content || 'Widget Preview'}
                    </div>
                </div>
            `;
        } catch (error) {
            console.error('Failed to generate widget content:', error);
            return `<div class="gridstack-widget ${widgetType}">Widget: ${widgetType}</div>`;
        }
    }
    
    getWidgetIcon(widgetType) {
        const iconMap = {
            'text': 'fas fa-font',
            'image': 'fas fa-image',
            'button': 'fas fa-hand-pointer',
            'form': 'fas fa-mb-form',
            'video': 'fas fa-video'
        };
        return iconMap[widgetType] || 'fas fa-puzzle-piece';
    }
    
    async syncToGrapesJS(items, action) {
        this.syncInProgress = true;
        
        try {
            for (const item of items) {
                switch (action) {
                    case 'add':
                        await this.addComponentToGrapesJS(item);
                        break;
                    case 'remove':
                        this.removeComponentFromGrapesJS(item);
                        break;
                    case 'update':
                        this.updateComponentInGrapesJS(item);
                        break;
                }
            }
        } catch (error) {
            console.error('Failed to sync to GrapesJS:', error);
        } finally {
            this.syncInProgress = false;
        }
    }
    
    async addComponentToGrapesJS(gridItem) {
        const isSection = gridItem.id.includes('section');
        const isWidget = gridItem.id.includes('widget');
        
        if (isSection) {
            await this.addSectionComponentToGrapesJS(gridItem);
        } else if (isWidget) {
            await this.addWidgetComponentToGrapesJS(gridItem);
        }
    }
    
    async addSectionComponentToGrapesJS(gridItem) {
        const sectionType = this.extractSectionTypeFromGridItem(gridItem);
        
        const component = {
            type: `${sectionType}-section`,
            attributes: {
                'data-section-id': gridItem.id,
                'data-section-type': sectionType
            },
            style: this.gridPositionToCSS(gridItem)
        };
        
        this.grapesJS.editor.DomComponents.getWrapper().append(component);
    }
    
    async addWidgetComponentToGrapesJS(gridItem) {
        const widgetType = this.extractWidgetTypeFromGridItem(gridItem);
        
        const component = {
            type: `${widgetType}-widget`,
            attributes: {
                'data-widget-id': gridItem.id,
                'data-widget-type': widgetType
            },
            style: this.gridPositionToCSS(gridItem)
        };
        
        // Find appropriate parent section or add to wrapper
        const parent = this.findComponentParent(gridItem) || this.grapesJS.editor.DomComponents.getWrapper();
        parent.append(component);
    }
    
    gridPositionToCSS(gridItem) {
        return {
            'grid-column-start': (gridItem.x + 1).toString(),
            'grid-row-start': (gridItem.y + 1).toString(),
            'grid-column-end': (gridItem.x + gridItem.w + 1).toString(),
            'grid-row-end': (gridItem.y + gridItem.h + 1).toString()
        };
    }
    
    extractSectionTypeFromGridItem(gridItem) {
        // Extract section type from GridStack item data
        const element = document.querySelector(`[data-section-id="${gridItem.id}"]`);
        return element?.classList.contains('multi-column') ? 'multi-column' : 'full-width';
    }
    
    extractWidgetTypeFromGridItem(gridItem) {
        // Extract widget type from GridStack item data
        const element = document.querySelector(`[data-widget-id="${gridItem.id}"]`);
        const classList = Array.from(element?.classList || []);
        return classList.find(cls => cls.includes('widget-'))?.replace('widget-', '') || 'text';
    }
    
    findComponentParent(gridItem) {
        // Logic to find the appropriate parent component based on grid position
        const components = this.grapesJS.editor.DomComponents.getWrapper().find('[data-section-id]');
        
        for (const component of components) {
            const sectionPosition = this.getComponentGridPosition(component);
            if (this.isWithinBounds(gridItem, sectionPosition)) {
                return component;
            }
        }
        
        return null;
    }
    
    isWithinBounds(item, bounds) {
        return item.x >= bounds.x && 
               item.y >= bounds.y && 
               (item.x + item.w) <= (bounds.x + bounds.w) && 
               (item.y + item.h) <= (bounds.y + bounds.h);
    }
    
    generateId(type) {
        return `${type}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }
}
```

---

## =' Implementation Timeline

### Week 1: Component System
- **Day 1-2**: Create section and widget components for GrapesJS
- **Day 3-4**: Integrate components into main designer
- **Day 5**: Test component functionality and rendering

### Week 2: Widget Integration
- **Day 1-2**: Build widget block manager and style panel integration
- **Day 3-4**: Implement dynamic trait system for widgets
- **Day 5**: Test widget library integration

### Week 3: GridStack Sync
- **Day 1-3**: Implement two-way synchronization system
- **Day 4-5**: Test sync functionality and resolve conflicts

### Week 4: API & Polish
- **Day 1-2**: Complete API endpoints for widget/section management
- **Day 3-4**: Performance optimization and bug fixes
- **Day 5**: Final testing and documentation

---

## <¯ Success Criteria

### Functional Requirements
1. **Widget Library**: All GridStack widgets available in GrapesJS
2. **Visual Editing**: Direct manipulation of widget properties
3. **Section Templates**: All section types available as GrapesJS blocks
4. **Two-Way Sync**: Changes sync between GrapesJS and GridStack
5. **Database Integration**: All changes persist to database

### Technical Requirements
1. **Performance**: No significant performance degradation
2. **Compatibility**: Works with existing GridStack functionality
3. **Extensibility**: Easy to add new widget types
4. **Responsiveness**: Visual editor works on different screen sizes

### User Experience Requirements
1. **Intuitive Interface**: Drag-drop functionality works seamlessly
2. **Real-time Preview**: Changes reflected immediately
3. **Error Handling**: Graceful handling of sync failures
4. **Undo/Redo**: Standard editor functionality works

---

This comprehensive plan provides the roadmap to complete the GrapesJS integration, creating a fully functional visual page builder that leverages both GridStack's layout capabilities and GrapesJS's visual editing power while maintaining synchronization with the database through the universal styling system.