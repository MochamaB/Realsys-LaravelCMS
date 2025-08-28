{{-- ✅ NEW VERSION with Universal Styling --}}
<x-universal-section :pageSection="$pageSection">
   
        <div class="row">
            <div class="col-12">
                {{-- Render widgets for this section --}}
                @if($widgets && count($widgets) > 0)
                    @foreach($widgets as $widget)
                        @if(isset($widget['view_path']) && View::exists($widget['view_path']))
                            <x-universal-widget :pageSectionWidget="$widget['pageSectionWidget']">
                                @include($widget['view_path'], [
                                    'fields' => $widget['fields'] ?? [],
                                    'settings' => $widget['settings'] ?? [],
                                    'widget' => $widget,
                                    'useCustomData' => false
                                ])
                            </x-universal-widget>
                        @else
                            {{-- Fallback if widget view not found --}}
                            <div class="widget-fallback alert alert-warning">
                                <strong>Widget:</strong> {{ $widget['name'] ?? 'Unknown Widget' }}<br>
                                <small>View not found: {{ $widget['view_path'] ?? 'N/A' }}</small>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="no-widgets text-center">
                            <em class="text-muted">No widgets assigned to this section</em>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    
</x-universal-section>