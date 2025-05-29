@extends('layouts.front')

@section('title', $page->title)

@section('meta')
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif
@endsection

@section('content')
<div class="container py-5">
    @if(isset($preview) && $preview)
        <div class="alert alert-info mb-4">
            <strong>Preview Mode:</strong> You are viewing a preview of this page. Some features may not be fully functional.
        </div>
    @endif
    
    <h1 class="mb-4">{{ $page->title }}</h1>
    
    @if($page->featured_image)
        <div class="mb-4">
            <img src="{{ asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}" class="img-fluid rounded">
        </div>
    @endif

    <div class="page-content">
        @if($page->sections->isNotEmpty())
            @foreach($page->sections as $section)
                <div class="page-section mb-5" id="section-{{ $section->id }}">
                    <h2 class="section-title">{{ $section->templateSection->name }}</h2>
                    
                    <div class="section-content">
                        {!! $section->content !!}
                    </div>
                    
                    @if($section->widgets->isNotEmpty())
                        <div class="section-widgets mt-4">
                            @foreach($section->widgets as $widget)
                                <div class="widget widget-{{ $widget->widgetType->slug }}">
                                    @include('front.widgets.' . $widget->widgetType->slug, ['widget' => $widget])
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="page-content">
                {!! $page->content !!}
            </div>
        @endif
    </div>
</div>
@endsection
