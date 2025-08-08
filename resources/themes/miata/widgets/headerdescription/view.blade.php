@php
/**
 * Header with Description Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $fields - Widget field values from content editor
 * $settings - Widget settings from admin panel
 */

// Get field values from content editor (these are the actual content)
$title = $fields['title'] ?? 'Heading';
$description = $fields['description'] ?? '';
$iconImage = $fields['icon'] ?? null; // This is an image field

// Get settings values from admin panel (these are styling/configuration)
$alignment = $settings['alignment'] ?? 'text-center';
$iconClass = $settings['icon'] ?? 'fa fa-bookmark';
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$padding = $settings['padding'] ?? 'p-4';
@endphp
<section class="counter_area ptb-70" style="background-color: {{ $backgroundColor }};">
<div class="widget widget-header-description">
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
</section>
