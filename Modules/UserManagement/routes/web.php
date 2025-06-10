<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\App\Http\Controllers\RegistrationController;
use Modules\UserManagement\Http\Controllers\UserManagementController;

// Public routes for registration
Route::group(['prefix' => 'join', 'as' => 'usermanagement.', 'middleware' => 'web'], function () {
    // Registration form
    Route::get('register', [RegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegistrationController::class, 'register'])->name('register.submit');
    Route::get('success', [RegistrationController::class, 'showSuccess'])->name('registration.success');
    
    // AJAX routes for dependent dropdowns
    Route::get('constituencies', [RegistrationController::class, 'getConstituencies'])->name('constituencies');
    Route::get('wards', [RegistrationController::class, 'getWards'])->name('wards');
});

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('usermanagements', UserManagementController::class)->names('usermanagement');
});
