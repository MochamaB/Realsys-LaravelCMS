# Theme System

This document outlines the theme system architecture for the CMS, explaining how themes are structured, registered, and used to render content.

## Theme Architecture Overview

The theme system is designed to provide complete separation between content and presentation. Themes define how content is displayed, while the content structure remains consistent across themes.

## Theme Directory Structure

Each theme is contained in its own directory within the `resources/themes` folder:

```
resources/
  themes/
    theme-name/
      theme.json           # Theme metadata
      templates/           # Page templates
        home.blade.php
        article.blade.php
        ...
      components/          # Widget components
        widgets/
          slider.blade.php
          featured.blade.php
          ...
      assets/              # Theme assets
        css/
        js/
        images/
      layouts/             # Layout templates
        default.blade.php
        full-width.blade.php
```

## Theme Configuration

Each theme includes a `theme.json` file that defines its metadata:

```json
{
  "name": "NPPK Default",
  "slug": "nppk-default",
  "description": "The default theme for the NPPK website",
  "version": "1.0.0",
  "author": "NPPK Development Team",
  "supports": {
    "widgets": ["slider", "featured", "text", "image", "gallery", "team"],
    "templates": ["home", "article", "contact", "about"]
  },
  "options": {
    "colors": {
      "primary": "#0066cc",
      "secondary": "#ff9900",
      "text": "#333333",
      "background": "#ffffff"
    },
    "fonts": {
      "heading": "Roboto, sans-serif",
      "body": "Open Sans, sans-serif"
    }
  }
}
```

## Theme Registration Process

Themes are registered through a service provider that scans the themes directory and registers each theme in the database:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ThemeManager;
use Illuminate\Support\Facades\File;
use App\Models\Theme;
use App\Models\Template;
use App\Models\TemplateSection;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register themes
        $this->registerThemes();
        
        // Load views from active theme
        $this->loadActiveThemeViews();
    }
    
    protected function registerThemes()
    {
        $themesPath = resource_path('themes');
        
        if (!File::isDirectory($themesPath)) {
            return;
        }
        
        foreach (File::directories($themesPath) as $themeDir) {
            $themeSlug = basename($themeDir);
            $configFile = $themeDir . '/theme.json';
            
            if (File::exists($configFile)) {
                $config = json_decode(File::get($configFile), true);
                
                if (!$config) {
                    continue;
                }
                
                // Register or update theme
                $theme = Theme::firstOrNew(['slug' => $themeSlug]);
                $theme->name = $config['name'] ?? $themeSlug;
                $theme->description = $config['description'] ?? '';
                $theme->version = $config['version'] ?? '1.0.0';
                $theme->author = $config['author'] ?? '';
                $theme->screenshot_path = File::exists($themeDir . '/screenshot.png') ? "themes/{$themeSlug}/screenshot.png" : null;
                $theme->save();
                
                // Register templates
                $this->registerTemplates($theme, $themeDir);
            }
        }
    }
    
    protected function registerTemplates($theme, $themeDir)
    {
        $templatesDir = $themeDir . '/templates';
        
        if (!File::isDirectory($templatesDir)) {
            return;
        }
        
        // Get template files
        $templateFiles = File::files($templatesDir);
        
        foreach ($templateFiles as $file) {
            $filename = $file->getFilename();
            
            // Skip non-blade files
            if (!str_ends_with($filename, '.blade.php')) {
                continue;
            }
            
            // Get template slug (filename without extension)
            $templateSlug = str_replace('.blade.php', '', $filename);
            
            // Parse template header comments for metadata
            $content = File::get($file->getPathname());
            $metadata = $this->parseTemplateMetadata($content);
            
            // Register or update template
            $template = Template::firstOrNew([
                'theme_id' => $theme->id,
                'slug' => $templateSlug
            ]);
            
            $template->name = $metadata['name'] ?? ucfirst($templateSlug);
            $template->description = $metadata['description'] ?? '';
            $template->file_path = "themes/{$theme->slug}/templates/{$filename}";
            $template->thumbnail_path = File::exists($themeDir . "/thumbnails/{$templateSlug}.png") 
                ? "themes/{$theme->slug}/thumbnails/{$templateSlug}.png" 
                : null;
            $template->is_active = true;
            $template->save();
            
            // Register template sections
            if (isset($metadata['sections'])) {
                $this->registerTemplateSections($template, $metadata['sections']);
            }
        }
    }
    
    protected function parseTemplateMetadata($content)
    {
        $metadata = [];
        
        // Extract header comment
        if (preg_match('/{{--\\s*\\n(.+?)\\n\\s*--}}/s', $content, $matches)) {
            $commentLines = explode("\n", $matches[1]);
            
            foreach ($commentLines as $line) {
                $line = trim($line);
                
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    if ($key === 'sections') {
                        // Parse sections
                        $sections = [];
                        $sectionItems = explode(',', $value);
                        
                        foreach ($sectionItems as $item) {
                            $item = trim($item);
                            
                            if (!empty($item)) {
                                $sections[] = $item;
                            }
                        }
                        
                        $metadata['sections'] = $sections;
                    } else {
                        $metadata[$key] = $value;
                    }
                }
            }
        }
        
        return $metadata;
    }
    
    protected function registerTemplateSections($template, $sections)
    {
        // Remove existing sections
        $template->sections()->delete();
        
        // Add new sections
        $order = 0;
        
        foreach ($sections as $sectionName) {
            $section = new TemplateSection([
                'name' => ucwords(str_replace('-', ' ', $sectionName)),
                'slug' => $sectionName,
                'description' => '',
                'is_required' => $sectionName === 'content',
                'max_widgets' => $sectionName === 'content' ? 1 : null,
                'order_index' => $order++
            ]);
            
            $template->sections()->save($section);
        }
    }
    
    protected function loadActiveThemeViews()
    {
        $activeTheme = Theme::where('is_active', true)->first();
        
        if ($activeTheme) {
            $themePath = resource_path("themes/{$activeTheme->slug}");
            
            if (File::isDirectory($themePath)) {
                // Register theme views
                $this->loadViewsFrom($themePath, 'theme');
                
                // Register theme components
                $this->loadViewComponentsAs('theme', [
                    // Register components here
                ]);
            }
        }
    }
}
```

## Template Structure

Templates are Blade files that define the structure of a page. Each template includes metadata in a comment at the top:

```php
{{-- 
name: Home Page
description: Template for the site homepage
sections: hero, featured, content, sidebar, testimonials, cta
--}}

@extends('theme::layouts.default')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @section('hero')
                    @foreach($page->getWidgetsBySection('hero') as $widget)
                        {!! $widget->render() !!}
                    @endforeach
                @show
                
                @section('featured')
                    @foreach($page->getWidgetsBySection('featured') as $widget)
                        {!! $widget->render() !!}
                    @endforeach
                @show
                
                @section('content')
                    @foreach($page->getWidgetsBySection('content') as $widget)
                        {!! $widget->render() !!}
                    @endforeach
                    
                    @if($page->content)
                        <div class="page-content">
                            {!! $page->content !!}
                        </div>
                    @endif
                @show
                
                @section('testimonials')
                    @foreach($page->getWidgetsBySection('testimonials') as $widget)
                        {!! $widget->render() !!}
                    @endforeach
                @show
            </div>
            
            <div class="col-md-4">
                @section('sidebar')
                    @foreach($page->getWidgetsBySection('sidebar') as $widget)
                        {!! $widget->render() !!}
                    @endforeach
                @show
            </div>
        </div>
        
        @section('cta')
            @foreach($page->getWidgetsBySection('cta') as $widget)
                {!! $widget->render() !!}
            @endforeach
        @show
    </div>
@endsection
```

## Widget Rendering in Themes

Widgets are rendered using Blade components defined in the theme. Each widget type has a corresponding component:

```php
{{-- resources/themes/nppk-default/components/widgets/slider.blade.php --}}

@props(['widget', 'data'])

<div class="slider-widget">
    <div class="slider-container">
        @if(isset($data['title']))
            <h2 class="slider-title">{{ $data['title'] }}</h2>
        @endif
        
        @if(isset($data['slides']) && is_array($data['slides']))
            <div class="slider">
                @foreach($data['slides'] as $slide)
                    <div class="slide">
                        @if(isset($slide['image']))
                            <img src="{{ asset('storage/' . $slide['image']) }}" alt="{{ $slide['title'] ?? '' }}">
                        @endif
                        
                        <div class="slide-content">
                            @if(isset($slide['title']))
                                <h3>{{ $slide['title'] }}</h3>
                            @endif
                            
                            @if(isset($slide['description']))
                                <p>{{ $slide['description'] }}</p>
                            @endif
                            
                            @if(isset($slide['button_text']) && isset($slide['button_url']))
                                <a href="{{ $slide['button_url'] }}" class="btn btn-primary">{{ $slide['button_text'] }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="slider-nav">
                @foreach($data['slides'] as $index => $slide)
                    <button class="slider-nav-item" data-slide="{{ $index }}"></button>
                @endforeach
            </div>
        @endif
    </div>
</div>
```

## Theme Switching Process

When switching themes, the system:

1. Updates the active theme in the database
2. Maps existing templates to new theme templates where possible
3. Updates template paths for all pages
4. Clears view and cache files
5. Regenerates assets if needed

```php
namespace App\Services;

use App\Models\Theme;
use App\Models\Template;
use App\Models\Page;
use Illuminate\Support\Facades\Artisan;

class ThemeManager
{
    /**
     * Activate a theme
     *
     * @param Theme $theme
     * @return bool
     */
    public function activateTheme(Theme $theme)
    {
        // Deactivate current theme
        Theme::where('is_active', true)->update(['is_active' => false]);
        
        // Activate new theme
        $theme->is_active = true;
        $theme->save();
        
        // Map templates
        $this->mapTemplates($theme);
        
        // Clear cache
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        
        return true;
    }
    
    /**
     * Map templates from old theme to new theme
     *
     * @param Theme $theme
     * @return void
     */
    protected function mapTemplates(Theme $theme)
    {
        // Get all pages
        $pages = Page::all();
        
        // Get available templates in the new theme
        $availableTemplates = Template::where('theme_id', $theme->id)->get();
        
        // Default template to use if no match is found
        $defaultTemplate = $availableTemplates->firstWhere('slug', 'default') 
            ?? $availableTemplates->first();
        
        if (!$defaultTemplate) {
            return;
        }
        
        foreach ($pages as $page) {
            $oldTemplate = $page->template;
            
            if (!$oldTemplate) {
                $page->template_id = $defaultTemplate->id;
                $page->save();
                continue;
            }
            
            // Try to find a matching template in the new theme
            $newTemplate = $availableTemplates->firstWhere('slug', $oldTemplate->slug);
            
            if (!$newTemplate) {
                $page->template_id = $defaultTemplate->id;
            } else {
                $page->template_id = $newTemplate->id;
            }
            
            $page->save();
            
            // Update page sections
            $this->updatePageSections($page);
        }
    }
    
    /**
     * Update page sections based on the new template
     *
     * @param Page $page
     * @return void
     */
    protected function updatePageSections($page)
    {
        $template = $page->template;
        
        if (!$template) {
            return;
        }
        
        // Get existing page sections
        $existingSections = $page->sections;
        
        // Get template sections
        $templateSections = $template->sections;
        
        // Create mapping of old sections to new sections
        $sectionMap = [];
        
        foreach ($existingSections as $existingSection) {
            $oldTemplateSection = $existingSection->templateSection;
            
            if (!$oldTemplateSection) {
                continue;
            }
            
            // Try to find a matching section in the new template
            $newTemplateSection = $templateSections->firstWhere('slug', $oldTemplateSection->slug);
            
            if ($newTemplateSection) {
                $sectionMap[$existingSection->id] = $newTemplateSection->id;
            }
        }
        
        // Remove existing sections and their widget associations
        foreach ($existingSections as $section) {
            $widgets = $section->widgets()->get();
            $section->widgets()->detach();
            $section->delete();
        }
        
        // Create new sections based on template
        foreach ($templateSections as $templateSection) {
            $page->sections()->create([
                'template_section_id' => $templateSection->id,
                'is_active' => true
            ]);
        }
        
        // Restore widget associations where possible
        $newSections = $page->sections()->with('templateSection')->get();
        
        foreach ($widgets as $widget) {
            $oldSectionId = $widget->pivot->page_section_id;
            
            if (isset($sectionMap[$oldSectionId])) {
                $newTemplateSection = $sectionMap[$oldSectionId];
                $newSection = $newSections->first(function ($section) use ($newTemplateSection) {
                    return $section->template_section_id == $newTemplateSection;
                });
                
                if ($newSection) {
                    $newSection->widgets()->attach($widget->id, [
                        'order_index' => $widget->pivot->order_index
                    ]);
                }
            }
        }
    }
}
```

## Theme Customization

Themes can define customization options that are stored in the database and accessible through a settings interface:

```php
namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Cache;

class ThemeSettings
{
    /**
     * Get a theme setting
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $settings = $this->getAllSettings();
        
        return $settings[$key] ?? $default;
    }
    
    /**
     * Set a theme setting
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $theme = Theme::where('is_active', true)->first();
        
        if (!$theme) {
            return;
        }
        
        $settings = json_decode($theme->settings, true) ?: [];
        $settings[$key] = $value;
        
        $theme->settings = json_encode($settings);
        $theme->save();
        
        // Clear cache
        Cache::forget('theme_settings');
    }
    
    /**
     * Get all theme settings
     *
     * @return array
     */
    public function getAllSettings()
    {
        return Cache::remember('theme_settings', 60 * 24, function () {
            $theme = Theme::where('is_active', true)->first();
            
            if (!$theme) {
                return [];
            }
            
            return json_decode($theme->settings, true) ?: [];
        });
    }
}
```

## Theme Assets

Theme assets (CSS, JavaScript, images) are published to the public directory and accessed through asset helpers:

```php
namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\File;

class ThemeAssetManager
{
    /**
     * Publish theme assets
     *
     * @param Theme $theme
     * @return bool
     */
    public function publishAssets(Theme $theme)
    {
        $themePath = resource_path("themes/{$theme->slug}");
        $assetPath = $themePath . '/assets';
        
        if (!File::isDirectory($assetPath)) {
            return false;
        }
        
        $publicPath = public_path("themes/{$theme->slug}");
        
        // Create public directory if it doesn't exist
        if (!File::isDirectory($publicPath)) {
            File::makeDirectory($publicPath, 0755, true);
        }
        
        // Copy assets
        File::copyDirectory($assetPath, $publicPath);
        
        return true;
    }
    
    /**
     * Get the URL for a theme asset
     *
     * @param string $path
     * @return string
     */
    public function asset($path)
    {
        $theme = Theme::where('is_active', true)->first();
        
        if (!$theme) {
            return asset($path);
        }
        
        return asset("themes/{$theme->slug}/{$path}");
    }
}
```

## Theme Development Workflow

For creating new themes:

1. Create a new theme directory with required structure
2. Define theme metadata in theme.json
3. Create templates with defined sections
4. Create widget components that render widget data
5. Add assets and styles
6. Register the theme in the system

## Conclusion

This theme system provides a powerful and flexible way to customize the appearance of the CMS while maintaining a consistent content structure. Key benefits include:

1. **Complete separation of content and presentation**: Content structure remains consistent across themes
2. **Theme development flexibility**: Theme developers can focus on presentation without worrying about data structure
3. **Multiple theme support**: Multiple themes can be installed and switched between easily
4. **Consistent widget system**: Widgets maintain their data structure across themes
5. **Template customization**: Each theme can define its own templates and sections

The system is designed to be extensible, allowing for the creation of new themes without modifying the core CMS code.
