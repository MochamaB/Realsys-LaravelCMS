# Theme Template Structure

This document outlines the structure required for theme templates to work with the Template System.

## Directory Structure

A properly structured theme should have the following directories for templates:

```
themes/
└── your-theme/
    └── resources/
        └── views/
            ├── templates/      # Template files go here
            │   ├── default.blade.php
            │   ├── blog.blade.php
            │   └── landing.blade.php
            └── sections/       # Section templates go here
                ├── header.blade.php
                ├── footer.blade.php
                ├── sidebar.blade.php
                ├── content.blade.php
                └── default.blade.php  # Fallback for undefined section types
```

## Template Files

Template files define the overall structure of a page and include sections in specific locations. 

### Example Template File (default.blade.php)

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $template->theme->slug . '/css/styles.css') }}">
</head>
<body class="template-{{ $template->slug }}">
    <header>
        @section('header')
    </header>

    <main>
        @hassection('hero')
            <div class="hero-container">
                @section('hero')
            </div>
        @endhassection

        <div class="content-container">
            <div class="row">
                <div class="col-md-8">
                    @section('content')
                </div>
                
                @hassection('sidebar')
                    <div class="col-md-4">
                        <aside class="sidebar">
                            @section('sidebar')
                        </aside>
                    </div>
                @endhassection
            </div>
        </div>

        @hassection('banner')
            <div class="banner-container">
                @section('banner')
            </div>
        @endhassection
    </main>

    <footer>
        @section('footer')
    </footer>

    <script src="{{ asset('themes/' . $template->theme->slug . '/js/main.js') }}"></script>
</body>
</html>
```

## Section Templates

Section templates define how a specific type of section should be rendered. These are reusable across different templates.

### Example Section Template (content.blade.php)

```php
<div class="section section-content {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <div class="section-header">
        @if($section->getSetting('show_title', true))
            <h2 class="section-title">{{ $section->name }}</h2>
        @endif
    </div>
    
    <div class="section-body">
        @if($widgets && $widgets->count() > 0)
            <div class="widgets-container">
                @foreach($widgets as $widget)
                    <div class="widget widget-{{ $widget->widgetType->slug }}" id="widget-{{ $widget->id }}">
                        @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-section">
                <p>No content in this section.</p>
            </div>
        @endif
    </div>
</div>
```

## Custom Blade Directives

The Template System provides the following Blade directives to use in your templates:

- `@section('section-slug')` - Renders a section with the given slug
- `@hassection('section-slug')` - Conditional that checks if a section exists
- `@endhassection` - Ends a hassection block
- `@nosection('section-slug')` - Conditional that checks if a section doesn't exist
- `@endnosection` - Ends a nosection block

## Template Variables

The following variables are available in template files:

- `$page` - The current page model
- `$template` - The current template model
- `$sections` - Collection of page sections

## Section Variables

The following variables are available in section templates:

- `$section` - The current template section model
- `$pageSection` - The current page section model
- `$widgets` - Collection of widgets for this section
