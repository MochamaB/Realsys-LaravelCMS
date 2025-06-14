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

            @if(session('admin_as_user'))
            <div class="admin-view-indicator">
                <span class="badge bg-warning">Viewing as User</span>
                <a href="{{ route('switch.to.admin') }}" class="btn btn-sm btn-primary">
                    Switch to Admin View
                </a>
            </div>
            @endif
            
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