<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\App\Http\Controllers\RegistrationController;
use Modules\UserManagement\App\Http\Controllers\UserManagementController;

// Public routes for registration
Route::group(['prefix' => 'join', 'as' => 'usermanagement.', 'middleware' => 'web'], function () {
    // Registration form

    Route::get('success', [RegistrationController::class, 'showSuccess'])->name('registration.success');
    Route::get('wizard', [RegistrationController::class, 'showWizard'])->name('register.wizard');
    // Registration Wizard

    // Step 1: Profile Type
    Route::get('register/step1', [RegistrationController::class, 'showStep1'])->name('register.wizard.step1');
    Route::post('register/step1', [RegistrationController::class, 'submitStep1'])->name('register.wizard.step1.post');
    
    // Step 2: Personal Information
    Route::get('register/step2', [RegistrationController::class, 'showStep2'])->name('register.wizard.step2');
    Route::post('register/step2', [RegistrationController::class, 'submitStep2'])->name('register.wizard.step2.post');
    
    // Step 3: Additional Information
    Route::get('register/step3', [RegistrationController::class, 'showStep3'])->name('register.wizard.step3');
    Route::post('register/step3', [RegistrationController::class, 'submitStep3'])->name('register.wizard.step3.post');
    
    // Step 4: Geographic Information
    Route::get('register/step4', [RegistrationController::class, 'showStep4'])->name('register.wizard.step4');
    Route::post('register/step4', [RegistrationController::class, 'submitStep4'])->name('register.wizard.step4.post');
    
    // Step 5: Terms & Photo
    Route::get('register/step5', [RegistrationController::class, 'showStep5'])->name('register.wizard.step5');
    Route::post('register/step5', [RegistrationController::class, 'submitStep5'])->name('register.wizard.step5.post');
    
    // AJAX data fetching routes
    Route::get('constituencies', [RegistrationController::class, 'getConstituencies'])->name('constituencies');
    Route::get('wards', [RegistrationController::class, 'getWards'])->name('wards');
});

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('usermanagements', UserManagementController::class)->names('usermanagement');

});
