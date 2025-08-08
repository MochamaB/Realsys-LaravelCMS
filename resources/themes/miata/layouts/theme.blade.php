<!DOCTYPE html>
<html lang="en">
    
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="{{ $page->meta_description ?? '' }}" />
        <meta name="author" content="{{ $page->meta_author ?? config('app.name') }}" />
        <title>{{ $page->title ?? config('app.name') }}</title>
        
          <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">
        <!-- Theme CSS -->
        @foreach($theme->css as $css)
            <link rel="stylesheet" href="{{ $css }}" />
        @endforeach
        
        <!-- Widget CSS Assets -->
        @if(isset($widgetAssets) && isset($widgetAssets['css']))
            @foreach($widgetAssets['css'] as $css)
                <link rel="stylesheet" href="{{ $css }}" />
            @endforeach
        @endif

        <!-- Custom CSS (Backward Compatibility) -->
        @stack('styles')
       

    </head>
    <body>
    <div class="wrapper">
       
        <!-- Navigation/Header Section -->
        @include('theme::partials.header')
        <!-- Main Content -->
       
            @yield('content')
        

        <!-- Footer Section -->
        @include('theme::partials.footer')

     
    </div>
       <!-- Theme JavaScript -->
       @foreach($theme->js as $js)
            <script src="{{ $js }}"></script>
        @endforeach
        
        <!-- Widget JavaScript Assets -->
        @if(isset($widgetAssets) && isset($widgetAssets['js']))
            @foreach($widgetAssets['js'] as $js)
                <script src="{{ $js }}"></script>
            @endforeach
        @endif

        <!-- Custom JavaScript (Backward Compatibility) -->
        @stack('scripts')
    </body>
</html>
