@php
    // Extract field values with defaults
    $slides = $fields['slides'] ?? [];
    $sliderHeight = $fields['slider_height'] ?? 'default';
    $animationSpeed = $fields['animation_speed'] ?? 500;
    $autoplay = isset($fields['autoplay']) ? filter_var($fields['autoplay'], FILTER_VALIDATE_BOOLEAN) : true;
    $autoplayDelay = $fields['autoplay_delay'] ?? 5000;
    $showNavArrows = isset($fields['show_nav_arrows']) ? filter_var($fields['show_nav_arrows'], FILTER_VALIDATE_BOOLEAN) : true;
    $showPagination = isset($fields['show_pagination']) ? filter_var($fields['show_pagination'], FILTER_VALIDATE_BOOLEAN) : true;
    
    // Assign a unique ID to this slider instance
    $sliderId = 'slider-' . uniqid();
    
    // Determine slider height CSS
    $sliderHeightCss = '';
    switch($sliderHeight) {
        case 'small':
            $sliderHeightCss = 'height: 300px;';
            break;
        case 'medium':
            $sliderHeightCss = 'height: 500px;';
            break;
        case 'large':
            $sliderHeightCss = 'height: 700px;';
            break;
        case 'fullscreen':
            $sliderHeightCss = 'height: 100vh;';
            break;
        default:
            $sliderHeightCss = 'height: 450px;';
    }
@endphp

<div class="widget widget-slider">
    <!-- Slider Area -->
    <div class="slider-area">
        <div id="{{ $sliderId }}" class="slider-active owl-carousel">
            @forelse($slides as $slide)
                <div class="single-slider" style="{{ $sliderHeightCss }} background-image: url('{{ $slide['image'] ?? '' }}'); background-size: cover; background-position: center;">
                    <div class="container">
                        <div class="slider-content">
                            @if(!empty($slide['title']))
                                <h1 class="slider-title">{{ $slide['title'] }}</h1>
                            @endif
                            
                            @if(!empty($slide['subtitle']))
                                <p class="slider-subtitle">{{ $slide['subtitle'] }}</p>
                            @endif
                            
                            @if(!empty($slide['button_text']))
                                <div class="button">
                                    <a href="{{ $slide['button_url'] ?? '#' }}" class="btn">{{ $slide['button_text'] }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="single-slider" style="{{ $sliderHeightCss }}">
                    <div class="container">
                        <div class="slider-content">
                            <h1 class="slider-title">Sample Slider</h1>
                            <p class="slider-subtitle">Please add slides in the widget settings</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $("#{{ $sliderId }}").owlCarousel({
        items: 1,
        loop: true,
        autoplay: {{ $autoplay ? 'true' : 'false' }},
        autoplayTimeout: {{ $autoplayDelay }},
        smartSpeed: {{ $animationSpeed }},
        autoplayHoverPause: true,
        nav: {{ $showNavArrows ? 'true' : 'false' }},
        dots: {{ $showPagination ? 'true' : 'false' }},
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 1
            },
            1000: {
                items: 1
            }
        }
    });
});
</script>
