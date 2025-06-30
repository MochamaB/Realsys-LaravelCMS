@php
    // Extract field values with defaults
    $title = $fields['title'] ?? '';
    $image = $fields['image'] ?? '';
    $caption = $fields['caption'] ?? '';
    $linkUrl = $fields['link_url'] ?? '';
    
    // Extract settings with defaults
    $displayStyle = $settings['display_style'] ?? 'default';
    $imageSize = $settings['image_size'] ?? 'medium';
    
    // Determine CSS classes based on display style
    $imageClass = '';
    switch($displayStyle) {
        case 'rounded':
            $imageClass = 'rounded';
            break;
        case 'circle':
            $imageClass = 'rounded-circle';
            break;
        case 'polaroid':
            $imageClass = 'polaroid shadow p-3 bg-white';
            break;
        default:
            $imageClass = '';
    }
    
    // Determine container width based on image size
    $containerClass = '';
    switch($imageSize) {
        case 'small':
            $containerClass = 'col-md-6 offset-md-3 col-lg-4 offset-lg-4';
            break;
        case 'medium':
            $containerClass = 'col-md-8 offset-md-2 col-lg-6 offset-lg-3';
            break;
        case 'large':
            $containerClass = 'col-md-10 offset-md-1 col-lg-8 offset-lg-2';
            break;
        case 'full':
            $containerClass = 'col-12';
            break;
    }
@endphp

<section class="elements-area ptb-140 widget widget-featuredimage">
    <div class="container">
        <div class="row">
            <div class="{{ $containerClass }}">
                @if(!empty($title))
                <div class="text-center mb-3">
                    <h3>{{ $title }}</h3>
                </div>
                @endif
                
                <div class="featured-image-container text-center">
                    @if(!empty($linkUrl))
                        <a href="{{ $linkUrl }}">
                            <img src="{{ $image }}" alt="{{ $title }}" class="img-fluid {{ $imageClass }}">
                        </a>
                    @else
                        <img src="{{ $image }}" alt="{{ $title }}" class="img-fluid {{ $imageClass }}">
                    @endif
                    
                    @if(!empty($caption))
                    <div class="image-caption mt-2 text-center">
                        <p class="text-muted">{{ $caption }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
