@extends('theme::layouts.theme')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="content-area">
                        @if(isset($sections) && count($sections) > 0)
                            @foreach($sections as $section)
                                <div class="content-section" id="section-{{ $section->id }}">
                                    {!! $section->content !!}
                                </div>
                            @endforeach
                        @else
                            <p>No content has been added to this page yet.</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar">
                        <div class="widget">
                            <h4 class="widget-title">Categories</h4>
                            <ul class="widget-list">
                                <li><a href="#">Category 1</a></li>
                                <li><a href="#">Category 2</a></li>
                                <li><a href="#">Category 3</a></li>
                            </ul>
                        </div>
                        
                        <div class="widget">
                            <h4 class="widget-title">Recent Posts</h4>
                            <ul class="recent-posts">
                                <li>
                                    <a href="#">
                                        <div class="post-image">
                                            <img src="{{ theme_asset('assets/images/recent-post-1.jpg') }}" alt="Recent Post">
                                        </div>
                                        <div class="post-content">
                                            <h5>Sample Post Title 1</h5>
                                            <span>June 4, 2025</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <div class="post-image">
                                            <img src="{{ theme_asset('assets/images/recent-post-2.jpg') }}" alt="Recent Post">
                                        </div>
                                        <div class="post-content">
                                            <h5>Sample Post Title 2</h5>
                                            <span>June 3, 2025</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection