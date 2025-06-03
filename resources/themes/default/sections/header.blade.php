<div class="section section-header {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <nav class="navbar navbar-expand-lg navbar-light" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                @if(!empty($widgets))
                    <!-- Render navigation widgets -->
                    @foreach($widgets as $widget)
                        <div class="widget widget-{{ $widget['slug'] }}">
                @include($widget['view_path'], ['widget' => $widget])
            </div>
                    @endforeach
                @else
                    <!-- Default navigation if no widgets -->
                    <ul class="navbar-nav ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="{{ url('/') }}">Home</a></li>
                        <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="{{ url('/about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="{{ url('/contact') }}">Contact</a></li>
                    </ul>
                @endif
            </div>
        </div>
    </nav>
</div>
