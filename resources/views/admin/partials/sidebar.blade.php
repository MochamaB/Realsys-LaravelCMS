<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/admin/images/realsyslogo.png') }}" alt="" height="67">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('admin.dashboard') }}" class="logo logo">
            <span class="logo-sm">
                <img src="{{ asset('assets/admin/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/admin/images/logo.png') }}" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                @foreach(config('adminsidebar.modules') as $module)
                    @if(!empty($module['submodules']))
                        <li class="menu-title">
                            @if(isset($module['icon']))
                                <i class="{{ $module['icon'] }}"></i>
                            @endif
                            <span data-key="t-{{ Str::slug($module['name']) }}">{{ $module['name'] }}</span>
                        </li>

                        @foreach($module['submodules'] as $submodule)
                            @php
                                // Determine if this submodule has items
                                $hasItems = isset($submodule['items']) && $submodule['show_children'] ?? false;
                                
                                // Get the current route name
                                $currentRoute = request()->route()->getName();
                                
                                // Determine active state based on route prefix
                                $isActive = false;
                                if (isset($submodule['route_prefix'])) {
                                    $isActive = str_starts_with($currentRoute, $submodule['route_prefix']);
                                } elseif (isset($submodule['route'])) {
                                    $isActive = $currentRoute === $submodule['route'];
                                }
                                
                                // Get default active submenu for highlighting
                                $defaultActive = $submodule['default_active'] ?? false;
                            @endphp
                            
                            @if($hasItems)
                                <li class="nav-item">
                                    @php
                                        // Collect all routes in this submodule for highlighting
                                        $allRoutes = collect($submodule['items'])->pluck('route')->toArray();
                                        
                                        // Add the route prefix with wildcard for parent-child detection
                                        $routePattern = $submodule['route_prefix'] . '*';
                                    @endphp
                                    <a class="nav-link menu-link @activeRoute($routePattern)" href="#sidebar{{ Str::studly($submodule['name']) }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}" aria-controls="sidebar{{ Str::studly($submodule['name']) }}">
                                        <i class="{{ $submodule['icon'] ?? 'ri-circle-line' }}"></i> <span data-key="t-{{ Str::slug($submodule['name']) }}">{{ $submodule['name'] }}</span>
                                    </a>
                                    <div class="collapse menu-dropdown @activeRouteShow($routePattern)" id="sidebar{{ Str::studly($submodule['name']) }}">
                                        <ul class="nav nav-sm flex-column">
                                            @foreach($submodule['items'] as $item)
                                                @php
                                                    // Check if this is the default item to highlight
                                                    $isDefaultActive = $defaultActive && $item['route'] === $defaultActive && !in_array($currentRoute, $allRoutes);
                                                @endphp
                                                <li class="nav-item">
                                                    <a href="{{ route($item['route']) }}" class="nav-link @activeRoute($item['route']) {{ $isDefaultActive ? 'active' : '' }}" data-key="t-{{ Str::slug($item['name']) }}">
                                                        {{ $item['name'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link menu-link @activeRoute($submodule['route'])" href="{{ route($submodule['route']) }}">
                                        <i class="{{ $submodule['icon'] ?? 'ri-circle-line' }}"></i> <span data-key="t-{{ Str::slug($submodule['name']) }}">{{ $submodule['name'] }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<div class="vertical-overlay"></div>