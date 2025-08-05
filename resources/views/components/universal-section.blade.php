@php
use App\Services\UniversalStylingService;

$stylingService = app(UniversalStylingService::class);
$sectionClasses = $stylingService->buildSectionClasses($pageSection);
$sectionStyles = $stylingService->buildSectionStyles($pageSection);
$gridAttributes = $stylingService->buildGridAttributes($pageSection);
@endphp

<section class="{{ $sectionClasses }}" 
         id="section-{{ $pageSection->id }}"
         {!! $sectionStyles ? 'style="' . $sectionStyles . '"' : '' !!}
         data-section-id="{{ $pageSection->id }}"
         data-section-type="{{ $pageSection->templateSection->section_type ?? 'default' }}"
         {!! $gridAttributes !!}>
    
    {{-- Theme-specific content --}}
    <div class="section-content">
        {{ $slot }}
    </div>
    
</section>