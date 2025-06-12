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
  
    Route::get('/force-change-password', [UserProfileController::class, 'showForceChangePassword'])
        ->name('password.force_change');
    Route::post('/force-change-password', [UserProfileController::class, 'updateForceChangePassword'])
        ->name('password.force_change.update');
});

// Auth routes for authenticated users
Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
});

// User Dashboard Routes
Route::middleware(['auth:web', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

    // Profile
    Route::get('/profile', function () {
        return view('user.profile');
    })->name('user.profile');

    // Membership
    Route::prefix('membership')->group(function () {
        Route::get('/', function () {
            return view('user.membership.index');
        })->name('user.membership');
        
        Route::get('/card', function () {
            return view('user.membership.card');
        })->name('user.membership.card');
        
        Route::get('/payments', function () {
            return view('user.membership.payments');
        })->name('user.membership.payments');
    });

    // Resources
    Route::prefix('resources')->group(function () {
        Route::get('/documents', function () {
            return view('user.resources.documents');
        })->name('user.resources.documents');
        
        Route::get('/events', function () {
            return view('user.resources.events');
        })->name('user.resources.events');
        
        Route::get('/news', function () {
            return view('user.resources.news');
        })->name('user.resources.news');
    });

    // Volunteer Portal
    Route::prefix('volunteer')->group(function () {
        Route::get('/activities', function () {
            return view('user.volunteer.activities');
        })->name('user.volunteer.activities');
        
        Route::get('/tasks', function () {
            return view('user.volunteer.tasks');
        })->name('user.volunteer.tasks');
        
        Route::get('/training', function () {
            return view('user.volunteer.training');
        })->name('user.volunteer.training');
    });

    // Communication
    Route::get('/messages', function () {
        return view('user.messages');
    })->name('user.messages');

    Route::get('/notifications', function () {
        return view('user.notifications');
    })->name('user.notifications');

    Route::get('/feedback', function () {
        return view('user.feedback');
    })->name('user.feedback');

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/account', function () {
            return view('user.settings.account');
        })->name('user.settings.account');
        
        Route::get('/privacy', function () {
            return view('user.settings.privacy');
        })->name('user.settings.privacy');
        
        Route::get('/notifications', function () {
            return view('user.settings.notifications');
        })->name('user.settings.notifications');
    });
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
