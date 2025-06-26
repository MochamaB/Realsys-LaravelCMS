@php
/**
 * Team Member Details Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $settings - Widget settings from admin panel
 */

// Get field values with defaults
$photo = $settings['photo'] ?? null;
$name = $settings['name'] ?? '';
$position = $settings['position'] ?? '';
$biography = $settings['biography'] ?? '';
$email = $settings['email'] ?? '';
$phone = $settings['phone'] ?? '';
$facebook = $settings['facebook'] ?? '';
$twitter = $settings['twitter'] ?? '';
$linkedin = $settings['linkedin'] ?? '';
$instagram = $settings['instagram'] ?? '';
$expertise = $settings['expertise'] ?? [];

// Get settings values with defaults
$layoutStyle = $settings['layout_style'] ?? 'standard';
$imagePosition = $settings['image_position'] ?? 'left';
$showSocial = $settings['show_social'] ?? true;
$showContactInfo = $settings['show_contact_info'] ?? true;
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$sectionPadding = $settings['section_padding'] ?? 'ptb-80';

// Set layout classes based on settings
$containerClasses = '';
$imageClasses = '';
$contentClasses = '';

switch($layoutStyle) {
    case 'sidebar':
        $containerClasses = 'row';
        $imageClasses = 'col-lg-4 col-md-5 mb-4 mb-md-0';
        $contentClasses = 'col-lg-8 col-md-7';
        break;
    case 'full':
        $containerClasses = 'row';
        $imageClasses = 'col-lg-3 col-md-4 mb-4 mb-md-0';
        $contentClasses = 'col-lg-9 col-md-8';
        break;
    default: // standard
        $containerClasses = 'row';
        if ($imagePosition === 'left') {
            $imageClasses = 'col-lg-5 col-md-6 mb-4 mb-md-0 order-md-1';
            $contentClasses = 'col-lg-7 col-md-6 order-md-2';
        } elseif ($imagePosition === 'right') {
            $imageClasses = 'col-lg-5 col-md-6 mb-4 mb-md-0 order-md-2';
            $contentClasses = 'col-lg-7 col-md-6 order-md-1';
        } elseif ($imagePosition === 'top') {
            $containerClasses = '';
            $imageClasses = 'text-center mb-4';
            $contentClasses = '';
        }
        break;
}
@endphp

<div class="widget widget-teamdetails">
    <section class="team-details {{ $sectionPadding }}" style="background-color: {{ $backgroundColor }};">
        <div class="container">
            @if($imagePosition === 'top')
                <div class="{{ $imageClasses }}">
                    @if($photo)
                        <img src="{{ $photo }}" alt="{{ $name }}" class="img-fluid rounded team-detail-image">
                    @else
                        <div class="placeholder-image" style="height: 350px; width: 350px; margin: 0 auto; background-color: #f2f2f2; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-user fa-4x" style="color: #cccccc;"></i>
                        </div>
                    @endif
                </div>
            @endif
            
            <div class="{{ $containerClasses }}">
                @if($imagePosition !== 'top')
                    <div class="{{ $imageClasses }}">
                        @if($photo)
                            <img src="{{ $photo }}" alt="{{ $name }}" class="img-fluid rounded team-detail-image">
                        @else
                            <div class="placeholder-image" style="height: 100%; min-height: 300px; background-color: #f2f2f2; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-user fa-4x" style="color: #cccccc;"></i>
                            </div>
                        @endif
                        
                        @if($showSocial && ($facebook || $twitter || $linkedin || $instagram))
                            <div class="team-social-links mt-3">
                                @if($facebook)
                                    <a href="{{ $facebook }}" target="_blank" class="social-icon"><i class="fa fa-facebook"></i></a>
                                @endif
                                
                                @if($twitter)
                                    <a href="{{ $twitter }}" target="_blank" class="social-icon"><i class="fa fa-twitter"></i></a>
                                @endif
                                
                                @if($linkedin)
                                    <a href="{{ $linkedin }}" target="_blank" class="social-icon"><i class="fa fa-linkedin"></i></a>
                                @endif
                                
                                @if($instagram)
                                    <a href="{{ $instagram }}" target="_blank" class="social-icon"><i class="fa fa-instagram"></i></a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
                
                <div class="{{ $contentClasses }}">
                    <div class="team-member-info">
                        <h2 class="member-name">{{ $name }}</h2>
                        <p class="member-position">{{ $position }}</p>
                        
                        @if($showContactInfo && ($email || $phone))
                            <div class="contact-info mb-4">
                                @if($email)
                                    <p><i class="fa fa-envelope mr-2"></i> <a href="mailto:{{ $email }}">{{ $email }}</a></p>
                                @endif
                                
                                @if($phone)
                                    <p><i class="fa fa-phone mr-2"></i> <a href="tel:{{ $phone }}">{{ $phone }}</a></p>
                                @endif
                            </div>
                        @endif
                        
                        @if($imagePosition === 'top' && $showSocial && ($facebook || $twitter || $linkedin || $instagram))
                            <div class="team-social-links mb-4">
                                @if($facebook)
                                    <a href="{{ $facebook }}" target="_blank" class="social-icon"><i class="fa fa-facebook"></i></a>
                                @endif
                                
                                @if($twitter)
                                    <a href="{{ $twitter }}" target="_blank" class="social-icon"><i class="fa fa-twitter"></i></a>
                                @endif
                                
                                @if($linkedin)
                                    <a href="{{ $linkedin }}" target="_blank" class="social-icon"><i class="fa fa-linkedin"></i></a>
                                @endif
                                
                                @if($instagram)
                                    <a href="{{ $instagram }}" target="_blank" class="social-icon"><i class="fa fa-instagram"></i></a>
                                @endif
                            </div>
                        @endif
                        
                        <div class="member-biography">
                            {!! $biography !!}
                        </div>
                        
                        @if(!empty($expertise))
                            <div class="expertise-area mt-4">
                                <h4>Areas of Expertise</h4>
                                <div class="row">
                                    @foreach($expertise as $item)
                                        <div class="col-md-6 mb-3">
                                            <div class="expertise-item">
                                                <h5>{{ $item['skill'] ?? '' }}</h5>
                                                @php
                                                    $level = $item['level'] ?? 'beginner';
                                                    $levelPercentage = [
                                                        'beginner' => 25,
                                                        'intermediate' => 50,
                                                        'advanced' => 75,
                                                        'expert' => 100
                                                    ][$level] ?? 25;
                                                @endphp
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $levelPercentage }}%;" 
                                                         aria-valuenow="{{ $levelPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                         {{ ucfirst($level) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
    .widget-teamdetails .team-detail-image {
        max-width: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .widget-teamdetails .member-name {
        margin-bottom: 5px;
    }
    .widget-teamdetails .member-position {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    .widget-teamdetails .team-social-links a {
        display: inline-block;
        width: 36px;
        height: 36px;
        line-height: 36px;
        text-align: center;
        background: #f8f9fa;
        border-radius: 50%;
        color: #333;
        margin-right: 8px;
        transition: all 0.3s ease;
    }
    .widget-teamdetails .team-social-links a:hover {
        background: #007bff;
        color: white;
    }
    .widget-teamdetails .contact-info p {
        margin-bottom: 5px;
    }
    .widget-teamdetails .member-biography {
        line-height: 1.8;
    }
    .widget-teamdetails .expertise-area h4 {
        margin-bottom: 15px;
    }
    .widget-teamdetails .expertise-item h5 {
        font-size: 1rem;
        margin-bottom: 8px;
    }
    .widget-teamdetails .progress {
        height: 10px;
    }
</style>
@endpush
