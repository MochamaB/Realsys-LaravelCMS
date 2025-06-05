<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserProfileController;

// Test routes to verify routing
Route::get('/test-route', function() {
    return 'Basic web route is working!';
});

Route::get('/test-admin', function() {
    return 'Admin test route from web.php is working!';
});

// Guest routes for user authentication
Route::middleware('guest:web')->group(function () {
    // User authentication routes with standard Laravel naming convention
    Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [UserAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [UserAuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [UserAuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [UserAuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [UserAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [UserAuthController::class, 'resetPassword'])->name('password.update');
});

// Auth routes for authenticated users
Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
});

// Dynamic page routes (accessible to all)
Route::get('/', [PageController::class, 'show'])->name('home');
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show')->where('slug', '[a-z0-9-]+');

// Page preview route (requires authentication)
Route::get('/preview/{id}', [PageController::class, 'preview'])->name('page.preview')
    ->middleware(['auth:web,admin']);

    // Add this above the fallback route
// Dynamic slug route - exclude 'admin' and other reserved paths
Route::get('/{slug}', [PageController::class, 'show'])
    ->name('page.show')
    ->where('slug', '^(?!admin)[a-z0-9-]+$');

// Fallback route for CMS pages
Route::fallback([PageController::class, 'resolve'])->name('page.resolve');
