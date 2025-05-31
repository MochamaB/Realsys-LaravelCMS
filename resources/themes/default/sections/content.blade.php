<div class="section section-content {{ $section->getSetting('custom_class', '') }}" id="section-{{ $section->slug }}">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                @if($widgets && $widgets->count() > 0)
                    <div class="widgets-container">
                        @foreach($widgets as $widget)
                            <div class="widget widget-{{ $widget->widgetType->slug }}">
                                @include('theme::widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-section">
                        <p>No content available for this section.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
