<div class="section section-default {{ $section['settings']['custom_class'] ?? '' }}" id="section-{{ $section['slug'] }}">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                @if(isset($section['settings']['show_title']) ? $section['settings']['show_title'] : true)
                    <h2 class="section-title">{{ $section['name'] }}</h2>
                @endif
                
                @if(!empty($section['widgets']))
                    <div class="widgets-container">
                        @foreach($section['widgets'] as $widget)
                            <div class="widget widget-{{ $widget['slug'] }}">
                                @include($widget['view_path'], ['widget' => $widget])
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-section">
                        <!-- Debug information to help identify widget rendering issues -->
                        <h4>Debug Info - Default Section Template</h4>
                        <p>Section: {{ $section['slug'] ?? 'unknown' }}</p>
                        <p>Widget Data Type: {{ gettype($section['widgets'] ?? null) }}</p>
                        <details>
                            <summary>Section Data</summary>
                            <pre>{{ json_encode($section ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    </div>
                @endif
                <!-- Note: This is the default section template used as a fallback when specific templates aren't found -->
            </div>
        </div>
    </div>
</div>
