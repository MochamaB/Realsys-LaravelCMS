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

        <!-- Custom CSS -->
        @stack('styles')
    </head>
    <body>
        <!-- Navigation/Header Section -->
        @templateSection('header')

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
    </body>
</html>
