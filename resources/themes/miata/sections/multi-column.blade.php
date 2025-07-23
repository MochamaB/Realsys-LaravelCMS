<div class="row mb-4">
    @php
        $columns = explode('-', $section->column_layout ?? '12');
    @endphp
    @foreach($columns as $colIndex => $col)
        <div class="col-md-{{ $col }}">
            <div class="template-box p-3 mb-3 bg-white border rounded">
                <h5>{{ $section->name }} - Column {{ $colIndex + 1 }}</h5>
                <small class="text-muted">Type: Box ({{ $col }} columns)</small>
                
                @if($widgets && count($widgets) > 0)
                    @php
                        $columnWidgets = collect($widgets)->filter(function($widget) use ($colIndex) {
                            return ($widget['column_position'] ?? 0) == $colIndex;
                        });
                    @endphp
                    
                    @if($columnWidgets->count() > 0)
                        <div class="section-widgets mt-3">
                            @foreach($columnWidgets as $widget)
                                <div class="widget widget-{{ $widget['slug'] }} mb-3">
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
                            <em class="text-muted">No widgets in this column</em>
                        </div>
                    @endif
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
    @endforeach
</div>