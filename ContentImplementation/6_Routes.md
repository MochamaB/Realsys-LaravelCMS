# Content-Driven CMS Architecture: Routes

This document details the Laravel routes required to implement the content-driven CMS architecture. Routes are organized by section and include admin routes, API routes, and frontend routes.

## Admin Routes

Add the following to your `routes/admin.php` file:

```php
<?php

use App\Http\Controllers\Admin\ContentTypeController;
use App\Http\Controllers\Admin\ContentTypeFieldController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\ContentCategoryController;
use App\Http\Controllers\Admin\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Content Management Routes
|--------------------------------------------------------------------------
*/

// Content Types
Route::group(['prefix' => 'content-types', 'as' => 'content-types.'], function () {
    Route::get('/', [ContentTypeController::class, 'index'])->name('index');
    Route::get('/create', [ContentTypeController::class, 'create'])->name('create');
    Route::post('/', [ContentTypeController::class, 'store'])->name('store');
    Route::get('/{contentType}', [ContentTypeController::class, 'show'])->name('show');
    Route::get('/{contentType}/edit', [ContentTypeController::class, 'edit'])->name('edit');
    Route::put('/{contentType}', [ContentTypeController::class, 'update'])->name('update');
    Route::delete('/{contentType}', [ContentTypeController::class, 'destroy'])->name('destroy');
    
    // Content Type Fields
    Route::group(['prefix' => '{contentType}/fields', 'as' => 'fields.'], function () {
        Route::get('/', [ContentTypeFieldController::class, 'index'])->name('index');
        Route::get('/create', [ContentTypeFieldController::class, 'create'])->name('create');
        Route::post('/', [ContentTypeFieldController::class, 'store'])->name('store');
        Route::get('/{field}', [ContentTypeFieldController::class, 'show'])->name('show');
        Route::get('/{field}/edit', [ContentTypeFieldController::class, 'edit'])->name('edit');
        Route::put('/{field}', [ContentTypeFieldController::class, 'update'])->name('update');
        Route::delete('/{field}', [ContentTypeFieldController::class, 'destroy'])->name('destroy');
        Route::post('/order', [ContentTypeFieldController::class, 'updateOrder'])->name('order');
    });
    
    // Content Items
    Route::group(['prefix' => '{contentType}/content-items', 'as' => 'content-items.'], function () {
        Route::get('/', [ContentItemController::class, 'index'])->name('index');
        Route::get('/create', [ContentItemController::class, 'create'])->name('create');
        Route::post('/', [ContentItemController::class, 'store'])->name('store');
        Route::get('/{contentItem}', [ContentItemController::class, 'show'])->name('show');
        Route::get('/{contentItem}/edit', [ContentItemController::class, 'edit'])->name('edit');
        Route::put('/{contentItem}', [ContentItemController::class, 'update'])->name('update');
        Route::delete('/{contentItem}', [ContentItemController::class, 'destroy'])->name('destroy');
        Route::get('/{contentItem}/preview', [ContentItemController::class, 'preview'])->name('preview');
    });
});

// Content Items (all types)
Route::group(['prefix' => 'content-items', 'as' => 'content-items.'], function () {
    Route::get('/', [ContentItemController::class, 'index'])->name('index');
});

// Content Categories
Route::group(['prefix' => 'content-categories', 'as' => 'content-categories.'], function () {
    Route::get('/', [ContentCategoryController::class, 'index'])->name('index');
    Route::get('/create', [ContentCategoryController::class, 'create'])->name('create');
    Route::post('/', [ContentCategoryController::class, 'store'])->name('store');
    Route::get('/{category}', [ContentCategoryController::class, 'show'])->name('show');
    Route::get('/{category}/edit', [ContentCategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [ContentCategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [ContentCategoryController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Widget System Routes
|--------------------------------------------------------------------------
*/

// Updated Widget Routes
Route::group(['prefix' => 'widgets', 'as' => 'widgets.'], function () {
    Route::get('/', [WidgetController::class, 'index'])->name('index');
    Route::get('/create', [WidgetController::class, 'create'])->name('create');
    Route::post('/', [WidgetController::class, 'store'])->name('store');
    Route::get('/{widget}', [WidgetController::class, 'show'])->name('show');
    Route::get('/{widget}/edit', [WidgetController::class, 'edit'])->name('edit');
    Route::put('/{widget}', [WidgetController::class, 'update'])->name('update');
    Route::delete('/{widget}', [WidgetController::class, 'destroy'])->name('destroy');
    Route::get('/{widget}/preview', [WidgetController::class, 'preview'])->name('preview');
});
```

## API Routes

Add the following to your `routes/api.php` file:

```php
<?php

use App\Http\Controllers\Api\ContentItemController;
use App\Http\Controllers\Api\ContentTypeController;
use App\Http\Controllers\Api\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Content API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Content Types
    Route::get('/content-types', [ContentTypeController::class, 'index']);
    Route::get('/content-types/{contentType}', [ContentTypeController::class, 'show']);
    
    // Content Items
    Route::get('/content-items', [ContentItemController::class, 'index']);
    Route::get('/content-items/{contentItem}', [ContentItemController::class, 'show']);
    
    // Widgets
    Route::get('/widgets', [WidgetController::class, 'index']);
    Route::get('/widgets/{widget}', [WidgetController::class, 'show']);
});
```

## Web Routes

Add the following to your `routes/web.php` file:

```php
<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Content Routes
|--------------------------------------------------------------------------
*/

// Content item routes
Route::get('/content/{contentType}/{slug}', [ContentController::class, 'show'])->name('content.show');
Route::get('/content/{contentType}', [ContentController::class, 'index'])->name('content.index');

// Existing page routes
Route::get('/{slug?}', [PageController::class, 'show'])->name('page.show')->where('slug', '.*');
```

## Route Registration

Ensure your routes are properly registered in the `RouteServiceProvider.php` file:

```php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
                
            Route::middleware(['web', 'auth:admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
```
