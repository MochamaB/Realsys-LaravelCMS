{{-- Original Miata theme section structure with multi-column support --}}
<div class="col-12" id="section-{{ $section->slug }}">
    <div class="container">
        @if($widgets && count($widgets) > 0)
            @php
                $widgetCount = count($widgets);
                $colClass = 'col-12';
                
                // Determine column classes based on widget count
                if ($widgetCount == 2) {
                    $colClass = 'col-md-6';
                } elseif ($widgetCount == 3) {
                    $colClass = 'col-md-4';
                } elseif ($widgetCount == 4) {
                    $colClass = 'col-md-3';
                } elseif ($widgetCount > 4) {
                    $colClass = 'col-md-2';
                }
            @endphp
            
            <div class="row">
                @foreach($widgets as $widget)
                    <div class="{{ $colClass }} mb-4">
                        @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                            @include($widget['view_path'], [
                                'fields' => $widget['fields'] ?? [],
                                'settings' => $widget['settings'] ?? [],
                                'widget' => $widget
                            ])
                        @else
                            {{-- Fallback if widget view not found --}}
                            <div class="widget-fallback alert alert-warning">
                                <strong>Widget:</strong> {{ $widget['name'] ?? 'Unknown Widget' }}<br>
                                <small>View not found: {{ $widget['view_path'] ?? 'N/A' }}</small>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="no-widgets text-center">
                        <em class="text-muted">No widgets assigned to this section</em>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>