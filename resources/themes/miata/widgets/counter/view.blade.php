@php
/**
 * Counter Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $fields - Widget fields from content editor
 * $settings - Widget settings from admin panel
 */

// Extract field values with defaults
$icon = $fields['icon'] ?? '';
$topText = $fields['top_text'] ?? '';
$counterNumber = $fields['counter_number'] ?? 0;

// Extract settings with defaults
$countSuffix = $settings['count_suffix'] ?? '';
$backgroundColor = $settings['background_color'] ?? '#f6f6f6';
$padding = $settings['padding'] ?? 'ptb-70';
$animationSpeed = $settings['animation_speed'] ?? 2000;
$layoutStyle = $settings['layout_style'] ?? 'default';

// Generate a unique ID for this counter instance
$counterId = 'counter-' . uniqid();

// Layout style classes
$containerClass = '';
$counterClass = '';
switch ($layoutStyle) {
    case 'centered':
        $containerClass = 'text-center';
        $counterClass = 'counter-centered';
        break;
    case 'boxed':
        $containerClass = '';
        $counterClass = 'counter-boxed';
        break;
    default:
        $containerClass = '';
        $counterClass = '';
}
@endphp

<section class="elements-area ptb-140 widget widget-counter">
    <div class="counter_area {{ $padding }}" style="background-color: {{ $backgroundColor }};">
        <div class="container">
            <div class="row {{ $containerClass }}">
                <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
                    <div class="counter-all {{ $counterClass }}">
                        <div class="counter-top">
                            <a href="javascript:void(0);">
                                @if(!empty($icon))
                                    <img src="{{ $icon }}" alt="{{ $topText }}" class="counter-icon">
                                @endif
                            </a>
                        </div>
                        <div class="counter-bottom">
                            <div class="counter-next">
                                <h2>{{ $topText }}</h2>
                            </div>
                            <div class="counter cnt-one res" 
                                 data-count="{{ $counterNumber }}" 
                                 data-speed="{{ $animationSpeed }}">0</div>
                            @if(!empty($countSuffix))
                                <span class="counter-suffix">{{ $countSuffix }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Counter animation script
    document.addEventListener('DOMContentLoaded', function() {
        // Function to animate counters
        function animateCounters() {
            const counters = document.querySelectorAll('.counter');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const speed = parseInt(counter.getAttribute('data-speed')) || 2000;
                const increment = target / speed * 10;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        if (current > target) current = target;
                        counter.innerText = Math.ceil(current);
                        setTimeout(updateCounter, 10);
                    } else {
                        counter.innerText = target;
                    }
                };
                
                updateCounter();
            });
        }
        
        // Check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
        
        // Initialize counters when they come into view
        function checkCounters() {
            const counterSection = document.querySelector('.counter_area');
            if (counterSection && isInViewport(counterSection)) {
                animateCounters();
                window.removeEventListener('scroll', checkCounters);
            }
        }
        
        // Check on scroll and initial load
        window.addEventListener('scroll', checkCounters);
        checkCounters();
    });
</script>
@endpush
