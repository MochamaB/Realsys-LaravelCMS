<?php

return [
    'modules' => [
        [
            'name' => 'CMS', // Main module name
            'submodules' => [
                [
                    'name' => 'Dashboard',
                    'icon' => 'ri-dashboard-2-line',
                    'route' => 'admin.dashboard',
                    'default_active' => false, // No children to set as default active
                    'show_children' => false, // No children to show
                ],
                [
                    'name' => 'Themes',
                    'icon' => 'ri-palette-line',
                    'route_prefix' => 'admin.themes', // Parent route prefix for auto-detection
                    'default_active' => 'admin.themes.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'All Themes',
                            'route' => 'admin.themes.index',
                        ],
                        [
                            'name' => 'Install Theme',
                            'route' => 'admin.themes.create',
                        ],
                    ],
                ],
                [
                    'name' => 'Templates',
                    'icon' => 'ri-layout-masonry-line',
                    'route_prefix' => 'admin.templates', // Parent route prefix for auto-detection
                    'default_active' => 'admin.templates.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'All Templates',
                            'route' => 'admin.templates.index',
                        ],
                        [
                            'name' => 'Create Template',
                            'route' => 'admin.templates.create',
                        ],
                    ],
                ],
                [
                    'name' => 'Pages',
                    'icon' => 'ri-pages-line',
                    'route_prefix' => 'admin.pages', // Parent route prefix for auto-detection
                    'default_active' => 'admin.pages.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'All Pages',
                            'route' => 'admin.pages.index',
                        ],
                        [
                            'name' => 'Create Page',
                            'route' => 'admin.pages.create',
                        ],
                    ],
                ],
                [
                    'name' => 'Menus',
                    'icon' => 'ri-menu-line',
                    'route_prefix' => 'admin.menus', // Parent route prefix for auto-detection
                    'default_active' => 'admin.menus.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'All Menus',
                            'route' => 'admin.menus.index',
                        ],
                        [
                            'name' => 'Create Menu',
                            'route' => 'admin.menus.create',
                        ],
                    ],
                ],
                [
                    'name' => 'Widgets',
                    'icon' => 'ri-layout-grid-line',
                    'route_prefix' => 'admin.widgets',
                    'default_active' => 'admin.widgets.index',
                    'show_children' => true,
                    'items' => [
                        [
                            'name' => 'All Widgets',
                            'route' => 'admin.widgets.index',
                        ],
                        [
                            'name' => 'Create Widget',
                            'route' => 'admin.widgets.create',
                        ],
                    ],
                ],
                [
                    'name' => 'Media',
                    'icon' => 'ri-image-line',
                    'route_prefix' => 'admin.media', // Parent route prefix for auto-detection
                    'default_active' => 'admin.media.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'Media Library',
                            'route' => 'admin.media.index',
                        ],
                        [
                            'name' => 'Upload Media',
                            'route' => 'admin.media.create',
                        ],
                    ],
                ],
                [
                    'name' => 'Content',
                    'icon' => 'ri-database-2-line',
                    'route_prefix' => 'admin.content', // Parent route prefix for auto-detection
                    'default_active' => 'admin.content-types.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'Content Types',
                            'route' => 'admin.content-types.index',
                        ],
                        [
                            'name' => 'All Content Items',
                            'route' => 'admin.content-items.all',
                        ],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Settings',
            'icon' => 'ri-more-fill',
            'submodules' => [
                [
                    'name' => 'Users',
                    'icon' => 'ri-user-line',
                    'route_prefix' => 'admin.users', // Parent route prefix for auto-detection
                    'default_active' => 'admin.users.index', // Default active submenu when in this section
                    'show_children' => true, // Whether to show the submenu
                    'items' => [
                        [
                            'name' => 'All Users',
                            'route' => 'admin.users.index',
                        ],
                        [
                            'name' => 'Create User',
                            'route' => 'admin.users.create',
                        ],
                        [
                            'name' => 'Roles',
                            'route' => 'admin.roles.index',
                        ],
                    ],
                ],
                [
                    'name' => 'General Settings',
                    'icon' => 'ri-settings-2-line',
                    'route' => 'admin.settings.index',
                    'default_active' => false,
                    'show_children' => false,
                ],
            ],
        ],
    ],
];