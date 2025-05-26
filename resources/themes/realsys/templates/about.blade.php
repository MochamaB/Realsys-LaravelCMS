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
                    <!-- Content -->
                    @if($page->hasSection('content'))
                        {!! $page->renderSection('content') !!}
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Team Section -->
    @if($page->hasSection('team'))
        {!! $page->renderSection('team') !!}
    @endif

    <!-- Footer -->
    @if($page->hasSection('footer'))
        {!! $page->renderSection('footer') !!}
    @endif
@endsection
