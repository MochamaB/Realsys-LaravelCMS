@extends('admin.layouts.master')

@section('title', 'Create User - Wizard')

@section('content')

    <div class="container-fluid">
        <!-- start page title -->
        
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Create New User</h4>
                            <a href="{{ route('admin.users.wizard.cancel') }}" class="btn btn-outline-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to cancel? All entered data will be lost.')">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 20px 25px 10px 35px;">
                        <div class="row">
                            <div class="col-lg-4">
                                <!-- Vertical Nav Steps -->
                                <div class="vertical-navs-step">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="nav flex-column custom-nav nav-pills" role="tablist" style="border-right: 1px solid #e9ebec;">
                                                <button class="nav-link {{ request()->is('*/wizard/step1*') ? 'active' : (Session::get('wizard_step', 0) >= 1 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step1" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-user-line"></i>
                                                    </span>
                                                    <span class="step-text">User Type</span>
                                                </button>
                                                <button class="nav-link {{ request()->is('*/wizard/step2*') ? 'active' : (Session::get('wizard_step', 0) >= 2 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step2" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-shield-user-line"></i>
                                                    </span>
                                                    <span class="step-text">Role Selection</span>
                                                </button>
                                                <button class="nav-link {{ request()->is('*/wizard/step3*') ? 'active' : (Session::get('wizard_step', 0) >= 3 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step3" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-user-settings-line"></i>
                                                    </span>
                                                    <span class="step-text">Personal Info</span>
                                                </button>
                                                <button class="nav-link {{ request()->is('*/wizard/step4*') ? 'active' : (Session::get('wizard_step', 0) >= 4 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step4" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-group-line"></i>
                                                    </span>
                                                    <span class="step-text">Party Membership</span>
                                                </button>
                                                <button class="nav-link {{ request()->is('*/wizard/step5*') ? 'active' : (Session::get('wizard_step', 0) >= 5 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step5" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-file-list-line"></i>
                                                    </span>
                                                    <span class="step-text">Additional Info</span>
                                                </button>
                                                <button class="nav-link {{ request()->is('*/wizard/step6*') ? 'active' : (Session::get('wizard_step', 0) >= 6 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step6" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-map-pin-line"></i>
                                                    </span>
                                                    <span class="step-text">Geographic Info</span>
                                                </button>
                                                <button class="nav-link {{ request()->is('*/wizard/step7*') ? 'active' : (Session::get('wizard_step', 0) >= 7 ? 'done' : '') }}" 
                                                        data-bs-toggle="pill" data-bs-target="#step7" type="button" role="tab">
                                                    <span class="step-title me-2">
                                                        <i class="ri-check-line"></i>
                                                    </span>
                                                    <span class="step-text">Terms & Photo</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <!-- Wizard Content -->
                                <div class="tab-content">
                                    <!-- Alert Messages -->
                                    @if(session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="ri-error-warning-line me-2"></i>
                                            {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                                    
                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="ri-check-line me-2"></i>
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
            </div>
        </div>
    </div>

@endsection

@section('css')
<style>
    .vertical-navs-step .nav-pills .nav-link {
        position: relative;
        color: #495057;
        background-color: transparent;
        border: 0;
        border-radius: 0;
        padding: 1rem 0;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .vertical-navs-step .nav-pills .nav-link:hover {
        color: #556ee6;
        background-color: transparent;
    }

    .vertical-navs-step .nav-pills .nav-link.active {
        color: #556ee6;
        background-color: transparent;
        font-weight: 600;
    }

    .vertical-navs-step .nav-pills .nav-link.done {
        color: #34c38f;
        background-color: transparent;
    }

    .vertical-navs-step .nav-pills .nav-link .step-title {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        font-size: 16px;
        margin-right: 0.75rem;
        transition: all 0.3s ease;
    }

    .vertical-navs-step .nav-pills .nav-link.active .step-title {
        background-color: #556ee6;
        color: #fff;
    }

    .vertical-navs-step .nav-pills .nav-link.done .step-title {
        background-color: #34c38f;
        color: #fff;
    }

    .vertical-navs-step .nav-pills .nav-link .step-text {
        font-size: 14px;
        font-weight: 500;
    }

    .vertical-navs-step .nav-pills .nav-link.active .step-text,
    .vertical-navs-step .nav-pills .nav-link.done .step-text {
        font-weight: 600;
    }

    /* Progress bar */
    .wizard-progress {
        position: relative;
        margin: 2rem 0;
    }

    .wizard-progress .progress {
        height: 8px;
        border-radius: 4px;
    }

    .wizard-progress .progress-bar {
        background-color: #556ee6;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .wizardbuttons {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e9ebec;
    }

    /* Form styles */
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control:focus {
        border-color: #556ee6;
        box-shadow: 0 0 0 0.2rem rgba(85, 110, 230, 0.25);
    }

    .btn-primary {
        background-color: #556ee6;
        border-color: #556ee6;
    }

    .btn-primary:hover {
        background-color: #485ec4;
        border-color: #485ec4;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    /* Card styles for user type selection */
    .user-type-card {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .user-type-card:hover {
        border-color: #556ee6;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .user-type-card.selected {
        border-color: #556ee6;
        background-color: #f8f9ff;
    }

    .user-type-card .card-icon {
        font-size: 2rem;
        color: #6c757d;
        margin-bottom: 1rem;
        text-align: center;
    }

    .user-type-card.selected .card-icon {
        color: #556ee6;
    }

    .user-type-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .user-type-card .card-text {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .vertical-navs-step .nav-pills {
            flex-direction: row;
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        .vertical-navs-step .nav-pills .nav-link {
            flex: 0 0 auto;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .vertical-navs-step .nav-pills .nav-link .step-title {
            margin-right: 0.5rem;
        }
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Update progress bar based on current step
        function updateProgress() {
            const currentStep = {{ Session::get('wizard_step', 1) }};
            const totalSteps = 7;
            const progress = (currentStep / totalSteps) * 100;
            
            $('.wizard-progress .progress-bar').css('width', progress + '%');
        }

        updateProgress();

        // Auto-check radio when card is clicked
        $('.user-type-card').click(function() {
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.user-type-card').removeClass('selected');
            $(this).addClass('selected');
        });

        // Update card selection when radio changes
        $('input[type="radio"]').change(function() {
            $('.user-type-card').removeClass('selected');
            $(this).closest('.user-type-card').addClass('selected');
        });

        // Initialize card selection on page load
        $('input[type="radio"]:checked').closest('.user-type-card').addClass('selected');
    });
</script>
@yield('step-scripts')
@endsection 