@php
/**
 * Counter Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $fields - Widget fields from content editor (contains repeater data)
 * $settings - Widget settings from admin panel
 */

// Extract repeater data - expecting array of counter items
$counters = $fields['counters'] ?? [];

// Extract settings with defaults
$countSuffix = $settings['count_suffix'] ?? '';
$backgroundColor = $settings['background_color'] ?? '#f6f6f6';
$padding = $settings['padding'] ?? 'ptb-70';
$animationSpeed = $settings['animation_speed'] ?? 2000;
$layoutStyle = $settings['layout_style'] ?? 'default';

// Ensure we have array data
if (!is_array($counters)) {
    $counters = [];
}

// If no counters, don't render anything
if (empty($counters)) {
    return;
}

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

// Calculate grid columns based on number of counters
$counterCount = count($counters);
$colClass = 'col-lg-3 col-md-6'; // Default: 4 columns
if ($counterCount == 1) {
    $colClass = 'col-lg-6 col-md-8 mx-auto';
} elseif ($counterCount == 2) {
    $colClass = 'col-lg-6 col-md-6';
} elseif ($counterCount == 3) {
    $colClass = 'col-lg-4 col-md-6';
}
@endphp

<section class="counter-area {{ empty($padding) ? 'ptb-70' : '' }} widget widget-counter" style="background-color: {{ $backgroundColor }};">
   
            <div class="row {{ $containerClass }} justify-content-center">
                @foreach($counters as $index => $counter)
                    @php
                        // Extract individual counter data with defaults
                        $icon = $counter['icon'] ?? '';
                        $topText = $counter['top_text'] ?? '';
                        $counterNumber = $counter['counter_number'] ?? 0;
                        
                        // Generate unique ID for each counter
                        $individualCounterId = $counterId . '-' . $index;
                    @endphp
                    
                    <div class="{{ $colClass }} mb-4">
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
                                     id="{{ $individualCounterId }}"
                                     data-count="{{ $counterNumber }}" 
                                     data-speed="{{ $animationSpeed }}">0</div>
                                @if(!empty($countSuffix))
                                    <span class="counter-suffix">{{ $countSuffix }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
      
</section>

@push('scripts')
<script>
    // Counter animation script
    document.addEventListener('DOMContentLoaded', function() {
        // Function to animate a single counter
        function animateCounter(counterElement) {
            const target = parseInt(counterElement.getAttribute('data-count'));
            const speed = parseInt(counterElement.getAttribute('data-speed')) || 2000;
            const increment = target / speed * 10;
            let current = 0;
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    if (current > target) current = target;
                    counterElement.innerText = Math.ceil(current);
                    setTimeout(updateCounter, 10);
                } else {
                    counterElement.innerText = target;
                }
            };
            
            updateCounter();
        }
        
        // Function to animate all counters
        function animateCounters() {
            const counters = document.querySelectorAll('#{{ $counterId }} .counter, [id^="{{ $counterId }}-"]');
            
            counters.forEach(counter => {
                // Only animate if it hasn't been animated yet
                if (!counter.hasAttribute('data-animated')) {
                    animateCounter(counter);
                    counter.setAttribute('data-animated', 'true');
                }
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

@push('styles')
<style>
    /* Counter widget responsive styles */
    .widget-counter .counter-all {
        text-align: center;
        padding: 30px 20px;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }
    
    .widget-counter .counter-top {
        margin-bottom: 20px;
    }
    
    .widget-counter .counter-icon {
        max-width: 80px;
        max-height: 80px;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    
    .widget-counter .counter-bottom h2 {
        font-size: 18px;
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .widget-counter .counter {
        font-size: 48px;
        font-weight: 700;
        color: #333;
        line-height: 1;
    }
    
    .widget-counter .counter-suffix {
        font-size: 24px;
        font-weight: 600;
        color: #666;
        margin-left: 5px;
    }
    
    /* Boxed style */
    .widget-counter .counter-boxed {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: 1px solid #f0f0f0;
    }
    
    .widget-counter .counter-boxed:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    /* Centered style */
    .widget-counter .counter-centered {
        max-width: 300px;
        margin: 0 auto;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .widget-counter .counter {
            font-size: 36px;
        }
        
        .widget-counter .counter-bottom h2 {
            font-size: 16px;
        }
        
        .widget-counter .counter-icon {
            max-width: 60px;
            max-height: 60px;
        }
        
        .widget-counter .counter-all {
            padding: 20px 15px;
        }
    }
    
    @media (max-width: 576px) {
        .widget-counter .counter {
            font-size: 28px;
        }
        
        .widget-counter .counter-bottom h2 {
            font-size: 14px;
        }
    }
</style>
@endpush
