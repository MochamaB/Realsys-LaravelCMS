@extends('themes.default.layouts.master')

@templateSection('content')
<div class="container py-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
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
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            @if($page->hasSection('sidebar'))
                <div class="sidebar">
                    {!! $page->renderSection('sidebar') !!}
                </div>
            @endif
        </div>
    </div>

    <!-- Footer Section -->
    @if($page->hasSection('footer'))
        <div class="page-footer mt-4">
            {!! $page->renderSection('footer') !!}
        </div>
    @endif
</div>
@endtemplateSection
