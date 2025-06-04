@extends('theme::layouts.theme')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Welcome to Miata</h1>
                        <p>A modern and professional theme for your business.</p>
                        <div class="hero-buttons">
                            <a href="#" class="btn btn-primary">Get Started</a>
                            <a href="#" class="btn btn-outline-secondary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="{{ theme_asset('assets/images/hero-image.png') }}" alt="Hero Image">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header text-center">
                <h2>Our Features</h2>
                <p>Discover what makes us different</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h3>Responsive Design</h3>
                        <p>Our themes look great on any device, ensuring a seamless experience for all users.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3>Clean Code</h3>
                        <p>We write clean, efficient code that's easy to understand and maintain.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Customer Support</h3>
                        <p>Our dedicated team is always ready to help with any questions or issues.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection