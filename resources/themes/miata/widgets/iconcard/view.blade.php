@php
/**
 * Icon Card Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $settings - Widget settings from admin panel
 */

// Get field values with defaults
$sectionTitle = $settings['section_title'] ?? '';
$iconCards = $settings['icon_cards'] ?? [];

// Get settings values with defaults
$sectionPadding = $settings['section_padding'] ?? 'ptb-80';
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$textAlignment = $settings['text_alignment'] ?? 'text-center';
$cardsPerRow = (int)($settings['cards_per_row'] ?? 3);

// Calculate Bootstrap column classes based on cards per row
switch($cardsPerRow) {
    case 1:
        $colClass = 'col-lg-12 col-md-12';
        break;
    case 2:
        $colClass = 'col-lg-6 col-md-6';
        break;
    case 3:
        $colClass = 'col-lg-4 col-md-4';
        break;
    case 4:
        $colClass = 'col-lg-3 col-md-3';
        break;
    default:
        $colClass = 'col-lg-4 col-md-4';
}
@endphp

<div class="widget widget-iconcard">
    <section class="what-area section-margin {{ $sectionPadding }}" style="background-color: {{ $backgroundColor }};">
        <div class="container">
            @if(!empty($sectionTitle))
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h2 class="section-heading">{{ $sectionTitle }}</h2>
                </div>
            </div>
            @endif
            
            <div class="row {{ $textAlignment }}">
                @foreach($iconCards as $card)
                <div class="{{ $colClass }} col-sm-12 col-12 mb-4">
                    <div class="what-bottom">
                        <div class="btn-icon">
                            <div class="then-icon">
                                @if(!empty($card['link_url']))
                                <a href="{{ $card['link_url'] }}">
                                    <i class="{{ $card['icon'] ?? 'fa fa-crosshairs' }}" aria-hidden="true"></i>
                                </a>
                                @else
                                <a href="javascript:void(0);">
                                    <i class="{{ $card['icon'] ?? 'fa fa-crosshairs' }}" aria-hidden="true"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="mission-text">
                            <h3>{{ $card['heading'] ?? 'Heading' }}</h3>
                        </div>
                        <p>{{ $card['content'] ?? 'Content text goes here' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
