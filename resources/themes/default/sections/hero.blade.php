<div class="section section-hero {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <header class="masthead" style="background-image: url('{{ $section->getSetting('background_image', theme_asset('assets/img/home-bg.jpg')) }}')">
        <div class="container position-relative px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <div class="site-heading">
                        <h1>{{ $section->getSetting('heading', $page->title) }}</h1>
                        <span class="subheading">{{ $section->getSetting('subheading', '') }}</span>
                    </div>
                    
                    @if(!empty($widgets))
                        <div class="widgets-container">
                            @foreach($widgets as $widget)
                                <div class="widget widget-{{ $widget['slug'] }}">
                                    @include($widget['view_path'], ['widget' => $widget])
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="debug-info">
                            <h4>Debug Info - Hero Section</h4>
                            <p>Section: {{ $section->slug }}</p>
                            <p>Widget Count: {{ is_array($widgets) ? count($widgets) : ($widgets ? $widgets->count() : 0) }}</p>
                            <details>
                                <summary>Widget Data</summary>
                                <pre>{{ json_encode($widgets, JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>
</div>
