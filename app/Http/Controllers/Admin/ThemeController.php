<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    /**
     * @var ThemeManager
     */
    protected $themeManager;
    
    /**
     * Constructor
     */
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }
    
    /**
     * Display a listing of all themes.
     */
    public function index()
    {
        // Scan and register any new themes in the filesystem
        $this->themeManager->scanAndRegisterThemes();
        
        $themes = Theme::all();
        $activeTheme = Theme::where('is_active', true)->first();
        
        return view('admin.themes.index', compact('themes', 'activeTheme'));
    }

    /**
     * Show the form for creating a new theme.
     */
    public function create()
    {
        return view('admin.themes.create');
    }

    /**
     * Store a newly created theme in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'author' => 'nullable|string|max:255',
            'screenshot' => 'nullable|image|max:2048',
        ]);

        // Generate slug from name
        $slug = Str::slug($validated['name']);
        
        // Check if the slug already exists
        if (Theme::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }
        
        // Handle screenshot upload
        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $filename = $slug . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/themes'), $filename);
            $screenshotPath = 'uploads/themes/' . $filename;
        } else {
            // Check if there's a default thumbnail in the theme directory
            // This would be used if registering an existing theme
            $themePath = resource_path('themes/' . $slug);
            if (File::isDirectory($themePath)) {
                $screenshotPath = $this->themeManager->getThemeScreenshotPath($slug);
            }
        }
        
        // Create theme record
        $theme = Theme::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'version' => $validated['version'] ?? null,
            'author' => $validated['author'] ?? null,
            'screenshot_path' => $screenshotPath,
            'is_active' => false,
        ]);
        
        return redirect()->route('admin.themes.show', $theme)
                ->with('success', 'Theme created successfully.');
    }

    /**
     * Display the specified theme.
     */
    public function show(Theme $theme)
    {
        // Load the theme's templates if available
        $templates = $theme->templates;
        
        return view('admin.themes.show', compact('theme', 'templates'));
    }

    /**
     * Show the form for editing the specified theme.
     */
    public function edit(Theme $theme)
    {
        return view('admin.themes.edit', compact('theme'));
    }

    /**
     * Update the specified theme in storage.
     */
    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'author' => 'nullable|string|max:255',
            'screenshot' => 'nullable|image|max:2048',
        ]);
        
        // Handle screenshot upload
        if ($request->hasFile('screenshot')) {
            // Remove old screenshot if exists
            if ($theme->screenshot_path && File::exists(public_path($theme->screenshot_path))) {
                File::delete(public_path($theme->screenshot_path));
            }
            
            $file = $request->file('screenshot');
            $filename = $theme->slug . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/themes'), $filename);
            $theme->screenshot_path = 'uploads/themes/' . $filename;
        }
        
        // Update theme data
        $theme->name = $validated['name'];
        $theme->description = $validated['description'] ?? null;
        $theme->version = $validated['version'] ?? null;
        $theme->author = $validated['author'] ?? null;
        $theme->save();
        
        return redirect()->route('admin.themes.show', $theme)
                ->with('success', 'Theme updated successfully.');
    }

    /**
     * Activate the specified theme.
     */
    public function activate(Theme $theme)
    {
        // Use the theme manager to activate the theme
        $this->themeManager->activateTheme($theme);
        
        // Clear any theme preview from session
        session()->forget('preview_theme');
        
        return redirect()->route('admin.themes.index')
                ->with('success', "Theme '{$theme->name}' has been activated.");
    }
    
    /**
     * Preview the specified theme.
     */
    public function preview(Theme $theme)
    {
        // Use session to store the preview theme ID
        session(['preview_theme' => $theme->id]);
        
        // Redirect to the home page to see the preview
        return redirect('/')->with('preview_mode', true);
    }

    /**
     * Publish assets for the specified theme.
     */
    public function publishAssets(Theme $theme)
    {
        $result = $this->themeManager->publishAssets($theme, true);
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
    
    /**
     * Remove the specified theme from storage.
     */
    public function destroy(Theme $theme)
    {
        // Prevent deleting the active theme
        if ($theme->is_active) {
            return redirect()->route('admin.themes.index')
                    ->with('error', 'Cannot delete the active theme. Please activate another theme first.');
        }
        
        // Delete the screenshot if exists
        if ($theme->screenshot_path && File::exists(public_path($theme->screenshot_path))) {
            File::delete(public_path($theme->screenshot_path));
        }
        
        // Clean up theme assets
        $this->themeManager->cleanupThemeAssets($theme->slug);
        
        // Delete the theme record
        $theme->delete();
        
        return redirect()->route('admin.themes.index')
                ->with('success', 'Theme deleted successfully.');
    }
}