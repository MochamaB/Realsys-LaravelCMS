<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="{{ $page->meta_description ?? '' }}" />
        <meta name="author" content="{{ $page->meta_author ?? config('app.name') }}" />
        <title>{{ $page->title ?? config('app.name') }}</title>
        
        <link rel="icon" type="image/x-icon" href="{{ theme_asset('assets/favicon.ico') }}" />
        
        <!-- Theme CSS -->
        @foreach($theme->css as $css)
            <link rel="stylesheet" href="{{ $css }}" />
        @endforeach
        <link rel="stylesheet" href="{{ theme_asset('assets/lib/css/nivo-slider.css') }}">
        <link rel="stylesheet" href="{{ theme_asset('assets/lib/css/preview.css') }}">

        <!-- Custom CSS -->
        @stack('styles')
       

    </head>
    <body>
    <div class="wrapper">
        <!-- Navigation/Header Section -->
         
        @templateSection('header')
        {{-- In the header section of your layout --}}
        <x-theme-navigation location="header" :page-id="$page->id ?? null" :template-id="$template->id ?? null" />

        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>

        <!-- Footer Section -->
        @templateSection('footer')

        <!-- Theme JavaScript -->
        @foreach($theme->js as $js)
            <script src="{{ $js }}"></script>
        @endforeach

        <!-- Custom JavaScript -->
        @stack('scripts')
    </div>
    </body>
</html>
