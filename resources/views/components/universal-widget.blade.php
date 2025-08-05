@php
use App\Services\UniversalStylingService;

$stylingService = app(UniversalStylingService::class);
$widgetClasses = $stylingService->buildWidgetClasses($pageSectionWidget);
$widgetStyles = $stylingService->buildWidgetStyles($pageSectionWidget);
$gridAttributes = $stylingService->buildWidgetGridAttributes($pageSectionWidget);
@endphp

<div class="{{ $widgetClasses }}" 
     id="widget-{{ $pageSectionWidget->id }}"
     {!! $widgetStyles ? 'style="' . $widgetStyles . '"' : '' !!}
     data-widget-id="{{ $pageSectionWidget->widget_id }}"
     data-widget-slug="{{ $pageSectionWidget->widget->slug }}"
     data-page-section-widget-id="{{ $pageSectionWidget->id }}"
     {!! $gridAttributes !!}>
    
    {{ $slot }}
    
</div>