@props(['name', 'page'])

@php
    // Find the page section by template section slug if the page has sections
    $section = null;
    if ($page && $page->sections) {
        foreach ($page->sections as $pageSection) {
            if ($pageSection->templateSection && $pageSection->templateSection->slug === $name) {
                $section = $pageSection;
                break;
            }
        }
    }
@endphp

@if($section)
    <div class="page-section" id="section-{{ $section->id }}">
        @if(isset($title) && $title)
            <h2 class="section-title">{{ $section->templateSection->name }}</h2>
        @endif
        
        <div class="section-content">
            {!! $section->content !!}
        </div>
        
        @if($section->widgets && $section->widgets->isNotEmpty())
            <div class="section-widgets">
                @foreach($section->widgets as $widget)
                    <div class="widget widget-{{ $widget->widgetType->slug }}">
                        @include('front.widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@elseif(isset($slot) && $slot->isNotEmpty())
    <div class="page-section default-content">
        {{ $slot }}
    </div>
@endif
