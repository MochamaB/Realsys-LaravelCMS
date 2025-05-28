# Theme Conversion Guide

This guide provides instructions for converting an existing theme to be compatible with the Realsys CMS Template System.

## Table of Contents

1. [Theme Structure Requirements](#theme-structure-requirements)
2. [Conversion Process](#conversion-process)
3. [Section Templates](#section-templates)
4. [Blade Directives](#blade-directives)
5. [Example Conversions](#example-conversions)

## Theme Structure Requirements

A properly formatted theme should follow this structure:

```
themes/
  ├── your-theme/
  │   ├── layouts/
  │   │   └── theme.blade.php
  │   ├── sections/
  │   │   ├── header.blade.php
  │   │   ├── footer.blade.php
  │   │   ├── hero.blade.php
  │   │   ├── content.blade.php
  │   │   ├── sidebar.blade.php
  │   │   └── default.blade.php
  │   ├── templates/
  │   │   ├── home.blade.php
  │   │   ├── post.blade.php
  │   │   ├── about.blade.php
  │   │   └── contact.blade.php
  │   └── widgets/
  │       └── [widget templates]
```

### Required Files

1. **Theme Layout** (`layouts/theme.blade.php`): Main layout that serves as the foundation for all templates
2. **Section Templates**: Template files for rendering specific sections of a page
3. **Page Templates**: Templates for different page types that extend the theme layout

## Conversion Process

Follow these steps to convert an existing theme:

### 1. Create Required Directories

Ensure your theme has the following directories:
- `layouts/` - For the main theme layout
- `sections/` - For section templates
- `templates/` - For page templates
- `widgets/` - For widget templates

### 2. Convert Master Layout to Theme Layout

1. Rename `master.blade.php` to `theme.blade.php`
2. Replace hard-coded partials with section directives
3. Modify the content area to support custom sections

**Before:**
```php
<!DOCTYPE html>
<html>
<head>
    <!-- Head content -->
</head>
<body>
    @include('themes.your-theme.partials.navigation')
    
    @yield('content')
    
    @include('themes.your-theme.partials.footer')
</body>
</html>
```

**After:**
```php
<!DOCTYPE html>
<html>
<head>
    <!-- Head content -->
</head>
<body>
    <!-- Navigation/Header Section -->
    @section('header')
    
    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
    
    <!-- Footer Section -->
    @section('footer')
</body>
</html>
```

### 3. Create Section Templates

Convert your partials into section templates:

1. **Header Section** (from navigation partial)
2. **Footer Section** (from footer partial)
3. **Content Section** (for main content)
4. **Hero Section** (for page headers/banners)
5. **Sidebar Section** (for sidebar content)

### 4. Update Template Files

Update your template files to use the new section directives:

1. Change `@extends` to use `theme::layouts.theme`
2. Replace direct section rendering with `@section` directives
3. Implement `@hassection` checks for conditional sections

**Before:**
```php
@extends('themes.your-theme.layouts.master')

@section('content')
    <!-- Hero Header -->
    @if($page->hasSection('hero'))
        {!! $page->renderSection('hero') !!}
    @endif
    
    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                {!! $page->renderSection('content') !!}
            </div>
        </div>
    </div>
@endsection
```

**After:**
```php
@extends('theme::layouts.theme')

@section('content')
    @hassection('hero')
        @section('hero')
    @endhassection
    
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @hassection('content')
                    @section('content')
                @endhassection
            </div>
            
            @hassection('sidebar')
                <div class="col-md-4">
                    @section('sidebar')
                </div>
            @endhassection
        </div>
    </div>
@endsection
```

## Section Templates

Here are the standard section types and their usage:

### Header Section

```php
<div class="section section-header {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <nav class="navbar">
        <!-- Navigation content -->
        
        @if($widgets && $widgets->count() > 0)
            @foreach($widgets as $widget)
                <div class="widget widget-{{ $widget->widgetType->slug }}">
                    @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                </div>
            @endforeach
        @else
            <!-- Default navigation -->
        @endif
    </nav>
</div>
```

### Footer Section

```php
<div class="section section-footer {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <footer>
        <!-- Footer content -->
        
        @if($widgets && $widgets->count() > 0)
            @foreach($widgets as $widget)
                <div class="widget widget-{{ $widget->widgetType->slug }}">
                    @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                </div>
            @endforeach
        @else
            <!-- Default footer content -->
        @endif
    </footer>
</div>
```

### Content Section

```php
<div class="section section-content {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <div class="container">
        @if($widgets && $widgets->count() > 0)
            @foreach($widgets as $widget)
                <div class="widget widget-{{ $widget->widgetType->slug }}">
                    @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                </div>
            @endforeach
        @else
            <!-- Default empty content message -->
        @endif
    </div>
</div>
```

### Hero Section

```php
<div class="section section-hero {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <header class="hero" style="background-image: url('{{ $section->getSetting('background_image', '') }}')">
        <div class="container">
            <h1>{{ $section->getSetting('heading', $page->title) }}</h1>
            <p>{{ $section->getSetting('subheading', '') }}</p>
            
            @if($widgets && $widgets->count() > 0)
                @foreach($widgets as $widget)
                    <div class="widget widget-{{ $widget->widgetType->slug }}">
                        @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                    </div>
                @endforeach
            @endif
        </div>
    </header>
</div>
```

### Sidebar Section

```php
<div class="section section-sidebar {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <div class="sidebar">
        @if($widgets && $widgets->count() > 0)
            @foreach($widgets as $widget)
                <div class="widget widget-{{ $widget->widgetType->slug }}">
                    @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                </div>
            @endforeach
        @else
            <!-- Default sidebar content -->
        @endif
    </div>
</div>
```

## Blade Directives

The template system provides custom Blade directives for rendering sections:

- `@section('name')` - Renders a section by its name
- `@hassection('name')` - Checks if a section exists
- `@endhassection` - Closes a hassection check

## Example Conversions

### Example 1: Home Template

**Before:**
```php
@extends('themes.your-theme.layouts.master')

@section('content')
    <!-- Hero Header -->
    <header class="masthead" style="background-image: url('{{ asset('img/home-bg.jpg') }}')">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            <p>{{ $page->subtitle }}</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!-- Recent Posts -->
                @foreach($posts as $post)
                    <div class="post-preview">
                        <a href="{{ route('post', $post->slug) }}">
                            <h2>{{ $post->title }}</h2>
                        </a>
                        <p>{{ $post->excerpt }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
```

**After:**
```php
@extends('theme::layouts.theme')

@section('content')
    @hassection('hero')
        @section('hero')
    @endhassection

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @hassection('content')
                    @section('content')
                @endhassection
                
                @hassection('posts')
                    @section('posts')
                @endhassection
            </div>
            
            @hassection('sidebar')
                <div class="col-md-4">
                    @section('sidebar')
                </div>
            @endhassection
        </div>
    </div>
@endsection
```

### Example 2: Post Template

**Before:**
```php
@extends('themes.your-theme.layouts.master')

@section('content')
    <!-- Page Header -->
    <header class="masthead" style="background-image: url('{{ $post->featured_image }}')">
        <div class="container">
            <h1>{{ $post->title }}</h1>
            <p>Posted by {{ $post->author->name }} on {{ $post->created_at->format('F d, Y') }}</p>
        </div>
    </header>

    <!-- Post Content -->
    <article>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    {!! $post->content !!}
                </div>
            </div>
        </div>
    </article>
@endsection
```

**After:**
```php
@extends('theme::layouts.theme')

@section('content')
    @hassection('hero')
        @section('hero')
    @endhassection

    <article>
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    @hassection('content')
                        @section('content')
                    @endhassection
                    
                    @hassection('author')
                        @section('author')
                    @endhassection
                    
                    @hassection('comments')
                        @section('comments')
                    @endhassection
                </div>
                
                @hassection('sidebar')
                    <div class="col-md-4">
                        @section('sidebar')
                    </div>
                @endhassection
            </div>
        </div>
    </article>
@endsection
```

By following this guide, you can successfully convert any theme to be compatible with the Realsys CMS Template System.
