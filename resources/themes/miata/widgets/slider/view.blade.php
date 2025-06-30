@php
    // Extract field values with defaults
    $mainImage = $fields['main_image'] ?? '';
    $slides = $fields['slides'] ?? [];

    // Extract settings values with defaults
    $animationSpeed = $settings['animation_speed'] ?? 500;
    $autoplay = isset($settings['autoplay']) ? filter_var($settings['autoplay'], FILTER_VALIDATE_BOOLEAN) : true;
    $autoplayDelay = $settings['autoplay_delay'] ?? 5000;
    $showNavArrows = isset($settings['show_nav_arrows']) ? filter_var($settings['show_nav_arrows'], FILTER_VALIDATE_BOOLEAN) : true;
    $showPagination = isset($settings['show_pagination']) ? filter_var($settings['show_pagination'], FILTER_VALIDATE_BOOLEAN) : true;
    
    // Assign a unique ID to this slider instance
    $sliderId = 'ensign-nivoslider-' . uniqid();
@endphp

<section class="elements-area ptb-140 widget widget-slider">
    <div class="slider-main-area">
        <div class="main-slider an-si">
            <div class="bend niceties preview-2">
                <div id="{{ $sliderId }}" class="slides nivoSlider">
                    @if(!empty($mainImage))
                        <img src="{{ $mainImage }}" alt="Main Slider Image" title="#slider-main-caption" />
                    @endif
                    
                    @forelse($slides as $index => $slide)
                        @if(!empty($slide['image']))
                            <img src="{{ $slide['image'] }}" alt="Slide {{ $index + 1 }}" title="#slider-direction-{{ $index + 1 }}" />
                        @endif
                    @empty
                        <img src="/themes/miata/img/slider/default.jpg" alt="Default Slide" title="#slider-default" />
                    @endforelse
                </div>
                
                <!-- Main caption if main image exists -->
                @if(!empty($mainImage))
                <div id="slider-main-caption" class="nivo-html-caption">
                    <div class="container">
                        <div class="slide-all">
                            <div class="layer-1">
                                <h3 class="title5">{{ $slides[0]['title_line1'] ?? 'WELCOME' }}</h3>
                            </div>
                            <div class="layer-2">
                                <h1 class="title6">{{ $slides[0]['title_line2'] ?? 'MAIN HEADING' }}</h1>
                            </div>
                            <div class="layer-2">
                                <h1 class="title6">{{ $slides[0]['title_line3'] ?? 'SUBTITLE HERE' }}</h1>
                            </div>
                            @if(!empty($slides[0]['button_text']))
                            <div class="layer-3">
                                <a class="min1" href="{{ $slides[0]['button_url'] ?? '#' }}">{{ $slides[0]['button_text'] }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Individual slide captions -->
                @forelse($slides as $index => $slide)
                <div id="slider-direction-{{ $index + 1 }}" class="nivo-html-caption">
                    <div class="container">
                        <div class="slide-all">
                            @if(!empty($slide['title_line1']))
                            <div class="layer-1">
                                <h3 class="title5">{{ $slide['title_line1'] }}</h3>
                            </div>
                            @endif
                            
                            @if(!empty($slide['title_line2']))
                            <div class="layer-2">
                                <h1 class="title6">{{ $slide['title_line2'] }}</h1>
                            </div>
                            @endif
                            
                            @if(!empty($slide['title_line3']))
                            <div class="layer-2">
                                <h1 class="title6">{{ $slide['title_line3'] }}</h1>
                            </div>
                            @endif
                            
                            @if(!empty($slide['button_text']))
                            <div class="layer-3">
                                <a class="min1" href="{{ $slide['button_url'] ?? '#' }}">{{ $slide['button_text'] }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div id="slider-default" class="nivo-html-caption">
                    <div class="container">
                        <div class="slide-all">
                            <div class="layer-1">
                                <h3 class="title5">SAMPLE SLIDER</h3>
                            </div>
                            <div class="layer-2">
                                <h1 class="title6">ADD SLIDES IN</h1>
                            </div>
                            <div class="layer-2">
                                <h1 class="title6">YOUR ADMIN PANEL</h1>
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#{{ $sliderId }}').nivoSlider({
        effect: 'random',                    // Slide effect
        slices: 15,                         // For slice animations
        boxCols: 8,                         // For box animations
        boxRows: 4,                         // For box animations
        animSpeed: {{ $animationSpeed }},   // Slide transition speed
        pauseTime: {{ $autoplayDelay }},    // How long each slide will show
        startSlide: 0,                      // Set starting Slide (0 index)
        directionNav: {{ $showNavArrows ? 'true' : 'false' }},  // Next & Prev navigation
        controlNav: {{ $showPagination ? 'true' : 'false' }},   // 1,2,3... navigation
        controlNavThumbs: false,            // Use thumbnails for Control Nav
        pauseOnHover: true,                // Stop animation while hovering
        manualAdvance: {{ $autoplay ? 'false' : 'true' }},  // Force manual transitions
        prevText: 'Prev',                   // Prev directionNav text
        nextText: 'Next',                   // Next directionNav text
        randomStart: false,                 // Start on a random slide
        beforeChange: function(){},         // Triggers before a slide transition
        afterChange: function(){},          // Triggers after a slide transition
        slideshowEnd: function(){},         // Triggers after all slides have been shown
        lastSlide: function(){},            // Triggers when last slide is shown
        afterLoad: function(){}             // Triggers when slider has loaded
    });
});
</script>
