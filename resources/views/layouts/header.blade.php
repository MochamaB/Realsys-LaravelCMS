<header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        

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