<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\WidgetController;
use App\Http\Controllers\Admin\WidgetTypeController;
use App\Http\Controllers\Admin\WidgetTypeFieldController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TemplateSectionController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ProfileController;

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
    
    // Dashboard routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Pages
    Route::resource('pages', PageController::class);

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
});