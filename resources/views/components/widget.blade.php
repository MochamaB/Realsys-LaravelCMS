@props(['type', 'data' => []])

@php
    $widgetType = \App\Models\WidgetType::where('slug', $type)->first();
@endphp

@if($widgetType)
    <div class="widget widget-{{ $widgetType->slug }}">
        @if(View::exists('front.widgets.' . $widgetType->slug))
            @include('front.widgets.' . $widgetType->slug, ['data' => $data])
        @else
            <div class="widget-missing">
                <p>Widget "{{ $widgetType->name }}" template not found.</p>
            </div>
        @endif
    </div>
@else
    <div class="widget-error">
        <p>Widget type "{{ $type }}" not found.</p>
    </div>
@endif
