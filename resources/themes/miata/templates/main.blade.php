@extends('theme::layouts.master')

@section('content')
    {{-- 
        REALSYS CMS TEMPLATE WITH DYNAMIC SECTIONS
        =========================================
        This template demonstrates two approaches for rendering sections:
        1. Dynamic approach: Rendering all sections automatically
        2. Explicit approach: Manually placing specific sections
    --}}

    {{-- APPROACH 1: AUTOMATIC SECTION RENDERING --}}
    <div class="dynamic-sections-container">
        {{-- This will automatically render all sections in the database for this template --}}
        @renderAllSections
    </div>

    {{-- APPROACH 2: EXPLICIT SECTION PLACEMENT --}}
    <div class="explicit-sections-container">
        {{-- Hero Section (only rendered if it exists) --}}
        @sectionExists('hero')
            <div class="hero-wrapper">
                @renderSection('hero')
            </div>
        @endsectionExists

        <div class="content-wrapper">
            <div class="container">
                <div class="row">
                    {{-- Main Content Section --}}
                    <div class="col-lg-8">
                        {{-- Content Section (always displays its wrapper even if empty) --}}
                        <div class="main-content">
                            @renderSection('content')
                        </div>
                    </div>

                    {{-- Sidebar Section --}}
                    <div class="col-lg-4">
                        <div class="sidebar">
                            @renderSection('sidebar')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Features Section --}}
        @sectionExists('features')
            <div class="features-wrapper py-5 bg-light">
                <div class="container">
                    @renderSection('features')
                </div>
            </div>
        @endsectionExists

        {{-- Testimonials Section --}}
        @sectionExists('testimonials')
            <div class="testimonials-wrapper py-5">
                <div class="container">
                    @renderSection('testimonials')
                </div>
            </div>
        @endsectionExists
    </div>
@endsection
