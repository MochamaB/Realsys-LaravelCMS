<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">
    
    <title>{{ $title ?? 'User Registration' }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Theme CSS (Using direct references to theme assets rather than theme:: namespace) -->
    <link rel="stylesheet" href="{{ asset('themes/miata/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/miata/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/miata/lib/css/nivo-slider.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/miata/lib/css/preview.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/miata/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/miata/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/miata/css/mystyles.css') }}">
    <!-- Additional Styles -->
    {{ $styles ?? '' }}
</head>

<body>
    <div class="wrapper">
        <!-- Navigation/Header Section -->
        @include('usermanagement::partials.header')
        @include('usermanagement::partials.hero')
        
        <!-- Main Content -->
        <div class="main-content">
            {{ $slot }}
        </div>

        <!-- Footer Section -->
        @include('usermanagement::partials.footer')
    </div>
    
    <!-- jQuery and Theme JavaScript -->
    <script src="{{ asset('themes/miata/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('themes/miata/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('themes/miata/js/main.js') }}"></script>
    
    <!-- Additional Scripts -->
    {{ $scripts ?? '' }}
