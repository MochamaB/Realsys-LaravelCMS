@php
/**
 * Counter Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $settings - Widget settings from admin panel
 */

// Set default values if settings are not provided
$backgroundColor = $settings['background_color'] ?? '#f6f6f6';
$padding = $settings['padding'] ?? 'ptb-70';
$animationSpeed = $settings['animation_speed'] ?? 2000;
$counterItems = $settings['counter_items'] ?? [];
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

<div class="widget widget-counter">
    <section class="counter_area {{ $padding }}" style="background-color: {{ $backgroundColor }};">
        <div class="container">
            <div class="row {{ $containerClass }}">
                @foreach($counterItems as $item)
                    <div class="col-lg-{{ 12 / min(count($counterItems), 4) }} col-md-{{ 12 / min(count($counterItems), 3) }} col-12">
                        <div class="counter-all {{ $counterClass }}">
                            <div class="counter-top">
                                <a href="javascript:void(0);">
                                    @if(isset($item['icon']) && !empty($item['icon']))
                                        <img src="{{ $item['icon'] }}" alt="{{ $item['title'] ?? '' }}" class="counter-icon">
                                    @endif
                                </a>
                            </div>
                            <div class="counter-bottom">
                                <div class="counter-next">
                                    <h2>{{ $item['title'] ?? 'Counter' }}</h2>
                                </div>
                                <div class="counter cnt-one res" 
                                     data-count="{{ $item['count_value'] ?? 0 }}" 
                                     data-speed="{{ $animationSpeed }}">0</div>
                                @if(isset($item['count_suffix']) && !empty($item['count_suffix']))
                                    <span class="counter-suffix">{{ $item['count_suffix'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

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
