{{-- Text Widget Template --}}
<div class="widget-text">
    @if(!empty($widget['fields']['title']))
        <h3 class="widget-title">{{ $widget['fields']['title'] }}</h3>
    @endif
    
    @if(!empty($widget['fields']['content']))
        <div class="widget-content">
            {!! $widget['fields']['content'] !!}
        </div>
    @endif
</div>
