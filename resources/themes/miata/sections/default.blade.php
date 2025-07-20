{{-- Default Section Template - Used as a fallback for all section types --}}
<section class="section section-default {{ $pageSection->css_classes ?? '' }}"
     id="section-{{ $section->slug }}"
     style="background-color: {{ $pageSection->background_color ?? 'transparent' }}; 
            padding: {{ $pageSection->padding ?? '1rem' }}; 
            margin: {{ $pageSection->margin ?? '0' }}">
    
    <div class="container">
        <div class="section-header mb-4">
            <h3>{{ $section->name }}</h3>
        </div>
        
        <div class="section-content">
            @if(empty($widgets))
                <div class="empty-section-placeholder p-4 text-center bg-light border rounded">
                    <h4 class="text-secondary">{{ $section->name ?? 'Section' }}</h4>
                    <p class="text-muted">This section is empty. Add widgets to display content.</p>
                </div>
            @else
                <div class="row widgets-container">
                    @foreach($widgets as $widget)
                        <div class="col-12 mb-4">
                            @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                                @include($widget['view_path'], ['widget' => $widget])
                            @else
                                <div class="widget widget-{{ $widget['slug'] ?? 'unknown' }} p-3 border rounded">
                                    <h5>{{ $widget['name'] ?? 'Widget' }}</h5>
                                    <p class="text-muted">Widget template not found.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>