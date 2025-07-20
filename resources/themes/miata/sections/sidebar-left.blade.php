{{-- sidebar-left.blade.php --}}
<div class="section section-sidebar-left {{ $pageSection->css_classes ?? '' }}"
     id="section-{{ $section->slug }}"
     style="background-color: {{ $pageSection->background_color ?? 'transparent' }}; 
            padding: {{ $pageSection->padding ?? '2rem 0' }}; 
            margin: {{ $pageSection->margin ?? '0' }}">
    
    <div class="container">
        <div class="row">
            {{-- Sidebar (Left) --}}
            <div class="col-md-3 sidebar-column mb-4">
                <div class="sidebar-content">
                    @if(!empty($widgets))
                        @php
                            // Filter widgets for sidebar (column_position = 0)
                            $sidebarWidgets = collect($widgets)->filter(function($widget) {
                                return ($widget['column_position'] ?? 0) == 0;
                            });
                        @endphp
                        
                        @forelse($sidebarWidgets as $widget)
                            <div class="sidebar-widget mb-3">
                                @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                                    @include($widget['view_path'], ['widget' => $widget])
                                @else
                                    <div class="widget widget-{{ $widget['slug'] ?? 'unknown' }} p-3 bg-light border rounded">
                                        <h6>{{ $widget['name'] ?? 'Sidebar Widget' }}</h6>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-sidebar p-3 text-center bg-light border rounded">
                                <p class="text-muted small mb-0">Sidebar is empty</p>
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>
            
            {{-- Main Content (Right) --}}
            <div class="col-md-9 main-column">
                <div class="main-content">
                    @if(!empty($widgets))
                        @php
                            // Filter widgets for main content (column_position = 1)
                            $mainWidgets = collect($widgets)->filter(function($widget) {
                                return ($widget['column_position'] ?? 1) == 1;
                            });
                        @endphp
                        
                        @forelse($mainWidgets as $widget)
                            <div class="main-widget mb-4">
                                @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                                    @include($widget['view_path'], ['widget' => $widget])
                                @else
                                    <div class="widget widget-{{ $widget['slug'] ?? 'unknown' }} p-4 border rounded">
                                        <h5>{{ $widget['name'] ?? 'Main Widget' }}</h5>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-main-content p-4 text-center bg-light border rounded">
                                <h5>{{ $section->name ?? 'Main Content' }}</h5>
                                <p class="text-muted">Main content area is empty</p>
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>