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
    <!-- Media Picker CSS -->
    <link href="{{ asset('assets/admin/css/media-picker.css') }}" rel="stylesheet" type="text/css" />
    @yield('css')
    @stack('styles')

    <!-- Designer Layout Specific CSS -->
    <style>
        /* Designer Layout - Vertical Hovered Sidebar */
        body {
            overflow-x: hidden;
        }
        #page-topbar {
        left: 0 !important;
        }
        /* Sidebar collapsed state */
        .app-menu {
            width: 70px !important;
            transition: width 0.3s ease;
        }

        /* Sidebar expanded on hover */
        .app-menu:hover {
            width: 260px !important;
        }

        /* Logo adjustments for collapsed state */
        .navbar-brand-box {
            padding: 0 0.5rem;
            text-align: center;
        }

        /* Ensure logo-sm is always visible in collapsed state */
        .logo-sm {
            display: block !important;
        }

        .logo-lg {
            display: none !important;
        }

        /* Show full logo on hover */
        .app-menu:hover .logo-lg {
            display: block !important;
        }

        .app-menu:hover .logo-sm {
            display: none !important;
        }

        /* Navigation text hidden in collapsed state */
        .navbar-nav .nav-link span,
        .navbar-nav .menu-title span {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Show navigation text on hover */
        .app-menu:hover .navbar-nav .nav-link span,
        .app-menu:hover .navbar-nav .menu-title span {
            display: inline;
            opacity: 1;
        }

        /* Menu title adjustments */
        .menu-title {
            padding: 0.5rem 1rem;
            text-align: center;
        }

        .app-menu:hover .menu-title {
            text-align: left;
        }

        /* Nav link adjustments */
        .navbar-nav .nav-link {
            padding: 0.75rem 1rem;
            text-align: center;
            white-space: nowrap;
        }

        .app-menu:hover .navbar-nav .nav-link {
            text-align: left;
        }

        /* Icon centering in collapsed state */
        .navbar-nav .nav-link i {
            font-size: 1.25rem;
            width: auto;
            margin-right: 0;
        }

        .app-menu:hover .navbar-nav .nav-link i {
            margin-right: 0.75rem;
        }

        /* Hide menu dropdowns in collapsed state */
        .app-menu .menu-dropdown {
            display: none !important;
        }

        /* Show only active/expanded menu dropdowns on hover */
        .app-menu:hover .menu-dropdown.show {
            display: block !important;
        }

        /* Hide menu items in collapsed state */
        .app-menu .nav-item .nav-link[data-bs-toggle="collapse"] {
            pointer-events: none;
        }

        .app-menu:hover .nav-item .nav-link[data-bs-toggle="collapse"] {
            pointer-events: auto;
        }

        /* Main content adjustment */
        .main-content {
            margin-left: 70px;
            transition: margin-left 0.3s ease;
        }

        .app-menu:hover + .main-content {
            margin-left: 260px;
        }

        /* Header adjustment */
        .navbar-header {
            margin-left: 70px;
            transition: margin-left 0.3s ease;
        }

        .app-menu:hover ~ .navbar-header {
            margin-left: 260px;
        }

        /* Mobile responsiveness */
        @media (max-width: 991.98px) {
            .app-menu {
                transform: translateX(-100%);
                width: 260px !important;
            }

            .app-menu.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .navbar-header {
                margin-left: 0;
            }

            /* Show dropdowns on mobile */
            .app-menu .menu-dropdown {
                display: block !important;
            }

            .app-menu .nav-item .nav-link[data-bs-toggle="collapse"] {
                pointer-events: auto;
            }
        }

        /* Ensure proper z-index */
        .app-menu {
            z-index: 1000;
        }

        /* Smooth transitions */
        * {
            transition: all 0.3s ease;
        }

        /* Remove any modal overlay on page load */
        .vertical-overlay {
            display: none !important;
        }

        /* Ensure no modal backdrop on load */
        .modal-backdrop {
            display: none !important;
        }

        /* Prevent body scroll lock on load */
        body {
            overflow: auto !important;
        }
    </style>
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
                
                    
                    <!-- Breadcrumb Navigation -->
                    @include('admin.partials.breadcrumb')
                    <!-- End Breadcrumb Navigation -->

                    <!-- Alert Messages -->
                    @include('admin.partials.alerts')
                    <!-- End Alert Messages -->

                    <!-- Page Content -->
                    @yield('content')
                    <!-- End Page Content -->
                
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
        // Initialize designer layout functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get the necessary elements
            const hamburgerIcon = document.getElementById('topnav-hamburger-icon');
            const body = document.body;
            const verticalOverlay = document.querySelector('.vertical-overlay');
            const appMenu = document.querySelector('.app-menu');

            // Remove any modal overlays on page load
            function removeModalOverlays() {
                // Remove any existing modal backdrops
                const modalBackdrops = document.querySelectorAll('.modal-backdrop');
                modalBackdrops.forEach(backdrop => {
                    backdrop.remove();
                });

                // Remove body classes that might lock scrolling
                body.classList.remove('modal-open');
                body.style.overflow = 'auto';
                body.style.paddingRight = '';

                // Hide vertical overlay
                if (verticalOverlay) {
                    verticalOverlay.style.display = 'none';
                }
            }

            // Function to handle mobile sidebar toggle
            function toggleMobileSidebar() {
                if (appMenu) {
                    appMenu.classList.toggle('show');
                }
                if (verticalOverlay) {
                    verticalOverlay.classList.toggle('show');
                }
            }

            // Add click event listeners
            if (hamburgerIcon) {
                hamburgerIcon.addEventListener('click', toggleMobileSidebar);
            }

            // Close sidebar when clicking overlay
            if (verticalOverlay) {
                verticalOverlay.addEventListener('click', function() {
                    if (appMenu) {
                        appMenu.classList.remove('show');
                    }
                    verticalOverlay.classList.remove('show');
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    if (appMenu) {
                        appMenu.classList.remove('show');
                    }
                    if (verticalOverlay) {
                        verticalOverlay.classList.remove('show');
                    }
                }
            });

            // Initialize the layout as vertical hovered by default
            body.classList.add('vertical-sidebar-enable');

            // Remove modal overlays on page load
            removeModalOverlays();

            // Also remove overlays after a short delay to ensure everything is loaded
            setTimeout(removeModalOverlays, 100);
            setTimeout(removeModalOverlays, 500);
        });
    </script>

     <!-- Scripts section -->
      <!-- Media Picker JS -->
      <script src="{{ asset('assets/admin/js/media-picker.js') }}"></script>
      <!-- For scripts using section @section('js') -->
     @yield('js')        

     <!-- Sortable.js Library -->
     <script src="{{ asset('assets/admin/libs/sortablejs/Sortable.min.js') }}"></script>
     <!-- Sortable Components Manager -->
     
     <!-- Include the media picker modal -->
     @include('admin.media.partials._media_picker_modal')
     <script src="{{ asset('assets/admin/js/sortable-manager.js') }}"></script>

 <!-- For scripts using push @push('scripts') -->
    @stack('scripts')  
</body>
</html>