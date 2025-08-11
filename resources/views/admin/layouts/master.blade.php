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
    
    <!-- Page Loader CSS -->
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #f8f9fa;
            z-index: 9999;
            overflow: hidden;
        }
        
        .page-loader .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997, #17a2b8);
            background-size: 200% 100%;
            animation: loading 2s ease-in-out infinite;
            border-radius: 0;
            transition: width 0.3s ease;
        }
        
        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
        
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
    </style>
    
    @yield('css')
    @stack('styles')
</head>

<body>
    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    
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

    <!-- Page Loader JavaScript -->
    <script>
        // Page Loader functionality
        document.addEventListener('DOMContentLoaded', function() {
            const pageLoader = document.getElementById('pageLoader');
            const progressBar = pageLoader.querySelector('.progress-bar');
            
            // Simulate loading progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                }
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', Math.round(progress));
            }, 100);
            
            // Hide loader when page is fully loaded
            window.addEventListener('load', function() {
                clearInterval(interval);
                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', 100);
                
                setTimeout(() => {
                    pageLoader.classList.add('hidden');
                }, 500);
            });
            
            // Fallback: Hide loader after 3 seconds if load event doesn't fire
            setTimeout(() => {
                if (!pageLoader.classList.contains('hidden')) {
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', 100);
                    pageLoader.classList.add('hidden');
                }
            }, 3000);
        });
    </script>

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

            // Universal clickable row functionality
            // This allows any table row with class 'clickable-row' and data-href attribute to be clickable
            document.addEventListener('click', function(e) {
                // Check if the clicked element is inside a clickable row
                const clickableRow = e.target.closest('.clickable-row');
                
                if (clickableRow && !e.target.closest('button, a, input, select, textarea, .btn-group, .form-check, .form-switch')) {
                    // Don't trigger row click if clicking on interactive elements
                    const href = clickableRow.getAttribute('data-href');
                    if (href) {
                        window.location.href = href;
                    }
                }
            });
            
            // Add hover effect for clickable rows
            document.querySelectorAll('.clickable-row').forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.transition = 'background-color 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>

     <!-- Scripts section -->
      <!-- Media Picker JS -->
      <script src="{{ asset('assets/admin/js/media-picker.js') }}"></script>
      <!-- Universal Preview Manager JS (Phase 4.2) -->
      <script src="{{ asset('assets/admin/js/universal-preview-manager.js') }}"></script>
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