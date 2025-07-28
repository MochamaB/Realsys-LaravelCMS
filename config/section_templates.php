<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Section Templates Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains predefined section templates for the GridStack page builder.
    | Each template defines the layout structure, GridStack configuration, and
    | widget constraints for different section types.
    |
    */

    'templates' => [
        'full-width' => [
            'name' => 'Full Width',
            'icon' => 'ri-layout-masonry-line',
            'description' => 'Full width single column layout for maximum content width',
            'category' => 'layout',
            'grid_config' => [
                'column' => 12,
                'cellHeight' => 80,
                'verticalMargin' => 10,
                'horizontalMargin' => 10,
                'minRow' => 1,
                'acceptWidgets' => true,
                'resizable' => ['handles' => 'se, sw'],
                'animate' => true,
                'float' => false
            ],
            'default_size' => ['w' => 12, 'h' => 4],
            'widget_constraints' => [
                'allowed_types' => ['text', 'image', 'counter', 'gallery', 'form', 'video'],
                'max_widgets' => null,
                'default_widget_size' => ['w' => 12, 'h' => 3],
                'column_layout' => false
            ],
            'styling' => [
                'default_padding' => ['top' => 40, 'bottom' => 40, 'left' => 0, 'right' => 0],
                'default_margin' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'default_background' => '#ffffff',
                'container_class' => 'container-fluid'
            ]
        ],

        'multi-column' => [
            'name' => 'Multi Column',
            'icon' => 'ri-layout-grid-line',
            'description' => 'Flexible multi-column layout for content organization',
            'category' => 'layout',
            'grid_config' => [
                'column' => 12,
                'cellHeight' => 80,
                'verticalMargin' => 10,
                'horizontalMargin' => 10,
                'minRow' => 1,
                'acceptWidgets' => true,
                'resizable' => ['handles' => 'se, sw, ne, nw'],
                'animate' => true,
                'float' => true
            ],
            'default_size' => ['w' => 12, 'h' => 4],
            'widget_constraints' => [
                'allowed_types' => ['text', 'image', 'counter', 'gallery', 'form', 'video'],
                'max_widgets' => null,
                'default_widget_size' => ['w' => 6, 'h' => 3],
                'column_layout' => true,
                'columns' => [
                    ['span' => 4, 'offset' => 0],
                    ['span' => 4, 'offset' => 4],
                    ['span' => 4, 'offset' => 8]
                ]
            ],
            'styling' => [
                'default_padding' => ['top' => 40, 'bottom' => 40, 'left' => 0, 'right' => 0],
                'default_margin' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'default_background' => '#f8f9fa',
                'container_class' => 'container'
            ]
        ],

        'sidebar-left' => [
            'name' => 'Sidebar Left',
            'icon' => 'ri-layout-left-line',
            'description' => 'Left sidebar with main content area',
            'category' => 'layout',
            'grid_config' => [
                'column' => 12,
                'cellHeight' => 80,
                'verticalMargin' => 10,
                'horizontalMargin' => 10,
                'minRow' => 1,
                'acceptWidgets' => true,
                'resizable' => ['handles' => 'se, sw'],
                'animate' => true,
                'float' => false
            ],
            'default_size' => ['w' => 12, 'h' => 4],
            'widget_constraints' => [
                'allowed_types' => ['text', 'image', 'counter', 'gallery', 'form', 'video', 'navigation'],
                'max_widgets' => null,
                'default_widget_size' => ['w' => 6, 'h' => 3],
                'column_layout' => true,
                'columns' => [
                    ['span' => 3, 'offset' => 0, 'area' => 'sidebar'],
                    ['span' => 9, 'offset' => 3, 'area' => 'main']
                ]
            ],
            'styling' => [
                'default_padding' => ['top' => 40, 'bottom' => 40, 'left' => 0, 'right' => 0],
                'default_margin' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'default_background' => '#ffffff',
                'container_class' => 'container',
                'sidebar_background' => '#f8f9fa'
            ]
        ],

        'sidebar-right' => [
            'name' => 'Sidebar Right',
            'icon' => 'ri-layout-right-line',
            'description' => 'Right sidebar with main content area',
            'category' => 'layout',
            'grid_config' => [
                'column' => 12,
                'cellHeight' => 80,
                'verticalMargin' => 10,
                'horizontalMargin' => 10,
                'minRow' => 1,
                'acceptWidgets' => true,
                'resizable' => ['handles' => 'se, sw'],
                'animate' => true,
                'float' => false
            ],
            'default_size' => ['w' => 12, 'h' => 4],
            'widget_constraints' => [
                'allowed_types' => ['text', 'image', 'counter', 'gallery', 'form', 'video', 'navigation'],
                'max_widgets' => null,
                'default_widget_size' => ['w' => 6, 'h' => 3],
                'column_layout' => true,
                'columns' => [
                    ['span' => 9, 'offset' => 0, 'area' => 'main'],
                    ['span' => 3, 'offset' => 9, 'area' => 'sidebar']
                ]
            ],
            'styling' => [
                'default_padding' => ['top' => 40, 'bottom' => 40, 'left' => 0, 'right' => 0],
                'default_margin' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'default_background' => '#ffffff',
                'container_class' => 'container',
                'sidebar_background' => '#f8f9fa'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Categories
    |--------------------------------------------------------------------------
    |
    | Categories for organizing section templates in the UI.
    |
    */
    'categories' => [
        'layout' => [
            'name' => 'Layout',
            'icon' => 'ri-layout-line',
            'description' => 'Basic layout structures'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    |
    | The default template to use when creating new sections.
    |
    */
    'default_template' => 'full-width',

    /*
    |--------------------------------------------------------------------------
    | Template Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validation rules for template configuration.
    |
    */
    'validation' => [
        'required_fields' => ['name', 'icon', 'description', 'grid_config', 'widget_constraints'],
        'grid_config_required' => ['column', 'cellHeight', 'acceptWidgets'],
        'widget_constraints_required' => ['allowed_types', 'default_widget_size']
    ]
]; 