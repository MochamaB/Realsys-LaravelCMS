@php
/**
 * Team Members Widget View Template
 * 
 * Available variables:
 * $widget - The widget instance
 * $settings - Widget settings from admin panel
 */

// Get field values with defaults
$sectionTitle = $settings['section_title'] ?? '';
$sectionDescription = $settings['section_description'] ?? '';
$teamMembers = $settings['team_members'] ?? [];

// Get settings values with defaults
$sectionPadding = $settings['section_padding'] ?? 'ptb-80';
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$textAlignment = $settings['text_alignment'] ?? 'text-center';
$membersPerRow = (int)($settings['members_per_row'] ?? 3);
$showSocial = $settings['show_social'] ?? true;

// Calculate Bootstrap column classes based on members per row
switch($membersPerRow) {
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

<div class="widget widget-team">
    <section class="team-area section-margin {{ $sectionPadding }}" style="background-color: {{ $backgroundColor }};">
        <div class="container">
            @if(!empty($sectionTitle) || !empty($sectionDescription))
            <div class="row mb-5">
                <div class="col-12 {{ $textAlignment }}">
                    @if(!empty($sectionTitle))
                    <h2 class="section-heading">{{ $sectionTitle }}</h2>
                    @endif
                    
                    @if(!empty($sectionDescription))
                    <div class="section-description">
                        <p>{{ $sectionDescription }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <div class="row {{ $textAlignment }}">
                @foreach($teamMembers as $member)
                <div class="{{ $colClass }} col-sm-12 col-12 mb-4">
                    <div class="team-member">
                        <div class="team-thumb">
                            @if(!empty($member['photo']))
                            <img src="{{ $member['photo'] }}" alt="{{ $member['name'] ?? 'Team Member' }}" class="img-fluid">
                            @else
                            <div class="placeholder-image" style="height: 280px; background-color: #f2f2f2; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-user fa-4x" style="color: #cccccc;"></i>
                            </div>
                            @endif
                            
                            @if($showSocial)
                            <div class="team-social">
                                @if(!empty($member['facebook']))
                                <a href="{{ $member['facebook'] }}" target="_blank"><i class="fa fa-facebook"></i></a>
                                @endif
                                
                                @if(!empty($member['twitter']))
                                <a href="{{ $member['twitter'] }}" target="_blank"><i class="fa fa-twitter"></i></a>
                                @endif
                                
                                @if(!empty($member['linkedin']))
                                <a href="{{ $member['linkedin'] }}" target="_blank"><i class="fa fa-linkedin"></i></a>
                                @endif
                                
                                @if(!empty($member['instagram']))
                                <a href="{{ $member['instagram'] }}" target="_blank"><i class="fa fa-instagram"></i></a>
                                @endif
                            </div>
                            @endif
                        </div>
                        
                        <div class="team-info mt-3">
                            <h3>{{ $member['name'] ?? 'Team Member' }}</h3>
                            <span class="position">{{ $member['position'] ?? 'Position' }}</span>
                            
                            @if(!empty($member['bio']))
                            <div class="bio mt-2">
                                <p>{{ $member['bio'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
    .team-member {
        transition: all 0.3s ease;
    }
    .team-member:hover {
        transform: translateY(-5px);
    }
    .team-thumb {
        position: relative;
        overflow: hidden;
    }
    .team-social {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0,0,0,0.7);
        padding: 10px;
        opacity: 0;
        transition: all 0.3s ease;
        display: flex;
        justify-content: center;
    }
    .team-thumb:hover .team-social {
        opacity: 1;
    }
    .team-social a {
        display: inline-block;
        margin: 0 10px;
        color: white;
        font-size: 18px;
    }
    .team-social a:hover {
        color: #f5f5f5;
    }
    .team-info h3 {
        margin-bottom: 5px;
    }
    .position {
        display: block;
        color: #666;
        font-style: italic;
    }
</style>
@endpush
