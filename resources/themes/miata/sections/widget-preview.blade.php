{{-- Widget Preview Section Template --}}
{{-- This template is specifically designed for isolated widget previews --}}

@foreach($widgets as $widget)
    <x-universal-widget :pageSectionWidget="$widget">
        @include("theme::widgets.{$widget->widget->slug}.view", [
            'widget' => $widget->widget,
            'fields' => $widget->fieldValues,
            'settings' => $widget->settings ?? []
        ])
    </x-universal-widget>
@endforeach
