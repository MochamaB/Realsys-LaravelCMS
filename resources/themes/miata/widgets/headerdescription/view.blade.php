@php
/**
 * Header with Description Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $settings - Widget settings from admin panel
 */

// Get field values with defaults
$title = $settings['title'] ?? 'Heading';
$description = $settings['description'] ?? '';

// Get settings values with defaults
$alignment = $settings['alignment'] ?? 'text-center';
$iconClass = $settings['icon'] ?? 'fa fa-bookmark';
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$padding = $settings['padding'] ?? 'p-4';
@endphp

<div class="widget widget-header-description" style="background-color: {{ $backgroundColor }};">
    <div class="row {{ $padding }}">
        <div class="col-md-12 {{ $alignment }}">
            <div class="what-top">
                <div class="section-title">
                    <h1>{{ $title }}</h1>
                    @if(!empty($iconClass))
                    <div class="what-icon">
                        <i class="{{ $iconClass }}" aria-hidden="true"></i>
                    </div>
                    @endif
                </div>
                @if(!empty($description))
                <p>{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
