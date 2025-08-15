<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\ContentTypeController;
use App\Http\Controllers\Admin\ContentTypeFieldController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaFolderController;
use App\Http\Controllers\Admin\MediaTagController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\PageWidgetController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TemplateSectionController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WidgetController;
use App\Http\Controllers\Admin\WidgetContentTypeController;
use App\Http\Controllers\Admin\WidgetAssociationController;
use App\Http\Controllers\Admin\PageSectionWidgetController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserViewSwitchController;
use App\Http\Controllers\Admin\PreviewController;

// Test route moved inside admin.auth middleware group
// Guest routes for admin authentication (only for non-authenticated admin users)
Route::middleware('admin.guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [AdminAuthController::class, 'showForgotForm'])->name('forgot-password');
    Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLink'])->name('forgot-password.post');
    Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('reset-password');
    Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('reset-password.post');
});

// Protected admin routes (require admin authentication)
Route::middleware('admin.auth')->group(function () {
    
    // Test route to verify admin routing
    Route::get('/test', function() {
        return 'Admin routes are working!';
    })->name('test');
    
    // Test flash message route
    Route::get('/test-flash', function() {
        return redirect()->route('admin.users.index')->with('success', 'Test flash message');
    })->name('test-flash');
    
    // Dashboard routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Admin to User View Switch Routes
    Route::get('/switch-to-user', [UserViewSwitchController::class, 'switchToUser'])
        ->name('switch.to.user');

    // Pages
    Route::resource('pages', PageController::class);
    Route::put('/pages/{page}/homepage', [PageController::class, 'toggleHomepage'])->name('admin.pages.homepage');
    Route::get('/pages/{page}/sections', [PageController::class, 'getSections'])->name('pages.sections');

    // Widgets - Global routes
    Route::resource('widgets', WidgetController::class);
    // Widget management routes
    Route::get('/widgets', [WidgetController::class, 'index'])->name('widgets.index');
    Route::get('/widgets/{widget}', [WidgetController::class, 'show'])->name('widgets.show');
    Route::get('/widgets/{widget}/preview', [WidgetController::class, 'preview'])->name('widgets.preview');
    Route::patch('/widgets/{widget}/toggle', [WidgetController::class, 'toggle'])->name('widgets.toggle');
    
    // Widget discovery routes
    Route::get('/widgets/scan/{theme?}', [WidgetController::class, 'scanThemeWidgets'])->name('widgets.scan');
    
    // Widget Content Type Associations
    Route::post('/widgets/{widget}/content-types', [WidgetContentTypeController::class, 'store'])->name('widgets.content-types.store');
    Route::delete('/widgets/{widget}/content-types/{contentType}', [WidgetContentTypeController::class, 'destroy'])->name('widgets.content-types.destroy');
    
    // Widget Code Editing
    Route::get('/widgets/{widget}/code', [WidgetController::class, 'editWidgetCode'])->name('widgets.edit_code');
    Route::post('/widgets/{widget}/code', [WidgetController::class, 'updateWidgetCode'])->name('widgets.update_code');

    // Widget Live Preview API (Missing routes for JavaScript UI)
    Route::prefix('preview')->name('preview.')->group(function () {
        Route::post('/widget/{widget}', [PreviewController::class, 'renderWidget'])->name('widget');
        Route::get('/widget/{widget}/content-options', [PreviewController::class, 'getWidgetContentOptions'])->name('widget.content-options');
        
        // Content Item Widget Preview API
        Route::post('/content/{contentItem}/widget/{widget}', [PreviewController::class, 'renderContentWithWidget'])->name('content.widget');
        Route::get('/content/{contentId}/widget-options', [PreviewController::class, 'getContentWidgetOptions'])->name('content.widget-options');
    });
    
    // Widget Association routes
    Route::post('/widgets/{widget}/associations', [WidgetAssociationController::class, 'store'])->name('widgets.associations.create');
    Route::post('/widgets/{widget}/preview-mappings', [WidgetAssociationController::class, 'previewMappings'])->name('widgets.associations.preview-mappings');
    Route::put('/widgets/associations/{association}/update', [WidgetAssociationController::class, 'update'])->name('widgets.associations.update');
    Route::delete('/widgets/associations/{association}', [WidgetAssociationController::class, 'destroy'])->name('widgets.associations.delete');
    Route::post('/widgets/associations/{association}/toggle', [WidgetAssociationController::class, 'toggle'])->name('widgets.associations.toggle');
    
    // Content Type Generation from Widget routes
    Route::get('/widgets/{widget}/suggest-content-type', [WidgetController::class, 'suggestContentType'])->name('widgets.suggest-content-type');
    Route::post('/widgets/{widget}/create-content-type', [WidgetController::class, 'createContentTypeFromSuggestion'])->name('widgets.create-content-type');

    // Page Section Widgets
    Route::get('/pages/{page}/sections/{section}/widgets', [PageSectionWidgetController::class, 'index'])->name('pages.sections.widgets.index');
    Route::post('/pages/{page}/sections/{section}/widgets', [PageSectionWidgetController::class, 'store'])->name('pages.sections.widgets.store');
    Route::get('/pages/{page}/sections/{section}/widgets/{widget}/edit', [PageSectionWidgetController::class, 'edit'])->name('pages.sections.widgets.edit');
    Route::put('/pages/{page}/sections/{section}/widgets/{widget}', [PageSectionWidgetController::class, 'update'])->name('pages.sections.widgets.update');
    Route::delete('/pages/{page}/sections/{section}/widgets/{widget}', [PageSectionWidgetController::class, 'destroy'])->name('pages.sections.widgets.destroy');
    Route::post('/pages/{page}/sections/{section}/widgets/positions', [PageSectionWidgetController::class, 'updatePositions'])->name('pages.sections.widgets.positions');
    

    // Menus
    Route::resource('menus', MenuController::class);

    // Menu items management page
    Route::get('menus/{menu}/items', [MenuController::class, 'items'])->name('menus.items');

    // Menu item routes
    Route::prefix('menus/{menu}')->name('menus.items.')->group(function () {
        Route::get('items/create', [MenuItemController::class, 'create'])->name('create');
        Route::post('items', [MenuItemController::class, 'store'])->name('store');
        Route::get('items/{item}/edit', [MenuItemController::class, 'edit'])->name('edit');
        Route::put('items/{item}', [MenuItemController::class, 'update'])->name('update');
        Route::delete('items/{item}', [MenuItemController::class, 'destroy'])->name('destroy');
        Route::post('items/positions', [MenuItemController::class, 'updatePositions'])->name('positions');
    });

    
    // Themes
    Route::resource('themes', ThemeController::class);
    Route::post('/themes/{theme}/activate', [ThemeController::class, 'activate'])->name('themes.activate');

    // Templates
    Route::get('/themes/{theme}/templates', [TemplateController::class, 'index'])->name('themes.templates.index');
    Route::get('/themes/{theme}/templates/{template}', [TemplateController::class, 'show'])->name('themes.templates.show');
    Route::get('/themes/{theme}/templates/{template}/sections', [TemplateSectionController::class, 'index'])->name('themes.templates.sections.index');

    // Media management
    Route::resource('media', MediaController::class);
    Route::get('media/filter', [MediaController::class, 'filter'])->name('media.filter');
    Route::get('media-picker', [MediaController::class, 'mediaPicker'])->name('media.picker');
    Route::get('/media/search', [MediaController::class, 'search'])->name('media.search');
    Route::post('/media/upload', [MediaController::class, 'store'])->name('media.upload');
    
    // Media Tags
    Route::resource('media-tags', MediaTagController::class)->except(['show']);
    Route::post('/media/{media}/tags', [MediaController::class, 'updateTags'])->name('media.update-tags');
    
    // Media Folders
    Route::resource('media-folders', MediaFolderController::class)->except(['show']);
    Route::post('/media/move-to-folder', [MediaController::class, 'moveToFolder'])->name('media.move-to-folder');
    
    // Media Batch Operations
    Route::post('/media/batch-delete', [MediaController::class, 'batchDelete'])->name('media.batch-delete');
    Route::post('/media/batch-tag', [MediaController::class, 'batchTag'])->name('media.batch-tag');

    // Users
    Route::resource('users', UserManagementController::class)
        ->names('users')
        ->middleware(['auth:admin', 'verified']);

    // Roles and Permissions
    Route::resource('roles', RoleController::class);
    Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Content Types
    Route::resource('content-types', ContentTypeController::class);
    
    // Themes
    Route::resource('themes', ThemeController::class);
    Route::post('themes/{theme}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    Route::get('themes/{theme}/preview', [ThemeController::class, 'preview'])->name('themes.preview');
    Route::post('themes/{theme}/publish-assets', [ThemeController::class, 'publishAssets'])->name('themes.publish-assets');
    
    // Templates
    Route::resource('templates', TemplateController::class);
    Route::post('templates/{template}/set-default', [TemplateController::class, 'setDefault'])->name('templates.set-default');
    Route::get('templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
    Route::get('template-files', [TemplateController::class, 'getTemplateFiles'])->name('templates.files');
    
    // Template Sections
    Route::prefix('templates/{template}')->group(function () {
        Route::resource('sections', TemplateSectionController::class)
            ->names([
                'index' => 'templates.sections.index',
                'create' => 'templates.sections.create',
                'store' => 'templates.sections.store',
                'show' => 'templates.sections.show',
                'edit' => 'templates.sections.edit',
                'update' => 'templates.sections.update',
                'destroy' => 'templates.sections.destroy',
            ]);
            
        // AJAX routes for template sections management
        Route::post('sections/positions', [TemplateSectionController::class, 'updatePositions'])->name('templates.sections.positions');
        Route::get('sections/{section}/get', [TemplateSectionController::class, 'getSection'])->name('templates.sections.get');
    });
    
    // Content Type Fields
    Route::prefix('content-types/{contentType}')->group(function () {
        Route::resource('fields', ContentTypeFieldController::class)
            ->except(['show'])
            ->names([
                'index' => 'content-types.fields.index',
                'create' => 'content-types.fields.create',
                'store' => 'content-types.fields.store',
                'edit' => 'content-types.fields.edit',
                'update' => 'content-types.fields.update',
                'destroy' => 'content-types.fields.destroy',
            ]);
        Route::post('fields/reorder', [ContentTypeFieldController::class, 'reorder'])->name('content-types.fields.reorder');
    });
    
    // Content Items
    Route::get('content-items', [ContentItemController::class, 'allItems'])->name('content-items.all');
    Route::prefix('content-types/{contentType}')->group(function () {
        Route::resource('items', ContentItemController::class)
            ->names([
                'index' => 'content-types.items.index',
                'create' => 'content-types.items.create',
                'store' => 'content-types.items.store',
                'show' => 'content-types.items.show',
                'edit' => 'content-types.items.edit',
                'update' => 'content-types.items.update',
                'destroy' => 'content-types.items.destroy',
            ]);
        Route::get('items/{item}/preview', [App\Http\Controllers\Admin\PreviewController::class, 'showContentItemPreview'])->name('content-types.items.preview');
    });

    // User Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/update-password', [UserManagementController::class, 'updatePassword'])->name('update-password');
        Route::put('/{id}/update-membership', [UserManagementController::class, 'updateMembership'])->name('update-membership');
        Route::post('/{id}/profile-picture', [UserManagementController::class, 'updateProfilePicture'])->name('update-profile-picture');
    
        // Wizard Routes
        Route::get('/wizard', [UserManagementController::class, 'showWizard'])->name('wizard');
        Route::get('/wizard/cancel', [UserManagementController::class, 'cancelWizard'])->name('wizard.cancel');
        
        // Step 1: User Type
        Route::get('/wizard/step1', [UserManagementController::class, 'showStep1'])->name('wizard.step1');
        Route::post('/wizard/step1', [UserManagementController::class, 'submitStep1'])->name('wizard.step1.post');
        
        // Step 2: Role Selection
        Route::get('/wizard/step2', [UserManagementController::class, 'showStep2'])->name('wizard.step2');
        Route::post('/wizard/step2', [UserManagementController::class, 'submitStep2'])->name('wizard.step2.post');
        
        // Step 3: Personal Information
        Route::get('/wizard/step3', [UserManagementController::class, 'showStep3'])->name('wizard.step3');
        Route::post('/wizard/step3', [UserManagementController::class, 'submitStep3'])->name('wizard.step3.post');
        
        // Step 4: Party Membership Decision
        Route::get('/wizard/step4', [UserManagementController::class, 'showStep4'])->name('wizard.step4');
        Route::post('/wizard/step4', [UserManagementController::class, 'submitStep4'])->name('wizard.step4.post');
        
        // Step 5: Additional Information (Conditional)
        Route::get('/wizard/step5', [UserManagementController::class, 'showStep5'])->name('wizard.step5');
        Route::post('/wizard/step5', [UserManagementController::class, 'submitStep5'])->name('wizard.step5.post');
        
        // Step 6: Geographic Information (Conditional)
        Route::get('/wizard/step6', [UserManagementController::class, 'showStep6'])->name('wizard.step6');
        Route::post('/wizard/step6', [UserManagementController::class, 'submitStep6'])->name('wizard.step6.post');
        
        // Step 7: Terms & Photo (Conditional)
        Route::get('/wizard/step7', [UserManagementController::class, 'showStep7'])->name('wizard.step7');
        Route::post('/wizard/step7', [UserManagementController::class, 'submitStep7'])->name('wizard.step7.post');
        
        // AJAX routes for dynamic data
        Route::get('/wizard/constituencies', [UserManagementController::class, 'getConstituencies'])->name('wizard.constituencies');
        Route::get('/wizard/wards', [UserManagementController::class, 'getWards'])->name('wizard.wards');
    });

    // API routes for GrapeJS (using web middleware for sessions)
Route::prefix('api')->middleware('admin.auth')->group(function () {
    // Page Designer API
    Route::get('/pages/{page}/render', [PageController::class, 'renderPageContent'])->name('api.pages.render');
    Route::post('/pages/{page}/save-content', [PageController::class, 'savePageContent'])->name('api.pages.save-content');
    
    // Theme Assets API for Canvas
    Route::get('/themes/active/assets', [App\Http\Controllers\Api\ThemeController::class, 'getActiveThemeAssets'])->name('api.themes.assets');
    Route::get('/themes/active/canvas-styles', [App\Http\Controllers\Api\ThemeController::class, 'getCanvasStyles'])->name('api.themes.canvas-styles');
    Route::get('/themes/active/canvas-scripts', [App\Http\Controllers\Api\ThemeController::class, 'getCanvasScripts'])->name('api.themes.active.canvas-scripts');
    
    // Widget Rendering API for Designer
    Route::get('/widgets/available', [App\Http\Controllers\Api\WidgetController::class, 'getAvailableWidgets'])->name('api.widgets.available');
    Route::get('/widgets/enhanced-blocks', [App\Http\Controllers\Api\WidgetController::class, 'getEnhancedWidgetBlocks'])->name('api.widgets.enhanced-blocks');
    Route::get('/widgets/{widget}/render', [App\Http\Controllers\Api\WidgetController::class, 'renderWidget'])->name('api.widgets.render');
    
    // Enhanced Widget Preview API (Phase 1.2)
    Route::match(['GET', 'POST'], '/widgets/{widget}/preview', [App\Http\Controllers\Api\WidgetController::class, 'renderWidgetPreview'])->name('api.widgets.preview');
    Route::get('/widgets/{widget}/preview-data', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetSampleData'])->name('api.widgets.preview-data');
    
    // Unified Live Preview API (Phase 4.1)
    Route::get('/widgets/{widget}/content-options', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetContentOptions'])->name('api.widgets.content-options');
    Route::get('/content/widget-options', [App\Http\Controllers\Api\WidgetController::class, 'getContentWidgetOptions'])->name('api.content.widget-options');
    Route::post('/widgets/{widget}/render-with-content', [App\Http\Controllers\Api\WidgetController::class, 'renderWidgetWithContent'])->name('api.widgets.render-with-content');
    Route::post('/content/render-with-widget', [App\Http\Controllers\Api\WidgetController::class, 'renderContentWithWidget'])->name('api.content.render-with-widget');
    
    // Frontend Preview API (New approach using frontend renderer)
    Route::get('/widgets/{widget}/frontend-preview', [App\Http\Controllers\Admin\FrontendPreviewController::class, 'renderWidgetPreview'])->name('api.widgets.frontend-preview');
    Route::post('/widgets/{widget}/frontend-preview-with-content', [App\Http\Controllers\Admin\FrontendPreviewController::class, 'renderWidgetWithContent'])->name('api.widgets.frontend-preview-with-content');
    
    // Full Page Preview (Complete frontend rendering)
    Route::get('/widgets/{widget}/page-preview', [App\Http\Controllers\Admin\WidgetPreviewPageController::class, 'showPreviewPage'])->name('admin.widgets.page-preview');
    
    // Frontend Widget Preview (Uses existing pages + filtering)
    Route::get('/widgets/{widget}/frontend-page-preview', [App\Http\Controllers\Admin\WidgetPreviewFrontendController::class, 'showWidgetPreview'])->name('admin.widgets.frontend-page-preview');
    
    // Isolated Widget Preview (Renders widget in isolation)
    Route::get('/widgets/{widget}/isolated-preview', [App\Http\Controllers\Admin\WidgetPreviewFrontendController::class, 'renderWidgetIsolated'])->name('admin.widgets.isolated-preview');
    
    // Content Options for Widget Preview
    Route::get('/widgets/{widget}/content-options', [App\Http\Controllers\Admin\WidgetPreviewFrontendController::class, 'getContentOptions'])->name('admin.widgets.content-options');
    
    // Widget Schema API for GrapesJS
    Route::get('/widgets/schemas', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetSchemas'])->name('api.widgets.schemas');
    Route::get('/widgets/{widget}/schema', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetSchema'])->name('api.widgets.schema');
    Route::get('/widgets/{widget}/sample-data', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetSampleData'])->name('api.widgets.sample-data');
    
    // Section Schema API for GrapesJS (Phase 1.3)
    Route::get('/pages/{page}/sections/schemas', [App\Http\Controllers\Api\SectionSchemaController::class, 'getPageSectionSchemas'])->name('api.pages.sections.schemas');
    Route::get('/sections/{section}/schema', [App\Http\Controllers\Api\SectionSchemaController::class, 'getSectionSchema'])->name('api.sections.schema');
    Route::get('/sections/types', [App\Http\Controllers\Api\SectionSchemaController::class, 'getAvailableSectionTypes'])->name('api.sections.types');
    Route::post('/sections/schema/create', [App\Http\Controllers\Api\SectionSchemaController::class, 'createNewSectionSchema'])->name('api.sections.schema.create');
    Route::post('/sections/schema/validate', [App\Http\Controllers\Api\SectionSchemaController::class, 'validateSectionSchema'])->name('api.sections.schema.validate');
    Route::post('/sections/schema/clear-cache', [App\Http\Controllers\Api\SectionSchemaController::class, 'clearCache'])->name('api.sections.schema.clear-cache');
    Route::get('/pages/{page}/sections/stats', [App\Http\Controllers\Api\SectionSchemaController::class, 'getPageSectionStats'])->name('api.pages.sections.stats');

    Route::get('/sections/{section}/render', [App\Http\Controllers\Api\PageSectionController::class, 'renderSection'])->name('api.sections.render');

    // Page Sections API
    Route::get('/pages/{page}/sections', [App\Http\Controllers\Api\PageSectionController::class, 'index']);
    Route::post('/pages/{page}/sections', [App\Http\Controllers\Api\PageSectionController::class, 'store']);
    Route::put('/pages/{page}/sections/{section}', [App\Http\Controllers\Api\PageSectionController::class, 'update']);
    Route::delete('/pages/{page}/sections/{section}', [App\Http\Controllers\Api\PageSectionController::class, 'destroy']);
    Route::put('/pages/{page}/sections/reorder', [App\Http\Controllers\Api\PageSectionController::class, 'reorder']);
    Route::get('/pages/{page}/template-sections', [App\Http\Controllers\Api\PageSectionController::class, 'getTemplateSections']);
    // Enhanced Section API with GridStack positioning
    Route::patch('/page-sections/{section}/grid', [App\Http\Controllers\Api\PageSectionController::class, 'updateGridPosition']);
    Route::patch('/page-sections/{section}/style', [App\Http\Controllers\Api\PageSectionController::class, 'updateStyles']);
    Route::get('/page-sections/{section}/widgets', [App\Http\Controllers\Api\PageSectionController::class, 'getSectionWidgets']);

    // Section Templates API
    Route::get('/section-templates', [App\Http\Controllers\Api\PageSectionController::class, 'getTemplates']);
    Route::get('/section-templates/{template}', [App\Http\Controllers\Api\PageSectionController::class, 'getTemplate']);

    // Page Section Widgets API
    Route::get('/sections/{section}/widgets', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'index']);
    Route::post('/sections/{section}/widgets', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'store']);
    Route::get('/page-section-widgets/{widget}', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'show']);
    Route::put('/page-section-widgets/{widget}', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'update']);
    Route::delete('/page-section-widgets/{widget}', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'destroy']);
    Route::put('/sections/{section}/widgets/reorder', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'reorder']);

    // Enhanced Widget API with GridStack positioning
    Route::patch('/page-section-widgets/{widget}/grid', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'updateGridPosition']);

    // PageSectionWidget API routes (for GridStack implementation)
    Route::post('/page-section-widgets', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'store']);
    Route::get('/page-section-widgets/{pageSectionWidget}', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'show']);
    Route::put('/page-section-widgets/{pageSectionWidget}', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'update']);
    Route::delete('/page-section-widgets/{pageSectionWidget}', [App\Http\Controllers\Api\PageSectionWidgetController::class, 'destroy']);

    // Widgets API
    Route::get('/widgets', [App\Http\Controllers\Api\WidgetController::class, 'index'])->name('api.widgets.index');
    Route::get('/widgets/{widget}/render', [App\Http\Controllers\Api\WidgetController::class, 'renderWidget'])->name('api.widgets.render');
    Route::get('/widgets/{widget}/preview', [App\Http\Controllers\Api\WidgetController::class, 'renderWidgetPreview'])->name('api.widgets.preview');
    Route::get('/widgets/{widget}/schema', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetSchema'])->name('api.widgets.schema');
    Route::get('/widgets/{widget}/sample-data', [App\Http\Controllers\Api\WidgetController::class, 'getWidgetSampleData'])->name('api.widgets.sample-data');
    Route::get('/widgets/{widget}/content-types', [App\Http\Controllers\Api\WidgetController::class, 'contentTypes'])->name('api.widgets.content-types');
    Route::get('/widgets/test-existing', [App\Http\Controllers\Api\WidgetController::class, 'testExistingWidget'])->name('api.widgets.test-existing');
    Route::get('/widgets/test-featured-image', [App\Http\Controllers\Api\WidgetController::class, 'testFeaturedImageWidget'])->name('api.widgets.test-featured-image');

    // Test route for existing widget
    Route::get('/widgets/test-existing', [App\Http\Controllers\Api\WidgetController::class, 'testExistingWidget']);
    
    // Content Items API
    
    Route::get('/content/{type}', [App\Http\Controllers\Api\ContentItemController::class, 'index']);
    Route::post('/content/{type}', [App\Http\Controllers\Api\ContentItemController::class, 'store']);
    // Test route for live preview debugging
    Route::get('/test-live-preview', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Live preview test route working',
            'timestamp' => now()->toISOString()
        ]);
    })->name('api.test-live-preview');
});
});