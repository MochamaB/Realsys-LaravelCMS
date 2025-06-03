<div class="section section-sidebar {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <div class="sidebar-container">
        @if(!empty($widgets))
            <div class="widgets-container">
                @foreach($widgets as $widget)
                    <div class="widget widget-{{ $widget['slug'] }} mb-4">
                        @include($widget['view_path'], ['widget' => $widget])
                    </div>
                @endforeach
            </div>
        @else
            <div class="card mb-4">
                <div class="card-header">About</div>
                <div class="card-body">
                    <p>This is a sidebar section. Add widgets to populate this area.</p>
                </div>
            </div>
        @endif
    </div>
</div>
