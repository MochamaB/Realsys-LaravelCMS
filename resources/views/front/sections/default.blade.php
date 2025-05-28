<div class="section {{ $section->type }} {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    @if($widgets && $widgets->count() > 0)
        <div class="widgets-container">
            @foreach($widgets as $widget)
                <div class="widget widget-{{ $widget->widgetType->slug }}" id="widget-{{ $widget->id }}">
                    @include('front.widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty section -->
        <div class="empty-section">
            <p>No content in this section.</p>
        </div>
    @endif
</div>
