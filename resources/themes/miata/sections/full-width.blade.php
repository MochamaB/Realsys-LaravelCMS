<section class="section-default">
    <div class="container">
        <div class="template-row p-4 mb-3 " id="section-{{ $section->slug }}">
            <h4>{{ $section->name }}</h4>
            <small class="text-muted">Type: Row (Full Width)</small>
            
            {{-- Render widgets for this section --}}
            @if($widgets && count($widgets) > 0)
                <div class="section-widgets mt-3">
                    @foreach($widgets as $widget)
                        <div class="widget widget-{{ $widget['slug'] }} mb-4">
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
                                    @if(config('app.debug'))
                                        <details class="mt-2">
                                            <summary>Debug Info</summary>
                                            <pre>{{ json_encode($widget, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-widgets mt-3">
                    <em class="text-muted">No widgets assigned to this section</em>
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
            @endif
        </div>
    </div>
</section>