<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $page->meta_description ?? '' }}">
    <meta name="keywords" content="{{ $page->meta_keywords ?? '' }}">
    
    <!-- Theme CSS -->
    @foreach($theme->css as $css)
        <link rel="stylesheet" href="{{ theme_asset($css) }}">
    @endforeach

    <!-- Custom CSS -->
    @stack('styles')
</head>
<body>
    <!-- Header -->
    @include('themes.default.partials.header')

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('themes.default.partials.footer')

    <!-- Theme JavaScript -->
    @foreach($theme->js as $js)
        <script src="{{ theme_asset($js) }}"></script>
    @endforeach

    <!-- Custom JavaScript -->
    @stack('scripts')
</body>
</html>
