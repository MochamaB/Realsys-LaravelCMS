@extends('themes.default.layouts.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    @if($page->hasSection('header'))
        <div class="page-header mb-4">
            {!! $page->renderSection('header') !!}
        </div>
    @endif

    <!-- Content Section -->
    @if($page->hasSection('content'))
        <div class="page-content">
            {!! $page->renderSection('content') !!}
        </div>
    @endif

    <!-- Footer Section -->
    @if($page->hasSection('footer'))
        <div class="page-footer mt-4">
            {!! $page->renderSection('footer') !!}
        </div>
    @endif
</div>
@endsection
