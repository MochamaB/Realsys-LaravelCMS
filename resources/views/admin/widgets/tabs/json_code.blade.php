<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bx bx-code me-2"></i> Widget JSON Configuration
        </h5>
    </div>
    <div class="card-body">
        <div class="code-editor-container" style="height: 500px; border-radius: 5px; border: 1px solid #ced4da; overflow: hidden;">
            <pre id="jsonCodeEditor" class="language-json h-100 w-100" style="margin: 0; overflow: auto;">{{ $jsonContent ?? 'No JSON configuration found for this widget.' }}</pre>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="bx bx-info-circle me-2"></i>
            <strong>JSON Config Path:</strong> <code>resources/themes/{{ $widget->theme->slug }}/widgets/{{ $widget->slug }}/widget.json</code>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/vs2015.min.css">
<style>
    .hljs {
        background: #1e1e1e;
        color: #dcdcdc;
        padding: 1rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/languages/json.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize syntax highlighting
        hljs.highlightElement(document.getElementById('jsonCodeEditor'));
    });
</script>
@endpush