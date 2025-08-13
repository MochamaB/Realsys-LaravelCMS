# Visual Page Builder Implementation Plan
## Page Builder Live Preview System - Preserving Frontend Rendering

### 🚨 Executive Summary

This document outlines the implementation plan for a visual page builder system that creates a **separate live preview environment** for page editing while **preserving the existing frontend rendering system**. The plan focuses on building new page builder-specific components that leverage our proven **Widget Preview Isolation System** without modifying any frontend rendering logic.

---

## 🎯 Core Implementation Principle

**CRITICAL RULE: NO FRONTEND RENDERING MODIFICATIONS**
- **Frontend rendering stays untouched** - it works perfectly as-is
- **Page builder operates in isolated preview environment** - separate from frontend
- **All new code is page builder-specific** - no impact on existing theme rendering
- **Focus on rendering first, editing second** - establish solid preview foundation before adding editing capabilities

---

## 📊 Current System Analysis

### ✅ Systems That Work (DO NOT MODIFY)

#### **Frontend Rendering Pipeline (PRESERVE)**
- **TemplateRenderer Service**: Page and section rendering for frontend
- **Theme System**: Asset loading and view resolution for public pages
- **Widget Rendering**: Frontend widget display in themes
- **Section Rendering**: Template sections rendered on public pages

#### **Widget Preview System (EXTEND)**
- **WidgetPreviewFrontendController**: Isolated widget rendering in admin
- **UniversalPreviewManager**: JavaScript framework for preview management
- **Iframe isolation**: Accurate preview rendering with proper assets
- **Content integration**: Content-widget mapping and filtering

#### **Database Schema (UTILIZE)**
- **PageSection Model**: GridStack positioning fields (`grid_x`, `grid_y`, `grid_w`, `grid_h`)
- **Styling Fields**: CSS classes, background colors, padding, margins
- **Widget Relationships**: Section-widget associations with settings
- **Template Structure**: Template-section hierarchy

### ❌ What Needs Implementation (NEW CODE ONLY)

#### **Page Builder Preview System**
- **Separate preview rendering** for page builder interface
- **GridStack-aware preview** that reads database positioning
- **Live preview updates** during page building
- **Component palette integration** for drag-drop

#### **Page Builder Interface**
- **Visual editor UI** with GridStack integration
- **Component management** for widgets and content items
- **Real-time preview canvas** using iframe isolation
- **Property panels** for editing components

---

## 🏗️ Implementation Architecture

### **Dual Rendering Strategy**

#### **Frontend Rendering (UNCHANGED)**
```
Page Request → TemplateRenderer → Theme Views → Public Output
```
- **No modifications** to existing rendering pipeline
- **Frontend continues** to work exactly as before
- **Theme compatibility** maintained across all themes

#### **Page Builder Preview Rendering (NEW)**
```
Page Builder → PageBuilderRenderer → GridStack Preview → Admin Preview Output
```
- **Separate rendering pipeline** for page builder
- **Reads same database** but renders for editing interface
- **Uses GridStack positioning** from database fields
- **Isolated from frontend** rendering logic

### **Component Integration Logic**

#### **Widget Integration (EXTEND EXISTING)**
- **Reuse WidgetPreviewFrontendController** patterns for page builder context
- **Same iframe isolation** approach for accurate widget preview
- **Same asset loading** logic for theme and widget CSS/JS
- **Same content integration** for content-driven widgets

#### **Content Item Integration (NEW)**
- **Content items as components** using widget rendering patterns
- **Same isolation approach** as widgets for consistency
- **Content component templates** (card, list, featured, carousel)
- **Unified preview management** with existing UniversalPreviewManager

---

## 📋 Implementation Phases

### **Phase 1: Page Builder Preview Foundation**
**Priority: CRITICAL - Establish Rendering Before Editing**

#### **Objective**: Create page builder preview system that renders pages with GridStack positioning

#### **Core Logic**:
1. **Read page structure** from database (Page → PageSections → Widgets)
2. **Apply GridStack positioning** from PageSection fields (`grid_x`, `grid_y`, `grid_w`, `grid_h`)
3. **Render components** using existing widget isolation approach
4. **Generate preview HTML** with GridStack structure and styling
5. **Display in iframe** for accurate preview (same as widget preview)

#### **Key Components (MAXIMUM CODE REUSE)**:
- **PreviewController** (EXTEND EXISTING): Add `renderPage()` method to existing controller
- **TemplateRenderer** (USE AS-IS): Already handles complete page rendering pipeline
- **GridStackService** (CREATE MINIMAL): Only positioning and styling logic
- **Theme section views** (ENHANCE EXISTING): Add GridStack attributes to existing Blade templates

### **Phase 2: Widget Content Integration**
**Priority: HIGH - Dynamic Content Display**

#### **Objective**: Integrate widget content rendering system with page builder for dynamic content display

#### **Core Logic**:
1. **Widget-content relationships** use existing PageSectionWidget.content_query system
2. **Content query execution** enhances existing Widget.render() method
3. **Widget template enhancement** adds content support to existing theme templates
4. **Content-aware asset loading** extends existing widget asset system
5. **Automatic content integration** through existing pivot table relationships

#### **Key Components (ENHANCE EXISTING SYSTEMS)**:
- **WidgetService** (ENHANCE): Improve content query execution in existing methods
- **Widget templates** (ENHANCE): Add content data support to existing theme widget templates
- **Widget assets** (ENHANCE): Extend existing widget asset system for content display
- **PageSectionWidget pivot** (USE AS-IS): Existing content_query and settings fields

### **Phase 3: Page Builder Interface**
**Priority: HIGH - Visual Editor Implementation**

#### **Objective**: Create visual page builder interface with drag-drop and live preview using existing GridStack UI

#### **Core Logic**:
1. **Existing GridStack UI** - Reuse complete admin interface already built
2. **Canvas iframe integration** - Use existing canvas area for page builder preview
3. **Widget library integration** - Connect existing left sidebar to new PreviewController methods
4. **Real-time component updates** - Use Option B individual rendering for instant feedback
5. **Property panels** - Extend existing right sidebar and modal system

#### **Key Components (REUSE EXISTING GRIDSTACK UI)**:
- **gridstack-designer.blade.php** (USE AS-IS): Main page builder interface already exists
- **Canvas area** (ENHANCE): Connect to new renderPageBuilder() method via iframe
- **Left sidebar** (ENHANCE): Connect widget library to new page builder APIs
- **Right sidebar** (ENHANCE): Add page builder properties to existing panel
- **Modal system** (EXTEND): Use existing widget configuration modals

### **Phase 4: Advanced Editing Features**
**Priority: MEDIUM - Enhanced User Experience**

#### **Objective**: Add advanced page builder features using existing infrastructure

#### **Core Logic**:
1. **Individual component editing** - Use Option B rendering for granular updates
2. **Real-time content updates** - Leverage widget-content integration for dynamic content
3. **Section management** - Use existing GridStack API endpoints for positioning
4. **Responsive preview** - Extend existing device preview controls
5. **Save workflow** - Use existing GridStack database update APIs

#### **Key Components (EXTEND EXISTING INFRASTRUCTURE)**:
- **Option B rendering** (USE): Individual section/widget updates without full page reload
- **Existing GridStack APIs** (USE): Section positioning and widget management already exist
- **UniversalPreviewManager** (ENHANCE): Add page builder support to existing framework
- **Existing modal system** (EXTEND): Widget configuration and content selection already built
- **Existing responsive controls** (ENHANCE): Device preview already exists in admin interface

---

## 🔧 Technical Implementation Details

### **Page Builder Preview Rendering Logic (OPTION B: INDIVIDUAL COMPONENTS)**

#### **Step 1: Individual Component Assembly (NEW APPROACH)**
```
PreviewController.renderPageBuilder() → TemplateRenderer.renderSectionById() → Individual Sections
```
- **Option B approach** - build pages from individual sections for granular control
- **TemplateRenderer.renderSectionById()** already exists and supports individual section rendering
- **UniversalStylingService** already provides GridStack attributes
- **Assemble complete page** from individual rendered sections

#### **Step 2: GridStack Integration (USE EXISTING)**
```
UniversalStylingService.buildGridAttributes() → GridStack Data Attributes
```
- **UniversalStylingService already exists** with complete GridStack functionality
- **No new GridStackService needed** - all functionality already implemented
- **GridStack attributes** automatically applied to sections and widgets
- **Database positioning** already stored in PageSection and PageSectionWidget models

#### **Step 3: Widget Content Integration (ENHANCE EXISTING)**
```
Widget.render() + content_query execution → Content-Aware Widgets
```
- **Existing widget rendering** enhanced with improved content query execution
- **PageSectionWidget.content_query** already exists for content filtering
- **Widget templates** enhanced to display content data
- **Content-aware asset loading** extends existing widget asset system

#### **Step 4: Preview Assembly (EXTEND EXISTING)**
```
TemplateRenderer Output + GridStack Enhancement + Existing Assets → Page Builder Preview
```
- **TemplateRenderer provides** complete page HTML structure
- **GridStackService enhances** with positioning attributes
- **Existing asset collection** includes all necessary CSS/JS
- **Same iframe display** as current widget preview system

### **Component Integration Strategy (MAXIMUM REUSE)**

#### **Widget Components (100% REUSE EXISTING)**
- **PreviewController.renderWidget()** - already implemented and working
- **WidgetService.collectWidgetAssets()** - existing asset loading
- **Existing content integration** - content-widget mapping already works
- **Proven iframe isolation** - same approach for page builder

#### **Content Components (EXTEND EXISTING PATTERNS)**
- **PreviewController.renderContent()** - already implemented for content items
- **Same WidgetService patterns** - extend existing service methods
- **Existing template resolution** - reuse theme view system
- **Same asset management** - no new asset loading logic needed

#### **Page Components (NEW MINIMAL LOGIC)**
- **PreviewController.renderPage()** - new method using existing TemplateRenderer
- **GridStackService** - minimal service for positioning only
- **Enhanced section views** - add GridStack attributes to existing templates
- **Same preview patterns** - iframe isolation and asset loading

---

## 💡 Strategic Benefits

### **Frontend Preservation**
- **Zero impact** on existing frontend rendering
- **TemplateRenderer unchanged** for public pages
- **Existing themes work** exactly as before
- **Performance maintained**

### **Maximum Code Reuse (98%)**:
- **PreviewController**: Add 4 methods (renderPageBuilder, renderPageSection, renderSectionWidget, getPageStructure)
- **TemplateRenderer**: Use existing renderSectionById() method as-is
- **UniversalStylingService**: Use existing GridStack methods as-is
- **Widget templates**: Enhance existing theme templates with content support
- **GridStack UI**: Reuse complete existing admin interface
- **Widget-content system**: Use existing PageSectionWidget pivot relationships

### **Proven Architecture Foundation**
- **Same patterns** as successful widget preview system
- **Same error handling** and response formats
- **Same asset loading** mechanisms proven to work
- **Same iframe isolation** approach for accuracy

### **Minimal Risk Implementation**
- **Extend existing working code** vs creating new systems
- **Proven rendering pipeline** vs experimental approaches
- **Existing database methods** vs new data access patterns
- **Incremental enhancement** vs complete system replacement

---

## 🔄 Implementation Sequence (LEVERAGING EXISTING CODE)

### **Week 1: Preview Foundation (EXTEND EXISTING)**
1. **PreviewController.renderPage()** - Add method to existing controller
2. **GridStackService** - Create minimal positioning service
3. **Enhance section views** - Add GridStack attributes to existing templates
4. **Test page preview** - Verify existing TemplateRenderer works with enhancements

### **Week 2: Component Integration (REUSE PATTERNS)**
1. **Extend PreviewController** - Add content component methods
2. **Enhance WidgetService** - Extend existing service for content components
3. **Reuse asset management** - Same CSS/JS loading for all components
4. **Test unified rendering** - Verify existing patterns work for all component types

### **Week 3: Visual Interface (BUILD ON FOUNDATION)**
1. **Extend admin layout** - Add page builder interface to existing layout
2. **Enhance UniversalPreviewManager** - Add page builder support to existing JavaScript
3. **Create component palette** - Use existing widget discovery and content APIs
4. **Test visual editing** - Ensure existing preview system works with page builder

### **Week 4: Advanced Features (EXTEND PROVEN PATTERNS)**
1. **Enhance UniversalPreviewManager** - Add inline editing to existing framework
2. **Reuse modal system** - Same editing patterns for all components
3. **Use existing PageSection methods** - Leverage database methods for save system
4. **Test complete workflow** - End-to-end page building with existing infrastructure

This plan ensures we build a robust page builder system that leverages our successful widget preview architecture while maintaining complete separation from the working frontend rendering system.
// resources/views/components/universal-section.blade.php
@php
use App\Services\UniversalStylingService;

$stylingService = app(UniversalStylingService::class);
$sectionClasses = $stylingService->buildSectionClasses($pageSection);
$sectionStyles = $stylingService->buildSectionStyles($pageSection);
$gridAttributes = $stylingService->buildGridAttributes($pageSection);
@endphp

<section class="{{ $sectionClasses }}" 
         id="section-{{ $pageSection->id }}"
         {!! $sectionStyles ? 'style="' . $sectionStyles . '"' : '' !!}
         data-section-id="{{ $pageSection->id }}"
         data-section-type="{{ $pageSection->templateSection->section_type ?? 'default' }}"
         {!! $gridAttributes !!}>
    
    {{-- Theme-specific content --}}
    <div class="section-content">
        {{ $slot }}
    </div>
    
</section>
```

**1.1.2 Create Universal Styling Service**
```php
// app/Services/UniversalStylingService.php
<?php

namespace App\Services;

use App\Models\PageSection;
use App\Models\PageSectionWidget;

class UniversalStylingService
{
    public function buildSectionClasses(PageSection $pageSection): string
    {
        return collect([
            'cms-section',
            'section-' . ($pageSection->templateSection->section_type ?? 'default'),
            $pageSection->css_classes
        ])->filter()->implode(' ');
    }

    public function buildSectionStyles(PageSection $pageSection): string
    {
        $styles = [];
        
        if ($pageSection->background_color) {
            $styles[] = "background-color: {$pageSection->background_color}";
        }
        
        if ($pageSection->padding) {
            $padding = is_array($pageSection->padding) 
                ? implode(' ', $pageSection->padding) 
                : $pageSection->padding;
            $styles[] = "padding: {$padding}";
        }
        
        if ($pageSection->margin) {
            $margin = is_array($pageSection->margin)
                ? implode(' ', $pageSection->margin)
                : $pageSection->margin;
            $styles[] = "margin: {$margin}";
        }
        
        return implode('; ', $styles);
    }

    public function buildGridAttributes(PageSection $pageSection): string
    {
        if ($pageSection->grid_x === null) {
            return '';
        }
        
        return sprintf(
            'data-gs-x="%d" data-gs-y="%d" data-gs-w="%d" data-gs-h="%d" data-gs-id="%s"',
            $pageSection->grid_x,
            $pageSection->grid_y,
            $pageSection->grid_w,
            $pageSection->grid_h,
            $pageSection->grid_id
        );
    }

    public function buildWidgetClasses(PageSectionWidget $widget): string
    {
        return collect([
            'cms-widget',
            'widget-' . $widget->widget->slug,
            $widget->css_classes
        ])->filter()->implode(' ');
    }

    public function buildWidgetStyles(PageSectionWidget $widget): string
    {
        $styles = [];
        
        if ($widget->padding) {
            $padding = is_array($widget->padding) 
                ? implode(' ', $widget->padding) 
                : $widget->padding;
            $styles[] = "padding: {$padding}";
        }
        
        if ($widget->margin) {
            $margin = is_array($widget->margin)
                ? implode(' ', $widget->margin)
                : $widget->margin;
            $styles[] = "margin: {$margin}";
        }
        
        if ($widget->min_height) {
            $styles[] = "min-height: {$widget->min_height}";
        }
        
        if ($widget->max_height) {
            $styles[] = "max-height: {$widget->max_height}";
        }
        
        return implode('; ', $styles);
    }

    public function buildWidgetGridAttributes(PageSectionWidget $widget): string
    {
        if ($widget->grid_x === null) {
            return '';
        }
        
        return sprintf(
            'data-gs-x="%d" data-gs-y="%d" data-gs-w="%d" data-gs-h="%d" data-gs-id="%s"',
            $widget->grid_x,
            $widget->grid_y,
            $widget->grid_w,
            $widget->grid_h,
            $widget->grid_id
        );
    }
}
```

#### 1.2 Update TemplateRenderer Service
**File:** `app/Services/TemplateRenderer.php`

**Modifications:**
```php
// Add to renderSection method
protected function renderSection(string $sectionSlug, array $data = []): string
{
    // ... existing code ...
    
    // ✅ ADD: Universal styling data
    $sectionData = array_merge([
        'pageSection' => $pageSection,
        'section' => $pageSection->templateSection,
        'widgets' => $widgetData,
        
        // NEW: Universal styling support
        'universalStyling' => app(UniversalStylingService::class)
    ], $data);
    
    // ... rest of existing code ...
}
```

#### 1.3 Create Universal Widget Wrapper
**File:** `resources/views/components/universal-widget.blade.php` (NEW)

```php
@php
use App\Services\UniversalStylingService;

$stylingService = app(UniversalStylingService::class);
$widgetClasses = $stylingService->buildWidgetClasses($pageSectionWidget);
$widgetStyles = $stylingService->buildWidgetStyles($pageSectionWidget);
$gridAttributes = $stylingService->buildWidgetGridAttributes($pageSectionWidget);
@endphp

<div class="{{ $widgetClasses }}" 
     id="widget-{{ $pageSectionWidget->id }}"
     {!! $widgetStyles ? 'style="' . $widgetStyles . '"' : '' !!}
     data-widget-id="{{ $pageSectionWidget->widget_id }}"
     data-widget-slug="{{ $pageSectionWidget->widget->slug }}"
     {!! $gridAttributes !!}>
    
    {{ $slot }}
    
</div>
```

---

### Phase 2: Theme Integration (Week 2)
**Priority: HIGH**

#### 2.1 Update Miata Theme Section Templates
**Files to Modify:**
- `resources/themes/miata/sections/full-width.blade.php`
- `resources/themes/miata/sections/multi-column.blade.php`
- `resources/themes/miata/sections/sidebar-left.blade.php`

**Updated full-width.blade.php:**
```php
{{-- ✅ NEW VERSION with Universal Styling --}}
<x-universal-section :pageSection="$pageSection">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                {{-- Render widgets for this section --}}
                @if($widgets && count($widgets) > 0)
                    @foreach($widgets as $widget)
                        @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                            <x-universal-widget :pageSectionWidget="$widget['pageSectionWidget']">
                                @include($widget['view_path'], [
                                    'fields' => $widget['fields'] ?? [],
                                    'settings' => $widget['settings'] ?? [],
                                    'widget' => $widget,
                                    'useCustomData' => false
                                ])
                            </x-universal-widget>
                        @endif
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="no-widgets text-center">
                            <em class="text-muted">No widgets assigned to this section</em>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-universal-section>
```

#### 2.2 Update WidgetService Data Passing
**File:** `app/Services/WidgetService.php`

**Modify prepareWidgetData method:**
```php
public function prepareWidgetData(Widget $widget, PageSectionWidget $pageSectionWidget = null): array
{
    // Basic widget data
    $data = [
        'id' => $widget->id,
        'name' => $widget->name,
        'slug' => $widget->slug,
        'view_path' => $this->resolveWidgetViewPath($widget),
        'fields' => $this->getWidgetFieldValues($widget, $pageSectionWidget),
        
        // ✅ ADD: Pass PageSectionWidget instance for universal styling
        'pageSectionWidget' => $pageSectionWidget
    ];
    
    // Add page section widget data if available
    if ($pageSectionWidget) {
        $data['position'] = $pageSectionWidget->position;
        $data['column_position'] = $pageSectionWidget->column_position;
        $data['css_classes'] = $pageSectionWidget->css_classes;
        $data['settings'] = $pageSectionWidget->settings ?? [];
        $data['content_query'] = $pageSectionWidget->content_query ?? [];
        
        // ✅ ADD: Universal styling data
        $data['universal_classes'] = app(UniversalStylingService::class)->buildWidgetClasses($pageSectionWidget);
        $data['universal_styles'] = app(UniversalStylingService::class)->buildWidgetStyles($pageSectionWidget);
        $data['grid_attributes'] = app(UniversalStylingService::class)->buildWidgetGridAttributes($pageSectionWidget);
    }
    
    // ... rest of existing code ...
}
```

---

### Phase 3: Database Enhancements (Week 3)
**Priority: MEDIUM**

#### 3.1 Create Migration for Missing Data
**File:** `database/migrations/2024_01_XX_fix_section_styling_data.php` (NEW)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\PageSection;
use App\Models\PageSectionWidget;

return new class extends Migration
{
    public function up()
    {
        // Populate missing grid IDs for sections
        PageSection::whereNull('grid_id')->each(function ($section) {
            $section->grid_id = $section->generateGridId();
            $section->save();
        });
        
        // Populate missing grid IDs for widgets
        PageSectionWidget::whereNull('grid_id')->each(function ($widget) {
            $widget->grid_id = $widget->generateGridId();
            $widget->save();
        });
        
        // Set default grid positions for sections without them
        PageSection::whereNull('grid_x')->each(function ($section, $index) {
            $section->update([
                'grid_x' => 0,
                'grid_y' => $index * 4,
                'grid_w' => 12,
                'grid_h' => 4
            ]);
        });
        
        // Set default grid positions for widgets without them
        PageSectionWidget::whereNull('grid_x')->each(function ($widget, $index) {
            $widget->update([
                'grid_x' => 0,
                'grid_y' => $index * 2,
                'grid_w' => 6,
                'grid_h' => 2
            ]);
        });
    }

    public function down()
    {
        // Reset grid data if needed
    }
};
```

#### 3.2 Add API Styling Endpoints
**File:** `app/Http/Controllers/Api/PageSectionController.php`

**Add styling update endpoints:**
```php
public function updateStyling(PageSection $section, Request $request)
{
    $validated = $request->validate([
        'css_classes' => 'nullable|string|max:500',
        'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        'padding' => 'nullable|array',
        'margin' => 'nullable|array',
        'grid_x' => 'nullable|integer|min:0|max:11',
        'grid_y' => 'nullable|integer|min:0',
        'grid_w' => 'nullable|integer|min:1|max:12',
        'grid_h' => 'nullable|integer|min:1|max:20',
    ]);
    
    $section->update($validated);
    
    return response()->json([
        'success' => true,
        'section' => $section->fresh()
    ]);
}
```

---

## 🔧 Implementation Timeline

### Week 1: Core Infrastructure
- **Day 1-2**: Create UniversalStylingService and universal components
- **Day 3-4**: Update TemplateRenderer service
- **Day 5**: Test core functionality

### Week 2: Theme Integration  
- **Day 1-2**: Update Miata theme templates
- **Day 3-4**: Update WidgetService data passing
- **Day 5**: Test theme integration

### Week 3: Database Enhancements
- **Day 1-2**: Create migration and validation
- **Day 3-4**: Test data migration
- **Day 5**: Optimize database queries

---

## 🎯 Success Criteria

### Technical Requirements
1. **Universal Styling**: All database styling fields are used in rendering
2. **Grid Integration**: GridStack positions are reflected in output
3. **Theme Agnostic**: Styling works across different themes
4. **Performance**: No significant performance impact
5. **Backward Compatibility**: Existing functionality continues to work

### Visual Requirements
1. **CSS Classes**: Custom CSS classes are applied to sections and widgets
2. **Colors**: Background colors are rendered correctly
3. **Spacing**: Padding and margin values are applied
4. **Positioning**: Grid positions are reflected in layout
5. **Responsiveness**: Styling works across different screen sizes

### Integration Requirements
1. **GridStack**: Visual changes in GridStack are reflected in frontend
2. **GrapesJS**: Visual changes in GrapesJS are reflected in frontend
3. **API**: Styling can be updated via API endpoints
4. **Admin Panel**: Styling can be configured through admin interface

---

## 📋 Testing Checklist

### Section Styling Tests
- [ ] CSS classes are applied to section elements
- [ ] Background colors are rendered correctly
- [ ] Padding values are applied
- [ ] Margin values are applied
- [ ] GridStack positioning attributes are present

### Widget Styling Tests
- [ ] Widget CSS classes are applied
- [ ] Widget padding/margin is rendered
- [ ] Widget min/max height constraints work
- [ ] Widget grid positioning is correct

### Cross-Theme Tests
- [ ] Universal styling works with Miata theme
- [ ] Same page renders consistently across theme switches
- [ ] No styling conflicts between universal and theme CSS

### Performance Tests
- [ ] Page load time impact is minimal
- [ ] Memory usage is acceptable
- [ ] Database query count is optimized

---

This plan provides a comprehensive roadmap to fix the rendering disconnect issues and create a robust foundation for visual page building. The phased approach ensures minimal disruption to existing functionality while systematically addressing all identified problems.