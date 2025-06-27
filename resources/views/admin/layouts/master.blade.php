<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Admin Dashboard') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="NPPK CMS Admin Panel" name="description" />
    <meta content="NPPK" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">

    <!-- Layout config -->
    <script src="{{ asset('assets/admin/js/layout.js') }}"></script>
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons CSS -->
    <link href="{{ asset('assets/admin/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="{{ asset('assets/admin/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Custom CSS -->
    <link href="{{ asset('assets/admin/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sortable Components CSS -->
    <link href="{{ asset('assets/admin/css/sortable-components.css') }}" rel="stylesheet" type="text/css" />
    
    @yield('css')
    @stack('styles')
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        
        @include('admin.partials.header')

        <!-- Vertical Menu -->
        @include('admin.partials.sidebar')
        <!-- End Vertical Menu -->

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    
                    <!-- Breadcrumb Navigation -->
                    @include('admin.partials.breadcrumb')
                    <!-- End Breadcrumb Navigation -->

                    <!-- Alert Messages -->
                    @include('admin.partials.alerts')
                    <!-- End Alert Messages -->

                    <!-- Page Content -->
                    @yield('content')
                    <!-- End Page Content -->
                </div>
                <!-- End Container-fluid -->
            </div>
            <!-- End Page-content -->

            @include('admin.partials.footer')
        </div>
        <!-- End main content -->
    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/feather-icons/feather.min.js') }}"></script>
    
    <!-- App js -->
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>

    <script>
        // Initialize sidebar collapse functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get the necessary elements
            const verticalHover = document.getElementById('vertical-hover');
            const hamburgerIcon = document.getElementById('topnav-hamburger-icon');
            const body = document.body;
            const verticalOverlay = document.querySelector('.vertical-overlay');

            // Function to handle sidebar collapse
            function toggleSidebar() {
                body.classList.toggle('sidebar-enable');
                if (verticalOverlay) {
                    verticalOverlay.classList.toggle('show');
                }
            }

            // Function to handle vertical hover
            function toggleVerticalHover() {
                body.classList.toggle('vertical-sidebar-enable');
            }

            // Add click event listeners
            if (verticalHover) {
                verticalHover.addEventListener('click', toggleVerticalHover);
            }

            if (hamburgerIcon) {
                hamburgerIcon.addEventListener('click', toggleSidebar);
            }

            // Close sidebar when clicking overlay
            if (verticalOverlay) {
                verticalOverlay.addEventListener('click', function() {
                    body.classList.remove('sidebar-enable');
                    verticalOverlay.classList.remove('show');
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    body.classList.remove('sidebar-enable');
                    if (verticalOverlay) {
                        verticalOverlay.classList.remove('show');
                    }
                }
            });
        });
    </script>

     <!-- Scripts section -->
      <!-- For scripts using section @section('js') -->
     @yield('js')        

     <!-- Sortable.js Library -->
     <script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>
     <!-- Sortable Components Manager -->
     <script src="{{ asset('assets/admin/js/sortable-manager.js') }}"></script>

 <!-- For scripts using push @push('scripts') -->
    @stack('scripts')  
</body>
</html>