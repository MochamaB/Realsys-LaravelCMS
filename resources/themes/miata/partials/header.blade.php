
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
                <button class="mobile-menu-toggle floatright">
                    <div class="hamburger"></div>
                    <div class="hamburger"></div>
                    <div class="hamburger"></div>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Sidebar -->
<div class="mobile-sidebar">
    <button class="mobile-sidebar-close">&times;</button>
    <div class="header-button">
        <a href="{{ route('usermanagement.register.wizard') }}" class="btn-primary">Join NPPK</a>
        <a href="#" class="btn-secondary">Donate Now</a>
    </div>
    <div class="main-menu">
        <x-theme-navigation location="header" :page-id="$page->id ?? null" :template-id="$template->id ?? null" />
    </div>
</div>
<div class="mobile-sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.mobile-sidebar');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    const closeButton = document.querySelector('.mobile-sidebar-close');

    function closeMenu() {
        menuToggle.classList.remove('active');
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }

    menuToggle.addEventListener('click', function() {
        this.classList.toggle('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    closeButton.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);

    // Set active menu item for both mobile and desktop menus
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.main-menu ul li a, .mobile-sidebar .main-menu ul li a');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            item.parentElement.classList.add('active');
            // If it's in a submenu, also highlight the parent
            const parentSubmenu = item.closest('.submenu-mainmenu');
            if (parentSubmenu) {
                const parentItem = parentSubmenu.previousElementSibling;
                if (parentItem) {
                    parentItem.classList.add('active');
                }
            }
        }
    });
});
</script>