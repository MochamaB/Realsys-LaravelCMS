# Custom GrapeJS Integration Implementation Plan

## Overview

This plan details how to create a full custom GrapeJS integration that bridges your existing widget system with GrapeJS's drag-and-drop capabilities. This is a complex undertaking that requires significant architectural changes.

**⚠️ WARNING: This is a high-risk, high-complexity implementation that could take 8-12 weeks and may destabilize your existing system.**

---

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   GrapeJS       │    │  Integration    │    │  Your CMS       │
│   Editor        │◄──►│    Bridge       │◄──►│   Backend       │
│                 │    │                 │    │                 │
│ - Visual Editor │    │ - Data Sync     │    │ - Page Models   │
│ - Component Mgr │    │ - Widget Bridge │    │ - Widget System │
│ - Style Mgr     │    │ - Asset Inject  │    │ - Theme System  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Core Challenges to Solve

1. **Widget-to-Component Translation**
2. **Bi-directional Data Synchronization**
3. **Asset Management Bridge**
4. **Content Rendering Pipeline**
5. **Responsive Editing System**

---

## Phase 1: Foundation Layer (Weeks 1-3)

### Step 1.1: Create Integration Services

**Widget to GrapeJS Block Converter:**
```php
<?php
// app/Services/GrapesJS/WidgetBlockConverter.php

namespace App\Services\GrapesJS;

use App\Models\Widget;
use App\Services\WidgetService;

class WidgetBlockConverter
{
    protected $widgetService;
    
    public function __construct(WidgetService $widgetService)
    {
        $this->widgetService = $widgetService;
    }
    
    /**
     * Convert all widgets to GrapeJS blocks
     */
    public function convertAllWidgets(): array
    {
        $widgets = Widget::where('is_active', true)->get();
        $blocks = [];
        
        foreach ($widgets as $widget) {
            $blocks[] = $this->convertWidget($widget);
        }
        
        return $blocks;
    }
    
    /**
     * Convert single widget to GrapeJS block
     */
    public function convertWidget(Widget $widget): array
    {
        return [
            'id' => "widget-{$widget->slug}",
            'label' => $widget->name,
            'category' => $widget->category ?: 'General',
            'content' => [
                'type' => "cms-widget-{$widget->slug}",
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'attributes' => [
                    'data-widget-type' => $widget->slug,
                    'data-widget-id' => $widget->id,
                    'class' => 'cms-widget'
                ],
                'components' => $this->generatePreviewHTML($widget),
                'traits' => $this->convertSettingsToTraits($widget)
            ],
            'media' => asset('assets/admin/images/widgets/' . $widget->slug . '.png'),
            'select' => true,
            'activate' => true,
        ];
    }
    
    /**
     * Generate preview HTML for widget
     */
    protected function generatePreviewHTML(Widget $widget): string
    {
        try {
            // Create dummy data for preview
            $dummyData = $this->generateDummyData($widget);
            
            // Render widget with dummy data
            $viewPath = "theme::widgets.{$widget->slug}.view";
            
            if (view()->exists($viewPath)) {
                return view($viewPath, $dummyData)->render();
            }
            
            return $this->generateFallbackHTML($widget);
            
        } catch (\Exception $e) {
            return $this->generateFallbackHTML($widget);
        }
    }
    
    /**
     * Convert widget settings schema to GrapeJS traits
     */
    protected function convertSettingsToTraits(Widget $widget): array
    {
        if (!$widget->settings_schema) {
            return [];
        }
        
        $traits = [];
        
        foreach ($widget->settings_schema as $field) {
            $traits[] = $this->convertFieldToTrait($field);
        }
        
        return $traits;
    }
    
    /**
     * Convert single field to GrapeJS trait
     */
    protected function convertFieldToTrait(array $field): array
    {
        $trait = [
            'name' => $field['name'],
            'label' => $field['label'] ?? $field['name'],
            'type' => $this->mapFieldTypeToTraitType($field['type'])
        ];
        
        // Add field-specific options
        switch ($field['type']) {
            case 'select':
                $trait['options'] = $field['options'] ?? [];
                break;
            case 'number':
                $trait['min'] = $field['min'] ?? null;
                $trait['max'] = $field['max'] ?? null;
                break;
            case 'color':
                $trait['type'] = 'color';
                break;
        }
        
        return $trait;
    }
    
    /**
     * Map CMS field types to GrapeJS trait types
     */
    protected function mapFieldTypeToTraitType(string $cmsType): string
    {
        $mapping = [
            'text' => 'text',
            'textarea' => 'textarea',
            'number' => 'number',
            'select' => 'select',
            'checkbox' => 'checkbox',
            'color' => 'color',
            'image' => 'text', // Will be enhanced later
            'url' => 'text',
            'email' => 'email'
        ];
        
        return $mapping[$cmsType] ?? 'text';
    }
}
```

**Data Synchronization Service:**
```php
<?php
// app/Services/GrapesJS/DataSynchronizer.php

namespace App\Services\GrapesJS;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use Illuminate\Support\Facades\Log;

class DataSynchronizer
{
    /**
     * Convert Page data to GrapeJS format
     */
    public function pageToGrapesJS(Page $page): array
    {
        $page->load(['sections.pageSectionWidgets.widget']);
        
        $components = [];
        
        foreach ($page->sections->sortBy('position') as $section) {
            $components[] = $this->sectionToComponent($section);
        }
        
        return [
            'html' => '', // Will be generated by GrapeJS
            'css' => $this->extractPageCSS($page),
            'components' => $components,
            'styles' => []
        ];
    }
    
    /**
     * Convert GrapeJS data back to Page structure
     */
    public function grapesJSToPage(array $grapesData, Page $page): void
    {
        try {
            \DB::transaction(function () use ($grapesData, $page) {
                $this->syncComponents($grapesData['components'], $page);
                $this->syncStyles($grapesData['styles'] ?? [], $page);
            });
            
            Log::info('GrapeJS data synchronized to page', ['page_id' => $page->id]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync GrapeJS data to page', [
                'page_id' => $page->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Convert section to GrapeJS component
     */
    protected function sectionToComponent(PageSection $section): array
    {
        $widgets = [];
        
        foreach ($section->pageSectionWidgets->sortBy('position') as $widget) {
            $widgets[] = $this->widgetToComponent($widget);
        }
        
        return [
            'type' => 'cms-section',
            'section_id' => $section->id,
            'attributes' => [
                'data-section-id' => $section->id,
                'class' => 'cms-section ' . ($section->css_classes ?? '')
            ],
            'components' => $widgets,
            'traits' => $this->sectionTraits($section)
        ];
    }
    
    /**
     * Convert widget to GrapeJS component
     */
    protected function widgetToComponent(PageSectionWidget $widget): array
    {
        return [
            'type' => "cms-widget-{$widget->widget->slug}",
            'widget_instance_id' => $widget->id,
            'widget_id' => $widget->widget->id,
            'attributes' => [
                'data-widget-instance-id' => $widget->id,
                'data-widget-type' => $widget->widget->slug,
                'class' => 'cms-widget ' . ($widget->css_classes ?? '')
            ],
            'traits' => $this->widgetTraits($widget),
            'components' => $this->renderWidgetContent($widget)
        ];
    }
    
    /**
     * Sync GrapeJS components back to CMS structure
     */
    protected function syncComponents(array $components, Page $page): void
    {
        $sectionPosition = 0;
        
        foreach ($components as $component) {
            if ($component['type'] === 'cms-section') {
                $this->syncSection($component, $page, $sectionPosition);
                $sectionPosition++;
            }
        }
    }
    
    /**
     * Sync individual section
     */
    protected function syncSection(array $component, Page $page, int $position): void
    {
        $sectionId = $component['section_id'] ?? null;
        
        if ($sectionId) {
            $section = PageSection::find($sectionId);
            if ($section && $section->page_id === $page->id) {
                $this->updateSectionFromComponent($section, $component, $position);
                $this->syncSectionWidgets($component['components'] ?? [], $section);
            }
        }
    }
    
    /**
     * Sync section widgets
     */
    protected function syncSectionWidgets(array $components, PageSection $section): void
    {
        $widgetPosition = 0;
        
        foreach ($components as $component) {
            if (strpos($component['type'], 'cms-widget-') === 0) {
                $this->syncWidget($component, $section, $widgetPosition);
                $widgetPosition++;
            }
        }
    }
}
```

### Step 1.2: Create Custom GrapeJS Components

**Widget Component Definition:**
```javascript
// public/assets/admin/js/live-designer/grapesjs/widget-components.js

class WidgetComponentManager {
    constructor(editor) {
        this.editor = editor;
        this.init();
    }
    
    init() {
        this.registerBaseWidgetComponent();
        this.loadAndRegisterWidgetComponents();
    }
    
    registerBaseWidgetComponent() {
        this.editor.DomComponents.addType('cms-widget', {
            model: {
                defaults: {
                    tagName: 'div',
                    draggable: true,
                    droppable: false,
                    copyable: true,
                    removable: true,
                    traits: [
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
                    ]
                },
                
                init() {
                    this.on('change:attributes', this.handleAttributeChange);
                    this.on('change:traits', this.handleTraitChange);
                },
                
                handleAttributeChange() {
                    // Sync attribute changes back to CMS
                    this.syncToCMS();
                },
                
                handleTraitChange() {
                    // Sync trait changes back to CMS
                    this.syncToCMS();
                },
                
                syncToCMS() {
                    const instanceId = this.get('widget_instance_id');
                    const traits = this.getTraits();
                    const attributes = this.getAttributes();
                    
                    // Send update to backend
                    this.syncWidgetData(instanceId, traits, attributes);
                }
            },
            
            view: {
                init() {
                    this.listenTo(this.model, 'change', this.handleModelChange);
                },
                
                handleModelChange() {
                    this.render();
                },
                
                onRender() {
                    const widgetType = this.model.get('widget_type');
                    this.el.classList.add('cms-widget', `widget-${widgetType}`);
                    
                    // Add editing indicators
                    if (!this.el.querySelector('.widget-edit-overlay')) {
                        this.addEditOverlay();
                    }
                },
                
                addEditOverlay() {
                    const overlay = document.createElement('div');
                    overlay.className = 'widget-edit-overlay';
                    overlay.innerHTML = `
                        <div class="widget-controls">
                            <button class="widget-edit-btn" title="Edit Widget">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="widget-content-btn" title="Manage Content">
                                <i class="ri-file-list-line"></i>
                            </button>
                        </div>
                    `;
                    this.el.appendChild(overlay);
                }
            }
        });
    }
    
    async loadAndRegisterWidgetComponents() {
        try {
            const response = await fetch('/admin/api/live-designer/widgets');
            const widgets = await response.json();
            
            if (widgets.success) {
                this.registerWidgetComponents(widgets.data.widgets);
            }
        } catch (error) {
            console.error('Failed to load widget components:', error);
        }
    }
    
    registerWidgetComponents(widgetsByCategory) {
        Object.values(widgetsByCategory).flat().forEach(widget => {
            this.registerWidgetComponent(widget);
        });
    }
    
    registerWidgetComponent(widget) {
        const componentType = `cms-widget-${widget.slug}`;
        
        this.editor.DomComponents.addType(componentType, {
            extend: 'cms-widget',
            model: {
                defaults: {
                    ...this.getWidgetDefaults(widget),
                    widget_id: widget.id,
                    widget_slug: widget.slug,
                    widget_type: widget.slug
                },
                
                init() {
                    this.constructor.__super__.init.apply(this, arguments);
                    this.loadWidgetContent();
                },
                
                async loadWidgetContent() {
                    try {
                        const html = await this.fetchWidgetPreview();
                        this.set('content', html);
                    } catch (error) {
                        console.error(`Failed to load content for widget ${widget.slug}:`, error);
                    }
                },
                
                async fetchWidgetPreview() {
                    const response = await fetch(`/admin/api/widgets/${widget.id}/preview`);
                    const data = await response.json();
                    return data.success ? data.html : this.getFallbackHTML();
                },
                
                getFallbackHTML() {
                    return `
                        <div class="widget-placeholder">
                            <i class="${widget.icon || 'ri-puzzle-line'}"></i>
                            <h4>${widget.name}</h4>
                            <p>${widget.description || 'No description available'}</p>
                        </div>
                    `;
                }
            }
        });
        
        // Register as block for drag-and-drop
        this.editor.BlockManager.add(`widget-${widget.slug}`, {
            label: widget.name,
            category: widget.category || 'General',
            content: {
                type: componentType,
                widget_id: widget.id
            },
            media: this.getWidgetIcon(widget)
        });
    }
    
    getWidgetDefaults(widget) {
        const defaults = {
            tagName: 'div',
            draggable: '.cms-section',
            droppable: false,
            copyable: true,
            removable: true,
            traits: [
                {
                    type: 'text',
                    name: 'css_classes',
                    label: 'CSS Classes'
                }
            ]
        };
        
        // Add widget-specific traits from settings schema
        if (widget.settings_schema) {
            const widgetTraits = this.convertSettingsToTraits(widget.settings_schema);
            defaults.traits = [...defaults.traits, ...widgetTraits];
        }
        
        return defaults;
    }
    
    convertSettingsToTraits(schema) {
        return schema.map(field => ({
            type: this.mapFieldType(field.type),
            name: field.name,
            label: field.label || field.name,
            options: field.options || undefined
        }));
    }
    
    mapFieldType(cmsType) {
        const mapping = {
            'text': 'text',
            'textarea': 'textarea', 
            'number': 'number',
            'select': 'select',
            'checkbox': 'checkbox',
            'color': 'color'
        };
        return mapping[cmsType] || 'text';
    }
    
    getWidgetIcon(widget) {
        return `<i class="${widget.icon || 'ri-puzzle-line'}"></i>`;
    }
}
```

### Step 1.3: Asset Management Bridge

**Asset Injection Service:**
```php
<?php
// app/Services/GrapesJS/AssetBridge.php

namespace App\Services\GrapesJS;

use App\Models\Page;
use App\Services\ThemeManager;
use App\Services\WidgetService;

class AssetBridge
{
    protected $themeManager;
    protected $widgetService;
    
    public function __construct(ThemeManager $themeManager, WidgetService $widgetService)
    {
        $this->themeManager = $themeManager;
        $this->widgetService = $widgetService;
    }
    
    /**
     * Get all assets needed for GrapeJS canvas
     */
    public function getCanvasAssets(Page $page): array
    {
        $page->load(['template.theme', 'sections.pageSectionWidgets.widget']);
        
        $assets = [
            'css' => [],
            'js' => []
        ];
        
        // Add theme assets
        $themeAssets = $this->themeManager->getThemeAssets($page->template->theme);
        $assets['css'] = array_merge($assets['css'], $themeAssets['css']);
        $assets['js'] = array_merge($assets['js'], $themeAssets['js']);
        
        // Add widget assets
        $widgetAssets = $this->collectWidgetAssets($page);
        $assets['css'] = array_merge($assets['css'], $widgetAssets['css']);
        $assets['js'] = array_merge($assets['js'], $widgetAssets['js']);
        
        // Add GrapeJS-specific assets
        $assets['css'][] = asset('assets/admin/css/live-designer/grapesjs-canvas.css');
        $assets['js'][] = asset('assets/admin/js/live-designer/grapesjs-canvas-helpers.js');
        
        return [
            'css' => array_unique($assets['css']),
            'js' => array_unique($assets['js'])
        ];
    }
    
    /**
     * Inject assets into GrapeJS canvas configuration
     */
    public function injectIntoCanvas(array $assets): array
    {
        return [
            'styles' => $assets['css'],
            'scripts' => $assets['js']
        ];
    }
    
    /**
     * Generate CSS for widget preview in canvas
     */
    public function generateWidgetPreviewCSS(): string
    {
        return '
            .cms-widget {
                position: relative;
                min-height: 50px;
                border: 1px dashed transparent;
                transition: all 0.2s ease;
            }
            
            .cms-widget:hover {
                border-color: #0d6efd;
                background-color: rgba(13, 110, 253, 0.05);
            }
            
            .cms-widget.gjs-selected {
                border-color: #0d6efd !important;
                border-style: solid !important;
                box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
            }
            
            .widget-edit-overlay {
                position: absolute;
                top: -30px;
                right: 0;
                background: #0d6efd;
                border-radius: 4px;
                opacity: 0;
                transition: opacity 0.2s ease;
                z-index: 10;
            }
            
            .cms-widget:hover .widget-edit-overlay,
            .cms-widget.gjs-selected .widget-edit-overlay {
                opacity: 1;
            }
            
            .widget-controls {
                display: flex;
                gap: 2px;
            }
            
            .widget-controls button {
                background: none;
                border: none;
                color: white;
                padding: 4px 6px;
                cursor: pointer;
                font-size: 12px;
            }
            
            .widget-controls button:hover {
                background: rgba(255, 255, 255, 0.2);
            }
        ';
    }
    
    protected function collectWidgetAssets(Page $page): array
    {
        $assets = ['css' => [], 'js' => []];
        $processedWidgets = [];
        
        foreach ($page->sections as $section) {
            foreach ($section->pageSectionWidgets as $pageWidget) {
                $widget = $pageWidget->widget;
                
                if (!in_array($widget->id, $processedWidgets)) {
                    $widgetAssets = $this->widgetService->getWidgetAssets($widget);
                    $assets['css'] = array_merge($assets['css'], $widgetAssets['css'] ?? []);
                    $assets['js'] = array_merge($assets['js'], $widgetAssets['js'] ?? []);
                    $processedWidgets[] = $widget->id;
                }
            }
        }
        
        return $assets;
    }
}
```

---

## Phase 2: Widget Integration (Weeks 4-7)

### Step 2.1: Content Management Integration

**Content Browser for GrapeJS:**
```javascript
// public/assets/admin/js/live-designer/grapesjs/content-browser.js
class GrapesJSContentBrowser {
    constructor(editor) {
        this.editor = editor;
        this.modal = null;
        this.currentWidget = null;
        
        this.init();
    }
    
    init() {
        this.createContentBrowserModal();
        this.setupCommands();
    }
    
    createContentBrowserModal() {
        this.editor.Modal.add('content-browser', {
            title: 'Content Browser',
            content: this.getModalContent(),
            attributes: { class: 'content-browser-modal' }
        });
    }
    
    setupCommands() {
        this.editor.Commands.add('open-content-browser', {
            run: (editor, sender, options = {}) => {
                this.currentWidget = options.widget;
                this.openContentBrowser();
            }
        });
    }
    
    openContentBrowser() {
        const modal = this.editor.Modal.open('content-browser');
        this.loadContentTypes();
    }
    
    async loadContentTypes() {
        try {
            const response = await fetch('/admin/api/live-designer/content-types');
            const data = await response.json();
            
            if (data.success) {
                this.renderContentTypes(data.data.content_types);
            }
        } catch (error) {
            console.error('Failed to load content types:', error);
        }
    }
    
    renderContentTypes(contentTypes) {
        const container = document.getElementById('content-types-list');
        if (!container) return;
        
        container.innerHTML = contentTypes.map(contentType => `
            <div class="content-type-item" data-content-type-id="${contentType.id}">
                <h4>${contentType.name}</h4>
                <p>${contentType.description || ''}</p>
                <span class="content-count">${contentType.content_count} items</span>
                <button class="btn btn-primary" onclick="contentBrowser.selectContentType(${contentType.id})">
                    Select
                </button>
            </div>
        `).join('');
    }
    
    async selectContentType(contentTypeId) {
        this.showLoading();
        
        try {
            const response = await fetch(`/admin/api/live-designer/content-items?content_type_id=${contentTypeId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderContentItems(data.data.items);
            }
        } catch (error) {
            console.error('Failed to load content items:', error);
        } finally {
            this.hideLoading();
        }
    }
    
    renderContentItems(items) {
        const container = document.getElementById('content-items-grid');
        if (!container) return;
        
        container.innerHTML = items.map(item => `
            <div class="content-item-card" data-item-id="${item.id}">
                <h5>${item.title}</h5>
                <p>${item.excerpt || ''}</p>
                <div class="content-meta">
                    <small>${item.created_at}</small>
                </div>
                <button class="btn btn-sm btn-primary" onclick="contentBrowser.selectContentItem(${item.id})">
                    Use This Content
                </button>
            </div>
        `).join('');
    }
    
    selectContentItem(itemId) {
        if (this.currentWidget) {
            this.applyContentToWidget(this.currentWidget, itemId);
            this.editor.Modal.close();
        }
    }
    
    async applyContentToWidget(widget, itemId) {
        try {
            const response = await fetch('/admin/api/live-designer/apply-content', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    widget_instance_id: widget.get('widget_instance_id'),
                    content_item_id: itemId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Reload widget content
                await widget.loadWidgetContent();
                this.editor.refresh();
            }
        } catch (error) {
            console.error('Failed to apply content to widget:', error);
        }
    }
    
    getModalContent() {
        return `
            <div class="content-browser-container">
                <div class="content-browser-sidebar">
                    <h3>Content Types</h3>
                    <div id="content-types-list" class="content-types-list">
                        <div class="loading">Loading content types...</div>
                    </div>
                </div>
                <div class="content-browser-main">
                    <h3>Content Items</h3>
                    <div id="content-items-grid" class="content-items-grid">
                        <div class="no-content">Select a content type to view items</div>
                    </div>
                </div>
            </div>
        `;
    }
}
```

### Step 2.2: Enhanced Widget Editing

**Advanced Widget Editor Panel:**
```javascript
// public/assets/admin/js/live-designer/grapesjs/advanced-widget-editor.js
class AdvancedWidgetEditor {
    constructor(editor) {
        this.editor = editor;
        this.currentWidget = null;
        this.panels = {};
        
        this.init();
    }
    
    init() {
        this.createEditorPanels();
        this.setupEventListeners();
    }
    
    createEditorPanels() {
        // Settings Panel
        this.panels.settings = this.editor.Panels.addPanel({
            id: 'widget-settings',
            buttons: [{
                id: 'widget-settings-btn',
                className: 'fa fa-cog',
                command: 'open-widget-settings',
                active: false
            }]
        });
        
        // Content Panel
        this.panels.content = this.editor.Panels.addPanel({
            id: 'widget-content',
            buttons: [{
                id: 'widget-content-btn',
                className: 'fa fa-file-text',
                command: 'open-widget-content',
                active: false
            }]
        });
        
        // Style Panel
        this.panels.style = this.editor.Panels.addPanel({
            id: 'widget-style',
            buttons: [{
                id: 'widget-style-btn',
                className: 'fa fa-paint-brush',
                command: 'open-widget-styles',
                active: false
            }]
        });
    }
    
    setupEventListeners() {
        this.editor.on('component:selected', (component) => {
            if (this.isWidgetComponent(component)) {
                this.currentWidget = component;
                this.showWidgetPanels();
                this.loadWidgetEditor(component);
            } else {
                this.hideWidgetPanels();
                this.currentWidget = null;
            }
        });
        
        this.editor.on('component:deselected', () => {
            this.hideWidgetPanels();
            this.currentWidget = null;
        });
    }
    
    isWidgetComponent(component) {
        return component.get('type').startsWith('cms-widget-');
    }
    
    showWidgetPanels() {
        Object.values(this.panels).forEach(panel => {
            panel.set('visible', true);
        });
    }
    
    hideWidgetPanels() {
        Object.values(this.panels).forEach(panel => {
            panel.set('visible', false);
        });
    }
    
    async loadWidgetEditor(widget) {
        const widgetInstanceId = widget.get('widget_instance_id');
        
        try {
            const response = await fetch(`/admin/api/live-designer/widget-editor/${widgetInstanceId}`);
            const html = await response.text();
            
            this.renderWidgetEditor(html);
        } catch (error) {
            console.error('Failed to load widget editor:', error);
        }
    }
    
    renderWidgetEditor(html) {
        // Create or update widget editor container
        let container = document.getElementById('widget-editor-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'widget-editor-container';
            container.className = 'widget-editor-panel';
            document.body.appendChild(container);
        }
        
        container.innerHTML = html;
        container.style.display = 'block';
        
        this.setupEditorFormHandlers(container);
    }
    
    setupEditorFormHandlers(container) {
        // Settings form handler
        const settingsForm = container.querySelector('.widget-settings-form');
        if (settingsForm) {
            settingsForm.addEventListener('change', this.handleSettingsChange.bind(this));
        }
        
        // Content form handler
        const contentForm = container.querySelector('.widget-content-form');
        if (contentForm) {
            contentForm.addEventListener('change', this.handleContentChange.bind(this));
        }
        
        // Style form handler
        const styleForm = container.querySelector('.widget-style-form');
        if (styleForm) {
            styleForm.addEventListener('change', this.handleStyleChange.bind(this));
        }
    }
    
    async handleSettingsChange(event) {
        if (!this.currentWidget) return;
        
        const formData = new FormData(event.target.form);
        const settings = Object.fromEntries(formData.entries());
        
        await this.updateWidget('settings', settings);
    }
    
    async handleContentChange(event) {
        if (!this.currentWidget) return;
        
        const formData = new FormData(event.target.form);
        const content = Object.fromEntries(formData.entries());
        
        await this.updateWidget('content', content);
    }
    
    async handleStyleChange(event) {
        if (!this.currentWidget) return;
        
        const formData = new FormData(event.target.form);
        const styles = Object.fromEntries(formData.entries());
        
        await this.updateWidget('style', styles);
    }
    
    async updateWidget(type, data) {
        const widgetInstanceId = this.currentWidget.get('widget_instance_id');
        
        try {
            const response = await fetch(`/admin/api/live-designer/update-widget-${type}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    widget_instance_id: widgetInstanceId,
                    data: data
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update widget content in canvas
                await this.refreshWidgetInCanvas();
            }
        } catch (error) {
            console.error(`Failed to update widget ${type}:`, error);
        }
    }
    
    async refreshWidgetInCanvas() {
        if (this.currentWidget) {
            await this.currentWidget.loadWidgetContent();
            this.editor.refresh();
        }
    }
}
```

---

## Phase 3: Advanced Features (Weeks 8-10)

### Step 3.1: Responsive Editing System

**Responsive Manager:**
```javascript
// public/assets/admin/js/live-designer/grapesjs/responsive-manager.js
class ResponsiveManager {
    constructor(editor) {
        this.editor = editor;
        this.devices = {
            desktop: { width: '', name: 'Desktop' },
            tablet: { width: '768px', name: 'Tablet' },
            mobile: { width: '375px', name: 'Mobile' }
        };
        this.currentDevice = 'desktop';
        
        this.init();
    }
    
    init() {
        this.setupDevices();
        this.setupDeviceControls();
        this.setupResponsiveEditing();
    }
    
    setupDevices() {
        const deviceManager = this.editor.DeviceManager;
        
        Object.entries(this.devices).forEach(([key, device]) => {
            deviceManager.add({
                name: key,
                width: device.width,
                widthMedia: device.width
            });
        });
    }
    
    setupDeviceControls() {
        this.editor.Panels.addButton('devices', {
            id: 'device-desktop',
            className: 'fa fa-desktop',
            command: () => this.setDevice('desktop'),
            active: true
        });
        
        this.editor.Panels.addButton('devices', {
            id: 'device-tablet', 
            className: 'fa fa-tablet',
            command: () => this.setDevice('tablet')
        });
        
        this.editor.Panels.addButton('devices', {
            id: 'device-mobile',
            className: 'fa fa-mobile',
            command: () => this.setDevice('mobile')
        });
    }
    
    setDevice(device) {
        this.currentDevice = device;
        this.editor.setDevice(device);
        
        // Update active button
        this.updateActiveButton(device);
        
        // Update style manager for responsive editing
        this.updateStyleManagerForDevice(device);
    }
    
    updateActiveButton(activeDevice) {
        document.querySelectorAll('[id^="device-"]').forEach(btn => {
            btn.classList.remove('gjs-pn-active');
        });
        
        document.getElementById(`device-${activeDevice}`)?.classList.add('gjs-pn-active');
    }
    
    setupResponsiveEditing() {
        // Enable responsive style editing
        this.editor.on('component:selected', (component) => {
            this.setupResponsiveTraits(component);
        });
    }
    
    setupResponsiveTraits(component) {
        const traits = component.get('traits');
        const responsiveTraits = this.createResponsiveTraits();
        
        component.set('traits', [...traits, ...responsiveTraits]);
    }
    
    createResponsiveTraits() {
        return [
            {
                type: 'checkbox',
                name: 'hide_on_mobile',
                label: 'Hide on Mobile'
            },
            {
                type: 'checkbox', 
                name: 'hide_on_tablet',
                label: 'Hide on Tablet'
            },
            {
                type: 'checkbox',
                name: 'hide_on_desktop', 
                label: 'Hide on Desktop'
            }
        ];
    }
    
    updateStyleManagerForDevice(device) {
        const sm = this.editor.StyleManager;
        
        // Update property names to include device prefix
        const sectors = sm.getSectors();
        
        sectors.forEach(sector => {
            const properties = sector.get('properties');
            properties.forEach(property => {
                if (device !== 'desktop') {
                    property.set('name', `${property.get('name')}-${device}`);
                }
            });
        });
    }
}
```

### Step 3.2: Advanced Styling System

**Custom Style Manager:**
```javascript
// public/assets/admin/js/live-designer/grapesjs/style-manager.js
class CustomStyleManager {
    constructor(editor) {
        this.editor = editor;
        this.init();
    }
    
    init() {
        this.setupCustomSectors();
        this.setupColorPalette();
        this.setupTypographySystem();
    }
    
    setupCustomSectors() {
        const sm = this.editor.StyleManager;
        
        // Clear default sectors
        sm.getSectors().reset();
        
        // Add custom sectors
        sm.addSector('layout', {
            name: 'Layout',
            open: false,
            properties: this.getLayoutProperties()
        });
        
        sm.addSector('typography', {
            name: 'Typography',
            open: false,
            properties: this.getTypographyProperties()
        });
        
        sm.addSector('appearance', {
            name: 'Appearance',
            open: false,
            properties: this.getAppearanceProperties()
        });
        
        sm.addSector('spacing', {
            name: 'Spacing',
            open: false,
            properties: this.getSpacingProperties()
        });
    }
    
    getLayoutProperties() {
        return [
            'display',
            'position',
            'top',
            'right',
            'bottom',
            'left',
            'width',
            'height',
            'max-width',
            'min-height',
            'float',
            'clear'
        ];
    }
    
    getTypographyProperties() {
        return [
            {
                property: 'font-family',
                type: 'select',
                defaults: 'Arial, sans-serif',
                options: [
                    {value: 'Arial, sans-serif', name: 'Arial'},
                    {value: 'Georgia, serif', name: 'Georgia'},
                    {value: 'Times New Roman, serif', name: 'Times New Roman'},
                    {value: 'Helvetica, sans-serif', name: 'Helvetica'},
                    {value: 'Verdana, sans-serif', name: 'Verdana'}
                ]
            },
            'font-size',
            'font-weight',
            'line-height',
            'letter-spacing',
            'text-align',
            'text-decoration',
            'color'
        ];
    }
    
    getAppearanceProperties() {
        return [
            'background-color',
            'background-image',
            'background-size',
            'background-position',
            'border',
            'border-radius',
            'box-shadow',
            'opacity'
        ];
    }
    
    getSpacingProperties() {
        return [
            'margin',
            'margin-top',
            'margin-right', 
            'margin-bottom',
            'margin-left',
            'padding',
            'padding-top',
            'padding-right',
            'padding-bottom',
            'padding-left'
        ];
    }
    
    setupColorPalette() {
        // Add custom color palette
        this.editor.StyleManager.addProperty('appearance', {
            property: 'background-color',
            type: 'color',
            defaults: '#ffffff',
            colors: [
                '#ffffff', '#f8f9fa', '#e9ecef', '#dee2e6',
                '#0d6efd', '#6610f2', '#6f42c1', '#d63384',
                '#dc3545', '#fd7e14', '#ffc107', '#198754',
                '#20c997', '#0dcaf0', '#495057', '#212529'
            ]
        });
    }
    
    setupTypographySystem() {
        // Add typography presets
        this.editor.StyleManager.addProperty('typography', {
            property: 'font-preset',
            type: 'select',
            options: [
                {value: 'heading-1', name: 'Heading 1'},
                {value: 'heading-2', name: 'Heading 2'},
                {value: 'heading-3', name: 'Heading 3'},
                {value: 'body-text', name: 'Body Text'},
                {value: 'small-text', name: 'Small Text'}
            ],
            onChange: this.applyTypographyPreset.bind(this)
        });
    }
    
    applyTypographyPreset(property, value) {
        const presets = {
            'heading-1': {
                'font-size': '2.5rem',
                'font-weight': '700',
                'line-height': '1.2'
            },
            'heading-2': {
                'font-size': '2rem',
                'font-weight': '600', 
                'line-height': '1.3'
            },
            'heading-3': {
                'font-size': '1.75rem',
                'font-weight': '600',
                'line-height': '1.4'
            },
            'body-text': {
                'font-size': '1rem',
                'font-weight': '400',
                'line-height': '1.5'
            },
            'small-text': {
                'font-size': '0.875rem',
                'font-weight': '400',
                'line-height': '1.4'
            }
        };
        
        const preset = presets[value];
        if (preset && this.editor.getSelected()) {
            Object.entries(preset).forEach(([prop, val]) => {
                this.editor.getSelected().addStyle({[prop]: val});
            });
        }
    }
}
```

---

## Phase 4: Testing & Polish (Weeks 11-12)

### Step 4.1: Comprehensive Testing

**Automated Testing Suite:**
```php
<?php
// tests/Feature/GrapesJSIntegrationTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Page;
use App\Models\Widget;
use App\Models\PageSectionWidget;

class GrapesJSIntegrationTest extends TestCase
{
    /** @test */
    public function it_can_convert_page_to_grapesjs_format()
    {
        $page = Page::factory()->withSections()->create();
        
        $response = $this->get("/admin/api/live-designer/page-content/{$page->id}");
        
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'html',
                'components',
                'styles',
                'assets'
            ]
        ]);
    }
    
    /** @test */
    public function it_can_sync_grapesjs_data_back_to_cms()
    {
        $page = Page::factory()->create();
        $grapesData = [
            'components' => [
                [
                    'type' => 'cms-section',
                    'section_id' => 1,
                    'components' => [
                        [
                            'type' => 'cms-widget-text',
                            'widget_instance_id' => 1,
                            'settings' => ['title' => 'New Title']
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->post("/admin/api/live-designer/save-page/{$page->id}", $grapesData);
        
        $response->assertOk();
        $this->assertDatabaseHas('page_section_widgets', [
            'id' => 1,
            'settings->title' => 'New Title'
        ]);
    }
    
    /** @test */
    public function it_handles_widget_updates_correctly()
    {
        $widget = PageSectionWidget::factory()->create();
        $newSettings = ['color' => 'blue', 'size' => 'large'];
        
        $response = $this->post("/admin/api/live-designer/update-widget/{$widget->id}", [
            'settings' => $newSettings
        ]);
        
        $response->assertOk();
        $widget->refresh();
        
        $this->assertEquals('blue', $widget->settings['color']);
        $this->assertEquals('large', $widget->settings['size']);
    }
    
    /** @test */
    public function it_preserves_data_integrity_during_sync()
    {
        // Test that malformed GrapeJS data doesn't corrupt CMS data
        $page = Page::factory()->withSections()->create();
        $originalWidgetCount = $page->sections->sum(fn($s) => $s->pageSectionWidgets->count());
        
        $malformedData = [
            'components' => [
                ['invalid' => 'data']
            ]
        ];
        
        $response = $this->post("/admin/api/live-designer/save-page/{$page->id}", $malformedData);
        
        $response->assertStatus(422);
        
        $page->refresh();
        $newWidgetCount = $page->sections->sum(fn($s) => $s->pageSectionWidgets->count());
        
        $this->assertEquals($originalWidgetCount, $newWidgetCount);
    }
}
```

**Performance Testing:**
```javascript
// tests/js/grapesjs-performance.test.js
describe('GrapeJS Performance Tests', () => {
    let editor;
    
    beforeEach(() => {
        editor = grapesjs.init({
            container: '#editor',
            // ... config
        });
    });
    
    test('loads page with 50+ widgets in under 3 seconds', async () => {
        const startTime = performance.now();
        
        await loadPageWithManyWidgets(50);
        
        const loadTime = performance.now() - startTime;
        expect(loadTime).toBeLessThan(3000);
    });
    
    test('widget updates complete in under 500ms', async () => {
        const widget = createTestWidget();
        const startTime = performance.now();
        
        await updateWidgetSettings(widget, { title: 'New Title' });
        
        const updateTime = performance.now() - startTime;
        expect(updateTime).toBeLessThan(500);
    });
    
    test('memory usage stays under 100MB during extended use', async () => {
        const initialMemory = performance.memory.usedJSHeapSize;
        
        // Simulate 1 hour of editing
        for (let i = 0; i < 100; i++) {
            await simulateEditingActions();
        }
        
        const finalMemory = performance.memory.usedJSHeapSize;
        const memoryIncrease = finalMemory - initialMemory;
        
        expect(memoryIncrease).toBeLessThan(100 * 1024 * 1024); // 100MB
    });
});
```

---

## Risk Assessment & Mitigation

### High Risk Areas

1. **Data Corruption Risk**
   - **Risk:** GrapeJS data sync corrupts existing Page/Widget data
   - **Mitigation:** Comprehensive transaction handling, backup before sync, rollback capability

2. **Performance Issues**
   - **Risk:** Large pages become unusably slow
   - **Mitigation:** Lazy loading, component virtualization, memory management

3. **Asset Conflicts**
   - **Risk:** Theme CSS conflicts with GrapeJS styles
   - **Mitigation:** CSS isolation, namespace prefixing, careful asset ordering

4. **Maintenance Complexity**
   - **Risk:** Future GrapeJS updates break custom components
   - **Mitigation:** Version pinning, comprehensive test coverage, abstraction layers

### Deployment Strategy

1. **Feature Flag Implementation**
2. **Gradual Rollout** (5% → 25% → 100% of users)
3. **Rollback Plan** ready at each stage
4. **24/7 Monitoring** during rollout
5. **User Training** and documentation

---

## Final Assessment

### Why This is High Risk:

1. **Complexity:** 8-12 weeks of development with many moving parts
2. **Data Risk:** Potential for corrupting existing content
3. **Maintenance:** Ongoing complexity with GrapeJS updates
4. **Performance:** Heavy client-side framework + server-side rendering
5. **Testing:** Difficult to test all edge cases and integrations

### When to Consider This Approach:

- You have 3+ months for development
- You have experienced JavaScript developers
- You can afford potential downtime during deployment
- Your users specifically require drag-and-drop editing
- You have resources for ongoing maintenance

### Recommendation:

**Unless you have specific business requirements for drag-and-drop editing, the Simplified Live Preview approach is strongly recommended.** It delivers 90% of the benefits with 30% of the effort and risk.