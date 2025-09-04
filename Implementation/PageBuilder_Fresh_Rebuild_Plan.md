# Page Builder Fresh Rebuild Plan
**Start Date:** September 2, 2025  
**Reason:** Modal display issues preventing core functionality - need clean foundation

---

## 🎯 QUICK OVERVIEW

**Current Problem:** Modals only show backdrops, no content displays (affects section templates, widget selection)
**Solution:** Complete rebuild from scratch, testing each component individually
**Goal:** Working Page Builder with reliable modal system

---

## 📋 PHASES SUMMARY

### Phase 1: Clean Slate & Modal Test (Day 1)
- Archive current implementation
- Create minimal test modal
- Verify Bootstrap functionality

### Phase 2: Section Templates (Day 1-2)  
- Working section template selection
- Modal or offcanvas with template cards
- Basic template selection events

### Phase 3: Basic Section Creation (Day 2-3)
- Simple backend API endpoint
- Create sections without GridStack
- Display in basic HTML grid

### Phase 4: Section Management (Day 3-4)
- Section editing modal
- Section deletion
- Position management

### Phase 5: Widget System (Day 4-5)
- Widget selection modal
- Add widgets to sections
- Basic widget editing

### Phase 6: Advanced Features (Day 5+)
- GridStack integration
- Drag & drop
- Preview functionality

---

## 🗂️ DETAILED IMPLEMENTATION STEPS

### STEP 1: Archive Current Implementation

**Files to Archive:**
```
Create: Implementation/Archive/PageBuilder_v1_archived/
Move:   public/assets/admin/js/page-builder/ → Archive/js/
Move:   resources/views/admin/pages/page-builder/ → Archive/views/
Move:   public/assets/admin/css/page-builder/ → Archive/css/
```

**Controller Backup:**
- Copy `app/Http/Controllers/Api/PageBuilderController.php` methods to archive
- Keep only basic route structure

---

### STEP 2: Bootstrap Modal Verification

**Create Test File:** `resources/views/admin/test/modal-test.blade.php`
```html
@extends('admin.layouts.master')
@section('content')
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testModal">Test Modal</button>
<div class="modal fade" id="testModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bootstrap Test</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>If you see this, Bootstrap modals work!</p>
            </div>
        </div>
    </div>
</div>
@endsection
```

**Test Route:** Add to `web.php`
```php
Route::get('/admin/test/modal', function() {
    return view('admin.test.modal-test');
})->name('admin.test.modal');
```

**Success Criteria:**
- [ ] Modal opens with full content
- [ ] Modal closes properly
- [ ] No backdrop-only issues

---

### STEP 3: Minimal Page Builder Structure

**Create Basic Files:**
```
resources/views/admin/pages/page-builder/
├── show.blade.php (minimal version)
├── components/
│   ├── toolbar.blade.php (just Add Section button)
│   └── canvas.blade.php (empty container)
└── modals/
    └── section-templates.blade.php (simple modal)
```

**show.blade.php Template:**
```html
@extends('admin.layouts.master')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('admin.pages.page-builder.components.toolbar')
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @include('admin.pages.page-builder.components.canvas')
        </div>
    </div>
</div>

@include('admin.pages.page-builder.modals.section-templates')
@endsection
```

---

### STEP 4: Section Templates Modal

**Create:** `resources/views/admin/pages/page-builder/modals/section-templates.blade.php`

**Requirements:**
- Standard Bootstrap modal structure
- 4-6 template cards (Hero, Two Column, Three Column, Footer)
- Select template → Enable "Add Section" button
- NO custom JavaScript initially (pure Bootstrap)

**Test Cases:**
- [ ] Modal opens when clicking "Add Section"
- [ ] Template cards are clickable
- [ ] "Add Section" button enables/disables
- [ ] Modal closes properly

---

### STEP 5: Basic Section Creation API

**Create:** `app/Http/Controllers/PageBuilder/SectionController.php`
```php
class SectionController extends Controller 
{
    public function create(Request $request)
    {
        // Create section with template
        // Return JSON response
        // NO GridStack integration yet
    }
}
```

**Test Endpoint:** `POST /api/page-builder/sections`
**Success:** Section created in database, returns section data

---

### STEP 6: Display Created Sections

**Update Canvas:** Show sections as simple HTML cards
**NO GridStack Yet:** Use Bootstrap grid system
**Features:**
- Display section name
- Show template type
- Basic edit/delete buttons

**Test Cases:**
- [ ] Created sections appear on page
- [ ] Sections display correctly
- [ ] Edit/Delete buttons present

---

### STEP 7: Section Editing

**Create:** Section editing modal
**Features:**
- Edit section name
- Change basic settings
- Save changes to database

**Test Cases:**
- [ ] Edit modal opens
- [ ] Form fields populate correctly  
- [ ] Changes save successfully
- [ ] UI updates after save

---

### STEP 8: Widget Selection Foundation

**Create:** Basic widget selection modal
**Start Simple:** 
- Text widget
- Image widget
- Button widget

**No Complex Features Yet:**
- No multi-step process
- No content selection
- Just widget type selection

---

### STEP 9: GridStack Integration

**ONLY AFTER** all above steps work perfectly:
- Add GridStack library
- Convert HTML grid to GridStack
- Add drag & drop functionality
- Add resize handles

---

## ✅ SUCCESS CHECKPOINTS

### Checkpoint 1: Bootstrap Works
- [ ] Test modal displays full content
- [ ] No backdrop-only issues
- [ ] Modal opens/closes smoothly

### Checkpoint 2: Section Templates  
- [ ] Section templates modal works perfectly
- [ ] Template selection functional
- [ ] Add Section button works

### Checkpoint 3: Basic Creation
- [ ] Sections created successfully
- [ ] API responds correctly
- [ ] Database updates properly

### Checkpoint 4: Display System
- [ ] Sections display on page
- [ ] Edit/delete buttons present
- [ ] No layout issues

### Checkpoint 5: Section Management
- [ ] Section editing works
- [ ] Changes persist
- [ ] UI updates correctly

### Checkpoint 6: Widget Foundation
- [ ] Widget selection modal works
- [ ] Widgets can be added to sections
- [ ] Basic widget display

---

## 🚫 RULES FOR SUCCESS

### 1. **One Step at a Time**
- Complete each checkpoint before proceeding
- Test thoroughly at each step
- Fix any issues immediately

### 2. **No Skipping Steps**
- Don't jump to GridStack early
- Don't add complex features prematurely
- Build solid foundation first

### 3. **Minimal Dependencies**
- Use only essential libraries
- No unnecessary complexity
- Keep JavaScript simple and clean

### 4. **Clear Debugging**
- Add console.log at every step
- Use descriptive variable names
- Comment all major functions

### 5. **Test Everything**
- Test in different browsers
- Test modal functionality repeatedly
- Verify database operations

---

## 📁 FILE ORGANIZATION

### Clean Structure:
```
resources/views/admin/pages/page-builder/
├── show.blade.php
├── components/
│   ├── toolbar.blade.php
│   ├── canvas.blade.php
│   └── section-card.blade.php
├── modals/
│   ├── section-templates.blade.php
│   ├── section-edit.blade.php
│   └── widget-select.blade.php
└── partials/
    └── section-types/ (individual template cards)

public/assets/admin/js/page-builder-v2/
├── core/
│   ├── main.js (initialization)
│   └── api.js (API calls)
├── modals/
│   ├── section-templates.js
│   ├── section-edit.js
│   └── widget-select.js
└── components/
    ├── section-manager.js
    └── widget-manager.js

public/assets/admin/css/page-builder-v2/
├── main.css
├── modals.css
└── components.css
```

---

## 🎯 FINAL GOAL

A working Page Builder where:
- ✅ All modals display content properly
- ✅ Sections can be created, edited, deleted
- ✅ Widgets can be added to sections  
- ✅ Basic drag & drop functionality
- ✅ Preview system works
- ✅ Changes persist to database
- ✅ Clean, maintainable code structure

**Timeline:** 5-7 days for full implementation
**Next Step:** Archive current files and create modal test