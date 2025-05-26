<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\Api\PageSectionController;
use App\Http\Controllers\Api\MediaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes
Route::prefix('api')->name('api.')->group(function () {
    // Public API routes
    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{slug}', [PageController::class, 'show']);
    Route::get('/menus/{location}', [MenuController::class, 'byLocation']);
    
    // Protected API routes
    Route::middleware('auth:sanctum')->group(function () {
        // Pages
        Route::post('/pages', [PageController::class, 'store']);
        Route::put('/pages/{page}', [PageController::class, 'update']);
        Route::delete('/pages/{page}', [PageController::class, 'destroy']);
        
        // Widgets
        Route::get('/widgets/{widget}', [WidgetController::class, 'show']);
        Route::post('/widgets/{widget}/data', [WidgetController::class, 'saveData']);
        
        // Page sections
        Route::get('/pages/{page}/sections', [PageSectionController::class, 'index']);
        Route::post('/pages/{page}/sections/{section}/widgets', [PageSectionController::class, 'addWidget']);
        Route::delete('/pages/{page}/sections/{section}/widgets/{widget}', [PageSectionController::class, 'removeWidget']);
        Route::post('/pages/{page}/sections/{section}/widgets/order', [PageSectionController::class, 'updateWidgetOrder']);
        
        // Media upload
        Route::post('/media/upload', [MediaController::class, 'upload']);
    });
});
