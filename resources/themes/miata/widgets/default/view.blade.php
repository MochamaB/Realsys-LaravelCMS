@php
    // Extract field values with defaults
    $title = $fields['title'] ?? 'Default Title';
    $content = $fields['content'] ?? '<p>This is the default content for this widget.</p>';
    $buttonText = $fields['button_text'] ?? '';
    $buttonUrl = $fields['button_url'] ?? '#';
    $layoutStyle = $fields['layout_style'] ?? 'default';
    
    // Determine CSS classes based on layout style
    $containerClass = '';
    switch($layoutStyle) {
        case 'boxed':
            $containerClass = 'content-box py-4 px-4 mb-5 bg-white shadow-sm';
            break;
        case 'full-width':
            $containerClass = 'content-full-width py-5 bg-light';
            break;
        default:
            $containerClass = 'content-default py-3';
    }
@endphp

<div class="widget widget-default {{ $containerClass }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center">
                    <h2>{{ $title }}</h2>
                    <div class="widget-content mt-4">
                        {!! $content !!}
                    </div>
                    
                    @if(!empty($buttonText))
                    <div class="button-area mt-4">
                        <a href="{{ $buttonUrl }}" class="btn btn-primary">{{ $buttonText }}</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
