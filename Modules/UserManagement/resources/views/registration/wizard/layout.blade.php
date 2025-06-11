<x-usermanagement::layouts.master>
    <x-slot name="title">Join Party - Registration Wizard</x-slot>

    <x-slot name="styles">
        <style>
            .registration-wizard {
                position: relative;
                margin: 30px 0;
            }
            
            .wizard-steps {
                display: flex;
                flex-direction: column;
                position: relative;
                height: 100vh;
                padding: 40px 30px;
                border-right: 2px solid #e9ecef;
                justify-content: center;

                
            }
            
            .wizard-step {
                display: flex;
                align-items: center;
                margin-bottom: 60px;
                padding: 15px 0;
                position: relative;

            }
            
            .wizard-step-circle {
                width: 60px;
                height: 60px;
                line-height: 60px;
                border-radius: 50%;
                background-color: #e9ecef;
                color: #6c757d;
                text-align: center;
                font-weight: 700;
                font-size: 24px;
                position: relative;
                z-index: 2;
                transition: all 0.3s ease;
                margin-right: 25px;

            }
            
            .wizard-step.active .wizard-step-circle {
                background-color: #dc3545;
                color: #fff;
            }
            
            .wizard-step.completed .wizard-step-circle {
                background-color: #343a40;

                color: #fff;
            }
            
            .wizard-step-text {
                font-size: 18px;
                color: #6c757d;
                font-weight: 500;
            }
            
            .wizard-step.active .wizard-step-text,
            .wizard-step.completed .wizard-step-text {
                color: #343a40;
                font-weight: 700;
                font-size: 20px;

            }
            
            .wizard-progress {
                position: absolute;
                left: 59px; /* Center of the circle (30px padding + 30px radius + 9px adjustment) */
                top: 70px; /* Start from middle of first circle */
                width: 4px;
                background-color: #e9ecef;
                height: calc(100% - 190px); /* End at middle of last circle */
                z-index: 1;
                border-radius: 2px;

            }
            
            .wizard-progress-bar {
                width: 100%;
                background-color: #343a40;
                height: 0%;
                transition: height 0.5s ease;
                border-radius: 2px;

            }
            
            .wizard-content {
                background-color: #fff;
                padding: 0px 40px;
                border-radius: 0;
                height: 100%;
                min-height: 100vh;

            }
            
            .wizard-content .tab-pane {
                display: none;
            }
            
            .wizard-content .tab-pane.active {
                display: block;
            }
            
            .wizard-footer {
                display: flex;
                justify-content: space-between;
                margin-top: 40px;
                padding-top: 30px;
                border-top: 1px solid #e9ecef;

            }
            
           
            
            /* Form styles */
            .form-group {
                margin-bottom: 20px;
            }
            
            label {
                font-weight: 600;
                color: #343a40;
                margin-bottom: 8px;
                display: block;
            }
            
            .form-text {
                color: #6c757d;
                font-size: 12px;
                margin-top: 5px;
            }
                        /* Button styles - Remove blue, use red or dark gray */
                        .btn-primary {
                background-color: #dc3545;
                border-color: #dc3545;
                color: #fff;
            }
            
            .btn-primary:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }
            
            .btn-secondary {
                background-color: #343a40;
                border-color: #343a40;
                color: #fff;
            }
            
            .btn-secondary:hover {
                background-color: #23272b;
                border-color: #1d2124;
            }
            
            .btn-outline-primary {
                color: #dc3545;
                border-color: #dc3545;
                background-color: transparent;
            }
            
            .btn-outline-primary:hover {
                background-color: #dc3545;
                border-color: #dc3545;
                color: #fff;
            }
            
            .btn-outline-secondary {
                color: #343a40;
                border-color: #343a40;
                background-color: transparent;
            }
            
            .btn-outline-secondary:hover {
                background-color: #343a40;
                border-color: #343a40;
                color: #fff;
            }
            
            /* Remove all shadows and unnecessary borders */
            .btn {
                box-shadow: none !important;
            }
            
            .btn:focus,
            .btn:active {
                box-shadow: none !important;
            }

            
            /* Card styles for profile types */
            .profile-type-card {
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .profile-type-card:hover {
                border-color: #dc3545;
                box-shadow: 0 5px 15px rgba(220, 53, 69, 0.1);
            }
            
            .profile-type-card.selected {
                border-color: #dc3545;
                background-color: rgba(220, 53, 69, 0.05);
            }
            
            .profile-type-card .card-title {
                font-weight: 600;
                color: #343a40;
                margin-bottom: 10px;
            }
            
            .profile-type-card .card-text {
                color: #6c757d;
                font-size: 14px;
            }
            
            .profile-type-card .card-icon {
                font-size: 32px;
                color: #dc3545;
                margin-bottom: 15px;
            }
             /* Alert styles - make them more prominent */
             .alert {
                border-radius: 8px;
                padding: 15px 20px;
                margin-bottom: 25px;
                border: none;
                font-weight: 500;
            }
            
            .alert-danger {
                background-color: #f8d7da;
                color: #721c24;
                border-left: 4px solid #dc3545;
            }
            
            .alert-success {
                background-color: #d1e7dd;
                color: #0f5132;
                border-left: 4px solid #198754;
            }
            
            .alert-dismissible .btn-close {
                padding: 8px 12px;
            }
              /* Form inputs */
              .form-control {
                border-radius: 6px;
                border: 1px solid #e9ecef;
                padding: 12px 15px;
                font-size: 15px;
                background-color: #fff;
            }
            
            .form-control:focus {
                border-color: #dc3545;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            }
            
            /* Select dropdown styling with arrow */
            select.form-control {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 16px 12px;
                padding-right: 45px;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
            }
            
            select.form-control:focus {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23dc3545' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
            }
            
            /* Invalid form styling */
            .form-control.is-invalid {
                border-color: #dc3545;
            }
            
            .invalid-feedback {
                display: block;
                color: #dc3545;
                font-size: 14px;
                margin-top: 5px;
                font-weight: 500;
            }
            
            /* Required field asterisk */
            .text-danger {
                color: #dc3545 !important;
            }
        </style>
    </x-slot>

    <div class="container my-5">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="text-center">Join Our Party</h2>
                <p class="text-center text-muted">Complete the registration form to become a member</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <!-- Wizard Steps Navigation -->
                <div class="wizard-steps">
                    <div class="wizard-progress">
                        <div class="wizard-progress-bar" id="wizard-progress-bar"></div>
                    </div>
                    <div class="wizard-step {{ request()->is('*/register/step1*') ? 'active' : (Session::get('wizard_step', 0) >= 1 ? 'completed' : '') }}">
                        <div class="wizard-step-circle">1</div>
                        <div class="wizard-step-text">Profile Type</div>
                    </div>
                    <div class="wizard-step {{ request()->is('*/register/step2*') ? 'active' : (Session::get('wizard_step', 0) >= 2 ? 'completed' : '') }}">
                        <div class="wizard-step-circle">2</div>
                        <div class="wizard-step-text">Personal Info</div>
                    </div>
                    <div class="wizard-step {{ request()->is('*/register/step3*') ? 'active' : (Session::get('wizard_step', 0) >= 3 ? 'completed' : '') }}">
                        <div class="wizard-step-circle">3</div>
                        <div class="wizard-step-text">Additional Info</div>
                    </div>
                    <div class="wizard-step {{ request()->is('*/register/step4*') ? 'active' : (Session::get('wizard_step', 0) >= 4 ? 'completed' : '') }}">
                        <div class="wizard-step-circle">4</div>
                        <div class="wizard-step-text">Geographic Info</div>
                    </div>
                    <div class="wizard-step {{ request()->is('*/register/step5*') ? 'active' : '' }}">
                        <div class="wizard-step-circle">5</div>
                        <div class="wizard-step-text">Terms & Photo</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="registration-wizard">
                    <!-- Wizard Content -->
                    <div class="wizard-content">
                        <!-- Alert Messages -->
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Wizard Form Content -->
                        @yield('wizard-content')
                    </div>
                </div>
            </div>
            
           
        </div>
    </div>

    <x-slot name="scripts">
   
        <script>
            $(document).ready(function() {
                // Auto-scroll to the form section
                $('html, body').animate({
                    scrollTop: $('.registration-wizard').offset().top - 100
                }, 800);
                
                // Calculate and update progress bar
                updateProgressBar();
                
                function updateProgressBar() {
                    // Get current active step
                    let currentStep = 1;
                    if ($('.wizard-step.active').length > 0) {
                        currentStep = $('.wizard-step').index($('.wizard-step.active')) + 1;
                    } else {
                        currentStep = {{ Session::get('wizard_step', 1) }};
                    }
                    
                    const stepsCount = $('.wizard-step').length;
                    const progressPercentage = ((currentStep - 1) / (stepsCount - 1)) * 100;
                    
                    // Update progress bar
                    $('#wizard-progress-bar').css('height', progressPercentage + '%');
                    
                    // Update completed steps
                    $('.wizard-step').each(function(index) {
                        if (index + 1 < currentStep) {
                            $(this).addClass('completed');
                        }
                    });
                }
                
                // Profile Type Selection
                $('.profile-type-card').click(function() {
                    $('.profile-type-card').removeClass('selected');
                    $(this).addClass('selected');
                    $(this).find('input[type="radio"]').prop('checked', true);
                });
                
                // Fix Font Awesome icons loading (if needed)
                if (typeof FontAwesome === 'undefined') {
                    // Add Font Awesome if not loaded from theme
                    if (!$('link[href*="font-awesome"]').length) {
                        $('head').append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />');
                    }
                }
            });
        
            @stack('scripts')
    </x-slot>
</x-usermanagement::layouts.master>
