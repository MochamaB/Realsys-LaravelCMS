 <!-- ========== App Menu ========== -->
 <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                
                <!-- Light Logo-->
                <a href="{{ route('dashboard') }}" class="logo logo">
                    <span class="logo-sm">
                            <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="50">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/admin/images/logo.png') }}" alt="" height="90">
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
                                <li class="menu-title">
                                    @if(isset($menu['icon']))
                                        <i class="{{ $menu['icon'] }}"></i>
                                    @endif
                                    <span data-key="t-menu">{{ $menu['title'] }}</span>
                                </li>
                                @foreach($menu['children'] as $child)
                                    @if(!$child['permission'] || auth()->user()->can($child['permission']))
                                        @php
                                            $currentRoute = request()->route()->getName();
                                            $isActive = false;
                                            
                                            // Check if route matches exactly or starts with a prefix (if route_prefix exists)
                                            if (isset($child['route_prefix'])) {
                                                $isActive = str_starts_with($currentRoute, $child['route_prefix']);
                                            } else {
                                                $isActive = $currentRoute === $child['route'];
                                            }
                                        @endphp
                                        
                                        <li class="nav-item">
                                            <a class="nav-link menu-link @if($isActive) active @endif" href="{{ route($child['route']) }}">
                                                <i class="{{ $child['icon'] ?? $menu['icon'] }}"></i>
                                                <span data-key="t-{{ Str::slug($child['title']) }}">{{ $child['title'] }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @else
                                @if(!$menu['permission'] || auth()->user()->can($menu['permission']))
                                    @php
                                        $currentRoute = request()->route()->getName();
                                        $isActive = false;
                                        
                                        if (isset($menu['route_prefix'])) {
                                            $isActive = str_starts_with($currentRoute, $menu['route_prefix']);
                                        } else {
                                            $isActive = $currentRoute === $menu['route'];
                                        }
                                    @endphp
                                    
                                    <li class="nav-item">
                                        <a class="nav-link menu-link @if($isActive) active @endif" href="{{ route($menu['route']) }}">
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