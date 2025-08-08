@php
    // Extract field values with defaults
    $title = $fields['title'] ?? '';
    $image = $fields['image'] ?? '';
    $caption = $fields['caption'] ?? '';
    $linkUrl = $fields['link_url'] ?? '';
    
    // Extract settings with defaults
    $displayStyle = $settings['display_style'] ?? 'default';
    $imageSize = $settings['image_size'] ?? 'medium';
    
    // Generate dynamic background class based on page or widget settings
    $backgroundClass = '';
    if (!empty($image)) {
        // Create a unique background class for this widget instance
        $backgroundClass = 'featured-bg-' . md5($image);
    }
    
    // Custom CSS classes from widget settings
    $customClasses = $widget['css_classes'] ?? '';
@endphp

{{-- Dynamic background CSS injection --}}
{{-- Note: This widget requires dynamic CSS due to user-uploaded background images --}}
{{-- Static CSS is in custom.css, dynamic CSS remains in @push for flexibility --}}
@if(!empty($image) && !empty($backgroundClass))
@push('styles')
<style>
.{{ $backgroundClass }} {
    background: rgba(0, 0, 0, 0.4) url({{ $image }}) no-repeat scroll center center / cover;
    float: left;
    position: relative;
    width: 100%;
}
</style>
@endpush
@endif

{{-- Original Miata theme breadcrumbs structure --}}
<section class="breadcrumbs-area ptb-140 {{ $backgroundClass }} {{ $customClasses }}">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="breadcrumbs">
                    @if(!empty($title))
                        <h2 class="page-title">{{ $title }}</h2>
                    @endif
                    
                    @if(!empty($caption) || !empty($linkUrl))
                        <ul>
                            @if(!empty($linkUrl))
                                <li><a href="{{ $linkUrl }}">{{ $caption ?: 'Home' }}</a></li>
                                @if(!empty($title))
                                    <li>{{ $title }}</li>
                                @endif
                            @else
                                <li><a href="#">Home</a></li>
                                @if(!empty($caption))
                                    <li>{{ $caption }}</li>
                                @endif
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>