# Admin Interface

This document outlines the admin interface architecture for the CMS, focusing on the backend logic while leveraging the Velzon admin template (from themesbrand.com/velzon/html/master).

## Admin Interface Overview

The admin interface will be built on top of the Velzon template, which provides a modern, responsive design with numerous UI components. The implementation will focus on integrating the template with the CMS backend logic.

## Admin Layout Structure

The admin interface will use a layout structure based on Velzon:

```
resources/
  views/
    admin/
      layouts/
        master.blade.php        # Main layout with header, sidebar, footer
        auth.blade.php          # Layout for authentication pages
      partials/
        header.blade.php        # Admin header
        sidebar.blade.php       # Admin sidebar
        footer.blade.php        # Admin footer
      dashboard/
        index.blade.php         # Dashboard view
      pages/
        index.blade.php         # Page listing
        create.blade.php        # Create page
        edit.blade.php          # Edit page
        show.blade.php          # Page details
      widgets/
        index.blade.php         # Widget listing
        create.blade.php        # Create widget
        edit.blade.php          # Edit widget
        preview.blade.php       # Widget preview
      widget-types/
        index.blade.php         # Widget type listing
        create.blade.php        # Create widget type
        edit.blade.php          # Edit widget type
        fields/
          index.blade.php       # Field listing
          create.blade.php      # Create field
          edit.blade.php        # Edit field
      menus/
        index.blade.php         # Menu listing
        create.blade.php        # Create menu
        edit.blade.php          # Edit menu
        items/
          index.blade.php       # Menu item listing
          create.blade.php      # Create menu item
          edit.blade.php        # Edit menu item
      themes/
        index.blade.php         # Theme listing
        show.blade.php          # Theme details
      media/
        index.blade.php         # Media library
        upload.blade.php        # Media upload
      users/
        index.blade.php         # User listing
        create.blade.php        # Create user
        edit.blade.php          # Edit user
      roles/
        index.blade.php         # Role listing
        create.blade.php        # Create role
        edit.blade.php          # Edit role
      settings/
        index.blade.php         # Settings page
      auth/
        login.blade.php         # Login page
        register.blade.php      # Register page
        forgot-password.blade.php # Forgot password page
        reset-password.blade.php  # Reset password page
```

## Admin Authentication

The authentication system will use Laravel's built-in authentication with custom controllers:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    
    /**
     * Handle login request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
    
    /**
     * Handle logout request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
    
    // Additional authentication methods (forgot password, reset password, etc.)
}
```

## Admin Dashboard

The dashboard will display key statistics and recent activity:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Widget;
use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats = [
            'pages' => Page::count(),
            'widgets' => Widget::count(),
            'users' => User::count(),
            'media' => Media::count(),
            'activeTheme' => \App\Models\Theme::getActive()->name ?? 'None'
        ];
        
        $recentPages = Page::orderBy('updated_at', 'desc')->take(5)->get();
        $recentWidgets = Widget::orderBy('updated_at', 'desc')->take(5)->get();
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        
        return view('admin.dashboard.index', compact(
            'stats', 
            'recentPages', 
            'recentWidgets', 
            'recentUsers'
        ));
    }
}
```

## Page Management

The page management interface will allow admins to create, edit, and delete pages:

### Page Form Component

```php
namespace App\View\Components\Admin;

use App\Models\Page;
use App\Models\Template;
use Illuminate\View\Component;

class PageForm extends Component
{
    public $page;
    public $templates;
    public $pages;
    public $action;
    
    /**
     * Create a new component instance.
     *
     * @param Page|null $page
     * @param string $action
     * @return void
     */
    public function __construct($page = null, $action = 'create')
    {
        $this->page = $page;
        $this->action = $action;
        $this->templates = Template::where('is_active', true)->get();
        
        if ($page) {
            $this->pages = Page::where('id', '!=', $page->id)->orderBy('title')->get();
        } else {
            $this->pages = Page::orderBy('title')->get();
        }
    }
    
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin.components.page-form');
    }
}
```

### Page Section Management

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Widget;
use Illuminate\Http\Request;

class PageSectionController extends Controller
{
    /**
     * Display the sections for a page
     *
     * @param Page $page
     * @return \Illuminate\View\View
     */
    public function index(Page $page)
    {
        $page->load('sections.templateSection', 'sections.widgets');
        $availableWidgets = Widget::where('is_active', true)->get();
        
        return view('admin.pages.sections.index', compact('page', 'availableWidgets'));
    }
    
    /**
     * Add a widget to a page section
     *
     * @param Request $request
     * @param Page $page
     * @param PageSection $section
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addWidget(Request $request, Page $page, PageSection $section)
    {
        $validated = $request->validate([
            'widget_id' => 'required|exists:widgets,id',
        ]);
        
        // Get the highest order index
        $maxOrder = $section->widgets()->max('page_widgets.order_index') ?? -1;
        
        // Attach the widget with the next order index
        $section->widgets()->attach($validated['widget_id'], [
            'order_index' => $maxOrder + 1
        ]);
        
        return redirect()->route('admin.pages.sections.index', $page)
            ->with('success', 'Widget added to section successfully.');
    }
    
    /**
     * Remove a widget from a page section
     *
     * @param Page $page
     * @param PageSection $section
     * @param Widget $widget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeWidget(Page $page, PageSection $section, Widget $widget)
    {
        $section->widgets()->detach($widget->id);
        
        return redirect()->route('admin.pages.sections.index', $page)
            ->with('success', 'Widget removed from section successfully.');
    }
    
    /**
     * Update the order of widgets in a section
     *
     * @param Request $request
     * @param Page $page
     * @param PageSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateWidgetOrder(Request $request, Page $page, PageSection $section)
    {
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*' => 'exists:widgets,id',
        ]);
        
        // Update the order of widgets
        foreach ($validated['widgets'] as $index => $widgetId) {
            $section->widgets()->updateExistingPivot($widgetId, [
                'order_index' => $index
            ]);
        }
        
        return response()->json(['success' => true]);
    }
}
```

## Widget Management

The widget management interface will allow admins to create, edit, and delete widgets:

### Widget Form Component

```php
namespace App\View\Components\Admin;

use App\Models\Widget;
use App\Models\WidgetType;
use App\Services\WidgetFormService;
use Illuminate\View\Component;

class WidgetForm extends Component
{
    public $widget;
    public $widgetType;
    public $widgetTypes;
    public $formFields;
    public $action;
    
    /**
     * Create a new component instance.
     *
     * @param Widget|null $widget
     * @param WidgetType|null $widgetType
     * @param string $action
     * @return void
     */
    public function __construct($widget = null, $widgetType = null, $action = 'create')
    {
        $this->widget = $widget;
        $this->widgetType = $widgetType ?? ($widget ? $widget->widgetType : null);
        $this->action = $action;
        $this->widgetTypes = WidgetType::where('is_active', true)->get();
        
        if ($this->widgetType) {
            $formService = new WidgetFormService();
            $this->formFields = $formService->generateFormFields($this->widgetType, $widget);
        }
    }
    
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin.components.widget-form');
    }
}
```

### Widget Type Field Management

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WidgetType;
use App\Models\WidgetTypeField;
use Illuminate\Http\Request;

class WidgetTypeFieldController extends Controller
{
    /**
     * Display the fields for a widget type
     *
     * @param WidgetType $widgetType
     * @return \Illuminate\View\View
     */
    public function index(WidgetType $widgetType)
    {
        $fields = $widgetType->fields()->orderBy('order_index')->get();
        
        return view('admin.widget-types.fields.index', compact('widgetType', 'fields'));
    }
    
    /**
     * Store a new field
     *
     * @param Request $request
     * @param WidgetType $widgetType
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, WidgetType $widgetType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'is_required' => 'boolean',
            'is_repeatable' => 'boolean',
            'validation_rules' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'default_value' => 'nullable|string',
        ]);
        
        // Get the highest order index
        $maxOrder = $widgetType->fields()->max('order_index') ?? -1;
        
        // Set default values for checkboxes
        $validated['is_required'] = $request->has('is_required');
        $validated['is_repeatable'] = $request->has('is_repeatable');
        
        // Create the field with the next order index
        $validated['order_index'] = $maxOrder + 1;
        $field = $widgetType->fields()->create($validated);
        
        // If field is a select, radio, or checkbox, create options
        if (in_array($validated['field_type'], ['select', 'radio', 'checkbox']) && $request->has('options')) {
            $options = $request->input('options');
            
            foreach ($options as $index => $option) {
                if (!empty($option['value']) && !empty($option['label'])) {
                    $field->options()->create([
                        'value' => $option['value'],
                        'label' => $option['label'],
                        'order_index' => $index
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.widget-types.fields.index', $widgetType)
            ->with('success', 'Field created successfully.');
    }
    
    /**
     * Update a field
     *
     * @param Request $request
     * @param WidgetType $widgetType
     * @param WidgetTypeField $field
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, WidgetType $widgetType, WidgetTypeField $field)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'is_required' => 'boolean',
            'is_repeatable' => 'boolean',
            'validation_rules' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'default_value' => 'nullable|string',
        ]);
        
        // Set default values for checkboxes
        $validated['is_required'] = $request->has('is_required');
        $validated['is_repeatable'] = $request->has('is_repeatable');
        
        // Update the field
        $field->update($validated);
        
        // If field is a select, radio, or checkbox, update options
        if (in_array($validated['field_type'], ['select', 'radio', 'checkbox']) && $request->has('options')) {
            // Remove existing options
            $field->options()->delete();
            
            // Add new options
            $options = $request->input('options');
            
            foreach ($options as $index => $option) {
                if (!empty($option['value']) && !empty($option['label'])) {
                    $field->options()->create([
                        'value' => $option['value'],
                        'label' => $option['label'],
                        'order_index' => $index
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.widget-types.fields.index', $widgetType)
            ->with('success', 'Field updated successfully.');
    }
    
    /**
     * Update the order of fields
     *
     * @param Request $request
     * @param WidgetType $widgetType
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request, WidgetType $widgetType)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'exists:widget_type_fields,id',
        ]);
        
        // Update the order of fields
        foreach ($validated['fields'] as $index => $fieldId) {
            WidgetTypeField::where('id', $fieldId)
                ->where('widget_type_id', $widgetType->id)
                ->update(['order_index' => $index]);
        }
        
        return response()->json(['success' => true]);
    }
}
```

## Menu Management

The menu management interface will allow admins to create, edit, and delete menus and menu items:

### Menu Item Form Component

```php
namespace App\View\Components\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\View\Component;

class MenuItemForm extends Component
{
    public $menu;
    public $menuItem;
    public $menuItems;
    public $pages;
    public $action;
    
    /**
     * Create a new component instance.
     *
     * @param Menu $menu
     * @param MenuItem|null $menuItem
     * @param string $action
     * @return void
     */
    public function __construct(Menu $menu, $menuItem = null, $action = 'create')
    {
        $this->menu = $menu;
        $this->menuItem = $menuItem;
        $this->action = $action;
        
        // Get menu items for parent selection (excluding current item and its descendants)
        if ($menuItem) {
            $excludeIds = $menuItem->descendants()->pluck('id')->push($menuItem->id)->toArray();
            $this->menuItems = $menu->items()->whereNotIn('id', $excludeIds)->orderBy('title')->get();
        } else {
            $this->menuItems = $menu->items()->orderBy('title')->get();
        }
        
        // Get pages for page selection
        $this->pages = Page::where('is_active', true)->orderBy('title')->get();
    }
    
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin.components.menu-item-form');
    }
}
```

## Theme Management

The theme management interface will allow admins to view and activate themes:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\ThemeManager;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Display a listing of themes
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $themes = Theme::all();
        $activeTheme = Theme::where('is_active', true)->first();
        
        return view('admin.themes.index', compact('themes', 'activeTheme'));
    }
    
    /**
     * Display the specified theme
     *
     * @param Theme $theme
     * @return \Illuminate\View\View
     */
    public function show(Theme $theme)
    {
        $theme->load('templates.sections');
        
        return view('admin.themes.show', compact('theme'));
    }
    
    /**
     * Activate a theme
     *
     * @param Request $request
     * @param Theme $theme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Request $request, Theme $theme)
    {
        $themeManager = new ThemeManager();
        $themeManager->activateTheme($theme);
        
        return redirect()->route('admin.themes.index')
            ->with('success', "Theme '{$theme->name}' activated successfully.");
    }
}
```

## Media Library

The media library will allow admins to upload and manage media files:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display the media library
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Media::query();
        
        // Apply filters
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
        }
        
        if ($request->has('type')) {
            $type = $request->input('type');
            $query->where('mime_type', 'like', "{$type}/%");
        }
        
        $media = $query->orderBy('created_at', 'desc')->paginate(24);
        
        return view('admin.media.index', compact('media'));
    }
    
    /**
     * Show the upload form
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.media.upload');
    }
    
    /**
     * Upload media files
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB max
        ]);
        
        $uploadedFiles = [];
        
        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();
            
            // Generate a unique filename
            $uniqueFilename = Str::slug($name) . '-' . uniqid() . '.' . $extension;
            
            // Store the file
            $path = $file->storeAs('media', $uniqueFilename, 'public');
            
            // Create media record
            $media = Media::create([
                'name' => $name,
                'file_name' => $uniqueFilename,
                'mime_type' => $mimeType,
                'size' => $size,
                'path' => $path,
                'disk' => 'public',
                'uploaded_by' => auth()->id(),
            ]);
            
            $uploadedFiles[] = $media;
        }
        
        return redirect()->route('admin.media.index')
            ->with('success', count($uploadedFiles) . ' file(s) uploaded successfully.');
    }
    
    /**
     * Delete a media file
     *
     * @param Media $media
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Media $media)
    {
        // Delete the file
        Storage::disk($media->disk)->delete($media->path);
        
        // Delete the record
        $media->delete();
        
        return redirect()->route('admin.media.index')
            ->with('success', 'File deleted successfully.');
    }
}
```

## User and Role Management

The user and role management interfaces will allow admins to manage users and their permissions:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Show the form for creating a new user
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::all();
        
        return view('admin.users.create', compact('roles'));
    }
    
    /**
     * Store a newly created user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);
        
        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        // Assign roles
        if (isset($validated['roles'])) {
            $user->roles()->attach($validated['roles']);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }
    
    /**
     * Show the form for editing a user
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }
    
    /**
     * Update the specified user
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);
        
        // Update user
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        // Sync roles
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        } else {
            $user->roles()->detach();
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
}
```

## Settings Management

The settings management interface will allow admins to configure system settings:

```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $settingsService;
    
    /**
     * Create a new controller instance.
     *
     * @param SettingsService $settingsService
     * @return void
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }
    
    /**
     * Display the settings page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = $this->settingsService->getAllSettings();
        
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * Update settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'site_logo' => 'nullable|string',
            'site_favicon' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'social_media' => 'nullable|array',
            'analytics_code' => 'nullable|string',
            'footer_text' => 'nullable|string',
        ]);
        
        foreach ($validated as $key => $value) {
            $this->settingsService->set($key, $value);
        }
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
```

## Conclusion

This admin interface architecture leverages the Velzon admin template while implementing the backend logic for the CMS. Key features include:

1. **Dashboard**: Displays key statistics and recent activity
2. **Page Management**: Create, edit, and delete pages with sections
3. **Widget Management**: Create, edit, and delete widgets with dynamic fields
4. **Menu Management**: Create, edit, and delete menus and menu items
5. **Theme Management**: View and activate themes
6. **Media Library**: Upload and manage media files
7. **User and Role Management**: Manage users and their permissions
8. **Settings Management**: Configure system settings

The implementation focuses on the backend logic, with the frontend components leveraging the Velzon template. This approach provides a modern, responsive admin interface with minimal custom frontend code.
