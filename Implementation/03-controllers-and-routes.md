# Controllers and Routes

This document outlines the controllers and routes for the CMS architecture.

## Route Structure

The CMS has three main route groups:

1. **Public Routes**: For the public-facing website
2. **Admin Routes**: For the administrative interface
3. **API Routes**: For AJAX operations and potential headless CMS functionality

## Authentication System

The CMS uses a dual authentication system with separate guards for regular users and administrators:

1. **Web Guard**: For regular users accessing the public-facing website
2. **Admin Guard**: For administrators accessing the admin interface

Laravel Fortify is used to provide authentication features, and Spatie Permissions is used for role and permission management.

## Public Routes

```php
// routes/web.php

// Guest routes for user authentication
Route::middleware('guest:web')->group(function () {
    // These routes will be handled by Laravel Fortify with the web guard
    Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::get('/register', [UserAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [UserAuthController::class, 'register']);
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

// Media routes
Route::get('/media/{id}/{filename?}', [MediaController::class, 'show'])->name('media.show');
```

## Admin Routes

```php
// routes/admin.php

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes for admin authentication
    Route::middleware('guest:admin')->group(function () {
        // These routes will be handled by Laravel Fortify with the admin guard
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::get('/forgot-password', [AdminAuthController::class, 'showForgotForm'])->name('forgot');
        Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLink']);
        Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('reset');
        Route::post('/reset-password', [AdminAuthController::class, 'resetPassword']);
    });
    
    // Auth routes for authenticated admins
    Route::middleware(['admin.auth'])->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Pages
        Route::resource('pages', PageController::class);
        
        // Widgets
        Route::resource('widgets', WidgetController::class);
        Route::get('/widgets/{widget}/preview', [WidgetController::class, 'preview'])->name('widgets.preview');
        
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
        
        // Roles
        Route::resource('roles', RoleController::class);
        
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
```

## API Routes

```php
// routes/api.php

Route::prefix('api')->name('api.')->group(function () {
    // Public API routes
    Route::get('/pages', [Api\PageController::class, 'index']);
    Route::get('/pages/{slug}', [Api\PageController::class, 'show']);
    Route::get('/menus/{location}', [Api\MenuController::class, 'byLocation']);
    
    // Admin API routes (requires authentication)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // Widget field options
        Route::get('/widget-types/{widgetType}/fields', [Api\WidgetTypeFieldController::class, 'index']);
        
        // Widget data
        Route::get('/widgets/{widget}/data', [Api\WidgetController::class, 'getData']);
        Route::post('/widgets/{widget}/data', [Api\WidgetController::class, 'saveData']);
        
        // Page sections
        Route::get('/pages/{page}/sections', [Api\PageSectionController::class, 'index']);
        Route::post('/pages/{page}/sections/{section}/widgets', [Api\PageSectionController::class, 'addWidget']);
        Route::delete('/pages/{page}/sections/{section}/widgets/{widget}', [Api\PageSectionController::class, 'removeWidget']);
        Route::post('/pages/{page}/sections/{section}/widgets/order', [Api\PageSectionController::class, 'updateWidgetOrder']);
        
        // Media upload
        Route::post('/media/upload', [Api\MediaController::class, 'upload']);
    });
});
```

## Controller Structure

### Public Controllers

#### PageController

```php
namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the specified page.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug = 'home')
    {
        $page = Page::where('slug', $slug)
                    ->where('is_active', true)
                    ->firstOrFail();
        
        // Get the main navigation menu
        $mainMenu = \App\Models\Menu::getByLocation('main');
        
        // Get the active theme
        $theme = \App\Models\Theme::getActive();
        
        // Get the template
        $template = $page->template;
        
        // For all pages, use the template specified in the page record
        return view("themes.{$theme->slug}.templates.{$template->slug}", [
            'page' => $page,
            'mainMenu' => $mainMenu,
            'theme' => $theme
        ]);
    }
}
```

### Admin Controllers

#### DashboardController

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Widget;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stats = [
            'pages' => Page::count(),
            'widgets' => Widget::count(),
            'users' => User::count(),
            'activeTheme' => \App\Models\Theme::getActive()->name
        ];
        
        $recentPages = Page::orderBy('updated_at', 'desc')->take(5)->get();
        $recentWidgets = Widget::orderBy('updated_at', 'desc')->take(5)->get();
        
        return view('admin.dashboard', compact('stats', 'recentPages', 'recentWidgets'));
    }
}
```

#### PageController (Admin)

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::orderBy('title')->paginate(15);
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pages = Page::orderBy('title')->get();
        $templates = Template::where('is_active', true)->get();
        return view('admin.pages.create', compact('pages', 'templates'));
    }

    /**
     * Store a newly created page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'template_id' => 'required|exists:templates,id',
            'parent_id' => 'nullable|exists:pages,id',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set default values for checkboxes if not present
        $validated['is_active'] = $request->has('is_active');
        $validated['show_in_menu'] = $request->has('show_in_menu');
        
        // Set creator
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $page = Page::create($validated);
        
        // Create page sections based on template
        $template = Template::findOrFail($request->template_id);
        foreach ($template->sections as $section) {
            $page->sections()->create([
                'template_section_id' => $section->id,
                'is_active' => true
            ]);
        }

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        $pages = Page::where('id', '!=', $page->id)->orderBy('title')->get();
        $templates = Template::where('is_active', true)->get();
        return view('admin.pages.edit', compact('page', 'pages', 'templates'));
    }

    /**
     * Update the specified page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages')->ignore($page->id),
            ],
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'template_id' => 'required|exists:templates,id',
            'parent_id' => [
                'nullable',
                'exists:pages,id',
                function ($attribute, $value, $fail) use ($page) {
                    if ($value == $page->id) {
                        $fail('A page cannot be its own parent.');
                    }
                },
            ],
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set default values for checkboxes if not present
        $validated['is_active'] = $request->has('is_active');
        $validated['show_in_menu'] = $request->has('show_in_menu');
        
        // Set updater
        $validated['updated_by'] = auth()->id();

        // Check if template has changed
        $templateChanged = $page->template_id != $request->template_id;
        
        $page->update($validated);
        
        // If template changed, update page sections
        if ($templateChanged) {
            // Remove existing sections and their widget associations
            foreach ($page->sections as $section) {
                $section->widgets()->detach();
                $section->delete();
            }
            
            // Create new sections based on template
            $template = Template::findOrFail($request->template_id);
            foreach ($template->sections as $section) {
                $page->sections()->create([
                    'template_section_id' => $section->id,
                    'is_active' => true
                ]);
            }
        }

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        // Check if page has children
        if ($page->children()->count() > 0) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'Cannot delete page with child pages. Please delete or reassign child pages first.');
        }

        // Check if page has menu items
        if ($page->menuItems()->count() > 0) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'Cannot delete page that is used in menus. Please remove from menus first.');
        }
        
        // Remove page sections and their widget associations
        foreach ($page->sections as $section) {
            $section->widgets()->detach();
            $section->delete();
        }

        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}
```

## Authentication Controllers

### User Authentication Controllers

The user authentication controllers handle authentication for the web guard:

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
    
    /**
     * Handle a logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    // Other authentication methods (registration, password reset, etc.)
}
```

### Admin Authentication Controllers

The admin authentication controllers handle authentication for the admin guard:

```php
namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    
    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
    
    /**
     * Handle a logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
    
    // Other authentication methods (password reset, etc.)
}
```

## Middleware

### Admin Authentication Middleware

This middleware ensures that only authenticated admins can access admin routes:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        return $next($request);
    }
}
```

### Role and Permission Middleware

These middleware are provided by Spatie Permissions to check for specific roles and permissions:

```php
// Example usage in routes
Route::middleware(['role:super-admin', 'permission:manage users'])->group(function () {
    // Routes that require super-admin role and manage users permission
});

// For admin guard
Route::middleware(['role:editor|content-manager,admin', 'permission:manage content,admin'])->group(function () {
    // Routes that require editor or content-manager role and manage content permission on admin guard
});
```

### API Controllers

#### Api\PageController

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Page::query();
        
        // Apply filters
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        
        $pages = $query->orderBy('title')->paginate(15);
        
        return response()->json($pages);
    }

    /**
     * Display the specified page.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        
        // Load sections and widgets
        $page->load(['sections.templateSection', 'sections.widgets']);
        
        return response()->json($page);
    }
}
```

## Middleware

### AdminMiddleware

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
```

## Service Providers

### RouteServiceProvider

```php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
}
```

## Conclusion

This controller and route structure provides a clean separation between:

1. **Public website functionality**: Simple controllers focused on rendering content
2. **Administrative functionality**: Comprehensive CRUD operations for content management
3. **API functionality**: JSON endpoints for AJAX operations and potential headless CMS use

The structure follows RESTful principles and Laravel best practices, with clear naming conventions and proper separation of concerns. The controllers handle validation, business logic, and response generation, while the routes define the URL structure and middleware requirements.
