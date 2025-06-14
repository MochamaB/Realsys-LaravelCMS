<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">

    <!-- Layout config Js -->
    <script src="{{ asset('assets/admin/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/admin/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/admin/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/admin/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    @stack('styles')
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/admin/images/logo-dark.png') }}" alt="" height="17">
                                </span>
                            </a>

                            <a href="{{ route('dashboard') }}" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/admin/images/logo-light.png') }}" alt="" height="17">
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>

                    <div class="d-flex align-items-center">
                        @if(session('admin_as_user'))
                            <div class="me-3">
                                <span class="badge bg-warning">Viewing as User</span>
                                <a href="{{ route('switch.to.admin') }}" class="btn btn-sm btn-primary ms-2">
                                    <i class="ri-admin-line me-1"></i> Switch to Admin
                                </a>
                            </div>
                        @endif

                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    @if(auth()->user()->getFirstMediaUrl('profile_photos'))
                                        <img class="rounded-circle header-profile-user" src="{{ auth()->user()->getFirstMediaUrl('profile_photos') }}" alt="Header Avatar">
                                    @else
                                        <span class="avatar-title rounded-circle bg-primary">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </span>
                                    @endif
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ auth()->user()->name }}</span>
                                        <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">{{ auth()->user()->profile->profileType->name ?? 'Member' }}</span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <h6 class="dropdown-header">Welcome {{ auth()->user()->name }}!</h6>
                                <a class="dropdown-item" href="{{ route('user.profile') }}">
                                    <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">My Profile</span>
                                </a>
                                <a class="dropdown-item" href="{{ route('user.settings.account') }}">
                                    <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">Settings</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                        <span class="align-middle" data-key="t-logout">Logout</span>
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/admin/images/logo-dark.png') }}" alt="" height="17">
                    </span>
                </a>
                <!-- Light Logo-->
                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/admin/images/logo-light.png') }}" alt="" height="17">
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
                        @foreach(config('usersidebar.menu') as $menu)
                            @if(isset($menu['children']))
                                <li class="menu-title"><span data-key="t-menu">{{ $menu['title'] }}</span></li>
                                @foreach($menu['children'] as $child)
                                    @if(!$child['permission'] || auth()->user()->can($child['permission']))
                                        <li class="nav-item">
                                            <a class="nav-link menu-link" href="{{ route($child['route']) }}">
                                                <i class="{{ $menu['icon'] }}"></i>
                                                <span data-key="t-{{ Str::slug($child['title']) }}">{{ $child['title'] }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @else
                                @if(!$menu['permission'] || auth()->user()->can($menu['permission']))
                                    <li class="nav-item">
                                        <a class="nav-link menu-link" href="{{ route($menu['route']) }}">
                                            <i class="{{ $menu['icon'] }}"></i>
                                            <span data-key="t-{{ Str::slug($menu['title']) }}">{{ $menu['title'] }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © {{ config('app.name') }}.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Design & Develop by <a href="#!" class="text-decoration-underline">Themesbrand</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/admin/js/plugins.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>
    @stack('scripts')
</body>

</html> 