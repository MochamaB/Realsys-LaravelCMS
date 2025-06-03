{{-- Default Widget Template - Used as a fallback for widgets without specific views --}}
<div class="widget widget-default">
    <div class="widget-header">
        <h4>{{ $widget['name'] ?? 'Widget' }}</h4>
    </div>
    <div class="widget-content">
        @if(!empty($widget['fields']))
            <dl>
                @foreach($widget['fields'] as $key => $value)
                    <dt>{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                    <dd>
                        @if(is_array($value))
                            {{ json_encode($value) }}
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                @endforeach
            </dl>
        @else
            <div class="debug-widget-info">
                <h5>Widget Debug Information</h5>
                <p><strong>Widget ID:</strong> {{ $widget['id'] ?? 'unknown' }}</p>
                <p><strong>Widget Slug:</strong> {{ $widget['slug'] ?? 'unknown' }}</p>
                <details>
                    <summary>Widget Data Structure</summary>
                    <pre>{{ json_encode($widget ?? [], JSON_PRETTY_PRINT) }}</pre>
                </details>
            </div>
        @endif
    </div>
</div>
