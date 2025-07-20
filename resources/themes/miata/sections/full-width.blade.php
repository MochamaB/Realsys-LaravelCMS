<section class="section-default">
    <div class="container">
        <div class="template-row p-4 mb-3 " id="section-{{ $templateSection->slug }}">
            <h4>{{ $templateSection->name }}</h4>
            <small class="text-muted">Type: Row (Full Width)</small>
            
            {{-- Render widgets for this section --}}
            @if($sectionData && isset($sectionData['widgets']) && count($sectionData['widgets']) > 0)
                <div class="section-widgets mt-3">
                    @foreach($sectionData['widgets'] as $widget)
                        <div class="widget-container mb-2 p-2 border-left border-primary">
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
                    <em class="text-muted">No widgets assigned to this section</em>
                </div>
            @endif
        </div>
    </div>
</section>