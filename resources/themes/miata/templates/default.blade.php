@extends('theme::layouts.theme')

@section('content')
<section class="elements-area ptb-140">
    <div class="container">
        {{-- Render template sections as rows and boxes --}}
        @if(isset($template) && $template->sections->count())
            @foreach($template->sections as $section)
                @if($section->section_type === 'full-width')
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="template-row p-4 mb-3 bg-light border rounded">
                                <h4>{{ $section->name }}</h4>
                                <small class="text-muted">Type: Row (Full Width)</small>
                                {{-- If you want to render widgets/boxes inside, add here --}}
                            </div>
                        </div>
                    </div>
                @elseif($section->section_type === 'multi-column')
                    <div class="row mb-4">
                        @php
                            $columns = explode('-', $section->column_layout ?? '12');
                        @endphp
                        @foreach($columns as $col)
                            <div class="col-md-{{ $col }}">
                                <div class="template-box p-3 mb-3 bg-white border rounded">
                                    <h5>{{ $section->name }}</h5>
                                    <small class="text-muted">Type: Box ({{ $col }} columns)</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="template-section p-3 mb-3 bg-secondary text-white border rounded">
                                <h5>{{ $section->name }}</h5>
                                <small class="text-muted">Type: {{ ucfirst(str_replace('-', ' ', $section->section_type)) }}</small>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">No sections defined for this template yet.</div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection