# Widget Drill-Down Implementation Plan

## Overview
Implement a 3-level drill-down navigation in the Live Designer left sidebar for theme widgets:
1. **Widgets** → 2. **Content Types** → 3. **Content Items**

## Files to Modify

### 1. Controller Changes
**File:** `app/Http/Controllers/Admin/LiveDesignerViewController.php`

#### Remove Category Grouping Functions
- Remove `groupWidgetsByFunctionality()`
- Remove `determineWidgetCategory()`
- Update `loadThemeWidgets()` to return flat array

#### Updated loadThemeWidgets() Method (Keep Current Structure + Add Content Info)
```php
private function loadThemeWidgets()
{
    $activeTheme = \App\Models\Theme::where('is_active', true)->first();
    
    if (!$activeTheme) {
        return [];
    }

    $widgets = \App\Models\Widget::where('theme_id', $activeTheme->id)
        ->with(['contentTypes', 'fieldDefinitions'])
        ->orderBy('name')
        ->get(['id', 'name', 'description', 'icon', 'theme_id', 'slug', 'view_path']);

    // Return flat array (no category grouping) but keep current preview structure
    return $widgets->map(function($widget) use ($activeTheme) {
        return [
            'id' => $widget->id,
            'name' => $widget->name,
            'description' => $widget->description,
            'icon' => $widget->icon ?? 'ri-puzzle-line',
            'preview_image' => $this->getWidgetPreviewImage($widget, $activeTheme),
            'slug' => $widget->slug,
            'has_content_types' => $widget->contentTypes->count() > 0,
            'content_types_count' => $widget->contentTypes->count(),
            'content_types' => $widget->contentTypes->map(function($ct) {
                return [
                    'id' => $ct->id,
                    'name' => $ct->name,
                    'slug' => $ct->slug,
                    'icon' => $ct->icon ?? 'ri-file-list-line',
                    'items_count' => $ct->contentItems()->count()
                ];
            })
        ];
    })->toArray();
}
```

### 2. API Endpoints
**File:** `routes/admin.php`

Add new routes:
```php
Route::get('/api/content-types/{contentType}/items', [LiveDesignerViewController::class, 'getContentItems']);
```

**File:** `app/Http/Controllers/Admin/LiveDesignerViewController.php`

Add new method:
```php
public function getContentItems(\App\Models\ContentType $contentType, Request $request)
{
    $items = $contentType->contentItems()
        ->with(['fieldValues.field'])
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    return response()->json([
        'success' => true,
        'data' => [
            'content_type' => [
                'id' => $contentType->id,
                'name' => $contentType->name,
                'slug' => $contentType->slug
            ],
            'items' => $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'excerpt' => \Str::limit($item->content ?? $item->description ?? '', 60),
                    'created_at' => $item->created_at?->format('M j, Y'),
                    'thumbnail' => $this->getItemThumbnail($item)
                ];
            })
        ]
    ]);
}

private function getItemThumbnail($item): ?string
{
    if (!$item->fieldValues || $item->fieldValues->isEmpty()) {
        return null;
    }

    $imageField = $item->fieldValues->first(function($fv) {
        return $fv->field && 
               in_array($fv->field->field_type ?? '', ['image', 'file']) && 
               $fv->value &&
               str_contains($fv->value, 'image');
    });
    
    return $imageField && $imageField->value ? asset($imageField->value) : null;
}
```

### 3. Left Sidebar View Updates
**File:** `resources/views/admin/pages/live-designer/components/left-sidebar.blade.php`

#### Update Theme Widgets Section (Keep Current Preview Structure)
```blade
<!-- Theme Widgets Category -->
<div class="nav-item">
    <a class="nav-link d-flex align-items-center collapsed" 
       data-bs-toggle="collapse" 
       href="#themeWidgetsCollapse" 
       role="button" 
       aria-expanded="false" 
       aria-controls="themeWidgetsCollapse">
        <i class="ri-palette-line nav-icon me-2"></i>
        <span class="nav-text">Theme Widgets</span>
        <i class="ri-arrow-down-s-line ms-auto collapse-icon"></i>
    </a>
    <div class="collapse" id="themeWidgetsCollapse">
        <div class="nav-item-content p-2">
            <!-- Drill-down container -->
            <div id="widgetDrillDownContainer">
                
                <!-- Breadcrumb Navigation -->
                <div class="drill-down-breadcrumb" id="drillDownBreadcrumb" style="display: none;">
                    <button class="btn btn-sm btn-outline-secondary back-btn" id="backButton">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <span class="breadcrumb-text" id="breadcrumbText"></span>
                </div>

                <!-- Widgets View (Default) - Keep Current Grid Structure -->
                <div class="widgets-view" id="widgetsView">
                    <div class="component-grid" id="themeWidgetsGrid">
                        @if(isset($themeWidgets) && count($themeWidgets) > 0)
                            @foreach($themeWidgets as $widget)
                                <div class="theme-widget-item-wrapper" data-widget-id="{{ $widget['id'] }}">
                                    
                                    <!-- Drill-down button (only if has content types) -->
                                    @if($widget['has_content_types'])
                                        <button class="drill-down-btn expand-content-types-btn" 
                                                data-widget-id="{{ $widget['id'] }}"
                                                title="View {{ $widget['content_types_count'] }} content types">
                                            <i class="ri-arrow-right-line"></i>
                                            <span class="content-count">{{ $widget['content_types_count'] }}</span>
                                        </button>
                                    @endif
                                    
                                    <!-- Keep existing widget item structure -->
                                    @if($widget['preview_image'])
                                        <div class="theme-widget-item" 
                                             data-widget-id="{{ $widget['id'] }}" 
                                             data-widget-slug="{{ $widget['slug'] }}"
                                             draggable="true"
                                             title="{{ $widget['description'] }}">
                                            <div class="widget-preview">
                                                <img src="{{ $widget['preview_image'] }}" alt="{{ $widget['name'] }}" 
                                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'{{ $widget['icon'] }}\'></i>';">
                                            </div>
                                            <div class="widget-title">{{ $widget['name'] }}</div>
                                        </div>
                                    @else
                                        <div class="component-item" 
                                             data-widget-id="{{ $widget['id'] }}" 
                                             data-widget-slug="{{ $widget['slug'] }}"
                                             draggable="true"
                                             title="{{ $widget['description'] }}">
                                            <i class="{{ $widget['icon'] }}"></i>
                                            <div class="label">{{ $widget['name'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center p-3">
                                <i class="ri-palette-line text-muted mb-2" style="font-size: 2rem;"></i>
                                <div class="text-muted small">No theme widgets available</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Content Types View -->
                <div class="content-types-view" id="contentTypesView" style="display: none;">
                    <div class="content-types-list" id="contentTypesList">
                        <!-- Content types will be populated here -->
                    </div>
                </div>

                <!-- Content Items View -->
                <div class="content-items-view" id="contentItemsView" style="display: none;">
                    <div class="content-items-list" id="contentItemsList">
                        <!-- Content items will be populated here -->
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
```

### 4. JavaScript Implementation
**File:** `public/assets/admin/js/live-designer/widget-drill-down.js` (NEW)

```javascript
class WidgetDrillDown {
    constructor() {
        this.currentView = 'widgets'; // 'widgets' | 'content-types' | 'content-items'
        this.currentWidget = null;
        this.currentContentType = null;
        this.widgetData = null;
        
        this.container = document.getElementById('widgetDrillDownContainer');
        this.breadcrumb = document.getElementById('drillDownBreadcrumb');
        this.breadcrumbText = document.getElementById('breadcrumbText');
        this.backButton = document.getElementById('backButton');
        
        this.widgetsView = document.getElementById('widgetsView');
        this.contentTypesView = document.getElementById('contentTypesView');
        this.contentItemsView = document.getElementById('contentItemsView');
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadWidgetData();
    }
    
    setupEventListeners() {
        // Expand content types
        document.addEventListener('click', (e) => {
            if (e.target.closest('.expand-content-types-btn')) {
                const widgetId = e.target.closest('.expand-content-types-btn').dataset.widgetId;
                this.showContentTypes(widgetId);
            }
        });
        
        // Expand content items
        document.addEventListener('click', (e) => {
            if (e.target.closest('.expand-content-items-btn')) {
                const contentTypeId = e.target.closest('.expand-content-items-btn').dataset.contentTypeId;
                this.showContentItems(contentTypeId);
            }
        });
        
        // Back button
        this.backButton.addEventListener('click', () => {
            this.navigateBack();
        });
    }
    
    loadWidgetData() {
        // Extract widget data from server-rendered content
        this.widgetData = {};
        document.querySelectorAll('.widget-item').forEach(item => {
            const widgetId = item.dataset.widgetId;
            const hasContent = item.dataset.hasContent === 'true';
            
            if (hasContent) {
                // Widget data is embedded in the page, extract it
                // This would be populated from the server-side data
                this.widgetData[widgetId] = {
                    id: widgetId,
                    name: item.querySelector('.widget-name').textContent,
                    contentTypes: [] // This would be populated from server data
                };
            }
        });
    }
    
    showContentTypes(widgetId) {
        this.currentView = 'content-types';
        this.currentWidget = widgetId;
        
        // Get widget data (from server-rendered data)
        const widget = this.getWidgetById(widgetId);
        
        if (!widget || !widget.content_types) {
            console.error('Widget or content types not found');
            return;
        }
        
        // Update breadcrumb
        this.updateBreadcrumb(`${widget.name} > Content Types`);
        
        // Render content types
        this.renderContentTypes(widget.content_types);
        
        // Switch views
        this.switchToView('content-types');
    }
    
    async showContentItems(contentTypeId) {
        this.currentView = 'content-items';
        this.currentContentType = contentTypeId;
        
        try {
            // Load content items via API
            const response = await fetch(`/admin/api/content-types/${contentTypeId}/items`);
            const data = await response.json();
            
            if (data.success) {
                // Update breadcrumb
                const widget = this.getWidgetById(this.currentWidget);
                this.updateBreadcrumb(`${widget.name} > ${data.data.content_type.name} > Items`);
                
                // Render content items
                this.renderContentItems(data.data.items);
                
                // Switch views
                this.switchToView('content-items');
            }
        } catch (error) {
            console.error('Failed to load content items:', error);
        }
    }
    
    renderContentTypes(contentTypes) {
        const html = contentTypes.map(ct => `
            <div class="content-type-item mb-2" data-content-type-id="${ct.id}">
                <div class="content-type-card border rounded p-2">
                    <div class="d-flex align-items-center">
                        <i class="${ct.icon} me-2 text-primary"></i>
                        <div class="flex-grow-1">
                            <div class="content-type-name fw-bold">${ct.name}</div>
                            <small class="text-muted">${ct.items_count} items</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary expand-content-items-btn" 
                                data-content-type-id="${ct.id}">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        document.getElementById('contentTypesList').innerHTML = html;
    }
    
    renderContentItems(items) {
        const html = items.map(item => `
            <div class="content-item mb-2" 
                 data-content-item-id="${item.id}"
                 draggable="true">
                <div class="content-item-card border rounded p-2">
                    <div class="d-flex align-items-center">
                        ${item.thumbnail ? 
                            `<img src="${item.thumbnail}" class="content-thumbnail me-2" style="width: 32px; height: 32px; object-fit: cover;">` :
                            `<i class="ri-file-text-line me-2 text-muted"></i>`
                        }
                        <div class="flex-grow-1">
                            <div class="content-title fw-bold">${item.title}</div>
                            <small class="text-muted">${item.excerpt}</small>
                            <div class="content-meta">
                                <small class="text-muted">${item.created_at}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        document.getElementById('contentItemsList').innerHTML = html;
    }
    
    navigateBack() {
        if (this.currentView === 'content-items') {
            this.showContentTypes(this.currentWidget);
        } else if (this.currentView === 'content-types') {
            this.backToWidgets();
        }
    }
    
    backToWidgets() {
        this.currentView = 'widgets';
        this.currentWidget = null;
        this.currentContentType = null;
        
        this.hideBreadcrumb();
        this.switchToView('widgets');
    }
    
    switchToView(viewName) {
        // Hide all views
        this.widgetsView.style.display = 'none';
        this.contentTypesView.style.display = 'none';
        this.contentItemsView.style.display = 'none';
        
        // Show target view
        switch(viewName) {
            case 'widgets':
                this.widgetsView.style.display = 'block';
                break;
            case 'content-types':
                this.contentTypesView.style.display = 'block';
                break;
            case 'content-items':
                this.contentItemsView.style.display = 'block';
                break;
        }
    }
    
    updateBreadcrumb(text) {
        this.breadcrumbText.textContent = text;
        this.breadcrumb.style.display = 'block';
    }
    
    hideBreadcrumb() {
        this.breadcrumb.style.display = 'none';
    }
    
    getWidgetById(widgetId) {
        // This would extract widget data from the server-rendered content
        // For now, return mock data structure
        return this.widgetData[widgetId] || null;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.widgetDrillDown = new WidgetDrillDown();
});
```

### 5. Include JavaScript in Main View
**File:** `resources/views/admin/pages/live-designer/show.blade.php`

Add before closing `</body>` tag:
```blade
<script src="{{ asset('assets/admin/js/live-designer/widget-drill-down.js') }}"></script>
```

### 6. CSS Styles
**File:** `public/assets/admin/css/live-designer.css`

Add styles:
```css
/* Widget Drill-Down Styles */
.drill-down-breadcrumb {
    margin-bottom: 1rem;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Widget wrapper with drill-down button */
.theme-widget-item-wrapper {
    position: relative;
    display: inline-block;
}

.drill-down-btn {
    position: absolute;
    top: 4px;
    left: 4px;
    z-index: 10;
    background: rgba(13, 110, 253, 0.9);
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

.drill-down-btn:hover {
    background: #0d6efd;
    transform: scale(1.1);
}

.drill-down-btn .content-count {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 0.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Keep existing widget styles but add hover for expandable widgets */
.theme-widget-item-wrapper:has(.drill-down-btn) .theme-widget-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.content-type-card,
.content-item-card {
    cursor: pointer;
    transition: all 0.2s ease;
}

.content-type-card:hover,
.content-item-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.content-thumbnail {
    border-radius: 0.25rem;
}

/* View transitions */
.widgets-view,
.content-types-view,
.content-items-view {
    transition: opacity 0.3s ease;
}
```

## Implementation Steps

1. **Update Controller** - Remove category grouping, update `loadThemeWidgets()`
2. **Add API Endpoint** - Create content items endpoint
3. **Update Sidebar View** - Replace theme widgets section with drill-down structure
4. **Create JavaScript** - Implement `WidgetDrillDown` class
5. **Add CSS Styles** - Style the drill-down interface
6. **Include Scripts** - Add JavaScript to main view
7. **Test Navigation** - Verify all drill-down levels work correctly

## Expected Behavior

- **Level 1:** Show all theme widgets with expand buttons for content-enabled widgets
- **Level 2:** Show content types for selected widget with item counts
- **Level 3:** Show content items for selected content type with thumbnails
- **Navigation:** Breadcrumb navigation with back button functionality
- **State Management:** Clean transitions between views with proper state tracking
