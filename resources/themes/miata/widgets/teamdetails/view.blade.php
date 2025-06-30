@php
/**
 * Team Member Details Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $fields - Widget fields for content
 * $settings - Widget settings from admin panel
 */

// Get field values with defaults
$photo = $fields['photo'] ?? null;
$name = $fields['name'] ?? '';
$position = $fields['position'] ?? '';
$biography = $fields['biography'] ?? '';
$email = $fields['email'] ?? '';
$phone = $fields['phone'] ?? '';
$facebook = $fields['facebook'] ?? '';
$twitter = $fields['twitter'] ?? '';
$linkedin = $fields['linkedin'] ?? '';
$instagram = $fields['instagram'] ?? '';
$expertise = $fields['expertise'] ?? [];

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
<section class="team-details-area ptb-80">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="tab-content2 tab-content">
                                <div role="tabpanel" class="tab-pane active" id="one">
                                    @if($photo)
                                    <div class="tab-img2">
                                        <img src="{{ $photo }}" alt="">
                                    </div>
                                    @else
                                    <div class="tab-img2">
                                        <img src="{{asset('img/default-user.png')}}" alt="">
                                    </div>
                                    @endif
                                    <div class="team-details-all fix">
                                        <div class="team-details-top">
                                            <div class="team-details-text">
                                                <h1>{{ $name }}</h1>
                                                <h3>{{ $position }}</h3>
                                                <p class="stone"><span>{{ $name }}</span>{{ $biography }}</p>
                                                
                                            </div>
                                            <div class="team-icon">
                                                <ul>
                                                    <li>
                                                        <a href="{{ $facebook }}">
                                                            <i class="fa fa-facebook"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ $twitter }}">
                                                            <i class="fa fa-google-plus"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ $linkedin }}">
                                                            <i class="fa fa-twitter" aria-hidden="true"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ $instagram }}">
                                                            <i class="fa fa-instagram" aria-hidden="true"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="event-list">
                                                <ul>
                                                    <li><i class="fa fa-map-marker ex" aria-hidden="true"></i><span>Where: </span>  {{ $address }}</li>
                                                    <li><i class="fa fa-phone" aria-hidden="true"></i><a href="#"><span>Phone: </span> {{ $phone }}</a></li>
                                                    <li><i class="fa fa-envelope" aria-hidden="true"></i><a href="#"><span>Email: </span> {{ $email }}</a></li>
                                                    <li><i class="fa fa-chrome" aria-hidden="true"></i><a href="#"><span>Website: </span> {{ $website }}</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                        </div>
                    </div>

                </div>
            </section>



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
