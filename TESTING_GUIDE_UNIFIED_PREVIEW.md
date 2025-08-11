# Unified Widget Live Preview System - Testing Guide

## Overview
The Unified Widget Live Preview System (Phase 4) has been fully implemented and integrated. This guide will help you test all the functionality comprehensively.

## System Components Implemented

### ✅ Backend API (Phase 4.1)
- **Extended WidgetController** with 4 new methods:
  - `getWidgetContentOptions()` - Get content items for widget preview
  - `getContentWidgetOptions()` - Get compatible widgets for content preview  
  - `renderWidgetWithContent()` - Render widget with content item
  - `renderContentWithWidget()` - Render content item through widget

### ✅ API Routes Integration
- **New Routes Added** to `routes/admin.php`:
  - `GET /admin/api/widgets/{widget}/content-options`
  - `GET /admin/api/content/widget-options`
  - `POST /admin/api/widgets/{widget}/render-with-content`
  - `POST /admin/api/content/render-with-widget`

### ✅ JavaScript Framework (Phase 4.2)
- **UniversalPreviewManager** class created at `public/assets/admin/js/universal-preview-manager.js`
- **Global Integration** - Available as `window.universalPreviewManager`
- **Event-driven Architecture** with caching, retry logic, and asset management

### ✅ Enhanced UI Integration
- **Widget Preview Tab** - Fully integrated with UniversalPreviewManager
- **Content Item Preview Page** - Fully integrated with UniversalPreviewManager

## Pre-Testing Setup

Run these commands to ensure the system is ready:

```bash
# Clear Laravel caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Verify new routes are registered
php artisan route:list --name=api.widgets
php artisan route:list --name=api.content

# Check if JavaScript file exists
ls public/assets/admin/js/universal-preview-manager.js
```

## Testing Scenarios

### 1. Widget Preview Tab Testing

**Location:** `/admin/widgets/{widget_id}` → Preview Tab

**Test Cases:**

#### A. Static Preview Mode
1. Navigate to any widget's preview tab
2. Ensure "Static Preview" is selected by default
3. Verify preview loads with sample data
4. Test device size switching (Desktop/Tablet/Mobile)
5. Test refresh button functionality
6. Check preview metadata (render time, assets count, cache status)

#### B. Live Preview Mode
1. Click "Live Preview" button
2. Verify content item dropdown appears
3. Select a content item from dropdown
4. Verify preview updates with real content data
5. Test field overrides in JSON format:
   ```json
   {
     "title": "Custom Override Title",
     "description": "Custom description text"
   }
   ```
6. Test settings overrides:
   ```json
   {
     "show_date": true,
     "layout": "grid"
   }
   ```
7. Test device size switching in live mode
8. Test auto-refresh functionality

#### C. Advanced Features
1. Test fullscreen preview mode
2. Test settings panel toggle
3. Verify error handling with invalid JSON
4. Test preview status indicators

### 2. Content Item Preview Page Testing

**Location:** `/admin/content-items/{content_id}/preview` (if route exists) or create test route

**Test Cases:**

#### A. Content-Only Preview
1. Navigate to content item preview page
2. Verify "Content Only" mode is selected by default
3. Check that content displays properly formatted
4. Verify field types are correctly styled

#### B. Widget Preview Mode
1. Click "Widget Preview" button
2. Verify widget selection dropdown appears
3. Select a compatible widget from dropdown
4. Verify content renders through selected widget
5. Test device size controls
6. Test field mapping overrides:
   ```json
   {
     "content_title": "widget_headline",
     "content_description": "widget_text"
   }
   ```
7. Test widget settings overrides
8. Test refresh functionality

### 3. API Endpoint Testing

Use these curl commands or Postman to test API endpoints directly:

#### Get Widget Content Options
```bash
curl -X GET "http://localhost/admin/api/widgets/1/content-options" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token"
```

#### Get Content Widget Options
```bash
curl -X GET "http://localhost/admin/api/content/widget-options?content_item_id=1" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token"
```

#### Render Widget with Content
```bash
curl -X POST "http://localhost/admin/api/widgets/1/render-with-content" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "content_item_id": 1,
    "field_overrides": {"title": "Test Override"},
    "settings_overrides": {"show_date": true}
  }'
```

#### Render Content with Widget
```bash
curl -X POST "http://localhost/admin/api/content/render-with-widget" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "content_item_id": 1,
    "widget_id": 1,
    "field_mapping_overrides": {"title": "headline"},
    "widget_settings_overrides": {"layout": "grid"}
  }'
```

### 4. JavaScript Framework Testing

Open browser console and test UniversalPreviewManager directly:

```javascript
// Check if manager is available
console.log(window.universalPreviewManager);

// Test widget content options
window.universalPreviewManager.getWidgetContentOptions(1)
  .then(data => console.log('Widget content options:', data));

// Test content widget options
window.universalPreviewManager.getContentWidgetOptions({content_item_id: 1})
  .then(data => console.log('Content widget options:', data));

// Test widget with content rendering
window.universalPreviewManager.renderWidgetWithContent(1, {
  content_item_id: 1,
  field_overrides: {title: 'Test Title'}
}).then(data => console.log('Widget render result:', data));

// Test content with widget rendering
window.universalPreviewManager.renderContentWithWidget({
  content_item_id: 1,
  widget_id: 1
}).then(data => console.log('Content render result:', data));

// Check cache and stats
console.log('Preview manager stats:', window.universalPreviewManager.getStats());
```

### 5. Error Handling Testing

#### Test Invalid Data
1. Try selecting non-existent content items
2. Try selecting non-existent widgets
3. Input invalid JSON in override fields
4. Test network disconnection scenarios
5. Test with widgets that have no compatible content
6. Test with content that has no compatible widgets

#### Test Edge Cases
1. Very large content items
2. Widgets with complex field schemas
3. Content items with missing required fields
4. Widgets with circular dependencies

## Expected Results

### Success Indicators
- ✅ All preview modes load without errors
- ✅ Content item dropdowns populate correctly
- ✅ Widget selection dropdowns populate correctly
- ✅ Preview updates in real-time with selections
- ✅ Device size switching works smoothly
- ✅ JSON overrides apply correctly
- ✅ Error messages are user-friendly
- ✅ Loading states display properly
- ✅ Assets (CSS/JS) load correctly in previews
- ✅ Fullscreen mode works
- ✅ Cache system functions properly

### Performance Benchmarks
- Initial preview load: < 2 seconds
- Preview refresh: < 1 second
- Device size switch: < 500ms
- Override application: < 1 second

## Troubleshooting

### Common Issues

#### 1. "UniversalPreviewManager not available" Error
- **Cause:** JavaScript file not loaded
- **Solution:** Check if `universal-preview-manager.js` is included in admin layout
- **Verify:** Check browser network tab for 404 errors

#### 2. API Routes Not Found (404)
- **Cause:** Routes not registered or cache issues
- **Solution:** Run `php artisan route:clear` and `php artisan config:clear`
- **Verify:** Run `php artisan route:list --name=api.widgets`

#### 3. CSRF Token Errors
- **Cause:** Missing or invalid CSRF token
- **Solution:** Ensure `<meta name="csrf-token">` is in page head
- **Verify:** Check browser console for CSRF-related errors

#### 4. Preview Not Loading
- **Cause:** Widget/content compatibility issues
- **Solution:** Check widget schema and content type matching
- **Debug:** Check browser console and Laravel logs

#### 5. JSON Override Errors
- **Cause:** Invalid JSON syntax
- **Solution:** Validate JSON before applying
- **Debug:** Check browser console for JSON parsing errors

## Next Steps After Testing

1. **Document Issues:** Record any bugs or unexpected behavior
2. **Performance Optimization:** Identify slow-loading scenarios
3. **User Experience:** Note any confusing UI elements
4. **Feature Requests:** List additional functionality needed
5. **Production Readiness:** Assess system stability and reliability

## Support

If you encounter issues during testing:

1. Check browser console for JavaScript errors
2. Check Laravel logs for backend errors
3. Verify database has sample widgets and content items
4. Ensure all dependencies are properly installed
5. Test with different browsers for compatibility

---

**Testing Status:** Ready for comprehensive testing
**Last Updated:** August 11, 2025
**System Version:** Phase 4.2 Complete
