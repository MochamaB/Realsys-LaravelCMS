@extends('themes.realsys.layouts.master')

@section('content')
    <!-- Hero Header -->
    @if($page->hasSection('hero'))
        {!! $page->renderSection('hero') !!}
    @endif

    <!-- Main Content -->
    <main class="mb-4">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <!-- Contact Form -->
                    @if($page->hasSection('form'))
                        {!! $page->renderSection('form') !!}
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Map Section -->
    @if($page->hasSection('map'))
        {!! $page->renderSection('map') !!}
    @endif

    <!-- Footer -->
    @if($page->hasSection('footer'))
        {!! $page->renderSection('footer') !!}
    @endif
@endsection
