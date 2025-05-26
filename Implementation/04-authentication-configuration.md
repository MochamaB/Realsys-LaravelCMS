# Authentication Configuration

This document outlines the authentication configuration for the CMS, using Laravel Fortify and Spatie Permissions with multiple guards.

## Authentication Guards

The CMS uses two separate authentication guards:

1. **Web Guard**: For regular users accessing the public-facing website
2. **Admin Guard**: For administrators accessing the admin interface

## Laravel Fortify Integration

### Installation

To install Laravel Fortify, run the following command:

```bash
composer require laravel/fortify
```

After installing the package, publish the configuration and migration files:

```bash
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

### Fortify Configuration

The `config/fortify.php` file should be configured as follows:

```php
<?php

return [
    'guard' => config('auth.defaults.guard', 'web'),
    'passwords' => config('auth.defaults.passwords', 'users'),
    'prefix' => '',
    'domain' => null,
    'home' => '/dashboard',
    'admin' => [
        'home' => '/admin/dashboard',
    ],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication(),
    ],
];
```

### FortifyServiceProvider

The FortifyServiceProvider needs to be customized to handle both web and admin guards:

```php
<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Customize login response based on guard
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                if ($request->is('admin*')) {
                    return redirect()->intended(config('fortify.admin.home', '/admin/dashboard'));
                }
                
                return redirect()->intended(config('fortify.home', '/dashboard'));
            }
        });
        
        // Customize logout response based on guard
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                if ($request->is('admin*')) {
                    return redirect()->route('admin.login');
                }
                
                return redirect('/');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure based on request path
        if (request()->is('admin*')) {
            config(['fortify.guard' => 'admin']);
            config(['fortify.home' => '/admin/dashboard']);
            config(['fortify.prefix' => 'admin']);
            Fortify::viewPrefix('admin.auth.');
        } else {
            Fortify::viewPrefix('auth.');
        }
        
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
```

## Spatie Permissions Integration

### Installation

To install Spatie Permissions, run the following command:

```bash
composer require spatie/laravel-permission
```

After installing the package, publish the configuration and migration files:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Permission Configuration

The `config/permission.php` file should be configured as follows:

```php
<?php

return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'register_permission_check_method' => true,
    'teams' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],

    'guard_names' => [
        'web',
        'admin',
    ],
];
```

## Auth Configuration

The `config/auth.php` file needs to be updated to include both guards:

```php
<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
```

## Middleware Configuration

The `app/Http/Kernel.php` file needs to be updated to include the admin authentication middleware:

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ...
    
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        'admin.auth' => \App\Http\Middleware\AdminAuthentication::class,
    ];
}
```

## Creating Roles and Permissions

Roles and permissions can be created for specific guards using a seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Admin permissions
        $adminPermissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage content',
            'manage settings',
            'manage themes',
            'manage media',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Admin roles
        $superAdmin = Role::create(['name' => 'super-admin', 'guard_name' => 'admin']);
        $editor = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $contentManager = Role::create(['name' => 'content-manager', 'guard_name' => 'admin']);

        // Super admin gets all permissions
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // Editor permissions
        $editor->givePermissionTo([
            'manage content',
            'manage media',
        ]);

        // Content manager permissions
        $contentManager->givePermissionTo([
            'manage content',
        ]);

        // User permissions
        $userPermissions = [
            'submit content',
            'edit own content',
            'delete own content',
        ];

        foreach ($userPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // User roles
        $subscriber = Role::create(['name' => 'subscriber', 'guard_name' => 'web']);
        $contributor = Role::create(['name' => 'contributor', 'guard_name' => 'web']);

        // Contributor permissions
        $contributor->givePermissionTo([
            'submit content',
            'edit own content',
            'delete own content',
        ]);
    }
}
```

## Implementation Steps

1. Install required packages:
   ```bash
   composer require spatie/laravel-permission
   composer require laravel/fortify
   ```

2. Publish and run the migrations:
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
   php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --tag="migrations"
   php artisan migrate
   ```

3. Publish the configuration files:
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"
   php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --tag="config"
   ```

4. Create the Admin model and migration
5. Update the User model to use HasRoles trait
6. Create the AdminAuthentication middleware
7. Update the auth configuration
8. Create the FortifyServiceProvider
9. Update the routes to use the appropriate guards
10. Create the necessary views for authentication
11. Run the roles and permissions seeder
