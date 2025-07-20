<div class="row mb-4">
    @php
        $columns = explode('-', $templateSection->column_layout ?? '12');
    @endphp
    @foreach($columns as $colIndex => $col)
        <div class="col-md-{{ $col }}">
            <div class="template-box p-3 mb-3 bg-white border rounded">
                <h5>{{ $templateSection->name }} - Column {{ $colIndex + 1 }}</h5>
                <small class="text-muted">Type: Box ({{ $col }} columns)</small>
                
                @if($sectionData && isset($sectionData['widgets']))
                    @php
                        $columnWidgets = collect($sectionData['widgets'])->filter(function($widget) use ($colIndex) {
                            return ($widget['column_position'] ?? 0) == $colIndex;
                        });
                    @endphp
                    
                    @if($columnWidgets->count() > 0)
                        <div class="section-widgets mt-3">
                            @foreach($columnWidgets as $widget)
                                <div class="widget-container mb-2 p-2 border-left border-success">
                                    <strong>Widget:</strong> {{ $widget['name'] ?? 'Unknown Widget' }}
                                    <br><small>Type: {{ $widget['type'] ?? 'N/A' }}</small>
                                    @if(isset($widget['content']))
                                        <div class="widget-content mt-2">
                                            {!! $widget['content'] !!}
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
                @endif
            </div>
        </div>
    @endforeach
</div>