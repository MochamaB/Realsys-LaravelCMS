@php
/**
 * Slider Widget View Template - Corrected to match original HTML structure
 * 
 * Available variables:
 * $widget - The widget instance
 * $fields - Widget fields from content editor (contains repeater data)
 * $settings - Widget settings from admin panel
 */

// Extract repeater data - expecting array of slide items
$slides = $fields['slides'] ?? [];

// Extract settings with defaults
$animationSpeed = $settings['animation_speed'] ?? 500;
$autoplay = $settings['autoplay'] ?? true;
$autoplayDelay = $settings['autoplay_delay'] ?? 5000;
$showNavArrows = $settings['show_nav_arrows'] ?? true;
$showPagination = $settings['show_pagination'] ?? true;

// Ensure we have array data
if (!is_array($slides)) {
    $slides = [];
}

// If no slides, don't render anything
if (empty($slides)) {
    return;
}

// Generate a unique ID for this slider instance
$sliderId = 'ensign-nivoslider-' . uniqid();
@endphp

<section class="slider-main-area">
    <div class="main-slider an-si">
        <div class="bend niceties preview-2">
            <div id="{{ $sliderId }}" class="slides nivoSlider">
                @foreach($slides as $index => $slide)
                    @php
                        // Extract individual slide data with defaults
                        $image = $slide['image'] ?? '';
                        $layer1Title = $slide['layer1_title'] ?? '';
                        $layer2Line1 = $slide['layer2_line1'] ?? '';
                        $layer2Line2 = $slide['layer2_line2'] ?? '';
                        $buttonText = $slide['button_text'] ?? '';
                        $buttonUrl = $slide['button_url'] ?? '#';
                        
                        // Generate direction ID for this slide
                        $directionId = 'slider-direction-' . ($index + 1);
                    @endphp
                    
                    @if(!empty($image))
                        <img src="{{ $image }}" alt="{{ $layer1Title }}" title="#{{ $directionId }}">
                    @endif
                @endforeach
            </div>
            
            @foreach($slides as $index => $slide)
                @php
                    // Extract individual slide data with defaults
                    $layer1Title = $slide['layer1_title'] ?? '';
                    $layer2Line1 = $slide['layer2_line1'] ?? '';
                    $layer2Line2 = $slide['layer2_line2'] ?? '';
                    $buttonText = $slide['button_text'] ?? '';
                    $buttonUrl = $slide['button_url'] ?? '#';
                    
                    // Generate direction ID for this slide
                    $directionId = 'slider-direction-' . ($index + 1);
                @endphp
                
                <!-- Direction content for slide {{ $index + 1 }} -->
                <div id="{{ $directionId }}" class="t-cn slider-direction Builder">
                    <div class="container">
                        <div class="slide-all {{ $index > 0 ? 'slide2' : '' }}">
                            @if(!empty($layer1Title))
                                <!-- layer 1 -->
                                <div class="layer-1">
                                    <h3 class="title5 {{ $index > 0 ? 'moment' : '' }}">{{ $layer1Title }}</h3>
                                </div>
                            @endif
                            
                            @if(!empty($layer2Line1))
                                <!-- layer 2 - Line 1 -->
                                <div class="layer-2">
                                    <h1 class="title6">{{ $layer2Line1 }}</h1>
                                </div>
                            @endif
                            
                            @if(!empty($layer2Line2))
                                <!-- layer 2 - Line 2 -->
                                <div class="layer-2">
                                    <h1 class="title6">{{ $layer2Line2 }}</h1>
                                </div>
                            @endif
                            
                            @if(!empty($buttonText))
                                <!-- layer 3 -->
                                <div class="layer-3">
                                    <a class="min1" href="{{ $buttonUrl }}">{{ $buttonText }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Nivo Slider for this instance
        $('#{{ $sliderId }}').nivoSlider({
            effect: 'random',
            slices: 15,
            boxCols: 8,
            boxRows: 4,
            animSpeed: {{ $animationSpeed }},
            pauseTime: {{ $autoplayDelay }},
            startSlide: 0,
            directionNav: {{ $showNavArrows ? 'true' : 'false' }},
            controlNav: {{ $showPagination ? 'true' : 'false' }},
            pauseOnHover: true,
            manualAdvance: {{ $autoplay ? 'false' : 'true' }},
            prevText: 'Prev',
            nextText: 'Next',
            beforeChange: function(){},
            afterChange: function(){},
            slideshowEnd: function(){},
            lastSlide: function(){},
            afterLoad: function(){}
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Ensure slider section has proper structure */
    .slider-main-area {
        position: relative;
        overflow: hidden;
    }
    
    .main-slider {
        position: relative;
    }
    
    .bend.niceties.preview-2 {
        position: relative;
    }
    
    /* Slider direction content positioning */
    .slider-direction {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        z-index: 10;
    }
    
    .slider-direction.Builder {
        display: block;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .slide-all .title5 {
            font-size: 18px;
        }
        
        .slide-all .title6 {
            font-size: 24px;
        }
        
        .slide-all .min1 {
            padding: 8px 20px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 576px) {
        .slide-all .title5 {
            font-size: 16px;
        }
        
        .slide-all .title6 {
            font-size: 20px;
        }
    }
</style>
@endpush