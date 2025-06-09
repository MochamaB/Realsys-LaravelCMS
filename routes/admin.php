<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\ContentTypeController;
use App\Http\Controllers\Admin\ContentTypeFieldController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
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
    
    // Dashboard routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Pages
    Route::resource('pages', PageController::class);
    Route::put('/pages/{page}/homepage', [PageController::class, 'toggleHomepage'])->name('admin.pages.homepage');
    Route::get('/pages/{page}/sections', [PageController::class, 'getSections'])->name('pages.sections');

    // Widgets
    Route::resource('widgets', WidgetController::class);
    Route::get('/widgets/{widget}/preview', [WidgetController::class, 'preview'])->name('widgets.preview');
    Route::patch('/widgets/{widget}/toggle', [WidgetController::class, 'toggle'])->name('widgets.toggle');
        

   

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

    // Media Library
    Route::resource('media', MediaController::class);
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

    // Users
    Route::resource('users', UserController::class);

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
        Route::get('items/{item}/preview', [ContentItemController::class, 'preview'])->name('content-types.items.preview');
    });
});