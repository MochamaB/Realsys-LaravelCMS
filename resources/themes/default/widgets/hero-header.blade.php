<!-- Page Header-->
<?php
// DEBUGGING INFORMATION - Remove in production
\Log::debug('Hero Header Widget Template Variables', [
    'widget_type' => gettype($widget),
    'widget_keys' => is_array($widget) ? array_keys($widget) : 'not an array',
    'has_content' => is_array($widget) && isset($widget['content']) ? 'yes' : 'no',
]);

// START: This will intentionally generate an error to show the raw widget data
if (false) {
    echo '<pre>';
    var_dump($widget);
    echo '</pre>';
}
// END debugging section
?>

@php
    // Set default header values
    $title = 'Welcome to Our Website';
    $subtitle = 'A place for inspiration and creativity';
    $background = asset('img/theme/home-bg.jpg');
    $header = [];
    
    // Check if there's content data from widget service
    if (is_array($widget) && isset($widget['content']) && !empty($widget['content'])) {
        // This is the expected path
        // FROM LOGS: Content is nested under 'header' key in 'content'
        if (isset($widget['content']['header'])) {
            $header = $widget['content']['header'];
            
            // Use database content if available
            $title = $header['title'] ?? $title;
            $subtitle = $header['subtitle'] ?? $subtitle;
            
            // Set background image if available
            // The log shows 'background' not 'background_image'
            $backgroundUrl = $header['background'] ?? null;
            if ($backgroundUrl) {
                $background = asset($backgroundUrl);
            }
        } else {
            \Log::warning('Header key not found in widget content', [
                'widget_id' => $widget['id'] ?? 'unknown',
                'content_keys' => array_keys($widget['content'])
            ]);
        }
    }
    
    // Call to action button
    $ctaText = $header['cta_text'] ?? null;
    $ctaUrl = $header['cta_url'] ?? null;
@endphp
<header class="masthead" style="background-image: url('{{ $background }}')">
    <div class="container position-relative px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                <div class="site-heading">
                    <h1>{{ $title }}</h1>
                    @if($subtitle)
                        <span class="subheading">{{ $subtitle }}</span>
                    @endif
                    
                    @if($ctaText && $ctaUrl)
                        <div class="mt-4">
                            <a href="{{ $ctaUrl }}" class="btn btn-primary">{{ $ctaText }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
