# Widget System Implementation - Detailed Plan

## Overview

This document outlines the detailed implementation plan for the Widget System in the RealsysCMS Laravel application. The Widget System serves as a critical bridge between the Theme System and Content System, allowing dynamic content display within page sections.

## System Architecture

Widgets are the fundamental display components that render content within page sections. They connect theme presentation with content data through a flexible query system.

### Widget Architecture Components

1. **Widget Definition**: Core widget metadata and configuration
2. **Widget Field Definitions**: Configuration parameters for each widget
3. **Widget Content Type Associations**: Links between widgets and content types they can display
4. **Page Section Widgets**: Instances of widgets placed in page sections
5. **Widget Query System**: Dynamic content retrieval mechanism
6. **Widget Rendering Pipeline**: Process for displaying widgets within the frontend

## Database Structure

The widget system requires the following database tables:

```php
Schema::create('widgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('theme_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->string('icon')->nullable();
    $table->string('view_path');
    $table->string('preview_image')->nullable();
    $table->timestamps();
});

Schema::create('widget_field_definitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('widget_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('slug');
    $table->string('field_type'); // text, number, select, color, etc.
    $table->text('validation_rules')->nullable();
    $table->json('settings')->nullable(); // For select options, min/max values, etc.
    $table->boolean('is_required')->default(false);
    $table->integer('position')->default(0);
    $table->timestamps();
});

Schema::create('widget_content_type_associations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('widget_id')->constrained()->onDelete('cascade');
    $table->foreignId('content_type_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

Schema::create('page_section_widgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('page_section_id')->constrained()->onDelete('cascade');
    $table->foreignId('widget_id')->constrained()->onDelete('restrict');
    $table->integer('position')->default(0);
    $table->string('column_position')->nullable(); // left, right, full
    $table->json('settings')->nullable(); // Widget-specific configuration
    $table->json('content_query')->nullable(); // Content retrieval configuration
    $table->string('css_classes')->nullable();
    $table->json('padding')->nullable();
    $table->json('margin')->nullable();
    $table->timestamps();
});
```

## System Components

### 1. Routes

Update the `routes/admin.php` file with the following routes:

```php
// Widget Management Routes
Route::get('themes/{theme}/widgets', [WidgetController::class, 'index'])->name('themes.widgets.index');
Route::get('themes/{theme}/widgets/{widget}', [WidgetController::class, 'show'])->name('themes.widgets.show');
Route::post('themes/{theme}/widgets/scan', [WidgetController::class, 'scanThemeWidgets'])->name('themes.widgets.scan');

// Widget Content Type Association Routes
Route::post('widgets/{widget}/content-types', [WidgetContentTypeController::class, 'store'])->name('widgets.content-types.store');
Route::delete('widgets/{widget}/content-types/{contentType}', [WidgetContentTypeController::class, 'destroy'])->name('widgets.content-types.destroy');

// Page Section Widget Routes
Route::get('pages/{page}/sections/{section}/widgets', [PageSectionWidgetController::class, 'index'])->name('pages.sections.widgets.index');
Route::post('pages/{page}/sections/{section}/widgets', [PageSectionWidgetController::class, 'store'])->name('pages.sections.widgets.store');
Route::get('pages/{page}/sections/{section}/widgets/{widget}/edit', [PageSectionWidgetController::class, 'edit'])->name('pages.sections.widgets.edit');
Route::put('pages/{page}/sections/{section}/widgets/{widget}', [PageSectionWidgetController::class, 'update'])->name('pages.sections.widgets.update');
Route::delete('pages/{page}/sections/{section}/widgets/{widget}', [PageSectionWidgetController::class, 'destroy'])->name('pages.sections.widgets.destroy');
Route::post('pages/{page}/sections/{section}/widgets/positions', [PageSectionWidgetController::class, 'updatePositions'])->name('pages.sections.widgets.positions');
```

### 2. Models

#### Widget Model

Create `app/Models/Widget.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Widget extends Model
{
    protected $fillable = [
        'theme_id',
        'name',
        'slug',
        'description',
        'icon',
        'view_path',
        'preview_image',
    ];

    /**
     * Get the theme that owns the widget.
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Get the field definitions for the widget.
     */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(WidgetFieldDefinition::class)->orderBy('position');
    }

    /**
     * Get the content types associated with this widget.
     */
    public function contentTypes(): BelongsToMany
    {
        return $this->belongsToMany(ContentType::class, 'widget_content_type_associations');
    }

    /**
     * Get all instances of this widget used in page sections.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(PageSectionWidget::class);
    }

    /**
     * Render this widget with the provided settings and content.
     */
    public function render(array $settings = [], $content = null): string
    {
        // Widget rendering logic will be implemented here
    }
}
```

#### Additional Models

Create models for:
- `WidgetFieldDefinition`
- `WidgetContentTypeAssociation`
- `PageSectionWidget`

With appropriate relationships and methods.

### 3. Controllers

#### WidgetController

Create `app/Http/Controllers/Admin/WidgetController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\Widget;
use App\Services\WidgetDiscoveryService;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Display a listing of widgets for a theme.
     */
    public function index(Theme $theme)
    {
        $widgets = $theme->widgets()->orderBy('name')->get();
        
        return view('admin.widgets.index', compact('theme', 'widgets'));
    }

    /**
     * Display the specified widget.
     */
    public function show(Theme $theme, Widget $widget)
    {
        // Load related data
        $widget->load(['fieldDefinitions', 'contentTypes']);
        $availableContentTypes = ContentType::whereNotIn('id', $widget->contentTypes->pluck('id'))->get();

        return view('admin.widgets.show', compact('theme', 'widget', 'availableContentTypes'));
    }

    /**
     * Scan the theme directory for widgets and register them.
     */
    public function scanThemeWidgets(Request $request, Theme $theme, WidgetDiscoveryService $widgetDiscovery)
    {
        $result = $widgetDiscovery->discoverAndRegisterWidgets($theme);

        return back()->with('success', "{$result['new']} new widgets discovered, {$result['updated']} widgets updated.");
    }
}
```

#### Create additional controllers:
- `WidgetContentTypeController`
- `PageSectionWidgetController`

### 4. Services

#### Widget Discovery Service

Create `app/Services/WidgetDiscoveryService.php`:

```php
<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\Widget;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WidgetDiscoveryService
{
    /**
     * Discover and register widgets within a theme.
     */
    public function discoverAndRegisterWidgets(Theme $theme): array
    {
        $widgetsDir = base_path("themes/{$theme->directory}/widgets");
        $result = ['new' => 0, 'updated' => 0];

        if (!File::isDirectory($widgetsDir)) {
            return $result;
        }

        // Implementation of widget scan and registration logic
        $widgetFiles = File::files($widgetsDir);

        foreach ($widgetFiles as $file) {
            // Process each widget file (implementation details)
        }

        return $result;
    }

    /**
     * Process a widget definition file and register it in the database.
     */
    protected function processWidgetFile($file, Theme $theme)
    {
        // Widget file processing logic
    }
}
```

#### Widget Rendering Service

Create `app/Services/WidgetRenderingService.php` for frontend widget rendering.

### 5. Views

#### Admin Widget Views

Create the following views:

1. `resources/views/admin/widgets/index.blade.php` - List of widgets in a theme
2. `resources/views/admin/widgets/show.blade.php` - Widget details and configuration
3. `resources/views/admin/widgets/_field_definition.blade.php` - Partial for displaying field definitions
4. `resources/views/admin/widgets/_content_type_association.blade.php` - Partial for content type associations

#### Page Section Widget Views

Create views for managing widgets within page sections:

1. `resources/views/admin/pages/sections/widgets/index.blade.php` - Widget list for a section
2. `resources/views/admin/pages/sections/widgets/edit.blade.php` - Edit widget instance configuration
3. `resources/views/admin/pages/sections/widgets/_form.blade.php` - Widget configuration form
4. `resources/views/admin/pages/sections/widgets/_widget.blade.php` - Visual representation of a widget

### 6. JavaScript Components

Create `public/assets/admin/js/widget-management.js`:

```javascript
$(document).ready(function() {
    // Initialize widget sorting
    initWidgetSorting();
    
    // Handle widget addition to sections
    initWidgetAddition();
    
    // Setup widget configuration handlers
    initWidgetConfigHandlers();
    
    // Initialize content query builder
    initContentQueryBuilder();
});

// Core functions to implement:
function initWidgetSorting() {}
function initWidgetAddition() {}
function initWidgetConfigHandlers() {}
function initContentQueryBuilder() {}
function saveWidgetConfig(widgetId) {}
function buildContentQuery() {}
function previewWidget(widgetId) {}
```

### 7. CSS Styles

Add CSS styles in `public/assets/admin/css/widget-management.css` for:

1. Widget cards and previews
2. Widget configuration forms
3. Content query builder interface
4. Widget sorting and positioning controls

## Implementation Phases

### Phase 1: Database & Core Models

1. **Create Database Migrations**
   - Implement all database tables
   - Run migrations and verify structure

2. **Implement Core Models**
   - Create Widget and related models
   - Set up relationships and validation rules
   - Test basic CRUD operations

3. **Build Widget Discovery Service**
   - Implement theme directory scanning
   - Create widget registration logic
   - Develop widget metadata extraction

### Phase 2: Admin Management UI

1. **Widget Listing Interface**
   - Create controller and views for widget listing
   - Implement theme-widget relationship display
   - Add widget scanning functionality

2. **Widget Detail Views**
   - Build detailed widget information page
   - Implement field definition display
   - Create content type association management

3. **Widget Configuration Interface**
   - Develop widget settings management
   - Create widget preview mechanism
   - Implement configuration validation

### Phase 3: Section Integration

1. **Page Section Widget UI**
   - Extend page section interface to support widgets
   - Create UI for adding widgets to sections
   - Implement widget positioning within sections

2. **Widget Configuration Forms**
   - Build dynamic forms based on field definitions
   - Create form validation based on rules
   - Implement settings persistence

3. **Content Query Builder**
   - Create UI for building content queries
   - Implement query parameter validation
   - Develop query preview functionality

### Phase 4: Frontend Rendering

1. **Widget Rendering Service**
   - Implement core widget rendering pipeline
   - Create template resolution mechanism
   - Build settings injection system

2. **Content Query Execution**
   - Develop query execution system
   - Implement content retrieval optimization
   - Create content transformation for widgets

3. **Frontend Display Logic**
   - Implement widget view templates
   - Create responsive behavior for widgets
   - Build widget styling application system

### Phase 5: Testing & Optimization

1. **System Testing**
   - Test widget discovery and registration
   - Verify widget configuration persistence
   - Validate content query execution
   - Test frontend rendering accuracy

2. **Performance Optimization**
   - Optimize database queries
   - Implement widget rendering caching
   - Create asset loading optimization

## Widget Types to Implement

1. **Basic Widgets**
   - Text Widget - Simple text display with formatting options
   - Image Widget - Image display with various size and positioning options
   - Button Widget - Configurable call-to-action buttons

2. **Content Widgets**
   - Content List Widget - Displays lists of content items
   - Content Grid Widget - Shows content in grid format
   - Featured Content Widget - Highlights specific content items

3. **Media Widgets**
   - Gallery Widget - Displays multiple images in gallery format
   - Video Widget - Embeds video content with playback controls
   - Audio Widget - Embeds audio players with playlist support

4. **Interactive Widgets**
   - Form Widget - Displays and processes forms
   - Search Widget - Provides search functionality
   - Social Media Widget - Embeds social feeds and sharing options

## Widget Content Query System

The widget content query system will allow widgets to dynamically retrieve and display content:

1. **Query Components**
   - Content type selection
   - Filter conditions
   - Sorting parameters
   - Pagination settings
   - Relationship inclusions

2. **Query Builder UI**
   - Visual interface for building queries
   - Condition configuration with operators
   - Sort order management
   - Pagination controls

3. **Query Execution**
   - Query transformation to database queries
   - Optimized retrieval with eager loading
   - Result transformation and preparation

## Integration Touchpoints

### Integration with Template System
- Widgets will be associated with templates through sections
- Template rendering will include widget resolution

### Integration with Page System
- Pages will use widgets within their sections
- Page rendering will trigger widget rendering pipeline

### Integration with Content System
- Widgets will query content based on configuration
- Content changes will be reflected in widget display

## Future Enhancements

1. **Widget Caching System**
   - Implement automatic widget rendering caching
   - Create cache invalidation based on content changes

2. **Widget Permissions**
   - Access control for widget management
   - Widget visibility rules based on user roles

3. **Custom Widget Development**
   - API for creating custom widgets
   - Widget package distribution system

4. **Widget Analytics**
   - Track widget performance metrics
   - Optimize widget content based on user engagement

## Conclusion

This implementation plan provides a structured approach to building the Widget System for RealsysCMS. By following these phases, the system will be developed incrementally, ensuring that each component is properly tested before integration into the larger system.
