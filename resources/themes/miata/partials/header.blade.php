<style>
     /* Button styles - Remove blue, use red or dark gray */
     .btn-primary {
                background-color: #dc3545;
                border-color: #dc3545;
                color: #fff;
            }
            
            .btn-primary:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }
            
            .btn-secondary {
                background-color: #343a40;
                border-color: #343a40;
                color: #fff;
            }
            
            .btn-secondary:hover {
                background-color: #23272b;
                border-color: #1d2124;
            }
            
            .btn-outline-primary {
                color: #dc3545;
                border-color: #dc3545;
                background-color: transparent;
            }
            
            .btn-outline-primary:hover {
                background-color: #dc3545;
                border-color: #dc3545;
                color: #fff;
            }
            
            .btn-outline-secondary {
                color: #343a40;
                border-color: #343a40;
                background-color: transparent;
            }
            
            .btn-outline-secondary:hover {
                background-color: #343a40;
                border-color: #343a40;
                color: #fff;
            }
            
            /* Remove all shadows and unnecessary borders */
            .btn {
                box-shadow: none !important;
            }
            
            .btn:focus,
            .btn:active {
                box-shadow: none !important;
            }
</style>
<header class="header-area">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="logo floatleft">
                    <a href="{{ route('home') }}">
                    <img src="{{ theme_asset('img/logo/logo.png') }}" alt="{{ config('app.name') }}" style="width: 250px; height: auto;" class="logo-img">
                    </a>
                </div>
                <div class="header-search floatright">
                                <div class="header-button floatright">
                                    <a href="{{ route('usermanagement.register.wizard') }}" class="btn-primary">Join NPPK</a>
                                    <a href="#" class="btn-secondary">Donate Now</a>
                                </div>
                </div>
                <div class="main-menu floatright">
                <x-theme-navigation :menu="$menus['header'] ?? null" location="header" />
                </div>
            </div>
        </div>
    </div>
</header>
<!-- mobile-menu-area start -->
<div class="mobile-menu-area d-block d-lg-none">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="mobile-menu">
                    <x-theme-navigation location="header" :page-id="$page->id ?? null" :template-id="$template->id ?? null" />
                </div>
            </div>
        </div>
    </div>
</div>
<!-- mobile-menu-area end -->