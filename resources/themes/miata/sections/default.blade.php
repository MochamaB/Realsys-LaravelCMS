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
                    @if(config('app.debug'))
                        <div class="debug-info mt-2">
                            <small class="text-muted">
                                Section ID: {{ $pageSection->id ?? 'N/A' }}<br>
                                Section Slug: {{ $section->slug ?? 'N/A' }}<br>
                                Widget Count: {{ is_array($widgets) ? count($widgets) : 0 }}
                            </small>
                        </div>
                    @endif
                </div>
            @else
                <div class="row widgets-container">
                    @foreach($widgets as $widget)
                        <div class="col-12 mb-4">
                            <div class="widget widget-{{ $widget['slug'] ?? 'unknown' }}">
                                @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                                    @include($widget['view_path'], [
                                        'fields' => $widget['fields'] ?? [],
                                        'settings' => $widget['settings'] ?? [],
                                        'widget' => $widget
                                    ])
                                @else
                                    {{-- Fallback if widget view not found --}}
                                    <div class="widget-fallback alert alert-warning">
                                        <h5>{{ $widget['name'] ?? 'Widget' }}</h5>
                                        <p class="text-muted">Widget template not found: {{ $widget['view_path'] ?? 'N/A' }}</p>
                                        @if(config('app.debug'))
                                            <details class="mt-2">
                                                <summary>Debug Info</summary>
                                                <pre>{{ json_encode($widget, JSON_PRETTY_PRINT) }}</pre>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>