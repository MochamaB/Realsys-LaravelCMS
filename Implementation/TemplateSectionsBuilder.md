# Template Sections Builder - Detailed Implementation Plan

## Overview

This document outlines the detailed implementation plan for the Template Sections Builder feature of the RealsysCMS Laravel application. The Template Sections Builder will provide an intuitive interface for creating, configuring, visualizing, and reordering template sections with a combination of form input and visual feedback.

## Database Structure

The existing database structure includes:

```php
Schema::create('template_sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('template_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('slug');
    $table->integer('position')->default(0);
    $table->string('section_type')->default('full-width'); // full-width, multi-column, etc.
    $table->string('column_layout')->nullable(); // 12, 6-6, 8-4, etc.
    $table->boolean('is_repeatable')->default(false);
    $table->integer('max_widgets')->nullable();
    $table->timestamps();
});
```

## System Components

### 1. Routes

Update the `routes/admin.php` file with the following routes:

```php
// Template Sections Routes
Route::get('templates/{template}/sections', [TemplateSectionController::class, 'index'])->name('templates.sections.index');
Route::post('templates/{template}/sections', [TemplateSectionController::class, 'store'])->name('templates.sections.store');
Route::get('templates/{template}/sections/{section}/edit', [TemplateSectionController::class, 'edit'])->name('templates.sections.edit');
Route::put('templates/{template}/sections/{section}', [TemplateSectionController::class, 'update'])->name('templates.sections.update');
Route::delete('templates/{template}/sections/{section}', [TemplateSectionController::class, 'destroy'])->name('templates.sections.destroy');
Route::post('templates/{template}/sections/positions', [TemplateSectionController::class, 'updatePositions'])->name('templates.sections.positions');
```

### 2. Controllers

#### TemplateSectionController

Implement or update the `TemplateSectionController.php` with the following methods:

1. **index()** - Display the template sections management view
2. **store()** - Handle creation of new sections (via AJAX)
3. **edit()** - Show the edit form for a specific section
4. **update()** - Update an existing section
5. **destroy()** - Remove a section
6. **updatePositions()** - Handle reordering of sections via SortableJS

### 3. Views

#### Main Template Sections View

Create a `resources/views/admin/templates/sections/index.blade.php` file with:

1. **Page layout** with header, breadcrumb navigation, and main content area
2. **Split layout**:
   - Left side: Form for creating/editing sections
   - Right side: Visual canvas showing existing sections and "add new" placeholder

#### Partial Views

1. **Section Form** (`_form.blade.php`):
   - Section name and slug (with auto-generation)
   - Section type dropdown (full-width, multi-column)
   - Column layout options (conditionally shown)
   - Repeatable toggle with max widgets input
   - Submit button

2. **Section Visual** (`_section.blade.php`):
   - Visual representation of a section based on its type and layout
   - Section name and descriptive details
   - Edit and delete action buttons

### 4. JavaScript Files

Create a new file `public/assets/admin/js/template-sections-builder.js` with the following structure:

```javascript
$(document).ready(function() {
    // Initialize SortableJS
    initSortable();
    
    // Handle form submission for new sections
    initFormHandlers();
    
    // Setup edit and delete actions
    initActions();
    
    // Handle section type changes
    handleSectionTypeChange();
});

// Core functions to implement:
function initSortable() {}
function initFormHandlers() {}
function initActions() {}
function handleSectionTypeChange() {}
function addNewSection() {}
function editSection(id) {}
function deleteSection(id) {}
function updatePositions() {}
function visualizeSection(data) {}
```

### 5. CSS Styles

Add CSS styles in `public/assets/admin/css/template-sections-builder.css`:

1. **Layout styles** for the split view
2. **Section visualization styles** for different types and layouts
3. **Form styles** for better UX
4. **SortableJS specific styles** for drag and drop

## Implementation Steps

### Phase 1: Basic Infrastructure

1. **Update Routes**
   - Add all necessary routes to admin.php
   - Test route resolution

2. **Create Controller**
   - Implement basic methods with proper request validation
   - Set up relationship handling between templates and sections

3. **Setup Basic Views**
   - Create the index view with split layout
   - Implement the section form partial
   - Add base visual representation for sections

### Phase 2: Core Functionality

1. **Form Handling**
   - Implement section creation form
   - Add validation (both client and server-side)
   - Create AJAX submission handlers
   - Implement edit mode toggle

2. **Visual Canvas**
   - Create the section visualization system
   - Add the "new section" placeholder
   - Implement visual rendering based on section types

3. **SortableJS Integration**
   - Set up the sortable container
   - Configure drag options and animation
   - Implement position updating via AJAX
   - Handle the "add new" placeholder special case

### Phase 3: Enhanced Features

1. **Section Type Handling**
   - Implement conditional fields based on section type
   - Add column layout visualization
   - Create live preview of section configuration

2. **Advanced Interactions**
   - Add section cloning functionality
   - Implement inline quick-edit for simple properties
   - Add confirmation dialogs for destructive actions

3. **User Experience Improvements**
   - Add loading indicators during AJAX operations
   - Implement error handling and user notifications
   - Add keyboard shortcuts for common actions

### Phase 4: Testing & Optimization

1. **Testing Scenarios**
   - Test all CRUD operations
   - Verify drag-and-drop behavior
   - Test with various section configurations
   - Check responsive behavior

2. **Performance Optimization**
   - Optimize AJAX calls
   - Implement request debouncing
   - Add caching where appropriate

## Detailed Component Specifications

### Section Types

1. **Full Width**
   - Spans the entire container
   - No column divisions

2. **Multi-Column**
   - Options for various column layouts:
     - Two equal columns (6-6)
     - Three equal columns (4-4-4)
     - Two unequal columns (8-4, 4-8)
     - Three unequal columns (6-3-3, 3-6-3, 3-3-6)

### Visual Representation

Design the visual representation to clearly show:

1. **Width** - Visual width corresponding to the actual layout
2. **Columns** - Visual dividers for multi-column layouts
3. **Status** - Different styling for repeatable vs. static sections
4. **Actions** - Hover-revealed action buttons

### AJAX Endpoints

1. **Create Section**
   - Endpoint: POST `/admin/templates/{template}/sections`
   - Payload: Section details (name, slug, type, layout, etc.)
   - Response: Created section data with ID

2. **Update Section**
   - Endpoint: PUT `/admin/templates/{template}/sections/{section}`
   - Payload: Updated section details
   - Response: Updated section data

3. **Delete Section**
   - Endpoint: DELETE `/admin/templates/{template}/sections/{section}`
   - Response: Success message

4. **Update Positions**
   - Endpoint: POST `/admin/templates/{template}/sections/positions`
   - Payload: Array of section IDs in the new order
   - Response: Success message

## UI/UX Considerations

1. **Visual Feedback**
   - Show drag handles on hover
   - Highlight the active section being edited
   - Use animation for section transitions
   - Provide clear success/error messages

2. **Accessibility**
   - Ensure keyboard navigation works for the entire interface
   - Add proper ARIA attributes to dynamic elements
   - Maintain sufficient color contrast

3. **Responsive Design**
   - Adapt the split layout for smaller screens
   - Ensure the form remains usable on mobile
   - Maintain drag-drop functionality on touch devices

## Conclusion

This implementation plan provides a detailed roadmap for creating a user-friendly Template Sections Builder with visual feedback and intuitive interaction. The phased approach allows for incremental development and testing, ensuring a robust final product.

By following this plan, we'll create a powerful yet easy-to-use tool for managing template sections, enhancing the overall CMS experience.
