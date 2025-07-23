{{-- Original Miata theme section structure with sidebar layout --}}
<section class="elements-area ptb-140" id="section-{{ $section->slug }}">
    <div class="container">
        <div class="row">
            {{-- Sidebar Column (Left) --}}
            <div class="col-lg-4 col-md-5">
                <div class="sidebar">
                    @php
                        $sidebarWidgets = collect($widgets)->filter(function($widget) {
                            return ($widget['position'] ?? 'main') === 'sidebar';
                        });
                    @endphp
                    
                    @if($sidebarWidgets->count() > 0)
                        @foreach($sidebarWidgets as $widget)
                            <div class="sidebar-widget mb-4">
                                @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                                    @include($widget['view_path'], [
                                        'fields' => $widget['fields'] ?? [],
                                        'settings' => $widget['settings'] ?? [],
                                        'widget' => $widget
                                    ])
                                @else
                                    <div class="widget-fallback alert alert-warning">
                                        <strong>Widget:</strong> {{ $widget['name'] ?? 'Unknown Widget' }}<br>
                                        <small>View not found: {{ $widget['view_path'] ?? 'N/A' }}</small>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="empty-sidebar text-center text-muted">
                            <em>No sidebar widgets</em>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Main Content Column (Right) --}}
            <div class="col-lg-8 col-md-7">
                <div class="main-content">
                    @php
                        $mainWidgets = collect($widgets)->filter(function($widget) {
                            return ($widget['position'] ?? 'main') === 'main' || !isset($widget['position']);
                        });
                    @endphp
                    
                    @if($mainWidgets->count() > 0)
                        @foreach($mainWidgets as $widget)
                            <div class="main-widget mb-4">
                                @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                                    @include($widget['view_path'], [
                                        'fields' => $widget['fields'] ?? [],
                                        'settings' => $widget['settings'] ?? [],
                                        'widget' => $widget
                                    ])
                                @else
                                    <div class="widget-fallback alert alert-warning">
                                        <strong>Widget:</strong> {{ $widget['name'] ?? 'Unknown Widget' }}<br>
                                        <small>View not found: {{ $widget['view_path'] ?? 'N/A' }}</small>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="empty-main text-center text-muted">
                            <em>No main content widgets</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>