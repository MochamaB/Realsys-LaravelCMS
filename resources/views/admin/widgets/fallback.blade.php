<div class="widget-fallback alert alert-warning border-2 border-warning" style="margin: 10px; padding: 15px;">
    <div class="d-flex align-items-center">
        <i class="ri-error-warning-line fs-4 me-2"></i>
        <div>
            <h6 class="mb-1">{{ $widget->name ?? 'Widget' }}</h6>
            <small class="text-muted">{{ $error ?? 'Widget could not be rendered' }}</small>
        </div>
    </div>
    @if(config('app.debug'))
        <div class="mt-2">
            <small class="text-muted">
                Widget ID: {{ $widget->id ?? 'N/A' }}<br>
                Slug: {{ $widget->slug ?? 'N/A' }}
            </small>
        </div>
    @endif
</div> 