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
use App\Http\Controllers\Admin\WidgetTypeController;
use App\Http\Controllers\Admin\WidgetTypeFieldController;
use App\Http\Controllers\Admin\WidgetTypeFieldOptionController;

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
    // Add this to your admin routes
    Route::put('/pages/{page}/homepage', [PageController::class, 'toggleHomepage'])->name('admin.pages.homepage');

    // Widgets
    Route::resource('widgets', WidgetController::class);
    Route::get('/widgets/{widget}/preview', [WidgetController::class, 'preview'])->name('widgets.preview');
    Route::patch('/widgets/{widget}/toggle', [WidgetController::class, 'toggle'])->name('widgets.toggle');

    // Widget Types
    Route::resource('widget-types', WidgetTypeController::class);
    Route::get('/widget-types/{widgetType}/fields', [WidgetTypeFieldController::class, 'index'])->name('widget-types.fields.index');
    Route::post('/widget-types/{widgetType}/fields', [WidgetTypeFieldController::class, 'store'])->name('widget-types.fields.store');
    Route::put('/widget-types/{widgetType}/fields/{field}', [WidgetTypeFieldController::class, 'update'])->name('widget-types.fields.update');
    Route::delete('/widget-types/{widgetType}/fields/{field}', [WidgetTypeFieldController::class, 'destroy'])->name('widget-types.fields.destroy');
    Route::post('/widget-types/{widgetType}/fields/order', [WidgetTypeFieldController::class, 'updateOrder'])->name('widget-types.fields.order');
    
    // Widget Type Field Options
    Route::get('/widget-types/fields/{field}/options', [WidgetTypeFieldOptionController::class, 'index'])->name('widget-types.fields.options.index');
    Route::get('/widget-types/fields/{field}/options/create', [WidgetTypeFieldOptionController::class, 'create'])->name('widget-types.fields.options.create');
    Route::post('/widget-types/fields/{field}/options', [WidgetTypeFieldOptionController::class, 'store'])->name('widget-types.fields.options.store');
    Route::get('/widget-types/fields/{field}/options/{option}/edit', [WidgetTypeFieldOptionController::class, 'edit'])->name('widget-types.fields.options.edit');
    Route::put('/widget-types/fields/{field}/options/{option}', [WidgetTypeFieldOptionController::class, 'update'])->name('widget-types.fields.options.update');
    Route::delete('/widget-types/fields/{field}/options/{option}', [WidgetTypeFieldOptionController::class, 'destroy'])->name('widget-types.fields.options.destroy');
    Route::post('/widget-types/fields/{field}/options/reorder', [WidgetTypeFieldOptionController::class, 'reorder'])->name('widget-types.fields.options.reorder');

    // Menus
    Route::resource('menus', MenuController::class);
    Route::get('/menus/{menu}/items', [MenuItemController::class, 'index'])->name('menus.items.index');
    Route::post('/menus/{menu}/items', [MenuItemController::class, 'store'])->name('menus.items.store');
    Route::put('/menus/{menu}/items/{item}', [MenuItemController::class, 'update'])->name('menus.items.update');
    Route::delete('/menus/{menu}/items/{item}', [MenuItemController::class, 'destroy'])->name('menus.items.destroy');
    Route::post('/menus/{menu}/items/order', [MenuItemController::class, 'updateOrder'])->name('menus.items.order');

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
        Route::post('sections/order', [TemplateSectionController::class, 'updateOrder'])->name('templates.sections.order');
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