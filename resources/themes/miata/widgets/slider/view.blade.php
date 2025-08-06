@php
/**
 * Slider Widget View Template - Corrected to match original HTML structure
 */
$slides = $fields['slides'] ?? [];
$animationSpeed = $settings['animation_speed'] ?? 500;
$autoplay = $settings['autoplay'] ?? true;
$autoplayDelay = $settings['autoplay_delay'] ?? 5000;
$showNavArrows = $settings['show_nav_arrows'] ?? true;
$showPagination = $settings['show_pagination'] ?? true;

if (!is_array($slides)) $slides = [];
if (empty($slides)) return;

$sliderId = 'ensign-nivoslider-' . uniqid();
@endphp

<section class="slider-main-area">
    <div class="main-slider an-si">
        <div class="bend niceties preview-2">
            <div id="{{ $sliderId }}" class="slides nivoSlider">
                @foreach($slides as $index => $slide)
                    @php
                        $image = $slide['image'] ?? '';
                        $title = $slide['layer1_title'] ?? '';
                        $directionId = 'slider-direction-' . ($index + 1);
                    @endphp
                    
                    @if(!empty($image))
                        <img src="{{ $image }}" alt="{{ $title }}" title="#{{ $directionId }}">
                    @endif
                @endforeach
            </div>
            
            @foreach($slides as $index => $slide)
                @php
                    $title = $slide['layer1_title'] ?? '';
                    $line1 = $slide['layer2_line1'] ?? '';
                    $line2 = $slide['layer2_line2'] ?? '';
                    $buttonText = $slide['button_text'] ?? '';
                    $buttonUrl = $slide['button_url'] ?? '#';
                    $directionId = 'slider-direction-' . ($index + 1);
                @endphp
                
                <div id="{{ $directionId }}" class="slider-direction">
                    <div class="container">
                        <div class="slide-all {{ $index > 0 ? 'slide2' : '' }}">
                            @if(!empty($title))
                                <div class="layer-1">
                                    <h3 class="title5 {{ $index > 0 ? 'moment' : '' }}">{{ $title }}</h3>
                                </div>
                            @endif
                            
                            @if(!empty($line1))
                                <div class="layer-2">
                                    <h1 class="title6">{{ $line1 }}</h1>
                                </div>
                            @endif
                            
                            @if(!empty($line2))
                                <div class="layer-2">
                                    <h1 class="title6">{{ $line2 }}</h1>
                                </div>
                            @endif
                            
                            @if(!empty($buttonText))
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

@push('styles')
<style>
    /* Remove default spacing */
    .slider-main-area, 
    .main-slider, 
    .bend.niceties.preview-2,
    .nivoSlider {
        margin: 0;
        padding: 0;
        width: 100%;
    }
    
    /* Ensure slider fills container */
    .nivoSlider img {
        width: 100%;
        display: block;
    }
    
    /* Position captions absolutely */
    .slider-direction {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        transform: translateY(-50%);
        text-align: center;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .title5 { font-size: 18px; }
        .title6 { font-size: 24px; }
        .min1 { padding: 8px 20px; font-size: 14px; }
    }
    
    @media (max-width: 576px) {
        .title5 { font-size: 16px; }
        .title6 { font-size: 20px; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            nextText: 'Next'
        });
    });
</script>
@endpush