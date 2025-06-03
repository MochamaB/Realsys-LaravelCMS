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
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
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
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('admin.dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarThemes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarThemes">
                        <i class="ri-palette-line"></i> <span data-key="t-themes">Themes</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarThemes">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.themes.index') }}" class="nav-link" data-key="t-all-themes">All Themes</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.themes.create') }}" class="nav-link" data-key="t-install-theme">Install Theme</a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTemplates" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTemplates">
                        <i class="ri-layout-masonry-line"></i> <span data-key="t-templates">Templates</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarTemplates">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.templates.index') }}" class="nav-link" data-key="t-all-templates">All Templates</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.templates.create') }}" class="nav-link" data-key="t-create-template">Create Template</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarPages" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPages">
                        <i class="ri-pages-line"></i> <span data-key="t-pages">Pages</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPages">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.pages.index') }}" class="nav-link" data-key="t-all-pages">All Pages</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.pages.create') }}" class="nav-link" data-key="t-create-page">Create Page</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarWidgets" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarWidgets">
                        <i class="ri-layout-grid-line"></i> <span data-key="t-widgets">Widgets</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarWidgets">
                        <ul class="nav nav-sm flex-column">
                            
                            <li class="nav-item">
                                <a href="{{ route('admin.widgets.index') }}" class="nav-link" data-key="t-all-widgets">
                                     Widgets
                                </a>
                            </li>
                           
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarMedia" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMedia">
                        <i class="ri-image-line"></i> <span data-key="t-media">Media</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarMedia">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.media.index') }}" class="nav-link" data-key="t-media-library">Media Library</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.media.create') }}" class="nav-link" data-key="t-upload-media">Upload Media</a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarMenus" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMenus">
                        <i class="ri-menu-line"></i> <span data-key="t-menus">Menus</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarMenus">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.menus.index') }}" class="nav-link" data-key="t-all-menus">All Menus</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.menus.create') }}" class="nav-link" data-key="t-create-menu">Create Menu</a>
                            </li>
                        </ul>
                    </div>
                </li>


                <!-- Content Management Section -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarContentManagement" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarContentManagement">
                        <i class="ri-database-2-line"></i>
                        <span data-key="t-content-management">Content Management</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarContentManagement">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.content-types.index') }}" class="nav-link" data-key="t-content-types">Content Types</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.content-items.all') }}" class="nav-link" data-key="t-content-items">All Content Items</a>
                            </li>
                        </ul>
                    </div>
                </li>
                

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-settings">Settings</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsers">
                        <i class="ri-user-line"></i> <span data-key="t-users">Users</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link" data-key="t-all-users">All Users</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.create') }}" class="nav-link" data-key="t-create-user">Create User</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}" class="nav-link" data-key="t-roles">Roles</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('admin.settings.index') }}">
                        <i class="ri-settings-2-line"></i> <span data-key="t-settings">General Settings</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<div class="vertical-overlay"></div>
