<div class="section section-content {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                @if(!empty($widgets))
                    <div class="widgets-container">
                        @foreach($widgets as $widget)
                        <div class="widget widget-{{ $widget['slug'] }}">
                            @include($widget['view_path'], ['widget' => $widget])
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-section">
                        <h4>Debug Info</h4>
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
</div>
