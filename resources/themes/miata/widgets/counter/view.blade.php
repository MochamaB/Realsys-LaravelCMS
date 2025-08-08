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

{{-- Counter JavaScript moved to custom.js asset file --}}
{{-- Animation will be handled by the widget asset system --}}

{{-- Counter CSS moved to custom.css asset file --}}
{{-- Styles will be handled by the widget asset system --}}
